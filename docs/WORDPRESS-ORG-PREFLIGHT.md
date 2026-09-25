# WordPress.org Preflight — Hatnikotni Chat

## Current RC
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- CI: Run #141 reported passing by project owner
- PHP matrix: 8.1–8.5
- Runtime package: production-only files; development tests and Composer tooling are excluded from the release ZIP.

## Pass
- GPLv2 or later declared in plugin header and readme.
- WordPress-native APIs are used for settings, admin actions, cron, database access, escaping and redirects.
- No external analytics service is required.
- Analytics is opt-in through hkc_has_analytics_consent.
- No third-party runtime JavaScript/CSS dependency is bundled or loaded by core.
- Admin UI uses a scoped Hatnikotni Chat design system with no external fonts, icon libraries or frontend runtime dependencies.
- Privacy Policy Guide integration is present.
- Uninstall removes the plugin custom tables and options.
- Plugin runtime files are organized under the plugin root, includes/, and assets/.
- Requires at least 6.6 and Requires PHP 8.1 are declared in the main plugin file.

## Release blockers / final checks
1. Before WordPress.org submission, Stable tag must be changed from trunk to the exact stable version tag.
2. WordPress.org submission requires the complete final plugin and documentation.
3. The readme lists four screenshots; matching lowercase screenshot assets must exist in the top-level WordPress.org SVN assets directory before submission.
4. The Contributors field should use actual WordPress.org usernames; do not invent one.
5. Run the official readme validator against the final stable readme.
6. Final staging browser tests remain pending because WPVibe runtime quota is exhausted.
7. Admin UI redesign is implemented in the RC source; visual browser verification on General, Contacts and Analytics remains pending.
8. Layout spacing and content-width refinement was applied after staging screenshots; the redesign now uses a wider 1320px maximum canvas with tighter, consistent panel spacing.

## Security findings
### Public WhatsApp endpoint
The anonymous GET endpoint is intentional. It accepts page context and an optional pre-filled message, sanitizes those values, resolves a server-side contact, records analytics only after consent, and redirects to a locally constructed wa.me URL.

### Round-robin concurrency
The current round-robin implementation reads and updates one WordPress option. Concurrent requests can theoretically observe the same previous state and select the same next contact. Sequential functional testing passed. High-concurrency stress testing remains pending.

### Phone validation
Admin input requires international digits only, 8–20 digits, without plus signs, spaces or hyphens. Stored/trusted phone values are normalized when constructing the wa.me URL.

## Privacy findings
- Default consent is false.
- No analytics event is recorded without consent.
- Campaign cookie is cleared when consent is absent.
- Analytics excludes IP address, identity fields, full user-agent, fingerprint and browsing history.
- Retention is 180 days.
- Campaign cookie retention is 30 days when consent is available.

## Production gate
Do not deploy until staging browser/cache/WooCommerce/accessibility checks are complete, consent integration is verified with the site's actual consent mechanism, temporary staging test contacts are removed, the final release artifact is inspected, and backup/rollback is ready.
## Latest UI refinement

- Admin UI layout spacing and content-width refinement applied after staging screenshot review.
- General, Contacts and Analytics now use a wider, more consistent content canvas and tighter panel spacing.
- Browser visual verification remains a staging gate because the source-side change cannot substitute for runtime verification.

- Analytics admin layout refined: summary cards and reporting filter now share a balanced top section, while data panels use a wider desktop grid.
