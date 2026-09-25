# Hatnikotni Chat Release Checklist

## Development
- [x] Core feature scope frozen for V1
- [x] Source audited
- [x] WordPress Coding Standards CI
- [x] PHP syntax CI
- [x] PHP 8.1–8.5 CI matrix
- [x] Production ZIP workflow
- [x] Privacy Policy Guide integration
- [x] Consent-aware analytics
- [x] Strict WhatsApp phone validation
- [x] Modernized admin UI

## Staging
- [x] Plugin activation
- [x] Database tables created
- [x] Direct routing
- [x] Random routing
- [x] Round-robin routing
- [x] WhatsApp redirect
- [x] Shortcode rendering
- [x] No-consent analytics suppression
- [x] WooCommerce present with no detected plugin-level dependency
- [x] Actual consent-manager integration
- [x] Desktop browser verification
- [x] Mobile browser verification
- [x] Cache behavior verification
- [x] Accessibility runtime verification
- [x] High-concurrency round-robin stress test
- [x] Remove temporary test contacts

## Release candidate
- [x] Runtime-only package structure
- [x] GPLv2-or-later declaration
- [x] readme.txt
- [x] uninstall.php
- [x] No development tests/vendor files in production ZIP
- [x] GitHub Actions RC artifact digest recorded
- [ ] Final WordPress.org readme validation
- [ ] Final WordPress.org assets/screenshots
- [ ] Versioned stable SVN tag

## Production
- [ ] Full backup
- [ ] Install stable package
- [ ] Configure production contact
- [ ] Verify floating button
- [ ] Verify shortcode if used
- [ ] Verify WhatsApp redirect
- [ ] Verify consent integration
- [ ] Verify analytics only after consent
- [ ] Disable old WP Chat App
- [ ] Monitor after activation
- [ ] Keep rollback package available

## Admin UI refinement
- [x] Admin UI layout spacing and content-width refinement applied after staging screenshot review.
- [x] Re-run visual browser verification on General, Contacts and Analytics after the updated RC is installed.

## Latest RC validation
- Run #183 passed; subsequent documentation-sync runs also passed.
- Commit: `5420c956b7a1f2dd5e6c5c791ce916bca14d85bf`.
- Branch: `release/0.1.0-rc1`.
- Latest Actions artifact: `hatnikotni-chat-0.1.0-rc1`.
- Artifact ID: `10857640335`.
- Artifact size: 22,508 bytes.
- Artifact SHA-256: `bdbf0476374ec9c7d767a6f7a4d072eb6f8714c33e43e9c5fcf7c53299f06b07`.
- Artifact expires: 2026-12-24.
- Desktop staging visual verification completed; General, Contacts and Analytics are visually acceptable and UI is frozen.
- All staging runtime gates are reported passed by the user; no staging gate remains open.
