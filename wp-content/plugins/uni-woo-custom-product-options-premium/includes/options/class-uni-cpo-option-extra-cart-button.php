<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*   Uni_Cpo_Option_Dynamic_Notice class
*
*/

class Uni_Cpo_Option_Extra_Cart_Button extends Uni_Cpo_Option implements Uni_Cpo_Option_Interface {

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
		return 'extra_cart_button';
	}

	public static function get_title() {
		return __( 'Extra Cart Button', 'uni-cpo' );
	}

	/**
	 * Returns an array of special vars associated with the option
	 *
	 * @return array
	 */
	public static function get_special_vars() {
		return array();
	}

	/**
	 * Returns an array of data used in js query builder
	 *
	 * @return array
	 */
	public static function get_filter_data() {
		$operators = array(
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
			'special_vars' => array()
		);
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

		return stripslashes_deep( $model );
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
                    'main' => array(
                        'width_type' => '',
                        'width' => array(
                            'value' => '',
                            'unit' => 'px'
                        ),
                        'height' => array(
                            'value' => '42',
                            'unit' => 'px'
                        )
                    ),
				),
				'style'           => array(
                    'text' => array(
                        'color' => '#ffffff',
                        'color_hover' => '',
                        'text_align' => 'center',
                    ),
                    'font' => array(
                        'font_family' => 'inherit',
                        'font_style' => 'inherit',
                        'font_weight' => '',
                        'font_size' => array(
                            'value' => '16',
                            'unit' => 'px'
                        ),
                        'letter_spacing' => ''
                    ),
                    'background' => array(
                        'background_color' => '#3bc5b6',
                        'background_hover_color' => ''
                    ),
                    'border' => array(
                        'border_unit' => 'px',
                        'border_top' => array(
                            'style' => 'none',
                            'width' => '',
                            'color' => ''
                        ),
                        'border_bottom' => array(
                            'style' => 'none',
                            'width' => '',
                            'color' => ''
                        ),
                        'border_left' => array(
                            'style' => 'none',
                            'width' => '',
                            'color' => ''
                        ),
                        'border_right' => array(
                            'style' => 'none',
                            'width' => '',
                            'color' => ''
                        ),
                        'radius' => array(
                            'value' => '4',
                            'unit' => 'px'
                        ),
                    )
				),
				'advanced'        => array(
                    'layout' => array(
                        'margin' => array(
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'unit' => 'px'
                        ),
                        'padding' => array(
                            'top' => 10,
                            'right' => 20,
                            'bottom' => 10,
                            'left' => 20,
                            'unit' => 'px'
                        )
                    ),
                    'selectors' => array(
                        'id_name' => '',
                        'class_name' => ''
                    )
				),
				'cpo_general'     => array(
					'main'     => array(
						'cpo_slug'           => '',
						'cpo_addtocart_mode' => 'regular',
						'cpo_samples_mode'   => 'regular'
					),
					'advanced' => array(
						'cpo_label'        => __('Add to cart', 'uni-cpo')
					)
				),
				'cpo_conditional' => array(
					'main' => array(
						'cpo_is_fc'      => 'no',
						'cpo_fc_default' => 'hide',
						'cpo_fc_scheme'  => ''
					)
				)
			)
		);
	}

	public static function js_template() {
		?>
        <script id="js-builderius-module-<?php echo self::get_type(); ?>-tmpl" type="text/template">
            {{ const { id, type } = data; }}
            {{ const { id_name, class_name } = data.settings.advanced.selectors; }}
            {{ const { width_type, width } = data.settings.general.main; }}
            {{ const height = uniGet(data, 'settings.general.main.height', ''); }}
			{{ const { color, text_align, color_hover } = data.settings.style.text; }}
			{{ const { font_family, font_style, font_weight, font_size, letter_spacing } = data.settings.style.font; }}
            {{ const { border_unit, border_top, border_bottom, border_left, border_right, radius } = data.settings.style.border; }}
            {{ const { background_color, background_hover_color } = data.settings.style.background; }}
            {{ const { margin, padding } = data.settings.advanced.layout; }}
            {{ const { cpo_slug } = data.settings.cpo_general.main; }}
            {{ const { cpo_label } = data.settings.cpo_general.advanced; }}
            <div
                id="{{- id_name }}"
                class="uni-module uni-module-{{- type }} uni-node-{{- id }} {{- class_name }}"
                data-node="{{- id }}"
                data-type="{{- type }}">
                <style type="text/css">
                    .uni-node-{{= id }} button, .uni-node-{{= id }} button:active, .uni-node-{{= id }} button:focus {
                        {{ if ( width_type == 'custom' ) { }} width: {{= width.value+width.unit }}; {{ } }}
                        {{ if ( height.value !== '' ) { }} height: {{= height.value+height.unit }}; {{ } }}
                        {{ if ( color !== '' ) { }} color: {{= color }}!important; {{ } }}
                        {{ if ( text_align !== '' ) { }} text-align: {{= text_align }}; {{ } }}
                        {{ if ( font_family !== 'inherit' ) { }} font-family: {{= font_family }}; {{ } }}
                        {{ if ( font_style !== 'inherit' ) { }} font-style: {{= font_style }}; {{ } }}
                        {{ if ( font_size.value !== '' ) { }} font-size: {{= font_size.value+font_size.unit }}; {{ } }}
                        {{ if ( font_weight !== '' ) { }} font-weight: {{= font_weight }}; {{ } }}
                        {{ if ( letter_spacing !== '' ) { }} letter-spacing: {{= letter_spacing+'em' }}; {{ } }}
                        {{ if ( background_color !== '' ) { }} background-color: {{= background_color }}!important; {{ } }}
                        {{ if ( border_top.style !== 'none' && border_top.color !== '' ) { }} border-top: {{= border_top.width + 'px '+ border_top.style +' '+ border_top.color }}!important;; {{ } }}
                        {{ if ( border_bottom.style !== 'none' && border_bottom.color !== '' ) { }} border-bottom: {{= border_bottom.width + 'px '+ border_bottom.style +' '+ border_bottom.color }}!important;; {{ } }}
                        {{ if ( border_left.style !== 'none' && border_left.color !== '' ) { }} border-left: {{= border_left.width + 'px '+ border_left.style +' '+ border_left.color }}!important;; {{ } }}
                        {{ if ( border_right.style !== 'none' && border_right.color !== '' ) { }} border-right: {{= border_right.width + 'px '+ border_right.style +' '+ border_right.color }}!important;; {{ } }}
                        {{ if ( radius.value !== '' ) { }} border-radius: {{= radius.value + radius.unit }}; {{ } }}
                        {{ if ( margin.top !== '' ) { }} margin-top: {{= margin.top + margin.unit }}; {{ } }}
                        {{ if ( margin.bottom !== '' ) { }} margin-bottom: {{= margin.bottom + margin.unit }}; {{ } }}
                        {{ if ( margin.left !== '' ) { }} margin-left: {{= margin.left + margin.unit }}; {{ } }}
                        {{ if ( margin.right !== '' ) { }} margin-right: {{= margin.right + margin.unit }}; {{ } }}
                        {{ if ( padding.top !== '' ) { }} padding-top: {{= padding.top + padding.unit }}; {{ } }}
                        {{ if ( padding.bottom !== '' ) { }} padding-bottom: {{= padding.bottom + padding.unit }}; {{ } }}
                        {{ if ( padding.left !== '' ) { }} padding-left: {{= padding.left + padding.unit }}; {{ } }}
                        {{ if ( padding.right !== '' ) { }} padding-right: {{= padding.right + padding.unit }}; {{ } }}
                    }
                    {{ if ( color_hover !== '' || background_hover_color !== '' ) { }}
                        .uni-node-{{= id }} button:hover { color: {{= color_hover }}!important; background-color: {{= background_hover_color }}!important; }
                    {{ } }}
                </style>
		            <button
				            class="button alt {{- cpo_slug }}-field js-uni-cpo-field-{{- type }}"
				            id="{{- cpo_slug }}-field"
				            name="{{- cpo_slug }}"
				            type="button"
		            value="{{- builderiusCfg.product.id }}">{{- cpo_label }}</button>
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
		$wrapper_attributes   = array();
		$option               = false;
		$rules_data           = $data['settings']['cpo_conditional']['main'];
		$is_enabled           = ( 'yes' === $rules_data['cpo_is_fc'] ) ? true : false;
		$is_hidden            = ( 'hide' === $rules_data['cpo_fc_default'] ) ? true : false;
		$is_addtocart_regular = ( isset( $cpo_general_main['cpo_addtocart_mode'] ) &&
		                          'samples' === $cpo_general_main['cpo_addtocart_mode'] )
			? false
			: true;
		$is_set_zero_price = ( isset( $cpo_general_main['cpo_samples_mode'] ) &&
		                          'free' === $cpo_general_main['cpo_samples_mode'] )
			? 0
			: 1;

		if ( ! empty( $data['pid'] ) ) {
			$option = uni_cpo_get_option( $data['pid'] );
		}

		$slug              = ( $pid && is_object( $option ) && 'trash' !== $option->get_status() ) ? $option->get_slug() : '';
		$css_id[]          = $slug;
		$css_class         = array(
			'uni-module',
			'uni-module-' . $type,
			'uni-node-' . $id
		);
		$input_css_class[] = $slug . '-field';
		$input_css_class[] = 'js-uni-cpo-field';
		$input_css_class[] = 'js-uni-cpo-field-' . $type;
		if ( ! empty( $selectors['id_name'] ) ) {
			array_push( $css_id, $selectors['id_name'] );
		}
		if ( ! empty( $selectors['class_name'] ) ) {
			array_push( $css_class, $selectors['class_name'] );
		}

		if ( $is_enabled && $is_hidden ) {
			$wrapper_attributes['style'] = 'display:none;';
			$input_css_class[]           = 'uni-cpo-excluded-field';
		}

		$wrapper_attributes = apply_filters( 'uni_wrapper_attributes_for_option', $wrapper_attributes, $slug, $id );
		?>
    	<div
            id="<?php echo implode( ' ', array_map( function ( $el ) { return esc_attr( $el ); }, $css_id ) ); ?>"
            class="<?php echo implode( ' ', array_map( function ( $el ) { return esc_attr( $el ); }, $css_class ) ); ?>"
			<?php echo self::get_custom_attribute_html( $wrapper_attributes ); ?>>
		    <button
				    class="button alt uni_cpo_ajax_add_to_cart <?php echo implode( ' ', array_map( function ( $el ) {
					    return esc_attr( $el );
				    }, $input_css_class ) ); ?>"
				    id="<?php echo esc_attr( $slug ); ?>-field"
				    name="<?php echo esc_attr( $slug ); ?>_btn"
				    type="button"
				    value="<?php echo get_the_ID(); ?>"><?php esc_html_e( uni_cpo_get_proper_option_label_sp( $cpo_general_advanced['cpo_label'] ) ); ?></button>
		    <?php if ( ! $is_addtocart_regular ) { ?>
		    <input type="hidden" class="js-uni-cpo-field" name="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $is_set_zero_price ) ?>" />
			<?php } ?>
        </div>

		<?php
		self::conditional_rules( $data );
	}

	public static function get_css( $data ) {
		$id            = $data['id'];
		$main          = $data['settings']['general']['main'];
		$text          = $data['settings']['style']['text'];
		$font          = $data['settings']['style']['font'];
		$background    = $data['settings']['style']['background'];
		$border_top    = $data['settings']['style']['border']['border_top'];
		$border_bottom = $data['settings']['style']['border']['border_bottom'];
		$border_left   = $data['settings']['style']['border']['border_left'];
		$border_right  = $data['settings']['style']['border']['border_right'];
		$radius        = $data['settings']['style']['border']['radius'];
		$padding       = $data['settings']['advanced']['layout']['padding'];
		$margin        = $data['settings']['advanced']['layout']['margin'];

		if ( ! empty( $data['pid'] ) ) {
			$option = uni_cpo_get_option( $data['pid'] );
		}
		$slug = ( ! empty( $data['pid'] ) && is_object( $option ) ) ? $option->get_slug() : '';

		ob_start();
		?>
        .uni-node-<?php echo esc_attr( $id ); ?> button, .uni-node-<?php echo esc_attr( $id ); ?> button:active, .uni-node-<?php echo esc_attr( $id ); ?> button:focus {
            <?php if ( $main['width_type'] === 'custom' ) { ?> width: <?php echo esc_attr( "{$main['width']['value']}{$main['width']['unit']}" ) ?>; <?php } ?>
            <?php if ( $main['height']['value'] !== '' ) { ?> height: <?php echo esc_attr( "{$main['height']['value']}{$main['height']['unit']}" ) ?>;<?php } ?>
            <?php if ( $text['color'] !== '' ) { ?> color: <?php echo esc_attr( $text['color'] ); ?>!important;<?php } ?>
            <?php if ( $text['text_align'] !== '' ) { ?> text-align: <?php echo esc_attr( $text['text_align'] ); ?>;<?php } ?>
            <?php if ( $font['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $font['font_family'] ); ?>;<?php } ?>
            <?php if ( $font['font_style'] !== 'inherit' ) { ?> font-style: <?php echo esc_attr( $font['font_style'] ); ?>;<?php } ?>
            <?php if ( $font['font_weight'] !== '' ) { ?> font-weight: <?php echo esc_attr( $font['font_weight'] ); ?>;<?php } ?>
            <?php if ( $font['font_size']['value'] !== '' ) { ?> font-size: <?php echo esc_attr( "{$font['font_size']['value']}{$font['font_size']['unit']}" ) ?>; <?php } ?>
            <?php if ( $font['letter_spacing'] !== '' ) { ?> letter-spacing: <?php echo esc_attr( $font['letter_spacing'] ); ?>em;<?php } ?>
            <?php if ( $background['background_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $background['background_color'] ); ?>!important;<?php } ?>
            <?php if ( $border_top['style'] !== 'none' && $border_top['color'] !== '' ) { ?> border-top: <?php echo esc_attr( "{$border_top['width']}px {$border_top['style']} {$border_top['color']}" ) ?>!important;; <?php } ?>
            <?php if ( $border_bottom['style'] !== 'none' && $border_bottom['color'] !== '' ) { ?> border-bottom: <?php echo esc_attr( "{$border_bottom['width']}px {$border_bottom['style']} {$border_bottom['color']}" ) ?>!important;; <?php } ?>
            <?php if ( $border_left['style'] !== 'none' && $border_left['color'] !== '' ) { ?> border-left: <?php echo esc_attr( "{$border_left['width']}px {$border_left['style']} {$border_left['color']}" ) ?>!important;; <?php } ?>
            <?php if ( $border_right['style'] !== 'none' && $border_right['color'] !== '' ) { ?> border-right: <?php echo esc_attr( "{$border_right['width']}px {$border_right['style']} {$border_right['color']}" ) ?>!important;; <?php } ?>
            <?php if ( $radius['value'] !== '' ) { ?> border-radius: <?php echo esc_attr( "{$radius['value']}{$radius['unit']}" ) ?>; <?php } ?>
            <?php if ( $margin['top'] !== '' ) { ?> margin-top: <?php echo esc_attr( "{$margin['top']}{$margin['unit']}" ) ?>; <?php } ?>
            <?php if ( $margin['bottom'] !== '' ) { ?> margin-bottom: <?php echo esc_attr( "{$margin['bottom']}{$margin['unit']}" ) ?>; <?php } ?>
            <?php if ( $margin['left'] !== '' ) { ?> margin-left: <?php echo esc_attr( "{$margin['left']}{$margin['unit']}" ) ?>; <?php } ?>
            <?php if ( $margin['right'] !== '' ) { ?> margin-right: <?php echo esc_attr( "{$margin['right']}{$margin['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['top'] !== '' ) { ?> padding-top: <?php echo esc_attr( "{$padding['top']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['bottom'] !== '' ) { ?> padding-bottom: <?php echo esc_attr( "{$padding['bottom']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['left'] !== '' ) { ?> padding-left: <?php echo esc_attr( "{$padding['left']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['right'] !== '' ) { ?> padding-right: <?php echo esc_attr( "{$padding['right']}{$padding['unit']}" ) ?>; <?php } ?>
        }

        <?php
        if ( $text['color_hover'] !== '' || $background['background_hover_color'] ) { ?>
            .uni-node-<?php echo esc_attr( $id ); ?> button:hover {
                color: <?php echo esc_attr( $text['color_hover'] ); ?>!important;
                background-color: <?php echo esc_attr( $background['background_hover_color'] ); ?>!important;
            }
        <?php } ?>

		<?php
		return ob_get_clean();
	}

	public function calculate( $form_data ) {
		$post_name = trim( $this->get_slug(), '{}' );

		if ( ! empty( $form_data[ $post_name ] ) || 0 === absint( $form_data[ $post_name ] ) ) {
			return absint( $form_data[ $post_name ] );
		} else {
			return false;
		}
	}

}
