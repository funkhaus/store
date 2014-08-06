<?php

	/*
	 * Make required store pages
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
				'rewrite'           => array( 'slug' => 'storecategory' ),
			);
			register_taxonomy( 'store-category', array( 'products' ), $args );

		}
		add_action( 'init', 'store_create_taxonomy_category', 0 );