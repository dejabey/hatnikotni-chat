# Changelog

## 0.1.3 — Unreleased

- Refined the native privacy panel with shorter copy, tighter spacing, smaller controls and responsive sizing.
- Updated the privacy panel's introductory and expandable copy for easier scanning.
- Added a staging check for compact layout and WhatsApp-button overlap.

## 0.1.2 — Previous development build

- Added a native, expandable Privacy choices panel beside the floating WhatsApp button.
- Added explicit Allow analytics and Reject analytics actions with a 180-day first-party preference cookie.
- Kept WhatsApp navigation independent from analytics consent.
- Added a short data-use explanation and link to the site's WordPress Privacy Policy page when configured.
- Updated privacy policy copy, readme, source contracts and clean package workflow.

## 0.1.1 — WordPress.org review remediation

- Migrated global declarations, hooks, admin page slugs, storage keys, table names, cookies and CSS handles/classes to the unique hatnch prefix.
- Added migration for legacy settings, routing state and custom tables.
- Enforced strict international digits-only phone input at the server validation layer.
- Confirmed PHP syntax, contract checks and WordPress Coding Standards passed in GitHub Actions.
- Added consent-aware visitor analytics and campaign attribution with default-deny behavior.
- Added WordPress Privacy Policy Guide integration.
- Added admin UI refinement, analytics reporting and 180-day event retention.
