var store_admin = {

	// Globals
	homeURL: store_admin_vars.homeURL,

	// Init things
    init: function() {
    	
    	// Update options input names
		store_admin.updateOptionsNames();
		store_admin.toggleMetaVariations();
		store_admin.disableVariations();
		store_admin.toggleDisabling();

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

	},

	toggleDisabling: function(){

		jQuery('input#store-enable-variant').change(function(){
			store_admin.disableVariations();
		});

	},

	disableVariations: function(){

		if ( ! jQuery('input#store-enable-variant').length ) return;

		if ( ! jQuery('input#store-enable-variant').attr('checked') ) {

			jQuery('#wpbody-content input, #wpbody-content textarea').not('#store-enable-variant').each(function(){
				jQuery(this).attr('disabled', false);
				jQuery('.postbox, .postarea, #titlediv').not('#store_enable_meta').css('opacity', 0.5);
			});

		} else {

			jQuery('#wpbody-content input, #wpbody-content textarea').not('#store-enable-variant').each(function(){
				jQuery(this).attr('disabled');
				jQuery('.postbox, .postarea, #titlediv').not('#store_enable_meta').css('opacity', 1);
			});

		}

	}

};

jQuery(document).ready(function(){

    store_admin.init();

});