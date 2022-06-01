<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! empty( $_GET['offer_name'] ) && ( isset( $_GET['rule_on'] ) && ( 'products' == $_GET['rule_on'] && ! empty( $_GET['product_id'] ) ) || ( 'categories' == $_GET['rule_on'] && ! empty( $_GET['category_id'] ) ) || ( 'cart' == $_GET['rule_on'] ) ) && ! empty( $_GET['check_on'] ) && ! empty( $_GET['min'] ) && ! empty( $_GET['discount_type'] ) && ! empty( $_GET['value'] ) && ! isset( $_GET['edit'] ) ) {
	$dummy_settings['product_rules'] = array();
	$dummy_settings['combinational_rules'] = array();
	$dummy_settings['category_rules'] = array();
	$dummy_settings['cart_rules'] = array();
	$dummy_settings['buy_get_free_rules'] = array();
	if ( 'products' == sanitize_text_field( $_GET['rule_on'] ) && isset( $_GET['product_id'] ) && ! empty( $_GET['product_id'] ) ) {
		if ( elex_dp_is_wc_version_gt_eql( '2.7' ) ) {
			$products_ids = array_map( 'sanitize_text_field', wp_unslash( $_GET['product_id'] ) );
		} else {
			$products_ids = explode( ',', sanitize_text_field( $_GET['product_id'] ) );
		}

		$categories = null;
	} elseif ( sanitize_text_field( $_GET['rule_on'] ) == 'categories' && isset( $_GET['category_id'] ) && ! empty( $_GET['category_id'] ) ) {
		$products_ids = null;
		$categories = sanitize_text_field( $_GET['category_id'] );
	} else {
		$products_ids = null;
		$categories = null;
	}



	$prev_data = get_option( 'xa_dp_rules', $dummy_settings );
	if ( ! isset( $prev_data[ $active_tab ] ) || count( $prev_data[ $active_tab ] ) == 0 ) {
		$prev_data[ $active_tab ][1] = array(
			'offer_name' => sanitize_text_field( $_GET['offer_name'] ),
			'rule_on' => sanitize_text_field( $_GET['rule_on'] ),
			'product_id' => $products_ids,
			'category_id' => $categories,
			'check_on' => sanitize_text_field( $_GET['check_on'] ),
			'min' => sanitize_text_field( $_GET['min'] ),
			'max' => ! empty( $_GET['max'] ) ? sanitize_text_field( $_GET['max'] ) : null,
			'discount_type' => sanitize_text_field( $_GET['discount_type'] ),
			'value' => sanitize_text_field( $_GET['value'] ),
			'max_discount' => null,
			'allow_roles' => ( ! empty( $_GET['allow_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['allow_roles'] ) ) : array() ),
			'from_date' => ! empty( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : null,
			'to_date' => ! empty( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : null,
			'adjustment' => null,
			'email_ids' => null,
			'prev_order_count' => null,
			'prev_order_total_amt' => null,
			'repeat_rule' => null,
		);
	} else {
		$prev_data[ $active_tab ][] = array(
			'offer_name' => sanitize_text_field( $_GET['offer_name'] ),
			'rule_on' => sanitize_text_field( $_GET['rule_on'] ),
			'product_id' => $products_ids,
			'category_id' => $categories,
			'check_on' => sanitize_text_field( $_GET['check_on'] ),
			'min' => sanitize_text_field( $_GET['min'] ),
			'max' => ! empty( $_GET['max'] ) ? sanitize_text_field( $_GET['max'] ) : null,
			'discount_type' => sanitize_text_field( $_GET['discount_type'] ),
			'value' => sanitize_text_field( $_GET['value'] ),
			'max_discount' => null,
			'allow_roles' => ( ! empty( $_GET['allow_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['allow_roles'] ) ) : array() ),
			'from_date' => ! empty( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : null,
			'to_date' => ! empty( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : null,
			'adjustment' => null,
			'email_ids' => null,
			'prev_order_count' => null,
			'prev_order_total_amt' => null,
			'repeat_rule' => null,
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
