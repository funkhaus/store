<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * @Description: Helper function used to return the calculated 
 * inventory quantity of a post based on its' variations
 *
 * @Param: INT, Id of product to retrieve quantity for
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_quantity( $product = false ){
		global $post;

		// Set default to be current product
		if ( $product === false ) {

			// if $post is not a product, abort
			if ( $post->post_type !== 'products' ) return false;
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
			'post_type'        => 'products',
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

?>