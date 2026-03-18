{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.verify.pageTitle"}

<style>
.verify-report {ldelim}
	max-width: 700px;
	margin: 40px auto;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
{rdelim}
.verify-banner {ldelim}
	padding: 24px 32px;
	border-radius: 12px;
	text-align: center;
	margin-bottom: 28px;
{rdelim}
.verify-banner.verified {ldelim}
	background: linear-gradient(135deg, #059669, #10b981);
	color: #fff;
{rdelim}
.verify-banner.revoked {ldelim}
	background: linear-gradient(135deg, #dc2626, #ef4444);
	color: #fff;
{rdelim}
.verify-banner.not_verified {ldelim}
	background: #f1f5f9;
	color: #475569;
	border: 1px solid #e2e8f0;
{rdelim}
.verify-banner.error {ldelim}
	background: #fef3c7;
	color: #92400e;
	border: 1px solid #fcd34d;
{rdelim}
.verify-banner h1 {ldelim}
	margin: 0 0 4px;
	font-size: 1.6rem;
{rdelim}
.verify-banner .timestamp {ldelim}
	opacity: 0.85;
	font-size: 0.88rem;
{rdelim}
.verify-section {ldelim}
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	padding: 20px 24px;
	margin-bottom: 18px;
{rdelim}
.verify-section h3 {ldelim}
	margin: 0 0 12px;
	font-size: 1rem;
	color: #334155;
	border-bottom: 1px solid #f1f5f9;
	padding-bottom: 8px;
{rdelim}
.verify-row {ldelim}
	display: flex;
	justify-content: space-between;
	padding: 6px 0;
	font-size: 0.92rem;
{rdelim}
.verify-row .label {ldelim}
	color: #64748b;
	font-weight: 500;
{rdelim}
.verify-row .value {ldelim}
	color: #1e293b;
	text-align: right;
	max-width: 60%;
	word-break: break-word;
{rdelim}
.match-badge {ldelim}
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 0.78rem;
	font-weight: 600;
	margin-left: 6px;
{rdelim}
.match-badge.match {ldelim}
	background: #dcfce7;
	color: #166534;
{rdelim}
.match-badge.mismatch {ldelim}
	background: #fee2e2;
	color: #991b1b;
{rdelim}
.verify-explain {ldelim}
	background: #f0f9ff;
	border: 1px solid #bae6fd;
	border-radius: 10px;
	padding: 18px 22px;
	font-size: 0.88rem;
	line-height: 1.65;
	color: #1e40af;
	margin-top: 24px;
{rdelim}
.verify-explain strong {ldelim}
	display: block;
	margin-bottom: 6px;
	font-size: 0.95rem;
{rdelim}
.orcid-link {ldelim}
	display: inline-flex;
	align-items: center;
	gap: 6px;
	color: #a6ce39;
	text-decoration: none;
	font-weight: 600;
{rdelim}
.orcid-link:hover {ldelim}
	text-decoration: underline;
{rdelim}
</style>

<div class="verify-report">

	{if $verificationStatus == 'verified'}
		<div class="verify-banner verified">
			<h1>&#10003; VERIFIED</h1>
			<div class="timestamp">{translate key="plugins.generic.orcidEditorialBoard.verify.liveCheck"}</div>
		</div>

		<div class="verify-section">
			<h3>{translate key="plugins.generic.orcidEditorialBoard.verify.consentDetails"}</h3>
			<div class="verify-row">
				<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.consentId"}</span>
				<span class="value"><code>{$consentFingerprint|escape}</code></span>
			</div>
			<div class="verify-row">
				<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.consentDate"}</span>
				<span class="value">{$consentDate|escape}</span>
			</div>
		</div>

		<div class="verify-section">
			<h3>{translate key="plugins.generic.orcidEditorialBoard.verify.orcidData"}</h3>
			<div class="verify-row">
				<span class="label">ORCID iD</span>
				<span class="value">
					<a href="https://orcid.org/{$orcidBare|escape}" target="_blank" class="orcid-link">
						<img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" alt="ORCID" width="16" height="16">
						{$orcidBare|escape}
					</a>
				</span>
			</div>
			<div class="verify-row">
				<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.orcidName"}</span>
				<span class="value">{$orcidName|escape|default:"N/A"}</span>
			</div>
			{if $orcidAffiliations && count($orcidAffiliations) > 0}
				<div class="verify-row">
					<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.orcidAffiliations"}</span>
					<span class="value">{', '|implode:$orcidAffiliations}</span>
				</div>
			{/if}
		</div>

		<div class="verify-section">
			<h3>{translate key="plugins.generic.orcidEditorialBoard.verify.matchComparison"}</h3>
			<div class="verify-row">
				<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.journalName"}</span>
				<span class="value">
					{$member->getFullName()|escape}
					{if $orcidName && $isNameMatch === true}
						<span class="match-badge match">{translate key="plugins.generic.orcidEditorialBoard.verify.match"}</span>
					{elseif $orcidName && $isNameMatch === false}
						<span class="match-badge mismatch">{translate key="plugins.generic.orcidEditorialBoard.verify.mismatch"}</span>
					{/if}
				</span>
			</div>
			{if $orcidName}
				<div class="verify-row">
					<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.orcidRegisteredName"}</span>
					<span class="value">{$orcidName|escape}</span>
				</div>
			{/if}
			<div class="verify-row">
				<span class="label">{translate key="plugins.generic.orcidEditorialBoard.verify.journalOrcid"}</span>
				<span class="value">
					{$member->getOrcidId()|escape}
					{if $verificationStatus == 'verified'}
						<span class="match-badge match">{translate key="plugins.generic.orcidEditorialBoard.verify.match"}</span>
					{/if}
				</span>
			</div>
		</div>

		<div class="verify-explain">
			<strong>{translate key="plugins.generic.orcidEditorialBoard.verify.explainTitle"}</strong>
			{translate key="plugins.generic.orcidEditorialBoard.verify.explainText"}
		</div>

	{elseif $verificationStatus == 'revoked'}
		<div class="verify-banner revoked">
			<h1>&#10007; {translate key="plugins.generic.orcidEditorialBoard.verify.revoked"}</h1>
		</div>
		<div class="verify-section">
			<p>{translate key="plugins.generic.orcidEditorialBoard.verify.revokedExplain"}</p>
		</div>

	{elseif $verificationStatus == 'not_verified'}
		<div class="verify-banner not_verified">
			<h1>{translate key="plugins.generic.orcidEditorialBoard.verify.notYetVerified"}</h1>
		</div>
		<div class="verify-section">
			<p>{translate key="plugins.generic.orcidEditorialBoard.verify.notYetExplain"}</p>
		</div>

	{else}
		<div class="verify-banner error">
			<h1>{translate key="plugins.generic.orcidEditorialBoard.verify.errorTitle"}</h1>
		</div>
		<div class="verify-section">
			<p>{translate key="plugins.generic.orcidEditorialBoard.verify.errorExplain"}</p>
		</div>
	{/if}

	<p style="text-align:center;margin-top:24px;">
		<a href="{url page='editorialBoard'}" style="color:#3b82f6;text-decoration:none;font-weight:500;">&larr; {translate key="plugins.generic.orcidEditorialBoard.verify.backToBoard"}</a>
	</p>
</div>

{include file="frontend/components/footer.tpl"}
