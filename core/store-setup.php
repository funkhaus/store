<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	/*
	 * General function to run on activation,
	 * sets up necessary options, pages, etc.
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

			// If no checkout page, create one
			$args['name'] = 'checkout';
			if ( ! get_posts($args) ) {

				$post['post_name'] = 'checkout';
				$post['post_title'] = 'checkout';
				wp_insert_post( $post );

			}

			// If no cart page, create one
			$args['name'] = 'cart';
			if ( ! get_posts($args) ) {

				$post['post_name'] = 'cart';
				$post['post_title'] = 'cart';
				wp_insert_post( $post );

			}

			// If no api page, create one
			$args['name'] = 'api';
			if ( ! get_posts($args) ) {

				$post['post_name'] = 'api';
				$post['post_title'] = 'api';
				wp_insert_post( $post );

			}

			// Flush permalinks
		    flush_rewrite_rules();

		}
		register_activation_hook( trailingslashit( pp() ) . 'store.php', 'store_activation' );

?>