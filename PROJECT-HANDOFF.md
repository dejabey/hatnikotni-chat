# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-23.**

**Current HEAD before this handoff update:** 9200965853d290420ec43f57cc2071c7bf282767

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface implemented with capability checks, nonces, validation, explicit input allowlisting, safe redirects, and no delete UI.
- General admin settings implemented for frontend enablement, default contact/message, button label, position, desktop/mobile visibility and routing method.
- Routing engine implemented: direct, random, round_robin.
- Direct requires the configured default contact to exist and be active.
- Random selects from active contacts.
- Round-robin follows active contacts by sort_order, then id, and stores last_contact_id in hkc_routing_state.
- Weight is stored but intentionally unused by V1 routing.
- Database upgrade check runs during plugin initialization and re-runs schema installers when HKC_DB_VERSION changes.
- Shared WhatsApp action layer uses public admin-post.php.
- WhatsApp click is recorded locally before redirecting to wa.me, but only when analytics consent is available.
- UTM last-touch attribution uses the first-party hkc_campaign cookie for 30 days only when analytics consent is available.
- Existing campaign cookie is cleared when analytics consent is absent.
- Global floating button and [hatnikotni_chat] shortcode implemented.
- Button position and desktop/mobile visibility settings are honored.
- Analytics reporting provides period totals plus device/contact/campaign breakdowns.
- Analytics events are automatically cleaned after 180 days by daily WP-Cron.
- WordPress Privacy Policy Guide integration implemented.
- WordPress.org readme.txt and readiness checklist implemented.
- Composer-based WordPress Coding Standards tooling and CI validation implemented.
- Contract checks cover the current core, frontend/action, analytics, campaign, privacy and WordPress.org readme paths.

## Privacy decision

Analytics is retained in core but is now consent-aware.

The plugin exposes hkc_has_analytics_consent with a default value of false. A site or consent-management integration must return true only after an explicit visitor consent signal.

When consent is absent:

- No WhatsApp click analytics event is recorded.
- No campaign attribution cookie is retained.
- Any existing hkc_campaign cookie is cleared when possible.
- WhatsApp routing and the contact button continue to work.

The plugin does not provide its own consent banner.

## Agreed V1

### Frontend

- Global floating WhatsApp button.
- Shortcode [hatnikotni_chat].
- Shared routing, campaign attribution, analytics and WhatsApp URL logic.
- Prefer HTML/CSS over frontend JS where possible.
- Prefer inline SVG over an icon library.

### Contacts

- Fields: id, name, phone, role, description, status, weight, sort_order, created_at, updated_at.
- Phone stored as digits only.
- Historical contacts are normally deactivated, not deleted.

### Analytics

- Primary event: whatsapp_click; this records a click, not proof that a message was sent.
- Store contact/page/device/UTM metadata only; no IP, visitor identity, fingerprint, conversation content, browsing history or visitor ID.
- Analytics failure must never block WhatsApp.
- Event retention: 180 days.
- Admin reporting: 7/30/90/180-day period with device/contact/campaign breakdown.
- Visitor analytics requires explicit consent through hkc_has_analytics_consent.

### Campaign

- UTM: source, medium, campaign, term, content.
- Last-touch attribution using first-party hkc_campaign cookie, 30-day retention.
- Attribution cookie requires analytics consent.
- A new UTM-bearing visit replaces previous attribution.

### Database

- {$wpdb->prefix}hkc_contacts
- {$wpdb->prefix}hkc_events
- WordPress prefix, charset/collation, dbDelta(), WordPress time functions.
- No DB foreign keys.
- Schema version stored in hkc_db_version.

### WordPress.org

- GPL-2.0-or-later.
- Human-readable source.
- No runtime third-party library dependency.
- WordPress-native APIs/libraries.
- Privacy Policy Guide integration.
- Consent-aware analytics.
- readme.txt present.
- CI includes WordPress Coding Standards.
- Final SVN assets, stable release packaging and submission remain pending.

### Integrations

- Future/optional: Webhook, WooCommerce, CRM, weighted/context routing, WhatsApp Business API.
- Core must not depend on them.
- rancang.perlis.xyz chatbot remains outside scope.

## Validation state

- Source syntax: CONFIRMED on the latest completed validation run.
- Automated contracts: CONFIRMED on the latest completed validation run.
- WordPress Coding Standards: FAILED on run 69; source formatting/standards findings remain to be fixed. PHP syntax and contract steps passed.
- CI/build: PENDING until WPCS is clean.
- Runtime consent integration: PENDING.
- Staging activation: PENDING.
- Frontend/mobile/desktop/cache/WooCommerce/accessibility/performance/security: PENDING.
- Production acceptance: PENDING.

No runtime acceptance is claimed.

## Next action

Fix the WordPress Coding Standards findings from the failed validation run before adding more features. Then re-run CI and complete the remaining WordPress.org preflight and staging runtime validation.

Before release, separately validate round-robin under concurrent requests, cron/retention behavior, consent withdrawal, cache behavior and the complete release package.
