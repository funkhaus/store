<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Create an order based on a given cart. If no cart provided, the currently active cart will be used.
 *
 * @Param: MIXED, cart object or ID to create order from. Optional.
 * @Return: MIXED, order ID on success, or false on failure
 */
	function store_create_order( $cart = null ) {

		// Get full cart object
		$cart = store_get_cart( $cart );

		// If cart has already been made into an order, abort.
		if ( store_cart_is_order($cart->ID) ) return false;

		// Set products, abort if none.
		$products = get_post_meta($cart->ID, '_store_cart_products', true);
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
			$meta_id = update_post_meta( $order_id, '_store_source_cart', $prev_id );

			// Set source cart meta
			$meta_id = update_post_meta( $order_id, '_store_cart_products', $products );

			// Set ID, title, and name
			$cart['ID'] = $order_id;
			$cart['post_title'] = 'Order #' . $order_id;
			$cart['post_name'] = 'Order #' . $order_id;

			// Update name and title
			wp_update_post( $cart );

			// Log creation in order history
			store_add_order_history($order_id, 'order ' . $order_id . ' created.');

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
 * @Return: BOOL, returns true on success, or false on failure
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

		// Log in order history
		if ( $output ) {
			store_add_order_history($order_id, 'order status changed to ' . $existing_term->name);
		}

		return $output;
	}


/*
 * @Description: Add an event to an order's history
 *
 * @Param 1: MIXED, ID or object of order to add event to. Required.
 * @Param 2: STRING, event to add to history. Required
 * @Return: MIXED, meta ID on success, false on failure
 */
	function store_add_order_history( $order = null, $event = null ) {

		// Get full object
		$order = get_post($order);

		// Check requirements, abort if not set
		if ( ! $order || ! is_string($event) ) return false;

		// Get current history
		$history = get_post_meta($order->ID, '_store_order_history', true);

		// if no history, set empty array
		if ( ! $history ) $history = array();

		// add event to history
		$history[] = $event;

		// Set meta and return result
		return update_post_meta($order->ID, '_store_order_history', $history);

	}


/*
 * @Description: Sets the order status of a given cart.
 *
 * @Param 1: INT, ID or object of order to get status for. Will default to post->ID. Optional.
 * @Return: MIXED, returns term object of current order status on success, or false on failure.
 */
	function store_get_order_status( $order = null ) {
		global $post;

		// Set order object
		$order = get_post( $order );

		// No order? get post
		if ( ! $order ) $order = $post;

		// Get current term(s)
		$current = wp_get_post_terms( $order->ID, 'store_status' );

		// Return first
		return reset($current);

	}


/*
 * @Description: Set a custom order status
 *
 * @Param: STRING, desired title of your status
 * @Return: BOOL, returns true on success, or false on failure
 */
	function store_add_custom_order_status( $status = null ) {

		// if status is not a string, abort.
		if ( ! is_string( $status ) ) return false;

		// query to see if this term exists already
		$term_exists = get_term_by( 'slug', $status, 'store_status' );

		// if so, abort
		if ( $term_exists ) return false;

		// Create status and return result
		return wp_insert_term( $store_status, 'store_status' );

	}


/*
 * @Description: Get all available order statuses
 *
 * @Return: ARRAY, all available terms within the store_status taxonomy, returned as objects
 */
	function store_get_registered_statuses() {

		// query for all statuses, in order of creation
		$args = array(
		    'orderby'           => 'id', 
		    'order'             => 'ASC',
		    'hide_empty'        => false
		);
		$terms = get_terms( 'store_status', $args );

		// Return array of objects
		return $terms;

	}


/*
 * @Description: Get shipping address for an order
 *
 * @Param: MIXED, order ID or object. Required.
 * @Return: MIXED, address array on success, false on failure
 */
 	function store_get_order_shipping_address( $order = null ) {

	 	// Get order object
	 	$order = get_post( $order );

	 	// Still no order? abort.
	 	if ( ! $order ) return false;

	 	return get_post_meta( $order->ID, '_store_shipping_address', true );

 	}


/*
 * @Description: Sets a shipping address for an order
 *
 * @Param 1: INT, ID or object of order to set status for. Required.
 * @Param 2: ARRAY, array of address fields to use for address. Must match format of store_get_address_fields(). If none provided the shipping address of the current customer will be used.
 * @Return: BOOL, true on success, or false on failure
 */
	function store_set_order_shipping_address( $order = null, $address = null ) {

		// Make sure order if a post object
		$order = get_post( $order );		

		// set default address to be current customer's shipping address
		if ( empty($address) || ! is_array($address) ) $address = store_get_customer_shipping_address( store_get_customer() );

		// No address || order? abort.
		if ( ! $address || $order->post_type !== 'orders' ) return false;

		// Get field template
		$field_template = store_get_address_fields();

	 	// Set output
	 	$output = false;

	 	// Loop through address fields
	 	foreach ( $field_template as $field ) {

		 	// if field is set, add to output
		 	if ( isset($address[$field]) ) $output[$field] = $address[$field];

	 	}

	 	// no output? abort.
	 	if ( ! $output ) return false;

	 	// return output of update post meta
	 	return update_post_meta( $order->ID, '_store_shipping_address', $output );

	}


/*
 * @Description: Save shipping rate data to a given order
 *
 * @Param 1: INT, Order ID. Required.
 * @Param 2: Shipping option
 * @Return:
 */
	function store_set_order_shipping_meta( $order_id = null, $shipping_option = null ) {

		// return output of update post meta
		return update_post_meta($order_id, '_store_shipping_option', $shipping_option);
	}


/*
 * @Description: Save shipping rate data to a given order
 *
 * @Param 1: INT, Order ID. Required.
 * @Param 2: Shipping option
 * @Return:
 */
	function store_get_order_shipping_meta( $order_id = null ) {

		// return output of update post meta
		return get_post_meta($order_id, '_store_shipping_option', true);
	}


/*
 * @Description: Get billing address for an order
 *
 * @Param: INT, order ID. Required.
 * @Return: MIXED, address object on success, false on failure
 */
 	function store_get_order_billing_address( $order = null ) {

	 	// Get order objeict
	 	$order = get_post( $order );

	 	// Still no order? abort.
	 	if ( ! $order ) return false;

	 	return get_post_meta( $order->ID, '_store_billing_address', true );
 	}


/*
 * @Description: Set the billing address for a given order
 *
 * @Param: ARRAY, address array, must match format of store_get_address_fields(). Required.
 * @Param: INT, ID of order to set address to. Required.
 * @Return: MIXED, meta ID on success, false on failure
 */
 	function store_set_order_billing_address( $order = null, $address = null ) {

		// Make sure order if a post object
		$order = get_post( $order );

		// set default address to be current customer's shipping address
		if ( empty($address) || ! is_array($address) ) $address = store_get_customer_billing_address( store_get_customer() );

		// No address || order? abort.
		if ( ! $address || $order->post_type !== 'orders' ) return false;

		// Get field template
		$field_template = store_get_address_fields();

	 	// Set output
	 	$output = false;

	 	// Loop through address fields
	 	foreach ( $field_template as $field ) {

		 	// if field is set, add to output
		 	if ( isset($address[$field]) ) $output[$field] = $address[$field];

	 	}

	 	// no output? abort.
	 	if ( ! $output ) return false;

	 	// return output of update post meta
	 	return update_post_meta( $order->ID, '_store_billing_address', $output );
 	}


/*
 * @Description: Calculate the total of a given order.
 *
 * @Param: MIXED, cart ID or object. Defaults to currently active cart. Optional.
 * @Returns: MIXED, integer of total in cents, false on failure
 */
 	function store_calculate_order_total( $order = null ) {

	 	// Get order object
	 	$order = get_post($order);

	 	// Get order items
	 	$items = store_get_order_items($order);

	 	// set output
	 	$total = false;

	 	// if items found in cart, loop through them
	 	if ( $items ) {
	 		$total = 0;
		 	foreach ( $items as $id => $qty ) {

			 	// if price comes back, add into total
			 	if ( $price = store_get_product_price($id) ) $total += ($price * $qty);

		 	}

		 	// if order shipping is available, add it to the total
		 	if ( $shipping = store_get_order_shipping_meta($order->ID) ) $total += (int) ( $shipping['cost'] * 100 ); // * 100 because shipwire returns in decimal (8.75)

	 	}

	 	return $total;

 	}


/*
 * @Description:
 *
 * @Param: ARRAY, arguments for function
 *		shipping_address: ARRAY, valid address array to use for shipping. Required.
 *		stripe_token: STRING, a valid charge token from stripe.js to charge card with. Required.
 *		billing_address: ARRAY, valid address array to use for billing, will default to shipping address. Optional.
 *		
 *
 * @Return:
 */
 	function store_submit_order( $args ) {

	 	// make sure all arguments are there
	 	if ( empty( $args['shipping_address'] ) || empty( $args['stripe_token'] ) ) return false;

	 	// default billing to be shipping
	 	if ( empty( $args['billing_address'] ) ) $args['billing_address'] = $args['shipping_address'];

		// Set for api logging
		$output = array();

		// Create the order from active cart
		$order_id = store_create_order();

		// if order created...
		if ( $order_id ) {

			// Add shipping to order
			$set_ship = store_set_order_shipping_address($order_id, $args['shipping_address']);

			// Add billing to order
			$set_bill = store_set_order_billing_address($order_id, $args['billing_address']);

			// Set status to submitted
			$set_status = store_set_order_status($order_id, 'submitted');

			// If shipping or billing were not set, remove order and fail
			if ( ! $set_ship || ! $set_bill ) {
				wp_delete_post( $order_id, true );
				$order_id = false;
			}

		} else {

			$output['code'] = 'FAILED_ORDER';
			$output['message'] = 'Failed to create order from active cart.';
			return store_get_json_template($output);
		}

		// Check inventory
		$items = store_get_order_items($order_id);
		if ( $items ) {
			$can_ship = true;
			foreach ( $items as $id => $qty ) {

				// Get stock from shipwire
				$stock = store_get_shipwire_qty( $id );

				// if stock is not greater than quantity in cart, set var
				if ( ! $stock || intval($stock) < intval($qty) ) $can_ship = false;
			}
		}

		// If order cannot ship, return with errors
		if ( ! $can_ship ) {

			$output['code'] = 'FAILED_INVENTORY';
			$output['message'] = 'There is not enough stock in Shipwire to complete this order.';
			return store_get_json_template($output);

		}

		// Calculate shipping

		// Get usable shipping options
		$ship_options = store_shipwire_retrieve_shipping( store_shipwire_request_order_shipping($order_id) );
		$ship_method = false;

		// If options came back
		if ( $ship_options ) {

			// if shipping method is set, target that method
			if ( isset($args['shipping_method']) ) {
				// loop through methods, find target
				foreach ( $ship_options as $option ) {
					if ( $option['method'] == $args['shipping_method'] ) $ship_method = $option;
				}
			}

			// If no ship method, default to first
			if ( ! $ship_method ) $ship_method = $ship_options[0];

			// Add shipping method data to order
			$save_shipping = store_set_order_shipping_meta($order_id, $ship_method);

			// if save shipping failed, report and output
			if ( ! $save_shipping ) {
				$output['code'] = 'FAILED_SAVE_SHIPPING';
				$output['message'] = 'Failed to save the shipping data to this order.';
				return store_get_json_template($output);
			}

		} else {

			$output['code'] = 'FAILED_SHIPPING_QUOTE';
			$output['message'] = 'Failed to get shipping options from Shipwire.';
			return store_get_json_template($output);
		}

		// Charge card
		$charged = store_stripe_run_charge( $args['stripe_token'], store_calculate_order_total($order_id), $description = 'Charge for Order ID ' . $order_id );

		// If error on charge, log and return
		if ( ! $charged['id'] ) {

			$output['code'] = strtoupper($charge['error']['code']);
			$output['message'] = $charge['error']['message'];
			$output['vendor_response'] = $charged;
			$output['vendor_response']['vendor'] = 'stripe';
			return store_get_json_template($output);
		}
		$output['vendor_response']['stripe'] = $charged;

		// Set order to paid
		store_set_order_status($order_id, 'paid');

		// Place order with shipwire
		$ship_request = store_shipwire_request_order($order_id, $ship_method['method']);

		// If order didn't go through...
		if ( $ship_request['status'] !== 200 || isset($ship_request['errors']) ) {

			$output['code'] = 'FAILED_SHIPPING';
			$output['message'] = 'The order has been charged, but not shipped.';
			$output['vendor_response'] = (array) $ship_request;
			$output['vendor_response']['vendor'] = 'shipwire';
			return store_get_json_template($output);
		}

		// Set order to shipped
		store_set_order_status($order_id, 'processed');

		$transaction = array();
		$transaction['stripe_id'] = $charged['id'];
		$transaction['shipwire_id'] = (string) $ship_request['resource']['items'][0]['resource']['id'];

		// save receipt to order
		update_post_meta($order_id, '_store_transaction_info', $transaction);

		// set tracking info to false, so it can be queried by the tracking cron
		update_post_meta($order_id, '_store_order_tracking', false);

		// Made it this far? everything is cool!
		$output['success'] = true;
		$output['code'] = 'OK';
		$output['message'] = 'Order #' . $order_id . ' successfully paid, processed and sent to shipwire.';
		$output['vendor_response']['shipwire'] = (array) $ship_request;

		// run anything hooked to completed orders
		do_action('store_order_completed');

		// Return
		return store_get_json_template($output);
	}


/*
 * @Description: Get all items in order by ID or obj
 *
 * @Param: MIXED, order ID or object. Required.
 * @Returns: MIXED, returns an array of cart items (value of _store_cart_products ), or false on failure
 */
 	function store_get_order_items($order = null) {

	 	// Get order object
	 	$order = get_post($order);

 		// Set output
 		$output = false;
 		if ( $order )
 			$output = get_post_meta($order->ID, '_store_cart_products', true);

	 	return $output;
 	}

?>