<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Send a full inventory request to shipwire
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_shipwire_request_inventory(){

		// Set unique ID by session
		// Use md5 hash to gaurantee 16 char length
		$session = md5(session_id(), true);

		// if transient is set, return it
		if ( $output = get_transient( $session . '_sw_inventory' ) ) {

			$output = simplexml_load_string( $output );
			return $output;

		} else {

			// Get user options
			$options = get_option('store_sw_settings');

			// Not enabled in settings? abort
			if ( ! $options['enabled'] ) return false;

			// Set URL to send request to
			$url = 'https://api.shipwire.com/exec/InventoryServices.php';

			// Set XML request
			$_ = array('<?xml version="1.0" encoding="UTF-8"?>');
			$_[] = '<!DOCTYPE InventoryUpdate SYSTEM "http://www.shipwire.com/exec/download/InventoryUpdate.dtd">';
			$_[] = '<InventoryUpdate>';
				$_[] = '<Username>' . $options['usnm'] . '</Username>';
				$_[] = '<Password>' . $options['pswd'] . '</Password>';
				$_[] = '<Server>Production</Server>';
			$_[] = '</InventoryUpdate>';
			$request = join( "\n", $_ );

			// Send request
			$response = wp_remote_post(
			    $url,
			    array(
			        'method' => 'POST',
			        'timeout' => 45,
			        'redirection' => 5,
			        'httpversion' => '1.0',
			        'headers' => array(
						'Content-Type' => 'application/xml',
			        ),
			        'body' => trim( $request ),
			        'sslverify' => false
			    )
			);
			$body = wp_remote_retrieve_body( $response );

			// Parse XML into usable object
			$output = simplexml_load_string( $body );

			// Cache raw shipwire API response for 10 seconds
			set_transient( $session . '_sw_inventory', $body, 10 );

		}

		// Return object of response
		return $output;

	};


/*
 * @Description: Get current shipwire inventory for a specific product
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_shipwire_qty( $product = null ){

		// get product object
		$product = store_get_product( $product );

		// still no product? abort.
		if ( ! $product ) return false;

		// Get full inventory
		$inventory = store_shipwire_request_inventory();

		// loop through inventory and find target product
		$output = false;
		foreach ( $inventory as $item ) {

			foreach ( $item->attributes() as $atts ) {
				if ( $atts == $product->_store_sku ) {
					// Set output to be integer value of quantity
					$output = intval( $item->attributes()->quantity );
					break;
				}
			}

		}

		return $output;

	};


/*
 * @Description: submit order to shipwire
 *
 * @Param: MIXED, ID or object of order to submit
 * @Returns: MIXED, XML object of shipwire response on success, false on failure
 */
	function store_shipwire_request_order( $order = null ){

		// Get user options
		$options = get_option('store_sw_settings');

		// Not enabled in settings? abort
		if ( ! $options['enabled'] ) return false;

		// get full order object
		$order = get_post( $order );

		// still no product? abort.
		if ( ! $order ) return false;

		// Get order items, return false if none exist.
		$items = store_get_order_items($order);
		if ( ! $items ) return false;



		// Set URL to send request to
		$url = 'https://api.shipwire.com/exec/FulfillmentServices.php';

		// Set XML request
		$_ = array('<?xml version="1.0" encoding="UTF-8"?>');
		$_[] = '<!DOCTYPE OrderList SYSTEM "http://www.shipwire.com/exec/download/OrderList.dtd">';
		$_[] = '<OrderList>';
			$_[] = '<Username>' . $options['usnm'] . '</Username>';
			$_[] = '<Password>' . $options['pswd'] . '</Password>';
			$_[] = '<Server>Production</Server>';
			$_[] = '<Order id="order-' . $order->ID . '">';
				$_[] = '<Warehouse>00</Warehouse>';
				$_[] = '<AddressInfo type="ship">';
					// NAME STUFF GOES HERE
					$_[] = '<Address1>321 Foo bar lane</Address1>';
					$_[] = '<Address2>Apartment #2</Address2>';
					$_[] = '<City>Nowhere</City>';
					$_[] = '<State>CA</State>';
					$_[] = '<Country>US</Country>';
					$_[] = '<Zip>12345</Zip>';
					$_[] = '<Phone>555-444-3210</Phone>';
					$_[] = '<Email>john@funkhaus.us</Email>';
				$_[] = '</AddressInfo>';
				$_[] = '<Shipping>GD</Shipping>';

				$count = 0;
				foreach ( $items as $id => $qty ) {

					$product = store_get_product($id);
					if ( ! $product || ! $product->_store_sku ) continue;

					$_[] = '<Item num="' . $count . '">';
						$_[] = '<Code>' . $product->_store_sku . '</Code>';
						$_[] = '<Quantity>' . $qty . '</Quantity>';
					$_[] = '</Item>';

					$count++;

				};

			$_[] = '</Order>';
		$_[] = '</OrderList>';
		$request = join( "\n", $_ );

		// Set output
		$output = false;

		// Send request
		$response = wp_remote_post(
		    $url,
		    array(
		        'method' => 'POST',
		        'timeout' => 45,
		        'redirection' => 5,
		        'httpversion' => '1.0',
		        'headers' => array(
					'Content-Type' => 'application/xml',
		        ),
		        'body' => trim( $request ),
		        'sslverify' => false
		    )
		);
		$body = wp_remote_retrieve_body( $response );

		// Parse XML into usable object
		$output = simplexml_load_string( $body );

		return $output;

	};

?>