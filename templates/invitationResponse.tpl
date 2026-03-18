{**
 * templates/invitationResponse.tpl
 *
 * Landing page for invitation accept / deny.
 * Variables: responseType ('accepted' | 'denied' | 'denyForm'), memberName, journalName, message
 *           For accepted: coiActionUrl (optional, link to COI declaration)
 *           For denyForm: memberId, denySessionToken
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}

<style>
{literal}
.eb-inv-wrap{max-width:620px;margin:40px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.eb-inv-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.eb-inv-header{padding:28px 32px;color:#fff}
.eb-inv-header--accepted{background:linear-gradient(135deg,#065f46,#059669)}
.eb-inv-header--denied{background:linear-gradient(135deg,#7f1d1d,#991b1b)}
.eb-inv-header--form{background:linear-gradient(135deg,#78350f,#b45309)}
.eb-inv-header h1{font-size:1.4rem;font-weight:800;margin:0 0 4px}
.eb-inv-header p{font-size:.9rem;opacity:.9;margin:0}
.eb-inv-body{padding:28px 32px}
.eb-inv-body p{font-size:.95rem;line-height:1.7;color:#374151;margin:0 0 14px}
.eb-inv-icon{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:50%;margin-bottom:16px}
.eb-inv-icon--ok{background:#dcfce7;color:#16a34a}
.eb-inv-icon--no{background:#fef2f2;color:#dc2626}
.eb-inv-form-group{margin-bottom:18px}
.eb-inv-form-group label{display:block;font-size:.85rem;font-weight:700;color:#334155;margin-bottom:6px}
.eb-inv-textarea{width:100%;min-height:90px;padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-family:inherit;font-size:.9rem;resize:vertical;transition:border-color .2s}
.eb-inv-textarea:focus{outline:none;border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.12)}
.eb-inv-submit{display:inline-flex;align-items:center;gap:6px;background:#dc2626;color:#fff;border:none;padding:12px 26px;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer;transition:background .2s}
.eb-inv-submit:hover{background:#b91c1c}
.eb-inv-note{margin-top:18px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:.82rem;color:#6b7280;line-height:1.6}
{/literal}
</style>

<div class="page page_editorial_board_invitation">
	<div class="eb-inv-wrap">
		<div class="eb-inv-card">
			{if $responseType === 'accepted'}
				<div class="eb-inv-header eb-inv-header--accepted">
					<h1>{translate key="plugins.generic.orcidEditorialBoard.invitation.accepted.title"}</h1>
					<p>{$journalName|escape}</p>
				</div>
				<div class="eb-inv-body">
					<div class="eb-inv-icon eb-inv-icon--ok">
						<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
					</div>
					<p>{$message}</p>
					{if isset($coiActionUrl) && $coiActionUrl}
						<div style="margin:20px 0;text-align:center">
							<a href="{$coiActionUrl|escape}" style="display:inline-block;background:#3182ce;color:#fff;padding:12px 28px;border-radius:10px;font-size:0.95rem;font-weight:700;text-decoration:none;transition:background 0.2s">{translate key="plugins.generic.orcidEditorialBoard.invitation.accepted.coiButton"}</a>
						</div>
					{/if}
					<div class="eb-inv-note">
						{translate key="plugins.generic.orcidEditorialBoard.invitation.accepted.note"}
					</div>
				</div>

			{elseif $responseType === 'denied'}
				<div class="eb-inv-header eb-inv-header--denied">
					<h1>{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.title"}</h1>
					<p>{$journalName|escape}</p>
				</div>
				<div class="eb-inv-body">
					<div class="eb-inv-icon eb-inv-icon--no">
						<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
					</div>
					<p>{$message}</p>
					<div class="eb-inv-note">
						{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.note"}
					</div>
				</div>

			{elseif $responseType === 'denyForm'}
				<div class="eb-inv-header eb-inv-header--form">
					<h1>{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.title"}</h1>
					<p>{$journalName|escape}</p>
				</div>
				<div class="eb-inv-body">
					<p>{translate key="plugins.generic.orcidEditorialBoard.invitation.denyForm.body" memberName=$memberName|escape journalName=$journalName|escape}</p>
					<form method="post" action="{url page="editorialBoard" op="denyInvitationConfirm"}">
						<input type="hidden" name="memberId" value="{$memberId|escape}" />
						<input type="hidden" name="denySessionToken" value="{$denySessionToken|escape}" />
						<div class="eb-inv-form-group">
							<label>{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.reasonLabel"}</label>
							<textarea name="denyReason" class="eb-inv-textarea" placeholder="{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.reasonPlaceholder"}"></textarea>
						</div>
						<button type="submit" class="eb-inv-submit">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
							{translate key="plugins.generic.orcidEditorialBoard.invitation.denied.submit"}
						</button>
					</form>
					<div class="eb-inv-note" style="margin-top:22px">
						{translate key="plugins.generic.orcidEditorialBoard.invitation.denyForm.note"}
					</div>
				</div>
			{/if}
		</div>
	</div>
</div>

{include file="frontend/components/footer.tpl"}
