<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	/*
	 * General function to run on activation,
	 * sets up necessary options, pages, roles, etc.
	 */
		function store_activation() {

			// Set template for query
			$args = array(
				'posts_per_page'	=> 1,
				'post_type'			=> 'store'
			);

			// Set template for post creation
			$post = array(
			  'post_status'    => 'publish',
			  'post_type'      => 'store',
			  'ping_status'    => 'closed',
			  'comment_status' => 'closed'
			);  

			// Create necessary store pages
			foreach ( array( 'checkout', 'cart', 'sign in', 'sign up', 'my account', 'thank you' ) as $store_page ) {

				// If page does not exist, create it
				$args['name'] = $store_page;
				if ( ! get_posts($args) ) {

					$post['post_name'] = $store_page;
					$post['post_title'] = $store_page;
					wp_insert_post( $post );

				}

			}

			// Make sure taxonomy is registered
			store_create_taxonomy_status();

			// Create order statuses
			foreach ( array('Active', 'Inactive', 'Submitted', 'Paid', 'Processed', 'Shipped', 'Refunded', 'Void') as $store_status ) {

				// Make slug from name
				$slug = sanitize_title($store_status);

				// If term does not exist...
				if ( ! get_term_by( 'slug', $slug, 'store_status') ) {

					// Create term
					wp_insert_term( $store_status, 'store_status' );

				}

			}

			// Add custom user role for customers
			add_role(
				'store_customer',
				__( 'Customer' ),
				array(
					'read'         		=> true,
					'edit_posts'   		=> false,
					'delete_posts' 		=> false
				)
			);

			// Flush permalinks
		    flush_rewrite_rules();

			// Create initial guest user
			store_create_guest_user();

		}
		register_activation_hook( pp() . 'store.php', 'store_activation' );


		// Initially create the guest user for this install
		function store_create_guest_user(){

			// split home url
			$url_split = parse_url( home_url() );

			// set guest email
			$guest_email = 'guest@' . $url_split['host'];

			// check if user with that email exists
			$existing_user = get_user_by( 'email', $guest_email );

			// if no existing guest user...
			if ( ! $existing_user ) {

			    // Create store guest user here
				$userdata = array(
					'user_login'	=> $guest_email,
					'user_email'	=> $guest_email,
					'user_nicename'	=> 'Store Guest',
					'role'			=> 'store_customer',
					'description'	=> 'all guest purchases for the store will be made by this user',
					'user_pass'		=> store_get_random_password(10)
				);
				wp_insert_user( $userdata );

			}
		}

?>