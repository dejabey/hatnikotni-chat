# Hatnikotni Chat Architecture

## Core modules

- Settings
- Contacts
- Routing
- Analytics
- Privacy and consent integration
- Campaign attribution
- WhatsApp action/URL layer
- Shortcode
- Admin

## Frontend interfaces

Hatnikotni Chat provides two V1 interfaces:

1. Global floating WhatsApp button
2. Shortcode: [hatnikotni_chat]

Both interfaces use the same routing, campaign attribution, analytics and WhatsApp URL/action logic.

The frontend action is a normal link to a public WordPress admin-post.php action. The handler resolves the contact, records the click when analytics consent is available, then redirects to https://wa.me/<number>. No frontend JavaScript or external analytics request is required.

## Privacy and consent

Analytics is opt-in at the visitor level.

The plugin exposes the filter:

hatc_has_analytics_consent

The default value is false. A site or consent-management integration must return true only after an explicit visitor consent signal has been granted.

When consent is absent:

- WhatsApp click events are not recorded.
- The hatc_campaign cookie is not set or read for attribution.
- The WhatsApp button and routing continue to work normally.

The plugin does not ship a consent banner. This avoids taking over the site's consent UI and allows the site owner to use its existing consent-management mechanism.

The plugin adds suggested privacy-policy content using WordPress wp_add_privacy_policy_content().

## Data

V1 uses two custom tables:

- {$wpdb->prefix}hatc_contacts
- {$wpdb->prefix}hatc_events

WordPress database prefix and charset/collation are always obtained from WordPress APIs.

The plugin records its schema version in hatc_db_version and checks for schema upgrades during plugin initialization. Deactivation preserves data.

## Contacts

Contacts are persistent entities. Historical contacts are deactivated rather than deleted.

- Phone numbers are stored as digits only.
- weight is stored for future weighted routing but is not used by V1 routing.
- sort_order controls deterministic contact ordering and is not routing priority.

## Routing

V1 routing methods:

- direct — uses the configured default contact and requires that contact to be active.
- random — selects one active contact uniformly.
- round_robin — selects active contacts in deterministic sort_order, then id order and stores the last selected contact in a WordPress option.

If no valid active contact can be resolved, routing returns null.

## Analytics

The primary V1 event is whatsapp_click.

Analytics is first-party and local. The event is recorded immediately before the WhatsApp redirect only when visitor analytics consent is available. It means a button click, not confirmation that a WhatsApp message was sent.

Stored fields are limited to contact, page, device and supported UTM attribution. IP addresses, visitor identity, full user-agent, fingerprints, conversation content, browsing history and visitor IDs are not stored.

Analytics failure must never prevent the WhatsApp redirect.

Analytics events are automatically cleaned after 180 days by daily WP-Cron.

## Campaign attribution

UTM parameters supported:

- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

V1 uses last-touch attribution with a first-party hatc_campaign cookie for 30 days, only after analytics consent is available. A new UTM-bearing visit replaces the previous attribution.

## Shortcode

[hatnikotni_chat] accepts optional:

- label
- message

The shortcode generates the same public WhatsApp action URL used by the global button.

## Extension points

WordPress actions/filters are preferred for internal extensibility. The core does not require external services.

Future integrations may include Webhook, WooCommerce, CRM, weighted/context routing, and WhatsApp Business API support without making them dependencies of the core plugin.

## WordPress.org readiness

The plugin is designed around the WordPress.org requirements for GPL-compatible licensing, human-readable code, privacy-aware tracking, WordPress-native libraries/APIs, versioned releases, and a complete release package.

The repository's readme.txt is intended for the WordPress.org Plugin Directory. GitHub documentation remains the source-control and development documentation.
