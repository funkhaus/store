<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


	// check if hook is scheduled, if not schedule it
	function store_set_schedule() {
		if ( ! wp_next_scheduled( 'store_hourly_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'store_hourly_cron');
		}
	}
	add_action( 'wp', 'store_set_schedule' );


	// On hook, run this
	function store_run_hourly() {

		// query for completed but untracked orders
		$query_args = array(
			'relation' => 'AND',
			array(
				'key' => '_store_order_tracking',
				'value' => '',
				'compare' => '='
			),
			array(
				'key' => '_store_transaction_info'
			)
		);
	    $args = array(
			'posts_per_page'	=> -1,
			'meta_query'		=> $query_args,
			'post_type'			=> 'orders'
		);
		$pending_orders = get_posts($args);

		// if untracked orders found, loop
		if ( $pending_orders ) {
			foreach( $pending_orders as $order ) {

				// check tracking for this order
				$response = store_shipwire_request_tracking( $order );

				if ( count($response['resource']['items']) ) {

					// Package has shipped, run hook
					do_action('store_order_shipped', $order);

					// set tracking into meta
					$meta = array();
					$meta['id'] = $response['resource']['items']['resource']['tracking'];
					$meta['url'] = $response['resource']['items']['resource']['url'];
					update_post_meta( $order->ID, '_store_order_tracking', $meta );
				}

			}
		}

	}
	add_action( 'store_hourly_cron', 'store_run_hourly' );