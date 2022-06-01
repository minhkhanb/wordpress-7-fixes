<?php
/*
*   Uni_Cpo_Ajax Class
*
*/

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Uni_Cpo_Ajax {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_cpo_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get Ajax Endpoint.
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'cpo-ajax', $request ) );
	}

	/**
	 * Set CPO AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['cpo-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'CPO_DOING_AJAX' ) ) {
				define( 'CPO_DOING_AJAX', true );
			}
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for CPO Ajax Requests
	 */
	private static function cpo_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for CPO Ajax request and fire action.
	 */
	public static function do_cpo_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['cpo-ajax'] ) ) {
			$wp_query->set( 'cpo-ajax', sanitize_text_field( $_GET['cpo-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'cpo-ajax' ) ) {
			self::cpo_ajax_headers();
			do_action( 'cpo_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 *   Hook in methods
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'uni_cpo_save_content'               => false,
			'uni_cpo_delete_content'             => false,
			'uni_cpo_save_model'                 => false,
			'uni_cpo_fetch_similar_modules'      => false,
			'uni_cpo_save_settings_data'         => false,
			'uni_cpo_fetch_similar_products'     => false,
			'uni_cpo_duplicate_product_settings' => false,
			'uni_cpo_save_discounts_data'        => false,
			'uni_cpo_save_formula_data'          => false,
			'uni_cpo_save_image_data'            => false,
			'uni_cpo_save_weight_data'           => false,
			'uni_cpo_save_dimensions_data'       => false,
			'uni_cpo_save_nov_data'              => false,
			'uni_cpo_import_matrix'              => false,
			'uni_cpo_sync_with_module'           => false,
			'uni_cpo_upload_file'                => true,
			'uni_cpo_remove_file'                => true,
			'uni_cpo_add_to_cart'                => true,
			'uni_cpo_price_calc'                 => true,
			'uni_cpo_cart_item_edit'             => true,
			'uni_cpo_cart_item_edit_inline'      => true,
			'uni_cpo_cart_item_update_inline'    => true,
			'uni_cpo_order_item_edit'            => false,
			'uni_cpo_order_item_update'          => false,
			'uni_cpo_product_settings_export'    => false,
			'uni_cpo_product_settings_import'    => false,
			'uni_cpo_exim_view'                  => false,
			'uni_cpo_exim_import'                => false,
			'uni_cpo_exim_export'                => false,
			'uni_cpo_prods_data_update'          => false
		);

		foreach ( $ajax_events as $ajax_event => $priv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $priv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}

	}

	/**
	 *   uni_cpo_save_content
	 */
	public static function uni_cpo_save_content() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$data    = json_decode( stripslashes_deep( $_POST['data'] ), true );
			$content = array();

			if ( is_array( $data ) && ! empty( $data ) ) {
				array_walk(
					$data,
					function ( $row, $row_key ) use ( &$content ) {
						$content[ $row_key ] = $row;
						if ( is_array( $row['columns'] ) && ! empty( $row['columns'] ) ) {
							array_walk(
								$row['columns'],
								function ( $column, $column_key ) use ( &$content, $row_key ) {
									$content[ $row_key ]['columns'][ $column_key ] = $column;
									if ( is_array( $column['modules'] ) && ! empty( $column['modules'] ) ) {
										array_walk(
											$column['modules'],
											function ( $module, $module_key ) use ( &$content, $row_key, $column_key ) {
												$content[ $row_key ]['columns'][ $column_key ]['modules'][ $module_key ] = $module;
												$module_settings                                                         = [];
												foreach ( $module['settings'] as $data_name => $data_data ) {
													$data_name                     = uni_cpo_clean( $data_name );
													$data_data                     = uni_cpo_get_settings_data_sanitized( $data_data, $data_name );
													$module_settings[ $data_name ] = $data_data;
												}
												$content[ $row_key ]['columns'][ $column_key ]['modules'][ $module_key ]['settings'] = $module_settings;
											}
										);
									}
								}
							);
						}
					}
				);
			}

			$result = Uni_Cpo_Product::save_content( absint( $_POST['product_id'] ), $content, false );

			if ( $result ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'error' => __( 'The data was not saved. Maybe, there is nothing to save?' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_delete_content
	 */
	public static function uni_cpo_delete_content() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}
			$result = Uni_Cpo_Product::delete_content( absint( $_POST['product_id'] ) );

			if ( $result ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'error' => __( 'Hm-m... Something went wrong.' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_save_model
	 */
	public static function uni_cpo_save_model() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$data = $_POST['model'];

			$post_id        = ( ! empty( $data['pid'] ) ) ? absint( $data['pid'] ) : 0;
			$model_obj_type = ( ! empty( $data['obj_type'] ) ) ? uni_cpo_clean( $data['obj_type'] ) : '';
			$model_type     = ( ! empty( $data['type'] ) ) ? uni_cpo_clean( $data['type'] ) : '';

			if ( ! $model_type ) {
				throw new Exception( __( 'Invalid model type', 'uni-cpo' ) );
			}

			if ( ! $model_obj_type ) {
				throw new Exception( __( 'Invalid builder model object type', 'uni-cpo' ) );
			}

			if ( $post_id > 0 ) {
				$model = uni_cpo_get_model( $model_obj_type, $post_id );
				if ( is_object( $model ) && 'trash' === $model->get_status() ) {
					wp_delete_post( $post_id, true );
					$post_id = 0;
					$model   = uni_cpo_get_model( $model_obj_type, $post_id, $model_type );
				} elseif ( ! is_object( $model ) ) {
					$post_id = 0;
					$model   = uni_cpo_get_model( $model_obj_type, $post_id, $model_type );
				}
			} else {
				$model = uni_cpo_get_model( $model_obj_type, $post_id, $model_type );
			}

			if ( ! $model ) {
				throw new Exception( __( 'Invalid model', 'uni-cpo' ) );
			}

			if ( 'option' === $model_obj_type ) {
				$cpo_general      = $data['settings']['cpo_general'];
				$slug_being_saved = ( ! empty( $cpo_general['main']['cpo_slug'] ) )
					? uni_cpo_clean( $cpo_general['main']['cpo_slug'] )
					: sanitize_title_with_dashes( uniqid( 'option_' ) );

				if ( empty( $model->get_slug() ) ) {
					// slug is empty, it is a new option
					$slug_check_result = uni_cpo_get_unique_slug( $slug_being_saved );
				} elseif ( ! empty( $model->get_slug() ) ) {
					if ( UniCpo()->get_var_slug() . $slug_being_saved !== $model->get_slug() ) {
						// looks like slug is going to be changed, so let's check its uniqueness
						$slug_check_result = uni_cpo_get_unique_slug( $slug_being_saved );
					} else {
						$slug_check_result = array(
							'unique' => true,
							'slug'   => $model->get_slug()
						);
					}
				}

				if ( ! isset( $slug_check_result ) ) {
					throw new Exception( __( 'Something went srong', 'uni-cpo' ) );
				}

				if ( $slug_check_result['unique'] && $slug_check_result['slug'] ) {

					unset( $data['settings']['general']['status'] );
					$data['settings']['cpo_general']['main']['cpo_slug'] = '';

					$props = array(
						'slug' => $slug_check_result['slug'],
					);

					foreach ( $data['settings'] as $data_name => $data_data ) {
						$data_name           = uni_cpo_clean( $data_name );
						$data_data           = uni_cpo_get_settings_data_sanitized( $data_data, $data_name );
						$props[ $data_name ] = $data_data;
					}

					$model->set_props( $props );
					$model->save();
					$model_data = $model->formatted_model_data();

					wp_send_json_success( $model_data );

				} elseif ( ! $slug_check_result['unique'] && $slug_check_result['slug'] ) {
					wp_send_json_error( array( 'error' => $slug_check_result ) );
				}

			} elseif ( 'module' === $model_obj_type ) {
				// TODO
			}

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_fetch_similar_modules
	 */
	public static function uni_cpo_fetch_similar_modules() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$data = uni_cpo_clean( $_POST );

			if ( ! isset( $data['type'] ) || ! isset( $data['obj_type'] ) ) {
				throw new Exception( __( 'Type is not specified', 'uni-cpo' ) );
			}

			$result = uni_cpo_get_similar_modules( $data );

			if ( $result ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( array( 'error' => __( 'No modules', 'uni-cpo' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_save_settings_data
	 */
	public static function uni_cpo_save_settings_data() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$model              = $_POST['model'];
			$data['product_id'] = absint( $model['id'] );
			foreach ( $model['settingsData'] as $data_name => $data_data ) {
				$data_name                           = uni_cpo_clean( $data_name );
				$data_data                           = html_entity_decode( uni_cpo_sanitize_text( $data_data ) );
				$data['settings_data'][ $data_name ] = $data_data;
			}
			$result = Uni_Cpo_Product::save_product_data( $data, 'settings_data' );

			if ( ! isset( $result['error'] ) ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( $result );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_fetch_similar_products
	 */
	public static function uni_cpo_fetch_similar_products() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				$data = uni_cpo_clean( $_POST );

				if ( ! isset( $data['pid'] ) || ! (int) $data['pid'] ) {
					throw new Exception( __( 'Product ID is not specified', 'uni-cpo' ) );
				}

				$result = uni_cpo_get_similar_products_ids( $data );

				if ( $result && is_array( $result ) ) {

					$posts = array();
					array_walk(
						$result,
						function ( $v ) use ( &$posts ) {
							$posts[ $v->ID ] = $v->post_title;
						}
					);

					wp_send_json_success( $posts );
				} else {
					wp_send_json_error( array( 'error' => __( 'No similar products', '' ) ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_duplicate_product_settings
	 */
	public static function uni_cpo_duplicate_product_settings() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				$data = uni_cpo_clean( $_POST );

				if ( ! isset( $data['pid'] ) || ! (int) $data['pid'] ) {
					throw new Exception( __( 'Product ID is not specified', 'uni-cpo' ) );
				}

				if ( ! isset( $data['target_id'] ) || ! (int) $data['target_id'] ) {
					throw new Exception( __( 'Target product ID is not specified', 'uni-cpo' ) );
				}

				$product = wc_get_product( $data['pid'] );

				if ( false === $product ) {
					/* translators: %s: product id */
					throw new Exception( sprintf( __( 'Product creation failed, could not find original product: %s', 'uni-cpo' ), $data['pid'] ) );
				}

				$product_from = wc_get_product( $data['target_id'] );

				if ( false === $product_from ) {
					/* translators: %s: product id */
					throw new Exception( sprintf( __( 'Product creation failed, could not find targeted product: %s', 'uni-cpo' ), $data['target_id'] ) );
				}

				$result = Uni_Cpo_Product::duplicate_product_settings( $product, $product_from );

				if ( is_bool( $result ) ) {
					wp_send_json_success();
				} else {
					if ( isset( $result['error'] ) ) {
						wp_send_json_error( $result );
					} else {
						throw new Exception( __( 'Error', 'uni-cpo' ) );
					}
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_save_discounts_data
	 */
	public static function uni_cpo_save_discounts_data() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				$model                  = $_POST['model'];
				$data['product_id']     = absint( $model['id'] );
				$data['discounts_data'] = uni_cpo_clean( $model['discountsData'] );
				$result                 = Uni_Cpo_Product::save_product_data( $data, 'discounts_data' );

				if ( ! isset( $result['error'] ) ) {
					wp_send_json_success( $result );
				} else {
					wp_send_json_error( $result );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_save_formula_data
	 */
	public static function uni_cpo_save_formula_data() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$model                = $_POST['model'];
			$data['product_id']   = absint( $model['id'] );
			$data['formula_data'] = uni_cpo_clean( $model['formulaData'] );
			if ( ! isset( $data['formula_data']['formula_scheme'] ) ) {
				$data['formula_data']['formula_scheme'] = '';
			}
			$result = Uni_Cpo_Product::save_product_data( $data, 'formula_data' );

			if ( ! isset( $result['error'] ) ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( $result );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_save_image_data
	 */
	public static function uni_cpo_save_image_data() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$model              = $_POST['model'];
			$data['product_id'] = absint( $model['id'] );
			$data['image_data'] = uni_cpo_clean( $model['imageData'] );
			if ( ! isset( $data['image_data']['image_scheme'] ) ) {
				$data['image_data']['image_scheme'] = '';
			}
			$result = Uni_Cpo_Product::save_product_data( $data, 'image_data' );

			if ( ! isset( $result['error'] ) ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( $result );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_save_weight_data
	 */
	public static function uni_cpo_save_weight_data() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				$model               = $_POST['model'];
				$data['product_id']  = absint( $model['id'] );
				$data['weight_data'] = uni_cpo_clean( $model['weightData'] );
				if ( ! isset( $data['weight_data']['weight_scheme'] ) ) {
					$data['weight_data']['weight_scheme'] = '';
				}
				$result = Uni_Cpo_Product::save_product_data( $data, 'weight_data' );

				if ( ! isset( $result['error'] ) ) {
					wp_send_json_success( $result );
				} else {
					wp_send_json_error( $result );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_save_dimensions_data
	 */
	public static function uni_cpo_save_dimensions_data() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				$model                   = $_POST['model'];
				$data['product_id']      = absint( $model['id'] );
				$data['dimensions_data'] = uni_cpo_clean( $model['dimensionsData'] );
				$result                  = Uni_Cpo_Product::save_product_data( $data, 'dimensions_data' );

				if ( ! isset( $result['error'] ) ) {
					wp_send_json_success( $result );
				} else {
					wp_send_json_error( $result );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_save_nov_data
	 */
	public static function uni_cpo_save_nov_data() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$model              = $_POST['model'];
			$data['product_id'] = absint( $model['id'] );
			$data['nov_data']   = uni_cpo_clean( $model['novData'] );
			if ( ! isset( $data['nov_data']['nov'] ) ) {
				$data['nov_data']['nov'] = '';
			}

			// patch; fixes 'undefined' NOV
			if ( isset( $data['nov_data']['nov']['<%row-count%>'] ) ) {
				unset( $data['nov_data']['nov']['<%row-count%>'] );
			}

			$result = Uni_Cpo_Product::save_product_data( $data, 'nov_data' );

			if ( ! isset( $result['error'] ) ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( $result );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_import_matrix
	 */
	public static function uni_cpo_import_matrix() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				if ( ! current_user_can( 'edit_products' ) ) {
					throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
				}

				if ( ! isset( $_FILES['file']['tmp_name'] ) ) {
					throw new Exception( __( 'No files added', 'uni-cpo' ) );
				}

				if ( ! in_array(
					$_FILES['file']['type'],
					array(
						'application/vnd.ms-excel',
						'text/csv',
						'application/octet-stream',
						'text/comma-separated-values',
						'application/csv',
						'application/excel',
						'application/vnd.msexcel',
						'text/anytext'
					)
				)
				) {
					throw new Exception( __( 'Only CSV files are allowed', 'uni-cpo' ) );
				}

				$file = uni_cpo_clean( $_FILES['file']['tmp_name'] );
				$data = uni_cpo_import_csv_data( $file );
				$data = uni_cpo_format_csv_matrix_data( $data ); // TODO check, fix

				if ( $data ) {
					wp_send_json_success( $data );
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_sync_with_module
	 */
	public static function uni_cpo_sync_with_module() {
		try {
			check_ajax_referer( 'uni_cpo_builder', 'security' );

			if ( ! current_user_can( 'edit_products' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$data = uni_cpo_clean( $_POST );

			if ( ! isset( $data['obj_type'] ) ) {
				throw new Exception( __( 'Type is not specified', 'uni-cpo' ) );
			}

			if ( ! isset( $data['pid'] ) ) {
				throw new Exception( __( 'Target post is not chosen', 'uni-cpo' ) );
			}

			if ( ! isset( $data['method'] ) ) {
				throw new Exception( __( 'Sync method is not chosen', 'uni-cpo' ) );
			}

			$result = uni_cpo_get_module_for_sync( $data );

			if ( $result ) {
				wp_send_json_success( $result );
			} else {
				wp_send_json_error( array( 'error' => __( 'No modules', 'uni-cpo' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_upload_file
	 */
	public static function uni_cpo_upload_file() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$post_id         = absint( $_POST['productId'] );
				$file_name       = wc_clean( $_POST['file_name'] );
				$option_slug     = wc_clean( $_POST['slug'] );
				$plugin_settings = UniCpo()->get_settings();

				if ( empty( $post_id ) ) {
					throw new Exception( __( 'No post ID specified', 'uni-cpo' ) );
				}

				if ( empty( $file_name ) ) {
					throw new Exception( __( 'No file name specified', 'uni-cpo' ) );
				}

				if ( empty( $option_slug ) ) {
					throw new Exception( __( 'No option variable specified', 'uni-cpo' ) );
				}

				if ( empty( $_FILES ) || $_FILES['file']['error'] ) {
					throw new Exception( __( 'Failed to upload file(s).', 'uni-cpo' ) );
				}

				if ( 'dropbox' === $plugin_settings['file_storage'] && empty( $plugin_settings['dropbox_token'] ) ) {
					throw new Exception( __( 'No dropbox token set. Abort operation.', 'uni-cpo' ) );
				}

				// sets up some vars
				$cpo_temp_dir   = UNI_CPO_TEMP_DIR;
				$temp_file_path = wp_normalize_path( trailingslashit( $cpo_temp_dir ) . $file_name );

				if ( ! is_dir( $cpo_temp_dir ) ) {
					if ( ! wp_mkdir_p( $cpo_temp_dir ) ) {
						throw new Exception( __( 'The temp uploading dir cannot be created.', 'uni-cpo' ) );
					}
				}

				// uploading in chunks
				$chunk  = isset( $_REQUEST['chunk'] ) ? intval( $_REQUEST['chunk'] ) : 0;
				$chunks = isset( $_REQUEST['chunks'] ) ? intval( $_REQUEST['chunks'] ) : 0;

				// Open temp file
				$out = @fopen( "{$temp_file_path}.part", $chunk == 0 ? "wb" : "ab" );
				if ( $out ) {
					// Read binary input stream and append it to temp file
					$in = @fopen( $_FILES['file']['tmp_name'], "rb" );

					if ( $in ) {
						while ( $buff = fread( $in, 4096 ) ) {
							fwrite( $out, $buff );
						}
					} else {
						throw new Exception( __( 'Failed to open input stream.', 'uni-cpo' ) );
					}

					@fclose( $in );
					fflush( $out );
					@fclose( $out );

					@unlink( $_FILES['file']['tmp_name'] );
				} else {
					throw new Exception( __( 'Failed to open input stream.', 'uni-cpo' ) );
				}

				// Check if file has been uploaded
				if ( ! $chunks || $chunk == $chunks - 1 ) {
					// remove ".part" part from the file name
					rename( "{$temp_file_path}.part", $temp_file_path );

					// Security check
					$wp_filetype     = wp_check_filetype_and_ext( $temp_file_path, $file_name );
					$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
					$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
					$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

					if ( $proper_filename ) {
						$file_name = $proper_filename;
					}

					if ( ! $type || ! $ext ) {
						throw new Exception( __( 'Sorry, this file type is not permitted for security reasons.', 'uni-cpo' ) );
					}

					if ( 'local' === $plugin_settings['file_storage'] ) {

						if ( ! empty( $plugin_settings['custom_path_enable'] ) ) {
							$uploads_path = UNI_CPO_FILES_DIR;
							$uploads_path = str_replace( '{{{POST_ID}}}', $post_id, $uploads_path );
							$uploads_path = str_replace( '{{{DATE}}}', date( 'Y/m/d' ), $uploads_path );
							$uploads_url  = UNI_CPO_FILES_URI;
							$uploads_url  = str_replace( '{{{POST_ID}}}', $post_id, $uploads_url );
							$uploads_url  = str_replace( '{{{DATE}}}', date( 'Y/m/d' ), $uploads_url );

							if ( ! is_dir( $uploads_path ) ) {
								if ( ! wp_mkdir_p( $uploads_path ) ) {
									throw new Exception( __( 'The uploading dir cannot be created.', 'uni-cpo' ) );
								}
							}

							$unique_filename = wp_unique_filename( $uploads_path, $file_name );
							$new_file        = wp_normalize_path( trailingslashit( $uploads_path ) . $unique_filename );
						} else {
							if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) ) {
								throw new Exception( __( 'An error occurred during uploading. Please, contact admin of the site.', 'uni-cpo' ) );
							}

							$unique_filename = wp_unique_filename( $uploads['path'], $file_name );
							$new_file        = wp_normalize_path( trailingslashit( $uploads['path'] ) . $unique_filename );
						}

						$move_new_file = @ copy( $temp_file_path, $new_file );
						@ unlink( $temp_file_path );

						if ( false === $move_new_file ) {
							throw new Exception( __( 'The uploaded file could not be moved.', 'uni-cpo' ) );
						}

						// Set correct file permissions.
						$stat  = stat( dirname( $new_file ) );
						$perms = $stat['mode'] & 0000666;
						@ chmod( $new_file, $perms );

						// Compute the URL.
						if ( ! empty( $plugin_settings['custom_path_enable'] ) ) {
							$url = trailingslashit( $uploads_url ) . basename( $unique_filename );
						} else {
							$url = trailingslashit( $uploads['url'] ) . basename( $unique_filename );
						}

						if ( is_multisite() ) {
							delete_transient( 'dirsize_cache' );
						}

						// adding an attachment
						$attachment = array(
							'post_mime_type' => $type,
							'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $unique_filename ) ),
							'post_content'   => '',
							'guid'           => $url,
							'post_parent'    => 0,
							'post_author'    => 1
						);

						$attachment_id = wp_insert_attachment( $attachment, $new_file );
						if ( ! is_wp_error( $attachment_id ) ) {
							wp_update_attachment_metadata(
								$attachment_id,
								wp_generate_attachment_metadata( $attachment_id, $new_file )
							);
						}
						//
						update_post_meta( $attachment_id, '_uni_cpo_media', 'product' );
						update_post_meta( $attachment_id, '_uni_cpo_media_for', $post_id );

						$meta = uni_cpo_get_attachment_meta( $attachment_id );

						wp_send_json_success(
							array(
								'message' => __( 'File is valid, and was successfully uploaded.', 'uni-cpo' ),
								'file'    => $meta
							)
						);

					} elseif ( 'dropbox' === $plugin_settings['file_storage'] ) {

						// dropbox sdk
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Auth.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/FileProperties.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/FileRequests.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Files.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Misc.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Paper.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Sharing.php' );
						include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Users.php' );

						$dropbox_token = $plugin_settings['dropbox_token'];
						$dropbox       = new \Dropbox\Dropbox( $dropbox_token );
						$date_today    = date( 'Y-m-d' );
						$path          = "/product-$post_id/$date_today/$file_name";
						$result        = $dropbox->files->upload( $path, $temp_file_path );

						@ unlink( $temp_file_path );

						if ( isset( $result['error'] ) ) {
							throw new Exception( $result['error_summary'] );
						} else {
							wp_send_json_success( $result );
						}
					}

				} else {
					wp_send_json_success( array( 'message' => __( 'Still uploading...', 'uni-cpo' ) ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_remove_file
	 */
	public static function uni_cpo_remove_file() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {

				$attach_id    = absint( $_POST['attach_id'] );
				$dropbox_path = $_POST['dropboxPath'];

				if ( empty( $attach_id ) && empty( $dropbox_path ) ) {
					throw new Exception( __( 'Not enough data', 'uni-cpo' ) );
				}

				if ( ! empty( $attach_id ) ) {
					$result = wp_delete_attachment( $attach_id, true );
				} elseif ( ! empty( $dropbox_path ) ) {
					$plugin_settings = UniCpo()->get_settings();

					// dropbox sdk
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Auth.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/FileProperties.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/FileRequests.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Files.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Misc.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Paper.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Sharing.php' );
					include_once( UNI_CPO_ABSPATH . 'includes/vendors/dropbox-v2-php-sdk/Dropbox/Users.php' );

					$dropbox_token = $plugin_settings['dropbox_token'];
					$dropbox       = new \Dropbox\Dropbox( $dropbox_token );
					$result        = $dropbox->files->delete_v2( $dropbox_path );

					if ( isset( $result['error'] ) ) {
						throw new Exception( $result['error_summary'] );
					}
				}

				if ( $result ) {
					wp_send_json_success( array( 'message' => __( 'File successfully deleted', 'uni-cpo' ) ) );
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 * Get a refreshed cart fragment, including the mini cart HTML.
	 */
	public static function get_refreshed_fragments() {
		ob_start();

		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();

		$data = array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
		);

		wp_send_json_success( $data );
	}

	/**
	 *   uni_cpo_add_to_cart
	 */
	public static function uni_cpo_add_to_cart() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				ob_start();

				$form_data    = wc_clean( $_POST['data'] );
				$cart_item_id = $form_data['cpo_cart_item_id'];

				// cart item edit 'full' mode functionality
				$cart_content = WC()->cart->get_cart();
				if ( ! empty( $cart_content ) && ! empty( $cart_content[ $cart_item_id ] ) ) {
					$edited_data = $form_data;
					unset( $edited_data['cpo_cart_item_id'] );
					unset( $edited_data['quantity'] );
					$cart_content                                        = WC()->cart->get_cart_contents();
					$cart_content[ $cart_item_id ]['_cpo_product_image'] = $edited_data['cpo_product_image'];
					$cart_content[ $cart_item_id ]['_cpo_data']          = $edited_data;
					$cart_content[ $cart_item_id ]['quantity']           = $form_data['quantity'];

					WC()->cart->set_cart_contents( $cart_content );
					WC()->cart->calculate_totals();

					$url = get_permalink( wc_get_page_id( 'cart' ) );
					wp_send_json_success( array( 'redirect' => $url ) );
				}

				$product_id     = apply_filters( 'woocommerce_add_to_cart_product_id', $form_data['cpo_product_id'] );
				$quantity       = empty( $form_data['quantity'] ) ? 1 : wc_stock_amount( $form_data['quantity'] );
				$product_status = get_post_status( $product_id );

				do_action( 'uni_cpo_ajax_before_add_to_cart', $product_id );

				if ( false !== WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $form_data )
				     && 'publish' === $product_status ) {

					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						wc_add_to_cart_message( array( $product_id => $quantity ), true );
					}

					// Return fragments
					self::get_refreshed_fragments();

				} else {

					// If there was an error adding to the cart, redirect to the product page to show any errors
					$data = array(
						'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
					);

					wp_send_json_error( $data );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_price_calc
	 */
	public static function uni_cpo_price_calc() {
		try {
			$form_data = uni_cpo_clean( $_POST['data'] );

			if ( ! isset( $form_data['product_id'] ) ) {
				throw new Exception( __( 'Product ID is not set', 'uni-cpo' ) );
			}

			$product_id          = absint( $form_data['product_id'] );
			$product             = wc_get_product( $product_id );
			$product_data        = Uni_Cpo_Product::get_product_data_by_id( $product_id );
			$variables           = array();
			$price_vars          = array();
			$extra_data          = array( 'order_product' => 'enabled' );
			$is_calc_disabled    = false;
			$options_eval_result = array();
			$formatted_vars      = array();
			$errors_in_options   = array();
			$nice_names_vars     = array();
			$all_options_data    = array();
			$is_free_sample      = false;

			if ( unicpo_fs()->is__premium_only() ) {
				$qty_field_slug = $product_data['settings_data']['qty_field'];
				if ( $product->is_sold_individually() && 'wc' !== $qty_field_slug
				     && isset( $form_data[ $qty_field_slug ] ) && ! empty( absint( $form_data[ $qty_field_slug ] ) ) ) {
					$price_vars['quantity'] = $form_data[ $qty_field_slug ];
				} else {
					$price_vars['quantity'] = ( ! empty( $form_data['quantity'] ) )
						? absint( $form_data['quantity'] )
						: 1;
				}
			} else {
				$price_vars['quantity'] = ( ! empty( $form_data['quantity'] ) )
					? absint( $form_data['quantity'] )
					: 1;
			}

			$currency                    = get_woocommerce_currency();
			$price_vars['currency']      = get_woocommerce_currency_symbol( $currency );
			$price_vars['currency_code'] = $currency;

			if ( 'on' === $product_data['settings_data']['cpo_enable']
			     && 'on' === $product_data['settings_data']['calc_enable'] ) {

				$main_formula = $product_data['formula_data']['main_formula'];

				$filtered_form_data = array_filter(
					$form_data,
					function ( $k ) {
						return false !== strpos( $k, UniCpo()->get_var_slug() );
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
					function ( $v ) use ( &$variables, &$formatted_vars, &$nice_names_vars, &$errors_in_options, &$extra_data ) {
						foreach ( $v as $slug => $value ) {
							// prepare $variables for calculation purpose
							$variables[ '{' . $slug . '}' ] = $value['calc'];
							// prepare $formatted_vars for conditional logic purpose
							$formatted_vars[ $slug ] = $value['cart_meta'];
							// errors
							if ( isset( $value['error'] ) && ! empty( $value['error'] ) ) {
								$errors_in_options[ $slug ]  = $value['error'];
								$extra_data['order_product'] = 'disabled';
							}
						}
					}
				);

				$variables['{uni_cpo_price}'] = $product->get_price( 'edit' );

				if ( unicpo_fs()->is__premium_only() ) {
					if ( isset( $form_data['nbdFinalPrice'] ) ) {
						$variables['{uni_cpo_price}'] = $form_data['nbdFinalPrice'];
					}
				}

				$nice_names_vars['uni_cpo_price'] = $variables['{uni_cpo_price}'];

				// non option variables
				if ( 'on' === $product_data['nov_data']['nov_enable']
				     && ! empty( $product_data['nov_data']['nov'] )
				) {
					$variables = uni_cpo_process_formula_with_non_option_vars( $variables, $product_data, $formatted_vars );
				}

				if ( unicpo_fs()->is__premium_only() ) {
					$all_options_data = uni_cpo_get_options_data_for_frontend(
						$product_id,
						$variables,
						$formatted_vars
					);
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
							'uni_cpo_ajax_formula_before_formula_eval',
							$main_formula,
							$product_data,
							$product_id,
							$filtered_form_data
						);

						$variables = apply_filters(
							'uni_cpo_ajax_variables_before_formula_eval',
							$variables,
							$product_data,
							$product_id,
							$filtered_form_data
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
						$falsy_cart_item                    = false;
						$price_vars['raw_price_discounted'] = uni_cpo_aelia_price_convert( uni_cpo_apply_cart_discounts( $price_calculated, $product_data, $variables, $falsy_cart_item, $price_vars['quantity'] ) );
						$price_vars['price_discounted']     = uni_cpo_price( $price_vars['raw_price_discounted'] );
					}

					// check for max price
					if ( ! empty( $price_max ) && $price_calculated >= $price_max ) {
						$is_calc_disabled = true;
					}

					if ( ! $is_calc_disabled ) {

						// filter, so 3rd party scripts can hook up
						$price_calculated = apply_filters(
							'uni_cpo_ajax_calculated_price',
							$price_calculated,
							$product,
							$filtered_form_data
						);

						$price_display = wc_get_price_to_display(
							$product,
							array( 'qty' => 1, 'price' => $price_calculated )
						);

						if ( $product->is_taxable() ) {
							$price_display_tax_rev = uni_cpo_get_display_price_reversed( $product, $price_calculated );
							// Returns the price with suffix inc/excl tax opposite to one above
							$price_display_suffix = $product->get_price_suffix( $price_calculated, 1 );
						}

						$price_vars['price'] = apply_filters(
							'uni_cpo_ajax_calculation_price_tag_filter',
							uni_cpo_price( $price_display ),
							$price_display,
							$formatted_vars,
							$product_id
						);

						$price_vars['raw_price'] = $price_calculated;
						$price_vars['raw_total'] = $price_vars['raw_price'] * $price_vars['quantity'];
						$price_vars['total']     = uni_cpo_price( $price_vars['raw_total'] );
						if ( ! empty( $price_vars['raw_price_discounted'] ) ) {
							$price_vars['raw_total_discounted'] = $price_vars['raw_price_discounted'] * $price_vars['quantity'];
							$price_vars['total_discounted']     = uni_cpo_price( $price_vars['raw_total_discounted'] );
						}
						if ( $product->is_taxable() ) {
							$price_vars['raw_price_tax_rev'] = $price_display_tax_rev;
							$price_vars['raw_total_tax_rev'] = $price_vars['raw_price_tax_rev'] * $price_vars['quantity'];
							$price_vars['total_tax_rev']     = uni_cpo_price( $price_vars['raw_total_tax_rev'] );
						}

						// price and total with suffixes
						if ( $product->is_taxable() ) {

							// total with suffix
							// creates 'with suffix' value for total
							if ( get_option( 'woocommerce_prices_include_tax' ) === 'no'
							     && get_option( 'woocommerce_tax_display_shop' ) == 'incl' ) {
								$total_suffix = $product->get_price_suffix( $price_vars['raw_price_tax_rev'] * $price_vars['quantity'] );
							} elseif ( get_option( 'woocommerce_prices_include_tax' ) === 'yes'
							           && get_option( 'woocommerce_tax_display_shop' ) == 'incl' ) {
								$total_suffix = $product->get_price_suffix( $price_vars['raw_price'] * $price_vars['quantity'] );
							} elseif ( get_option( 'woocommerce_prices_include_tax' ) === 'no'
							           && get_option( 'woocommerce_tax_display_shop' ) == 'excl' ) {
								$total_suffix = $product->get_price_suffix( $price_vars['raw_price'] * $price_vars['quantity'] );
							} elseif ( get_option( 'woocommerce_prices_include_tax' ) === 'yes'
							           && get_option( 'woocommerce_tax_display_shop' ) == 'excl' ) {
								$total_suffix = $product->get_price_suffix( $price_vars['raw_price_tax_rev'] * $price_vars['quantity'] );
							}

							$price_vars['price_tax_suffix'] = $price_display_suffix;
							$price_vars['total_tax_suffix'] = $total_suffix;

						}

					} else {
						if ( $is_calc_disabled ) {  // ordering is disabled

							$price_display       = 0;
							$price_vars['price'] = apply_filters(
								'uni_cpo_ajax_calculation_price_tag_disabled_filter',
								uni_cpo_price( $price_display ),
								$price_display,
								$formatted_vars,
								$product_id
							);
							$extra_data          = apply_filters(
								'uni_cpo_ajax_calculation_price_extra_data_filter',
								array( 'order_product' => 'disabled' ),
								$formatted_vars,
								$product_id
							);

						}
					}

					$result['formatted_vars']   = $formatted_vars;
					$result['nice_names_vars']  = $nice_names_vars;
					$result['price_vars']       = apply_filters(
						'uni_cpo_ajax_calculation_price_vars_filter',
						$price_vars,
						$formatted_vars,
						$product_id
					);
					$result['extra_data']       = apply_filters(
						'uni_cpo_ajax_calculation_price_extra_data_filter',
						$extra_data,
						$formatted_vars,
						$product_id
					);
					$result['all_options_data'] = $all_options_data;
					$result['errors']           = $errors_in_options;

					wp_send_json_success( $result );

				} else {
					$price_display       = 0;
					$price_vars['price'] = apply_filters(
						'uni_cpo_ajax_calculation_price_tag_disabled_filter',
						uni_cpo_price( $price_display ),
						$price_display
					);
					$extra_data          = array( 'order_product' => 'disabled' );

					$result['formatted_vars']  = $formatted_vars;
					$result['nice_names_vars'] = $nice_names_vars;
					$result['price_vars']      = $price_vars;
					$result['extra_data']      = $extra_data;
					$result['errors']          = $errors_in_options;

					wp_send_json_success( $result );
				}

			} else {
				throw new Exception( __( 'Price calculation is disabled in settings', 'uni-cpo' ) );
			}

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_cart_item_edit
	 */
	public static function uni_cpo_cart_item_edit() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$cart_item_key = sanitize_text_field( $_POST['key'] );
				$product_id    = absint( $_POST['product_id'] );
				$hash          = sanitize_title( wp_hash_password( $cart_item_key . $product_id ) );
				$data          = array(
					'key'        => $cart_item_key,
					'product_id' => $product_id
				);

				if ( $product_id ) {
					// save data as post meta
					set_transient( '_cpo_cart_item_edit_' . $hash, $data, 3600 );

					// send user with a single GET param
					$url = add_query_arg( array(
						'cpo_cart_item_edit' => $hash
					), get_permalink( $product_id ) );

					wp_send_json_success( array( 'redirect' => $url ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_cart_item_edit_inline
	 */
	public static function uni_cpo_cart_item_edit_inline() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$cart_item_key = sanitize_text_field( $_POST['key'] );
				$cart_content  = WC()->cart->get_cart_contents();
				$edited_item   = $cart_content[ $cart_item_key ];
				$form_data     = $edited_item['_cpo_data'];
				$product_data  = Uni_Cpo_Product::get_product_data_by_id( $edited_item['product_id'] );
				$form_field    = '';
				$options_array = array();

				$filtered_form_data = array_filter(
					$form_data,
					function ( $k ) use ( $form_data ) {
						return false !== strpos( $k, UniCpo()->get_var_slug() ) && ! empty( $form_data[ $k ] );
					},
					ARRAY_FILTER_USE_KEY
				);

				array_walk(
					$product_data['content'],
					function ( $row, $row_key ) use ( &$options_array ) {
						if ( is_array( $row['columns'] ) && ! empty( $row['columns'] ) ) {
							array_walk(
								$row['columns'],
								function ( $column, $column_key ) use ( &$options_array, $row_key ) {
									if ( is_array( $column['modules'] ) && ! empty( $column['modules'] ) ) {
										array_walk(
											$column['modules'],
											function ( $module ) use ( &$options_array, $row_key, $column_key ) {
												if ( isset( $module['pid'] ) && 'option' === $module['obj_type'] ) {
													$options_array[ $module['pid'] ] = $module;
												}
											}
										);
									}
								}
							);
						}
					}
				);

				if ( ! empty( $filtered_form_data ) ) {
					$posts = uni_cpo_get_posts_by_slugs( array_keys( $filtered_form_data ) );
					if ( ! empty( $posts ) ) {
						$posts_ids = wp_list_pluck( $posts, 'ID' );
						foreach ( $posts_ids as $post_id ) {
							$option = uni_cpo_get_option( $post_id );

							if ( $option->cpo_order_visibility() ) {
								continue;
							}

							$post_name = trim( $option->get_slug(), '{}' );
							if ( is_object( $option ) && 'trash' !== $option->get_status() ) {
								$form_field .= $option->get_edit_field( $options_array[ $option->get_id() ], $form_data[ $post_name ] );

							}
						}
					}
				}

				if ( $form_field ) {
					wp_send_json_success( $form_field );
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_cart_item_update_inline
	 */
	public static function uni_cpo_cart_item_update_inline() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$cart_item_key                               = sanitize_text_field( $_POST['key'] );
				$data                                        = wc_clean( $_POST['data'] );
				$cart_content                                = WC()->cart->get_cart_contents();
				$original_data                               = $cart_content[ $cart_item_key ]['_cpo_data'];
				$cart_content[ $cart_item_key ]['_cpo_data'] = $data;

				WC()->cart->set_cart_contents( $cart_content );

				if ( unicpo_fs()->is__premium_only() ) {
					// exclude option chosen as qty field
					$product_id             = $cart_content[ $cart_item_key ]['product_id'];
					$product_data           = Uni_Cpo_Product::get_product_data_by_id( $product_id );
					$qty_field_slug         = $product_data['settings_data']['qty_field'];
					$is_sold_individually   = $product_data['settings_data']['sold_individually'];
					$plugin_settings        = UniCpo()->get_settings();
					$is_free_sample_enabled = $plugin_settings['free_sample_enable'];
					$free_samples_limit     = ( isset( $plugin_settings['free_samples_limit'] ) )
						? absint( $plugin_settings['free_samples_limit'] )
						: 0;
					$is_found               = false;

					if ( 'wc' !== $qty_field_slug && ! empty( absint( $data[ $qty_field_slug ] ) ) ) {
						WC()->cart->set_quantity( $cart_item_key, absint( $data[ $qty_field_slug ] ), false );
					}

					if ( 'on' === $is_sold_individually ) {
						$is_found = uni_cpo_find_product_in_cart( $product_id );

						if ( $is_found ) {
							throw new Exception( sprintf( __( 'You cannot add another "%s" to your cart.', 'uni-cpo' ), $cart_content[ $cart_item_key ]['data']->get_name() ) );
						}
					}

					if ( isset( $cart_content[ $cart_item_key ]['_cpo_is_free_sample'] ) && ! empty( $is_free_sample_enabled ) && 'on' === $is_free_sample_enabled ) {
						$is_found = uni_cpo_find_product_in_cart( $product_id, true, $free_samples_limit );

						if ( $is_found ) {
							throw new Exception( sprintf( __( 'The total number of samples in the cart is limited to %d', 'uni-cpo' ), $free_samples_limit ) );
						}
					}

					if ( ! $is_found && 'wc' !== $qty_field_slug && ! empty( absint( $data[ $qty_field_slug ] ) ) ) {
						WC()->cart->set_quantity( $cart_item_key, absint( $data[ $qty_field_slug ] ), false );
					}
				}

				WC()->cart->calculate_totals();

				$referer = wp_get_referer() ? remove_query_arg( array(
					'cpo_duplicate_cart_item',
					'add-to-cart',
					'added-to-cart',
					'cpo_edited_cart_item'
				), add_query_arg( 'cpo_edited_cart_item', '1', wp_get_referer() ) ) : wc_get_cart_url();
				wp_safe_redirect( $referer );
				exit;

			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

				return false;
			}
		}
	}

	/**
	 *   uni_cpo_order_item_edit
	 */
	public static function uni_cpo_order_item_edit() {
		try {
			check_ajax_referer( 'order-item', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$product_id    = absint( $_POST['product_id'] );
			$order_item_id = absint( $_POST['order_item_id'] );

			$product_data  = Uni_Cpo_Product::get_product_data_by_id( $product_id );
			$item          = new WC_Order_Item_Product( $order_item_id );
			$form_data     = uni_cpo_re_add_cpo_item_data( array(), $item->get_meta_data(), $product_data );
			$form_field    = '';
			$options_array = array();

			$filtered_form_data = array_filter(
				$form_data['_cpo_data'],
				function ( $k ) use ( $form_data ) {
					return false !== strpos( $k, UniCpo()->get_var_slug() )
					       && isset( $form_data['_cpo_data'] ) && ! empty( $form_data['_cpo_data'][ $k ] );
				},
				ARRAY_FILTER_USE_KEY
			);

			array_walk(
				$product_data['content'],
				function ( $row, $row_key ) use ( &$options_array ) {
					if ( is_array( $row['columns'] ) && ! empty( $row['columns'] ) ) {
						array_walk(
							$row['columns'],
							function ( $column, $column_key ) use ( &$options_array, $row_key ) {
								if ( is_array( $column['modules'] ) && ! empty( $column['modules'] ) ) {
									array_walk(
										$column['modules'],
										function ( $module ) use ( &$options_array, $row_key, $column_key ) {
											if ( isset( $module['pid'] ) && 'option' === $module['obj_type'] ) {
												$options_array[ $module['pid'] ] = $module;
											}
										}
									);
								}
							}
						);
					}
				}
			);

			if ( ! empty( $options_array ) ) {
				$posts = uni_cpo_get_posts_by_ids( array_keys( $options_array ) );
				if ( ! empty( $posts ) ) {
					$posts_ids = wp_list_pluck( $posts, 'ID' );
					foreach ( $posts_ids as $post_id ) {
						$option    = uni_cpo_get_option( $post_id );
						$post_name = trim( $option->get_slug(), '{}' );
						if ( is_object( $option ) && 'trash' !== $option->get_status() ) {
							$field_value = ( isset( $filtered_form_data[ $post_name ] ) ) ? $filtered_form_data[ $post_name ] : '';
							$form_field  .= $option->get_edit_field( $options_array[ $option->get_id() ], $field_value, 'order' );

						}
					}
				}

				wp_send_json_success( $form_field );
			}

			wp_send_json_error( array( 'error' => __( 'No options available', 'uni-cpo' ) ) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_order_item_update
	 */
	public static function uni_cpo_order_item_update() {
		try {
			check_ajax_referer( 'order-item', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				throw new Exception( __( 'Insufficient permissions for this operation', 'uni-cpo' ) );
			}

			$form_data  = wc_clean( $_POST );
			$product_id = $form_data['product_id'];
			$order      = wc_get_order( $form_data['order_id'] ); // is used in the template below
			$item_id    = absint( $form_data['order_item_id'] );
			$item       = new WC_Order_Item_Product( $item_id );

			unset( $form_data['product_id'] );
			unset( $form_data['order_item_id'] );
			unset( $form_data['security'] );
			unset( $form_data['order_id'] );
			unset( $form_data['action'] );
			unset( $form_data['dataType'] );

			$meta_data      = $item->get_meta_data();
			$formatted_meta = array();
			if ( ! empty ( $meta_data ) ) {
				foreach ( $meta_data as $key => $meta_data_item ) {
					$meta                           = $meta_data_item->get_data();
					$formatted_meta[ $meta['key'] ] = $meta['id'];

					if ( unicpo_fs()->is__premium_only() ) {
						if ( $meta['key'] === '_nbdFinalPrice' ) {
							$cart_item_data['nbo_meta']['price'] = $meta['value'];
						}
					}
				}
			}

			if ( ! empty ( $form_data ) ) {
				foreach ( $form_data as $key => $value ) {
					if ( isset( $formatted_meta[ '_' . $key ] ) ) {
						$item->update_meta_data( '_' . $key, $value, $formatted_meta[ '_' . $key ] );
						unset( $formatted_meta[ '_' . $key ] );
					} else {
						$item->add_meta_data( '_' . $key, $value );
					}
				}

				$cart_item_data['_cpo_data'] = $form_data;
				$item_price                  = uni_cpo_calculate_price_in_cart( $cart_item_data, $product_id );

				if ( unicpo_fs()->is__premium_only() ) {
					if ( isset( $cart_item_data['_cpo_nov'] ) ) {
						foreach ( $cart_item_data['_cpo_nov'] as $k => $v ) {
							if ( isset( $formatted_meta[ '_' . $k ] ) ) {
								$item->update_meta_data( '_' . $k, $v['value'], $formatted_meta[ '_' . $key ] );
								unset( $formatted_meta[ '_' . $k ] );
							} else {
								$item->add_meta_data( '_' . $k, $v['value'] );
							}
						}
					}
				}

				if ( ! empty( $formatted_meta ) ) {
					foreach ( $formatted_meta as $k => $v ) {
						if ( false !== strpos( $k, UniCpo()->get_var_slug() ) ) {
							$item->delete_meta_data( $k );
						}
					}
				}

				$item_qty = 0;

				if ( unicpo_fs()->is__premium_only() ) {
					// exclude option chosen as qty field
					$product_data   = Uni_Cpo_Product::get_product_data_by_id( $product_id );
					$qty_field_slug = $product_data['settings_data']['qty_field'];

					if ( 'wc' !== $qty_field_slug && isset( $form_data[ $qty_field_slug ] )
					     && ! empty( absint( $form_data[ $qty_field_slug ] ) ) ) {
						$item_qty = absint( $form_data[ $qty_field_slug ] );
						$item->set_quantity( $item_qty );
					} else {
						$item_qty = $item->get_quantity();
					}
				} else {
					$item_qty = $item->get_quantity();
				}

				$item->set_subtotal( $item_price );
				$item_total = $item_qty * $item_price;
				$item->set_total( $item_total );
				$item->calculate_taxes();

				$item->save();
			}

			ob_start();
			include( wp_normalize_path( WP_PLUGIN_DIR . '/woocommerce/includes/admin/meta-boxes/views/html-order-item.php' ) );
			$html = ob_get_clean();
			wp_send_json_success( array(
				'html' => $html
			) );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 *   uni_cpo_product_settings_export
	 */
	public static function uni_cpo_product_settings_export() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				$product_id = absint( $_POST['pid'] );
				$user_email = sanitize_email( $_POST['email'] );

				if ( ! isset( $product_id ) ) {
					throw new Exception( __( 'Product ID is not set', 'uni-cpo' ) );
				}

				if ( ! isset( $user_email ) ) {
					throw new Exception( __( 'Email is not set', 'uni-cpo' ) );
				}

				$cpo_temp_dir         = UNI_CPO_TEMP_DIR;
				$builder_content_file = $cpo_temp_dir . 'builder_content_for_' . $product_id . '.txt';
				//$product_settings_file = UNI_CPO_TEMP_DIR . 'product_settings_for_' . $product_id;
				$product_settings = Uni_Cpo_Product::get_product_data_by_id( $product_id );
				$product_settings = uni_cpo_encode( $product_settings );

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}

				WP_Filesystem();
				global $wp_filesystem;

				if ( ! is_dir( $cpo_temp_dir ) ) {
					$wp_filesystem->mkdir( $cpo_temp_dir );
				}
				$result = $wp_filesystem->put_contents( $builder_content_file, $product_settings, 0644 );

				if ( ! $result ) {
					throw new Exception( __( 'File cannot be saved', 'uni-cpo' ) );
				}

				$subject = sprintf( __( '[%s] Export request for product #%d', 'uni-cpo' ), get_bloginfo( 'name' ), $product_id );
				$body    = sprintf( __( 'A .txt file with encoded info of settings for product #%d is attached to this email. The info contains the builder content as well as all the product settings added via CPO plugin for this product. Use this file to re-import the settings elsewhere.', 'uni-cpo' ), $product_id );
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );

				$mail_result = wp_mail( $user_email, $subject, $body, $headers, array( $builder_content_file ) );

				if ( $result && $mail_result ) {
					@ unlink( $builder_content_file );
					wp_send_json_success();
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_product_settings_import
	 */
	public static function uni_cpo_product_settings_import() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				check_ajax_referer( 'uni_cpo_builder', 'security' );

				$file          = $_FILES['file'];
				$product_id    = absint( $_POST['pid'] );
				$is_remove_pid = ( 'true' === wc_clean( $_POST['is_remove_pid'] ) ) ? true : false;

				if ( ! isset( $product_id ) ) {
					throw new Exception( __( 'Product ID is not set', 'uni-cpo' ) );
				}

				// MIME type check
				$wp_filetype = wp_check_filetype_and_ext( $file, $file['name'] );

				$ext  = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
				$type = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];

				if ( 'text/plain' !== $type || 'txt' !== $ext ) {
					throw new Exception( __( 'Sorry, this file type is not permitted for security reasons.', 'uni-cpo' ) );
				}

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}

				WP_Filesystem();
				global $wp_filesystem;
				$content = $wp_filesystem->get_contents( $file['tmp_name'] );

				if ( ! $content ) {
					throw new Exception( __( 'File cannot be read', 'uni-cpo' ) );
				}

				$content = uni_cpo_decode( $content );

				unset( $content['id'] );
				$content['product_id'] = $product_id;
				unset( $content['post_thumb_id'] );
				unset( $content['uri'] );

				// TODO
				$should_update_urls = true;

				if ( $is_remove_pid || $should_update_urls ) {
					$builder_content = array();

					if ( is_array( $content['content'] ) && ! empty( $content['content'] ) ) {
						array_walk(
							$content['content'],
							function ( $row, $row_key ) use ( &$builder_content, $is_remove_pid, $should_update_urls ) {
								$row['pid']                  = '';
								$builder_content[ $row_key ] = $row;
								if ( is_array( $row['columns'] ) && ! empty( $row['columns'] ) ) {
									array_walk(
										$row['columns'],
										function ( $column, $column_key ) use ( &$builder_content, $row_key, $is_remove_pid, $should_update_urls ) {
											$column['pid']                                         = '';
											$builder_content[ $row_key ]['columns'][ $column_key ] = $column;
											if ( is_array( $column['modules'] ) && ! empty( $column['modules'] ) ) {
												array_walk(
													$column['modules'],
													function ( $module, $module_key ) use ( &$builder_content, $row_key, $column_key, $is_remove_pid, $should_update_urls ) {
														if ( $is_remove_pid ) {
															$module['pid'] = '';
														} else {
															if ( isset( $module['settings']['cpo_general']['main']['cpo_slug'] )
															     && ! empty( $module['settings']['cpo_general']['main']['cpo_slug'] ) ) {
																$post = uni_cpo_get_post_by_slug( 'uni_cpo_' . $module['settings']['cpo_general']['main']['cpo_slug'] );

																if ( isset( $post->ID ) ) {
																	$moduleObj = uni_cpo_get_option( $post->ID );
																	if ( $moduleObj && $module['type'] === $moduleObj->get_type() ) {
																		$module['pid'] = $post->ID;
																	}
																}
															}
														}

														if ( $should_update_urls ) {
															if ( isset( $module['settings']['cpo_suboptions']['data'] ) ) {
																foreach ( $module['settings']['cpo_suboptions']['data'] as $key => $sub_option ) {
																	$module['settings']['cpo_suboptions']['data'][ $key ] = array_map(
																		function ( $item ) {
																			if ( ! empty( $item['attach_uri'] ) ) {
																				$post_id = get_attachment_by_url( $item['attach_uri'] );

																				if ( $post_id ) {
																					$img_data            = wp_get_attachment_image_src( $post_id, 'full' );
																					$item['attach_name'] = basename( $item['attach_uri'] );
																					$item['attach_id']   = $post_id;
																					$item['attach_uri']  = isset( $img_data[0] ) ? $img_data[0] : '';
																				}
																			}
																			if ( ! empty( $item['attach_uri_r'] ) ) {
																				$post_id = get_attachment_by_url( $item['attach_uri_r'] );

																				if ( $post_id ) {
																					$img_data             = wp_get_attachment_image_src( $post_id, 'full' );
																					$item['attach_id_r']  = $post_id;
																					$item['attach_uri_r'] = isset( $img_data[0] ) ? $img_data[0] : '';
																				}
																			}

																			return $item;
																		},
																		$sub_option
																	);
																}
															}
														}

														$builder_content[ $row_key ]['columns'][ $column_key ]['modules'][ $module_key ] = $module;
													}
												);
											}
										}
									);
								}
							}
						);
					}
				} else {
					$builder_content = $content['content'];
				}

				unset( $content['content'] );

				$content_saved  = Uni_Cpo_Product::save_content( $product_id, $builder_content, false );
				$settings_saved = Uni_Cpo_Product::save_product_data( $content );

				if ( $content_saved && $settings_saved ) {
					wp_send_json_success();
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_exim_view
	 */
	public static function uni_cpo_exim_view() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$oid = absint( $_POST['oid'] );

				if ( ! isset( $oid ) ) {
					throw new Exception( __( 'No option chosen', 'uni-cpo' ) );
				}

				$suboptions = get_post_meta( $oid, '_cpo_suboptions', true );

				if ( $suboptions ) {
					wp_send_json_success( $suboptions );
				} else {
					wp_send_json_error( array( 'error' => __( 'No suboptions yet', 'uni-cpo' ) ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_exim_import
	 */
	public static function uni_cpo_exim_import() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$oid = absint( $_POST['oid'] );

				if ( ! isset( $oid ) ) {
					throw new Exception( __( 'No option chosen', 'uni-cpo' ) );
				}

				if ( ! isset( $_FILES['file']['tmp_name'] ) ) {
					throw new Exception( __( 'No files added', 'uni-cpo' ) );
				}

				if ( ! in_array(
					$_FILES['file']['type'],
					array(
						'application/vnd.ms-excel',
						'text/csv',
						'application/octet-stream',
						'text/comma-separated-values',
						'application/csv',
						'application/excel',
						'application/vnd.msexcel',
						'text/anytext'
					)
				)
				) {
					throw new Exception( __( 'Only CSV files are allowed', 'uni-cpo' ) );
				}

				$option      = uni_cpo_get_option( $oid );
				$option_type = $option::get_type();

				if ( $option_type === 'select' ) {
					$suboptions_type = 'cpo_select_options';
				} else {
					$suboptions_type = 'cpo_radio_options';
				}

				$file = uni_cpo_clean( $_FILES['file']['tmp_name'] );
				$data = uni_cpo_import_csv_data( $file );

				$csv_data                                 = uni_cpo_format_csv_suboptions_data( $data, $suboptions_type, $option_type );
				$data_to_save['data'][ $suboptions_type ] = $csv_data;

				if ( $csv_data ) {
					update_post_meta( $oid, '_cpo_suboptions', $data_to_save );
					wp_send_json_success();
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_exim_export
	 */
	public static function uni_cpo_exim_export() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$oid = absint( $_POST['oid'] );

				if ( ! isset( $oid ) ) {
					throw new Exception( __( 'No option chosen', 'uni-cpo' ) );
				}

				$option      = uni_cpo_get_option( $oid );
				$option_type = $option::get_type();

				if ( $option_type === 'select' ) {
					$suboptions_type = 'cpo_select_options';
				} else {
					$suboptions_type = 'cpo_radio_options';
				}

				$suboptions_meta = get_post_meta( $oid, '_cpo_suboptions', true );
				$suboptions      = isset( $suboptions_meta['data'][ $suboptions_type ] )
					? $suboptions_meta['data'][ $suboptions_type ]
					: array();
				$plugin_settings = UniCpo()->get_settings();
				$delimiter       = $plugin_settings['csv_delimiter'];

				if ( $suboptions ) {
					$metadata = array_keys( $suboptions[0] );
					$final[]  = $metadata;
					foreach ( $suboptions as $data ) {
						$final[] = array_values( $data );
					}

					wp_send_json_success(
						array( 'data' => $final, 'delimiter' => $delimiter )
					);
				} else {
					wp_send_json_error( array( 'error' => __( 'Something went wrong', 'uni-cpo' ) ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

	/**
	 *   uni_cpo_prods_data_update
	 */
	public static function uni_cpo_prods_data_update() {
		if ( unicpo_fs()->is__premium_only() ) {
			try {
				$data = json_decode( stripslashes_deep( $_POST['data'] ), true );

				if ( ! empty( $data ) ) {
					foreach ( $data as $post_id => $settings ) {
						$product_data                    = Uni_Cpo_Product::get_product_data_by_id( $post_id );
						$final_settings                  = [
							'product_id' => $post_id,
						];
						$final_settings                  = array_merge( $final_settings, $settings );
						$final_settings['settings_data'] = empty( $final_settings['settings_data'] )
							? $product_data['settings_data']
							: array_merge(
								$product_data['settings_data'],
								$final_settings['settings_data']
							);

						$final_settings['formula_data'] = empty( $final_settings['formula_data'] )
							? $product_data['formula_data']
							: array_merge(
								$product_data['formula_data'],
								$final_settings['formula_data']
							);

						$final_settings['weight_data'] = empty( $final_settings['weight_data'] )
							? $product_data['weight_data']
							: array_merge(
								$product_data['weight_data'],
								$final_settings['weight_data']
							);

						$final_settings['discounts_data']  = $product_data['discounts_data'];
						$final_settings['image_data']      = $product_data['image_data'];
						$final_settings['dimensions_data'] = $product_data['dimensions_data'];
						$final_settings['nov_data']        = $product_data['nov_data'];

						Uni_Cpo_Product::save_product_data( $final_settings );
					}
				}

				wp_send_json_success();
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) );
			}
		}
	}

}

Uni_Cpo_Ajax::init();
