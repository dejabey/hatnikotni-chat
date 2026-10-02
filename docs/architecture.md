# Hatnikotni Chat Architecture

## Core modules

- Settings
- Contacts
- Routing
- Analytics
- Privacy and consent
- Campaign attribution
- WhatsApp action/URL layer
- Shortcode
- Admin

## Frontend interfaces

1. Global floating WhatsApp button.
2. Shortcode: [hatnikotni_chat].
3. Small shield-shaped privacy icon immediately left of the floating WhatsApp button; it opens a compact consent card.

The WhatsApp action remains a normal link to the public WordPress admin-post.php action. The handler resolves a contact, records a click only when analytics consent is available, then redirects to https://wa.me/<number>. The privacy panel uses a small local JavaScript file; no external JavaScript or analytics service is required.

## Privacy and consent

The global floating widget exposes a small shield-shaped privacy icon. Selecting it opens a compact consent card with a Reject/Accept switch, a Privacy Policy link when configured, and a Read More disclosure. The expanded explanation uses justified text and the card uses compact horizontal padding.

The native UI stores the explicit choice in the first-party hatnch_analytics_consent cookie for up to 180 days. The value is yes only after the visitor selects Accept. A no value or missing cookie means analytics remains disabled. Visitors can reopen the card and change their choice. The WhatsApp link itself is never gated by this choice.

When a visitor rejects consent, the browser posts to the same-origin `admin-post.php` action `hatnch_revoke_campaign`. The public handler only expires that visitor's own HttpOnly attribution cookie and returns HTTP 204 without rendering a page. If the request fails, campaign capture also expires the cookie on the next request while consent remains rejected.

The filter hatnch_has_analytics_consent receives the native cookie-derived value (false by default) and remains available for deliberate site-level integrations.

When analytics consent is absent:
- WhatsApp click events are not recorded.
- Campaign attribution is not read or captured.
- Existing campaign cookie is expired where possible.
- WhatsApp routing continues normally.

The plugin adds suggested privacy-policy content through WordPress wp_add_privacy_policy_content().

## Data

V1 uses two custom tables:
- {$wpdb->prefix}hatnch_contacts
- {$wpdb->prefix}hatnch_events

WordPress database prefix and charset/collation are obtained from WordPress APIs. The schema version is stored in hatnch_db_version. Deactivation preserves data.

## Contacts and routing

Contacts are persistent entities; historical contacts are deactivated rather than deleted. Phone numbers are stored as digits only. Weight is reserved for future weighted routing and is not used by V1.

Routing methods:
- direct — configured default contact, which must be active.
- random — uniformly selects an active contact.
- round_robin — deterministic sort_order then ID order; stores the last selected contact.

If no valid active contact can be resolved, routing returns null.

## Analytics

The primary event is whatsapp_click. Analytics is first-party and local. It is recorded immediately before the WhatsApp redirect only when consent is available. It indicates a click, not confirmation that a WhatsApp message was sent.

Stored fields are limited to contact, page, broad device category and supported UTM attribution. IP addresses, visitor identity, full user-agent, fingerprints, conversation content, browsing history and visitor IDs are not stored intentionally. Events are deleted after 180 days by daily WP-Cron.

## Campaign attribution

Supported parameters: utm_source, utm_medium, utm_campaign, utm_term and utm_content. Last-touch attribution uses the first-party hatnch_campaign cookie for up to 30 days and only while analytics consent is available.

## Shortcode

[hatnikotni_chat] accepts optional label and message attributes and generates the same public WhatsApp action URL. The global floating widget provides the native Privacy choices interface; shortcode use remains functional without analytics consent.

## WordPress.org readiness

The plugin uses GPL-compatible licensing, WordPress-native APIs, prefixed declarations/storage, consent-aware analytics and a clean release workflow. GitHub Actions build #571 passed PHP 8.1–8.5 validation and production packaging on 2026-10-02 (artifact ID `11238156392`). Branch changes after #549 are documentation-only; plugin source is unchanged from #549. The user confirmed build #549 is installed on staging; post-install runtime tests remain pending. On 2026-10-02, the user supplied a staging Plugin Check screenshot showing “Checks complete. No errors found.” with Error and Warning selected and AI Analysis unchecked. The screenshot does not identify the installed plugin build, so record that correlation separately. See `docs/wordpress-org-readiness.md` and `docs/staging-test-plan.md`.
