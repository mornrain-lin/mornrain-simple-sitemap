<?php
/**
 * XML document builder.
 *
 * @package Mornrain_Simple_Sitemap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mornrain_Simple_Sitemap_Renderer' ) ) :
	/**
	 * Turns the requested target into an XML response and stops the request.
	 *
	 * @since 1.0.0
	 */
	final class Mornrain_Simple_Sitemap_Renderer {

		/**
		 * Build the public URL of a sitemap page.
		 *
		 * @since 1.0.0
		 * @param int $page Page number, 1 based.
		 * @return string
		 */
		public static function page_url( $page ) {
			$page = max( 1, (int) $page );

			if ( '' === (string) get_option( 'permalink_structure' ) ) {
				return (string) add_query_arg(
					MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR,
					$page,
					home_url( '/' )
				);
			}

			if ( $page > 1 ) {
				return (string) home_url( '/sitemap-' . $page . '.xml' );
			}

			return (string) home_url( '/sitemap.xml' );
		}

		/**
		 * Build the public URL of the sitemap index.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public static function index_url() {
			if ( '' === (string) get_option( 'permalink_structure' ) ) {
				return (string) add_query_arg(
					MORNRAIN_SIMPLE_SITEMAP_QUERY_VAR,
					'index',
					home_url( '/' )
				);
			}

			return (string) home_url( '/sitemap-index.xml' );
		}

		/**
		 * Render the requested document and exit.
		 *
		 * @since 1.0.0
		 * @param string $target Either 'index' or a page number.
		 * @return void
		 */
		public function render( $target ) {
			if ( headers_sent() ) {
				return;
			}

			nocache_headers();
			header( 'Content-Type: application/xml; charset=' . get_bloginfo( 'charset' ) );
			header( 'X-Robots-Tag: noindex, follow', true );

			$xml = ( 'index' === $target )
				? $this->build_index()
				: $this->build_urlset( (int) $target );

			echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML is escaped while it is built.

			exit;
		}

		/**
		 * Build the XML declaration with the site charset.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private function header_xml() {
			$charset = (string) get_bloginfo( 'charset' );

			if ( '' === $charset ) {
				$charset = 'UTF-8';
			}

			return '<?xml version="1.0" encoding="' . esc_attr( $charset ) . '"?>' . "\n";
		}

		/**
		 * Build a urlset document for one page of entries.
		 *
		 * @since 1.0.0
		 * @param int $page Page number, 1 based.
		 * @return string
		 */
		private function build_urlset( $page ) {
			$page     = max( 1, (int) $page );
			$settings = mornrain_simple_sitemap_get_settings();
			$query    = new WP_Query(
				array(
					'post_type'              => array_map( 'strval', (array) $settings['post_types'] ),
					'post_status'            => 'publish',
					'posts_per_page'         => (int) $settings['per_page'],
					'paged'                  => $page,
					'orderby'                => 'modified',
					'order'                  => 'DESC',
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => false,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				)
			);

			$entries = array();

			if ( 1 === $page && ! empty( $settings['home_entry'] ) ) {
				/**
				 * Filter whether the site home page is listed.
				 *
				 * @since 1.0.0
				 * @param bool $include Whether to include the home page.
				 */
				if ( apply_filters( 'mornrain_simple_sitemap_include_home', true ) ) {
					$entries[] = array(
						'loc'     => home_url( '/' ),
						'lastmod' => (string) gmdate( 'c' ),
					);
				}
			}

			foreach ( $query->posts as $post ) {
				if ( ! $post instanceof WP_Post ) {
					continue;
				}

				/**
				 * Filter whether a single post appears in the sitemap.
				 *
				 * @since 1.0.0
				 * @param bool    $include Whether to include the post.
				 * @param WP_Post $post    The post being considered.
				 */
				if ( ! apply_filters( 'mornrain_simple_sitemap_include_post', true, $post ) ) {
					continue;
				}

				$permalink = get_permalink( $post );

				if ( ! is_string( $permalink ) || '' === $permalink ) {
					continue;
				}

				$entries[] = array(
					'loc'     => $permalink,
					'lastmod' => mornrain_simple_sitemap_lastmod( $post ),
				);
			}

			$lines   = array();
			$lines[] = $this->header_xml();
			$lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			foreach ( $entries as $entry ) {
				$lines[] = "\t" . '<url>';
				$lines[] = "\t\t" . '<loc>' . mornrain_simple_sitemap_escape( $entry['loc'] ) . '</loc>';
				$lines[] = "\t\t" . '<lastmod>' . mornrain_simple_sitemap_escape( $entry['lastmod'] ) . '</lastmod>';
				$lines[] = "\t" . '</url>';
			}

			$lines[] = '</urlset>';

			return implode( "\n", $lines ) . "\n";
		}

		/**
		 * Build a sitemap index listing every page.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		private function build_index() {
			$settings = mornrain_simple_sitemap_get_settings();
			$query    = new WP_Query(
				array(
					'post_type'              => array_map( 'strval', (array) $settings['post_types'] ),
					'post_status'            => 'publish',
					'posts_per_page'         => 1,
					'paged'                  => 1,
					'fields'                 => 'ids',
					'orderby'                => 'modified',
					'order'                  => 'DESC',
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => false,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				)
			);

			$per_page = max( 1, (int) $settings['per_page'] );
			$total    = max( 1, (int) $query->found_posts );
			$pages    = (int) ceil( $total / $per_page );
			$lastmod  = (string) gmdate( 'c' );

			if ( ! empty( $query->posts ) ) {
				$lastmod = mornrain_simple_sitemap_lastmod( (int) $query->posts[0] );
			}

			$lines   = array();
			$lines[] = $this->header_xml();
			$lines[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			for ( $page = 1; $page <= $pages; $page++ ) {
				$lines[] = "\t" . '<sitemap>';
				$lines[] = "\t\t" . '<loc>' . mornrain_simple_sitemap_escape( self::page_url( $page ) ) . '</loc>';
				$lines[] = "\t\t" . '<lastmod>' . mornrain_simple_sitemap_escape( $lastmod ) . '</lastmod>';
				$lines[] = "\t" . '</sitemap>';
			}

			$lines[] = '</sitemapindex>';

			return implode( "\n", $lines ) . "\n";
		}
	}
endif;
