<?php

class Elex_DP_Dynamic_Pricing_Plugin_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function elex_dp_load_plugin_textdomain() {

		load_plugin_textdomain( 'xa-dynamic-pricing-plugin', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

}
