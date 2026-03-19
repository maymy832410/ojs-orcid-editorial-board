<?php

namespace APP\plugins\generic\orcidEditorialBoard\controllers\grid;

use APP\plugins\generic\orcidEditorialBoard\controllers\grid\form\EditorialBoardMemberForm;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMemberDAO;
use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardConsentRequest;
use APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardChangeNotification;
use PKP\db\DAO;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use APP\core\Application;
use APP\template\TemplateManager;
use PKP\controllers\grid\GridColumn;
use PKP\controllers\grid\GridHandler;
use PKP\core\JSONMessage;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;
use PKP\plugins\PluginRegistry;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\Role;

class EditorialBoardGridHandler extends GridHandler
{
    /** @var OrcidEditorialBoardPlugin */
    private $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER],
            [
                'index',
                'fetchGrid',
                'fetchRow',
                'addMember',
                'editMember',
                'updateMember',
                'deleteMember',
                'sendConsentEmail',
                'searchOpenAlex',
                'selectOpenAlexAuthor',
                // Wizard endpoints
                'startWizard',
                'wizardSearch',
                'wizardAdd',
                'wizardRemove',
                'wizardUpdate',
                'wizardFinalize',
                'sendCoiEmail',
                'toggleVisibility',
                'checkTenureExpiry',
                'verifyCredentials',
            ]
        );
        $this->plugin = PluginRegistry::getPlugin('generic', ORCID_EDITORIAL_BOARD_PLUGIN_NAME);
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);
        $context = $request->getContext();

        $this->setTitle('plugins.generic.orcidEditorialBoard.displayName');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */

        $members = [];
        if ($dao && $context) {
            $result = $dao->getByContextId($context->getId());
            while ($member = $result->next()) {
                $members[$member->getId()] = $member;
            }
        }

        $this->setGridDataElements($members);
        $this->setEmptyRowText('plugins.generic.orcidEditorialBoard.none');

        $router = $request->getRouter();
        $this->addAction(
            new LinkAction(
                'addMember',
                new AjaxModal(
                    $router->url($request, null, null, 'startWizard'),
                    __('plugins.generic.orcidEditorialBoard.grid.addMember'),
                    'modal_add_item'
                ),
                __('plugins.generic.orcidEditorialBoard.grid.addMember'),
                'add_item'
            )
        );

        $this->addAction(
            new LinkAction(
                'checkTenureExpiry',
                new RemoteActionConfirmationModal(
                    $request->getSession(),
                    __('plugins.generic.orcidEditorialBoard.tenure.checkExpiry.confirm'),
                    __('plugins.generic.orcidEditorialBoard.tenure.checkExpiry'),
                    $router->url($request, null, null, 'checkTenureExpiry'),
                    'modal_information'
                ),
                __('plugins.generic.orcidEditorialBoard.tenure.checkExpiry'),
                'reload'
            )
        );

        $cellProvider = new EditorialBoardGridCellProvider();
        $this->addColumn(new GridColumn('fullName', 'common.name', null, null, $cellProvider));
        $this->addColumn(new GridColumn('role', 'common.role', null, null, $cellProvider));
        $this->addColumn(new GridColumn('email', 'user.email', null, null, $cellProvider));
        $this->addColumn(new GridColumn('orcid', 'plugins.generic.orcidEditorialBoard.orcidStatus', null, null, $cellProvider));
        $this->addColumn(new GridColumn('invitationStatus', 'plugins.generic.orcidEditorialBoard.grid.invitationColumn', null, null, $cellProvider));
    }

    protected function getRowInstance()
    {
        return new EditorialBoardGridRow($this->plugin);
    }

    public function addMember($args, $request)
    {
        $context = $request->getContext();
        $form = new EditorialBoardMemberForm(
            $this->plugin->getTemplateResource('editorialBoardMemberForm.tpl'),
            $context->getId(),
            $this->plugin
        );
        $form->initData();
        return new JSONMessage(true, $form->fetch($request));
    }

    public function editMember($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $form = new EditorialBoardMemberForm(
            $this->plugin->getTemplateResource('editorialBoardMemberForm.tpl'),
            $context->getId(),
            $this->plugin,
            $memberId
        );
        $form->initData();
        return new JSONMessage(true, $form->fetch($request));
    }

    public function updateMember($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');

        // ── Snapshot identity fields BEFORE update for re-verification check ──
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $oldMember = $memberId ? $dao->getById($memberId, $context->getId()) : null;
        $oldIdentity = $oldMember ? [
            'fullName'    => $oldMember->getFullName(),
            'role'        => $oldMember->getRole(),
            'affiliation' => $oldMember->getAffiliation(),
            'orcidId'     => $oldMember->getOrcidId(),
        ] : null;
        $wasVerified = $oldMember && $oldMember->getOrcidVerified();

        $form = new EditorialBoardMemberForm(
            $this->plugin->getTemplateResource('editorialBoardMemberForm.tpl'),
            $context->getId(),
            $this->plugin,
            $memberId
        );
        $form->readInputData();
        if ($form->validate()) {
            $form->execute(...$args);

            // Re-read the updated member
            $updatedMember = $memberId ? $dao->getById($memberId, $context->getId()) : null;
            if (!$updatedMember) {
                return DAO::getDataChangedEvent($memberId ?: null);
            }

            // ── Always reset status to 'active' when admin saves a record ──
            if ($updatedMember->getStatus() === 'removed_by_owner') {
                $updatedMember->setStatus('active');
                OrcidEditorialBoardPlugin::log('Status reset to active for member ' . $memberId . ' (admin edit).');
            }

            // ── Re-verification: compare identity fields ──
            $identityChanged = false;
            $consentUrl = null;
            if ($oldIdentity && $wasVerified && $memberId) {
                $newIdentity = [
                    'fullName'    => $updatedMember->getFullName(),
                    'role'        => $updatedMember->getRole(),
                    'affiliation' => $updatedMember->getAffiliation(),
                    'orcidId'     => $updatedMember->getOrcidId(),
                ];
                if ($oldIdentity !== $newIdentity) {
                    $identityChanged = true;

                    // Preserve old ORCID so the original owner can still dispute
                    $oldOrcid = $oldIdentity['orcidId'] ?? null;
                    $newOrcid = $newIdentity['orcidId'] ?? null;
                    if ($oldOrcid && $oldOrcid !== $newOrcid) {
                        $updatedMember->setPreviousOrcidId($oldOrcid);
                        OrcidEditorialBoardPlugin::log('Preserved previous ORCID for member ' . $memberId . ': ' . $oldOrcid);
                    }

                    OrcidEditorialBoardPlugin::log('Identity fields changed for member ' . $memberId . '; invalidating verification and triggering re-verification.');
                    $updatedMember->setOrcidVerified(false);
                    $updatedMember->setOrcidAccessToken(null);
                    $updatedMember->setOrcidVerifiedCachedAt(null);
                    $token = bin2hex(random_bytes(32));
                    $updatedMember->setConsentToken($token);
                    $updatedMember->setConsentTokenExpires(Carbon::now()->addDays(7)->toDateTimeString());

                    // Reset COI declaration — identity changed, COI must be re-declared
                    $updatedMember->setCoiStatus('pending');
                    $updatedMember->setCoiText(null);
                    $updatedMember->setCoiDeclaredAt(null);
                    $coiToken = bin2hex(random_bytes(32));
                    $updatedMember->setCoiToken($coiToken);
                    $updatedMember->setCoiTokenExpiresAt(Carbon::now()->addDays(7)->toDateTimeString());
                    OrcidEditorialBoardPlugin::log('COI declaration reset for member ' . $memberId . ' due to identity change.');

                    // Build consent URL for the combined email
                    if ($this->plugin->isOrcidApiConfigured()) {
                        $dispatcher = $request->getDispatcher();
                        $consentUrl = $dispatcher->url(
                            $request,
                            Application::ROUTE_PAGE,
                            $context->getPath(),
                            'editorialBoard',
                            'consent',
                            null,
                            ['token' => $token]
                        );
                    }

                    // Build COI re-declaration URL for the combined email
                    $dispatcher = $dispatcher ?? $request->getDispatcher();
                    $coiUrl = $dispatcher->url(
                        $request,
                        Application::ROUTE_PAGE,
                        $context->getPath(),
                        'editorialBoard',
                        'coiDeclare',
                        null,
                        ['token' => $coiToken]
                    );
                }
            }

            // ── Detect all field changes for notification ──
            $changedFields = [];
            if ($oldMember && $memberId) {
                $trackFields = [
                    'fullName'    => ['get' => 'getFullName',    'label' => 'Name'],
                    'role'        => ['get' => 'getRole',        'label' => 'Role'],
                    'affiliation' => ['get' => 'getAffiliation', 'label' => 'Affiliation'],
                    'orcidId'     => ['get' => 'getOrcidId',     'label' => 'ORCID iD'],
                    'email'       => ['get' => 'getEmail',       'label' => 'Email'],
                    'scopusId'    => ['get' => 'getScopusId',    'label' => 'Scopus ID'],
                    'googleScholar' => ['get' => 'getGoogleScholar', 'label' => 'Google Scholar'],
                    'photoUrl'    => ['get' => 'getPhotoUrl',    'label' => 'Photo URL'],
                    'country'     => ['get' => 'getCountry',     'label' => 'Country'],
                    'isVisible'   => ['get' => 'getIsVisible',   'label' => 'Visibility'],
                    'tenureStart' => ['get' => 'getTenureStart', 'label' => 'Tenure Start'],
                    'tenureEnd'   => ['get' => 'getTenureEnd',   'label' => 'Tenure End'],
                ];
                foreach ($trackFields as $key => $info) {
                    $getter = $info['get'];
                    $oldVal = $oldMember->$getter();
                    $newVal = $updatedMember->$getter();
                    if ($key === 'isVisible') {
                        $oldVal = $oldVal ? 'Yes' : 'No';
                        $newVal = $newVal ? 'Yes' : 'No';
                    }
                    if ((string) $oldVal !== (string) $newVal) {
                        $changedFields[] = [
                            'label' => $info['label'],
                            'old'   => (string) $oldVal ?: '(empty)',
                            'new'   => (string) $newVal ?: '(empty)',
                        ];
                    }
                }
            }

            // ── Set 7-day dispute window when any change is detected ──
            $disputeUrl = null;
            $approveUrl = null;
            if (!empty($changedFields)) {
                $updatedMember->setDisputeExpiresAt(Carbon::now()->addDays(7)->toDateTimeString());

                $dispatcher = $dispatcher ?? $request->getDispatcher();

                // Build approve URL only for non-identity edits (identity changes use ORCID re-verification instead)
                if (!$identityChanged) {
                    $approveSig = hash_hmac('sha256', 'approve:' . $memberId, \APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin::getHmacSecret());
                    $approveUrl = $dispatcher->url(
                        $request,
                        Application::ROUTE_PAGE,
                        $context->getPath(),
                        'editorialBoard',
                        'approveEdit',
                        null,
                        ['memberId' => $memberId, 'sig' => $approveSig]
                    );
                }

                // Build dispute URL — use current OR previous ORCID so original owner can dispute
                if ($updatedMember->getOrcidId() || $updatedMember->getPreviousOrcidId()) {
                    $reportSig = hash_hmac('sha256', 'report:' . $memberId, \APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin::getHmacSecret());
                    $disputeUrl = $dispatcher->url(
                        $request,
                        Application::ROUTE_PAGE,
                        $context->getPath(),
                        'editorialBoard',
                        'reportFalseClaim',
                        null,
                        ['memberId' => $memberId, 'sig' => $reportSig]
                    );
                }
            }

            // ── Persist all changes ──
            $dao->updateObject($updatedMember);

            // ── Send ONE combined notification email ──
            // Uses the same pattern as sendConsentEmail() which is proven to work:
            // 1. Construct mailable (addData only, no subject/body in constructor)
            // 2. Set from/to
            // 3. Set subject/body AFTER construction via chained call
            // 4. Mail::send()
            if (!empty($changedFields) && $updatedMember->getEmail()) {
                OrcidEditorialBoardPlugin::log('Change detected for member ' . $memberId
                    . ': ' . count($changedFields) . ' field(s) changed. Sending notification to ' . $updatedMember->getEmail());
                try {
                    $mailable = new EditorialBoardChangeNotification(
                        $context,
                        $updatedMember,
                        $changedFields,
                        $consentUrl,    // null if no identity change or ORCID not configured
                        $disputeUrl,    // null if member has no ORCID
                        $coiUrl ?? null, // null if no identity change (COI not reset)
                        $approveUrl     // lets the member approve changes and clear the pending badge
                    );
                    $mailable->from($context->getData('contactEmail'), $context->getData('contactName'));
                    $mailable->to($updatedMember->getEmail(), $updatedMember->getFullName());

                    // CC old email address if email was changed, so original owner is always notified
                    foreach ($changedFields as $cf) {
                        if ($cf['label'] === 'Email' && !empty($cf['old']) && $cf['old'] !== '(empty)' && $cf['old'] !== $updatedMember->getEmail()) {
                            $mailable->cc($cf['old']);
                            OrcidEditorialBoardPlugin::log('Also notifying previous email address: ' . $cf['old']);
                            break;
                        }
                    }

                    // Set subject and body AFTER construction (matching consent email pattern)
                    $mailable->subject(__('plugins.generic.orcidEditorialBoard.changeNotification.subject', [
                            'journalName' => $context->getLocalizedName(),
                        ]))
                        ->body(__('plugins.generic.orcidEditorialBoard.changeNotification.body'));
                    Mail::send($mailable);
                    OrcidEditorialBoardPlugin::log('Combined change notification email sent for member ' . $memberId
                        . ($consentUrl ? ' (includes re-verification link)' : '')
                        . ($disputeUrl ? ' (includes dispute link)' : ''));
                } catch (\Throwable $e) {
                    OrcidEditorialBoardPlugin::log('Change notification email error for member ' . $memberId
                        . ': ' . get_class($e) . ' — ' . $e->getMessage()
                        . ' at ' . $e->getFile() . ':' . $e->getLine());
                }
            } else {
                OrcidEditorialBoardPlugin::log('updateMember ' . $memberId
                    . ': no email sent (changedFields=' . count($changedFields)
                    . ', email=' . ($updatedMember->getEmail() ? 'set' : 'empty') . ')');
            }

            // ── Write audit log for admin edits ──
            if (!empty($changedFields) && $memberId) {
                $diffSummary = implode('; ', array_map(function ($cf) {
                    return $cf['label'] . ': ' . $cf['old'] . ' → ' . $cf['new'];
                }, $changedFields));
                $this->insertAuditRecord($memberId, 'admin_edit', $diffSummary);
            }

            // Signal the grid to refresh this row (or reload) and close the modal
            return DAO::getDataChangedEvent($memberId ?: null);
        }

        return new JSONMessage(false, $form->fetch($request));
    }

    public function deleteMember($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if ($member) {
            $dao->deleteById($memberId);
            // Tell the grid to refresh after deletion
            return DAO::getDataChangedEvent($memberId);
        }
        return new JSONMessage(false, __('common.error'));
    }

    public function sendConsentEmail($args, $request)
    {
        if (!$this->plugin->isOrcidApiConfigured()) {
            OrcidEditorialBoardPlugin::log('sendConsentEmail called but ORCID not configured');
            return new JSONMessage(false, __('plugins.generic.orcidEditorialBoard.error.orcidNotConfigured'));
        }

        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            OrcidEditorialBoardPlugin::log('sendConsentEmail: member not found ' . $memberId);
            return new JSONMessage(false, __('common.error'));
        }

        $token = bin2hex(random_bytes(32));
        $expires = Carbon::now()->addDays(7)->toDateTimeString();
        $member->setConsentToken($token);
        $member->setConsentTokenExpires($expires);
        $dao->updateObject($member);

        $dispatcher = $request->getDispatcher();
        $consentUrl = $dispatcher->url(
            $request,
            Application::ROUTE_PAGE,
            $context->getPath(),
            'editorialBoard',
            'consent',
            null,
            ['token' => $token]
        );

        try {
            $mailable = new EditorialBoardConsentRequest($context, $member, $consentUrl);
            $mailable->from($context->getData('contactEmail'), $context->getData('contactName'));
            $mailable->to($member->getEmail(), $member->getFullName());
            // Always use the plugin’s localized premium template body to avoid stale DB templates and unsupported Smarty tags.
            $mailable->subject(__('plugins.generic.orcidEditorialBoard.consentRequest.subject'))
                ->body(__('plugins.generic.orcidEditorialBoard.consentRequest.body'));
            Mail::send($mailable);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('Consent email error: ' . $e->getMessage());
            return new JSONMessage(false, 'Email sending failed: ' . $e->getMessage());
        }

        OrcidEditorialBoardPlugin::log('Consent email sent for member ' . $memberId);

        return new JSONMessage(true);
    }

    /**
     * Search OpenAlex authors for a member (modal list).
     */
    public function searchOpenAlex($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return new JSONMessage(false, __('common.error'));
        }

        $httpClient = Application::get()->getHttpClient();
        $searchUrl = '';
        $rawOrcid = $member->getOrcidId();

        // Clean and validate ORCID (0000-0000-0000-0000 or with X)
        $cleanOrcid = null;
        if ($rawOrcid) {
            $candidate = preg_replace('~^https?://orcid\\.org/~i', '', trim($rawOrcid));
            if (preg_match('~^\\d{4}-\\d{4}-\\d{4}-[\\dX]{3,4}$~', $candidate)) {
                $cleanOrcid = $candidate;
            }
        }

        if ($cleanOrcid) {
            $searchUrl = 'https://api.openalex.org/authors?filter=orcid:' . urlencode($cleanOrcid) . '&per_page=5';
        } else {
            // Fallback: search by name (and affiliation if available) to improve recall
            $query = $member->getFullName();
            if ($member->getAffiliation()) {
                $query .= ' ' . $member->getAffiliation();
            }
            $searchUrl = 'https://api.openalex.org/authors?search=' . urlencode($query) . '&per_page=5';
        }

        try {
            $response = $httpClient->request('GET', $searchUrl, ['timeout' => 10]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('OpenAlex search failed: ' . $e->getMessage());
            return new JSONMessage(false, __('common.error'));
        }

        $data = json_decode($response->getBody(), true);
        $results = [];
        foreach ($data['results'] ?? [] as $row) {
            $topics = [];
            foreach (array_slice($row['topics'] ?? [], 0, 5) as $t) {
                $topics[] = $t['display_name'];
            }
            $results[] = [
                'id' => $row['id'] ?? '',
                'openalexId' => isset($row['id']) ? basename($row['id']) : '',
                'display_name' => $row['display_name'] ?? '',
                'orcid' => $row['orcid'] ?? '',
                'works_count' => $row['works_count'] ?? 0,
                'cited_by_count' => $row['cited_by_count'] ?? 0,
                'h_index' => $row['summary_stats']['h_index'] ?? null,
                'institution' => $row['last_known_institutions'][0]['display_name'] ?? '',
                'country' => $row['last_known_institutions'][0]['country_code'] ?? '',
                'topics' => $topics,
            ];
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'member' => $member,
            'results' => $results,
        ]);
        $content = $templateMgr->fetch($this->plugin->getTemplateResource('openalexSearchResults.tpl'));

        return new JSONMessage(true, $content);
    }

    /**
     * Select an OpenAlex author for a member and cache keywords.
     */
    public function selectOpenAlexAuthor($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return new JSONMessage(false, __('common.error'));
        }

        $openalexId = (string) $request->getUserVar('openalexId') ?: $member->getOpenalexId();
        if (!$openalexId) {
            return new JSONMessage(false, __('common.error'));
        }

        $httpClient = Application::get()->getHttpClient();
        $authorUrl = 'https://api.openalex.org/authors/' . urlencode($openalexId);
        try {
            $response = $httpClient->request('GET', $authorUrl, ['timeout' => 10]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('OpenAlex fetch failed: ' . $e->getMessage());
            return new JSONMessage(false, __('common.error'));
        }

        $data = json_decode($response->getBody(), true);
        $topics = [];
        foreach (array_slice($data['topics'] ?? [], 0, 8) as $t) {
            $topics[] = $t['display_name'];
        }

        $member->setOpenalexId($openalexId);
        $member->setOpenalexKeywords($topics);
        $member->setOpenalexFetchedAt(Carbon::now()->toDateTimeString());
        $member->setOpenalexPayload($data);
        $member->setOpenalexAffiliation($data['last_known_institutions'][0]['display_name'] ?? null);
        $member->setOpenalexCountry($data['last_known_institutions'][0]['country_code'] ?? null);
        $dao->updateObject($member);

        return DAO::getDataChangedEvent($memberId);
    }

    // ---------------- Wizard helpers ----------------

    private function getWizardSessionKey($contextId): string
    {
        return 'orcidEditorialBoardWizard_' . (int) $contextId;
    }

    /**
     * Keep only the OpenAlex fields needed by finalize/email flows.
     */
    private function compactOpenalexPayload(array $data, array $topics = [], array $affiliations = []): array
    {
        $topicNames = [];
        if (!empty($topics)) {
            foreach (array_slice($topics, 0, 10) as $topic) {
                if (is_string($topic) && $topic !== '') {
                    $topicNames[] = $topic;
                } elseif (is_array($topic) && !empty($topic['display_name'])) {
                    $topicNames[] = $topic['display_name'];
                }
            }
        } else {
            foreach (array_slice($data['topics'] ?? [], 0, 10) as $topic) {
                if (!empty($topic['display_name'])) {
                    $topicNames[] = $topic['display_name'];
                }
            }
        }

        $compactAffiliations = [];
        foreach (array_slice($affiliations, 0, 5) as $aff) {
            if (!is_array($aff)) {
                continue;
            }
            $compactAffiliations[] = [
                'name' => (string) ($aff['name'] ?? ($aff['institution']['display_name'] ?? '')),
                'country' => (string) ($aff['country'] ?? ($aff['institution']['country_code'] ?? '')),
            ];
        }

        if (empty($compactAffiliations) && !empty($data['last_known_institutions'][0])) {
            $compactAffiliations[] = [
                'name' => (string) ($data['last_known_institutions'][0]['display_name'] ?? ''),
                'country' => (string) ($data['last_known_institutions'][0]['country_code'] ?? ''),
            ];
        }

        return [
            'id' => $data['id'] ?? null,
            'display_name' => $data['display_name'] ?? null,
            'orcid' => $data['orcid'] ?? null,
            'works_count' => $data['works_count'] ?? 0,
            'cited_by_count' => $data['cited_by_count'] ?? 0,
            'summary_stats' => [
                'h_index' => $data['summary_stats']['h_index'] ?? null,
            ],
            'topics' => $topicNames,
            'last_known_institutions' => [
                [
                    'display_name' => $data['last_known_institutions'][0]['display_name'] ?? ($compactAffiliations[0]['name'] ?? ''),
                    'country_code' => $data['last_known_institutions'][0]['country_code'] ?? ($compactAffiliations[0]['country'] ?? ''),
                ],
            ],
            'affiliations' => $compactAffiliations,
        ];
    }

    private function compactPayloadFromStagedEntry(array $entry): array
    {
        $payload = is_array($entry['payload'] ?? null) ? $entry['payload'] : [];

        $topicSource = $entry['topics'] ?? $entry['keywords'] ?? ($payload['topics'] ?? []);
        $affiliations = $entry['affiliations'] ?? ($payload['affiliations'] ?? []);

        $openalexId = (string) ($entry['openalexId'] ?? '');
        $defaultLastInstitution = [
            [
                'display_name' => $entry['selected_affiliation'] ?? $entry['affiliation'] ?? ($payload['last_known_institutions'][0]['display_name'] ?? ''),
                'country_code' => $entry['selected_country'] ?? $entry['country'] ?? ($payload['last_known_institutions'][0]['country_code'] ?? ''),
            ],
        ];

        $data = [
            'id' => $payload['id'] ?? ($openalexId ? ('https://api.openalex.org/authors/' . $openalexId) : null),
            'display_name' => $entry['display_name'] ?? $entry['fullName'] ?? ($payload['display_name'] ?? null),
            'orcid' => $entry['orcid'] ?? $entry['orcidId'] ?? ($payload['orcid'] ?? null),
            'works_count' => $entry['works_count'] ?? ($payload['works_count'] ?? 0),
            'cited_by_count' => $entry['cited_by_count'] ?? ($payload['cited_by_count'] ?? 0),
            'summary_stats' => [
                'h_index' => $entry['h_index'] ?? ($payload['summary_stats']['h_index'] ?? null),
            ],
            'last_known_institutions' => $payload['last_known_institutions'] ?? $defaultLastInstitution,
            'topics' => $payload['topics'] ?? [],
        ];

        return $this->compactOpenalexPayload($data, is_array($topicSource) ? $topicSource : [], is_array($affiliations) ? $affiliations : []);
    }

    private function getWizardState($request, $contextId): array
    {
        $session = $request->getSession();
        $state = $session->getSessionVar($this->getWizardSessionKey($contextId)) ?? [];
        if (!is_array($state) || empty($state)) {
            return [];
        }

        $dirty = false;
        $sanitized = [];
        foreach ($state as $openalexId => $entry) {
            if (!is_array($entry)) {
                $dirty = true;
                continue;
            }

            $entry['payload'] = $this->compactPayloadFromStagedEntry($entry);

            if (isset($entry['topics']) && is_array($entry['topics']) && count($entry['topics']) > 10) {
                $entry['topics'] = array_slice($entry['topics'], 0, 10);
                $dirty = true;
            }
            if (isset($entry['keywords']) && is_array($entry['keywords']) && count($entry['keywords']) > 10) {
                $entry['keywords'] = array_slice($entry['keywords'], 0, 10);
                $dirty = true;
            }
            if (isset($entry['affiliations']) && is_array($entry['affiliations']) && count($entry['affiliations']) > 5) {
                $entry['affiliations'] = array_slice($entry['affiliations'], 0, 5);
                $dirty = true;
            }

            $sanitized[$openalexId] = $entry;
        }

        if ($dirty || $sanitized !== $state) {
            $this->saveWizardState($request, $contextId, $sanitized);
        }

        return $sanitized;
    }

    private function saveWizardState($request, $contextId, array $state): void
    {
        $request->getSession()->setSessionVar($this->getWizardSessionKey($contextId), $state);
    }

    /**
     * Start wizard: render staging panel and search UI.
     */
    public function startWizard($args, $request)
    {
        $context = $request->getContext();
        $state = $this->getWizardState($request, $context->getId());

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'staged' => $state,
            'countryOptions' => $this->getCountryOptions(),
            'roleOptions' => [
                'Editor in Chief' => 'Editor in Chief',
                'Managing Editor' => 'Managing Editor',
                'Associate Editor' => 'Associate Editor',
                'Editorial Member' => 'Editorial Member',
            ],
            'plugin' => $this->plugin,
        ]);
        $content = $templateMgr->fetch($this->plugin->getTemplateResource('wizard.tpl'));
        return new JSONMessage(true, $content);
    }

    /**
     * Search OpenAlex for wizard (by query param q).
     */
    public function wizardSearch($args, $request)
    {
        $context = $request->getContext();
        $query = (string) $request->getUserVar('q');
        $page = max(1, (int) $request->getUserVar('page'));
        $perPage = 5;
        if (!$query) {
            return new JSONMessage(false, __('common.error'));
        }

        $httpClient = Application::get()->getHttpClient();
        $searchUrl = 'https://api.openalex.org/authors?search=' . urlencode($query) . '&per_page=' . $perPage . '&page=' . $page;

        try {
            $response = $httpClient->request('GET', $searchUrl, ['timeout' => 10]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('OpenAlex wizard search failed: ' . $e->getMessage());
            return new JSONMessage(false, __('common.error'));
        }

        $data = json_decode($response->getBody(), true);
        $results = [];
        foreach ($data['results'] ?? [] as $row) {
            $topics = [];
            foreach (array_slice($row['topics'] ?? [], 0, 5) as $t) {
                $topics[] = $t['display_name'];
            }
            $results[] = [
                'id' => $row['id'] ?? '',
                'openalexId' => isset($row['id']) ? basename($row['id']) : '',
                'display_name' => $row['display_name'] ?? '',
                'orcid' => $row['orcid'] ?? '',
                'works_count' => $row['works_count'] ?? 0,
                'cited_by_count' => $row['cited_by_count'] ?? 0,
                'h_index' => $row['summary_stats']['h_index'] ?? null,
                'institution' => $row['last_known_institutions'][0]['display_name'] ?? '',
                'country' => $row['last_known_institutions'][0]['country_code'] ?? '',
                'topics' => $topics,
            ];
        }

        $meta = [
            'count' => $data['meta']['count'] ?? 0,
            'page' => $data['meta']['page'] ?? $page,
            'per_page' => $data['meta']['per_page'] ?? $perPage,
        ];

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('results', $results);
        $templateMgr->assign('meta', $meta);
        $content = $templateMgr->fetch($this->plugin->getTemplateResource('wizardSearchResults.tpl'));
        return new JSONMessage(true, $content);
    }

    /**
     * Add an author to staging (fetch details).
     */
    public function wizardAdd($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false, __('common.error'));
        }

        $context = $request->getContext();
        $openalexId = (string) $request->getUserVar('openalexId');
        if (!$openalexId) {
            return new JSONMessage(false, __('common.error'));
        }

        $httpClient = Application::get()->getHttpClient();
        $authorUrl = 'https://api.openalex.org/authors/' . urlencode($openalexId);
        try {
            $response = $httpClient->request('GET', $authorUrl, ['timeout' => 10]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('OpenAlex wizard add fetch failed: ' . $e->getMessage());
            return new JSONMessage(false, __('common.error'));
        }
        $data = json_decode($response->getBody(), true);

        $topics = [];
        foreach (array_slice($data['topics'] ?? [], 0, 10) as $t) {
            $topics[] = $t['display_name'];
        }

        $affiliations = [];
        foreach ($data['affiliations'] ?? [] as $aff) {
            $affiliations[] = [
                'name' => $aff['institution']['display_name'] ?? '',
                'country' => $aff['institution']['country_code'] ?? '',
            ];
        }
        if (empty($affiliations) && !empty($data['last_known_institutions'][0]['display_name'])) {
            $affiliations[] = [
                'name' => $data['last_known_institutions'][0]['display_name'],
                'country' => $data['last_known_institutions'][0]['country_code'] ?? '',
            ];
        }
        $affiliations = array_slice($affiliations, 0, 5);

        $state = $this->getWizardState($request, $context->getId());
        $state[$openalexId] = [
            'openalexId' => $openalexId,
            'payload' => $this->compactOpenalexPayload($data, $topics, $affiliations),
            'display_name' => $data['display_name'] ?? '',
            'orcid' => $data['orcid'] ?? '',
            'works_count' => $data['works_count'] ?? 0,
            'cited_by_count' => $data['cited_by_count'] ?? 0,
            'h_index' => $data['summary_stats']['h_index'] ?? null,
            'topics' => $topics,
            'affiliations' => $affiliations,
            'selected_affiliation' => $affiliations[0]['name'] ?? '',
            'selected_country' => $affiliations[0]['country'] ?? '',
            // User editable fields, defaulted from payload
            'fullName' => $data['display_name'] ?? '',
            'email' => '',
            'role' => '',
            'orcidId' => $data['orcid'] ?? '',
            'scopusId' => '',
            'googleScholar' => '',
            'photoUrl' => '',
            'affiliation' => $affiliations[0]['name'] ?? '',
            'country' => $affiliations[0]['country'] ?? '',
            'keywords' => $topics,
        ];
        $this->saveWizardState($request, $context->getId(), $state);

        return $this->renderWizardStaged($request, $context, $state);
    }

    /**
     * Remove staged author.
     */
    public function wizardRemove($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false, __('common.error'));
        }

        $context = $request->getContext();
        $openalexId = (string) $request->getUserVar('openalexId');
        $state = $this->getWizardState($request, $context->getId());
        unset($state[$openalexId]);
        $this->saveWizardState($request, $context->getId(), $state);
        return $this->renderWizardStaged($request, $context, $state);
    }

    /**
     * Update staged fields (role, affiliation, country, manual overrides).
     */
    public function wizardUpdate($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false, __('common.error'));
        }

        $context = $request->getContext();
        $openalexId = (string) $request->getUserVar('openalexId');
        $field = (string) $request->getUserVar('field');
        $value = $request->getUserVar('value');

        // Whitelist allowed editable fields to prevent arbitrary injection
        $allowedFields = ['fullName', 'email', 'role', 'affiliation', 'country', 'orcidId', 'scopusId', 'googleScholar', 'photoUrl', 'selected_affiliation', 'selected_country'];
        if (!in_array($field, $allowedFields, true)) {
            return new JSONMessage(false, __('common.error'));
        }

        // Server-side email format validation
        if ($field === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return new JSONMessage(false, __('plugins.generic.orcidEditorialBoard.validation.email'));
        }

        $state = $this->getWizardState($request, $context->getId());
        if (!isset($state[$openalexId])) {
            return new JSONMessage(false, __('common.error'));
        }
        $state[$openalexId][$field] = $value;

        // If updating affiliation, adjust selected_country if provided
        if ($field === 'selected_affiliation' && $value) {
            foreach ($state[$openalexId]['affiliations'] as $aff) {
                if ($aff['name'] === $value && !empty($aff['country'])) {
                    $state[$openalexId]['selected_country'] = $aff['country'];
                    break;
                }
            }
        }

        $this->saveWizardState($request, $context->getId(), $state);
        return new JSONMessage(true);
    }

    /**
     * Finalize wizard: persist staged authors and send invitation emails.
     */
    public function wizardFinalize($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false, __('common.error'));
        }

        $context = $request->getContext();
        $contextId = $context->getId();
        $state = $this->getWizardState($request, $contextId);
        if (empty($state)) {
            return new JSONMessage(false, __('common.error'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $errors = [];

        // Validate all entries first before inserting any
        foreach ($state as $entry) {
            $email = trim($entry['email'] ?? '');
            $country = $entry['country'] ?? ($entry['selected_country'] ?? '');
            if ($email === '') {
                $errors[] = ($entry['display_name'] ?? 'Unknown') . ': email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ($entry['display_name'] ?? 'Unknown') . ': invalid email format';
            }
            if ($country === '') {
                $errors[] = ($entry['display_name'] ?? 'Unknown') . ': country is required';
            }
        }

        if (!empty($errors)) {
            return new JSONMessage(false, implode("\n", $errors));
        }

        // All valid — insert inside a transaction and collect saved members
        $savedMembers = [];
        DB::transaction(function () use ($state, $contextId, $dao, &$savedMembers) {
            foreach ($state as $openalexId => $entry) {
                $role = $entry['role'] ?: 'Editorial Member';
                $email = trim($entry['email'] ?? '');
                $country = $entry['country'] ?? ($entry['selected_country'] ?? '');

                $member = new \APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember();
                $member->setContextId($contextId);
                $member->setFullName($entry['fullName'] ?? '');
                $member->setRole($role);
                $member->setEmail($email);
                $member->setOrcidId($entry['orcidId'] ?? null);
                $member->setScopusId($entry['scopusId'] ?? null);
                $member->setGoogleScholar($entry['googleScholar'] ?? null);
                $member->setPhotoUrl($entry['photoUrl'] ?? null);
                $member->setAffiliation($entry['affiliation'] ?? ($entry['selected_affiliation'] ?? ''));
                $member->setCountry($country);
                $member->setSortOrder(0);
                $member->setOpenalexId($entry['openalexId'] ?? null);
                $member->setOpenalexKeywords($entry['keywords'] ?? null);
                $member->setOpenalexFetchedAt(Carbon::now()->toDateTimeString());
                $member->setOpenalexPayload($entry['payload'] ?? null);
                $member->setOpenalexAffiliation($entry['selected_affiliation'] ?? null);
                $member->setOpenalexCountry($entry['selected_country'] ?? null);

                $dao->insertObject($member);
                $savedMembers[] = ['member' => $member, 'payload' => $entry['payload'] ?? []];
            }
        });

        // ── Send invitation emails to all saved members ──
        $dispatcher = $request->getDispatcher();
        foreach ($savedMembers as $item) {
            $member = $item['member'];
            $payload = $item['payload'];

            try {
                // Generate invitation token (kept for record-keeping / legacy)
                $token = bin2hex(random_bytes(32));
                $expires = Carbon::now()->addDays(7)->toDateTimeString();
                $member->setInvitationToken($token);
                $member->setInvitationTokenExpiresAt($expires);
                $member->setInvitationStatus('pending');
                $member->setInvitationSentAt(Carbon::now()->toDateTimeString());
                $dao->updateObject($member);

                // Build HMAC-signed ORCID-gated action URLs
                $secret = \APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin::getHmacSecret();
                $acceptSig = hash_hmac('sha256', 'action:accept:' . $member->getId(), $secret);
                $denySig   = hash_hmac('sha256', 'action:deny:' . $member->getId(), $secret);

                $acceptUrl = $dispatcher->url(
                    $request,
                    Application::ROUTE_PAGE,
                    $context->getPath(),
                    'editorialBoard',
                    'action',
                    null,
                    ['type' => 'accept', 'memberId' => $member->getId(), 'sig' => $acceptSig]
                );
                $denyUrl = $dispatcher->url(
                    $request,
                    Application::ROUTE_PAGE,
                    $context->getPath(),
                    'editorialBoard',
                    'action',
                    null,
                    ['type' => 'deny', 'memberId' => $member->getId(), 'sig' => $denySig]
                );

                // Extract OpenAlex stats for the email
                $openalexData = [
                    'h_index' => $payload['summary_stats']['h_index'] ?? null,
                    'works_count' => $payload['works_count'] ?? null,
                    'cited_by_count' => $payload['cited_by_count'] ?? null,
                ];

                $mailable = new \APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardInvitation(
                    $context, $member, $acceptUrl, $denyUrl, $openalexData
                );
                $mailable->from($context->getData('contactEmail'), $context->getData('contactName'));
                $mailable->to($member->getEmail(), $member->getFullName());
                $mailable->subject(__('plugins.generic.orcidEditorialBoard.invitation.subject'))
                    ->body(__('plugins.generic.orcidEditorialBoard.invitation.body'));
                Mail::send($mailable);
                OrcidEditorialBoardPlugin::log('Invitation email sent for member ' . $member->getId() . ' (' . $member->getFullName() . ')');
            } catch (\Exception $e) {
                OrcidEditorialBoardPlugin::log('Invitation processing error for member ' . $member->getId() . ': ' . $e->getMessage());
            }
        }

        // Clear staging
        $this->saveWizardState($request, $contextId, []);

        return DAO::getDataChangedEvent();
    }

    public function sendCoiEmail($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO');
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return new JSONMessage(false, __('common.error'));
        }

        $token = bin2hex(random_bytes(32));
        $expires = Carbon::now()->addDays(7)->toDateTimeString();
        $member->setCoiToken($token);
        $member->setCoiTokenExpiresAt($expires);
        $dao->updateObject($member);

        // Build HMAC-signed ORCID-gated action URL for COI
        $secret = \APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin::getHmacSecret();
        $coiSig = hash_hmac('sha256', 'action:coi:' . $member->getId(), $secret);

        $dispatcher = $request->getDispatcher();
        $coiUrl = $dispatcher->url(
            $request,
            Application::ROUTE_PAGE,
            $context->getPath(),
            'editorialBoard',
            'action',
            null,
            ['type' => 'coi', 'memberId' => $member->getId(), 'sig' => $coiSig]
        );

        try {
            $mailable = new \APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardCoiRequest($context, $member, $coiUrl);
            $mailable->from($context->getData('contactEmail'), $context->getData('contactName'));
            $mailable->to($member->getEmail(), $member->getFullName());
            // Always use the plugin’s localized premium template body to avoid stale DB templates and unsupported Smarty tags.
            $mailable->subject(__('plugins.generic.orcidEditorialBoard.coiRequest.subject'))
                ->body(__('plugins.generic.orcidEditorialBoard.coiRequest.body'));
            Mail::send($mailable);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('COI email error: ' . $e->getMessage());
            return new JSONMessage(false, 'Email sending failed: ' . $e->getMessage());
        }

        OrcidEditorialBoardPlugin::log('COI email sent for member ' . $memberId);
        return new JSONMessage(true);
    }

    public function toggleVisibility($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO');
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return new JSONMessage(false, __('common.error'));
        }

        $newVisible = !$member->getIsVisible();
        $member->setIsVisible($newVisible);

        // When admin re-enables visibility, reset status to 'active' so Report False Claim reappears
        if ($newVisible && $member->getStatus() === 'removed_by_owner') {
            $member->setStatus('active');
            OrcidEditorialBoardPlugin::log('Status reset to active for member ' . $memberId . ' (visibility re-enabled).');
        }

        $dao->updateObject($member);

        return DAO::getDataChangedEvent($memberId);
    }

    /**
     * Check tenure expiry for all members; mark expired, auto-hide, send reminders.
     * Can be triggered by admin or a lightweight cron endpoint.
     */
    public function checkTenureExpiry($args, $request)
    {
        $context = $request->getContext();
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO');
        $result = $dao->getByContextId($context->getId());
        $now = Carbon::now();
        $reminderDays = 30;
        $processed = 0;

        while ($member = $result->next()) {
            $changed = false;
            $tenureEnd = $member->getTenureEnd();
            if (!$tenureEnd) {
                continue;
            }

            $endDate = Carbon::parse($tenureEnd);

            // If already past end date and still active: mark expired and auto-hide
            if ($endDate->isPast() && $member->getTenureStatus() === 'active') {
                $member->setTenureStatus('expired');
                $member->setIsVisible(false);
                $changed = true;
                OrcidEditorialBoardPlugin::log('Tenure expired for member ' . $member->getId() . '; auto-hidden.');
            }

            // Send reminder N days before expiry if not already sent recently
            $daysUntilExpiry = $now->diffInDays($endDate, false);
            if ($daysUntilExpiry > 0 && $daysUntilExpiry <= $reminderDays && $member->getTenureStatus() === 'active') {
                $lastReminder = $member->getLastReminderSentAt();
                $shouldSend = !$lastReminder || Carbon::parse($lastReminder)->diffInDays($now) >= 7;

                if ($shouldSend && $member->getEmail()) {
                    try {
                        $reminderMailable = new \PKP\mail\Mailable([$context]);
                        $reminderMailable->from($context->getData('contactEmail'), $context->getData('contactName'));
                        $reminderMailable->to($member->getEmail(), $member->getFullName());
                        $reminderMailable->subject(__('plugins.generic.orcidEditorialBoard.tenure.reminderSubject'));
                        $reminderMailable->body(__('plugins.generic.orcidEditorialBoard.tenure.reminderBody', [
                            'memberName' => $member->getFullName(),
                            'journalName' => $context->getLocalizedName(),
                            'tenureEnd' => $tenureEnd,
                        ]));
                        Mail::send($reminderMailable);

                        $member->setLastReminderSentAt($now->toDateTimeString());
                        $changed = true;
                        OrcidEditorialBoardPlugin::log('Tenure reminder sent for member ' . $member->getId());
                    } catch (\Exception $e) {
                        OrcidEditorialBoardPlugin::log('Tenure reminder email error for member ' . $member->getId() . ': ' . $e->getMessage());
                    }
                }
            }

            if ($changed) {
                $dao->updateObject($member);
                $processed++;
            }
        }

        return new JSONMessage(true, "Processed {$processed} members.");
    }

    private function renderWizardStaged($request, $context, array $state)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'staged' => $state,
            'countryOptions' => $this->getCountryOptions(),
            'roleOptions' => [
                'Editor in Chief' => 'Editor in Chief',
                'Managing Editor' => 'Managing Editor',
                'Associate Editor' => 'Associate Editor',
                'Editorial Member' => 'Editorial Member',
            ],
            'plugin' => $this->plugin,
        ]);
        $content = $templateMgr->fetch($this->plugin->getTemplateResource('wizardStaged.tpl'));
        return new JSONMessage(true, $content);
    }

    private function getCountryOptions(): array
    {
        $countries = [];
        foreach (\PKP\facades\Locale::getCountries() as $c) {
            $countries[$c->getAlpha2()] = $c->getLocalName();
        }
        asort($countries);
        return $countries;
    }

    public function verifyCredentials($args, $request)
    {
        $contextId = $request->getContext()->getId();
        $clientId = $this->plugin->getSetting($contextId, 'orcidClientId');
        $clientSecret = $this->plugin->getSetting($contextId, 'orcidClientSecret');

        if (!$clientId || !$clientSecret) {
            return new JSONMessage(false, __('plugins.generic.orcidEditorialBoard.settings.testFail'));
        }

        $tokenUrl = 'https://orcid.org/oauth/token';

        try {
            $httpClient = Application::get()->getHttpClient();
            $response = $httpClient->request('POST', $tokenUrl, [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => '/read-public',
                ],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 10,
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
            if (!empty($body['access_token'])) {
                return new JSONMessage(true, __('plugins.generic.orcidEditorialBoard.settings.testSuccess'));
            }
            return new JSONMessage(false, __('plugins.generic.orcidEditorialBoard.settings.testFail'));
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('verifyCredentials error: ' . $e->getMessage());
            return new JSONMessage(false, __('plugins.generic.orcidEditorialBoard.settings.testFail') . ': ' . $e->getMessage());
        }
    }

    /**
     * Write an audit record to editorial_board_disputes.
     * Self-heals by creating the table if it doesn't exist yet.
     */
    private function insertAuditRecord(int $memberId, string $type, string $details): void
    {
        try {
            // Self-healing: create the audit table if it doesn't exist
            if (!\Illuminate\Support\Facades\Schema::hasTable('editorial_board_disputes')) {
                \Illuminate\Support\Facades\Schema::create('editorial_board_disputes', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('member_id')->unsigned();
                    $table->string('orcid', 40)->nullable();
                    $table->string('type', 50)->default('admin_edit');
                    $table->text('details')->nullable();
                    $table->dateTime('created_at')->nullable();
                    $table->index(['member_id'], 'editorial_board_disputes_member');
                });
            }
            DB::table('editorial_board_disputes')->insert([
                'member_id' => $memberId,
                'orcid' => null,
                'type' => $type,
                'details' => $details,
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('Failed to insert audit record: ' . $e->getMessage());
        }
    }
}
