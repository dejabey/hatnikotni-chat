# Hatnikotni Chat 0.1.15 — Staging Handoff

## Scope
Targeted CSS-only responsive correction based on the supplied 302px mobile screenshot. Earlier desktop refinement rules reset `.hatnch-data-grid` to two columns after the earlier mobile media query, so the mobile single-column rule was overridden by source order. This build adds a final narrow-screen override.

## Changes
- At <=782px, analytics device/contact/campaign panels stack as one full-width column.
- Reporting period control stacks label, selector and Apply button; controls fill available width.
- Analytics table cells wrap content and use compact mobile padding.
- PHP/analytics logic, reporting query, retention, contact data, routing and deletion handlers unchanged.

## Checks
- Package integrity and PHP syntax should be checked before installation.
- Visual QA remains required on staging: desktop plus narrow mobile. No browser-rendered test was performed for this candidate.

## Staging validation
1. Back up staging plugin and retain previous ZIP.
2. Install 0.1.15 on staging only; hard-refresh Analytics.
3. At ~302px, verify each analytics section is one full-width column.
4. Verify reporting-period control is readable and aligned.
5. Verify no horizontal overflow and desktop remains unchanged.
6. Do not use destructive actions with real records for layout testing.

## Rollback
Restore the prior staging plugin package if the UI or function regresses.

## Release gate
Not release-ready. Pending visual QA, WordPress Plugin Check/PHPCS and repository synchronization.
