<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( isset( $_REQUEST['submit'] ) ) {
	$en_modes = ! empty( $_REQUEST['enabled_modes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['enabled_modes'] ) ) : array();
	$enabled_modes = array();
	foreach ( $en_modes as $emode ) {
		if ( 'product_rules' == $emode || 'category_rules' == $emode ) {
			$enabled_modes[] = $emode;
		}
	}
	//$enabled_modes = array_intersect( array( 'product_rules','category_rules' ),( array )$enabled_modes );
	$prev_data = array(
		'product_rules_on_off' => in_array( 'product_rules', $enabled_modes ) ? 'enable' : 'disable',
		'combinational_rules_on_off' => 'disable',
		'category_rules_on_off' => in_array( 'category_rules', $enabled_modes ) ? 'enable' : 'disable',
		'cat_comb_rules_on_off' => 'disable',
		'cart_rules_on_off' => 'disable',
		'buy_and_get_free_rules_on_off' => 'disable',
		'price_table_on_off' => ! empty( $_REQUEST['price_table_on_off'] ) ? sanitize_text_field( $_REQUEST['price_table_on_off'] ) : 'disable',
		'xa_product_add_on_option' => ! empty( $_REQUEST['xa_product_add_on_option'] ) ? sanitize_text_field( $_REQUEST['xa_product_add_on_option'] ) : 'disable',
		'offer_table_on_off' => 'disable',
		'auto_add_free_product_on_off' => ! empty( $_REQUEST['auto_add_free_product_on_off'] ) ? sanitize_text_field( $_REQUEST['auto_add_free_product_on_off'] ) : 'enable',
		'pricing_table_qnty_shrtcode' => ! empty( $_REQUEST['pricing_table_qnty_shrtcode'] ) ? sanitize_text_field( $_REQUEST['pricing_table_qnty_shrtcode'] ) : 'nos.',
		'pricing_table_position' => ! empty( $_REQUEST['pricing_table_position'] ) ? sanitize_text_field( $_REQUEST['pricing_table_position'] ) : 'woocommerce_before_add_to_cart_button',
		'offer_table_position' => ! empty( $_REQUEST['offer_table_position'] ) ? sanitize_text_field( $_REQUEST['offer_table_position'] ) : 'woocommerce_before_add_to_cart_button',
		'mode' => ! empty( $_REQUEST['mode'] ) ? sanitize_text_field( $_REQUEST['mode'] ) : 'first_match',
		'execution_order' => $enabled_modes,
	);

	update_option( 'xa_dynamic_pricing_setting', $prev_data );
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Saved Successfully', 'eh-dynamic-pricing-discounts' ); ?></p>
	</div>
	<?php
	wp_safe_redirect( admin_url( 'admin.php?page=dynamic-pricing-main-page&tab=' . $active_tab ) );
} else {
	echo '<div class="notice notice-error is-dismissible">';
	echo '<p>' . esc_html_e( 'Please Enter All Fields!! Then Save', 'eh-dynamic-pricing-discounts' ) . '</p> </div>';
}
