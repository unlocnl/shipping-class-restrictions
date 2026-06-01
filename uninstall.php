<?php

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

// Settings persist inside WooCommerce's per-instance option rows and are
// removed by WooCommerce when a shipping method instance is deleted.
// Nothing plugin-specific to clean up.
