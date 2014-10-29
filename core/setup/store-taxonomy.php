<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	/*
	 * Make custom 'Store Category' taxonomy
	 */
		function store_create_taxonomy_category() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => _x( 'Store Categories', 'taxonomy general name' ),
				'singular_name'     => _x( 'Store Category', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Store Categories' ),
				'all_items'         => __( 'All Store Categories' ),
				'parent_item'       => __( 'Parent Store Category' ),
				'parent_item_colon' => __( 'Parent Store Category:' ),
				'edit_item'         => __( 'Edit Store Category' ),
				'update_item'       => __( 'Update Store Category' ),
				'add_new_item'      => __( 'Add New Store Category' ),
				'new_item_name'     => __( 'New Store Category' ),
				'menu_name'         => __( 'Store Categories' )
			);

			$args = array(
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_in_nav_menus'	=> true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'store/category' ),
			);
			register_taxonomy( 'store_category', array( 'product' ), $args );

		}
		add_action( 'init', 'store_create_taxonomy_category', 0 );


	/*
	 * Make status taxonomy for orders post type
	 */
		function store_create_taxonomy_status() {
			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => _x( 'Status', 'taxonomy general name' ),
				'singular_name'     => _x( 'Status', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Order Statuses' ),
				'all_items'         => __( 'All Order Statuses' ),
				'parent_item'       => __( 'Parent Order Status' ),
				'parent_item_colon' => __( 'Parent Order Status:' ),
				'edit_item'         => __( 'Edit Order Status' ),
				'update_item'       => __( 'Update Order Status' ),
				'add_new_item'      => __( 'Add New Order Status' ),
				'new_item_name'     => __( 'New Order Status' ),
				'menu_name'         => __( 'Order Statuses' )
			);

			$args = array(
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => false,
				'show_in_nav_menus'	=> false,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'store/status' ),
			);
			register_taxonomy( 'store_status', array( 'orders' ), $args );

		}
		add_action( 'init', 'store_create_taxonomy_status', 0 );


		function jrr_report_screen(){
			$screen = get_current_screen();

			echo $screen->id; exit;
		}
		//add_action('current_screen', 'jrr_report_screen');

	/*
	 * add action links to store_categories
	 */
		add_filter("manage_edit-store_category_columns", 'store_add_taxonomy_actions', 10, 3);

		function store_add_taxonomy_actions($cols) {
			$cols['order'] = 'Order';
			return $cols;
		}


		add_filter('manage_store_category_custom_column', 'manage_theme_columns', 10, 3);

		function manage_theme_columns($out, $column_name, $term_id) {

			switch ($column_name) {
				case 'order':
					$data = maybe_unserialize($theme->description);
					$out .= '<a href="' . admin_url() . 'admin.php?page=store_category_order&category=' . $term_id . '">Edit</a>';
					break;

				default:
					break;
			}
			return $out;
		}

?>