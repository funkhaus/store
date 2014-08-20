<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Helper function to make a default password for a user
 *
 * @Param: INT | length of string to generate. Optional.
 * @Return: MIXED | random password of X length on success, false on failure
 */
	function store_get_random_password($length = 7) {

		// Make sure length is integer
		$length = intval( $length );

		// If length is not usable, abort
		if ( ! $length ) return false;

		// Set available characters
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	    // Set output
	    $randomString = '';

		// Loop and add a random character until length is reached
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }

	    return $randomString;
	}

/*
 * @Description: Function used to create a new Store Customer
 *
 * @Param: STRING | email to use for customer. Required
 * @Param: STRING | password to use for customer, if none provided a 7-digit random password will be used. Optional.
 * @Return: MIXED | integer of user ID on success, false on failure
 */
	function store_create_customer( $email, $password = null ) {

		// No password? get a random one
		$temp = false;
		if ( ! $password ) {
			$temp = true;
			$password = store_get_random_password();
		}

		// Set user object
		$userdata = array(
			'user_pass'			=> $password,
			'user_login'		=> $email,
			'user_email'		=> $email,
			'role'				=> 'store_customer'
		);

		// Set user ID by creating user
		$user_id = wp_insert_user( $userdata );

		// If user wasn't created, abort
		if ( ! $user_id ) return $false;

		// If temp password was created, save it to meta
		if ( $temp ) update_user_meta( $user_id, 'store_temp_pass', $password );

		// Email user what they're new password is?

		return $user_id;

	};

/*
 * @Description: function to save an address
 *
 * @Param: ARRAY | associative array of address fields, must match fields returned by store_get_address_fields(). Required.
 * @Param: MIXED | ID or email address of customer to associate address to. Optional.
 * @Param: INT | ID of order/cart to associate this address with. Optional.
 *
 * @Return: MIXED | address ID on success, or false on failure
 */
	function store_save_address( $address = null, $customer = null, $order = null, $shipping = true, $billing = true ) {

		// No address info? abort
		if ( ! $address ) return false;

		// customer and order are both false? abort.
		if ( $customer === false && $order === false ) return false;

		// If customer is not false, attempt to get intended customer ID
		if ( $customer !== false ) {

			// Set customer default to be current user
			if ( ! $customer ) $customer = get_current_user_id();

			$field = false;

			// If customer is email address, search by email
			if ( strstr($customer, '@') ) $field = 'email';

			// If customer is ID, search by ID
			if ( is_int(intval($customer)) ) $field = 'id';

			// Get specified customer
			$customer = get_user_by( $field, $customer );

			if ( $customer ) {
				$customer = $customer->ID;
			} else {
				$customer = false;
			}

		}

		// Create address post (setting author accordingly)
		$args = array(
			'post_status'    => 'publish',
			'post_type'      => 'address',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_author'	 => $customer,
			'post_title'	 => 'Address #?',
			'post_content'	 => '',
			'post_parent'	 =>	38
		);
		$address_id = wp_insert_post($args, true);

		// If address created...
		if ( $address_id ) {

			$args['ID'] = $address_id;
			$args['post_title'] = 'Address #' . $address_id;
			$args['post_name'] = 'Address #' . $address_id;

			// update post name and title
			wp_update_post( $args );

		} else {

			// No address created? abort.
			return false;

		}

		// Set order default to be currently active cart
		if ( ! $order ) $order = store_get_active_cart_id();

		$address_parent = false;

		// Check if order ID is set and is valid
		if ( intval($order) ) $address_parent = get_post( intval($order) );

		// if post is not an address, set to false
		if ( $address_parent->post_type !== 'orders' ) $address_parent = false;

		// If valid address was found, set as address parent
		if ( $address_parent ) {
			update_post_meta( $address_id, '_store_address_parent', intval($address_parent->ID) );
		}

		// Load address fields
		$fields = store_get_address_fields();

		// Loop through fields and set accordingly
		$field_match = false;
		foreach ( $fields as $field ) {

			// if field is set, update meta
			if ( isset($address[$field]) ) {
				$field_match = true;
				update_post_meta($address_id, "_store_address_" . $field, $address[$field]);
			}

		}

		// Set shipping and billing
		if ( $shipping ) update_post_meta( $address_id, "_store_address_shipping", 1);
		if ( $billing ) update_post_meta( $address_id, "_store_address_billing", 1);

		// If field match is still false, delete the address that was created
		if ( ! $field_match ) {
			$address_id = false;
			wp_delete_post( $address_id, true );
		}

		return $address_id;

	}

	/*
	 * @Description: Get billing address for this User/Customer
	 *
	 * @Param: INT, user ID. If none provided, the currently logged in user ID will be used. Optional.
	 * @Returns: MIXED, returns an array of address properties on success, or false on failure
	 */
	 	function store_get_user_billing_address( $user_id = null ){

		 	// Set default user to be current user
		 	if ( ! $user_id ) $user_id = get_current_user_id();

		 	// Still no user ID? abort.
		 	if ( ! $user_id ) return false;

		 	// set output and args
		 	$output = false;
		    $args = array(
				'posts_per_page'	=> 1,
				'meta_key'			=> '_store_address_billing',
				'meta_value'		=> '1',
				'post_type'			=> 'address',
				'author'			=> $user_id
			);

			// Query for address
			$result = get_posts($args);

			// if anything came back, set output
			if ( ! empty($result) ) {
				$address = reset($result);
			}

			// Loop through all address fields
			foreach ( store_get_address_fields() as $field ) {

				// Set each field into output array
				$output[$field] = get_post_meta( $address->ID, '_store_address_' . $field, true );

			}

			return $output;

	 	}

	/*
	 * @Description: Get shipping address for this User/Customer
	 *
	 * @Param: INT, user ID. If none provided, the currently logged in user ID will be used. Optional.
	 * @Returns: MIXED, returns an array of address properties on success, or false on failure
	 */
	 	function store_get_user_shipping_address( $user_id = null ){

		 	// Set default user to be current user
		 	if ( ! $user_id ) $user_id = get_current_user_id();

		 	// Still no user ID? abort.
		 	if ( ! $user_id ) return false;

		 	// set output and args
		 	$output = false;
		    $args = array(
				'posts_per_page'	=> 1,
				'meta_key'			=> '_store_address_shipping',
				'meta_value'		=> '1',
				'post_type'			=> 'address',
				'author'			=> $user_id
			);

			// Query for address
			$result = get_posts($args);

			// if anything came back, set output
			if ( ! empty($result) ) {
				$address = reset($result);
			}

			// Loop through all address fields
			foreach ( store_get_address_fields() as $field ) {

				// Set each field into output array
				$output[$field] = get_post_meta( $address->ID, '_store_address_' . $field, true );

			}

			return $output;

	 	}

	/*
	 * @Description: Get all addresses for a user
	 *
	 * @Param: INT, user ID or email. If none provided, the currently logged in user will be used. Optional.
	 * @Returns: MIXED, array of address arrays on success, or false on failure
	 */
	 	function store_get_user_shipping_addresses( $customer = null ){

		 	$customer = store_get_customer( $customer );

		 	// get all address post types authored by customer
		    $args = array(
				'posts_per_page'	=> -1,
				'orderby'			=> 'post_date',
				'order'				=> 'DESC',
				'author'			=> $customer->ID,
				'post_type'			=> 'address'
			);
			$addresses = get_posts( $args );

			// Set output
			$output = false;

			// if any addresses returned...
			if ( $addresses ) {

				// Loop through addresses
				foreach ( $addresses as $address ) {

					// Set this ID as output key
					$output[$address->ID] = array();

					// Loop through address fields
					foreach ( store_get_address_fields() as $field ) {

						// Add this address field value to output
						$output[$address->ID][$field] = get_post_meta( $address->ID, '_store_address_' . $field, true );

					}

				}

			}

			return $output;

	 	}

	/*
	 * @Description: Get user object of a given cutomer by ID, email, or currently logged in
	 *
	 * @Param: MIXED, user ID or email. If none provided, the currently logged in user will be used. Optional.
	 * @Returns: MIXED, user object on success, false on failure
	 */
	 	function store_get_customer( $customer = null ){

			if ( ! $customer ) $customer = get_current_user_id();

			$field = false;

			// If customer is email address, search by email
			if ( strstr($customer, '@') ) $field = 'email';

			// If customer is ID, search by ID
			if ( is_int(intval($customer)) ) $field = 'id';

			// Get specified customer
			return get_user_by( $field, $customer );

	 	}

?>