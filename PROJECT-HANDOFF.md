# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-23.**

**Current HEAD:** `97b7ee73708330344f3085e5b86102b58905745b`

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface implemented with capability checks, nonces, validation, explicit input allowlisting, safe redirects, and no delete UI.
- Routing engine implemented: `direct`, `random`, `round_robin`.
- Direct requires the configured default contact to exist and be active.
- Random selects from active contacts.
- Round-robin follows active contacts by `sort_order`, then `id`, and stores `last_contact_id` in `hkc_routing_state`.
- Weight is stored but intentionally unused by V1 routing.
- Database upgrade check now runs when the plugin initializes and re-runs schema installers when `HKC_DB_VERSION` changes.
- Contract checks now cover Contact CRUD, routing methods, active-contact routing, round-robin state, and database upgrades.

## Agreed V1

### Frontend
- Global floating WhatsApp button.
- Shortcode `[hatnikotni_chat]`.
- Shared routing, campaign attribution, analytics and WhatsApp URL logic.
- Prefer HTML/CSS over frontend JS where possible.
- Prefer inline SVG over an icon library.

### Contacts
- Fields: id, name, phone, role, description, status, weight, sort_order, created_at, updated_at.
- Phone stored as digits only.
- Historical contacts are normally deactivated, not deleted.

### Analytics
- Primary event: `whatsapp_click`; this records a click, not proof that a message was sent.
- Store contact/page/device/UTM metadata only; no IP, visitor identity, fingerprint, conversation content, browsing history or visitor ID.
- Analytics failure must never block WhatsApp.

### Campaign
- UTM: source, medium, campaign, term, content.
- Last-touch attribution using first-party `hkc_campaign` cookie, 30-day retention.

### Database
- `{$wpdb->prefix}hkc_contacts`
- `{$wpdb->prefix}hkc_events`
- WordPress prefix, charset/collation, `dbDelta()`, WordPress time functions.
- No DB foreign keys.
- Event retention target: 180 days.

### Integrations
- Future/optional: Webhook, WooCommerce, CRM, weighted/context routing, WhatsApp Business API.
- Core must not depend on them.
- `rancang.perlis.xyz` chatbot remains outside scope.

## Validation state

- Source syntax: PENDING — latest routing/admin/upgrade changes require fresh CI confirmation.
- Automated contracts: PENDING — latest contract changes require fresh CI confirmation.
- CI/build: PENDING.
- Staging activation: PENDING.
- Frontend/mobile/desktop/cache/WooCommerce/accessibility/performance/security: PENDING.
- Production acceptance: PENDING.

No runtime acceptance is claimed.

## Next action

Confirm CI for the current HEAD. Then perform the routing source audit and proceed to shared WhatsApp URL/action generation. Runtime acceptance remains pending until staging installation and testing.
