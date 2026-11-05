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
