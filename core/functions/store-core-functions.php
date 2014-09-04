<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get store conditionals
	include_once( trailingslashit( pp() ) . 'core/functions/store-conditionals.php' );

	// Get cart functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-cart-functions.php' );

	// Get order functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-order-functions.php' );

	// Get product functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-product-functions.php' );

	// Get product functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-page-functions.php' );

	// Get user functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-customer-functions.php' );

	// Get address functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-address-functions.php' );