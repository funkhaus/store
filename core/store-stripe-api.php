<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Setup stripe PHP class
 */
	function store_stripe_server_setup(){

		// Get API keys from settings
		$stripe_options = get_option('store_st_settings');

		// Set secret key in stripe object
		Stripe::setApiKey($stripe_options['scrt']);

	}
	add_action('init', 'store_stripe_server_setup');


/*
 * Enqueue stripe.js library on front end
 */
	function store_stripe_enqueue_js() {
		wp_enqueue_script( 'store-stripe-js', 'https://js.stripe.com/v2/', array(), '2.0', false );
	}
	add_action( 'wp_enqueue_scripts', 'store_stripe_enqueue_js' );


/*
 * Inject publishable key into head tag on front end
 */
	function store_stripe_add_publishable(){

		// Get API keys from settings
		$stripe_options = get_option('store_st_settings');

		ob_start(); ?>
			<script type="text/javascript">
				/* <![CDATA[ */
				Stripe.setPublishableKey('<?php echo $stripe_options['pblsh']; ?>');
				/* ]]> */
			</script>
		<?php echo ob_get_clean();
	}
	add_action('wp_head', 'store_stripe_add_publishable');


/*
 * @Description: use stripe to run a credit card charge
 *
 * @Param: STRING, card token provided by the stripe api
 * @Param: INT, amount to be charged to the cart in cents i.e. 1500 = $15.00
 * @Returns: MIXED, true on a successful charge, or the stripe error object on failure
 */
	function store_stripe_run_charge( $token = null, $amount = null, $description = '' ){

		// Enforce required properties
		if ( ! intval($amount) || $token ) return false;

		// careful, this will actually charge the card
		try {
			$charge = Stripe_Charge::create(array(
				"amount" => $amount,
				"currency" => "usd",
				"card" => $token,
				"description" => $description)
			);
		} catch(Stripe_CardError $e) {
			// The card has been declined

		}

	};


/*
 * @Description: Save stripe customer ID to user account if possible
 *
 * @Param: MIXED, user ID or object of a valid cutomer
 * @Param: STRING, customer ID as provided by the Stripe API
 * @Returns: BOOL, true on success or false on failure
 */
	function store_stripe_save_customer($user, $customer_id){

		

	};