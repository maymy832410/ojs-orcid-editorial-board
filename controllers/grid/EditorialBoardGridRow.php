<?php

namespace APP\plugins\generic\orcidEditorialBoard\controllers\grid;

use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use PKP\controllers\grid\GridRow;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\request\RemoteActionConfirmationModal;

class EditorialBoardGridRow extends GridRow
{
    /** @var OrcidEditorialBoardPlugin */
    private $plugin;

    public function __construct(OrcidEditorialBoardPlugin $plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
    }

    public function initialize($request, $template = null)
    {
        parent::initialize($request, $template);

        $memberId = $this->getId();
        if ($memberId) {
            $router = $request->getRouter();

            $this->addAction(
                new LinkAction(
                    'editMember',
                    new AjaxModal(
                        $router->url($request, null, null, 'editMember', null, ['memberId' => $memberId]),
                        __('grid.action.edit'),
                        'modal_edit',
                        true
                    ),
                    __('grid.action.edit'),
                    'edit'
                )
            );

            $this->addAction(
                new LinkAction(
                    'deleteMember',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('common.confirmDelete'),
                        __('grid.action.delete'),
                        $router->url($request, null, null, 'deleteMember', null, ['memberId' => $memberId]),
                        'modal_delete'
                    ),
                    __('grid.action.delete'),
                    'delete'
                )
            );

            $this->addAction(
                new LinkAction(
                    'sendConsentEmail',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('plugins.generic.orcidEditorialBoard.sendConsent.confirm'),
                        __('plugins.generic.orcidEditorialBoard.sendConsent'),
                        $router->url($request, null, null, 'sendConsentEmail', null, ['memberId' => $memberId]),
                        'modal_information'
                    ),
                    __('plugins.generic.orcidEditorialBoard.sendConsent'),
                    'notify'
                )
            );

            // Search OpenAlex for this member
            $this->addAction(
                new LinkAction(
                    'searchOpenAlex',
                    new AjaxModal(
                        $router->url($request, null, null, 'searchOpenAlex', null, ['memberId' => $memberId]),
                        __('plugins.generic.orcidEditorialBoard.openalex.search'),
                        'modal_information'
                    ),
                    __('plugins.generic.orcidEditorialBoard.openalex.search'),
                    'information'
                )
            );

            // Refresh cached OpenAlex keywords if already linked
            $member = $this->getData();
            if ($member && $member->getOpenalexId()) {
                $this->addAction(
                    new LinkAction(
                        'refreshOpenAlex',
                        new RemoteActionConfirmationModal(
                            $request->getSession(),
                            __('plugins.generic.orcidEditorialBoard.openalex.refresh.confirm'),
                            __('plugins.generic.orcidEditorialBoard.openalex.refresh'),
                            $router->url($request, null, null, 'selectOpenAlexAuthor', null, ['memberId' => $memberId]),
                            'modal_information'
                        ),
                        __('plugins.generic.orcidEditorialBoard.openalex.refresh'),
                        'reload'
                    )
                );
            }

            // Send COI declaration request email
            $this->addAction(
                new LinkAction(
                    'sendCoiEmail',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('plugins.generic.orcidEditorialBoard.coi.sendConfirm'),
                        __('plugins.generic.orcidEditorialBoard.coi.sendEmail'),
                        $router->url($request, null, null, 'sendCoiEmail', null, ['memberId' => $memberId]),
                        'modal_information'
                    ),
                    __('plugins.generic.orcidEditorialBoard.coi.sendEmail'),
                    'notify'
                )
            );

            // Toggle visibility
            $visLabel = ($member && $member->getIsVisible())
                ? __('plugins.generic.orcidEditorialBoard.hideEditor')
                : __('plugins.generic.orcidEditorialBoard.showEditor');
            $this->addAction(
                new LinkAction(
                    'toggleVisibility',
                    new RemoteActionConfirmationModal(
                        $request->getSession(),
                        __('plugins.generic.orcidEditorialBoard.toggleVisibility.confirm'),
                        $visLabel,
                        $router->url($request, null, null, 'toggleVisibility', null, ['memberId' => $memberId]),
                        'modal_information'
                    ),
                    $visLabel,
                    'setting'
                )
            );
        }
    }
}
