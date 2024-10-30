<?php

namespace TinySolutions\cptwooint\PluginsSupport\JetEngine;

// Do not allow directly accessing this file.
use Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package\Meta_Boxes;
use Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package\Meta_Boxes\Product_Data_Panel;
use Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package\Meta_Boxes\Product_Variation_Panel;
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Loader;
use TinySolutions\cptwooint\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * LPInit
 */
class JetManager {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_action( 'jet-engine/meta-boxes/register-custom-source/woocommerce_product_data', [ $this, 'register_product_panel_meta_box' ] );
		 add_action( 'jet-engine/meta-boxes/register-custom-source/woocommerce_product_variation', [ $this, 'register_product_variation_meta_box' ] );
	}

	/**
	 * Register product panel meta box.
	 *
	 * Register meta box for product panel.
	 *
	 * @since  3.2.0
	 * @access public
	 *
	 * @param array $meta_box List of meta box settings.
	 *
	 * @return void
	 */
	public function register_product_panel_meta_box( $meta_box ) {
		if ( ! class_exists( 'Product_Data_Panel' ) ) {
			require_once jet_engine()->plugin_path( 'includes/compatibility/packages/woocommerce/inc/meta-boxes/product-data-panel.php' );
		}
		new ProductDataPanel( $meta_box );
		Meta_Boxes\Manager::instance()->enqueue_custom_styles();
	}
	
	
	/**
	 * Register product variation meta box.
	 *
	 * Register meta box for product variation panel.
	 *
	 * @since  3.2.0
	 * @access public
	 *
	 * @param array $meta_box List of meta box settings.
	 *
	 * @return void
	 */
	public function register_product_variation_meta_box( $meta_box ) {
		if ( ! class_exists( 'Product_Variation_Panel' ) ) {
			require_once jet_engine()->plugin_path( 'includes/compatibility/packages/woocommerce/inc/meta-boxes/product-variation-panel.php' );
		}
		new ProductVariationPanel( $meta_box );
		add_action( 'admin_enqueue_scripts', [ Meta_Boxes\Manager::instance(), 'enqueue_inline_script' ], 20 );
		Meta_Boxes\Manager::instance()->enqueue_custom_styles();
	}
	
	
}
