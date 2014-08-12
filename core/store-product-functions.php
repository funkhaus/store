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
		global $post;

		// Set default to be current product
		if ( $product === false ) {

			// if $post is not a product, abort
<<<<<<< HEAD
			if ( $post->post_type !== 'product' ) return false;
=======
			if ( $post->post_type !== 'products' ) return false;
>>>>>>> FETCH_HEAD
			$product = $post;

		}

		// If ID was passed, get full object
		if ( is_int( intval($product) ) ) {
			$product = get_post($product);
		}

		// If this is a variation, return qty
		if ( $product->post_parent ) return $product->_store_qty;

		// query children
	    $args = array(
			'posts_per_page'   => -1,
<<<<<<< HEAD
			'post_type'        => 'product',
=======
			'post_type'        => 'products',
>>>>>>> FETCH_HEAD
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
 * @Description: Get all variants of a product
 *
 * @Param: MIXED, can be an ID or post object of product to retrieve variants of. If not set, attempts to get current ID from global $post.
 * @Returns: MIXED, can be an array of post objects for each variant or false for no varients. Uses get_posts().
 */
	function store_get_product_variants( $product = false ){
		
		// Declaring vars
		$product_id;

		// If no $product_id, then attempt to get from loop
		if( empty($product) ) {
			global $post;
			$product = $post;
			$product_id = $post->ID;

		} elseif( is_int($product) ) {
			// ID was passed, get full object
			$product = get_post($product);
			$product_id = $product->ID;
			
		} elseif( is_object($product) ) {
			// Object passed
			$product_id = $product->ID;
		}
		
		// Some error handling if above failed
		if( !is_int($product_id) || !is_object($product) ) {
			return false;
		}
		
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
<<<<<<< HEAD
			'post_type'        => 'product', // We could make the post type a setting that could be changed/filtered?
=======
			'post_type'        => 'products', // We could make the post type a setting that could be changed/filtered?
>>>>>>> FETCH_HEAD
			'post_parent'      => $parent_id,
			'post_status'      => 'publish'
		);
		$products = get_posts($args);

		// We could loop through each product here an add any extra data we need to
		// if we did, we should do it as a seperate function that takes a get_posts() array

		// Return results of get_posts();
		return $products;

	};

?>