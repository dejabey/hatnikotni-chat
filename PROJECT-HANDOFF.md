# PROJECT HANDOFF — Hatnikotni Chat

**Current state document. Updated 2026-09-23.**

> This file is the single current project-state document. Update it after every material development step.

## Identity

- Product: Hatnikotni Chat
- Repository: dejabey/hatnikotni-chat
- Plugin slug: hatnikotni-chat
- Text domain: hatnikotni-chat
- PHP prefix: HKC_
- CSS prefix: hkc-
- Parent brand: Hatnikotni
- Primary ecosystem: Jelajah Perlis / perlis.xyz

## Boundary

Hatnikotni Chat is a standalone WordPress plugin.

Core must not depend on:

- Jelajah Perlis
- UrbanGo
- WooCommerce
- WPVibe
- AI/chatbot plugins
- external runtime services

The Planner chatbot at rancang.perlis.xyz is a separate project and is outside this plugin.

## Production baseline

perlis.xyz currently uses WP Chat App — Ninja Team for WhatsApp support.

Hatnikotni Chat has NOT been deployed to production.

Migration remains:

development → GitHub → review → release candidate → staging → acceptance testing → activate Hatnikotni Chat → verify → disable WP Chat App → retain rollback safety temporarily.

## Agreed V1

### Frontend

- Global floating WhatsApp button.
- Shortcode: [hatnikotni_chat].
- Both use the same routing, campaign, analytics and WhatsApp URL logic.
- Core button should not require frontend JavaScript where HTML/CSS is sufficient.
- Prefer inline SVG instead of loading an icon library.

### Contacts

Fields:

- id
- name
- phone
- role
- description
- status
- weight
- sort_order
- created_at
- updated_at

Phone storage is digits-only.

Historical contacts should normally be deactivated rather than deleted.

### Routing

V1:

- direct
- random
- round_robin

Weighted routing is reserved for later.

### Analytics

Primary event: whatsapp_click.

This means a click on the WhatsApp button, not confirmation that a message was sent.

Fields:

- id
- event_type
- created_at
- contact_id
- page_id
- page_type
- device
- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

Do not store IP, visitor identity, full user-agent, fingerprint, conversation content, browsing history or visitor ID.

Analytics failure must never prevent the WhatsApp action.

### Campaign

Supported UTM fields:

- utm_source
- utm_medium
- utm_campaign
- utm_term
- utm_content

V1 uses last-touch attribution through a first-party hkc_campaign cookie with 30-day retention.

New UTM attribution overwrites the previous campaign attribution.

No general visitor tracking is required.

### Database

Tables:

- {$wpdb->prefix}hkc_contacts
- {$wpdb->prefix}hkc_events

Use WordPress DB prefix, charset/collation and dbDelta.

Use WordPress time functions.

No database foreign keys.

Schema is versioned through HKC_DB_VERSION.

Initial event retention target: 180 days. Admin retention controls are not implemented yet.

### Admin

Planned top-level pages:

- General
- Contacts
- Routing
- Analytics
- Integrations

Campaign is part of Analytics, not a separate top-level page.

### Integrations

Optional/future:

- Webhook
- WooCommerce
- CRM
- weighted/context routing
- WhatsApp Business API

These must not become core dependencies.

Chatbot remains out of scope unless explicitly reintroduced.

## Architecture baseline

Planned modules:

- HKC_Plugin
- HKC_Settings
- HKC_Contacts
- HKC_Routing
- HKC_Analytics
- HKC_Campaign
- HKC_Shortcode
- HKC_Admin

Avoid unnecessary repositories, controllers, service containers, event buses or frontend frameworks unless a real requirement justifies them.

## Repository state

Completed:

- private repository created;
- README;
- CHANGELOG;
- architecture document;
- plugin skeleton;
- activation/deactivation lifecycle;
- initial contacts/events schema installers;
- AI development protocol;
- current handoff.

Skeleton files currently include:

- hatnikotni-chat.php
- includes/* core module stubs
- assets/css/hatnikotni-chat.css
- uninstall.php

Not implemented:

- Contact CRUD
- routing algorithms
- floating button
- shortcode rendering
- WhatsApp URL generation
- analytics recording/query UI
- UTM cookie capture
- retention cleanup
- webhook
- WooCommerce integration
- CI/build workflow
- automated contract tests
- staging acceptance
- production deployment

## Validation state

- Source syntax: PENDING
- Automated tests/contracts: PENDING
- CI/build: PENDING
- Staging activation: PENDING
- Frontend functional testing: PENDING
- Mobile/desktop: PENDING
- Cache compatibility: PENDING
- WooCommerce compatibility: PENDING
- Accessibility: PENDING
- Performance: PENDING
- Security review: PENDING
- Production acceptance: PENDING

No runtime acceptance is claimed.

## Development rule

Every material implementation must be reviewed for:

- security
- WordPress compatibility
- PHP syntax
- naming/prefix hygiene
- unused/dead code
- dependency boundaries
- accessibility
- cache behaviour
- performance
- migration/uninstall safety
- documentation continuity

## Current next action

Run a source-level audit and syntax validation of the skeleton. Correct issues before implementing Contact CRUD.

After the audit, update this file and CHANGELOG with the exact commit and validation status.
