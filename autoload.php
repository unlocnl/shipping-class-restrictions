<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

spl_autoload_register(static function (string $class): void {
    $prefix = 'ShippingClassRestrictions\\';
    $length = strlen($prefix);

    if (strncmp($prefix, $class, $length) !== 0) {
        return;
    }

    $relative = substr($class, $length);
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_readable($file)) {
        require $file;
    }
});
