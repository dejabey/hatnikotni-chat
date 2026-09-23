# Hatnikotni Chat Architecture

## Core modules

- Settings
- Contacts
- Routing
- Analytics
- Campaign attribution
- WhatsApp action/URL layer
- Shortcode
- Admin

## Frontend interfaces

Hatnikotni Chat provides two V1 interfaces:

1. Global floating WhatsApp button
2. Shortcode: `[hatnikotni_chat]`

Both interfaces use the same routing, campaign attribution, analytics, and WhatsApp URL/action logic.

The frontend action is a normal link to a public WordPress `admin-post.php` action. The handler resolves the contact, records the click locally, then redirects to `https://wa.me/<number>`. No frontend JavaScript or external analytics request is required.

## Data

V1 uses two custom tables:

- `{$wpdb->prefix}hkc_contacts`
- `{$wpdb->prefix}hkc_events`

WordPress database prefix and charset/collation are always obtained from WordPress APIs.

The plugin records its schema version in `hkc_db_version` and checks for schema upgrades during plugin initialization. Deactivation preserves data.

## Contacts

Contacts are persistent entities. Historical contacts are deactivated rather than deleted.

- Phone numbers are stored as digits only.
- `weight` is stored for future weighted routing but is not used by V1 routing.
- `sort_order` controls deterministic contact ordering and is not routing priority.

## Routing

V1 routing methods:

- **direct** — uses the configured default contact and requires that contact to be active.
- **random** — selects one active contact uniformly.
- **round_robin** — selects active contacts in deterministic `sort_order`, then `id` order and stores the last selected contact in a WordPress option.

If no valid active contact can be resolved, routing returns `null`.

## Analytics

The primary V1 event is `whatsapp_click`.

Analytics is first-party and local. The event is recorded immediately before the WhatsApp redirect and means a button click, not confirmation that a WhatsApp message was sent.

Stored fields are limited to contact, page, device and supported UTM attribution. IP addresses, visitor identity, full user-agent, fingerprints, conversation content, browsing history and visitor IDs are not stored.

Analytics failure must never prevent the WhatsApp redirect.

## Campaign attribution

UTM parameters supported:

- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

V1 uses last-touch attribution with a first-party `hkc_campaign` cookie for 30 days. A new UTM-bearing visit replaces the previous attribution.

## Shortcode

`[hatnikotni_chat]` accepts optional:

- `label`
- `message`

The shortcode generates the same public WhatsApp action URL used by the global button.

## Extension points

WordPress actions/filters are preferred for internal extensibility. The core does not require external services.

Future integrations may include Webhook, WooCommerce, CRM, weighted/context routing, and WhatsApp Business API support without making them dependencies of the core plugin.
