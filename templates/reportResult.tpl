{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.reportFalseClaim.title"}

<style>
.eb-report-result {
	max-width: 620px;
	margin: 40px auto;
	padding: 26px 28px;
	background:#ffffff;
	border-radius:14px;
	box-shadow:0 10px 40px rgba(15,23,42,0.14);
	font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
}
.eb-report-result h1 {
	font-size:1.4rem;
	margin:0 0 10px;
}
.eb-report-status-ok { color:#166534; }
.eb-report-status-error { color:#b91c1c; }
.eb-report-status-warn { color:#92400e; }
.eb-report-text { font-size:0.95rem;color:#374151;line-height:1.6; }
.eb-report-meta { margin-top:16px;font-size:0.85rem;color:#6b7280; }
</style>

<div class="eb-report-result">
	{if $reportStatus == 'matched'}
		<h1 class="eb-report-status-ok">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.matchedTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.matchedBody" memberName=$member->getFullName()|escape journalName=$journalName|escape}
		</p>
		<form method="post" action="{url page='editorialBoard' op='callback'}">
			<input type="hidden" name="memberId" value="{$member->getId()|escape}" />
			<input type="hidden" name="sessionCheck" value="{$sessionCheck|escape}" />
			<button type="submit" name="confirmRemove" value="1" class="eb-report-button">
				{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.confirmRemove"}
			</button>
		</form>
	{elseif $reportStatus == 'orcid_mismatch'}
		<h1 class="eb-report-status-error">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.mismatchTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.mismatchBody"}
		</p>
	{elseif $reportStatus == 'expired'}
		<h1 class="eb-report-status-warn">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.expiredTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.expiredBody"}
		</p>
	{elseif $reportStatus == 'denied'}
		<h1 class="eb-report-status-warn">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.deniedTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.deniedBody"}
		</p>
	{elseif $reportStatus == 'removed'}
		<h1 class="eb-report-status-ok">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.removedTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.removedBody"}
		</p>
	{else}
		<h1 class="eb-report-status-error">{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.errorTitle"}</h1>
		<p class="eb-report-text">
			{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.errorBody"}
		</p>
	{/if}

	{if isset($member)}
		<div class="eb-report-meta">
			<strong>{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.member"}:</strong>
			{$member->getFullName()|escape}
		</div>
	{/if}
</div>

{include file="frontend/components/footer.tpl"}

