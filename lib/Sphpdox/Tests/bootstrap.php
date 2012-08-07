<?php

/**
 * Bootstrap file for the test suite
 */

if (is_readable(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

// Use SplClassLoader
require_once __DIR__ . '/../../SplClassLoader.php';

$classLoader = new \SplClassLoader('Sphpdox', __DIR__ . '/../..');
$classLoader->register();
