<?php

namespace APP\plugins\generic\orcidEditorialBoard\controllers\grid\form;

use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;

class OrcidApiSettingsForm extends Form
{
    private $contextId;
    private $plugin;

    public function __construct(OrcidEditorialBoardPlugin $plugin, int $contextId)
    {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
        $this->addCheck(new FormValidator($this, 'orcidClientId', 'required', 'plugins.generic.orcidEditorialBoard.settings.clientIdRequired'));
        $this->addCheck(new FormValidator($this, 'orcidClientSecret', 'required', 'plugins.generic.orcidEditorialBoard.settings.clientSecretRequired'));
    }

    public function initData()
    {
        $this->setData('orcidApiEnvironment', $this->plugin->getSetting($this->contextId, 'orcidApiEnvironment') ?: 'production');
        $this->setData('orcidClientId', $this->plugin->getSetting($this->contextId, 'orcidClientId') ?: '');
        $this->setData('orcidClientSecret', $this->plugin->getSetting($this->contextId, 'orcidClientSecret') ?: '');
    }

    public function readInputData()
    {
        $this->readUserVars(['orcidApiEnvironment', 'orcidClientId', 'orcidClientSecret']);
    }

    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = \APP\template\TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());

        $context = $request->getContext();
        $redirectUri = $request->getDispatcher()->url(
            $request,
            \APP\core\Application::ROUTE_PAGE,
            $context ? $context->getPath() : null,
            'editorialBoard',
            'callback'
        );
        $templateMgr->assign('redirectUri', $redirectUri);

        $editorialBoardUrl = $request->getDispatcher()->url(
            $request,
            \APP\core\Application::ROUTE_PAGE,
            $context ? $context->getPath() : null,
            'editorialBoard',
            'index'
        );
        $templateMgr->assign('editorialBoardUrl', $editorialBoardUrl);

        $savedSecret = $this->plugin->getSetting($this->contextId, 'orcidClientSecret');
        if ($savedSecret) {
            $templateMgr->assign('maskedSecret', str_repeat('*', max(0, strlen($savedSecret) - 4)) . substr($savedSecret, -4));
        } else {
            $templateMgr->assign('maskedSecret', '');
        }

        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs)
    {
        $this->plugin->updateSetting($this->contextId, 'orcidApiEnvironment', $this->getData('orcidApiEnvironment'), 'string');
        $this->plugin->updateSetting($this->contextId, 'orcidClientId', trim($this->getData('orcidClientId')), 'string');
        $secret = $this->getData('orcidClientSecret');
        if ($secret && !preg_match('/^\*+/', $secret)) {
            $this->plugin->updateSetting($this->contextId, 'orcidClientSecret', trim($secret), 'string');
        }
        parent::execute(...$functionArgs);
    }
}
