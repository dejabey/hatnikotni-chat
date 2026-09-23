# Hatnikotni Chat Architecture

## Core modules

- Settings
- Contacts
- Routing
- Analytics
- Campaign attribution
- Shortcode
- Admin

## Frontend interfaces

Hatnikotni Chat provides two V1 interfaces:

1. Global floating WhatsApp button
2. Shortcode: `[hatnikotni_chat]`

Both interfaces use the same routing, campaign attribution, analytics, and WhatsApp URL generation logic.

## Data

V1 uses two custom tables:

- `{$wpdb->prefix}hkc_contacts`
- `{$wpdb->prefix}hkc_events`

WordPress database prefix and charset/collation are always obtained from WordPress APIs.

## Routing

V1 routing methods:

- direct
- random
- round_robin

Weighted routing is reserved for future extension.

## Analytics

The primary V1 event is `whatsapp_click`.

Analytics records interaction metadata required for reporting and campaign attribution. It does not store IP addresses, visitor identity, WhatsApp conversations, fingerprints, or full user-agent strings.

## Campaign attribution

UTM parameters supported:

- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

V1 uses last-touch attribution with a first-party campaign cookie.

## Extension points

WordPress actions/filters are preferred for internal extensibility. The core does not require external services.

Future integrations may include Webhook, WooCommerce, CRM, and WhatsApp Business API support without making them dependencies of the core plugin.
