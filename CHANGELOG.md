# Changelog

## Unreleased

- Added General admin settings for frontend enablement, default contact/message, button label, position, desktop/mobile visibility, and routing method.
- Added Analytics admin reporting for 7/30/90/180-day periods with device, contact and campaign breakdowns.
- Added daily 180-day analytics retention cleanup.
- Added frontend handling for button position and visibility settings.
- Hardened WhatsApp redirect handling when a routed contact has no valid phone.
- Extended contract checks for analytics reporting and retention cleanup.

## 0.1.0

- Initial development release.
- Contact CRUD and activation/deactivation.
- Direct, random and round-robin contact routing.
- Database schema/version upgrade foundation.
- Shared WhatsApp action endpoint.
- Local WhatsApp click analytics.
- 30-day UTM campaign attribution.
- Global floating button and `[hatnikotni_chat]` shortcode.
