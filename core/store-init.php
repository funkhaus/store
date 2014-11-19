<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// filter main archive
	function store_filter_main_archive($query) {
		if ( $query->is_main_query() && $query->is_post_type_archive('store') && ! is_admin() ) {
			$query->set('post_type', 'product');
			$query->set('post_parent', 0);
			$query->set('orderby', 'menu_order');
			$query->set('order', 'ASC');
		}
	}
	add_action('pre_get_posts', 'store_filter_main_archive');


	// Set redirects for files within store directory
	function store_set_template_redirects( $template ) {
		global $post;

		if ( is_post_type_archive( 'product' )  ) {
			$new_template = locate_template( array( 'store/store-front-page.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		} elseif ( is_single() && get_post_type() === 'store' ) {
			$new_template = locate_template( array( 'store/store-page-' . $post->post_name . '.php' ) );
			if ( '' != $new_template ) {
				return $new_template;
			}

		} elseif ( is_single() && get_post_type() === 'product' ) {
			$new_template = locate_template( array( 'store/store-product-' . $post->post_name . '.php' ) );
			if ( ! $new_template ) $new_template = locate_template( array( 'store/store-product.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		} elseif ( is_single() && get_post_type() === 'orders' ) {
			$new_template = locate_template( array( 'store/store-order-' . $post->ID . '.php' ) );
			if ( ! $new_template ) $new_template = locate_template( array( 'store/store-order.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		}

		return $template;
	}
	add_filter( 'template_include', 'store_set_template_redirects', 99 );


	// if user is not authorized to view order, redirect them
	function store_protect_orders(){
		global $post;

		// only for orders pages
		if ( $post->post_type === 'orders' ) {
			$transaction_info = get_post_meta($post->ID, '_store_transaction_info', true);

			if( $post->post_author == get_current_user_id() ) return;
			if ( $_REQUEST['token'] === $transaction_info['token'] ) return;

			wp_redirect( home_url('/store/') );
			exit();

		}
	}
	add_action( 'template_redirect', 'store_protect_orders' );


	// transfer newest cart
	function store_transfer_cart($user_login, $user) {

		if( isset($_COOKIE['store_active_cart_id']) ) {

			// check for cookie cart and user cart
			$cookie_cart = $_COOKIE['store_active_cart_id'];
			$user_cart = get_user_meta( $user->ID, '_store_active_cart_id', true );

			// get full cart object
			$cookie_cart = get_post($cookie_cart);

			if ( $cookie_cart->post_author !== $user->ID ) {
				$cookie_cart->post_author = $user->ID;
				wp_update_post($cookie_cart);
			}

			// User has cart
			if ( $user_cart ) {

				$user_cart = get_post($user_cart);

				// check if cookie cart is newer...
				if ( strtotime( $cookie_cart->post_modified_gmt ) > strtotime( $user_cart->post_modified_gmt ) ) {

					// if so, overwrite user cart
					update_user_meta( $user->ID, '_store_active_cart_id', $cookie_cart->ID);

				// User cart is newer...
				} else {

					//overwrite cookie cart
					setcookie('store_active_cart_id', $user_cart->ID, time()+3600*24*30, '/', store_get_cookie_url(), false);  /* expire in 30 days */

				}

			// User does not have cart
			} else {

				// Set cookie as user cart
				update_user_meta( $user->ID, '_store_active_cart_id', $cookie_cart->ID);

			}
		}

	}
	add_action('wp_login', 'store_transfer_cart', 10, 2);


	// Set store-specific body classes
	function store_add_bodyclasses($classes) {

		// if you are within the store hierarchy, add class to body
		if ( store_is_within_store() ) $classes[] = 'store';

		// if this is the product archive, add class to product
		if ( is_post_type_archive( 'product' ) ) $classes[] = 'store-front-page';

		// if this page is in the 'store' category, give it the store-page class
		if ( get_post_type() === 'store' ) $classes[] = 'store-page';

		// if this is a single product
		if ( get_post_type() === 'product' && is_single() ) $classes[] = 'store-product';

		return $classes;
	}
	add_filter('body_class', 'store_add_bodyclasses');


	// Send confirmation email if template is set
	function store_send_order_confirmation($order_id){

		// get full post object
		$order = get_post($order_id);

		// get full user object, then email
		$userdata = get_userdata( $order->post_author );
		$user_email = $userdata->data->user_email;

		// if template has been set in the theme
		if ( $path = locate_template('store/template-order-confirmation.php') ) {

			// start buffer
			ob_start();

			// include template
			include($path);

			// load result into variable
			$returned = ob_get_contents();

			// clear buffer
			ob_end_clean();

			// send email
			$headers = 'From: What Youth <hello@whatyouth.com>' . "\r\n";
			$headers .= 'Content-type: text/html' . "\r\n";
			wp_mail( $user_email, 'Thank You For Your Order', $returned, $headers );
			wp_mail( 'john@funkhaus.us', 'Thank You For Your Order', $returned, $headers );

		}

	}
	add_action('store_order_completed', 'store_send_order_confirmation');

?>