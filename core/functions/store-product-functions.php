<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Helper function used to return the calculated inventory quantity of a post based on its' variations
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_quantity( $product = false ){

		// init out
		$output = 0;

		// Get product object
		$product = store_get_product( $product );

		// If this is a variation, return qty
		if ( ! store_has_variants($product) ) {
			$output = intval($product->_store_qty);

		} else {

			// query variants
			$variations = store_get_product_variants($product);

			if ( $variations ) {

				// Loop through children and add qtys
				foreach ( $variations as $variation ) {

					$output += intval( $variation->_store_qty );

				}
			}
		}

		return $output;
	}


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
	}


/*
 * @Description: get the SKU of any given product
 *
 * @Param: MIXED, ID or object of product to get SKU of. If no product, $post will be used.
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_sku( $product = null ){

		$product = store_get_product( $product );

		return get_post_meta( $product->ID, '_store_sku', true );

	}


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
		$price = intval( (int) $price );

		// If price is falsey, set to false
		if ( ! $price ) $price = false;

		return $price;
	}


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
	}


/*
 * @Description: Get an array of custom options that have been set for this product
 *
 * @Param: MIXED, ID or object of product (or variant) to get keys for. Defaults to $post. Optional
 * @Returns: MIXED, Array of keys on success, or false on failure
 */
 	function store_get_product_option_keys( $product = null ){

	 	// Get full post object
	 	$product = store_get_product( $product );

	 	// Get all meta for this product
	 	$meta = get_post_meta($product->ID);

	 	// Extract valid option keys from meta
	 	$keys = store_sort_options($meta);

	 	return $keys;
 	}


/*
 * @Description: Get the value of a particular option for a given variant
 *
 * @Param: STRING, a specific key to get the value of
 * @Param: MIXED, the ID or object of the variant to get the option value for. Required.
 * @Returns: MIXED, the value of the option on success, false on failure. Defaults to $post. Optional.
 */
 	function store_get_option_value( $key = null, $product = null ){

	 	// no key? abort
	 	if ( ! $key ) return false;

	 	// get valid product object
	 	$product = store_get_product( $product );

	 	return get_post_meta($product->ID, '_store_meta_' . $key, true);
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

	 	if ( is_array($keys) ) {

		 	// Loop through keys
		 	foreach ( $keys as $key => $value ) {
	
			 	// format key to be readable
			 	$key = store_format_option_key($key);
	
			 	// if value is comma-separated...
			 	if ( strstr($value, ', ') ) {
	
				 	// Set key to be array of options
				 	$output[$key] = explode(', ', $value);
	
			 	} else {
	
				 	// otherwise just set to be value
				 	$output[$key] = $value;
	
			 	}
	
		 	}

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
			'fields'			=> 'ids'
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
 * @Description: Consistently get the ID of a given product. If a variant is given, its parent product ID will be returned.
 *
 * @Param: MIXED, product/variant ID or object. If none provided, $post will be used. Optional.
 * @Returns: INT, ID of product
 */
 	function store_get_product_id( $product = null ){

	 	// get full product obj
	 	$product = store_get_product($product);

	 	// If variant given, return parent ID
	 	$output = $product->ID;
	 	if ( $product->post_parent !== 0 ) $output = $product->post_parent;

	 	return $output;
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


/*
 * @Description:
 *
 * @Param:
 * @Returns:
 */
 	function store_get_category_order( $term_id = null ) {

	 	// get meta for this term
	 	return get_term_meta( $term_id, 'store-category-order', true );
 	}


/*
 * @Description: Get term object of the first category this post is in
 *
 * @Param:
 * @Returns:
 */
	function store_get_product_category( $product = null ){

		// get product object
		$product = store_get_product( $product );

		// Get terms for this product
		$terms = wp_get_post_terms( $product->ID, 'store_category' );

		// Set output
		$output = false;
		if ( ! empty($terms) ) $output = reset($terms);

		return $output;
	}


/*
 * @Description:
 *
 * @Param:
 * @Returns:
 */
	function store_get_attached_images( $product = null, $featured = true, $attachments = true ){

		// get product object
		$product = store_get_product($product);

		// Set featured ID
		$featured_id = get_post_thumbnail_id($product->ID);

		// init output
		$output = array();

		// if attachments is true...
		if ( $attachments ) {

			$exclude = '';
			if ( ! $featured ) $exclude = $featured_id;

			// query attachments
			$args = array(
				'posts_per_page'	=> -1,
				'exclude'			=> $exclude,
				'orderby'			=> 'menu_order',
				'order'				=> 'ASC',
				'post_type'			=> 'attachment',
				'post_mime_type'	=> 'image',
				'post_parent'		=> $product->ID
			);
			$output = get_posts($args);

		// if only featured images targeted...
		} elseif ( $featured ) {

			// if product has featured...
			if ( $featured_id ) {

				// get full post object
				$featured_image = get_post($featured_id);

				// add to output array
				$output[] = $featured_image;
			}
		}

		return $output;
	}


/*
 * @Description: Gather all images for product and variants
 *
 * @Param:
 * @Returns:
 */
	function store_get_product_images( $args = null ){

		// set defaults
		$defaults = array(
			'product_parent'	=> false,
			'featured'			=> true,
			'attachments'		=> true,
			'maximum'			=> -1
		);

		// parse incoming args and merge with defaults
		$args = wp_parse_args( $args, $defaults );

		// get valid parent
		$parent = store_get_product( $args['product_parent'] );

		// bail out if no parent
		if ( ! $parent ) return false;

		// Add parent images into array
		$output = store_get_attached_images($parent);

		// get all variants
		$all_products = store_get_product_variants($parent);

		// push parent product into array
		$all_products[] = $parent;

		// init output
		$output = array();

		// if any posts, loop through
		if ( $all_products ) {
			foreach ( $all_products as $product ) {

				// add all $product/variant attachments into output
				$output = array_merge( $output, store_get_attached_images($product, $args['featured'], $args['attachments']) );

			}
		}

		// enforce maximum if needed
		if ( $args['maximum'] > 0 ) {
			$output = array_splice($output, 0, $args['maximum']);
		}

		return $output;
	}


/*
 * @Description: Get the quantity of items in stock based on option_key => option_value pair
 *
 * @Param: STRING, the key of the option to query for. Required.
 * @Param: MIXED, the value of the option to query for. Optional.
 * @Param: MIXED, product ID or object. If none provided all products will be queried. Optional.
 * @Returns: INT, integer value of products returned
 */
	function store_get_option_quantity($option_key = '', $option_value = '', $product = null) {

		// abort if no key provided
		if ( !$option_key ) return 0;

		// set variant parent product
		$parent = '';
		if ( $product ) {
			$product = store_get_product($product);
			$parent = $product->ID;
		}

		// ensure key is consistent
		$option_key = '_store_meta_' . str_replace('_store_meta_', '', $option_key);

		// query by meta
	    $args = array(
			'posts_per_page'   => -1,
			'meta_key'         => $option_key,
			'meta_value'       => $option_value,
			'post_type'        => 'product',
			'post_parent'      => $parent
		);
		$found = get_posts($args);

		// total qtys
		$total = 0;
		foreach ( $found as $variant ) {
			$total = intval( $total + $variant->_store_qty );
		}

		// return quantity
		return $total;
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


/*
 * @Description:
 *
 * @Param:
 * @Returns:
 */
 	if ( ! function_exists('get_the_price') ) {
 		function get_the_price($product = '') {
 			$product = store_get_product( $product );

 			$price = store_get_product_price($product);
 			$price = number_format(($price / 100), 2, '.', ',');

			return $price;
		}
	}

?>