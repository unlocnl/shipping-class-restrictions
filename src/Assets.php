<?php

declare(strict_types=1);

namespace ShippingClassRestrictions;

defined('ABSPATH') || exit;

final class Assets
{
    public static function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * WooCommerce renders the shipping method settings in a Backbone modal but
     * never fires `wc-enhanced-select-init` for it, so our class multiselect
     * would fall back to a plain native listbox. Rather than firing the global
     * init (which would also enhance core's own selects in that modal), enhance
     * only our own field when the modal loads.
     *
     * Appended inline to the already-enqueued `wc-enhanced-select` handle, so it
     * adds no extra HTTP request.
     */
    public static function enqueue(string $hookSuffix): void
    {
        if ('woocommerce_page_wc-settings' !== $hookSuffix) {
            return;
        }

        wp_enqueue_script('wc-enhanced-select');

        $script = <<<'JS'
jQuery(function ($) {
	$(document.body).on('wc_backbone_modal_loaded', function (event, target) {
		if ('wc-modal-shipping-method-settings' !== target || !$.fn.selectWoo) {
			return;
		}
		$('.wc-backbone-modal select.scr-enhanced-select').filter(':not(.enhanced)').each(function () {
			var $select = $(this);
			$select.selectWoo({
				minimumResultsForSearch: 10,
				allowClear: false,
				placeholder: $select.data('placeholder'),
				width: '100%'
			}).addClass('enhanced');
		});
	});
});
JS;

        wp_add_inline_script('wc-enhanced-select', $script);
    }
}
