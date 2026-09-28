# PROJECT HANDOFF — Hatnikotni Chat

**Updated:** 2026-09-28  
**Branch:** wordpress-org-compliance  
**Base branch:** main  
**Current feature version:** 0.1.7 (unreleased)  
**Database schema:** 1.1.0  
**WordPress.org review remediation:** in progress; do not reply to reviewer until current package and staging validation pass.

## Current objective

Remediate WordPress.org review feedback, maintain a clean production package, and validate Hatnikotni Chat on staging. The reviewer identified the old three-character prefix and inaccessible Plugin URI. The runtime namespace has been migrated to HATNCH_/hatnch_, and the GitHub repository is now public.

## Recent feature work: native analytics privacy choices and compact UI

Implemented on wordpress-org-compliance:
- Expandable Privacy choices control beside the floating WhatsApp button.
- Short explanation plus expandable details about what analytics records.
- Explicit Allow analytics and Reject analytics buttons.
- Link to the WordPress Privacy Policy page when configured.
- A first-party hatnch_analytics_consent cookie storing yes/no for up to 180 days.
- English is the source language for frontend privacy controls. Malay equivalents are provided in languages/hatnikotni-chat-ms_MY.po and compiled to .mo in the package workflow. Strings are gettext-wrapped under the hatnikotni-chat text domain.
- Native consent is false by default. Analytics and campaign attribution stay disabled unless consent is explicitly allowed or a deliberate site integration overrides the filter.
- WhatsApp routing is not blocked by the visitor's analytics choice.
- Added a frontend JavaScript asset, scoped CSS, source contract checks, updated readme/changelog and updated architecture/readiness/staging documentation.
- Version 0.1.6 fixed the Contact Us handler and compacted the privacy UI. Version 0.1.7 establishes English as the source language, adds Malay translation files, and compiles the .mo file into the release ZIP; DB schema remains 1.1.0.

Important implementation note: consent is cookie-based and takes effect on the next HTTP request. This is sufficient for the WhatsApp action because the browser sends the cookie to admin-post.php. Staging must verify allow/reject/choice changes, campaign-cookie clearing, custom cookie paths and cached pages. Initial staging rejection test was performed by the user: after Reject analytics and a WhatsApp click, the hatnch_events table remained at 0 events, consistent with analytics being blocked. Allow analytics and switching consent back and forth still require verification. The 0.1.5 staging test revealed a critical error when clicking Contact Us. Root cause found in source: the handler called the misspelled class HATNCH_Analitik::record_click(), while the actual class is HATNCH_Analytics. Version 0.1.6 fixes this and adds a regression contract test. Version 0.1.7 restores English source strings for the frontend privacy controls and JavaScript status messages, adds a Malay translation source, and loads the text domain on init. Re-run CI and verify Contact Us on staging before any production release.

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

1. Fetch the current branch head and confirm all commits are on wordpress-org-compliance.
2. CI and clean-package audit must pass for the current 0.1.7 version before staging installation.
3. Install 0.1.7 on staging without touching production. Use the latest successful workflow ZIP; upload/replace the existing plugin through wp-admin, and do not uninstall it because that can trigger data cleanup.
4. Test missing consent, allow, reject, changing choices, campaign-cookie deletion, WhatsApp redirect and routing.
5. Verify keyboard accessibility, mobile layout, page caching and the WordPress Privacy Policy URL.
6. Record actual staging evidence in this handoff.
7. Only after the release gates pass, prepare the corrected package and reply in the existing WordPress.org review email thread.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Plugin URI: https://github.com/dejabey/hatnikotni-chat
