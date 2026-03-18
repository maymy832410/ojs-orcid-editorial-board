{**
 * templates/editorialBoardMemberForm.tpl
 * Premium edit form — grouped sections, two-column grid, modern styling.
 *}
<form class="pkp_form eb-edit-form" id="editorialBoardMemberForm" method="post" action="{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="updateMember" memberId=$memberId}">
	{csrf}
	{fbvElement type="hidden" id="memberId" value=$memberId}

	{* ── Section 1: Identity ── *}
	<div class="eb-form-section">
		<div class="eb-form-section-header">
			<div class="eb-form-section-icon">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4-4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</div>
			<div>
				<h3 class="eb-form-section-title">{translate key="plugins.generic.orcidEditorialBoard.form.identity"}</h3>
				<p class="eb-form-section-desc">{translate key="plugins.generic.orcidEditorialBoard.form.identityDesc"}</p>
			</div>
		</div>
		<div class="eb-form-grid">
			<div class="eb-form-field eb-form-field--full">
				<label class="eb-form-label" for="fullName">{translate key="common.name"} <span class="eb-form-req">*</span></label>
				<input type="text" id="fullName" name="fullName" value="{$fullName|escape}" required class="eb-form-input" placeholder="{translate key="common.name"}" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="role">{translate key="common.role"} <span class="eb-form-req">*</span></label>
				<select id="role" name="role" required class="eb-form-input">
					{foreach from=$roleOptions key=k item=v}
						<option value="{$k|escape}"{if $role == $k} selected{/if}>{$v|escape}</option>
					{/foreach}
				</select>
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="email">{translate key="user.email"} <span class="eb-form-req">*</span></label>
				<input type="email" id="email" name="email" value="{$email|escape}" required class="eb-form-input" placeholder="name@example.com" />
			</div>
			<div class="eb-form-field eb-form-field--full">
				<label class="eb-form-label" for="photoUrl">{translate key="plugins.generic.orcidEditorialBoard.photoUrl"}</label>
				<input type="url" id="photoUrl" name="photoUrl" value="{$photoUrl|escape}" class="eb-form-input" placeholder="https://..." />
			</div>
		</div>
	</div>

	{* ── Section 2: Identifiers ── *}
	<div class="eb-form-section">
		<div class="eb-form-section-header">
			<div class="eb-form-section-icon eb-form-section-icon--green">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
			</div>
			<div>
				<h3 class="eb-form-section-title">{translate key="plugins.generic.orcidEditorialBoard.form.identifiers"}</h3>
				<p class="eb-form-section-desc">{translate key="plugins.generic.orcidEditorialBoard.form.identifiersDesc"}</p>
			</div>
		</div>
		<div class="eb-form-grid">
			<div class="eb-form-field">
				<label class="eb-form-label" for="orcidId">
					<svg viewBox="0 0 256 256" width="14" height="14"><path fill="#a6ce39" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z"/><path fill="#FFF" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.5-107.1h41.6c39.6 0 57 28.3 57 53.6 0 27.5-21.5 53.5-56.8 53.5h-41.8V79.1zm15.4 93.3h24.5c34.9 0 42.9-26.5 42.9-39.7C191.6 111 176 92.9 150.4 92.9h-26.2v79.5zM108.9 55.6c0 5.7-4.5 10.3-10.2 10.3s-10.2-4.6-10.2-10.3c0-5.7 4.5-10.3 10.2-10.3s10.2 4.6 10.2 10.3z"/></svg>
					{translate key="plugins.generic.orcidEditorialBoard.orcidId"}
				</label>
				<input type="text" id="orcidId" name="orcidId" value="{$orcidId|escape}" class="eb-form-input" placeholder="0000-0000-0000-0000" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="scopusId">{translate key="plugins.generic.orcidEditorialBoard.scopusId"}</label>
				<input type="text" id="scopusId" name="scopusId" value="{$scopusId|escape}" class="eb-form-input" />
			</div>
			<div class="eb-form-field eb-form-field--full">
				<label class="eb-form-label" for="googleScholar">{translate key="plugins.generic.orcidEditorialBoard.googleScholar"}</label>
				<input type="text" id="googleScholar" name="googleScholar" value="{$googleScholar|escape}" class="eb-form-input" placeholder="URL or user ID" />
			</div>
		</div>
	</div>

	{* ── Section 3: Affiliation ── *}
	<div class="eb-form-section">
		<div class="eb-form-section-header">
			<div class="eb-form-section-icon eb-form-section-icon--blue">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
			</div>
			<div>
				<h3 class="eb-form-section-title">{translate key="plugins.generic.orcidEditorialBoard.form.affiliation"}</h3>
				<p class="eb-form-section-desc">{translate key="plugins.generic.orcidEditorialBoard.form.affiliationDesc"}</p>
			</div>
		</div>
		<div class="eb-form-grid">
			<div class="eb-form-field">
				<label class="eb-form-label" for="affiliation">{translate key="plugins.generic.orcidEditorialBoard.affiliation"}</label>
				<input type="text" id="affiliation" name="affiliation" value="{$affiliation|escape}" class="eb-form-input" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="country">{translate key="plugins.generic.orcidEditorialBoard.country"} <span class="eb-form-req">*</span></label>
				<select id="country" name="country" required class="eb-form-input">
					<option value="">{translate key="common.chooseOne"}</option>
					{foreach from=$countryOptions key=cKey item=cVal}
						<option value="{$cKey|escape}"{if $country == $cKey} selected{/if}>{$cVal|escape}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>

	{* ── Section 4: Tenure & Display ── *}
	<div class="eb-form-section">
		<div class="eb-form-section-header">
			<div class="eb-form-section-icon eb-form-section-icon--amber">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			</div>
			<div>
				<h3 class="eb-form-section-title">{translate key="plugins.generic.orcidEditorialBoard.form.tenure"}</h3>
				<p class="eb-form-section-desc">{translate key="plugins.generic.orcidEditorialBoard.form.tenureDesc"}</p>
			</div>
		</div>
		<div class="eb-form-grid">
			<div class="eb-form-field">
				<label class="eb-form-label" for="tenureStart">{translate key="plugins.generic.orcidEditorialBoard.tenureStart"}</label>
				<input type="date" id="tenureStart" name="tenureStart" value="{$tenureStart|escape}" class="eb-form-input" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="tenureEnd">{translate key="plugins.generic.orcidEditorialBoard.tenureEnd"}</label>
				<input type="date" id="tenureEnd" name="tenureEnd" value="{$tenureEnd|escape}" class="eb-form-input" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label" for="sortOrder">{translate key="plugins.generic.orcidEditorialBoard.sortOrder"}</label>
				<input type="number" id="sortOrder" name="sortOrder" value="{$sortOrder|escape}" class="eb-form-input" min="0" />
			</div>
			<div class="eb-form-field">
				<label class="eb-form-label">{translate key="plugins.generic.orcidEditorialBoard.visibility"}</label>
				<label class="eb-form-toggle">
					<input type="checkbox" id="isVisible" name="isVisible" value="1"{if $isVisible} checked{/if} />
					<span class="eb-form-toggle-slider"></span>
					<span class="eb-form-toggle-text">{translate key="plugins.generic.orcidEditorialBoard.visibleOnPage"}</span>
				</label>
			</div>
		</div>
	</div>

	{* ── Section 5: Status (read-only) ── *}
	{if $coiStatus || $memberStatus}
		<div class="eb-form-section eb-form-section--status">
			<div class="eb-form-section-header">
				<div class="eb-form-section-icon eb-form-section-icon--slate">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				</div>
				<div>
					<h3 class="eb-form-section-title">{translate key="plugins.generic.orcidEditorialBoard.form.status"}</h3>
					<p class="eb-form-section-desc">{translate key="plugins.generic.orcidEditorialBoard.form.statusDesc"}</p>
				</div>
			</div>
			<div class="eb-form-status-grid">
				{if $coiStatus}
					<div class="eb-form-status-item">
						<span class="eb-form-status-label">{translate key="plugins.generic.orcidEditorialBoard.coi.status"}</span>
						{if $coiStatus == 'declared'}
							<span class="eb-form-status-badge eb-form-status-badge--green">
								<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
								{translate key="plugins.generic.orcidEditorialBoard.coi.statusDeclared"}
							</span>
							{if $coiDeclaredAt}<span class="eb-form-status-date">{$coiDeclaredAt|escape}</span>{/if}
							{if $coiText}<p class="eb-form-status-detail"><em>{$coiText|escape}</em></p>{/if}
						{else}
							<span class="eb-form-status-badge eb-form-status-badge--amber">
								<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><circle cx="10" cy="10" r="8"/></svg>
								{translate key="plugins.generic.orcidEditorialBoard.coi.statusPending"}
							</span>
						{/if}
					</div>
				{/if}
				{if $memberStatus}
					<div class="eb-form-status-item">
						<span class="eb-form-status-label">{translate key="plugins.generic.orcidEditorialBoard.memberStatus"}</span>
						{if $memberStatus == 'removed_by_owner'}
							<span class="eb-form-status-badge eb-form-status-badge--red">
								<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
								{translate key="plugins.generic.orcidEditorialBoard.form.removedByOwner"}
							</span>
							<label class="eb-form-reset-toggle">
								<input type="checkbox" name="resetStatus" value="1" />
								<span>{translate key="plugins.generic.orcidEditorialBoard.form.resetToActive"}</span>
							</label>
						{else}
							<span class="eb-form-status-badge eb-form-status-badge--green">
								<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
								{translate key="plugins.generic.orcidEditorialBoard.form.statusActive"}
							</span>
						{/if}
					</div>
				{/if}
			</div>
		</div>
	{/if}

	<div class="eb-form-actions">
		<button type="submit" class="eb-form-btn eb-form-btn--save">
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
			{translate key="common.save"}
		</button>
		<button type="button" class="eb-form-btn eb-form-btn--cancel" id="ebFormCancel">{translate key="common.cancel"}</button>
	</div>
</form>

<style>
{literal}
.eb-edit-form {
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	max-width: 680px;
	padding: 0;
}
.eb-form-section {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 12px;
	padding: 0;
	margin-bottom: 16px;
	overflow: hidden;
}
.eb-form-section--status {
	background: #fafbfc;
}
.eb-form-section-header {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	padding: 18px 22px 14px;
	border-bottom: 1px solid #f1f5f9;
}
.eb-form-section-icon {
	width: 36px; height: 36px;
	background: #eef2ff;
	border-radius: 10px;
	display: flex; align-items: center; justify-content: center;
	flex-shrink: 0;
	color: #4f46e5;
}
.eb-form-section-icon--green { background: #dcfce7; color: #16a34a; }
.eb-form-section-icon--green svg { stroke: #16a34a; }
.eb-form-section-icon--blue { background: #dbeafe; color: #2563eb; }
.eb-form-section-icon--blue svg { stroke: #2563eb; }
.eb-form-section-icon--amber { background: #fef3c7; color: #d97706; }
.eb-form-section-icon--amber svg { stroke: #d97706; }
.eb-form-section-icon--slate { background: #f1f5f9; color: #475569; }
.eb-form-section-icon--slate svg { stroke: #475569; }
.eb-form-section-title {
	margin: 0;
	font-size: 0.95rem;
	font-weight: 700;
	color: #0f172a;
}
.eb-form-section-desc {
	margin: 2px 0 0;
	font-size: 0.78rem;
	color: #64748b;
}
.eb-form-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 14px;
	padding: 18px 22px;
}
.eb-form-field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.eb-form-field--full {
	grid-column: 1 / -1;
}
.eb-form-label {
	display: flex;
	align-items: center;
	gap: 5px;
	font-size: 0.78rem;
	font-weight: 600;
	color: #374151;
	text-transform: uppercase;
	letter-spacing: 0.03em;
}
.eb-form-req {
	color: #ef4444;
	font-weight: 700;
}
.eb-form-input {
	padding: 9px 12px;
	border: 1.5px solid #e2e8f0;
	border-radius: 8px;
	font-size: 0.88rem;
	color: #1e293b;
	background: #f8fafc;
	transition: border-color 0.15s, box-shadow 0.15s;
	width: 100%;
	box-sizing: border-box;
}
.eb-form-input:focus {
	border-color: #6366f1;
	box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
	outline: none;
	background: #fff;
}
.eb-form-input:invalid:not(:placeholder-shown) {
	border-color: #ef4444;
}

/* Toggle switch */
.eb-form-toggle {
	display: flex;
	align-items: center;
	gap: 10px;
	cursor: pointer;
	padding: 6px 0;
}
.eb-form-toggle input { display: none; }
.eb-form-toggle-slider {
	width: 40px; height: 22px;
	background: #cbd5e1;
	border-radius: 999px;
	position: relative;
	transition: background 0.2s;
	flex-shrink: 0;
}
.eb-form-toggle-slider::after {
	content: '';
	position: absolute;
	top: 2px; left: 2px;
	width: 18px; height: 18px;
	background: #fff;
	border-radius: 50%;
	transition: transform 0.2s;
	box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.eb-form-toggle input:checked + .eb-form-toggle-slider {
	background: #16a34a;
}
.eb-form-toggle input:checked + .eb-form-toggle-slider::after {
	transform: translateX(18px);
}
.eb-form-toggle-text {
	font-size: 0.82rem;
	color: #475569;
}

/* Status indicators */
.eb-form-status-grid {
	padding: 16px 22px;
	display: flex;
	flex-direction: column;
	gap: 14px;
}
.eb-form-status-item {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-wrap: wrap;
}
.eb-form-status-label {
	font-size: 0.78rem;
	font-weight: 600;
	color: #64748b;
	text-transform: uppercase;
	letter-spacing: 0.03em;
	min-width: 80px;
}
.eb-form-status-badge {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 4px 12px;
	border-radius: 20px;
	font-size: 0.78rem;
	font-weight: 600;
}
.eb-form-status-badge--green { background: #dcfce7; color: #166534; }
.eb-form-status-badge--amber { background: #fef3c7; color: #92400e; }
.eb-form-status-badge--red { background: #fee2e2; color: #991b1b; }
.eb-form-status-date {
	font-size: 0.75rem;
	color: #94a3b8;
}
.eb-form-status-detail {
	width: 100%;
	margin: 4px 0 0;
	font-size: 0.82rem;
	color: #475569;
	padding-left: 90px;
}
.eb-form-reset-toggle {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 0.82rem;
	color: #2563eb;
	cursor: pointer;
	padding: 2px 8px;
	border-radius: 6px;
	border: 1px solid #bfdbfe;
	background: #eff6ff;
}
.eb-form-reset-toggle input { cursor: pointer; }

/* Form actions */
.eb-form-actions {
	display: flex;
	gap: 10px;
	justify-content: flex-end;
	padding-top: 8px;
}
.eb-form-btn {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 10px 22px;
	border-radius: 10px;
	font-size: 0.88rem;
	font-weight: 600;
	cursor: pointer;
	border: none;
	transition: all 0.15s;
}
.eb-form-btn--save {
	background: #4f46e5;
	color: #fff;
}
.eb-form-btn--save:hover {
	background: #4338ca;
}
.eb-form-btn--cancel {
	background: #f1f5f9;
	color: #475569;
	border: 1px solid #e2e8f0;
}
.eb-form-btn--cancel:hover {
	background: #e2e8f0;
}

@media (max-width: 500px) {
	.eb-form-grid { grid-template-columns: 1fr; }
	.eb-form-section-header { padding: 14px 16px 10px; }
	.eb-form-grid { padding: 14px 16px; }
}
{/literal}
</style>

<script type="text/javascript">
$(function() {
	$('#editorialBoardMemberForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	$('#ebFormCancel').on('click', function() {
		var $dlg = $(this).closest('.ui-dialog-content');
		if ($dlg.length) { $dlg.dialog('close'); }
		else { $(this).closest('.pkp_modal').find('.close').trigger('click'); }
	});
});
</script>
