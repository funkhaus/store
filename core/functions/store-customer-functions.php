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
	function store_create_customer( $userdata ) {

		// init output
		$output = array();

		// No email? fail.
		$temp = false;
		if ( empty($userdata['user_email']) || ! is_email($userdata['user_email']) ) {
			$output['code'] = 'INVALID_EMAIL';
			$output['message'] = 'Please provide a valid email address.';
			return store_get_json_template( $output );
		}

		// No password? generate a random one
		if ( empty($userdata['user_pass'] ) ) {
			$temp = true;
			$userdata['user_pass'] = store_get_random_password();
		}

    	// Set username to next avaible user ID number
    	global $wpdb;
		$userdata['user_login'] = 'sc-' . $wpdb->get_row("SHOW TABLE STATUS WHERE name='wp_users'")->Auto_increment;
		$userdata['role'] = 'store_customer';

		// Set user ID by creating user
		$user_id = wp_insert_user( $userdata );

	    // Did WP throw any errors?
	    if( isset($user_id->errors) ) {
	    	$wp_errors = $user_id->errors;				    
	    }				    
    	if( isset($wp_errors['empty_user_login']) ) {
    		// No user login set
    		$output['code'] = 'EMPTY_EMAIL';
    		$output['message'] = 'No email address set.';

    	}
		if( isset($wp_errors['existing_user_login']) ) {
			// The username already exsists
			$output['code'] = 'EXISTING_USER';
			$output['message'] = 'Username already used.';

			// Send an email, this is a critcal error
			wp_mail( 'john@funkhaus.us', 'Store Critical Bug', "Automated mail: Someone got a username invalid error when signing up. They shouldn't get this ever. Check store_create_customer().");

		}
		if( isset($wp_errors['existing_user_email']) ) {
			// The email is already in use.
			$output['code'] = 'EXISTING_EMAIL';
			$output['message'] = 'This email address is already in use.';

		}

		if ( ! is_wp_error($user_id) ) {

			// turn off admin bar for user
			update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
			update_user_meta( $user_id, 'show_admin_bar_admin', 'false' );

			// set reporting
			$output['success'] = true;
			$output['code'] = 'OK';
			$output['message'] = 'User successfully created';
			$output['vendor_response'] = $user_id;

			// If temp password was created, save it to meta
			if ( $temp ) update_user_meta( $user_id, 'store_temp_pass', $password );

			// Email user what they're new password is?
		}

		return store_get_json_template( $output );
	};


/*
 * @Description:
 *
 * @Param:
 * @Return:
 */
	function store_get_customer_data( $field ){

		// user not logged in? return empty string
		if ( ! is_user_logged_in() ) return '';

		$output = '';

		if ( $field === 'first_name' ){

			$current_user = wp_get_current_user();
			$output = $current_user->user_firstname;

		} elseif ( $field === 'last_name' ) {

			$current_user = wp_get_current_user();
			$output = $current_user->user_lastname;

		} elseif ( $field === 'email' ) {

			$current_user = wp_get_current_user();
			$output = $current_user->user_email;

		} elseif ( strstr($field, 'shipping') ) {
			$shipping = store_get_customer_shipping_address();

			switch ($field){
				case 'shipping_line_1':
					$output = $shipping['line_1'];
					break;
				case 'shipping_line_2':
					$output = $shipping['line_2'];
					break;
				case 'shipping_city':
					$output = $shipping['city'];
					break;
				case 'shipping_state':
					$output = $shipping['state'];
					break;
				case 'shipping_zip':
					$output = $shipping['zip'];
					break;
				case 'shipping_country':
					$output = $shipping['country'];
					break;
			}

		} elseif ( strstr($field, 'billing') ) {
			$billing = store_get_customer_billing_address();

			switch ($field){
				case 'billing_line_1':
					$output = $billing['line_1'];
					break;
				case 'billing_line_2':
					$output = $billing['line_2'];
					break;
				case 'billing_city':
					$output = $billing['city'];
					break;
				case 'billing_state':
					$output = $billing['state'];
					break;
				case 'billing_zip':
					$output = $billing['zip'];
					break;
				case 'billing_country':
					$output = $billing['country'];
					break;
			}

		}

		// ensure that an empty string comes back
		if ( empty($output) ) $output = '';
		return $output;
	};

/*
 * @Description: function to save an address
 *
 * @Param: ARRAY | associative array of address fields, must match fields returned by store_get_address_fields(). Required.
 * @Param: MIXED | ID or email address of customer to associate address to. Optional.
 * @Param: BOOL | whether or not to set as the shipping address
 * @Param: BOOL | whether or not to set as the billing address
 *
 * @Return: MIXED | address ID on success, or false on failure
 */
	function store_save_customer_address( $address = null, $customer = null, $shipping = true, $billing = true ) {

		// No address info? abort
		if ( ! $address ) return false;

		// Get valid customer object
		$customer = store_get_customer( $customer );

		// Still no customer? abort.
		if ( ! $customer ) return false;

		// Create address post (setting author accordingly)
		$args = array(
			'post_status'    => 'publish',
			'post_type'      => 'address',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_author'	 => $customer->ID,
			'post_title'	 => 'Address #?',
			'post_content'	 => ''
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
		if ( $shipping ) update_post_meta( $address_id, "_store_address_is_shipping", 1);
		if ( $billing ) update_post_meta( $address_id, "_store_address_is_billing", 1);

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
	 	function store_get_customer_billing_address( $customer = null ){

		 	// Get valid customer
		 	$customer = store_get_customer( $customer );

		 	// Still no customer? abort.
		 	if ( ! $customer ) return false;

		 	// set output and args
		 	$output = false;
		    $args = array(
				'posts_per_page'	=> 1,
				'meta_key'			=> '_store_address_is_billing',
				'meta_value'		=> '1',
				'post_type'			=> 'address',
				'author'			=> $customer->ID
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
	 	function store_get_customer_shipping_address( $customer = null ){

		 	// Get valid customer
		 	$customer = store_get_customer( $customer );

		 	// Still no customer? abort.
		 	if ( ! $customer ) return false;

		 	// set output and args
		 	$output = false;
		    $args = array(
				'posts_per_page'	=> 1,
				'meta_key'			=> '_store_address_is_shipping',
				'meta_value'		=> '1',
				'post_type'			=> 'address',
				'author'			=> $customer->ID
			);

			// Query for address
			$result = get_posts($args);

			// if anything came back, set output
			if ( ! empty($result) ) {
				$address = reset($result);
			}

			// return false if no address
			if ( empty($address) ) return false;

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
	 	function store_get_customer_addresses( $customer = null ){

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
	 * @Description: 
	 *
	 * @Param: 
	 * @Returns: 
	 */
	 	function store_login( $data ){

		 	if ( isset( $data['user_email'] ) ) {

			 	// get username by email address
			 	$user = get_user_by( 'email', $data['user_email'] );

			 	// if user was found
			 	if ( $user ) {

			 		// set user login to be found login
			 		$data['user_login'] = $user->data->user_login;
				}

				// remove email from data
				unset($data['user_email']);
		 	}

		 	return wp_signon( $data, true );
	 	};

	/*
	 * @Description: 
	 *
	 * @Param: 
	 * @Returns: 
	 */
	 	function store_get_guest(){

			// split home url
			$url_split = parse_url( home_url() );

			// set guest email
			$guest_email = 'guest@' . $url_split['host'];

			// check if user with that email exists
			return get_user_by( 'email', $guest_email );
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
			if ( is_string($customer) )
				if ( strstr($customer, '@') ) $field = 'email';

			// If customer is ID, search by ID
			if ( is_int(intval($customer)) ) $field = 'id';

			// Get specified customer
			return get_user_by( $field, intval($customer) );
	 	}

?>