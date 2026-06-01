<?php
/**
 * Plugin Name: Shipping Class Restrictions
 * Plugin URI: https://github.com/unlocnl/shipping-class-restrictions
 * Description: Restrict WooCommerce shipping method availability by the shipping classes in the cart, per method instance and zone. Works with any third-party shipping method.
 * Version: 1.0.0
 * Author: Unloc
 * Author URI: https://unloc.nl
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shipping-class-restrictions
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * WC requires at least: 8.6
 * WC tested up to: 10.5
 * Requires Plugins: woocommerce
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('SCR_VERSION', '1.0.0');
define('SCR_FILE', __FILE__);
define('SCR_DIR', plugin_dir_path(__FILE__));

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/autoload.php';
}

add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__);
    }
});

add_action('plugins_loaded', static function (): void {
    \ShippingClassRestrictions\Plugin::init();
});
