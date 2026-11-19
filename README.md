# Hatnikotni Chat

Standalone WordPress plugin for WhatsApp contact routing, consent-aware first-party interaction analytics, and campaign attribution.

## Project status

Active development. The plugin is being prepared for WordPress.org Plugin Directory standards before a stable release.

## V1 scope

- Global floating WhatsApp button
- Shortcode: [hatnikotni_chat]
- Contact management
- Direct, random, and round-robin routing
- WhatsApp click analytics
- UTM campaign attribution
- 180-day analytics retention
- WordPress Privacy Policy Guide integration
- Extension foundation for Webhook and WooCommerce

## Privacy model

Analytics is opt-in at the visitor level.

Hatnikotni Chat does not provide its own consent banner. Analytics and campaign attribution remain disabled unless the site provides an explicit consent signal through the hatnch_has_analytics_consent filter.

When consent is granted, analytics stores only contact, page, broad device category, and supported UTM attribution in the local WordPress database. The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history, or WhatsApp conversation content.

Analytics events are retained for 180 days. The first-party hatnch_campaign cookie is retained for up to 30 days when consent is granted.

The plugin also adds suggested privacy-policy text through the WordPress Privacy Policy Guide.

## Design principles

- Lightweight runtime
- No external service dependency for core functionality
- Consent-aware first-party analytics
- WordPress-native APIs and hooks
- Secure, accessible, cache-friendly frontend
- Backward-compatible database migrations and namespace migration
- GitHub-managed source and release history
- WordPress.org-ready licensing and documentation

## Development and release

Development source is maintained in GitHub. Stable releases will be reviewed, tested on staging, packaged, and then published to the WordPress.org Plugin Directory.

Production deployment is not automatic.

## Repository

GitHub: dejabey/hatnikotni-chat
