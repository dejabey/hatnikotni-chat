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
