<?php
/**
 * PHPUnit bootstrap file.
 *
 * Loads Composer's autoloader when present. These projects intentionally keep
 * zero runtime dependencies, so the bootstrap must stay tolerant.
 *
 * @package Mornrain_Simple_Sitemap
 */

$autoload = __DIR__ . '/../vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require_once $autoload;
}
