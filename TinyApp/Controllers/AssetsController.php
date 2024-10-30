<?php

namespace TinySolutions\cptwooint\Controllers;

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Loader;
use TinySolutions\cptwooint\Traits\SingletonTrait;
use WC_Frontend_Scripts;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * AssetsController
 */
class AssetsController {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ajax URL
	 *
	 * @var string
	 */
	private $ajaxurl;

	/**
	 * @var object
	 */
	protected $loader;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		$this->loader  = Loader::instance();
		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : CPTWI_VERSION;
		/**
		 * Admin scripts.
		 */
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'backend_assets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'frontend_assets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'inline_css_frontend' );
	}
	/**
	 * Frontend Script
	 */
	public function frontend_assets() {
		$styles = [
			[
				'handle' => 'cptwooint-public',
				'src'    => cptwooint()->get_assets_uri( 'css/frontend/frontend.css' ),
			],
		];

		// Register public styles.
		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		$post_type            = get_post_type( get_queried_object_id() );
		$wc_script_permission = true;
		if ( $wc_script_permission ) {
			WC_Frontend_Scripts::init();
		}

		if ( is_single() && Fns::is_supported( $post_type ) && $wc_script_permission ) {

			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				wp_enqueue_script( 'zoom' );
			}

			if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
				wp_enqueue_script( 'flexslider' );
			}

			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
				wp_enqueue_script( 'photoswipe-ui-default' );
				wp_enqueue_style( 'photoswipe-default-skin' );

			}
			wp_enqueue_script( 'wc-single-product' );

		}

		if ( Fns::is_supported( $post_type ) ) {
			do_action( 'cptwooint_supported_post_type_frontend_assets', $post_type );
		}

		if ( ( is_single() && Fns::is_supported( get_the_ID() ) ) || ( is_archive() && get_post_type( get_queried_object_id() ) ) ) {
			wp_enqueue_style( 'cptwooint-public' );
		}
		if ( is_single() && Fns::is_supported( get_the_ID() ) ) {
			add_action( 'wp_footer', 'woocommerce_photoswipe' );
		}
	}
	/**
	 * Registers Admin scripts.
	 *
	 * @return void
	 */
	public function backend_assets( $hook ) {

		$styles = [
			[
				'handle' => 'cptwooint-settings',
				'src'    => cptwooint()->get_assets_uri( 'css/backend/admin-settings.css' ),
			],
		];

		// Register public styles.
		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		$scripts = [
			[
				'handle' => 'cptwooint-settings',
				'src'    => cptwooint()->get_assets_uri( 'js/backend/admin-settings.js' ),
				'deps'   => [],
				'footer' => true,
			],
			[
				'handle' => 'cptwooint-metabox-scripts',
				'src'    => cptwooint()->get_assets_uri( 'js/backend/cptwooint-metabox-scripts.js' ),
				'deps'   => [],
				'footer' => true,
			],
		];

		// Register public scripts.
		foreach ( $scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->version, $script['footer'] );
		}

		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'admin.php' === $pagenow && 'cptwooint-admin' === sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ) ) {
			// Enqueue ThickBox scripts and styles.
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'cptwooint-settings' );
			wp_enqueue_script( 'cptwooint-settings' );

			// WPml Create Issue.
			wp_dequeue_style( 'wpml-tm-styles' );

			wp_localize_script(
				'cptwooint-settings',
				'cptwoointParams',
				[
					'adminUrl'           => esc_url( admin_url() ),
					'restApiUrl'         => esc_url_raw( rest_url() ),
					'hasExtended'        => cptwooint()->has_pro(),
					'proFeature'         => wp_json_encode( Fns::pro_feature_list() ),
					'ajaxUrl'            => esc_url( admin_url( 'admin-ajax.php' ) ),
					'rest_nonce'         => wp_create_nonce( 'wp_rest' ),
					'proLink'            => cptwooint()->pro_version_link(),
					cptwooint()->nonceId => wp_create_nonce( cptwooint()->nonceId ),

				]
			);

		}
	}

	/**
	 * @return void
	 */
	public function inline_css_frontend() {
		$options               = Fns::get_options();
		$style                 = $options['style'] ?? [];
		$field_gap             = trim( $style['fieldGap'] ?? '' );
		$field_width           = trim( $style['fieldWidth'] ?? '' );
		$field_height          = trim( $style['fieldHeight'] ?? '' );
		$button_width          = trim( $style['buttonWidth'] ?? '' );
		$button_color          = trim( $style['buttonColor'] ?? '' );
		$button_bg_color       = trim( $style['buttonBgColor'] ?? '' );
		$button_hover_color    = trim( $style['buttonHoverColor'] ?? '' );
		$button_hover_hg_color = trim( $style['buttonHoverBgColor'] ?? '' );

		ob_start();
		if ( ! empty( $field_width ) ) {
			?>
			width: <?php echo absint( $field_width ); ?>px;
			<?php
		}
		if ( ! empty( $field_height ) ) {
			?>
			height: <?php echo absint( $field_height ); ?>px;
			<?php
		}
		$field_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );

		ob_start();
		?>
		<?php if ( ! empty( $button_width ) ) { ?>
			width: <?php echo absint( $button_width ); ?>px;
		<?php } ?>
		<?php if ( ! empty( $field_height ) ) { ?>
			height: <?php echo absint( $field_height ); ?>px;
		<?php } ?>
		<?php if ( ! empty( $button_color ) ) { ?>
			color: <?php echo esc_html( $button_color ); ?>;
			<?php
		}
		if ( ! empty( $button_bg_color ) ) {
			?>
			background-color: <?php echo esc_html( $button_bg_color ); ?>;
			border-color: <?php echo esc_html( $button_bg_color ); ?>;
			<?php
		}
		$button_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );
		ob_start();
		?>
		<?php if ( ! empty( $button_hover_color ) ) { ?>
			color: <?php echo esc_html( $button_hover_color ); ?>;
		<?php } ?>
		<?php if ( ! empty( $button_hover_hg_color ) ) { ?>
			background-color: <?php echo esc_html( $button_hover_hg_color ); ?>;
			border-color: <?php echo esc_html( $button_hover_hg_color ); ?>;
			<?php
		}
		$button_hover_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );
		ob_start();
		?>
		<?php if ( ! empty( $field_gap ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart{
			gap: <?php echo absint( $field_gap ); ?>px;
			}
		<?php } ?>
		
		<?php if ( ! empty( $field_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart input[type="number"],
			.cptwooint-cart-btn-wrapper .cart input[type="number"] {
			box-sizing: border-box;
			padding: 7px 15px;
			border: 1px solid;
			<?php echo esc_html( $field_style ); ?>
			}
		<?php } ?>
		
		<?php if ( ! empty( $button_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart .button {
			box-sizing: border-box;
			padding: 5px 10px;
			transition: 0.3s all;
			cursor: pointer;
			border: 1px solid;
			<?php echo esc_html( $button_style ); ?>
			}
		<?php } ?>
		
		<?php if ( ! empty( $button_hover_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart .button:focus,
			.cptwooint-cart-btn-wrapper .cart .button:hover {
			<?php echo esc_html( $button_hover_style ); ?>
			}
		<?php } ?>
		
		<?php
		$generated_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );
		if ( ! empty( $generated_style ) ) {
			wp_add_inline_style( 'cptwooint-public', $generated_style );
		}
	}
}
