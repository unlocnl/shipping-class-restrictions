# CLAUDE.md — shipping-class-restrictions

Standalone WooCommerce plugin. Restricts shipping-method availability by the shipping classes in the cart, configured per method instance (hence per zone). Works with any third-party method that extends `WC_Shipping_Method`.

**Standalone by design:** pure WP + WooCommerce APIs only. No Acorn/Laravel, no unloc-core, no auto-discovery, no `RegisterPlugin`. Submittable to a public repo. Don't add unloc dependencies.

## Architecture

- **Namespace:** `ShippingClassRestrictions\` (PSR-4 from `src/`)
- **Entry point:** `shipping-class-restrictions.php` — constants (`SCR_*`), Composer-or-fallback autoload, WC compat declarations, bootstraps `Plugin::init()` on `plugins_loaded`
- **WooCommerce dependency:** native `Requires Plugins: woocommerce` header — no manual `class_exists` guard/notice
- **Autoload:** prefers Bedrock `vendor/autoload.php`; ships `autoload.php` (SPL PSR-4 fallback) so it also runs installed standalone

### Classes

| Class | Role |
|-|-|
| `Plugin` | Wires hooks only. Textdomain on `init`; `SettingsFields::register` on `woocommerce_init` (admin only); `RateFilter::filter` on `woocommerce_package_rates` |
| `SettingsFields` | Injects two fields into every method's instance settings popup |
| `RateFilter` | Removes rates whose include/exclude rule doesn't match the cart's shipping classes |

## Non-obvious patterns

**Field injection without custom storage.** WooCommerce renders a method's instance popup from `get_instance_form_fields()`, which applies the dynamic filter `woocommerce_shipping_instance_form_fields_{$method_id}`. `SettingsFields::register()` enumerates `WC()->shipping()->get_shipping_methods()` and attaches `appendFields` per method id. Because the fields are part of `get_instance_form_fields()`, WooCommerce's `process_admin_options()` **auto-saves and auto-loads** them into option `woocommerce_{id}_{instance_id}_settings` — no save handler, no AJAX, no custom table. This is why it works for any third-party method.

There is **no catch-all filter** for instance form fields — the tag is per method id, so enumeration is required. Only needed in admin.

**Reading config at checkout.** `RateFilter::filter` gets `$rates` keyed by `method_id:instance_id` and the `$package`. For each `WC_Shipping_Rate`, it reads `get_method_id()` + `get_instance_id()` and loads the same `woocommerce_{id}_{instance_id}_settings` option directly (no method instantiation on the frontend). Shared path covers classic checkout AND Blocks/Store API.

**Availability rule** — a single select `scr_class_scope`, evaluated in `RateFilter::passes` (pure/testable). For cart class set C and selected set A:
- `show_only` — show iff every item ∈ A (C ⊆ A). "Only when the whole cart is these classes."
- `show_any` — show iff any item ∈ A (C ∩ A ≠ ∅).
- `hide_any` — hide iff any item ∈ A (show iff C ∩ A = ∅). "Carrier can't carry these at all."
- `hide_only` — hide iff every item ∈ A (show iff C ⊄ A). The motivating case: a default/parcel method hidden when the cart is *only* letterbox, but shown the moment a parcel item is added.
- `''` or no classes selected → rate untouched (`RateFilter::filter` short-circuits).

`hide_only` is the negation of `show_only`; `hide_any` the negation of `show_any`. Unclassed products contribute class id `0`; the `0 => "No shipping class"` option targets them explicitly. Empty cart: `everyIn` is vacuously true, `anyIn` false.

Field keys are prefixed `scr_` (`SettingsFields::SCOPE_KEY`, `CLASSES_KEY`) to avoid collision with a method's own field keys.

## Translations

- Text domain `shipping-class-restrictions`; primary translation `nl_NL`. No `load_plugin_textdomain` call — WordPress 6.5+ loads translations just-in-time because the text domain matches the slug.
- After changing strings, regenerate from the plugin root:
  ```
  wp i18n make-pot . languages/shipping-class-restrictions.pot --slug=shipping-class-restrictions --domain=shipping-class-restrictions --skip-js --skip-audit
  # add msgstr to languages/shipping-class-restrictions-nl_NL.po, then:
  cd languages && msgfmt --check -o shipping-class-restrictions-nl_NL.mo shipping-class-restrictions-nl_NL.po && cd ..
  wp i18n make-php languages
  ```
- Don't translate the plugin name. The plugin checker flags `.l10n.php` for a missing ABSPATH guard — false positive on the auto-generated file, ignore it.

## Constraints

- All user-facing strings via `__()` with text domain `shipping-class-restrictions`. Settings API renders the field HTML/escaping itself.
- Out of scope: per-class pricing (core already does this for its own methods), global matrix admin page, conditions beyond shipping class.
