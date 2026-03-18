{**
 * Staged editors — premium card layout with visible email.
 * Variables: staged (array keyed by openalexId), countryOptions, roleOptions
 *}

{if $staged|@count > 0}
	{foreach from=$staged item=item}
		<div class="eb-staged-item" data-id="{$item.openalexId|escape}">

			{* ── Card header: avatar + name + badges + actions ── *}
			<div class="eb-staged-header">
				<svg class="eb-staged-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
				<div class="eb-staged-avatar">{$item.display_name|substr:0:1|upper}</div>
				<div class="eb-staged-header-info">
					<div class="eb-staged-name">{$item.display_name|escape}</div>
					<div class="eb-staged-meta">
						{if $item.role || $item.affiliation}
							<span class="eb-staged-meta-text">
								{if $item.role}{$item.role|escape}{/if}
								{if $item.affiliation}{if $item.role} · {/if}{$item.affiliation|escape}{/if}
							</span>
						{/if}
						{if $item.h_index !== null}<span class="eb-staged-mbadge eb-staged-mbadge--hi"><strong>h</strong>{$item.h_index|escape}</span>{/if}
						{if $item.works_count}<span class="eb-staged-mbadge">{$item.works_count|escape} works</span>{/if}
					</div>
				</div>
				{if $item.orcid}
					<a class="eb-staged-orcid" href="{$item.orcid|escape}" target="_blank" rel="noopener" onclick="event.stopPropagation()">
						<img src="https://info.orcid.org/wp-content/uploads/2019/11/orcid_16x16.png" alt="ORCID" width="13" height="13" />
						{$item.orcid|regex_replace:"/.*\//":""|escape}
					</a>
				{/if}
				<a href="#" class="eb-staged-remove js-eb-remove" data-openalex-id="{$item.openalexId|escape}" onclick="event.stopPropagation()">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
					Remove
				</a>
			</div>

			{* ── Email row: always visible, prominent ── *}
			<div class="eb-staged-email-row" onclick="event.stopPropagation()">
				<label class="eb-staged-email-label">
					<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
					Email <span class="eb-staged-req">*</span>
				</label>
				<input type="email" class="eb-staged-email-input js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="email" value="{$item.email|escape}" placeholder="name@example.com" required />
			</div>

			{* ── Expanded detail: other fields ── *}
			<div class="eb-staged-detail">
				<div class="eb-staged-metrics">
					{if $item.h_index !== null}
						<span class="eb-staged-metric eb-staged-metric--hi"><strong>h</strong>{$item.h_index|escape}</span>
					{/if}
					<span class="eb-staged-metric">
						<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
						{$item.works_count|escape} works
					</span>
					<span class="eb-staged-metric">
						<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
						{$item.cited_by_count|escape} cited
					</span>
				</div>

				<div class="eb-staged-fields">
					<div class="eb-staged-field">
						<label>{translate key="common.name"}</label>
						<input type="text" class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="fullName" value="{$item.fullName|escape}" />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="common.role"}</label>
						<select class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="role">
							<option value="">{translate key="common.chooseOne"}</option>
							{foreach from=$roleOptions key=k item=v}
								<option value="{$k|escape}"{if $item.role == $k} selected{/if}>{$v|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.orcidId"}</label>
						<input type="text" class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="orcidId" value="{$item.orcidId|escape}" placeholder="0000-0000-0000-0000" />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.scopusId"}</label>
						<input type="text" class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="scopusId" value="{$item.scopusId|escape}" />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.googleScholar"}</label>
						<input type="text" class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="googleScholar" value="{$item.googleScholar|escape}" />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.photoUrl"}</label>
						<input type="url" class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="photoUrl" value="{$item.photoUrl|escape}" placeholder="https://..." />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.affiliation"}</label>
						<select class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="affiliation">
							{if $item.affiliations|@count > 0}
								{foreach from=$item.affiliations item=aff}
									<option value="{$aff.name|escape}"{if $item.affiliation == $aff.name} selected{/if}>{$aff.name|escape}</option>
								{/foreach}
							{/if}
							<option value="__other__"{if $item.affiliation && $item.affiliations|@count == 0} selected{/if}>{translate key="common.other"}</option>
						</select>
						<input type="text" class="eb-affiliation-other js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="affiliation"
							placeholder="{translate key="plugins.generic.orcidEditorialBoard.wizard.otherAffiliation"}"
							value="{if $item.affiliation && $item.affiliations|@count == 0}{$item.affiliation|escape}{/if}"
							style="{if !$item.affiliation || $item.affiliations|@count > 0}display:none;{/if}" />
					</div>
					<div class="eb-staged-field">
						<label>{translate key="plugins.generic.orcidEditorialBoard.country"}</label>
						<select class="js-eb-update" data-openalex-id="{$item.openalexId|escape}" data-field="country">
							<option value="">{translate key="common.chooseOne"}</option>
							{foreach from=$countryOptions key=cKey item=cVal}
								<option value="{$cKey|escape}"{if $item.country == $cKey} selected{/if}>{$cVal|escape}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{else}
	<div class="eb-staged-none">
		<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4-4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
		<p>Search for authors on the left, then add them here to edit &amp; save.</p>
	</div>
{/if}
