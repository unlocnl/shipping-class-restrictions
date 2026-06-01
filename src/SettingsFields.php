<?php

declare(strict_types=1);

namespace ShippingClassRestrictions;

defined('ABSPATH') || exit;

final class SettingsFields
{
    public const SECTION_KEY = 'scr_section';
    public const SCOPE_KEY = 'scr_class_scope';
    public const CLASSES_KEY = 'scr_shipping_classes';

    /**
     * Attach a field-injection filter for every registered shipping method id.
     * Runs in admin only (gated by the caller).
     */
    public static function register(): void
    {
        $shipping = WC()->shipping();

        if (!$shipping) {
            return;
        }

        foreach (array_keys($shipping->get_shipping_methods()) as $methodId) {
            add_filter(
                'woocommerce_shipping_instance_form_fields_' . $methodId,
                [self::class, 'appendFields']
            );
        }
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    public static function appendFields(array $fields): array
    {
        $fields[self::SECTION_KEY] = [
            'title' => __('Shipping class restrictions', 'shipping-class-restrictions'),
            'type' => 'title',
            'description' => __('Limit when this shipping method is offered, based on the shipping classes of the products in the cart. Leave it on "No restriction" to always offer the method.', 'shipping-class-restrictions'),
        ];

        $fields[self::SCOPE_KEY] = [
            'title' => __('Availability', 'shipping-class-restrictions'),
            'type' => 'select',
            'description' => __('When this method is available, based on the shipping classes in the cart.', 'shipping-class-restrictions'),
            'desc_tip' => false,
            'default' => '',
            'options' => [
                '' => __('No restriction', 'shipping-class-restrictions'),
                'show_only' => __('Show only if cart contains only these classes', 'shipping-class-restrictions'),
                'show_any' => __('Show if cart contains any of these classes', 'shipping-class-restrictions'),
                'hide_any' => __('Hide if cart contains any of these classes', 'shipping-class-restrictions'),
                'hide_only' => __('Hide if cart contains only these classes', 'shipping-class-restrictions'),
            ],
        ];

        $fields[self::CLASSES_KEY] = [
            'title' => __('Shipping classes', 'shipping-class-restrictions'),
            'type' => 'multiselect',
            'class' => 'scr-enhanced-select',
            'css' => 'width: 100%;',
            'custom_attributes' => [
                'data-placeholder' => __('Select shipping classes', 'shipping-class-restrictions'),
            ],
            'description' => __('The shipping classes the availability rule above applies to. "No shipping class" covers products without a class.', 'shipping-class-restrictions'),
            'desc_tip' => false,
            'default' => [],
            'options' => self::classOptions(),
        ];

        return $fields;
    }

    /**
     * @return array<int, string>
     */
    private static function classOptions(): array
    {
        $options = [
            0 => __('No shipping class', 'shipping-class-restrictions'),
        ];

        foreach (WC()->shipping()->get_shipping_classes() as $class) {
            $options[(int) $class->term_id] = $class->name;
        }

        return $options;
    }
}
