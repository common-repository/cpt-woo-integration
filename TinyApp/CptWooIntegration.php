<?php
/**
 * Main initialization class.
 *
 * @package TinySolutions\cptwooint
 */
namespace TinySolutions\cptwooint;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use TinySolutions\cptwooint\Controllers\Admin\AdminMenu;
use TinySolutions\cptwooint\Controllers\Admin\Api;
use TinySolutions\cptwooint\Controllers\AdminController;
use TinySolutions\cptwooint\Controllers\AssetsController;
use TinySolutions\cptwooint\Controllers\Dependencies;
use TinySolutions\cptwooint\Controllers\Installation;
use TinySolutions\cptwooint\Controllers\Notice\AdminNotice;
use TinySolutions\cptwooint\Controllers\ShortCodes;
use TinySolutions\cptwooint\Hooks\ActionHooks;
use TinySolutions\cptwooint\Hooks\FilterHooks;
use TinySolutions\cptwooint\PluginsSupport\RootSupport;
use TinySolutions\cptwooint\Traits\SingletonTrait;

/**
* Main initialization class.
*/
final class CptWooIntegration {

	/**
	 * Nonce id
	 *
	 * @var string
	 */
	public $nonceId = 'cptwooint_wpnonce';

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	public $category = 'cptwooint_category';
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
		$this->loader->add_action( 'init', $this, 'init' );
		// HPOS.
		$this->loader->add_action( 'before_woocommerce_init', $this, 'wc_declare_compatibility' );
		// Register Plugin Active Hook.
		register_activation_hook( CPTWI_FILE, [ Installation::class, 'activate' ] );
		// Register Plugin Deactivate Hook.
		register_deactivation_hook( CPTWI_FILE, [ Installation::class, 'deactivation' ] );
		$this->run();
	}
	/**
	 * Woocommerce Compatibility
	 *
	 * @return void
	 */
	public function wc_declare_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPTWI_FILE, true );
		}
	}
	/**
	 * Assets url generate with given assets file
	 *
	 * @param string $file File.
	 *
	 * @return string
	 */
	public function get_assets_uri( $file ) {
		$file = ltrim( $file, '/' );
		return trailingslashit( CPTWI_URL . '/assets' ) . $file;
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		return apply_filters( 'cptwooint_template_path', 'templates/' );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( CPTWI_FILE ) );
	}

	/**
	 * Load Text Domain
	 */
	public function init() {
		load_plugin_textdomain( 'cptwooint', false, CPTWI_ABSPATH . '/languages/' );
	}

	/**
	 * Load Text Domain
	 */
	public function plugins_loaded() {
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init_controller() {
		do_action( 'cptwooint/before_loaded' );
		// Include File.
		AssetsController::instance();
		FilterHooks::instance();
		ActionHooks::instance();
		RootSupport::instance();
		Api::instance();

		if ( is_admin() ) {
			AdminNotice::instance();
			AdminController::instance();
			AdminMenu::instance();
		} else {
			ShortCodes::instance();
		}
		do_action( 'cptwooint/after_loaded' );
	}

	/**
	 * Checks if Pro version installed
	 *
	 * @return boolean
	 */
	public function has_pro() {
		if ( function_exists( 'cptwoointp' ) && version_compare( CPTWIP_VERSION, '1.1.4', '>=' ) ) {
			// Decrypt the license value.
			$is_valid = 'e655d5f802d3d9724f02f3af4e71a0dc' === ( defined( 'TINY_DEBUG_CPTWI_PRO_1_2_7' ) ? md5( TINY_DEBUG_CPTWI_PRO_1_2_7 ) : '' );
			return cptwoointp()->user_can_use_cptwooinitpro() || $is_valid;
		}
		return false;
	}

	/**
	 * PRO Version URL.
	 *
	 * @return string
	 */
	public function pro_version_link() {
		return 'https://www.wptinysolutions.com/tiny-products/cpt-woo-integration/';
	}

	/**
	 * @return void
	 */
	private function run() {
		if ( Dependencies::instance()->check() ) {
			$this->init_controller();
		}
		do_action( 'cptwooint/before_run' );
		$this->loader->run();
	}
}
