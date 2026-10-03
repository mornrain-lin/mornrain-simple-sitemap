<?php
/**
 * Plugin Name: MornRain Simple Sitemap
 * Plugin URI: https://github.com/mornrain-lin/mornrain-simple-sitemap
 * Description: Serves a plain XML sitemap at /sitemap.xml, with automatic paging for large sites and an optional robots.txt entry.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: MornRain
 * Author URI: https://github.com/mornrain-lin
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mornrain-simple-sitemap
 * Domain Path: /languages
 *
 * @package Mornrain_Simple_Sitemap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'MORNRAIN_SIMPLE_SITEMAP_VERSION', '1.0.0' );
define( 'MORNRAIN_SIMPLE_SITEMAP_FILE', __FILE__ );
define( 'MORNRAIN_SIMPLE_SITEMAP_PATH', plugin_dir_path( __FILE__ ) );
define( 'MORNRAIN_SIMPLE_SITEMAP_URL', plugin_dir_url( __FILE__ ) );
define( 'MORNRAIN_SIMPLE_SITEMAP_OPTION', 'mornrain_simple_sitemap_settings' );
define( 'MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR', 'mornrain_simple_sitemap' );

require_once MORNRAIN_SIMPLE_SITEMAP_PATH . 'includes/functions-simple-sitemap.php';
require_once MORNRAIN_SIMPLE_SITEMAP_PATH . 'includes/class-mornrain-simple-sitemap.php';

if ( ! function_exists( 'mornrain_simple_sitemap' ) ) :
	/**
	 * Return the shared plugin instance.
	 *
	 * @since 1.0.0
	 * @return Mornrain_Simple_Sitemap
	 */
	function mornrain_simple_sitemap() {
		return Mornrain_Simple_Sitemap::instance();
	}
endif;

register_activation_hook( __FILE__, array( 'Mornrain_Simple_Sitemap', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Mornrain_Simple_Sitemap', 'on_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Mornrain_Simple_Sitemap', 'on_uninstall' ) );

mornrain_simple_sitemap();
