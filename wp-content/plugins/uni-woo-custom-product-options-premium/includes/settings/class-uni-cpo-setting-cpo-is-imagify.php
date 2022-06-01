<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
* Uni_Cpo_Setting_Cpo_Is_Imagify class
*
*/

class Uni_Cpo_Setting_Cpo_Is_Imagify extends Uni_Cpo_Setting implements Uni_Cpo_Setting_Interface {

	/**
	 * Init
	 *
	 */
	public function __construct() {
		$this->setting_key  = 'cpo_is_imagify';
		$this->setting_data = array(
			'title'      => __( 'Use for Imagify?', 'uni-cpo' ),
			'is_tooltip' => true,
			'desc_tip'   => __( 'Enables using this option in Imagify functionality.', 'uni-cpo' ),
			'options'    => array(
				'no'  => __( 'No', 'uni-cpo' ),
				'yes' => __( 'Yes', 'uni-cpo' )
			),
			'js_var'     => 'data'
		);
		add_action( 'wp_footer', array( $this, 'js_template' ), 10 );
	}


	/**
	 * A template for the module
	 *
	 * @since 1.0
	 * @return string
	 */
	public function js_template() {
		?>
        <script id="js-builderius-setting-<?php echo $this->setting_key; ?>-tmpl" type="text/template">
            <div class="uni-modal-row uni-clear<?php if ( ! unicpo_fs()->is__premium_only() ) { echo ' uni-premium-content'; } ?>">
				<?php echo $this->generate_field_label_html(); ?>
                <div class="uni-modal-row-second uni-clear">
                    <div class="uni-setting-fields-wrap-2 uni-clear">
						<?php echo $this->generate_radio_html(); ?>
                    </div>
                </div>
            </div>
        </script>
		<?php
	}

}