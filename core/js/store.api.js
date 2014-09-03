var storeAPI = {

	// Globals
	homeURL: store_api_vars.homeURL,
	ajaxURL: store_api_vars.ajaxURL,

/*
 * @Description: Add a product to a cart
 *
 * @Param: ARRAY, array of parameters that maps to the store_add_product_to_cart() PHP function.
 * @Returns: OBJ, the jQuery XMLHTTPRequest object from $.post()
 */
	addToCart: function(data){

		// The PHP AJAX action hook to call
		data.action = 'add_to_cart';

		// Submit to PHP
		var jqxhr = jQuery.post( storeAPI.ajaxURL, data, function(results) {
			console.log(results);
		});

		return jqxhr;
	},

/*
 * @Description: Return contents of a cart as defined by the theme developer
 *
 * @Returns: OBJ, the jQuery XMLHTTPRequest object from $.post()
 */
	getMiniCart: function(callback){

		// The PHP AJAX action hook to call
    	data = {
	    	'action' : 'get_mini_cart'
    	};

		// Submit to PHP
		jQuery.post( storeAPI.ajaxURL, data, function(response){
			if ( typeof callback === 'function' ) callback(response);
		});

		return;
	},

/*
 * @Description: generate a token using stripe.js, this is a just a wrapper for consistency
 *
 * @Param: MIXED, can be a jquery element of the whole <form> or an object of input values (i.e. { number: $('.card-number').val(), cvc: $('.card-cvc').val() } )
 * @Param: FUNCTION, callback of asynchronous call. receives two parameters, store-formatted json response and a string of the stripe token on success
 * @Returns: nothing, response information goes through callback
 */
 	encryptCard: function( cardData, callback ){

	 	// Asynchronous stripe call
	 	Stripe.card.createToken( cardData, function(status, response){

	 		// run callback if provided
		 	if ( typeof callback === 'function' ) {
			 	var json = {};
			 	var token = false;

			 	// set standardized response message
			 	json.success = false;
			 	if ( status === 200 ) {
			 		json.success = true;
			 		json.code = 'OK';
			 		json.message = 'Card successfuly tokenized.';
			 		token = response.id;
			 	} else {
				 	json.code = response.error.code;
				 	json.message = response.error.message;
			 	}

			 	json.vendor_response = response;
			 	json.vendor_response.vendor = 'stripe';

			 	callback(json, token);
		 	}
	 	});

	 	return;

 	},

/*
 * @Description: submit a payment to the store ajax api (server side)
 *
 * @Param: MIXED, can be a string of the card token or the entire response object returned by stripe.js (createToken)
 * @Param: FUNCTION, callback of asychronous call, returns store-formatted json response of store_ajax_stripe_charge()
 * @Returns: nothing, response information goes through callback
 */
 	submitPayment: function( tokenData, callback ){

	 	// If full token response was given, set token to be ID
	 	if ( typeof tokenData === 'object' ) tokenData = tokenData.id;

	 	// If token is string, run charge via ajax
	 	if ( typeof tokenData === 'string' ) {

		 	data = {
		 		'action'	: 'stripe_charge',
			 	'token'		: tokenData
		 	};

			// Submit to PHP
			jQuery.post( storeAPI.ajaxURL, data, function(results) {
				if ( typeof callback === 'function' ) callback(results);
			});

	 	}

		return;
 	},


/*
 * @Description: high level pay function, get token through stripe.js and then run payment through the php sdk
 *
 * @Param: MIXED, can be a jquery element of the whole <form> or an object of input values (i.e. { number: $('.card-number').val(), cvc: $('.card-cvc').val() } )
 * @Param: FUNCTION, callback of asychronous call, returns store-formatted json response
 * @Returns: nothing, response information goes through callback
 */
 	pay: function(cardData, callback){

	 	// get token from stripe.js
		storeAPI.encryptCard(cardData, function(response, token){

			// if response failed, return full error message
			if ( ! response.success ){
				if ( typeof callback === 'function' ) callback(response);
				return;
			}

			// take response and submit it for payment, then run callback
			storeAPI.submitPayment(token, function(results){
				if ( typeof callback === 'function' ) callback(results);
			});

		});

		return;
 	},


/*
 * @Description: Get quote from shipwire based on a user-inputted address
 *
 * @Param: MIXED, can be a jquery element of the whole <form> (with proper data-ship atts) or an object of input values (i.e. { number: $('.address-line-1').val(), cvc: $('.address-zip').val() } )
 * @Param: FUNCTION, callback of asychronous call, returns store-formatted json response
 * @Returns: nothing, response information goes through callback
 */
 	shippingQuote: function(address, callback){

	 	// set output variable
	 	var addressFields = {};

	 	// Set each address field, default to an input with the proper data-ship value set
	 	addressFields.line_1	= address.line_1 	|| address.find('input[data-ship="line_1"]').val();
	 	addressFields.line_2	= address.line_2 	|| address.find('input[data-ship="line_2"]').val();
	 	addressFields.city		= address.city 		|| address.find('input[data-ship="city"]').val();
	 	addressFields.state		= address.state 	|| address.find('input[data-ship="state"]').val();
	 	addressFields.country	= address.country 	|| address.find('input[data-ship="country"]').val();
	 	addressFields.zip		= address.zip 		|| address.find('input[data-ship="zip"]').val();

	 	// set data
	 	data = {
		 	'action'	:	'shipwire_quote',
		 	'address'	:	addressFields
		};

		// Submit to PHP
		jQuery.post( storeAPI.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback(results);
		});

		return;
 	}

};