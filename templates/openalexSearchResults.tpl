{**
 * Modal content for OpenAlex author search results.
 * Expects variables: $member, $results
 *}

<div class="pkp_modal_panel_content">
	<h3>{translate key="plugins.generic.orcidEditorialBoard.openalex.searchTitle"}</h3>
	<p>{translate key="plugins.generic.orcidEditorialBoard.openalex.searchInstructions" name=$member->getFullName()|escape}</p>

	{if $results|@count > 0}
		<ul class="pkpList">
			{foreach from=$results item=result}
				<li class="pkpListItem">
					<div class="openalex-result">
						<div class="openalex-result__header">
							<strong>{$result.display_name|escape}</strong>
							{if $result.orcid}
								<span class="openalex-result__orcid"><a href="{$result.orcid|escape}" target="_blank" rel="noopener">ORCID</a></span>
							{/if}
						</div>
						{if $result.institution}
							<div class="openalex-result__line">
								<span class="label">{translate key="plugins.generic.orcidEditorialBoard.openalex.institution"}:</span>
								<span>{$result.institution|escape}</span>
								{if $result.country}
									<span class="openalex-result__country">({$result.country|escape})</span>
								{/if}
							</div>
						{/if}
						<div class="openalex-result__stats">
							<span>{translate key="plugins.generic.orcidEditorialBoard.openalex.works"}: {$result.works_count|escape}</span>
							<span>{translate key="plugins.generic.orcidEditorialBoard.openalex.citedBy"}: {$result.cited_by_count|escape}</span>
							{if $result.h_index !== null}
								<span>{translate key="plugins.generic.orcidEditorialBoard.openalex.hIndex"}: {$result.h_index|escape}</span>
							{/if}
						</div>
						{if $result.topics|@count > 0}
							<div class="openalex-result__topics">
								{foreach from=$result.topics item=topic}
									<span class="openalex-topic-pill">{$topic|escape}</span>
								{/foreach}
							</div>
						{/if}
						<div class="openalex-result__actions">
							<a href="#" class="pkp_button pkp_button_primary js-select-openalex"
							   data-openalex-id="{$result.openalexId|escape}"
							   data-member-id="{$member->getId()|escape}">
								{translate key="plugins.generic.orcidEditorialBoard.openalex.select"}
							</a>
						</div>
					</div>
				</li>
			{/foreach}
		</ul>
	{else}
		<p>{translate key="plugins.generic.orcidEditorialBoard.openalex.noResults"}</p>
	{/if}
</div>

<style>
{literal}
.openalex-result {padding:12px 10px; border-bottom:1px solid #e2e8f0;}
.openalex-result__header {display:flex; align-items:center; gap:8px; font-size:1rem;}
.openalex-result__orcid a {font-size:0.85rem; color:#4a5568; text-decoration:none;}
.openalex-result__stats {display:flex; gap:12px; font-size:0.85rem; color:#4a5568; margin:6px 0;}
.openalex-result__topics {display:flex; gap:6px; flex-wrap:wrap; margin:6px 0;}
.openalex-topic-pill {background:#edf2f7; color:#2d3748; border-radius:12px; padding:4px 10px; font-size:0.8rem;}
.openalex-result__actions {margin-top:8px;}
.openalex-result__line .label {font-weight:600; margin-right:4px;}
.openalex-result__country {color:#4a5568; margin-left:6px;}
{/literal}
</style>

<script type="text/javascript">
{literal}
$(function() {
	$('.js-select-openalex').on('click', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var openalexId = $btn.data('openalex-id');
		var memberId = $btn.data('member-id');
		var postUrl = '{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="selectOpenAlexAuthor" memberId=$member->getId() escape=false}{literal}';
		$btn.addClass('disabled');
		$.post(postUrl, {openalexId: openalexId}, function (jsonData) {
			if (jsonData && jsonData.status) {
				// Close modal
				$('.pkp_modal_panel').trigger('close');
				// Refresh the first grid on the page (the editorial board grid)
				var $grid = $('.pkp_controllers_grid').first();
				if ($grid.length) {
					$grid.trigger('dataChanged');
				}
			} else {
				alert((jsonData && jsonData.content) ? jsonData.content : 'Error selecting author.');
				$btn.removeClass('disabled');
			}
		}, 'json').fail(function () {
			alert('Error selecting author.');
			$btn.removeClass('disabled');
		});
	});
});
{/literal}
</script>
