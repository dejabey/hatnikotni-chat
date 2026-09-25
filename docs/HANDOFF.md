# Hatnikotni Chat — Handoff

## Purpose
This document is the continuation point for future ChatGPT sessions. It records the current architecture, repository state, staging status, verified tests, known limitations, and exact next steps so the project can continue without screenshots or manual reconstruction.

## Project identity
- Project: Hatnikotni Chat
- Plugin slug: hatnikotni-chat
- Text domain: hatnikotni-chat
- PHP prefix: HKC_
- CSS prefix: hkc-
- Version: 0.1.0
- Repository: dejabey/hatnikotni-chat
- Current branch: release/0.1.0-rc1
- Main site: https://perlis.xyz
- Staging: https://staging.perlis.xyz
- Production currently uses WP Chat App — Ninja Team. Hatnikotni Chat has NOT replaced it in production.
- Hatnikotni Chat must remain standalone and must not depend on Ninja GDPR, WooCommerce, UrbanGo, WPVibe, or any AI/chatbot plugin.

## Development/release model
Development → GitHub → review → release candidate → staging → full validation → stable release → production.
Production deployment is manual. Do not automatically replace the production WhatsApp plugin.
GitHub is source control/release management only; it is not a runtime dependency.

## Current source state
Latest RC commit:
- SHA: 5420c956b7a1f2dd5e6c5c791ce916bca14d85bf
- Message: docs: update RC staging verification
- CI run: #179
- CI result: success

Latest Actions artifact:
- Name: hatnikotni-chat-0.1.0-rc1
- Artifact ID: 10857640335
- Size: 22,508 bytes
- SHA-256: bdbf0476374ec9c7d767a6f7a4d072eb6f8714c33e43e9c5fcf7c53299f06b07
- Expires: 2026-12-24

Run #183 is the latest confirmed green validation run. Earlier run #176 and its artifact are superseded as the latest RC reference.

## V1 scope
1. Multi-contact management and routing
2. WhatsApp interaction analytics
3. UTM campaign attribution
4. Webhook foundation
5. WooCommerce integration foundation

Core frontend:
- Global floating WhatsApp button
- Shortcode: [hatnikotni_chat]
- Both use the same routing/action layer
- Core action requires no frontend JavaScript
- Inline SVG is used instead of an icon library
- Accessibility attributes and reduced-motion support are implemented at source level

## General settings
- Enable
- Default Contact
- Default Message
- Button Label
- Button Position: left/right
- Desktop visibility
- Mobile visibility
- Routing Method: direct/random/round_robin
WhatsApp phone belongs to the Contact entity, not General settings.

## Contacts
Contact fields:
- id
- name
- phone
- role
- description
- status
- weight
- sort_order
- created_at
- updated_at

Phone contract:
- International digits only
- 8–20 digits
- Example: 601155898464
- No +, spaces or hyphens
- Invalid formatted input must be rejected; do not silently strip formatting

Historical contacts should normally be deactivated, not deleted.
Analytics references contact IDs rather than names.

## Routing
Supported V1 methods:
- direct
- random
- round_robin
Weighted routing is reserved for a future version.
Round-robin uses active contacts ordered by sort_order then id and stores state in the WordPress option hkc_routing_state.
Sequential round-robin testing passed:
PakYa → HKC Test A → HKC Test B → repeat.
Concurrency remains an open test because option read/update is not an atomic transaction.

## Analytics
Primary event:
- whatsapp_click
Meaning:
A visitor clicked the WhatsApp button. It does NOT prove that a WhatsApp message was sent.

Event fields:
- id
- event_type
- created_at
- contact_id
- page_id
- page_type
- device
- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

Intentionally not stored:
- IP
- visitor name
- visitor phone
- visitor email
- full user-agent
- fingerprint
- visitor ID
- browsing history
- WhatsApp conversation content

Device categories:
- mobile
- tablet
- desktop
Retention:
- 180 days
- daily WP-Cron cleanup

## Privacy/consent
Analytics is consent-aware and opt-in.
Filter:
- hkc_has_analytics_consent
Default:
- false
Hatnikotni Chat does not provide its own consent banner and must not become dependent on a specific consent plugin.

Without consent:
- WhatsApp still works
- no analytics event
- campaign attribution is not retained
- existing campaign cookie is cleared where possible

With consent:
- analytics can record click data
- UTM attribution can be stored in the first-party hkc_campaign cookie for up to 30 days

Known unresolved integration point:
Consent becoming available after initial page load may require an explicit consent-granted integration/hook. This must be tested with the actual site's consent mechanism before production.

## Database
Tables:
- {$wpdb->prefix}hkc_contacts
- {$wpdb->prefix}hkc_events
DB version:
- 1.0.0
Schema uses WordPress dbDelta(), charset/collation from WordPress, and WordPress time functions.
No database foreign keys are used; validation is application-level.

## Admin UI
Top-level:
- General
- Contacts
- Analytics

Design direction is frozen:
Calm · Precise · Premium · Functional

Implementation:
- CSS-only admin design system
- Scoped hkc-admin styles
- No external fonts
- No external icon libraries
- No unnecessary JavaScript
- Wider 1320px maximum admin canvas
- Approximately 1180px working content width
- Analytics summary/filter top section and wider data grid
- Compact reporting-period panel
- Clear field placeholders/help
- WhatsApp interaction states use green rather than red
- Reduced-motion support

General, Contacts and Analytics desktop screenshots were reviewed after the updated RC was installed and accepted. Do not redesign the UI unless an actual defect is found.

## Staging environment
- WordPress 7.1.2
- PHP 8.5.10
- UrbanGo Child 1.0.1
- WooCommerce 11.1.2
- WPVibe 1.18.0
- Hatnikotni Chat 0.1.0 active
- WP_DEBUG true during staging work
WPVibe runtime quota is currently exhausted. Do not claim untested runtime gates are complete.

## Verified staging behavior
Verified:
- Plugin activation
- DB tables
- Default settings
- Global floating button
- WhatsApp action endpoint
- HTTPS wa.me redirect
- Correct phone 601155898464
- Shortcode rendering
- Direct routing
- Random routing
- Sequential round-robin routing
- No-consent analytics suppression
- Analytics failure does not block WhatsApp redirect
- No frontend JS required for core action
- WooCommerce present without detected plugin-level dependency
- Desktop admin visual verification
- UI freeze

Shortcode test page:
- Temporary page ID 6424 was used
- It was moved to Trash after testing
- Do not describe it as permanently deleted unless independently verified

Lighthouse mobile observation:
- Performance 58
- Accessibility 75
- Best Practices 92
- LCP 13.5s
- CLS 0.084
- TBT 20ms
These were treated as site-wide/UrbanGo observations, not evidence of a Hatnikotni Chat defect.

## Temporary staging contacts
Still present:
- id 2 — HKC Test A — 601100000001
- id 3 — HKC Test B — 601100000002
They were created solely for routing tests. They must be removed before final production migration.
Removal is a destructive staging action. Do not delete by inference if the tool requires explicit approval.

## Remaining gates
Not yet verified:
1. Actual consent-manager integration.
2. Consent timing before and after page load.
3. Campaign cookie behavior under consent/no-consent.
4. Mobile/responsive runtime verification.
5. Cache/CDN behavior.
6. Accessibility runtime verification.
7. High-concurrency round-robin stress.
8. Invalid phone runtime rejection.
9. Invalid/inactive default-contact failure behavior.
10. Inactive contact routing skip.
11. Full analytics report validation.
12. 7/30/90/180-day filters.
13. 180-day cleanup runtime.
14. Full lifecycle install/deactivate/reactivate/upgrade/uninstall.
15. Final migration rehearsal.
16. Temporary contact cleanup.
Do not mark these complete from source inspection alone.

## WordPress.org preflight
Already addressed:
- GPLv2 or later
- WordPress-native APIs
- no external analytics dependency
- opt-in analytics
- no third-party runtime JS/CSS
- scoped admin design
- privacy policy guide integration
- uninstall cleanup
- PHP 8.1–8.5 CI
- production-only RC package

Still required before stable WordPress.org submission:
- final readme validation
- final top-level SVN screenshot assets
- actual WordPress.org contributor usernames
- stable tag/versioned SVN tag
- complete final plugin package
- final staging gates
- final release review
Stable tag remains trunk until the actual stable release.

## Production migration plan
Do not change production yet.
When staging is fully accepted:
1. Take a fresh production backup.
2. Keep current WP Chat App intact.
3. Install/activate the stable Hatnikotni Chat package.
4. Configure production contact/settings.
5. Verify floating button and shortcode.
6. Verify WhatsApp redirect.
7. Verify real consent integration.
8. Verify analytics only after consent.
9. Disable WP Chat App.
10. Re-test critical frontend paths.
11. Monitor.
12. Keep the previous plugin/package available for rollback.

## Important implementation constraints
- Keep runtime lightweight.
- Do not add external services unless explicitly approved.
- Do not add tracking beyond the documented first-party analytics model.
- Do not store IP or visitor identity data.
- Do not add frontend JS unless necessary.
- Do not introduce a hard dependency on Ninja GDPR.
- Do not introduce a hard dependency on WooCommerce.
- Preserve WordPress capability checks, nonces, sanitization, escaping and safe redirects.
- Do not silently normalize invalid phone formats.
- Do not treat a WhatsApp click as a sent message.
- Keep documentation updated with every material release-state change.
- Do not redesign the already accepted admin UI without a concrete defect.

## Continuation instruction for a new chat
Start by reading this file and:
- docs/RELEASE-CHECKLIST.md
- docs/WORDPRESS-ORG-PREFLIGHT.md
- docs/staging-test-plan.md

Then verify the current GitHub branch/commit and CI state before making changes.

The immediate task is NOT new feature development. Finish the remaining staging gates, clean temporary staging contacts, inspect the final RC package, complete WordPress.org preflight, then prepare the stable release and production migration only after staging acceptance.

Never assume an unverified gate passed just because source code appears correct.
