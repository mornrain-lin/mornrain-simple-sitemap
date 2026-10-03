<?php
/**
 * Uninstall routine.
 *
 * Deletes the single option row the plugin creates, on every site of a
 * multisite network, and clears the rewrite rules so no stale route survives.
 * The sitemap itself is generated on the fly and nothing is written to disk.
 *
 * @package Mornrain_Simple_Sitemap
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$mornrain_simple_sitemap_option = 'mornrain_simple_sitemap_settings';

delete_option( $mornrain_simple_sitemap_option );

if ( is_multisite() ) {
	$mornrain_simple_sitemap_sites = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $mornrain_simple_sitemap_sites as $mornrain_simple_sitemap_site ) {
		switch_to_blog( (int) $mornrain_simple_sitemap_site );
		delete_option( $mornrain_simple_sitemap_option );
		restore_current_blog();
	}
}

flush_rewrite_rules();
