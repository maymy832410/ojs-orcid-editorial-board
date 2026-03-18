{**
 * Wizard container — two-panel split layout for bulk adding editors via OpenAlex.
 * Variables: staged (array), countryOptions, roleOptions
 *}

<div class="pkp_modal_panel_content eb-wizard">
	<div class="eb-wiz-header">
		<h3 class="eb-wiz-title">{translate key="plugins.generic.orcidEditorialBoard.wizard.title"}</h3>
		<p class="eb-wiz-subtitle">{translate key="plugins.generic.orcidEditorialBoard.wizard.searchLabel"}</p>
	</div>

	<div class="eb-wiz-panels">
		{* ── LEFT PANEL: Search + Results ── *}
		<div class="eb-wiz-panel eb-wiz-panel--search">
			<div class="eb-wiz-panel-label">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				{translate key="plugins.generic.orcidEditorialBoard.openalex.search"}
			</div>
			<div class="eb-wiz-search-bar">
				<input type="text" id="ebWizardSearchInput" class="eb-wiz-search-input" placeholder="{translate key="plugins.generic.orcidEditorialBoard.wizard.searchPlaceholder"}" />
				<button class="eb-wiz-search-btn" id="ebWizardSearchBtn">
					<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
					{translate key="plugins.generic.orcidEditorialBoard.wizard.searchButton"}
				</button>
			</div>
			<div id="ebWizardSearchResults" class="eb-wiz-results-scroll"></div>
		</div>

		{* ── RIGHT PANEL: Staged Members ── *}
		<div class="eb-wiz-panel eb-wiz-panel--staged">
			<div class="eb-wiz-panel-label">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4-4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
				{translate key="plugins.generic.orcidEditorialBoard.wizard.stagedTitle"}
				<span class="eb-wiz-staged-count" id="ebStagedCount">{$staged|@count}</span>
			</div>
			<div id="ebWizardStaged" class="eb-wiz-staged-scroll">
				{include file=$plugin->getTemplateResource('wizardStaged.tpl')}
			</div>
		</div>
	</div>

	<div class="eb-wiz-footer">
		<button class="eb-wiz-btn eb-wiz-btn--primary" id="ebWizardSaveAll">
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9"/></svg>
			{translate key="plugins.generic.orcidEditorialBoard.wizard.saveAndNotify"}
		</button>
		<button class="eb-wiz-btn eb-wiz-btn--secondary" id="ebWizardClose">{translate key="common.close"}</button>
	</div>

	{* ── Confirmation overlay ── *}
	<div id="ebConfirmOverlay" class="eb-confirm-overlay" style="display:none">
		<div class="eb-confirm-box">
			<div class="eb-confirm-icon">
				<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
			</div>
			<h4 class="eb-confirm-title">{translate key="plugins.generic.orcidEditorialBoard.invitation.confirmPopup.title"}</h4>
			<p class="eb-confirm-body">{translate key="plugins.generic.orcidEditorialBoard.invitation.confirmPopup.body"}</p>
			<div id="ebConfirmList" class="eb-confirm-list"></div>
			<div class="eb-confirm-actions">
				<button class="eb-wiz-btn eb-wiz-btn--primary" id="ebConfirmProceed">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9"/></svg>
					{translate key="plugins.generic.orcidEditorialBoard.invitation.confirmPopup.proceed"}
				</button>
				<button class="eb-wiz-btn eb-wiz-btn--secondary" id="ebConfirmCancel">{translate key="plugins.generic.orcidEditorialBoard.invitation.confirmPopup.cancel"}</button>
			</div>
		</div>
	</div>
</div>

<style>
{literal}
/* ── Wider modal panel (CSS fallback — JS is primary) ── */
.pkp_modal .pkp_modal_panel:has(.eb-wizard) {
	width: 95%;
	max-width: 1200px;
}
.pkp_modal .pkp_modal_panel:has(.eb-wizard) > .content {
	max-height: 85vh;
	overflow-y: auto;
}

/* ── Base ── */
.eb-wizard {
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	display: flex; flex-direction: column;
	height: 100%; min-height: 70vh;
}
.eb-wiz-header { flex-shrink: 0; margin-bottom: 14px; }
.eb-wiz-title { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0 0 2px; }
.eb-wiz-subtitle { font-size: 0.85rem; color: #64748b; margin: 0; }

/* ── Two-panel layout ── */
.eb-wiz-panels { display: flex; gap: 18px; flex: 1; min-height: 0; overflow: hidden; }
.eb-wiz-panel {
	flex: 1; display: flex; flex-direction: column; min-height: 0;
	border: 1px solid #e2e8f0; border-radius: 14px;
	background: #fafbfc; overflow: hidden;
}
.eb-wiz-panel--staged { flex: 1.15; }
.eb-wiz-panel-label {
	display: flex; align-items: center; gap: 7px; padding: 12px 16px;
	font-size: 0.82rem; font-weight: 700; color: #334155;
	text-transform: uppercase; letter-spacing: 0.03em;
	background: #f1f5f9; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;
}
.eb-wiz-panel--search .eb-wiz-search-bar { flex-shrink: 0; }
.eb-wiz-results-scroll, .eb-wiz-staged-scroll { flex: 1; overflow-y: auto; padding: 12px 14px; }

/* ── Search bar ── */
.eb-wiz-search-bar {
	display: flex; align-items: center; gap: 0; margin: 12px 14px;
	background: #fff; border: 2px solid #e2e8f0; border-radius: 10px;
	padding: 3px 3px 3px 14px; transition: border-color 0.2s, box-shadow 0.2s;
}
.eb-wiz-search-bar:focus-within { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.eb-wiz-search-input {
	flex: 1; border: none; background: transparent; padding: 9px 10px;
	font-size: 0.92rem; color: #1e293b; outline: none;
}
.eb-wiz-search-input::placeholder { color: #94a3b8; }
.eb-wiz-search-btn {
	display: inline-flex; align-items: center; gap: 5px;
	background: #4f46e5; color: #fff; border: none; border-radius: 7px;
	padding: 9px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
	transition: background 0.15s; white-space: nowrap;
}
.eb-wiz-search-btn:hover { background: #4338ca; }
.eb-wiz-search-btn:disabled { background: #a5b4fc; cursor: wait; }

/* ── Loading ── */
.eb-wiz-loading { display: flex; align-items: center; gap: 10px; padding: 20px; color: #64748b; font-size: 0.88rem; }
.eb-wiz-loading-spinner {
	width: 18px; height: 18px; border: 3px solid #e2e8f0; border-top-color: #6366f1;
	border-radius: 50%; animation: ebSpin 0.6s linear infinite; display: inline-block;
}
@keyframes ebSpin { to { transform: rotate(360deg); } }

/* ── Staged count badge ── */
.eb-wiz-staged-count {
	background: #e0e7ff; color: #3730a3; font-size: 0.72rem; font-weight: 700;
	padding: 2px 9px; border-radius: 20px; min-width: 18px; text-align: center; margin-left: auto;
}

/* ── Footer (pinned) ── */
.eb-wiz-footer {
	display: flex; gap: 10px; flex-shrink: 0;
	padding-top: 14px; margin-top: 10px; border-top: 1px solid #e2e8f0;
}
.eb-wiz-btn {
	display: inline-flex; align-items: center; gap: 6px;
	padding: 10px 20px; border-radius: 10px; font-size: 0.88rem; font-weight: 600;
	cursor: pointer; border: none; transition: all 0.15s;
}
.eb-wiz-btn--primary { background: #16a34a; color: #fff; }
.eb-wiz-btn--primary:hover { background: #15803d; }
.eb-wiz-btn--primary:disabled { background: #86efac; cursor: wait; }
.eb-wiz-btn--secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.eb-wiz-btn--secondary:hover { background: #e2e8f0; }

/* ────────────────────────────────────────
   Staged editor cards — premium layout
   ──────────────────────────────────────── */
.eb-staged-item {
	border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px;
	background: #fff; transition: box-shadow 0.2s, border-color 0.2s; overflow: hidden;
}
.eb-staged-item:hover { border-color: #c7d2fe; box-shadow: 0 2px 12px rgba(99,102,241,0.07); }

/* ── Card header row ── */
.eb-staged-header {
	display: flex; align-items: center; gap: 12px;
	padding: 14px 16px 0; cursor: pointer; user-select: none;
}
.eb-staged-header:hover { background: #fafbfc; }
.eb-staged-chevron {
	width: 18px; height: 18px; transition: transform 0.25s ease;
	flex-shrink: 0; color: #94a3b8;
}
.eb-staged-item.eb-staged--open .eb-staged-chevron { transform: rotate(90deg); }
.eb-staged-avatar {
	width: 38px; height: 38px; border-radius: 50%;
	background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
	display: flex; align-items: center; justify-content: center;
	font-weight: 700; font-size: 0.95rem; flex-shrink: 0;
	text-transform: uppercase;
}
.eb-staged-header-info { flex: 1; min-width: 0; }
.eb-staged-name { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
.eb-staged-meta {
	display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;
}
.eb-staged-meta-text { font-size: 0.78rem; color: #64748b; }
.eb-staged-mbadge {
	display: inline-flex; align-items: center; gap: 2px;
	background: #f0f9ff; color: #0369a1; padding: 1px 8px;
	border-radius: 6px; font-size: 0.7rem; font-weight: 600;
}
.eb-staged-mbadge--hi { background: #fef3c7; color: #92400e; }
.eb-staged-orcid {
	display: inline-flex; align-items: center; gap: 3px;
	font-size: 0.75rem; color: #a6ce39; text-decoration: none; flex-shrink: 0;
}
.eb-staged-remove {
	display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem;
	color: #ef4444; text-decoration: none; font-weight: 600;
	padding: 5px 10px; border-radius: 8px; transition: background 0.15s; flex-shrink: 0;
}
.eb-staged-remove:hover { background: #fef2f2; }

/* ── Email row — always visible below header ── */
.eb-staged-email-row {
	display: flex; align-items: center; gap: 8px;
	margin: 10px 16px 14px 16px;
	padding: 10px 14px;
	background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px;
	transition: border-color 0.2s;
}
.eb-staged-email-row:focus-within { border-color: #6366f1; }
.eb-staged-email-label {
	display: flex; align-items: center; gap: 4px;
	font-size: 0.72rem; font-weight: 700; color: #475569;
	text-transform: uppercase; letter-spacing: 0.04em;
	white-space: nowrap; flex-shrink: 0;
}
.eb-staged-req { color: #ef4444; font-weight: 800; font-size: 0.85rem; }
.eb-staged-email-input {
	flex: 1; min-width: 200px;
	padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
	font-size: 0.88rem; color: #1e293b; background: #fff;
	transition: border-color 0.15s, box-shadow 0.15s;
}
.eb-staged-email-input:focus {
	border-color: #6366f1; outline: none;
	box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.eb-staged-email-input.eb-email-missing {
	border-color: #ef4444;
	box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
	background: #fef2f2;
}
.eb-staged-email-input.eb-email-valid {
	border-color: #16a34a;
}

/* ── Expanded detail ── */
.eb-staged-detail {
	display: none; padding: 0 16px 16px;
	border-top: 1px solid #f1f5f9;
}
.eb-staged-item.eb-staged--open .eb-staged-detail { display: block; }
.eb-staged-metrics {
	display: flex; gap: 6px; flex-wrap: wrap; margin: 12px 0 10px;
}
.eb-staged-metric {
	background: #f0f9ff; color: #0369a1; padding: 3px 10px;
	border-radius: 8px; font-size: 0.75rem; font-weight: 600;
	display: inline-flex; align-items: center; gap: 4px;
}
.eb-staged-metric--hi { background: #fef3c7; color: #92400e; }
.eb-staged-fields {
	display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.eb-staged-field { display: flex; flex-direction: column; gap: 3px; }
.eb-staged-field--full { grid-column: 1 / -1; }
.eb-staged-field label {
	font-size: 0.72rem; font-weight: 700; color: #64748b;
	text-transform: uppercase; letter-spacing: 0.04em;
}
.eb-staged-field input,
.eb-staged-field select {
	padding: 8px 10px; border: 1.5px solid #e2e8f0; border-radius: 8px;
	font-size: 0.85rem; color: #1e293b; background: #f8fafc;
	transition: border-color 0.15s;
}
.eb-staged-field input:focus,
.eb-staged-field select:focus {
	border-color: #6366f1; outline: none;
	box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
	background: #fff;
}
.eb-staged-none {
	text-align: center; padding: 40px 20px; color: #94a3b8; font-size: 0.88rem;
}

/* ── Responsive: stack panels on narrow screens ── */
@media (max-width: 720px) {
	.eb-wiz-panels { flex-direction: column; }
	.eb-wiz-panel { max-height: 40vh; }
	.eb-staged-fields { grid-template-columns: 1fr; }
}

/* ── Confirmation overlay ── */
.eb-confirm-overlay {
	position: absolute; inset: 0;
	background: rgba(15,23,42,.55); backdrop-filter: blur(3px);
	display: flex; align-items: center; justify-content: center;
	z-index: 100; border-radius: 14px;
}
.eb-confirm-box {
	background: #fff; border-radius: 16px; padding: 28px 32px;
	max-width: 460px; width: 90%; box-shadow: 0 12px 40px rgba(0,0,0,.18);
	text-align: center;
}
.eb-confirm-icon { margin-bottom: 12px; }
.eb-confirm-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
.eb-confirm-body { font-size: .88rem; color: #475569; line-height: 1.6; margin: 0 0 14px; }
.eb-confirm-list {
	text-align: left; background: #f8fafc; border: 1px solid #e2e8f0;
	border-radius: 10px; padding: 10px 14px; margin-bottom: 18px;
	max-height: 140px; overflow-y: auto; font-size: .82rem; color: #334155;
}
.eb-confirm-list div { padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
.eb-confirm-list div:last-child { border-bottom: none; }
.eb-confirm-list .eb-cl-email { color: #6366f1; font-weight: 600; }
.eb-confirm-actions { display: flex; gap: 10px; justify-content: center; }
{/literal}
</style>

<input type="hidden" id="ebWizardCsrf" value="{csrf type="raw"}" />

<script type="text/javascript">
{literal}
$(function() {
	var searchBtn = $('#ebWizardSearchBtn');
	var searchInput = $('#ebWizardSearchInput');
	var searchResults = $('#ebWizardSearchResults');
	var stagedContainer = $('#ebWizardStaged');
	var csrfToken = $('#ebWizardCsrf').val();
	var addBtnLabel = '+ Add';
	var loadingHtml = '<div class="eb-wiz-loading"><span class="eb-wiz-loading-spinner"></span> Loading…</div>';

	// ── Resize the modal panel (JS — works in all browsers) ──
	var $panel = $('.eb-wizard').closest('.pkp_modal_panel');
	if ($panel.length) {
		$panel.css({ 'width': '95%', 'max-width': '1200px' });
		$panel.find('> .content').css({ 'max-height': '85vh', 'overflow-y': 'auto' });
	}

	function closeModal() {
		// OJS 3.4: close via the .pkpModalCloseButton in the .pkp_modal overlay
		var $modal = $('.eb-wizard').closest('.pkp_modal');
		if ($modal.length) {
			var $closeBtn = $modal.find('.pkpModalCloseButton, .close');
			if ($closeBtn.length) { $closeBtn.first().trigger('click'); return; }
		}
		// Fallback: hide any visible pkp_modal
		$('.pkp_modal.is_visible').removeClass('is_visible');
	}

	function refreshGrid() {
		var $grid = $('[id*="editorialboardgrid"]');
		if ($grid.length) { $grid.trigger('dataChanged'); }
		else {
			$grid = $('div.pkp_controllers_grid').first();
			if ($grid.length) { $grid.trigger('dataChanged'); }
			else { location.reload(); }
		}
	}

	function updateStagedCount() {
		$('#ebStagedCount').text($('.eb-staged-item').length);
	}

	searchBtn.on('click', function(e) {
		e.preventDefault();
		var q = searchInput.val();
		if (!q) return;
		searchBtn.prop('disabled', true);
		searchResults.html(loadingHtml);
		$.get('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardSearch" escape=false}{literal}', { q: q }, function (json) {
			searchBtn.prop('disabled', false);
			if (json && json.status) {
				searchResults.html(json.content);
				bindSearchResults();
			} else {
				searchResults.html('<div class="eb-wiz-loading">No results found.</div>');
			}
		}, 'json').fail(function() {
			searchBtn.prop('disabled', false);
			searchResults.html('<div class="eb-wiz-loading">Search failed. Please try again.</div>');
		});
	});

	searchInput.on('keypress', function(e) {
		if (e.which === 13) { e.preventDefault(); searchBtn.trigger('click'); }
	});

	function bindSearchResults() {
		$('.js-eb-search-page').off('click').on('click', function(e){
			e.preventDefault();
			var q = searchInput.val();
			var page = $(this).data('page');
			searchResults.html(loadingHtml);
			$.get('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardSearch" escape=false}{literal}', { q: q, page: page }, function (json) {
				if (json && json.status) { searchResults.html(json.content); bindSearchResults(); }
			}, 'json');
		});

		$('.js-eb-add').off('click').on('click', function(e){
			e.preventDefault();
			var btn = $(this);
			if (btn.data('loading')) return; // Prevent double-click
			btn.data('loading', true).prop('disabled', true).html('<span class="eb-wiz-loading-spinner" style="width:12px;height:12px;"></span> Adding…');
			var openalexId = btn.data('openalex-id');
			$.post('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardAdd" escape=false}{literal}', { openalexId: openalexId, csrfToken: csrfToken }, function (json) {
				btn.data('loading', false).prop('disabled', false).html(addBtnLabel);
				if (json && json.status) {
					stagedContainer.html(json.content);
					bindStagedEvents();
					updateStagedCount();
					btn.html('✓ Added').prop('disabled', true).css({'background':'#dcfce7','color':'#16a34a','border-color':'#86efac'});
				} else {
					alert((json && json.content) ? json.content : 'Error adding author');
				}
			}, 'json').fail(function() {
				btn.data('loading', false).prop('disabled', false).html(addBtnLabel);
				alert('Network error — please try again');
			});
		});

		// Toggle "Show more" in search result cards
		$('.js-eb-sr-toggle').off('click').on('click', function(e){
			e.preventDefault();
			var $btn = $(this);
			var $extras = $btn.next('.eb-sr-extras');
			$btn.toggleClass('active');
			if ($extras.is(':visible')) {
				$extras.slideUp(150);
				$btn.find('.eb-sr-toggle-text').text('{/literal}{translate key="plugins.generic.orcidEditorialBoard.wizard.showMore"}{literal}');
			} else {
				$extras.slideDown(150);
				$btn.find('.eb-sr-toggle-text').text('{/literal}{translate key="plugins.generic.orcidEditorialBoard.wizard.showLess"}{literal}');
			}
		});
	}

	function bindStagedEvents() {
		// Accordion toggle — ignore clicks on email input row and remove btn
		$('.eb-staged-header').off('click').on('click', function(e){
			if ($(e.target).closest('.eb-staged-remove, .eb-staged-email-row').length) return;
			$(this).closest('.eb-staged-item').toggleClass('eb-staged--open');
		});

		// Real-time email validation on the inline email input
		$('.eb-staged-email-input').off('input.ebval').on('input.ebval', function(){
			var $inp = $(this);
			var val = $.trim($inp.val());
			var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!val) {
				$inp.removeClass('eb-email-valid').addClass('eb-email-missing');
			} else if (!emailRegex.test(val)) {
				$inp.removeClass('eb-email-valid').addClass('eb-email-missing');
			} else {
				$inp.removeClass('eb-email-missing').addClass('eb-email-valid');
			}
		});

		$('.js-eb-remove').off('click').on('click', function(e){
			e.preventDefault();
			var openalexId = $(this).data('openalex-id');
			$.post('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardRemove" escape=false}{literal}', { openalexId: openalexId, csrfToken: csrfToken }, function (json) {
				if (json && json.status) {
					stagedContainer.html(json.content);
					bindStagedEvents();
					updateStagedCount();
				}
			}, 'json');
		});

		$('.js-eb-update').off('change').on('change', function(){
			var $el = $(this);
			var openalexId = $el.data('openalex-id');
			var field = $el.data('field');
			var value = $el.val();

			// Client-side email validation
			if (field === 'email' && value !== '') {
				var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				if (!emailRegex.test(value)) {
					$el.css('border-color', '#ef4444').css('box-shadow', '0 0 0 2px rgba(239,68,68,0.15)');
					if (!$el.next('.eb-field-error').length) {
						$el.after('<div class="eb-field-error" style="color:#ef4444;font-size:0.72rem;margin-top:2px;">Please enter a valid email address</div>');
					}
					return;
				} else {
					$el.css('border-color', '#16a34a').css('box-shadow', '0 0 0 2px rgba(22,163,74,0.1)');
					$el.next('.eb-field-error').remove();
				}
			}

			if (field === 'affiliation' && value === '__other__') {
				$el.closest('.eb-staged-field').find('.eb-affiliation-other').show().focus();
				return;
			}
			$.post('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardUpdate" escape=false}{literal}', { openalexId: openalexId, field: field, value: value, csrfToken: csrfToken });
		});

		$('.eb-affiliation-other').off('change').on('change', function(){
			var $el = $(this);
			$.post('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardUpdate" escape=false}{literal}', { openalexId: $el.data('openalex-id'), field: 'affiliation', value: $el.val(), csrfToken: csrfToken });
		});
	}

	$('#ebWizardSaveAll').on('click', function(e){
		e.preventDefault();
		if (!$('.eb-staged-item').length) {
			alert('No editors staged yet. Search and add candidates first.');
			return;
		}

		// Pre-submit: validate all staged editors have a valid email
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		var missing = [];
		$('.eb-staged-email-input').each(function() {
			var $inp = $(this);
			var val = $.trim($inp.val());
			if (!val || !emailRegex.test(val)) {
				$inp.addClass('eb-email-missing').removeClass('eb-email-valid');
				var $item = $inp.closest('.eb-staged-item');
				$item.addClass('eb-staged--open'); // auto-expand so user sees the issue
				var name = $item.find('.eb-staged-name').text();
				missing.push(name);
			} else {
				$inp.removeClass('eb-email-missing').addClass('eb-email-valid');
			}
		});
		if (missing.length) {
			alert('Please provide a valid email for:\n• ' + missing.join('\n• '));
			// Scroll to the first problematic input
			var $first = $('.eb-email-missing').first();
			if ($first.length) $first.focus();
			return;
		}

		// ── Show confirmation popup ──
		var listHtml = '';
		$('.eb-staged-item').each(function(){
			var name = $(this).find('.eb-staged-name').text();
			var email = $(this).find('.eb-staged-email-input').val() || '—';
			listHtml += '<div><strong>' + $('<span>').text(name).html() + '</strong> → <span class="eb-cl-email">' + $('<span>').text(email).html() + '</span></div>';
		});
		$('#ebConfirmList').html(listHtml);
		$('#ebConfirmOverlay').fadeIn(150);
	});

	// Confirmation: proceed
	$('#ebConfirmProceed').on('click', function(e){
		e.preventDefault();
		$('#ebConfirmOverlay').fadeOut(100);

		var btn = $('#ebWizardSaveAll');
		btn.prop('disabled', true).html('<span class="eb-wiz-loading-spinner" style="width:14px;height:14px;"></span> Saving &amp; sending…');
		$.post('{/literal}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="wizardFinalize" escape=false}{literal}', { csrfToken: csrfToken }, function(json){
			btn.prop('disabled', false).html('{/literal}{translate key="plugins.generic.orcidEditorialBoard.wizard.saveAndNotify"}{literal}');
			if (json && json.status) {
				closeModal();
				setTimeout(function() { refreshGrid(); }, 300);
			} else {
				alert((json && json.content) ? json.content : 'An error occurred.');
			}
		}, 'json').fail(function() {
			btn.prop('disabled', false).html('{/literal}{translate key="plugins.generic.orcidEditorialBoard.wizard.saveAndNotify"}{literal}');
			alert('Request failed');
		});
	});

	// Confirmation: cancel
	$('#ebConfirmCancel').on('click', function(e){
		e.preventDefault();
		$('#ebConfirmOverlay').fadeOut(100);
	});

	$('#ebWizardClose').on('click', function(e){ e.preventDefault(); closeModal(); });

	bindStagedEvents();
});
{/literal}
</script>
