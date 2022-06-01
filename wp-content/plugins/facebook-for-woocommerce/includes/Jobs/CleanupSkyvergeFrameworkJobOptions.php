<?php

namespace SkyVerge\WooCommerce\Facebook\Jobs;

use SkyVerge\WooCommerce\Facebook\Utilities\Heartbeat;

defined( 'ABSPATH' ) || exit;

/**
 * Class CleanupSkyvergeFrameworkJobOptions
 *
 * Responsible for cleaning up old completed background sync jobs from SkyVerge background job system.
 * Each job is represented by a row in wp_options table, and these can accumulate over time.
 *
 * Note - this is closely coupled to the SkyVerge background job system, and is essentially a patch to improve it.
 *
 * @see SV_WP_Background_Job_Handler
 *
 * @since 2.6.0
 */
class CleanupSkyvergeFrameworkJobOptions {

	/**
	 * Add hooks.
	 */
	public function init() {
		// Register our cleanup routine to run regularly.
		add_action( Heartbeat::DAILY, array( $this, 'clean_up_old_completed_options' ) );
	}

	/**
	 * Delete old completed product sync job rows from options table.
	 *
	 * Logic and database query are adapted from SV_WP_Background_Job_Handler::get_jobs().
	 *
	 * @see SV_WP_Background_Job_Handler
	 * @see Products\Sync\Background
	 */
	public function clean_up_old_completed_options() {
		global $wpdb;

		/**
		 * Query notes:
		 * - Matching product sync job only (Products\Sync\Background class).
		 * - Matching "completed" status by sniffing json option value.
		 * - Order by lowest id, to delete older rows first.
		 * - Limit number of rows (periodic task will eventually remove all).
		 * Using `get_results` so we can limit number of items; `delete` doesn't allow this.
		 */
		$wpdb->query(
			"DELETE
			FROM {$wpdb->options}
			WHERE option_name LIKE 'wc_facebook_background_product_sync_job_%'
			AND option_value LIKE '%\"status\":\"completed\"%'
			ORDER BY option_id ASC
			LIMIT 250"
		);
	}

}
