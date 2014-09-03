<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * @Description: Helper function used to return the calculated inventory quantity of a post based on its' variations
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_quantity( $product = false ){

		// Get product object
		$product = store_get_product( $product );

		// If this is a variation, return qty
		if ( $product->post_parent ) return $product->_store_qty;

		// query children
	    $args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'product',
			'post_parent'      => $product->ID,
			'post_status'      => 'any'
		);
		$variations = get_posts($args);

		$quantity = false;
		if ( $variations ) {

			$quantity = 0;

			// Loop through children and add qtys
			foreach ( $variations as $variation ) {

				$quantity += intval( $variation->_store_qty );

			}

		}

		return $quantity;
	};


/*
 * @Description: Get qty 
 *
 * @Param: MIXED, ID or object of cart to get qty for, defaults to current. Optional
 * @Param: MIXED, ID or object of product to get qty for, Defaults to $post. Optional
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_cart_quantity( $cart = null, $product = null ){

		// Get cart and product objects
		$cart = store_get_cart( $cart );
		$product = store_get_product( $product );

		// if either are empty, abort
		if ( ! $product || ! $cart ) return false;

		// Set qty
		$qty = false;

		// If product is in cart, set qty to cart value
		if ( $items = store_get_cart_items($cart) ) {
			if ( isset($items[$product->ID]) ) $qty = $items[$product->ID];
		}

		return $qty;
	};


/*
 * @Description: get the SKU of any given product
 *
 * @Param: MIXED, ID or object of product to get SKU of. If no product, $post will be used.
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_sku( $product = null ){

		$product = store_get_product( $product );

		return get_post_meta( $product->ID, '_store_sku', true );

	};


/*
 * @Description: get the price of a single product
 *
 * @Param: MIXED, ID or object of product to get price of. If no product, $post will be used.
 * @Returns: MIXED, integer value of price in cents on success, bool false on failure
 */
	function store_get_product_price( $product = null ){

		// get full product object
		$product = store_get_product( $product );

		// get price from meta
		$price = get_post_meta( $product->ID, '_store_price', true );

		// format number into cents
		$price = intval( (int) $price * 100);

		// If price is falsey, set to false
		if ( ! $price ) $price = false;

		return $price;

	};


/*
 * @Description: Get all variants of a product
 *
 * @Param: MIXED, can be an ID or post object of product to retrieve variants of. If not set, attempts to get current ID from global $post.
 * @Returns: MIXED, can be an array of post objects for each variant or false for no varients. Uses get_posts().
 */
	function store_get_product_variants( $product = false ){

		// Get valid product object
		$product = store_get_product( $product );

		// No product? abort
		if ( ! $product ) return false;

		// Get the proper parent ID value
		if( $product->post_parent !== 0 ) {
			// Product not top level, so this must be a vareint, get siblings
			$parent_id = $product->post_parent;
		} else {
			// Must be a top level product, attempt to get children
			$parent_id = $product->ID;
		}

		// Get any variants and return results
		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'post_type'        => 'product', // We could make the post type a setting that could be changed/filtered?
			'post_parent'      => $parent_id,
			'post_status'      => 'publish'
		);
		$products = get_posts($args);

		// We could loop through each product here an add any extra data we need to
		// if we did, we should do it as a seperate function that takes a get_posts() array

		// Return results of get_posts();
		return $products;

	};


/*
 * @Description: Get an array of custom options that have been set for this product
 *
 * @Param: MIXED, ID or object of product (or variant) to get keys for. Defaults to $post. Optional
 * @Returns: MIXED, Array of keys on success, or false on failure
 */
 	function store_get_product_option_keys( $product = null ){

	 	// Get full post object
	 	$product = store_get_product( $product );

	 	// Make sure this is a top-level product
	 	if ( $product->post_parent !== 0 ) $product = get_post($product->post_parent);

	 	// Get all meta for this product
	 	$meta = get_post_meta($product->ID);

	 	// Extract valid option keys from meta
	 	$keys = store_sort_options($meta);

	 	return $keys;
 	}


/*
 * @Description: Get array of options for a given product
 *
 * @Param: MIXED, ID or object of product (or variant) to get options for. Defaults to $post. Optional
 * @Returns: MIXED, Array of options on success, false on failure
 */
 	function store_get_options( $product = null ){

	 	// Set output
	 	$output = array();

	 	// Get all option key => value pairs
	 	$keys = store_get_product_option_keys($product);

	 	// Loop through keys
	 	foreach ( $keys as $key => $value ) {

		 	// format key to be readable
		 	$key = store_format_option_key($key);

		 	// Set key to be array of options
		 	$output[$key] = explode(', ', $value);

	 	}

	 	return $output;
 	}


/*
 * @Description: get the full post object of a variant based on options provided
 *
 * @Param: ARRAY, associative array of options for the target variant. Required.
 * @Param: MIXED, ID or object of the parent product. If none provided $post will be used.
 * @Returns: MIXED, full post object of matching variant on success, or false on failure
 */
 	function store_get_variant_id( $options = null, $product = null ){

	 	// Get full product object
	 	$product = store_get_product($product);

	 	// enforce requirements
	 	if ( ! is_array($options) || ! $product ) return false;

	 	// Set meta query
	 	$meta_query = array('relation' => 'AND');
	 	foreach ( $options as $key => $val ) {
		 	$meta_query[] = array(
		 		'key'		=>	'_store_meta_' . $key,
		 		'value'		=> $val,
		 		'compare'	=> '='
		 	);
		}

	 	// set args
 	    $args = array(
 			'posts_per_page'	=> 1,
 			'meta_query'		=> $meta_query,
 			'post_type'			=> 'product',
 			'post_parent'		=> $product->ID,
 			'feilds'			=> 'id'
 		);

 		// query for variant
 		$results = get_posts($args);

 		// if nothing came back, return false
 		if ( empty($results) )
 			return false;

 		// return first (and only) result
 		return reset($results);
 	}


/*
 * @Description: Get product object, use $post as default
 *
 * @Param: MIXED, product ID or object. If none provided, $post will be used. Optional.
 * @Returns: MIXED, user object on success, false on failure
 */
 	function store_get_product( $product = null ){
	 	global $post;

	 	// Set default to $post
		if ( ! $product ) $product = $post;

		// get full post object
		$product = get_post( $product );

		// Make sure this is a product
		if ( is_object($product) ) {
			if ( $product->post_type !== 'product' ) $product = false;
		}

		// Return result
		return $product;

 	}


/* ------------ Higher level functions below ------------ */


/*
 * @Description: Echo the current $post price
 *
 * @Param: STRING, something to append before the quantity
 * @Param: STRING, something to append after the quantity
 * @Param: BOOL, true to echo the value, false to return
 * @Returns: MIXED, quantity value of product
 */
 	if ( ! function_exists('the_price') ) {
	 	function the_price($before = '', $after = '', $echo = true) {
		 	$price = store_get_product_price();
		 	$price = number_format(($price / 100), 2, '.', ',');

	        $price = $before . $price . $after;

	        if ( $echo )
	            echo $price;
	        else
		        return $price;
		}
	}

?>