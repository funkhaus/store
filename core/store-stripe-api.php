<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: 
 *
 * @Param: MIXED,
 * @Returns: MIXED,
 */
	function store_stripe_run_charge( $token = null, $amount = null, $description = '' ){

		// Set secret key from option
		Stripe::setApiKey("sk_test_vJ8C25Ed5PcG9a2IOsWwXddz");

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = Stripe_Charge::create(array(
				"amount" => 1000, // amount in cents, this is $10.00
				"currency" => "usd",
				"card" => $token,
				"description" => $description)
			);
		} catch(Stripe_CardError $e) {
			// The card has been declined

		}

	};