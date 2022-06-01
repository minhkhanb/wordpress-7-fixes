<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! empty( $_REQUEST['offer_name'] ) && ! empty( $_REQUEST['category_id'] ) && ! empty( $_REQUEST['check_on'] ) && ! empty( $_REQUEST['min'] ) && ! empty( $_REQUEST['discount_type'] ) && ! empty( $_REQUEST['value'] ) ) {

	$dummy_settings['product_rules'] = array();
	$dummy_settings['combinational_rules'] = array();
	$dummy_settings['category_rules'] = array();
	$dummy_settings['cart_rules'] = array();
	$dummy_settings['buy_get_free_rules'] = array();

	$prev_data = get_option( 'xa_dp_rules', $dummy_settings );
	if ( ! isset( $prev_data[ $active_tab ] ) || count( $prev_data[ $active_tab ] ) == 0 ) {
		$prev_data[ $active_tab ][1] = array(
			'offer_name' => sanitize_text_field( $_REQUEST['offer_name'] ),
			'category_id' => ! empty( $_REQUEST['category_id'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['category_id'] ) ) : '',
			'check_on' => sanitize_text_field( $_REQUEST['check_on'] ),
			'min' => sanitize_text_field( $_REQUEST['min'] ),
			'max' => ! empty( $_REQUEST['max'] ) ? sanitize_text_field( $_REQUEST['max'] ) : null,
			'discount_type' => sanitize_text_field( $_REQUEST['discount_type'] ),
			'value' => sanitize_text_field( $_REQUEST['value'] ),
			'max_discount' => null,
			'allow_roles' => 'all',
			'from_date' => null,
			'to_date' => null,
			'adjustment' => null,
			'email_ids' => null,
			'prev_order_count' => null,
			'prev_order_total_amt' => null,
		);
	} else {
		$prev_data[ $active_tab ][] = array(
			'offer_name' => sanitize_text_field( $_REQUEST['offer_name'] ),
			'category_id' => array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['category_id'] ) ),
			'check_on' => sanitize_text_field( $_REQUEST['check_on'] ),
			'min' => sanitize_text_field( $_REQUEST['min'] ),
			'max' => ! empty( $_REQUEST['max'] ) ? sanitize_text_field( $_REQUEST['max'] ) : null,
			'discount_type' => sanitize_text_field( $_REQUEST['discount_type'] ),
			'value' => sanitize_text_field( $_REQUEST['value'] ),
			'max_discount' => null,
			'allow_roles' => 'all',
			'from_date' => null,
			'to_date' => null,
			'adjustment' => null,
			'email_ids' => null,
			'prev_order_count' => null,
			'prev_order_total_amt' => null,
		);
	}

	update_option( 'xa_dp_rules', $prev_data );
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
