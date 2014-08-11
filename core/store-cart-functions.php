<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 


/*
 * @Description: Return the ID of a users active cart
 *
 * @Param: None
 * @Returns: MIXED, integer value of quantity on success, bool false on failure. Will return false if active cart no longer exists (been deleted or expired)
 */
	function store_get_active_cart_id(){
		
		// Declare vars		
		$active_cart_id;

		// First, check if logged in
		if ( is_user_logged_in() ) {
			// Get active cart ID from user meta			
			$active_cart_id = get_user_meta( get_current_user_id(), '_store_active_cart_id', true );

		} else {
			// If not logged in, check cookie for saved ID
			if( isset($_COOKIE['_store_active_cart_id']) ) {
				$active_cart_id = $_COOKIE['_store_active_cart_id'];				
			}
		}

		// Is it empty?
		if( empty($active_cart_id) ) {
			$active_cart_id = false;
		}
		
		// Check to see that cart still exsits (it may have been deleted)
		if( store_is_cart_available($active_cart_id) ) {
			// Retrun ID
			return $active_cart_id;			
		} else {
			return false;			
		}
		
	};
	
	
/*
 * @Description: Create a cart, set it as active to logged in user, or to a cookie for guests
 *
 * @Param: INT, a user ID to attribute the cart to. Not required.
 * @Returns: INT, integer value of new cart ID or 0 (same vaule as wp_insert_post).
 */
	function store_create_active_cart($user_id = null){
		
		// If no user ID set, try to figure one out
		if( empty($user_id) || !is_int($user_id) ) {
			// Get logged in user ID
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();

			} else {
				// Attribute to guest user
				$user_id = 2; // Guest user made by Drew. Should get from settings in future.

			}
		}

		// Create a cart post
		$args = array(
			'post_status'    => 'publish',
			'post_type'      => 'orders', // This should probably be changed to "order"
			'ping_status'    => 'closed', // This will need to be a custom status
			'comment_status' => 'closed',
			'post_author'	 => $user_id,
			'post_title'	 => 'Cart #?',
			'post_content'	 => ''
		);
		$cart_id = wp_insert_post($args, true);
		
		// Update post if it was created
		if( $cart_id ) {
			$updated_cart = array(
				'ID'           		=> $cart_id,
				'post_title' 		=> 'Cart #'.$cart_id,
				'post_name'			=> 'cart-'.$cart_id
			);
			wp_update_post( $updated_cart );			
			
			// Set as active
			store_set_active_cart_id($user_id, $cart_id);

		}

		// Return cart post ID number
		return $cart_id;
	};	
	
	
/*
 * @Description: Save a given cart ID to a user or to a cookie for not logged in guests. This can only be run before headers are sent.
 *
 * @Param: INT, user ID to save to, if not set uses current logged in user. 
 * @Param: INT, cart post ID to set as active. Required.
 * @Returns: MIXED, returns update_user_meta() value if logged in, else setcookie() value
 */
 
 	function store_set_active_cart_id($user_id = null, $cart_id = null) {

		// If no cart ID set then return false
		if( empty($cart_id) ) {
			return false;		
		}

	 	if ( is_user_logged_in() ) {		 	
			// If no user ID, use logged in user
			if( empty($user_id) ) {
				$user_id = get_current_user_id();
				
			} else {
				// Verify user exsits
				$user = get_userdata($user_id);
				if(!$user) {
					// No user exists, abort
					return false;
				}
			}
			
			// Save cart id to user
			return update_user_meta( $user_id, '_store_active_cart_id', $cart_id);
			
	 	} else {
		 	// Not logged in, save to cookie
			return setcookie('_store_active_cart_id', $cart_id, time()+3600*24*30, '/', store_get_cookie_url(), false);  /* expire in 30 days */

	 	}
 	}
 
 
 
 
/*
 * @Description: Save a given product ID to a cart
 *
 * @Param: INT, product ID to add to cart. Required.
 * @Param: INT, quantity of product to add to cart. Defaults to 1.
 * @Param: INT, cart post ID to add product too. If not set, then uses active cart.
 * @Returns: BOOL, true if saved, false if not saved
 */
	function store_add_product_to_cart($product_id = null, $quantity = 1, $cart_id = null){
		
		// If no product ID set, or not an INT, then return false.
		if( empty($product_id) || !is_int($product_id) ) {
			return false;
		}
		
		// Check product is avaible to add to cart
		if( !store_is_product_available($product_id) ) {
			return false;			
		}
		
		// If cart ID set, then try to use that cart ID
		if( isset($cart_id) ) {
			$cart_id = store_get_cart();
		}
		
		// If set cart has expired, use active cart
		if( !store_is_cart_available($cart_id) ) {
			$cart_id = store_get_active_cart_id();
		}

		// If still no cart, make one!
		if( empty($cart_id) ) {
			$active_cart_id = store_create_active_cart($active_cart_id);
		}

		// Get cart product meta as array
		$products = get_post_meta($active_cart_id, '_store_cart_products', true);

		// Add product ID to array
		$products[$product_id] = array(
			'qty'		=> $quantity,
			'price'		=> 'NEED_TO_BUILD_LATER'
		);

		// Save meta array, return result
		return update_post_meta($active_cart_id, '_store_cart_products', $products);
		die;
	};
	

/*
 * @Description: Remove a given product ID from a cart
 *
 * @Param: INT, product ID to add to cart. If not set, returns false.
 * @Param: INT, cart post ID to set as active. If not set, then use active cart
 * @Returns: BOOL, true if saved, false if not saved
 */
	function store_remove_product_from_cart($product_id = null, $cart_id = null){
	
		// If no product ID set, or not an INT, then return false.

		// If cart ID set, then remove to that cart ID

		// If no cart ID set, save to active cart
		$cart_id = store_get_active_cart_id();

		// Get cart product meta as array

		// Remove product ID from array

		// Update meta array

		// Returns result of update_post_meta
	};	



/*
 * @Description: Get the cart post object (uses get_post).
 *
 * @Param: INT, cart ID. If empty trys to get current active cart.
 * @Returns: MIXED, returns a WP_Post object, or null. Just like get_post().
 */
 	function store_get_cart($cart_id = null) {
	 	if( empty($cart_id) ) {
			$cart_id = store_get_active_cart_id();		 	
	 	}

	 	return get_post($cart_id);
 	}


/*
 * @Description: Helper function used to set the cookie directory URL.
 *
 * @Returns: STRING, a parsed version of home_url().
 */
 	function store_get_cookie_url() {
	 	$url = parse_url( home_url() );
	 	return $url['host'];
 	}
?>