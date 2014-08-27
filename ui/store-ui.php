<?php 

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	/*
	 * Plugin Scripts and styles
	 */
		function store_admin_scripts() {
			wp_register_style( 'store_admin_css', plugins_url( 'ui/css/store.admin.css', dirname(__FILE__) ));
			wp_register_script( 'store_admin_js', plugins_url( 'ui/js/store.admin.js', dirname(__FILE__) ));

			wp_enqueue_style( 'store_admin_css');
			wp_enqueue_script( 'store_admin_js');

	        // Setup JS variables in scripts
			wp_localize_script('store_admin_js', 'store_admin_vars', 
				array(
					'homeURL'		=> home_url()
				)
			);

		}
		add_action( 'admin_enqueue_scripts', 'store_admin_scripts' );


	/*
	 * Add Meta boxes
	 */
		include_once( trailingslashit( pp() ) . 'ui/store-metaboxes.php');

	/*
	 * Add top-level menu page for 'Store'
	 */
		function store_register_main_menu(){
		    add_menu_page( 'Store', 'Store', 'manage_options', 'store/store-admin.php', '', 'dashicons-products', 59 );
		}
		add_action( 'admin_menu', 'store_register_main_menu' );


	/*
	 * Add categories as sub-menu item
	 */
		function store_register_categories_menu() {
			add_submenu_page( 'store/store-admin.php', 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=store_category' );
		}
		add_action('admin_menu', 'store_register_categories_menu');

	/*
	 * Add settings sub-menu page
	 */
		function store_register_settings_menu() {
			add_submenu_page( 'store/store-admin.php', 'Settings', 'Settings', 'manage_options', 'store-settings', 'store_output_settings_page' ); 
		}
		add_action('admin_menu', 'store_register_settings_menu');

	/*
	 * Output HTML for settings page
	 */
		function store_output_settings_page() {

			include_once( trailingslashit( pp() ) . 'ui/store-settings.php');

		}

	/*
	 * Register these options on settings save
	 */
		function store_register_settings(){
			register_setting('store_settings', 'store_sw_settings');
			register_setting('store_settings', 'store_st_settings');
		}
		add_action('admin_init', 'store_register_settings');

?>