<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Setup stripe PHP class on init
 */
	function store_stripe_server_setup(){

		// Get API keys from settings
		$stripe_options = get_option('store_st_settings');

		// Set secret key in stripe object
		Stripe::setApiKey($stripe_options['scrt']);

	}
	add_action('init', 'store_stripe_server_setup', 5);


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
	function store_stripe_add_publishable_key(){

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
	add_action('wp_head', 'store_stripe_add_publishable_key');


/*
 * @Description: use stripe to run a credit card charge
 *
 * @Param: STRING, card token provided by the stripe api. Required
 * @Param: INT, amount to be charged to the cart, defaults to calculated total of current cart. measured in cents i.e. 1500 = $15.00. Optional.
 * @Returns: MIXED, true on a successful charge, or the stripe error object on failure. Optional.
 */
	function store_stripe_run_charge( $token = null, $amount = null, $description = '' ){

		// set default amount to be calculated cart total
		if ( ! is_int($amount) ) $amount = store_calculate_cart_total();

		// If object was given, attempt to get token from object
		if ( is_object($token) ) $token = (string) $token->id;

		$args = array(
			"amount"		=> $amount,
			"currency"		=> "usd",
			"card"			=> $token,
			"description"	=> $description
		);

		$output = false;

		try {
			// attempt to run charge, set into output
			$output = Stripe_Charge::create($args);
			$output = $output->__toArray(true);

		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			$output = $e->getJsonBody();

		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			$output = $e->getJsonBody();

		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$output = $e->getJsonBody();

		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			$output = $e->getJsonBody();

		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			$output = $e->getJsonBody();

		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			$output = $e->getJsonBody();

		}

		return $output;

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