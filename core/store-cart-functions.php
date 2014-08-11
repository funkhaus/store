<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 


/*
 * @Description: Return the ID of a users active cart
 *
 * @Param: None
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_active_cart_id(){
		
		// Declare vars		
		$active_cart_id;

		// First, check if logged in
		if ( is_user_logged_in() ) {
			// Get active cart ID from user meta			
			$active_cart_id = get_user_meta( $user->ID, 'store_active_cart_id', true );

		} else {
			// If not logged in, check cookie for saved ID
			if( isset($_COOKIE['store_active_cart_id']) ) {
				$active_cart_id = $_COOKIE['store_active_cart_id'];				
			}
		}

		// Is it empty?
		if( empty($active_cart_id) ) {
			$active_cart_id = false;
		}
		
		// Return cart ID, or false if none active
		return $active_cart_id;
		
	};
	
	
/*
 * @Description: Create a cart, set it as active to logged in user, or to a cookie for guests
 *
 * @Param: INT, a user ID to attribute the cart to. Not required.
 * @Returns: INT, integer value of new cart ID
 */
	function store_create_active_cart($user_id = null){

		// Create a cart post
		
		// If logged in, set author to logged in user
			// Save cart ID to user meta

		// If no user ID, attribute it to Guest user		
			// Save cart ID to cookie


		// Return cart post ID number

	};	
	
	
/*
 * @Description: Save a given cart ID to a cookie. This can only be run before headers are sent.
 *
 * @Param: INT, cart post ID to set as active 
 * @Returns: BOOL, true if saved, false if not saved
 */
	function store_save_cart_id_to_cookie($cart_id = null){
		// If no cart ID set, then return false
		if( !empty($cart_id) ) {
			return setcookie('store_active_cart_id', $cart_id, time()+3600*24*30, '/', home_url(), false);  /* expire in 30 days */						
		} else {
			return false;
		}
	};
	


/*
 * @Description: Save a given cart ID to a users profile
 *
 * @Param: INT, user ID to save to, if not set users current logged in user 
 * @Param: INT, cart post ID to set as active 
 * @Returns: BOOL, true if saved, false if not saved
 */
	function store_save_cart_id_to_user($user_id = null, $cart_id = null){
		// If no cart ID set then return false
		
		// If no user ID set, then use logged in user
		
		// Save cart ID to user meta, return true
	};



/*
 * @Description: Save a given product ID to a cart
 *
 * @Param: INT, product ID to add to cart. If not set, returns false.
 * @Param: INT, quantity of product to add to cart 
 * @Param: INT, cart post ID to set as active. If not set, then use active cart
 * @Returns: BOOL, true if saved, false if not saved
 */
	function store_add_product_to_cart($product_id = null, $quantity = 1, $cart_id = null){
	
		// If no product ID set, or not an INT, then return false.
		
		// Check that quantity of product_id exists, else return false
		
		// If cart ID set, then use that cart ID

		// If no cart ID set, save to active cart
		$cart_id = store_get_active_cart_id();

		// Get cart product meta as array

		// Add product ID to array

		// Save meta array

		// Return result of add_post_meta
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


?>