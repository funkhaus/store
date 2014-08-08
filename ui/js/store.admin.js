var store = {

	// Globals
	homeURL: store_vars.homeURL,

	// Init things
    init: function() {
    	
    	// Update options input names
		store.updateOptionsNames();
		store.toggleMetaVariations();
    },

	updateOptionsNames: function(){

		// Update the meta input "names" to be what the user is typing
		jQuery('#store_options_meta .store-option').change(function(){
			jQuery(this).siblings('.store-option-variant').attr( 'name', '_store_meta_' + jQuery(this).val() );
		});

	},

	toggleMetaVariations: function(){

		jQuery('#store-toggle-options').click(function(e){
			e.preventDefault();

			jQuery('#store-variation-table-wrapper').toggleClass('hidden');
			jQuery('#store-edit-options').toggleClass('hidden');
		});

	}

};

jQuery(document).ready(function(){

    store.init();

});