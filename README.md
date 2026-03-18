# Editorial Board ORCID Verification (OJS Plugin)

Editorial Board ORCID Verification is a generic plugin for Open Journal Systems (OJS) that helps journals manage editorial board records with ORCID-backed identity verification, consent workflows, COI declarations, and dispute reporting.

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

## Compatibility

- **OJS:** 3.4.x
- **Plugin type:** `plugins.generic`
- **Tested on:** OJS 3.4.0-8

## Installation

1. Download a release archive (e.g. [orcidEditorialBoard-v1.6.5.0.tar.gz](https://github.com/user-attachments/files/26099683/orcidEditorialBoard-v1.6.5.0.tar.gz)
).
2. In OJS, go to: **Dashboard → Settings → Website → Plugins → Upload A New Plugin**.
3. Upload the `.tar.gz` and install.
4. Enable **Editorial Board ORCID Verification** from the plugin list.
5. Open plugin **Settings** and set ORCID credentials.

## Manual Installation

Extract the plugin to:

`plugins/generic/orcidEditorialBoard`

Then clear caches and enable the plugin in OJS.

## Configuration Notes

- Register your ORCID app and set the callback URL shown in plugin settings.
- Use production ORCID credentials for live journals.
- Keep HMAC secret configured in plugin settings/environment for secure action links.

## Security

If you discover a security issue, please report it privately to:

- **support@peers.ae**

## License

This plugin is distributed under the **GNU General Public License v3.0**.
See [LICENSE](LICENSE).
