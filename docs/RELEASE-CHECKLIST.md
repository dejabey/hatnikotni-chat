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
- [ ] Desktop browser verification
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
- [ ] Final artifact hash recorded
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