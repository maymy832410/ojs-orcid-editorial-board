{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}

<style>
{literal}
.eb-error-wrap{max-width:520px;margin:60px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.eb-error-card{background:#fff;border:1px solid #fecaca;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.eb-error-header{padding:24px 32px;background:linear-gradient(135deg,#7f1d1d,#991b1b);color:#fff;text-align:center}
.eb-error-header h1{font-size:1.25rem;font-weight:800;margin:0}
.eb-error-body{padding:32px;text-align:center}
.eb-error-icon{display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#fef2f2;color:#dc2626;margin-bottom:18px}
.eb-error-msg{font-size:.95rem;line-height:1.7;color:#374151;margin:0 0 24px}
.eb-error-back{display:inline-block;background:#e5e7eb;color:#374151;text-decoration:none;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;transition:background .2s}
.eb-error-back:hover{background:#d1d5db}
{/literal}
</style>

<div class="page page_editorial_board_error">
	<div class="eb-error-wrap">
		<div class="eb-error-card">
			<div class="eb-error-header">
				<h1>{translate key="common.error"}</h1>
			</div>
			<div class="eb-error-body">
				<div class="eb-error-icon">
					<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
				</div>
				<p class="eb-error-msg">{$errorMessage|escape}</p>
				<a href="{url page="index"}" class="eb-error-back">&larr; {translate key="navigation.home"}</a>
			</div>
		</div>
	</div>
</div>

{include file="frontend/components/footer.tpl"}
