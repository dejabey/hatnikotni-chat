## WordPress.org review remediation checkpoint — 2026-09-27

- WordPress.org pre-review feedback identified the 3-character `HKC` prefix as non-compliant and the GitHub Plugin URI as unreachable because the repository is private.
- Remediation branch `review/wporg-remediation` replaces the global/declaration/storage prefix with `HATNCH` and changes Plugin URI to `https://perlis.xyz/`.
- This branch must pass fresh CI and staging validation before any resubmission.

# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-24.**

**Current HEAD:** be6d15e3bec648000185a8597b2d5a043b29435f

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface implemented with capability checks, nonces, validation, explicit input allowlisting, safe redirects, and no delete UI.
- Contact phone input is strictly validated as 8–20 international digits only; +, spaces and hyphens are rejected.
- General admin settings implemented for frontend enablement, default contact/message, button label, position, desktop/mobile visibility and routing method.
- Routing engine implemented: direct, random, round_robin.
- Direct requires the configured default contact to exist and be active.
- Random selects from active contacts.
- Round-robin follows active contacts by sort_order, then id, and stores last_contact_id in hatnch_routing_state.
- Weight is stored but intentionally unused by V1 routing.
- Database upgrade check runs during plugin initialization and re-runs schema installers when HATNCH_DB_VERSION changes.
- Shared WhatsApp action layer uses public admin-post.php.
- WhatsApp click is recorded locally before redirecting to wa.me, but only when analytics consent is available.
- UTM last-touch attribution uses the first-party hatnch_campaign cookie for 30 days only when analytics consent is available.
- Existing campaign cookie is cleared when analytics consent is absent.
- Global floating button and [hatnikotni_chat] shortcode implemented.
- Button position and desktop/mobile visibility settings are honored.
- Analytics reporting provides period totals plus device/contact/campaign breakdowns.
- Analytics events are automatically cleaned after 180 days by daily WP-Cron.
- WordPress Privacy Policy Guide integration implemented.
- WordPress.org readme.txt and readiness documentation implemented.
- Composer-based WordPress Coding Standards tooling and CI validation implemented.
- Contract checks cover the current core, frontend/action, analytics, campaign, privacy and WordPress.org readme paths.

## Privacy decision

Analytics is retained in core but is now consent-aware.

The plugin exposes hatnch_has_analytics_consent with a default value of false. A site or consent-management integration must return true only after an explicit visitor consent signal.

When consent is absent:

- No WhatsApp click analytics event is recorded.
- No campaign attribution cookie is retained.
- Any existing hatnch_campaign cookie is cleared when possible.
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
- Visitor analytics requires explicit consent through hatnch_has_analytics_consent.

### Campaign

- UTM: source, medium, campaign, term, content.
- Last-touch attribution using first-party hatnch_campaign cookie, 30-day retention.
- Attribution cookie requires analytics consent.
- A new UTM-bearing visit replaces previous attribution.

### Database

- {$wpdb->prefix}hatnch_contacts
- {$wpdb->prefix}hatnch_events
- WordPress prefix, charset/collation, dbDelta(), WordPress time functions.
- No DB foreign keys.
- Schema version stored in hatnch_db_version.

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

- Source syntax: PASSED on the latest green GitHub Actions matrix run #134 (PHP 8.1, 8.2, 8.3 and 8.4).
- Automated contracts: PASSED on the latest green GitHub Actions matrix run #134.
- WordPress Coding Standards: PASSED on the latest green GitHub Actions matrix run #134.
- CI/build validation: PASSED on run #134 at commit be6d15e3bec648000185a8597b2d5a043b29435f.
- Runtime direct routing: PASSED on staging; contact id 1 resolves to international WhatsApp number 601155898464 and the action redirects to WhatsApp.
- Runtime analytics without consent: PASSED; the WhatsApp action works and no hatnch_events row is created by default-deny consent.
- Runtime consent integration with an external consent signal: PENDING; with the default filter false, consent-enabled analytics still requires a staging consent hook.
- Admin submenu UI refinement is implemented in source: scoped admin stylesheet, clearer introductory guidance, contextual placeholders/help text and improved table presentation. Visual verification on the updated build remains pending.
- Frontend/mobile/desktop/cache/WooCommerce/accessibility/performance/security and production acceptance: PENDING.
- Staging PHP 8.5.10 is recorded from the last staging environment inspection, but is not part of the current CI matrix.
- No runtime acceptance is claimed for the updated commits until the release candidate is rebuilt and retested.

## Source/release audit state

- Repository tree audited after the latest cleanup.
- No temporary consent test harness remains in the runtime or WPCS scope.
- Test infrastructure retained only for the skeleton contract used by CI.
- Source contains no bundled runtime vendor library or unnecessary frontend JavaScript.
- readme.txt, uninstall.php, privacy integration, WPCS configuration and WordPress.org readiness documentation are present.
- Release ZIP must contain only the plugin runtime files and required distribution documentation/assets; development-only repository files must not be copied into the installable plugin package unless deliberately required.

## Next action

Prepare the current release candidate from HEAD be6d15e3 and move it to staging for the remaining runtime/release gates.

Remaining gates:
- consent integration and consent withdrawal;
- multi-contact direct/random/round-robin routing;
- strict phone rejection;
- shortcode;
- cache behavior;
- WooCommerce coexistence;
- accessibility;
- performance/security;
- cron/180-day retention behavior;
- clean ZIP inspection;
- final documentation/release metadata;
- production approval.

Do not deploy to production until staging acceptance is complete.
