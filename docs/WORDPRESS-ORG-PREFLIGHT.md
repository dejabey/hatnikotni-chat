# WordPress.org Preflight — Hatnikotni Chat

## Current RC
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Latest CI: Run #179 passed.
- Latest commit: 6f4c9229d952b447052c9cb24c42be49edd32866
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
6. Final staging browser tests remain partially pending because WPVibe runtime quota is exhausted; desktop visual verification of General, Contacts and Analytics has been completed from the installed RC screenshots.
7. Mobile/responsive runtime verification, consent-manager integration, cache behavior, accessibility runtime verification and high-concurrency round-robin stress testing remain pending.
8. Temporary staging contacts HKC Test A and HKC Test B remain present because their removal requires a destructive staging action that has not been completed.

## Security findings
### Public WhatsApp endpoint
The anonymous GET endpoint is intentional. It accepts page context and an optional pre-filled message, sanitizes those values, resolves a server-side contact, records analytics only after consent, and redirects to a locally constructed wa.me URL.

### Round-robin concurrency
The current round-robin implementation reads and updates one WordPress option. Concurrent requests can theoretically observe the same previous state and select the same next contact. Sequential functional testing passed. High-concurrency stress testing remains pending.

### Phone validation
Admin input requires international digits only, 8–20 digits, without plus signs, spaces or hyphens. Invalid formatted input is rejected rather than silently converted. Stored/trusted phone values are used to construct the wa.me destination.

## Privacy findings
- Default consent is false.
- No analytics event is recorded without consent.
- Campaign cookie is cleared when consent is absent.
- Analytics excludes IP address, identity fields, full user-agent, fingerprint and browsing history.
- Retention is 180 days.
- Campaign cookie retention is 30 days when consent is available.
- The plugin does not provide its own consent banner and must remain standalone from Ninja GDPR or another specific consent plugin.

## Production gate
Do not deploy until staging browser/cache/WooCommerce/accessibility checks are complete, consent integration is verified with the site's actual consent mechanism, temporary staging test contacts are removed, the final release artifact is inspected, and backup/rollback is ready.

## UI status
- Admin UI layout spacing and content-width refinement applied after staging screenshot review.
- General, Contacts and Analytics use a wider, consistent content canvas and tighter panel spacing.
- Analytics summary/filter layout was rebalanced and verified visually.
- UI is frozen unless a real defect is discovered.

## Latest RC validation
- Run #179 passed.
- Actions artifact: `hatnikotni-chat-0.1.0-rc1`.
- Artifact ID: 10856710948.
- Artifact SHA-256: 667eb55904731bae7cb0092bd1354a96396b56b3eae6c235c4109daa964b2404.
- Desktop staging screenshots were reviewed after RC installation; General, Contacts and Analytics are acceptable.
