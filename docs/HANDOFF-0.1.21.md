# Hatnikotni Chat 0.1.21 — Handoff

## Candidate purpose

Consolidate navigation presentation and apply the approved privacy UX refinements without changing the contact routing model.

## Changes

- Workspace nav links: General, Contacts, Analytics. Existing admin page slugs are retained.
- Settings saves now redirect to the allow-listed active tab (Setup, Routing, Extensions).
- Privacy notice links and explanation are more compact.
- Privacy shield button is layered partly behind the main WhatsApp action.

## Install / staging

1. Take a fresh staging backup.
2. Install/replace the plugin with this candidate through WordPress Plugins.
3. Open Hatnikotni Chat → General, Contacts and Analytics.
4. Run the acceptance list in `TEST-REPORT-0.1.21.md`.
5. Keep purpose routing in its current state during initial UI regression.
6. Do not deploy to production until staging checks pass.

## Rollback

Deactivate 0.1.21 and reinstall the previously installed 0.1.20 package. No database schema change is intended in this candidate. Preserve a backup regardless.

## Release status

Candidate only. Static syntax/integrity checks do not establish WordPress runtime, browser layout, accessibility, or routing acceptance.
