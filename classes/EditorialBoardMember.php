<?php

namespace APP\plugins\generic\orcidEditorialBoard\classes;

use PKP\core\DataObject;

class EditorialBoardMember extends DataObject
{
    public function getContextId(): int
    {
        return (int) $this->getData('contextId');
    }

    public function setContextId(int $contextId): void
    {
        $this->setData('contextId', $contextId);
    }

    public function getFullName(): string
    {
        return (string) $this->getData('fullName');
    }

    public function setFullName(string $fullName): void
    {
        $this->setData('fullName', $fullName);
    }

    public function getRole(): string
    {
        return (string) $this->getData('role');
    }

    public function setRole(string $role): void
    {
        $this->setData('role', $role);
    }

    public function getEmail(): string
    {
        return (string) $this->getData('email');
    }

    public function setEmail(string $email): void
    {
        $this->setData('email', $email);
    }

    public function getScopusId(): ?string
    {
        return $this->getData('scopusId');
    }

    public function setScopusId(?string $scopusId): void
    {
        $this->setData('scopusId', $scopusId);
    }

    public function getOrcidId(): ?string
    {
        return $this->getData('orcidId');
    }

    public function setOrcidId(?string $orcidId): void
    {
        $this->setData('orcidId', $orcidId);
    }

    public function getGoogleScholar(): ?string
    {
        return $this->getData('googleScholar');
    }

    public function setGoogleScholar(?string $googleScholar): void
    {
        $this->setData('googleScholar', $googleScholar);
    }

    public function getPhotoUrl(): ?string
    {
        return $this->getData('photoUrl');
    }

    public function setPhotoUrl(?string $photoUrl): void
    {
        $this->setData('photoUrl', $photoUrl);
    }

    public function getAffiliation(): ?string
    {
        return $this->getData('affiliation');
    }

    public function setAffiliation(?string $affiliation): void
    {
        $this->setData('affiliation', $affiliation);
    }

    public function getCountry(): string
    {
        return (string) $this->getData('country');
    }

    public function setCountry(string $country): void
    {
        $this->setData('country', $country);
    }

    public function getSortOrder(): int
    {
        return (int) $this->getData('sortOrder');
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->setData('sortOrder', $sortOrder);
    }

    public function getConsentToken(): ?string
    {
        return $this->getData('consentToken');
    }

    public function setConsentToken(?string $token): void
    {
        $this->setData('consentToken', $token);
    }

    public function getConsentTokenExpires(): ?string
    {
        return $this->getData('consentTokenExpires');
    }

    public function setConsentTokenExpires(?string $expires): void
    {
        $this->setData('consentTokenExpires', $expires);
    }

    public function getEmploymentAddedAt(): ?string
    {
        return $this->getData('employmentAddedAt');
    }

    public function setEmploymentAddedAt(?string $timestamp): void
    {
        $this->setData('employmentAddedAt', $timestamp);
    }

    public function getOrcidVerified(): bool
    {
        return (bool) $this->getData('orcidVerified');
    }

    public function setOrcidVerified(bool $verified): void
    {
        $this->setData('orcidVerified', $verified);
    }

    public function getOrcidVerifiedCachedAt(): ?string
    {
        return $this->getData('orcidVerifiedCachedAt');
    }

    public function setOrcidVerifiedCachedAt(?string $timestamp): void
    {
        $this->setData('orcidVerifiedCachedAt', $timestamp);
    }

    public function getOpenalexId(): ?string
    {
        return $this->getData('openalexId');
    }

    public function setOpenalexId(?string $openalexId): void
    {
        $this->setData('openalexId', $openalexId);
    }

    /**
     * Returns decoded keywords array or null.
     */
    public function getOpenalexKeywords(): ?array
    {
        $raw = $this->getData('openalexKeywords');
        if ($raw === null) {
            return null;
        }
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function setOpenalexKeywords(?array $keywords): void
    {
        $this->setData('openalexKeywords', $keywords);
    }

    public function getOpenalexFetchedAt(): ?string
    {
        return $this->getData('openalexFetchedAt');
    }

    public function setOpenalexFetchedAt(?string $timestamp): void
    {
        $this->setData('openalexFetchedAt', $timestamp);
    }

    /**
     * Raw payload stored (trimmed OpenAlex author JSON). Returns array|null.
     */
    public function getOpenalexPayload(): ?array
    {
        $raw = $this->getData('openalexPayload');
        if ($raw === null) {
            return null;
        }
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function setOpenalexPayload(?array $payload): void
    {
        $this->setData('openalexPayload', $payload);
    }

    public function getOpenalexAffiliation(): ?string
    {
        return $this->getData('openalexAffiliation');
    }

    public function setOpenalexAffiliation(?string $affiliation): void
    {
        $this->setData('openalexAffiliation', $affiliation);
    }

    public function getOpenalexCountry(): ?string
    {
        return $this->getData('openalexCountry');
    }

    public function setOpenalexCountry(?string $country): void
    {
        $this->setData('openalexCountry', $country);
    }

    // ── COI fields ──

    public function getCoiStatus(): string
    {
        return (string) ($this->getData('coiStatus') ?: 'pending');
    }

    public function setCoiStatus(string $status): void
    {
        $this->setData('coiStatus', $status);
    }

    public function getCoiText(): ?string
    {
        return $this->getData('coiText');
    }

    public function setCoiText(?string $text): void
    {
        $this->setData('coiText', $text);
    }

    public function getCoiDeclaredAt(): ?string
    {
        return $this->getData('coiDeclaredAt');
    }

    public function setCoiDeclaredAt(?string $timestamp): void
    {
        $this->setData('coiDeclaredAt', $timestamp);
    }

    public function getCoiToken(): ?string
    {
        return $this->getData('coiToken');
    }

    public function setCoiToken(?string $token): void
    {
        $this->setData('coiToken', $token);
    }

    public function getCoiTokenExpiresAt(): ?string
    {
        return $this->getData('coiTokenExpiresAt');
    }

    public function setCoiTokenExpiresAt(?string $expires): void
    {
        $this->setData('coiTokenExpiresAt', $expires);
    }

    // ── Tenure fields ──

    public function getTenureStart(): ?string
    {
        return $this->getData('tenureStart');
    }

    public function setTenureStart(?string $date): void
    {
        $this->setData('tenureStart', $date);
    }

    public function getTenureEnd(): ?string
    {
        return $this->getData('tenureEnd');
    }

    public function setTenureEnd(?string $date): void
    {
        $this->setData('tenureEnd', $date);
    }

    public function getTenureStatus(): string
    {
        return (string) ($this->getData('tenureStatus') ?: 'active');
    }

    public function setTenureStatus(string $status): void
    {
        $this->setData('tenureStatus', $status);
    }

    // ── Visibility ──

    public function getIsVisible(): bool
    {
        $val = $this->getData('isVisible');
        return $val === null ? true : (bool) $val;
    }

    public function setIsVisible(bool $visible): void
    {
        $this->setData('isVisible', $visible);
    }

    // ── Reminder tracking ──

    public function getLastReminderSentAt(): ?string
    {
        return $this->getData('lastReminderSentAt');
    }

    public function setLastReminderSentAt(?string $timestamp): void
    {
        $this->setData('lastReminderSentAt', $timestamp);
    }

    // ── ORCID Public API fields ──

    public function getOrcidAccessToken(): ?string
    {
        return $this->getData('orcidAccessToken');
    }

    public function setOrcidAccessToken(?string $token): void
    {
        $this->setData('orcidAccessToken', $token);
    }

    public function getOrcidAuthName(): ?string
    {
        return $this->getData('orcidAuthName');
    }

    public function setOrcidAuthName(?string $name): void
    {
        $this->setData('orcidAuthName', $name);
    }

    /**
     * Consent fingerprint: truncated SHA-256 hash of the access token + member ID.
     * Returns null if no access token is stored.
     */
    public function getConsentFingerprint(): ?string
    {
        $token = $this->getOrcidAccessToken();
        if (!$token) {
            return null;
        }
        return substr(hash('sha256', $token . $this->getId()), 0, 12);
    }

    // ── Dispute / false-claim fields ──

    public function getStatus(): string
    {
        return (string) ($this->getData('status') ?: 'active');
    }

    public function setStatus(string $status): void
    {
        $this->setData('status', $status);
    }

    public function getReportToken(): ?string
    {
        return $this->getData('reportToken');
    }

    public function setReportToken(?string $token): void
    {
        $this->setData('reportToken', $token);
    }

    public function getReportTokenExpiresAt(): ?string
    {
        return $this->getData('reportTokenExpiresAt');
    }

    public function setReportTokenExpiresAt(?string $expires): void
    {
        $this->setData('reportTokenExpiresAt', $expires);
    }

    // ── Dispute window ──

    public function getDisputeExpiresAt(): ?string
    {
        return $this->getData('disputeExpiresAt');
    }

    public function setDisputeExpiresAt(?string $expires): void
    {
        $this->setData('disputeExpiresAt', $expires);
    }

    // ── Previous ORCID (preserved when admin changes ORCID so original owner can dispute) ──

    public function getPreviousOrcidId(): ?string
    {
        return $this->getData('previousOrcidId');
    }

    public function setPreviousOrcidId(?string $orcid): void
    {
        $this->setData('previousOrcidId', $orcid);
    }

    // ── Invitation workflow fields ──

    public function getInvitationStatus(): string
    {
        return (string) ($this->getData('invitationStatus') ?: 'not_sent');
    }

    public function setInvitationStatus(string $status): void
    {
        $this->setData('invitationStatus', $status);
    }

    public function getInvitationToken(): ?string
    {
        return $this->getData('invitationToken');
    }

    public function setInvitationToken(?string $token): void
    {
        $this->setData('invitationToken', $token);
    }

    public function getInvitationTokenExpiresAt(): ?string
    {
        return $this->getData('invitationTokenExpiresAt');
    }

    public function setInvitationTokenExpiresAt(?string $expires): void
    {
        $this->setData('invitationTokenExpiresAt', $expires);
    }

    public function getInvitationSentAt(): ?string
    {
        return $this->getData('invitationSentAt');
    }

    public function setInvitationSentAt(?string $timestamp): void
    {
        $this->setData('invitationSentAt', $timestamp);
    }

    public function getInvitationDeniedAt(): ?string
    {
        return $this->getData('invitationDeniedAt');
    }

    public function setInvitationDeniedAt(?string $timestamp): void
    {
        $this->setData('invitationDeniedAt', $timestamp);
    }

    public function getInvitationDenyReason(): ?string
    {
        return $this->getData('invitationDenyReason');
    }

    public function setInvitationDenyReason(?string $reason): void
    {
        $this->setData('invitationDenyReason', $reason);
    }
}
