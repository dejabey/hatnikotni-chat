# Hatnikotni Chat — Handoff

## Current release state
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Latest release commit: 3120a5e69d102a43713435b3dcf689d66decbfda
- Latest CI: Run #194 — success
- Stable Git tag: 0.1.0 → 3120a5e69d102a43713435b3dcf689d66decbfda
- WordPress.org contributor: zaryl
- Tested up to: 7.1
- Stable tag in readme: 0.1.0
- All previously open staging gates are reported passed by the user.
- Production remains untouched.

## Remaining release work
1. Official readme validation.
4. WordPress.org submission/review.
5. Production migration only after explicit approval.

## Project constraints
- Standalone plugin; no hard dependency on Ninja GDPR, WooCommerce, UrbanGo, WPVibe or AI plugins.
- Keep runtime lightweight.
- No external analytics service.
- Analytics is consent-aware and first-party.
- Do not store IP or visitor identity data.
- Preserve capability checks, nonces, sanitization, escaping and safe redirects.
- Do not silently normalize invalid phone formats.
- Do not treat a WhatsApp click as proof a message was sent.
- Keep documentation updated.
- UI is frozen unless a concrete defect appears.

## Production migration
Do not change production yet. After stable release preparation and explicit approval: backup, install stable package, configure contact, verify button/shortcode/redirect/consent/analytics, disable WP Chat App, retest, monitor and retain rollback package.

## Continuation
Read this file, docs/RELEASE-CHECKLIST.md, docs/WORDPRESS-ORG-PREFLIGHT.md and docs/staging-test-plan.md before continuing. Verify current branch, tag and CI state first.
