<?php

/**
 * @file plugins/generic/orcidEditorialBoard/OrcidEditorialBoardPlugin.php
 *
 * @brief Main plugin class for ORCID-backed editorial board verification.
 *
 * @copyright Copyright (c) 2026 Peers Publishing
 * @author Mohanad G. Yaseen
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3.0
 */

namespace APP\plugins\generic\orcidEditorialBoard;

use APP\core\Application;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMemberDAO;
use APP\plugins\generic\orcidEditorialBoard\controllers\grid\EditorialBoardGridHandler;
use APP\plugins\generic\orcidEditorialBoard\controllers\grid\form\OrcidApiSettingsForm;
use APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardConsentRequest;
use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardHandler;
use APP\template\TemplateManager;
use PKP\config\Config;
use PKP\core\JSONMessage;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class OrcidEditorialBoardPlugin extends GenericPlugin
{
    public const ORCID_API_URL_MEMBER = 'https://api.orcid.org/';
    public const ORCID_API_URL_PUBLIC = 'https://pub.orcid.org/';
    public const ORCID_SITE_BASE = 'https://orcid.org/';

    /** @var bool */
    private $orcidConfigured = false;

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (!$success) {
            return false;
        }

        // During maintenance/upgrade skip further bootstrapping.
        if (Application::isUnderMaintenance()) {
            return true;
        }

        if ($this->getEnabled($mainContextId)) {
            self::log('Registering OrcidEditorialBoardPlugin for context ' . (string) $mainContextId);
            $this->orcidConfigured = $this->isOrcidApiConfigured();

            // DAO registration
            DAORegistry::registerDAO('EditorialBoardMemberDAO', new EditorialBoardMemberDAO());

            Hook::add('LoadHandler', [$this, 'setupPageHandler']);
            Hook::add('LoadComponentHandler', [$this, 'setupGridHandler']);
            Hook::add('Template::Settings::website', [$this, 'addSettingsTab']);
            Hook::add('Mailer::Mailables', [$this, 'addMailable']);
        }

        return true;
    }

    /**
     * Load handler for the public page.
     */
    public function setupPageHandler($hookName, $params)
    {
        $page = &$params[0];
        $op = &$params[1];
        $sourceFile = &$params[2];
        $handler = &$params[3];

        if ($page === 'editorialBoard') {
            // Only allow specific operations to prevent unintended method routing
            $allowedOps = [
                'index', 'consent', 'callback',
                'coiDeclare', 'verify',
                'reportFalseClaim',
                'action', 'denyInvitationConfirm',
            ];
            if ($op && !in_array($op, $allowedOps, true)) {
                return false;
            }
            // Handler class is autoloaded via Composer namespace
            $handler = new OrcidEditorialBoardHandler();
            return true;
        }

        return false;
    }

    /**
     * Load component handler for grids.
     */
    public function setupGridHandler($hookName, $params)
    {
        $component = &$params[0];
        $componentInstance = &$params[2];

        if ($component === 'plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler') {
            if (!defined('ORCID_EDITORIAL_BOARD_PLUGIN_NAME')) {
                define('ORCID_EDITORIAL_BOARD_PLUGIN_NAME', $this->getName());
            }
            // Provide a ready-made handler instance so OJS does not call instantiate()
            $componentInstance = new EditorialBoardGridHandler();
            return true;
        }

        return false;
    }

    /**
     * Add settings tab to Website settings.
     */
    public function addSettingsTab($hookName, $args)
    {
        $templateMgr = $args[1];
        $output = &$args[2];
        $output .= $templateMgr->fetch($this->getTemplateResource('editorialBoardTab.tpl'));
        return false;
    }

    /**
     * Register mailables.
     */
    public function addMailable(string $hookName, array $args): void
    {
        $args[0]->push(EditorialBoardConsentRequest::class);
        $args[0]->push(\APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardCoiRequest::class);
        $args[0]->push(\APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardChangeNotification::class);
        $args[0]->push(\APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardInvitation::class);
        $args[0]->push(\APP\plugins\generic\orcidEditorialBoard\mailables\EditorialBoardDenialConfirmation::class);
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.generic.orcidEditorialBoard.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.generic.orcidEditorialBoard.description');
    }

    /**
     * @copydoc Plugin::getInstallMigration()
     */
    public function getInstallMigration()
    {
        return new Schema\EditorialBoardSchemaMigration();
    }

    /**
     * Install email templates for the plugin.
     */
    public function getInstallEmailTemplatesFile()
    {
        return $this->getPluginPath() . '/emailTemplates.xml';
    }

    /**
     * Expose ORCID readiness (for templates/forms).
     */
    public function isOrcidConfigured(): bool
    {
        return $this->orcidConfigured;
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        array_unshift($actions, new LinkAction(
            'settings',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        ));
        array_unshift($actions, new LinkAction(
            'about',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, ['verb' => 'about', 'plugin' => $this->getName(), 'category' => 'generic']),
                __('plugins.generic.orcidEditorialBoard.about.title')
            ),
            __('plugins.generic.orcidEditorialBoard.about.title'),
            null
        ));
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $contextId = $request->getContext()->getId();
                $form = new OrcidApiSettingsForm($this, $contextId);
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));

            case 'about':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign([
                    'pluginVersion' => $this->getCurrentVersion()->getVersionString(),
                    'currentYear' => date('Y'),
                    'peersLogoUrl' => $request->getBaseUrl() . '/plugins/generic/' . $this->getName() . '/assets/peers-logo.png',
                ]);
                return new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('about.tpl')));
        }
        return parent::manage($args, $request);
    }

    /**
     * Check that ORCID API credentials are configured in plugin settings.
     */
    public function isOrcidApiConfigured(): bool
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : 0;

        $clientId = $this->getSetting($contextId, 'orcidClientId');
        $clientSecret = $this->getSetting($contextId, 'orcidClientSecret');
        if (!$clientId || !$clientSecret) {
            self::log('ORCID config missing orcidClientId/orcidClientSecret in plugin settings');
            return false;
        }
        return true;
    }

    /**
     * Helper to read an ORCID plugin setting for the given context.
     */
    public function getOrcidSetting(int $contextId, string $key)
    {
        return $this->getSetting($contextId, $key);
    }

    /**
     * Simple plugin log helper.
     */
    public static function log(string $message): void
    {
        $filesDir = Config::getVar('files', 'files_dir');
        $file = rtrim($filesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'orcidEditorialBoard.log';
        $timestamp = date('Y-m-d H:i:s');
        error_log("{$timestamp} {$message}\n", 3, $file);
    }

    /**
     * Get the HMAC secret used for signing URLs.
     * Centralised to avoid duplication and the risk of inconsistent fallback values.
     *
     * @throws \RuntimeException if no secret is available
     */
    public static function getHmacSecret(): string
    {
        $secret = Config::getVar('security', 'api_key_secret')
            ?: Config::getVar('database', 'password');
        if (!$secret) {
            self::log('CRITICAL: No HMAC secret available (security.api_key_secret and database.password are both empty)');
            throw new \RuntimeException('Server configuration error: no signing secret available.');
        }
        return $secret;
    }
}
