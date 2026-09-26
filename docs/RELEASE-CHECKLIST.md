# Hatnikotni Chat Release Checklist

## Release candidate / stable preparation
- [x] Runtime-only package structure
- [x] GPLv2-or-later declaration
- [x] Final readme metadata
- [x] uninstall.php
- [x] No development tests/vendor files in production ZIP
- [x] GitHub Actions validation
- [x] Final WordPress.org readme metadata
- [ ] Final WordPress.org assets/screenshots
- [ ] Versioned stable Git/SVN tag 0.1.0

## Staging
- [x] All staging validation gates reported passed by user

## Production
- [ ] Full backup
- [ ] Install stable package
- [ ] Configure production contact
- [ ] Verify frontend and WhatsApp redirect
- [ ] Verify consent/analytics
- [ ] Disable old WP Chat App
- [ ] Monitor after activation
- [ ] Keep rollback package available

## Latest validation
- Run #190 passed.
- Commit: c1c17734250be276355241e2b3788181d405c568.
- Branch: release/0.1.0-rc1.
- Plugin version: 0.1.0.
- WordPress.org contributor: zaryl.
- Tested up to: 7.1.
- Stable tag: 0.1.0.
- Production remains untouched.

## Next
1. Finalize WordPress.org screenshot assets.
2. Create/publish stable tag 0.1.0.
3. Submit complete stable package to WordPress.org.
4. Await review/approval.
5. Only after explicit production approval, perform production migration.
