{**
 * Search results list for wizard — premium card layout.
 * Expects $results, $meta
 *}

{if $results|@count > 0}
	<div class="eb-sr-list">
		{foreach from=$results item=result}
			<div class="eb-sr-card">
				<div class="eb-sr-left">
					<div class="eb-sr-avatar">
						{assign var="srInit" value=$result.display_name|substr:0:1|upper}
						<span>{$srInit}</span>
					</div>
				</div>
				<div class="eb-sr-body">
					<div class="eb-sr-title-row">
						<span class="eb-sr-name">{$result.display_name|escape}</span>
					</div>
					{if $result.institution}
						<div class="eb-sr-inst">
							<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#64748b" stroke-width="2"><path d="M2 22h20"/><path d="M6 18V8l6-5 6 5v10"/><path d="M10 22v-4h4v4"/></svg>
							{$result.institution|escape}
							{if $result.country}<span class="eb-sr-country">· {$result.country|escape}</span>{/if}
						</div>
					{/if}
					<div class="eb-sr-metrics">
						{if $result.h_index !== null}
							<span class="eb-sr-m eb-sr-m--hi" title="{translate key="plugins.generic.orcidEditorialBoard.openalex.hIndex"}">
								<strong>h</strong>{$result.h_index|escape}
							</span>
						{/if}
						<span class="eb-sr-m" title="{translate key="plugins.generic.orcidEditorialBoard.openalex.works"}">
							<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
							{$result.works_count|escape}
						</span>
					</div>

					{* ── Collapsible extra details ── *}
					{assign var="hasExtras" value=false}
					{if $result.orcid || $result.cited_by_count > 0 || ($result.topics|@count > 0)}{assign var="hasExtras" value=true}{/if}
					{if $hasExtras}
						<button class="eb-sr-toggle js-eb-sr-toggle" type="button">
							<svg class="eb-sr-toggle-icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
							<span class="eb-sr-toggle-text">{translate key="plugins.generic.orcidEditorialBoard.wizard.showMore"}</span>
						</button>
						<div class="eb-sr-extras" style="display:none;">
							{if $result.orcid}
								<div class="eb-sr-extra-row">
									<a class="eb-sr-orcid" href="{$result.orcid|escape}" target="_blank" rel="noopener" title="ORCID">
										<img src="https://info.orcid.org/wp-content/uploads/2019/11/orcid_16x16.png" alt="ORCID" width="14" height="14" />
										{$result.orcid|regex_replace:"/.*\//":""|escape}
									</a>
								</div>
							{/if}
							{if $result.cited_by_count > 0}
								<div class="eb-sr-extra-row">
									<span class="eb-sr-m" title="{translate key="plugins.generic.orcidEditorialBoard.openalex.citedBy"}">
										<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
										{$result.cited_by_count|escape} cited
									</span>
								</div>
							{/if}
							{if $result.topics|@count > 0}
								<div class="eb-sr-topics">
									{foreach from=$result.topics item=topic name=topicloop}
										{if $smarty.foreach.topicloop.index < 5}
											<span class="eb-sr-topic">{$topic|escape}</span>
										{/if}
									{/foreach}
									{if $result.topics|@count > 5}
										<span class="eb-sr-topic eb-sr-topic--more">+{math equation="t-5" t=$result.topics|@count}</span>
									{/if}
								</div>
							{/if}
						</div>
					{/if}
				</div>
				<div class="eb-sr-action">
					<button class="eb-sr-add js-eb-add" data-openalex-id="{$result.openalexId|escape}" title="{translate key="plugins.generic.orcidEditorialBoard.wizard.addToStaged"}">+ Add</button>
				</div>
			</div>
		{/foreach}
	</div>

	{if $meta}
		<div class="eb-sr-pager">
			{if $meta.page > 1}
				<button class="eb-sr-pager-btn js-eb-search-page" data-page="{$meta.page-1}">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
					{translate key="plugins.generic.orcidEditorialBoard.wizard.previous"}
				</button>
			{else}
				<span></span>
			{/if}
			<span class="eb-sr-pager-info">Page {$meta.page} &middot; {$meta.count} results</span>
			{if ($meta.page * $meta.per_page) < $meta.count}
				<button class="eb-sr-pager-btn js-eb-search-page" data-page="{$meta.page+1}">
					{translate key="plugins.generic.orcidEditorialBoard.wizard.next"}
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
				</button>
			{else}
				<span></span>
			{/if}
		</div>
	{/if}
{else}
	<div class="eb-sr-empty">
		<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#cbd5e1" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
		<p>{translate key="plugins.generic.orcidEditorialBoard.openalex.noResults"}</p>
	</div>
{/if}

<style>
{literal}
.eb-sr-list { display: flex; flex-direction: column; gap: 8px; padding-right: 4px; }
.eb-sr-card {
	display: flex; align-items: flex-start; gap: 14px;
	padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 12px;
	background: #fff; transition: border-color 0.15s, box-shadow 0.15s;
}
.eb-sr-card:hover { border-color: #c7d2fe; box-shadow: 0 2px 10px rgba(99,102,241,0.08); }
.eb-sr-avatar {
	width: 42px; height: 42px; border-radius: 50%;
	background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
	display: flex; align-items: center; justify-content: center;
	font-weight: 700; font-size: 1rem; flex-shrink: 0;
}
.eb-sr-body { flex: 1; min-width: 0; }
.eb-sr-title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.eb-sr-name { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
.eb-sr-orcid {
	display: inline-flex; align-items: center; gap: 3px;
	font-size: 0.78rem; color: #a6ce39; text-decoration: none;
}
.eb-sr-inst { font-size: 0.82rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.eb-sr-country { color: #94a3b8; }
.eb-sr-metrics {
	display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;
}
.eb-sr-m {
	display: inline-flex; align-items: center; gap: 3px;
	background: #f0f9ff; color: #0369a1; padding: 2px 9px;
	border-radius: 8px; font-size: 0.78rem; font-weight: 600;
}
.eb-sr-m--hi { background: #fef3c7; color: #92400e; }
.eb-sr-topics { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
.eb-sr-topic {
	background: #eef2ff; color: #3730a3; padding: 2px 8px;
	border-radius: 8px; font-size: 0.72rem; font-weight: 600;
}
.eb-sr-topic--more { background: #f1f5f9; color: #64748b; }
.eb-sr-toggle {
	display: inline-flex; align-items: center; gap: 4px;
	background: none; border: none; cursor: pointer;
	font-size: 0.72rem; font-weight: 600; color: #6366f1;
	padding: 4px 0 0; margin: 0; transition: color 0.15s;
}
.eb-sr-toggle:hover { color: #4338ca; }
.eb-sr-toggle-icon { transition: transform 0.2s; }
.eb-sr-toggle.active .eb-sr-toggle-icon { transform: rotate(180deg); }
.eb-sr-extras {
	margin-top: 6px;
	padding-top: 6px;
	border-top: 1px dashed #e2e8f0;
	animation: ebSrSlide 0.2s ease;
}
.eb-sr-extra-row { margin-bottom: 4px; }
@keyframes ebSrSlide {
	from { opacity: 0; transform: translateY(-4px); }
	to { opacity: 1; transform: translateY(0); }
}
.eb-sr-action { flex-shrink: 0; padding-top: 2px; }
.eb-sr-add {
	border: 2px solid #e0e7ff; border-radius: 10px; background: #fff; color: #4f46e5;
	display: inline-flex; align-items: center; justify-content: center; gap: 4px;
	padding: 6px 14px; font-size: 0.82rem; font-weight: 700;
	cursor: pointer; transition: all 0.15s; white-space: nowrap;
}
.eb-sr-add:hover { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.eb-sr-add:disabled { background: #e0e7ff; color: #a5b4fc; border-color: #e0e7ff; cursor: wait; }

.eb-sr-pager {
	display: flex; justify-content: space-between; align-items: center;
	padding: 12px 0 0; margin-top: 8px; border-top: 1px solid #f1f5f9;
}
.eb-sr-pager-btn {
	display: inline-flex; align-items: center; gap: 4px;
	background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
	padding: 6px 14px; font-size: 0.82rem; font-weight: 600;
	color: #334155; cursor: pointer; transition: all 0.15s;
}
.eb-sr-pager-btn:hover { background: #e0e7ff; border-color: #c7d2fe; color: #3730a3; }
.eb-sr-pager-info { font-size: 0.82rem; color: #64748b; font-weight: 500; }
.eb-sr-empty { text-align: center; padding: 30px 20px; color: #94a3b8; }
.eb-sr-empty p { margin: 8px 0 0; font-size: 0.9rem; }
{/literal}
</style>
