# Hatnikotni Chat 0.1.22 — Staging handoff

## Changes
- Workspace nav is now plain General / Contacts / Analytics text navigation.
- Settings save retains active Setup/Routing/Extensions tab (carried forward).
- Privacy shield is compact, icon-only, and visually tucked under the WhatsApp action.
- Consent note is shorter and links are closer. Consent behavior is intended unchanged.
- Routing tab now includes Support Page URL alongside Support Email. The purpose chooser presents the page link when configured, otherwise email.

## Install and validate
1. Take a fresh staging backup.
2. Install this ZIP as a plugin replacement.
3. Check General, Contacts, Analytics workspace navigation.
4. Save settings on Setup, Routing and Extensions; confirm active tab remains.
5. Set a Support Page URL and confirm it appears as the alternative support action. Test blank URL with email fallback and both blank.
6. Test shield icon position/hit area, consent panel, focus and touch at desktop/mobile, left/right placements.
7. Run existing purpose routing and legacy shortcode checks.

## Rollback
Deactivate 0.1.22 and restore the prior candidate ZIP. No DB schema migration is intended.

## Release gate
Candidate only. PHP/JS syntax and ZIP checks do not prove WordPress runtime, screen-reader, or browser layout acceptance. No staging or production deployment was performed.
