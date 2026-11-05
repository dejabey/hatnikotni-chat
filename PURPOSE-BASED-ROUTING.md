# Purpose-based WhatsApp routing (0.1.19)

## Overview

Purpose routing is an optional layer on the floating WhatsApp button. It lets a visitor choose a reason for contact before the plugin routes the click to an active contact. It does not send a WhatsApp message: it opens WhatsApp with a prefilled message, and the visitor must press **Send**.

## Visitor purposes

| Purpose ID | Visitor label | Initial role mapping |
| --- | --- | --- |
| `general_enquiries` | General Enquiries | `general_enquiries` |
| `listing_assistance` | Listing Assistance | `business_support` |
| `business_partnerships` | Business & Partnerships | `partnerships` |
| `technical_question` | Technical Question (light support) | `business_support` |

The purpose-to-role mapping is configurable in **Hatnikotni Chat → General → Routing**.

## Standard contact roles

Set a contact's role under **Hatnikotni Chat → Contacts**:

- `general_enquiries` — General Enquiries
- `business_support` — Business Support
- `partnerships` — Business & Partnerships

Only **active** contacts assigned to the mapped role are eligible for that purpose. Existing free-text roles are preserved in the database, but will not match these standardized role IDs. Review and reassign contacts as needed.

## Selection behavior

The configured global routing method also controls selection within the role-eligible set:

- **Direct:** prefer the configured Default Contact if it is in the eligible set; otherwise use the first eligible contact in contact sort order.
- **Random:** choose one eligible contact at random.
- **Round Robin:** rotate within each standardized role using role-specific state. The role queues do not share a counter.
- **No eligible contact:** fall back to the existing global routing behavior. This preserves the established Default/Random/Round Robin behavior rather than producing a dead-end.
- **Unknown purpose ID:** use global routing. The request never supplies a phone number or role, only an allow-listed purpose identifier.

Direct with multiple contacts is intentionally deterministic: Default Contact when eligible, otherwise first eligible contact. If evenly distributing work is desired, select Random or Round Robin.

## Enablement and setup

1. Update the plugin to 0.1.19 on staging first.
2. Open **Hatnikotni Chat → Contacts**.
3. Assign the appropriate standardized role to each contact. Check that each needed role has at least one active contact.
4. Open **Hatnikotni Chat → General → Routing**.
5. Confirm the existing routing method and Default Contact.
6. Review all four purpose-to-role mappings.
7. Optionally enter a support email. This appears as an alternative for complex technical issues or payment investigations; no email is invented or assumed.
8. Enable **Purpose-based routing** and save.
9. Test all purposes, inactive/empty role fallbacks, each routing method, desktop/mobile presentation, keyboard interaction and new-tab handoff.

## Visitor messaging and support boundaries

The purpose message is generated server-side from an allow-listed purpose and is URL-encoded into the WhatsApp handoff. The categories are:

- General enquiries
- Listing assistance
- Business and partnerships
- Technical question (light support)

Technical routing is for light triage. Complex troubleshooting and payment investigations may need email support. Avoid requesting passwords, one-time codes, payment card details or other sensitive information in WhatsApp.

## Privacy and analytics

Purpose selection is not separately recorded as a purpose analytics field. The existing consent-gated click analytics continue to record the existing click fields. The feature does not add visitor message content, IP address, phone numbers, or purpose text to analytics. A click is not evidence that a message was sent or received.

## Compatibility

- When purpose routing is disabled, the existing floating-button link behavior is preserved.
- The `[hatnikotni_chat]` shortcode remains on its existing direct click-through behavior and is not converted into a chooser.
- The click handler accepts purpose IDs from a fixed allow-list and resolves the destination server-side.
- Contact table schema and DB version remain unchanged.
- The chooser script is loaded only when purpose routing is enabled.

## Release / staging checklist

- [ ] Existing global Direct, Random and Round Robin behavior still works with purpose routing disabled.
- [ ] Each purpose maps to its configured role.
- [ ] Direct prefers the default contact only when it is eligible for that role.
- [ ] Random only selects active role-matched contacts.
- [ ] Round Robin rotates independently per role.
- [ ] Inactive contacts are never selected for a purpose role.
- [ ] Empty/unconfigured role falls back to global routing.
- [ ] Unknown or tampered purpose ID cannot select a role or phone.
- [ ] New-tab behavior and `noopener noreferrer` remain in place.
- [ ] Purpose chooser is usable by keyboard and dismisses with Escape/outside click.
- [ ] Support email is absent when not configured and correctly encoded when shown.
- [ ] Consent rejection leaves WhatsApp access available; analytics remains consent-gated.
- [ ] Desktop and mobile layouts are acceptable.
- [ ] No regression in shortcode, campaign attribution or existing analytics.

## Known implementation caveats for verification

- The chooser is an inline panel rather than a native modal. Screen-reader and mobile-browser testing remains required.
- Role-based routing uses the selected method shared with global routing. Round-robin state is role-specific, but concurrent requests may race in state updates, as in the existing global mechanism.
- The first implementation supports a fixed purpose catalog and role catalog. It does not support custom purposes, custom roles, per-purpose routing algorithms, or contact blacklists.
