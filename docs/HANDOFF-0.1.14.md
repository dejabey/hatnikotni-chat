# Handoff — Hatnikotni Chat 0.1.14

## Purpose
Fix the Analytics dashboard summary row. In staging 0.1.13, the three top panels remained confined to the left portion of the available row.

## Change
- Replaced the nested `.hatnch-stat-grid` wrapper with three direct children under `.hatnch-analytics-top`: WhatsApp Clicks card, Retention card, and Reporting Period panel.
- Configured the summary container as a three-column `minmax(0, 1fr)` CSS grid at desktop widths.
- Kept the single-column breakpoint at 782px and below.
- Removed reliance on `display: contents` for this row.
- No intended changes to analytics queries, event collection, retention, export/delete handlers, contacts, or WhatsApp routing.

## Build and checks
- Version: 0.1.14 (staging candidate)
- Package derived locally from the available 0.1.13 candidate; not pushed to GitHub.
- PHP lint: 13 PHP files, no syntax failures.
- ZIP integrity: passed (`testzip` returned no errors).
- SHA-256: `2bf92541ddf9378d524481a0e72d17a16893dbbad153cb085b57b9d0b725bf11`
- Not run: WordPress Plugin Check, PHPCS, browser visual QA, interactive functionality QA.

## Staging procedure
1. Back up current staging plugin/files and database as appropriate.
2. Install candidate 0.1.14 on staging only.
3. Reload Analytics with cache bypass/hard refresh; confirm displayed plugin version.
4. Desktop acceptance: KPI cards and reporting filter occupy three equal columns across the full available content row, aligned to the panels below.
5. Narrow/mobile acceptance: one column, no horizontal overflow.
6. Smoke test reporting period Apply and inspect all analytics sections.
7. Confirm destructive analytics controls and routing/contact data remain intact; do not perform irreversible deletion merely as a smoke test.
8. Record screenshot and result.

## Rollback
Restore the previous staging plugin build from the saved ZIP/backup if layout or function regresses.

## Release gate
Do not publish or treat as release-ready until visual acceptance, functional smoke tests, WordPress Plugin Check/PHPCS, and source-of-truth/GitHub reconciliation are completed.
