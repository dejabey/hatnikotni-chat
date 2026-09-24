# Changelog

## Unreleased

- Confirmed GitHub Actions PHP 8.1–8.4 matrix is fully green for syntax, contracts and WPCS.
- Staging direct routing verified with international WhatsApp number format; default-deny analytics produced no event without consent.
- Staging UI refinement planned: modernize Hatnikotni Chat admin submenus with clearer layout and contextual field guidance before release.

- Completed WPCS source cleanup across admin, analytics, contacts, routing, settings, campaign, WhatsApp and plugin bootstrap layers.
- Hardened custom-table queries with prepared identifiers and sanitized device detection input.
- Confirmed latest CI validation passes PHP syntax, contract checks and WordPress Coding Standards.

- Audited admin, analytics-admin, WhatsApp, shortcode, routing, contact storage, uninstall and frontend CSS layers for WordPress.org/WPCS readiness.
- Tightened admin markup/source formatting and restored distinct settings/contact success notices.

- Added consent-aware visitor analytics with a default-deny consent filter.
- Added WordPress Privacy Policy Guide integration.
- Made UTM campaign attribution and the hkc_campaign cookie consent-aware, including cookie clearing when consent is absent.
- Added WordPress.org readme and readiness checklist.
- Added Composer-based WordPress Coding Standards tooling and CI validation.
- Added source contracts for privacy, consent and WordPress.org readme requirements.
- Source audit fixed uninstall cleanup for the round-robin state option and restored contact save error notices.
- Added General admin settings for frontend enablement, default contact/message, button label, position, desktop/mobile visibility, and routing method.
- Added Analytics admin reporting for 7/30/90/180-day periods with device, contact and campaign breakdowns.
- Added daily 180-day analytics retention cleanup.
- Added frontend handling for button position and visibility settings.
- Hardened WhatsApp redirect handling when a routed contact has no valid phone.

## 0.1.0

- Initial development release.
- Contact CRUD and activation/deactivation.
- Direct, random and round-robin contact routing.
- Database schema/version upgrade foundation.
- Shared WhatsApp action endpoint.
- Local WhatsApp click analytics.
- 30-day UTM campaign attribution.
- Global floating button and [hatnikotni_chat] shortcode.
