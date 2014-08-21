<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Create an order based on a given cart. If no cart provided, the currently active cart will be used.
 *
 * @Param: MIXED, cart object or ID to create order from. Optional.
 * @Return: MIXED, order ID on success, or false on failure
 */
	function store_create_order( $cart = null ) {

		// Set default to be active cart
		if ( ! $cart ) $cart = store_get_active_cart_id();

		// if cart is an ID, get whole post
		if ( is_int($cart) ) $cart = get_post($cart);

		// If cart is not a cart post, abort.
		if ( $cart->post_type != 'cart' ) return false;

		// If cart has already been made into an order, abort.
		if ( store_cart_is_order($cart->ID) ) return false;

		// Set products, abort if none.
		$products = $cart->_store_cart_products;
		if ( empty($products) ) return false;

		// Convert to associative array
		$cart = (array) $cart;

		// Cache ID
		$prev_id = $cart['ID'];

		// Remove ID and date
		unset($cart['ID']);
		unset($cart['post_date']);
		unset($cart['post_date_gmt']);

		// Change to order post type
		$cart['post_type'] = 'orders';

		// Make order out of cart
		$order_id = wp_insert_post( $cart );

		// If order was created...
		if ( $order_id ) {

			// Set product meta
			$meta_id = update_post_meta( $order_id, '_store_cart_products', $prev_id );

			// Set source cart meta
			$meta_id = update_post_meta( $order_id, '_store_source_cart', $products );

			// Set ID, title, and name
			$cart['ID'] = $order_id;
			$cart['post_title'] = 'Order #' . $order_id;
			$cart['post_name'] = 'Order #' . $order_id;

			// Update name and title
			wp_update_post( $cart );

			// Set default order status
			$status = store_set_order_status( $order_id );

			// reset active cart
			$set_cart = store_unset_active_cart();

		}

		// If something went wrong, abort.
		if ( ! $meta_id || ! $status ) return false;

		// return
		return $order_id;

	}


/*
 * @Description: Sets the order status of a given cart.
 *
 * @Param 1: INT, ID or object of cart to set status for, if none provided active cart will be used. Optional.
 * @Param 2: MIXED, string of status slug, or tag ID of status to set cart to. Optional.
 * @Returns: BOOL, returns true on success, or false on failure
 */
	function store_set_order_status( $order_id = null, $status = 'active' ) {

		// If no proper order ID, abort.
		if ( ! is_int($order_id) ) return false;

		// If null or false given for status, set to default
		if ( ! $status ) $status = 'active';

		$field = false;

		// If status is a string, field is slug
		if ( is_string($status) ) $field = 'slug';

		// If status is a integer, field is id
		if ( is_int($status) ) $field = 'id';

		// If field is still false, abort
		if ( ! $field ) return false;

		// Set cart meta to be that status, return result
		$output = false;
		$existing_term = get_term_by( $field, $status, 'store_status' );
		if ( $existing_term ) $output = wp_set_post_terms( $order_id, $existing_term->name, 'store_status' );

		return $output;

	}


/*
 * @Description: Set a custom order status
 *
 * @Param: STRING, desired title of your status
 * @Returns: BOOL, returns true on success, or false on failure
 */
	function store_add_custom_order_status( $status = null ) {

		if ( ! is_string( $status ) ) return false;

		$term_exists = get_term_by( 'slug', $status, 'store_status' );

		if ( $term_exists ) return false;

		return wp_insert_term( $store_status, 'store_status' );

	}


/*
 * @Description: Get shipping address for an order
 *
 * @Param: INT, order ID. Required.
 * @Return: MIXED, address array on success, false on failure
 */
 	function store_get_order_shipping_address( $order = null ) {

	 	// no order var? abort.
	 	if ( ! $order ) return false;

	 	// If is ID, get by ID
	 	if ( is_int( $order ) ) $order = get_post( $order );

	 	return get_post_meta( $order->ID, '_store_shipping_address' );

 	}


/*
 * @Description: Set the shipping address for a given order
 *
 * @Param: ARRAY, address array, must match format of store_get_address_fields(). Required.
 * @Param: INT, ID of order to set address to. Required.
 * @Return: MIXED, meta ID on success, false on failure
 */
 	function store_set_order_shipping_address( $address = null, $order_id = null ) {

	 	// Abort if either parameter is not set
	 	if ( empty($address) || ! is_array($address) || ! $order_id ) return false;

	 	// Get address template
	 	$address_template = store_get_address_fields();

	 	// Set output
	 	$output = false;

	 	// Loop through address fields
	 	foreach ( $address_template as $field ) {

		 	// if field is set, add to output
		 	if ( isset($address[$field]) ) $output[$field] = $address[$field];

	 	}

	 	// no output? abort.
	 	if ( ! $output ) return false;

	 	// return output of update post meta
	 	return update_post_meta( $order_id, '_store_shipping_address', $output );

 	}


/*
 * @Description: Get billing address for an order
 *
 * @Param: INT, order ID. Required.
 * @Return: MIXED, address object on success, false on failure
 */
 	function store_get_order_billing_address( $order = null ) {

	 	// no order var? abort.
	 	if ( ! $order ) return false;

	 	// If is ID, get by ID
	 	if ( is_int( $order ) ) $order = get_post( $order );

	 	return get_post_meta( $order->ID, '_store_billing_address' );

 	}


/*
 * @Description: Set the billing address for a given order
 *
 * @Param: ARRAY, address array, must match format of store_get_address_fields(). Required.
 * @Param: INT, ID of order to set address to. Required.
 * @Return: MIXED, meta ID on success, false on failure
 */
 	function store_set_order_billing_address( $address = null, $order_id = null ) {

	 	// Abort if either parameter is not set
	 	if ( empty($address) || ! is_array($address) || ! $order_id ) return false;

	 	// Get address template
	 	$address_template = store_get_address_fields();

	 	// Set output
	 	$output = false;

	 	// Loop through address fields
	 	foreach ( $address_template as $field ) {

		 	// if field is set, add to output
		 	if ( isset($address[$field]) ) $output[$field] = $address[$field];

	 	}

	 	// no output? abort.
	 	if ( ! $output ) return false;

	 	// return output of update post meta
	 	return update_post_meta( $order_id, '_store_billing_address', $output );

 	}


?>