<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Add ten minute interval into CRON schedule
	function store_add_tenmin( $schedules ) {

		$schedules['everyten'] = array(
			'interval' 	=> 600,
			'display' 	=> __('Every Ten Minutes')
		);
		return $schedules;

	}
	add_filter( 'cron_schedules', 'store_add_tenmin' );


	// check if hooks are scheduled, if not schedule them
	function store_set_schedule() {
		
		//wp_clear_scheduled_hook( 'store_ten_cron');
		
		if ( ! wp_next_scheduled( 'store_hourly_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'store_hourly_cron');
		}
		if ( ! wp_next_scheduled( 'store_ten_cron' ) ) {
			wp_schedule_event( time(), 'everyten', 'store_ten_cron');
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

				/*
				 * @Hook: store_order_shipped, fires when an order has been shipped by shipwire
				 *
				 * @Param: OBJ, the full post object of the shipped order
				 */
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


	// Runs every ten minutes
	function store_run_every_ten() {

		// full inventory update
		$report = store_update_shipwire_inventory();

	}
	add_action( 'store_ten_cron', 'store_run_every_ten' );

