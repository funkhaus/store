<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Helper function used to get array of fields needed for a full address
 *
 * @Return: ARRAY, key-based array of unique slugs for each field
 */
	function store_get_address_fields(){

		$fields = array();
		$fields[] = 'line_1';
		$fields[] = 'line_2';
		$fields[] = 'city';
		$fields[] = 'state';
		$fields[] = 'zip';

		return $fields;
	}

	// Get store conditionals
	include_once( trailingslashit( pp() ) . 'core/functions/store-conditionals.php' );

	// Get cart functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-cart-functions.php' );

	// Get order functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-order-functions.php' );

	// Get product functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-product-functions.php' );

	// Get user functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-customer-functions.php' );

	// Get address functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-address-functions.php' );