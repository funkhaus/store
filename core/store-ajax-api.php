<?php
/*
 * This file contains all the PHP functions that get run using an AJAX request
 
	The difference between wp_ajax and wp_ajax_nopriv is as simple as being logged in vs not
	
	wp_ajax – Use when you require the user to be logged in.	
		add_action( 'wp_ajax_<ACTION NAME>', <YOUR FUNCTION> );
	
	wp_ajax_nopriv – Use when you do not require the user to be logged in.
		add_action( 'wp_ajax_nopriv_<ACTION NAME>', <YOUR FUNCTION> );
	
	The one trick to this is that if you want to handle BOTH cases (i.e. the user is logged in as well as not), you need to implement both action hooks.
 */


/*
 * @Description: Get a standardized template to build json responses on. Defaults to a general error.
 * @Returns: ARRAY, response template in the form of an associative array 
 */
	function store_get_json_template($data = null){

		$template = array();

		$template['success'] = false;
		$template['code'] = 'ERROR';
		$template['vendor_response'] = false;
		$template['message'] = 'An error occurred, please try again.';

		if ( is_array($data) ) {
			foreach ( $data as $prop => $val ) {
				if ( array_key_exists($prop, $template) ) $template[$prop] = $val;
			}
		}

		return $template;
	}

/*
 * @Description: The AJAX wrapper for store_add_product_to_cart(). Must POST an array of parameters that store_add_product_to_cart() requires 
 */
	add_action( 'wp_ajax_nopriv_add_to_cart', 'store_ajax_add_product_to_cart' );
	add_action( 'wp_ajax_add_to_cart', 'store_ajax_add_product_to_cart' );
	function store_ajax_add_product_to_cart() {

		// Import vars from the AJAX array if set
		if( isset($_REQUEST['product_id']) ) {
			$product_id = (int) $_REQUEST['product_id'];
		}
		if( isset($_REQUEST['quantity']) ) {
			$quantity = (int) $_REQUEST['quantity'];
		} else {
			$quantity = 1;
		}
		if( isset($_REQUEST['cart_id']) ) {
			$cart_id = (int) $_REQUEST['cart_id'];
		}

		// Pass into PHP function, echo results and die.
		$added = store_add_product_to_cart($product_id, $cart_id, $quantity);

		// Set api logging
		$output = array();
		if ( $removed ) {
			$output['success'] = true;
			$output['code'] = 'OK';
			$output['message'] = 'Product successfully added to cart.';
		}

		// Set proper header, output
		header('Content-Type: application/json');
		echo json_encode(store_get_json_template($output));
		die;
	}


/*
 * @Description: The AJAX wrapper for store_remove_product_from_cart(). Must POST an array of parameters that store_remove_product_from_cart() requires
 */
	add_action( 'wp_ajax_nopriv_remove_from_cart', 'store_ajax_remove_product_from_cart' );
	add_action( 'wp_ajax_remove_from_cart', 'store_ajax_remove_product_from_cart' );
	function store_ajax_remove_product_from_cart() {

		// Import vars from the AJAX array if set
		if( isset($_REQUEST['product_id']) ) {
			$product_id = (int) $_REQUEST['product_id'];
		}
		if( isset($_REQUEST['cart_id']) ) {
			$cart_id = (int) $_REQUEST['cart_id'];
		}
		if( isset($_REQUEST['quantity']) ) {
			$quantity = (int) $_REQUEST['quantity'];
		} else {
			$quantity = -1;
		}

		// Pass into PHP function, echo results and die.
		$removed = store_remove_product_from_cart($product_id, $cart_id, $quantity);

		// Set api logging
		$output = array();
		if ( $removed ) {
			$output['success'] = true;
			$output['code'] = 'OK';
			$output['message'] = 'Product successfully removed from cart.';
		}

		// Set proper header, output
		header('Content-Type: application/json');
		echo json_encode(store_get_json_template($output));
		die;
	}


/*
 * @Description: Run the build cart function as defined by theme author. 
 *
 * @Returns: MIXED, either result of defined function, or JSON object
 * @Todo: make a default json response
 */
	add_action( 'wp_ajax_nopriv_get_mini_cart', 'store_ajax_get_mini_cart' );
	add_action( 'wp_ajax_get_mini_cart', 'store_ajax_get_mini_cart' );
	function store_ajax_get_mini_cart() {

		// if theme author has defined a cart, return it
		if( locate_template('store-mini-cart.php') ) {
			get_template_part('store-mini-cart');

		// Otherwise return json data
		} else {

			// Set output
			$output = array();

			// Get current cart
			$items = store_get_cart_items();

			// No items? abort
			if ( empty($items) ) return false;

			// Add total to cart
			$output['total'] = store_calculate_cart_total();

			// Add quantity of unique items in cart
			$output['count'] = count($items);

			// Add non-unique quantity
			$output['quantity'] = array_sum($items);

			// loop through items
			foreach ( $items as $id => $qty ) {

				$product = store_get_product($id);

				// add each product post object
				$output['items'][$id]['name'] = get_the_title($product);
				$output['items'][$id]['sku'] = store_get_sku($product);
				$output['items'][$id]['qty'] = $qty;
				$output['items'][$id]['price'] = store_get_product_price($product);

			}

			// Set header and output JSON
			header('Content-Type: application/json');
			echo json_encode( $output );
		}

		die;
	}


/*
 * @Description: Sync inventory with shipwire
 *
 * @Returns: MIXED, result of store_update_shipwire_inventory
 */
	add_action( 'wp_ajax_nopriv_update_inventory', 'store_ajax_update_inventory' );
	add_action( 'wp_ajax_update_inventory', 'store_ajax_update_inventory' );
	function store_ajax_update_inventory() {

		// Import vars from the AJAX array if set
		$product_id = false;
		if( isset($_REQUEST['product_id']) ) {
			$product_id = (int) $_REQUEST['product_id'];
		}

		// attempt to update inventory
		$updated = store_update_shipwire_inventory($product_id);

		// Set api logging
		$output = array();
		if ( $updated ) {
			$output['success'] = true;
			$output['code'] = 'OK';
			$output['message'] = 'All inventory updated.';
		}

		// Set proper header, output
		header('Content-Type: application/json');
		echo json_encode($output);
		die;
	}


/*
 * @Description: Sync inventory with shipwire
 *
 * @Returns: MIXED, result of store_update_shipwire_inventory
 */
	add_action( 'wp_ajax_nopriv_shipwire_quote', 'store_ajax_shipwire_quote' );
	add_action( 'wp_ajax_shipwire_quote', 'store_ajax_shipwire_quote' );
	function store_ajax_shipwire_quote() {

		$address = array();
		if( isset($_REQUEST['address']) ){
			$address = $_REQUEST['address'];
		}

		// Get quote from shipwire
		$options = store_shipwire_request_cart_shipping( $address );

		// Set proper header, output
		header('Content-Type: application/json');
		echo json_encode($options);
		die;
	}


/*
 * @Description:
 *
 * @Returns:
 */
	add_action( 'wp_ajax_nopriv_stripe_charge', 'store_ajax_stripe_charge' );
	add_action( 'wp_ajax_stripe_charge', 'store_ajax_stripe_charge' );
	function store_ajax_stripe_charge() {

		// Import vars from the AJAX array if set
		$token = false;
		if( isset($_REQUEST['token']) ) {
			$token = (string) $_REQUEST['token'];
		}

		// run stripe charge and get response
		$charge = store_stripe_run_charge($token);

		// Set response var
		$response = array();

		// charge was successful, set response
		if ( $charge['id'] ) {
			$response['success'] = true;
			$response['code'] = 'OK';
			$response['message'] = 'Card xxxxxxxxxxxx' . $charge['card']['last4'] . ' successfully charged for $' . number_format($charge['amount'] / 100, 2, '.', '');
		}

		// Charge was unsuccessful, set response
		if ( $charge['error'] ) {
			$response['success'] = false;
			$response['code'] = strtoupper($charge['error']['code']);
			$response['message'] = $charge['error']['message'];
		}

		// forward raw response into output
		$response['vendor_response'] = $charge;
		$response['vendor_response']['vendor'] = 'stripe';

		// make sure response is properly formatted
		$output = store_get_json_template($response);

		// Set proper header, output
		header('Content-Type: application/json');
		echo json_encode($output);
		die;
	}


?>