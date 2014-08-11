<?php

	/*
	 * Make required store pages
	 */
		function create_post_type_store() {
			
			// Labels
			$labels = array(
	            'name'					=> __('Store', 'store'),
	            'singular_name'			=> __('Store', 'store'),
	            'add_new'				=> __('Add New Store Page', 'store'),
	            'add_new_item'			=> __('Add New Store Page', 'store'),
	            'edit'					=> __('Edit', 'store'),
	            'edit_item'				=> __('Edit Store Page', 'store'),
	            'new_item'				=> __('New Store Page', 'store'),
	            'view'					=> __('View Store Page', 'store'),
	            'view_item'				=> __('View Store Page', 'store'),
	            'search_items'			=> __('Search Store Pages', 'store'),
	            'not_found'				=> __('No Store Pages found', 'store'),
	            'not_found_in_trash'	=> __('No Store Pages found in Trash', 'store')
			);
	
			// Create store post type
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_in_menu'       => false,
				'query_var'          => true,
				'capability_type'    => 'page',
				'has_archive'        => true,
				'menu_icon'			 => 'dashicons-products',
				'hierarchical'       => true,
				'menu_position'      => 20,
				'exclude_from_search'=> true,
			    'supports'			 => array(
			        'title',
			        'editor',
			        'excerpt',
			        'thumbnail',
			        'page-attributes'
			    ),
				'rewrite'			 => array(
					'slug'	=> 'store'
				)
			);
			register_post_type( 'store', $args ); // We could make the post type a setting that could be changed/filtered?

		}
		add_action( 'init', 'create_post_type_store' );





	/*
	 * Make product post type
	 */
		function create_post_type_products() {
			
			// Labels
			$labels = array(
	            'name'					=> __('Products', 'products'),
	            'singular_name'			=> __('Product', 'products'),
	            'add_new'				=> __('Add New Product', 'products'),
	            'add_new_item'			=> __('Add New Product', 'products'),
	            'edit'					=> __('Edit', 'products'),
	            'edit_item'				=> __('Edit Product', 'products'),
	            'new_item'				=> __('New Product', 'products'),
	            'view'					=> __('View Products', 'products'),
	            'view_item'				=> __('View Product', 'products'),
	            'search_items'			=> __('Search Products', 'products'),
	            'not_found'				=> __('No Products found', 'products'),
	            'not_found_in_trash'	=> __('No Products found in Trash', 'products')
			);
	
			// Create products post type
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_in_menu'       => 'store/store-admin.php',
				'query_var'          => true,
				'capability_type'    => 'page',
				'has_archive'        => false,
				'menu_icon'			 => 'dashicons-tag',
				'hierarchical'       => true,
				'menu_position'      => 20,
			    'supports'			=> array(
			        'title',
			        'editor',
			        'excerpt',
			        'thumbnail',
			    ),
				'rewrite'			 => array(
					'slug'	=> 'products',
				)
			);
			register_post_type( 'products', $args ); // We could make the post type a setting that could be changed/filtered?
			
		}
		add_action( 'init', 'create_post_type_products' );



	
	
	/*
	 * Make orders/carts post type
	 */
		function create_post_type_orders() {
			
			// Labels
			$labels = array(
	            'name'					=> __('Orders', 'orders'),
	            'singular_name'			=> __('Order', 'orders'),
	            'add_new'				=> __('Add New Order', 'orders'),
	            'add_new_item'			=> __('Add New Order', 'orders'),
	            'edit'					=> __('Edit', 'orders'),
	            'edit_item'				=> __('Edit Order', 'orders'),
	            'new_item'				=> __('New Order', 'orders'),
	            'view'					=> __('View Orders', 'orders'),
	            'view_item'				=> __('View Order', 'orders'),
	            'search_items'			=> __('Search Orders', 'orders'),
	            'not_found'				=> __('No Orders found', 'orders'),
	            'not_found_in_trash'	=> __('No Orders found in Trash', 'orders')
			);

			// Create orders post type
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_in_menu'       => 'store/store-admin.php',
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'menu_icon'			 => 'dashicons-cart',
				'hierarchical'       => true,
				'exclude_from_search'=> true,				
				'menu_position'      => 20,
			    'supports'			=> array(
			        'title',
			        'editor',
			        'excerpt',
			        'thumbnail',
			        'page-attributes',
			        'author'
			    ),
				'rewrite'			 => array(
					'slug'	=> 'orders',
				)
			);
			register_post_type( 'orders', $args ); // We could make the post type a setting that could be changed/filtered?
			
		}
		add_action( 'init', 'create_post_type_orders' );