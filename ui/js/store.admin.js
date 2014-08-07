var store = {

	// Globals
	homeURL: store_vars.homeURL,

	// Init things
    init: function() {
    	
    	// Update options input names
		store.update_options_names();
    },

	update_options_names: function(){
		
		// Update the meta input "names" to be what the user is typing
		jQuery('#store_options_meta .store-option').change(function(){
			jQuery(this).siblings('.store-option-variant').attr( 'name', '_store_meta_' + jQuery(this).val() );
		});
	}

};

jQuery(document).ready(function(){

    store.init();

});