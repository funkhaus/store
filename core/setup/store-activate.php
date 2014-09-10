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
			foreach ( array( 'checkout', 'cart', 'api' ) as $store_page ) {

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

		}
		register_activation_hook( trailingslashit( pp() ) . 'store.php', 'store_activation' );

?>