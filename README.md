# Taxonomy Terms Order

Drag & drop reordering for taxonomy terms in the Statamic Control Panel.

Statamic supports reordering entries in collections, but taxonomy terms are always sorted alphabetically. This addon adds a dedicated **Terms Order** page per taxonomy where you can drag terms into the desired order. After saving, the Control Panel terms listing follows that order automatically — and your frontend templates can too.

## Features

- Drag & drop reordering of taxonomy terms, straight from the CP navigation
- Order is stored as a plain `order` field in each term's YAML — no database, no magic
- Sets `sort_by: order` on the taxonomy, so the native CP terms listing follows the manual order automatically
- New terms are appended to the end of the list automatically
- Reset button to remove the manual order and go back to alphabetical sorting
- Dedicated `reorder {taxonomy} terms` permission per taxonomy
- Multisite-ready: order is shared across sites, titles stay localized
- Zero core overrides: built entirely on Statamic's public addon APIs

## How to Install

Run the following command from your project root:

``` bash
composer require keyagency/taxonomy-terms-order
```

## How to Use

1. Open the Control Panel. A **Terms Order** item appears in the Tools section of the navigation. It links to an overview of all orderable taxonomies (there's also a child link per taxonomy).
2. Pick a taxonomy, drag the terms into the desired order and hit **Save Order** (or <kbd>cmd</kbd>+<kbd>s</kbd>).
3. Done. The terms listing and your frontend now follow the manual order.

Changed your mind? Hit **Reset** on the taxonomy's order page to remove the manual order from all terms and go back to alphabetical sorting.

### Limiting which taxonomies are orderable

By default every taxonomy can be reordered. To limit this, publish the config:

``` bash
php artisan vendor:publish --tag=taxonomy-terms-order-config
```

``` php
// config/taxonomy-terms-order.php

return [
    'taxonomies' => ['article_categories'], // or ['*'] for all
];
```

### Permissions

Non-super users need the **Reorder terms** permission, which appears per taxonomy in the "Taxonomy Terms Order" permissions group. Users without this permission won't see the navigation item.

### Frontend

The `{{ taxonomy:* }}` tag does not apply a default sort, so add the `order_by` parameter in your templates:

``` antlers
{{ taxonomy:article_categories order_by="order:asc|title:asc" }}
    {{ title }}
{{ /taxonomy:article_categories }}
```

The `title:asc` fallback keeps taxonomies without a manual order sorted alphabetically, so the same partial works for both.

## How it works

- Saving an order writes a sequential `order` key into each term's YAML file (`content/taxonomies/<taxonomy>/<slug>.yaml`).
- The taxonomy config gets `sort_by: order` and `sort_dir: asc`, which Statamic natively supports for sorting the CP terms listing.
- A `TermSaving` listener assigns `max(order) + 1` to terms created without an order, so they land at the bottom of the list.

## Support

Found a bug or have a feature request? [Open an issue](https://github.com/keyagency/taxonomy-terms-order/issues) or email [development@keyagency.nl](mailto:development@keyagency.nl).

## About Key Agency

Key Agency is a digital agency based in Amsterdam that helps brands grow with strategy, content, technology and advertising working together as one digital ecosystem.

- Website: [keyagency.nl](https://keyagency.nl)
- Contact: [development@keyagency.nl](mailto:development@keyagency.nl)
