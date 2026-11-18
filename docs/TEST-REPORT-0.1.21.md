# Hatnikotni Chat 0.1.21 — Test Report

## Scope

UI/UX refinement candidate based on the installed 0.1.20 candidate. Changes: workspace navigation links, active settings-tab persistence, compact privacy notice, and overlapping privacy toggle treatment.

## Static checks

- PHP lint: all plugin PHP files passed.
- JavaScript syntax checks: admin tabs, purpose chooser and privacy scripts passed.
- ZIP integrity: to be verified after packaging.
- Manual WordPress/browser regression: **not performed in this environment**.

## Regression risks requiring staging confirmation

1. Save settings while each Setup, Routing and Extensions tab is active; confirm same tab returns and success notice appears.
2. Navigate General / Contacts / Analytics using workspace links; confirm active item is correct and each page remains operable.
3. Check privacy notice spacing at desktop and mobile widths, including expanded explanation.
4. Confirm privacy control remains independently clickable/focusable where overlapped; test keyboard focus and mobile touch.
5. Confirm Reject and Accept behavior is unchanged and WhatsApp remains accessible regardless of analytics consent.
6. Test both bottom-right and bottom-left widget placements, including narrow viewport and any theme sticky/overflow contexts.
7. Confirm purpose-routing disabled mode and legacy shortcode behavior remain unchanged.

## Important limitation

The overlapping shield's effective visible hit area depends on browser stacking and actual rendered dimensions. This build needs real-device/browser acceptance before production use.
