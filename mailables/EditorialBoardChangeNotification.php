<?php

namespace APP\plugins\generic\orcidEditorialBoard\mailables;

use APP\journal\Journal;
use APP\plugins\generic\orcidEditorialBoard\classes\EditorialBoardMember;
use PKP\mail\Mailable;
use PKP\mail\traits\Configurable;

class EditorialBoardChangeNotification extends Mailable
{
    use Configurable;

    protected static ?string $name = 'plugins.generic.orcidEditorialBoard.changeNotification.name';
    protected static ?string $description = 'plugins.generic.orcidEditorialBoard.changeNotification.description';
    protected static ?string $emailTemplateKey = 'EDITORIAL_BOARD_CHANGE_NOTIFICATION';

    /**
     * @param Journal $context
     * @param EditorialBoardMember $member
     * @param array $changedFields  Array of ['label'=>..., 'old'=>..., 'new'=>...]
     * @param string|null $consentUrl  If identity fields changed, the re-verification consent URL
     * @param string|null $disputeUrl  URL the member can use to dispute the change
     * @param string|null $coiUrl      If identity fields changed, the COI re-declaration URL
     * @param string|null $approveUrl  URL for the member to approve changes and restore verified badge
     */
    public function __construct(
        Journal $context,
        EditorialBoardMember $member,
        array $changedFields,
        ?string $consentUrl = null,
        ?string $disputeUrl = null,
        ?string $coiUrl = null,
        ?string $approveUrl = null
    ) {
        parent::__construct([$context]);

        $journalName = $context->getLocalizedName();
        $publisherName = (string) $context->getData('publisherInstitution');
        $contactName = (string) $context->getData('contactName');
        $contactEmail = (string) $context->getData('contactEmail');
        $journalUrl = method_exists($context, 'getUrl') ? $context->getUrl() : null;

        // Build an HTML diff table
        $diffHtml = '<table style="border-collapse:collapse;width:100%;font-size:14px;margin:12px 0">';
        $diffHtml .= '<tr style="background:#f1f5f9"><th style="text-align:left;padding:8px 12px;border:1px solid #e2e8f0">Field</th><th style="text-align:left;padding:8px 12px;border:1px solid #e2e8f0">Before</th><th style="text-align:left;padding:8px 12px;border:1px solid #e2e8f0">After</th></tr>';
        foreach ($changedFields as $cf) {
            $diffHtml .= '<tr>';
            $diffHtml .= '<td style="padding:8px 12px;border:1px solid #e2e8f0;font-weight:600">' . htmlspecialchars($cf['label']) . '</td>';
            $diffHtml .= '<td style="padding:8px 12px;border:1px solid #e2e8f0;color:#ef4444;text-decoration:line-through">' . htmlspecialchars($cf['old']) . '</td>';
            $diffHtml .= '<td style="padding:8px 12px;border:1px solid #e2e8f0;color:#16a34a;font-weight:600">' . htmlspecialchars($cf['new']) . '</td>';
            $diffHtml .= '</tr>';
        }
        $diffHtml .= '</table>';

        // Build action buttons HTML (re-verify + dispute)
        $actionsHtml = '';
        if ($consentUrl) {
            $actionsHtml .= '<div style="margin:20px 0;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;">';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#991b1b;font-weight:700;font-size:14px;margin-bottom:8px;">⚠ Your identity information was changed — ORCID re-verification required</div>';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#7f1d1d;font-size:13px;margin-bottom:14px;">Your ORCID verification has been reset because identity-related fields were changed. Please re-verify:</div>';
            $actionsHtml .= '<a href="' . htmlspecialchars($consentUrl) . '" style="display:inline-block;padding:12px 24px;background:#16a34a;color:#ffffff;text-decoration:none;border-radius:8px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;font-weight:700;font-size:14px;">Review &amp; Re-Verify via ORCID</a>';
            $actionsHtml .= '</div>';
        }
        if ($approveUrl) {
            $actionsHtml .= '<div style="margin:16px 0;padding:14px 18px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;">';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#166534;font-weight:700;font-size:14px;margin-bottom:8px;">✅ Review the changes above</div>';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#15803d;font-size:13px;margin-bottom:14px;">If the changes are correct, click below to approve them and restore your verified badge:</div>';
            $actionsHtml .= '<a href="' . htmlspecialchars($approveUrl) . '" style="display:inline-block;padding:12px 24px;background:#16a34a;color:#ffffff;text-decoration:none;border-radius:8px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;font-weight:700;font-size:14px;">Approve &amp; Confirm Changes</a>';
            $actionsHtml .= '</div>';
        }
        if ($disputeUrl) {
            $actionsHtml .= '<div style="margin:16px 0;padding:14px 18px;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;">';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;font-size:13px;margin-bottom:10px;">If you believe these changes are incorrect, you have <strong>7 days</strong> to dispute them:</div>';
            $actionsHtml .= '<a href="' . htmlspecialchars($disputeUrl) . '" style="display:inline-block;padding:10px 20px;background:#dc2626;color:#ffffff;text-decoration:none;border-radius:6px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;font-weight:700;font-size:13px;">Dispute This Change</a>';
            $actionsHtml .= '</div>';
        }
        if ($coiUrl) {
            $actionsHtml .= '<div style="margin:16px 0;padding:14px 18px;background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;">';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#1e40af;font-weight:700;font-size:14px;margin-bottom:8px;">📋 COI Declaration Required</div>';
            $actionsHtml .= '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;color:#1e3a5f;font-size:13px;margin-bottom:14px;">Your conflict-of-interest declaration has been reset because your identity information was changed. Please re-submit your COI disclosure:</div>';
            $actionsHtml .= '<a href="' . htmlspecialchars($coiUrl) . '" style="display:inline-block;padding:10px 20px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;font-weight:700;font-size:13px;">Re-Submit COI Declaration</a>';
            $actionsHtml .= '</div>';
        }

        $footerLine1 = $journalName . ($publisherName ? ' — ' . $publisherName : '');
        $footerLine2 = $journalUrl ? ('Website: ' . $journalUrl) : '';
        $footerLine3 = ($contactName || $contactEmail) ? ('Contact: ' . trim($contactName . ' ' . ($contactEmail ? '(' . $contactEmail . ')' : ''))) : '';

        $this->addData([
            'memberName'     => $member->getFullName(),
            'journalName'    => $journalName,
            'changeDiffHtml' => $diffHtml,
            'actionsHtml'    => $actionsHtml,
            'consentUrl'     => $consentUrl ?? '',
            'disputeUrl'     => $disputeUrl ?? '',
            'approveUrl'     => $approveUrl ?? '',
            'coiUrl'         => $coiUrl ?? '',
            'contactName'    => $contactName,
            'contactEmail'   => $contactEmail,
            'footerLine1'    => $footerLine1,
            'footerLine2'    => $footerLine2,
            'footerLine3'    => $footerLine3,
        ]);
        // NOTE: subject() and body() are intentionally NOT called here.
        // They must be set by the handler AFTER construction (matching the
        // consent email pattern that is proven to work with OJS 3.4's
        // Mailer pipeline).
    }

    public static function getDataDescriptions(): array
    {
        return array_merge(parent::getDataDescriptions(), [
            'memberName'     => __('emailTemplate.variable.orcid.memberName'),
            'journalName'    => __('emailTemplate.variable.context.name'),
            'changeDiffHtml' => __('plugins.generic.orcidEditorialBoard.emailVar.changeDiffHtml'),
            'actionsHtml'    => __('plugins.generic.orcidEditorialBoard.emailVar.actionsHtml'),
            'consentUrl'     => __('plugins.generic.orcidEditorialBoard.emailVar.consentUrl'),
            'disputeUrl'     => __('plugins.generic.orcidEditorialBoard.emailVar.disputeUrl'),
            'contactName'    => __('plugins.generic.orcidEditorialBoard.emailVar.contactName'),
            'contactEmail'   => __('plugins.generic.orcidEditorialBoard.emailVar.contactEmail'),
            'footerLine1'    => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine1'),
            'footerLine2'    => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine2'),
            'footerLine3'    => __('plugins.generic.orcidEditorialBoard.emailVar.footerLine3'),
        ]);
    }
}
