<?php
/**
 * Uni Cpo Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author        MooMoo
 * @category    Core
 * @package    UniCpo/Functions
 * @version     4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;  // Exit if accessed directly
}

// Include core functions (available in both admin and frontend).
include( 'uni-cpo-functions.php' );
include( 'uni-cpo-formatting-functions.php' );

/**
 * Display an Uni Cpo help tip.
 *
 * @param string $tip Help tip text
 * @param bool $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 * @since  4.0.0
 *
 */
function uni_cpo_help_tip( $tip, $allow_html = false, $args = array() ) {
	if ( $allow_html ) {
		$tip = uni_cpo_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	if ( isset( $args['type'] ) && 'warning' === $args['type'] ) {
		$css_class = 'uni-cpo-tooltip-warning';
	} else {
		$css_class = 'uni-cpo-tooltip';
	}

	return '<span
                class="' . $css_class . '"
                data-tip="' . $tip . '">
                </span>';
}

/**
 * Serialize and encode
 *
 * @return    string
 *
 * @access    public
 * @since     4.0.0
 */
function uni_cpo_encode( $value ) {

	$func = 'base64' . '_encode';

	return $func( maybe_serialize( $value ) );

}

/**
 * Decode and unserialize
 *
 * @return    string
 *
 * @access    public
 * @since     4.0.0
 */
function uni_cpo_decode( $value ) {

	$func  = 'base64' . '_decode';
	$value = $func( $value );

	return maybe_unserialize( $value );

}

/**
 * Get values of modules from multidimensional array
 *
 * @return array
 * @since  4.0.0
 *
 */
function uni_cpo_get_mod_values( $content ) {
	$new_content = array();
	if ( is_array( $content ) ) {
		foreach ( $content as $key => $value ) {
			if ( 'content' === $key || 'html' === $key ) {
				$new_content[] = $value;
			}
			if ( is_array( $value ) ) {
				$new_content = array_merge( $new_content, uni_cpo_get_mod_values( $value ) );
			}
		}
	}

	return $new_content;
}

//
function uni_cpo_get_modules_by_type( $data, $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query(
		array(
			'post_type'      => $post_type,
			'meta_query'     => array(
				array(
					'key'     => '_module_type',
					'value'   => $data['type'],
					'compare' => '=',
				),
			),
			'posts_per_page' => - 1,
			'post__not_in'   => ( ! empty( $data['exclude_id'] ) ) ? array( $data['exclude_id'] ) : array()
		)
	);
	if ( ! empty( $query->posts ) ) {
		return $query->posts;
	} else {
		return false;
	}
}

//
function uni_cpo_is_slug_exists( $slug, $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query( array( 'name' => $slug, 'post_type' => $post_type ) );
	if ( ! empty( $query->posts ) ) {
		return true;
	} else {
		return false;
	}
}

//
function uni_cpo_get_post_by_slug( $slug, $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query( array( 'name' => $slug, 'post_type' => $post_type ) );
	if ( ! empty( $query->posts ) ) {
		return $query->posts[0];
	}

	return null;
}

//
function uni_cpo_get_posts_by_slugs( $slugs, $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query(
		array(
			'post_name__in'  => $slugs,
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'orderby'        => 'post_name__in'
		)
	);
	if ( ! empty( $query->posts ) ) {
		return $query->posts;
	}

	return null;
}

//
function uni_cpo_get_posts_by_ids( $ids, $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query(
		array(
			'post__in'       => $ids,
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'orderby'        => 'post__in'
		)
	);
	if ( ! empty( $query->posts ) ) {
		return $query->posts;
	}

	return null;
}

//
function uni_cpo_get_posts_slugs( $post_type = 'uni_cpo_option' ) {
	$query = new WP_Query( array( 'post_type' => $post_type, 'posts_per_page' => - 1 ) );
	if ( ! empty( $query->posts ) ) {
		$slugs_list = wp_list_pluck( $query->posts, 'post_name' );

		return $slugs_list;
	}

	return [];
}

//
function uni_cpo_truncate_post_slug( $slug, $length = 200 ) {
	if ( strlen( $slug ) > $length ) {
		$decoded_slug = urldecode( $slug );
		if ( $decoded_slug === $slug ) {
			$slug = substr( $slug, 0, $length );
		} else {
			$slug = utf8_uri_encode( $decoded_slug, $length );
		}
	}

	return rtrim( $slug, '-' );
}

//
function uni_cpo_get_unique_slug( $slug ) {

	if ( empty( $slug ) ) {
		return array( 'unique' => false, 'slug' => false );
	}

	$suffix           = 2;
	$existed_slugs    = uni_cpo_get_posts_slugs();
	$reserved_slugs   = uni_cpo_get_reserved_option_slugs();
	$prohibited_slugs = array_merge( $existed_slugs, $reserved_slugs );
	$is_slug_valid    = ( ! in_array( UniCpo()->get_var_slug() . $slug, $prohibited_slugs ) ) ? true : false;

	if ( $is_slug_valid ) {
		return array( 'unique' => true, 'slug' => $slug );
	} else {
		do {
			$alt_slug      = uni_cpo_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "_$suffix";
			$is_slug_valid = ( ! in_array( UniCpo()->get_var_slug() . $alt_slug, $prohibited_slugs ) ) ? true : false;
			$suffix ++;
		} while ( ! $is_slug_valid );

		return array( 'unique' => false, 'slug' => $alt_slug );
	}
}

function uni_cpo_get_similar_modules( $data ) {
	$items = array();
	if ( 'option' === $data['obj_type'] ) {
		$posts = uni_cpo_get_modules_by_type(
			array(
				'type'       => $data['type'],
				'exclude_id' => $data['pid']
			)
		);
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$module                     = uni_cpo_get_option( $post->ID );
				$items[ $module->get_id() ] = $module->get_slug();
			}
		}
	} elseif ( 'module' === $data['obj_type'] ) {
		$posts = uni_cpo_get_modules_by_type(
			array(
				'type'       => $data['type'],
				'exclude_id' => $data['pid']
			),
			'uni_module'
		);
		// TODO
	}

	return $items;
}


function uni_cpo_get_module_for_sync( $data ) {
	if ( 'option' === $data['obj_type'] ) {
		$module = uni_cpo_get_option( $data['pid'] );
		if ( $module ) {
			return $module->formatted_model_data();
		}
	} elseif ( 'module' === $data['obj_type'] ) {
		// TODO
	}

	return false;
}

function uni_cpo_get_similar_products_ids( $data ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
				),
			),
			'posts_per_page' => - 1,
			'post__not_in'   => ( ! empty( $data['pid'] ) ) ? array( $data['pid'] ) : array()
		)
	);
	if ( ! empty( $query->posts ) ) {
		return $query->posts;
	} else {
		return false;
	}
}

function uni_cpo_get_settings_data_sanitized( $data_data, $data_name ) {
	$original_data = $data_data;
	$data_data     = uni_cpo_clean( $data_data );

	return apply_filters( 'uni_cpo_filter_settings_data', $data_data, $original_data, $data_name );
}

add_filter( 'uni_cpo_filter_settings_data', 'uni_cpo_filter_settings_data_func', 10, 3 );

function uni_cpo_filter_settings_data_func( $data_data, $original_data, $data_name ) {
	if ( 'general' === $data_name ) {
		$data_data['main']['content'] = ! empty( $original_data['main']['content'] )
			? uni_cpo_sanitize_text( $original_data['main']['content'] )
			: '';
	}
	if ( 'cpo_general' === $data_name ) {
		$data_data['advanced']['cpo_tooltip'] = ! empty( $original_data['advanced']['cpo_tooltip'] )
			? uni_cpo_sanitize_text( stripslashes_deep( $original_data['advanced']['cpo_tooltip'] ) )
			: '';
		$data_data['main']['cpo_notice_text'] = ! empty( $original_data['main']['cpo_notice_text'] )
			? html_entity_decode( uni_cpo_sanitize_text( $original_data['main']['cpo_notice_text'] ) )
			: '';
	}
	if ( 'cpo_rules' === $data_name ) {
		$data_data['data'] = ! empty( $original_data['data'] )
			? $original_data['data']
			: '';
	}
	if ( 'cpo_validation' === $data_name ) {
		$data_data['main']  = ! empty( $original_data['main'] )
			? $original_data['main']
			: '';
		$data_data['logic'] = ! empty( $original_data['logic'] )
			? $original_data['logic']
			: '';
	}

	return $data_data;
}

function uni_cpo_option_apply_changes_walk( $v, $k, $d ) {
	if ( is_array( $v ) && ! empty( $v ) && isset( $d[1][ $k ] ) ) {
		array_walk( $v, 'uni_cpo_option_apply_changes_walk', array( &$d[0][ $k ], $d[1][ $k ] ) );
	} elseif ( ! is_array( $v ) && isset( $d[1][ $v ] ) ) {
		$d[0][ $v ] = $d[1][ $v ];
	} elseif ( ! is_array( $v ) ) {
		if ( isset( $d[0][ $v ] ) ) {
			$d[0][ $v ] = array();
		} else {
			$d[0] = [ $v => array() ];
		}
	}
}

//////////////////////////////////////////////////////////////////////////////////////
// Calculation functions
//////////////////////////////////////////////////////////////////////////////////////

function uni_cpo_process_formula_with_non_option_vars( &$variables, $product_data, &$formatted_vars ) {
	if ( is_array( $product_data['nov_data']['nov'] ) && ! empty( $product_data['nov_data']['nov'] ) ) {

		if ( isset( $product_data['nov_data']['nov']['<%row-count%>'] ) ) {
			return $variables;
		}

		if ( isset( $product_data['nov_data']['nov'][0] ) ) {
			$nov = $product_data['nov_data']['nov'][0];
		} elseif ( isset( $product_data['nov_data']['nov'][1] ) ) {
			$nov = $product_data['nov_data']['nov'][1];
		}
		$var_name = '{' . UniCpo()->get_nov_slug() . $nov['slug'] . '}';
		$formula  = 0;

		if ( isset( $nov['roles'] ) && 'on' === $product_data['nov_data']['wholesale_enable']
		     && ( ! isset( $nov['matrix']['enable'] ) || 'on' !== $nov['matrix']['enable'] ) ) {
			$formula = uni_cpo_get_role_based_nov_formula( $nov );
		} elseif ( isset( $nov['matrix']['enable'] ) && 'on' === $nov['matrix']['enable'] ) {
			if ( unicpo_fs()->is__premium_only() ) {
				$formula = uni_cpo_get_matrix_based_value( $nov, $variables );
			}
		} else {
			$formula = ( isset( $nov['formula'] ) ) ? $nov['formula'] : '';
		}

		if ( unicpo_fs()->is__premium_only() ) {
			// convert unit functionality
			if ( isset( $nov['convert']['enable'] ) && 'on' === $nov['convert']['enable'] ) {
				$formula = uni_cpo_get_converted_to_value( $nov, $variables, $product_data, $formatted_vars );
			}
		}

		$formula                                                   = uni_cpo_process_formula_with_vars( $formula, $variables );
		$nov_val                                                   = apply_filters(
			'uni_cpo_nov_variable_value',
			uni_cpo_calculate_formula( $formula ),
			$product_data,
			$variables,
			$nov['slug']
		);
		$variables[ $var_name ]                                    = $nov_val;
		$formatted_vars[ UniCpo()->get_nov_slug() . $nov['slug'] ] = $nov_val;
		array_splice( $product_data['nov_data']['nov'], 0, 1 );
		uni_cpo_process_formula_with_non_option_vars( $variables, $product_data, $formatted_vars );
	}

	return $variables;
}

if ( unicpo_fs()->is__premium_only() ) {
	function uni_cpo_get_matrix_based_value( $nov, $variables ) {
		$x_var       = $nov['matrix']['x_var'];
		$y_var       = $nov['matrix']['y_var'];
		$x_axis      = $nov['matrix']['x_axis'];
		$x_axis_data = explode( '|', $x_axis );

		$matrix_data_raw    = json_decode( $nov['matrix']['data'] );
		$matrix_data_keys   = array();
		$matrix_data_values = array();
		if ( is_array( $matrix_data_raw ) ) {
			foreach ( $matrix_data_raw as $k => $v ) {
				$data_key = array_shift( $v->columns );
				array_pop( $v->columns );
				$matrix_data_keys[]   = $data_key;
				$matrix_data_values[] = $v->columns;
			}
		} else {
			return 0;
		}

		if ( ! isset( $variables[ $x_var ] ) || ! isset( $variables[ $y_var ] ) ) {
			return 0;
		}

		$nov_var_name = UniCpo()->get_nov_slug() . $nov['slug'];
		if ( in_array( $nov_var_name, apply_filters( 'uni_cpo_rangesum_enabled_novs', [], 10 ) ) ) {
			$closest_x       = uni_cpo_get_closest( $variables[ $x_var ], $x_axis_data );
			$closest_x_index = array_search( $closest_x, $x_axis_data );
			$from            = min( $variables[ $x_var ], $variables[ $y_var ] );
			$to              = max( $variables[ $x_var ], $variables[ $y_var ] );

			$closest_from         = uni_cpo_get_closest( $from, $matrix_data_keys );
			$closest_from_index   = array_search( $closest_from, $matrix_data_keys );
			$closest_to           = uni_cpo_get_closest( $to, $matrix_data_keys );
			$closest_to_index     = array_search( $closest_to, $matrix_data_keys );
			$prices               = [];
			$prices_iterate_index = $closest_from_index;

			if ( $closest_to_index >= $closest_from_index ) {
				while ( $prices_iterate_index <= $closest_to_index ) {
					$prices[ $prices_iterate_index ] = $matrix_data_values[ $prices_iterate_index ][ $closest_x_index ];
					$prices_iterate_index ++;
				}
			} else {
				while ( $prices_iterate_index >= $closest_to_index ) {
					$prices[ $prices_iterate_index ] = $matrix_data_values[ $prices_iterate_index ][ $closest_x_index ];
					$prices_iterate_index --;
				}
			}

			//print_r( $matrix_data_keys );
			//print_r( "closest: $closest_from ($closest_from_index), $closest_to ($closest_to_index);; $from; $to" );
			//print_r($prices);

			$range                 = range( $from, $to );
			$prices_for_each_piece = [];

			foreach ( $range as $range_item ) {
				$closest_for_range                    = uni_cpo_get_closest( $range_item, $matrix_data_keys );
				$closest_for_range_index              = array_search( $closest_for_range, $matrix_data_keys );
				$prices_for_each_piece[ $range_item ] = isset( $prices[ $closest_for_range_index ] )
					? $prices[ $closest_for_range_index ]
					: 0;
			}

			//print_r( $prices_for_each_piece );

			return array_sum( array_values( $prices_for_each_piece ) );
		} elseif ( in_array( $nov_var_name, apply_filters( 'uni_cpo_rangesum_from_one_enabled_novs', [], 10 ) ) ) {
			$closest_x       = uni_cpo_get_closest( $variables[ $x_var ], $x_axis_data );
			$closest_x_index = array_search( $closest_x, $x_axis_data );
			$to              = $variables[ $y_var ];

			$closest_from_index   = 0;
			$closest_to           = uni_cpo_get_closest( $to, $matrix_data_keys );
			$closest_to_index     = array_search( $closest_to, $matrix_data_keys );
			$prices               = [];
			$prices_iterate_index = $closest_from_index;

			if ( $closest_to_index >= $closest_from_index ) {
				while ( $prices_iterate_index <= $closest_to_index ) {
					$prices[ $prices_iterate_index ] = $matrix_data_values[ $prices_iterate_index ][ $closest_x_index ];
					$prices_iterate_index ++;
				}
			} else {
				while ( $prices_iterate_index >= $closest_to_index ) {
					$prices[ $prices_iterate_index ] = $matrix_data_values[ $prices_iterate_index ][ $closest_x_index ];
					$prices_iterate_index --;
				}
			}

			//print_r( $matrix_data_keys );
			//print_r( "closest: 1 ($closest_from_index), $closest_to ($closest_to_index);; 1; $to" );
			//print_r($prices);

			$range                 = range( 1, $to );
			$prices_for_each_piece = [];

			foreach ( $range as $range_item ) {
				$closest_for_range                    = uni_cpo_get_closest( $range_item, $matrix_data_keys );
				$closest_for_range_index              = array_search( $closest_for_range, $matrix_data_keys );
				$prices_for_each_piece[ $range_item ] = isset( $prices[ $closest_for_range_index ] )
					? $prices[ $closest_for_range_index ]
					: 0;
			}

			//print_r( $prices_for_each_piece );

			return array_sum( array_values( $prices_for_each_piece ) );
		} else {
			$closest_x       = uni_cpo_get_closest( $variables[ $x_var ], $x_axis_data );
			$closest_x_index = array_search( $closest_x, $x_axis_data );
			if ( ! empty ( $y_var ) ) {
				$closest_y       = uni_cpo_get_closest( $variables[ $y_var ], $matrix_data_keys );
				$closest_y_index = array_search( $closest_y, $matrix_data_keys );

				return $matrix_data_values[ $closest_y_index ][ $closest_x_index ];
			}

			return $matrix_data_values[0][ $closest_x_index ];
		}
	}

	function uni_cpo_get_converted_to_value( $nov, $variables, $product_data, $filtered_form_data ) {
		$unit_default = strtolower( get_option( 'woocommerce_dimension_unit' ) );
		$to_unit      = ( ! empty( $nov['convert']['to'] ) )
			? $nov['convert']['to']
			: $unit_default;
		$unit_option  = ( ! empty( $product_data['dimensions_data']['d_unit_option'] ) && ! empty( $filtered_form_data[ $product_data['dimensions_data']['d_unit_option'] ] ) )
			? $filtered_form_data[ $product_data['dimensions_data']['d_unit_option'] ]
			: '';
		$from_unit    = $unit_option ? $unit_option : $unit_default;
		$from_value   = ( ! empty( $variables[ $nov['formula'] ] ) ) ? $variables[ $nov['formula'] ] : 0;

		if ( $from_value ) {
			return uni_cpo_get_dimension( $from_value, $from_unit, $to_unit );
		}

		return 0;
	}
}

function uni_cpo_get_role_based_nov_formula( $nov ) {
	$price_for_role = '';
	$current_user   = wp_get_current_user();

	if ( current_user_can( 'edit_shop_orders' ) ) {
		$plugin_settings = UniCpo()->get_settings();
		$price_for_role  = $plugin_settings['order_edit_role'];
	} else {
		$price_for_role = isset( $current_user->roles ) &&
		                  isset( $current_user->roles[0] ) ? $current_user->roles[0] : '';
	}

	if ( empty( $price_for_role ) ) {
		return $nov['formula'];
	} else {
		if ( in_array( $price_for_role, $nov['roles'] ) ) {
			return $nov[ $price_for_role ]['formula'];
		} else {
			return $nov['formula'];
		}
	}
}

function uni_cpo_process_formula_scheme( $variables, $product_data, $purpose = 'price' ) {

	if ( 'price' === $purpose ) {
		$scheme_data = $product_data['formula_data']['formula_scheme'];
	} elseif ( 'weight' === $purpose ) {
		$scheme_data = $product_data['weight_data']['weight_scheme'];
	} elseif ( 'option_rules' === $purpose ) {
		$scheme_data = $product_data;
	}

	// let's inject a special variables to be used in formula logic
	$variables['currency'] = get_woocommerce_currency();
	$variables             = apply_filters( 'uni_cpo_variables_for_formula_logic', $variables );

	if ( ! isset( $scheme_data ) ) {
		return false;
	}

	foreach ( $scheme_data as $scheme_key => $scheme_item ) {
		$formula_block     = $scheme_item['formula'];
		$rules_block       = json_decode( $scheme_item['rule'], true );
		$block_condition   = $rules_block['condition'];
		$is_passed_block   = false;
		$block_rules_count = is_array( $rules_block['rules'] ) ? count( $rules_block['rules'] ) : 0;

		if ( $block_rules_count > 1 ) {
			$check_for_1 = array();
			$check_for_2 = array();

			foreach ( $rules_block['rules'] as $rule_key => $rule_item ) {
				$check_for_3 = array();

				if ( isset( $rule_item['rules'] ) ) {
					$rule_1_condition = $rule_item['condition'];
					foreach ( $rule_item['rules'] as $rule_2_key => $rule_2_item ) {
						$check_for_3[] = uni_cpo_formula_condition_check( $rule_2_item, $variables );
					}

					if ( false === in_array( false, $check_for_3, true ) && 'AND' === $rule_1_condition ) {
						$is_passed_2 = true;
					} elseif ( false !== in_array( true, $check_for_3, true ) && 'OR' === $rule_1_condition ) {
						$is_passed_2 = true;
					} else {
						$is_passed_2 = false;
					}
					array_push( $check_for_2, $is_passed_2 );
				} else {
					$check_for_1[] = uni_cpo_formula_condition_check( $rule_item, $variables );
				}
			}

			$check_for_1 = array_merge( $check_for_1, $check_for_2 );

			if ( false === in_array( false, $check_for_1, true ) && 'AND' === $block_condition ) {
				$is_passed_block = true;
			} elseif ( false !== in_array( true, $check_for_1, true ) && 'OR' === $block_condition ) {
				$is_passed_block = true;
			} else {
				$is_passed_block = false;
			}

		} else {
			if ( is_array( $rules_block['rules'] ) ) {
				foreach ( $rules_block['rules'] as $rule_key => $rule_item ) {
					$is_passed_block = uni_cpo_formula_condition_check( $rule_item, $variables );
				}
			}
		}

		if ( $is_passed_block ) {
			return $formula_block;
		}
	}

	return false;
}

// formula condition check
function uni_cpo_formula_condition_check( $rule, $variables ) {


	$var_name   = $rule['id'];
	$rule_value = $rule['value'];
	$rule_type  = $rule['type'];
	$is_passed  = false;

	switch ( $rule['operator'] ) {

		case 'less':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_date   = new DateTime( $rule_value );
					$chosen_date = new DateTime( $variables[ $var_name ] );

					if ( $chosen_date < $rule_date ) {
						$is_passed = true;
					}
				} else {
					if ( floatval( $variables[ $var_name ] ) < floatval( $rule_value ) ) {
						$is_passed = true;
					}
				}
			}
			break;

		case 'less_or_equal':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_date   = new DateTime( $rule_value );
					$chosen_date = new DateTime( $variables[ $var_name ] );

					if ( $chosen_date <= $rule_date ) {
						$is_passed = true;
					}
				} else {
					if ( floatval( $variables[ $var_name ] ) <= floatval( $rule_value ) ) {
						$is_passed = true;
					}
				}
			}
			break;

		case 'equal':
			if ( isset( $variables[ $var_name ] ) && ! is_array( $variables[ $var_name ] ) ) {
				if ( in_array( $rule_type, array( 'double', 'integer' ) ) ) {
					$is_passed = floatval( $variables[ $var_name ] ) === floatval( $rule_value );
				} else {
					$is_passed = $variables[ $var_name ] === $rule_value;
				}
			} elseif ( isset( $variables[ $var_name ] ) && is_array( $variables[ $var_name ] ) ) {
				foreach ( $variables[ $var_name ] as $value ) {
					if ( $value === $rule_value ) {
						$is_passed = true;
						break;
					}
				}
			}
			break;

		case 'not_equal':
			if ( isset( $variables[ $var_name ] ) && ! is_array( $variables[ $var_name ] ) ) {
				if ( in_array( $rule_type, array( 'double', 'integer' ) ) ) {
					$is_passed = floatval( $variables[ $var_name ] ) !== floatval( $rule_value );
				} else {
					$is_passed = $variables[ $var_name ] !== $rule_value;
				}
			} elseif ( isset( $variables[ $var_name ] ) && is_array( $variables[ $var_name ] ) ) {
				foreach ( $variables[ $var_name ] as $value ) {
					if ( $value !== $rule_value ) {
						$is_passed = true;
						break;
					}
				}
			}
			break;

		case 'greater_or_equal':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_date   = new DateTime( $rule_value );
					$chosen_date = new DateTime( $variables[ $var_name ] );

					if ( $chosen_date >= $rule_date ) {
						$is_passed = true;
					}
				} else {
					if ( floatval( $variables[ $var_name ] ) >= floatval( $rule_value ) ) {
						$is_passed = true;
					}
				}
			}
			break;

		case 'greater':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_date   = new DateTime( $rule_value );
					$chosen_date = new DateTime( $variables[ $var_name ] );

					if ( $chosen_date > $rule_date ) {
						$is_passed = true;
					}
				} else {
					if ( floatval( $variables[ $var_name ] ) > floatval( $rule_value ) ) {
						$is_passed = true;
					}
				}
			}
			break;

		case 'is_empty':
			if ( ! isset( $variables[ $var_name ] ) || empty( $variables[ $var_name ] ) ) {
				$is_passed = true;
			}
			break;

		case 'is_not_empty':
			if ( ! empty( $variables[ $var_name ] ) ) {
				$is_passed = true;
			}
			break;

		case 'between':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_startdate = new DateTime( $rule_value[0] );
					$rule_enddate   = new DateTime( $rule_value[1] );
					$chosen_date    = new DateTime( $variables[ $var_name ] );

					if ( $rule_startdate <= $chosen_date && $chosen_date <= $rule_enddate ) {
						$is_passed = true;
					}
				} else {
					$is_passed = floatval( $rule_value[0] ) <= floatval( $variables[ $var_name ] ) && floatval( $variables[ $var_name ] ) <= floatval( $rule_value[1] );
				}
			}
			break;

		case 'not_between':
			if ( isset( $variables[ $var_name ] ) ) {
				if ( 'date' === $rule_type ) {
					$rule_startdate = new DateTime( $rule_value[0] );
					$rule_enddate   = new DateTime( $rule_value[1] );
					$chosen_date    = new DateTime( $variables[ $var_name ] );

					if ( $rule_startdate >= $chosen_date || $chosen_date >= $rule_enddate ) {
						$is_passed = true;
					}
				} else {
					$is_passed = floatval( $rule_value[0] ) >= floatval( $variables[ $var_name ] ) || floatval( $variables[ $var_name ] ) >= floatval( $rule_value[1] );
				}
			}
			break;

	}

	return $is_passed;
}

//
function uni_cpo_process_formula_with_vars( $main_formula, $variables = array() ) {
	$main_formula = preg_replace( '/\s+/', '', $main_formula );

	if ( ! empty( $variables ) ) {
		foreach ( $variables as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( ! empty( $v ) ) {
					foreach ( $v as $k_child => $v_child ) {
						$search       = "/($k_child)/";
						$main_formula = preg_replace( $search, $v_child, $main_formula );
					}
				}
			} else {
				$search       = "/($k)/";
				$main_formula = preg_replace( $search, $v, $main_formula );
			}
		}

		$pattern      = "/{([^}]*)}/";
		$main_formula = preg_replace( $pattern, '0', $main_formula );
	} else {
		$pattern      = "/{([^}]*)}/";
		$main_formula = preg_replace( $pattern, '0', $main_formula );
	}

	return $main_formula;
}

//
function uni_cpo_calculate_formula( $main_formula = '' ) {

	if ( ! empty( $main_formula ) && 'disable' !== $main_formula ) {
		// change the all unused variables to zero, so formula calculation will not fail
		$pattern      = "/{([^}]*)}/";
		$main_formula = preg_replace( $pattern, '0', $main_formula );

		// calculate
		$m                  = new EvalMath;
		$m->suppress_errors = true;
		$calc_price         = $m->evaluate( $main_formula );
		$calc_price         = ( ! is_infinite( $calc_price ) && ! is_nan( $calc_price ) ) ? $calc_price : 0;

		return floatval( $calc_price );
	} else {
		return 0;
	}
}

//
function uni_cpo_option_js_condition_prepare( $scheme ) {
	$condition_operator = $scheme['condition'];
	$operator           = ( 'AND' === $condition_operator ) ? '&&' : '||';
	$rules              = $scheme['rules'];
	$rules_count        = is_array( $rules ) ? count( $rules ) : 0;

	if ( $rules_count > 1 ) {
		foreach ( $rules as $rule ) {
			if ( isset( $rule['rules'] ) ) {
				$statements[] = uni_cpo_option_js_condition_prepare( $rule );
			} else {
				$statements[] = uni_cpo_option_js_condition( $rule );
			}
		}
		$condition = '(' . implode( " $operator ", $statements ) . ')';
	} else {
		if ( is_array( $rules ) ) {
			foreach ( $rules as $rule ) {
				if ( isset( $rule['rules'] ) ) {
					$statement = uni_cpo_option_js_condition_prepare( $rule );
				} else {
					$statement = uni_cpo_option_js_condition( $rule );
				}
			}
			$condition = '(' . $statement . ')';
		}
	}

	return $condition;
}

// option condition js builder
function uni_cpo_option_js_condition( $rule ) {

	$cpo_var = 'formData';

	switch ( $rule['operator'] ) {
		case 'less':
			$statement = "UniCpo.isProp({$cpo_var}, '{$rule['id']}') && {$cpo_var}.{$rule['id']} < {$rule['value']}";
			break;
		case 'less_or_equal':
			$statement = "UniCpo.isProp({$cpo_var}, '{$rule['id']}') && {$cpo_var}.{$rule['id']} <= {$rule['value']}";
			break;
		case 'equal':
			$statement = "UniCpo.isProp({$cpo_var}, '{$rule['id']}') && ({$cpo_var}.{$rule['id']}.constructor === Array ? {$cpo_var}.{$rule['id']}.indexOf('{$rule['value']}') !== -1 : (window.UniCpo.isNumber('{$rule['value']}') ? parseFloat({$cpo_var}.{$rule['id']}) === parseFloat('{$rule['value']}') : {$cpo_var}.{$rule['id']} === '{$rule['value']}'))";
			break;
		case 'not_equal':
			$statement = "!UniCpo.isProp({$cpo_var}, '{$rule['id']}') || (UniCpo.isProp({$cpo_var}, '{$rule['id']}') && ({$cpo_var}.{$rule['id']}.constructor === Array ? {$cpo_var}.{$rule['id']}.indexOf('{$rule['value']}') === -1 : (window.UniCpo.isNumber('{$rule['value']}') ? parseFloat({$cpo_var}.{$rule['id']}) !== parseFloat('{$rule['value']}') : {$cpo_var}.{$rule['id']} !== '{$rule['value']}')))";
			break;
		case 'greater_or_equal':
			$statement = "UniCpo.isProp({$cpo_var}, '{$rule['id']}') && {$cpo_var}.{$rule['id']} >= {$rule['value']}";
			break;
		case 'greater':
			$statement = "UniCpo.isProp({$cpo_var}, '{$rule['id']}') && {$cpo_var}.{$rule['id']} > {$rule['value']}";
			break;
		case 'is_empty':
			$statement = "(R.isNil({$cpo_var}.{$rule['id']}) || R.isEmpty({$cpo_var}.{$rule['id']}))";
			break;
		case 'is_not_empty':
			$statement = "!(R.isNil({$cpo_var}.{$rule['id']}) || R.isEmpty({$cpo_var}.{$rule['id']}))";
			break;
		case 'between':
			if ( $rule['type'] === 'date' ) {
				$statement = "(UniCpo.isProp({$cpo_var}, '{$rule['id']}') && UniCpo.isDateBetween('{$rule['value'][0]}', '{$rule['value'][1]}', {$cpo_var}.{$rule['id']}))";
			} else {
				$statement = "(UniCpo.isProp({$cpo_var}, '{$rule['id']}') && {$cpo_var}.{$rule['id']} >= {$rule['value'][0]} && {$cpo_var}.{$rule['id']} <= {$rule['value'][1]})";
			}
			break;
		case 'not_between':
			if ( $rule['type'] === 'date' ) {
				$statement = "(UniCpo.isProp({$cpo_var}, '{$rule['id']}') && !UniCpo.isDateBetween('{$rule['value'][0]}', '{$rule['value'][1]}', {$cpo_var}.{$rule['id']}))";
			} else {
				$statement = "(UniCpo.isProp({$cpo_var}, '{$rule['id']}') && ({$cpo_var}.{$rule['id']} <= {$rule['value'][0]} || {$cpo_var}.{$rule['id']} >= {$rule['value'][1]}))";
			}
			break;
	}

	return $statement;

}

if ( unicpo_fs()->is__premium_only() ) {
	function uni_cpo_apply_cart_discounts( $price_calculated, $product_data, $variables, &$cart_item_data = false, $quantity = 1 ) {
		$is_role_based_discounts = isset( $product_data['discounts_data']['role_cart_discounts_enable'] ) && 'on' === $product_data['discounts_data']['role_cart_discounts_enable'];
		$is_qty_based_discounts  = isset( $product_data['discounts_data']['qty_cart_discounts_enable'] ) && 'on' === $product_data['discounts_data']['qty_cart_discounts_enable'];
		$discounts_data          = array();
		$is_discount_applied     = 0;

		// role based discounts
		if ( $is_role_based_discounts ) {
			$discounts_data[0]   = array();
			$current_user        = wp_get_current_user();
			$role_cart_discounts = $product_data['discounts_data']['role_cart_discounts'];

			// we have a logged in user with a certain role
			if ( $current_user->ID ) {
				$role = $current_user->roles ? $current_user->roles[0] : false;

				if ( ! empty( $role_cart_discounts[ $role ]['value'] ) ) {
					$discounts_data[0]   = uni_cpo_calc_discount_by_type( $role_cart_discounts[ $role ]['type'], $role_cart_discounts[ $role ]['value'], $variables, $price_calculated );
					$is_discount_applied = 1;
				}
			}

			if ( empty( $discounts_data[0] ) ) {
				unset( $discounts_data[0] );
			}
		}

		// qty based discounts
		if ( $is_qty_based_discounts ) {
			$discounts_data[1]  = array();
			$qty_cart_discounts = $product_data['discounts_data']['qty_cart_discounts'];
			$qty_field_name     = $product_data['discounts_data']['qty_cart_discounts_field'];

			if ( ! empty( $qty_cart_discounts ) && is_array( $qty_cart_discounts ) ) {
				$qty = $qty_field_name === 'wc'
					? ( false !== $cart_item_data ? $cart_item_data['quantity'] : $quantity )
					: floatval( $variables[ '{' . $qty_field_name . '}' ] );

				foreach ( $qty_cart_discounts as $qty_cart_discount ) {
					if ( $qty >= $qty_cart_discount['min'] && $qty <= $qty_cart_discount['max'] && ! empty( $qty_cart_discount['type'] ) && ! empty( $qty_cart_discount['value'] ) ) {
						$discounts_data[1]   = uni_cpo_calc_discount_by_type( $qty_cart_discount['type'], $qty_cart_discount['value'], $variables, $price_calculated );
						$is_discount_applied = 1;
					}
				}
			}

			if ( empty( $discounts_data[1] ) ) {
				unset( $discounts_data[1] );
			}
		}

		$cart_item_data['_cpo_cart_discounts']['is_applied'] = $is_discount_applied;

		// cart discounts strategy
		$cart_discounts_strategy = $product_data['discounts_data']['cart_discounts_strategy'];
		if ( ! empty( $cart_discounts_strategy ) ) {
			switch ( $cart_discounts_strategy ) {
				case 'highest' :
					$highest_discount_data = array_reduce( $discounts_data, function ( $a, $b ) {
						return $a ? ( $a['diff'] > $b['diff'] ? $a : $b ) : $b;
					} );

					if ( null !== $highest_discount_data ) {
						// transferring data to the cart item
						if ( false !== $cart_item_data ) {
							$cart_item_data['_cpo_cart_discounts']               = $highest_discount_data;
							$cart_item_data['_cpo_cart_discounts']['is_applied'] = $is_discount_applied;
						}

						return $highest_discount_data['discounted'];
					}
					break;
				case 'combine' :
					$combined_discount = array_reduce( $discounts_data, function ( $a, $b ) {
						return $a + $b['diff'];
					}, 0 );

					if ( $combined_discount ) {
						$combined_discount_price = uni_cpo_calculate_formula( $discounts_data[0]['original'] - $combined_discount );
						if ( false !== $cart_item_data ) {
							$cart_item_data['_cpo_cart_discounts'] = array(
								'discounted' => $combined_discount_price,
								'original'   => $discounts_data[0]['original'],
								'type'       => 'abs',
								'discount'   => $combined_discount,
								'diff'       => $combined_discount,
								'is_applied' => $is_discount_applied
							);
						}

						return $combined_discount_price;
					}

					break;
			}
		}

		return $price_calculated;
	}

	function uni_cpo_calc_discount_by_type( $type, $rate, $variables, $price_calculated ) {
		$data = array();
		// in case a maths formula is given
		$value = uni_cpo_process_formula_with_vars( $rate, $variables );
		$value = uni_cpo_calculate_formula( $value );

		switch ( $type ) {
			case 'per':
				$discounted_price   = uni_cpo_calculate_formula( "$price_calculated - $price_calculated * $value/100" );
				$data['discounted'] = $discounted_price;
				$data['original']   = $price_calculated;
				$data['type']       = $type;
				$data['discount']   = $value;
				$data['diff']       = uni_cpo_calculate_formula( "$price_calculated - $discounted_price" );
				break;
			case 'abs' :
				$discounted_price   = uni_cpo_calculate_formula( "$price_calculated - $value" );
				$data['discounted'] = $discounted_price;
				$data['original']   = $price_calculated;
				$data['type']       = $type;
				$data['discount']   = uni_cpo_aelia_price_convert( $value );
				$data['diff']       = uni_cpo_aelia_price_convert( $value );
				break;
		}

		return $data;
	}

	add_filter( 'woocommerce_cart_item_price', 'uni_cpo_display_cart_discounts', 10, 2 );
	function uni_cpo_display_cart_discounts( $price, $cart_item ) {

		$discounts_data = ( isset( $cart_item['_cpo_cart_discounts'] ) ) ? $cart_item['_cpo_cart_discounts'] : array();

		if ( ! empty( $discounts_data['is_applied'] ) && ! empty( $discounts_data ) && ! empty( $discounts_data['type'] ) ) {
			$discount_suffix = '';

			switch ( $discounts_data['type'] ) {
				case 'per' :
					$discount_suffix = sprintf( __( '%1$s(Saved %2$s%%!)%3$s', 'uni-cpo' ), '<span class="cpo-cart-discount-info">', $discounts_data['discount'], '<span>' );
					break;
				case 'abs' :
					$discount_suffix = sprintf( __( '%1$s(Saved %2$s%!)%3$s', 'uni-cpo' ), '<span class="cpo-cart-discount-info">', uni_cpo_price( $discounts_data['diff'] ), '<span>' );
					break;
			}

			$price .= apply_filters( 'uni_cpo_cart_price_tag_with_discount', $discount_suffix, $price, $cart_item );
		}

		return $price;
	}

	add_filter( 'woocommerce_cart_item_quantity', 'uni_cpo_woocommerce_cart_item_quantity', 10, 3 );
	function uni_cpo_woocommerce_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		$product        = $cart_item['data'];
		$product_id     = intval( $product->get_id() );
		$product_data   = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$qty_field_slug = $product_data['settings_data']['qty_field'];

		if ( $product->is_sold_individually() && 'wc' !== $qty_field_slug
		     && isset( $cart_item['_cpo_data'][ $qty_field_slug ] ) && ! empty( absint( $cart_item['_cpo_data'][ $qty_field_slug ] ) ) ) {
			$product_quantity = sprintf( '%1$s <input type="hidden" name="cart[%2$s][qty]" value="%1$s" />', $cart_item['_cpo_data'][ $qty_field_slug ], $cart_item_key );
		}

		return $product_quantity;
	}
}

//////////////////////////////////////////////////////////////////////////////////////
// WC related functions and hooks
//////////////////////////////////////////////////////////////////////////////////////
/**
 * Format the price with a currency symbol. Adapted from wc_price()
 *
 * @param $price
 * @param array $args
 *
 * @return string
 */
function uni_cpo_price( $price, $args = array() ) {

	$defaults = array(
		'ex_tax_label'       => false,
		'currency'           => '',
		'decimal_separator'  => wc_get_price_decimal_separator(),
		'thousand_separator' => wc_get_price_thousand_separator(),
		'decimals'           => wc_get_price_decimals(),
		'price_format'       => get_woocommerce_price_format(),
	);

	$data = apply_filters( 'wc_price_args', wp_parse_args( $args, $defaults ) );

	$negative = $price < 0;
	$price    = apply_filters( 'uni_cpo_price_raw', floatval( $negative ? $price * - 1 : $price ) );
	$price    = apply_filters(
		'formatted_uni_cpo_price',
		number_format( $price, $data['decimals'], $data['decimal_separator'], $data['thousand_separator'] ),
		$price,
		$data['decimals'],
		$data['decimal_separator'],
		$data['thousand_separator']
	);

	if ( apply_filters( 'uni_cpo_price_trim_zeros', false ) && $data['decimals'] > 0 ) {
		$price = wc_trim_zeros( $price );
	}

	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $data['price_format'], get_woocommerce_currency_symbol( $data['currency'] ), $price );

	if ( $data['ex_tax_label'] && wc_tax_enabled() ) {
		$formatted_price .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
	}

	return apply_filters( 'uni_cpo_price', $formatted_price, $price, $args );

}

// customers try to add a product to the cart from an archive page? let's check if it is possible to do!
add_filter( 'woocommerce_loop_add_to_cart_link', 'uni_cpo_add_to_cart_button', 10, 2 );
function uni_cpo_add_to_cart_button( $link, $product ) {

	$product_id   = intval( $product->get_id() );
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

	if ( $product->is_in_stock() ) {
		$button_text = __( 'Select options', 'uni-cpo' );
	} else {
		$button_text = __( 'Out of stock / See details', 'uni-cpo' );
	};

	if ( 'on' === $product_data['settings_data']['cpo_enable'] ) {
		$link = sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
			esc_url( get_permalink( $product_id ) ),
			esc_attr( isset( $quantity ) ? $quantity : 1 ),
			esc_attr( $product->get_id() ),
			esc_attr( $product->get_sku() ),
			esc_attr( isset( $class ) ? $class : 'button' ),
			esc_html( $button_text )
		);
	}

	return $link;
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	add_action( 'woocommerce_after_add_to_cart_quantity', 'uni_cpo_update_cart_item_edit_btn_html', 10 );
	function uni_cpo_update_cart_item_edit_btn_html() {
		global $post;
		$is_cart_editing = Uni_Cpo_Product::is_cart_item_editing( $post->ID );

		if ( $is_cart_editing ) {
			$btn_text = apply_filters(
				'uni_cpo_update_cart_item_edit_btn_text',
				'<i class="fa fa-cancel" aria-hidden="true"></i>' . esc_html__( 'Update', 'uni-cpo' ),
				$post->ID
			);
			?>
            <script>
                jQuery(document).ready(function($) {
                    'use strict';

                    window.UniCpo.addToCartBtnEl
                        .attr('type', 'button')
                        .attr('name', 'update-cart-item')
                        .addClass('uni-cpo-update-btn js-uni-cpo-update-btn uni_cpo_ajax_add_to_cart')
                        .html('<?php echo $btn_text ?>');
                });
            </script>
			<?php
		}
	}


	//
	add_action( 'woocommerce_after_add_to_cart_button', 'uni_cpo_cancel_cart_item_edit_link_html', 10 );
	function uni_cpo_cancel_cart_item_edit_link_html() {
		global $post;
		$product_data = Uni_Cpo_Product::get_product_data_by_id( $post->ID );

		if ( isset( $_GET['cpo_cart_item_edit'] ) && get_transient( '_cpo_cart_item_edit_' . $_GET['cpo_cart_item_edit'] ) ) {
			$transient_data = get_transient( '_cpo_cart_item_edit_' . $_GET['cpo_cart_item_edit'] );

			if ( WC()->cart->get_cart_contents() && $transient_data['product_id'] === $product_data['id'] ) {
				$btn_text = apply_filters(
					'uni_cpo_cancel_cart_item_edit_link_text',
					'<i class="fa fa-cancel" aria-hidden="true"></i>' . esc_html__( 'Cancel', 'uni-cpo' ),
					$post->ID
				);

				echo '<a href="' . esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ) . '" type="button" class="uni-cpo-cancel-edit-link button alt">' . $btn_text . '</a>';
			}
		}
	}
}

//
add_action( 'uni_cpo_after_render_content', 'uni_cpo_calculate_button_html', 10 );
function uni_cpo_calculate_button_html() {
	global $post;
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $post->ID );

	if ( 'on' === $product_data['settings_data']['calc_btn_enable'] ) {
		$btn_text = apply_filters(
			'uni_cpo_calculate_btn_text',
			'<i class="fas fa-calculator" aria-hidden="true"></i>' . esc_html__( 'Calculate', 'uni-cpo' ),
			$post->ID
		);

		echo '<button type="button" class="uni-cpo-calculate-btn js-uni-cpo-calculate-btn button alt">' . $btn_text . '</button>';
	}
}

//
add_action( 'uni_cpo_after_render_content', 'uni_cpo_reset_form_btn_html', 10 );
function uni_cpo_reset_form_btn_html() {
	global $post;
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $post->ID );

	if ( 'on' === $product_data['settings_data']['reset_form_btn'] ) {
		$btn_text = apply_filters(
			'uni_cpo_reset_form_btn_text',
			'' . esc_html__( 'Reset form', 'uni-cpo' ),
			$post->ID
		);

		echo '<button type="button" class="uni-cpo-reset-form-btn js-uni-cpo-reset-form-btn button alt">' . $btn_text . '</button>';
	}
}

add_filter( 'woocommerce_get_price_html', 'uni_cpo_display_custom_price_on_archives', 10, 2 );
function uni_cpo_display_custom_price_on_archives( $price, $product ) {

	if ( is_admin() ) {
		return $price;
	}

	$product_id      = intval( $product->get_id() );
	$product_data    = Uni_Cpo_Product::get_product_data_by_id( $product_id );
	$product_post_id = 0;
	global $wp_query;

	if ( isset( $wp_query->queried_object->post_content )
	     && has_shortcode( $wp_query->queried_object->post_content, 'product_page' ) ) {
		if ( has_shortcode( $wp_query->queried_object->post_content, 'product_page' ) ) {
			$pattern = '\[(\[?)(product_page)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			if ( preg_match_all( '/' . $pattern . '/s', $wp_query->queried_object->post_content, $matches )
			     && array_key_exists( 2, $matches )
			     && in_array( 'product_page', $matches[2] ) ) {
				foreach ( $matches[2] as $key => $value ) {
					if ( $value === 'product_page' ) {
						$parsed = shortcode_parse_atts( $matches[3][ $key ] );
						if ( is_array( $parsed ) ) {
							foreach ( $parsed as $attr_name => $attr_value ) {
								if ( $attr_name === 'id' ) {
									$product_post_id = intval( $attr_value );
									break 2;
								}
							}
						}
					}
				}
			}
		}
	}

	if ( 'on' === $product_data['settings_data']['cpo_enable'] && 'on' === $product_data['settings_data']['calc_enable']
	     && (
		     ( is_single() && $product_id !== $wp_query->queried_object_id )
		     || ( is_page() && $product_id !== $product_post_id )
		     || is_tax() || is_archive()
		     || ( is_single() && ! is_singular( 'product' ) && isset( $wp_query->queried_object->post_content )
		          && ! has_shortcode( $wp_query->queried_object->post_content, 'product_page' )
		     )
		     || ( is_main_query() && ! in_the_loop() )
	     )
	) {
		$price = uni_cpo_get_proper_price_for_archive( $product );

		return $price;
	} else {
		return $price;
	}
}

function uni_cpo_get_proper_price_for_archive( $product ) {
	$defaults = array(
		'decimal_separator'  => wc_get_price_decimal_separator(),
		'thousand_separator' => wc_get_price_thousand_separator(),
		'decimals'           => wc_get_price_decimals(),
		'price_format'       => get_woocommerce_price_format(),
	);

	$product_id            = intval( $product->get_id() );
	$product_data          = Uni_Cpo_Product::get_product_data_by_id( $product_id );
	$raw_regular_price     = $product->get_regular_price( 'edit' );
	$raw_sale_price        = $product->get_sale_price( 'edit' );
	$display_regular_price = apply_filters(
		'uni_cpo_price_regular_archive',
		wc_get_price_to_display( $product, array( 'price' => $raw_regular_price ) ),
		$product );
	$display_sale_price    = apply_filters(
		'uni_cpo_price_sale_archive',
		wc_get_price_to_display( $product, array( 'price' => $raw_sale_price ) ),
		$product );
	$starting_price        = 0;
	$is_using_archive_tmpl = false;
	$price                 = wc_get_price_to_display( $product );
	$display_regular_price = number_format( $display_regular_price, $defaults['decimals'], $defaults['decimal_separator'], $defaults['thousand_separator'] );
	$display_sale_price    = number_format( $display_sale_price, $defaults['decimals'], $defaults['decimal_separator'], $defaults['thousand_separator'] );

	if ( unicpo_fs()->is__premium_only() ) {
		$starting_price     = ( ! empty( $product_data['settings_data']['starting_price'] ) )
			? uni_cpo_aelia_price_convert( floatval( $product_data['settings_data']['starting_price'] ) )
			: 0;
		$archives_tmpl      = ( ! empty( $product_data['settings_data']['price_archives'] ) )
			? __( $product_data['settings_data']['price_archives'], 'uni-cpo' )
			: '';
		$archives_tmpl_sale = ( ! empty( $product_data['settings_data']['price_archives_sale'] ) )
			? __( $product_data['settings_data']['price_archives_sale'], 'uni-cpo' )
			: '';

		if ( $starting_price ) {
			$price = apply_filters( 'uni_cpo_price_starting_archive', $starting_price, $product );
		}

		if ( ! empty( $archives_tmpl ) || ( $product->is_on_sale() && ! empty( $archives_tmpl_sale ) ) ) {
			$is_using_archive_tmpl    = true;
			$currency                 = get_woocommerce_currency();
			$starting_price_formatted = uni_cpo_price( $starting_price );
			$template                 = $product->is_on_sale() && ! empty( $archives_tmpl_sale )
				? $archives_tmpl_sale
				: $archives_tmpl;

			$formatted = str_replace( '{{{CURRENCY_SIGN}}}', get_woocommerce_currency_symbol( $currency ), $template );
			$formatted = str_replace( '{{{REGULAR_PRICE}}}', $display_regular_price, $formatted );
			$formatted = str_replace( '{{{SALE_PRICE}}}', $display_sale_price, $formatted );
			$formatted = str_replace( '{{{STARTING_PRICE}}}', $starting_price_formatted, $formatted );

			// check for NOVs
			if ( is_array( $product_data['nov_data']['nov'] ) && ! empty( $product_data['nov_data']['nov'] ) ) {
				foreach ( $product_data['nov_data']['nov'] as $nov ) {
					$var_name = '{{{' . UniCpo()->get_nov_slug() . $nov['slug'] . '}}}';
					$formula  = 0;
					if ( isset( $nov['roles'] ) && 'on' === $product_data['nov_data']['wholesale_enable']
					     && ( ! isset( $nov['matrix']['enable'] ) || 'on' !== $nov['matrix']['enable'] ) ) {
						$formula = uni_cpo_get_role_based_nov_formula( $nov );
					} elseif ( isset( $nov['matrix']['enable'] ) && 'on' === $nov['matrix']['enable'] ) {
						// no support for matrices here
						$formula = 0;
					} else {
						$formula = ( isset( $nov['formula'] ) ) ? $nov['formula'] : 0;
					}

					$formula   = uni_cpo_process_formula_with_vars( $formula, array() );
					$nov_val   = uni_cpo_aelia_price_convert( uni_cpo_calculate_formula( $formula ) );
					$nov_val   = uni_cpo_price( $nov_val );
					$formatted = str_replace( $var_name, $nov_val, $formatted );
				}
			}

			$price = $formatted;
		} else {
			$price = uni_cpo_price( $price );
		}
	} else {
		$starting_price = ( ! empty( $product_data['settings_data']['min_price'] ) ) ? floatval( $product_data['settings_data']['min_price'] ) : $price;
		$starting_price = apply_filters( 'uni_cpo_price_starting_archive', $starting_price, $product );
		$price          = uni_cpo_price( $starting_price );
	}

	if ( $product->is_taxable() && $starting_price && ! ( $is_using_archive_tmpl && $product->is_on_sale() ) ) {
		$tax_suffix = $product->get_price_suffix( $starting_price );
		$price      = $price . $tax_suffix;
	}

	return $price;
}

//
function uni_cpo_get_price_for_meta() {

	global $product;
	$product_id   = intval( $product->get_id() );
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

	if ( 'on' === $product_data['settings_data']['cpo_enable'] && 'on' === $product_data['settings_data']['calc_enable'] ) {

		if ( unicpo_fs()->is__premium_only() ) {
			$starting_price = ( ! empty( $product_data['settings_data']['starting_price'] ) )
				? floatval( $product_data['settings_data']['starting_price'] )
				: ( ( ! empty( $product_data['settings_data']['min_price'] ) )
					? floatval( $product_data['settings_data']['min_price'] )
					: 0 );
		} else {
			$starting_price = ( ! empty( $product_data['settings_data']['min_price'] ) )
				? floatval( $product_data['settings_data']['min_price'] )
				: 0;
		}

		if ( 0 === $starting_price ) {
			$price = apply_filters( 'uni_cpo_display_price_meta_tag', $starting_price, $product );
			$price = wc_get_price_to_display( $product, array( 'price' => $price ) );
		} else {
			$price = wc_get_price_to_display( $product );
		}

		return $price;
	} else {
		return wc_get_price_to_display( $product );
	}

}

//
add_action( 'woocommerce_single_product_summary', 'uni_cpo_display_price_custom_meta', 11 );
function uni_cpo_display_price_custom_meta() {
	global $product;
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product->get_id() );

	if ( 'on' === $product_data['settings_data']['cpo_enable'] && 'on' === $product_data['settings_data']['calc_enable']
	     && ( ! empty( $product_data['settings_data']['min_price'] ) || ! empty( $product_data['settings_data']['starting_price'] ) )
	) {

		$price = uni_cpo_get_price_for_meta();

		echo '<meta itemprop="minPrice" content="' . esc_attr( $price ) . '" itemtype="http://schema.org/PriceSpecification" />';
	}
}

//
function uni_cpo_get_display_price_reversed( $product, $price ) {
	$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
	$price_incl       = wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $price ) );
	$price_excl       = wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $price ) );
	$display_price    = $tax_display_mode == 'incl' ? $price_excl : $price_incl;

	return $display_price;
}

// displays a new and discounted price in the cart
function uni_cpo_change_cart_item_price( $price, $cart_item ) {
	$product_id   = $cart_item['product_id'];
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

	if ( 'on' === $product_data['settings_data']['cpo_enable']
	     && 'on' === $product_data['settings_data']['calc_enable']
	) {

		$product    = wc_get_product( $product_id );
		$price_calc = wc_get_price_to_display( $product, array(
			'qty'   => 1,
			'price' => $cart_item['_cpo_price']
		) );

		$cpo_price = apply_filters( 'uni_cpo_get_cart_price_calculated_raw', $price_calc, $product_data );
		$cpo_price = wc_price( $cpo_price );

		return $cpo_price;
	} else {
		return $price;
	}
}

//
add_action( 'woocommerce_before_calculate_totals', 'uni_cpo_before_calculate_totals', 10, 1 );
function uni_cpo_before_calculate_totals( $object ) {
	if ( method_exists( $object, 'get_cart' ) ) {
		foreach ( $object->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];

			if ( $product->is_type( 'simple' ) && ! empty( $object->coupons ) ) {
				foreach ( $object->coupons as $code => $coupon ) {
					if ( $coupon->is_valid()
					     && (
						     $coupon->is_valid_for_product( $product, $values )
						     || $coupon->is_valid_for_cart()
					     )
					) {
						if ( isset( $values['_cpo_price'] ) ) {
							$product->set_price( $values['_cpo_price'] );
						}
					}
				}
			}

		}
	}
}

// associate with order's meta
add_filter( 'woocommerce_add_cart_item_data', 'uni_cpo_add_cart_item_data', 10, 2 );
if ( unicpo_fs()->is__premium_only() ) {
	add_filter( 'woocommerce_add_to_cart_sold_individually_quantity', 'uni_cpo_woocommerce_add_to_cart_sold_individually_quantity', 10, 5 );
	add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', 'uni_cpo_woocommerce_add_to_cart_sold_individually_found_in_cart', 11, 5 );
	add_filter( 'woocommerce_add_to_cart', 'uni_cpo_woocommerce_add_to_cart', 10, 6 );
}
add_filter( 'woocommerce_get_cart_item_from_session', 'uni_cpo_get_cart_item_from_session', 10, 3 );
add_filter( 'woocommerce_add_cart_item', 'uni_cpo_add_cart_item', 10, 1 );
// sets uni cpo's price after all plugins ;)
add_action( 'woocommerce_cart_loaded_from_session', 'uni_cpo_re_calculate_price', 99, 1 );
// get item data to display in cart and checkout page
add_filter( 'woocommerce_get_item_data', 'uni_cpo_get_item_data', 10, 2 );
// add meta data for each order item
add_action( 'woocommerce_checkout_create_order_line_item', 'uni_cpo_checkout_create_order_line_item', 10, 4 );

// adds custom option data to the cart
function uni_cpo_add_cart_item_data( $cart_item_data, $product_id ) {
	$product = wc_get_product($product_id);

    if ('simple' !== $product->get_type()) {
        return $cart_item_data;
    }

	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

	if (
		isset( $_GET['add-to-cart'] )
		&& isset( $product_data['settings_data']['cpo_enable'] )
		&& 'on' === $product_data['settings_data']['cpo_enable']
	) {
		throw new Exception( __( 'Sorry, this product cannot be added to the cart in this way', 'uni-cpo' ) );
	}

	try {
		// YITH bundle products compatibility start
		if ( 'on' !== $product_data['settings_data']['cpo_enable'] ) {
			$product                  = wc_get_product( $product_id );
			$add_cart_item_data_check = $product->is_type( 'yith_bundle' ) && ( ! isset( $cart_item_data['cartstamp'] ) || ! isset( $cart_item_data['bundled_items'] ) );
			$add_cart_item_data_check = apply_filters( 'yith_wcpb_add_cart_item_data_check', $add_cart_item_data_check, $cart_item_data, $product_id );

			if ( ! $add_cart_item_data_check ) {
				return $cart_item_data;
			}
		}
		// YITH bundle products compatibility end

		$qty_field_slug = $product_data['settings_data']['qty_field'];
		if ( unicpo_fs()->is__premium_only() ) {
			if ( 'wc' !== $qty_field_slug && isset( $cart_item_data['_cpo_data'] ) && ! empty( absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] ) ) ) {
				$cart_item_data['quantity'] = absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] );
			}
		}

		if ( isset( $_POST['action'] ) && $_POST['action'] === 'uni_cpo_add_to_cart' ) {
			$form_data = wc_clean( $_POST['data'] );
		} elseif ( isset( $cart_item_data['cpo_data'] ) ) { // duplicating or ordering again
			$form_data = $cart_item_data;
		} else {
			$form_data = wc_clean( $_POST );
		}

		if ( 'on' === $product_data['settings_data']['cpo_enable'] ) {

			$cart_item_data['_cpo_calc_option']   = ( 'on' === $product_data['settings_data']['calc_enable'] )
				? true : false;
			$cart_item_data['_cpo_cart_item_id']  = ( ! empty( $form_data['cpo_cart_item_id'] ) )
				? $form_data['cpo_cart_item_id']
				: '';
			$cart_item_data['_cpo_product_image'] = ( ! empty( $form_data['cpo_product_image'] ) )
				? $form_data['cpo_product_image']
				: '';

			if ( ! empty( $form_data['cpo_product_layered_image'] ) ) {
				$cart_item_data['_cpo_product_image'] = uni_cpo_upload_base64_image(
					$form_data['cpo_product_layered_image'],
					'product_' . $form_data['cpo_product_id'] . '_image_' . time()
				);
			}

			if ( unicpo_fs()->is__premium_only() ) {
				if ( isset( $form_data['nbdFinalPrice'] ) ) {
					$cart_item_data['nbo_meta']['price'] = $form_data['nbdFinalPrice'];
				}
			}

			$cart_item_data['_cpo_data'] = isset( $form_data['cpo_data'] )
				? $form_data['cpo_data']
				: $form_data;

			// values to be unset
			$unset_values = apply_filters(
				'uni_cpo_add_to_cart_values_to_be_unset',
				array(
					'cpo_cart_item_id',
					'cpo_product_id',
					'add-to-cart',
					'cpo_data', // without slash
					'cpo_nov', // without slash
					'cpo_product_image',
					'cpo_product_layered_image',
					'quantity'
				),
				$cart_item_data,
				$product_id
			);

			if ( ! empty( $unset_values ) ) {
				foreach ( $unset_values as $v ) {
					if ( isset( $form_data[ $v ] ) ) {
						unset( $form_data[ $v ] );
					}
					if ( isset( $cart_item_data[ $v ] ) ) {
						unset( $cart_item_data[ $v ] );
					}
				}
			}

			if ( true === boolval( $cart_item_data['_cpo_calc_option'] ) ) {

				if ( ! empty( $cart_item_data['_cpo_data'] ) ) {
					$posts = uni_cpo_get_posts_by_slugs( array_keys( $cart_item_data['_cpo_data'] ) );
					if ( ! empty( $posts ) ) {
						$posts_ids = wp_list_pluck( $posts, 'ID' );
						foreach ( $posts_ids as $post_id ) {
							$option = uni_cpo_get_option( $post_id );
							if ( is_object( $option ) ) {
								if ( 'extra_cart_button' === $option->get_type() ) {
									$cart_item_data['_cpo_is_free_sample'] = $option->calculate( $cart_item_data['_cpo_data'] );
									$post_name                             = trim( $option->get_slug(), '{}' );
									unset( $cart_item_data['_cpo_data'][ $post_name ] );
									continue;
								}
							}
						}
					}
				}

				$price = uni_cpo_calculate_price_in_cart( $cart_item_data, $product_id );
			} else {
				$product = wc_get_product( $product_id );
				$price   = $product->get_price();
			}

			$price                        = wc_format_decimal( $price );
			$cart_item_data['_cpo_price'] = $price;

		}

		return $cart_item_data;

	} catch ( Exception $e ) {
		if ( $e->getMessage() ) {
			wc_add_notice( $e->getMessage(), 'error' );
		}

		return false;
	}
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	function uni_cpo_woocommerce_add_to_cart_sold_individually_quantity( $sold_individually_qty, $quantity, $product_id, $variation_id, $cart_item_data ) {
		$product_data   = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$qty_field_slug = $product_data['settings_data']['qty_field'];

		if ( 'wc' !== $qty_field_slug && isset( $cart_item_data['_cpo_data'][ $qty_field_slug ] )
		     && ! empty( absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] ) ) ) {
			return absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] );
		}

		return $sold_individually_qty;
	}

	//
	function uni_cpo_woocommerce_add_to_cart_sold_individually_found_in_cart( $is_found, $product_id, $variation_id, $cart_item_data, $cart_id ) {
		$product_data         = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$qty_field_slug       = $product_data['settings_data']['qty_field'];
		$is_sold_individually = $product_data['settings_data']['sold_individually'];

		if ( 'on' === $is_sold_individually ) {
			$is_found = uni_cpo_find_product_in_cart( $product_id );
		}

		if ( ! $is_found && 'wc' !== $qty_field_slug && isset( $cart_item_data['_cpo_data'][ $qty_field_slug ] )
		     && ! empty( absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] ) ) ) {
			return false;
		}

		if ( 'off' === $is_sold_individually && class_exists( 'WCML_Cart' ) ) {
			$is_found = false;
		}

		return $is_found;
	}

	function uni_cpo_find_product_in_cart( $product_id = false, $count_samples = false, $limit = false, $new_item_qty = 0 ) {
		if ( false !== $product_id ) {
			$count = 0;
			if ( ! $count_samples ) {
				if ( is_array( WC()->cart->cart_contents ) ) {
					foreach ( WC()->cart->cart_contents as $cart_item ) {
						if ( $product_id === $cart_item['product_id'] && $cart_item['quantity'] > 0 ) {
							$count += $cart_item['quantity'];
						}
					}
				}

				return $count > 0;
			} else {
				if ( is_array( WC()->cart->cart_contents ) ) {
					foreach ( WC()->cart->cart_contents as $cart_item ) {
						if ( isset( $cart_item['_cpo_is_free_sample'] ) ) {
							$count += $cart_item['quantity'];
						}
					}
				}

				if ( 0 !== $limit && $count > absint( $limit ) ) {
					return true;
				}

				return false;
			}
		}

		return false;
	}

	//
	function uni_cpo_woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$product_data           = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$qty_field_slug         = $product_data['settings_data']['qty_field'];
		$plugin_settings        = UniCpo()->get_settings();
		$is_free_sample_enabled = $plugin_settings['free_sample_enable'];
		$free_samples_limit     = ( isset( $plugin_settings['free_samples_limit'] ) )
			? absint( $plugin_settings['free_samples_limit'] )
			: 0;
		$qty                    = 'wc' !== $qty_field_slug && isset( $cart_item_data['_cpo_data'][ $qty_field_slug ] )
		                          && ! empty( absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] ) )
			? absint( $cart_item_data['_cpo_data'][ $qty_field_slug ] )
			: WC()->cart->cart_contents[ $cart_item_key ]['quantity'];

		if ( isset( $cart_item_data['_cpo_is_free_sample'] ) && ! empty( $is_free_sample_enabled ) && 'on' === $is_free_sample_enabled ) {
			$is_found = uni_cpo_find_product_in_cart( $product_id, true, $free_samples_limit );

			if ( $is_found ) {
				unset( WC()->cart->cart_contents[ $cart_item_key ] );
				throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View cart', 'uni-cpo' ), sprintf( __( 'The total number of samples in the cart is limited to %d', 'uni-cpo' ), $free_samples_limit ) ) );
			}
		}
	}
}

//
function uni_cpo_get_cart_item_from_session( $session_data, $values, $key ) {
    $product = wc_get_product($values['product_id']);

	$session_data['_cpo_calc_option']   = 'simple' === $product->get_type() && ( isset( $values['_cpo_calc_option'] ) )
		? boolval( $values['_cpo_calc_option'] )
		: false;
	$session_data['_cpo_cart_item_id']  = ( isset( $values['_cpo_cart_item_id'] ) )
		? $values['_cpo_cart_item_id']
		: '';
	$session_data['_cpo_product_image'] = ( isset( $values['_cpo_product_image'] ) )
		? $values['_cpo_product_image']
		: '';
	$session_data['_cpo_data']          = ( isset( $values['_cpo_data'] ) )
		? $values['_cpo_data']
		: '';

	if ( isset( $session_data['_cpo_data'] ) ) {
		return uni_cpo_add_cart_item( $session_data );
	} else {
		return $session_data;
	}
}

function uni_cpo_add_cart_item( $cart_item_data ) {

	$product_id      = $cart_item_data['product_id'];
	$is_calc_enabled = ( isset( $cart_item_data['_cpo_calc_option'] ) )
		? boolval( $cart_item_data['_cpo_calc_option'] )
		: false;

	// price calc
	if ( true === $is_calc_enabled && isset( $cart_item_data['_cpo_data'] ) ) {
		$price                        = uni_cpo_calculate_price_in_cart( $cart_item_data, $product_id );
		$price                        = wc_format_decimal( $price );
		$cart_item_data['_cpo_price'] = $price;
		$cart_item_data['data']->set_price( $cart_item_data['_cpo_price'] );
	}

	return $cart_item_data;
}

function uni_cpo_re_calculate_price( $cart ) {
	foreach ( $cart->cart_contents as $cart_item_key => $cart_item_data ) {
		if ( isset( $cart_item_data['_cpo_price'] ) ) {
			WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $cart_item_data['_cpo_price'] );
		}
	}
}

//
function uni_cpo_get_item_data( $item_data, $cart_item ) {
	if ( ! empty( $cart_item['_cpo_data'] ) ) {

		// saves an information about chosen options and their values in cart meta
		$form_data      = $cart_item['_cpo_data'];
		$formatted_vars = array();
		$variables      = array();

		$filtered_form_data = array_filter(
			$form_data,
			function ( $k ) use ( $form_data ) {
				return false !== strpos( $k, UniCpo()->get_var_slug() ) && ! empty( $form_data[ $k ] );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( unicpo_fs()->is__premium_only() ) {
			// exclude option chosen as qty field
			$options_eval_result = [];
			$product_id          = $cart_item['product_id'];
			$product_data        = Uni_Cpo_Product::get_product_data_by_id( $product_id );
			$qty_field_slug      = $product_data['settings_data']['qty_field'];

			if ( 'wc' !== $qty_field_slug && isset( $filtered_form_data[ $qty_field_slug ] )
			     && ! empty( absint( $filtered_form_data[ $qty_field_slug ] ) ) ) {
				unset( $filtered_form_data[ $qty_field_slug ] );
			}

			if ( ! empty( $filtered_form_data ) ) {
				$posts = uni_cpo_get_posts_by_slugs( array_keys( $filtered_form_data ) );
				if ( ! empty( $posts ) ) {
					$posts_ids = wp_list_pluck( $posts, 'ID' );
					foreach ( $posts_ids as $post_id ) {
						$option = uni_cpo_get_option( $post_id );
						if ( is_object( $option ) ) {
							$calculate_result = $option->calculate( $filtered_form_data );

							if ( 'extra_cart_button' === $option->get_type() ) {
								$is_free_sample = $calculate_result;
								continue;
							}

							if ( ! empty( $calculate_result ) ) {
								foreach ( $calculate_result as $k => $v ) {
									$options_eval_result[ $option->get_slug() ] = $calculate_result;
								}
							}
						}
					}
				}
			}

			array_walk(
				$options_eval_result,
				function ( $v ) use ( &$variables, &$formatted_vars, &$nice_names_vars ) {
					foreach ( $v as $slug => $value ) {
						// prepare $variables for calculation purpose
						$variables[ '{' . $slug . '}' ] = $value['calc'];
						// prepare $formatted_vars for conditional logic purpose
						$formatted_vars[ $slug ] = $value['cart_meta'];
					}
				}
			);

			// non option variables
			if ( 'on' === $product_data['nov_data']['nov_enable']
			     && ! empty( $product_data['nov_data']['nov'] )
			) {
				$variables = uni_cpo_process_formula_with_non_option_vars( $variables, $product_data, $formatted_vars );
			}
		}

		if ( ! empty( $filtered_form_data ) ) {
			$posts = uni_cpo_get_posts_by_slugs( array_keys( $filtered_form_data ) );
			if ( ! empty( $posts ) ) {
				$posts_ids = wp_list_pluck( $posts, 'ID' );
				foreach ( $posts_ids as $post_id ) {
					$option = uni_cpo_get_option( $post_id );
					if ( is_object( $option ) ) {
						$post_name   = trim( $option->get_slug(), '{}' );
						$display_key = uni_cpo_sanitize_label( $option->cpo_order_label() );

						if ( unicpo_fs()->is__premium_only() ) {
							if ( $option->cpo_order_visibility() ) {
								continue;
							}
						}

						$calculate_result = $option->calculate( $filtered_form_data );
						if ( is_array( $calculate_result ) ) {
							foreach ( $calculate_result as $k => $v ) {
								if ( $post_name === $k ) { // excluding special vars
									if ( is_array( $v['cart_meta'] ) ) {
										$value = implode( ', ', $v['cart_meta'] );
									} else {
										$value = $v['cart_meta'];
									}

									if ( is_array( $v['order_meta'] ) ) {
										$v['order_meta'] = array_map(
											function ( $item ) {
												if ( ! is_numeric( $item ) ) {
													return esc_html__( $item );
												} else {
													return $item;
												}
											},
											$v['order_meta']
										);
										$display_value   = implode( ', ', $v['order_meta'] );
									} else {
										if ( ! is_numeric( $v['order_meta'] ) ) {
											$display_value = esc_html__( $v['order_meta'] );
										} else {
											$display_value = $v['order_meta'];
										}
									}

									if ( unicpo_fs()->is__premium_only() ) {
										$display_value = uni_cpo_replace_curly(
											$display_value,
											$formatted_vars,
											$product_data,
											$variables
										);
										$display_value = uni_cpo_get_proper_option_label_cart( $display_value );
										$display_key   = uni_cpo_replace_curly(
											$display_key,
											$formatted_vars,
											$product_data,
											$variables
										);
										$display_key   = trim( $display_key, ' ' );
										$display_key   = uni_cpo_get_proper_option_label_cart( $display_key );
									}

									$item_data[] = array(
										'name'    => $option->get_slug(),
										'key'     => esc_html__( $display_key ),
										'value'   => $value,
										'display' => $display_value
									);
									break;
								}
							}
						}
					}
				}
			}
		}

		if ( unicpo_fs()->is__premium_only() ) {
			if ( isset( $cart_item['_cpo_nov'] ) ) {
				foreach ( $cart_item['_cpo_nov'] as $k => $v ) {
					$item_data[] = array(
						'name'    => $k,
						'key'     => esc_html__( $v['display_name'] ),
						'value'   => $v['value'],
						'display' => $v['value']
					);
				}
			}
		}

	}

	return $item_data;
}

// adds meta info for order items
function uni_cpo_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['_cpo_data'] ) ) {
		$form_data = $values['_cpo_data'];

		foreach ( $form_data as $name => $value ) {
			$item->add_meta_data( '_' . $name, $value );
		}

		if ( unicpo_fs()->is__premium_only() ) {
			if ( isset( $values['_cpo_nov'] ) && is_array( $values['_cpo_nov'] ) ) {
				foreach ( $values['_cpo_nov'] as $k => $v ) {
					$item->add_meta_data( '_' . $k, $v['value'] );
				}
			}
		}

		if ( unicpo_fs()->is__premium_only() ) {
			$physical_attrs = array( 'weight', 'width', 'length', 'height' );
			foreach ( $physical_attrs as $attr ) {
				$item->add_meta_data( "_uni_item_{$attr}", $values['data']->{"get_$attr"}() );
			}
		}

		if ( unicpo_fs()->is__premium_only() ) {
			if ( isset( $values['_cpo_is_free_sample'] ) ) {
				$item->add_meta_data( '_cpo_is_free_sample', $values['_cpo_is_free_sample'] );
			}
		}
	}

	$additional_data = apply_filters( 'uni_cpo_additional_item_data', array(), $item, $cart_item_key, $values );

	if ( ! empty( $values['_cpo_product_image'] ) ) {
		$additional_data = $additional_data + array( '_uni_custom_item_image' => $values['_cpo_product_image'] );
	}

	if ( ! empty( $additional_data ) && is_array( $additional_data ) ) {
		foreach ( $additional_data as $k => $v ) {
			$item->add_meta_data( $k, $v );
		}
	}
}

//
function uni_cpo_calculate_price_in_cart( &$cart_item_data, $product_id ) {
	try {
		$product             = wc_get_product( $product_id );
		$product_data        = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$form_data           = $cart_item_data['_cpo_data'];
		$options_eval_result = array();
		$variables           = array();
		$is_calc_disabled    = false;
		$formatted_vars      = array();
		$is_free_sample      = ( isset( $cart_item_data['_cpo_is_free_sample'] ) )
			? $cart_item_data['_cpo_is_free_sample']
			: false;

		$main_formula = $product_data['formula_data']['main_formula'];

		if ( unicpo_fs()->is__premium_only() ) {
			$is_calc_weight     = ( 'on' === $product_data['weight_data']['weight_enable'] )
				? true : false;
			$is_calc_dimensions = ( 'on' === $product_data['dimensions_data']['dimensions_enable'] )
				? true : false;
		}

		$filtered_form_data = array_filter(
			$form_data,
			function ( $k ) use ( $form_data ) {
				return false !== strpos( $k, UniCpo()->get_var_slug() ) && ! empty( $form_data[ $k ] );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( ! empty( $filtered_form_data ) ) {
			$posts = uni_cpo_get_posts_by_slugs( array_keys( $filtered_form_data ) );
			if ( ! empty( $posts ) ) {
				$posts_ids = wp_list_pluck( $posts, 'ID' );
				foreach ( $posts_ids as $post_id ) {
					$option = uni_cpo_get_option( $post_id );
					if ( is_object( $option ) ) {
						$calculate_result = $option->calculate( $filtered_form_data );
						if ( ! empty( $calculate_result ) ) {
							$options_eval_result[ $option->get_slug() ] = $calculate_result;
						}
					}
				}
			}
		}

		array_walk(
			$options_eval_result,
			function ( $v ) use ( &$variables, &$formatted_vars ) {
				foreach ( $v as $slug => $value ) {
					// prepare $variables for calculation purpose
					$variables[ '{' . $slug . '}' ] = $value['calc'];
					// prepare $formatted_vars for conditional logic purpose
					$formatted_vars[ $slug ] = $value['cart_meta'];
				}
			}
		);

		$variables['{uni_cpo_price}'] = $product->get_price( 'edit' );

		if ( unicpo_fs()->is__premium_only() ) {
			if ( isset( $cart_item_data['nbo_meta']['price'] ) ) {
				$variables['{uni_cpo_price}'] = $cart_item_data['nbo_meta']['price'];
			}
		}

		// non option variables
		if ( 'on' === $product_data['nov_data']['nov_enable']
		     && ! empty( $product_data['nov_data']['nov'] )
		) {
			$variables = uni_cpo_process_formula_with_non_option_vars( $variables, $product_data, $formatted_vars );
		}

		if ( unicpo_fs()->is__premium_only() ) {
			$novs_nice = uni_cpo_filter_novs( $variables );

			$nov_data = $product_data['nov_data']['nov'];
			$cpo_nov  = array();

			if ( ! empty( $nov_data ) && isset( $novs_nice ) ) {
				array_walk(
					$nov_data,
					function ( $nov ) use ( &$cpo_nov, $novs_nice ) {
						$nov_slug = UniCpo()->get_nov_slug() . $nov['slug'];
						if ( isset( $nov['cart_display'] ) && 'on' === $nov['cart_display']['enable']
						     && isset( $novs_nice[ $nov_slug ] ) ) {
							$cpo_nov[ $nov_slug ] = array(
								'display_name' => $nov['cart_display']['name'],
								'value'        => $novs_nice[ $nov_slug ]
							);
						}
					}
				);
			}

			$cart_item_data['_cpo_nov'] = $cpo_nov;
		}

		// formula conditional logic
		if ( 'on' === $product_data['formula_data']['rules_enable']
		     && ! empty( $product_data['formula_data']['formula_scheme'] )
		     && is_array( $product_data['formula_data']['formula_scheme'] )
		) {

			$conditional_formula = uni_cpo_process_formula_scheme( $formatted_vars, $product_data );
			if ( $conditional_formula ) {
				$main_formula = $conditional_formula;
			}

		}

		if ( 'disable' === $main_formula || 0 === $is_free_sample ) {
			$is_calc_disabled = true;
		}
		//
		if ( ! $is_calc_disabled ) {

			if ( unicpo_fs()->is__premium_only() ) {
				$main_formula = apply_filters(
					'uni_cpo_in_cart_formula_before_formula_eval',
					$main_formula,
					$product_data,
					$product_id
				);

				$variables = apply_filters(
					'uni_cpo_in_cart_variables_before_formula_eval',
					$variables,
					$product_data,
					$product_id
				);
			}

			$main_formula = uni_cpo_process_formula_with_vars( $main_formula, $variables );

			// calculates formula
			$price_calculated = uni_cpo_calculate_formula( $main_formula );

			$price_min = $product_data['settings_data']['min_price'];
			$price_max = $product_data['settings_data']['max_price'];

			// check for min price
			if ( $price_calculated < $price_min ) {
				$price_calculated = $price_min;
			}

			if ( unicpo_fs()->is__premium_only() ) {
				// cart discounts
				$price_calculated = uni_cpo_apply_cart_discounts( $price_calculated, $product_data, $variables, $cart_item_data );
			}

			// check for max price
			if ( ! empty( $price_max ) && $price_calculated >= $price_max ) {
				$is_calc_disabled = true;
			}

			if ( unicpo_fs()->is__premium_only() ) {
				// weight calculation
				if ( $is_calc_weight ) {

					$main_weight_formula = $product_data['weight_data']['main_weight_formula'];
					// cart discounts rules
					if ( 'on' === $product_data['weight_data']['weight_rules_enable']
					     && ! empty( $product_data['weight_data']['weight_scheme'] )
					     && is_array( $product_data['weight_data']['weight_scheme'] )
					) {

						$weight_conditional_formula = uni_cpo_process_formula_scheme( $formatted_vars, $product_data, 'weight' );
						if ( $weight_conditional_formula ) {
							$main_weight_formula = $weight_conditional_formula;
						}

					}

					if ( ! empty( $main_weight_formula ) ) {
						$main_weight_formula = uni_cpo_process_formula_with_vars( $main_weight_formula, $variables );
						$weight              = uni_cpo_calculate_formula( $main_weight_formula );
						if ( isset( $cart_item_data['data'] ) && is_a( $cart_item_data['data'], 'WC_Product_Simple' ) ) {

							// filter, so 3rd party scripts can hook up
							$weight = apply_filters(
								'uni_cpo_in_cart_calculated_weight',
								$weight,
								$product,
								$filtered_form_data
							);

							$cart_item_data['data']->set_weight( $weight );
						}
					}

				}

				// dimensions calculation
				if ( $is_calc_dimensions && isset( $cart_item_data['data'] ) && is_a( $cart_item_data['data'], 'WC_Product_Simple' ) ) {

					$d_unit_option   = $product_data['dimensions_data']['d_unit_option'];
					$d_length_option = $product_data['dimensions_data']['d_length_option'];
					$d_width_option  = $product_data['dimensions_data']['d_width_option'];
					$d_height_option = $product_data['dimensions_data']['d_height_option'];
					$to_unit         = strtolower( get_option( 'woocommerce_dimension_unit' ) );
					$from_unit       = '';

					if ( ! empty( $d_unit_option ) && ! empty( $filtered_form_data[ $d_unit_option ] ) ) {
						$from_unit = $filtered_form_data[ $d_unit_option ];
					}

					if ( ! empty( $d_length_option ) && ! empty( $variables[ '{' . $d_length_option . '}' ] ) ) {
						$length            = $variables[ '{' . $d_length_option . '}' ];
						$is_convert_length = ( 'on' === $product_data['dimensions_data']['convert_length'] )
							? true : false;
						if ( $is_convert_length && ! empty( $from_unit ) ) {
							$length = uni_cpo_get_dimension( $length, $from_unit, $to_unit );
						}
						// filter, so 3rd party scripts can hook up
						$length = apply_filters(
							'uni_cpo_in_cart_calculated_length',
							$length,
							$product,
							$variables
						);
						$cart_item_data['data']->set_length( $length );
					}

					if ( ! empty( $d_width_option ) && ! empty( $variables[ '{' . $d_width_option . '}' ] ) ) {
						$width            = $variables[ '{' . $d_width_option . '}' ];
						$is_convert_width = ( 'on' === $product_data['dimensions_data']['convert_width'] )
							? true : false;
						if ( $is_convert_width && ! empty( $from_unit ) ) {
							$width = uni_cpo_get_dimension( $width, $from_unit, $to_unit );
						}
						// filter, so 3rd party scripts can hook up
						$width = apply_filters(
							'uni_cpo_in_cart_calculated_width',
							$width,
							$product,
							$variables
						);
						$cart_item_data['data']->set_width( $width );
					}

					if ( ! empty( $d_height_option ) && ! empty( $variables[ '{' . $d_height_option . '}' ] ) ) {
						$height            = $variables[ '{' . $d_height_option . '}' ];
						$is_convert_height = ( 'on' === $product_data['dimensions_data']['convert_height'] )
							? true : false;
						if ( $is_convert_height && ! empty( $from_unit ) ) {
							$height = uni_cpo_get_dimension( $height, $from_unit, $to_unit );
						}
						// filter, so 3rd party scripts can hook up
						$height = apply_filters(
							'uni_cpo_in_cart_calculated_height',
							$height,
							$product,
							$variables
						);
						$cart_item_data['data']->set_height( $height );
					}

				}
			}

			if ( true !== $is_calc_disabled ) {
				// filter, so 3rd party scripts can hook up
				$price_calculated = apply_filters(
					'uni_cpo_in_cart_calculated_price',
					$price_calculated,
					$product,
					$filtered_form_data
				);

				return $price_calculated;
			} else {
				return $price_max;
			}
		} else {
			return 0;
		}

	} catch ( Exception $e ) {
		return new WP_Error( 'cart-error', $e->getMessage() );
	}
}

if ( unicpo_fs()->is__premium_only() ) {
	// update weight of the cart content
	add_action( 'woocommerce_checkout_update_order_meta', 'uni_cpo_save_cart_weight_in_order_meta' );
	function uni_cpo_save_cart_weight_in_order_meta( $order_id ) {
		$weight = WC()->cart->cart_contents_weight;
		update_post_meta( $order_id, '_cpo_cart_weight', $weight );
	}
}

//
add_filter( 'woocommerce_order_again_cart_item_data', 'uni_cpo_woocommerce_order_again_cart_item_data', 10, 3 );
function uni_cpo_woocommerce_order_again_cart_item_data( $cart_item_meta, $item, $order ) {
	$product_id   = $item->get_product_id();
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

	return uni_cpo_re_add_cpo_item_data( $cart_item_meta, $item->get_meta_data(), $product_data );
}

//
function uni_cpo_re_add_cpo_item_data( $item_data, $raw_data, $product_data, $is_duplicate = false ) {
	if ( 'on' === $product_data['settings_data']['cpo_enable'] ) {

		$item_data['_cpo_calc_option']  = ( 'on' === $product_data['settings_data']['calc_enable'] )
			? true : false;
		$item_data['cpo_cart_item_id']  = current_time( 'timestamp' );
		$item_data['cpo_product_image'] = isset( $raw_data['_cpo_product_image'] )
			? $raw_data['_cpo_product_image']
			: '';
		unset( $item_data['cpo_price'] );

		if ( is_array( $raw_data ) ) {
			foreach ( $raw_data as $k => $v ) {
				if ( is_array( $v ) ) {
					if ( false !== strpos( $k, '_cpo' ) ) {
						$meta_key_new = ltrim( $k, '_' );
						if ( false !== strpos( $k, 'uni_cpo' ) ) {
							$item_data['_cpo_data'][ $meta_key_new ] = $v;
						} else {
							$item_data[ $meta_key_new ] = $v;
						}

					}
				} elseif ( is_a( $v, 'WC_Meta_Data' ) ) {
					$meta_data = $v->get_data();
					if ( false !== strpos( $meta_data['key'], '_cpo' ) ) {
						$meta_key_new = ltrim( $meta_data['key'], '_' );
						if ( false !== strpos( $meta_data['key'], 'uni_cpo' ) ) {
							$item_data['_cpo_data'][ $meta_key_new ] = $meta_data['value'];
						} else {
							$item_data[ $meta_key_new ] = $meta_data['value'];
						}

					}
					if ( '_uni_custom_item_image' === $meta_data['key'] ) {
						$item_data['cpo_product_image'] = $meta_data['value'];
					}
				}
			}
		}

	}

	return $item_data;
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	add_action( 'wp_loaded', 'uni_cpo_update_cart', 20 );
	function uni_cpo_update_cart() {

		if ( isset( $_REQUEST['page_id'] )
		     && false !== strpos( $_REQUEST['page_id'], 'cpo_duplicate_cart_item' )
		) { // not pretty permalinks?
			$request_params = explode( '?', $_REQUEST['page_id'] );
			foreach ( $request_params as $param ) {
				if ( false !== strpos( $param, 'cpo_duplicate_cart_item' ) ) {
					$cart_item_id_arr = explode( '=', $_REQUEST['page_id'] );
					if ( isset( $cart_item_id_arr[1] ) ) {
						$_REQUEST['cpo_duplicate_cart_item'] = $cart_item_id_arr[1];
					}
				}
			}
		}

		if ( ! isset( $_REQUEST['cpo_duplicate_cart_item'] ) ) {
			return;
		}

		wc_nocache_headers();

		if ( ! empty( $_REQUEST['cpo_duplicate_cart_item'] ) && wp_verify_nonce( wc_get_var( $_REQUEST['_nonce'] ), 'woocommerce-cart' ) ) {
			$cart_item_key   = sanitize_text_field( $_REQUEST['cpo_duplicate_cart_item'] );
			$cart_content    = WC()->cart->get_cart();
			$duplicated_item = $cart_content[ $cart_item_key ];
			$product_data    = Uni_Cpo_Product::get_product_data_by_id( $duplicated_item['product_id'] );
			$cart_item_data  = uni_cpo_re_add_cpo_item_data( array(), $duplicated_item, $product_data );
			$cart_item_key   = WC()->cart->add_to_cart( $duplicated_item['product_id'], $duplicated_item['quantity'], 0, array(), $cart_item_data );

			if ( $cart_item_key ) {

				$product = wc_get_product( $duplicated_item['product_id'] );

				$item_duplicated_title = apply_filters( 'uni_cpo_cart_item_duplicated_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'uni-cpo' ), $product->get_name() ) : __( 'Item', 'uni-cpo' ), $duplicated_item );

				$duplicated_notice = sprintf( __( '%s duplicated.', 'uni-cpo' ), $item_duplicated_title );

				wc_add_notice( $duplicated_notice );
			}

			$referer = wp_get_referer() ? remove_query_arg( array(
				'cpo_duplicate_cart_item',
				'add-to-cart',
				'added-to-cart',
				'cpo_edited_cart_item'
			), add_query_arg( 'cpo_duplicated_cart_item', '1', wp_get_referer() ) ) : wc_get_cart_url();
			wp_safe_redirect( $referer );
			exit;
		}
	}
}

//
function uni_cpo_get_options_data_for_frontend( $product_id, $variables = [], $formatted_vars = [] ) {
	if ( is_singular( 'product' ) || wp_doing_ajax() ) {
		$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );
		$content      = $product_data['content'];
		$options_data = array();

		if ( is_array( $content ) && ! empty( $content ) ) {
			array_walk(
				$content,
				function ( $row, $row_key ) use ( &$options_data, $variables, $formatted_vars, $product_data ) {
					if ( is_array( $row['columns'] ) && ! empty( $row['columns'] ) ) {
						array_walk(
							$row['columns'],
							function ( $column, $column_key ) use ( &$options_data, $row_key, $variables, $formatted_vars, $product_data ) {
								if ( is_array( $column['modules'] ) && ! empty( $column['modules'] ) ) {
									array_walk(
										$column['modules'],
										function ( $module ) use ( &$options_data, $row_key, $column_key, $variables, $formatted_vars, $product_data ) {
											if ( ! empty ( $module['settings']['cpo_general']['main']['cpo_slug'] ) ) {
												$slug                 = UniCpo()->get_var_slug() . $module['settings']['cpo_general']['main']['cpo_slug'];
												$label                = isset( $module['settings']['cpo_general']['advanced']['cpo_label'] )
													? __( $module['settings']['cpo_general']['advanced']['cpo_label'] )
													: '';
												$cartlabel            = isset( $module['settings']['cpo_general']['advanced']['cpo_order_label'] )
													? __( $module['settings']['cpo_general']['advanced']['cpo_order_label'] )
													: '';
												$suboptions           = ( isset( $module['settings']['cpo_suboptions']['data']['cpo_radio_options'] ) )
													? $module['settings']['cpo_suboptions']['data']['cpo_radio_options']
													: ( ( isset( $module['settings']['cpo_suboptions']['data']['cpo_select_options'] ) )
														? $module['settings']['cpo_suboptions']['data']['cpo_select_options']
														: array() );
												$suboptions_formatted = array();
												$colorify_data        = array();
												$is_imagify           = ( ! empty ( $module['settings']['cpo_general']['main']['cpo_is_imagify'] )
												                          && 'yes' === $module['settings']['cpo_general']['main']['cpo_is_imagify'] )
													? true
													: false;

												if ( ! empty( $suboptions ) ) {
													foreach ( $suboptions as $suboption ) {
														if ( ! empty( $suboption['label'] ) && ! empty( $suboption['slug'] ) ) {
															$suboptions_formatted[ $suboption['slug'] ]['label'] = __( $suboption['label'] );
															$suboptions_formatted[ $suboption['slug'] ]['rate']  = floatVal( $suboption['rate'] );

															if ( ! empty( $suboption['attach_id'] ) || ! empty( $suboption['attach_id_r'] ) ) {
																$replacement_attach_id                                        = ( ! empty( $suboption['attach_id_r'] ) ) ? $suboption['attach_id_r'] : $suboption['attach_id'];
																$image_thumb                                                  = wp_get_attachment_image_src( $replacement_attach_id, 'woocommerce_single' );
																$suboptions_formatted[ $suboption['slug'] ]['imagify']['src'] = $image_thumb[0];
																if ( ! empty( $suboption['def'] ) ) {
																	$suboptions_formatted[ $suboption['slug'] ]['imagify']['def'] = $image_thumb[0];
																}
															}
														}
													}
												}

												if ( ! empty( $module['settings']['cpo_general']['main']['cpo_encoded_image'] )
												     && ! empty ( $module['settings']['cpo_general']['main']['cpo_slug'] ) ) {
													$colorify_data = array(
														'img_encoded' => $module['settings']['cpo_general']['main']['cpo_encoded_image']
													);
												}

												if ( unicpo_fs()->is__premium_only() ) {
													$label     = uni_cpo_replace_curly( $label, $formatted_vars, $product_data, $variables );
													$cartlabel = uni_cpo_replace_curly( $cartlabel, $formatted_vars, $product_data, $variables );

													if ( ! empty( $suboptions_formatted ) ) {
														foreach ( $suboptions_formatted as $key => $value ) {
															$variables[ "{" . $slug . "}" ]        = $suboptions_formatted[ $key ]['rate'];
															$formatted_vars[ $slug ]               = $key;
															$suboptions_formatted[ $key ]['label'] = uni_cpo_replace_curly(
																$suboptions_formatted[ $key ]['label'],
																$formatted_vars,
																$product_data,
																$variables
															);
														}
													}
												}

												$options_data[ $slug ] = array(
													'type'             => $module['type'],
													'label'            => $label,
													'cartLabel'        => $cartlabel,
													'suboptions'       => $suboptions_formatted,
													'colorify'         => $colorify_data,
													'is_imagify'       => $is_imagify,
													'is_dynamic_label' => true
												);
											}
										}
									);
								}
							}
						);
					}
				}
			);
		}

		return $options_data;
	}
}

function uni_cpo_replace_curly( $string, $formatted_vars, $product_data, $variables = array() ) {
	preg_match_all( '/{{{(\w+)}}}/', $string, $matches );
	foreach ( $matches[0] as $index => $var_name ) {
		$stripped_var_name = trim( $var_name, '{{{' );
		$stripped_var_name = trim( $stripped_var_name, '}}}' );

		if ( ! empty( $variables ) ) {
			$variables = uni_cpo_process_formula_with_non_option_vars(
				$variables,
				$product_data,
				$formatted_vars
			);
		}

		$stripped_variables = array();
		foreach ( $variables as $key => $value ) {
			$stripped_variables[ trim( $key, '{}' ) ] = $value;
		}

		if ( isset( $stripped_variables[ $stripped_var_name ] ) ) {
			$string = str_replace( $var_name, $stripped_variables[ $stripped_var_name ], $string );
		} else {
			$string = str_replace( $var_name, '', $string );
		}
	}

	return $string;
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	add_action( 'wp_footer', 'uni_cpo_add_canvas' );
	function uni_cpo_add_canvas() {
		echo '<canvas id="uni_canvas" style="display:none;"></canvas>';
	}

	//
	add_filter( 'woocommerce_single_product_image_gallery_classes', 'uni_cpo_woocommerce_single_product_image_gallery_classes', 10, 1 );
	function uni_cpo_woocommerce_single_product_image_gallery_classes( $classes ) {
		if ( is_singular( 'product' ) ) {
			global $product;
			$product_id   = $product->get_id();
			$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );

			if ( has_post_thumbnail() && 'on' === $product_data['settings_data']['layered_image_enable'] ) {
				$classes[] = 'uni_cpo_colorify_enabled';
			} elseif ( has_post_thumbnail() && 'on' === $product_data['settings_data']['imagify_enable'] ) {
				$classes[] = 'uni_cpo_imagify_enabled';
			}
		}

		return $classes;
	}

	//
	add_action( 'woocommerce_product_thumbnails', 'uni_cpo_woocommerce_product_thumbnails', 33 );
	function uni_cpo_woocommerce_product_thumbnails() {

		if ( is_singular( 'product' ) ) {
			global $product;
			$product_id   = $product->get_id();
			$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );
			$options_data = uni_cpo_get_options_data_for_frontend( $product_id );

			if ( 'on' === $product_data['settings_data']['layered_image_enable'] && has_post_thumbnail() ) {

				$palettes_data = [];

				if ( is_array( $options_data ) && ! empty( $options_data ) ) {
					array_walk(
						$options_data,
						function ( $v, $k ) use ( &$palettes_data ) {
							if ( ! empty( $v['colorify']['img_encoded'] ) ) {
								$palettes_data[] = array(
									'slug' => $k,
									'src'  => $v['colorify']['img_encoded']
								);
							}
						}
					);
				}

				if ( ! empty( $palettes_data ) ) {
					$thumbnail_uri = UniCpo()->plugin_url() . '/assets/images/blank.png';

					$html = '<div id="uni_cpo_layered_image" data-thumb="' . esc_url( $thumbnail_uri ) . '" class="woocommerce-product-gallery__image uni_cpo_main_image_layered_image" data-type-of-src="base64">';
					foreach ( $palettes_data as $data ) {
						$html .= '<img id="palette-layer-' . $data['slug'] . '" src="' . $data['src'] . '" />';
					}
					$html .= '<div class="uni_cpo_main_image_overlay">';
					$html .= '<p>' . esc_html( 'Background color:', 'uni-cpo' ) . '</p>';
					$html .= '<span class="uni-cpo-main-image-bg-colorpicker"></span>';
					$html .= '</div>';
					$html .= '</div>';

					echo $html;
				}

			} elseif ( 'on' === $product_data['settings_data']['imagify_enable'] && has_post_thumbnail() ) {

				$images_data = [];

				if ( is_array( $options_data ) && ! empty( $options_data ) ) {
					array_walk(
						$options_data,
						function ( $v, $k ) use ( &$images_data ) {
							if ( $v['is_imagify'] && ! empty( $v['suboptions'] ) ) {
								$first_suboption     = array_slice( $v['suboptions'], 0, 1 );
								$first_suboption_val = array_values( $first_suboption );
								$first_sub_src       = $first_suboption_val[0]['imagify']['src'];
								$default_sub_src     = '';

								foreach ( $v['suboptions'] as $suboption_slug => $suboption ) {
									if ( ! empty( $suboption['imagify']['def'] ) ) {
										$default_sub_src = $suboption['imagify']['def'];
									}
								}

								$images_data[] = array(
									'slug' => $k,
									'src'  => ( ! empty( $default_sub_src )
										? $default_sub_src
										: $first_sub_src )
								);
							}
						}
					);
				}

				if ( ! empty( $images_data ) ) {
					$thumbnail_uri = UniCpo()->plugin_url() . '/assets/images/blank.png';

					$html = '<div id="uni_cpo_layered_image" data-thumb="' . esc_url( $thumbnail_uri ) . '" class="woocommerce-product-gallery__image uni_cpo_main_image_layered_image" data-type-of-src="url">';
					if ( ! empty( $product_data['settings_data']['imagify_base_image'] ) ) {
						$image = wp_get_attachment_image_src( $product_data['settings_data']['imagify_base_image'], 'woocommerce_single' );
						$html  .= '<img src="' . $image[0] . '" />';
					}
					foreach ( $images_data as $data ) {
						$html .= '<img id="imagify-layer-' . $data['slug'] . '" src="' . $data['src'] . '" />';
					}
					$html .= '</div>';

					echo $html;
				}
			}
		}

	}

	//
	function uni_cpo_upload_base64_image( $base64_img, $title ) {
		$img             = str_replace( 'data:image/png;base64,', '', $base64_img );
		$img             = str_replace( ' ', '+', $img );
		$decoded         = base64_decode( $img );
		$file_type       = 'image/png';
		$uploads         = wp_upload_dir();
		$unique_filename = wp_unique_filename( $uploads['path'], $title . '.png' );

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// sets up some vars
		$cpo_temp_dir = UNI_CPO_TEMP_DIR;

		if ( ! is_dir( $cpo_temp_dir ) ) {
			if ( ! wp_mkdir_p( $cpo_temp_dir ) ) {
				throw new Exception( __( 'The temp uploading dir cannot be created.', 'uni-cpo' ) );
			}
		}

		$temp_file_path = wp_normalize_path( trailingslashit( $cpo_temp_dir ) . $unique_filename );
		$upload_file    = file_put_contents( $temp_file_path, $decoded );

		$file = array(
			'name'     => basename( $temp_file_path ),
			'type'     => $file_type,
			'tmp_name' => $temp_file_path,
			'error'    => 0,
			'size'     => $upload_file,
		);

		$overrides = array(
			'test_form' => false,
			'test_size' => true,
		);

		// Move the temporary file into the uploads directory
		$results = wp_handle_sideload( $file, $overrides );

		if ( ! empty( $results['error'] ) ) {
			return 0;
		} else {

			@ unlink( $temp_file_path );

			$local_path = $results['file'];
			$local_url  = $results['url'];
			$filetype   = $results['type'];

			$attachment = array(
				'post_mime_type' => $filetype,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $local_path ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => $local_url,
				'post_parent'    => 0
			);

			$attach_id = wp_insert_attachment( $attachment, $local_path );

			if ( ! is_wp_error( $attach_id ) ) {
				wp_update_attachment_metadata(
					$attach_id,
					wp_generate_attachment_metadata( $attach_id, $local_path )
				);
			}

			return $attach_id;
		}
	}

	function get_attachment_by_url( $url ) {
		global $wpdb;

		$filename = basename( $url );
		$sql      = $wpdb->prepare(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
			'%' . $filename . '%'
		);

		$results = $wpdb->get_results( $sql );
		$post_id = null;

		if ( $results ) {
			$post_id = reset( $results )->post_id;

			if ( count( $results ) > 1 ) {
				foreach ( $results as $result ) {
					if ( $filename === $result->meta_value ) {
						$post_id = $result->post_id;
						break;
					}
				}
			}
		}

		return $post_id;
	}
}
