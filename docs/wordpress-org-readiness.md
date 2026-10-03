# WordPress.org Readiness

## Current target

Hatnikotni Chat is being developed for eventual submission to the WordPress.org Plugin Directory. Official requirements are treated as release gates, not as post-submission cleanup.

## WordPress.org review remediation

- [x] Replace legacy three-character declarations with unique HATNCH_/hatnch_ namespace.
- [x] Prefix custom hooks, options, cron events, admin page slugs, storage tables, cookies and asset handles/classes.
- [x] Add baseline migration for existing settings, routing state and custom tables.
- [x] Implement migration safeguards for legacy/new table collisions, failed rename handling, schema-version advancement after failure, and deactivate/reactivate ordering; verify required columns before accepting the current schema. Build #608 passed for the migration-hardening code: https://github.com/dejabey/hatnikotni-chat/actions/runs/37111027511. Follow-up documentation commit passed Build #611: https://github.com/dejabey/hatnikotni-chat/actions/runs/37118281504.
- [ ] Complete code review and controlled migration tests for legacy-only tables, collisions, option conflicts, rename failure/partial retry, incomplete schema, and deactivate/reactivate before release.
- [x] Retain the unique public shortcode [hatnikotni_chat].
- [x] Make Plugin URI repository publicly reachable.
- [x] Add clean production package workflow.
- [x] GitHub Actions build #578 passed all six jobs on 2026-10-02: PHP 8.1–8.5 validation and production package build. [Run #578](https://github.com/dejabey/hatnikotni-chat/actions/runs/37033299925). Artifact ID: `11238198052`; SHA-256: `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`.
- [x] Build #578 commit `de9b2539232bfedc36c1e5806407bd4f52cb08d1` does not change PHP/JavaScript runtime source. However, the packaged `readme.txt` description differs from build #549, so package contents are not identical.
- [x] User-reported runtime tests on the active 0.1.8 staging installation passed for WhatsApp redirect, consent UI, Accept/Reject event-count behavior, persisted choice, withdrawal, and campaign-cookie deletion.
- [ ] Verify exact staging ZIP provenance by comparing its SHA-256 to a known artifact.
- [x] User confirmed the current compact privacy UI looks correct on staging (2026-09-30).
- [x] CI matrix includes PHP 8.5 to match staging; build #571 passed.
- [x] User-reported staging tests for consent Accept/Reject/withdrawal and immediate campaign-cookie clearing passed; exact installed artifact remains unverified.

## Release gates

### Licensing and packaging
- [x] GPL-2.0-or-later plugin license
- [x] No bundled runtime third-party libraries
- [x] WordPress-native APIs and libraries
- [x] Main plugin file and optional uninstall.php at repository root
- [x] WordPress.org readme.txt
- [ ] Final WordPress.org SVN assets
- [ ] Final stable release package

### Privacy
- [x] Analytics disabled when no explicit allow choice exists
- [x] Native expandable privacy choices control beside the floating button
- [x] Explicit allow and reject actions
- [x] WhatsApp action remains functional regardless of analytics choice
- [x] Campaign cookie and attribution are consent-aware
- [x] No IP storage, visitor identity or fingerprinting
- [x] No external analytics service
- [x] 180-day event retention
- [x] WordPress Privacy Policy Guide integration
- [ ] Runtime validation of allow, reject, choice change and campaign-cookie withdrawal

### Code quality and security
- [x] Direct-access guards
- [x] Capability checks for admin actions
- [x] Nonces for admin state-changing actions
- [x] Input sanitization/validation and escaped frontend/admin output
- [x] GitHub Actions build #578 passed the current PHP matrix and production package job. PHP/JavaScript runtime source is unchanged since #549, but the packaged `readme.txt` description was revised, so the ZIP contents are not identical.
- [x] Initial Plugin Check on 0.1.8 reported 2 readme errors and 37 warnings; a subsequent detailed report showed six warnings on 2026-10-02.
- [x] Fixed `Tested up to` and `Stable tag` readme headers; removed discouraged manual `load_plugin_textdomain()` call and declared `Domain Path: /languages`.
- [x] Earlier Plugin Check report supplied on 2026-10-02 showed six warnings: one recommended nonce warning for public UTM parameters and five unprefixed variables in `uninstall.php`.
- [x] Current source documents the intentional no-nonce exception for public, read-only UTM query parameters and prefixes uninstall variables with `hatnch_`.
- [x] Fresh staging Plugin Check screenshot on 2026-10-02 reports “Checks complete. No errors found.” with Error and Warning types selected and AI Analysis unchecked.
- [ ] Correlate the clean screenshot with the exact installed plugin version/build; the screenshot itself does not display the version or export metadata. Save/export the full report if possible.
- [ ] Complete staging security review.

### Functional validation
- [x] Round-robin routing verified on staging for 0.1.1; current setting is still round_robin.
- [x] Current 0.1.8 UI is installed on staging; user reports appearance is satisfactory.
- [x] Current staging read-only checks found three contacts and a scheduled daily cleanup event.
- [x] User-reported consent allow/reject/withdrawal and event-count changes passed on the active 0.1.8 staging installation; [ ] correlate the installed ZIP to an exact CI artifact.
- [ ] Fresh install and upgrade test for 0.1.2
- [ ] Activation/deactivation and database upgrade, including controlled legacy migration and schema-integrity cases
- [ ] Direct and random routing
- [ ] WhatsApp redirect
- [ ] Consent absent: no analytics/campaign cookie
- [ ] Explicit allow: analytics and campaign attribution
- [ ] Explicit reject: no analytics and campaign cookie removed
- [ ] Change allow/reject choice after initial selection
- [ ] Analytics retention cleanup
- [ ] Desktop/mobile visibility and shortcode
- [ ] Cache compatibility, accessibility, WooCommerce and performance
- [ ] Uninstall verification

## Repository flow

Development -> GitHub -> source audit -> CI -> release candidate -> staging -> full validation -> stable release -> WordPress.org SVN.

GitHub is the development source. WordPress.org SVN is the distribution/release channel.

## Submission preparation

Before submission, verify the current Plugin Handbook and Plugin Directory guidelines, then prepare a stable version, matching plugin header/readme, inspected ZIP, SVN trunk/tag, assets, privacy documentation and support details.
