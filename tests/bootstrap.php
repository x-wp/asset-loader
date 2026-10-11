<?php
/**
 * PHPUnit bootstrap file.
 *
 * Only the composer autoloader is loaded. WordPress is intentionally absent so the
 * unit tests exercise the bundle classes in isolation.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
