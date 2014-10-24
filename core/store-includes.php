<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get functions
	include_once( pp() . 'core/functions/store-core-functions.php' );

	// Testing function to run things. Delete Me eventually.
	function store_test_things(){

		$address = array(
			'line_1'	=> '1855 Industrial St.',
			'line_2'	=> 'Suite 103',
			'city'		=> 'Los Angeles',
			'state'		=> 'ca',
			'zip'		=> '90021',
			'country'	=> 'us'
		);

		//store_save_customer_address($address, null, true, true);
	}
	add_action('init', 'store_test_things', 60);

	// Setup product post types
	include_once( pp() . 'core/setup/store-post-types.php' );

	// Setup taxonomies
	include_once( pp() . 'core/setup/store-taxonomy.php' );

	// Run activation functions
	include_once( pp() . 'core/setup/store-activate.php' );

	// Include term meta plugin
	include_once( pp() . 'core/plugins/simple-term-meta.php' );

	// Load stripe classes
	include_once( pp() . 'core/includes/stripe-php/Stripe.php' );

	// Hook saving functionality
	include_once( pp() . 'core/store-save-products.php' );

	// Add cart AJAX functions
	include_once( pp() . 'core/apis/store-ajax-api.php' );

	// Add js product matrix
	include_once( pp() . 'core/store-product-matrix.php' );

	// Add shipwire AJAX functions
	include_once( pp() . 'core/apis/store-shipwire-api.php' );

	// Add functions for init
	include_once( pp() . 'core/store-init.php' );

	// Add stripe setup AJAX functions
	include_once( pp() . 'core/apis/store-stripe-api.php' );

	// Setup user meta functions
	include_once( pp() . 'core/store-customer-meta.php' );

	// Set crons
	include_once( pp() . 'core/store-set-crons.php' );

	/*
	 * Enqueue JavaScript API Scripts
	 */
		function store_api_scripts() {
			wp_register_script( 'store_api_js', plugins_url( 'core/js/store.api.js', dirname(__FILE__) ));
			wp_enqueue_script( 'store_api_js');

			// Setup JS variables in scripts
			wp_localize_script('store_api_js', 'store_api_vars',
				array(
					'homeURL'		=> home_url(),
					'ajaxURL'		=> admin_url( 'admin-ajax.php' ),
					'pluginURL'		=> pp()
				)
			);

		}
		add_action( 'wp_enqueue_scripts', 'store_api_scripts' );

?>