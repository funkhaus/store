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
	function store_save_address( $address = null, $customer = null, $order = null ) {

		// No address info? abort
		if ( ! $address ) return false;

		// No customer or order to attach address to? abort
		if ( ! $customer && ! $order ) return false;

		// Load address fields
		$fields = store_get_address_fields();


		// Check if customer is set and is a valid customer ID or email

		// Create address post (setting author accordingly)

		// Check if order ID is set and is valid

		// If so, add meta to address post ( _store_address_parent or something )

		// Loop through fields and set accordingly
		$field_match = false;
		foreach ( $fields as $field ) {

			if ( isset($address[$field]) ) {

				$field_match = true;
				update_post_meta($address_id, "_store_address_" . $field, $address[$field]);

			}

		}

		// If field match is still false, delete the address that was created

	}

?>