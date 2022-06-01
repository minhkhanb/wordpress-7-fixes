<?php

//
function uni_cpo_get_decimals_count( $value ) {
	if ( (int) $value == $value ) {
		return 0;
	} elseif ( ! is_numeric( $value ) ) {
		return false;
	}

	return strlen( $value ) - strrpos( $value, '.' ) - 1;
}

//
function uni_cpo_get_all_roles() {
	global $wp_roles;
	$all_roles  = $wp_roles->roles;
	$role_names = array();
	foreach ( $all_roles as $role_name => $role_data ) {
		$role_names[ $role_name ] = $role_data['name'];
	}

	return $role_names;
}

function uni_cpo_field_attributes_modifier( $new_attrs, $attributes ) {
	array_walk(
		$new_attrs,
		function ( $v ) use ( &$attributes ) {
			$rule = explode( '=', $v );
			if ( isset( $rule[0] ) && ! empty( $rule[1] ) ) {
				$attr_name                = $rule[0];
				$attr_val                 = trim( $rule[1], '"' );
				$attributes[ $attr_name ] = $attr_val;
			}
		}
	);

	return $attributes;
}

function uni_cpo_add_slashes( $attributes = array() ) {
	if ( ! empty( $attributes ) && is_array( $attributes ) ) {
		foreach ( $attributes as $k => $v ) {
			$attributes[ $k ] = preg_match( "/(?=.*parsley)(?!.*message).*/", $k )
				? addslashes( $v )
				: $v;
		}
	}

	return $attributes;
}

//
function uni_cpo_get_image_sizes( $size = '' ) {

	global $_wp_additional_image_sizes;

	$sizes                        = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach ( $get_intermediate_image_sizes as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

			$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );

		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
			);

		}

	}

	// Get only 1 size if found
	if ( $size ) {

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}

	}

	return $sizes;
}

//
function uni_cpo_get_image_sizes_list() {
	$sizes = uni_cpo_get_image_sizes();
	$list  = array();
	foreach ( $sizes as $k => $v ) {
		$list[ $k ] = $k;
	}

	return $list;
}

//
function uni_cpo_pro_content() {
	return ( ! unicpo_fs()->is__premium_only() ) ? 'uni-premium-content' : '';
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	function uni_cpo_import_csv_data( $file ) {
		if ( false !== ( $handle = fopen( $file, 'r' ) ) ) {

			$plugin_settings = UniCpo()->get_settings();
			$delimiter       = $plugin_settings['csv_delimiter'];

			while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) {
				$raw_data[] = $row;
			}

			return $raw_data;
		}

		return [];
	}


	function uni_cpo_format_csv_matrix_data( $data ) {
		if ( ! empty( $data ) ) {
			$final = array();

			$final['metadata'] = $data[0];
			unset( $data[0] );

			if ( ! empty( $data ) ) {
				$i = 1;
				foreach ( $data as $k => $v ) {
					$final['data'][] = array(
						'columns' => $v,
						'id'      => $i
					);
					$i ++;
				}
			} else {
				$final['data'][] = array(
					'columns' => array(),
					'id'      => 1
				);
			}

			return $final;
		}

		return false;
	}

	function uni_cpo_get_closest( $needle, $haystack = array() ) {
		$closest = 0;
		foreach ( $haystack as $k => $v ) {
			if ( is_numeric( $k ) && is_numeric( $v ) ) {
				if ( ! is_array( $v ) ) {
					if ( $closest === 0 || abs( $needle - $closest ) >= abs( $v - $needle ) ) {
						$closest = $v;
					}
				} else {
					if ( $closest === 0 || abs( $needle - $closest ) >= abs( $k - $needle ) ) {
						$closest = $k;
					}
				}
			}
		}

		return $closest;
	}

	function uni_cpo_suboptions_format_select() {
		return array( 'def', 'label', 'slug', 'rate' );
	}

	function uni_cpo_suboptions_format_radio() {
		$arr   = uni_cpo_suboptions_format_select();
		$arr[] = 'suboption_class';
		$arr[] = 'suboption_text';
		$arr[] = 'suboption_colour';
		$arr[] = 'attach_id';
		$arr[] = 'attach_uri';
		$arr[] = 'attach_name';
		$arr[] = 'attach_id_r';
		$arr[] = 'attach_uri_r';

		return $arr;
	}

	function uni_cpo_suboptions_formats() {
		return array(
			'cpo_select_options' => uni_cpo_suboptions_format_select(),
			'cpo_radio_options'  => uni_cpo_suboptions_format_radio()
		);
	}

	function uni_cpo_format_csv_suboptions_data( $data, $suboptions_type, $option_type ) {

		if ( ! empty( $data ) ) {
			$final           = array();
			$allowed_formats = uni_cpo_suboptions_formats();
			$allowed_headers = $allowed_formats[ $suboptions_type ];
			$metadata        = array_filter(
				$data[0],
				function ( $item ) use ( $allowed_headers ) {
					return in_array( trim( $item ), $allowed_headers );
				}
			);
			unset( $data[0] );

			if ( ! empty( array_values( $data ) ) ) {
				$i               = 0;
				$metadata_keys   = array_keys( $metadata );
				$permitted_chars = 'abcdefghijklmnopqrstuvwxyz_';
				foreach ( $data as $k => $v ) {
					foreach ( $metadata_keys as $meta_k ) {
						$prop = trim( $metadata[ $meta_k ] );
						if ( 'def' === $prop ) {
							$def                  = ! empty( $v[ $meta_k ] )
								? $option_type === 'checkbox'
									? array( '1' )
									: 'checked'
								: '';
							$final[ $i ][ $prop ] = $def;
						} elseif ( 'slug' === $prop ) {
							$slug = uni_cpo_generate_slug( uni_cpo_clean( $v[ $meta_k ] ) );
							if ( empty( $slug ) ) {
								$slug = substr( str_shuffle( $permitted_chars ), 0, 10 );
							}
							if ( preg_match( '/^\d/', $slug ) === 1 ) {
								$slug = 'a' . $slug;
							}
							$final[ $i ][ $prop ] = $slug;
						} elseif ( 'rate' === $prop ) {
							$final[ $i ][ $prop ] = empty( $v[ $meta_k ] )
								? ''
								: floatval(
									str_replace(
										',',
										'.',
										$v[ $meta_k ]
									)
								);
						} else {
							$final[ $i ][ $prop ] = uni_cpo_clean( $v[ $meta_k ] );
						}
					}
					$i ++;
				}
			}

			return $final;
		}

		return false;
	}

	function uni_cpo_generate_slug( $string ) {
		$string = preg_replace( '/[^a-z0-9\s\-\_]/i', '', $string );
		$string = preg_replace( '/\s/', '_', $string );
		$string = preg_replace( '/\-\-+/', '_', $string );
		$string = strtolower( trim( $string, '-' ) );

		return $string;
	}

	function uni_cpo_get_attachment_meta( $id ) {
		$id = absint( $id );

		if ( $id ) {
			$meta           = array();
			$metadata       = wp_get_attachment_metadata( $id, true );
			$meta['id']     = $id;
			$meta['url']    = wp_get_attachment_url( $id );
			$meta['title']  = get_the_title( $id );
			$meta['width']  = ! empty( $metadata['width'] ) ? $metadata['width'] : 0;
			$meta['height'] = ! empty( $metadata['height'] ) ? $metadata['height'] : 0;
		} else {
			$meta['id']     = 0;
			$meta['url']    = '';
			$meta['title']  = '';
			$meta['width']  = 0;
			$meta['height'] = 0;
		}

		return $meta;
	}

	//
	add_filter( 'woocommerce_cart_item_name', 'uni_cpo_woocommerce_cart_item_display_sample_label', 10, 2 );
	function uni_cpo_woocommerce_cart_item_display_sample_label( $title, $cart_item ) {
		if ( isset( $cart_item['_cpo_is_free_sample'] ) ) {
			$title .= ' ' . __( '(Sample)', 'uni-cpo' );
		}

		return $title;
	}

	//
	add_filter( 'woocommerce_cart_item_name', 'uni_cpo_woocommerce_cart_item_display_weight', 11, 2 );
	function uni_cpo_woocommerce_cart_item_display_weight( $title, $cart_item ) {
		$plugin_settings = UniCpo()->get_settings();

		if ( 'on' === $plugin_settings['display_weight_in_cart'] || 'on' == $plugin_settings['display_dimensions_in_cart'] ) {
			$title_data = array();

			if ( $cart_item['data']->get_weight() && 'on' === $plugin_settings['display_weight_in_cart'] ) {
				$weight_unit = get_option( 'woocommerce_weight_unit' );

				$weight        = $cart_item['data']->get_weight();
				$suffix_weight = "$weight $weight_unit";
				//$title         .= $suffix_weight;
				$title_data['weight'] = $suffix_weight;
			}

			if ( ( $cart_item['data']->get_width() || $cart_item['data']->get_height() || $cart_item['data']->get_length() )
			     && 'on' == $plugin_settings['display_dimensions_in_cart'] ) {

				$dimensions = $cart_item['data']->get_dimensions( false );
				array_walk(
					$dimensions,
					function ( $v, $k ) use ( $dimensions, &$title_data ) {
						if ( ! empty( $v ) ) {
							$dim_key                       = ( function_exists( 'mb_strtoupper' ) ) ? mb_strtoupper( $k[0] ) : $k[0];
							$title_data['dim'][ $dim_key ] = $v;
						}
					}
				);
			}

			if ( ! empty( $title_data ) ) {
				$title                .= ' (';
				$title_data_formatted = array();
				$dimensions_unit      = get_option( 'woocommerce_dimension_unit' );
				foreach ( $title_data as $t => $v ) {
					if ( 'dim' === $t ) {
						if ( ! empty( $v ) && is_array( $v ) ) {
							$keys                   = array_keys( $v );
							$values                 = array_values( $v );
							$title_data_formatted[] = implode( 'x', $keys ) . ': ' . implode( 'x', $values ) . ' ' . $dimensions_unit;
						}
					} else {
						$title_data_formatted[] = $v;
					}
				}
				$title .= implode( '; ', $title_data_formatted );
				$title .= ')';
			}
		}

		return $title;
	}

	//
	add_filter( 'woocommerce_cart_item_thumbnail', 'uni_cpo_woocommerce_cart_item_thumbnail', 10, 2 );
	function uni_cpo_woocommerce_cart_item_thumbnail( $image, $cart_item ) {

		if ( ! empty( $cart_item['_cpo_product_image'] ) ) {
			$size  = apply_filters( 'uni_cpo_woocommerce_cart_item_thumbnail', 'woocommerce_gallery_thumbnail', $cart_item, $image );
			$image = wp_get_attachment_image( $cart_item['_cpo_product_image'], $size );
		}

		if (
			defined( 'PP_IOBASE' ) && isset( $cart_item['_cpo_data'] )
			&& ! empty( $cart_item['_cpo_data']['_w2p_set_option'] )
		) {
			$itm = $cart_item['_cpo_data']['_w2p_set_option'];
			$itm = json_decode( rawurldecode( $itm ), true );
			if ( $itm['type'] == 'p' ) {
				$image = '<img style="width:90px" src="' . PP_IOBASE . '/previews/' . $itm['projectId'] . '_1.jpg" >';
			} else {
				$image = '<img style="width:90px" src="' . $itm['previews'][0] . '" >';
			}
		}

		return $image;
	}

	//
	add_filter( 'woocommerce_admin_order_item_thumbnail', 'uni_cpo_woocommerce_admin_order_item_thumbnail', 10, 3 );
	function uni_cpo_woocommerce_admin_order_item_thumbnail( $image, $item_id, $item ) {
		$item_meta = $item->get_meta_data();
		foreach ( $item_meta as $k => $v ) {
			$meta_data = $v->get_data();
			if ( '_uni_custom_item_image' === $meta_data['key'] ) {
				$size  = 'thumbnail';
				$image = wp_get_attachment_image( $meta_data['value'], $size );
			}
		}

		return $image;
	}

	//
	add_filter( 'wpo_wcpdf_order_item_data', 'uni_cpo_wpo_wcpdf_order_item_data', 10, 2 );
	function uni_cpo_wpo_wcpdf_order_item_data( $data, $wcpdf_order ) {
		$items   = $wcpdf_order->get_items();
		$item_id = $data['item_id'];
		$item    = $items[ $item_id ];

		$item_meta = $item->get_meta_data();
		foreach ( $item_meta as $k => $v ) {
			$meta_data = $v->get_data();
			if ( '_uni_custom_item_image' === $meta_data['key'] ) {
				$size              = apply_filters( 'wpo_wcpdf_thumbnail_size', 'woocommerce_gallery_thumbnail' );
				$data['thumbnail'] = wp_get_attachment_image( $meta_data['value'], $size );
			}
		}

		return $data;
	}


	//
	add_filter( 'woocommerce_cart_item_remove_link', 'uni_cpo_woocommerce_cart_add_action_btns', 10, 2 );
	function uni_cpo_woocommerce_cart_add_action_btns( $html, $cart_item_key ) {
		$cart_content = WC()->cart->get_cart();
		$cart_item    = $cart_content[ $cart_item_key ];
		$product_data = Uni_Cpo_Product::get_product_data_by_id( $cart_item['product_id'] );

		if ( 'on' === $product_data['settings_data']['cpo_enable'] ) {
			$nonce = wp_create_nonce( 'woocommerce-cart' );

			if ( 'on' === $product_data['settings_data']['cart_duplicate_enable'] ) {
				$duplicate_title = __( 'Duplicate', 'uni-cpo' );
				$duplicate_btn   = '<span data-key="' . esc_attr( $cart_item_key ) . '" data-nonce="' . esc_attr( $nonce ) . '" class="uni-cpo-cart-action uni-cpo-action-duplicate" title="' . esc_attr( $duplicate_title ) . '" aria-label="' . esc_attr( $duplicate_title ) . '">&times;</span>';
				$html            = $html . $duplicate_btn;
			}
			if ( 'on' === $product_data['settings_data']['cart_edit_full_enable'] ) {
				$edit_title = __( 'Edit', 'uni-cpo' );
				$edit_btn   = '<span data-key="' . esc_attr( $cart_item_key ) . '" data-product_id="' . esc_attr( $cart_item['product_id'] ) . '" data-nonce="' . esc_attr( $nonce ) . '" class="uni-cpo-cart-action uni-cpo-action-edit" title="' . esc_attr( $edit_title ) . '" aria-label="' . esc_attr( $edit_title ) . '">&times;</span>';
				$html       = $html . $edit_btn;
			}
			if ( 'on' === $product_data['settings_data']['cart_edit_enable'] ) {
				$edit_title = __( 'Edit (inline)', 'uni-cpo' );
				$edit_btn   = '<span data-key="' . esc_attr( $cart_item_key ) . '" data-nonce="' . esc_attr( $nonce ) . '" class="uni-cpo-cart-action uni-cpo-action-edit-inline" title="' . esc_attr( $edit_title ) . '" aria-label="' . esc_attr( $edit_title ) . '">&times;</span>';
				$html       = $html . $edit_btn;
			}
		}

		return $html;
	}

	//
	add_filter( 'upload_mimes', 'uni_cpo_extend_upload_mimes' );
	function uni_cpo_extend_upload_mimes( $existing_mimes ) {
		$existing_mimes['txt'] = 'text/plain';

		return $existing_mimes;
	}

	//
	add_filter( 'shipperhq_custom_product_attribute_value', 'uni_cpo_dimensions_for_shipperhq', 10, 4 );
	function uni_cpo_dimensions_for_shipperhq( $attribute_value, $attribute, $product_id, $cart_item_data ) {
		$product_data       = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$is_calc_dimensions = ( 'on' === $product_data['dimensions_data']['dimensions_enable'] )
			? true : false;

		if ( $is_calc_dimensions && is_a( $cart_item_data, 'WC_Product_Simple' ) ) {
			$attribute_value = call_user_func( array( $cart_item_data, "get_{$attribute}" ) );
		}

		return $attribute_value;
	}

	//
	add_filter( 'envoimoinscher_order_line_item_weight', 'uni_cpo_get_weight_for_boxtal', 10, 2 );
	function uni_cpo_get_weight_for_boxtal( $product_weight, $item ) {
		$item_meta_data = $item->get_meta_data();

		array_walk(
			$item_meta_data,
			function ( $v ) use ( &$product_weight ) {
				$meta_data = $v->get_data();
				if ( false !== strpos( $meta_data['key'], 'uni_item_weight' ) ) {
					$product_weight = $meta_data['value'];
				}
			}
		);

		return $product_weight;
	}

	//
	function uni_cpo_filter_novs( $variables ) {
		$novs_nice = array();

		$novs_only = array_filter(
			$variables,
			function ( $k ) {
				return false !== strpos( $k, UniCpo()->get_nov_slug() );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( ! empty( $novs_only ) ) {
			array_walk(
				$novs_only,
				function ( &$v, $k ) use ( &$novs_nice ) {
					$k               = trim( $k, '{}' );
					$novs_nice[ $k ] = $v;
				}
			);
		}

		return $novs_nice;
	}

	// various themes CSS selectors compatibility
	// storefront | price tag in sticky bar
	add_filter( 'uni_cpo_price_selector', 'uni_cpo_storefront_theme_price_tag', 10, 1 );
	function uni_cpo_storefront_theme_price_tag( $selector ) {
		$selector[] = '.storefront-sticky-add-to-cart__content-price';

		return $selector;
	}

	// flatsome price tag
	add_filter( 'uni_cpo_price_selector', 'uni_cpo_flatsome_theme_price_tag', 10, 1 );
	function uni_cpo_flatsome_theme_price_tag( $selector ) {
		$selector[] = '.price-wrapper .price > .amount, .price-wrapper .price ins .amount';

		return $selector;
	}

	// jetwoobuilder price tag
	add_filter( 'uni_cpo_price_selector', 'uni_cpo_jetwoobuilder_theme_price_tag', 10, 1 );
	function uni_cpo_jetwoobuilder_theme_price_tag( $selector ) {
		$selector[] = '.elementor-widget-container .price > .amount, .elementor-widget-container .price ins .amount';

		return $selector;
	}

	// aelia currency switcher support
	/**
	 * Basic integration with WooCommerce Currency Switcher, developed by Aelia
	 * (http://aelia.co). This method can be used by any 3rd party plugin to
	 * return prices converted to the active currency.
	 *
	 * Need a consultation? Find us on Codeable: https://bit.ly/aelia_codeable
	 *
	 * @param double price The source price.
	 * @param string to_currency The target currency. If empty, the active currency
	 * will be taken.
	 * @param string from_currency The source currency. If empty, WooCommerce base
	 * currency will be taken.
	 *
	 * @return double The price converted from source to destination currency.
	 * @author Aelia <support@aelia.co>
	 * @link http://aelia.co
	 */
	function uni_cpo_aelia_price_convert( $price, $to_currency = null, $from_currency = null ) {
		// If source currency is not specified, take the shop's base currency as a default
		if ( empty( $from_currency ) ) {
			$from_currency = get_option( 'woocommerce_currency' );
		}
		// If target currency is not specified, take the active currency as a default.
		// The Currency Switcher sets this currency automatically, based on the context. Other
		// plugins can also override it, based on their own custom criteria, by implementing
		// a filter for the "woocommerce_currency" hook.
		//
		// For example, a subscription plugin may decide that the active currency is the one
		// taken from a previous subscription, because it's processing a renewal, and such
		// renewal should keep the original prices, in the original currency.
		if ( empty( $to_currency ) ) {
			$to_currency = get_woocommerce_currency();
		}

		// Call the currency conversion filter. Using a filter allows for loose coupling. If the
		// Aelia Currency Switcher is not installed, the filter call will return the original
		// amount, without any conversion being performed. Your plugin won't even need to know if
		// the multi-currency plugin is installed or active
		return apply_filters( 'wc_aelia_cs_convert', $price, $from_currency, $to_currency );
	}

	// price calculated on the product page
	add_filter( 'uni_cpo_ajax_calculated_price', 'uni_cpo_ajax_calculated_price_aelia_currency_switcher', 10, 1 );
	function uni_cpo_ajax_calculated_price_aelia_currency_switcher( $price ) {
		return uni_cpo_aelia_price_convert( $price );
	}

	add_filter( 'uni_cpo_in_cart_calculated_price', 'uni_cpo_in_cart_calculated_price_aelia_currency_switcher', 11, 1 );
	function uni_cpo_in_cart_calculated_price_aelia_currency_switcher( $price ) {
		return uni_cpo_aelia_price_convert( $price );
	}

	add_filter( 'uni_cpo_price_regular_archive', 'uni_cpo_price_regular_archive_aelia_currency_switcher', 9, 1 );
	function uni_cpo_price_regular_archive_aelia_currency_switcher( $price ) {
		return uni_cpo_aelia_price_convert( $price );
	}

	add_filter( 'uni_cpo_price_starting_archive', 'uni_cpo_price_starting_archive_aelia_currency_switcher', 9, 1 );
	function uni_cpo_price_starting_archive_aelia_currency_switcher( $price ) {
		return uni_cpo_aelia_price_convert( $price );
	}

	add_filter( 'uni_cpo_display_price_meta_tag', 'uni_cpo_price_for_meta_tag_displaying_aelia_currency_switcher', 10, 1 );
	function uni_cpo_price_for_meta_tag_displaying_aelia_currency_switcher( $price ) {
		return uni_cpo_aelia_price_convert( $price );
	}

	function uni_cpo_wcpbc_price_convert( $price ) {
		if ( function_exists( 'wcpbc_the_zone' ) ) {
			return wcpbc_the_zone()->get_exchange_rate_price( $price );
		} else {
			return $price;
		}
	}

	add_filter( 'uni_cpo_ajax_calculated_price', 'uni_cpo_ajax_calculated_price_wcpbc', 10, 1 );
	function uni_cpo_ajax_calculated_price_wcpbc( $price ) {
		return uni_cpo_wcpbc_price_convert( $price );
	}

	add_filter( 'uni_cpo_in_cart_calculated_price', 'uni_cpo_in_cart_calculated_price_wcpbc', 11, 1 );
	function uni_cpo_in_cart_calculated_price_wcpbc( $price ) {
		return uni_cpo_wcpbc_price_convert( $price );
	}

	add_filter( 'uni_cpo_price_regular_archive', 'uni_cpo_price_regular_archive_wcpbc', 9, 1 );
	function uni_cpo_price_regular_archive_wcpbc( $price ) {
		return uni_cpo_wcpbc_price_convert( $price );
	}

	add_filter( 'uni_cpo_price_starting_archive', 'uni_cpo_price_starting_archive_wcpbc', 9, 1 );
	function uni_cpo_price_starting_archive_wcpbc( $price ) {
		return uni_cpo_wcpbc_price_convert( $price );
	}

	add_filter( 'uni_cpo_display_price_meta_tag', 'uni_cpo_price_for_meta_tag_displaying_wcpbc', 10, 1 );
	function uni_cpo_price_for_meta_tag_displaying_wcpbc( $price ) {
		return uni_cpo_wcpbc_price_convert( $price );
	}
}

function uni_cpo_get_vars_from_content( $array ) {
	$return = array();
	foreach ( $array as $key => $value ) {
		if ( $key === 'cpo_slug' ) {
			$return[] = $value;
		}
		if ( is_array( $value ) ) {
			$return = array_merge( $return, uni_cpo_get_vars_from_content( $value ) );
		}
	}

	return $return;
}

function get_products_data_for_manager() {
	$products      = wc_get_products( [ 'type' => 'simple', 'posts_per_page' => - 1 ] );
	$products_data = [];

	if ( ! empty( $products ) ) {
		foreach ( $products as $product ) {
			$id              = $product->get_id();
			$product_data    = Uni_Cpo_Product::get_product_data_by_id( $id );
			$vars_used       = uni_cpo_get_vars_from_content( $product_data['content'] );
			$products_data[] = [
				'id'            => $id,
				'product'       => [
					'name'   => $product->get_name(),
					'url'    => $product_data['uri'],
					'cpoUrl' => Uni_Cpo_Product::get_edit_url( $id ),
				],
				'price'         => $product->get_price(),
				'settings_data' => $product_data['settings_data'],
				'formula_data'  => [
					'main_formula' => $product_data['formula_data']['main_formula']
				],
				'weight_data'   => [
					'weight_enable'       => $product_data['weight_data']['weight_enable'],
					'main_weight_formula' => $product_data['weight_data']['main_weight_formula']
				],
				//'nov_data'      => $product_data['nov_data'],
				'vars'          => $vars_used
			];
		}
	}

	return $products_data;
}
