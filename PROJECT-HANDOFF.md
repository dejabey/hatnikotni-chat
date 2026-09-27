# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-27.**

**Review remediation branch:** `wordpress-org-compliance`  
**Current remediation HEAD:** `8408fbe2dca66a8d7d0fdefda1ac9486b73e14d9`
**Base:** `main` HEAD `06c443a3ddf1e1d6f64c0f16f4ede18d97d60fcd`  
**Release target:** 0.1.1  
**Database schema target:** 1.1.0

## WordPress.org review status

The 27 Sep 2026 WordPress.org pre-review identified two concrete blockers:

1. Global declarations and stored identifiers used the three-character `HKC/hkc` prefix.
2. The Plugin URI pointed to a GitHub repository that was private/returned 404 to the reviewer.

The review specifically requires a distinct prefix of at least four characters for globally accessible declarations and stored data. The remediation uses **`HATNCH_` / `hatnch_`** throughout the runtime namespace. The review also says the Plugin URI must resolve publicly.

Reference: WordPress.org review ID `AUTOPREREVIEW COM hatnikotni-chat/zaryl/27Sep26/T1 27Sep26/4.3 (P0TDX376260HGN)`.

## Remediation completed in source

- PHP classes migrated from `HKC_*` to `HATNCH_*`.
- Constants migrated to `HATNCH_*`.
- Custom actions, filters, cron hooks and lifecycle hooks migrated to `hatnch_*`.
- Admin page/menu slugs migrated from `hkc*` to `hatnch*`.
- Options migrated to `hatnch_settings`, `hatnch_routing_state` and `hatnch_db_version`.
- Custom tables migrated to `{$wpdb->prefix}hatnch_contacts` and `{$wpdb->prefix}hatnch_events`.
- Campaign cookie migrated to `hatnch_campaign`.
- WordPress script/style handles migrated to the `hatnch` namespace.
- Frontend/admin CSS classes and IDs migrated to the `hatnch` namespace.
- Existing shortcode `[hatnikotni_chat]` is intentionally retained because it is already a unique, descriptive public interface and does not use the legacy three-character prefix.
- Legacy settings, routing state and custom tables are migrated during plugin initialization before normal schema upgrade handling.
- Legacy cron cleanup hook is cleared during migration.
- Plugin version bumped to 0.1.1; DB version bumped to 1.1.0.
- Readme, changelog, readiness documentation and this handoff updated.

## Data migration behavior

On upgrade from a legacy build:

- Existing `hkc` settings are copied to `hatnch_settings` if the new option does not already exist.
- Existing routing state is copied to `hatnch_routing_state`.
- Legacy custom contact/event tables are renamed to their `hatnch` equivalents when the destination table does not already exist.
- Legacy DB-version option is removed.
- Legacy daily cleanup cron hook is cleared.
- Normal schema installation then verifies the new tables.
- New installations create only the `hatnch` storage names.

The migration is deliberately one-way. No new runtime dependency on the old namespace remains.

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface with capability checks, nonces, validation, explicit input allowlisting, safe redirects, and no delete UI.
- Phone input is strictly 8–20 international digits only; +, spaces and hyphens are rejected.
- General settings for frontend enablement, default contact/message, button label, position, desktop/mobile visibility and routing.
- Routing: direct, random and round-robin.
- Direct requires an active configured default contact.
- Random selects only active contacts.
- Round-robin follows active contacts by sort order/id and stores last contact state.
- Shared WhatsApp action endpoint through `admin-post.php`.
- Click analytics is consent-gated and does not block WhatsApp routing.
- UTM last-touch attribution is consent-gated and retained for 30 days.
- Analytics retention is 180 days with daily WP-Cron cleanup.
- WordPress Privacy Policy Guide integration.
- Global floating button and `[hatnikotni_chat]` shortcode.
- No bundled runtime third-party library and no required frontend JavaScript.

## Privacy model

Analytics defaults to false through `hatnch_has_analytics_consent`.

Without consent:

- no analytics event;
- no campaign attribution cookie;
- existing campaign cookie is cleared where possible;
- WhatsApp routing remains functional.

With consent, the plugin stores only the documented contact/page/device/UTM event data locally.

## Validation state

### Source / CI
- **CONFIRMED:** GitHub Actions run #294 for commit `8408fbe2dca66a8d7d0fdefda1ac9486b73e14d9` passed on PHP 8.1, 8.2, 8.3 and 8.4.
- **CONFIRMED:** PHP syntax checks, skeleton contract checks and WordPress Coding Standards passed across the full matrix.
- Previous green run #134 applies to the earlier `HKC` namespace and is retained only as historical evidence.
- **PENDING:** clean ZIP/source audit after the final handoff update.

### Runtime
- Existing staging results remain historical evidence only.
- **CONFIRMED PRE-MIGRATION:** staging.perlis.xyz currently has Hatnikotni Chat 0.1.0 active and the legacy `hkc_db_version`, `hkc_routing_state` and `hkc_settings` options present.
- **PENDING:** install the 0.1.1 remediation build on staging and run the actual upgrade migration rehearsal.
- **PENDING:** direct/random/round-robin routing after migration.
- **PENDING:** shortcode and frontend/admin UI after migration.
- **PENDING:** consent integration and withdrawal.
- **PENDING:** cache/CDN, WooCommerce, accessibility, performance and security checks.
- **PENDING:** uninstall verification for the new storage names.

### Repository / WordPress.org
- **DONE:** source namespace remediation.
- **DONE:** class filenames aligned with `HATNCH_*` classes for WPCS.
- **DONE:** documentation update.
- **PENDING:** public Plugin URI. The repository must be publicly reachable before the WordPress.org reply.
- **PENDING:** final release packaging and submission response.

## Required next actions

1. Re-run the full source collision audit after the final documentation update.
2. Inspect the clean plugin package contents.
3. Install the 0.1.1 remediation build on staging and perform the migration/functional tests.
4. Verify the public Plugin URI from an unauthenticated browser context.
5. Only after all gates pass, merge/release and reply to the same WordPress.org review email.

**Do not reply to WordPress.org yet.**

## Source of truth

This handoff must be updated whenever the current commit, CI status, runtime status, migration behavior, release metadata or WordPress.org status changes.
