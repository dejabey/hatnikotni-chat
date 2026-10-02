# PROJECT HANDOFF — Hatnikotni Chat

**Updated:** 2026-10-02  
**Branch:** wordpress-org-compliance  
**Base branch:** main  
**Current feature version:** 0.1.8 (installed on staging; not yet released to WordPress.org)  
**Current branch HEAD:** ac6098703082d5eb5b427556bf2a3e0d12736cd6  
**Database schema:** 1.1.0  
**WordPress.org review remediation:** in progress; do not reply to reviewer until the final package, Plugin Check, and required staging consent tests pass.

## Current verification state — 2026-09-30

- Previous GitHub Actions run #484 passed PHP 8.1–8.4, JavaScript syntax, source contract tests, and WordPress Coding Standards on commit 27cca341cba18b172853554de4de94c9a57b5fcd.
- Current staging environment: WordPress 7.1.2, PHP 8.5.10; Hatnikotni Chat 0.1.8 is active.
- User confirmed the latest privacy card looks correct on staging. Rendered HTML confirms Malay labels, the Privacy Policy URL, Read More disclosure, and shield icon.
- Read-only runtime checks: 3 contact records, routing method round_robin, and the daily hatnch_daily_cleanup event is scheduled. The analytics table contained 1 event when checked; this count alone does not prove consent behavior.
- PHP 8.5 has been added to the CI matrix to match staging; wait for the resulting workflow before declaring current CI complete.
- Initial WordPress Plugin Check on 0.1.8 reported 2 errors (missing `Tested up to`, incorrect `Stable tag: trunk`) and 37 warnings. Readme errors have been fixed on the branch; rerun Plugin Check on the rebuilt package.
- Added `Domain Path: /languages` and removed the manual `load_plugin_textdomain()` hook after Plugin Check flagged that call as discouraged for WordPress.org-hosted plugins.
- Still pending: runtime verification of no-consent default, Accept, Reject, changing choices, immediate campaign-cookie clearing, event-count changes, full routing methods, cache behavior, keyboard accessibility, and review of remaining Plugin Check warnings.
- No production changes have been made. No plugin uninstall/reinstall was performed during these checks.

## Current objective

Remediate WordPress.org review feedback, maintain a clean production package, and validate Hatnikotni Chat on staging. The reviewer identified the old three-character prefix and inaccessible Plugin URI. The runtime namespace has been migrated to HATNCH_/hatnch_, and the GitHub repository is now public.

## Recent feature work: native analytics privacy choices and compact UI

Implemented on wordpress-org-compliance:
- Small shield icon for Privacy settings, positioned inline immediately to the left of the floating WhatsApp button.
- Compact consent card with a Reject/Accept switch, Privacy Policy link and Read More disclosure.
- Short data-use explanation is hidden until Read More is selected; the consent card itself opens only when the privacy icon is clicked.
- The admin phone placeholder and helper example now use generic number 601234567890; existing saved contact values are not changed.
- Explicit visitor analytics choice remains available, and WhatsApp navigation remains independent of that choice.
- Link to the WordPress Privacy Policy page when configured.
- A first-party hatnch_analytics_consent cookie storing yes/no for up to 180 days.
- English is the source language for frontend privacy controls. Malay equivalents are provided in languages/hatnikotni-chat-ms_MY.po and compiled to .mo in the package workflow. Strings are gettext-wrapped under the hatnikotni-chat text domain.
- Native consent is false by default. Analytics and campaign attribution stay disabled unless consent is explicitly allowed or a deliberate site integration overrides the filter.
- WhatsApp routing is not blocked by the visitor's analytics choice.
- Added a frontend JavaScript asset, scoped CSS, source contract checks, updated readme/changelog and updated architecture/readiness/staging documentation.
- Version 0.1.6 fixed the Contact Us handler and compacted the privacy UI. Version 0.1.7 establishes English as the source language, adds Malay translation files, and compiles the .mo file into the release ZIP; DB schema remains 1.1.0.

Important implementation note: consent is cookie-based. Rejecting consent now sends a same-origin request to `admin-post.php` so the server can expire the HttpOnly campaign cookie immediately; if that request fails, campaign capture clears it on the next request while consent remains rejected. Staging must verify allow/reject/choice changes, immediate cookie clearing, custom cookie paths and cached pages. Initial staging rejection test was performed by the user: after Reject analytics and a WhatsApp click, the `hatnch_events` table remained at 0 events, consistent with analytics being blocked. Allow analytics and switching consent back and forth still require verification. Version 0.1.7 established English as the source language and added Malay translation files. Plugin Check flagged manual `load_plugin_textdomain()` use as discouraged for WordPress.org plugins, so the main plugin header now declares `Domain Path: /languages` and relies on WordPress just-in-time translation loading.

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface with capability checks, nonces, validation, safe redirects and no delete UI.
- Direct, random and round-robin routing.
- Shared WhatsApp action endpoint through admin-post.php.
- First-party click analytics and UTM attribution with consent gates.
- Analytics retention: 180 days; campaign attribution cookie: up to 30 days.
- Global floating button and shortcode [hatnikotni_chat].
- No bundled runtime third-party library or external analytics service.
- Clean package workflow builds a ZIP containing only the plugin root file, uninstall.php, readme.txt, assets, includes and languages (with compiled Malay .mo generated during packaging).

## Staging evidence before this feature

On staging.perlis.xyz, Hatnikotni Chat 0.1.1 was active after prefix migration. Migration preserved three contacts, updated the schema option to 1.1.0 and created hatnch-prefixed tables. Round-robin was tested manually and the user confirmed the sequence followed the configured contact order. These are 0.1.1 results, not validation of the new 0.1.6 consent UI.

## Required next actions

1. Wait for CI to complete with PHP 8.5 included, then inspect its result.
2. Run WordPress Plugin Check on the final clean package; resolve any actionable findings.
3. Complete consent runtime tests on staging: no choice, Accept, Reject, switching both ways, campaign-cookie clearing, and event-count deltas.
4. Verify direct, random and round-robin routing, shortcode, desktop/mobile visibility, keyboard focus/Escape behavior, cache compatibility, and WooCommerce pages.
5. Update the handoff with actual test evidence and any remaining limitations.
6. Prepare a versioned release candidate only after these gates pass. Do not uninstall the staging plugin; uninstall.php intentionally deletes plugin data.
7. After the package and staging checks pass, prepare the WordPress.org response in the existing review email thread. Production deployment remains a separate decision.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Plugin URI: https://github.com/dejabey/hatnikotni-chat
