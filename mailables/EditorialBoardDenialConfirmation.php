<?php

namespace APP\plugins\generic\orcidEditorialBoard\mailables;

use APP\journal\Journal;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;

class EditorialBoardDenialConfirmation extends Mailable
{
    use Configurable;

    protected static ?string $name = 'plugins.generic.orcidEditorialBoard.denialConfirmation.name';
    protected static ?string $description = 'plugins.generic.orcidEditorialBoard.denialConfirmation.description';
    protected static ?string $emailTemplateKey = 'EDITORIAL_BOARD_DENIAL_CONFIRMATION';

    public function __construct(Journal $context, EditorialBoardMember $member)
    {
        parent::__construct([$context]);
        $journalUrl = method_exists($context, 'getUrl') ? $context->getUrl() : null;
        $publisherName = (string) $context->getData('publisherInstitution');
        $contactName = (string) $context->getData('contactName');
        $contactEmail = (string) $context->getData('contactEmail');

        $footerLine1 = $context->getLocalizedName() . ($publisherName ? ' — ' . $publisherName : '');
        $footerLine2 = $journalUrl ? ('Website: ' . $journalUrl) : '';
        $footerLine3 = ($contactName || $contactEmail) ? ('Contact: ' . trim($contactName . ' ' . ($contactEmail ? '(' . $contactEmail . ')' : ''))) : '';

        $this->addData([
            'journalName' => $context->getLocalizedName(),
            'memberName' => $member->getFullName(),
            'contactName' => $contactName,
            'contactEmail' => $contactEmail,
            'publisherName' => $publisherName,
            'footerLine1' => $footerLine1,
            'footerLine2' => $footerLine2,
            'footerLine3' => $footerLine3,
        ]);
    }

    public static function getDataDescriptions(): array
    {
        return array_merge(parent::getDataDescriptions(), [
            'journalName' => __('emailTemplate.variable.context.name'),
            'memberName' => __('emailTemplate.variable.orcid.memberName'),
            'footerLine1' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine1'),
            'footerLine2' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine2'),
            'footerLine3' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine3'),
        ]);
    }
}
