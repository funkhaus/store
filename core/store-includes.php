<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-core-functions.php' );



	function jrr_create_customer(){

		//store_create_customer('john@funkhaus.us', 'funky-house');
		$post = array(
		  'post_content'   => '',
		  'post_name'      => 'test',
		  'post_title'     => 'test',
		  'post_status'    => 'publish',
		);

		wp_insert_post($post);
	}
	//add_action('init', 'jrr_create_customer');

	function jrr_get_userdata(){
		$user = get_user_by('email', 'john@funkhaus.us');
		var_dump( $user ); exit;
	}
	//add_action('init', 'jrr_get_userdata');



	function jrr_create_address(){

		$post = array(
			'post_name'      => 'address',
			'post_title'     => 'address',
			'post_status'    => 'publish',
			'post_type'      => 'address'
		);

		$created_id = wp_insert_post( $post );

		if ( $created_id ) {

			$post['ID'] = $created_id;
			$post['post_name'] = 'Address #' . $created_id;
			$post['post_title'] = 'Address #' . $created_id;

			wp_update_post( $post );

		}

	}
	//add_action('init', 'jrr_create_address');



	// Setup product post types
	include_once( trailingslashit( pp() ) . 'core/setup/store-post-types.php' );

	// Setup taxonomies
	include_once( trailingslashit( pp() ) . 'core/setup/store-taxonomy.php' );

	// Run activation functions
	include_once( trailingslashit( pp() ) . 'core/setup/store-activate.php' );

	// Hook saving functionality
	include_once( trailingslashit( pp() ) . 'core/store-save-products.php' );

	// Add cart AJAX functions
	include_once( trailingslashit( pp() ) . 'core/store-ajax-api.php' );

	// Setup user meta functions
	include_once( trailingslashit( pp() ) . 'core/store-user-meta.php' );

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