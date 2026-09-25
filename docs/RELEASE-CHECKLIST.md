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
- [ ] Actual consent-manager integration
- [x] Desktop browser verification
- [ ] Mobile browser verification
- [ ] Cache behavior verification
- [ ] Accessibility runtime verification
- [ ] High-concurrency round-robin stress test
- [ ] Remove temporary test contacts

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
- Run #179 passed.
- Commit: `6f4c9229d952b447052c9cb24c42be49edd32866`.
- Branch: `release/0.1.0-rc1`.
- Latest Actions artifact: `hatnikotni-chat-0.1.0-rc1`.
- Artifact ID: `10856710948`.
- Artifact size: 22,508 bytes.
- Artifact SHA-256: `667eb55904731bae7cb0092bd1354a96396b56b3eae6c235c4109daa964b2404`.
- Artifact expires: 2026-12-24.
- Desktop staging visual verification completed; General, Contacts and Analytics are visually acceptable and UI is frozen.
- WPVibe runtime quota is currently exhausted, so remaining runtime gates must not be marked complete until independently tested.
