{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.reportFalseClaim.title"}

<style>
.eb-report-container {
	max-width: 640px;
	margin: 40px auto;
	padding: 28px 30px;
	background: #ffffff;
	border-radius: 14px;
	box-shadow: 0 10px 40px rgba(15,23,42,0.14);
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.eb-report-title {
	font-size: 1.4rem;
	font-weight: 700;
	color: #111827;
	margin-bottom: 6px;
}
.eb-report-subtitle {
	font-size: 0.95rem;
	color: #6b7280;
	margin-bottom: 20px;
}
.eb-report-box {
	background: #f9fafb;
	border-radius: 10px;
	border: 1px solid #e5e7eb;
	padding: 14px 16px;
	font-size: 0.9rem;
	color: #374151;
	margin-bottom: 18px;
}
.eb-report-box dt {
	font-weight: 600;
	color: #4b5563;
}
.eb-report-box dd {
	margin: 0 0 8px 0;
}
.eb-report-warning {
	background: #fef3c7;
	border: 1px solid #fcd34d;
	border-radius: 10px;
	padding: 12px 14px;
	font-size: 0.88rem;
	color: #92400e;
	margin-bottom: 22px;
}
.eb-report-button {
	display: inline-block;
	background: #111827;
	color: #ffffff;
	text-decoration: none;
	padding: 11px 22px;
	border-radius: 999px;
	font-size: 0.95rem;
	font-weight: 600;
}
.eb-report-button:hover {
	background: #020617;
}
</style>

<div class="eb-report-container">
	<div class="eb-report-title">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.title"}</div>
	<div class="eb-report-subtitle">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.subtitle"}</div>

	<div class="eb-report-box">
		<dl>
			<dt>{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.journal"}</dt>
			<dd>{$journalName|escape}</dd>
			<dt>{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.member"}</dt>
			<dd>{$member->getFullName()|escape}</dd>
			<dt>{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.orcid"}</dt>
			<dd>{$member->getOrcidId()|escape}</dd>
		</dl>
	</div>

	<div class="eb-report-warning">
		{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.warning"}
	</div>

	<a class="eb-report-button" href="{url page='editorialBoard' op='reportFalseClaim' memberId=$member->getId() sig=$sig confirm=1}">
		{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.continue"}
	</a>
</div>

{include file="frontend/components/footer.tpl"}

