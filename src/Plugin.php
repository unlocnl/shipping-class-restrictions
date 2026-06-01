<?php

declare(strict_types=1);

namespace ShippingClassRestrictions;

defined('ABSPATH') || exit;

final class Plugin
{
    public static function init(): void
    {
        if (is_admin()) {
            add_action('woocommerce_init', [SettingsFields::class, 'register']);
            Assets::register();
        }

        add_filter('woocommerce_package_rates', [RateFilter::class, 'filter'], 10, 2);
    }
}
