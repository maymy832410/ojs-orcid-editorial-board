<?php

/**
 * @file plugins/generic/orcidEditorialBoard/OrcidEditorialBoardHandler.php
 *
 * @brief Public handler for editorial board ORCID workflows.
 *
 * @copyright Copyright (c) 2026 Peers Publishing
 * @author Mohanad G. Yaseen
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3.0
 */

namespace APP\plugins\generic\orcidEditorialBoard;

use APP\handler\Handler;
use APP\template\TemplateManager;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMemberDAO;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use PKP\db\DAORegistry;
use PKP\plugins\PluginRegistry;
use PKP\facades\Locale;

class OrcidEditorialBoardHandler extends Handler
{
    /** @var OrcidEditorialBoardPlugin */
    private $plugin;

    private const ROLE_ORDER = [
        'Editor in Chief' => 1,
        'Managing Editor' => 2,
        'Associate Editor' => 3,
        'Editorial Member' => 4,
    ];

    public function index($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $context = $request->getContext();
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */

        $grouped = [];
        if ($context) {
            $result = $dao->getByContextId($context->getId());
            while ($member = $result->next()) {
                // Skip hidden members
                if (!$member->getIsVisible()) {
                    continue;
                }

                // Skip denied invitations (defensive — they should be hidden already)
                if ($member->getInvitationStatus() === 'denied') {
                    continue;
                }

                // Auto-expire tenure if end date has passed
                if ($member->getTenureEnd() && Carbon::parse($member->getTenureEnd())->isPast() && $member->getTenureStatus() === 'active') {
                    $member->setTenureStatus('expired');
                    $dao->updateObject($member);
                }

                $role = $member->getRole();
                if (!isset($grouped[$role])) {
                    $grouped[$role] = [];
                }

                // Derive OpenAlex stats and affiliation if available
                $payload = $member->getOpenalexPayload();
                $openalexStats = [
                    'works' => $payload['works_count'] ?? null,
                    'citations' => $payload['cited_by_count'] ?? null,
                    'h_index' => $payload['summary_stats']['h_index'] ?? null,
                ];
                $member->setData('openalexStats', $openalexStats);
                if (!$member->getAffiliation() && !empty($payload['last_known_institutions'][0]['display_name'])) {
                    $member->setAffiliation($payload['last_known_institutions'][0]['display_name']);
                }
                if (!$member->getCountry() && !empty($payload['last_known_institutions'][0]['country_code'])) {
                    $member->setCountry($payload['last_known_institutions'][0]['country_code']);
                }

                // Calculate tenure urgency for color-coded badges
                $tenureUrgency = 'none'; // no tenure dates set
                if ($member->getTenureStart()) {
                    if ($member->getTenureStatus() === 'expired') {
                        $tenureUrgency = 'expired';
                    } elseif ($member->getTenureEnd()) {
                        $daysRemaining = Carbon::now()->diffInDays(Carbon::parse($member->getTenureEnd()), false);
                        if ($daysRemaining <= 0) {
                            $tenureUrgency = 'expired';
                        } elseif ($daysRemaining <= 90) {
                            $tenureUrgency = 'warning';
                        } else {
                            $tenureUrgency = 'active';
                        }
                    } else {
                        $tenureUrgency = 'active'; // no end date = indefinite
                    }
                }
                $member->setData('tenureUrgency', $tenureUrgency);

                $grouped[$role][] = $member;
            }
        }

        uksort($grouped, function ($a, $b) {
            $oa = self::ROLE_ORDER[$a] ?? 99;
            $ob = self::ROLE_ORDER[$b] ?? 99;
            return $oa <=> $ob;
        });

        // Country diversity stats
        $countryCounts = [];
        foreach ($grouped as $roleMembers) {
            foreach ($roleMembers as $member) {
                $c = $member->getCountry();
                if ($c) {
                    $countryCounts[$c] = ($countryCounts[$c] ?? 0) + 1;
                }
            }
        }
        arsort($countryCounts);

        $countryNames = [];
        foreach (Locale::getCountries() as $country) {
            $countryNames[$country->getAlpha2()] = $country->getLocalName();
        }

        $totalMembers = array_sum(array_map('count', $grouped));
        $countryStats = [];
        foreach ($countryCounts as $code => $count) {
            $percent = $totalMembers > 0 ? round(($count / $totalMembers) * 100) : 0;
            $countryStats[] = [
                'code' => $code,
                'count' => $count,
                'percent' => $percent,
            ];
        }

        // Generate HMAC verify sigs for each member
        $verifySigs = [];
        $reportSigs = [];
        foreach ($grouped as $roleMembers) {
            foreach ($roleMembers as $member) {
                $verifySigs[$member->getId()] = $this->generateVerifySig($member->getId());
                $reportSigs[$member->getId()] = $this->generateReportSig($member->getId());
            }
        }

        $templateMgr->assign([
            'groupedMembers' => $grouped,
            'totalMembers' => $totalMembers,
            'orcidConfigured' => $this->getPlugin()->isOrcidApiConfigured(),
            'journalName' => $context ? $context->getLocalizedName() : '',
            'publisherName' => $context ? $context->getData('publisherInstitution') : '',
            'countryCounts' => $countryCounts,
            'totalCountries' => count($countryCounts),
            'countryNames' => $countryNames,
            'countryStats' => $countryStats,
            'verifySigs' => $verifySigs,
            'reportSigs' => $reportSigs,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('editorialBoard.tpl'));
    }

    public function consent($args, $request)
    {
        $context = $request->getContext();
        $token = $request->getUserVar('token');
        if (!$context || !$token) {
            OrcidEditorialBoardPlugin::log('Consent called without context or token');
            return $this->showError($request, __('common.error'));
        }

        if (!$this->getPlugin()->isOrcidApiConfigured()) {
            OrcidEditorialBoardPlugin::log('Consent called but ORCID not configured');
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.orcidNotConfigured'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getByConsentToken($token);
        if (!$member || $member->getContextId() !== $context->getId()) {
            OrcidEditorialBoardPlugin::log('Consent token not found or context mismatch');
            return $this->showError($request, __('common.error'));
        }

        // Optional expiry check
        if ($member->getConsentTokenExpires() && Carbon::parse($member->getConsentTokenExpires())->isPast()) {
            OrcidEditorialBoardPlugin::log('Consent token expired for member ' . $member->getId());
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.consentExpired'));
        }

        $authorizeUrl = $this->buildAuthorizeUrl($request, $token);
        return $request->redirectUrl($authorizeUrl);
    }

    /**
     * Unified ORCID OAuth callback — single redirect URI for all flows.
     *
     * Dispatches based on the `state` parameter format:
     *   - Contains ':' → action flow (accept/deny/coi)
     *   - Matches a report token → report false claim flow
     *   - Matches a consent token → consent/verify flow
     *
     * Also handles the report POST confirmation (confirmRemove).
     */
    public function callback($args, $request)
    {
        $context = $request->getContext();
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $templateMgr = TemplateManager::getManager($request);

        // ── Handle report POST confirmation (no ORCID exchange here) ──
        if ($request->isPost() && $request->getUserVar('confirmRemove')) {
            return $this->handleReportConfirmPost($request, $context, $dao, $templateMgr);
        }

        $state = $request->getUserVar('state');
        $code = $request->getUserVar('code');
        $error = $request->getUserVar('error');

        if (!$context) {
            return $this->showError($request, __('common.error'));
        }

        // ── Detect which flow initiated the redirect ──
        if ($state && strpos($state, ':') !== false) {
            // Action flow: state = "{actionToken}:{type}:{memberId}"
            return $this->handleActionCallback($request, $context, $dao, $templateMgr, $state, $code, $error);
        }

        if ($state) {
            // Try report flow first (report token on member)
            $reportMember = $dao->getByReportToken($state, $context->getId());
            if ($reportMember) {
                return $this->handleReportCallback($request, $context, $dao, $templateMgr, $reportMember, $state, $code, $error);
            }

            // Try consent flow (consent token on member)
            $consentMember = $dao->getByConsentToken($state);
            if ($consentMember) {
                return $this->handleConsentCallback($request, $context, $dao, $templateMgr, $consentMember, $code, $error);
            }
        }

        // No matching flow
        if ($error === 'access_denied') {
            OrcidEditorialBoardPlugin::log('ORCID access denied in callback (no matching flow)');
            $templateMgr->assign('currentUrl', $request->url(null, 'index'));
            $templateMgr->display($this->getPlugin()->getTemplateResource('consentDenied.tpl'));
            return;
        }

        OrcidEditorialBoardPlugin::log('Callback with unrecognized state: ' . ($state ?: '(empty)'));
        return $this->showError($request, __('common.error'));
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Callback sub-handlers (dispatched from unified callback)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Handle the consent/verify ORCID callback.
     */
    private function handleConsentCallback($request, $context, $dao, $templateMgr, $member, $code, $error)
    {
        $templateMgr->assign('currentUrl', $request->url(null, 'index'));

        if ($error === 'access_denied') {
            OrcidEditorialBoardPlugin::log('ORCID access denied in consent callback');
            $templateMgr->display($this->getPlugin()->getTemplateResource('consentDenied.tpl'));
            return;
        }

        if (!$code) {
            OrcidEditorialBoardPlugin::log('Consent callback missing code');
            return $this->showError($request, __('common.error'));
        }

        // Verify the member belongs to the current context
        if ($member->getContextId() !== $context->getId()) {
            OrcidEditorialBoardPlugin::log('Consent callback context mismatch for member ' . $member->getId());
            return $this->showError($request, __('common.error'));
        }

        // Re-check consent token expiry (user may have spent time on ORCID)
        if ($member->getConsentTokenExpires() && Carbon::parse($member->getConsentTokenExpires())->isPast()) {
            OrcidEditorialBoardPlugin::log('Consent token expired during ORCID redirect for member ' . $member->getId());
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.consentExpired'));
        }

        // Exchange authorization code for access token
        $tokenBody = $this->exchangeOrcidCode($request, $code);
        if (!$tokenBody) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        $orcidFromApi = $tokenBody['orcid'] ?? null;
        $accessToken = $tokenBody['access_token'] ?? null;
        if (!$orcidFromApi || !$accessToken) {
            OrcidEditorialBoardPlugin::log('Token response missing orcid or access_token');
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        // Fetch name from ORCID API (token response does not include it with /authenticate scope)
        $nameFromApi = $this->fetchOrcidName($orcidFromApi, $accessToken);

        // ── ORCID iD comparison ──
        $storedBare = $this->bareOrcid($member->getOrcidId());
        $orcidSite = $this->getOrcidSiteBase();

        if ($storedBare) {
            // Admin entered an ORCID — it MUST match the authenticated one
            if (strcasecmp($storedBare, $orcidFromApi) !== 0) {
                OrcidEditorialBoardPlugin::log(
                    'ORCID MISMATCH for member ' . $member->getId() .
                    ': admin stored ' . $storedBare . ', member authenticated as ' . $orcidFromApi .
                    '. Verification DENIED.'
                );
                $templateMgr->assign([
                    'orcidMismatch' => true,
                    'storedOrcid' => $storedBare,
                    'authenticatedOrcid' => $orcidFromApi,
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('consentDenied.tpl'));
                return;
            }
        } else {
            // No ORCID on file — first-time linking, accept the authenticated value
            $member->setOrcidId($orcidSite . $orcidFromApi);
            OrcidEditorialBoardPlugin::log('First-time ORCID linked: ' . $orcidFromApi . ' for member ' . $member->getId());
        }

        // ── Name comparison (informational — logged, not blocking) ──
        if ($nameFromApi && !$this->namesMatch($member->getFullName(), $nameFromApi)) {
            OrcidEditorialBoardPlugin::log(
                'Name MISMATCH (non-blocking) for member ' . $member->getId() .
                ': DB="' . $member->getFullName() . '", ORCID="' . $nameFromApi . '"'
            );
        }

        // ── Mark verified ──
        $member->setOrcidAccessToken($accessToken);
        $member->setOrcidAuthName($nameFromApi);
        $member->setOrcidVerified(true);
        $member->setOrcidVerifiedCachedAt(Carbon::now()->toDateTimeString());
        $member->setConsentToken(null);
        $member->setConsentTokenExpires(null);
        // Clear the dispute window so the verified badge takes priority on the public page
        $member->setDisputeExpiresAt(null);
        $dao->updateObject($member);

        OrcidEditorialBoardPlugin::log('ORCID verification SUCCESS for ORCID ' . $orcidFromApi . ' member ' . $member->getId());

        $templateMgr->display($this->getPlugin()->getTemplateResource('consentSuccess.tpl'));
    }

    /**
     * Handle the report false claim ORCID callback (GET from ORCID with ?code=&state=).
     */
    private function handleReportCallback($request, $context, $dao, $templateMgr, $member, $token, $code, $error)
    {
        $templateMgr->assign('journalName', $context->getLocalizedName());

        if ($error === 'access_denied') {
            $templateMgr->assign('reportStatus', 'denied');
            $templateMgr->display($this->getPlugin()->getTemplateResource('reportResult.tpl'));
            return;
        }

        if (!$code) {
            return $this->showError($request, __('common.error'));
        }

        if (!$member->getReportToken() || $member->getReportToken() !== $token) {
            return $this->showError($request, __('common.error'));
        }

        if ($member->getReportTokenExpiresAt() && Carbon::parse($member->getReportTokenExpiresAt())->isPast()) {
            OrcidEditorialBoardPlugin::log('Report token expired for member ' . $member->getId());
            $templateMgr->assign('reportStatus', 'expired');
            $templateMgr->display($this->getPlugin()->getTemplateResource('reportResult.tpl'));
            return;
        }

        // Exchange authorization code for access token
        $tokenBody = $this->exchangeOrcidCode($request, $code);
        if (!$tokenBody) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        $orcidFromToken = $tokenBody['orcid'] ?? null;
        if (!$orcidFromToken) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        // Use unified bareOrcid() to strip any URL prefix
        $storedBare = $this->bareOrcid($member->getOrcidId());
        $previousBare = $this->bareOrcid($member->getPreviousOrcidId());

        // Accept dispute from EITHER current or previous ORCID owner
        $orcidMatches = ($storedBare && strcasecmp($storedBare, $orcidFromToken) === 0)
                     || ($previousBare && strcasecmp($previousBare, $orcidFromToken) === 0);

        if (!$orcidMatches) {
            OrcidEditorialBoardPlugin::log('Report ORCID mismatch for member ' . $member->getId()
                . ': stored=' . ($storedBare ?: 'null')
                . ' previous=' . ($previousBare ?: 'null')
                . ' auth=' . $orcidFromToken);

            $this->insertDisputeRecord($member->getId(), $orcidFromToken, 'dispute',
                'ORCID mismatch: stored=' . ($storedBare ?: 'null')
                . ' previous=' . ($previousBare ?: 'null')
                . ' authenticated=' . $orcidFromToken);

            $templateMgr->assign([
                'reportStatus' => 'orcid_mismatch',
                'member' => $member,
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('reportResult.tpl'));
            return;
        }

        // Clear token so it can't be reused
        $member->setReportToken(null);
        $member->setReportTokenExpiresAt(null);
        $dao->updateObject($member);

        // Bind a one-time session token so POST can only succeed after this GET
        $sessionCheck = bin2hex(random_bytes(16));
        $session = $request->getSession();
        $session->setSessionVar('reportConfirmMember_' . $member->getId(), $sessionCheck);

        $templateMgr->assign([
            'reportStatus' => 'matched',
            'member' => $member,
            'orcidBare' => $storedBare,
            'sessionCheck' => $sessionCheck,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('reportResult.tpl'));
    }

    /**
     * Handle the report POST confirmation (member clicks "Remove my listing").
     */
    private function handleReportConfirmPost($request, $context, $dao, $templateMgr)
    {
        $templateMgr->assign('journalName', $context ? $context->getLocalizedName() : '');

        $memberId = (int) $request->getUserVar('memberId');
        if (!$context || !$memberId) {
            return $this->showError($request, __('common.error'));
        }

        // Validate session binding (set during GET step after ORCID verification)
        $session = $request->getSession();
        $sessionKey = 'reportConfirmMember_' . $memberId;
        $sessionCheck = $request->getUserVar('sessionCheck');
        $expectedCheck = $session->getSessionVar($sessionKey);
        if (!$expectedCheck || $sessionCheck !== $expectedCheck) {
            OrcidEditorialBoardPlugin::log('Report POST without valid session for member ' . $memberId);
            return $this->showError($request, __('common.error'));
        }
        // Consume the session token so it can't be replayed
        $session->setSessionVar($sessionKey, null);

        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('common.error'));
        }

        $member->setIsVisible(false);
        $member->setStatus('removed_by_owner');
        $member->setReportToken(null);
        $member->setReportTokenExpiresAt(null);
        $member->setPreviousOrcidId(null);
        $dao->updateObject($member);

        $this->insertDisputeRecord($member->getId(), $this->bareOrcid($member->getOrcidId()), 'remove', 'Member confirmed removal via ORCID authentication.');

        $templateMgr->assign([
            'reportStatus' => 'removed',
            'member' => $member,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('reportResult.tpl'));
    }

    /**
     * Handle the action ORCID callback (accept/deny/coi).
     */
    private function handleActionCallback($request, $context, $dao, $templateMgr, $state, $code, $error)
    {
        if ($error === 'access_denied') {
            OrcidEditorialBoardPlugin::log('ORCID access denied in action callback');
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.action.orcidDenied'));
        }

        if (!$code || !$state) {
            return $this->showError($request, __('common.error'));
        }

        // Parse state: {actionToken}:{type}:{memberId}
        $parts = explode(':', $state, 3);
        if (count($parts) !== 3) {
            return $this->showError($request, __('common.error'));
        }
        [$actionToken, $type, $memberId] = $parts;
        $memberId = (int) $memberId;

        // Validate session binding
        $session = $request->getSession();
        $expectedToken = $session->getSessionVar('eb_action_token');
        $expectedType = $session->getSessionVar('eb_action_type');
        $expectedMemberId = (int) $session->getSessionVar('eb_action_member_id');

        if (!$expectedToken || $actionToken !== $expectedToken || $type !== $expectedType || $memberId !== $expectedMemberId) {
            OrcidEditorialBoardPlugin::log('Action callback session mismatch');
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }

        // Consume session tokens
        $session->setSessionVar('eb_action_token', null);
        $session->setSessionVar('eb_action_type', null);
        $session->setSessionVar('eb_action_member_id', null);

        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('common.error'));
        }

        // ── Exchange ORCID authorization code for access token ──
        $tokenBody = $this->exchangeOrcidCode($request, $code);
        if (!$tokenBody) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        $orcidFromApi = $tokenBody['orcid'] ?? null;
        if (!$orcidFromApi) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.token'));
        }

        // ── ORCID identity verification ──
        $storedBare = $this->bareOrcid($member->getOrcidId());
        $previousBare = $this->bareOrcid($member->getPreviousOrcidId());

        $orcidMatches = ($storedBare && strcasecmp($storedBare, $orcidFromApi) === 0)
                     || ($previousBare && strcasecmp($previousBare, $orcidFromApi) === 0);

        if (!$orcidMatches) {
            OrcidEditorialBoardPlugin::log(
                'Action ORCID MISMATCH for member ' . $memberId . ' type=' . $type .
                ': stored=' . ($storedBare ?: 'null') .
                ' previous=' . ($previousBare ?: 'null') .
                ' authenticated=' . $orcidFromApi
            );
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.action.orcidMismatch'));
        }

        OrcidEditorialBoardPlugin::log(
            'ORCID VERIFIED for action=' . $type . ' member=' . $memberId .
            ' orcid=' . $orcidFromApi
        );

        // ── Persist ORCID verification data (same as consent flow) ──
        $accessToken = $tokenBody['access_token'] ?? null;
        if ($accessToken) {
            $member->setOrcidAccessToken($accessToken);
        }
        $member->setOrcidVerified(true);
        $member->setOrcidVerifiedCachedAt(Carbon::now()->toDateTimeString());
        $member->setConsentToken(null);
        $member->setConsentTokenExpires(null);

        // Fetch name from ORCID API (token response does not include it)
        $orcidName = $this->fetchOrcidName($orcidFromApi, $accessToken);
        if ($orcidName) {
            $member->setOrcidAuthName($orcidName);
        }

        $dao->updateObject($member);

        // ── Dispatch to action handler ──
        switch ($type) {
            case 'accept':
                return $this->executeAcceptInvitation($request, $context, $dao, $member);
            case 'deny':
                return $this->executeDenyInvitation($request, $context, $dao, $member);
            case 'coi':
                return $this->executeCoiAfterOrcid($request, $context, $dao, $member);
            default:
                return $this->showError($request, __('common.error'));
        }
    }

    /**
     * Exchange an ORCID authorization code for an access token.
     * Returns the decoded token body array, or null on failure.
     * All flows use the single buildCallbackUrl() redirect URI.
     */
    private function exchangeOrcidCode($request, string $code): ?array
    {
        $httpClient = \APP\core\Application::get()->getHttpClient();
        $orcidSite = $this->getOrcidSiteBase();

        try {
            $tokenResponse = $httpClient->request(
                'POST',
                $orcidSite . 'oauth/token',
                [
                    'form_params' => [
                        'code' => $code,
                        'grant_type' => 'authorization_code',
                        'client_id' => $this->getOrcidClientId(),
                        'client_secret' => $this->getOrcidClientSecret(),
                        'redirect_uri' => $this->buildCallbackUrl($request),
                    ],
                    'headers' => ['Accept' => 'application/json'],
                    'allow_redirects' => ['strict' => true],
                ]
            );
        } catch (ClientException $exception) {
            OrcidEditorialBoardPlugin::log('ORCID token exchange failed: ' . $exception->getMessage());
            return null;
        }

        if ($tokenResponse->getStatusCode() !== 200) {
            OrcidEditorialBoardPlugin::log('Unexpected ORCID token status: ' . $tokenResponse->getStatusCode());
            return null;
        }

        return json_decode($tokenResponse->getBody(), true);
    }

    /**
     * Fetch a person's name from the ORCID API using their access token.
     * Returns "Given Family" string, or null on failure.
     */
    private function fetchOrcidName(string $orcidBare, ?string $accessToken): ?string
    {
        if (!$accessToken || !$orcidBare) {
            return null;
        }

        $apiBase = $this->getApiBase();
        $httpClient = \APP\core\Application::get()->getHttpClient();

        try {
            $response = $httpClient->request(
                'GET',
                rtrim($apiBase, '/') . '/v3.0/' . $orcidBare . '/person',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 10,
                ]
            );

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody()->getContents(), true);
                $given = $body['name']['given-names']['value'] ?? '';
                $family = $body['name']['family-name']['value'] ?? '';
                $name = trim($given . ' ' . $family);
                return $name ?: null;
            }
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('fetchOrcidName failed for ' . $orcidBare . ': ' . $e->getMessage());
        }

        return null;
    }

    /**
     * COI Declaration — requires ORCID-verified session token.
     * Accessible via: editorialBoard/coiDeclare?memberId=X&sessionToken=Y
     * The sessionToken is set by executeCoiAfterOrcid() after ORCID authentication.
     * Legacy token-based access is still supported but will redirect through ORCID auth.
     */
    public function coiDeclare($args, $request)
    {
        $context = $request->getContext();
        if (!$context) {
            return $this->showError($request, __('common.error'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO');

        // ── New ORCID-verified path: memberId + sessionToken ──
        $memberId = (int) $request->getUserVar('memberId');
        $sessionToken = (string) $request->getUserVar('sessionToken');

        if ($memberId && $sessionToken) {
            $session = $request->getSession();
            $expected = $session->getSessionVar('eb_coi_verified_' . $memberId);
            if (!$expected || !hash_equals($expected, $sessionToken)) {
                OrcidEditorialBoardPlugin::log('coiDeclare: invalid session token for member ' . $memberId);
                return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
            }
            // Session is valid — look up member directly
            $member = $dao->getById($memberId, $context->getId());
            if (!$member) {
                return $this->showError($request, __('common.error'));
            }
            // Keep the session token alive for POST submission
            // (it will be consumed after successful declaration)
        }
        // ── Legacy token-based path: redirect through ORCID auth ──
        else {
            $token = $request->getUserVar('token');
            if (!$token) {
                return $this->showError($request, __('common.error'));
            }
            $member = $dao->getByCoiToken($token);
            if (!$member || $member->getContextId() !== $context->getId()) {
                return $this->showError($request, __('plugins.generic.orcidEditorialBoard.coi.invalidToken'));
            }
            if ($member->getCoiTokenExpiresAt() && Carbon::parse($member->getCoiTokenExpiresAt())->isPast()) {
                return $this->showError($request, __('plugins.generic.orcidEditorialBoard.coi.tokenExpired'));
            }
            // Redirect to ORCID auth action flow
            $sig = $this->generateActionSig('coi', $member->getId());
            $dispatcher = $request->getDispatcher();
            $actionUrl = $dispatcher->url(
                $request,
                \APP\core\Application::ROUTE_PAGE,
                $context->getPath(),
                'editorialBoard',
                'action',
                null,
                ['type' => 'coi', 'memberId' => $member->getId(), 'sig' => $sig]
            );
            return $request->redirectUrl($actionUrl);
        }

        if ($member->getCoiStatus() === 'declared') {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'member' => $member,
                'alreadyDeclared' => true,
                'journalName' => $context->getLocalizedName(),
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
            return;
        }

        if ($request->isPost()) {
            $financialOptions = (array) $request->getUserVar('financialOptions');
            $financialDetails = trim((string) $request->getUserVar('financialDetails'));

            $personalOptions = (array) $request->getUserVar('personalOptions');
            $personalDetails = trim((string) $request->getUserVar('personalDetails'));

            $orgFinancialInterest = (string) $request->getUserVar('orgFinancialInterest');
            $orgFinancialDetails = trim((string) $request->getUserVar('orgFinancialDetails'));

            $otherConflicts = trim((string) $request->getUserVar('otherConflicts'));
            $declarationAccepted = (bool) $request->getUserVar('declarationAccepted');

            // Minimal validation
            if (!$declarationAccepted) {
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign([
                    'member' => $member,
                    'memberId' => $member->getId(),
                    'sessionToken' => $sessionToken ?: '',
                    'journalName' => $context->getLocalizedName(),
                    'coiError' => __('plugins.generic.orcidEditorialBoard.coi.error.declarationRequired'),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
                return;
            }

            if (!empty($financialOptions) && $financialDetails === '') {
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign([
                    'member' => $member,
                    'memberId' => $member->getId(),
                    'sessionToken' => $sessionToken ?: '',
                    'journalName' => $context->getLocalizedName(),
                    'coiError' => __('plugins.generic.orcidEditorialBoard.coi.error.financialDetailsRequired'),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
                return;
            }

            if (!empty($personalOptions) && $personalDetails === '') {
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign([
                    'member' => $member,
                    'memberId' => $member->getId(),
                    'sessionToken' => $sessionToken ?: '',
                    'journalName' => $context->getLocalizedName(),
                    'coiError' => __('plugins.generic.orcidEditorialBoard.coi.error.personalDetailsRequired'),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
                return;
            }

            if ($orgFinancialInterest === 'yes' && $orgFinancialDetails === '') {
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign([
                    'member' => $member,
                    'memberId' => $member->getId(),
                    'sessionToken' => $sessionToken ?: '',
                    'journalName' => $context->getLocalizedName(),
                    'coiError' => __('plugins.generic.orcidEditorialBoard.coi.error.orgFinancialDetailsRequired'),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
                return;
            }

            // Build a single stored disclosure narrative
            $lines = [];
            $lines[] = 'Disclosure Form';
            $lines[] = 'Journal: ' . $context->getLocalizedName();
            $lines[] = 'Member: ' . $member->getFullName();
            $lines[] = 'Submitted at: ' . Carbon::now()->toDateTimeString();
            $lines[] = '';

            $lines[] = '1. Financial Conflicts of Interest';
            $lines[] = 'Selected: ' . (!empty($financialOptions) ? implode(', ', $financialOptions) : 'None');
            $lines[] = 'Details: ' . ($financialDetails !== '' ? $financialDetails : 'N/A');
            $lines[] = '';

            $lines[] = '2. Personal or Professional Conflicts of Interest';
            $lines[] = 'Selected: ' . (!empty($personalOptions) ? implode(', ', $personalOptions) : 'None');
            $lines[] = 'Details: ' . ($personalDetails !== '' ? $personalDetails : 'N/A');
            $lines[] = '';

            $lines[] = '3. Financial Interests in Related Organizations';
            $lines[] = 'Yes/No: ' . ($orgFinancialInterest ?: 'no');
            $lines[] = 'Details: ' . ($orgFinancialDetails !== '' ? $orgFinancialDetails : 'N/A');
            $lines[] = '';

            $lines[] = '4. Other Potential Conflicts of Interest';
            $lines[] = $otherConflicts !== '' ? $otherConflicts : 'None';
            $lines[] = '';

            $lines[] = '5. Declaration';
            $lines[] = 'Accepted: yes';

            $member->setCoiStatus('declared');
            $member->setCoiText(implode("\n", $lines));
            $member->setCoiDeclaredAt(Carbon::now()->toDateTimeString());
            $member->setCoiToken(null);
            $member->setCoiTokenExpiresAt(null);
            $dao->updateObject($member);

            // Consume ORCID session token after successful declaration
            if ($memberId) {
                $session = $request->getSession();
                $session->setSessionVar('eb_coi_verified_' . $memberId, null);
            }

            OrcidEditorialBoardPlugin::log('COI declared (ORCID-verified) for member ' . $member->getId());

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'member' => $member,
                'journalName' => $context->getLocalizedName(),
                'coiSuccess' => true,
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
            return;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'member' => $member,
            'memberId' => $member->getId(),
            'sessionToken' => $sessionToken ?: '',
            'journalName' => $context->getLocalizedName(),
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('coiForm.tpl'));
    }

    public function reportFalseClaim($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $sig = (string) $request->getUserVar('sig');
        if (!$context || !$memberId) {
            return $this->showError($request, __('common.error'));
        }

        // HMAC signature check — prevents member ID enumeration
        $expectedSig = $this->generateReportSig($memberId);
        if (!$sig || !hash_equals($expectedSig, $sig)) {
            OrcidEditorialBoardPlugin::log('reportFalseClaim called with invalid HMAC sig for member ' . $memberId);
            return $this->showError($request, __('common.error'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        // Allow dispute if member has current OR previous ORCID (previous preserved after admin edits)
        if (!$member || (!$member->getOrcidId() && !$member->getPreviousOrcidId())) {
            return $this->showError($request, __('common.error'));
        }

        // If confirm=1 was submitted from the intermediate page, proceed with ORCID redirect
        if ($request->getUserVar('confirm')) {
            $token = bin2hex(random_bytes(32));
            $member->setReportToken($token);
            $member->setReportTokenExpiresAt(Carbon::now()->addDay()->toDateTimeString());
            $dao->updateObject($member);

            $authorizeUrl = $this->buildReportAuthorizeUrl($request, $token);
            return $request->redirectUrl($authorizeUrl);
        }

        // Show intermediate confirmation page first
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'member' => $member,
            'journalName' => $context->getLocalizedName(),
            'sig' => $sig,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('reportFalseClaimStart.tpl'));
    }



    /**
     * Write a record to the editorial_board_disputes audit table.
     */
    private function insertDisputeRecord(int $memberId, ?string $orcid, string $type, string $details): void
    {
        try {
            \Illuminate\Support\Facades\DB::table('editorial_board_disputes')->insert([
                'member_id' => $memberId,
                'orcid' => $orcid,
                'type' => $type,
                'details' => $details,
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('Failed to insert dispute record: ' . $e->getMessage());
        }
    }

    private function buildAuthorizeUrl($request, string $token): string
    {
        $base = $this->getOrcidSiteBase();
        $query = http_build_query([
            'client_id' => $this->getOrcidClientId(),
            'response_type' => 'code',
            'scope' => '/authenticate',
            'redirect_uri' => $this->buildCallbackUrl($request),
            'state' => $token,
        ]);
        return $base . 'oauth/authorize?' . $query;
    }

    private function buildCallbackUrl($request): string
    {
        $context = $request->getContext();
        return $request->getDispatcher()->url(
            $request,
            \APP\core\Application::ROUTE_PAGE,
            $context ? $context->getPath() : null,
            'editorialBoard',
            'callback'
        );
    }

    private function buildReportAuthorizeUrl($request, string $token): string
    {
        $base = $this->getOrcidSiteBase();
        $query = http_build_query([
            'client_id' => $this->getOrcidClientId(),
            'response_type' => 'code',
            'scope' => '/authenticate',
            'redirect_uri' => $this->buildCallbackUrl($request),
            'state' => $token,
        ]);
        return $base . 'oauth/authorize?' . $query;
    }

    private function getApiBase(): string
    {
        return OrcidEditorialBoardPlugin::ORCID_API_URL_PUBLIC;
    }

    private function getOrcidSiteBase(): string
    {
        return OrcidEditorialBoardPlugin::ORCID_SITE_BASE;
    }

    private function getOrcidClientId(): string
    {
        $request = \APP\core\Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : 0;
        return (string) $this->getPlugin()->getSetting($contextId, 'orcidClientId');
    }

    private function getOrcidClientSecret(): string
    {
        $request = \APP\core\Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : 0;
        return (string) $this->getPlugin()->getSetting($contextId, 'orcidClientSecret');
    }

    public function verify($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $sig = (string) $request->getUserVar('sig');
        $templateMgr = TemplateManager::getManager($request);

        if (!$context || !$memberId) {
            return $this->showError($request, __('common.error'));
        }

        // HMAC signature check — prevents anonymous visitors from triggering API calls
        $expectedSig = $this->generateVerifySig($memberId);
        if (!$sig || !hash_equals($expectedSig, $sig)) {
            OrcidEditorialBoardPlugin::log('Verify called with invalid HMAC sig for member ' . $memberId);
            return $this->showError($request, __('common.error'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO');
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('common.error'));
        }

        $accessToken = $member->getOrcidAccessToken();
        $orcidId = $member->getOrcidId();
        $orcidBare = $this->bareOrcid($orcidId);

        // Rate-limit: reuse cached result if checked within last 5 minutes
        $lastCheck = $member->getOrcidVerifiedCachedAt();
        if ($lastCheck && Carbon::parse($lastCheck)->diffInMinutes(Carbon::now()) < 5) {
            $cachedStatus = $member->getOrcidVerified() ? 'verified' : 'not_verified';
            $templateMgr->assign([
                'member' => $member,
                'verificationStatus' => $cachedStatus,
                'journalName' => $context->getLocalizedName(),
                'orcidBare' => $orcidBare,
                'orcidName' => $member->getOrcidAuthName(),
                'consentFingerprint' => $member->getConsentFingerprint(),
                'consentDate' => $lastCheck,
                'rateLimited' => true,
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('orcidVerifyReport.tpl'));
            return;
        }

        if (!$accessToken || !$orcidBare) {
            $templateMgr->assign([
                'member' => $member,
                'verificationStatus' => 'not_verified',
                'journalName' => $context->getLocalizedName(),
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('orcidVerifyReport.tpl'));
            return;
        }

        $apiBase = $this->getApiBase();
        $httpClient = \APP\core\Application::get()->getHttpClient();

        try {
            $response = $httpClient->request(
                'GET',
                rtrim($apiBase, '/') . '/v3.0/' . $orcidBare . '/record',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 15,
                ]
            );

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200) {
                // Extract ORCID iD and name from API response for comparison
                $orcidName = null;
                if (isset($body['person']['name'])) {
                    $given = $body['person']['name']['given-names']['value'] ?? '';
                    $family = $body['person']['name']['family-name']['value'] ?? '';
                    $orcidName = trim($given . ' ' . $family);
                }

                // Extract the ORCID iD from the record path
                $apiOrcidBare = $body['orcid-identifier']['path'] ?? $orcidBare;

                // Compare ORCID iD: the API-returned iD must match the stored one
                $isOrcidMatch = $orcidBare && $apiOrcidBare && strcasecmp($orcidBare, $apiOrcidBare) === 0;
                $isNameMatch = $orcidName ? $this->namesMatch($member->getFullName(), $orcidName) : null;

                $member->setOrcidVerifiedCachedAt(Carbon::now()->toDateTimeString());
                if ($isOrcidMatch) {
                    $member->setOrcidVerified(true);
                } else {
                    $member->setOrcidVerified(false);
                    OrcidEditorialBoardPlugin::log('verify() ORCID iD mismatch: stored=' . $orcidBare . ' api=' . $apiOrcidBare);
                }
                $dao->updateObject($member);

                if ($isNameMatch === false) {
                    OrcidEditorialBoardPlugin::log('verify() Name mismatch (non-blocking): DB="' . $member->getFullName() . '" ORCID="' . $orcidName . '"');
                }

                $affiliations = [];
                $activities = $body['activities-summary'] ?? [];
                $employments = $activities['employments']['affiliation-group'] ?? [];
                foreach ($employments as $group) {
                    foreach ($group['summaries'] ?? [] as $summary) {
                        $emp = $summary['employment-summary'] ?? [];
                        $orgName = $emp['organization']['name'] ?? '';
                        if ($orgName) {
                            $affiliations[] = $orgName;
                        }
                    }
                }

                $templateMgr->assign([
                    'member' => $member,
                    'verificationStatus' => $isOrcidMatch ? 'verified' : 'not_verified',
                    'orcidName' => $orcidName,
                    'isNameMatch' => $isNameMatch,
                    'orcidAffiliations' => $affiliations,
                    'consentFingerprint' => $member->getConsentFingerprint(),
                    'consentDate' => $member->getOrcidVerifiedCachedAt(),
                    'orcidBare' => $orcidBare,
                    'journalName' => $context->getLocalizedName(),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('orcidVerifyReport.tpl'));
                return;
            }
        } catch (ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                $member->setOrcidVerified(false);
                $member->setOrcidAccessToken(null);
                $dao->updateObject($member);

                OrcidEditorialBoardPlugin::log('ORCID token revoked for member ' . $member->getId());

                $templateMgr->assign([
                    'member' => $member,
                    'verificationStatus' => 'revoked',
                    'journalName' => $context->getLocalizedName(),
                ]);
                $templateMgr->display($this->getPlugin()->getTemplateResource('orcidVerifyReport.tpl'));
                return;
            }
            OrcidEditorialBoardPlugin::log('ORCID verify error: ' . $e->getMessage());
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('ORCID verify error: ' . $e->getMessage());
        }

        $templateMgr->assign([
            'member' => $member,
            'verificationStatus' => 'error',
            'journalName' => $context->getLocalizedName(),
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('orcidVerifyReport.tpl'));
    }

    /**
     * Normalize an ORCID value to bare 0000-0000-0000-000X format.
     * Strips any https://orcid.org/ or https://sandbox.orcid.org/ prefix.
     */
    private function bareOrcid(?string $orcid): ?string
    {
        if (!$orcid) {
            return null;
        }
        $bare = preg_replace('~^https?://(sandbox\.)?orcid\.org/~i', '', trim($orcid));
        return preg_match('~^\d{4}-\d{4}-\d{4}-[\dX]{3,4}$~i', $bare) ? $bare : null;
    }

    /**
     * Fuzzy name comparison. Returns true if the names are likely the same person.
     * Normalizes: lowercase, strips titles/suffixes, compares sorted word tokens.
     * Matches if ≥80% of tokens overlap.
     */
    private function namesMatch(string $a, string $b): bool
    {
        $normalize = function (string $name): array {
            $name = mb_strtolower($name);
            // Strip common titles / suffixes
            $name = preg_replace('/\b(dr|prof|professor|mr|mrs|ms|sir|jr|sr|phd|md|ii|iii|iv)\b\.?/i', '', $name);
            // Remove punctuation (preserve all Unicode letters)
            $name = preg_replace('/[^\p{L}\s]/u', '', $name);
            $tokens = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
            sort($tokens);
            return $tokens;
        };

        $tokensA = $normalize($a);
        $tokensB = $normalize($b);

        if (empty($tokensA) || empty($tokensB)) {
            return false;
        }

        $intersection = count(array_intersect($tokensA, $tokensB));
        $maxLen = max(count($tokensA), count($tokensB));

        return ($intersection / $maxLen) >= 0.8;
    }

    /**
     * Generate an HMAC signature for a verify URL.
     */
    private function generateVerifySig(int $memberId): string
    {
        $secret = OrcidEditorialBoardPlugin::getHmacSecret();
        return hash_hmac('sha256', 'verify:' . $memberId, $secret);
    }

    /**
     * Generate an HMAC signature for an action URL.
     * Signs: "action:{type}:{memberId}" so parameters can't be tampered.
     */
    private function generateActionSig(string $type, int $memberId): string
    {
        $secret = OrcidEditorialBoardPlugin::getHmacSecret();
        return hash_hmac('sha256', 'action:' . $type . ':' . $memberId, $secret);
    }

    /**
     * Approve profile changes and clear the pending-confirmation dispute window.
     * Called when the member clicks "Approve & Confirm Changes" in the notification email.
     */
    public function approveEdit($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $sig = (string) $request->getUserVar('sig');
        if (!$context || !$memberId) {
            return $this->showError($request, __('common.error'));
        }

        // HMAC signature check
        $expectedSig = hash_hmac('sha256', 'approve:' . $memberId, OrcidEditorialBoardPlugin::getHmacSecret());
        if (!$sig || !hash_equals($expectedSig, $sig)) {
            OrcidEditorialBoardPlugin::log('approveEdit called with invalid HMAC sig for member ' . $memberId);
            return $this->showError($request, __('common.error'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('common.error'));
        }

        // Check if there is an active dispute window to clear
        $disputeExpiry = $member->getDisputeExpiresAt();
        if (!$disputeExpiry || strtotime($disputeExpiry) <= time()) {
            // No active dispute window — show friendly message instead of error
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'memberName' => $member->getFullName(),
                'journalName' => $context->getLocalizedName(),
                'alreadyApproved' => true,
            ]);
            $templateMgr->display($this->getPlugin()->getTemplateResource('approveSuccess.tpl'));
            return;
        }

        // Clear the dispute window — badge will show verified again
        $member->setDisputeExpiresAt(null);
        $dao->updateObject($member);

        OrcidEditorialBoardPlugin::log('Member ' . $memberId . ' approved profile changes; dispute window cleared.');

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'memberName' => $member->getFullName(),
            'journalName' => $context->getLocalizedName(),
            'alreadyApproved' => false,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('approveSuccess.tpl'));
    }

    /**
     * Generate an HMAC signature for a reportFalseClaim URL.
     */
    private function generateReportSig(int $memberId): string
    {
        $secret = OrcidEditorialBoardPlugin::getHmacSecret();
        return hash_hmac('sha256', 'report:' . $memberId, $secret);
    }

    /**
     * Build an ORCID OAuth authorize URL for the action flow.
     * State encodes: {actionToken}:{type}:{memberId}
     */
    private function buildActionAuthorizeUrl($request, string $actionToken, string $type, int $memberId): string
    {
        $base = $this->getOrcidSiteBase();
        $state = $actionToken . ':' . $type . ':' . $memberId;
        $query = http_build_query([
            'client_id' => $this->getOrcidClientId(),
            'response_type' => 'code',
            'scope' => '/authenticate',
            'redirect_uri' => $this->buildCallbackUrl($request),
            'state' => $state,
        ]);
        return $base . 'oauth/authorize?' . $query;
    }

    /**
     * Unified ORCID-gated action entry point.
     *
     * URL: editorialBoard/action?type=accept|deny|coi&memberId=X&sig=HMAC
     *
     * Shows a landing page with action details and "Sign in with ORCID" button.
     * The ORCID OAuth callback will verify the editor's identity before executing.
     */
    public function action($args, $request)
    {
        $context = $request->getContext();
        $type = (string) $request->getUserVar('type');
        $memberId = (int) $request->getUserVar('memberId');
        $sig = (string) $request->getUserVar('sig');

        if (!$context || !$memberId || !$type) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }

        // Validate HMAC to prevent parameter tampering
        $expectedSig = $this->generateActionSig($type, $memberId);
        if (!$sig || !hash_equals($expectedSig, $sig)) {
            OrcidEditorialBoardPlugin::log('Action called with invalid HMAC sig for type=' . $type . ' memberId=' . $memberId);
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }

        if (!in_array($type, ['accept', 'deny', 'coi'], true)) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }

        if (!$this->getPlugin()->isOrcidApiConfigured()) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.error.orcidNotConfigured'));
        }

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }

        // Check if action is still valid
        if ($type === 'accept' || $type === 'deny') {
            if ($member->getInvitationStatus() === 'accepted') {
                return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyAccepted'));
            }
            if ($member->getInvitationStatus() === 'denied') {
                return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyDenied'));
            }
        }
        if ($type === 'coi' && $member->getCoiStatus() === 'declared') {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.coi.alreadyDeclared'));
        }

        // Generate a one-time action token and store it in the session
        $actionToken = bin2hex(random_bytes(32));
        $session = $request->getSession();
        $session->setSessionVar('eb_action_token', $actionToken);
        $session->setSessionVar('eb_action_type', $type);
        $session->setSessionVar('eb_action_member_id', $memberId);

        // Build ORCID authorize URL
        $orcidUrl = $this->buildActionAuthorizeUrl($request, $actionToken, $type, $memberId);

        // Determine action description for the landing page
        $actionDescriptions = [
            'accept' => __('plugins.generic.orcidEditorialBoard.action.accept.description', ['journalName' => $context->getLocalizedName()]),
            'deny'   => __('plugins.generic.orcidEditorialBoard.action.deny.description', ['journalName' => $context->getLocalizedName()]),
            'coi'    => __('plugins.generic.orcidEditorialBoard.action.coi.description', ['journalName' => $context->getLocalizedName()]),
        ];

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'actionType' => $type,
            'memberName' => $member->getFullName(),
            'memberOrcid' => $this->bareOrcid($member->getOrcidId()),
            'journalName' => $context->getLocalizedName(),
            'orcidUrl' => $orcidUrl,
            'actionDescription' => $actionDescriptions[$type] ?? '',
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('actionLanding.tpl'));
    }

    /**
     * Execute invitation acceptance after ORCID verification.
     */
    private function executeAcceptInvitation($request, $context, $dao, $member)
    {
        if ($member->getInvitationStatus() === 'accepted') {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyAccepted'));
        }
        if ($member->getInvitationStatus() === 'denied') {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyDenied'));
        }

        $member->setInvitationStatus('accepted');
        $member->setInvitationToken(null);
        $member->setInvitationTokenExpiresAt(null);
        $dao->updateObject($member);

        OrcidEditorialBoardPlugin::log('Invitation ACCEPTED (ORCID-verified) by member ' . $member->getId() . ' (' . $member->getFullName() . ')');

        // After acceptance, redirect to COI declaration via ORCID-gated action
        // so the member must complete the full COI disclosure form
        $sig = $this->generateActionSig('coi', $member->getId());
        $dispatcher = $request->getDispatcher();
        $coiActionUrl = $dispatcher->url(
            $request,
            \APP\core\Application::ROUTE_PAGE,
            $context->getPath(),
            'editorialBoard',
            'action',
            null,
            ['type' => 'coi', 'memberId' => $member->getId(), 'sig' => $sig]
        );

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'responseType' => 'accepted',
            'memberName' => $member->getFullName(),
            'journalName' => $context->getLocalizedName(),
            'coiActionUrl' => $coiActionUrl,
            'message' => __('plugins.generic.orcidEditorialBoard.invitation.accepted.body', [
                'journalName' => $context->getLocalizedName(),
            ]),
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('invitationResponse.tpl'));
    }

    /**
     * Execute invitation denial after ORCID verification.
     * Shows deny form on GET, processes on POST.
     */
    private function executeDenyInvitation($request, $context, $dao, $member)
    {
        if ($member->getInvitationStatus() === 'accepted') {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyAccepted'));
        }
        if ($member->getInvitationStatus() === 'denied') {
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.alreadyDenied'));
        }

        $templateMgr = TemplateManager::getManager($request);

        // After ORCID callback this is always a GET — show the deny confirmation form
        // Store a session token so the POST can be validated
        $session = $request->getSession();
        $denySessionToken = bin2hex(random_bytes(16));
        $session->setSessionVar('eb_deny_session_' . $member->getId(), $denySessionToken);

        $templateMgr->assign([
            'responseType' => 'denyForm',
            'denySessionToken' => $denySessionToken,
            'memberId' => $member->getId(),
            'memberName' => $member->getFullName(),
            'journalName' => $context->getLocalizedName(),
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('invitationResponse.tpl'));
    }

    /**
     * Process the deny form POST after ORCID verification + deny form display.
     */
    public function denyInvitationConfirm($args, $request)
    {
        $context = $request->getContext();
        $memberId = (int) $request->getUserVar('memberId');
        $denySessionToken = (string) $request->getUserVar('denySessionToken');

        if (!$context || !$memberId || !$request->isPost()) {
            return $this->showError($request, __('common.error'));
        }

        // Validate session binding
        $session = $request->getSession();
        $expectedToken = $session->getSessionVar('eb_deny_session_' . $memberId);
        if (!$expectedToken || !hash_equals($expectedToken, $denySessionToken)) {
            OrcidEditorialBoardPlugin::log('Deny confirm POST without valid session for member ' . $memberId);
            return $this->showError($request, __('plugins.generic.orcidEditorialBoard.invitation.invalid'));
        }
        $session->setSessionVar('eb_deny_session_' . $memberId, null);

        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $dao->getById($memberId, $context->getId());
        if (!$member) {
            return $this->showError($request, __('common.error'));
        }

        $reason = trim((string) $request->getUserVar('denyReason'));

        $member->setInvitationStatus('denied');
        $member->setInvitationDeniedAt(Carbon::now()->toDateTimeString());
        $member->setInvitationDenyReason($reason ?: null);
        $member->setIsVisible(false);
        $member->setInvitationToken(null);
        $member->setInvitationTokenExpiresAt(null);
        $dao->updateObject($member);

        OrcidEditorialBoardPlugin::log(
            'Invitation DENIED (ORCID-verified) by member ' . $member->getId() . ' (' . $member->getFullName() . ')' .
            ($reason ? ' Reason: ' . $reason : '')
        );

        // Send denial confirmation email to the member
        try {
            $mailable = new \APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardDenialConfirmation($context, $member);
            $mailable->from($context->getData('contactEmail'), $context->getData('contactName'));
            $mailable->to($member->getEmail(), $member->getFullName());
            $mailable->subject(__('plugins.generic.orcidEditorialBoard.denialConfirmation.subject'))
                ->body(__('plugins.generic.orcidEditorialBoard.denialConfirmation.body'));
            \Illuminate\Support\Facades\Mail::send($mailable);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('Denial confirmation email error: ' . $e->getMessage());
        }

        // Notify admin
        try {
            $adminMail = new \APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardAdminNotification(
                $context, $member, $reason
            );
            $adminMail->from($context->getData('contactEmail'), $context->getData('contactName'));
            $adminMail->to($context->getData('contactEmail'), $context->getData('contactName'));
            $adminMail->subject(__('plugins.generic.orcidEditorialBoard.adminNotification.subject'));
            $adminMail->body(__('plugins.generic.orcidEditorialBoard.adminNotification.body'));
            \Illuminate\Support\Facades\Mail::send($adminMail);
        } catch (\Exception $e) {
            OrcidEditorialBoardPlugin::log('Admin denial notification error: ' . $e->getMessage());
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'responseType' => 'denied',
            'memberName' => $member->getFullName(),
            'journalName' => $context->getLocalizedName(),
            'message' => __('plugins.generic.orcidEditorialBoard.invitation.denied.body', [
                'journalName' => $context->getLocalizedName(),
            ]),
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('invitationResponse.tpl'));
    }

    /**
     * Redirect to COI form after ORCID verification.
     * Sets a session flag so coiDeclare knows the editor is ORCID-verified.
     */
    private function executeCoiAfterOrcid($request, $context, $dao, $member)
    {
        // Store ORCID-verification flag in session
        $session = $request->getSession();
        $coiSessionToken = bin2hex(random_bytes(16));
        $session->setSessionVar('eb_coi_verified_' . $member->getId(), $coiSessionToken);

        // Redirect to the existing coiDeclare page with verified session
        $dispatcher = $request->getDispatcher();
        $coiUrl = $dispatcher->url(
            $request,
            \APP\core\Application::ROUTE_PAGE,
            $context->getPath(),
            'editorialBoard',
            'coiDeclare',
            null,
            ['memberId' => $member->getId(), 'sessionToken' => $coiSessionToken]
        );
        return $request->redirectUrl($coiUrl);
    }

    /**
     * Display a styled error page.
     */
    private function showError($request, string $message)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'errorMessage' => $message,
        ]);
        $templateMgr->display($this->getPlugin()->getTemplateResource('error.tpl'));
    }

    private function getPlugin(): OrcidEditorialBoardPlugin
    {
        if (!$this->plugin) {
            $this->plugin = PluginRegistry::getPlugin('generic', 'orcideditorialboardplugin');
        }
        if (!$this->plugin) {
            throw new \RuntimeException('OrcidEditorialBoard plugin is not registered or is disabled.');
        }
        return $this->plugin;
    }
}
