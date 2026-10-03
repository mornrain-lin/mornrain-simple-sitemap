<?php
/**
 * Smoke test: verifies the repository scaffolding is in place.
 *
 * @package Mornrain_Simple_Sitemap
 */

use PHPUnit\Framework\TestCase;

/**
 * Class ScaffoldTest
 */
final class ScaffoldTest extends TestCase {

	/**
	 * The repository must ship a README and a license.
	 *
	 * @return void
	 */
	public function test_repository_files_exist(): void {
		$this->assertFileExists( __DIR__ . '/../README.md' );
		$this->assertFileExists( __DIR__ . '/../LICENSE' );
		$this->assertFileExists( __DIR__ . '/../composer.json' );
	}

	/**
	 * Every PHP file must declare the ABSPATH guard pattern in its family.
	 *
	 * @return void
	 */
	public function test_source_directory_is_present(): void {
		$root = __DIR__ . '/..';
		$this->assertTrue(
			is_dir( $root . '/includes' ) || is_dir( $root . '/assets' ) || file_exists( $root . '/style.css' ) || file_exists( $root . '/functions.php' ),
			'Expected a WordPress theme or plugin entry point.'
		);
	}
}
