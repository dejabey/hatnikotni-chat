# Changelog

All notable changes to Hatnikotni Chat will be documented here.

## Unreleased

### Added
- Contact CRUD storage and an initial Contacts admin interface with capability and nonce protection.
- Direct, random and round-robin contact routing.
- WordPress-option-backed round-robin routing state.
- Database upgrade check tied to `HKC_DB_VERSION`.
- Shared WhatsApp action/URL layer using a public WordPress action endpoint.
- WhatsApp click analytics recording before redirect.
- Last-touch UTM campaign cookie capture.
- Global floating WhatsApp button with inline SVG.
- `[hatnikotni_chat]` shortcode with optional label/message attributes.
- Accessible namespaced frontend button styles.
- Private GitHub repository and initial project documentation.
- Plugin skeleton with Hatnikotni naming/prefix conventions.
- Activation/deactivation lifecycle.
- Initial contacts and events database schema installers.
- Uninstall handler with WordPress uninstall guard.
- GitHub Actions PHP syntax validation.
- Contract coverage for Contact CRUD, routing, database upgrades, WhatsApp action, campaign attribution and analytics.
- AI development protocol and current project handoff.

### Validation
- Latest source and contract changes require fresh GitHub Actions confirmation.
- Runtime/staging acceptance has not started.
