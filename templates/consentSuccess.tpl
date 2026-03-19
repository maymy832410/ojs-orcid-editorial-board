{**
 * templates/consentSuccess.tpl
 *
 * Premium success page shown after ORCID verification completes.
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}
<div class="page page_orcid_consent_success" style="max-width:640px;margin:40px auto;padding:0 20px;">
	<div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">

		{* ── Header gradient ── *}
		<div style="background:linear-gradient(135deg,#166534,#16a34a);padding:28px 30px;text-align:center;">
			<div style="margin-bottom:14px;">
				<svg viewBox="0 0 64 64" width="56" height="56" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="32" cy="32" r="30" fill="rgba(255,255,255,.18)" stroke="rgba(255,255,255,.35)" stroke-width="2"/>
					<path d="M20 33l8 8 16-16" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>
			<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#ffffff;font-size:22px;font-weight:700;line-height:1.3;">
				{translate key="plugins.generic.orcidEditorialBoard.displayName"}
			</div>
		</div>

		{* ── Body ── *}
		<div style="padding:30px 30px 10px;">
			<div style="text-align:center;margin-bottom:18px;">
				<span style="display:inline-block;background:#dcfce7;color:#166534;font-size:14px;font-weight:700;padding:6px 16px;border-radius:20px;border:1px solid #86efac;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
					✅ Verification Complete
				</span>
			</div>
			<p style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#374151;font-size:15px;line-height:1.75;text-align:center;margin:0 0 20px;">
				{translate key="plugins.generic.orcidEditorialBoard.consentSuccess"}
			</p>
		</div>

		{* ── ORCID badge ── *}
		<div style="padding:0 30px 26px;text-align:center;">
			<div style="display:inline-flex;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 22px;">
				<svg viewBox="0 0 256 256" width="28" height="28" xmlns="http://www.w3.org/2000/svg">
					<path d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z" fill="#A6CE39"/>
					<path fill="#FFF" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.5-107.1h41.6c39.6 0 57 28.3 57 53.6 0 27.5-21.5 53.5-56.8 53.5h-41.8V79.1zm15.4 93.3h24.5c34.9 0 42.9-26.5 42.9-39.7C191.6 111 176 92.9 150.4 92.9h-26.2v79.5zM108.9 55.6c0 5.7-4.5 10.3-10.2 10.3s-10.2-4.6-10.2-10.3c0-5.7 4.5-10.3 10.2-10.3s10.2 4.6 10.2 10.3z"/>
				</svg>
				<div style="text-align:left;">
					<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#166534;font-size:13px;font-weight:700;">ORCID Authenticated</div>
					<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#15803d;font-size:12px;">Your identity has been cryptographically verified</div>
				</div>
			</div>
		</div>

		{* ── Info box ── *}
		<div style="padding:0 30px 26px;">
			<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;">
				<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#374151;font-size:13px;line-height:1.7;">
					<strong>What happens next?</strong> Your editorial board badge now shows a verified status. The journal may contact you separately for a conflict-of-interest declaration. You can safely close this page.
				</div>
			</div>
		</div>

		{* ── Footer ── *}
		<div style="padding:16px 30px;background:#f9fafb;border-top:1px solid #e5e7eb;">
			<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#9ca3af;font-size:11px;text-align:center;">
				Secured by ORCID authentication · Editorial Board Verification System
			</div>
		</div>

	</div>
</div>
{include file="frontend/components/footer.tpl"}
