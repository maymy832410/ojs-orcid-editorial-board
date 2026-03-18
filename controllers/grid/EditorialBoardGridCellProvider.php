<?php

namespace APP\plugins\generic\orcidEditorialBoard\controllers\grid;

use PKP\controllers\grid\DataObjectGridCellProvider;

class EditorialBoardGridCellProvider extends DataObjectGridCellProvider
{
    public function getTemplateVarsFromRowColumn($row, $column)
    {
        $member = $row->getData();
        switch ($column->getId()) {
            case 'fullName':
                return ['label' => $member->getFullName()];
            case 'role':
                return ['label' => $member->getRole()];
            case 'email':
                return ['label' => $member->getEmail()];
            case 'orcid':
                $label = $member->getOrcidVerified() ? __('plugins.generic.orcidEditorialBoard.verifiedShort') : __('plugins.generic.orcidEditorialBoard.notVerifiedShort');
                return ['label' => $label];
            case 'invitationStatus':
                $status = $member->getInvitationStatus() ?: 'not_sent';
                $map = [
                    'not_sent' => __('plugins.generic.orcidEditorialBoard.invitation.statusNotSent'),
                    'pending'  => __('plugins.generic.orcidEditorialBoard.invitation.statusPending'),
                    'accepted' => __('plugins.generic.orcidEditorialBoard.invitation.statusAccepted'),
                    'denied'   => __('plugins.generic.orcidEditorialBoard.invitation.statusDenied'),
                ];
                return ['label' => $map[$status] ?? $status];
            default:
                return ['label' => ''];
        }
    }
}
