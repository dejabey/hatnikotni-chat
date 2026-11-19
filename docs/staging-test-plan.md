# Hatnikotni Chat — Contact Management Staging Test Plan

## Scope
Validate contact CRUD, state transitions, routing, and historical analytics on staging. Run destructive cases only with disposable test contacts. Do not delete or deactivate production contacts as part of this plan.

## Preconditions
- Take a current staging backup.
- Record plugin version/build and current routing method.
- Record the current Default Contact ID.
- Record active contact IDs and current round-robin state.
- Record analytics event totals and per-contact attribution for test fixtures.
- Create dedicated disposable contacts for each destructive test.

## Contact list presentation
- [ ] Confirm the finalized UI: ID, Name, Phone, Role, Status; no Weight or Actions columns.
- [ ] Confirm Edit, Activate/Deactivate and Delete are row actions beneath Name.
- [ ] Confirm Delete is red and rendered without a button box.
- [ ] Confirm long names are ellipsized on desktop and remain usable on mobile.
- [ ] Confirm mobile-only horizontal-scroll guidance and horizontal access to all columns.
- [ ] Confirm no visual redesign is introduced by this hardening change.

## Edit and save
- [ ] Edit a disposable contact and save changed fields.
- [ ] Verify persisted values and updated timestamp.
- [ ] Submit missing name and malformed phone values; confirm server-side validation rejects them.
- [ ] Confirm editing one field does not unintentionally change other fields.

## Routing
- [x] Direct selects active default contact
- [ ] Invalid/inactive default fails safely
- [ ] Random selects only active contacts
- [x] Sequential round-robin alternation observed with two active contacts (staging Ujian 3.19: PakYa → PakSu → PakYa)
- [x] Round-robin state persisted across reload in earlier staging test
- [ ] Inactive contacts are skipped (observed route to active default; skip behavior not isolated conclusively)
- [x] Concurrent requests produce valid routing (0.1.26, 2026-11-18: 12 concurrent HTTP requests → 6/6, all handler-confirmed 302; see log below)

### Purpose-based routing (staging observations)
- [x] Purpose chooser enabled; configured mappings verified during Ujian 3.19
- [x] Role-targeted routing: General Enquiries → PakYa; Customer Support → PakSu; Technical Support → PakYa (tests 3.6–3.10)
- [x] No-match fallback: Sales & Partnerships routed to active default PakYa (test 3.8)
- [x] Role-unassigned fallback to global Round robin observed: repeated General Enquiries yielded PakYa (ID 1) → PakSu (ID 3) → PakYa (ID 1) (Ujian 3.19)
- [x] Contact and routing settings restored/verified against target baseline before Ujian 3.19
- [ ] Concurrency/parallel routing not verified; sequential alternation is not proof of race safety

## WhatsApp action
- [x] Global button works
- [ ] Shortcode works
- [ ] Both use the same routing/action layer
- [ ] Default/custom message works
- [x] Destination is HTTPS wa.me with normalized phone
- [x] Analytics failure never blocks redirect
- [x] No frontend JS required for core action
- [ ] Failure fallback is safe

## Activate / Deactivate
- [ ] Activate an inactive disposable contact via POST.
- [ ] Deactivate a non-default active disposable contact via POST.
- [ ] Verify each state in the database and contact list.
- [ ] Verify malformed/missing nonce and unauthorized capability are rejected.
- [ ] Verify direct GET to the toggle handler does not change state.
- [ ] Attempt to deactivate the current Default Contact; confirm the operation is blocked and a clear admin notice is shown.
- [ ] Set another active Default Contact, then verify the previous default can be deactivated.

## Permanent Delete
- [ ] Delete a disposable non-default contact and cancel confirmation; verify no data changes.
- [ ] Delete the same disposable contact and confirm; verify the contact row is removed.
- [ ] Verify its analytics event rows remain, with contact attribution set to NULL.
- [ ] Verify unrelated contact records, events, and routing settings remain unchanged.
- [ ] Verify missing/invalid nonce and unauthorized capability are rejected.
- [ ] Verify a stale ID produces a not-found notice without changing data.
- [ ] Delete a disposable current Default Contact; verify the tailored confirmation text.
- [ ] After confirmation, verify the default setting is cleared and Direct routing has no recipient until a new active default is selected.
- [ ] Verify database failure/transaction refusal does not report a successful deletion and analytics remain intact.

## Routing regression
Use disposable test contacts and capture the resolved destination without sending a real enquiry.
- [ ] Direct resolves the configured active Default Contact.
- [ ] Direct returns no contact when default is missing, inactive, or deleted.
- [ ] Random selects only from active contacts.
- [ ] Round-robin follows `sort_order`, then ID, and skips inactive contacts.
- [ ] Deactivated or deleted contacts are not selected.
- [ ] Routing state and analytics attribution are consistent after each controlled test.
- [ ] Restore intended routing method, Default Contact and fixture state after tests.

## Analytics and cleanup
- [ ] Compare event counts before and after tests.
- [ ] Confirm deleted-contact events remain unattributed, not deleted.
- [ ] Confirm analytics retention/cleanup behavior is unchanged.
- [ ] Remove disposable contacts only after collecting evidence.
- [ ] Record screenshots, database checks and plugin-check output.

## Exit criteria
All applicable checks pass, no PHP errors or unexpected database changes occur, documentation matches shipped behavior, and the reviewed ZIP matches the intended source commit. Record skipped tests explicitly; do not report them as passed.

## Analytics Management regression

- Verify database version upgrades to 1.2.0 and the nullable `contact_reference` field/index exist without losing prior events.
- Record a consented click for a contact and confirm both `contact_id` and `contact_reference` identify the same contact.
- Delete a disposable contact; confirm related events keep `contact_reference`, set `contact_id` to NULL, and report as `Removed contact #ID`.
- Confirm already-anonymized historic events remain `Unknown / removed` and are not retroactively attributed.
- Export each supported reporting period; compare aggregate totals against the dashboard and inspect that phone numbers, event IDs, and visitor identifiers are absent.
- Test CSV values beginning with `=`, `+`, `-`, and `@` are neutralized against spreadsheet formula interpretation.
- Reject export/deletion requests without `manage_options`, with an invalid nonce, or with invalid reporting periods/dates.
- Delete a date range and confirm only events inside the inclusive local date boundaries are removed; adjacent-day records remain.
- Delete all analytics after explicit `DELETE` confirmation; verify contacts, routing settings, and plugin options remain unchanged.
- Confirm retention cleanup remains bounded to events older than 180 days.
- Confirm Default badge follows the configured contact and routing method behavior remains unchanged.


## 0.1.11 candidate — Default Contact and Analytics layout
- [ ] Switch Default from contact row; verify confirmation target.
- [ ] Verify one Default badge and former contact remains active.
- [ ] Verify Direct routing recipient changes; test old and new contacts.
- [ ] Confirm analytics history unchanged by switching.
- [ ] Confirm current Default cannot be deactivated or deleted, including valid-nonce POST.
- [ ] After reassignment, verify prior contact can be toggled/deleted as permitted.
- [ ] Verify Export and Delete-by-range are side-by-side on desktop; Delete All is separate/full-width.
- [ ] Verify management panels stack on mobile and remain usable.
- [ ] Regression-test Direct, Random, Round Robin, consent, attribution, export/deletion and contact CRUD.
- [ ] Run syntax, standards and full staging checks.


## 0.1.12 candidate — Analytics layout refinement

- [ ] Analytics panels occupy the available content width without overflowing WordPress admin.
- [ ] By Device and By Contact appear as equal-width columns on desktop.
- [ ] By Campaign spans both columns.
- [ ] Export Reports and Delete Analytics Records are equal-width; Delete All remains a separate full-width warning section.
- [ ] Tables do not cause unintended horizontal scrolling at common desktop widths.
- [ ] At mobile widths, analytics and management sections stack into one column; controls remain usable.
- [ ] Export CSV, date-range deletion, and Delete All are functionally tested separately.
- [ ] Verify these operations leave contact records, default contact, routing configuration, and plugin settings unchanged.

Candidate 0.1.12 is a local package derived from the available 0.1.11 staging candidate. It has not been pushed to GitHub.

### 0.1.13 — Analytics KPI row
- [ ] Desktop: WhatsApp Clicks, Retention, and Reporting Period form three equal-width cards across the full available content width.
- [ ] Tablet/mobile: KPI cards stack in one column at 782px and below.
- [ ] Reporting period Apply continues to submit the selected period.
- [ ] Confirm no horizontal overflow and no regression to analytics sections or destructive actions.

### 0.1.14 — Analytics KPI full-width layout
- [ ] Desktop: the three summary items (WhatsApp Clicks, Retention, Reporting Period) are direct grid items and fill the full available row in three equal columns.
- [ ] Confirm computed/visual widths align with the content edges of the analytics panels below.
- [ ] At 782px and below, summary items stack into one column.
- [ ] Confirm Apply/reporting period, By Device, By Contact, By Campaign and data-management forms still render and function.
- [ ] Capture desktop and mobile screenshots; do not mark visual acceptance from CSS inspection alone.

### 0.1.15 — Mobile Analytics layout
- [ ] At mobile viewport (e.g. 302px), By Device, By Contact, and By Campaign each use the full content width in one vertical column.
- [ ] Reporting period label, selector, and Apply button are left-aligned and full-width.
- [ ] No horizontal overflow; table text wraps without truncation.
- [ ] Desktop KPI three-column layout and management panel layout remain intact.

### 0.1.16 — KPI full-width regression check
- [ ] Desktop KPI row spans the same usable content width as Analytics Data Management.
- [ ] Three equally sized columns on desktop; one column at viewport widths <= 782px.
- [ ] Verify cache/version refresh, no overflow, and unchanged period Apply behavior.


## 0.1.18 — WhatsApp new-tab navigation
1. Install on staging and confirm the active plugin version is 0.1.18.
2. Click the floating WhatsApp button: confirm the current Jelajah Perlis tab remains on the same page and a new tab navigates through the plugin click endpoint to WhatsApp.
3. Repeat with the `[hatnikotni_chat]` shortcode button.
4. Test when analytics consent is accepted and rejected; WhatsApp contact remains available in both cases.
5. Verify recipient and event attribution; note browser popup policy may affect automation, but a direct user click should open the new tab.
6. Then test Round Robin with at least two active contacts, including the Default. Record each selected recipient across sequential clicks and confirm the Default participates according to the configured method.

Production activation remains a separate approval step.

## Staging test log — Ujian 3.19
- Environment: `staging.perlis.xyz` only; production untouched.
- Baseline contacts verified: PakYa (ID 1, active, default, role `unassigned`); PakSu (ID 3, active, not default, role `unassigned`); znz (ID 5, inactive).
- Routing configuration verified: method `Round robin`; Purpose chooser enabled; mappings unchanged from the test plan.
- Three consecutive `General Enquiries` routing outcomes: PakYa → PakSu → PakYa.
- Result: **PASS** for observed sequential global fallback behavior.
- Scope: functional sequential observation only; does not establish concurrent correctness, exhaustive error handling, or production readiness.


## Ujian 3.20 — Routing dengan satu contact aktif

**Keputusan: LULUS (ujian berurutan; bukan ujian concurrency).**

- Baseline awal disahkan sepadan.
- PakSu (ID 3) dinyahaktifkan sementara; PakYa (ID 1) kekal Active dan Default; znz (ID 5) kekal Inactive.
- Dengan hanya PakYa aktif, routing **General Enquiries** memilih PakYa (ID 1) — Lulus.
- Dengan konfigurasi sama, routing **Customer Support** memilih PakYa (ID 1) — Lulus.
- PakSu diaktifkan semula dan pengguna mengesahkan baseline dipulihkan — Lulus.

**Interpretasi:** Kedua-dua senario yang diuji berjaya memilih satu-satunya contact aktif. Ini mengesahkan hasil bagi senario tersebut sahaja; tidak membuktikan semua laluan ralat, concurrency/race safety, atau keseluruhan kesiapsiagaan release.


## Ujian 3.21 — Pengesahan identiti build v0.1.25

**Keputusan: LULUS untuk identiti ZIP (SHA-256 exact match).**

- Environment: `staging.perlis.xyz` sahaja; production tidak disentuh.
- WordPress Installed Plugins menunjukkan Hatnikotni Chat versi `0.1.25`, status Active.
- Plugin header dalam `hatnikotni-chat.php`: `Version: 0.1.25`.
- Constant dalam fail utama: `HATCH_VERSION = 0.1.25`; `HATCH_DB_VERSION = 1.2.0`.
- Pengguna mengeksport folder plugin terpasang sebagai `hatnikotni-chat-installed-qa.zip` dan memuat naik arkib untuk pemeriksaan.
- SHA-256 ZIP diperiksa: `78a83dd9db41ff6636bc4366a8a9e73514f531e92d5da349cd295836015c751e`.
- SHA-256 tersebut sepadan tepat dengan rekod SHA-256 ZIP stable v0.1.25 yang diperiksa terdahulu: `78a83dd9db41ff6636bc4366a8a9e73514f531e92d5da349cd295836015c751e`.

**Interpretasi/skop:** Identiti arkib ZIP terpasang sepadan byte-for-byte dengan ZIP stable yang dirujuk. Ini mengesahkan padanan arkib bagi bahan yang dibandingkan; ia bukan bukti tersendiri bahawa semua fungsi telah diuji, concurrency/race safety selamat, atau release/production sudah diluluskan.


## Ujian 0.1.26-RR — Round Robin sequential + concurrent (HTTP, 2026-11-18)

**Result: PASS (sequential and concurrent distribution); lock-contention isolation not established.**

- Build: Hatnikotni Chat 0.1.26. Installed ZIP `hatnikotni-chat-0.1.26.zip`, SHA-256 `5F59A39E…42AD`. This is the inner ZIP of CI #842 artifact `78b31a78…1fba`, built from source `25d0d40`.
- Environment: `staging.perlis.xyz`, WordPress 7.1.4, PHP 8.4.27, LiteSpeed, `WP_CACHE=false`, default object cache.
- Method: `tests/staging-rr-http-check.sh` via `curl` from the user's PC, without following redirects. The destination is recorded only as a 12-character SHA-256 prefix (ID 1 `5be82a43464d`, ID 3 `3ddfc822c932`).
- Before: active IDs 1, 3; inactive 5; `last_contact_id=1`.
- Sequential 4: 3, 1, 3, 1.
- Concurrent 12, launched within 109 ms: ID 1 ×6, ID 3 ×6.
- All responses were `302` with `X-Redirect-By: Hatnikotni Chat`. There was no fallback to the home page.
- After: `last_contact_id=1`, as expected.
- Limits: requests may have been serialized by the server before reaching the handler. No WhatsApp messages were sent.

## Ujian 0.1.27-RR — Round Robin re-check after the Plugin Check clean-up (2026-11-19)

**Result: PASS.** Installed build: 0.1.27 from CI #868 artifact `8248a39e…` (inner ZIP `068B2081…176B`), source `7263a09`. Plugin Check (user-reported): no errors. Before: `last_contact_id=1`. Sequential: 3, 1, 3, 1. 12 concurrent requests (111 ms window): 6/6. All responses were `302` with `X-Redirect-By: Hatnikotni Chat`. After: `last_contact_id=1`. Limits are the same as Ujian 0.1.26-RR.

## Ujian 0.1.27-BR — Browser matrix (2026-11-19)

**Result: 20/21 PASS, 1 FAILED (step 9 — UTM attribution not stored).** The owner ran 21 steps (checklist in a private Claude Sheet). Claude cross-checked analytics events via WPVibe (baseline: 49 events).
- Consent Accept → event #50; Reject → no event; consent persists across reload.
- UTM cookie appears only after Accept and is removed on Reject.
- Keyboard Tab, Enter and Escape focus work.
- Mobile, shortcode, WooCommerce pages and JS-disabled fallback all work.
- **Step 9:** event #51 had `utm_campaign` NULL. Root cause and fix are in 0.1.28 (see `PROJECT-HANDOFF.md`). Re-test steps 7–10 on 0.1.28.

## Ujian 0.1.28 — attribution fix and Round Robin (2026-11-19)

**Result: PASS.**
- Step 9 re-test: event #53 (11:31:43) carries `utm_source=ujian`, `utm_medium=qa`, `utm_campaign=c0127`. This is the first attributed event on staging.
- Round Robin HTTP check: 3, 1, 3, 1; 12 concurrent requests → 6/6; all `302` with `X-Redirect-By: Hatnikotni Chat`; cursor 1 → 1.
- Package hash correlation for 0.1.28 is still pending.

## Ujian 0.1.29 — concept B UI, greeting card, Round Robin fallback

**Result (2026-11-19 14:45–15:30 MYT): 22/22 PASS (owner-run; checklist Sheet https://claude.ai/artifact/EVhkB38Ka39yFgjx4qyyDN), cross-checked by Claude with one WPVibe query. See the results section in `PROJECT-HANDOFF.md`.**

Original plan: Install only the **inner** ZIP from the 0.1.29 CI artifact, after hashing the outer artifact against the recorded digest. Record the cursor before and after. Do not force lock failures on staging.

Package and baseline
1. Hash the outer artifact and the inner ZIP; compare with the CI digest in `PROJECT-HANDOFF.md`.
2. After installation: version `0.1.29` active; contacts, routing method, cursor and analytics count unchanged (WPVibe, read-only).
3. Plugin Check: no errors.

Floating button (light page and a dark page or dark section, desktop and phone)
4. Round green bubble with a dark WhatsApp glyph; label chip visible on desktop, hidden on a phone (≤480 px).
5. Shield badge sits on the bubble's outer top corner and does not cover the label. Theme styles do not distort the button (no theme padding, borders or uppercase text).
6. Settings → Button & message: untick "Show the label beside the button", save → chip gone, the button still has an accessible name (screen reader or devtools).
7. Bottom-left position mirrors correctly (badge on the top-right corner).

Consent card
8. Fresh browser (no consent cookie): open the badge → neither Tolak nor Terima is selected; focus lands on Tolak.
9. Terima → selected; status message; reload → still Terima. Click WhatsApp → one new analytics event.
10. Tolak → selected; reload → still Tolak; click WhatsApp → no new event; the `hatnch_campaign` cookie is removed.
11. Butiran shows the full explanation; Privacy Policy link opens.
12. Escape and the × button both close the card and return focus to the badge.
13. Repeat a UTM URL → Terima → click; event carries the UTM values (regression for 0.1.28).

Keyboard and fallback
14. Tab order: badge, then the WhatsApp button; focus rings visible on light and dark pages.
15. JavaScript disabled: the WhatsApp button works; the badge does nothing; no card is visible.

Greeting card (enable it only for this test, then restore the setting)
16. Enable the greeting card (delay 5 s) → it appears once after about 5 s without moving focus; reload in the same tab → it does not appear again.
17. CTA opens WhatsApp through the click endpoint (purpose routing off) or opens the purpose chooser (purpose routing on).
18. × or Escape closes it. A new private window shows it again.
19. Disable the greeting card again and confirm it no longer renders.

Round Robin
20. Run `tests/staging-rr-http-check.sh` from the owner's PC: sequential alternation and 12 concurrent → 6/6; all `302` with `X-Redirect-By: Hatnikotni Chat`.
21. Routing tab → Routing failures shows "None recorded." (or explain any count).

Translation
22. With the site or user language set to Malay, skim the admin tabs (Setup, Routing, Contacts, Analytics Data Management) and the front-end card for wording that reads wrong.
