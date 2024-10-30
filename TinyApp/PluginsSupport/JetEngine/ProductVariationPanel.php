<?php

namespace TinySolutions\cptwooint\PluginsSupport\JetEngine;

// Do not allow directly accessing this file.
use Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package\Meta_Boxes;
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
class ProductVariationPanel extends Product_Variation_Panel {
	
	/**
	 * @param $hook
	 * @return bool|mixed|null
	 */
	public function is_allowed_on_current_admin_hook( $hook ) {
		
		if ( ! Fns::is_supported( get_post_type() ) ) {
			return parent::is_allowed_on_current_admin_hook( $hook );
		}
		
		if ( null !== $this->is_allowed_on_admin_hook ) {
			return $this->is_allowed_on_admin_hook;
		}
		$allowed_hooks = [
			'post-new.php',
			'post.php',
		];
		if ( ! in_array( $hook, $allowed_hooks ) ) {
			$this->is_allowed_on_admin_hook = false;
			return $this->is_allowed_on_admin_hook;
		}
		$this->is_allowed_on_admin_hook = true;
		return $this->is_allowed_on_admin_hook;
	}
}
