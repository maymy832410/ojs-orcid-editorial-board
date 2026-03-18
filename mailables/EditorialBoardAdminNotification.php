<?php

namespace APP\plugins\generic\orcidEditorialBoard\mailables;

use APP\journal\Journal;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;

class EditorialBoardAdminNotification extends Mailable
{
    use Configurable;

    protected static ?string $name = 'plugins.generic.orcidEditorialBoard.adminNotification.name';
    protected static ?string $description = 'plugins.generic.orcidEditorialBoard.adminNotification.description';
    protected static ?string $emailTemplateKey = 'EDITORIAL_BOARD_ADMIN_NOTIFICATION';

    public function __construct(Journal $context, EditorialBoardMember $member, ?string $reason = null)
    {
        parent::__construct([$context]);

        $this->addData([
            'journalName' => $context->getLocalizedName(),
            'memberName' => $member->getFullName(),
            'memberEmail' => $member->getEmail(),
            'memberRole' => $member->getRole(),
            'denyReason' => $reason ?: 'No reason provided.',
        ]);
    }

    public static function getDataDescriptions(): array
    {
        return array_merge(parent::getDataDescriptions(), [
            'journalName' => __('emailTemplate.variable.context.name'),
            'memberName' => __('emailTemplate.variable.orcid.memberName'),
            'memberEmail' => __('plugins.generic.orcidEditorialBoard.emailVar.memberEmail'),
            'memberRole' => __('plugins.generic.orcidEditorialBoard.emailVar.memberRole'),
            'denyReason' => __('plugins.generic.orcidEditorialBoard.emailVar.denyReason'),
        ]);
    }
}
