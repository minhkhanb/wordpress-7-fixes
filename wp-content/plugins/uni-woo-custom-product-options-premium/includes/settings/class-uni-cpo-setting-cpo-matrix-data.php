<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
* Uni_Cpo_Setting_Cpo_Matrix_Data class
*
*/

class Uni_Cpo_Setting_Cpo_Matrix_Data extends Uni_Cpo_Setting implements Uni_Cpo_Setting_Interface {

    /**
     * Init
     *
     */
    public function __construct() {
        $this->setting_key  = 'cpo_matrix_data';
        $this->setting_data = array(
            'title'      => __( 'Matrix data', 'uni-cpo' ),
            'is_tooltip' => true,
            'desc_tip'   => __( '', 'uni-cpo' ),
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
            <?php if ( unicpo_fs()->is__premium_only() ) { ?>
                <div class="uni-modal-row uni-modal-matrix-option-row uni-clear">
                    <label for="cpo_matrix_data[in_col]">
                        <?php esc_html_e( '# in cols', 'uni-cpo' ) ?>
                        <span class="uni-cpo-tooltip" data-tip="<?php esc_attr_e( 'Values in columns. Syntax: "slug : Name|slug2 : Name2"', 'uni-cpo' ) ?>"></span>
                    </label>
                    <div class="uni-modal-row-second">
                        <textarea
                            class = "uni-matrix-option-data-in-col builderius-setting-field"
                            cols = "20"
                            id = "builderius-setting-cpo_matrix_data[in_col]"
                            name = "cpo_matrix_data[in_col]"
                            rows = "3">{{- data.in_col }}</textarea>
                    </div>
                </div>
                <div class="uni-modal-row uni-modal-matrix-option-row uni-clear">
                    <label for="cpo_matrix_data[in_row]">
                        <?php esc_html_e( '# in rows', 'uni-cpo' ) ?>
                        <span class="uni-cpo-tooltip" data-tip="<?php esc_attr_e( 'Values in rows. Syntax: "slug : Name|slug2 : Name2"', 'uni-cpo' ) ?>"></span>
                    </label>
                    <div class="uni-modal-row-second">
                        <textarea
                            class = "uni-matrix-option-data-in-row builderius-setting-field"
                            cols = "20"
                            id = "builderius-setting-cpo_matrix_data[in_row]"
                            name = "cpo_matrix_data[in_row]"
                            rows = "3">{{- data.in_row }}</textarea>
                    </div>
                </div>
                <div class="uni-modal-row uni-clear">
                    <label for="cpo_matrix_data[template]">
                        <?php esc_html_e( 'Template', 'uni-cpo' ) ?>
                        <span class="uni-cpo-tooltip" data-tip="<?php esc_attr_e( 'Custom template to output Matrix value. Use &#123;&#123;&#123;row&#125;&#125;&#125; and &#123;&#123;&#123;col&#125;&#125;&#125; to get names of the row and the column chosen.', 'uni-cpo' ) ?>"></span>
                    </label>
                    <div class="uni-modal-row-second">
                        <?php
    					echo $this->generate_text_html(
    						'cpo_matrix_data[template]',
    						array(
                                'class' => array(
    								'uni-matrix-option-table-template'
    							),
    							'value' => '{{- data.template }}',
                                'js_var'  => 'data.template'
    						)
    					);
    					?>
                    </div>
                </div>
                <div class="uni-modal-row uni-clear">
                    <div class="uni-cpo-matrix-options-wrap">
                        <div class="uni-matrix-generate-btn">
                            <?php esc_html_e( 'Generate', 'uni-cpo' ) ?>
                        </div>
                        <div class="uni-matrix-import">
                            <input
                                id="uni-matrix-option-import-input"
                                name="import"
                                type="file"/>
                            <label
                                for="uni-matrix-option-import-input">
                                <span></span>
                                <?php esc_html_e( 'Choose a file', 'uni-cpo' ) ?>
                            </label>
                            <button
                                type="button"
                                class="uni-matrix-import-btn">
                                <?php esc_html_e( 'Import', 'uni-cpo' ) ?>
                            </button>
                        </div>
                        <div class="uni-matrix-table-wrapper">
                            <div
                                id="uni-matrix-option-table-container"
                                class="uni-matrix-option-table-container"></div>
                        </div>
                        <?php
    					echo $this->generate_text_html(
    						'cpo_matrix_data[json]',
    						array(
                                'class' => array(
    								'uni-matrix-option-json'
    							),
                                'type' => 'hidden',
    							'value' => '{{- data.json }}',
                                'js_var'  => 'data.json'
    						)
    					); ?>
                    </div>
                </div>
            <?php } ?>
        </script>
        <?php
    }

}
