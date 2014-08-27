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
	getCartContents: function(){

		// The PHP AJAX action hook to call
    	data = {
	    	'action' : 'get_cart_contents'
    	};

		// Submit to PHP
		var jqxhr = jQuery.post( storeAPI.ajaxURL, data);
		return jqxhr;
	}

/*
 * @Description: submit a payment to the store ajax api
 *
 * @Param: MIXED, can be a string of the card token or the entire response object returned by stripe.js (createToken)
 * @Returns: 
 */
 	submitPayment: function( tokenData ){

	 	var jqxhr = false;
	 	if ( typeof tokenData === 'object' ) tokenData = tokenData.id;
	 	if ( typeof tokenData === 'string' ) {

		 	data = {
			 	'token' : tokenData
		 	};

			// Submit to PHP
			jqxhr = jQuery.post( storeAPI.ajaxURL, data, function(results) {
				console.log(results);
			});

	 	}
		return jqxhr;

 	}

};