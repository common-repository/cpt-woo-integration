<?php

namespace TinySolutions\cptwooint\Controllers\Admin;

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Loader;
use TinySolutions\cptwooint\Traits\SingletonTrait;

/**
 * @var string
 */
class Api {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * @var string
	 */
	private $namespace = 'TinySolutions/cptwooint/v1';
	/**
	 * @var string
	 */
	private $resource_name = '/cptwooint';

	/**
	 * @var object
	 */
	protected $loader;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		$this->loader = Loader::instance();
		$this->loader->add_action( 'rest_api_init', $this, 'register_routes' );
	}

	/**
	 * Register our routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getOptions',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_options' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/updateOptions',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_option' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getPostTypes',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_post_types' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getPostMetas',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_post_metas' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getPluginList',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_plugin_list' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name . '/clearCache',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'clear_data_cache' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
	}

	/**
	 * @return true
	 */
	public function login_permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @return false|string
	 */
	public function get_plugin_list() {
		// Define a unique key for the transient.
		$transient_key = 'get_plugin_list_use_cache_' . CPTWI_VERSION;
		// Try to get the cached data.
		$cached_data = get_transient( $transient_key );
		if ( ! empty( $cached_data ) ) {
			$is_empty = json_decode( $cached_data, true );
			// Return the cached data if it exists.
			if ( ! empty( $is_empty ) ) {
				return $cached_data;
			}
		}
		// Initialize the result array.
		$result = [];
		try {
			// Fetch data from the API.
			$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=tinysolution' );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$responseBody = json_decode( $response['body'], true );
				if ( json_last_error() === JSON_ERROR_NONE && is_array( $responseBody['plugins'] ) ) {
					foreach ( $responseBody['plugins'] as $plugin ) {
						$result[] = [
							'plugin_name'       => $plugin['name'],
							'slug'              => $plugin['slug'],
							'author'            => $plugin['author'],
							'homepage'          => $plugin['homepage'],
							'download_link'     => $plugin['download_link'],
							'author_profile'    => $plugin['author_profile'],
							'icons'             => $plugin['icons'],
							'short_description' => $plugin['short_description'],
							'TB_iframe'         => esc_url( self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin['slug'] . '&TB_iframe=true&width=772&height=700' ) ),
						];
					}
				}
			}
		} catch ( \Exception $ex ) {
			// Handle exception (optional logging or error handling can be added here).
		}

		// Encode the result to JSON.
		$json_result = wp_json_encode( $result );

		// Cache the result for 1 day (24 hours * 60 minutes * 60 seconds).
		set_transient( $transient_key, $json_result, 7 * DAY_IN_SECONDS );

		return $json_result;
	}


	/**
	 * @return true
	 */
	public function clear_data_cache() {
		$result            = [
			'updated' => false,
			'message' => esc_html__( 'Action Failed ', 'cptwooint' ),
		];
		$result['updated'] = Fns::clear_data_cache();
		if ( $result['updated'] ) {
			$result['message'] = esc_html__( 'Cache Cleared.', 'cptwooint' );
		}

		return $result;
	}

	/**
	 * @return false|string
	 */
	public function update_option( $request_data ) {

		$result = [
			'updated' => false,
			'message' => esc_html__( 'Update failed. Maybe change not found. ', 'cptwooint' ),
		];

		$parameters = $request_data->get_params();

		$the_settings = get_option( 'cptwooint_settings', [] );

		//$the_settings['price_position'] = sanitize_text_field( $parameters['price_position'] ?? '' );

		$the_settings['price_after_content_post_types'] = array_map( 'sanitize_text_field', $parameters['price_after_content_post_types'] ?? [] );

		$the_settings['cart_button_position'] = sanitize_text_field( $parameters['cart_button_position'] ?? '' );

		$the_settings['cart_button_after_content_post_types'] = array_map( 'sanitize_text_field', $parameters['cart_button_after_content_post_types'] ?? [] );

		$the_settings['selected_post_types'] = $parameters['selected_post_types'] ?? []; // Multi label Array No need sanitization.

		$the_settings['default_price_meta_field'] = array_map( 'sanitize_text_field', $parameters['default_price_meta_field'] ?? [] );

		$the_settings['show_shortdesc_meta'] = array_map( 'sanitize_text_field', $parameters['show_shortdesc_meta'] ?? [] );

		$the_settings['show_gallery_meta'] = array_map( 'sanitize_text_field', $parameters['show_gallery_meta'] ?? [] );

		$the_settings['archive_similar_shop_page'] = array_map( 'sanitize_text_field', $parameters['archive_similar_shop_page'] ?? [] );

		$the_settings['details_similar_product_page'] = array_map( 'sanitize_text_field', $parameters['details_similar_product_page'] ?? [] );

		$the_settings['enable_product_review'] = array_map( 'sanitize_text_field', $parameters['enable_product_review'] ?? [] );

		$the_settings['enable_product_schema'] = array_map( 'sanitize_text_field', $parameters['enable_product_schema'] ?? [] );

		$the_settings['enable_post_for_shop_page'] = array_map( 'sanitize_text_field', $parameters['enable_post_for_shop_page'] ?? [] );

		$styles                = $parameters['style'] ?? [];
		$the_settings['style'] = [];
		if ( is_array( $styles ) ) {
			foreach ( $styles as $key => $value ) {
				if ( ! empty( $key ) ) {
					$the_settings['style'][ $key ] = sanitize_text_field( $value );
				}
			}
		}

		$options = update_option( 'cptwooint_settings', $the_settings );

		$result['updated'] = boolval( $options );

		if ( $result['updated'] ) {
			$result['message'] = esc_html__( 'Updated.', 'cptwooint' );
		}

		return $result;
	}

	/**
	 * @return false|string
	 */
	public function get_options() {
		$options = Fns::get_options();
		return wp_json_encode( $options );
	}

	/**
	 * @return false|string
	 */
	public function get_post_types() {
		// Get all meta keys saved in posts of the specified post type.
		$cpt_args        = [
			'public'   => true,
			'_builtin' => false,
		];
		$post_types      = get_post_types( $cpt_args, 'objects' );
		$post_type_array = apply_filters(
			'cptwooint_post_types',
			[
				[
					'value' => 'post',
					'label' => 'Posts',
				],
				[
					'value' => 'page',
					'label' => 'Page',
				],
			]
		);

		// BabeInit tripfery theme support.

		if ( class_exists( 'BABE_Order' ) && ! class_exists( 'BabeInit' ) ) {
			$post_type_array[] = [
				'value' => 'order',
				'label' => 'Order ( BA Book Everything )',
			];
		}

		foreach ( $post_types as $key => $post_type ) {
			if ( 'product' === $key ) {
				continue;
			}
			$post_type_array[] = [
				'value' => $post_type->name,
				'label' => $post_type->label,
			];
		}

		return wp_json_encode( $post_type_array );
	}

	/**
	 * @return false|string
	 */
	public function get_post_metas( $request_data ) {

		$parameters = $request_data->get_params();
		$meta_keys  = [];
		if ( ! empty( $parameters['post_type'] ) ) {
			$post_type = $parameters['post_type'];
			// Get all meta keys saved in posts of the specified post type.
			$cache_key = 'cptwooint_meta_query_' . $post_type;
			// Removed Cache.

			$meta_keys = wp_cache_get( $cache_key );
			if ( false === $meta_keys ) {
				global $wpdb;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$meta_keys = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT meta_key
					FROM $wpdb->postmeta
					INNER JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
					WHERE $wpdb->posts.post_type = %s",
						$post_type
					)
				);
				wp_cache_set( $cache_key, $meta_keys, '', HOUR_IN_SECONDS );
			}
		}

		$the_metas = [];
		if ( ! empty( $meta_keys ) ) {
			$remove_wp_default = [
				'_pingme',
				'_edit_last',
				'_encloseme',
				'_edit_lock',
				'_sale_price',
				'_regular_price',
				'_wp_page_template',
				'total_sales',
				'_tax_status',
				'_tax_class',
				'_manage_stock',
				'_backorders',
				'_sold_individually',
				'_virtual',
				'_downloadable',
				'_download_limit',
				'_download_expiry',
				'_sku',
				'_stock',
				'_price',
				'_stock_status',
				'_wc_average_rating',
				'_wc_review_count',
				'_product_attributes',
				'_product_version',
				'_wc_rating_count',
				'_thumbnail_id',
				'_product_image_gallery',
				'_wp_trash_meta_status',
				'_wp_trash_meta_time',
				'_wp_desired_post_slug',
				'_wp_trash_meta_comments_status',
			];
			foreach ( $meta_keys as $result ) {
				if ( in_array( $result->meta_key, $remove_wp_default, true ) ) {
					continue;
				}
				$the_metas[] = [
					'value' => $result->meta_key,
					'label' => $result->meta_key,
				];
			}
		}

		return wp_json_encode( $the_metas );
	}
}
