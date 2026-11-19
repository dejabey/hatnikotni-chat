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
3. Shield badge on the round floating WhatsApp button (concept B, 0.1.29): a 28 px disc inside a 44 px hit area, a separate button that precedes the WhatsApp action in DOM and Tab order; it opens a compact consent card. An optional label chip (`button_show_label`, default on, hidden below 481 px) sits beside the bubble.

The WhatsApp action remains a normal link to the public WordPress admin-post.php action. The handler resolves a contact, records a click only when analytics consent is available, then redirects to https://wa.me/<number>. The privacy panel uses a small local JavaScript file; no external JavaScript or analytics service is required.

## Privacy and consent

The global floating widget exposes a shield badge. Selecting it opens a compact consent card (232 px; since 0.1.29) with a title, a one-line summary, a Reject/Accept segmented control (`button` elements with `aria-pressed`, `data-hatnch-consent="no|yes"`; neither pressed when no choice is saved), a Privacy Policy link when configured, a Details disclosure for the full explanation, and a close button. Escape and the close button return focus to the badge. The stored value is still the `hatnch_analytics_consent` cookie (`yes`/`no`).

Optional greeting card (concept C, 0.1.29, setting `greeting_enabled`, default off): a hidden `<section id="hatnch-greeting">` inside the widget, revealed by `assets/js/hatnikotni-chat-greeting.js` (enqueued only when enabled) once per browser session after `greeting_delay` seconds. State is a single `sessionStorage` flag; no cookie, no analytics, no focus change. Its CTA links to the same click endpoint, or with purpose routing opens the purpose chooser.

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

WordPress database prefix and charset/collation are obtained from WordPress APIs. The schema version is stored in hatnch_db_version. Deactivation preserves data. The migration-hardening branch runs legacy migration before defaults/schema installation, stops without merging when legacy and current tables coexist, verifies rename outcomes, and preserves legacy options until copied values are checked. Before advancing the schema version, the upgrade gate checks that both plugin tables contain all required columns and required named indexes with the expected ordered columns. It does not automatically merge contacts/events because event contact_id values must remain linked to the correct contacts when IDs collide. Build #604 passed per user report, but controlled migration tests—including incomplete-schema and partial-rename/retry cases—remain pending; see docs/staging-test-plan.md.

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

The plugin uses GPL-compatible licensing, WordPress-native APIs, prefixed declarations/storage, consent-aware analytics and a clean release workflow. GitHub Actions build #578 passed PHP 8.1–8.5 validation and production packaging on 2026-10-02 (artifact ID `11238198052`, SHA-256 `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`). PHP/JavaScript runtime source is unchanged from #549, but the packaged `readme.txt` description changed; package contents are therefore not identical. The user reported successful consent/analytics/campaign-cookie runtime tests on the active 0.1.8 staging installation, but the exact installed ZIP has not been correlated to a CI artifact. On 2026-10-02, the user supplied a staging Plugin Check screenshot showing “Checks complete. No errors found.” with Error and Warning selected and AI Analysis unchecked. The screenshot does not identify the installed plugin build, so package correlation remains pending. See `docs/wordpress-org-readiness.md` and `docs/staging-test-plan.md`.
