<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Sets the order status of a given cart.
 *
 * @Param 1: INT, ID or object of cart to set status for, if none provided active cart will be used. Optional
 * @Param 2: MIXED, string of status slug, or tag ID of status to set cart to
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