# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-23.**

**Current HEAD:** `04cfc78793519c8b7bb54797c04dc77fde6f12a0`

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface implemented with capability checks, nonces, validation, explicit input allowlisting, safe redirects, and no delete UI.
- Routing engine implemented: `direct`, `random`, `round_robin`.
- Direct requires the configured default contact to exist and be active.
- Random selects from active contacts.
- Round-robin follows active contacts by `sort_order`, then `id`, and stores `last_contact_id` in `hkc_routing_state`.
- Weight is stored but intentionally unused by V1 routing.
- Database upgrade check runs during plugin initialization and re-runs schema installers when `HKC_DB_VERSION` changes.
- Shared WhatsApp action layer implemented through public `admin-post.php`.
- WhatsApp click is recorded locally before redirecting to `wa.me`.
- UTM last-touch attribution is captured in the first-party `hkc_campaign` cookie for 30 days.
- Global floating button and `[hatnikotni_chat]` shortcode implemented.
- Frontend uses namespaced CSS and inline SVG; no frontend JS or external analytics request is required.
- Contract checks cover Contact CRUD, routing, database upgrades, WhatsApp action, campaign attribution and analytics.

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
- Event retention target: 180 days; cleanup not implemented yet.

### Campaign

- UTM: source, medium, campaign, term, content.
- Last-touch attribution using first-party `hkc_campaign` cookie, 30-day retention.
- A new UTM-bearing visit replaces previous attribution.

### Database

- `{$wpdb->prefix}hkc_contacts`
- `{$wpdb->prefix}hkc_events`
- WordPress prefix, charset/collation, `dbDelta()`, WordPress time functions.
- No DB foreign keys.
- Schema version stored in `hkc_db_version`.

### Integrations

- Future/optional: Webhook, WooCommerce, CRM, weighted/context routing, WhatsApp Business API.
- Core must not depend on them.
- `rancang.perlis.xyz` chatbot remains outside scope.

## Validation state

- Source syntax: PENDING — latest implementation changes require fresh GitHub Actions confirmation.
- Automated contracts: PENDING — latest contract changes require fresh GitHub Actions confirmation.
- CI/build: PENDING.
- Staging activation: PENDING.
- Frontend/mobile/desktop/cache/WooCommerce/accessibility/performance/security: PENDING.
- Production acceptance: PENDING.

No runtime acceptance is claimed.

## Next action

Confirm CI for the current HEAD. Then perform a full source audit of the current frontend/action/analytics flow. After that, implement Analytics admin reporting and retention cleanup. Runtime acceptance remains pending until staging installation and testing.
