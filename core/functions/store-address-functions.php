<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Check if a given address is a shipping address
 *
 * @Param: INT, address ID. Required.
 * @Return: Bool, true if address is a shipping address, false if not
 */
 	function store_is_shipping_address( $address_id = null ) {

	 	if ( ! $address_id ) return false;

	 	$shipping = get_post_meta( $address_id, '_store_address_shipping', true );
	 	$shipping = intval($shipping);

	 	$output = false;
	 	if ( $shipping ) $output = true;

	 	return $output;

 	}


/*
 * @Description: Check if a given address is a billing address
 *
 * @Param: INT, address ID. Required.
 * @Return: Bool, true if address is a billing address, false if not
 */
 	function store_is_billing_address( $address_id = null ) {

	 	if ( ! $address_id ) return false;

	 	$shipping = get_post_meta( $address_id, '_store_address_billing', true );
	 	$shipping = intval($shipping);

	 	$output = false;
	 	if ( $shipping ) $output = true;

	 	return $output;

 	}


/*
 * @Description: Delete an address from the database
 *
 * @Param: INT, address ID. Required.
 * @Return: Bool, true on success or false on failure
 */
 	function store_delete_address( $address_id = null ) {

	 	if ( ! $address_id ) return false;

	 	return wp_delete_post($address_id, true);

 	}


?>