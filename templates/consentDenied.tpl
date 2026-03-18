{**
 * templates/consentDenied.tpl
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.displayName"}
<div class="page page_orcid_consent_denied">
	<h1>{translate key="plugins.generic.orcidEditorialBoard.displayName"}</h1>
	{if $orcidAPIError}
		<p>{$orcidAPIError|escape}</p>
	{else}
		<p>{translate key="plugins.generic.orcidEditorialBoard.consentDenied"}</p>
	{/if}
</div>
{include file="frontend/components/footer.tpl"}
