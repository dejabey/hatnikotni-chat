# PROJECT HANDOFF — Hatnikotni Chat

**Updated:** 2026-09-28  
**Branch:** wordpress-org-compliance  
**Base branch:** main  
**Current feature version:** 0.1.2 (unreleased)  
**Database schema:** 1.1.0  
**WordPress.org review remediation:** in progress; do not reply to reviewer until current package and staging validation pass.

## Current objective

Remediate WordPress.org review feedback, maintain a clean production package, and validate Hatnikotni Chat on staging. The reviewer identified the old three-character prefix and inaccessible Plugin URI. The runtime namespace has been migrated to HATNCH_/hatnch_, and the GitHub repository is now public.

## Recent feature work: native analytics privacy choices

Implemented on wordpress-org-compliance:
- Expandable Privacy choices control beside the floating WhatsApp button.
- Short explanation plus expandable details about what analytics records.
- Explicit Allow analytics and Reject analytics buttons.
- Link to the WordPress Privacy Policy page when configured.
- A first-party hatnch_analytics_consent cookie storing yes/no for up to 180 days.
- Native consent is false by default. Analytics and campaign attribution stay disabled unless consent is explicitly allowed or a deliberate site integration overrides the filter.
- WhatsApp routing is not blocked by the visitor's analytics choice.
- Added a frontend JavaScript asset, scoped CSS, source contract checks, updated readme/changelog and updated architecture/readiness/staging documentation.
- Version advanced to 0.1.2; DB schema remains 1.1.0.

Important implementation note: consent is cookie-based and takes effect on the next HTTP request. This is sufficient for the WhatsApp action because the browser sends the cookie to admin-post.php. Staging must verify allow/reject/choice changes, campaign-cookie clearing, custom cookie paths and cached pages. No real runtime test of the new UI has yet been completed. GitHub Actions run 36395722717 passed syntax, contract checks and WPCS across PHP 8.1–8.4. The production-package job also passed. Artifact: hatnikotni-chat-0.1.2; plugin ZIP SHA-256: b150e3a4a4a17805a8c78a3677ba57341950561fe7f6318a1740bb0eebf9b40c. The packaged frontend JavaScript passed node --check. The clean ZIP contains only the plugin root, readme, uninstall file, assets and includes.

## Current implementation

- Standalone WordPress plugin; not deployed to production.
- Contact CRUD/admin interface with capability checks, nonces, validation, safe redirects and no delete UI.
- Direct, random and round-robin routing.
- Shared WhatsApp action endpoint through admin-post.php.
- First-party click analytics and UTM attribution with consent gates.
- Analytics retention: 180 days; campaign attribution cookie: up to 30 days.
- Global floating button and shortcode [hatnikotni_chat].
- No bundled runtime third-party library or external analytics service.
- Clean package workflow builds a ZIP containing only the plugin root file, uninstall.php, readme.txt, assets and includes.

## Staging evidence before this feature

On staging.perlis.xyz, Hatnikotni Chat 0.1.1 was active after prefix migration. Migration preserved three contacts, updated the schema option to 1.1.0 and created hatnch-prefixed tables. Round-robin was tested manually and the user confirmed the sequence followed the configured contact order. These are 0.1.1 results, not validation of the new 0.1.2 consent UI.

## Required next actions

1. Fetch the current branch head and confirm all commits are on wordpress-org-compliance.
2. CI and clean-package audit are complete for the current code; run them again if source changes.
3. Install 0.1.2 on staging without touching production. The ZIP is ready, but the connected site tool cannot directly upload the locally materialized artifact, so a manual upload through wp-admin is the remaining handoff step.
4. Test missing consent, allow, reject, changing choices, campaign-cookie deletion, WhatsApp redirect and routing.
5. Verify keyboard accessibility, mobile layout, page caching and the WordPress Privacy Policy URL.
6. Record actual staging evidence in this handoff.
7. Only after the release gates pass, prepare the corrected package and reply in the existing WordPress.org review email thread.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Plugin URI: https://github.com/dejabey/hatnikotni-chat
