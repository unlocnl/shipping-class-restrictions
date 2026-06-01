<?php

declare(strict_types=1);

namespace ShippingClassRestrictions;

defined('ABSPATH') || exit;

final class RateFilter
{
    /**
     * @param array<string, \WC_Shipping_Rate> $rates
     * @param array<string, mixed> $package
     * @return array<string, \WC_Shipping_Rate>
     */
    public static function filter(array $rates, array $package): array
    {
        if ($rates === []) {
            return $rates;
        }

        $cartClasses = self::cartClassIds($package);

        foreach ($rates as $rateId => $rate) {
            [$mode, $classes] = self::ruleFor($rate);

            if ($mode === '' || $classes === []) {
                continue;
            }

            if (!self::passes($mode, $classes, $cartClasses)) {
                unset($rates[$rateId]);
            }
        }

        return $rates;
    }

    /**
     * @return array{0: string, 1: array<int, int>}
     */
    private static function ruleFor(\WC_Shipping_Rate $rate): array
    {
        $methodId = $rate->get_method_id();
        $instanceId = $rate->get_instance_id();

        if (!$methodId || !$instanceId) {
            return ['', []];
        }

        $settings = get_option("woocommerce_{$methodId}_{$instanceId}_settings", []);

        if (!is_array($settings)) {
            return ['', []];
        }

        $mode = (string) ($settings[SettingsFields::SCOPE_KEY] ?? '');
        $classes = $settings[SettingsFields::CLASSES_KEY] ?? [];
        $classes = is_array($classes) ? array_map('intval', $classes) : [];

        return [$mode, $classes];
    }

    /**
     * Pure decision: should a rate with this rule survive for this cart?
     *
     * @param array<int, int> $allowed
     * @param array<int, int> $cartClasses
     */
    private static function passes(string $mode, array $allowed, array $cartClasses): bool
    {
        $everyIn = true;
        $anyIn = false;

        foreach ($cartClasses as $classId) {
            if (in_array($classId, $allowed, true)) {
                $anyIn = true;
            } else {
                $everyIn = false;
            }
        }

        return match ($mode) {
            'show_only' => $everyIn,
            'show_any' => $anyIn,
            'hide_any' => !$anyIn,
            'hide_only' => !$everyIn,
            default => true,
        };
    }

    /**
     * Distinct shipping class ids in the package (0 = no shipping class).
     *
     * @param array<string, mixed> $package
     * @return array<int, int>
     */
    private static function cartClassIds(array $package): array
    {
        $ids = [];

        foreach (($package['contents'] ?? []) as $item) {
            $product = $item['data'] ?? null;

            if ($product instanceof \WC_Product) {
                $ids[(int) $product->get_shipping_class_id()] = true;
            }
        }

        return array_keys($ids);
    }
}
