# Release Notes

## 1.0.0 (2026-07-08)

### What's new
- Drag & drop reordering for taxonomy terms in the Control Panel
- Terms Order overview page listing all orderable taxonomies, with term counts
- Terms Order item in the Tools navigation, with a child link per taxonomy
- Order is stored as a plain `order` field in each term's YAML
- Reordering sets `sort_by: order` on the taxonomy, so the native CP terms listing follows the manual order
- New terms are automatically appended to the end of manually ordered taxonomies
- Reset button to remove the manual order and restore alphabetical sorting
- Dedicated `reorder {taxonomy} terms` permission per taxonomy, grouped under "Taxonomy Terms Order"
- Configurable list of orderable taxonomies (defaults to all)
- English and Dutch translations
