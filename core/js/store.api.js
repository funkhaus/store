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

		var jqxhr = jQuery.post( storeAPI.ajaxURL, data, function(results) {
			console.log('success', results);
		});

		return jqxhr;
	}

};