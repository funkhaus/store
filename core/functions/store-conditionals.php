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

		if ( is_search() ) $output = false;

		return $output;
	}


/*
 * @Description: Used to check if a given page is the store front page
 *
 * @Param: MIXED, ID or post object
 * @Returns: BOOL, true if is front page, otherwise false
 */
	function store_is_front_page( $post = null ){
		$post = get_post($post);

		return (is_post_type_archive( 'product' ) && store_is_within_store());
	}


/*
 * @Description: check if a given product is a variant
 *
 * @Param: MIXED, ID or full object of a product. Defaults to $post. Optional
 * @Returns: BOOL, true if a variant is given, false if not
 */
 	function store_is_variant( $product = null ){

	 	// get full post object, or $post
	 	$product = get_post($product);

	 	// init output
	 	$output = false;

	 	// if is a product and has a parent, it's a variant
	 	if ( $product->post_type === 'product' && $product->post_parent !== 0 ) $output = true;

	 	return $output;
 	}


/*
 * @Description: check if a given product has variants
 *
 * @Param: MIXED, ID or full object of a product. Defaults to $post. Optional
 * @Returns: BOOL, true if product has variants. false if not
 */
    function store_has_variants($product = null) {

	 	// get full post object, or $post
	 	$product = store_get_product($product);
	 	$output = false;

	 	$args = array(
	 		'post_parent' 		=> $product->ID,
	 		'post_type'			=> 'product',
	 		'posts_per_page'	=> 1,
	 		'fields'			=> 'ids'
	 	);
	 	$variants = get_posts($args);

	 	if( count( $variants ) !== 0 ) $output = true;
	 	return $output;
    }

?>