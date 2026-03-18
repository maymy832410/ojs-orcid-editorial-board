<script>
	$(function() {ldelim}
		$('#orcidEditorialBoardSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="orcidEditorialBoardSettingsForm" method="post" action="{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="orcidSettingsNotification"}

	{fbvFormArea id="orcidSetupInstructions" title="plugins.generic.orcidEditorialBoard.settings.instructionsTitle"}
		{fbvFormSection}
			<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px 20px;margin-bottom:16px;font-size:0.92rem;line-height:1.6;">
				<strong>{translate key="plugins.generic.orcidEditorialBoard.settings.instructionsHeading"}</strong>
				<ol style="margin:8px 0 0 18px;padding:0;">
					<li>{translate key="plugins.generic.orcidEditorialBoard.settings.step1"}</li>
					<li>{translate key="plugins.generic.orcidEditorialBoard.settings.step2"}</li>
					<li>{translate key="plugins.generic.orcidEditorialBoard.settings.step3"}</li>
					<li>{translate key="plugins.generic.orcidEditorialBoard.settings.step4"} <br><code style="background:#e2e8f0;padding:2px 6px;border-radius:4px;">{$redirectUri|escape}</code></li>
					<li>{translate key="plugins.generic.orcidEditorialBoard.settings.step5"}</li>
				</ol>
			</div>
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="orcidApiCredentials" title="plugins.generic.orcidEditorialBoard.settings.credentialsTitle"}
		{fbvFormSection title="plugins.generic.orcidEditorialBoard.settings.environment"}
			<input type="hidden" name="orcidApiEnvironment" value="production" />
			<div style="background:#dcfce7;border:1px solid #86efac;border-radius:6px;padding:8px 12px;font-size:0.88rem;color:#166534;font-weight:600;">&#10003; Production (orcid.org)</div>
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.orcidEditorialBoard.settings.clientId" required=true}
			{fbvElement type="text" id="orcidClientId" value=$orcidClientId required=true maxlength="40"}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.orcidEditorialBoard.settings.clientSecret" required=true}
			{fbvElement type="password" id="orcidClientSecret" value=$orcidClientSecret required=true maxlength="64"}
			{if $maskedSecret}
				<p style="font-size:0.85rem;color:#64748b;margin-top:4px;">{translate key="plugins.generic.orcidEditorialBoard.settings.currentSecret"}: <code>{$maskedSecret|escape}</code></p>
			{/if}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.orcidEditorialBoard.settings.redirectUri"}
			<div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;font-family:monospace;font-size:0.88rem;word-break:break-all;">
				{$redirectUri|escape}
			</div>
			<p style="font-size:0.82rem;color:#64748b;margin-top:4px;">{translate key="plugins.generic.orcidEditorialBoard.settings.redirectUriHelp"}</p>
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
