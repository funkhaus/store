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
		$cart = store_get_cart($cart_id);
		
		if( is_object($cart) ) {
			if( $cart->post_status !== 'trash' ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
?>