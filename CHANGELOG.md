## 0.1.30 — readme "External services" section (release candidate; not released)

- `readme.txt` gains an `== External services ==` section, as WordPress.org reviewers require for any third-party service. It says when visitors are redirected to WhatsApp (`wa.me`), what the URL carries (the routed contact's number and the optional pre-filled message), that the visitor's browser makes the request, that analytics stay in the WordPress database, and links to WhatsApp's Terms of Service and Privacy Policy (both URLs checked on 2026-11-19).
- Malay consent wording: "Accept" is now **"Setuju"** (was "Terima"), and "Analytics accepted." is "Anda bersetuju dengan analitik." (was "Analitik diterima."). On a site that machine-translates pages with GTranslate, "Terima" became "Thank you", which hid the meaning of the consent button. Found by the owner on staging, 2026-11-19 16:21 MYT. A contract test guards it.
- No PHP, JavaScript or CSS changes: the plugin behaves exactly as 0.1.29 apart from those two Malay strings.

## 0.1.29 — Concept B front end, greeting card, Round Robin fallback (release candidate; not released)

### Front-end redesign: concept B
- The floating button is now a round 60 px WhatsApp bubble (56 px on phones) with an optional label chip beside it. The plugin's own WhatsApp glyph is kept.
- **Contrast fix:** the glyph and the inline shortcode text are now dark green `#0b2e1a` on `#25d366` (7.46:1; hover `#1ebe5d`, 6.04:1). The previous white text on `#25d366` was 1.98:1 and failed WCAG AA.
- The privacy control is a shield badge on the bubble's outer top corner: a 28 px dark disc with a 2 px white ring inside a 44 px hit area. It stays visible on dark pages (white ring on `#1e293b`: 14.63:1; the previous dark-only shield measured 1.11:1). It remains a separate button, never nested inside the WhatsApp link, and still comes first in Tab order.
- Focus rings use a white-dark-white ring so focus is visible on light and dark pages.
- New setting **Show the label beside the button** (`button_show_label`, default on, so existing sites keep a visible label). The chip is hidden below 481 px wide. The accessible name is the label in every case; an empty label falls back to "Contact Us".
- **Compact consent card** (232 px): title, a one-line summary, a Reject/Accept segmented control (32 px high), Privacy Policy and Details links, and a close button. The full explanation stays behind Details (previously "Read More").
- Consent semantics are unchanged: the same `hatnch_analytics_consent` cookie (`yes`/`no`), the same campaign capture on accept and cookie clearing on reject, and WhatsApp works either way. One improvement: with no saved choice, neither option is shown as selected (the old switch looked like "rejected").
- Keyboard: opening the card focuses the selected choice (or Reject when none); Escape and the new close button return focus to the shield badge. With JavaScript off, the WhatsApp link still works and the badge does nothing, as before.
- Theme-proofing: the widget resets inherited `a`/`button` styles (padding, borders, text-transform, min-height).

### Greeting card: concept C (opt-in, off by default)
- New setting **Greeting card** (`greeting_enabled`, default **off**) with optional title, text (empty uses the translated defaults "Got a question?" / "Chat with our team on WhatsApp.") and delay (0–60 s, default 5).
- When enabled, a 292 px card appears above the button **once per browser session** after the delay. It never takes focus, sets no cookies (only a `sessionStorage` flag), stays hidden with JavaScript off, and does not appear if the privacy or purpose panel is already open.
- The card's "Start a WhatsApp chat" button uses the same click endpoint as the floating button; with purpose routing on, it opens the purpose chooser instead. Close (44 px), Escape, or using the main button or the privacy badge retires it; focus returns to the WhatsApp button when the card had focus.
- Deliberate departures from the design canvas: no "online" indicator (the plugin has no real availability data, so it would be a false claim), no staff initials (it would add a contacts query to every page and publish staff names), and no separate privacy link in the card (the shield badge stays visible).
- CTA contrast: white on `#0f6b3a`, 6.59:1.
- New script `assets/js/hatnikotni-chat-greeting.js`, enqueued only when the card is enabled. CI now runs `node --check` on every file in `assets/js/`.

### Round Robin fallback and observability
- **Fix:** when the Round Robin advisory lock timed out, or the cursor could not be read, saved or verified, `resolve()` returned null and the visitor was **silently redirected to the home page**. The visitor is now routed to a fallback contact from the same eligible set: the default contact if it is active and eligible, otherwise the first one. The cursor is not moved, so normal rotation resumes once the failure clears.
- A purpose lane whose contacts all become inactive during the lock now falls back to global routing instead of the home page.
- New PII-free failure counter (`hatnch_routing_failures` option, not autoloaded): totals per reason and first/latest timestamps only. Reasons: `lock_unavailable`, `state_read_failed`, `state_write_failed`, `state_verify_failed`, `no_contact` (the click endpoint had no active contact at all). Shown under **Routing → Routing failures** in the admin. Increments are best effort and can under-count when failures race. Removed on uninstall.
- New action `hatnch_routing_failure( $reason, $lane, $fallback_contact_id )` (`$lane` is `global` or the purpose role; ID 0 means the visitor got no contact) and filter `hatnch_routing_fallback_contact( $contact, $contacts, $reason, $lane )`; a filtered contact outside the eligible set is ignored.
- Trade-off: during a sustained failure, all traffic goes to one contact. The counter makes that visible.
- Test: `tests/routing-fallback-integration.php` (WordPress + MySQL in CI) holds the lock from a second connection so `GET_LOCK` really times out, then checks the fallback contact, the unchanged cursor, the counter, the hook, the filter guard and resumed rotation. It was committed first (`7c603eb`) and run against the old code.

### Translations
- POT regenerated (`wp i18n make-pot`). New strings: "Optional analytics", "Clicks are counted only if you accept. WhatsApp works either way.", "Details", and the label-chip setting text; "Read More" removed. Malay added for these four and for "Close", plus the 11 greeting-card strings and 9 routing-failure strings.
- **Malay translation complete: 226/226 strings** (the 98 previously untranslated admin, migration and purpose-routing strings are now translated). Terms follow the existing catalogue ("kenalan lalai", "penghalaan", "peristiwa analitik"). The delete-all confirmation keeps the literal `DELETE` token that the code checks; a contract test guards this. Placeholders (`%s`, `%d`, `%1$s`, `%2$s`) were checked for parity on every string.

### readme.txt
- Added `Contributors: zaryl` (owner-supplied WordPress.org username; the profile exists, joined June 2009).

### Tests
- `tests/skeleton-contract.sh` now asserts the bubble, the badge, the label setting (rendered and saved), both consent choices with `aria-pressed`, the close handler and focus return, that the badge precedes the WhatsApp action instead of being nested in it, and that white text on `#25d366` is not reintroduced. The obsolete `role="switch"` assertion is replaced by a check that the old switch is gone.

## 0.1.28 — Campaign attribution fix

- **Fix:** UTM campaign attribution was never stored on click events, from its introduction in `5617257` (2026-09-23) through 0.1.27. `HATNCH_Campaign::get_attribution()` ran `sanitize_text_field()` on the cookie before `rawurldecode()`. `sanitize_text_field()` strips percent-encoded octets, so the JSON payload was destroyed and every event was stored with NULL UTM fields. The cookie is now decoded first and each field sanitized and length-limited, as before.
- Found by the 0.1.27 staging browser matrix (step 9, 2026-11-19): event #51 had `utm_campaign` NULL while the `hatnch_campaign` cookie was present. All 51 staging events had NULL UTM fields.
- Added `tests/campaign-attribution-integration.php`, a WordPress-backed CI test covering consented capture, the cookie round-trip as PHP and WordPress deliver it, the stored click event, malformed cookies and withdrawn consent.
- Historical events cannot be repaired: the attribution was never stored.
- `readme.txt` restructured into the standard WordPress.org order. There is now a single `== Changelog ==` (previously three, one placed before Description) plus a `Tags:` line; Privacy, Contact management, Analytics management and Extension points are now `= subsections =` of Description; a stray Markdown `## 0.1.18` section was folded into the changelog; and an Upgrade Notice was added for 0.1.28. All other content is unchanged.
- Translation template regenerated with `wp i18n make-pot`: 115 → 204 strings (the POT had been stale since 0.1.7). `hatnikotni-chat-ms_MY.po` merged with `wp i18n update-po`. 104 Malay translations are kept; 10 obsolete strings no longer in source were dropped; **99 strings are untranslated** and show in English until translated.
- No `Contributors:` line yet: it needs the owner's WordPress.org username.

## 0.1.27 — Plugin Check clean-up

- Resolved all 10 Plugin Check warnings reported against 0.1.26 (9 in `class-hatnch-routing.php`, 1 in `class-hatnch-admin.php`) with narrowly scoped, justified `phpcs:ignore` comments. The direct database calls are required for the Round Robin advisory lock and cache-bypassing cursor persistence.
- Removed the project-wide exclusions of `WordPress.DB.DirectDatabaseQuery.DirectQuery`, `.NoCaching` and `WordPress.Security.NonceVerification.Recommended` from `phpcs.xml.dist`, so CI now reports what Plugin Check reports.
- No runtime behavior change: `class-hatnch-routing.php` is token-identical to 0.1.26 after stripping comments and whitespace.
- CI: the incremental PHPCS step now ignores deleted files (`--diff-filter=d`).

## 0.1.26 — Round Robin persistence and contact status result handling

- Persist and verify Round Robin cursors atomically in the database while holding the routing advisory lock, avoiding stale option-cache reads; lock release failure fires `hatnch_routing_lock_release_failed`.
- Distinguish an unchanged contact from a missing contact when updating status; handle concurrent deletion during save.
- Sanitize purpose-role input; explicit analytics nonce checks and prepared statements; admin denials return HTTP 403.
- Added a 12-worker concurrent Round Robin WordPress/MySQL CI test; removed temporary QA diagnostics.
- (Entry added on 2026-11-19; it was previously recorded only in `readme.txt`.)

## 0.1.25 — Round-robin serialization candidate

- Serialize global and purpose-role round-robin cursor selection using database advisory locks.
- Fail closed if a routing lane lock cannot be acquired.
- Preserve purpose-routing behavior and existing cursor option formats.
- Requires WordPress staging verification; not approved for production.

## 0.1.24
- Removed redundant table-scroll hint markup and obsolete responsive hint CSS. The Contacts table keeps its genuine horizontal overflow behavior for narrow viewports.
- Constrained two-column Analytics tables to their panel width and wrapped long values to avoid unnecessary horizontal scroll cues.
- No routing, persistence, consent, or analytics collection logic changed.

## 0.1.23
- Refined mobile purpose chooser sizing, safe-area spacing, scroll behavior, and option touch targets.
- Improved privacy shield icon visibility and compacted privacy links.
- Removed theme-derived border styling from the primary WhatsApp action while preserving visible keyboard focus.

# Changelog

## 0.1.19 — Purpose-based support routing

- Added optional purpose chooser to the floating WhatsApp entry point.
- Added four visitor purposes: General Enquiries, Listing Assistance, Business & Partnerships, and Technical Question (light support).
- Added standardized contact routing roles: General Enquiries, Business Support, and Business & Partnerships.
- Purpose routing selects active role-assigned contacts using the configured Direct, Random, or Round Robin method; Direct prefers the configured Default Contact when that contact belongs to the assigned role, otherwise the first eligible contact.
- Purpose routes fall back to existing global routing when no active contact matches the assigned role.
- Added optional support email display for complex technical issues and payment investigations.
- Added an accessible chooser interaction with Escape and outside-click dismissal.
- Preserved legacy shortcode behavior, click analytics schema, privacy consent handling, and new-tab WhatsApp handoff.
- Existing contact free-text roles are retained in storage but need reassignment to a standardized role to participate in purpose routing.


- Refined Analytics content width and table sizing to use the available admin workspace more consistently.
- Hardened responsive sizing for analytics panels, tabular data, and management controls.
- Kept the existing KPI/filter layout, two-column By Device/By Contact, full-width By Campaign, two-column management actions, and separated Delete All panel.
- No analytics queries, retention behavior, export/delete handlers, contacts, or routing logic changed.
- Visual review and responsive browser testing are still required on staging.


## Unreleased — Analytics Management

- Added aggregate CSV export for supported reporting periods (7, 30, 90 and 180 days).
- Added administrator-only analytics event deletion by inclusive site-local date range and Delete All with explicit typed confirmation.
- Added the non-identifying `contact_reference` field for new analytics events so future deleted-contact attribution can display `Removed contact #ID` without retaining contact names or phone numbers.
- Updated contact attribution reporting and exports to show active contact names, removed-contact references, or `Unknown / removed` when historical attribution is unavailable.
- Added a Default badge to the configured contact in Contact Management.
- Bumped the database schema version to 1.2.0; dbDelta adds the nullable reference field and index without backfilling unknown historical identity.

## 0.1.8 — Analytics visual refresh
- Refined analytics overview, KPI cards, reporting filter, report tables, and data-management presentation.
- Added responsive styling scoped to Analytics; no analytics data behavior was intentionally changed.
- Added analytics dashboard design and staging verification notes.

- Bumped plugin patch version to 0.1.9 for staging visual refresh and admin stylesheet cache invalidation.


## 0.1.10 — Contact identity indicators
- Moved the Default indicator above the contact name and restyled it as green text without a pill, box, or background.
- Preserved removed-contact analytics labels as `Removed contact #ID` whenever a non-identifying contact reference exists.
- Clarified that events whose contact identity was already unavailable remain `Unknown / removed`; their original ID cannot be reconstructed safely.
- Bumped the plugin patch version to invalidate cached admin styling.


## 0.1.11 — Staging candidate
- Added confirmed, nonce-protected `Set as Default` action for active contacts.
- Updated routing default selection; former default remains active and analytics history is unchanged.
- Blocked deletion of the current Default until replacement is selected.
- Presented analytics data-management tools in two desktop columns; Delete All remains a separate full-width section; mobile stacks.
- Updated architecture and staging test documentation.
- Not release-approved; routing, concurrency and regression tests remain required.

## 0.1.13
- Expanded the Analytics summary row so WhatsApp Clicks, Retention, and Reporting Period each occupy an equal-width column across the available dashboard width.
- Preserved stacked single-column behavior on narrow/mobile layouts.

## 0.1.14
- Restructured Analytics summary markup so WhatsApp Clicks, Retention, and Reporting Period are direct grid children; each now occupies one equal-width column across the available dashboard width.
- Removed reliance on `display: contents` for summary layout; retained mobile single-column breakpoint.

## 0.1.15
- Fixed narrow-screen Analytics data cards to stack By Device, By Contact, and By Campaign into one full-width column.
- Improved mobile reporting-period filter layout and constrained analytics table cell wrapping.

## 0.1.16
- Explicitly stretch the Analytics KPI summary row to the full available content width and remove inherited max-width constraints; preserve 3-column desktop and 1-column mobile behavior.


## 0.1.20 — Core architecture and settings workspace

- Reframed visitor-facing purpose labels for reusable, non-industry-specific deployments.
- Added compact Setup / Routing / Extensions settings tabs with keyboard navigation and responsive styling.
- Added an Extensions admin panel surface through `hatnch_admin_extension_panels`.
- Added purpose registry and role-route extension filters for optional site-specific modules.
- Changed default purpose message language to refer generically to the website team.
- No database schema change. Existing global routing remains the fallback when purpose routing is disabled or a purpose has no eligible contact.
- Technical support form, entitlement verification, and full extension module implementation remain future work; this release does not claim those features.


## 0.1.21 — Admin workspace and privacy UX refinement

- Added consistent navigation links for General, Contacts and Analytics workspace screens.
- Preserved the active Setup, Routing or Extensions tab across settings save using an allow-listed tab key.
- Tightened privacy notice spacing and shortened the analytics explanation.
- Layered the circular privacy control partially behind the WhatsApp action while retaining its own button semantics.
- Existing admin screen slugs, consent semantics, analytics behavior and routing logic are intended to remain unchanged.
- Static checks only; see `docs/TEST-REPORT-0.1.21.md` for verification scope and staging checks.


## 0.1.22 — Support page and final privacy/navigation polish

- Changed General, Contacts and Analytics workspace navigation from a pill container to plain text navigation with a restrained active underline.
- Replaced the oversized white privacy disc with a compact shield icon control and subtle icon shadow, overlapping the WhatsApp action.
- Reduced privacy explanation length and brought Privacy Policy and Read More closer together.
- Added an optional Support Page URL setting; the chooser links to that page for complex matters, with support email as fallback.
- Support URLs are restricted to HTTP/HTTPS and output-escaped.
- Static verification only; manual WordPress/browser tests remain required.
