# Hatnikotni Chat 0.1.24 — Handoff

## Baseline
Built from the supplied `hatnikotni-chat-0.1.23-mobile-ux-refinement-candidate.zip` (plugin header and constant both reported 0.1.23). This handoff supersedes the incorrectly based 0.1.19 table-layout review artifact.

## Changes
- Removed the Contacts table's explanatory scroll-hint paragraph and obsolete CSS that hid the hint on desktop but re-enabled it on small screens. The table remains wider than narrow viewports and its wrapper remains horizontally scrollable.
- Analytics report tables have two columns. Their wrapper is constrained against horizontal scrolling, table layout is fixed to available width, and cells can wrap long values.
- Version metadata and changelog updated to 0.1.24.

## Intentionally unchanged
- No database schema or stored option changes.
- No routing/role/purpose mapping, consent, privacy collection, contact CRUD, or analytics logic changes.
- Existing workspace nav and admin page registrations remain in place.

## Verification performed
- Source baseline archive existed and extracted successfully.
- Confirmed plugin header and `HATNCH_VERSION` were 0.1.23 before modification.
- Confirmed Contact list has a horizontally scrollable wrapper and a deliberately wider table, while Analytics tables render only two data columns.
- Static checks and archive checks are recorded in the accompanying release report.

## Manual staging checks still required
1. Open Contacts at desktop and mobile widths; verify full table remains reachable by horizontal scroll, including keyboard focus.
2. Open Analytics on desktop and mobile; check all labels/values wrap without clipped text or horizontal scrollbar. Test long attribution labels and contact names.
3. Verify direct access and workspace navigation for General, Contacts, and Analytics.
4. Confirm translations and front-end purpose chooser on WordPress locales actually used by the site.
5. Regression test existing contact status/default changes, routing methods, and consent analytics.

This is an installable review candidate, not a claim of staging validation or production readiness.
