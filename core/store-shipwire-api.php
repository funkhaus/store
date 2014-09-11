<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Simple check if shipwire is enabled
 *
 * @Returns: BOOL, if shipping is enabled, false on failure
 */
	function store_is_shipping_enabled(){
		$sw_settings = get_option('store_sw_settings');
		return $sw_settings['enabled'];
	}


/*
 * @Description: Send a full inventory request to shipwire
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, shipwire xml response on success, bool false on failure
 */
	function store_shipwire_request_inventory(){

		// Set unique ID by session
		// Use md5 hash to gaurantee 16 char length (no encoding, so it will look like complete nonsense)
		$session = md5(session_id(), true);

		// get shipwire settings
		$options = get_option('store_sw_settings');

		// shipwire not enabled? abort
		if ( ! store_is_shipping_enabled() ) return false;

		// If transient CANNOT be retrieved and set to output...
		if ( ! $output = get_transient( $session . '_sw_inventory' ) ) {

			// Set URL to send request to
			$url = 'https://api.shipwire.com/api/v3/stock';

			// Set authentication
			$headers = array( 'Authorization' => 'Basic ' . base64_encode( $options['usnm'] . ':' . $options['pswd'] ) );

			// Send request
			$response = wp_remote_get(
			    $url,
			    array(
			        'headers'		=> $headers,
			        'httpversion' => '1.1'
				)
			);
			$body = wp_remote_retrieve_body( $response );

			// Decode into array
			$output = json_decode($body, true);

			// Cache raw shipwire API response for 10 seconds
			set_transient( $session . '_sw_inventory', $output, 10 );

		}

		// return decoded response
		return $output;
	}


/*
 * @Description: Get current shipwire inventory for a specific product. 
 * This function is optimized, feel free to run it within a loop
 *
 * @Param: MIXED, ID or obj of product to retrieve quantity for, or $post object. If string provided, function will assume it is the SKU
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_shipwire_qty( $product = null ){

		$sku = false;

		// If prod is string, assume it's the SKU
		if ( is_string($product) ) {
			$sku = $product;

		// Otherwise get full post object
		} else {
			// get product object
			$product = store_get_product( $product );
			$sku = $product->_store_sku;
		}

		// still no product? abort.
		if ( ! $sku ) return false;

		// Get full inventory
		$inventory = store_shipwire_request_inventory();

		// loop through inventory and find target product
		$output = false;
		if ( $inventory['status'] === 200 ) {
			foreach ( $inventory['resource']['items'] as $item ) {

				if ( $item['resource']['sku'] == $sku ) {
					// Set output to be integer value of quantity
					$output = intval( $item['resource']['good'] );
					break;
				}

			}
		}

		return $output;
	}


/*
 * @Description: Update inventory for one or all products
 *
 * @Param: MIXED, ID or object of product to retrieve quantity for. If none, all will be updated. Optional.
 * @Returns: MIXED, integer value of quantity changed on success, bool false on failure
 */
	function store_update_shipwire_inventory( $product = null ){

		$output = 0;

		// Set product to default, unless product was left empty
		if ( $product ) {

			$output = store_update_shipwire_inventory_single($product);

		// If product was left empty, update all products
		} else {

		    $args = array(
				'posts_per_page'   => -1,
				'post_type'        => 'product',
				'post_parent'      => 0
			);
			$products = get_posts($args);

			// Get all top-level products, loop through them
			if ( $products ) {
				foreach ( $products as $target_product ) {

					// update inventory for this product
					$inv = store_update_shipwire_inventory_single($target_product);

					// on success, add count to output
					if ( $inv ) $output += $inv;

				}

				// If output is 0, set to false
				if ( ! $output ) $output = false;

			}

		}

		return $output;
	}


/*
 * @Description: Update inventory of a single product (from shipwire)
 *
 * @Param: MIXED, ID or object or SKU of product to retrieve quantity for. Required.
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_update_shipwire_inventory_single( $product = null ){

		// get full post object
		$product = get_post( $product );

		// Guarantee that this is a product post object
		if ( ! is_object($product) ) {
			return false;
		} else {
			if ( $product->post_type !== 'product' ) return false;
		}

		// Start counter
		$count = 0;

		// If there are variants for this product...
		if ( $variants = store_get_product_variants($product) ) {

			// loop through variants
			foreach ( $variants as $variant ) {

				// get quantity from shipwire for this variant
				$qty = store_get_shipwire_qty($variant);

				if ( $qty ) {
					$count++;
					update_post_meta($variant->ID, '_store_qty', $qty);
					update_post_meta($variant->ID, '_store_shipwire_synced', true);
				} else {
					update_post_meta($variant->ID, '_store_shipwire_synced', false);
				}

			}

		// No variants? attempt to update qty for just this product
		} else {

			// Get qty from shipwire
			$qty = store_get_shipwire_qty($product);

			// if there is inventory, update and mark as synced
			if ( $qty ) {
				$count++;
				update_post_meta($product->ID, '_store_qty', $qty);
				update_post_meta($product->ID, '_store_shipwire_synced', true);

			// No inventory? mark as unsynced
			} else {
				update_post_meta($product->ID, '_store_shipwire_synced', false);

			}

		}

		// if count is 0, set to output false
		if ( ! $count ) $count = false;

		return $count;
	}


/*
 * @Description: submit order to shipwire
 *
 * @Param: MIXED, ID or object of order to submit
 * @Returns: MIXED, XML object of shipwire response on success, false on failure
 */
	function store_shipwire_request_order( $order = null, $shipping_method = null ){

		// set default shipping
		if ( empty($shipping_method) ) $shipping_method = 'GD';

		// get full obj
		$order = get_post($order);

		// Get user options
		$options = get_option('store_sw_settings');

		// Not enabled in settings? abort
		if ( ! $options['enabled'] ) return false;

		// if order has already been submitted to shipwire, abort.
		if ( get_post_meta($order->ID, '_store_shipwire_receipt', true ) ) return false;

		// Get order items, return false if none exist.
		$items = store_get_order_items($order);

		// Get shipping address for this order
		$ship_address = store_get_order_shipping_address($order);

		// If all not all data is available, abort
		if ( ! $items || ! $ship_address ) return false;

		// Set customer email if available
		if ( $order->post_author ) $customer = store_get_customer( $order->post_author );
		$email = $customer->user_email ? $customer->user_email : '';


		$json_items = array();

		// loop through items
		foreach ( $items as $id => $qty ) {

			// get and validate product
			$product = store_get_product($id);
			if ( ! $product || ! $product->_store_sku ) continue;

			// add this item's values to request
			$json_items[] = array(
				'sku'			=> $product->_store_sku,
				'quantity'		=> $qty
			);
		}

		// Build request for shipwire
		$json = array(
			'orderNo' 			=> $order->ID,
			'externalId'		=> $order->ID,
			'commerceName'		=> 'Funkhaus Store',
			'items'				=> $json_items,
			'options'			=> array(
				'serviceLevelCode'	=> $shipping_method,
				'affiliate'			=> 10852,
				'currency'			=> 'USD',
				'server'			=> 'Production'
			),
			'shipTo'			=> array(
				'email'				=> $email,
				'name'				=> '', // name stuff here
				'address1'			=> $ship_address['line_1'],
				'address2'			=> $ship_address['line_2'],
				'city'				=> $ship_address['city'],
				'state'				=> $ship_address['state'],
				'postalCode'		=> $ship_address['zip'],
				'country'			=> 'US',
				'phone'				=> '',
			)
		);
		$request = json_encode($json);

		// Set output
		$output = false;

		// Set URL to send request to
		$url = 'https://api.shipwire.com/api/v3/orders';

		// Set authentication and content type
		$headers = array( 
			'Authorization'		=> 'Basic ' . base64_encode( $options['usnm'] . ':' . $options['pswd'] ),
			'Content-Type'		=> 'application/json'
		);

		// Send request
		$response = wp_remote_post(
		    $url,
		    array(
		        'headers'		=> $headers,
		        'httpversion' 	=> '1.1',
		        'body'			=> $request
			)
		);
		$body = wp_remote_retrieve_body( $response );

		// Decode into array
		$output = json_decode($body, true);

		// If order was successful, save receipt
		if ( $output['status'] === 200 ) update_post_meta($order->ID, '_store_shipwire_receipt', $output );

		return $output;
	}


/*
 * @Description: get receipt from a completed order
 *
 * @Param: MIXED, ID or object of order.
 * @Returns: MIXED, XML object of shipwire receipt on success, false on failure
 */
	function store_get_shipwire_receipt( $order = null ){

		// Get valid object
		$order = get_post($order);

		// attempt to get receipt from meta
		$xml = get_post_meta($order->ID, '_store_shipwire_receipt', true );

		$output = false;
		if ( $xml ) 
			$output = simplexml_load_string($xml);

		return $output;

	}


/*
 * @Description: wrapper function to do a shipping request on an order post
 *
 * @Param: MIXED, ID or object of order to quote.
 * @Returns: MIXED, json_decoded shipwire response on success, or false on failure
 */
	function store_shipwire_request_order_shipping( $order = null ){

		// get full order object
		$order = get_post( $order );

		// Get order items, return false if none exist.
		$items = store_get_order_items($order);

		// Get shipping address for this order
		$ship_address = store_get_order_shipping_address($order);

		// Return output of shipping request
		return store_shipwire_request_shipping($ship_address, $order, $items);
	}


/*
 * @Description: wrapper function to do a shipping request on a cart post
 *
 * @Param: MIXED, address object to quote shipping for. Required.
 * @Param: MIXED, ID or object of cart to quote for. Defaults to active cart. Optional.
 * @Returns: MIXED, json_decoded shipwire response on success, or false on failure
 */
	function store_shipwire_request_cart_shipping( $address = null, $cart = null ){

		// get full cart object
		$cart = store_get_cart($cart);

		// Get order items, return false if none exist.
		$items = store_get_cart_items($cart);

		// Return output of shipping request
		return store_shipwire_request_shipping($address, $cart, $items);
	}

/*
 * @Description: Core function to get a shipping quote from shipwire.
 *
 * @Param: MIXED, address object to quote shipping for. Required.
 * @Param: MIXED, ID or object of cart OR order to quote for. Required.
 * @Param: ARRAY, array of items in [id] => [qty] format. Required.
 * @Returns: MIXED, json_decoded shipwire response on success, or false on failure
 */
	function store_shipwire_request_shipping( $address = null, $cart = null, $items = null ){

		// Get user options
		$options = get_option('store_sw_settings');

		// Not enabled in settings? abort
		if ( ! $options['enabled'] ) return false;

		// get full cart/order obj
		$cart = get_post($cart);

		// If all not all data is available, abort
		if ( ! $cart || ! is_array($items) || empty($address) ) return false;

		$json_items = array();

		// loop through items
		foreach ( $items as $id => $qty ) {

			// get and validate product
			$product = store_get_product($id);
			if ( ! $product || ! $product->_store_sku ) continue;

			// add this item's values to request
			$json_items[] = array(
				'sku'		=> $product->_store_sku,
				'quantity'	=> $qty
			);
		}

		// Build request for shipwire
		$json = array(
			'options'			=> array(
				'currency'			=> 'USD',
				'groupBy'			=> 'all',
				'canSplit'			=> 0,
				'warehouseArea'		=> 'US'
			),
			'order'				=> array(
				'shipTo'			=> array(
					'address1'			=> $address['line_1'],
					'address2'			=> $address['line_2'],
					'city'				=> $address['city'],
					'region'			=> $address['state'],
					'postalCode'		=> $address['zip'],
					'country'			=> 'US'
				),
				'items'				=> $json_items
			)
		);
		$request = json_encode($json);

		// Set output
		$output = false;

		// Set URL to send request to
		$url = 'https://api.shipwire.com/api/v3/rate';

		// Set authentication and content type
		$headers = array( 
			'Authorization'		=> 'Basic ' . base64_encode( $options['usnm'] . ':' . $options['pswd'] ),
			'Content-Type'		=> 'application/json'
		);

		// Send request
		$response = wp_remote_post(
		    $url,
		    array(
		        'headers'		=> $headers,
		        'httpversion' 	=> '1.1',
		        'body'			=> $request
			)
		);
		$body = wp_remote_retrieve_body( $response );

		// Decode into array
		$output = json_decode($body, true);

		// If order was successful, save receipt
		if ( $output['status'] === 200 ) update_post_meta($order->ID, '_store_shipwire_receipt', $output );

		return $output;
	}


/*
 * @Description: check if a shipwire request was successful, works in tandem with any function that starts with store_shipwire_request_
 *
 * @Param: MIXED, simpleXML object (shipwire api response), or false if request function has failed
 * @Returns: BOOL, true if request was successful, false on failure
 */
	function store_shipwire_retrieve_status( $response = false ){

		if ( ! is_array($response) ) return false;

		$output = false;
		if ( $response['status'] == 200 && ! isset($response['errors']) ) $output = true;

		return $output;
	}


/*
 * @Description: get shipping options from a shipping request
 *
 * @Param: MIXED, simpleXML object (output of store_shipwire_request_shipping()), or false if request function has failed
 * @Returns: MIXED, array of shippng options if successful, false on failure
 */
	function store_shipwire_retrieve_shipping( $response = false ){

		// If response came back with errors, abort
		if ( ! store_shipwire_retrieve_status($response) ) return $response;

		$i = 0;
		$output = false;

		foreach( $response['resource']['rates'][0]['serviceOptions'] as $quote ){

			// Format relevant figures into output
			$output[$i]['service'] = (string) $quote['shipments'][0]['carrier']['description'];
			$output[$i]['method'] = (string) $quote['serviceLevelCode'];
			$output[$i]['cost'] = (string) $quote['shipments'][0]['cost']['amount'];
			$output[$i]['delivery']['min'] = (string) $quote['shipments'][0]['expectedDeliveryMinDate'];
			$output[$i]['delivery']['max'] = (string) $quote['shipments'][0]['expectedDeliveryMaxDate'];

			$i++;
		}

		return $output;
	}

?>