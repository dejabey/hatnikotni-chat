# Claude Continuation Handoff — Hatnikotni Chat

**Prepared:** 2026-11-17  
**Repository:** https://github.com/dejabey/hatnikotni-chat  
**Current work branch:** `fix/contact-status-result-0.1.26`  
**Current PR:** [#7 — Fix contact status update result handling (0.1.26 candidate)](https://github.com/dejabey/hatnikotni-chat/pull/7)  
**Current source commit:** `25d0d40aa6b0d1ae3749dad5014a33d94932dab5`  
**Current plugin candidate:** Hatnikotni Chat 0.1.26  
**Status:** PR open and Draft; no merge, no production release.

This document is a handoff for Claude or another developer to continue the work without relying on the previous ChatGPT conversation. It distinguishes repository/tool-verified facts from user-reported staging facts and unresolved tests.

## 1. Immediate state and important distinction

### Verified from GitHub on 2026-11-17
- PR #7 is **OPEN / DRAFT / NOT MERGED**.
- PR #7 head branch is `fix/contact-status-result-0.1.26`.
- PR #7 head SHA is `25d0d40aa6b0d1ae3749dad5014a33d94932dab5`.
- PR #7 base is `recovery/staging-0.1.25-audit`, base SHA `26d9699e258ce54023eb813ea267942c3062de71`.
- PR #6 is also **OPEN / DRAFT / NOT MERGED**. It is the recovery/documentation baseline for 0.1.25.
- Latest GitHub Actions runs returned by the repository API were #843 and #842; both completed successfully on the same source SHA above. The latest observed run is [#843](https://github.com/dejabey/hatnikotni-chat/actions/runs/41997762597).
- CI #842 produced artifact `hatnikotni-chat-0.1.26`, artifact ID `13401470474`, SHA-256 `78b31a781f3b9629d4ef2795f29e1a043af9faf9fc05173be1ec318b3d781fba`.
- PR #7 has no GitHub review comments, issue comments, or submitted reviews at the time of inspection.
- No code or documentation changes were made to GitHub in the handoff-preparation inspection before this handoff commit.

### User-reported, not independently verified
- On 2026-11-17, the user said they had installed Hatnikotni Chat **v0.1.26 on staging.perlis.xyz** using the ZIP previously supplied.
- This staging installation has NOT yet been independently confirmed through WPVibe after that report. Treat it as **USER-REPORTED / PENDING VERIFICATION**, not as a verified deployment.
- The exact installed ZIP is expected to be the CI #842 artifact above, but the installed site's files/version/hash have not yet been checked. Do not claim cryptographic package correlation until verified.

## 2. Development work completed in PR #7 (source-level)

PR #7 is the 0.1.26 candidate built on top of the recovered 0.1.25 staging source baseline. Its code changes include:

### Round Robin concurrency and persistence
- Global Round Robin and purpose-role lanes use database advisory locks (`GET_LOCK` / `RELEASE_LOCK`) with separate state keys/locks.
- Eligible contacts are re-fetched inside the lock.
- Cursor state is read directly from the database rather than relying on potentially stale `get_option()` cache reads.
- Cursor is persisted through a prepared atomic MySQL upsert, avoiding deprecated `VALUES()` syntax.
- WordPress option caches are invalidated after the database write.
- Persisted state is read back and verified; lock release is handled in `finally`.
- Lock release failure emits the documented `hatnch_routing_lock_release_failed` action.
- Temporary `[HATNCH_QA322]` diagnostic logging has been removed from the candidate source.

### Contact status result handling
- `HATNCH_Contacts::set_status()` now distinguishes an unchanged existing contact from a missing/deleted contact when `$wpdb->update()` returns zero.
- `save()` handles a concurrent deletion after an update and returns an appropriate `WP_Error`.

### Security and code-quality changes
- Purpose-role input is centrally sanitized and validated.
- Analytics action nonce checks are explicit.
- Analytics SQL uses explicit prepared statements.
- Admin denials return actual HTTP 403 responses.
- PHPCS baseline was resolved; full repository PHPCS is now a required CI gate.
- Documentation and changelog were updated for 0.1.26 and the extension action.

## 3. CI evidence

Latest observed CI:
- [Run #843](https://github.com/dejabey/hatnikotni-chat/actions/runs/41997762597) — success.
- [Run #842](https://github.com/dejabey/hatnikotni-chat/actions/runs/41997753909) — success.
- Both runs target commit `25d0d40aa6b0d1ae3749dad5014a33d94932dab5`.

The CI workflow covers:
- PHP syntax checks across PHP 8.1–8.5.
- JavaScript syntax and skeleton contract.
- Incremental PHPCS across PHP 8.1–8.5.
- Full-repository PHPCS hard gate (PHP 8.1).
- 12 independent concurrent Round Robin workers in a disposable GitHub Actions WordPress/MySQL environment, expecting exactly 6 selections per each of two active contacts.
- WordPress migration lifecycle and migration integration tests.
- Clean production package build and artifact upload.

**Important limitation:** CI concurrency runs in a disposable environment (WordPress 6.6 / MySQL 8.0 as documented in PR). It does not prove behavior on the current staging host or with staging's actual object-cache configuration.

## 4. Staging history and current test status

### Previous staging test on 0.1.25 (observed before user installed 0.1.26)
The prior assistant inspected staging while it was still running 0.1.25:
- Plugin version reported as 0.1.25.
- `WP_CACHE = false`.
- Object cache type reported as `default`.
- No `wp-content/object-cache.php` drop-in was found in the inspected path.
- Contacts observed: PakYa (ID 1, active), PakSu (ID 3, active), znz (ID 5, inactive).
- Previous global cursor value was `last_contact_id=3`.
- Twelve near-simultaneous endpoint calls were initiated, but the HTTP helper followed redirects and returned mixed response bodies (some WhatsApp/Facebook content, some site HTML). Therefore these calls are **not proof that all 12 requests reached the routing handler**.
- The debug log contained new `state_verify_failed` events, including examples where selected ID and persisted ID differed. The database cursor after that test was `last_contact_id=1`.
- This reproduced a persistence/state-verification symptom under the old 0.1.25 implementation, but did not isolate the exact cause.
- The old log also contains unrelated plugin notices/deprecations. Do not attribute unrelated notices to Hatnikotni Chat.
- No WhatsApp message was sent; the endpoint only redirects to a WhatsApp URL.
- Cursor was not reset and `debug.log` was not cleared.

### After user-installed 0.1.26
- User has reported installation on staging.
- No post-install WPVibe inspection has yet been performed.
- The earlier cache findings apply to the earlier 0.1.25 inspection only; re-check the current staging configuration before making claims about the 0.1.26 environment.
- Round Robin runtime verification on 0.1.26 is **PENDING**.
- Persistent object-cache verification is **PENDING**; earlier inspection found no persistent object-cache drop-in, but verify current state.

## 5. WPVibe quota / access

Last recorded WPVibe profile check (2026-11-05):
- Usage reported: 106 of 100 calls.
- Remaining calls: 0.
- One banked reset was available.
- The returned reset estimate was around 2026-11-05 22:40 Malaysia time.

That quota reading is stale relative to this handoff date. **Claude must re-check WPVibe profile/usage before assuming the current quota state.** Do not spend a banked reset unless the user explicitly agrees. WPVibe account links previously available:
- `link_6a64061b22b081919ab28c146bf2a227`
- `link_6a69d63d5fb88191a85d4149271707b2`

WPVibe tools available in the previous session included site info, read-only file/log inspection, REST API, WP-CLI emulator, and page HTML retrieval. WP-CLI is a constrained emulator, not a real shell. The earlier `get_page_html` endpoint approach followed redirects and was unsuitable for proving concurrent handler execution.

## 6. Exact next actions

Do these in order, after checking WPVibe quota and current GitHub state:

1. **Verify staging installation**
   - Read site/plugin metadata and confirm Hatnikotni Chat reports 0.1.26 active.
   - If possible, inspect plugin header/version and compare the installed source/package provenance with commit `25d0d40...` and artifact SHA-256 above.
   - Record WordPress/PHP versions and current cache configuration.
   - Do not infer current values from the old 0.1.25 inspection.

2. **Inspect pre-test state read-only**
   - Read the current routing method, active contact set, and `hatnch_routing_state`.
   - Preserve current cursor value. Do not reset it.
   - Inspect the current log tail without truncating or clearing it.

3. **Run controlled 0.1.26 Round Robin tests**
   - First perform a small sequential test and capture selected contact IDs plus persisted cursor.
   - Then run a genuinely concurrent test with a reliable request method that records HTTP status, response headers/Location, and whether the WordPress routing handler was reached. Do not use a helper that silently follows the WhatsApp redirect and then treat the final Facebook/WhatsApp HTML as the routing response.
   - No actual WhatsApp messages should be sent; this plugin endpoint redirects only.
   - If there are only two active contacts, document the expected alternation/distribution; do not assume exact 6/6 from a test that did not confirm all requests reached the handler.
   - Correlate request evidence with database cursor and new log entries.
   - Do not clear logs or reset state.

4. **Report outcome honestly**
   - Separate verified source/CI, verified staging facts, user-reported facts, and unresolved items.
   - If the request mechanism cannot prove handler execution, mark the concurrency test inconclusive rather than passed.
   - If failures occur, preserve logs/state and stop before trying destructive cleanup.

5. **Update GitHub handoff**
   - Record actual staging test data, timestamps, request method, request count, selected IDs, cursor before/after, cache setup, and relevant log evidence.
   - Update `PROJECT-HANDOFF.md`, this handoff document, and PR #7 body as appropriate.
   - Add tests/code changes only on the development branch and run CI again.
   - Keep PR #7 Draft until staging evidence and release gates are reviewed.

## 7. Decisions and non-negotiable boundaries

- GitHub is the single source of truth for plugin source, tests, and handoff documentation.
- All source changes go through branches/commits/PRs; do not edit live staging plugin source directly.
- CI artifact is a build artifact, not automatically an approved production release.
- Do not merge PR #7 or PR #6 without the user's explicit approval.
- Do not merge PR #4 or PR #5.
- Do not deploy to production or publish a release without explicit approval.
- Do not deploy the old unofficial 0.1.26 cache-isolation prototype.
- Do not reset staging's routing cursor or clear/truncate `debug.log`.
- Do not send WhatsApp messages during testing.
- Do not uninstall/reinstall the plugin as a debugging shortcut; uninstall may delete plugin data.
- Do not change database settings, contacts, or routing configuration unless the test plan requires it and the user has approved the specific change.
- User's authorization to run the Round Robin verification does not authorize merge, release, production deployment, or destructive cleanup.
- Never report a test as passed unless the test actually established the required condition.

## 8. Useful links

- Repository: https://github.com/dejabey/hatnikotni-chat
- PR #7: https://github.com/dejabey/hatnikotni-chat/pull/7
- PR #6: https://github.com/dejabey/hatnikotni-chat/pull/6
- CI #843: https://github.com/dejabey/hatnikotni-chat/actions/runs/41997762597
- CI #842: https://github.com/dejabey/hatnikotni-chat/actions/runs/41997753909
- Staging: https://staging.perlis.xyz/

## 9. Addendum — 2026-11-18

- WPVibe quota on 2026-11-17: 81/100 remaining; banked reset unused.
- Staging state was verified read-only; see `PROJECT-HANDOFF.md` (2026-11-18). Version 0.1.26 is active and routing source matches. Round Robin runtime remains **PENDING**.
- The prior HTTP method is replaced by `tests/staging-rr-http-check.sh`, which records status, `X-Redirect-By` and the hashed `Location` without following redirects. It must run from a machine that can reach staging; the cloud workspace gets proxy 403.
- With user approval, `release/0.1.26` was created from `main` and PR #8 (Draft) was opened as the direct path to `main`. PR #7's branch remains as-is. CI #856/#857 passed all 8 jobs on `998820c` after the incremental-PHPCS deleted-file fix.
- 2026-11-18 ~12:00 UTC: staging Round Robin HTTP test **CONFIRMED** (sequential 3,1,3,1; concurrent 12 → 6/6; all handler-confirmed 302; cursor 1 → 1). Package provenance confirmed against CI #842. Details are in `PROJECT-HANDOFF.md`.
- 2026-11-19: Plugin Check on 0.1.26 found 0 errors and 10 warnings. Resolved in **0.1.27** (`7263a09`, CI #868/#869 green, artifact ID `14486959133`). 0.1.27 is not yet installed on staging. See `PROJECT-HANDOFF.md`.
- 2026-11-19 01:11 UTC: 0.1.27 installed on staging. Provenance matches CI #868, Plugin Check reports no errors, and the Round Robin HTTP check passed (3,1,3,1; 6/6; cursor 1→1). Remaining: manual browser matrix, readme/POT clean-up, and the owner's merge decision.
- 2026-11-19: the browser matrix on 0.1.27 found UTM attribution never stored (since 2026-09-23). Fixed in **0.1.28** (`3f22fdc`) with a regression test that failed first (CI #878) and then passed (CI #880). Next: readme/POT clean-up in 0.1.28, staging install, re-test.
- 2026-11-19 11:35 MYT: 0.1.28 on staging. Attribution CONFIRMED (event #53 carries c0127); Round Robin CONFIRMED (3,1,3,1; 6/6). Pending: 0.1.28 hashes, Plugin Check, and the owner's merge decision on PR #8. Note: another Claude session committed `8ea1b32` with the same findings concurrently.
