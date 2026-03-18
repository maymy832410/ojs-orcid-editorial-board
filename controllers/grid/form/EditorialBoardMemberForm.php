<?php

namespace APP\plugins\generic\orcidEditorialBoard\controllers\grid\form;

use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMemberDAO;
use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorEmail;
use PKP\form\validation\FormValidatorPost;

class EditorialBoardMemberForm extends Form
{
    private $contextId;
    private $plugin;
    private $memberId;

    public function __construct(string $template, int $contextId, OrcidEditorialBoardPlugin $plugin, ?int $memberId = null)
    {
        parent::__construct($template);
        $this->contextId = $contextId;
        $this->plugin = $plugin;
        $this->memberId = $memberId;

        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
        $this->addCheck(new FormValidator($this, 'fullName', 'required', 'plugins.generic.orcidEditorialBoard.validation.fullName'));
        $this->addCheck(new FormValidator($this, 'role', 'required', 'plugins.generic.orcidEditorialBoard.validation.role'));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'plugins.generic.orcidEditorialBoard.validation.email'));
        $this->addCheck(new FormValidator($this, 'country', 'required', 'plugins.generic.orcidEditorialBoard.validation.country'));
    }

    /**
     * Custom validation for tenure dates.
     */
    public function validate($callHooks = true)
    {
        $valid = parent::validate($callHooks);

        $tenureStart = $this->getData('tenureStart');
        $tenureEnd = $this->getData('tenureEnd');

        // Validate Y-m-d format if provided
        if ($tenureStart && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tenureStart)) {
            $this->addError('tenureStart', __('plugins.generic.orcidEditorialBoard.validation.dateFormat'));
            $valid = false;
        }
        if ($tenureEnd && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tenureEnd)) {
            $this->addError('tenureEnd', __('plugins.generic.orcidEditorialBoard.validation.dateFormat'));
            $valid = false;
        }

        // Validate tenureEnd >= tenureStart when both are provided
        if ($tenureStart && $tenureEnd && $tenureEnd < $tenureStart) {
            $this->addError('tenureEnd', __('plugins.generic.orcidEditorialBoard.validation.tenureEndBeforeStart'));
            $valid = false;
        }

        return $valid;
    }

    public function initData()
    {
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $this->memberId ? $dao->getById($this->memberId, $this->contextId) : null;

        $this->setData('memberId', $member?->getId());
        $this->setData('fullName', $member?->getFullName());
        $this->setData('role', $member?->getRole());
        $this->setData('email', $member?->getEmail());
        $this->setData('orcidId', $member?->getOrcidId());
        $this->setData('scopusId', $member?->getScopusId());
        $this->setData('googleScholar', $member?->getGoogleScholar());
        $this->setData('photoUrl', $member?->getPhotoUrl());
        $this->setData('affiliation', $member?->getAffiliation());
        $this->setData('country', $member?->getCountry() ?? '');
        $this->setData('sortOrder', $member?->getSortOrder() ?? 0);
        $this->setData('tenureStart', $member?->getTenureStart() ?? date('Y-m-d'));
        $this->setData('tenureEnd', $member?->getTenureEnd() ?? '');
        $this->setData('isVisible', $member ? $member->getIsVisible() : true);
        $this->setData('coiStatus', $member?->getCoiStatus() ?? 'pending');
        $this->setData('coiDeclaredAt', $member?->getCoiDeclaredAt());
        $this->setData('coiText', $member?->getCoiText());
        $this->setData('memberStatus', $member?->getStatus() ?? 'active');

        // Predefined role options
        $this->setData('roleOptions', [
            'Editor in Chief' => 'Editor in Chief',
            'Managing Editor' => 'Managing Editor',
            'Associate Editor' => 'Associate Editor',
            'Editorial Member' => 'Editorial Member',
        ]);

        // Country options (ISO alpha-2 => localized name)
        $countries = [];
        foreach (Locale::getCountries() as $c) {
            $countries[$c->getAlpha2()] = $c->getLocalName();
        }
        asort($countries);
        $this->setData('countryOptions', $countries);
    }

    public function readInputData()
    {
        $this->readUserVars([
            'memberId',
            'fullName',
            'role',
            'email',
            'orcidId',
            'scopusId',
            'googleScholar',
            'photoUrl',
            'affiliation',
            'country',
            'sortOrder',
            'tenureStart',
            'tenureEnd',
            'isVisible',
            'resetStatus',
        ]);
    }

    public function execute(...$functionArgs)
    {
        $dao = DAORegistry::getDAO('EditorialBoardMemberDAO'); /** @var EditorialBoardMemberDAO $dao */
        $member = $this->memberId ? $dao->getById($this->memberId, $this->contextId) : null;
        if (!$member) {
            $member = new EditorialBoardMember();
            $member->setContextId($this->contextId);
        }

        $member->setFullName($this->getData('fullName'));
        $member->setRole($this->getData('role'));
        $member->setEmail($this->getData('email'));
        $member->setOrcidId($this->getData('orcidId'));
        $member->setScopusId($this->getData('scopusId'));
        $member->setGoogleScholar($this->getData('googleScholar'));
        $member->setPhotoUrl($this->getData('photoUrl'));
        $member->setAffiliation($this->getData('affiliation'));
        $member->setCountry($this->getData('country'));
        $member->setSortOrder((int) $this->getData('sortOrder'));
        $member->setTenureStart($this->getData('tenureStart') ?: null);
        $member->setTenureEnd($this->getData('tenureEnd') ?: null);
        $member->setIsVisible((bool) $this->getData('isVisible'));

        // Reset status to active if admin checked the box
        if ($this->getData('resetStatus')) {
            $member->setStatus('active');
        }

        if ($member->getTenureEnd() && \Carbon\Carbon::parse($member->getTenureEnd())->isPast()) {
            $member->setTenureStatus('expired');
        } else {
            $member->setTenureStatus('active');
        }

        if ($member->getId()) {
            $dao->updateObject($member);
        } else {
            $dao->insertObject($member);
        }
    }
}
