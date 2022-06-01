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

// CPO settings tab
add_filter( 'woocommerce_product_data_tabs', 'uni_cpo_add_settings_tab' );
function uni_cpo_add_settings_tab( $product_data_tabs ) {

	$product_data_tabs['uni_cpo_settings'] = array(
		'label'  => __( 'CPO Form Builder', 'uni-cpo' ),
		'target' => 'uni_cpo_settings_data',
		'class'  => array( 'hide_if_grouped', 'hide_if_external', 'hide_if_variable' ),
	);

	return $product_data_tabs;
}

// CPO settings (price formula) tab content
add_action( 'woocommerce_product_data_panels', 'uni_cpo_add_custom_settings_tab_content' );
function uni_cpo_add_custom_settings_tab_content() {
	?>
    <div
            id="uni_cpo_settings_data"
            class="panel woocommerce_options_panel">
        <a
                href="<?php echo esc_url( Uni_Cpo_Product::get_edit_url() ); ?>"
                target="_blank">
			<?php esc_html_e( 'go to the builder', 'uni-cpo' ); ?>
        </a>
    </div>
	<?php
}

//
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'uni_cpo_order_formatted_meta_data', 10, 2 );
function uni_cpo_order_formatted_meta_data( $formatted_meta, $item ) {
	try {

		if ( ! method_exists( $item, 'get_product_id' ) ) {
			return $formatted_meta;
		}

		$item_meta_data      = $item->get_meta_data();
		$formatted_vars      = array();
		$variables           = array();
		$options_eval_result = array();
		$product_id          = $item->get_product_id();
		$product_data        = Uni_Cpo_Product::get_product_data_by_id( $product_id );

		if ( empty( $product_data ) ) {
			return $formatted_meta;
		}

		$filtered_form_data = array();
		array_walk(
			$item_meta_data,
			function ( $v ) use ( &$filtered_form_data ) {
				$meta_data = $v->get_data();
				if ( false !== strpos( $meta_data['key'], UniCpo()->get_var_slug() ) ) {
					$filtered_form_data[ ltrim( $meta_data['key'], '_' ) ] = $meta_data['value'];
				}
			}
		);

		// excluded from displaying
		$excluded_order_item_meta_keys = apply_filters( 'uni_cpo_excluded_order_item_meta_keys', array(
			'_uni_custom_item_image',
			'_uni_item_weight',
			'_uni_item_width',
			'_uni_item_height',
			'_uni_item_length'
		), $item );

		if ( unicpo_fs()->is__premium_only() ) {
			// exclude option chosen as qty field
			$qty_field_slug = isset( $product_data['settings_data']['qty_field'] )
				? $product_data['settings_data']['qty_field']
				: 'wc';

			if ( 'wc' !== $qty_field_slug && isset( $filtered_form_data[ $qty_field_slug ] ) && ! empty( absint( $filtered_form_data[ $qty_field_slug ] ) ) ) {
				$excluded_order_item_meta_keys[] = '_' . $qty_field_slug;
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

		array_walk(
			$item_meta_data,
			function ( $v ) use ( &$formatted_meta, $filtered_form_data, $excluded_order_item_meta_keys, $product_data, $formatted_vars, $variables ) {
				$meta_data = $v->get_data();

				if ( in_array( $meta_data['key'], $excluded_order_item_meta_keys ) ) {
					unset( $formatted_meta[ $meta_data['id'] ] );
				} elseif ( false !== strpos( $meta_data['key'], UniCpo()->get_var_slug() ) && ! empty( $meta_data['value'] ) ) {
					$slug = ltrim( $meta_data['key'], '_' );

					$post = uni_cpo_get_post_by_slug( $slug );

					if ( $post ) {
						$option = uni_cpo_get_option( $post->ID );

						if ( unicpo_fs()->is__premium_only() ) {
							if ( $option instanceof Uni_Cpo_Option && $option->cpo_order_visibility() ) {
								unset( $formatted_meta[ $meta_data['id'] ] );
								return;
							}
						}

						if ( is_object( $option ) ) {
							$display_key = uni_cpo_sanitize_label( $option->cpo_order_label() );

							if ( 'matrix' === $option::get_type() ) {
								//$form_data[ $slug ] = $meta_data['value'];
								if ( ! empty( $formatted_meta ) ) {
									foreach ( $formatted_meta as $formatted_meta_obj_key => $formatted_meta_obj ) {
										if ( in_array( $formatted_meta_obj->key, array(
											'_' . $slug . '_col',
											'_' . $slug . '_row'
										) ) ) {
											$form_data[ ltrim( $formatted_meta_obj->key, '_' ) ] = $formatted_meta_obj->value;
											unset( $formatted_meta[ $formatted_meta_obj_key ] );
										}
									}
								}
							}

							$calculate_result = $option->calculate( $filtered_form_data );

							if ( is_array( $meta_data['value'] ) ) {
								$value = implode( ', ', $meta_data['value'] );
							} else {
								$value = $meta_data['value'];
							}

							$display_value = $value;
							if ( $calculate_result ) {
								foreach ( $calculate_result as $k => $v ) {
									if ( $slug === $k ) { // excluding special vars

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
										break;
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

								$formatted_meta[ $meta_data['id'] ] = (object) array(
									'key'           => $meta_data['key'],
									'value'         => $value,
									'display_key'   => apply_filters( 'uni_cpo_order_item_display_meta_key', $display_key, $v ),
									'display_value' => wpautop( make_clickable( apply_filters( 'uni_cpo_order_item_display_meta_value', $display_value, $v ) ) ),
								);
							}
						}
					}

				}
			}
		);

		if ( unicpo_fs()->is__premium_only() ) {
			$filtered_novs = array();
			//$product_data  = Uni_Cpo_Product::get_product_data_by_id( $item->get_product_id() );

			array_walk(
				$item_meta_data,
				function ( $v ) use ( &$filtered_novs ) {
					$meta_data = $v->get_data();
					if ( false !== strpos( $meta_data['key'], UniCpo()->get_nov_slug() ) ) {
						$filtered_novs[ ltrim( $meta_data['key'], '_' ) ] = $meta_data['value'];
					}
				}
			);

			$nov_data = $product_data['nov_data']['nov'];
			$cpo_nov  = array();

			if ( ! empty( $nov_data ) && isset( $filtered_novs ) ) {
				array_walk(
					$nov_data,
					function ( $nov ) use ( &$cpo_nov, $filtered_novs ) {
						$nov_slug = UniCpo()->get_nov_slug() . $nov['slug'];
						if ( isset( $nov['cart_display'] ) && 'on' === $nov['cart_display']['enable']
						     && isset( $filtered_novs[ $nov_slug ] ) ) {
							if ( is_array( $filtered_novs[ $nov_slug ] ) ) {
								$cpo_nov[ $nov_slug ] = $filtered_novs[ $nov_slug ];
							} else {
								$cpo_nov[ $nov_slug ] = array(
									'display_name' => $nov['cart_display']['name'],
									'value'        => $filtered_novs[ $nov_slug ]
								);
							}
						}
					}
				);
			}

			array_walk(
				$item_meta_data,
				function ( $v ) use ( &$formatted_meta, $cpo_nov ) {
					$meta_data = $v->get_data();
					$nov_slug  = ltrim( $meta_data['key'], '_' );
					if ( false !== strpos( $nov_slug, UniCpo()->get_nov_slug() ) && isset( $cpo_nov[ $nov_slug ] ) ) {
						//print_r($cpo_nov);
						$display_key                        = $cpo_nov[ $nov_slug ]['display_name'];
						$display_value                      = $cpo_nov[ $nov_slug ]['value'];
						$formatted_meta[ $meta_data['id'] ] = (object) array(
							'key'           => $meta_data['key'],
							'value'         => $display_value,
							'display_key'   => apply_filters( 'uni_cpo_order_item_display_meta_key', $display_key, $v ),
							'display_value' => wpautop( make_clickable( apply_filters( 'uni_cpo_order_item_display_meta_value', $display_value, $v ) ) ),
						);
					}
				}
			);
		}

		return $formatted_meta;
	} catch ( Exception $e ) {
		return new WP_Error( 'cart-error', $e->getMessage() );
	}
}

add_action( 'admin_footer', 'uni_cpo_order_edit_options_modal' );
function uni_cpo_order_edit_options_modal() {
	$screen = get_current_screen();
	if ( 'shop_order' === $screen->post_type ) {
		?>
        <script
                type="text/template"
                id="tmpl-uni-cpo-modal-add-options">
            <div class="wc-backbone-modal">
                <div class="wc-backbone-modal-content">
                    <section
                            class="wc-backbone-modal-main"
                            role="main">
                        <header class="wc-backbone-modal-header">
                            <h1><?php _e( 'Add/edit CPO options', 'uni-cpo' ); ?></h1>
                            <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                <span class="screen-reader-text">Close modal panel</span>
                            </button>
                        </header>
                        <article id="cpo-order-edit-options-wrapper">
                            <form
                                    action=""
                                    method="post">
                                <input
                                        type="hidden"
                                        id="cpo-order-product-id"
                                        name="product_id"
                                        value="{{{data.pid}}}"/>
                                <input
                                        type="hidden"
                                        id="cpo-order-security"
                                        name="security"
                                        value="{{{data.security}}}"/>
                                <input
                                        type="hidden"
                                        name="action"
                                        value="uni_cpo_order_item_update"/>
                                <input
                                        type="hidden"
                                        id="cpo-order-item-id"
                                        name="order_item_id"
                                        value="{{{data.order_item_id}}}"/>
                                <input
                                        type="hidden"
                                        name="order_id"
                                        value="{{{woocommerce_admin_meta_boxes.post_id}}}"/>
                            </form>

                            <form
                                    id="cpo-item-options-form"
                                    action=""
                                    method="post">
                            </form>
                        </article>
                        <footer>
                            <div class="inner">
                                <button
                                        id="btn-ok"
                                        class="button button-primary button-large"><?php _e( 'Update', 'uni-cpo' ); ?></button>
                            </div>
                        </footer>
                    </section>
                </div>
            </div>
            <div class="wc-backbone-modal-backdrop modal-close"></div>
        </script>
		<?php
	}
}

if ( unicpo_fs()->is__premium_only() ) {
	//
	add_filter( 'woocommerce_order_item_name', 'uni_cpo_order_item_title_sample', 10, 2 );

	function uni_cpo_order_item_title_sample( $title, $item ) {
		$item_meta_data = $item->get_meta_data();

		array_walk(
			$item_meta_data,
			function ( $v ) use ( &$title ) {
				$meta_data = $v->get_data();
				if ( '_cpo_is_free_sample' === $meta_data['key'] ) {
					$title .= ' ' . __( '(Sample)', 'uni-cpo' );
				}
			}
		);

		return $title;
	}
}

//////////////////////////////////////////////////////////////////////////////////////
// WC order edit page
//////////////////////////////////////////////////////////////////////////////////////
// adds Add/Edit CPO options btn for order items
add_action( 'woocommerce_after_order_itemmeta', 'uni_cpo_woocommerce_order_item_add_action_buttons', 10, 2 );
function uni_cpo_woocommerce_order_item_add_action_buttons( $item_id, $item ) {

	if ( 'line_item' !== $item->get_type() ) {
		return;
	}

	$product_id   = $item->get_product_id();
	$product_data = Uni_Cpo_Product::get_product_data_by_id( $product_id );
	$nonce        = wp_create_nonce( 'order-item' );
	if ( 'on' === $product_data['settings_data']['cpo_enable'] ) {
		echo '<button type="button" class="button cpo-edit-options-btn cpo-for-item-' . esc_attr( $item_id ) . '" data-security="' . esc_attr( $nonce ) . '" data-pid="' . esc_attr( $product_id ) . '" data-order_item_id="' . esc_attr( $item_id ) . '">' . esc_html__( 'Add/Edit CPO option(s)', 'uni-cpo' ) . '</button>';
	}
}

add_filter( 'woocommerce_hidden_order_itemmeta', 'uni_cpo_hidden_order_itemmeta', 10, 1 );
function uni_cpo_hidden_order_itemmeta( $list ) {
    $list[] = '_cpo_product_id';
	$list[] = '_add-to-cart';
	$list[] = '_cpo_product_image';
	$list[] = '_cpo_cart_item_id';

    return $list;
}
