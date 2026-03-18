# Editorial Board ORCID Verification (OJS Plugin)

Editorial Board ORCID Verification is a generic plugin for Open Journal Systems (OJS) focused on **transparency and integrity in editorial board assignment**.

Its core purpose is to help journals prevent cases where editors are listed **without their knowledge or consent**, and to help auditors/indexing-service reviewers quickly detect editorial records that may compromise integrity.

## Maintainer

- **Organization:** Peers Publishing (Dubai, UAE)
- **Lead Developer:** Mohanad G. Yaseen
- **Contact:** support@peers.ae
- **Website:** https://peers.ae

## Features

- ORCID OAuth verification for editorial board identity
- Invitation and consent workflow with tokenized links
- Conflict-of-interest (COI) declaration flow
- Dispute/false-claim reporting with HMAC-signed links
- OpenAlex-assisted discovery/import support
- Editorial board profile metadata and visibility controls
- About modal with developer and support information

## Why this plugin matters

- Promotes **editorial transparency** by requiring consent-backed identity flows.
- Reduces unauthorized editorial listings.
- Provides an auditable trail for disputes and removals.
- Supports research-integrity checks used by indexing and quality-audit services.

## Compatibility

- **OJS:** 3.4.x
- **Plugin type:** `plugins.generic`
- **Tested on:** OJS 3.4.0-8

## Installation

### A) Prerequisite: Set a valid journal contact email (required)

Before using the plugin, set a valid email in OJS:

1. Go to: **Administration → Hosted Journals**
2. Click your journal → **Edit**
3. Ensure **Principal Contact Email** (required field) is set to a real, functional email address
4. Save

This is required so invitation and consent emails can be delivered correctly.

### B) Install from Plugin Gallery upload

1. Download the release archive ([orcidEditorialBoard-v1.6.5.0.tar.gz](https://github.com/user-attachments/files/26100134/orcidEditorialBoard-v1.6.5.0.tar.gz)
  ).
2. In OJS go to: **Dashboard → Settings → Website → Plugins**.
3. Click **Upload A New Plugin**.
4. Upload the `.tar.gz` file.
5. Install and enable **Editorial Board ORCID Verification**.
6. Open plugin **Settings**.

## Manual Installation

If you install manually, extract the plugin to:

`plugins/generic/orcidEditorialBoard`

Then:

1. Clear OJS caches
2. Go to **Website → Plugins**
3. Enable **Editorial Board ORCID Verification**

## ORCID API setup (step by step)

### 1) Create ORCID API credentials

1. Sign in to your ORCID account.
2. Go to ORCID developer tools / API registration.
3. Create a new client application.
4. Add your application name and website details.
5. In redirect URIs, add the exact callback URL shown in plugin settings.
6. Save and copy:
	- **Client ID**
	- **Client Secret**

### 2) Configure in plugin settings

1. In OJS go to: **Website → Plugins → Generic Plugins**.
2. Open **Editorial Board ORCID Verification** → **Settings**.
3. Paste the ORCID **Client ID**.
4. Paste the ORCID **Client Secret**.
5. Confirm the callback/redirect URI in OJS matches your ORCID app registration exactly.
6. Save settings.

### 3) Validate configuration

1. Add or select an editorial board member.
2. Send invitation/verification flow.
3. Complete ORCID authorization as a test user.
4. Confirm verified status and email actions are recorded correctly.

## Operational notes

- Use production ORCID credentials for live journals.
- Keep plugin security/HMAC configuration enabled for signed action links.
- Ensure outgoing mail is working in OJS before starting invitation workflows.

## Security

If you discover a security issue, please report it privately to:

- **support@peers.ae**

## License

This plugin is distributed under the **GNU General Public License v3.0**.
See [LICENSE](LICENSE).
