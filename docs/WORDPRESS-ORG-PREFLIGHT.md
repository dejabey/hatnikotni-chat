# WordPress.org Preflight — Hatnikotni Chat

## Current state
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Latest validation: Run #190 — success
- Latest commit: c1c17734250be276355241e2b3788181d405c568
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
1. Finalize four WordPress.org screenshot assets matching readme entries.
2. Place screenshots in the top-level WordPress.org SVN assets directory.
3. Create/publish versioned stable tag 0.1.0.
4. Run official readme validator against final readme.
5. Submit complete stable package.
6. Await WordPress.org review.

## Production gate
Production remains untouched. Do not migrate until the stable release is finalized and the user explicitly approves production migration.

## UI
Admin UI is frozen after staging verification. Do not redesign unless a concrete defect is discovered.
