<?php

namespace APP\plugins\generic\orcidEditorialBoard\classes;

use PKP\db\DAO;
use PKP\db\DAOResultFactory;
use PKP\config\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EditorialBoardMemberDAO extends DAO
{
    /** @var string|null Cached 32-byte encryption key */
    private ?string $_encKey = null;

    /** @var bool Whether we've already verified the upgrade columns exist */
    private static bool $_upgradeColumnsChecked = false;

    /**
     * Ensure upgrade columns exist (dispute_expires_at, previous_orcid_id).
     * Auto-creates them if the migration hasn't run yet (self-healing upgrade).
     * Cached per-request so the check only runs once.
     */
    private function ensureUpgradeColumns(): void
    {
        if (self::$_upgradeColumnsChecked) {
            return;
        }
        self::$_upgradeColumnsChecked = true;
        if (!Schema::hasColumn('editorial_board_members', 'dispute_expires_at')) {
            Schema::table('editorial_board_members', function (Blueprint $table) {
                $table->dateTime('dispute_expires_at')->nullable()->after('report_token_expires_at');
            });
        }
        if (!Schema::hasColumn('editorial_board_members', 'previous_orcid_id')) {
            Schema::table('editorial_board_members', function (Blueprint $table) {
                $table->text('previous_orcid_id')->nullable()->after('dispute_expires_at');
            });
        }
        if (!Schema::hasColumn('editorial_board_members', 'invitation_status')) {
            Schema::table('editorial_board_members', function (Blueprint $table) {
                $table->string('invitation_status', 30)->default('not_sent')->after('previous_orcid_id');
                $table->string('invitation_token', 64)->nullable()->after('invitation_status');
                $table->dateTime('invitation_token_expires_at')->nullable()->after('invitation_token');
                $table->dateTime('invitation_sent_at')->nullable()->after('invitation_token_expires_at');
                $table->dateTime('invitation_denied_at')->nullable()->after('invitation_sent_at');
                $table->text('invitation_deny_reason')->nullable()->after('invitation_denied_at');
            });
        }
    }

    /**
     * Derive a 32-byte AES-256 key from OJS config.
     * Uses api_key_secret → database password as fallback chain.
     * Returns null if no usable secret is found (encryption disabled).
     */
    private function getEncryptionKey(): ?string
    {
        if ($this->_encKey !== null) {
            return $this->_encKey ?: null;
        }
        $secret = Config::getVar('security', 'api_key_secret')
            ?: Config::getVar('database', 'password')
            ?: null;
        if (!$secret) {
            $this->_encKey = '';
            return null;
        }
        $this->_encKey = hash('sha256', 'eb-plugin-enc:' . $secret, true); // raw 32 bytes
        return $this->_encKey;
    }

    /**
     * Encrypt a value for storage using AES-256-CBC.
     * Returns the original value if encryption key is unavailable.
     * Format: "enc:" . base64( IV(16) . ciphertext )
     */
    private function safeEncrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }
        $key = $this->getEncryptionKey();
        if (!$key) {
            return $value; // No key available — store plaintext
        }
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            return $value; // Encryption failed — store plaintext
        }
        return 'enc:' . base64_encode($iv . $cipher);
    }

    /**
     * Decrypt a value from storage.
     * Handles both encrypted ("enc:" prefix) and legacy plaintext values gracefully.
     */
    private function safeDecrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }
        // Only attempt decryption if the value has our marker prefix
        if (strncmp($value, 'enc:', 4) !== 0) {
            return $value; // Legacy plaintext — return as-is
        }
        $key = $this->getEncryptionKey();
        if (!$key) {
            return $value; // No key — can't decrypt, return raw
        }
        $decoded = base64_decode(substr($value, 4), true);
        if ($decoded === false || strlen($decoded) < 17) {
            return $value; // Malformed — return raw
        }
        $iv = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);
        $plain = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            return $value; // Decryption failed (wrong key?) — return raw
        }
        return $plain;
    }
    public function newDataObject(): EditorialBoardMember
    {
        return new EditorialBoardMember();
    }

    public function fromRow(array $row): EditorialBoardMember
    {
        $member = $this->newDataObject();
        $member->setId((int) $row['id']);
        $member->setContextId((int) $row['context_id']);
        $member->setFullName($row['full_name']);
        $member->setRole($row['role']);
        $member->setEmail($this->safeDecrypt($row['email']));
        $member->setScopusId($row['scopus_id']);
        $member->setOrcidId($this->safeDecrypt($row['orcid_id']));
        $member->setGoogleScholar($row['google_scholar']);
        $member->setPhotoUrl($row['photo_url']);
        $member->setAffiliation($row['affiliation']);
        $member->setCountry($row['country'] ?? '');
        $member->setSortOrder((int) $row['sort_order']);
        $member->setConsentToken($row['consent_token']);
        $member->setConsentTokenExpires($row['consent_token_expires']);
        $member->setEmploymentAddedAt($row['employment_added_at']);
        $member->setOrcidVerified((bool) $row['orcid_verified']);
        $member->setOrcidVerifiedCachedAt($row['orcid_verified_cached_at']);
        $member->setOpenalexId($row['openalex_id'] ?? null);
        $member->setOpenalexKeywords(isset($row['openalex_keywords']) ? json_decode($row['openalex_keywords'], true) : null);
        $member->setOpenalexFetchedAt($row['openalex_fetched_at'] ?? null);
        $member->setOpenalexPayload(isset($row['openalex_payload']) ? json_decode($row['openalex_payload'], true) : null);
        $member->setOpenalexAffiliation($row['openalex_affiliation'] ?? null);
        $member->setOpenalexCountry($row['openalex_country'] ?? null);
        $member->setCoiStatus($row['coi_status'] ?? 'pending');
        $member->setCoiText($row['coi_text'] ?? null);
        $member->setCoiDeclaredAt($row['coi_declared_at'] ?? null);
        $member->setCoiToken($row['coi_token'] ?? null);
        $member->setCoiTokenExpiresAt($row['coi_token_expires_at'] ?? null);
        $member->setTenureStart($row['tenure_start'] ?? null);
        $member->setTenureEnd($row['tenure_end'] ?? null);
        $member->setTenureStatus($row['tenure_status'] ?? 'active');
        $member->setIsVisible(isset($row['is_visible']) ? (bool) $row['is_visible'] : true);
        $member->setLastReminderSentAt($row['last_reminder_sent_at'] ?? null);
        $member->setOrcidAccessToken($this->safeDecrypt($row['orcid_access_token'] ?? null));
        $member->setOrcidAuthName($row['orcid_auth_name'] ?? null);
        $member->setStatus($row['status'] ?? 'active');
        $member->setReportToken($row['report_token'] ?? null);
        $member->setReportTokenExpiresAt($row['report_token_expires_at'] ?? null);
        $member->setDisputeExpiresAt($row['dispute_expires_at'] ?? null);
        $member->setPreviousOrcidId($this->safeDecrypt($row['previous_orcid_id'] ?? null));
        $member->setInvitationStatus($row['invitation_status'] ?? 'not_sent');
        $member->setInvitationToken($row['invitation_token'] ?? null);
        $member->setInvitationTokenExpiresAt($row['invitation_token_expires_at'] ?? null);
        $member->setInvitationSentAt($row['invitation_sent_at'] ?? null);
        $member->setInvitationDeniedAt($row['invitation_denied_at'] ?? null);
        $member->setInvitationDenyReason($row['invitation_deny_reason'] ?? null);
        return $member;
    }

    public function getById(int $id, ?int $contextId = null): ?EditorialBoardMember
    {
        $params = [$id];
        $sql = 'SELECT * FROM editorial_board_members WHERE id = ?';
        if ($contextId) {
            $sql .= ' AND context_id = ?';
            $params[] = $contextId;
        }
        $result = $this->retrieve($sql, $params);
        $row = $result->current();
        return $row ? $this->fromRow((array) $row) : null;
    }

    public function getByContextId(int $contextId, $rangeInfo = null): DAOResultFactory
    {
        $result = $this->retrieveRange(
            'SELECT * FROM editorial_board_members WHERE context_id = ? ORDER BY sort_order ASC, id ASC',
            [$contextId],
            $rangeInfo
        );
        return new DAOResultFactory($result, $this, 'fromRow');
    }

    public function getByConsentToken(string $token): ?EditorialBoardMember
    {
        $result = $this->retrieve(
            'SELECT * FROM editorial_board_members WHERE consent_token = ?',
            [$token]
        );
        $row = $result->current();
        return $row ? $this->fromRow((array) $row) : null;
    }

    public function insertObject(EditorialBoardMember $member): int
    {
        $this->ensureUpgradeColumns();
        $this->update(
            'INSERT INTO editorial_board_members
                (context_id, role, email, full_name, scopus_id, orcid_id, google_scholar, photo_url, affiliation, country, sort_order, consent_token, consent_token_expires, employment_added_at, orcid_verified, orcid_verified_cached_at, openalex_id, openalex_keywords, openalex_fetched_at, openalex_payload, openalex_affiliation, openalex_country, coi_status, coi_text, coi_declared_at, coi_token, coi_token_expires_at, tenure_start, tenure_end, tenure_status, is_visible, last_reminder_sent_at, orcid_access_token, orcid_auth_name, status, report_token, report_token_expires_at, dispute_expires_at, previous_orcid_id, invitation_status, invitation_token, invitation_token_expires_at, invitation_sent_at, invitation_denied_at, invitation_deny_reason)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $member->getContextId(),
                $member->getRole(),
                $this->safeEncrypt($member->getEmail()),
                $member->getFullName(),
                $member->getScopusId(),
                $this->safeEncrypt($member->getOrcidId()),
                $member->getGoogleScholar(),
                $member->getPhotoUrl(),
                $member->getAffiliation(),
                $member->getCountry(),
                $member->getSortOrder(),
                $member->getConsentToken(),
                $member->getConsentTokenExpires(),
                $member->getEmploymentAddedAt(),
                $member->getOrcidVerified() ? 1 : 0,
                $member->getOrcidVerifiedCachedAt(),
                $member->getOpenalexId(),
                $member->getOpenalexKeywords() ? json_encode($member->getOpenalexKeywords()) : null,
                $member->getOpenalexFetchedAt(),
                $member->getOpenalexPayload() ? json_encode($member->getOpenalexPayload()) : null,
                $member->getOpenalexAffiliation(),
                $member->getOpenalexCountry(),
                $member->getCoiStatus(),
                $member->getCoiText(),
                $member->getCoiDeclaredAt(),
                $member->getCoiToken(),
                $member->getCoiTokenExpiresAt(),
                $member->getTenureStart(),
                $member->getTenureEnd(),
                $member->getTenureStatus(),
                $member->getIsVisible() ? 1 : 0,
                $member->getLastReminderSentAt(),
                $this->safeEncrypt($member->getOrcidAccessToken()),
                $member->getOrcidAuthName(),
                $member->getStatus(),
                $member->getReportToken(),
                $member->getReportTokenExpiresAt(),
                $member->getDisputeExpiresAt(),
                $this->safeEncrypt($member->getPreviousOrcidId()),
                $member->getInvitationStatus(),
                $member->getInvitationToken(),
                $member->getInvitationTokenExpiresAt(),
                $member->getInvitationSentAt(),
                $member->getInvitationDeniedAt(),
                $member->getInvitationDenyReason(),
            ]
        );

        $member->setId((int) $this->getInsertId());
        return $member->getId();
    }

    public function updateObject(EditorialBoardMember $member): void
    {
        $this->ensureUpgradeColumns();
        $this->update(
            'UPDATE editorial_board_members
                SET context_id = ?, role = ?, email = ?, full_name = ?, scopus_id = ?, orcid_id = ?, google_scholar = ?, photo_url = ?, affiliation = ?, country = ?, sort_order = ?, consent_token = ?, consent_token_expires = ?, employment_added_at = ?, orcid_verified = ?, orcid_verified_cached_at = ?, openalex_id = ?, openalex_keywords = ?, openalex_fetched_at = ?, openalex_payload = ?, openalex_affiliation = ?, openalex_country = ?, coi_status = ?, coi_text = ?, coi_declared_at = ?, coi_token = ?, coi_token_expires_at = ?, tenure_start = ?, tenure_end = ?, tenure_status = ?, is_visible = ?, last_reminder_sent_at = ?, orcid_access_token = ?, orcid_auth_name = ?, status = ?, report_token = ?, report_token_expires_at = ?, dispute_expires_at = ?, previous_orcid_id = ?, invitation_status = ?, invitation_token = ?, invitation_token_expires_at = ?, invitation_sent_at = ?, invitation_denied_at = ?, invitation_deny_reason = ?
             WHERE id = ?',
            [
                $member->getContextId(),
                $member->getRole(),
                $this->safeEncrypt($member->getEmail()),
                $member->getFullName(),
                $member->getScopusId(),
                $this->safeEncrypt($member->getOrcidId()),
                $member->getGoogleScholar(),
                $member->getPhotoUrl(),
                $member->getAffiliation(),
                $member->getCountry(),
                $member->getSortOrder(),
                $member->getConsentToken(),
                $member->getConsentTokenExpires(),
                $member->getEmploymentAddedAt(),
                $member->getOrcidVerified() ? 1 : 0,
                $member->getOrcidVerifiedCachedAt(),
                $member->getOpenalexId(),
                $member->getOpenalexKeywords() ? json_encode($member->getOpenalexKeywords()) : null,
                $member->getOpenalexFetchedAt(),
                $member->getOpenalexPayload() ? json_encode($member->getOpenalexPayload()) : null,
                $member->getOpenalexAffiliation(),
                $member->getOpenalexCountry(),
                $member->getCoiStatus(),
                $member->getCoiText(),
                $member->getCoiDeclaredAt(),
                $member->getCoiToken(),
                $member->getCoiTokenExpiresAt(),
                $member->getTenureStart(),
                $member->getTenureEnd(),
                $member->getTenureStatus(),
                $member->getIsVisible() ? 1 : 0,
                $member->getLastReminderSentAt(),
                $this->safeEncrypt($member->getOrcidAccessToken()),
                $member->getOrcidAuthName(),
                $member->getStatus(),
                $member->getReportToken(),
                $member->getReportTokenExpiresAt(),
                $member->getDisputeExpiresAt(),
                $this->safeEncrypt($member->getPreviousOrcidId()),
                $member->getInvitationStatus(),
                $member->getInvitationToken(),
                $member->getInvitationTokenExpiresAt(),
                $member->getInvitationSentAt(),
                $member->getInvitationDeniedAt(),
                $member->getInvitationDenyReason(),
                $member->getId(),
            ]
        );
    }

    public function getByInvitationToken(string $token): ?EditorialBoardMember
    {
        $result = $this->retrieve(
            'SELECT * FROM editorial_board_members WHERE invitation_token = ?',
            [$token]
        );
        $row = $result->current();
        return $row ? $this->fromRow((array) $row) : null;
    }

    public function getByCoiToken(string $token): ?EditorialBoardMember
    {
        $result = $this->retrieve(
            'SELECT * FROM editorial_board_members WHERE coi_token = ?',
            [$token]
        );
        $row = $result->current();
        return $row ? $this->fromRow((array) $row) : null;
    }

    public function getByReportToken(string $token, ?int $contextId = null): ?EditorialBoardMember
    {
        $params = [$token];
        $sql = 'SELECT * FROM editorial_board_members WHERE report_token = ?';
        if ($contextId) {
            $sql .= ' AND context_id = ?';
            $params[] = $contextId;
        }
        $result = $this->retrieve($sql, $params);
        $row = $result->current();
        return $row ? $this->fromRow((array) $row) : null;
    }

    public function deleteById(int $id): void
    {
        $this->update('DELETE FROM editorial_board_members WHERE id = ?', [$id]);
    }
}
