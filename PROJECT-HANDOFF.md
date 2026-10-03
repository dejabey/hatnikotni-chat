# PROJECT HANDOFF — Hatnikotni Chat

**Updated:** 2026-10-03  
**Active review branch:** `wordpress-org-compliance`  
**Migration hardening branch:** `migration-hardening`  
**Migration hardening PR:** https://github.com/dejabey/hatnikotni-chat/pull/3 (draft; not merged)  
**Base branch:** `main`  
**Current feature version:** 0.1.8 (installed on staging; not yet released to WordPress.org)  
**Latest confirmed successful CI before migration hardening:** Build #578, branch HEAD `de9b2539232bfedc36c1e5806407bd4f52cb08d1`. All six jobs passed: PHP validation on 8.1–8.5 and production package build. This commit changes documentation only; PHP/JavaScript runtime source is unchanged from build #549, but packaged `readme.txt` has a revised description; therefore the package contents are not byte-for-byte the same as build #549.  
**Latest known package artifact before migration hardening:** `hatnikotni-chat-0.1.8`, artifact ID `11238198052` (build #578; SHA-256 `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`).  
**Database schema:** 1.1.0  
**WordPress.org review remediation:** in progress; do not reply to reviewer until the package, Plugin Check evidence and required staging consent tests are fully correlated and reviewed.

## Current verification state — 2026-10-03

- GitHub Actions build #578 passed all six jobs: PHP validation on 8.1–8.5 and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37033299925.
- Build #578 artifact: `hatnikotni-chat-0.1.8`, artifact ID `11238198052`, SHA-256 `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`. Branch: `wordpress-org-compliance`; HEAD: `de9b2539232bfedc36c1e5806407bd4f52cb08d1`.
- Commit #578 is documentation-only. PHP/JavaScript runtime source is unchanged from build #549 (`fe98c0dd194d5194df08cdadd0290a6c31a9ee04`), but packaged `readme.txt` has a revised plugin description. Build #578 therefore contains no new runtime-code revision, but its package contents are not byte-for-byte identical to build #549.
- **Staging runtime tests reported by the user:** active plugin version 0.1.8; WhatsApp action redirected successfully; privacy panel opened; Accept recorded an analytics click (count 3→4); Reject prevented a new event; consent choices persisted across reloads; withdrawal restored consent to `no`; and the HttpOnly `hatnch_campaign` cookie was present before withdrawal, absent after Reject, and remained absent after reload. These tests pass as reported.
- **Important provenance limitation:** the exact artifact installed on staging has not been cryptographically correlated to build #578. Do not state that the installed ZIP is artifact #578 unless its hash is independently verified. Runtime test results establish observed behavior of the active 0.1.8 installation, not the ZIP's SHA-256.
- PR #2 remains open as a draft and has not been merged. PR #3 is a separate draft PR for migration hardening, based on `wordpress-org-compliance`; neither PR is merged. Check [GitHub Actions](https://github.com/dejabey/hatnikotni-chat/actions) for current validation before treating migration code as CI-validated.
- The Plugin Check screenshot reported “Checks complete. No errors found,” with Error and Warning selected and AI Analysis unchecked. The screenshot does not identify the installed build; retain this as a clean screenshot result, not as cryptographic package correlation.
- **Migration audit — 2026-10-03 (code inspection; no database mutation):** the previous implementation could leave legacy rows invisible when old and new tables coexisted; activation could create defaults/tables before runtime migration; and a failed rename could still be followed by schema-version advancement.
- **Migration hardening implementation — PR #3:** initial implementation `8d5d6ca002a0f26442c78957a19ddcfab67c775c`; PHPCS spacing fix `064214d2a54ea400a4dcdd57ed6dd214f1c3973c`; required-column verification added in `595b511c64209681a8f324ff2e9a1e7c0b97853e`. Build #608 passed in GitHub Actions with conclusion `success`, run ID `37111027511`, associated with handoff commit `5883f2b1b67ce88296552a21ac4a53e4ffc86e2e`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37111027511. The schema gate checks required columns in both plugin tables before advancing the version. Documentation is current; controlled migration tests remain pending. No staging/production database was accessed or changed. Automatic merging of coexisting tables is intentionally not attempted because contact/event primary-key references need a deliberate reconciliation strategy.
- **Validation status for PR #3:** Build #608 passed; GitHub Actions reports `success` for run `37111027511`, associated with commit `5883f2b1b67ce88296552a21ac4a53e4ffc86e2e` and including schema-column verification commit `595b511c64209681a8f324ff2e9a1e7c0b97853e`. Follow-up Build #611 also passed: run `37118281504`, conclusion `success`, associated with documentation commit `ea591b664bc9da87f83c85600a5fdf0e5f90b4c0`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37118281504. Build #611 validates the latest documentation commit; it is not runtime proof of migration safety. No staging database was accessed or changed. Controlled tests remain required for legacy-only, new-only, table collision (including empty new table), option conflict, rename failure, partial rename/retry, incomplete schema, and deactivate/reactivate.
- Remaining staging coverage: direct/random/round-robin routing on the current build, cache/CDN behavior, keyboard accessibility/Escape/focus, mobile layout, shortcode, WooCommerce pages, JavaScript-disabled behavior, custom cookie path/domain, and upgrade collision handling.
- No production changes have been made. Do not uninstall/reinstall the staging plugin; `uninstall.php` intentionally deletes plugin data.

## Follow-up schema audit — 2026-10-03

- Added verification of required named indexes and their ordered columns for both `hatnch_contacts` and `hatnch_events`, in addition to required-column checks. Code commit: `3292afaaa91f04f39402e221b039654509351d57`.
- Added static contract assertions in `tests/skeleton-contract.sh` (commit `15dcedd2040784bc9a781f87df2eb517936cc5c0`) and documented a missing/misordered-index test in `docs/staging-test-plan.md` (commit `23c40fe69ced0c8af0c0be46cf1a29b578db82c7`).
- Build #614 (run `37118448499`) failed only at PHPCS on a PHPDoc spacing issue (PHP syntax and skeleton contract passed); fixed in commit `2e7a5c5d420ffa4ba3cf5b6e25e580618731ed5c`. A fresh CI result for that fix has not yet been returned by the workflow lookup. Build #615 (`37118455872`) was queued for documentation. Recheck CI before considering the latest code validated.
- This remains code-level hardening only. No staging/production database was accessed or changed. Controlled tests for legacy data, conflicts, partial migration, schema columns/indexes, and retry behavior remain required.

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

Important implementation note: consent is cookie-based. Rejecting consent posts to same-origin `admin-post.php` so the server can expire the HttpOnly campaign cookie immediately; if that request fails, campaign capture clears it on the next request while consent remains rejected. The user has now reported successful staging tests for Accept, Reject, consent withdrawal, analytics event counts and campaign-cookie removal. Exact build-artifact correlation remains unverified, and custom cookie paths/cached pages still require testing.

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

1. Keep Build #578 as the latest confirmed successful production package build. Build #608 validates the migration-hardening branch; it is not a replacement release package artifact. Build #578 artifact: `11238198052`; SHA-256: `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`.
2. Do not claim the ZIP on staging is exactly Build #578 until the installed artifact hash is verified. The user-reported 0.1.8 runtime tests pass, but that is a separate evidence track.
3. Review PR #3 after Build #604 passed; CI is not a substitute for code review or the controlled migration test matrix. Do not merge it yet.
4. Run the migration test matrix on a disposable staging clone or controlled test database: legacy-only; new-only; both tables including empty/new and non-empty/old; conflicting options; rename failure; partial rename followed by retry; and deactivate/reactivate before migration.
5. Verify that failed migration does not delete legacy options/tables or advance the schema version, and that the admin notice is visible only to administrators.
6. Complete remaining staging tests: routing modes, shortcode, cache/CDN, keyboard accessibility, mobile layout, WooCommerce pages, JavaScript-disabled behavior and custom cookie path/domain.
7. Re-run/export Plugin Check with the installed version/build visible if possible, then correlate the report to the release candidate.
8. Update this handoff with new evidence. Prepare a versioned release candidate only after remaining gates pass.
9. After package, Plugin Check and staging checks are correlated, prepare the WordPress.org response in the existing review email thread. Production deployment remains a separate decision.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Migration hardening branch: migration-hardening  
Plugin URI: https://github.com/dejabey/hatnikotni-chat
