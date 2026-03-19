# Changelog

All notable changes to this plugin are documented in this file.

## [1.6.7.0] - 2026-03-19

### Added
- "Approve & Confirm Changes" button in profile change notification emails, allowing members to approve non-identity edits and immediately restore their verified badge.
- New `approveEdit` public handler with HMAC-signed URL for secure one-click approval.
- Success page for approved changes with contextual messaging.

### Fixed
- Non-identity field edits (photo, email, etc.) no longer leave the badge stuck on "⏳ Pending confirmation" with no way to resolve — members can now approve or dispute.

## [1.6.6.1] - 2026-03-19

### Fixed
- Client secret field now renders as a visible text input instead of a hidden password field.
- Editorial board page URL is now a clickable hyperlink that opens in a new tab.

## [1.6.6.0] - 2026-03-19

### Added
- Plugin settings now display a generated **Editorial Board Page URL** for easy copy/use.

### Changed
- Expanded settings tutorial to include navigation setup guidance (Remote URL menu item flow).
- Updated README with public editorial page URL and navigation steps.

## [1.6.5.1] - 2026-03-19

### Fixed
- Reduced wizard session footprint by storing a compact OpenAlex payload in staged entries instead of full API responses.
- Added server-side CSRF checks to wizard mutation endpoints (`wizardAdd`, `wizardRemove`, `wizardUpdate`, `wizardFinalize`).
- Improved staged-state sanitization to keep payload/topic/affiliation data bounded and prevent session bloat during bulk additions.

## [1.6.5.0] - 2026-03-18

### Added
- PKP/GitHub publishing preparation files: `README.md`, `CHANGELOG.md`, `.gitignore`, `LICENSE`.
- Attribution and GPL license headers in core plugin files.

### Changed
- About modal now receives a correct web URL for the Peers logo asset.

## [1.6.4.1] - 2026-03-18

### Changed
- Polished About page UI.
- Standardized developer social icons to consistent sizes.
- Replaced placeholder icon with actual Peers Publishing logo in About page.

## [1.6.4.0] - 2026-03-18

### Added
- New About modal page in plugin actions.
- Company/developer attribution and support details.

## [1.6.3.2] - 2026-03-18

### Fixed
- Report false claim flow restored by passing HMAC signature in confirmation step.
- Dispute URL now includes HMAC signature.

## [1.6.3.1] - 2026-03-18

### Fixed
- ORCID name retrieval from person API when token response lacks name.
- Multi-editor notification loop error handling.
- Action callback now persists ORCID verification/auth state before dispatch.

## [1.6.3.0] - 2026-03-18

### Changed
- Unified ORCID callbacks into a single `callback()` dispatcher.
- Centralized token exchange logic.

## [1.6.2.0] - 2026-03-18

### Security
- Implemented comprehensive hardening: HMAC link signing, context checks, null guards, safer error handling, and i18n cleanup.
