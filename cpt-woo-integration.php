<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Custom Post Type WooCommerce Integration
 * Plugin URI:        https://www.wptinysolutions.com/tiny-products/cpt-woo-integration
 * Description:       Integrate custom post type with woocommerce. Sell Any Kind Of Custom Post
 * Version:           2.0.6
 * Author:            Tiny Solutions
 * Author URI:        https://www.wptinysolutions.com/
 * Tested up to:      6.7
 * WC tested up to:   9.0.1
 * Text Domain:       cptwooint
 * Domain Path:       /languages
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @package TinySolutions\WM
 */

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\CptWooIntegration;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Define cptwooint Constant.
 */

define( 'CPTWI_VERSION', '2.0.6' );

define( 'CPTWI_FILE', __FILE__ );

define( 'CPTWI_BASENAME', plugin_basename( CPTWI_FILE ) );

define( 'CPTWI_URL', plugins_url( '', CPTWI_FILE ) );

define( 'CPTWI_ABSPATH', dirname( CPTWI_FILE ) );

define( 'CPTWI_PATH', plugin_dir_path( __FILE__ ) );

/**
 * App Init.
 */

require_once CPTWI_PATH . 'vendor/autoload.php';

/**
 * @return CptWooIntegration
 */
function cptwooint() {
	return CptWooIntegration::instance();
}
add_action( 'plugins_loaded', 'cptwooint' );

// Available all functionality and variable
// https://www.businessbloomer.com/woocommerce-easily-get-product-info-title-sku-desc-product-object/
