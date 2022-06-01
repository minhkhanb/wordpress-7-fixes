<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Elex_DP_Admin_Actions_Function' ) ) {

	class Elex_DP_Admin_Actions_Function {

		public function elex_dp_func_enqueue_search_product_enhanced_select() {
			global $wp_scripts;
			global $woocommerce;
			$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
			wp_enqueue_script( 'wc-enhanced-select' ); // if your are using recent versions
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/admin/css/elex-dynamic-pricing-plugin-admin.css', array(), $woocommerce_version );
			wp_enqueue_script( 'woocommerce_admin' );
		}

		public function elex_dp_func_enqueue_jquery() {
			wp_enqueue_style( 'jquery' );
		}

		public function elex_dp_func_enqueue_jquery_ui_datepicker() {
			global $woocommerce;
			$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
			//jQuery UI date picker file
			wp_enqueue_script( 'jquery-ui-datepicker' );
			//jQuery UI theme css file
			wp_enqueue_style( 'e2b-admin-ui-css', plugins_url( 'css/jquery-ui.css', __FILE__ ), array(), $woocommerce_version );
		}

		/// Creates New Sub Menu under main Woocommerce menu
		public function elex_dp_register_sub_menu() {
			add_submenu_page( 'woocommerce', 'Dynamic Pricing Main Page', __( 'Dynamic Pricing' ), 'manage_woocommerce', 'dynamic-pricing-main-page', array( $this, 'elex_dp_dynamic_pricing_admin_page' ) );
		}

		//Gets the plugin page and display to user
		public function elex_dp_dynamic_pricing_admin_page() {
			require( 'view/elex-dynamic-pricing-plugin-admin-display.php' );
		}
	}
}
add_action( 'wp_ajax_update_rules_arrangement', 'elex_dp_update_rules_arrangement' );

function elex_dp_update_rules_arrangement() {
	if ( ! empty( $_POST['xa-nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_POST['xa-nonce'] ), 'update_rules_arrangement' ) ) {
		wp_die( 'unauthorised access [unable to verify nonce]' );
	} else {
		$rules_order = ! empty( $_POST['rules-order'] ) ? sanitize_text_field( $_POST['rules-order'] ) : '';
		$rules_type = ! empty( $_POST['rules-type'] ) ? sanitize_text_field( $_POST['rules-type'] ) : '';
		$order_array = explode( ',', $rules_order );
		$ordered_product_rules = array();
		$rules = get_option( 'xa_dp_rules' );
		$product_rules = ! empty( $rules[ $rules_type ] ) ? $rules[ $rules_type ] : array();
		foreach ( $order_array as $index ) {
			if ( empty( $ordered_product_rules ) ) {
				$ordered_product_rules[1] = $product_rules[ $index ];
			} else {
				$ordered_product_rules[] = $product_rules[ $index ];
			}
		}
		if ( ! empty( $ordered_product_rules ) ) {
			$rules[ $rules_type ] = $ordered_product_rules;
			update_option( 'xa_dp_rules', $rules );
			wp_die( 'Arrangements Saved' );
		} else {
			wp_die( 'unable to save' );
		}
	}
}
