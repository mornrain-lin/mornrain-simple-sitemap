<?php
/**
 * Main plugin class.
 *
 * @package Mornrain_Simple_Sitemap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mornrain_Simple_Sitemap' ) ) :
	/**
	 * Registers the /sitemap.xml route, renders the document and keeps the
	 * query variable, heading and body builders in one place.
	 *
	 * @since 1.0.0
	 */
	final class Mornrain_Simple_Sitemap {

		/**
		 * Shared instance.
		 *
		 * @since 1.0.0
		 * @var Mornrain_Simple_Sitemap|null
		 */
		private static $instance = null;

		/**
		 * Retrieve the shared instance, creating it on first call.
		 *
		 * @since 1.0.0
		 * @return Mornrain_Simple_Sitemap
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Wire up the plugin.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->includes();
			$this->hooks();
		}

		/**
		 * Load module files.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function includes() {
			require_once MORNRAIN_SIMPLE_SITEMAP_PATH . 'includes/class-mornrain-simple-sitemap-renderer.php';
		}

		/**
		 * Register WordPress hooks.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function hooks() {
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_action( 'init', array( $this, 'register_rewrite_rules' ) );
			add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
			add_action( 'template_redirect', array( $this, 'maybe_render' ) );
			add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 10, 1 );
		}

		/**
		 * Load translations.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain(
				'mornrain-simple-sitemap',
				false,
				dirname( plugin_basename( MORNRAIN_SIMPLE_SITEMAP_FILE ) ) . '/languages'
			);
		}

		/**
		 * Register the pretty routes, if the site uses permalinks.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function register_rewrite_rules() {
			$var = MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR;

			add_rewrite_rule( '^sitemap\\-index\\.xml$', 'index.php?' . $var . '=index', 'top' );
			add_rewrite_rule( '^sitemap\\-([0-9]+)\\.xml$', 'index.php?' . $var . '=$matches[1]', 'top' );
			add_rewrite_rule( '^sitemap\\.xml$', 'index.php?' . $var . '=1', 'top' );
		}

		/**
		 * Make the query variable public.
		 *
		 * @since 1.0.0
		 * @param array<int, string>|mixed $vars Registered query variables.
		 * @return array<int, string>
		 */
		public function register_query_vars( $vars ) {
			if ( ! is_array( $vars ) ) {
				return array();
			}

			$vars[] = MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR;

			return $vars;
		}

		/**
		 * Render the sitemap when the route is requested.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function maybe_render() {
			$target = $this->get_requested_target();

			if ( '' === $target ) {
				return;
			}

			/**
			 * Filter whether the current request is allowed to render a sitemap.
			 *
			 * @since 1.0.0
			 * @param bool   $allowed Whether rendering is allowed.
			 * @param string $target  Requested target.
			 */
			if ( ! apply_filters( 'mornrain_simple_sitemap_allow_render', true, $target ) ) {
				return;
			}

			$renderer = new Mornrain_Simple_Sitemap_Renderer();
			$renderer->render( $target );
		}

		/**
		 * Read the requested target from the query variable or the URL.
		 *
		 * @since 1.0.0
		 * @return string 'index', a positive page number, or an empty string.
		 */
		private function get_requested_target() {
			$var    = MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR;
			$target = get_query_var( $var );

			if ( '' === $target || false === $target ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only route.
				if ( isset( $_GET[ $var ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only route.
					$target = sanitize_text_field( wp_unslash( (string) $_GET[ $var ] ) );
				}
			}

			if ( ! is_scalar( $target ) ) {
				return '';
			}

			$target = strtolower( trim( (string) $target ) );

			if ( 'index' === $target || 'all' === $target ) {
				return 'index';
			}

			$page = absint( $target );

			if ( $page < 1 ) {
				return '';
			}

			return (string) $page;
		}

		/**
		 * Append the sitemap location to robots.txt.
		 *
		 * @since 1.0.0
		 * @param string|mixed $output robots.txt content produced by core.
		 * @return string
		 */
		public function filter_robots_txt( $output ) {
			$output   = is_string( $output ) ? $output : '';
			$settings = mornrain_simple_sitemap_get_settings();

			if ( empty( $settings['robots'] ) ) {
				return $output;
			}

			$line = 'Sitemap: ' . Mornrain_Simple_Sitemap_Renderer::index_url();

			/**
			 * Filter the robots.txt entry.
			 *
			 * @since 1.0.0
			 * @param string $line   The full "Sitemap: ..." line.
			 * @param string $output The original robots.txt body.
			 */
			$line = (string) apply_filters( 'mornrain_simple_sitemap_robots_line', $line, $output );

			if ( '' === trim( $line ) || false !== strpos( $output, $line ) ) {
				return $output;
			}

			return rtrim( $output, " \n\r\t" ) . "\n" . $line . "\n";
		}

		/**
		 * Seed the default settings and flush the rewrite rules on activation.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function on_activate() {
			$stored = get_option( MORNRAIN_SIMPLE_SITEMAP_OPTION, array() );

			if ( ! is_array( $stored ) ) {
				$stored = array();
			}

			update_option( MORNRAIN_SIMPLE_SITEMAP_OPTION, array_merge( mornrain_simple_sitemap_defaults(), $stored ) );

			Mornrain_Simple_Sitemap::instance()->register_rewrite_rules();
			flush_rewrite_rules();
		}

		/**
		 * Flush the rewrite rules on deactivation.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function on_deactivate() {
			flush_rewrite_rules();
		}

		/**
		 * Remove the option row on uninstall. Bound to register_uninstall_hook().
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function on_uninstall() {
			delete_option( MORNRAIN_SIMPLE_SITEMAP_OPTION );

			if ( is_multisite() ) {
				$site_ids = get_sites( array( 'fields' => 'ids' ) );

				foreach ( $site_ids as $site_id ) {
					switch_to_blog( (int) $site_id );
					delete_option( MORNRAIN_SIMPLE_SITEMAP_OPTION );
					restore_current_blog();
				}
			}

			flush_rewrite_rules();
		}
	}
endif;
