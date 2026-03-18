{**
 * templates/editorialBoard.tpl
 *
 * Premium public-facing editorial board page with verified badges,
 * SVG arc role text around avatars, and compact metrics.
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.orcidEditorialBoard.pageTitle"}

<style>
{literal}
:root {
	--eb-gold: #C9A84C;
	--eb-gold-bg: linear-gradient(135deg, #FFF9E6 0%, #FFF3CC 100%);
	--eb-gold-border: #E8D48B;
	--eb-silver: #5B7FBF;
	--eb-silver-bg: linear-gradient(135deg, #F0F4FB 0%, #E1EAFC 100%);
	--eb-silver-border: #B3C9ED;
	--eb-bronze: #2A9D8F;
	--eb-bronze-bg: linear-gradient(135deg, #EEFAF8 0%, #D5F2EE 100%);
	--eb-bronze-border: #A8DDD6;
	--eb-default: #6C757D;
	--eb-default-bg: linear-gradient(135deg, #F8F9FA 0%, #EFF1F3 100%);
	--eb-default-border: #DEE2E6;
	--eb-verified: #16A34A;
	--eb-unverified: #9CA3AF;
	--eb-card-shadow: 0 2px 12px rgba(0,0,0,0.06);
	--eb-card-hover-shadow: 0 8px 30px rgba(0,0,0,0.12);
	--eb-radius: 16px;
	--eb-text-primary: #1A1A2E;
	--eb-text-secondary: #4A5568;
	--eb-text-muted: #718096;
}

.eb-page {
	max-width: 1200px;
	margin: 0 auto;
	padding: 40px 20px 60px;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.eb-header {
	text-align: center;
	margin-bottom: 48px;
}

.eb-header__title {
	font-size: 2.2rem;
	font-weight: 800;
	color: var(--eb-text-primary);
	margin: 0 0 8px;
	letter-spacing: -0.5px;
}

.eb-header__journal {
	font-size: 1.05rem;
	color: var(--eb-text-muted);
	margin: 0 0 16px;
	font-weight: 400;
}

.eb-header__divider {
	width: 60px;
	height: 4px;
	background: linear-gradient(90deg, var(--eb-gold), var(--eb-bronze));
	border: none;
	border-radius: 2px;
	margin: 0 auto;
}

.eb-notice {
	background: #FFF3CD;
	color: #856404;
	padding: 12px 20px;
	border-radius: 8px;
	border: 1px solid #FFEAA7;
	margin-bottom: 32px;
	text-align: center;
	font-size: 0.9rem;
}

.eb-auditor-box {
	background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 50%, #F0FDFA 100%);
	border: 1px solid #BBF7D0;
	border-left: 4px solid #16A34A;
	border-radius: 12px;
	padding: 24px 28px;
	margin-bottom: 40px;
	display: flex;
	gap: 16px;
	align-items: flex-start;
}

.eb-auditor-box__icon {
	flex-shrink: 0;
	width: 44px;
	height: 44px;
	background: #16A34A;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
}

.eb-auditor-box__icon svg {
	width: 24px;
	height: 24px;
	fill: #FFF;
}

.eb-auditor-box__content {
	flex: 1;
}

.eb-auditor-box__title {
	font-size: 1.05rem;
	font-weight: 700;
	color: #166534;
	margin: 0 0 8px;
}

.eb-auditor-box__text {
	font-size: 0.9rem;
	color: #15803D;
	line-height: 1.65;
	margin: 0;
}

.eb-auditor-box__text strong {
	color: #166534;
}

@media (max-width: 768px) {
	.eb-auditor-box {
		flex-direction: column;
		padding: 18px 20px;
	}
}

/* ── Diversity ── */
.eb-diversity {
	margin-bottom: 36px;
}

.eb-diversity__header {
	display: flex;
	align-items: baseline;
	gap: 10px;
	margin-bottom: 12px;
}

.eb-diversity__title {
	font-size: 1.1rem;
	font-weight: 700;
	color: var(--eb-text-primary);
	margin: 0;
}

.eb-diversity__count {
	font-size: 0.95rem;
	color: var(--eb-text-secondary);
}

.eb-diversity__list {
	list-style: none;
	padding: 0;
	margin: 0;
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.eb-diversity__item {
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.eb-diversity__label {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 0.95rem;
	color: var(--eb-text-secondary);
}

.eb-diversity__bar {
	height: 8px;
	background: #E5E7EB;
	border-radius: 999px;
	overflow: hidden;
}

.eb-diversity__bar-fill {
	height: 100%;
	background: linear-gradient(90deg, #4F46E5, #22C55E);
}

/* ── Role section ── */
.eb-role-section {
	margin-bottom: 48px;
}

.eb-role-header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 24px;
	padding-bottom: 12px;
	border-bottom: 2px solid #E2E8F0;
}

.eb-role-title {
	font-size: 1.3rem;
	font-weight: 700;
	color: var(--eb-text-primary);
	margin: 0;
}

.eb-role-count {
	background: #E2E8F0;
	color: var(--eb-text-secondary);
	font-size: 0.8rem;
	font-weight: 600;
	padding: 2px 10px;
	border-radius: 12px;
}

/* ── Card grid ── */
.eb-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
	gap: 24px;
}

.eb-grid--eic {
	grid-template-columns: 1fr;
	max-width: 720px;
	margin: 0 auto;
}

/* ── Member card ── */
.eb-card {
	background: #FFFFFF;
	border-radius: var(--eb-radius);
	border: 1px solid #E2E8F0;
	box-shadow: var(--eb-card-shadow);
	padding: 28px;
	transition: box-shadow 0.25s ease, transform 0.25s ease;
	position: relative;
	overflow: hidden;
}

.eb-card:hover {
	box-shadow: var(--eb-card-hover-shadow);
	transform: translateY(-3px);
}

.eb-card--eic {
	border-top: 4px solid var(--eb-gold);
	background: var(--eb-gold-bg);
}

.eb-card--me {
	border-top: 3px solid var(--eb-silver);
}

.eb-card--ae {
	border-top: 3px solid var(--eb-bronze);
}

.eb-card__top {
	display: flex;
	align-items: flex-start;
	gap: 20px;
}

/* ── Avatar with SVG arc role text ── */
.eb-avatar-wrap {
	position: relative;
	width: 130px;
	height: 130px;
	flex-shrink: 0;
}

.eb-avatar-wrap--eic {
	width: 150px;
	height: 150px;
}

.eb-avatar-ring {
	width: 100%;
	height: 100%;
	display: block;
}

.eb-avatar-ring__circle {
	fill: none;
	stroke: #E2E8F0;
	stroke-width: 1.5;
}

.eb-card--eic .eb-avatar-ring__circle {
	stroke: var(--eb-gold);
	stroke-width: 2;
}

.eb-card--me .eb-avatar-ring__circle {
	stroke: var(--eb-silver);
	stroke-width: 1.5;
}

.eb-card--ae .eb-avatar-ring__circle {
	stroke: var(--eb-bronze);
	stroke-width: 1.5;
}

.eb-arc-text {
	font-size: 9px;
	font-weight: 800;
	letter-spacing: 2.5px;
	fill: var(--eb-default);
}

.eb-arc-text--eic {
	fill: var(--eb-gold);
	font-size: 9.5px;
	letter-spacing: 2px;
}

.eb-arc-text--me {
	fill: var(--eb-silver);
}

.eb-arc-text--ae {
	fill: var(--eb-bronze);
}

.eb-avatar__photo {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 84px;
	height: 84px;
	border-radius: 50%;
	object-fit: cover;
	border: 3px solid #FFF;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.eb-avatar-wrap--eic .eb-avatar__photo {
	width: 100px;
	height: 100px;
}

.eb-avatar__initials {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 84px;
	height: 84px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.6rem;
	font-weight: 700;
	color: #FFF;
	border: 3px solid #FFF;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.eb-avatar-wrap--eic .eb-avatar__initials {
	width: 100px;
	height: 100px;
	font-size: 1.9rem;
}

.eb-avatar__initials--eic {
	background: linear-gradient(135deg, #C9A84C, #B8963C);
}

.eb-avatar__initials--me {
	background: linear-gradient(135deg, #5B7FBF, #4A6FA8);
}

.eb-avatar__initials--ae {
	background: linear-gradient(135deg, #2A9D8F, #238B7E);
}

.eb-avatar__initials--default {
	background: linear-gradient(135deg, #6C757D, #5A636A);
}

/* ── Verified tick ── */
.eb-verified-tick {
	position: absolute;
	bottom: 10px;
	right: 10px;
	width: 26px;
	height: 26px;
	background: var(--eb-verified);
	border-radius: 50%;
	border: 3px solid #FFF;
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 2px 6px rgba(22,163,74,0.35);
	z-index: 2;
}

.eb-avatar-wrap--eic .eb-verified-tick {
	width: 30px;
	height: 30px;
	bottom: 12px;
	right: 12px;
}

.eb-verified-tick svg {
	width: 14px;
	height: 14px;
	fill: #FFF;
}

.eb-avatar-wrap--eic .eb-verified-tick svg {
	width: 16px;
	height: 16px;
}

/* ── Mobile role badge (hidden on desktop, shown on mobile) ── */
.eb-role-badge-mobile {
	display: none;
	align-items: center;
	gap: 4px;
	font-size: 0.68rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	padding: 2px 8px;
	border-radius: 20px;
	white-space: nowrap;
	color: #FFF;
}

.eb-role-badge-mobile--eic {
	background: var(--eb-gold);
}

.eb-role-badge-mobile--me {
	background: var(--eb-silver);
}

.eb-role-badge-mobile--ae {
	background: var(--eb-bronze);
}

.eb-role-badge-mobile--default {
	background: var(--eb-default);
}

/* ── Info block ── */
.eb-info {
	flex: 1;
	min-width: 0;
}

.eb-info__name {
	font-size: 1.15rem;
	font-weight: 700;
	color: var(--eb-text-primary);
	margin: 0 0 2px;
	line-height: 1.3;
}

.eb-card--eic .eb-info__name {
	font-size: 1.35rem;
}

.eb-info__name-row {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	margin-bottom: 2px;
}

.eb-info__affiliation {
	font-size: 0.85rem;
	color: var(--eb-text-secondary);
	margin: 0 0 8px;
	display: flex;
	align-items: center;
	gap: 5px;
}

.eb-info__affiliation svg {
	width: 13px;
	height: 13px;
	fill: var(--eb-text-muted);
	flex-shrink: 0;
}

.eb-info__metrics {
	display: flex;
	gap: 6px;
	flex-wrap: wrap;
}

.eb-metric-pill {
	background: #E0F2FE;
	color: #075985;
	padding: 3px 8px;
	border-radius: 8px;
	font-size: 0.73rem;
	font-weight: 600;
}

/* ── Verification status ── */
.eb-verification {
	margin-top: 14px;
	padding-top: 12px;
	border-top: 1px solid #EDF2F7;
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
}

.eb-status {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	font-size: 0.8rem;
	font-weight: 600;
	padding: 4px 12px;
	border-radius: 20px;
}

.eb-status--verified {
	background: #DCFCE7;
	color: var(--eb-verified);
}

.eb-status--unverified {
	background: #F3F4F6;
	color: var(--eb-unverified);
}

.eb-status--dispute {
	background: #FEF3C7;
	color: #92400E;
	border: 1px solid #F59E0B;
}

.eb-status svg {
	width: 14px;
	height: 14px;
	fill: currentColor;
}

.eb-verify-link {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	font-size: 0.78rem;
	font-weight: 600;
	color: #A6CE39;
	text-decoration: none;
	padding: 4px 12px;
	border-radius: 20px;
	border: 1px solid #A6CE39;
	transition: background 0.2s, color 0.2s;
}

.eb-verify-link:hover {
	background: #A6CE39;
	color: #FFF;
}

.eb-verify-link svg {
	width: 16px;
	height: 16px;
}

.eb-consent-id {
	display: flex;
	align-items: center;
	gap: 4px;
	font-size: 0.72rem;
	color: #94a3b8;
	margin-top: 2px;
}
.eb-consent-id code {
	background: #f1f5f9;
	padding: 1px 5px;
	border-radius: 4px;
	font-size: 0.72rem;
	letter-spacing: 0.5px;
}

.eb-verify-actions {
	display: flex;
	gap: 6px;
	flex-wrap: wrap;
	margin-top: 4px;
}

.eb-verify-btn {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	font-size: 0.78rem;
	font-weight: 600;
	color: #FFF;
	background: #16a34a;
	text-decoration: none;
	padding: 4px 12px;
	border-radius: 20px;
	border: 1px solid #16a34a;
	transition: background 0.2s;
}
.eb-verify-btn:hover {
	background: #15803d;
	border-color: #15803d;
}
.eb-verify-btn svg {
	width: 14px;
	height: 14px;
}

/* ── Keywords (compact single row) ── */
.eb-keywords-compact {
	display: flex;
	gap: 5px;
	flex-wrap: wrap;
	align-items: center;
	margin-top: 10px;
}

.eb-keyword-tag {
	background: #EEF2FF;
	color: #3730A3;
	border-radius: 10px;
	padding: 2px 8px;
	font-size: 0.7rem;
	font-weight: 600;
	white-space: nowrap;
}

.eb-keyword-more {
	color: var(--eb-text-muted);
	font-size: 0.7rem;
	font-weight: 500;
	font-style: italic;
}

/* ── Profile links row ── */
.eb-links {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-top: 10px;
	flex-wrap: wrap;
}

.eb-link {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 0.75rem;
	font-weight: 500;
	color: var(--eb-text-secondary);
	text-decoration: none;
	padding: 3px 8px;
	border-radius: 6px;
	background: #F7FAFC;
	border: 1px solid #E2E8F0;
	transition: background 0.15s, border-color 0.15s;
}

.eb-link:hover {
	background: #EDF2F7;
	border-color: #CBD5E0;
	color: var(--eb-text-primary);
}

.eb-link svg {
	width: 14px;
	height: 14px;
}

.eb-link--email:hover {
	background: #EEF2FF;
	border-color: #A5B4FC;
}

.eb-orcid-logo {
	fill: #A6CE39;
}

/* ── Status badges (COI & Tenure) ── */
.eb-status-badges {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	margin-top: 10px;
	padding-top: 10px;
	border-top: 1px solid #f0f0f0;
}
.eb-badge {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 3px 10px;
	border-radius: 20px;
	font-size: 0.72rem;
	font-weight: 600;
	letter-spacing: 0.02em;
}
.eb-badge--coi-ok {
	background: #f0fff4;
	color: #276749;
	border: 1px solid #c6f6d5;
}
.eb-badge--coi-pending {
	background: #fffbeb;
	color: #92400e;
	border: 1px solid #fde68a;
}
.eb-badge--active {
	background: #dcfce7;
	color: #166534;
	border: 1px solid #86efac;
}
.eb-badge--warning {
	background: #fef3c7;
	color: #92400e;
	border: 1px solid #fde68a;
	animation: eb-pulse-warning 2s ease-in-out infinite;
}
@keyframes eb-pulse-warning { 0%,100%{opacity:1} 50%{opacity:.7} }
.eb-badge--expired {
	background: #fee2e2;
	color: #991b1b;
	border: 1px solid #fecaca;
}

/* ── Report false claim — card footer ── */
.eb-card-footer {
	margin-top: 10px;
	padding-top: 8px;
	border-top: 1px solid #f1f5f9;
	text-align: right;
}
.eb-report-link {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 0.72rem;
	color: #94a3b8;
	text-decoration: none;
	transition: color 0.15s;
}
.eb-report-link:hover {
	color: #ef4444;
}
.eb-report-link svg {
	flex-shrink: 0;
}

/* ── Empty state ── */
.eb-empty {
	text-align: center;
	padding: 80px 20px;
	color: var(--eb-text-muted);
}

.eb-empty svg {
	width: 64px;
	height: 64px;
	fill: #CBD5E0;
	margin-bottom: 16px;
}

.eb-empty__text {
	font-size: 1.1rem;
	font-weight: 500;
}

/* ── Footer note ── */
.eb-footer-note {
	text-align: center;
	margin-top: 40px;
	padding-top: 24px;
	border-top: 1px solid #E2E8F0;
	font-size: 0.82rem;
	color: var(--eb-text-muted);
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
}

.eb-footer-note svg {
	width: 16px;
	height: 16px;
}

/* ── Responsive ── */
@media (max-width: 768px) {
	.eb-grid {
		grid-template-columns: 1fr;
	}
	.eb-header__title {
		font-size: 1.6rem;
	}
	.eb-card {
		padding: 20px;
	}
	.eb-card__top {
		gap: 14px;
	}
	.eb-avatar-wrap {
		width: 70px;
		height: 70px;
	}
	.eb-avatar-wrap--eic {
		width: 80px;
		height: 80px;
	}
	.eb-avatar__photo {
		width: 58px;
		height: 58px;
	}
	.eb-avatar-wrap--eic .eb-avatar__photo {
		width: 66px;
		height: 66px;
	}
	.eb-avatar__initials {
		width: 58px;
		height: 58px;
		font-size: 1.2rem;
	}
	.eb-avatar-wrap--eic .eb-avatar__initials {
		width: 66px;
		height: 66px;
		font-size: 1.4rem;
	}
	.eb-arc-text {
		display: none;
	}
	.eb-role-badge-mobile {
		display: inline-flex;
	}
}

/* ── CSS Tooltips ── */
[data-tooltip] {
	position: relative;
	cursor: help;
}
[data-tooltip]::after {
	content: attr(data-tooltip);
	position: absolute;
	bottom: calc(100% + 8px);
	left: 50%;
	transform: translateX(-50%);
	background: #1e293b;
	color: #fff;
	font-size: 0.72rem;
	font-weight: 500;
	line-height: 1.4;
	padding: 6px 12px;
	border-radius: 8px;
	white-space: nowrap;
	max-width: 260px;
	white-space: normal;
	text-align: center;
	pointer-events: none;
	opacity: 0;
	transition: opacity 0.2s;
	z-index: 100;
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
[data-tooltip]::before {
	content: '';
	position: absolute;
	bottom: calc(100% + 2px);
	left: 50%;
	transform: translateX(-50%);
	border: 5px solid transparent;
	border-top-color: #1e293b;
	pointer-events: none;
	opacity: 0;
	transition: opacity 0.2s;
	z-index: 100;
}
[data-tooltip]:hover::after,
[data-tooltip]:hover::before {
	opacity: 1;
}

/* ── Walkthrough overlay ── */
.eb-walkthrough-overlay {
	position: fixed;
	top: 0; left: 0; right: 0; bottom: 0;
	background: rgba(0,0,0,0.55);
	z-index: 10000;
	display: flex;
	align-items: center;
	justify-content: center;
}
.eb-walkthrough-card {
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 20px 60px rgba(0,0,0,0.3);
	max-width: 460px;
	width: 90%;
	padding: 0;
	overflow: hidden;
	animation: eb-wt-in 0.3s ease-out;
}
@keyframes eb-wt-in { from { transform: translateY(30px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
.eb-wt-header {
	background: linear-gradient(135deg, #4f46e5, #7c3aed);
	padding: 24px 28px;
	color: #fff;
}
.eb-wt-header h3 {
	margin: 0 0 4px;
	font-size: 1.15rem;
	font-weight: 700;
}
.eb-wt-header p {
	margin: 0;
	font-size: 0.85rem;
	opacity: 0.85;
}
.eb-wt-body {
	padding: 24px 28px;
}
.eb-wt-step {
	display: none;
}
.eb-wt-step.eb-wt-active {
	display: block;
}
.eb-wt-step-icon {
	width: 48px;
	height: 48px;
	background: #eef2ff;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 14px;
}
.eb-wt-step-icon svg {
	width: 24px;
	height: 24px;
	stroke: #4f46e5;
}
.eb-wt-step h4 {
	margin: 0 0 8px;
	font-size: 1rem;
	font-weight: 700;
	color: #0f172a;
}
.eb-wt-step p {
	margin: 0;
	font-size: 0.88rem;
	color: #475569;
	line-height: 1.6;
}
.eb-wt-footer {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px 28px;
	border-top: 1px solid #f1f5f9;
}
.eb-wt-dots {
	display: flex;
	gap: 6px;
}
.eb-wt-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #e2e8f0;
	transition: background 0.2s;
}
.eb-wt-dot.eb-wt-dot--active {
	background: #4f46e5;
}
.eb-wt-btns {
	display: flex;
	gap: 8px;
}
.eb-wt-btn {
	padding: 8px 18px;
	border-radius: 8px;
	border: none;
	font-size: 0.85rem;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.15s;
}
.eb-wt-btn--skip {
	background: #f1f5f9;
	color: #64748b;
}
.eb-wt-btn--skip:hover {
	background: #e2e8f0;
}
.eb-wt-btn--next {
	background: #4f46e5;
	color: #fff;
}
.eb-wt-btn--next:hover {
	background: #4338ca;
}

/* ── Show tutorial link ── */
.eb-tutorial-link {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	color: #6366f1;
	font-size: 0.82rem;
	font-weight: 600;
	cursor: pointer;
	text-decoration: none;
	padding: 4px 10px;
	border-radius: 6px;
	border: 1px solid #e0e7ff;
	transition: all 0.15s;
	margin-left: 10px;
}
.eb-tutorial-link:hover {
	background: #eef2ff;
	border-color: #c7d2fe;
}
{/literal}
</style>

<div class="eb-page">

	<div class="eb-header">
		<h1 class="eb-header__title">{translate key="plugins.generic.orcidEditorialBoard.pageTitle"}</h1>
		{if $journalName}
			<p class="eb-header__journal">{$journalName|escape}</p>
		{/if}
		<hr class="eb-header__divider">
	</div>

	{if !$orcidConfigured}
		<div class="eb-notice">{translate key="plugins.generic.orcidEditorialBoard.notice.orcidNotConfigured"}</div>
	{/if}

	{if $totalMembers > 0}
		<div class="eb-auditor-box">
			<div class="eb-auditor-box__icon">
				<svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" fill="none" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div class="eb-auditor-box__content">
				<h3 class="eb-auditor-box__title">{translate key="plugins.generic.orcidEditorialBoard.auditorNotice.title"}</h3>
				<p class="eb-auditor-box__text">{translate key="plugins.generic.orcidEditorialBoard.auditorNotice.body"}</p>
			</div>
		</div>

		{if $totalCountries > 0}
			<div class="eb-diversity">
				<div class="eb-diversity__header">
					<h3 class="eb-diversity__title">{translate key="plugins.generic.orcidEditorialBoard.diversity.title"}</h3>
					<span class="eb-diversity__count">{translate key="plugins.generic.orcidEditorialBoard.diversity.count" total=$totalCountries}</span>
				</div>
				<ul class="eb-diversity__list">
					{foreach from=$countryStats item=stat}
						{assign var=code value=$stat.code}
						{assign var=countryName value=$countryNames[$code]|default:$code}
						<li class="eb-diversity__item">
							<div class="eb-diversity__label">
								<span>{$countryName|escape} ({$code|escape})</span>
								<span>- {$stat.count|escape}</span>
							</div>
							<div class="eb-diversity__bar">
								<div class="eb-diversity__bar-fill" style="width:{$stat.percent|escape}%"></div>
							</div>
						</li>
					{/foreach}
				</ul>
			</div>
		{/if}

		{foreach from=$groupedMembers key=roleName item=roleMembers}
			{if $roleName == 'Editor in Chief'}
				{assign var="roleKey" value="eic"}
			{elseif $roleName == 'Managing Editor'}
				{assign var="roleKey" value="me"}
			{elseif $roleName == 'Associate Editor'}
				{assign var="roleKey" value="ae"}
			{else}
				{assign var="roleKey" value="default"}
			{/if}

			<section class="eb-role-section">
				<div class="eb-role-header">
					<h2 class="eb-role-title">{$roleName|escape}</h2>
					<span class="eb-role-count">{$roleMembers|@count}</span>
				</div>

				<div class="eb-grid{if $roleKey == 'eic'} eb-grid--eic{/if}">
					{foreach from=$roleMembers item=member}
						<div class="eb-card eb-card--{$roleKey}" data-member-id="{$member->getId()}">
							<div class="eb-card__top">

								{* ── Avatar with SVG arc role text ── *}
								<div class="eb-avatar-wrap eb-avatar-wrap--{$roleKey}">
									<svg class="eb-avatar-ring" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
										<circle class="eb-avatar-ring__circle" cx="70" cy="70" r="66"/>
										<path id="ebArc_{$member->getId()}" d="M 16,70 A 54,54 0 0,1 124,70" fill="none" stroke="none"/>
										<text class="eb-arc-text eb-arc-text--{$roleKey}">
											<textPath href="#ebArc_{$member->getId()}" startOffset="50%" text-anchor="middle">{$roleName|upper|escape}</textPath>
										</text>
									</svg>

									{if $member->getPhotoUrl()}
										<img class="eb-avatar__photo" src="{$member->getPhotoUrl()|escape}" alt="{$member->getFullName()|escape}">
									{else}
										{assign var="initials" value=$member->getFullName()|regex_replace:"/^(.).* (.).*$/":"$1$2"|regex_replace:"/^(.)..+$/":"$1"|upper}
										<div class="eb-avatar__initials eb-avatar__initials--{$roleKey}">{$initials}</div>
									{/if}

									{if $member->getOrcidVerified()}
										<div class="eb-verified-tick" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.verifiedTooltip'}">
											<svg viewBox="0 0 24 24"><path d="M20.24 12.24a6 6 0 01-8.49 8.49L12 21l-.24-.27a6 6 0 01-8.49-8.49L3 12l.27-.24a6 6 0 018.49-8.49L12 3l.24.27a6 6 0 018.49 8.49L21 12z" fill="#FFF" stroke="#FFF" stroke-width="0"/><path d="M9 12l2 2 4-4" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
										</div>
									{/if}
								</div>

								{* ── Info block with metrics ── *}
								<div class="eb-info">
									<div class="eb-info__name-row">
										<h3 class="eb-info__name">{$member->getFullName()|escape}</h3>
										<span class="eb-role-badge-mobile eb-role-badge-mobile--{$roleKey}">{$roleName|escape}</span>
									</div>

									{if $member->getAffiliation()}
										<p class="eb-info__affiliation">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
											{$member->getAffiliation()|escape}
										</p>
									{/if}

									{assign var="stats" value=$member->getData('openalexStats')}
									{if $stats.works || $stats.citations || $stats.h_index}
										<div class="eb-info__metrics">
											{if $stats.works}<span class="eb-metric-pill">{translate key="plugins.generic.orcidEditorialBoard.openalex.works"}: {$stats.works|escape}</span>{/if}
											{if $stats.citations}<span class="eb-metric-pill">{translate key="plugins.generic.orcidEditorialBoard.openalex.citedBy"}: {$stats.citations|escape}</span>{/if}
											{if $stats.h_index !== null}<span class="eb-metric-pill">{translate key="plugins.generic.orcidEditorialBoard.openalex.hIndex"}: {$stats.h_index|escape}</span>{/if}
										</div>
									{/if}
								</div>
							</div>

							{* ── Verification status ── *}
							<div class="eb-verification">
								{* Show "Pending confirmation" badge during dispute window *}
								{if $member->getDisputeExpiresAt() && $member->getDisputeExpiresAt()|strtotime > $smarty.now}
									<span class="eb-status eb-status--dispute">
										<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><circle cx="12" cy="16" r="0.5" fill="#d97706"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.disputePending"}
									</span>
								{elseif $member->getOrcidVerified() && $member->getOrcidId()}
									<span class="eb-status eb-status--verified">
										<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.verifiedViaOrcid"}
									</span>
									{if $member->getConsentFingerprint()}
										<span class="eb-consent-id" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.consentIdTooltip'}">
											<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
											{translate key="plugins.generic.orcidEditorialBoard.consentIdLabel"}: <code>{$member->getConsentFingerprint()|escape}</code>
										</span>
									{/if}
									<div class="eb-verify-actions">
										{assign var="memberSig" value=$verifySigs[$member->getId()]}
										<a class="eb-verify-btn" href="{url page='editorialBoard' op='verify' memberId=$member->getId() sig=$memberSig}" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.verifyNowTooltip'}">
											<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
											{translate key="plugins.generic.orcidEditorialBoard.verifyNow"}
										</a>
										<a class="eb-verify-link" href="{$member->getOrcidId()|escape}" target="_blank" rel="noopener" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.verifyOnOrcidTooltip'}">
											<svg viewBox="0 0 256 256"><path class="eb-orcid-logo" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z"/><path fill="#FFF" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.5-107.1h41.6c39.6 0 57 28.3 57 53.6 0 27.5-21.5 53.5-56.8 53.5h-41.8V79.1zm15.4 93.3h24.5c34.9 0 42.9-26.5 42.9-39.7C191.6 111 176 92.9 150.4 92.9h-26.2v79.5zM108.9 55.6c0 5.7-4.5 10.3-10.2 10.3s-10.2-4.6-10.2-10.3c0-5.7 4.5-10.3 10.2-10.3s10.2 4.6 10.2 10.3z"/></svg>
											{translate key="plugins.generic.orcidEditorialBoard.verifyOnOrcid"}
										</a>
									</div>
								{else}
									<span class="eb-status eb-status--unverified">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.pendingVerification"}
									</span>
								{/if}

							</div>

							{* ── Keywords (show up to 6) ── *}
							{assign var="keywords" value=$member->getOpenalexKeywords()}
							{if $keywords}
								<div class="eb-keywords-compact">
									{foreach from=$keywords item=kw name=kwloop}
										{if $smarty.foreach.kwloop.index < 6}
											<span class="eb-keyword-tag">{$kw|escape}</span>
										{/if}
									{/foreach}
								</div>
							{/if}

							{* ── Profile links (with email) ── *}
							{if $member->getEmail() || $member->getOrcidId() || $member->getScopusId() || $member->getGoogleScholar()}
								<div class="eb-links">
									{if $member->getEmail()}
										<a class="eb-link eb-link--email" href="mailto:{$member->getEmail()|escape}" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.tooltip.email'}">
											<svg viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg>
											Email
										</a>
									{/if}
									{if $member->getOrcidId()}
										<a class="eb-link" href="{$member->getOrcidId()|escape}" target="_blank" rel="noopener">
											<svg viewBox="0 0 256 256"><path class="eb-orcid-logo" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z"/><path fill="#FFF" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.5-107.1h41.6c39.6 0 57 28.3 57 53.6 0 27.5-21.5 53.5-56.8 53.5h-41.8V79.1zm15.4 93.3h24.5c34.9 0 42.9-26.5 42.9-39.7C191.6 111 176 92.9 150.4 92.9h-26.2v79.5zM108.9 55.6c0 5.7-4.5 10.3-10.2 10.3s-10.2-4.6-10.2-10.3c0-5.7 4.5-10.3 10.2-10.3s10.2 4.6 10.2 10.3z"/></svg>
											ORCID
										</a>
									{/if}
									{if $member->getScopusId()}
										<a class="eb-link" href="https://www.scopus.com/authid/detail.uri?authorId={$member->getScopusId()|escape}" target="_blank" rel="noopener">
											<svg viewBox="0 0 24 24" fill="none" stroke="#E9711C" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
											Scopus
										</a>
									{/if}
									{if $member->getGoogleScholar()}
										{if $member->getGoogleScholar()|strstr:"http"}
											{assign var="gsUrl" value=$member->getGoogleScholar()}
										{else}
											{assign var="gsUrl" value="https://scholar.google.com/citations?user=`$member->getGoogleScholar()`"}
										{/if}
										<a class="eb-link" href="{$gsUrl|escape}" target="_blank" rel="noopener">
											<svg viewBox="0 0 24 24" fill="none" stroke="#4285F4" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
											Google Scholar
										</a>
									{/if}
								</div>
							{/if}

							{* ── Status badges (COI + Tenure) ── *}
							<div class="eb-status-badges">
								{if $member->getCoiStatus() == 'declared'}
									<span class="eb-badge eb-badge--coi-ok" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.coi.declaredTooltip'}">
										<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.coi.badge"}
									</span>
								{else}
									<span class="eb-badge eb-badge--coi-pending" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.coi.pendingTooltip'}">
										{translate key="plugins.generic.orcidEditorialBoard.coi.badgePending"}
									</span>
								{/if}

								{assign var="tenureUrgency" value=$member->getData('tenureUrgency')}
								{if $tenureUrgency == 'expired'}
									<span class="eb-badge eb-badge--expired" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.tenure.expiredTooltip'}">
										<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.tenure.expired"}
									</span>
								{elseif $tenureUrgency == 'warning'}
									<span class="eb-badge eb-badge--warning" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.tenure.warningTooltip'}">
										<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.tenure.nearExpiry"}
										{if $member->getTenureEnd()}
											&middot; {translate key="plugins.generic.orcidEditorialBoard.tenure.until"} {$member->getTenureEnd()|escape}
										{/if}
									</span>
								{elseif $tenureUrgency == 'active'}
									<span class="eb-badge eb-badge--active" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.tenure.activeTooltip'}">
										<svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.tenure.active"}
										{if $member->getTenureEnd()}
											&middot; {translate key="plugins.generic.orcidEditorialBoard.tenure.until"} {$member->getTenureEnd()|escape}
										{/if}
									</span>
								{/if}
							</div>

							{* ── Report false claim — card footer ── *}
							{if $member->getOrcidId()}
								<div class="eb-card-footer">
									<a class="eb-report-link" href="{url page='editorialBoard' op='reportFalseClaim' memberId=$member->getId() sig=$reportSigs[$member->getId()]}" data-tooltip="{translate key='plugins.generic.orcidEditorialBoard.reportFalseClaim.tooltip'}">
										<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
										{translate key="plugins.generic.orcidEditorialBoard.reportFalseClaim.link"}
									</a>
								</div>
							{/if}

						</div>
					{/foreach}
				</div>
			</section>
		{/foreach}

		<div class="eb-footer-note">
			<a class="eb-tutorial-link" id="ebShowTutorial" href="#">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
				{translate key="plugins.generic.orcidEditorialBoard.tutorial.showLink"}
			</a>
			<svg viewBox="0 0 256 256"><path class="eb-orcid-logo" d="M256 128c0 70.7-57.3 128-128 128S0 198.7 0 128 57.3 0 128 0s128 57.3 128 128z"/><path fill="#FFF" d="M86.3 186.2H70.9V79.1h15.4v107.1zm22.5-107.1h41.6c39.6 0 57 28.3 57 53.6 0 27.5-21.5 53.5-56.8 53.5h-41.8V79.1zm15.4 93.3h24.5c34.9 0 42.9-26.5 42.9-39.7C191.6 111 176 92.9 150.4 92.9h-26.2v79.5zM108.9 55.6c0 5.7-4.5 10.3-10.2 10.3s-10.2-4.6-10.2-10.3c0-5.7 4.5-10.3 10.2-10.3s10.2 4.6 10.2 10.3z"/></svg>
			{translate key="plugins.generic.orcidEditorialBoard.footerNote"}
		</div>

	{else}
		<div class="eb-empty">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
			<p class="eb-empty__text">{translate key="plugins.generic.orcidEditorialBoard.none"}</p>
		</div>
	{/if}

</div>

{* ── Walkthrough Tutorial ── *}
<div class="eb-walkthrough-overlay" id="ebWalkthrough" style="display:none">
	<div class="eb-walkthrough-card">
		<div class="eb-wt-header">
			<h3>{translate key="plugins.generic.orcidEditorialBoard.tutorial.title"}</h3>
			<p>{translate key="plugins.generic.orcidEditorialBoard.tutorial.subtitle"}</p>
		</div>
		<div class="eb-wt-body">
			<div class="eb-wt-step eb-wt-active" data-step="0">
				<div class="eb-wt-step-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
				</div>
				<h4>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step1.title"}</h4>
				<p>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step1.body"}</p>
			</div>
			<div class="eb-wt-step" data-step="1">
				<div class="eb-wt-step-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
				</div>
				<h4>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step2.title"}</h4>
				<p>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step2.body"}</p>
			</div>
			<div class="eb-wt-step" data-step="2">
				<div class="eb-wt-step-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
				</div>
				<h4>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step3.title"}</h4>
				<p>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step3.body"}</p>
			</div>
			<div class="eb-wt-step" data-step="3">
				<div class="eb-wt-step-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
				</div>
				<h4>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step4.title"}</h4>
				<p>{translate key="plugins.generic.orcidEditorialBoard.tutorial.step4.body"}</p>
			</div>
		</div>
		<div class="eb-wt-footer">
			<div class="eb-wt-dots">
				<span class="eb-wt-dot eb-wt-dot--active" data-dot="0"></span>
				<span class="eb-wt-dot" data-dot="1"></span>
				<span class="eb-wt-dot" data-dot="2"></span>
				<span class="eb-wt-dot" data-dot="3"></span>
			</div>
			<div class="eb-wt-btns">
				<button class="eb-wt-btn eb-wt-btn--skip" id="ebWtSkip">{translate key="plugins.generic.orcidEditorialBoard.tutorial.skip"}</button>
				<button class="eb-wt-btn eb-wt-btn--next" id="ebWtNext">{translate key="plugins.generic.orcidEditorialBoard.tutorial.next"}</button>
			</div>
		</div>
	</div>
</div>

<script>
{literal}
(function() {
	var WT_KEY = 'eb_walkthrough_seen';
	var overlay = document.getElementById('ebWalkthrough');
	var steps = overlay.querySelectorAll('.eb-wt-step');
	var dots = overlay.querySelectorAll('.eb-wt-dot');
	var nextBtn = document.getElementById('ebWtNext');
	var skipBtn = document.getElementById('ebWtSkip');
	var currentStep = 0;
	var totalSteps = steps.length;

	function showStep(n) {
		steps.forEach(function(s) { s.classList.remove('eb-wt-active'); });
		dots.forEach(function(d) { d.classList.remove('eb-wt-dot--active'); });
		steps[n].classList.add('eb-wt-active');
		dots[n].classList.add('eb-wt-dot--active');
		nextBtn.textContent = (n === totalSteps - 1) ? 'Got it!' : 'Next →';
	}

	function closeWalkthrough() {
		overlay.style.display = 'none';
		try { localStorage.setItem(WT_KEY, '1'); } catch(e) {}
	}

	nextBtn.addEventListener('click', function() {
		if (currentStep < totalSteps - 1) { currentStep++; showStep(currentStep); }
		else { closeWalkthrough(); }
	});

	skipBtn.addEventListener('click', closeWalkthrough);
	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) closeWalkthrough();
	});

	// Show on first visit
	try {
		if (!localStorage.getItem(WT_KEY)) {
			overlay.style.display = 'flex';
		}
	} catch(e) {
		// localStorage not available — skip
	}

	// Manual trigger
	var showLink = document.getElementById('ebShowTutorial');
	if (showLink) {
		showLink.addEventListener('click', function(e) {
			e.preventDefault();
			currentStep = 0;
			showStep(0);
			overlay.style.display = 'flex';
		});
	}
})();
{/literal}
</script>


{include file="frontend/components/footer.tpl"}
