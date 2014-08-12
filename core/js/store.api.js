var storeAPI = {

	// Globals
	homeURL: store_api_vars.homeURL,
	ajaxURL: store_api_vars.ajaxURL,
<<<<<<< HEAD

=======
    
>>>>>>> FETCH_HEAD
	/*
	 * @Description: Add a product to a cart
	 *
	 * @Param: ARRAY, array of parameters that maps to the store_add_product_to_cart() PHP function.
	 * @Returns: OBJ, the jQuery XMLHTTPRequest object from $.post()
	 */
	addToCart: function(data){

		// The PHP AJAX action hook to call
<<<<<<< HEAD
		data.action = 'add_to_cart';

		// Submit to PHP
=======
		data['action'] = 'add_to_cart';

		// Submit to PHP		
>>>>>>> FETCH_HEAD
		var jqxhr = jQuery.post( storeAPI.ajaxURL, data, function(results) {
			console.log(results);
		});

		return jqxhr;
<<<<<<< HEAD
	},

	getCartContents: function(){

		// The PHP AJAX action hook to call
    	data = {
	    	'action' : 'get_cart_contents'
    	};

		// Submit to PHP		
		var jqxhr = jQuery.post( storeAPI.ajaxURL, data);
		return jqxhr;
=======
>>>>>>> FETCH_HEAD
	}

};