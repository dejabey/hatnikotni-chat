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
- [x] Build #643 passed all five PHP matrix jobs (8.1–8.5), migration integration tests, and production package build: https://github.com/dejabey/hatnikotni-chat/actions/runs/37123621773. Branch commit: `5e2381194d3e1007b3744cd6adbfe912f99a9c9c`.
- [x] Integration job output explicitly reported PASS for all five cases above.
- Scope limitation: the test harness invokes migration/upgrade routines against real MySQL with a small WordPress-option/`$wpdb` adapter. It does not boot WordPress, test the full activation/init lifecycle, test real `dbDelta()` repair, or verify administrator-notice permissions. Manual staging checks remain required.
- These automated tests use an ephemeral GitHub Actions MySQL service only. No staging or production database was accessed or modified. PR #3 remains draft and unmerged.
