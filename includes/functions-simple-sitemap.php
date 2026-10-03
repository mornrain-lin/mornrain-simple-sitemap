<?php
/**
 * Settings, URL and XML helpers for MornRain Simple Sitemap.
 *
 * @package Mornrain_Simple_Sitemap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mornrain_simple_sitemap_defaults' ) ) :
	/**
	 * Default settings.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	function mornrain_simple_sitemap_defaults() {
		return array(
			'post_types' => array( 'post', 'page' ),
			'per_page'   => 1000,
			'robots'     => 1,
			'home_entry' => 1,
		);
	}
endif;

if ( ! function_exists( 'mornrain_simple_sitemap_get_settings' ) ) :
	/**
	 * Read the stored settings merged over the defaults, then normalised.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	function mornrain_simple_sitemap_get_settings() {
		$stored = get_option( MORNRAIN_SIMPLE_SITEMAP_OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = array_merge( mornrain_simple_sitemap_defaults(), $stored );

		$settings['per_page']   = min( 5000, max( 1, (int) $settings['per_page'] ) );
		$settings['robots']     = empty( $settings['robots'] ) ? 0 : 1;
		$settings['home_entry'] = empty( $settings['home_entry'] ) ? 0 : 1;
		$settings['post_types'] = array_values(
			array_filter( array_map( 'sanitize_key', (array) $settings['post_types'] ) )
		);

		if ( empty( $settings['post_types'] ) ) {
			$settings['post_types'] = array( 'post', 'page' );
		}

		/**
		 * Filter the effective sitemap settings.
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $settings Effective settings.
		 */
		return (array) apply_filters( 'mornrain_simple_sitemap_settings', $settings );
	}
endif;

if ( ! function_exists( 'mornrain_simple_sitemap_sanitize' ) ) :
	/**
	 * Sanitise a settings array.
	 *
	 * @since 1.0.0
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	function mornrain_simple_sitemap_sanitize( $input ) {
		$defaults = mornrain_simple_sitemap_defaults();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$allowed = (array) get_post_types( array( 'public' => true ), 'names' );
		$types   = array();

		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			foreach ( $input['post_types'] as $type ) {
				$type = sanitize_key( (string) $type );

				if ( in_array( $type, $allowed, true ) && 'attachment' !== $type ) {
					$types[] = $type;
				}
			}
		}

		$per_page = isset( $input['per_page'] ) ? absint( $input['per_page'] ) : (int) $defaults['per_page'];

		return array(
			'post_types' => empty( $types ) ? $defaults['post_types'] : array_values( array_unique( $types ) ),
			'per_page'   => min( 5000, max( 1, $per_page ) ),
			'robots'     => empty( $input['robots'] ) ? 0 : 1,
			'home_entry' => empty( $input['home_entry'] ) ? 0 : 1,
		);
	}
endif;

if ( ! function_exists( 'mornrain_simple_sitemap_escape' ) ) :
	/**
	 * Escape a value for an XML text node.
	 *
	 * @since 1.0.0
	 * @param string $value Raw value.
	 * @return string
	 */
	function mornrain_simple_sitemap_escape( $value ) {
		if ( function_exists( 'esc_xml' ) ) {
			return esc_xml( (string) $value );
		}

		return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8' );
	}
endif;

if ( ! function_exists( 'mornrain_simple_sitemap_lastmod' ) ) :
	/**
	 * Build a W3C datetime string for a post.
	 *
	 * @since 1.0.0
	 * @param int|WP_Post|null $post Post to inspect.
	 * @return string
	 */
	function mornrain_simple_sitemap_lastmod( $post = null ) {
		$post = get_post( $post );

		if ( $post instanceof WP_Post ) {
			$stamp = strtotime( $post->post_modified_gmt . ' UTC' );

			if ( false !== $stamp ) {
				return (string) gmdate( 'c', $stamp );
			}
		}

		return (string) gmdate( 'c' );
	}
endif;
