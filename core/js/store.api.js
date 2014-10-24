var storeAPI = {

	// Globals
	homeURL: store_api_vars.homeURL,
	ajaxURL: store_api_vars.ajaxURL,
	pluginURL: store_api_vars.pluginURL,

/*
 * @Description: Add a product to a cart
 *
 * @Param: ARRAY, array of parameters that maps to the store_add_product_to_cart() PHP function.
 * @Param: FUNCTION, callback of asynchronous call. Returns store-formatted json response
 * @Returns: nothing, get output from callback
 */
	addToCart: function(args, callback){

		// init data
		var data = {};

		// if jQuery object was passed, attempt to parse options via data-atts
		if ( args.jquery ) {

			// Set ID and qty
			data.product_id = args.data('productId') || args.find('*[data-product-id]').first().data('productId');
			data.quantity = args.find('*[data-product-quantity]').first().data('productQuantity') || args.find('*[data-product="quantity"]').val();

			// Init options
			data.options = {};

			// find DOM elements being used to identify options (usually <select>s)
			var $selects = args.find('*[data-product-option]') || args.find('*[data-product="option"]');

			// add each option
			$selects.each(function(){
				var key = jQuery(this).data('productOption') || jQuery(this).data('product').attr('name');
				data.options[key] = jQuery(this).val();
			});

		} else {
			data = args;
		}

		// The PHP AJAX action hook to call
		data.action = 'add_to_cart';

		// Submit to PHP
		jQuery.post( storeAPI.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback(results);
		});

		return;
	},

/*
 * @Description: Add a product to a cart
 *
 * @Param: ARRAY, array of parameters that maps to the store_add_product_to_cart() PHP function.
 * @Param: FUNCTION, callback of asynchronous call. Returns store-formatted json response
 * @Returns: nothing, get output from callback
 */
	removeFromCart: function(data, callback){

		// The PHP AJAX action hook to call
		data.action = 'remove_from_cart';

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback(results);
		});

		return;
	},

/*
 * @Description: 
 *
 * @Param: ARRAY, array of parameters that maps to the store_add_product_to_cart() PHP function.
 * @Param: FUNCTION, callback of asynchronous call. Returns store-formatted json response
 * @Returns: nothing, get output from callback
 */
	emptyCart: function(callback){

		// init data
		var data = {};

		// The PHP AJAX action hook to call
		data.action = 'empty_cart';

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback(results);
		});

		return;
	},

/*
 * @Description: Return contents of a cart as defined by the theme developer
 *
 * @Returns: Nothing, use callback
 */
	updateMiniCart: function(callback){

		// The PHP AJAX action hook to call
    	data = {
	    	'action' : 'get_mini_cart'
    	};

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(response){

			// if function was passed in, use as callback
			if ( typeof callback === 'function' ) callback(response);

			// If string was passed in, try and match it to a DOM element and replace it with the new cart
			if ( typeof callback === 'string' ) jQuery(callback).replaceWith(jQuery(response));

		});

		return;
	},

/*
 * @Description: Return contents of a custom template
 *
 * @Returns:
 */
	updateTemplate: function(templateName, callback){

		// The PHP AJAX action hook to call
    	data = {
	    	'action' 	: 'get_template',
	    	'template'	: templateName
    	};

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(response){

			// if function was passed in, use as callback
			if ( typeof callback === 'function' ) callback(response);

			// If string was passed in, try and match it to a DOM element and replace it with the new cart
			if ( typeof callback === 'string' ) jQuery(callback).replaceWith(jQuery(response));

		});

		return;
	},

/*
 * @Description: Log a user in via AJAX
 * @Param: object with login information
 *
 * @Returns: 
 */
	login: function(data, callback){

/*
		{
			'email': 'YourEmail',
			'password': 'YourPassword',
			'security': 'ValueOfSecurityInput'
		}
*/

		// The PHP AJAX action hook to call
    	data.action = 'sign_user_on';

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(response){

			// if function was passed in, use as callback
			if ( typeof callback === 'function' ) callback(response);

		});

		return;
	},

/*
 * @Description:
 * @Param:
 *
 * @Returns: 
 */
	createCustomer: function(data, callback){

/*
		{
			'email': 'YourEmail',
			'password': 'YourPassword',
			'nonce_code': 'ValueOfNonceInput'
		}
*/

		// The PHP AJAX action hook to call
    	data.action = 'create_customer';

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(response){

			// if function was passed in, use as callback
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

				if ( typeof callback === 'function' ) callback(json, token);
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
			jQuery.post( this.ajaxURL, data, function(results) {
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
		this.encryptCard(cardData, function(response, token){

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
 * @Description: Helper function to flexibly retrieve a valid address from a number of different formats
 *
 * @Param: OBJ, can be a jQuery object containing address inputs, or an object with address formatted property > value pairs
 * @Returns: 
 */
 	parseAddress: function(address){

	 	if ( ! address ) return false;

	 	// init output
	 	var output = {};

	 	// Set each address field, default to an input with the proper data-ship value set
	 	output.line_1	= address.line_1 	|| address.find('[data-address="line_1"]').val();
	 	output.line_2	= address.line_2 	|| address.find('[data-address="line_2"]').val();
	 	output.city		= address.city 		|| address.find('[data-address="city"]').val();
	 	output.state	= address.state 	|| address.find('[data-address="state"]').val();
	 	output.country	= address.country 	|| address.find('[data-address="country"]').val();
	 	output.zip		= address.zip 		|| address.find('[data-address="zip"]').val();

	 	return output;
 	},

/*
 * @Description:
 *
 * @Param:
 * @Param:
 * @Returns:
 */
 	submitOrder: function( args, callback ){

		// validate shipping and billing addresses
		args.shipping_address = this.parseAddress(args.shipping_address) || false;
		args.billing_address = this.parseAddress(args.billing_address) || args.shipping_address;

		// set proper action value
		args.action = 'submit_order';

		// if cc is set
		if ( typeof args.credit_card !== 'undefined' ) {

			// get token from stripe.js
			this.encryptCard(args.credit_card, function(response, token){

				// if card encryption failed, callback with failure message
				if ( ! response.success ) {
					if ( typeof callback === 'function' ) callback(response);
					return;
				}

				// remove card info from args
				delete args.credit_card;

				// Set stripe token
				args.stripe_token = token;

				// Submit to PHP
				jQuery.post( storeAPI.ajaxURL, args, function(results) {
					if ( typeof callback === 'function' ) callback(results);
				});

			});

		// if stripe token is set...
		} else if( typeof args.stripe_token !== 'undefined' ) {

			// Submit to PHP
			jQuery.post( storeAPI.ajaxURL, args, function(results) {
				if ( typeof callback === 'function' ) callback(results);
			});

		}

		return;
	},


/*
 * @Description: 
 *
 * @Param: 
 * @Param: 
 * @Returns: 
 */
 	saveAddress: function( address, shipping, billing, callback ){

		// set data
		var data = {
			'action'	:	'customer_address',
			'address'	:	this.parseAddress(address),
			'shipping'	:	Boolean(shipping),
			'billing'	:	Boolean(billing)
		};

		jQuery.post( this.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback(results);
		});
	},


/*
 * @Description: Get quote from shipwire based on a user-inputted address
 *
 * @Param: MIXED, can be a jquery element of the whole <form> (with proper data-ship atts) or an object of input values (i.e. { number: $('.address-line-1').val(), cvc: $('.address-zip').val() } )
 * @Param: FUNCTION, callback of asychronous call, returns store-formatted json response
 * @Returns: nothing, response information goes through callback
 */
 	shippingQuote: function(address, callback){

	 	// Set context for callback
	 	var context = window;
	 	if (address.jquery) context = address;

	 	// format address properly
	 	address = this.parseAddress(address);

	 	// set data
	 	var data = {
		 	'action'	:	'shipwire_quote',
		 	'address'	:	address
		};

		// Submit to PHP
		jQuery.post( this.ajaxURL, data, function(results) {
			if ( typeof callback === 'function' ) callback.apply(context, [results]);
		});

		return;
 	},


// ---------------- Product Matrix API ---------------- //


 	matrix: {

	 	// holds all product data
		data: {},

	/*
	 * @Description: Helper function used to check if an object has a set of all required properties
	 *
	 * @Param: OBJ, object to check properties of
	 * @Param: OBJ, set of required options to search for
	 * @Returns: BOOL, true if all required options are matched, false if not
	 */
		matchOptions: function(obj, options){

			// init output
			var output = true;

			// loop through options
			for ( var prop in options ) {

				// If target post does not have a match for this option, break and return false
				if ( obj[prop] != options[prop] ) {
					output = false;
					break;
				}
			}

			// if all options are matched, output will still be true
			return output;
		},

	/*
	 * @Description: Flexible function used to query for products out of storeAPI.matrix.data
	 *
	 * @Param: MIXED, can be an integer of a product ID, an object of properties to query by, or an array of product IDs
	 * @Returns: ARRAY, all objects that match the criteria provided
	 */
		getProducts: function(options){

			// init vars
			var output = [];
			var products = this.data;

			// If integer was provided...
			if ( options === parseInt(options) ) {

				if ( products.hasOwnProperty(options) ) {
					// try to set as top-level product
					output = [products[options]];
				}

				// if it didn't work...
				if ( output.length === 0 ) {

					// loop through products
					for ( var id in products ) {

						// when a match is found...
						if ( options in products[id].variants ) {

							// set output and break
							output.push(products[id].variants[options]);
							break;

						}
					}
				}
			}

			// If object was provided...
			if ( typeof options === 'object' ) {

				// Loop through products
				for ( var id in products ){

					// loop through variants
					for ( var varId in products[id].variants ) {

						// no options? skip
						if ( ! options in products[id].variants[varId] ) continue;

						// if matchOptions comes back true, push this product to output
						if ( this.matchOptions(products[id].variants[varId].options, options) ) {
							output.push( products[id].variants[varId] );
						}

					}
				}

			}

			// if empty array, set to false
			if ( output.length === 0 ) output = false;
			return output;
		},


	/*
	 * @Description: recursive search for properies through array or object
	 *
	 * @Param:
	 * @Returns:
	 */
		searchFor: function(haystack, needle){

			var found = [];
			function recursiveSearch( obj ){

				if ( typeof obj === 'object' && obj.hasOwnProperty(needle) ) found.push(obj);

				if ( typeof obj === 'array' || typeof obj === 'object' ) {

					for ( var prop in obj ) {

						recursiveSearch(obj[prop]);

					}
				}
			}
			recursiveSearch( haystack );

			return found;
		}

	}
};