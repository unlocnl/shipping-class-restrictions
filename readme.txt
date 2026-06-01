=== Shipping Class Restrictions ===
Contributors: unloc
Tags: woocommerce, shipping, shipping class, shipping zones
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Restrict WooCommerce shipping methods by the shipping classes in the cart, per method instance and zone. Works with any third-party method.

== Description ==

Adds two fields to every shipping method's settings (in the zone editor):

* Availability: a single rule choosing when the method shows, based on the classes in the cart
* Shipping classes: which classes the rule applies to (including "No shipping class")

Availability options:

* Show only if cart contains only these classes
* Show if cart contains any of these classes
* Hide if cart contains any of these classes
* Hide if cart contains only these classes

For example, a letterbox-only method can be set to "Show only if cart contains only [letterbox]", while the default parcel method is set to "Hide if cart contains only [letterbox]" — so the parcel method disappears for an all-letterbox cart but returns as soon as a parcel item is added. Because configuration is per method instance, it is inherently scoped to the shipping zone the instance belongs to.

== Changelog ==

= 1.0.0 =
* Initial release.
