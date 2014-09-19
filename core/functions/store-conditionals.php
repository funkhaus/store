<?php
/*
 * @Description: Make sure a product is available to purchase. Checks quantity available and any other flags.
 *
 * @Param: MIXED, can be an ID or post object of product to retrieve variants of. If not set, attempts to get current ID from global $post.
 */
	function store_is_product_available( $product = false ){

		// Get Product post
		if ( is_int( intval($product) ) ) {
			// If ID was passed, get full object
			$product = get_post($product);

		} elseif( is_object($product) ) {
			// Assume post object passed
			$product = $product;

		} else {
			// Try to get from global
			global $post;
			$product = $post;

		}

		// Check that we have an object
		if( !is_object($product) ) {
			return false;

		}

		// Check for quantity 
		if( $product->_store_qty > 0 ) {
			return true;
		} else {
			return false;
		}

	}


/*
 * @Description: Make sure a cart hasn't been deleted
 *
 * @Param: INT, cart ID to test. Required.
 */
	function store_is_cart_available( $cart_id ){

		// get full post object
		$cart = get_post($cart_id);

		// init output
		$output = true;

		// Set false if is not an object, not a cart, or is in trash
		if( ! is_object($cart) || $cart->post_type !== 'cart' || $cart->post_status === 'trash' ) $output = false;

		return $output;
	}


/*
 * @Description: Used to check if the current page is within the whole store umbrella (products, checkout, archives, etc.)
 *
 * @Param:
 */
	function store_is_within_store( $post = null ){
		$post = get_post($post);

		// init output
		$output = false;

		// if post is any of these post types, output true
		if (
			get_post_type($post) === 'product'	||
			get_post_type($post) === 'orders'	|| 
			get_post_type($post) === 'cart'		|| 
			get_post_type($post) === 'store'

		) { $output = true; }

		return $output;
	}

?>