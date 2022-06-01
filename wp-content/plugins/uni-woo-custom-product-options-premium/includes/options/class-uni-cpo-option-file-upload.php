<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*   Uni_Cpo_Option_File_Upload class
*
*/

class Uni_Cpo_Option_File_Upload extends Uni_Cpo_Option implements Uni_Cpo_Option_Interface {

	/**
	 * Stores extra (specific to this) option data.
	 *
	 * @var array
	 */
	protected $extra_data = array();

	/**
	 * Constructor gets the post object and sets the ID for the loaded option.
	 *
	 */
	public function __construct( $option = 0 ) {

		parent::__construct( $option );

	}

	public static function get_type() {
		return 'file_upload';
	}

	public static function get_title() {
		return __( 'File Upload', 'uni-cpo' );
	}

	/**
	 * Returns an array of special vars associated with the option
	 *
	 * @return array
	 */
	public static function get_special_vars() {
		return array( 'width', 'height' );
	}

	/**
	 * Returns an array of data used in js query builder
	 *
	 * @return array
	 */
	public static function get_filter_data() {
		$operators = array(
			'is_empty',
			'is_not_empty'
		);

		$operators_special = array(
			'less',
			'less_or_equal',
			'equal',
			'not_equal',
			'greater_or_equal',
			'greater',
			'is_empty',
			'is_not_empty'
		);

		return array(
			'input'        => 'text',
			'operators'    => $operators,
			'special_vars' => array(
				'width'        => array(
					'type'      => 'integer',
					'input'     => 'text',
					'operators' => $operators
				),
				'height' => array(
					'type'      => 'integer',
					'input'     => 'text',
					'operators' => $operators
				)
			)
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	public function get_cpo_rate() {
		$cpo_general = $this->get_cpo_general();

		return ( ! empty( $cpo_general['main']['cpo_rate'] ) ) ? floatval( $cpo_general['main']['cpo_rate'] ) : 0;
	}

	/*
    |--------------------------------------------------------------------------
    | Other Actions
    |--------------------------------------------------------------------------
    */

	public function formatted_model_data() {

		$model['pid']                                         = $this->get_id();
		$model['settings']['general']                         = $this->get_general();
		$model['settings']['general']['status']               = array(
			'sync' => array(
				'type' => 'none',
				'pid'  => 0
			)
		);
		$model['settings']['general']                         = array_reverse( $model['settings']['general'] );
		$model['settings']['style']                           = $this->get_style();
		$model['settings']['advanced']                        = $this->get_advanced();
		$model['settings']['cpo_general']                     = $this->get_cpo_general();
		$model['settings']['cpo_general']['main']['cpo_slug'] = $this->get_slug_ending();
		$model['settings']['cpo_conditional']                 = $this->get_cpo_conditional();
		$model['settings']['cpo_validation']                  = $this->get_cpo_validation();

		return stripslashes_deep( $model );
	}

    public function get_edit_field( $data, $value, $context = 'cart' ) {
        $id               = $data['id'];
        $main             = $data['settings']['general']['main'];
        $cpo_general_main = $data['settings']['cpo_general']['main'];
        $attributes       = array( 'data-parsley-trigger' => 'change focusout submit' );
        $is_required      = ( 'yes' === $cpo_general_main['cpo_is_required'] ) ? true : false;
        $plugin_settings  = UniCpo()->get_settings();

        $slug              = $this->get_slug();
        $input_css_class[] = $slug . '-field';
        $input_css_class[] = 'cpo-cart-item-option';

	    if ( 'cart' === $context ) {
	        return;
	    }

        if ( 'order' === $context ) {
            $input_css_class[] = 'uni-admin-order-item-option-text';
        }

        if ( $is_required ) {
            $attributes['data-parsley-required'] = 'true';
        }

        if ( empty( $main['width']['value'] ) ) {
            $attributes['size'] = 2;
        }

        ob_start();
        if ( 'dropbox' !== $plugin_settings['file_storage'] ) {
            ?>
	        <div class="cpo-cart-item-option-wrapper uni-node-<?php echo esc_attr( $id ) ?> <?php if ( 'order' === $context ) {
                echo esc_attr( "uni-admin-order-item-option-wrapper" );
            } ?>">
		        <label><?php esc_html_e( uni_cpo_get_proper_option_label_sp( uni_cpo_sanitize_label( $this->cpo_order_label() ) ) ) ?></label>
		        <span class="uni-admin-order-item-option-file-upload-text">
					<?php if ( ! empty( $value ) ) {
                        $attach_url = wp_get_attachment_url( $value );
                        echo '<a href="' . esc_url( $attach_url ) . '">' . basename( $attach_url ) . '</a>';
                    } else {
                        echo '<p>' . esc_html__( 'No file uploaded', 'uni-cpo' ) . '</p>';
                    } ?>
			        <input
					        type="button"
					        class="cpo-upload-attachment"
					        data-slug="<?php echo esc_attr( $slug ) ?>"
					        value="<?php esc_html_e( 'Add/Edit file', 'uni-cpo' ) ?>">
                <input
		                type="button"
		                class="cpo-remove-attachment"
		                data-slug="<?php echo esc_attr( $slug ) ?>"
		                value="<?php esc_html_e( 'Remove', 'uni-cpo' ) ?>">
            </span>
		        <input
				        class="cpo-cart-item-option"
				        name="<?php echo esc_attr( $slug ) ?>"
				        value="<?php echo esc_attr( $value ) ?>"
				        type="hidden"/>
	        </div>
            <?php
        } else {
            ?>
	        <div class="cpo-cart-item-option-wrapper uni-node-<?php echo esc_attr( $id ) ?> <?php if ( 'order' === $context ) {
                echo esc_attr( "uni-admin-order-item-option-wrapper" );
            } ?>">
	        <label><?php esc_html_e( uni_cpo_get_proper_option_label_sp( uni_cpo_sanitize_label( $this->cpo_order_label() ) ) ) ?></label>
        	<p><?php esc_html_e('Local storage setting is set to "Dropbox", so it is not possible to add/edit files from here', 'uni-cpo'); ?></p>
	        </div>
	        <?php
        }

        return ob_get_clean();
    }

	public static function get_settings() {
		return array(
			'settings' => array(
				'general'         => array(
					'status' => array(
						'sync' => array(
							'type' => 'none',
							'pid'  => 0
						),
					),
					'main'   => array(
						'width'  => array(
							'value' => 100,
							'unit'  => '%'
						)
					)
				),
				'style'           => array(
                    'label'   => array(
                        'color'          => '',
                        'text_align_label'     => '',
                        'font_family'    => 'inherit',
                        'font_weight'    => '',
                        'font_size_label'      => array(
                            'value' => '',
                            'unit'  => 'px'
                        ),
                    ),
					'font' => array(
						'color' => '#f75555',
                        'font_family' => 'inherit',
                        'font_style' => 'inherit',
                        'font_weight' => '',
                        'font_size' => array(
                            'value' => 16,
                            'unit' => 'px'
                        ),
                        'letter_spacing' => ''
                    )
				),
				'advanced'        => array(
					'layout'    => array(
						'margin'  => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
							'unit'   => 'px'
						)
					),
					'selectors' => array(
						'id_name'    => '',
						'class_name' => ''
					)
				),
				'cpo_general'     => array(
					'main'     => array(
						'cpo_slug'        => '',
						'cpo_is_required' => 'no',
						//'cpo_upload_mode' => 'single', //multiple
						'cpo_max_filesize' => 0,
						'cpo_mime_types'  => '',
						'cpo_rate'        => ''
					),
					'advanced' => array(
						'cpo_label'        => '',
						'cpo_label_tag'    => 'label',
						'cpo_order_label'  => '',
						'cpo_is_tooltip'   => 'no',
						'cpo_tooltip_type' => 'classic',
						'cpo_tooltip'      => '',
						'cpo_tooltip_image' => array(
							'url' => '',
							'id' => 0,
							'alt' => ''
						),
						'cpo_tooltip_class' => '',
						'cpo_order_visibility' => 'no'
					)
				),
				'cpo_conditional' => array(
					'main' => array(
						'cpo_is_fc'      => 'no',
						'cpo_fc_default' => 'hide',
						'cpo_fc_scheme'  => ''
					)
				),
				'cpo_validation' => array(
					'main' => array(
						'cpo_validation_msg' => array(
                            'req' => '',
                            'type' => '',
                            'custom' => ''
                        ),
						'cpo_file_maxsize' => '',
						'cpo_file_mime'  => '',
                        'cpo_file_minwidth' => '',
						'cpo_file_maxwidth' => '',
						'cpo_file_minheight' => '',
						'cpo_file_maxheight' => '',
					)
				)
			)
		);
	}

	public static function js_template() {
		?>
        <script id="js-builderius-module-<?php echo self::get_type(); ?>-tmpl" type="text/template">
        	{{ const { id, type } = data; }}
            {{ const { general, style, advanced, cpo_general, cpo_suboptions, cpo_conditional, cpo_validation } = data.settings; }}
            {{ const { id_name, class_name } = advanced.selectors; }}

            {{ const { width } = general.main; }}

            {{ const color_label = uniGet( data.settings.style, 'label.color', '' ); }}
            {{ const text_align_label = uniGet( data.settings.style, 'label.text_align_label', 'inherit' ); }}
            {{ const font_family_label = uniGet( data.settings.style, 'label.font_family', '' ); }}
            {{ const font_weight_label = uniGet( data.settings.style, 'label.font_weight', '' ); }}
            {{ const font_size_label = uniGet( data.settings.style, 'label.font_size_label', {value:'',unit:'px'} ); }}

            {{ const color = uniGet(style, 'font.color', '#f75555'); }}
            {{ const font_family = uniGet(style, 'font.font_family', 'inherit'); }}
            {{ const font_style = uniGet(style, 'font.font_style', 'inherit'); }}
            {{ const font_weight = uniGet(style, 'font.font_weight', ''); }}
            {{ const font_size_value = uniGet(style, 'font.font_size.value', '16'); }}
            {{ const font_size_unit = uniGet(style, 'font.font_size_unit', 'px'); }}
            {{ const letter_spacing = uniGet(style, 'font.letter_spacing', ''); }}


            {{ const radius_value = uniGet(style, 'upload_btn.radius.value', '3'); }}
            {{ const radius_unit = uniGet(style, 'upload_btn.radius.unit', 'px'); }}

            {{ const { margin } = advanced.layout; }}

            {{ const { cpo_slug, cpo_is_required } = cpo_general.main; }}
            {{ const { cpo_label_tag, cpo_label, cpo_is_tooltip, cpo_tooltip } = cpo_general.advanced; }}
			{{ const cpo_tooltip_type = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_type', 'classic' ); }}
			{{ const cpo_tooltip_image = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_image', {url:''} ); }}
			{{ const cpo_tooltip_class = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_class', '' ); }}
            <div
                id="{{- id_name }}"
                class="uni-module uni-module-{{- type }} uni-node-{{- id }} {{- class_name }}"
                data-node="{{- id }}"
                data-type="{{- type }}">
            <style>
            	.uni-node-{{= id }} {
            		{{ if ( width.value !== '' ) { }} width: {{= width.value+width.unit }}!important; {{ } }}
            		{{ if ( margin.top !== '' ) { }} margin-top: {{= margin.top + margin.unit }}; {{ } }}
                    {{ if ( margin.bottom !== '' ) { }} margin-bottom: {{= margin.bottom + margin.unit }}; {{ } }}
                    {{ if ( margin.left !== '' ) { }} margin-left: {{= margin.left + margin.unit }}; {{ } }}
                    {{ if ( margin.right !== '' ) { }} margin-right: {{= margin.right + margin.unit }}; {{ } }}
            	}
                {{ if ( cpo_label_tag && cpo_label !== '' ) { }}
                    .uni-node-{{= id }} .uni-cpo-module-{{- type }}-label {
                        {{ if ( color_label !== '' ) { }} color: {{= color_label }}!important; {{ } }}
                        {{ if ( text_align_label !== '' ) { }} text-align: {{= text_align_label }}!important; display: block; {{ } }}
                        {{ if ( font_family_label !== 'inherit' ) { }} font-family: {{= font_family_label }}!important; {{ } }}
                        {{ if ( font_size_label.value !== '' ) { }} font-size: {{= font_size_label.value+font_size_label.unit }}!important; {{ } }}
                        {{ if ( font_weight_label !== '' ) { }} font-weight: {{= font_weight_label }}!important; {{ } }}
                    }
                {{ } }}
            	.uni-node-{{= id }} .uni-cpo-file-upload-choose-btn {
            		{{ if ( color ) { }} color: {{= color }}; {{ } }}
            		{{ if ( font_family !== 'inherit' ) { }} font-family: {{= font_family }}; {{ } }}
                    {{ if ( font_style !== 'inherit' ) { }} font-style: {{= font_style }}; {{ } }}
                    {{ if ( font_weight ) { }} font-weight: {{= font_weight }}; {{ } }}
                    {{ if ( font_size_value ) { }} font-size: {{= font_size_value+font_size_unit }}; {{ } }}
                    {{ if ( letter_spacing ) { }} letter-spacing: {{= letter_spacing+'em' }}; {{ } }}
            	}
        	</style>
            {{ if ( cpo_label_tag && cpo_label !== '' ) { }}
                <{{- cpo_label_tag }} class="uni-cpo-module-{{- type }}-label {{ if ( cpo_is_required === 'yes' ) { }} uni_cpo_field_required {{ } }}">
                	{{- cpo_label }}
					{{ if ( cpo_is_tooltip === 'yes' && cpo_tooltip !== '' && cpo_tooltip_type === 'classic' ) { }} <span class="uni-cpo-tooltip" data-tip="{{- cpo_tooltip }}"></span> {{ } else if ( cpo_is_tooltip === 'yes' && cpo_tooltip_image.url !== '' && cpo_tooltip_type === 'lightbox' ) { }} <span class="uni-cpo-tooltip"></span> {{ } else if ( cpo_is_tooltip === 'yes' && cpo_tooltip_class !== '' && cpo_tooltip_type === 'popup' ) { }} <span class="uni-cpo-tooltip"></span> {{ } }}
            	</{{- cpo_label_tag }}>
        	{{ } }}
            <input
                class="{{- cpo_slug }}-field js-uni-cpo-field-{{- type }}"
                id="{{- cpo_slug }}-field"
                name="{{- cpo_slug }}"
                type="hidden"
                value=""/>
            <span
                class="uni-cpo-file-upload-choose-btn js-uni-cpo-field-{{- type }}-el"
                data-pid=""
                data-slug="{{- cpo_slug }}"
                id="{{- cpo_slug }}-el">
                <i class="fa fa-upload"></i>
                {{- builderius_i18n.select_file }}</span>
            </div>
        </script>
		<?php
	}

	public static function template( $data, $post_data = array() ) {
		$id                   = $data['id'];
		$pid                  = ( ! empty( $data['pid'] ) ) ? absint( $data['pid'] ) : 0;
		$type                 = $data['type'];
		$selectors            = $data['settings']['advanced']['selectors'];
		$cpo_general_main     = $data['settings']['cpo_general']['main'];
		$cpo_general_advanced = $data['settings']['cpo_general']['advanced'];
		$cpo_validation_main  = ( isset( $data['settings']['cpo_validation']['main'] ) )
			? $data['settings']['cpo_validation']['main']
			: array();
		$cpo_label_tag        = $cpo_general_advanced['cpo_label_tag'];
		$attributes           = array(
			'data-parsley-trigger'          => 'change submit',
			'data-parsley-errors-container' => '.uni-node-' . $id,
			'data-parsley-class-handler'    => '.uni-node-' . $id,
			'autocomplete'                  => 'off'
		);
		$wrapper_attributes   = array();
		$option               = false;
		$rules_data           = $data['settings']['cpo_conditional']['main'];
		$is_required          = ( 'yes' === $cpo_general_main['cpo_is_required'] ) ? true : false;
		$is_tooltip           = ( 'yes' === $cpo_general_advanced['cpo_is_tooltip'] ) ? true : false;
		$is_enabled           = ( 'yes' === $rules_data['cpo_is_fc'] ) ? true : false;
		$is_hidden            = ( 'hide' === $rules_data['cpo_fc_default'] ) ? true : false;
		$cpo_tooltip_type  	  = ( isset( $cpo_general_advanced['cpo_tooltip_type'] ) )
            ? $cpo_general_advanced['cpo_tooltip_type']
            : 'classic';
		$cpo_tooltip_image    = ( isset( $cpo_general_advanced['cpo_tooltip_image']['url'] ) )
            ? $cpo_general_advanced['cpo_tooltip_image']['url']
            : '';

		if ( $pid ) {
			$option = uni_cpo_get_option( $data['pid'] );
		}

		$slug                = ( $pid && is_object( $option ) && 'trash' !== $option->get_status() ) ? $option->get_slug() : '';
		$css_id[]            = $slug;
		$css_class           = array(
			'uni-module',
			'uni-module-' . $type,
			'uni-node-' . $id
		);
		$input_css_class[]   = $slug . '-field';
		$input_css_class[]   = 'js-uni-cpo-field';
		$input_css_class[]   = 'js-uni-cpo-field-' . $type;
		$plupload_attributes = array(
			'data-post-id'      => $pid,
			'data-slug'         => $slug,
			'data-upload-mode'  => ( ! empty( $cpo_general_main['cpo_upload_mode'] ) ) ? $cpo_general_main['cpo_upload_mode'] : 'single',
			'data-max-filesize' => ( isset( $cpo_general_main['cpo_max_filesize'] ) ) ? $cpo_general_main['cpo_max_filesize'] : 0,
			'data-mime-types'   => ( isset( $cpo_general_main['cpo_mime_types'] ) ) ? str_replace( ' ', '', $cpo_general_main['cpo_mime_types'] ) : ''
		);
		if ( ! empty( $selectors['id_name'] ) ) {
			array_push( $css_id, $selectors['id_name'] );
		}
		if ( ! empty( $selectors['class_name'] ) ) {
			array_push( $css_class, $selectors['class_name'] );
		}

		if ( 'yes' === $cpo_general_main['cpo_is_required'] ) {
			$attributes['data-parsley-required'] = 'true';
		}

		if ( ! empty( $cpo_validation_main ) && isset( $cpo_validation_main['cpo_validation_msg'] )
		     && is_array( $cpo_validation_main['cpo_validation_msg'] ) ) {
			foreach ( $cpo_validation_main['cpo_validation_msg'] as $k => $v ) {
				if ( empty( $v ) ) {
					continue;
				}
				switch ( $k ) {
					case 'req':
						$attributes['data-parsley-required-message'] = $v;
						break;
					case 'custom' :
						$extra_validation_msgs = preg_split( '/\R/', $v );
						$attributes            = uni_cpo_field_attributes_modifier( $extra_validation_msgs, $attributes );
						break;
					default :
						break;
				}
			}
		}

		if ( $is_enabled && $is_hidden ) {
			$wrapper_attributes['style'] = 'display:none;';
			$input_css_class[]           = 'uni-cpo-excluded-field';
		}

		$wrapper_attributes = apply_filters( 'uni_wrapper_attributes_for_option', $wrapper_attributes, $slug, $id );
		$value = '';
		if ( ! empty( $post_data ) && ! empty( $slug ) && ! empty( $post_data[$slug] ) ) {
		    $attach_id = $post_data[$slug];
			$value = $attach_id;
            $attachment_meta = uni_cpo_get_attachment_meta( $attach_id );
			$attributes['data-filename'] = basename( $attachment_meta['url'] );
			$attributes['data-width'] = $attachment_meta['width'];
			$attributes['data-height'] = $attachment_meta['height'];
		}
		?>
    <div
            id="<?php echo implode( ' ', array_map( function ( $el ) {
				return esc_attr( $el );
			}, $css_id ) ); ?>"
            class="<?php echo implode( ' ', array_map( function ( $el ) {
				return esc_attr( $el );
			}, $css_class ) ); ?>"
		<?php echo self::get_custom_attribute_html( $wrapper_attributes ); ?>>
		<?php
		if ( ! empty( $cpo_general_advanced['cpo_label'] ) ) { ?>
            <<?php echo esc_attr( $cpo_label_tag ); ?> class="uni-cpo-module-<?php echo esc_attr( $type ); ?>-label <?php if ( $is_required ) { ?> uni_cpo_field_required <?php } ?>">
			<?php esc_html_e( uni_cpo_get_proper_option_label_sp( $cpo_general_advanced['cpo_label'] ) ); ?>
			<?php if ( $is_tooltip && $cpo_general_advanced['cpo_tooltip'] !== '' && $cpo_tooltip_type === 'classic' ) { ?>
                <span class="uni-cpo-tooltip" data-tip="<?php echo uni_cpo_sanitize_tooltip( $cpo_general_advanced['cpo_tooltip'] ); ?>"></span>
			<?php } else if ( $is_tooltip && $cpo_tooltip_image !== '' && $cpo_tooltip_type === 'lightbox' ) { ?>
				<a href="<?php esc_html_e( $cpo_tooltip_image ); ?>" data-lity class="uni-cpo-tooltip"></a>
			<?php } else if ( $is_tooltip && $cpo_general_advanced['cpo_tooltip_class'] !== '' && $cpo_tooltip_type === 'popup' ) { ?>
				<span class="uni-cpo-tooltip <?php esc_html_e( $cpo_general_advanced['cpo_tooltip_class'] ); ?>"></span>
			<?php } ?>
            </<?php echo esc_attr( $cpo_label_tag ); ?>>
		<?php } ?>

        <input
                id="<?php echo esc_attr( $slug ); ?>-field"
                class="<?php echo implode( ' ', array_map( function ( $el ) {
					return esc_attr( $el );
				}, $input_css_class ) ); ?>"
                name="<?php echo esc_attr( $slug ); ?>"
                type="hidden"
                value="<?php echo esc_attr( $value ) ?>"
			    <?php echo self::get_custom_attribute_html( $attributes ); ?> />
        <span
                id="<?php echo esc_attr( $slug ); ?>-el"
                class="uni-cpo-file-upload-choose-btn  js-uni-cpo-field-<?php echo esc_attr( $type ) ?>-el"
			    <?php echo self::get_custom_attribute_html( $plupload_attributes ); ?>>
            <i class="fa fa-upload"></i>
			<?php esc_html_e( 'Select file', 'uni-cpo' ) ?></span>
        <ul
                id="<?php echo esc_attr( $slug ); ?>-files-list"
                class="uni-cpo-file-upload-files js-uni-cpo-file-upload-files"></ul>
        </div>
		<?php

		self::conditional_rules( $data );
	}

	public static function get_css( $data ) {
		$id                   = $data['id'];
		$type                 = $data['type'];
		$main                 = $data['settings']['general']['main'];
		$margin               = $data['settings']['advanced']['layout']['margin'];
		$label                = ( ! empty( $data['settings']['style']['label'] ) ) ? $data['settings']['style']['label'] : array();
		$font                 = $data['settings']['style']['font'];
		$cpo_general_advanced = $data['settings']['cpo_general']['advanced'];

		ob_start();
		?>

        .uni-node-<?php echo esc_attr( $id ); ?> {
        	<?php if ( $main['width']['value'] ) { ?> width: <?php echo esc_attr( "{$main['width']['value']}{$main['width']['unit']}" ) ?>!important;<?php } ?>
			<?php if ( $margin['top'] ) { ?> margin-top: <?php echo esc_attr( "{$margin['top']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( $margin['bottom'] ) { ?> margin-bottom: <?php echo esc_attr( "{$margin['bottom']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( $margin['left'] ) { ?> margin-left: <?php echo esc_attr( "{$margin['left']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( $margin['right'] ) { ?> margin-right: <?php echo esc_attr( "{$margin['right']}{$margin['unit']}" ) ?>; <?php } ?>
        }
        <?php if ( ! empty( $cpo_general_advanced['cpo_label'] ) ) { ?>
            .uni-node-<?php echo esc_attr( $id ); ?> .uni-cpo-module-<?php echo esc_attr( $type ); ?>-label {
            <?php if ( ! empty( $label['color'] ) ) { ?> color: <?php echo esc_attr( $label['color'] ); ?>!important;<?php } ?>
            <?php if ( ! empty( $label['text_align_label'] ) ) { ?> text-align: <?php echo esc_attr( $label['text_align_label'] ); ?>!important; display: block; <?php } ?>
            <?php if ( ! empty( $label['font_family'] ) && $label['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $label['font_family'] ); ?>!important;<?php } ?>
            <?php if ( ! empty( $label['font_weight'] ) ) { ?> font-weight: <?php echo esc_attr( $label['font_weight'] ); ?>!important;<?php } ?>
            <?php if ( ! empty( $label['font_size_label']['value'] ) ) { ?> font-size: <?php echo esc_attr( "{$label['font_size_label']['value']}{$label['font_size_label']['unit']}" ) ?>!important; <?php } ?>
            }
        <?php } ?>
        .uni-node-<?php echo esc_attr( $id ); ?>  .uni-cpo-file-upload-choose-btn {
        	<?php if ( $font['color'] ) { ?> color: <?php echo esc_attr( $font['color'] ); ?>;<?php } ?>
			<?php if ( $font['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $font['font_family'] ); ?>;<?php } ?>
			<?php if ( $font['font_style'] !== 'inherit' ) { ?> font-style: <?php echo esc_attr( $font['font_style'] ); ?>;<?php } ?>
			<?php if ( $font['font_weight'] ) { ?> font-weight: <?php echo esc_attr( $font['font_weight'] ); ?>;<?php } ?>
			<?php if ( $font['font_size']['value'] ) { ?> font-size: <?php echo esc_attr( "{$font['font_size']['value']}{$font['font_size']['unit']}" ) ?>; <?php } ?>
			<?php if ( $font['letter_spacing'] ) { ?> letter-spacing: <?php echo esc_attr( $font['letter_spacing'] ); ?>em;<?php } ?>
        }

		<?php
		return ob_get_clean();
	}

    public function calculate( $form_data ) {
        $post_name = trim( $this->get_slug(), '{}' );

        if ( ! empty( $form_data[ $post_name ] ) ) {
            $price          = $this->get_cpo_rate();
            $meta           = uni_cpo_get_attachment_meta( $form_data[ $post_name ] );
            $full_file_name = ( ! empty( basename( $meta['url'] ) ) ) ? basename( $meta['url'] ) : $form_data[ $post_name ];

            if ( ! empty( $price ) ) {
                return array(
                    $post_name             => array(
                        'calc'       => floatval( $price ),
                        'cart_meta'  => $form_data[ $post_name ],
                        'order_meta' => ( ! empty( basename( $meta['url'] ) ) && isset( $full_file_name ) )
                            ? esc_html( $full_file_name )
                            : $form_data[ $post_name ]
                    ),
                    $post_name . '_width'  => array(
                        'calc'       => ( isset( $meta['width'] ) ) ? $meta['width'] : 0,
                        'cart_meta'  => ( isset( $meta['width'] ) ) ? $meta['width'] : 0,
                        'order_meta' => ( isset( $meta['width'] ) ) ? $meta['width'] : 0
                    ),
                    $post_name . '_height' => array(
                        'calc'       => ( isset( $meta['height'] ) ) ? $meta['height'] : 0,
                        'cart_meta'  => ( isset( $meta['height'] ) ) ? $meta['height'] : 0,
                        'order_meta' => ( isset( $meta['height'] ) ) ? $meta['height'] : 0
                    )
                );
            } else {
                return array(
                    $post_name             => array(
                        'calc'       => 0,
                        'cart_meta'  => $form_data[ $post_name ],
                        'order_meta' => ( ! empty( basename( $meta['url'] ) ) && isset( $full_file_name ) )
	                        ? esc_html( $full_file_name )
	                        : $form_data[ $post_name ]
                    ),
                    $post_name . '_width'  => array(
                        'calc'       => ( isset( $meta['width'] ) ) ? $meta['width'] : 0,
                        'cart_meta'  => ( isset( $meta['width'] ) ) ? $meta['width'] : 0,
                        'order_meta' => ( isset( $meta['width'] ) ) ? $meta['width'] : 0
                    ),
                    $post_name . '_height' => array(
                        'calc'       => ( isset( $meta['height'] ) ) ? $meta['height'] : 0,
                        'cart_meta'  => ( isset( $meta['height'] ) ) ? $meta['height'] : 0,
                        'order_meta' => ( isset( $meta['height'] ) ) ? $meta['height'] : 0
                    )
                );
            }
        } else {
            return array(
                $post_name             => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                ),
                $post_name . '_width'  => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                ),
                $post_name . '_height' => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                )
            );
        }
    }

}
