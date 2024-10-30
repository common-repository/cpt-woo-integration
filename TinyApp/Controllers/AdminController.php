<?php

namespace TinySolutions\cptwooint\Controllers;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Controllers\Admin\ProductAdminAssets;
use TinySolutions\cptwooint\Controllers\Admin\ProductMetaBoxes;
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Loader;
use TinySolutions\cptwooint\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
/**
 * AdminController
 */
class AdminController {
	/**
	 * Singleton
	 */
	use SingletonTrait;
	
	/**
	 * @var object
	 */
	protected $loader;
	
	/**
	 * Class Constructor
	 */
	private function __construct() {
		$this->loader = Loader::instance();
		if ( ! is_admin() ) {
			return;
		}
	
		$this->loader->add_action( 'add_meta_boxes', $this, 'add_meta_boxes', 30 );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_scripts' );
	}
	/**
	 * @return void
	 */
	public function add_meta_boxes() {
		// Global object containing current admin page.
		global $pagenow;
		$current_post_type = '';
		// If current page is post.php and post isset than query for its post type.
		if ( 'post.php' === $pagenow ) {
			$current_post_type = get_post_type( absint( $_GET['post'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( 'post-new.php' === $pagenow ) {
			$current_post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! Fns::is_supported( $current_post_type ) ) {
			return;
		}
	
		ProductMetaBoxes::instance();
	}
	
	/**
	 * @return void
	 */
	public function admin_scripts() {
		// Global object containing current admin page.
		global $pagenow;
		$current_post_type = '';
		// If current page is post.php and post isset than query for its post type.
		if ( 'post.php' === $pagenow ) {
			$current_post_type = get_post_type( absint( $_GET['post'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( 'post-new.php' === $pagenow ) {
			$current_post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! Fns::is_supported( $current_post_type ) ) {
			return;
		}
		ProductAdminAssets::instance();
	}
	
	
}
