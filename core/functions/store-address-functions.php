<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Helper function used to get array of fields needed for a full address
 *
 * @Return: ARRAY, key-based array of unique slugs for each field
 */
	function store_get_address_fields(){

		$fields = array();
		$fields[] = 'name';
		$fields[] = 'line_1';
		$fields[] = 'line_2';
		$fields[] = 'city';
		$fields[] = 'state';
		$fields[] = 'country';
		$fields[] = 'zip';

		return $fields;
	}

/*
 * @Description: Check if a given address is a shipping address
 *
 * @Param: INT, address ID. Required.
 * @Return: Bool, true if address is a shipping address, false if not
 */
 	function store_is_shipping_address( $address_id = null ) {

	 	if ( ! $address_id ) return false;

	 	$shipping = get_post_meta( $address_id, '_store_address_is_shipping', true );
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

	 	$shipping = get_post_meta( $address_id, '_store_address_is_billing', true );
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