# Analytics dashboard visual refinement

## Scope
Presentation-only changes scoped to the Hatnikotni Chat Analytics admin screen. Event capture, retention, calculations, CSV export, delete actions, contact records, and routing are unchanged.

## Design
- Soft white/green dashboard surfaces and restrained WhatsApp-green accent.
- Clearer hierarchy for page heading, KPI cards, reporting period, report panels, and data-management controls.
- Consistent borders, radii, type, spacing, and table presentation.
- Destructive actions remain visually distinct in red.
- Responsive layouts; native WordPress form semantics and visible focus retained.

## Staging verification
1. Review Analytics at desktop, tablet, and mobile widths.
2. Check all reporting periods (7/30/90/180 days).
3. Export CSV and compare values with the dashboard.
4. Test date-range delete and typed DELETE only with designated test records.
5. Confirm Contact Management and front-end WhatsApp routing remain unchanged.

## Implementation
The wrapper receives `hatnch-admin--analytics`, isolating these visual rules from other plugin admin screens.


## Contact attribution semantics
- Current contacts are reported by contact name. The configured contact is marked with a green text-only `Default` indicator above its name.
- Deleted contacts are labeled `Removed contact #ID` when the retained non-identifying `contact_reference` is available. The analytics event does not retain the deleted contact's name or phone number.
- Events that were already missing both `contact_reference` and `contact_id` display `Unknown / removed`. Their former contact ID cannot be recovered from the analytics record and must not be guessed.
- Contact deletion retains analytics rows and transfers the old ID into `contact_reference` before clearing `contact_id`, subject to the existing transactional safeguards.


## 0.1.12 visual refinement

The dashboard follows a compact WordPress reporting-dashboard pattern: summary and period controls first; By Device and By Contact side by side; By Campaign across the full row; then Analytics Data Management with export and date-range deletion side by side and Delete All separated. Responsive layout stacks the sections. The 0.1.12 pass is limited to sizing/layout CSS; it does not change report data or destructive-action behavior.


## 0.1.14 implementation note
The summary region uses three direct grid children (two KPI cards and the reporting-period panel). This avoids flattening a nested summary wrapper with `display: contents`, which did not deliver the intended visual width in staging. The intended desktop grid is three equal columns; the narrow-screen layout is one column.
