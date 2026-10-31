# Feature Contract Gap — Routing and Consent UX
Date: 2026-10-31
Review scope: Direct inspection of source files on `migration-hardening`, comparison with v0.1.24 candidate findings. Not runtime certification.

## Routing contract discrepancy
The reviewed PR-chain routing class `HATNCH_Routing` resolves only:
- direct via one configured default contact;
- random across active contacts;
- round-robin across active contacts using one global last-contact option.

The candidate v0.1.24 resolver previously inspected supports purpose/role-aware resolution, active contact filtering, configured allow-listed purpose/role mapping, role-scoped round-robin state, and fallback behavior.

Thus, the current PR-chain resolver is materially narrower than the candidate contract. This is not merely a missing file concern. Do not describe the PR chain as functionally equivalent until the role/purpose feature is either restored with tests or explicitly removed by a product decision. Existing tests must assert contact fairness, role isolation, invalid mappings, no eligible active contact, default fallback, and migration of relevant settings/state.

## Consent UI code review notes
The inspected privacy JS:
- sets the consent choice cookie with Path=/, SameSite=Lax and Secure on HTTPS;
- invokes campaign capture when consent changes to yes;
- clears a browser-visible campaign cookie and sends a same-origin POST for server-side clearing on reject;
- closes the panel with Escape and restores focus to its toggle.

The server campaign cookie uses configured WordPress COOKIEPATH and COOKIE_DOMAIN. The JS visible-cookie clearing uses configurable privacy config, but the consent-choice cookie itself is Path=/ and does not visibly apply a Domain attribute. Verify multisite/subdirectory/multiple installation and stale-cookie behavior.
- Campaign capture sends supported UTM values as query parameters to the configured endpoint using GET with cache:no-store and same-origin credentials. These values can appear in server/proxy request logs; policy/log minimization review is needed even though event storage is consent-gated.
- The public revoke endpoint clears only the visitor's own attribution cookie; check CSRF/privacy threat model and cache controls.
- UI status message lifecycle and its spacing were not visually assessed.

## Disposition
- PR-chain direct/random/round-robin resolver: **CONFIRMED in inspected file**
- Purpose/role routing equivalent in that resolver: **NOT PRESENT in inspected implementation**
- Candidate role/purpose routing support: **CONFIRMED in previously inspected candidate source**
- Product requirement disposition for purpose routing: **PENDING**
- Consent browser UX and cookie-scope runtime tests: **PENDING**
- Privacy/logging and endpoint threat model: **PENDING**
- No PR or source code modified; only audit documentation added.
- Production: **NOT AUTHORIZED**

## Required next decisions/work
1. Restore the purpose/role contract to consolidated baseline if still required by product intent, keeping route allow-list and safe fallback.
2. Design deterministic and role-isolated round-robin tests.
3. Align consent cookie path/domain behavior and revocation behavior for subdirectory/multisite installations.
4. Avoid query-string exposure of attribution where feasible; evaluate POST/body transport and verify server handling, request size, cache, and nonce/CSRF rationale. Do not change until contract and tests are established.
5. Test message UX and consent lifecycle visually and functionally on staging.
