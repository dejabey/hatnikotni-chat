# Changelog

## Unreleased

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
