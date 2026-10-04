# Staging Test Plan

Run all tests on staging, not production. Record the plugin version, browser, device, result and any relevant console/PHP errors.

## Core functional tests

- [ ] Fresh installation and activation.
- [ ] Upgrade migration from existing data; verify contacts and routing state survive.
- [ ] Migration case: legacy contacts/events tables exist and new tables do not; verify rename succeeds and all rows remain accessible.
- [ ] Migration case: new tables already exist and legacy tables also contain data; verify migration pauses with an administrator notice and preserves both tables.
- [ ] Migration case: new tables exist but are empty while legacy tables contain data; verify migration pauses rather than assuming the legacy table is disposable.
- [ ] Migration case: legacy table rename fails (e.g. simulated database error); verify migration does not mark the schema version complete and preserves remaining legacy data.
- [ ] Migration case: deactivate/reactivate an old installation before migration; verify migration runs before activation defaults/schema installation.
- [ ] Migration case: existing new settings coexist with legacy settings and values differ; verify migration pauses, does not overwrite either value, and gives a recovery instruction.
- [ ] Migration case: copied legacy settings match the new option; verify legacy options are deleted only after the copy is verified.
- [ ] Migration case: one table rename succeeds and a later rename fails; retry and verify the already-renamed table is handled idempotently without data loss.
- [ ] Verify schema version is not advanced when migration, table verification, or version-option update fails.
- [ ] Simulate an incomplete current table (one required column missing) and verify the upgrade gate does not accept the schema or advance the database version until `dbDelta()` restores the required column.
- [ ] Simulate an `id` column with the wrong type, nullable definition, or missing `AUTO_INCREMENT`; verify schema validation pauses and does not advance the database version until corrected.
- [ ] Simulate a missing or incorrectly ordered required index on each table and verify the upgrade gate does not accept the schema or advance the database version until `dbDelta()` restores the required index.
- [ ] Simulate a required non-primary index with the wrong uniqueness setting (unique instead of non-unique, or vice versa); verify the schema gate rejects it until the index definition is corrected.
- [ ] Verify migration/upgrade errors are shown only to users with manage_options capability and do not expose sensitive data.
- [ ] Direct routing.
- [ ] Random routing.
- [ ] Round-robin order across active contacts.
- [ ] WhatsApp redirect and optional pre-filled message; confirm no critical error and the browser reaches wa.me.
- [ ] Shortcode output and routing.
- [ ] Desktop/mobile visibility and left/right positioning.
- [ ] Disabled state hides the floating widget.

## Native privacy choices

- [ ] With no consent cookie, click analytics does not record an event.
- [ ] Privacy choices control expands/collapses and has correct aria-expanded state.
- [ ] Analytics consent switch starts off when there is no saved consent and reflects a previously saved allow choice.
- [ ] Switch is keyboard accessible and its accessible label/checked state are correct.
- [ ] Read More (Baca Lagi in Malay) expands/collapses the data-use explanation with keyboard and pointer.
- [ ] Privacy icon is immediately left of the WhatsApp button; card uses compact horizontal padding and justified explanation text.
- [ ] Privacy Policy link points to the configured WordPress Privacy Policy page.
- [ ] Switching to Accept writes hatnch_analytics_consent=yes for 180 days.
- [ ] After accepting, a WhatsApp click records one event on the next request.
- [ ] Switching to Reject writes hatnch_analytics_consent=no.
- [ ] After rejecting, WhatsApp still redirects and no new analytics event is recorded.
- [ ] Rejecting expires the campaign cookie and the next request also clears it server-side where possible.
- [ ] Visitor can change from reject to allow and from allow to reject.
- [ ] Missing choice and rejected choice both default to analytics disabled.
- [ ] Shortcode works when consent is absent or rejected.
- [ ] Browser with JavaScript disabled can still use WhatsApp; analytics remains disabled unless an explicit site integration supplies consent.
- [ ] Check cookie path/domain behavior when WordPress is installed in a subdirectory or uses a custom COOKIEPATH/COOKIE_DOMAIN.
- [ ] No console errors, PHP notices or layout overlap on desktop/mobile.
- [ ] Compact privacy panel remains readable on narrow mobile screens and does not cover the WhatsApp action.
- [ ] Keyboard focus is visible; Escape closes the panel and returns focus to its toggle.

## WordPress Plugin Check / package verification

- [x] Build #578 passed all six CI jobs: https://github.com/dejabey/hatnikotni-chat/actions/runs/37033299925. Artifact ID: `11238198052`; SHA-256: `044709b0e37fd1155460daf44339d513e790c90a2b6106ab76c896de29efa760`.
- [x] Build #578 is based on branch HEAD `de9b2539232bfedc36c1e5806407bd4f52cb08d1`; the PHP/JavaScript runtime source remains unchanged from build #549, but the packaged `readme.txt` description changed, so package contents are not identical to build #549.
- [x] User-reported runtime tests on the active Hatnikotni Chat 0.1.8 staging installation: WhatsApp redirect works; consent panel opens; Accept increments analytics 3→4; Reject prevents a new event; choices persist after reload; withdrawal sets consent to `no`; and the HttpOnly campaign cookie is removed on Reject and stays absent after reload.
- [ ] Verify the exact ZIP installed on staging matches a specific artifact by SHA-256. Runtime tests alone do not prove artifact provenance.
- [x] User supplied a staging Plugin Check screenshot reading “Checks complete. No errors found,” with Error and Warning selected and AI Analysis unchecked (2026-10-02).
- [ ] Correlate the clean Plugin Check screenshot/report with the exact installed version/build; screenshot itself does not display that information. Export the complete report if possible.
- [x] Earlier six warnings were addressed in source: public UTM query nonce recommendation has a documented PHPCS exception, and uninstall variables use the plugin prefix.
- [x] **Migration hardening CI:** Build #632 passed all six jobs for code commit `c09393f02e34f085e4b5abcf47423330169a26ed` (PHP 8.1–8.5 and production package build): https://github.com/dejabey/hatnikotni-chat/actions/runs/37122659995. Build #633 then passed all six jobs for handoff commit `49ad6d0049ea55b20a463e48eb7d065d74eb44d2`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37122709277. PR #3 remains draft and unmerged. CI passing does not replace code review or controlled migration tests.
- [ ] **Migration hardening code review and controlled test matrix remain pending.** CI passing does not prove migration behavior against legacy/coexisting/partially migrated database states.
- [ ] After CI/code review, run the migration test matrix on a disposable staging clone or controlled test database before any real staging upgrade.
- [ ] Complete remaining runtime checks: routing modes, shortcode, keyboard/focus/Escape, mobile layout, cache/CDN, JavaScript-disabled behavior, custom cookie path/domain, and WooCommerce pages.

## Cache/CDN

- [ ] Page cache enabled.
- [ ] CDN enabled if production uses one.
- [ ] Routing remains dynamic.
- [ ] Analytics action is not cached.
- [ ] Consent choice takes effect on the next request even with cached frontend pages.
- [ ] Settings changes reflect after cache purge.

## WooCommerce

- [ ] Shop/product pages.
- [ ] Cart/checkout.
- [ ] Button does not interfere with WooCommerce UI.
- [ ] Logged-in/logged-out behavior.

## Performance and security

- [ ] No PHP notices/warnings/fatals.
- [ ] No unnecessary external requests.
- [ ] Inputs sanitized and outputs escaped.
- [ ] External redirect constrained to intended wa.me destination.
- [ ] No secrets/API keys.

## Release gate

Do not release until the current branch passes CI, the clean ZIP is inspected, consent allow/reject/withdrawal behavior is confirmed on staging, and critical routing/privacy tests pass. Migration hardening must also pass code review and the controlled migration test matrix. Production deployment requires separate approval.


## Automated migration integration tests — 2026-10-03

- [x] Legacy-only tables/options: rename both tables and verify representative row and option preservation.
- [x] Conflicting legacy/current settings options: migration pauses before table changes and preserves both values.
- [x] Legacy/current table collision: migration pauses and preserves both tables and the legacy option.
- [x] Injected failure on the second table rename: verify the first rename remains valid, the failed legacy table remains present, and a retry completes with both rows preserved.
- [x] Incomplete current schema: missing required contact column prevents the database version from advancing.
- [x] Invalid primary ID type: non-BIGINT ID prevents the database version from advancing.
- [x] Missing AUTO_INCREMENT: schema gate rejects the ID and prevents version advancement.
- [x] Misordered required index: schema gate rejects the index and prevents version advancement.
- [x] Wrong secondary-index uniqueness: unique `status` index is rejected and the database version is not advanced.
- [x] Failed database-version option write: the upgrade returns failure and leaves the old version unchanged.
- [x] Migration error notice is hidden from non-administrators and visible to administrators.
- [x] Build #653 passed all five PHP matrix jobs (8.1–8.5), migration integration tests, and production package build: https://github.com/dejabey/hatnikotni-chat/actions/runs/37124265410. Commit: `eebf15e65703f1b4634937e46c91da346eba59c8`.
- [x] Integration job output explicitly reported PASS for all eleven cases above.
- Scope limitation: the test harness invokes migration/upgrade routines against real MySQL with a small WordPress-option/`$wpdb` adapter. It does not boot WordPress, test the full activation/init lifecycle, or test real `dbDelta()` repair. Manual staging checks remain required.
- These automated tests use an ephemeral GitHub Actions MySQL service only. No staging or production database was accessed or modified. PR #3 remains draft and unmerged.


## WordPress-backed migration CI — 2026-10-03

- [x] Added a disposable WordPress 6.6 + MySQL 8.0 CI job using WP-CLI; no staging/production database is used.
- [x] Real WordPress activation migrates legacy contacts/events tables and legacy settings while preserving representative rows.
- [x] Real `init()` upgrade path repairs a missing contacts column using `dbDelta()` before the schema version advances.
- [x] Real `init()` upgrade path repairs a missing `status` index using `dbDelta()` before the schema version advances.
- [x] Build #657 passed all eight jobs, including PHP 8.1–8.5 validation, MySQL migration integration tests, WordPress-backed lifecycle tests, and production package build: https://github.com/dejabey/hatnikotni-chat/actions/runs/37130408312. Commit: `90450534421f653879e9157a04a45b49af8d5550`.
- [ ] Manual staging checks remain required for table/option conflicts, partial failure recovery, frontend behavior, and exact ZIP/artifact provenance. CI does not authorize a staging or production migration.
- No staging or production database was accessed or changed. PR #3 remains draft and unmerged.


## Additional real-WordPress collision coverage — 2026-10-03

- [x] Real WordPress activation pauses on a legacy/current table collision and preserves both tables' rows and legacy options.
- [x] Real WordPress activation pauses on conflicting legacy/current settings options, preserves both values, creates no current table, and does not advance the DB version.
- [x] Build #659 passed all eight jobs for commit `d5a0f4ea2a8464df5bd47774b8b3bed8995fcdba`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132015156.
- The WordPress-backed suite now covers eight scenarios, including actual activation/init, `dbDelta()` column/index repair, populated and empty-current-table collisions, conflicting options, an injected real WordPress `$wpdb` `RENAME TABLE` failure followed by successful retry, and deactivate/reactivate migration before defaults/schema installation. Build #670 passed all eight jobs for the rename-failure test commit: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132997229. Build #675 passed all eight jobs for the expanded empty-table-collision and deactivate/reactivate test commit `6e7ca5d54477615207a5932297dc8c22d442e04e`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133763454.
- All automated database tests use ephemeral GitHub Actions services. No staging/production database was accessed or changed.


## Routing-state migration assertion — 2026-10-03

- [x] WordPress-backed activation test verifies the legacy routing-state option is copied exactly and deleted only after the new value is verified.
- [x] Build #662 passed all eight jobs for commit `bd64f13fe23a9a43a3b3066b187e363e00796f85`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132140225.
- This assertion supplements the real WordPress tests for activation migration, `dbDelta()` column/index repair, table collisions, and option conflicts.
- No staging/production database was accessed or changed.


## Latest migration-hardening CI confirmation — Build #665 (2026-10-03)

- [x] Re-checked live GitHub Actions run #665: all eight jobs passed for current branch HEAD `a1e81919aaf79ade4ec4f59ea9a106efc2cad788`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132255572.
- [x] Job-level result confirmed: PHP 8.1–8.5 validation, MySQL migration integration tests, WordPress 6.6 lifecycle tests, and production package build all completed successfully.
- [ ] Still required before migration/release: controlled failure/retry tests on a disposable clone, remaining runtime/privacy/routing/cache/WooCommerce checks, SHA-256 provenance check for the exact candidate ZIP, and correlation of the complete Plugin Check report to the same candidate.
- PR #3 remains draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.


## Latest migration-hardening CI — Builds #670 and #671 (2026-10-03)

- [x] Build #670 passed all eight jobs, including PHP 8.1–8.5 validation, MySQL integration tests, WordPress 6.6 lifecycle tests, and production package build. The WordPress-backed suite injects a real `$wpdb` rename failure after the contacts table has been renamed, verifies that the schema version does not advance and the remaining legacy events table survives, then retries and verifies both data rows.
- [x] Build #671 passed all eight jobs for the subsequent handoff commit.
- These CI tests use disposable GitHub Actions WordPress/MySQL services only. They do not constitute permission to migrate the live staging or production database.
- [ ] Controlled migration matrix on a disposable clone, remaining frontend/runtime checks, exact ZIP SHA-256 provenance, and Plugin Check correlation remain release gates.
- PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.


## Expanded lifecycle coverage — Build #675 (2026-10-03)

- [x] Real WordPress activation pauses when the current contacts table exists but is empty while the legacy contacts table contains a row; both tables and the legacy option remain unchanged, and the schema version does not advance.
- [x] Real WordPress deactivate/reactivate sequence migrates legacy contacts/events and settings/routing-state options before defaults or schema installation; representative rows and option values survive.
- [x] Build #675 passed all eight jobs for commit `6e7ca5d54477615207a5932297dc8c22d442e04e`: PHP 8.1–8.5, MySQL integration, WordPress 6.6 lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133763454.
- These are isolated CI tests, not staging results. Keep the manual staging-clone checkboxes below open until tested on a disposable clone.



## Contact input validation hardening — Build #686 (2026-10-04)

- [x] Server-side validation rejects contact names longer than 100 characters, roles longer than 100 characters, and descriptions longer than 255 characters; limits match the custom-table schema and the admin form.
- [x] Unicode-aware character counting uses `mb_strlen` when available and a UTF-8 regex fallback otherwise.
- [x] Contract checks verify the validation error codes and admin-facing messages.
- [x] Build #686 passed all eight jobs for code commit `c447ac724aa9b88425a295480b90b4d2aa04f480`, including PHPCS on PHP 8.1–8.5, migration integration, WordPress lifecycle tests, and production package build: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172705507.
- [ ] On a disposable staging clone, verify that values at each exact maximum save successfully and values one character over are rejected with the expected admin error message. Do not test by changing production contacts.
- [ ] Verify the final candidate ZIP SHA-256 against the CI artifact and correlate the complete Plugin Check report with that exact artifact.
- No staging/production database was accessed or changed. PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.



## UTM attribution input bounds — Build #691 (2026-10-04)

- [x] Incoming UTM values are bounded to the matching analytics-table column lengths: source/medium 100 characters; campaign/term/content 150 characters.
- [x] Cookie-decoded values are bounded again before use, protecting against older or malformed oversized attribution cookies.
- [x] Contract assertions cover all five field limits. Build #691 passed all eight jobs for commit `179e60f74df2013f6a473c2631df5cabca52171d`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172876517.
- [ ] On a disposable staging clone, verify long UTM values are bounded and analytics insertion still succeeds when consent is granted. Verify attribution remains absent when consent is not granted.
- No staging/production database was accessed or changed. PR #3 remains draft and unmerged.



## Post-consent UTM capture — Build #700 (2026-10-04)

- [x] Added a same-site capture endpoint so a visitor who accepts analytics on a UTM landing page can capture attribution without reloading the page.
- [x] Server-side `init` capture still enforces `HATNCH_Privacy::has_analytics_consent()`; the endpoint does not bypass the consent gate.
- [x] Added a 3,500-character guard on the URL-encoded campaign cookie value and a contract assertion for the guard.
- [x] Build #700 passed all eight jobs for commit `e45c73aa5d448a67ebb85085dd7c4fce4fb0b14b`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37173121178.
- [ ] On a disposable staging clone, land on a URL with UTM values before consent, accept analytics without reloading, then verify the campaign cookie and the next click event's UTM attribution.
- [ ] Repeat with consent rejected and with consent withdrawn; verify no attribution is stored/retained. Test oversized multibyte UTM values and confirm the browser is not sent an oversized campaign cookie.
- No staging/production database was accessed or changed. PR #3 remains draft and unmerged.
