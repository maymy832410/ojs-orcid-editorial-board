{**
 * templates/approveSuccess.tpl
 *
 * Success page after a member approves profile changes.
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}
<div class="page page_orcid_approve_success" style="max-width:640px;margin:40px auto;padding:0 20px;">
	<div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
		{if $alreadyApproved}
			<div style="background:linear-gradient(135deg,#1e3a5f,#1e40af);padding:22px 26px;">
				<div style="color:#ffffff;font-size:20px;font-weight:700;">{translate key="plugins.generic.orcidEditorialBoard.displayName"}</div>
			</div>
			<div style="padding:26px;">
				<div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:12px;">ℹ️ No Action Needed</div>
				<p style="color:#374151;font-size:14px;line-height:1.7;">
					{translate key="plugins.generic.orcidEditorialBoard.approveEdit.alreadyApproved"}
				</p>
			</div>
		{else}
			<div style="background:linear-gradient(135deg,#166534,#16a34a);padding:22px 26px;">
				<div style="color:#ffffff;font-size:20px;font-weight:700;">{translate key="plugins.generic.orcidEditorialBoard.displayName"}</div>
			</div>
			<div style="padding:26px;">
				<div style="font-size:18px;font-weight:700;color:#166534;margin-bottom:12px;">✅ Changes Approved</div>
				<p style="color:#374151;font-size:14px;line-height:1.7;">
					{translate key="plugins.generic.orcidEditorialBoard.approveEdit.success"}
				</p>
			</div>
		{/if}
	</div>
</div>
{include file="frontend/components/footer.tpl"}
