# WordPress.org Readiness

## Current target

Hatnikotni Chat is being developed for eventual submission to the WordPress.org Plugin Directory.

Official requirements are treated as release gates, not as post-submission cleanup.

## WordPress.org review remediation

- [x] Replace legacy three-character `HKC/hkc` declarations with unique `HATNCH_/hatnch_` namespace.
- [x] Prefix custom hooks, options, cron events, admin page slugs, storage tables, cookies and asset handles/classes.
- [x] Add migration for existing plugin settings, routing state and custom tables.
- [x] Retain the unique public shortcode `[hatnikotni_chat]`.
- [ ] Make Plugin URI publicly reachable; current GitHub repository is private.
- [ ] Re-run full CI and contract/WPCS checks after remediation.
- [ ] Re-run staging and clean ZIP validation.

## Release gates

### Licensing and packaging

- [x] GPL-2.0-or-later plugin license
- [x] No bundled runtime third-party libraries
- [x] WordPress-native APIs and libraries
- [x] Main plugin file at repository root
- [x] Optional uninstall.php at repository root
- [x] WordPress.org readme.txt
- [ ] Final WordPress.org SVN assets
- [ ] Final stable release package

### Privacy

- [x] Analytics does not run without visitor consent
- [x] Campaign cookie is consent-aware
- [x] No IP storage
- [x] No visitor identity or fingerprinting
- [x] No external analytics service
- [x] 180-day event retention
- [x] WordPress Privacy Policy Guide integration
- [ ] Runtime validation with the site's actual consent mechanism

### Code quality and security

- [x] Direct-access guards
- [x] Capability checks for admin actions
- [x] Nonces for admin state-changing actions
- [x] Input sanitization/validation
- [x] Escaped frontend/admin output
- [x] Safe redirects for internal fallback redirects
- [x] WordPress Coding Standards CI
- [x] PHP compatibility matrix CI
- [ ] Full staging security review

### Functional validation

- [ ] Fresh plugin installation
- [ ] Activation/deactivation
- [ ] Database creation and upgrade
- [ ] Direct routing
- [ ] Random routing
- [ ] Round-robin routing under concurrent requests
- [ ] WhatsApp redirect
- [ ] Consent absent: no analytics/campaign cookie
- [ ] Consent present: analytics/campaign attribution
- [ ] Analytics retention cleanup
- [ ] Desktop/mobile visibility
- [ ] Shortcode
- [ ] Cache compatibility
- [ ] Accessibility
- [ ] WooCommerce compatibility
- [ ] Performance
- [ ] Production rollback procedure

## Repository flow

Development -> GitHub -> source audit -> CI -> release candidate -> staging -> full validation -> stable release -> WordPress.org SVN.

GitHub remains the development source. WordPress.org SVN is the distribution/release channel.

## Submission preparation

Before submission, verify the current WordPress.org Plugin Handbook and Plugin Directory guidelines again, then prepare:

- stable plugin version
- matching plugin header and readme.txt version
- complete ZIP
- WordPress.org SVN trunk/tag structure
- plugin icon/screenshots/assets
- final privacy documentation
- support/contact details
