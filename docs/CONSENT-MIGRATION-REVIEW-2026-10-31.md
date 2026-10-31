# Hatnikotni Chat — Consent and Migration Review Note
Date: 2026-10-31
Repository review scope: code on `migration-hardening` and selected changes in its parent PR lineage. This note is not a runtime certification.

## Consent/campaign code observations
The `migration-hardening` version of the privacy controller:
- Reads an explicit analytics consent cookie.
- Writes consent as yes/no with a bounded age and SameSite=Lax.
- Calls campaign capture after the consent switch is enabled.
- Clears the campaign cookie and calls a revocation endpoint when consent is rejected.
- Keeps WhatsApp navigation independent of analytics consent.

The campaign handler:
- Gates campaign attribution processing on `HATNCH_Privacy::has_analytics_consent()`.
- Accepts only the documented UTM fields, sanitizes and length-bounds them.
- Limits encoded cookie size and uses an HttpOnly campaign cookie.
- Provides public authenticated/unauthenticated admin-post routes for visitor-owned capture/revoke operations.

Initial code review found the consent gate in the capture handler and capture invocation after affirmative consent. No immediate evidence in the inspected path showed campaign persistence before consent. This is a scoped source finding, not end-to-end privacy approval.

## Remaining privacy tests
- First visit with UTM, no prior choice: verify no campaign cookie and no analytics event.
- Reject: verify no attribution persisted, existing campaign cookie cleared, revoke request handled.
- Accept: verify bounded attribution capture and expected event fields.
- Withdraw after acceptance: verify cookie deletion and server-side revocation.
- Malformed, repeated, oversized, Unicode and adversarial UTM values.
- Consent cookie path/domain, HTTPS, subdirectory installs, caching/CDN and multiple widgets.
- Analytics/database failure must not block WhatsApp contact use.
- Confirm event retention and uninstall/delete behavior remain aligned with policy.

## Migration review
The migration hardening code preflights table collisions and option conflicts, verifies table renames and option copies, and pauses with an admin notice when it cannot establish safe migration. The integration test additions exercise baseline migration, schema repair, table/option collisions and an injected rename failure.
Still inspect/test:
- Every partial rename state and safe rerun.
- Interrupted option copy/cleanup.
- Realistic prior schema variants and unexpected/missing columns.
- Foreign references between event contact IDs and contacts IDs under every recovery route.
- DB permissions and errors on target hosting.
- Whether failure notice and recovery steps are actionable without exposing sensitive values.

## Review disposition
- Consent gate present in reviewed source path: **CONFIRMED**
- Capture only called on affirmative toggle in reviewed JS path: **CONFIRMED**
- Browser/runtime consent behavior: **PENDING**
- Revocation across cookie scopes and caching: **PENDING**
- Migration coverage added: **CONFIRMED**
- Migration safety across all live-state permutations: **PENDING**
- No source branch merged or modified in this review note.
- Production: **NOT AUTHORIZED**
