# Hatnikotni Chat development history: 0.1.8–0.1.25

This record preserves version purposes recovered from project conversation history, but is NOT yet a canonical verified changelog. The supplied 0.1.25 archive's own CHANGELOG.md conflicts with some conversation-recovered labels: the archive labels 0.1.8 as an Analytics visual refresh and places Analytics Management under an Unreleased heading, while conversation notes associate 0.1.8 with Analytics Management and 0.1.9 with the visual refresh. Keep this discrepancy visible until original versioned artifacts or dated commits resolve it. Do not silently overwrite either source.

| Version | Development purpose |
|---|---|
| 0.1.8 | Analytics Management staging build; analytics export/delete, contact reference, Removed contact and Default badge; DB schema 1.2.0. Historical records also associate this version with stabilizing the ZIP root folder. Several build/staging contexts exist under this version number; do not assume they are the same artifact. |
| 0.1.9 | Analytics visual refresh; CSS/layout only, DB schema unchanged. |
| 0.1.10 | Contact indicator fix; Default badge moved above the contact name, green text without a box; Removed contact attribution handling. |
| 0.1.11 | Set as Default for active contacts with confirmation, Default protection, direct routing to new default; analytics responsive layout refinement. |
| 0.1.12 | Analytics layout refinements. |
| 0.1.13 | KPI width fix; three-column grid/display:contents. |
| 0.1.14 | KPI markup/grid fix; three direct items and repeat(3,minmax(0,1fr)). |
| 0.1.15 | Mobile correction; one-column mobile layout and vertical reporting controls. Mobile visual passed at that point; desktop remained unresolved. |
| 0.1.16 | KPI full-width desktop fix. |
| 0.1.17 | Default protection UI; hide Deactivate/Delete for Default. |
| 0.1.18 | WhatsApp destination opens a new tab with target and rel protections. |
| 0.1.19 | Purpose-based routing, role mapping, fallback, prefilled messages, documentation and staging candidate. |
| 0.1.20 | Generic public plugin core; extension hooks for Jelajah Perlis, modern tabbed admin, documentation/handoff. |
| 0.1.21 | Workspace navigation, tab persistence, compact privacy notice, shield overlap fix. |
| 0.1.22 | Plain-text workspace navigation, smaller dark shield, compact privacy UX, Support Email and Support Page URL, preserved tab persistence. |
| 0.1.23 | Mobile UX refinement candidate. Static checks passed; browser/staging validation was pending. Follow-up audit identified incomplete Malay translation/no compiled .mo, desktop consent-message cleanup, mobile purpose-panel behavior, CSS overlap and incomplete focus management. |
| 0.1.24 | Table-scroll refinement from 0.1.23; Contacts guidance cleaned, Analytics fixed. Candidate package static checks passed; runtime concurrency and staging validation remained pending. Global round-robin lock behavior was under investigation. |
| 0.1.25 | Round-robin concurrency candidate. Added MySQL advisory locks for global and purpose-role round-robin, separate routing-lane locks, fail-closed behavior on lock failure, and preserved cursor format; DB schema 1.2.0. Static checks passed, but concurrency was not verified. User confirms this package is installed on staging. |

## Important historical qualifications

- Version numbers are not sufficient provenance. Some 0.1.8 records refer to distinct build or staging contexts.
- 0.1.23 and 0.1.24 were candidates with runtime validation still pending.
- 0.1.25 is confirmed as the staging-installed version, but the previous concurrent round-robin test exposed state verification failures.
- Do not treat the unofficial 0.1.26 cache-isolation prototype as an approved version or source of truth.
- Future release artifacts must be generated from an identified, reviewed Git commit and have their SHA-256 recorded.
