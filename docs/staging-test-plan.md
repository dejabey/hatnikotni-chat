# Staging Test Plan — Hatnikotni Chat

## Purpose
Validate the release candidate on a real WordPress staging site before production migration.

## Test environment
- WordPress compatible with production
- PHP production version
- HTTPS enabled
- Production-equivalent cache/CDN
- WooCommerce enabled if production uses it
- Consent mechanism enabled if production uses one

## Installation and lifecycle
- [ ] Fresh installation
- [ ] Activation
- [ ] Database tables created
- [ ] Default settings created
- [ ] Deactivation
- [ ] Reactivation preserves data
- [ ] Upgrade preserves data
- [ ] Uninstall removes plugin-owned data only

## Contacts
- [x] Create/edit contact
- [x] Phone normalized to digits only
- [ ] Invalid phone rejected
- [ ] Activate/deactivate
- [ ] Historical inactive contacts remain usable in analytics
- [ ] No delete UI

## Routing
- [x] Direct selects active default contact
- [ ] Invalid/inactive default fails safely
- [ ] Random selects only active contacts
- [x] Sequential round-robin alternation observed with two active contacts (staging Ujian 3.19: PakYa → PakSu → PakYa)
- [x] Round-robin state persisted across reload in earlier staging test
- [ ] Inactive contacts are skipped (observed route to active default; skip behavior not isolated conclusively)
- [ ] Concurrent requests produce valid routing

### Purpose-based routing (staging observations)
- [x] Purpose chooser enabled; configured mappings verified during Ujian 3.19
- [x] Role-targeted routing: General Enquiries → PakYa; Customer Support → PakSu; Technical Support → PakYa (tests 3.6–3.10)
- [x] No-match fallback: Sales & Partnerships routed to active default PakYa (test 3.8)
- [x] Role-unassigned fallback to global Round robin observed: repeated General Enquiries yielded PakYa (`601172715154`) → PakSu (`601155898464`) → PakYa (`601172715154`) (Ujian 3.19)
- [x] Contact and routing settings restored/verified against target baseline before Ujian 3.19
- [ ] Concurrency/parallel routing not verified; sequential alternation is not proof of race safety

## WhatsApp action
- [x] Global button works
- [ ] Shortcode works
- [ ] Both use the same routing/action layer
- [ ] Default/custom message works
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
- [ ] Shortcode rendering
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
- [ ] Nonce/capability checks enforced
- [ ] Inputs sanitized and outputs escaped
- [ ] External redirect constrained to intended wa.me destination
- [ ] No secrets/API keys
- [ ] No unexpected external HTTP requests

## Admin UI refinement
- [ ] Modernize Hatnikotni Chat submenu layout
- [ ] Add concise placeholders/help text to precision-sensitive fields
- [ ] Improve grouping and visual hierarchy without adding unnecessary JS/dependencies
- [ ] Preserve WordPress admin accessibility and responsive behavior

## Migration rehearsal
- [ ] Keep current WP Chat App intact
- [ ] Activate Hatnikotni Chat on staging
- [ ] Verify contacts/routing
- [ ] Disable WP Chat App
- [ ] Re-test all critical paths
- [ ] Re-enable WP Chat App and verify rollback

## Release gate
Release candidate proceeds only after critical tests pass, CI is green, privacy/cache/routing behavior is verified, WordPress.org preflight is complete, and a clean ZIP has been inspected.

Production activation remains a separate approval step.

## Staging test log — Ujian 3.19
- Environment: `staging.perlis.xyz` only; production untouched.
- Baseline contacts verified: PakYa (ID 1, active, default, role `unassigned`); PakSu (ID 3, active, not default, role `unassigned`); znz (ID 5, inactive).
- Routing configuration verified: method `Round robin`; Purpose chooser enabled; mappings unchanged from the test plan.
- Three consecutive `General Enquiries` routing outcomes: PakYa → PakSu → PakYa.
- Result: **PASS** for observed sequential global fallback behavior.
- Scope: functional sequential observation only; does not establish concurrent correctness, exhaustive error handling, or production readiness.
