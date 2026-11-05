# Handoff — Hatnikotni Chat 0.1.20

## Purpose
Move the plugin presentation and extension architecture toward a reusable public core, keeping Jelajah Perlis-specific functionality in a future companion module.

## Changes in this candidate
- Version updated to 0.1.20; database schema remains 1.2.0.
- Added compact Setup / Routing / Extensions tabs on the main settings page.
- Added keyboard navigation (arrow keys, Home, End), selected state and responsive layout.
- Added Extensions panel hook `hatnch_admin_extension_panels`.
- Added purpose registry hook `hatnch_purpose_definitions`.
- Added route mapping hook `hatnch_purpose_role_routes`.
- Added permitted role hook `hatnch_allowed_contact_roles`.
- Reworded built-in purpose labels and default WhatsApp copy for broader use.
- Documented extension boundaries and security responsibilities.

## Preserved
- Existing contact directory and management operations.
- Global Direct / Random / Round Robin selection.
- Purpose routing fallback to global routing when no eligible role contact is available.
- Existing consent-aware analytics architecture.
- Existing shortcodes and contact action endpoint.

## Explicitly not delivered
- Native multi-step technical-support form.
- Package entitlement verification or WooCommerce integration.
- Automatic customer identity validation.
- Module installer/manager.
- New purposes with fully configurable forms out of the box.
- Live WordPress/browser acceptance test, WordPress Plugin Check, or WordPress.org review.

## Next engineering gate
1. Install only on staging after backup.
2. Validate Settings tabs in desktop and mobile; keyboard navigation; no-JS behavior.
3. Re-run global and purpose routing matrix.
4. Verify contact roles and routing fallback with active/inactive contacts.
5. Validate extension filter contracts in a small companion test module.
6. Design generic purpose/form schema and multi-step support UI as a separate change.
7. Only then start the Jelajah Perlis adapter and entitlement audit.

## Release state
Candidate only. Do not promote to production. No GitHub branch/commit/PR or staging deployment was performed.
