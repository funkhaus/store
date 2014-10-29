<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Get an array of all valid US states
 *
 * @Returns: ARRAY, associative array of US states on success, empty array on failure
 */
	function store_list_states(){
		$json = file_get_contents( pp() . 'core/includes/states.json' );

		$states = json_decode($json, true);
		if ( ! $states ) $states = array();

		return (array) $states;
	}

/*
 * @Description: Get an array of all valid countries with codes
 * https://gist.github.com/Keeguon/2310008
 *
 * @Returns: ARRAY, associative array of US states on success, empty array on failure
 */
	function store_list_countries(){
		$json = file_get_contents( pp() . 'core/includes/countries.json' );

		$countries = json_decode($json, true);
		if ( ! $countries ) $countries = array();

		return (array) $countries;
	}


	// Get store conditionals
	include_once( pp() . 'core/functions/store-conditionals.php' );

	// Get cart functions
	include_once( pp() . 'core/functions/store-cart-functions.php' );

	// Get order functions
	include_once( pp() . 'core/functions/store-order-functions.php' );

	// Get product functions
	include_once( pp() . 'core/functions/store-product-functions.php' );

	// Get product functions
	include_once( pp() . 'core/functions/store-page-functions.php' );

	// Get user functions
	include_once( pp() . 'core/functions/store-customer-functions.php' );

	// Get address functions
	include_once( pp() . 'core/functions/store-address-functions.php' );