# Staging 0.1.25 reconciliation audit

Date: 2026-11-05

## Baseline
The supplied archive `hatnikotni-chat(3).zip` is the user's confirmed staging package for Hatnikotni Chat 0.1.25. It contains 40 files (47 ZIP entries including directory entries). Runtime metadata in the archive identifies plugin version 0.1.25 and database schema 1.2.0. The archive SHA-256 recorded during inspection is `9ccfd0c9698fdcf5a46e3edbc7a4e82e96a9c52aa864bb9a79ef68f5fa8f5175`.

## GitHub comparison
The branch `ui-compact-contact-form` still identifies plugin version 0.1.8 and database schema 1.1.0. The principal runtime files differ from the staging archive, including:
- `hatnikotni-chat.php`
- `includes/class-hatnch-routing.php`
- `includes/class-hatnch-contacts.php`
- `includes/class-hatnch-plugin.php`
- `includes/class-hatnch-admin.php`
- `includes/class-hatnch-whatsapp.php`
- `assets/css/hatnikotni-chat.css`
- `CHANGELOG.md`

The purpose-routing JavaScript `assets/js/hatnikotni-chat-purpose.js` and purpose-routing documentation `docs/PURPOSE-BASED-ROUTING.md` are present in the staging archive but absent from `ui-compact-contact-form`.

Other examined branches do not provide a complete 0.1.25 baseline:
- `diagnostics/rr-lock-staging` adds one routing diagnostics commit on top of the 0.1.8 line.
- `docs/handoff-2026-10-31-version-audit` remains on a legacy 0.1.0 line.
- `migration-hardening` retains 0.1.8 plugin metadata.
- `release/0.1.1-rc1` is an older release-candidate line.
- `review/wporg-remediation` is a legacy remediation line.

## Static checks observed on the supplied archive
- 13/13 PHP files passed `php -l`.
- 3/3 JavaScript files passed `node --check`.
- ZIP integrity check passed.

These checks do not establish WordPress runtime, database, browser, or concurrent-request correctness.

## Known routing risk
The 0.1.25 routing implementation contains QA diagnostic `error_log()` calls tagged `[HATNCH_QA322]`. A previous CI run on PR #5 failed PHPCS because debug `error_log()` was present. The diagnostic output is useful evidence for investigating the staging round-robin state verification failures and must not be discarded before the failure is understood. It should be isolated from production code or replaced with a controlled diagnostic mechanism.

The previous near-simultaneous staging run recorded `state_verify_failed` events where the selected cursor state did not match the persisted value. The suspected WordPress options/object-cache interaction remains a hypothesis, not a proven root cause. Advisory locks alone have not yet demonstrated correct behavior under actual staging concurrency.

## Disposition
- Treat the supplied 0.1.25 archive as the staging reference, not as an already-approved production release.
- Do not merge PR #4 or PR #5.
- Do not deploy the unofficial 0.1.26 cache-isolation prototype.
- Do not change staging state, routing cursor, or `debug.log` during source reconciliation.
- Reconcile the full 40-file staging source into a dedicated recovery branch only after per-file provenance and tests are recorded.
- Validate direct, random, purpose-based, sequential round-robin, and genuinely concurrent round-robin behavior against the target database before release.
- Keep production release on HOLD until runtime, privacy/consent, accessibility, browser, Plugin Check, and package-provenance gates pass.


## Detailed routing source review — 2026-11-05

The supplied 0.1.25 `includes/class-hatnch-routing.php` implements:
- Global Direct, Random and Round Robin routing.
- Purpose-to-role allow-list mapping with global fallback for unknown purposes or empty eligible roles.
- Role-specific round-robin cursor keys (`hatnch_rr_<role>`) separate from the global cursor (`hatnch_routing_state`).
- MySQL/MariaDB advisory locks via `GET_LOCK(..., 5)` and `RELEASE_LOCK(...)`, with lock failure returning no contact.
- Cursor persistence and verification through WordPress `get_option()` / `update_option()`.
- QA logging through `error_log('[HATNCH_QA322] ...')` at empty-contact, lock-failure, state-verification, resolved and release-unexpected stages.

### Concurrency review

The advisory lock serializes the critical section only when all requests use the same database connection/session semantics and the database supports named locks. The source then reads and writes cursor state through the WordPress Options API. Because the staging test observed a selected cursor that differed from the value returned by the subsequent read, object-cache interaction and persistence behavior require isolation testing. This is a diagnostic hypothesis, not a proven cause. The source also does not check the return value of `update_option()`; the subsequent read is its only verification.

The current evidence does not justify claiming that the round-robin defect is fixed. A correction should be tested against the actual staging database with cache enabled/disabled and real parallel requests. Do not change the live cursor or clear the diagnostic log during these tests.

### Purpose routing behavior

The supplied purpose-routing documentation says Random chooses an eligible contact at random and Round Robin rotates independently per role. In the supplied PHP source, global Random uses weighted selection, while purpose-role Random uses uniform `wp_rand(0, count - 1)` selection. This is a behavior difference worth explicitly deciding and documenting; it must not be silently changed during concurrency remediation.

## Changelog provenance discrepancy

The supplied archive's `CHANGELOG.md` labels its 0.1.8 entry as an Analytics visual refresh and includes an unreleased Analytics Management section describing schema 1.2.0. Conversation-recovered version notes separately associate 0.1.8 with Analytics Management and 0.1.9 with visual refresh. These records conflict. Preserve both pieces of evidence and do not publish the version-history table as a verified canonical changelog until the original versioned artifacts/commits or dated handoffs resolve the discrepancy.
