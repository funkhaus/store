<?php 

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get setup
	include_once( trailingslashit( pp() ) . 'core/store-setup.php' );

	// Get functions
	include_once( trailingslashit( pp() ) . 'core/store-product-functions.php' );

	// Setup product post types
	include_once( trailingslashit( pp() ) . 'core/store-post-types.php' );

	// Setup taxonomies
	include_once( trailingslashit( pp() ) . 'core/store-taxonomy.php' );

	// Hook saving functionality
	include_once( trailingslashit( pp() ) . 'core/store-save-products.php' );
	
	// Setup cart functions
	include_once( trailingslashit( pp() ) . 'core/store-cart-functions.php' );	
	
	// Add cart AJAX functions
	include_once( trailingslashit( pp() ) . 'core/store-ajax-api.php' );		
	
	// Setup user meta functions
	include_once( trailingslashit( pp() ) . 'core/store-user-meta.php' );			
	
	// Setup store conditionals functions
	include_once( trailingslashit( pp() ) . 'core/store-conditionals.php' );				

	/*
	 * Enqueue JavaScript API Scripts
	 */
		function store_api_scripts() {
			wp_register_script( 'store_api_js', plugins_url( 'core/js/store.api.js', dirname(__FILE__) ));
			wp_enqueue_script( 'store_api_js');

	        // Setup JS variables in scripts
			wp_localize_script('store_api_js', 'store_api_vars', 
				array(
					'homeURL'	=> home_url(),
					'ajaxURL'	=> admin_url( 'admin-ajax.php' )
				)
			);

		}
		add_action( 'wp_enqueue_scripts', 'store_api_scripts' );	

?>