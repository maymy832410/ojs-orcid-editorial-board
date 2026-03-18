{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.coi.pageTitle"}

<style>
.coi-container {
	max-width: 640px;
	margin: 40px auto;
	padding: 32px;
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.08);
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.coi-title {
	font-size: 1.4rem;
	font-weight: 700;
	color: #1a202c;
	margin-bottom: 8px;
}
.coi-subtitle {
	font-size: 0.95rem;
	color: #718096;
	margin-bottom: 24px;
}
.coi-success {
	background: #f0fff4;
	border: 1px solid #c6f6d5;
	color: #276749;
	padding: 16px 20px;
	border-radius: 8px;
	margin-bottom: 20px;
	font-weight: 600;
}
.coi-already {
	background: #ebf8ff;
	border: 1px solid #bee3f8;
	color: #2b6cb0;
	padding: 16px 20px;
	border-radius: 8px;
	margin-bottom: 20px;
}
.coi-error {
	background: #fff5f5;
	border: 1px solid #fed7d7;
	color: #c53030;
	padding: 12px 16px;
	border-radius: 8px;
	margin-bottom: 16px;
	font-size: 0.9rem;
}
.coi-label {
	display: block;
	font-weight: 600;
	margin-bottom: 8px;
	color: #2d3748;
}
.coi-radio-group {
	margin-bottom: 20px;
}
.coi-radio-group label {
	display: block;
	padding: 10px 14px;
	margin-bottom: 6px;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	cursor: pointer;
	transition: background 0.15s;
}
.coi-radio-group label:hover {
	background: #f7fafc;
}
.coi-radio-group input[type="radio"] {
	margin-right: 8px;
}
.coi-textarea {
	width: 100%;
	min-height: 100px;
	padding: 12px;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	font-size: 0.95rem;
	resize: vertical;
	margin-bottom: 20px;
}
.coi-textarea:focus {
	outline: none;
	border-color: #4299e1;
	box-shadow: 0 0 0 3px rgba(66,153,225,0.15);
}
.coi-submit {
	display: inline-block;
	background: #3182ce;
	color: #fff;
	padding: 12px 28px;
	border: none;
	border-radius: 8px;
	font-size: 1rem;
	font-weight: 600;
	cursor: pointer;
	transition: background 0.15s;
}
.coi-submit:hover {
	background: #2b6cb0;
}
.coi-info {
	font-size: 0.85rem;
	color: #a0aec0;
	margin-top: 20px;
}
</style>

<div class="coi-container">
	<div class="coi-title">{translate key="plugins.generic.orcidEditorialBoard.coi.formTitle"}</div>
	<div class="coi-subtitle">{$journalName|escape} &mdash; {$member->getFullName()|escape}</div>

	{if isset($coiSuccess) && $coiSuccess}
		<div class="coi-success">{translate key="plugins.generic.orcidEditorialBoard.coi.successMessage"}</div>
		<p class="coi-info">{translate key="plugins.generic.orcidEditorialBoard.coi.successDetail"}</p>
	{elseif isset($alreadyDeclared) && $alreadyDeclared}
		<div class="coi-already">{translate key="plugins.generic.orcidEditorialBoard.coi.alreadyDeclared"}</div>
		<p><strong>{translate key="plugins.generic.orcidEditorialBoard.coi.declaredOn"}:</strong> {$member->getCoiDeclaredAt()|escape}</p>
		<p><strong>{translate key="plugins.generic.orcidEditorialBoard.coi.statement"}:</strong> {$member->getCoiText()|escape}</p>
	{else}
		{if isset($coiError)}
			<div class="coi-error">{$coiError|escape}</div>
		{/if}
		<form method="post" action="{url page="editorialBoard" op="coiDeclare"}">
			<input type="hidden" name="memberId" value="{$memberId|escape}" />
			<input type="hidden" name="sessionToken" value="{$sessionToken|escape}" />

			<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:18px;font-size:0.92rem;line-height:1.65;color:#334155;">
				<strong>{translate key="plugins.generic.orcidEditorialBoard.coi.disclosure.title"}</strong><br>
				{translate key="plugins.generic.orcidEditorialBoard.coi.disclosure.intro"}
			</div>

			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section1.title"}</span>
			<div class="coi-radio-group">
				<label><input type="checkbox" name="financialOptions[]" value="support" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section1.opt.support"}</label>
				<label><input type="checkbox" name="financialOptions[]" value="honoraria" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section1.opt.honoraria"}</label>
				<label><input type="checkbox" name="financialOptions[]" value="other" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section1.opt.other"}</label>
			</div>
			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section1.details"}</span>
			<textarea name="financialDetails" class="coi-textarea" placeholder="{translate key='plugins.generic.orcidEditorialBoard.coi.detailsPlaceholder'}"></textarea>

			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section2.title"}</span>
			<div class="coi-radio-group">
				<label><input type="checkbox" name="personalOptions[]" value="professional_relationships" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section2.opt.professional"}</label>
				<label><input type="checkbox" name="personalOptions[]" value="personal_relationships" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section2.opt.personal"}</label>
				<label><input type="checkbox" name="personalOptions[]" value="advisory_roles" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section2.opt.advisory"}</label>
				<label><input type="checkbox" name="personalOptions[]" value="other" /> {translate key="plugins.generic.orcidEditorialBoard.coi.section2.opt.other"}</label>
			</div>
			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section2.details"}</span>
			<textarea name="personalDetails" class="coi-textarea" placeholder="{translate key='plugins.generic.orcidEditorialBoard.coi.detailsPlaceholder'}"></textarea>

			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section3.title"}</span>
			<div class="coi-radio-group">
				<label><input type="radio" name="orgFinancialInterest" value="yes" /> {translate key="common.yes"}</label>
				<label><input type="radio" name="orgFinancialInterest" value="no" checked /> {translate key="common.no"}</label>
			</div>
			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section3.details"}</span>
			<textarea name="orgFinancialDetails" class="coi-textarea" placeholder="{translate key='plugins.generic.orcidEditorialBoard.coi.detailsPlaceholder'}"></textarea>

			<span class="coi-label">{translate key="plugins.generic.orcidEditorialBoard.coi.section4.title"}</span>
			<textarea name="otherConflicts" class="coi-textarea" placeholder="{translate key='plugins.generic.orcidEditorialBoard.coi.detailsPlaceholder'}"></textarea>

			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin:18px 0 18px 0;">
				<label style="display:flex;gap:10px;align-items:flex-start;cursor:pointer;color:#1f2937;line-height:1.6;">
					<input type="checkbox" name="declarationAccepted" value="1" style="margin-top:3px;" />
					<span>
						<strong>{translate key="plugins.generic.orcidEditorialBoard.coi.section5.title"}</strong><br>
						{translate key="plugins.generic.orcidEditorialBoard.coi.section5.text"}<br>
						<span style="color:#64748b;font-size:0.88rem;">{translate key="plugins.generic.orcidEditorialBoard.coi.section5.signedBy"}: {$member->getFullName()|escape}</span>
					</span>
				</label>
			</div>

			<button type="submit" class="coi-submit">{translate key="plugins.generic.orcidEditorialBoard.coi.submit"}</button>
		</form>
	{/if}
</div>

{include file="frontend/components/footer.tpl"}
