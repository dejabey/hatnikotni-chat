# Staging Test Plan — Hatnikotni Chat

## Purpose
Validate the release candidate on a real WordPress staging site before production migration.

## Test environment
- Site: https://staging.perlis.xyz
- WordPress: 7.1.2
- PHP: 8.5.10
- HTTPS enabled
- WooCommerce: 11.1.2
- Active theme: UrbanGo Child 1.0.1
- Hatnikotni Chat: 0.1.0
- WPVibe plugin: 1.18.0
- WPVibe runtime quota is currently exhausted.

## Installation and lifecycle
- [x] Activation
- [x] Database tables created
- [x] Default settings created
- [ ] Fresh installation
- [ ] Deactivation
- [ ] Reactivation preserves data
- [ ] Upgrade preserves data
- [ ] Uninstall removes plugin-owned data only

## Contacts
- [x] Create/edit contact
- [x] International phone format stored correctly
- [ ] Invalid formatted phone rejected in runtime
- [ ] Activate/deactivate
- [ ] Historical inactive contacts remain usable in analytics
- [x] No delete UI

## Routing
- [x] Direct selects active default contact
- [ ] Invalid/inactive default fails safely
- [x] Random selects only active contacts
- [x] Round-robin follows sort order and wraps correctly
- [ ] Inactive contacts are skipped
- [ ] Round-robin state persists across all lifecycle scenarios
- [ ] Concurrent requests produce valid routing

## WhatsApp action
- [x] Global button works
- [x] Shortcode renders
- [x] Both use the same routing/action layer
- [ ] Default/custom message runtime verification
- [x] Destination is HTTPS wa.me with normalized phone
- [x] Analytics failure never blocks redirect
- [x] No frontend JS required for core action
- [ ] Failure fallback is safe

## Consent and privacy
### Without consent
- [x] WhatsApp works
- [x] No analytics event
- [ ] No campaign cookie retained
- [ ] Existing campaign cookie cleared where possible

### With consent
- [ ] Click event recorded
- [ ] Contact/page/device fields correct
- [ ] Supported UTM fields captured
- [ ] Campaign cookie created
- [ ] New UTM visit replaces last-touch attribution

### Consent timing
- [ ] Consent before page load
- [ ] Consent after page load with UTM parameters
- [ ] Consent withdrawal
- [ ] Subsequent click follows new consent state

## Analytics
- [ ] Event is whatsapp_click (click, not proof of message sent)
- [ ] Device classification works
- [ ] No IP/full user-agent/visitor identity/fingerprint/visitor ID stored
- [ ] Admin totals match event counts
- [ ] Device/contact/campaign reports are consistent
- [ ] 7/30/90/180-day filters work
- [ ] 180-day cleanup works
- [ ] Daily WP-Cron cleanup works

## Frontend
- [ ] Enable/disable setting
- [ ] Desktop/mobile visibility
- [ ] Left/right position
- [x] Shortcode rendering
- [ ] Keyboard focus
- [ ] Accessible label
- [ ] Reduced motion
- [ ] Mobile layout
- [ ] No unnecessary external requests

## Cache/CDN
- [ ] Page cache enabled
- [ ] CDN enabled if production uses one
- [ ] Routing remains dynamic
- [ ] Analytics action is not cached
- [ ] Settings changes reflect after cache purge
- [ ] No personalized data in cached HTML

## WooCommerce
- [ ] Shop/product pages
- [ ] Cart/checkout
- [ ] Button does not interfere with WooCommerce UI
- [ ] Analytics attribution remains correct
- [ ] Logged-in/logged-out tests

## Performance and security
- [ ] No PHP notices/warnings/fatals
- [ ] No repeated unnecessary schema work
- [x] Nonce/capability checks enforced
- [x] Inputs sanitized and outputs escaped
- [x] External redirect constrained to intended wa.me destination
- [x] No secrets/API keys
- [ ] No unexpected external HTTP requests

## Admin UI
- [x] Modernize Hatnikotni Chat submenu layout
- [x] Add concise placeholders/help text to precision-sensitive fields
- [x] Improve grouping and visual hierarchy without adding unnecessary JS/dependencies
- [x] Preserve WordPress admin accessibility and responsive behavior at source level
- [x] Desktop visual verification completed for General, Contacts and Analytics
- [x] UI frozen after screenshot review

## Migration rehearsal
- [x] Keep current WP Chat App intact
- [x] Hatnikotni Chat active on staging
- [x] Verify contacts/routing
- [ ] Disable WP Chat App
- [ ] Re-test all critical paths after disabling old plugin
- [ ] Re-enable WP Chat App and verify rollback

## Release gate
Release candidate proceeds only after critical tests pass, CI is green, privacy/cache/routing behavior is verified, WordPress.org preflight is complete, and a clean ZIP has been inspected.

Production activation remains a separate approval step.

## Known staging data
- Original production-style test contact: id 1, PakYa, phone 601155898464.
- Temporary test contacts remain:
  - id 2 — HKC Test A — 601100000001
  - id 3 — HKC Test B — 601100000002
- Do not delete them by inference; remove them only through an authorized/destructive staging action.

## Verified runtime results
- Global floating button rendered correctly.
- Public action endpoint redirected to WhatsApp with phone 601155898464.
- Shortcode test page rendered the expected hkc-inline button and was moved to Trash after testing.
- No-consent analytics produced zero event rows.
- Random routing exercised across three active contacts.
- Round-robin produced the expected sequential cycle PakYa → HKC Test A → HKC Test B → repeat.
- Routing was restored to direct after the routing tests.
- Desktop admin screenshots for General, Contacts and Analytics were reviewed and accepted; UI is frozen.
- Lighthouse mobile performance/accessibility measurements were collected, but those results are site-wide/UrbanGo observations and are not treated as Hatnikotni Chat defects.

## Remaining runtime gates
These are intentionally not marked complete:
1. Actual consent signal from the site's real consent mechanism.
2. Mobile/responsive browser verification.
3. Cache/CDN behavior.
4. Accessibility runtime verification.
5. High-concurrency round-robin stress.
6. Temporary contact cleanup.
7. Full lifecycle tests and final migration rehearsal.
