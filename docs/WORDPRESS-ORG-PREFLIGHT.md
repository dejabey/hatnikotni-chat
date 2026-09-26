# WordPress.org Preflight — Hatnikotni Chat

## Current state
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Latest validation: Run #194 — success
- Release commit: 3120a5e69d102a43713435b3dcf689d66decbfda
- Stable Git tag: 0.1.0
- Contributor: zaryl
- Tested up to: 7.1
- Stable tag: 0.1.0

## Completed
- GPLv2 or later
- WordPress-native APIs
- No external analytics service
- Consent-aware analytics
- No third-party runtime JS/CSS dependency
- Privacy Policy Guide integration
- Uninstall cleanup
- Runtime-only package
- Staging validation reported complete by user

## Remaining
1. Run official readme validator against final readme.
2. Submit complete stable package.
3. Await WordPress.org review.

The four screenshot files supplied for the readme are prepared. WordPress.org SVN asset placement happens with the directory release workflow after approval.

## Production gate
Production remains untouched. Do not migrate until the stable release is finalized and the user explicitly approves production migration.

## UI
Admin UI is frozen after staging verification. Do not redesign unless a concrete defect is discovered.
