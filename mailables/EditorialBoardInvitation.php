<?php

namespace APP\plugins\generic\orcidEditorialBoard\mailables;

use APP\journal\Journal;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;

class EditorialBoardInvitation extends Mailable
{
    use Configurable;

    protected static ?string $name = 'plugins.generic.orcidEditorialBoard.invitation.name';
    protected static ?string $description = 'plugins.generic.orcidEditorialBoard.invitation.description';
    protected static ?string $emailTemplateKey = 'EDITORIAL_BOARD_INVITATION';

    public function __construct(
        Journal $context,
        EditorialBoardMember $member,
        string $acceptUrl,
        string $denyUrl,
        array $openalexData = []
    ) {
        parent::__construct([$context]);

        $journalUrl = method_exists($context, 'getUrl') ? $context->getUrl() : null;
        $publisherName = (string) $context->getData('publisherInstitution');
        $contactName = (string) $context->getData('contactName');
        $contactEmail = (string) $context->getData('contactEmail');

        $footerLine1 = $context->getLocalizedName() . ($publisherName ? ' — ' . $publisherName : '');
        $footerLine2 = $journalUrl ? ('Website: ' . $journalUrl) : '';
        $footerLine3 = ($contactName || $contactEmail) ? ('Contact: ' . trim($contactName . ' ' . ($contactEmail ? '(' . $contactEmail . ')' : ''))) : '';

        // Build the member detail card HTML
        $detailRows = [];
        $detailRows[] = $this->detailRow('Role', $member->getRole());
        $detailRows[] = $this->detailRow('Affiliation', $member->getAffiliation());
        $detailRows[] = $this->detailRow('Country', $member->getCountry());
        if ($member->getOrcidId()) {
            $detailRows[] = $this->detailRow('ORCID iD', $member->getOrcidId());
        }
        if ($member->getScopusId()) {
            $detailRows[] = $this->detailRow('Scopus ID', $member->getScopusId());
        }
        if ($member->getGoogleScholar()) {
            $detailRows[] = $this->detailRow('Google Scholar', $member->getGoogleScholar());
        }
        if ($member->getEmail()) {
            $detailRows[] = $this->detailRow('Email', $member->getEmail());
        }

        // OpenAlex metrics
        $hIndex = $openalexData['h_index'] ?? null;
        $worksCount = $openalexData['works_count'] ?? null;
        $citedBy = $openalexData['cited_by_count'] ?? null;
        if ($hIndex !== null) {
            $detailRows[] = $this->detailRow('H-Index', (string) $hIndex);
        }
        if ($worksCount !== null) {
            $detailRows[] = $this->detailRow('Works', (string) $worksCount);
        }
        if ($citedBy !== null) {
            $detailRows[] = $this->detailRow('Citations', (string) $citedBy);
        }

        $memberDetailsHtml = implode('', $detailRows);

        $this->addData([
            'acceptUrl' => $acceptUrl,
            'denyUrl' => $denyUrl,
            'journalName' => $context->getLocalizedName(),
            'memberName' => $member->getFullName(),
            'memberRole' => $member->getRole(),
            'memberAffiliation' => $member->getAffiliation() ?: '',
            'memberDetailsHtml' => $memberDetailsHtml,
            'journalUrl' => $journalUrl,
            'contactName' => $contactName,
            'contactEmail' => $contactEmail,
            'publisherName' => $publisherName,
            'footerLine1' => $footerLine1,
            'footerLine2' => $footerLine2,
            'footerLine3' => $footerLine3,
        ]);
    }

    private function detailRow(string $label, ?string $value): string
    {
        if (!$value) {
            return '';
        }
        $font = "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;";
        return '<tr>'
            . '<td style="' . $font . 'padding:8px 14px;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;vertical-align:top;border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($label) . '</td>'
            . '<td style="' . $font . 'padding:8px 14px;font-size:14px;color:#1e293b;border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($value) . '</td>'
            . '</tr>';
    }

    public static function getDataDescriptions(): array
    {
        return array_merge(parent::getDataDescriptions(), [
            'acceptUrl' => __('plugins.generic.orcidEditorialBoard.emailVar.acceptUrl'),
            'denyUrl' => __('plugins.generic.orcidEditorialBoard.emailVar.denyUrl'),
            'journalName' => __('emailTemplate.variable.context.name'),
            'memberName' => __('emailTemplate.variable.orcid.memberName'),
            'memberRole' => __('plugins.generic.orcidEditorialBoard.emailVar.memberRole'),
            'memberAffiliation' => __('plugins.generic.orcidEditorialBoard.emailVar.memberAffiliation'),
            'memberDetailsHtml' => __('plugins.generic.orcidEditorialBoard.emailVar.memberDetailsHtml'),
            'footerLine1' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine1'),
            'footerLine2' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine2'),
            'footerLine3' => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine3'),
        ]);
    }
}
