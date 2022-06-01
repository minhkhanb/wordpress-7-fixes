<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<style>
	.xa_sp_table_cell{
		text-align:center;
	}
	.xa_sp_table_head2 tr td
	{
		text-align: center;

	} 
	.xa_sp_table_head1{
		border-bottom: 1px solid #e1e1e1;
		font-weight: 600;
		font-size: 15px;

	}
	.xa_sp_table_head2_cell,xa_sp_table_cell{
		border-bottom: 1px solid #e1e1e1;
		font-weight: 600;
		text-align: left;
		line-height: 1.3em;
		font-size: 14px;
		padding: 6px 6px !important;
		word-wrap: break-word;
		display: table-cell;
		vertical-align: inherit;
		color: #32373c;
		min-width: 100%;
	}

</style>

<?php
ob_start();
?>
<h4 class='xa_sp_table_head1 manage-column column-product column-primary' style="text-align:center"><?php esc_html_e( 'Bulk Product Offers', 'eh-dynamic-pricing-discounts' ); ?></h4>
<table class='xa_sp_table wp-list-table widefat fixed striped stock' style=' width:100%;   margin-right: auto;'>

	<thead class='xa_sp_table_head2' style="font-size: 14px;  "><tr ><td width=10px class="xa_sp_table_head2_cell"><?php esc_html_e( 'Min Buy', 'eh-dynamic-pricing-discounts' ); ?></td><td  class="xa_sp_table_head2_cell"><?php esc_html_e( 'Max Buy', 'eh-dynamic-pricing-discounts' ); ?></td><td  class="xa_sp_table_head2_cell"><?php esc_html_e( 'Offer', 'eh-dynamic-pricing-discounts' ); ?></td></tr></thead>
	<tbody class='xa_sp_table_body'>
		<?php
		$settings_optn = get_option( 'xa_dynamic_pricing_setting' );
		if ( isset( $settings_optn['pricing_table_qnty_shrtcode'] ) && ! empty( $settings_optn['pricing_table_qnty_shrtcode'] ) ) {
			$pricing_table_qnty_shrtcode = $settings_optn['pricing_table_qnty_shrtcode'];
		} else {
			$pricing_table_qnty_shrtcode = __( 'nos', 'eh-dynamic-pricing-discounts' );
		}


		global $product;
		$rules_validator = new Elex_DP_Rules_Validator( 'all_match', true, 'product_rules' );
		//checking if product rules are enabled on settings page
		global $xa_dp_setting;
		if ( ! empty( $xa_dp_setting['product_rules_on_off'] ) && 'enable' !== $xa_dp_setting['product_rules_on_off'] ) {
			$product_rules = array();
		} else {
			$product_rules = $rules_validator->elex_dp_get_valid_rules_for_product( $product, elex_dp_get_pid( $product ), 1 );  // this will calculate all valid rules and assign to $offers
		}
		$count = 0;

		$weight = get_option( 'woocommerce_weight_unit' );
		$quantity = $pricing_table_qnty_shrtcode;

		$woo_currency = get_option( 'woocommerce_currency' );
		if ( ! empty( $product_rules ) ) {
			foreach ( $product_rules as $rule ) {
				if ( isset( $rule['min'] ) && isset( $rule['discount_type'] ) && isset( $rule['value'] ) && isset( $rule['check_on'] ) ) {


					switch ( $rule['check_on'] ) {
						case 'Weight':
							$unit = $weight;
							break;
						case 'Quantity':
							$unit = $quantity;
							break;
						case 'Price':
							$unit = $woo_currency;
							break;

						default:
							break;
					}
					$count++;
					echo "<tr  class='xa_sp_table_body_row' style='font-size:14px; font-family: Verdana;'>";
					echo "<td class='xa_sp_table_cell'>" . esc_html( $rule['min'] ) . ' ' . esc_html( $unit ) . ' </td>';
					echo "<td class='xa_sp_table_cell'>";
					echo ( isset( $rule['max'] ) ? esc_html( $rule['max'] ) : '-' );
					echo ' ' . ( isset( $rule['max'] ) ? esc_html( $unit ) : '' ) . '</td>';
					echo "<td class='xa_sp_table_cell'>" . esc_html( $rule['value'] );
					if ( 'Percent Discount' == $rule['discount_type'] ) {
						echo '   % ' . esc_html__( 'Discount', 'eh-dynamic-pricing-discounts' ) . '</td>';
					} elseif ( 'Flat Discount' == $rule['discount_type'] ) {
						echo ' ' . esc_html( $woo_currency ) . ' ' . esc_html__( 'Discount', 'eh-dynamic-pricing-discounts' ) . '</td>';
					} elseif ( 'Fixed Price' == $rule['discount_type'] ) {
						echo ' ' . esc_html( $woo_currency ) . ' ' . esc_html__( 'Fixed Price', 'eh-dynamic-pricing-discounts' ) . '</td>';
					} else {
						echo ' ' . esc_html( $woo_currency ) . ' ' . esc_html( $rule['discount_type'] );
					}
					echo '</tr>';
				}
			}
		}
		?>

	</tbody>

	<tfoot></tfoot>
</table>


<?php
$output = ob_get_clean();
if ( $count > 0 ) {
	$allowed_html = wp_kses_allowed_html( 'post' );
	echo wp_kses( $output, $allowed_html );
}
