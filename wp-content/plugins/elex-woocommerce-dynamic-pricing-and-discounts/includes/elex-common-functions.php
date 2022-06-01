<?php
add_shortcode( 'eha_dynamic_pricing_offers_list_short_code', 'elex_dp_dynamic_pricing_offers_list_short_code' );

function elex_dp_dynamic_pricing_offers_list_short_code( $atts, $content = null ) {
	$selected_type = '';
	$html = '';
	if ( ! empty( $atts ) && isset( $atts['type'] ) ) {
		$selected_type = $atts['type'];
	}
	$dummy_option = array( 'buy_get_free_rules' => array() );
	$rules_option_array = get_option( 'xa_dp_rules', $dummy_option );
	foreach ( $rules_option_array as $rule_type => $rule_array ) {
		if ( ! empty( $selected_type ) && $rule_type != $selected_type ) {
			continue;
		}
		$offertp = ucwords( $rule_type );
		$offertp = str_replace( 'Cat_combinational', 'Category Combinational ', $offertp );
		$offertp = str_replace( 'Buy_get_free_rules', 'BOGO', $offertp );
		$offertp = explode( '_', $offertp );
		$offertp = $offertp[0];
		$offertp = $offertp . ' Offers';
		if ( empty( $rule_array ) ) {
			continue;
		}
		$html .= "<div id='offertype' name='offertype' class='offertype $rule_type'>";
		$html .= '<h2>' . $offertp . "</h2><table class='widefat fixed'>";
		$html .= "<tr id=offerrow_head' class='offerrow_head' font-size='14px'>"
		. "<th class='offername_head' width='25%'>Offer Name</th>"
		. "<th class='ofrmin_head' width='15%'>Buy Minimum</th>"
		. "<th class='ofrmax_head' width='15%'>Buy Maximum</th>"
		. "<th class='offervalue_head' width='15%'>Offer value</th>"
		. "<th class='offermaxdis_head' width='10%'>Maximum Discount</th>"
		. "<th class='offerdate_head' width='20%'>Valid Till</th>"
		. '</tr>';
		foreach ( $rule_array as $ruleno => $rule ) {
			$fromdate = $rule['from_date'];
			$todate = $rule['to_date'];
			$user_role = $rule['allow_roles'];
			$offer_name = $rule['offer_name'];
			$user = wp_get_current_user();
			if ( 'all' != $user_role && array_search( 'all', $user_role ) === false && ! empty( $user_role ) && ! array_intersect( $user_role, $user->roles ) ) {
				continue;
			}
			$now = gmdate( 'd-m-Y' );
			$return = false;
			if ( empty( $fromdate ) && empty( $todate ) ) {
				$return = true;
			} elseif ( empty( $fromdate ) && empty( $todate ) == false && ( strtotime( $now ) <= strtotime( $todate ) ) ) {
				$return = true;
			} elseif ( empty( $fromdate ) == false && ( strtotime( $now ) >= strtotime( $fromdate ) ) && empty( $todate ) ) {
				$return = true;
			} elseif ( ( strtotime( $now ) >= strtotime( $fromdate ) ) && ( strtotime( $now ) <= strtotime( $todate ) ) ) {
				$return = true;
			}
			if ( ! $return ) {
				continue;
			}
			$rule['max_discount'] = empty( $rule['max_discount'] ) ? '-' : wc_price( $rule['max_discount'] );
			$rule['max'] = empty( $rule['max'] ) ? '-' : ( $rule['max'] . '  </br>' . $rule['check_on'] );
			$rule['min'] = empty( $rule['min'] ) ? '-' : ( $rule['min'] . '  </br>' . $rule['check_on'] );
			$rule['value'] = empty( $rule['value'] ) ? '-' : ( $rule['value'] . '</br>( ' . $rule['discount_type'] . ' )' );
			$html .= "<tr id=offerrow' class='offerrow ' style='font-size:14px;padding:2px;'>"
			. "<td class='offername' style='padding:10px;'>" . $rule['offer_name'] . '</td>'
			. "<td class='ofrmin'  style='text-align:center;padding:5px;'>" . $rule['min'] . '  </td>'
			. "<td class='ofrmax'  style='text-align:center;padding:5px;'>" . $rule['max'] . '  </td>'
			. "<td class='offervalue' style='text-align:center;padding:5px;'>" . $rule['value'] . '</td>'
			. "<td class='offermaxdis'  style='text-align:center;padding:5px;'>" . $rule['max_discount'] . '</td>'
			. "<td class='offerdate'  style='text-align:center;padding:5px;'>" . $rule['to_date'] . '</td>'
			. '</tr>';
		}
		$html .= '</table></div>';
	}
	return $html;
}

function elex_dp_get_product_category_by_id( $category_id ) {
	$term = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
	return $term['name'];
}


function elex_dp_is_wc_version_gt_eql( $version ) {
	return elex_dp_get_wc_version() && version_compare( elex_dp_get_wc_version(), $version, '>=' );
}

if ( ! function_exists( 'elex_dp_get_wc_version' ) ) {

	function elex_dp_get_wc_version() {
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/woocommerce' );
		$plugin_file = 'woocommerce.php';

		// If the plugin version number is set, return it
		if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			// Otherwise return null
			global $woocommerce;
			return get_option( 'woocommerce_version', null );
		}
	}
}

function elex_dp_init_wc_functions() {

	if ( ! function_exists( 'wc_get_price_including_tax' ) ) {

		function wc_get_price_including_tax( $product, $args = array() ) {
			$qty_price_arr = array(
				'qty' => '',
				'price' => '',
			);
			$args = wp_parse_args( $args, $qty_price_arr );

			$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
			$qty = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;

			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}

			$line_price = $price * $qty;
			$return_price = $line_price;

			if ( $product->is_taxable() ) {
				if ( ! wc_prices_include_tax() ) {
					$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
					$taxes = WC_Tax::calc_tax( $line_price, $tax_rates, false );
					$tax_amount = WC_Tax::get_tax_total( $taxes );
					$return_price = round( $line_price + $tax_amount, wc_get_price_decimals() );
				} else {
					$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
					$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( true ) );

					/**
					 * If the customer is excempt from VAT, remove the taxes here.
					 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
					 */
					$customer = WC()->customer;
					if ( ! empty( $customer ) && WC()->customer->get_is_vat_exempt() ) {
						$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
						$remove_tax = array_sum( $remove_taxes );
						$return_price = round( $line_price - $remove_tax, wc_get_price_decimals() );

						/**
						 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
						 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
						 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
						 */
					} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
						$base_taxes = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
						$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );
						$return_price = round( $line_price - array_sum( $base_taxes ) + array_sum( $modded_taxes ), wc_get_price_decimals() );
					}
				}
			}
			return apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $product );
		}
	}

	if ( ! function_exists( 'wc_get_price_excluding_tax' ) ) {

		function wc_get_price_excluding_tax( $product, $args = array() ) {
			$qty_price_arr = array(
				'qty' => '',
				'price' => '',
			);
			$args = wp_parse_args( $args, $qty_price_arr );

			$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
			$qty = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;

			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}

			if ( $product->is_taxable() && wc_prices_include_tax() ) {
				$tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( true ) );
				$taxes = WC_Tax::calc_tax( $price * $qty, $tax_rates, true );
				$price = WC_Tax::round( $price * $qty - array_sum( $taxes ) );
			} else {
				$price = $price * $qty;
			}

			return apply_filters( 'woocommerce_get_price_excluding_tax', $price, $qty, $product );
		}
	}
	if ( ! function_exists( 'wc_format_price_range' ) ) {

		function wc_format_price_range( $from, $to ) {
			$price = sprintf( '%1$s &ndash; %2$s', 'Price range: from-to', is_numeric( $from ) ? wc_price( $from ) : $from, is_numeric( $to ) ? wc_price( $to ) : $to );
			return apply_filters( 'woocommerce_format_price_range', $price, $from, $to );
		}
	}
	if ( ! function_exists( 'wc_format_sale_price' ) ) {

		function wc_format_sale_price( $regular_price, $sale_price ) {
			$price = '<del>' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del> <ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins>';
			return apply_filters( 'woocommerce_format_sale_price', $price, $regular_price, $sale_price );
		}
	}
	if ( ! function_exists( 'elex_dp_get_pid' ) ) {

		function elex_dp_get_pid( $product ) {
			if ( is_object( $product ) ) {
				return elex_dp_is_wc_version_gt_eql( '2.7' ) ? $product->get_id() : $product->id;
			}
			return false;
		}
	}

	if ( ! function_exists( 'elex_dp_get_category_ids' ) ) {

		function elex_dp_get_category_ids( $product ) {
			if ( is_numeric( $product ) ) {
				$prod_id = $product;
				$product = wc_get_product( $prod_id );
			} else {
				$prod_id = elex_dp_get_pid( $product );
			}
			if ( $product->is_type( 'variation' ) ) {
				$prod_id = elex_dp_is_wc_version_gt_eql( '2.7' ) ? $product->get_parent_id() : $product->parent->id;
			}
			$terms = get_the_terms( $prod_id, 'product_cat' );
			if ( $terms ) {
				$cats_ids_array = array();
				foreach ( $terms as $key => $term ) {
					array_push( $cats_ids_array, $term->term_id );
					$term2 = $term;

					if ( ! in_array( $term2->parent, $cats_ids_array ) ) {
						while ( $term2->parent > 0 ) {
							array_push( $cats_ids_array, $term2->parent );
							$term2 = get_term_by( 'id', $term2->parent, 'product_cat' );
						}
					}
				}

				return $cats_ids_array;
			}
		}
	}
	if ( ! function_exists( 'elex_dp_wpml_compatible_ids' ) ) {
		function elex_dp_wpml_compatible_ids( $id, $type = 'product', $convert_key = false ) {
			if ( empty( $id ) ) {
				return $id;
			}

			if ( function_exists( 'wpml_object_id_filter' ) ) {
				if ( is_array( $id ) == true ) {
					$is_array = true;
				} else {
					$is_array = false;
					$id = array( $id );
				}
				$converted_ids = array();
				foreach ( $id as $key => $val ) {
					if ( true == $convert_key ) {
						$key = wpml_object_id_filter( $key, $type, true );
					} else {
						$val = wpml_object_id_filter( $val, $type, true );
					}
					$converted_ids[ $key ] = $val;
				}
				return $is_array ? $converted_ids : current( $converted_ids );
			} else {
				return $id;
			}
		}
	}
}
