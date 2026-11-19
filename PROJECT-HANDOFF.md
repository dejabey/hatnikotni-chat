# CURRENT PROJECT STATE — 2026-11-19 ~14:20 MYT (0.1.29 on staging: artifact identified as CI #916)

> Supersedes the 13:45 and both 13:55 sections below where they conflict.

- **Outer-artifact check (owner, 14:12 MYT):** the downloaded `hatnikotni-chat-0.1.29.zip` hashes to `E20BE52FA9385503FAC737113C8213454BBB0C2A89D57E1D1DB395B1D0ABA4C9`, so the check against CI #913 printed "GAGAL".
- **Root cause:** the owner downloaded the artifact of **CI #916**, not #913. #916 is the `pull_request` run for the docs-only commit `8a4a4e8`: artifact ID `14502292000`, created 05:37:32 UTC, digest `sha256:e20be52f…aba4c9`, which matches exactly. Every docs commit on the branch triggers two runs and a new 0.1.29 artifact with its own digest (#913–#922). Claude named the candidate run but gave no direct link at the time, so a later run was easy to pick.
- **Why the file is still valid:** `git diff 2361579 7e0838b -- hatnikotni-chat.php uninstall.php readme.txt assets includes languages` is empty, so all 0.1.29 artifacts contain identical plugin source. `main` (`4360ea3`) is an ancestor of `8a4a4e8`, so the PR merge ref used by #916 has the same tree.
- **Provenance chain:** `8a4a4e8` (PR merge ref) → CI #916 → outer `E20BE52F…` (hash matched, tool-verified against the GitHub API digest) → inner `hatnikotni-chat-0.1.29.zip` `8AF6ABC919A19C602BB4A089EB90D6CBF66C792A39953DF58C57C5E95DC36440` (owner-reported). **CONFIRMED** once the owner confirms that this inner ZIP is the file uploaded at about 13:42; until then **PENDING**.
- **Duplicate execution again:** commit `12a062b` (13:43:57 MYT) was made in this same workspace and session (reflog), from a duplicated execution of the 13:42 turn. It added `tests/verify-artifact.sh` (Git Bash; that turn reports a synthetic self-test) and the second "~13:55" section below. That duplicated turn also reports its own WPVibe read, so **probably two WPVibe calls were spent instead of one** on the post-install check. Both "~13:55" sections are kept as history; they agree on the state. Both verifiers are kept: `tests/verify-artifact.ps1` (PowerShell) and `tests/verify-artifact.sh` (Git Bash).
- **Process fix:** give the direct run URL (`https://github.com/dejabey/hatnikotni-chat/actions/runs/<run id>`) together with the digest for that exact run. When docs commits have moved HEAD, either say which run to use or update the link to the latest run and its digest.

## Immediate next action
1. Owner confirms that the uploaded file was the inner ZIP `8AF6ABC9…`.
2. Owner runs "Ujian 0.1.29" (`docs/staging-test-plan.md`).

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:55 MYT (0.1.29 installed on staging; testing pending)

> Supersedes the earlier 2026-11-19 sections below where they conflict.

- **Owner reports 0.1.29 installed on staging** (13:45 MYT). USER-REPORTED.
  - **Inner ZIP (owner-reported, 14:08 MYT):** `hatnikotni-chat-0.1.29.zip`, SHA-256 `8AF6ABC919A19C602BB4A089EB90D6CBF66C792A39953DF58C57C5E95DC36440`, extracted from the downloaded artifact.
  - Package provenance stays **PENDING** until two things are confirmed: (a) the outer-file check printed LULUS, i.e. the download equals the CI #913 digest `f85ad689…c501e`; (b) this inner ZIP is the file that was uploaded to staging.
- **WPVibe read-only check (one `db query`, 2026-11-19 ~13:50 MYT):**
  - WordPress's own update-check record lists `hatnikotni-chat/hatnikotni-chat.php` as **0.1.29**. This is indirect evidence of the installed header version.
  - Data preserved: `hatnch_db_version` 1.2.0; contacts `1:active, 3:active, 5:inactive`; cursor `last_contact_id=1`; 53 events, last #53 at 11:31:43 (no events since the upgrade).
  - `hatnch_settings` unchanged (12 keys; `round_robin`; purpose routing off). The new keys are absent, so the defaults apply: label chip **on**, greeting card **off**.
  - `hatnch_routing_failures` absent, so no routing failures recorded.
- **Owner working preferences (2026-11-19):**
  1. When an artifact must be downloaded, give the **direct GitHub Actions run link**, e.g. https://github.com/dejabey/hatnikotni-chat/actions/runs/44465896794 (CI #913, 0.1.29 candidate). The artifact is at the bottom of that page under "Artifacts".
  2. Provide the **SHA-256 verification command** every time. Script: `tests/verify-artifact.ps1` (PowerShell, built-in cmdlets only). Written but **not executed** by Claude: PowerShell is not available in the cloud workspace, and installing it there was not permitted.
  3. The owner uses the **free WPVibe plan, which has a daily limit**. Batch reads into a single `db query` (UNION ALL) where possible, skip `list_sites` unless quota matters, and never spend calls on what the owner can see directly.

## Immediate next action
1. Owner runs `tests/verify-artifact.ps1` on the downloaded artifact and reports both hashes, then works through "Ujian 0.1.29" in `docs/staging-test-plan.md`.
2. After the owner reports, Claude does one batched WPVibe read (events, cursor, failure counter) to cross-check.

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:55 MYT (0.1.29 installed on staging; testing pending)

> Supersedes the earlier 2026-11-19 sections below where they conflict.

- **Owner reports (13:42 MYT) that 0.1.29 is installed on staging.** Hashes of the downloaded artifact and the inner ZIP are not reported yet, so **package provenance for 0.1.29: PENDING**.
- **Post-install state (WPVibe, read-only, 2026-11-19 ~13:50 MYT):** plugin `0.1.29` active; `hatnch_db_version` 1.2.0; routing `round_robin`, purpose routing off; contacts unchanged (ID 1 and 3 active, ID 5 inactive with the 100×"R" role); cursor `last_contact_id=1`; lane cursors `business_support=3`, `general_enquiries=1` (unchanged); 53 analytics events (latest #53, 11:31:43); **no `hatnch_routing_failures` option** (no failure recorded). The saved settings predate 0.1.29, so `button_show_label` and the `greeting_*` keys are absent: the label shows (default on) and the greeting card is off, as designed.
- **New helper `tests/verify-artifact.sh`:** checks the outer artifact's SHA-256 against the recorded digest, extracts the inner ZIP, and prints its SHA-256 and plugin version. Self-tested in the workspace with a synthetic artifact (match exits 0; mismatch exits 1 with "Do not install"). A PowerShell equivalent is in the chat answer of 2026-11-19 13:55 MYT and below.
- **Owner instruction (2026-11-19 13:42 MYT), standing for this project:** whenever an artifact is to be downloaded, give the direct GitHub Actions run link (for example https://github.com/dejabey/hatnikotni-chat/actions/runs/44465896794 for the 0.1.29 candidate, CI #913; artifacts are at the bottom of the run page) together with the expected digest and the verification command.

PowerShell (Windows) check, run in the Downloads folder:
```powershell
$expected = 'f85ad689b589c51ddf055de400e189faade95689b5c0724b44c74e1fab1989a7'
$actual = (Get-FileHash .\hatnikotni-chat-0.1.29.zip -Algorithm SHA256).Hash.ToLower()
if ($actual -eq $expected) { 'OK: matches CI #913' } else { "FAIL: $actual" }
Expand-Archive .\hatnikotni-chat-0.1.29.zip -DestinationPath .\hatnch-0.1.29 -Force
Get-FileHash .\hatnch-0.1.29\*.zip -Algorithm SHA256
```

## Immediate next action
1. Owner reports both hashes and runs "Ujian 0.1.29" (`docs/staging-test-plan.md`).
2. Claude cross-checks each consent step against the events table (read-only; 53 events now).

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:45 MYT (0.1.29 release candidate cut)

> Supersedes the earlier 2026-11-19 sections below where they conflict.

- **Version bumped to 0.1.29** on `dev/0.1.29`, with owner approval (2026-11-19 13:31 MYT: "ok jalan terus jika itu adalah langkah terbaik"). Updated: plugin header, `HATNCH_VERSION`, readme `Stable tag`, readme Description feature list, readme Changelog and Upgrade Notice, POT/PO `Project-Id-Version`, CHANGELOG heading.
- **Staging test plan:** `docs/staging-test-plan.md` → "Ujian 0.1.29" (22 steps: package hash, button, consent card, keyboard/no-JS, greeting card, Round Robin, Malay wording).
- **Candidate artifact (use this one):** commit `2361579`, CI **#913** (push, run `44465896794`, all 8 jobs green; #914 PR run also green). Artifact `hatnikotni-chat-0.1.29`, ID `14502725441`, 85,319 bytes, `sha256:f85ad689b589c51ddf055de400e189faade95689b5c0724b44c74e1fab1989a7`. The #914 artifact (`1746a61a…`) differs only by ZIP timestamps; do not use it. Later documentation-only commits do not change the plugin package contents, but their CI artifacts will have other digests: always install the #913 artifact.
  - Package contents (from the CI build log, 40 entries): plugin PHP, assets incl. `hatnikotni-chat-greeting.js`, `languages/` with the `msgfmt --check`-compiled `hatnikotni-chat-ms_MY.mo`; no `tests/`, `docs/` or vendor files.
  - Claude could not download the artifact from the cloud workspace (GitHub's blob storage is blocked here), so the inner ZIP hash is not yet recorded. The owner should report both hashes after downloading.
- **Not authorized:** merge of PR #9, tag, GitHub release, production deployment, WordPress.org submission. Installing on staging is the owner's action and changes staging; Claude does not install it.

## Immediate next action
1. Owner downloads the 0.1.29 artifact, verifies the digest, installs the inner ZIP on staging and runs "Ujian 0.1.29".
2. Claude cross-checks read-only via WPVibe (version, cursor, analytics events, failure counter) after the owner reports.

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:35 MYT (0.1.29 scope items 1–4 implemented)

> Supersedes the earlier 2026-11-19 sections below where they conflict.

- **Malay translation complete:** `hatnikotni-chat-ms_MY.po` has 226/226 strings translated (was 128/98). Placeholder parity checked by script; the delete-all confirmation keeps the literal `DELETE` token (contract-guarded). Translations were written by Claude and have **not** been reviewed by a native speaker; the owner should skim the admin screens in Malay during staging testing.
- **`Contributors: zaryl`** added to `readme.txt`. The owner supplied the username on 2026-11-19 13:21 MYT; Claude confirmed that https://profiles.wordpress.org/zaryl/ exists (joined June 2009). Contract-guarded.
- **0.1.29 scope status:** items 1 (concept B, owner-accepted look; concept C opt-in), 2 (Round Robin fallback), 3 (translation) and 4 (Contributors) are implemented. Item 5 (click log) stays deferred by product decision.
- **Still required before any release:** bump the version to 0.1.29 (plugin header, `HATNCH_VERSION`, `Stable tag`, POT/PO `Project-Id-Version`, CHANGELOG heading, readme changelog and upgrade notice), CI on the bumped HEAD, then staging installation and the browser matrix for the new UI. Each step after CI needs the owner's approval.

## Immediate next action
1. Confirm CI on the pushed HEAD.
2. Ask the owner whether to cut the 0.1.29 release candidate (version bump + candidate artifact) for staging testing.

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:20 MYT (0.1.29 in development: Round Robin fallback)

> Supersedes the earlier 2026-11-19 sections below where they conflict.

- **0.1.29 scope item 2 (Round Robin fallback and observability) implemented.** See CHANGELOG `0.1.29` → "Round Robin fallback and observability".
  - Lock timeout, cursor read failure, cursor write failure and read-back mismatch now return a fallback contact (active default, else first eligible). The cursor is not moved; the failure is counted in `hatnch_routing_failures` (counts and timestamps only) and `hatnch_routing_failure` fires. The click endpoint counts `no_contact` before its home-page redirect. Admin: Routing → Routing failures.
  - Purpose lane emptied inside the lock now falls back to global routing.
- **Test-first evidence:** `7c603eb` added `tests/routing-fallback-integration.php` and a CI step without the fix. CI #907 (push, run `44463298104`) and #908 **failed** in the WordPress lifecycle job with `FAIL: Lock timeout still returned no contact (visitor would be sent to the home page).` about 5 s after the concurrency test, which shows the second-connection lock really forced the timeout. The fix commit follows; check its CI.
- **Not covered by tests:** read, write and verify failures are not forced in CI (no safe way to make MySQL fail those statements on demand). They share the same `fallback()` path as the lock case and are covered by contract greps only.
- **Staging:** PENDING. Do not try to force a lock failure on staging without explicit authorization. The safe check is the admin "Routing failures" row (expected: "None recorded.") plus the existing HTTP check script.
- **Remaining 0.1.29 scope:** the 98 remaining Malay strings and the `Contributors:` line (owner's WordPress.org username).

## Immediate next action
1. Confirm CI is green on the fix commit (the fallback test must pass).
2. Translate the remaining 98 Malay strings.

---

# CURRENT PROJECT STATE — 2026-11-19 ~13:30 MYT (0.1.29 in development: concept B accepted, concept C added)

> Supersedes the 12:40 section below where they conflict.

- **Owner accepted the concept B look** on 2026-11-19 12:59 MYT ("ok saya terima"), from the headless-Chrome preview of commit `a065190`. This is a design acceptance only; staging runtime is still **PENDING**.
- **Concept C (greeting card) implemented, opt-in, off by default.** See CHANGELOG `0.1.29` → "Greeting card". Settings: `greeting_enabled` (false), `greeting_title`, `greeting_text` (empty = translated defaults), `greeting_delay` (0–60 s, default 5).
  - Departures from the canvas, decided by Claude and reported to the owner: no "Dalam talian" (online) dot, because the plugin has no availability data and the dot would be false; no staff-initial avatars (a contacts query on every page and public staff names); no in-card privacy link (the badge remains).
  - The default text makes no reply-time promise. The canvas placeholder "[MASA BALASAN]" is left for the owner to write in the setting.
- **Evidence (workspace; not runtime):** contract tests PASS, including new guards (default off, rendered hidden, sessionStorage only, no cookie, no presence claim); PHPCS 0/0; `node --check` on all JS. Headless Chrome (stubbed WordPress render, served over local HTTP): hidden at t=0, shown after a 1 s delay with focus untouched; the close button hides it and returns focus to the WhatsApp button; it stays hidden after reload in the same session; with purpose routing the CTA opens the chooser (focus on its Close button); if the privacy panel is opened before the delay, the card never appears; Escape hides it; at 390 px width the card is 292 px wide.
- **Remaining 0.1.29 scope:** Round Robin fallback and observability (next), the remaining 98 Malay strings, the `Contributors:` line (owner's WordPress.org username needed).

## Immediate next action
1. Check CI on the pushed HEAD.
2. Start the Round Robin fallback: replace the silent `wp_safe_redirect( home_url( '/' ) )` on lock/verify failure with a safe fallback contact, a PII-free failure counter and a failure action hook, with tests.

---

# CURRENT PROJECT STATE — 2026-11-19 ~12:40 MYT (0.1.29 in development: concept B front end)

> Read [CLAUDE.md](CLAUDE.md) and [AI-DEVELOPMENT-PROTOCOL.md](AI-DEVELOPMENT-PROTOCOL.md) first. This section supersedes the next one where they conflict.

- **Branch / PR:** `dev/0.1.29`, PR #9 → `main` (open, not merged). Plugin header is still **0.1.28**. The version bump to 0.1.29 happens only when the release candidate is cut.
- **Done in this checkpoint: 0.1.29 scope item 1 (concept B button), except concept C.** See CHANGELOG `0.1.29`. Files: `assets/css/hatnikotni-chat.css` (rewritten floating/consent sections), `assets/js/hatnikotni-chat-privacy.js` (segmented choices, close button, focus), `includes/class-hatnch-whatsapp.php` (markup), `includes/class-hatnch-settings.php` and `includes/class-hatnch-admin.php` (new `button_show_label`, default on), POT/ms_MY.po, `tests/skeleton-contract.sh`, `readme.txt`, `docs/architecture.md`.
- **Design decisions made without asking (owner may redirect):**
  - Consent card width **232 px**, as the handoff states. The design canvas artboard "MobileB" still draws 190 px; the handoff value was treated as authoritative.
  - The label chip is **hidden below 481 px** (matches MobileB, which has no chip). The setting turns it off everywhere.
  - "Read More" became **"Details"** ("Butiran"), as agreed for the full text.
  - With no saved choice, **neither** Reject nor Accept is pressed. The old switch rendered "off", which looked like a rejection. The stored cookie is unchanged.
  - The inline shortcode button also gets dark text (same 1.98:1 failure).
- **Evidence (cloud workspace; NOT runtime acceptance):**
  - `tests/skeleton-contract.sh` PASS. The new guards fail against the 0.1.28 CSS (white-on-green regex matches the old file).
  - PHPCS (`phpcs.xml.dist`, WPCS cloned from GitHub because Packagist is blocked here): 0 errors, 0 warnings on the changed PHP files. `node --check` OK. POT via WP-CLI 2.12.0 `make-pot`; PO compiled with polib as a sanity check (no `msgfmt` here; CI runs `msgfmt --check`).
  - Headless Chrome on a stubbed render of `render_floating_button()`: geometry matches the canvas (bubble 60 px / 56 px on phones; badge hit area 44 px at -16 px offset; disc 28 px; card 232 px; choices 32 px high; no overlap between chip and badge hit area), both positions, light and dark pages, purpose-routing variant. Keyboard: Tab order badge → WhatsApp; opening focuses the choice; Escape and close return focus to the badge. Cookie `yes`/`no` written and restored after reload (served over local HTTP).
  - Does **not** prove: real themes, real WordPress output, mobile Safari, screen readers, or caching. Staging: **PENDING**.
- **Not started:** concept C (opt-in greeting card), Round Robin fallback/observability, the remaining 98 Malay strings, the `Contributors:` line (needs the owner's WordPress.org username).
- **Concurrent-write note:** `1e9aa2f` (docs, 11:59 MYT) landed on `dev/0.1.29` after this checkpoint's work began; it was fast-forwarded before committing. Always fetch and compare HEAD before pushing.

## Immediate next action
1. Check CI on the pushed HEAD of PR #9 (all 8 jobs).
2. Owner review of the concept B look (screenshots in chat). If accepted, continue with concept C (opt-in, off by default), then the Round Robin fallback.
3. Staging test of concept B only after a candidate ZIP is built and the owner authorises installing it.

---

# CURRENT PROJECT STATE — 2026-11-19 12:00 MYT (0.1.28 merged to main; next: 0.1.29)

> **Start here in a new session.** Read [CLAUDE.md](CLAUDE.md), then [AI-DEVELOPMENT-PROTOCOL.md](AI-DEVELOPMENT-PROTOCOL.md), then this section. Older sections below are history.

## Where things stand
- **`main` = Hatnikotni Chat 0.1.28.**
  - PR #8 was merged on 2026-11-19 03:48 UTC as merge commit `4360ea3`, after owner approval ("if every angle has been checked and it is the best path, go ahead") and Claude's pre-merge checks.
  - `main`'s tree equals the verified PR head `de6d383` (tree `2b3352f`).
  - CI #898 on `main` passed.
- **0.1.28 verification, all on staging.perlis.xyz:**
  - Package provenance **CONFIRMED**: `ed26d87` → CI #884 artifact `9c3cab14…0254` (hash matched by the owner) → inner ZIP `4B7331F8…5BA5`, which the owner uploaded.
  - Plugin Check: no errors (owner-reported).
  - Round Robin HTTP check, twice: 3,1,3,1 and 12 concurrent → 6/6; cursor 1→1.
  - Campaign attribution **CONFIRMED**: event #53 carries `utm_campaign=c0127`.
  - Browser matrix: 21/21 after the fix.
- **Not done, and none authorized:** no git tag, no GitHub release, no production deployment, no WordPress.org submission. Each needs separate explicit approval.
- **PRs:** #1–#7 closed as superseded; #8 merged. The active development branch is **`dev/0.1.29`** (from `main` @ `4360ea3`).
- **Process note (corrected 2026-11-19 12:05 MYT):** `8ea1b32` (11:35) and `de6d383` (11:44) were first attributed to a separate Claude session. That was wrong: both carry this same session's trailer (`session_018NR6…`), and the project has had only one Claude chat since 2026-11-10. They were a duplicate execution of the same turn within this session, coinciding with the owner's messages at 11:35 and 11:45. The content was consistent and nothing conflicted. Mitigation: always `git pull` and re-check HEAD before writing, and lock merges to an explicit head SHA (done for PR #8).
- **CI minutes:** the repository is **public**, and this repository's workflow uses only standard `ubuntu-latest` runners, which GitHub bills at zero for public repositories. The owner's 2,000-minute free quota (resets 1 Dec) applies to private repositories. CI kept running normally on 2026-11-19 (#884–#898). If runs ever queue with a billing message, stop and tell the owner instead of retrying.

## 0.1.29 scope (agreed with the owner)
1. **Front-end redesign: concept B.** A round WhatsApp button with a shield badge (44 px hit area) and an optional label chip.
   - Concept **C** (team greeting card) as an opt-in setting, off by default.
   - Compact mobile consent card: about 232 px wide; Tolak/Terima segmented control 32 px high; one-line explanation; the full text stays behind "Butiran".
   - Design canvas (private): https://claude.ai/artifact/GzPqJ5guUP55XcZfhroKFQ (artboards 0, A, B, C, and an interactive mobile B).
   - Fix the measured contrast failures: white text on `#25D366` is 1.98:1; the dark shield on dark backgrounds is 1.11:1. Keep the plugin's own WhatsApp icon; the mockups use a generic glyph.
   - Keep keyboard behaviour (Tab, Enter, Escape focus return), JS-off fallback and consent semantics unchanged.
2. **Round Robin fallback and observability.** Today, a lock or verify failure silently redirects to the home page. Add a safe fallback (for example, the first active contact), a PII-free failure counter, and a failure action hook. Tests are required.
3. **Malay translation** of the 99 untranslated strings in `languages/hatnikotni-chat-ms_MY.po`.
4. **`Contributors:` line in `readme.txt`**: needs the owner's WordPress.org username. Ask; never invent one.
5. **Product question, deferred:** the owner asked about a click log/engagement list. Claude recommended against raw per-click logs for privacy and suggested revisiting after a few weeks of real attributed data.

## Staging baseline (WPVibe, 2026-11-19)
- WordPress 7.1.4, PHP 8.4.27, LiteSpeed.
- `WP_CACHE=false`; default object cache, no drop-in.
- Routing `round_robin`; purpose routing off.
- Active contacts: ID 1 and ID 3. ID 5 is inactive; its role field holds leftover 100×"R" test data, which can be cleaned only with approval.
- Cursor `last_contact_id=1`.
- Analytics: 53 events; #53 is the first with UTM data.
- Tools:
  - `tests/staging-rr-http-check.sh` runs from the owner's PC (Git Bash). The cloud workspace cannot reach staging.
  - Browser checklist: private Claude Sheet https://claude.ai/artifact/PRxngfHf9Qq99CeaTDD6p7.
  - Installing a CI artifact: upload the **inner** ZIP. The outer artifact ZIP is a wrapper that WordPress reports as empty.

---

# CURRENT PROJECT STATE — 2026-11-19 10:30 MYT (candidate 0.1.28)

> Supersedes the 0.1.27 section below where they conflict.

- **Browser matrix on 0.1.27 (owner-run, 2026-11-19 09:30–10:12 MYT; checklist in private Claude Sheet https://claude.ai/artifact/PRxngfHf9Qq99CeaTDD6p7):** the owner marked 21/21 steps "Lulus". Claude cross-checked the analytics table via WPVibe.
  - **Consistent with expectations:** step 4 (Accept, 09:36) → event #50, contact 3. Step 6 (Reject, 09:38) → no event. Steps 17, 19 and 20 (consent rejected) → no events.
  - **Step 9: FAILED (tool-verified).** Event #51 (09:50:26) was recorded with `utm_campaign` NULL, although step 8 showed the `hatnch_campaign` cookie present and HttpOnly. Every one of the 51 staging events since 2026-09-30 has NULL UTM fields.
  - Other owner observations: with JS disabled the WhatsApp button worked and the shield did not (expected). Row 25 asks whether a click log/engagement list is needed; that is a product question and is not filed as a defect.
- **Root cause:** `HATNCH_Campaign::get_attribution()` called `sanitize_text_field()` before `rawurldecode()`. WordPress's `_sanitize_text_fields()` strips `%xx` octets, and `setcookie()` re-encodes the already `rawurlencode()`d payload, so `$_COOKIE` holds `%7B%22…` and the JSON was destroyed. Reproduced with WordPress's real `_sanitize_text_fields()` source. The defect has existed since `5617257` (2026-09-23).
- **Fix: 0.1.28** (`3f22fdc`) decodes first, then sanitizes and length-limits each field; a non-string cookie yields empty attribution.
  - Regression test `tests/campaign-attribution-integration.php` (WordPress-backed, in the lifecycle job) was committed first in `d0acb55`, and CI #878/#879 **failed** on the old code exactly as on staging (`utm_source:null…`).
  - With the fix, CI #880 (run `44443391627`) and #881 passed all 8 jobs.
  - The #880 artifact `hatnikotni-chat-0.1.28` (ID `14492291635`, `sha256:2d547da9…be519`) is **not the final candidate**: the readme/POT clean-up below changes the package.
- **Decision update:** since 0.1.28 needs a full staging re-verification anyway, the readme.txt/POT WordPress.org clean-up joins **0.1.28** instead of later. The Round Robin fallback/observability work moves to **0.1.29**.
- **Merge note:** PR #8 should not be merged at 0.1.27, which has a known broken advertised feature (campaign attribution).

- **0.1.28 readme/POT (`ed26d87`):**
  - `readme.txt`: standard WordPress.org structure (single Changelog, Tags, subsections).
  - POT regenerated (115 → 204 strings). ms_MY.po merged: 104 translated, 99 untranslated, 10 obsolete dropped.
  - Still missing: a `Contributors:` line, which needs the owner's WordPress.org username.
- **Final 0.1.28 candidate:** CI #884 (push, run `44446934827`) and #885 passed all 8 jobs on `ed26d87`, including the attribution regression test and `msgfmt --check`. Artifact `hatnikotni-chat-0.1.28`, ID `14493841538`, 70,220 bytes, `sha256:9c3cab14ec62b69c80513f0d31484a8286b6938d2e6d89388f879e274e010254`. The earlier #880 artifact is superseded.

- **0.1.28 on staging (WPVibe, 2026-11-19 ~11:22 MYT):**
  - Version `0.1.28` active; installed `class-hatnch-campaign.php` contains the decode-before-sanitize fix (lines 116–125).
  - The owner reports steps 7–10 passed; steps 7, 8 and 10 are browser-observable.
  - Only one new event exists: #52 (11:20:03, contact 3, no UTM). It matches the owner's re-run of step 4, which had no UTM.
  - **Campaign attribution on 0.1.28: CONFIRMED.** Owner repeated step 9 (UTM URL → Accept → click, ~11:32 MYT). WPVibe shows event **#53** (2026-11-19 11:31:43, contact 1, desktop) with `utm_source=ujian`, `utm_medium=qa`, `utm_campaign=c0127`. This is the first event on staging that ever carried UTM data.
  - **Round Robin HTTP check on 0.1.28: CONFIRMED** (owner-run, 03:33:40–03:33:56 UTC).
    - Cursor before: 1, consistent with event #53's selection of ID 1.
    - Sequential: 3, 1, 3, 1.
    - 12 concurrent requests (118 ms window): ID 1 ×6 (c1, c3, c4, c7, c8, c9) and ID 3 ×6 (c2, c5, c6, c10, c11, c12).
    - All 16 responses were `302` with `X-Redirect-By: Hatnikotni Chat`.
    - Cursor after: 1 (WPVibe); purpose-lane cursors unchanged.
  - **Package hashes for 0.1.28: partly reported (2026-11-19 11:39 MYT).** The inner ZIP extracted from the user's `hatnikotni-chat-0.1.28.zip` is `hatnikotni-chat-0.1.28.zip`, 74,277 bytes, SHA-256 `4B7331F85D91109AD27E5219B33164EF16DD23C02AB37DDC43107C49ED545BA5`. The user's outer `hatnikotni-chat-0.1.28.zip` hashes to `9C3CAB14EC62B69C80513F0D31484A8286B6938D2E6D89388F879E274E010254`, which equals the CI #884 artifact digest. **Package provenance for 0.1.28: CONFIRMED** (commit `ed26d87` → CI #884 → artifact `9c3cab14…` → inner ZIP `4B7331F8…`, which the user uploaded).
  - Second Round Robin HTTP check (03:38:07–03:38:23 UTC): sequential 3, 1, 3, 1; 12 concurrent → ID 3 ×6, ID 1 ×6; all handler-confirmed 302; cursor 1 → 1 (WPVibe).
  - **Plugin Check on 0.1.28 (user-reported, 2026-11-19 11:44 MYT): "Checks complete. No errors found."**
- **Design decision (owner, 2026-11-19), for 0.1.29:**
  - Concept **B** (round button + shield badge) is the base. Concept **C** (team greeting card) becomes an opt-in setting, off by default.
  - Owner feedback: make the mobile consent card more compact, shorten Tolak/Terima, narrow the explanation. Revised in the private design canvas https://claude.ai/artifact/GzPqJ5guUP55XcZfhroKFQ: 232 px card, 32 px segmented control, one-line explanation.
  - Measured issues that 0.1.29 addresses:
    - White text on `#25D366` is 1.98:1 (fails WCAG AA).
    - The current dark shield on dark backgrounds is 1.11:1.
  - Note: the 32 px segmented buttons meet the WCAG 2.2 AA target size (24 px) but not the 44 px guideline.

## Immediate next action

1. **Merge of PR #8 approved by the owner on 2026-11-19 11:44 MYT** ("if every angle has been checked and it is the best path, go ahead").
   - Pre-merge checks: `main` unchanged at `fb8aa6c`; PR `mergeable_state: clean`; CI #894/#895 green on `53d1613`.
   - The only workflow is `validate.yml`; there is no deployment workflow.
   - The 70-file diff contains no binaries, archives, env files or secrets.
   - Merge method: merge commit, so history is preserved.
   - The merge does **not** deploy to production, tag or release anything; each of those still needs separate approval.
3. Then 0.1.29: the concept B redesign (with C as an opt-in setting), Round Robin fallback and observability, Malay translation of the 99 untranslated strings, and a `Contributors:` line (needs the owner's WordPress.org username).

---

# PROJECT STATE — 2026-11-19 (candidate 0.1.27; superseded where it conflicts with 0.1.28 above)

> Supersedes the 2026-11-18 section below where they conflict. Read [CLAUDE.md](CLAUDE.md) and [AI-DEVELOPMENT-PROTOCOL.md](AI-DEVELOPMENT-PROTOCOL.md) first.

- **Candidate:** Hatnikotni Chat **0.1.27** on `release/0.1.26` (branch name retained), PR #8 → `main`: OPEN, DRAFT, NOT MERGED. Merge requires explicit owner approval.
- **Plugin Check on staging (0.1.26, 2026-11-19 08:50 MYT, report supplied by user):** 0 errors, 10 warnings.
  - 9 warnings in `class-hatnch-routing.php`: DirectQuery/NoCaching at lines 148/175/202/220/236.
  - 1 warning in `class-hatnch-admin.php:412`: NonceVerification.Recommended.
- **Root cause:** `phpcs.xml.dist` excluded those sniffs project-wide, so CI never reported them. Reproduced locally (WPCS from GitHub, since Packagist is blocked from the workspace): exactly the same 10.
- **Fix (`7263a09`, version 0.1.27):**
  - Per-line justified `phpcs:ignore` comments.
  - Global excludes removed; `SchemaChange` stays excluded for dbDelta.
  - Alignment re-flowed by phpcbf.
  - `class-hatnch-routing.php` is token-identical to 0.1.26 under `php -w`. The admin change adds only an empty `<?php ?>` comment block.
  - CHANGELOG.md gains the missing 0.1.26 entry.
- **CI:** #868 (push, run `44432791581`) and #869 (PR, run `44432801892`) passed all 8 jobs on `7263a09`. Candidate artifact (from #868): `hatnikotni-chat-0.1.27`, ID `14486959133`, 63,900 bytes, `sha256:8248a39ed6dd81d28430d1b1cccdf867bee335f974602c3f934dede403c86d95`. The #869 artifact has a different digest, `6ca42847…`, because ZIP timestamps differ. Use the #868 artifact.
- **Staging runtime 0.1.27 (2026-11-19):**
  - **Installed:** the owner installed 0.1.27. WPVibe shows version `0.1.27` active; `class-hatnch-routing.php` on staging carries the new `phpcs:ignore` lines (line 148 checked, 247 lines as in the repository); `hatnch_db_version` is 1.2.0; routing is `round_robin`; data was preserved through the upgrade.
  - **Provenance:** the user's downloaded artifact `hatnikotni-chat-0.1.27.zip` hashes to `8248A39E…6D95`, which equals the CI #868 artifact digest. It contains a single inner ZIP `hatnikotni-chat-0.1.27.zip` (68,023 bytes, SHA-256 `068B2081783DD61BFC0250587933FABB400B5CA311048DD75BEC51625F7A176B`). The user confirmed on 2026-11-19 that the **inner ZIP** (`068B2081…176B`) was the file uploaded (USER-REPORTED). Uploading the outer artifact ZIP directly makes WordPress report an empty package, because a GitHub artifact wraps the installable ZIP. **CONFIRMED (artifact chain).**
  - **Plugin Check (user-reported):** "Checks complete. No errors found."
  - **Round Robin HTTP check:** **CONFIRMED.** User-run `tests/staging-rr-http-check.sh` (Git Bash), 2026-11-19 01:11:02–01:11:19 UTC.
    - Sequential: ID 3, 1, 3, 1.
    - 12 concurrent requests launched within 111 ms: ID 3 ×6 (c1, c2, c3, c6, c7, c10) and ID 1 ×6 (c4, c5, c8, c9, c11, c12).
    - All 16 were `302` with `X-Redirect-By: Hatnikotni Chat` and a wa.me destination; no fallback; inactive ID 5 never selected.
    - Cursor `last_contact_id` 1 → 1 (WPVibe); purpose-lane cursors unchanged; no object-cache drop-in.
    - Same limits as the 0.1.26 run: server-side serialization before the handler cannot be excluded, and lock contention was not isolated.
- **Verified after Plugin Check (WPVibe):** no `object-cache.php` drop-in was left behind (`drop_in: false`).
- **Known WP.org readiness issues, not fixed in 0.1.27:**
  - `readme.txt` has three separate `== Changelog ==` sections; changelog precedes Description; there is no `Contributors:` header line.
  - POT/PO `Project-Id-Version` still says 0.1.7.
  - These need a dedicated readme/i18n clean-up before any WordPress.org submission.
- **Roadmap:** the Round Robin failure fallback/observability work moves from 0.1.27 to **0.1.28**.

## Immediate next action

0.1.27 staging gates (install, provenance, Plugin Check, Round Robin) are complete. Remaining before the owner's merge decision on PR #8:
1. Manual browser matrix (21 steps; owner runs it).
   - Checklist: the private Claude Sheet "Hatnikotni Chat 0.1.27 — Senarai Semak Ujian Pelayar (Staging)", https://claude.ai/artifact/PRxngfHf9Qq99CeaTDD6p7. It covers consent accept/reject/persist, UTM cookie before and after consent plus withdrawal, keyboard Tab/Enter/Escape focus, mobile, shortcode, WooCommerce, JS disabled, and the admin Analytics page.
   - Analytics baseline before the matrix (WPVibe, 2026-11-19 ~01:30 UTC): `hatnch_events` total 49, max id 49, last event 2026-11-05 10:22:19. Claude verifies the event delta after the owner's run: expect +1 after step 4, none after step 6, and +1 with `utm_campaign=c0127` after step 9.
2. **Decision (2026-11-19, delegated to Claude by the owner):** the readme.txt/POT WordPress.org clean-up goes into **0.1.28** together with the Round Robin fallback/observability work. Reason: `readme.txt` ships in the ZIP, so fixing it now would force another full staging re-verification of 0.1.27 for no runtime change. It blocks WordPress.org submission only, not the merge to `main`.

---

# PROJECT STATE — 2026-11-18 (superseded where it conflicts with 2026-11-19)

> **Read first:** [CLAUDE.md](CLAUDE.md), [AI-DEVELOPMENT-PROTOCOL.md](AI-DEVELOPMENT-PROTOCOL.md), then [docs/CLAUDE-CONTINUATION-HANDOFF-2026-11-17.md](docs/CLAUDE-CONTINUATION-HANDOFF-2026-11-17.md) including its 2026-11-18 addendum. This section supersedes the 2026-11-17 section below where they conflict.

- **Active release branch:** `release/0.1.26`, based on `main`. **PR #8** → `main`: OPEN, DRAFT, NOT MERGED. User approved creating this path on 2026-11-18. Merge still requires separate explicit approval.
- **Why:** the earlier stacked chain `#7 → #6 → #4 (ui-compact-contact-form) → #3 → #2 → main` could never reach `main`, because PR #4 must not be merged. PR #8 contains the full candidate in one diff (66 files vs `main`).
- `release/0.1.26` = `main` `fb8aa6c` + candidate `4327a2b` (merge `e91d148`), plus CI fix `998820c`. Plugin runtime code is identical to the 0.1.26 runtime candidate `25d0d40`.
- **CI:** Builds #854/#855 (`e91d148`) FAILED in the incremental PHPCS step. Root cause: the changed-file list included deleted `class-hkc-*.php` paths, so phpcs emitted non-JSON output. Fixed with `--diff-filter=d` in `998820c`. **Builds #856 (push, run `44291258757`) and #857 (PR, run `44291267670`) passed all 8 jobs** on `998820c`, including the 12-worker concurrent Round Robin test in disposable WP 6.6/MySQL 8.0. Build #856 artifact `hatnikotni-chat-0.1.26`, ID `14421323121`, 63,530 bytes, `sha256:597ce9924af23e53e03b65e54e32027ff69d1af4b28234af8444259799ca608d`. This is a new build. Staging provenance must still be compared against the **CI #842** artifact digest `78b31a78…1fba`, which is the ZIP the user installed.
- **Correction to the 2026-11-17 section:** PR #7's head moved to `4327a2b` through documentation-only commits; CI #850–#853 passed there. The recorded `25d0d40` / #842–#843 remain correct for runtime code.
- **Staging (tool-verified read-only via WPVibe, 2026-11-17 ~07:16 UTC):**
  - Environment: WordPress 7.1.4, PHP 8.4.27; Hatnikotni Chat **0.1.26 active**; `hatnch_db_version` 1.2.0.
  - Installed `class-hatnch-routing.php` matches the repository line-for-line. This is source-level only, not a ZIP hash.
  - Cache: `WP_CACHE=false`; object cache `default`, no drop-in.
  - Routing: method `round_robin`; purpose routing disabled.
  - Contacts: active PakYa (ID 1, sort 1) and PakSu (ID 3, sort 2); inactive znz (ID 5).
  - Cursor: `last_contact_id=1`, re-read unchanged on 2026-11-18 ~11:00 UTC.
  - `debug.log` tail (to 2026-11-15 10:33 UTC) has no Hatnikotni entries.
- **Observability gap:** 0.1.26 redirects silently to the home page on lock or verify failure and logs nothing. Staging failures are only detectable through the HTTP `Location` header. Proposed for 0.1.27, not 0.1.26: fallback contact, a PII-free failure counter, and a failure action hook.
- **Staging Round Robin runtime test (0.1.26): CONFIRMED (sequential + concurrent HTTP)** on 2026-11-18.
  - Method: `tests/staging-rr-http-check.sh`, run by the user from Git Bash on Windows (`curl`, no redirect following; output saved to `rr-result.txt`). Recorded 11:59:37–11:59:53 UTC.
  - Preconditions (WPVibe, read-only): routing `round_robin`; purpose routing disabled; active IDs 1 and 3; ID 5 inactive; cursor `last_contact_id=1`; `WP_CACHE=false`; default object cache; server `LiteSpeed`.
  - Sequential, 4 requests: ID 3 → 1 → 3 → 1, as expected.
  - Concurrent, 12 requests: launched within a 109 ms window (11:59:45.592–.701). Result: ID 1 ×6 (c1, c2, c3, c5, c11, c12) and ID 3 ×6 (c4, c6–c10). Exactly 6/6.
  - All 16 responses: `HTTP 302`, `X-Redirect-By: Hatnikotni Chat` (proves the plugin handler ran), `Location` to wa.me. No `NOT_WA` / home-page fallback. Inactive ID 5 never selected. Response headers carried `Cache-Control: no-cache, no-store` with no cache hit.
  - After the test: cursor `last_contact_id=1`, matching the expected end state after 16 alternating selections from 1. Purpose-lane cursors unchanged (`general_enquiries`=1, `business_support`=3). Contact statuses unchanged.
  - An earlier aborted attempt at 11:41 UTC completed only `s1`=ID 3 and `s2`=ID 1, which was also correct and also returned the cursor to 1.
  - **What this does not prove:**
    - Whether the 12 requests overlapped inside the PHP handler or were serialized earlier by LiteSpeed/PHP worker limits. The responses spanned ~8 s, so lock contention specifically was not isolated.
    - The absence of third-party clicks during the window. The exact 6/6 and expected cursor make interference unlikely, but this was not proven.
    - Behaviour with a persistent object cache or with purpose routing enabled.
    - That the 302 responses also mean WhatsApp opened. No message was sent and redirects were not followed.
- Upload identity: the user confirmed on 2026-11-18 that `hatnikotni-chat-0.1.26.zip` (`5F59A39E…42AD`) is the file uploaded to staging (**USER-REPORTED**).
- **Package provenance (2026-11-18 ~11:53 UTC):**
  - User-run PowerShell `Get-FileHash`: the user's downloaded `hatnikotni-chat-0.1.26-ci842.zip` (63,513 bytes) = `78B31A78…1FBA`, matching the CI #842 artifact digest exactly.
  - That artifact contains one file, `hatnikotni-chat-0.1.26.zip` (67,654 bytes), SHA-256 `5F59A39EF32BEB4C302F8571AB564D1FF0B75BFE237AEEFCF11BCDB3D95442AD`. It is byte-identical to the user's standalone `Downloads\hatnikotni-chat-0.1.26.zip`.
  - The entry listing shows 21 source files with byte sizes identical to commit `25d0d40`, plus the CI-generated `.mo`; there are no extra files. This is not the unofficial prototype.
  - That this exact ZIP is the one uploaded to staging is **USER-REPORTED** (confirmation requested). It is consistent with the staging file listing and the line-for-line routing source match.
  - Status: **CONFIRMED for artifact chain**; staging upload identity is user-reported.
- **Privacy note for review:** `docs/staging-test-plan.md` (Ujian 3.19/3.20 logs inherited from `main`) contains full contact phone numbers in a public repository. They were not altered by the merge. Redact them in a separate commit only with user approval, since it edits historical evidence.
- **2026-11-19 owner-approved actions:**
  - Real contact phone numbers were redacted from `docs/staging-test-plan.md` (commit `1073adb`; replaced with ID 1 / ID 3). They remain in older git history; removing them from history would need a separately approved rewrite.
  - PRs #1–#7 were **closed unmerged** with a "Superseded by #8" comment. Branches were kept, and every PR can be reopened.
  - Supersession check:
    - #2, #3, #4, #6 and #7 are ancestors of `release/0.1.26`.
    - #1 (`review/wporg-remediation`, `d288c84`, 2026-09-27) is an early 0.1.0 HKC→HATNCH prefix attempt. It was later redone in the main chain, and current code uses `HATNCH_`.
    - #5 (`diagnostics/rr-lock-staging`, `52d7268`) is opt-in staging diagnostics that was never intended for merge.

## Immediate next action

Round Robin staging verification and package provenance are complete for 0.1.26. Remaining gates on PR #8:
1. Plugin Check against the installed 0.1.26 build.
2. Manual browser matrix: consent/privacy, mobile, keyboard, shortcode, WooCommerce pages, JS-disabled.
3. The owner's explicit merge decision.

---

# PROJECT STATE — 2026-11-17 (superseded by 2026-11-18 above where they conflict)

> **Read first:** Claude must follow the repository-root [CLAUDE.md](CLAUDE.md), then read [AI-DEVELOPMENT-PROTOCOL.md](AI-DEVELOPMENT-PROTOCOL.md), and then the detailed [Claude continuation handoff](docs/CLAUDE-CONTINUATION-HANDOFF-2026-11-17.md). This section supersedes older dated status statements below where they conflict; older sections are retained as historical records.

- Repository: `dejabey/hatnikotni-chat`
- Active candidate branch: `fix/contact-status-result-0.1.26`
- Candidate: Hatnikotni Chat 0.1.26
- Current candidate commit: `25d0d40aa6b0d1ae3749dad5014a33d94932dab5`
- PR #7: OPEN, DRAFT, NOT MERGED; base `recovery/staging-0.1.25-audit`.
- PR #6: OPEN, DRAFT, NOT MERGED; recovery/documentation baseline for 0.1.25.
- Latest observed GitHub Actions: #843 and #842 both SUCCESS on the candidate commit. This validates the CI environment, not staging runtime.
- User reported on 2026-11-17 that 0.1.26 has been installed on `staging.perlis.xyz`. This is USER-REPORTED and still requires independent post-install verification; do not mark as confirmed until inspected.
- Previous 0.1.25 staging test had reproduced `state_verify_failed` symptoms. Its concurrent HTTP test was inconclusive as not all helper responses proved that the routing handler had been reached.
- Previous 0.1.25 inspection found `WP_CACHE=false`, default object cache, and no `object-cache.php` drop-in. These are historical observations only; re-check current 0.1.26 staging.
- Current 0.1.26 staging Round Robin verification: PENDING.
- WPVibe last reported 0 remaining calls on 2026-11-05, with one banked reset; this reading is stale. Re-check quota before using WPVibe. Do not spend a banked reset without user approval.
- No PR merge, production deployment, or official release is authorized.

## Immediate next action

After checking WPVibe quota, verify the staging plugin version and source provenance read-only, capture current routing state/cache configuration/log tail, then run controlled sequential and genuinely concurrent Round Robin tests. Do not reset the cursor, clear logs, send WhatsApp messages, merge PRs, or deploy to production. See the Claude continuation handoff for the full test method, evidence requirements, prior findings, and decision log.

---

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
**Current migration branch status (2026-10-03):** Build #670 passed all eight jobs for code/test commit `189dd82529ceced521b92d89864edce86c23fe96`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132997229. Build #671 then passed all eight jobs for handoff commit `69557fdb7ba3e15bc1e89b4e3af29978861822f6`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133299028. The latest verified CI therefore includes PHP 8.1–8.5 validation, MySQL migration integration tests, WordPress 6.6 lifecycle tests, and production package build.
**Migration test coverage:** real WordPress activation migrates tables, settings and routing-state options; real `init()` repairs a missing column and index through `dbDelta()`; collision tests preserve old/new tables and conflicting options without advancing the database version. Separate MySQL integration tests cover partial rename retry and schema/version failure gates.
**Safety gates:** PR #3 is draft/unmerged; PR #2 is draft/unmerged. No staging/production database has been accessed or modified. Do not merge or run migration on the existing staging site without explicit authorization and a verified backup/clone.

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

1. Verify the latest CI runs for the two documentation commits and for the current PR #3 HEAD; do not infer success from an earlier build.
2. Perform a code review of PR #3, focusing on activation ordering, migration idempotence, error paths, schema verification, and compatibility with the declared WordPress/PHP minimums. Keep PR #3 draft and unmerged until review and controlled tests are complete.
3. Before any staging migration, obtain explicit authorization, make and verify a restorable backup, and use a disposable clone where possible. Do not uninstall/reinstall the existing staging plugin; `uninstall.php` deletes plugin data.
4. On a disposable staging clone, run the remaining migration matrix: empty/new plus populated/legacy table collision, actual rename failure and retry, deactivate/reactivate before migration, and verify schema version/legacy options remain unchanged on failure. CI already covers several of these, but does not replace environment-specific testing.
5. Verify the exact ZIP installed on staging against a specific CI artifact using SHA-256. The existing runtime tests and Plugin Check screenshot do not establish artifact provenance.
6. Complete remaining runtime tests: direct/random/round-robin routing, shortcode, cache/CDN, keyboard focus/Escape, mobile layout, WooCommerce pages, JavaScript-disabled behavior, and custom cookie path/domain.
7. Correlate the complete Plugin Check report with the exact candidate version. Do not respond to the WordPress.org reviewer or release until code review, controlled migration tests, package provenance, and required runtime checks are complete.
8. Update this handoff with verified evidence after each gate. Production deployment remains a separate decision requiring separate approval.

## Source of truth

Repository: https://github.com/dejabey/hatnikotni-chat  
Review branch: wordpress-org-compliance  
Migration hardening branch: migration-hardening  
Plugin URI: https://github.com/dejabey/hatnikotni-chat


## CI follow-up — 2026-10-03 (Build #619)

- Build #619 failed in **Run WordPress Coding Standards** in `includes/class-hatnch-plugin.php`, PHPDoc lines 349–350: spacing alignment for the `@param` annotations of `table_has_required_indexes()`.
- PHP syntax checks and `tests/skeleton-contract.sh` passed in the failed run; PHPCS stopped the job before package build, so this is not a successful validation run.
- Corrected the two PHPDoc spacing issues in commit `89e8a1ddc1e30729d1a99e6f1e93307c154b4738`.
- **Next gate:** verify a fresh Validate workflow run for that commit and inspect all matrix jobs before describing the branch as passing.
- No PR was merged and no staging/production database was accessed or modified.


## CI follow-up — Build #621 passed (2026-10-03)

- Verified GitHub Actions run #621: all five PHP validation matrix jobs (PHP 8.1–8.5) and the production package build completed successfully.
- Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37118840794
- The successful run is for commit `a1f917090b9fac8ddfb8a8b8d5298dcec186aece`, which includes the Build #619 PHPDoc spacing correction and this handoff update.
- Compatibility check: plugin header/readme require WordPress 6.6 and PHP 8.1; the `%i` identifier placeholder used by schema inspection is compatible with the declared WordPress minimum.
- Follow-up schema review identified that required-column and index checks did not verify the primary ID definition. Added checks for `id` to be unsigned `BIGINT`, `NOT NULL`, and `AUTO_INCREMENT` in both custom tables; the existing required-index check also requires `PRIMARY(id)`. Static contract assertions and a staging test-plan case were added. This does not yet verify every column type/default or index uniqueness/visibility; those remain review considerations.
- Build success validates automated checks/package generation only. The controlled migration matrix and runtime staging verification remain pending; PR #3 stays draft and must not be merged yet.


## Schema ID contract follow-up — 2026-10-03

- Inspected the actual DDL in `includes/class-hatnch-contacts.php` and `includes/class-hatnch-analytics.php`: both tables define `id bigint(20) unsigned NOT NULL AUTO_INCREMENT` and `PRIMARY KEY (id)`.
- Added `table_has_auto_increment_primary_id()` in `includes/class-hatnch-plugin.php`; schema verification now rejects either table if its `id` is not an unsigned BIGINT, is nullable, or lacks `AUTO_INCREMENT`. Existing index validation also requires the primary index on `id`.
- Added static contract checks in `tests/skeleton-contract.sh` and documented a controlled test for wrong ID type/nullability/missing AUTO_INCREMENT in `docs/staging-test-plan.md`.
- Code commit: `e0a57d230ebf2162a0c6213788c397e70af8afc0`; test commit: `e7dac64095a1cc873c34ec188db41e963d8bfdb6`; test-plan commit: `aa8364877d9fae1a9e9042dc4e3e351533df01ce`.
- **Validation pending:** these new commits still need a fresh GitHub Actions run. Do not call this new check CI-verified until the workflow completes successfully. Column types/defaults beyond `id`, and index uniqueness/visibility, are not yet comprehensively checked. Controlled migration tests remain outstanding. No staging/production database was accessed or changed; PR #3 remains draft and unmerged.


## CI and index-contract follow-up — 2026-10-03

- Verified Build #627 succeeded: all five PHP matrix jobs (8.1–8.5) and the production package build passed, including PHP syntax, JavaScript syntax, skeleton contract tests, and WordPress Coding Standards. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37122434046.
- Further review found index verification compared names and ordered columns but not uniqueness. Updated `table_has_required_indexes()` to verify `Non_unique`: `PRIMARY` must be unique and each declared secondary index must be non-unique. Added static contract assertions and a staging test-plan case for wrong uniqueness.
- Relevant commits: ID schema validation `e0a57d230ebf2162a0c6213788c397e70af8afc0`; uniqueness verification `73cc40aa37f8e459ecd7b8a706fa9ff0d139d3bb`; static test `0f73862434f4dd8888a8138fdcd8d7d5d29ba0b2`; test plan `96e535cedbce2cc1727dff38f1b0637483b1098f`.
- **Validation status:** Build #627 predates the index-uniqueness change. A fresh CI run is required; do not treat the latest changes as CI-verified until all jobs pass.
- Still pending: controlled migration test matrix and review of all column types/defaults/nullability beyond the ID column. PR #3 remains draft and unmerged; no staging/production database was accessed or modified.


## CI verification — Build #632 (2026-10-03)

- Verified Build #632 succeeded for commit `c09393f02e34f085e4b5abcf47423330169a26ed`: all five PHP validation jobs (8.1–8.5) and the production package build passed. This run includes the index uniqueness check, its skeleton-contract assertions, and the PHPCS alignment correction.
- Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37122659995
- Builds #628–#631 failed in PHPCS due to assignment alignment in the new index verifier; the reported spacing was corrected and Build #632 confirms the fix.
- Migration hardening now checks required column presence, the `id` column's unsigned BIGINT / NOT NULL / AUTO_INCREMENT definition, required index names and ordered columns, and index uniqueness (`PRIMARY` unique; secondary indexes non-unique).
- Remaining gates: controlled migration matrix on a disposable test database; broader review of column types/defaults/nullability beyond `id`; confirm failure and retry semantics under real database errors. PR #3 remains draft and unmerged. No staging/production database was accessed or modified.


## Migration retry-semantics review — 2026-10-03

- Reviewed the current migration sequence in `includes/class-hatnch-plugin.php` against partial-failure scenarios. Table-pair collision checks run before any rename; options are not copied or deleted until all planned table renames have been verified.
- If the first table rename succeeds and a later rename fails, the next request should treat the first table as already migrated and retry the remaining legacy table. If tables are renamed but option copying/verification fails, the next request retries the option migration because the legacy options remain. These are code-inspection conclusions, not database integration-test results.
- Schema upgrade is gated on required columns, unsigned BIGINT NOT NULL AUTO_INCREMENT IDs, required index names/ordered columns, and expected index uniqueness. Version advancement follows the schema gate.
- DDL review: the contacts table has required non-null fields and defaults for `role`, `description`, `status`, `weight`, and `sort_order`; the events table intentionally permits NULL for optional attribution/context fields. Adding strict checks for every column default/type would duplicate part of `dbDelta()` and could reject compatible database representations. Keep the current explicit checks focused on structural requirements that the plugin relies on; add further checks only for a demonstrated compatibility or data-integrity risk.
- **Not verified yet:** no disposable database was available in this workflow to execute fault-injection or migration integration tests. Static contract tests and CI do not establish runtime retry behavior. PR #3 remains draft and unmerged; no staging/production database was accessed or changed.
- Build #633 passed all six jobs for handoff commit `49ad6d0049ea55b20a463e48eb7d065d74eb44d2`: PHP validation on 8.1–8.5 and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37122709277. This verifies the branch HEAD and static/packaging checks, not the controlled migration matrix.


## CI verification — Build #634 (2026-10-03)

- Verified Build #634 completed successfully for commit `8d3bc1ec1f599430089037ef583185a3da3d2e9d`: all five PHP validation jobs (8.1–8.5) and the production package build passed.
- Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37122792361
- This confirms CI for the latest test-plan and handoff updates at that commit. It does not execute the controlled migration matrix against a real database.
- Current release gate remains unchanged: PR #3 is draft/unmerged; run migration fault-injection and retry tests on a disposable database before considering merge. No staging/production database was accessed or changed.


## Database-backed migration test results — 2026-10-03

- Added `tests/migration-integration.php`, run against an ephemeral MySQL 8.0 service in GitHub Actions. The harness exercises migration and schema-upgrade gates using real MySQL table operations and a minimal adapter for the WordPress options API and `$wpdb` methods used by the routines.
- Added a dedicated `migration-integration` job to `.github/workflows/validate.yml`; the existing five-version PHP matrix and clean production package job remain enabled.
- **Build #653 passed** all seven jobs: PHP validation on 8.1–8.5, migration integration tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37124265410. Commit: `eebf15e65703f1b4634937e46c91da346eba59c8`.
- The integration log explicitly reports PASS for eleven cases: legacy-only table/option migration and data preservation; conflicting option preservation; table-collision preservation; injected partial rename failure and successful retry; missing required column; invalid primary ID type; missing `AUTO_INCREMENT`; misordered required index; wrong index uniqueness; failed database-version option write; and administrator-only migration notice visibility.
- **Test-harness correction recorded:** Build #642 failed because the fixture for the conflicting-options case omitted the current option it intended to conflict with. The fixture was corrected and the separated option-conflict and table-collision cases both passed in Build #643 and subsequent runs. This was a test-fixture failure, not evidence of a plugin runtime failure.
- **Scope limitation:** these tests exercise migration/upgrade logic with real MySQL but use a minimal adapter rather than booting WordPress. They do not test the complete activation/init lifecycle or actual `dbDelta()` recovery. The manual staging test plan remains in force.
- No staging/production database was accessed or changed. PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.


## WordPress-backed migration lifecycle verification — 2026-10-03

- Added `tests/wordpress-migration-integration.php`, executed with WP-CLI against a disposable WordPress 6.6 install and ephemeral MySQL 8.0 service in GitHub Actions.
- Added the dedicated `WordPress migration lifecycle tests` job to `.github/workflows/validate.yml`.
- **Build #657 passed all eight jobs**, including PHP validation on PHP 8.1–8.5, the original MySQL migration integration suite, WordPress-backed lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37130408312. Commit: `90450534421f653879e9157a04a45b49af8d5550`.
- WordPress-backed test output explicitly reports PASS for: real activation migrating legacy tables/options while preserving rows; real `init()` upgrade restoring a missing contacts column through `dbDelta()` before version advancement; and restoring a missing `status` index through `dbDelta()` before version advancement.
- This closes the previous CI gap for a real WordPress activation callback, the actual WordPress options/`$wpdb` APIs, and real `dbDelta()` column/index repair. It does not replace the separate manual staging checks for collision scenarios, failure injection, frontend behavior, or artifact provenance.
- CI uses an isolated disposable WordPress/MySQL environment only. No staging or production database was accessed or changed. PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.


## Expanded WordPress-backed collision tests — 2026-10-03

- Expanded `tests/wordpress-migration-integration.php` with two real WordPress activation scenarios: a legacy/current table collision, and conflicting legacy/current settings options.
- **Build #659 passed all eight jobs** for commit `d5a0f4ea2a8464df5bd47774b8b3bed8995fcdba`: PHP 8.1–8.5 validation, MySQL migration integration, WordPress-backed lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132015156.
- WordPress-backed test logs explicitly report PASS for five scenarios: legacy table/option migration with rows preserved; real `dbDelta()` repair of a missing column; real `dbDelta()` repair of a missing index; table-collision preservation; and conflicting-option preservation without creating current tables or advancing the database version.
- Tests run only against disposable WordPress/MySQL services in GitHub Actions. No staging/production database was accessed or changed. PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.


## Routing-state preservation assertion — 2026-10-03

- Strengthened the WordPress-backed activation test to assert that legacy `hkc_routing_state` is copied exactly to `hatnch_routing_state` and removed only after verified copy.
- **Build #662 passed all eight jobs** for test commit `bd64f13fe23a9a43a3b3066b187e363e00796f85`: PHP validation on 8.1–8.5, MySQL migration integration, WordPress-backed lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132140225.
- The WordPress-backed test log passed all five scenarios; the activation-migration assertion now checks both settings and routing-state options.
- No staging/production database was accessed or changed. PR #3 remains draft and unmerged; PR #2 remains draft and unmerged.


## Current verified state — Build #665 (2026-10-03)

- Re-checked the live GitHub Actions API during the continuation review: Build #665 completed successfully with all eight jobs green for branch HEAD `a1e81919aaf79ade4ec4f59ea9a106efc2cad788`. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132255572.
- The eight successful jobs are PHP validation on PHP 8.1–8.5, MySQL migration integration tests, WordPress 6.6 migration lifecycle tests, and production ZIP build.
- Re-checked PR #3: open, draft, and unmerged. Keep PR #3 and PR #2 unmerged pending the release gates below.
- Review of `includes/class-hatnch-plugin.php` found no new confirmed defect that justifies a code change in this pass. This is a code-inspection result, not a substitute for controlled failure injection.
- Still outstanding: execute the migration failure/retry matrix on a disposable staging clone or isolated test database; complete the frontend/privacy/routing/cache/WooCommerce checks; verify the exact candidate ZIP against the CI artifact using SHA-256; correlate the complete Plugin Check report with the exact candidate build.
- Safety boundary unchanged: no staging or production database was accessed or modified; do not uninstall/reinstall the existing staging plugin because `uninstall.php` is destructive. Obtain explicit authorization and verify a restorable backup before any staging migration. Production deployment requires separate approval.


## Documentation CI confirmation — Builds #666 and #667 (2026-10-03)

- Re-checked live GitHub Actions results after the two documentation commits: Build #666 passed for handoff commit `e36288404021f53f96e177eaf46baa2269f7dcce`; Build #667 passed for staging-plan commit / branch HEAD `0c65fb89b42ddad8995f41ba93827c982cd477c0`.
- Runs: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132487851 and https://github.com/dejabey/hatnikotni-chat/actions/runs/37132489013.
- Both runs completed with conclusion `success`. Build #667 is the latest verified branch-head CI result at the time of this entry.
- This confirms the documentation commits pass the existing CI workflow; it does not complete the controlled staging migration matrix or runtime/release gates.
- PR #3 remains draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.


## WordPress-backed rename-failure and retry test — Build #670 (2026-10-03)

- Verified live GitHub Actions run #670 completed with conclusion `success` for HEAD commit `189dd82529ceced521b92d89864edce86c23fe96`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132997229.
- All eight jobs passed: PHP validation on PHP 8.1–8.5, MySQL migration integration tests, WordPress 6.6 migration lifecycle tests, and production package build.
- Added a WordPress-backed fault-injection scenario that forces a real `$wpdb` `RENAME TABLE` failure after the contacts table has already been renamed. The test verifies that the legacy events table and data remain available, the database version does not advance on failure, and a subsequent activation retry completes migration while preserving contact/event rows.
- This verifies the injected partial-rename failure/retry path in the isolated disposable WordPress/MySQL CI environment. It does not replace testing against a disposable clone of the actual staging environment or establish candidate ZIP provenance.
- PR #3 remains draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed. Do not uninstall/reinstall the existing staging plugin; `uninstall.php` deletes plugin data.


## Latest verified state — Build #671 (2026-10-03)

- Re-checked the live GitHub Actions API: Build #671 completed with conclusion `success` and all eight jobs passed for handoff commit `69557fdb7ba3e15bc1e89b4e3af29978861822f6`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133299028.
- Build #670 passed all eight jobs for code/test commit `189dd82529ceced521b92d89864edce86c23fe96`, including the real WordPress `$wpdb` rename-failure/retry scenario: https://github.com/dejabey/hatnikotni-chat/actions/runs/37132997229.
- Updated PR #3's description to include both results. PR #3 remains draft/unmerged; PR #2 remains draft/unmerged.
- Code review is still in progress. Automated CI does not replace the remaining disposable-staging-clone migration matrix, frontend/runtime checks, ZIP SHA-256 provenance verification, or exact-candidate Plugin Check correlation.
- Safety unchanged: no staging/production database was accessed or modified. Do not uninstall/reinstall the existing staging plugin; `uninstall.php` deletes plugin data. No merge or production deployment is authorized.


## Documentation and test-plan validation — Build #673 (2026-10-03)

- Build #673 passed all eight jobs for commit `9090399ef72476882c82af6a8105f49ced2e7795`, which updates this handoff and the staging migration test plan: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133572267.
- Verified job results: PHP 8.1–8.5 validation, MySQL migration integration tests, WordPress 6.6 lifecycle tests, and production package build all succeeded.
- The updated staging test plan records six WordPress-backed migration scenarios and preserves the outstanding release gates: disposable-clone migration matrix, runtime/privacy/routing/cache/WooCommerce checks, ZIP SHA-256 provenance, and Plugin Check correlation.
- PR #3 remains draft/unmerged; PR #2 remains draft/unmerged. No staging/production database was accessed or changed.


## Expanded migration lifecycle coverage — Build #675 (2026-10-03)

- Added a WordPress-backed test for the edge case where a current contacts table exists but is empty while the legacy contacts table contains data. Activation must pause and preserve both tables/options without advancing the schema version.
- Added a WordPress-backed deactivate/reactivate scenario for a legacy installation. It verifies table rows, settings and routing-state options migrate before defaults/schema installation.
- Build #675 passed all eight jobs for test commit `6e7ca5d54477615207a5932297dc8c22d442e04e`: PHP 8.1–8.5 validation, MySQL migration integration, WordPress 6.6 lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133763454.
- The WordPress-backed suite now explicitly reports PASS for eight lifecycle/migration scenarios. Expected injected rename failure is logged by WordPress, followed by a PASS confirming data preservation and retry recovery.
- Staging-clone tests, frontend/runtime checks, exact candidate ZIP SHA-256 provenance, and Plugin Check correlation remain open. CI does not authorize a live staging/production migration.
- PR #3 remains draft/unmerged; PR #2 remains draft/unmerged. No staging/production database was accessed or changed.


## Migration test-harness cleanup — Build #678 (2026-10-03)

- Refined the injected rename-failure test to remove only its own `query` filter instead of clearing all WordPress query filters, preserving test isolation.
- Suppressed the expected database error output for the deliberately injected failure and restored WordPress's previous error-suppression setting immediately afterward.
- Build #678 passed all eight jobs for commit `18f7bbd65441e434e5f29ae67f36dab6b6d9e731`: https://github.com/dejabey/hatnikotni-chat/actions/runs/37133913786.
- The expanded lifecycle tests from Build #675 and this test-harness cleanup are CI-verified. The test matrix on a disposable staging clone and the frontend/package-provenance gates remain outstanding.
- PR #3 remains draft/unmerged; PR #2 remains draft/unmerged. No staging/production database was accessed or changed.



## Contact input validation hardening — Builds #680–#686 (2026-10-04)

- **Build #680 passed all eight jobs** for commit `1e15d4a1753be0dcea7a2d1d7713fb53757fb2e5`: `HATNCH_Settings::get()` now safely returns its fallback when the stored settings option is malformed and not an array. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37171831143.
- **Builds #681 and #682 passed all eight jobs** for commits `09729b684f679d9a890ae125d8c9750131756be4` and `8ff485baee30cc1e34f291a8846dcee55ca7ca65`. The contact save path now clamps `weight` to the unsigned SMALLINT range and `sort_order` to the unsigned INT range; contract checks cover both limits. Runs: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172084235 and https://github.com/dejabey/hatnikotni-chat/actions/runs/37172093587.
- Follow-up review found that HTML `maxlength` attributes for contact name, role and description were not enforced server-side. Added Unicode-aware server-side length validation for the schema limits (name 100, role 100, description 255 characters), explicit error codes/messages, and static contract assertions. The character counter uses `mb_strlen` when available and a UTF-8 regex fallback otherwise.
- Builds #684 and #685 failed the WordPress Coding Standards step because the newly added admin error-message array was misaligned. The contract checks, PHP syntax and migration integration/lifecycle jobs had passed in those runs; the failure was a style issue. The array was aligned and the final verification is **Build #686 passed all eight jobs**, including PHPCS on PHP 8.1–8.5, both migration test suites, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172705507. Final code commit at this entry: `c447ac724aa9b88425a295480b90b4d2aa04f480`.
- PR #3 remains open, draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.
- Remaining gates are unchanged: finish the broader privacy/routing/campaign review, run the manual runtime checks on a disposable staging clone, verify the exact candidate ZIP against CI artifact SHA-256, and correlate the complete Plugin Check report with that exact candidate. Do not uninstall/reinstall the staging plugin; `uninstall.php` is destructive.



## UTM attribution input bounds — Build #691 (2026-10-04)

- Follow-up review found that incoming UTM values were sanitized but not bounded to the custom analytics table's declared column lengths. Added per-field limits matching the schema: `utm_source` and `utm_medium` 100 characters; `utm_campaign`, `utm_term`, and `utm_content` 150 characters.
- The limits are applied both when capturing query-string values and when decoding the campaign cookie, so malformed or older oversized cookie values are bounded before insertion.
- Unicode-safe truncation uses `mb_substr` when available and a UTF-8 regex split fallback otherwise. Added contract checks for all five field limits.
- Builds #689 and #690 failed only the PHPCS step because the new class constants were not aligned. The constants were aligned; **Build #691 passed all eight jobs** for commit `179e60f74df2013f6a473c2631df5cabca52171d`, including PHP 8.1–8.5/PHPCS, both migration suites, and production package build: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172876517.
- Build #688 also passed all eight jobs for the staging-plan documentation update: https://github.com/dejabey/hatnikotni-chat/actions/runs/37172807564.
- PR #3 remains open, draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.
- Remaining gates: manual contact-length boundary checks on a disposable clone; finish broader privacy/routing/campaign review; verify candidate ZIP SHA-256 against the CI artifact; correlate the complete Plugin Check report with the same artifact. Do not uninstall/reinstall staging plugin; `uninstall.php` is destructive.



## Post-consent UTM capture and cookie-size guard — Build #700 (2026-10-04)

- Review found a consent-flow gap: when a visitor landed on a UTM URL before opting in, the initial server request correctly refused attribution capture, but accepting analytics on that same page did not trigger a second capture request. The first WhatsApp click could therefore be missing campaign attribution.
- Added a public, same-site `admin-post.php` capture endpoint. After explicit acceptance, the frontend sends the current page's supported UTM parameters to that endpoint; WordPress processes them during `init`, where the existing consent gate still applies. No page reload is required. The endpoint only finalizes the request with a no-cache 204 response.
- Added a 3,500-character encoded-value guard before setting the campaign cookie, to keep the cookie below common browser size limits even when UTM values contain multibyte characters. Oversized attribution is not stored; an existing campaign cookie is cleared where possible.
- Contract checks now assert the post-consent capture endpoint/request and the encoded cookie-size guard.
- Builds #697–#699 failed PHPCS because the new localized endpoint settings were not aligned. The alignment was corrected; **Build #700 passed all eight jobs** for commit `e45c73aa5d448a67ebb85085dd7c4fce4fb0b14b`: PHP 8.1–8.5/PHPCS, MySQL migration integration, WordPress lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37173121178.
- This is automated CI validation, not a manual frontend verification. Still verify on a disposable staging clone: UTM landing without consent; accept on the same page; confirm attribution cookie is set only after consent and the next click event receives the UTM values; reject/withdraw consent and confirm attribution is cleared.
- PR #3 remains open, draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.



## Campaign consent endpoint documentation cleanup and package inspection — Build #703 (2026-10-04)

- Corrected the PHPDoc placement for `capture_campaign_request()` and `revoke_campaign()`; this is documentation-only within the source file, with no behavior change.
- **Build #703 passed all eight jobs** for commit `d22a069d6161a3b94f4485472de6fcfe5b4af73e`: PHP 8.1–8.5/PHPCS, MySQL migration integration, WordPress 6.6 lifecycle tests, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/37173471101.
- Downloaded the Build #703 artifact (ID `11292656339`) and inspected the contained production plugin ZIP. GitHub artifact SHA-256: `956de98a4187072a6a6ad40fed9a5dcb9f90acf96044ff5da90f588f4e9be537`. SHA-256 of the inner installable plugin ZIP `hatnikotni-chat-0.1.8.zip`: `3d77b463fdf0bce5ee29e0943899d72c513de4d374c2271847c82409a8e62971`.
- Package inspection confirms one stable `hatnikotni-chat/` root, expected PHP/CSS/JS/language files, and no tests, vendor, .github, docs, handoff or .git paths in the installable ZIP. Required entry files and the new campaign/privacy JS files are present.
- **Important limitation:** the CI artifact and its inner ZIP have verified hashes, but the ZIP installed on staging has not been hashed; staging artifact provenance remains unverified. Plugin Check has not been rerun against this exact artifact, and manual post-consent UTM tests remain open.
- PR #3 remains open, draft and unmerged; PR #2 remains draft and unmerged. No staging/production database was accessed or changed.

## Audit continuation and handoff refresh — 2026-11-01

**Reviewed branch/PR:** `ui-compact-contact-form`, PR #4 (https://github.com/dejabey/hatnikotni-chat/pull/4), draft and unmerged. **Reviewed HEAD at start:** `d23f877752910d8b39e4eb7f164295591dcc08cc`. **Base:** `migration-hardening` at PR metadata SHA `43e805e6e9e396b0e17223f8e3be00419be1879d`. The handoff metadata above predates the current branch and is historically stale; this section is the current audit checkpoint.

### Work completed in this audit continuation
- Re-fetched PR #4 metadata, diff and branch sources from GitHub.
- Confirmed PR #4 is still open, draft and unmerged; PR body still incorrectly says automated CI has not run. Its head SHA is `d23f877752910d8b39e4eb7f164295591dcc08cc`.
- Queried GitHub commit status for that HEAD: **state pending; zero status records** at the time of check. No successful current-HEAD CI result was verified. Do not treat older branch build results as validation of this revision.
- Reviewed current admin contact form/save/toggle, settings-save handler, round-robin routing, and contact status database update behavior.

### Audit findings / review notes
1. **Settings save reports success unconditionally (correctness/observability).** `save_settings()` calls `update_option('hatnch_settings', $settings, false)` and immediately redirects with `updated=1`, without checking the result. WordPress `update_option()` returning false is ambiguous (unchanged option versus failed update), so a simple boolean check alone is not sufficient to prove persistence. Add a reliable verification strategy and tests (including unchanged values and injected DB failure) before presenting a definitive success notice.
2. **Round-robin state update is not atomic (concurrency limitation).** `resolve_round_robin()` reads a shared option, chooses the next active contact, then writes the option. Simultaneous click requests can select the same contact or overwrite sequence state. This is not necessarily a blocker for low traffic, but round-robin must not be described as strict/fair under concurrent requests. A robust fix needs an atomic persistence/locking strategy and contention tests; avoid a superficial patch.
3. **Contact status toggle uses a nonce-protected GET.** Capability and action nonce checks exist, reducing CSRF exposure, but a state-changing operation over GET is still an HTTP-method semantics weakness. Prefer POST with nonce and regression coverage when revising this path; classify as hardening rather than demonstrated exploit.
4. **Status switch display text does not live-update.** The checked state changes natively, but adjacent “Active/Inactive” text is rendered server-side and is not bound to switch changes. Native switch semantics remain available to assistive technology; visible text may disagree until submit/reload. Either update text client-side accessibly or remove the misleading static state label.
5. **Current PR remains presentation-focused.** It introduces a two-column editor and horizontally scrollable fixed-width contact table; visual confirmation at narrow/desktop breakpoints, keyboard operation, focus visibility, and admin regression checks remain manual release gates.
6. **Server-side validation is claimed as previously implemented, not revalidated here end-to-end.** Save handler routes returned `WP_Error` codes to allow-listed notices and preserves existing weight when omitted. Re-run boundary/Unicode, malformed input, vanished-record, insert/update failure and unchanged-value scenarios in isolated tests.
7. **Potential misleading notice coupling.** The general settings renderer displays an error notice map containing contact-specific error strings, while Contacts renderer independently displays its own allow-listed errors. Confirm the handler-to-screen redirects consistently place each message on the intended page; avoid stale or duplicated notices.

### Current decision / gates
- PR #4 remains **draft/unmerged**. No approval to merge, release, install, or deploy was exercised.
- No staging or production site/database was accessed or changed during this continuation.
- Before release: obtain passing CI for the exact final HEAD; review the complete PR diff; test admin behavior at desktop/mobile breakpoints and keyboard-only; exercise CRUD/status/validation/error paths; inspect package contents and correlate the exact candidate ZIP hash with CI output.
- Continue the broader privacy, consent, campaign capture, routing and package-provenance audit separately from this UI PR. Round-robin concurrency and persistence-verification behavior are tracked audit topics, not marked fixed.

## Full audit pass — 2026-11-01 (static review + CI evidence; staging checks outstanding)

**Audit target:** PR #4 branch `ui-compact-contact-form`, commit `0e572eb24e15abeeb51910f1d5abe815eeb7d338`. PR remains open, draft, unmerged. CI Build #723, run `41276995173`, completed successfully on this exact SHA (8 jobs). Report: https://github.com/dejabey/hatnikotni-chat/actions/runs/41276995173. The successful workflow includes PHP syntax/standards and JS syntax, skeleton contract, WordPress 6.6/MySQL migration lifecycle tests, and production packaging. This is CI validation, not visual/browser/staging verification.

### Audit 1 — Contacts CRUD/admin UI
**Code inspection outcome:** save validation rejects missing names, non-digit or 8–20 digit invalid phone formats, and over-limit name/role/description lengths. Contact CRUD distinguishes DB false from zero affected rows and checks concurrent deletion. Editor preserves stored Weight when omitted from compact form; error codes are routed to allow-listed text notices. Status mutation has capability + nonce checks.
**Findings retained:** status action is state-changing GET; recommend POST. Static Active/Inactive copy does not live-update as switch changes. Admin screen still needs human responsive/keyboard/focus/contrast review. A visually scrollable table can be usable, but needs browser verification. Save DB error handling and unchanged/no-op branches need controlled tests. UI does not constitute evidence for those tests.
**Disposition:** static review complete; browser and interaction checks pending.

### Audit 2 — Routing & persistence
**Code inspection outcome:** Direct validates active selected contact; random routing uses positive relative weights; round-robin reads shared option, selects next active contact in sort/id order, then writes state.
**Finding retained:** round-robin is not atomic. Concurrent requests may duplicate a selected contact or lose advancement. Random selection is weighted probability, not guaranteed proportional distribution over small samples. Direct method becomes unavailable if its configured contact is missing/inactive. The admin UI supports choosing a contact, but save normalizes an invalid/inactive selection to 0.
**Disposition:** algorithm review complete; race-condition stress test and any strict fairness guarantee are pending. Do not promise exact turn-by-turn round robin under concurrent traffic.

### Audit 3 — Privacy, consent & campaign
**Code inspection scope:** consent gate, campaign capture/revoke, analytics recording/aggregation/retention, visitor redirect flow and uninstall cleanup were reviewed at source level in this project audit stream.
**Positive controls:** analytics recording checks consent; native consent defaults false absent an explicit choice; UTM values are bounded/sanitized before campaign storage; analytics data records event/page/contact/device/campaign dimensions rather than a plugin-defined visitor identity; retention cleanup targets events older than 180 days; WhatsApp contact routing is designed to remain available independent of analytics consent.
**Residual limitations:** plugin cannot prevent web-server/CDN/WAF access logs or external WhatsApp processing. The policy statement should be understood as plugin application data, not all infrastructure traces. Consent cookie behavior must be rechecked with actual cache/CDN layers, cookie path/domain variants, JavaScript-disabled scenarios, revocation request failures, and browser reloads. The stored consent is browser-cookie based, not a server-side consent ledger. Native UI testing from earlier handoff is user-reported and exact ZIP provenance was not verified.
**Disposition:** code-path inspection complete for current pass; controlled browser/privacy validation and legal/policy review remain open.

### Audit 4 — Release readiness
- Build #723 passed for exact handoff SHA `0e572eb24e15abeeb51910f1d5abe815eeb7d338`: PHP 8.1–8.5 syntax/validation matrix, JavaScript syntax, skeleton contract, WordPress coding standards, WordPress migration lifecycle tests, and package build (see workflow run for authoritative job list).
- Exact candidate package artifact/hash has not been retrieved and independently correlated with a staging-installed ZIP in this audit pass.
- No live staging/production runtime, cache/CDN, mobile layout, keyboard-only browser, shortcode/WooCommerce context, or JavaScript-disabled test was executed here.
- Existing documented manual staging test matrix remains applicable; mark unperformed cases as pending, not passed.
- Uninstall routine intentionally deletes plugin-owned current and legacy tables/options. Do not use uninstall/reinstall as a validation shortcut on populated site data.

### Consolidated release recommendation
**NOT READY TO RELEASE / MERGE.** CI is green, but merge/release gates remain: resolve or explicitly accept the state-changing GET and admin status-label UX findings; decide how to message or address round-robin concurrency; add/execute controlled persistence and routing tests; complete privacy/cookie and browser accessibility/responsive staging matrix; correlate exact release ZIP hash; refresh package/release documentation. Keep PR #4 draft. No runtime source was modified by this audit checkpoint; no staging/production site or database was accessed or changed.


## Follow-up hardening — 2026-11-01

- Converted contact activation/deactivation from state-changing GET links to POST forms. Each form submits the contact ID and action-specific nonce; the handler reads the ID from POST and validates the nonce field explicitly. Capability checks and allow-listed result redirects remain in place.
- Source commit: `94f3338d269c47981b341bc8962de3aa111d928c` (`includes/class-hatnch-admin.php`). This change addresses the audit's GET-mutation hardening recommendation; it does not claim browser regression tests.
- CI must be confirmed for this exact commit before considering the patch validated. No staging or production site/database was accessed or changed.
- Admin status label remains static text in the edit switch; it can display stale Active/Inactive wording if toggled before submit. Follow-up UX choice: remove the redundant status state copy or bind it accessibly to the switch in a dedicated tested frontend/admin script. This is not marked resolved.
- Round-robin concurrency, controlled DB/routing tests, privacy/cookie checks, browser accessibility/responsive matrix, exact staging ZIP hash correlation, and Plugin Check candidate correlation remain open release gates.
- PR #4 remains draft/unmerged; no release or deployment approval was made.


## Audit continuation — 2026-11-01 (persistence regression harness + final-HEAD CI)

**Target:** PR #4, branch `ui-compact-contact-form`. Source HEAD under verification: `73076b384629a154a03a939b6a1b72410783021c`. PR #4 remains open, draft, and unmerged. No deployment or merge was authorized.

### Persistence regression harness
- Added `tests/contacts-regression.php`, a standalone PHP test-double suite for `HATNCH_Contacts`.
- Wired the suite into `tests/skeleton-contract.sh`; CI's skeleton contract step now executes the persistence tests.
- Intended coverage: contact insert/readback; update/readback; no-op update; status no-op/change and persistence; missing contact status failure; insert/update DB error handling; deletion between existence check and update; missing-record edit; required-name validation.
- **Test boundary:** the fake wpdb harness tests the PHP class's CRUD/error branches. It does not exercise a real MySQL server, database isolation, actual concurrent requests, lock contention, or routing fairness. Its simulated concurrent deletion is deterministic test-double behavior, not a race test against MySQL.

### CI evidence — verified for exact source HEAD
- GitHub Actions **Validate**, run **#734**, run ID `41280413559`, completed with conclusion **success** for commit `73076b384629a154a03a939b6a1b72410783021c`.
- All eight jobs passed: WordPress migration lifecycle tests; migration integration tests; validation on PHP 8.1, 8.2, 8.3, 8.4 and 8.5; and production package build.
- Validation includes PHP syntax, frontend JavaScript syntax, skeleton contract (including the new persistence regression script), and WordPress Coding Standards.
- Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/41280413559
- This is CI evidence for the exact commit. It is not evidence of browser usability, runtime routing concurrency, staging artifact provenance, or production readiness.

### Admin state mutation hardening
- The current handler uses a capability check, contact-specific nonce, POSTed ID and explicit target state (0/1), then redirects to an allow-listed success/error destination.
- This avoids toggle ambiguity from stale forms by expressing the intended state. Confirm actual form method/field contract and status UI manually before release.
- The compact form omits Weight while retaining the stored field; inspect the save path when changing editor fields to ensure preservation remains intentional.

### Remaining gates (not passed by CI)
1. Manual responsive, keyboard-only, focus/contrast and status-switch UI checks.
2. Controlled real database tests for CRUD, no-op semantics, DB failures and concurrent deletion; the current fake harness is useful but not a substitute for integration coverage.
3. Actual round-robin contention/concurrency tests and an explicit decision on strict fairness guarantees. Advisory-lock behavior/support on target hosting must be verified against current implementation.
4. Review complete PR diff and confirm status POST contract and redirect/notice behavior end-to-end.
5. Correlate candidate release ZIP SHA-256 with the exact artifact installed on staging; run Plugin Check against that candidate.
6. Complete previously listed privacy/cookie, cache/CDN, mobile/browser and runtime routing test matrix.

### Decision
**CI gate: PASS for source HEAD `73076b384629a154a03a939b6a1b72410783021c`. Overall merge/release gate: NOT YET PASSED.** Keep PR #4 draft/unmerged pending remaining manual/integration gates and review. No staging or production site/database was accessed or modified during this checkpoint.


### Production-package artifact evidence — Build #734
- CI artifact name: `hatnikotni-chat-0.1.8`
- Artifact ID: `13072358694`
- Size: `46,234 bytes`
- GitHub artifact digest: `sha256:d7f65d3b8e2e3f468ea3d0f8bb1a38f413c313717f37e27a03cefa17fa5b599f`
- Artifact source: branch `ui-compact-contact-form`, HEAD `73076b384629a154a03a939b6a1b72410783021c`; workflow run `41280413559`.
- Artifact URL: https://api.github.com/repos/dejabey/hatnikotni-chat/actions/artifacts/13072358694
- **Provenance qualification:** the CI artifact digest identifies the build artifact, not the ZIP currently installed on staging. No claim of staging package match is made until that installed ZIP is independently hashed and compared.
- This artifact evidence is for source HEAD `73076b3`. The later `PROJECT-HANDOFF.md`-only commits do not change plugin runtime files or the generated package inputs. The latest documentation commit still has no separately observed workflow run.


## Final audit checkpoint — 2026-11-01 (current PR HEAD CI verified)

- Rechecked PR #4 metadata: open, draft, unmerged; head `af33c0d6377d2d781c7acbd8889869f3e382beac`, base `migration-hardening`. No merge or deployment performed.
- **Correction to the earlier handoff checkpoint:** a workflow run is now present for the latest documentation head. GitHub Actions Validate **Build #736**, run ID `41281003996`, completed with conclusion **success** for exact HEAD `af33c0d6377d2d781c7acbd8889869f3e382beac`. All eight jobs passed: migration lifecycle tests, migration integration tests, PHP validation on 8.1–8.5, and production package build. Run: https://github.com/dejabey/hatnikotni-chat/actions/runs/41281003996
- Build #736 production artifact: `hatnikotni-chat-0.1.8`; artifact ID `13073540665`; size `46,234 bytes`; digest `sha256:7f91317a5e9d6006f19d89d478ee8253635ad53daf5a6202c4ce0cca6d55c655`.
- This artifact is the current candidate tied to the current PR HEAD. It does **not** prove that staging has this artifact installed; independent installed-ZIP SHA-256 comparison remains required.
- Current source review confirms the contact status mutation path uses POST with capability check, per-contact nonce, explicit requested status, and allow-listed success/error redirects. The formerly stale switch-state label element is absent; the switch label says “Status” and does not promise a dynamic Active/Inactive copy.
- Round-robin implementation currently acquires a MySQL named lock, selects and advances shared state while locked, verifies state, and releases in `finally`. CI verifies contracts/syntax, not actual target-server GET_LOCK support, connection persistence, lock timeout contention, or parallel request behavior. Confirm host engine compatibility and run controlled concurrency testing before any strict round-robin assurance.
- Contacts regression tests are a fake-wpdb test-double suite wired into the skeleton contract. They cover several CRUD/error/no-op and simulated deletion cases, but are not actual MySQL persistence or concurrency integration tests. The test file itself states this boundary.
- **Release disposition remains HOLD / NOT READY.** Automated CI is green for exact HEAD; outstanding release gates remain: actual staging ZIP hash correlation and candidate Plugin Check; desktop/mobile visual and keyboard/accessibility review; direct/random/round-robin runtime checks including lock contention on target database; real DB failure/no-op/concurrent-deletion tests; privacy/consent, cache/CDN, cookie and browser matrix; review/approval of full PR diff. Keep PR #4 draft/unmerged until gates are addressed and explicit approval is given.
- No staging or production site/database was accessed or changed during this checkpoint.
