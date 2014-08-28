<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * @Description: Helper function used to return the calculated 
 * inventory quantity of a post based on its' variations
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

?>