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
		if( store_add_product_to_cart($product_id, $quantity, $cart_id) ) {
			echo 1;
		} else {
			echo 0;
		}
		die;
	}

?>