# Hatnikotni Chat — Core and Extension Architecture

## Product boundary

Hatnikotni Chat core is a reusable WordPress contact-routing and consent-aware click analytics layer. It must not contain assumptions about Jelajah Perlis, WooCommerce products/orders, UrbanGo listing CPTs, package tiers, entitlement duration, or a single site's customer policy.

Site-specific features belong in optional companion modules. Jelajah Perlis may later have a dedicated integration module that handles package entitlement, listing context, support policy, and site-specific form fields.

## Core responsibilities

- Floating WhatsApp entry point and purpose chooser.
- Contact directory and contact activation/default protection.
- Direct, Random and Round Robin routing.
- Configurable built-in purpose-to-role mapping.
- Click analytics subject to configured consent.
- General-purpose labels and messages.
- Admin tabs: Setup, Routing, Extensions.

## Extension contracts

### `hatnch_purpose_definitions`

Filter receives the built-in associative map and returns purpose definitions:

```php
add_filter( 'hatnch_purpose_definitions', function ( $purposes ) {
    $purposes['appointments'] = array( 'label' => __( 'Appointments', 'my-module' ) );
    return $purposes;
} );
```

Purpose keys are sanitized with `sanitize_key()`. Each definition must be an array containing a non-empty scalar `label`. This filter changes the visitor choice registry only; it does not itself configure role mapping, forms, or custom admin controls.

### `hatnch_purpose_role_routes`

Filter receives purpose ID => role ID mapping. Modules may append routes, but should register matching purpose definitions and ensure role IDs are permitted.

```php
add_filter( 'hatnch_purpose_role_routes', function ( $routes ) {
    $routes['appointments'] = 'general_enquiries';
    return $routes;
} );
```

### `hatnch_allowed_contact_roles`

Filter receives the core role allow-list. Custom roles need to be included here, and contact records must store matching role IDs. Role UI and data migration remain module responsibilities unless core later formalizes a role registry.

### `hatnch_admin_extension_panels`

Filter returns an array of descriptors. Each descriptor supports:
- `title`: plain text title.
- `callback`: trusted PHP callable that renders the module's admin settings content.

The callback executes within the core settings form. It must not create a nested form. Module save actions should use separate admin-post endpoints with their own capability and nonce validation where appropriate. Escape all output.

## Security contract

- Never accept phone number, destination, or routing role from visitor query parameters.
- Visitor purpose values must resolve against registered definitions/routes.
- Validate module options server-side; sanitize on input and escape on output.
- Avoid putting email addresses, support descriptions, or other personal information into analytics.
- Do not add customer entitlement checks to core.

## Jelajah Perlis module — future scope

An optional module may add:
- Integration with supported account/order/package data sources.
- Package entitlement checks and expiration semantics.
- Listing selection and context.
- Per-tier support scope/policy.
- Project-specific purposes, labels, form fields, email routing and staff workflows.

Before implementation, audit how the site's commerce/theme currently models package ownership, cumulative packages, expiry, and listing limits. An email address is a lookup hint, not proof of identity or entitlement.

## Current limitations

- Built-in purpose chooser is a simple choice panel. Per-purpose native multi-step form workflows are not implemented in this version.
- Extension surfaces provide hooks and settings rendering only, not a fully packaged module manager or module installer.
- Adding custom purposes requires corresponding route mapping and may require a module-provided configuration UX.
- The settings tab UI is progressive JavaScript; when JavaScript is unavailable, the initial Setup pane remains visible, but other panes are not navigable.
