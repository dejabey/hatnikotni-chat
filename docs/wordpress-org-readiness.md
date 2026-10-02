# WordPress.org Readiness

## Current target

Hatnikotni Chat is being developed for eventual submission to the WordPress.org Plugin Directory. Official requirements are treated as release gates, not as post-submission cleanup.

## WordPress.org review remediation

- [x] Replace legacy three-character declarations with unique HATNCH_/hatnch_ namespace.
- [x] Prefix custom hooks, options, cron events, admin page slugs, storage tables, cookies and asset handles/classes.
- [x] Add migration for existing settings, routing state and custom tables.
- [x] Retain the unique public shortcode [hatnikotni_chat].
- [x] Make Plugin URI repository publicly reachable.
- [x] Add clean production package workflow.
- [x] GitHub Actions build #531 passed all six jobs on 2026-10-02: PHP 8.1–8.5 validation and production package build. [Run #531](https://github.com/dejabey/hatnikotni-chat/actions/runs/37021952844).
- [x] Audit the 0.1.2 ZIP structure and verify the JavaScript asset is included.
- [x] User confirmed the current compact privacy UI looks correct on staging (2026-09-30).
- [x] CI matrix includes PHP 8.5 to match staging; build #531 passed.
- [ ] Complete runtime tests for consent Accept/Reject/withdrawal and immediate campaign-cookie clearing; UI appearance alone does not prove these behaviors.

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
- [ ] Confirm WordPress Coding Standards on the current branch including PHP 8.5.
- [x] Initial Plugin Check on 0.1.8: 2 readme errors and 37 warnings on 2026-10-02.
- [x] Fixed `Tested up to` and `Stable tag` readme headers; removed discouraged manual `load_plugin_textdomain()` call and declared `Domain Path: /languages`.
- [x] Plugin Check results supplied on 2026-10-02: 6 warnings remain — one nonce-recommended warning for read-only public UTM parameters and five unprefixed variables in uninstall.php.
- [ ] Re-run WordPress Plugin Check after the targeted warning fixes and inspect the remaining warnings.
- [ ] Complete staging security review.

### Functional validation
- [x] Round-robin routing verified on staging for 0.1.1; current setting is still round_robin.
- [x] Current 0.1.8 UI is installed on staging; user reports appearance is satisfactory.
- [x] Current staging read-only checks found three contacts and a scheduled daily cleanup event.
- [ ] Reconfirm live routing, consent allow/reject, and event-count changes against the current 0.1.8 build.
- [ ] Fresh install and upgrade test for 0.1.2
- [ ] Activation/deactivation and database upgrade
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
