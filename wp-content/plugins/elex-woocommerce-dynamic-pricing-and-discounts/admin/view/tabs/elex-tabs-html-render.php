<h2><?php esc_attr_e( 'Dynamic Pricing Main Page', 'eh-dynamic-pricing-discounts' ); ?></h2>
<script>
	jQuery( function () {
		jQuery( '.woocommerce-help-tip' ).tipTip( {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		} );
		jQuery( '.roles_select' ).select2();
		jQuery( '#eh_rule_form' ).on( 'submit', function () {
			const min = parseFloat( jQuery( '#min' ).val() );
			const max = parseFloat( jQuery( '#max' ).val() );
			const value = parseFloat( jQuery( '#value' ).val() );
			if ( min <= 0 ) {
				alert( 'The Value of ' + jQuery( "[for=min]" ).text() + ' should be greater than zero' );
			} else if ( max < min ) {
				alert( 'The Value of ' + jQuery( "[for=max]" ).text() + ' should be greater than ' + jQuery( "[for=min]" ).text() );
			} else if ( value <= 0 ) {
				alert( 'The Value of ' + jQuery( "[for=value]" ).text() + ' should be greater than zero' );

			} else {
				return true;
			}
			return false;
		} );
		jQuery( 'tbody' ).on( 'click','button.deletebtn',function(){
			var r = confirm( "You are deleting rule no " + jQuery( this ).val() );
			if ( r != true ) {
				return false;
			}
		} );                             
		jQuery( '#rule_tab' ).on( 'click','#deletebtn,#freedeletebtn',function(){
			if( jQuery( this ).parent().parent().parent().parent().find( 'tbody>tr' ).size()>1 ) {
			jQuery( this ).parent().parent().parent().parent().find( 'tbody>tr:last-child' ).remove();
			}else {
				alert( 'At least one row required' );
			}
		} );
	} );
	function select( obj, selector ) {
		let all_links = document.querySelectorAll( '.active' );
		for ( let dv of all_links ) {
			dv.classList.remove( 'active' );
		}
		obj.parentElement.className += ' active';
		let div = document.querySelector( selector );
		let alldiv = document.querySelectorAll( '.options_group' );
		for ( let dv of alldiv ) {
			dv.style.display = 'none';
		}
		if ( div.style.display == "none" ) {
			div.style.display = "block";
		} else {
			div.style.display = "none";
		}
	}
</script>
<style>
	.xa_link{
			cursor: pointer;
	}
	.woocommerce_options_panel label{
		width:210px;
	}
	#tiptip_content{
		max-width: 500px;
		width:auto;
	}

	.deletebtn{
		background: url( <?php echo esc_html( ELEX_DP_MAIN_URL_PATH . '/img/del.png' ); ?> ) 10px 10px no-repeat;
		width: 15px;
		height: 15px;
		background-size: 100%;
		background-position: top left;
		border: none;
		margin-left:10px;
		cursor:pointer;
	}
	.editbtn{
		background: url( <?php echo esc_html( ELEX_DP_MAIN_URL_PATH . '/img/edit.png' ); ?> ) 10px 10px no-repeat;
		width: 15px;
		height: 15px;
		background-size: 100%;
		background-position: top left;
		border: none;
		margin-left:10px;
		cursor:pointer;
	}
	.add_new{
		/*    background: url( http://localhost/wordpress/wp-content/plugins/eh-dynamic-pricing-discounts/img/add_new.png ) 10px 10px no-repeat;
		  width: 50px;
		  height: 50px;
		  background-size: 100%;
		  background-position: top left;
		  border: none;
		  margin-left:10px;
		  cursor:pointer; */
	}
</style>
<div class = "wrap" style = "margin: auto;">
	<?php
	$rule_tab = false;
	global $on_plugin_page;
	global $thepostid;
	$thepostid = 99999999999;
	$on_plugin_page = true;
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) {
		$active_tab = sanitize_text_field( $_REQUEST['tab'] );
	} else {
		$active_tab = 'No Rules Selected';
	}

	if ( ! empty( $_REQUEST['deletesuccess'] ) ) {
		echo '<div class="notice notice-warning inline is-dismissible"><p></br><lable>Deleted Successfully !!</p></div>';
	}
	$settings = get_option( 'xa_dynamic_pricing_setting' );
	$execution_order = isset( $settings['execution_order'] ) ? $settings['execution_order'] : array(
		'product_rules',
		'combinational_rules',
		'cat_combinational_rules',
		'category_rules',
		'cart_rules',
		'buy_get_free_rules',
		'BOGO_category_rules',
		'tag_rules',
		'bogo_tag_rules',
	);
	if ( in_array( $active_tab, $execution_order ) ) {
		$rule_tab = true;
	}
	if ( 'No Rules Selected' == $active_tab && ! empty( $settings['execution_order'] ) ) {
		$active_tab = current( $settings['execution_order'] );
		$rule_tab = true;
	}
	if ( ! empty( $_REQUEST['delete'] ) && isset( $_REQUEST['tab'] ) ) {
		$prev_data = get_option( 'xa_dp_rules' );
		unset( $prev_data[ $active_tab ][ $_REQUEST['delete'] ] );
		update_option( 'xa_dp_rules', $prev_data );
		wp_redirect( admin_url( 'admin.php?page=dynamic-pricing-main-page&tab=' . sanitize_text_field( $_REQUEST['tab'] ) . '&deletesuccess' ) );
	}
	//add_thickbox();
	?>
	<h2 class="nav-tab-wrapper">        
		<a href="?page=dynamic-pricing-main-page<?php echo ! empty( $execution_order ) ? '&tab=' . esc_html( current( $execution_order ) ) : ''; ?>" class="nav-tab <?php echo true == $rule_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Discount Rules', 'eh-dynamic-pricing-discounts' ); ?></a>
		<a href="?page=dynamic-pricing-main-page&tab=settings_page" class="nav-tab <?php echo 'settings_page' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'eh-dynamic-pricing-discounts' ); ?></a>
	<!--        <a href="?page=dynamic-pricing-main-page&tab=licence" class="nav-tab  thickbox <?php //echo $active_tab == 'import_export' ? 'nav-tab-active' : ''; ?>"><?php //_e( 'Import/Export', 'eh-dynamic-pricing-discounts' ); ?></a>  -->
		<a href="?page=dynamic-pricing-main-page&tab=licence" style="color:red;" class="nav-tab <?php echo 'licence' == $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Go Premium', 'eh-dynamic-pricing-discounts' ); ?></a>
		
	</h2>
	<div class="clear"></div>
	</br>
		<?php
		if ( ! empty( $rule_tab ) && $rule_tab ) {
			?>
			<ul class="subsubsub">
				<?php
				foreach ( $execution_order as $key => $tabkey ) {
					switch ( $tabkey ) {
						case 'product_rules':
							$tab_name = esc_html__( 'Product Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'combinational_rules':
							$tab_name = esc_html__( 'Combi Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'category_rules':
							$tab_name = esc_html__( 'Category Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'cat_combinational_rules':
							$tab_name = esc_html__( 'Category Combi Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'cart_rules':
							$tab_name = esc_html__( 'Cart Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'buy_get_free_rules':
							$tab_name = esc_html__( 'BOGO Rules', 'eh-dynamic-pricing-discounts' );
							break;
						case 'BOGO_category_rules':
							$tab_name = esc_html__( 'BOGO Category Rules', 'eh-dynamic-pricing-discounts' );
							break;
						default:
							break;
					}
					$active_class = '';
					if ( $active_tab == $tabkey ) {
						$active_class = 'current';
					}
					$delimiter = ( $key < count( $execution_order ) - 1 ) ? '  |  ' : ' ';
					echo '<li>';
					echo '<a href="?page=dynamic-pricing-main-page&tab=' . esc_html( $tabkey ) . '" class="' . esc_html( $active_class ) . '" >' . esc_html( $tab_name ) . '</a>' . esc_html( $delimiter );
					echo '</li>';
				}
				?>
			</ul>
			<?php
		} elseif ( 'No Rules Selected' == $active_tab ) {
			echo "</br></br>No Rules Available!! .  <a href='admin.php?page=dynamic-pricing-main-page&tab=settings_page' >Go to Settings Page and Enable Rules </a></br></r>";
		}
		?>
	<br class="clear">
	<style>
		.super{
			vertical-align: super;
			font-size:x-small;
			}
	</style>
	<div id="col-container2">
		<div class="col-wrap">
			<div class="inside">
				<div style="">
					<button style="margin: 10px 0px;
						<?php
						if ( ! empty( $_REQUEST['edit'] ) || in_array( $active_tab, array( 'settings_page', 'import_export', 'licence', 'No Rules Selected' ) ) ) {
							echo 'display:none;';
							echo esc_html( $active_tab );
						}
						?>
						" class="add_new add_new_rule_btn button button-primary" onclick="elex_dp_show_form()" >Add New Rule
					</button> 
				</div>
				<form method="get" action="" id="eh_rule_form" style=
				<?php
				if ( ! ( ! empty( $_REQUEST['edit'] ) || isset( $_REQUEST['update'] ) || 'settings_page' == $active_tab || 'import_export' == $active_tab || ! $rule_tab || 'licence' == $active_tab ) ) {
					echo '"display:none"';
				}
				?>
					  >
					<?php
					if ( isset( $_REQUEST['edit'] ) ) {
						wp_nonce_field( 'update_rule_' . sanitize_text_field( $_REQUEST['edit'] ) );
					} else {
						wp_nonce_field( 'save_rule' );
					}
					if ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) {
						$dp_tab = sanitize_text_field( $_REQUEST['tab'] );
					} else {
						$dp_tab = $active_tab;
					}
					?>
					<input type="hidden" id="page" name="page" value="dynamic-pricing-main-page">
					<input type="hidden" id="tab" name="tab" value="<?php echo esc_html( $dp_tab ); ?>">

					<?php
					do_action( 'my_admin_notification' );

					if ( 'category_rules' == $active_tab ) {
						require( 'selected-tab-content/elex-category-rule-tab.php' );
					} elseif ( 'settings_page' == $active_tab ) {
						require( 'selected-tab-content/elex-settings-page-tab.php' );
					} elseif ( 'import_export' == $active_tab ) {
						require( 'selected-tab-content/import_export-tab.php' );
					} elseif ( 'licence' == $active_tab ) {
						require( 'market.php' );
					} elseif ( 'product_rules' == $active_tab ) {    // product rule tab
						require( 'selected-tab-content/elex-product-rule-tab.php' );
					}
					if ( 'import_export' == $active_tab && ! empty( $_REQUEST['action'] ) && 'delete_all_rules' == sanitize_text_field( $_REQUEST['action'] ) ) {
						if ( is_admin() ) {
							$dummy_settings['product_rules'] = array();
							$dummy_settings['combinational_rules'] = array();
							$dummy_settings['cat_combinational_rules'] = array();
							$dummy_settings['category_rules'] = array();
							$dummy_settings['cart_rules'] = array();
							$dummy_settings['buy_get_free_rules'] = array();
							$dummy_settings['BOGO_category_rules'] = array();
							update_option( 'xa_dp_rules', $dummy_settings );
							wp_redirect( admin_url( 'admin.php?page=dynamic-pricing-main-page&tab=import_export&deleted' ) );
						}
					}
					if ( 'import_export' != $active_tab && $rule_tab ) {
						?>
						<p class="submit" style="
						<?php
						if ( 'settings_page' == $active_tab ) {
							echo 'display:none;';
						}
						?>
						">
							<button type="submit" name="update" id="update" class="button button-primary" value="<?php echo ! empty( $_REQUEST['edit'] ) ? esc_html( sanitize_text_field( $_REQUEST['edit'] ) ) : ''; ?>"> 
										<?php
										if ( isset( $_REQUEST['edit'] ) ) {
											esc_html_e( 'Update Rule', 'eh-dynamic-pricing-discounts' );
										} else {
											esc_html_e( 'Save Rule', 'eh-dynamic-pricing-discounts' );
										}
										?>
							</button>
						<?php
					}

					if ( ! in_array( $active_tab, array( 'settings_page', 'import_export', 'licence', 'No Rules Selected' ) ) ) {
						?>
						<button name="cancel_btn" style="
						<?php
						if ( empty( $_REQUEST['edit'] ) ) {
							echo 'display:none;';
						}
						?>
						" id="cancel_btn" class="button button-primary"
						<?php
						if ( ! isset( $_REQUEST['edit'] ) ) {
							echo ' onclick="return elex_dp_hide_rule_form()" ';
						}
						?>
						>Cancel</button>
						<?php
					}
					?>
					</p>
				</form>

				<form name="alter_display_form" method="get" action="">			<!--Displays Table List Of Saved Rules--->
					<input type="hidden" name="page" value="dynamic-pricing-main-page">
					<?php
					if ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) {
						$dp_tab = sanitize_text_field( $_REQUEST['tab'] );
					} else {
						$dp_tab = $active_tab;
					}
					?>
					<input type="hidden" id="tab" name="tab" value="<?php echo esc_html( $dp_tab ); ?>">
				<?php
				if ( ! isset( $_REQUEST['tab'] ) ) {
					$_REQUEST['tab'] = $active_tab;
				}
				if ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) {
					if ( file_exists( plugin_dir_path( __FILE__ ) . '/selected-tab-content/elex-display-saved-' . sanitize_text_field( $_REQUEST['tab'] ) . '-table.php' ) === true ) {
						include( 'selected-tab-content/elex-display-saved-' . sanitize_text_field( $_REQUEST['tab'] ) . '-table.php' );
					}
				}
				?>
				</form>

			</div>
		</div>
	</div>
</div>
<script>
function elex_dp_show_form()
{
	jQuery( '#eh_rule_form' ).show();
	jQuery( '.add_new_rule_btn' ).hide();
	jQuery( '#cancel_btn' ).show();        
	return false;
}
function elex_dp_hide_rule_form()
{
	jQuery( '#eh_rule_form' ).hide();
	jQuery( '.add_new_rule_btn' ).show();
	jQuery( '#update' ).val( '' );
	jQuery( '#update' ).text( 'Save Rule' );

	return false;
}
jQuery( document ).ready( function ()
{
	jQuery( ".date-picker" ).datepicker( {
		dateFormat: "dd-mm-yy 00:00",minDate: 0
	} );
	jQuery( '#more_options' ).on( 'click', function () {
		jQuery( '.more_options' ).toggle();
		if ( jQuery( '.more_options' ).is( ":hidden" ) )
		{
			jQuery( '#more_options' ).html( '<h4><a>More Options+</a></h4>' );
		} else
		{
			jQuery( '#more_options' ).html( '<h4><a>More Options-</a></h4>' );
		}
	} );

	jQuery( '.more_options' ).hide();
	jQuery( 'tbody' ).sortable( {
		placeholder: "ui-widget-shadow",
		handle: 'td.icon-move',
		update: function () {
			elex_dp_update_rules_arrangement();
		}
	} );
} );

function elex_dp_update_rules_arrangement() {
	var rules_order = '';
	var new_index = 1;
	jQuery( '.saved_row' ).each( function ( $index, $element ) {
		var current_row_no = jQuery( $element ).find( 'td:nth-child( 3 )' ).text();
		jQuery( $element ).find( 'td:nth-child( 3 )' ).text( new_index++ );
		if ( rules_order )
			rules_order = rules_order + ',' + current_row_no;
		else
			rules_order = current_row_no;
	} );
	//alert( rules_order );
	jQuery.post( 
			ajaxurl,
			{
				'action': 'update_rules_arrangement',
				'rules-order': rules_order,
				'rules-type': '<?php echo ! empty( $_REQUEST['tab'] ) ? esc_html( sanitize_text_field( $_REQUEST['tab'] ) ) : esc_html( current( $execution_order ) ); ?>',
				'xa-nonce': '<?php echo esc_html( wp_create_nonce( 'update_rules_arrangement' ) ); ?>'
			},
			function ( response ) {
				//alert( response );
			}
	 );

	return false;
}

</script>
<style>
	td.icon-move{
		background-image: url( '<?php echo esc_html( ELEX_DP_MAIN_URL_PATH . '/img/drag2.png' ); ?>' );
		background-size: auto auto ;
		background-position: center;
		background-repeat: no-repeat;
	}
</style>
