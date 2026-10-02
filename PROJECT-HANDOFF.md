# PROJECT HANDOFF — Hatnikotni Chat

**Updated:** 2026-10-02  
**Branch:** wordpress-org-compliance  
**Base branch:** main  
**Current feature version:** 0.1.8 (installed on staging; not yet released to WordPress.org)  
**Latest successful CI:** Build #559, branch HEAD `cb2edf4777f953e18a04682c3dc264fb00539683`. Plugin source/package code is unchanged from build #549 (`fe98c0dd194d5194df08cdadd0290a6c31a9ee04`); builds #550–#559 contain documentation-only changes.  
**Latest package artifact:** `hatnikotni-chat-0.1.8`, artifact ID `11235744294` (build #559; not expired at last check).  
**Database schema:** 1.1.0  
**WordPress.org review remediation:** in progress; do not reply to reviewer until the final package, Plugin Check, and required staging consent tests pass.

## Current verification state — 2026-10-02

- GitHub Actions build #559 passed all six jobs: PHP validation on 8.1–8.5 and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37028877654.
- Build #559 produced artifact `hatnikotni-chat-0.1.8` (artifact ID `11235744294`, not expired at the last check). Plugin source/package code is unchanged from build #549; builds #550–#559 were documentation-only changes.
- PR #2 remains open as a draft and has not been merged. Latest branch HEAD: `cb2edf4777f953e18a04682c3dc264fb00539683`.
- Current staging environment: WordPress 7.1.2, PHP 8.5.10; Hatnikotni Chat 0.1.8 is active. The user has confirmed the privacy card/button appearance is satisfactory.
- Read-only runtime checks: three contact records, routing method `round_robin`, and the daily `hatnch_daily_cleanup` event is scheduled. The analytics table contained one event when checked; this count alone does not prove consent behavior.
- Plugin Check was rerun on staging on 2026-10-02 after the previous six-warning report. The latest screenshot shows “Checks complete. No errors found.” with both Error and Warning types selected and AI Analysis unchecked; no findings were displayed. The screenshot does not show the installed plugin version/build or an exported report, so correlate the result with the exact installed package before treating it as package-specific release evidence. The code fixes address the previous public UTM nonce recommendation and five unprefixed uninstall variables.
- The earlier two readme errors were addressed by fixing `Tested up to` and `Stable tag`. The main plugin header now declares `Domain Path: /languages`; manual `load_plugin_textdomain()` was removed after Plugin Check flagged it as discouraged for WordPress.org-hosted plugins.
- Still pending: record the exact plugin version/build used for the clean Plugin Check result (the screenshot does not show it); export the report if possible; test consent default/Accept/Reject/choice changes, immediate campaign-cookie clearing, event-count changes, direct/random/round-robin routing, cache behavior, keyboard accessibility, and relevant WooCommerce pages.
- No production changes have been made. Do not uninstall/reinstall the staging plugin; `uninstall.php` intentionally deletes plugin data.

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

1. The latest package build is #559: https://github.com/dejabey/hatnikotni-chat/actions/runs/37028877654 (artifact ID `11235744294`). Builds #550–#559 contain documentation-only changes compared with #549.
2. The user has already run Plugin Check on staging and supplied a screenshot showing no findings; confirm the exact installed version/build if package-level evidence is needed.
3. Do not uninstall the staging plugin because `uninstall.php` intentionally deletes plugin data.
4. Complete consent runtime tests on staging: no choice, Accept, Reject, switching both ways, immediate campaign-cookie clearing, and event-count deltas.
5. Verify direct, random and round-robin routing, shortcode, desktop/mobile visibility, keyboard focus/Escape behavior, cache compatibility, and WooCommerce pages.
6. Update this handoff with actual test evidence and remaining limitations. Prepare a versioned release candidate only after these gates pass.
7. After the package and staging checks pass, prepare the WordPress.org response in the existing review email thread. Production deployment remains a separate decision.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Plugin URI: https://github.com/dejabey/hatnikotni-chat
