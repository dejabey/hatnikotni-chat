# Hatnikotni Chat Release Checklist

## Release candidate / stable preparation
- [x] Runtime-only package structure
- [x] GPLv2-or-later declaration
- [x] Final readme metadata
- [x] uninstall.php
- [x] No development tests/vendor files in production ZIP
- [x] GitHub Actions validation
- [x] Final WordPress.org readme metadata
- [x] Final WordPress.org screenshot set prepared
- [x] Versioned stable Git tag 0.1.0

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
- Run #194 passed.
- Release commit: 3120a5e69d102a43713435b3dcf689d66decbfda.
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
