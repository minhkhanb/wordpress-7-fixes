<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include 'elex-new-calculation-handler.php';

add_action( 'wp_loaded', 'elex_dp_init_calculator' );
$GLOBALS['elex_dp_settings'] = get_option( 'xa_dynamic_pricing_setting' );
add_action( 'wp_footer', 'elex_dp_print_scripts_payment_gateway', 5 );
function elex_dp_print_scripts_payment_gateway() {
	?>
<script>
jQuery( function( $  ) {
	"use strict";
	$( 'body' ).on( 'change', 'input[name="payment_method"]', function() {
		$( 'body' ).trigger( 'update_checkout' );
	} );
	$( 'body' ).on( 'change', '.shipping_method', function() { 
					   setTimeout( function() {
						$( 'body' ).trigger( 'update_checkout' );  // for checkout page ( update product prices and recalculate  )
						jQuery( "[name='update_cart']" ).removeAttr( 'disabled' );   //for cart page ( update product prices and recalculate  )
				jQuery( "[name='update_cart']" ).trigger( "click" );        // for cart page ( update product prices and recalculate  )
						}, 2000 );
	} );
} );
</script>
	<?php
}
function elex_dp_init_calculator() {
	elex_dp_init_wc_functions();
	global $xa_common_flat_discount;
	global $xa_hooks;
	global $executed_rule_pid_price;
	$executed_rule_pid_price = array();
	$xa_common_flat_discount = array();
	$obj = new Elex_DP_New_Calculation_Handler();
	add_action( 'woocommerce_cart_calculate_fees', 'elex_dp_calculate_and_apply_discount_and_add_fee' );
	add_action( 'woocommerce_before_calculate_totals', 'elex_dp_update_globals' );

	function elex_dp_update_globals( $cart ) {
		if ( isset( $_REQUEST['debugcart'] ) ) {
			echo '<pre>';
			print_r( $cart );
			echo '</pre>';
		}
		global $xa_cart_quantities;
		global $xa_cart_weight;
		global $xa_cart_price;
		global $xa_cart_categories;
		global $xa_cart_categories_items;
		global $xa_cart_categories_units;
		global $woocommerce;
		$xa_cart_categories_items = array();
		$xa_cart_categories_units = array();
		////Removing Free Products Which are Automatically Added by Dynamic Pricing
		$cart_item_data = $woocommerce->cart->get_cart();
		foreach ( $cart_item_data as $key => $values ) {
			if ( strpos( $key, 'FreeForRule' ) !== false ) {            //remove free products
				//$woocommerce->cart->remove_cart_item( $key );
				unset( $woocommerce->cart->cart_contents[ $key ] );
				continue;
			}
				$product = $values['data'];
				$id = $product->get_id();
				$xa_cart_quantities[ $id ] = ! empty( $values['quantity'] ) ? $values['quantity'] : 0;
		}
		//////////////////////////////////////
		foreach ( $xa_cart_quantities as $_pid => $_qnty ) {
			$prod = wc_get_product( $_pid );
			$xa_cart_weight[ $_pid ] = $prod->get_weight();
			$xa_cart_price[ $_pid ] = $prod->get_price( 'edit' );
			if ( $prod->is_type( 'variation' ) ) {
				$parent_id = elex_dp_is_wc_version_gt_eql( '2.7' ) ? $prod->get_parent_id() : $prod->parent->id;
				$parent_product = wc_get_product( $parent_id );
				$xa_cart_categories[ $_pid ] = elex_dp_is_wc_version_gt_eql( '2.7' ) ? $parent_product->get_category_ids() : elex_dp_get_category_ids( $parent_product );
			} else {
				$xa_cart_categories[ $_pid ] = elex_dp_is_wc_version_gt_eql( '2.7' ) ? $prod->get_category_ids() : elex_dp_get_category_ids( $prod );
			}
			foreach ( $xa_cart_categories[ $_pid ] as $_cid ) {
				$xa_cart_categories_items[ $_cid ] = isset( $xa_cart_categories_items[ $_cid ] ) ? ( $xa_cart_categories_items[ $_cid ] + 1 ) : 1;
				$xa_cart_categories_units[ $_cid ] = isset( $xa_cart_categories_units[ $_cid ] ) ? ( $xa_cart_categories_units[ $_cid ] + $_qnty ) : $_qnty;
			}
		}
	}
	$xa_hooks['woocommerce_get_price_hook_name'] = 'woocommerce_get_price';
	if ( elex_dp_is_wc_version_gt_eql( '2.7.0' ) == true ) {
		$xa_hooks['woocommerce_get_price_hook_name'] = 'woocommerce_product_get_price';
	}
	$xa_hooks['woocommerce_get_sale_price_hook_name'] = 'woocommerce_get_sale_price';
	if ( elex_dp_is_wc_version_gt_eql( '2.7' ) == true ) {
		$xa_hooks['woocommerce_get_sale_price_hook_name'] = 'woocommerce_product_get_sale_price';
	}

	add_filter( 'woocommerce_product_is_on_sale', 'elex_dp_product_is_on_sale', 99, 2 );

	function elex_dp_product_is_on_sale( $on_sale, $product ) {
		if ( $product->is_type( 'grouped' ) || $product->is_type( 'variable' ) ) {
			$childrens = $product->get_children();
			foreach ( $childrens as $child ) {
				$prod = wc_get_product( $child );
				$sale_price = $prod->get_sale_price();
				$regular_price = $prod->get_regular_price();
				if ( ! empty( $sale_price ) && $sale_price != $regular_price ) {
					return true;
				}
			}
		} elseif ( $product->is_type( 'simple' ) ) {
			if ( '' !== (string) $product->get_price() && $product->get_regular_price() > $product->get_price() ) {
				$on_sale = true;
				if ( WC()->version >= '3.0.0' ) {
					if ( $product->get_date_on_sale_from() && $product->get_date_on_sale_from()->getTimestamp() > time() ) {
						$on_sale = false;
					}

					if ( $product->get_date_on_sale_to() && $product->get_date_on_sale_to()->getTimestamp() < time() ) {
						$on_sale = false;
					}
				}
			} else {
				$on_sale = false;
			}
		}
		return $on_sale;
	}

	add_filter( 'woocommerce_get_price_html', array( $obj, 'elex_dp_get_discounted_price_html' ), 22, 2 );              // update sale price on product variation page
	add_filter( $xa_hooks['woocommerce_get_price_hook_name'], array( $obj, 'elex_dp_get_discounted_price_for_product' ), 22, 2 );         // update sale price on product page
	add_filter( $xa_hooks['woocommerce_get_sale_price_hook_name'], array( $obj, 'elex_dp_get_discounted_price_for_product' ), 22, 2 );    // update sale price on product page
	add_filter( 'woocommerce_product_variation_get_price', array( $obj, 'elex_dp_get_discounted_price_for_product' ), 22, 2 );    // update sale price on product page
	add_filter( 'woocommerce_product_variation_get_sale_price', array( $obj, 'elex_dp_get_discounted_price_for_product' ), 22, 2 );    // update sale price on product page
}

function elex_dp_calculate_and_apply_discount_and_add_fee() {
	global $xa_common_flat_discount;
	global $woocommerce;
	$total_flat_discount = 0;
	foreach ( $xa_common_flat_discount as $dis ) {
		$total_flat_discount += $dis;
	}
	if ( $total_flat_discount > 0 ) {
		$label = apply_filters( 'eha_change_discount_label_filter', __( 'Discount', 'eh-dynamic-pricing-discounts' ) );
		$woocommerce->cart->add_fee( $label, -$total_flat_discount );
	} elseif ( $total_flat_discount < 0 ) {
		$label = apply_filters( 'eha_change_discount_label_filter', __( 'Discount Adjustment', 'eh-dynamic-pricing-discounts' ) );
		$woocommerce->cart->add_fee( $label, -$total_flat_discount );
	}
}

$pricing_table_hook = isset( $GLOBALS['elex_dp_settings']['pricing_table_position'] ) ? sanitize_text_field( $GLOBALS['elex_dp_settings']['pricing_table_position'] ) : 'woocommerce_before_add_to_cart_button';
add_action( $pricing_table_hook, 'elex_dp_show_pricing_table', 40 );

function elex_dp_show_pricing_table() {
	$path = dirname( __FILE__ ) . '/elex-single-product-pricing-table.php';
	if ( 'enable' == $GLOBALS['elex_dp_settings']['price_table_on_off'] && file_exists( $path ) == true ) {
		include 'elex-single-product-pricing-table.php';
	}
}

add_filter( 'woocommerce_cart_item_price', 'elex_dp_show_discount_on_line_item', 100, 2 );

function elex_dp_show_discount_on_line_item( $price, $cart_item ) {
	$prod = $cart_item['data'];
	$price = $prod->get_price_html();
	return $price;
}

add_action( 'wc_ajax_get_refreshed_fragments', 'elex_dp_wc_ajax_get_refreshed_fragments', 1 );

function elex_dp_wc_ajax_get_refreshed_fragments() {
	if ( is_cart() ) {
		global $woocommerce;
		$woocommerce->cart->calculate_totals();
	}
}
