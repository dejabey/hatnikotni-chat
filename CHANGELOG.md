# Changelog

## 0.1.7 — Unreleased

- Set English as the source language for frontend privacy controls.
- Localized JavaScript consent status messages through PHP's translation system.
- Added Malay translation source and text-domain loading for bundled translations.
- Updated tests and package workflow to validate/include translation assets.

## 0.1.6 — Previous development build

- Fixed the fatal class-name typo in the WhatsApp click handler (`HATNCH_Analitik` → `HATNCH_Analytics`).
- Added a contract test to prevent regression of the analytics handler call.
- Reduced privacy panel width to 250px maximum and tightened typography/padding.
- Made the “Privasi” control quieter and renamed the disclosure to “Butiran”.

## 0.1.5 — Previous development build

- Localized visible consent-panel labels and status messages in Malay.
- Shortened the floating privacy control label to “Privasi”.
- Reduced panel width, padding and spacing while keeping the expanded explanation readable.

## 0.1.4 — Previous development build

- Replaced separate Allow/Reject buttons with an accessible, compact analytics consent switch.
- Added a Read more disclosure beside the Analytics heading.
- Initialize the switch from the saved consent cookie; switching either way updates the stored choice immediately.
- Kept analytics disabled by default and WhatsApp navigation independent of the choice.
- Updated contract tests and staging test plan for the new control.

## 0.1.3 — Previous development build

- Refined the native privacy panel with shorter copy, tighter spacing, smaller controls and responsive sizing.
- Updated the privacy panel's introductory and expandable copy for easier scanning.
- Added a staging check for compact layout and WhatsApp-button overlap.
- Updated the clean-package workflow to derive ZIP and artifact names from the plugin header version.

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
