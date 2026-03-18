{**
 * templates/actionLanding.tpl
 *
 * Landing page for ORCID-gated actions (accept / deny invitation, COI declaration).
 * Variables: actionType, memberName, memberOrcid, journalName, orcidUrl, actionDescription
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}

<style>
{literal}
.eb-action-wrap{max-width:560px;margin:50px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.eb-action-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.eb-action-header{padding:28px 32px;background:linear-gradient(135deg,#1e293b,#334155);color:#fff;text-align:center}
.eb-action-header h1{font-size:1.35rem;font-weight:800;margin:0 0 6px}
.eb-action-header p{font-size:.88rem;opacity:.85;margin:0}
.eb-action-body{padding:32px;text-align:center}
.eb-action-orcid-badge{display:inline-flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 16px;border-radius:99px;font-size:.85rem;color:#15803d;font-weight:600;margin-bottom:20px}
.eb-action-orcid-badge svg{width:20px;height:20px}
.eb-action-desc{font-size:.95rem;line-height:1.7;color:#374151;margin:0 0 28px}
.eb-action-btn{display:inline-flex;align-items:center;gap:10px;background:#a6ce39;color:#fff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:1rem;font-weight:700;transition:background .2s,transform .1s}
.eb-action-btn:hover{background:#8fb82e;transform:translateY(-1px)}
.eb-action-btn:active{transform:translateY(0)}
.eb-action-btn svg{width:22px;height:22px}
.eb-action-note{margin-top:24px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:.8rem;color:#6b7280;line-height:1.6;text-align:left}
.eb-action-note strong{color:#374151}
{/literal}
</style>

<div class="page page_editorial_board_action">
	<div class="eb-action-wrap">
		<div class="eb-action-card">
			<div class="eb-action-header">
				<h1>{$journalName|escape}</h1>
				<p>{translate key="plugins.generic.orcidEditorialBoard.action.landing.subtitle"}</p>
			</div>
			<div class="eb-action-body">
				<div class="eb-action-orcid-badge">
					<svg viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
						<path fill="#a6ce39" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z"/>
						<path fill="#fff" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.2 0h41.4c39.3 0 56.3-25.3 56.3-53.5 0-28.2-17-53.6-56.3-53.6h-41.4v107.1zm15.4-93.7h24.1c31.3 0 42.4 19.5 42.4 40.2 0 20.6-11.1 40.1-42.4 40.1h-24.1V92.5zM108.5 67c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z"/>
					</svg>
					{$memberOrcid|escape}
				</div>

				<p class="eb-action-desc">
					{translate key="plugins.generic.orcidEditorialBoard.action.landing.hello" memberName=$memberName|escape}<br>
					{$actionDescription|escape}
				</p>

				<a href="{$orcidUrl|escape}" class="eb-action-btn">
					<svg viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
						<path fill="#fff" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z" opacity=".3"/>
						<path fill="#fff" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.2 0h41.4c39.3 0 56.3-25.3 56.3-53.5 0-28.2-17-53.6-56.3-53.6h-41.4v107.1zm15.4-93.7h24.1c31.3 0 42.4 19.5 42.4 40.2 0 20.6-11.1 40.1-42.4 40.1h-24.1V92.5zM108.5 67c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z"/>
					</svg>
					{translate key="plugins.generic.orcidEditorialBoard.action.landing.signIn"}
				</a>

				<div class="eb-action-note">
					<strong>{translate key="plugins.generic.orcidEditorialBoard.action.landing.whyTitle"}</strong><br>
					{translate key="plugins.generic.orcidEditorialBoard.action.landing.whyBody"}
				</div>
			</div>
		</div>
	</div>
</div>

{include file="frontend/components/footer.tpl"}
