<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Elex_DP_Dynamic_Pricing_Plugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $xa_dynamic_pricing_plugin    The ID of this plugin.
	 */
	private $xa_dynamic_pricing_plugin;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $xa_dynamic_pricing_plugin       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $xa_dynamic_pricing_plugin, $version ) {

		$this->xa_dynamic_pricing_plugin = $xa_dynamic_pricing_plugin;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function elex_dp_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in xa_dynamic_pricing_plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The xa_dynamic_pricing_plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->xa_dynamic_pricing_plugin, plugin_dir_url( __FILE__ ) . 'css/elex-dynamic-pricing-plugin-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->xa_dynamic_pricing_plugin . '_bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function elex_dp_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in xa_dynamic_pricing_plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The xa_dynamic_pricing_plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->xa_dynamic_pricing_plugin, plugin_dir_url( __FILE__ ) . 'js/elex-dynamic-pricing-plugin-admin.js', array( 'jquery' ), $this->version, false );
	}

}
