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
