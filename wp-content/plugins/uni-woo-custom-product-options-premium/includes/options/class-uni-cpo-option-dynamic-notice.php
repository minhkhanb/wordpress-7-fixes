<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
*   Uni_Cpo_Option_Dynamic_Notice class
*
*/

class Uni_Cpo_Option_Dynamic_Notice extends Uni_Cpo_Option implements Uni_Cpo_Option_Interface {

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
		return 'dynamic_notice';
	}

	public static function get_title() {
		return __( 'Dynamic Notice', 'uni-cpo' );
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
					)
				),
				'style'           => array(
					'text' => array(
                        'color' => '',
                        'text_align' => ''
                    ),
                    'font' => array(
                        'font_family' => 'inherit',
                        'font_style' => 'inherit',
                        'font_weight' => '',
                        'font_size' => array(
                            'value' => '14',
                            'unit' => 'px'
                        ),
                        'letter_spacing' => '',
                        'line_height' => ''
                    ),
					'background' => array(
                        'background_color' => '',
                    ),
					'border' => array(
						'border_unit'   => 'px',
						'border_top'    => array(
							'style' => 'solid',
							'width' => '0',
							'color' => '#d7d7d7'
						),
						'border_bottom' => array(
							'style' => 'solid',
							'width' => '0',
							'color' => '#d7d7d7'
						),
						'border_left'   => array(
							'style' => 'solid',
							'width' => '0',
							'color' => '#d7d7d7'
						),
						'border_right'  => array(
							'style' => 'solid',
							'width' => '0',
							'color' => '#d7d7d7'
						),
						'radius'        => array(
							'value' => '',
							'unit'  => 'px'
						),
					),
				),
				'advanced'        => array(
					'layout' => array(
                        'margin' => array(
                            'top' => 0,
                            'right' => 0,
                            'bottom' => 0,
                            'left' => 0,
                            'unit' => 'px'
                        ),
                        'padding' => array(
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
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
						'cpo_slug'        => '',
						'cpo_notice_text' => ''
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
			{{ const { color, text_align } = data.settings.style.text; }}
			{{ const { font_family, font_style, font_weight, font_size, letter_spacing, line_height } = data.settings.style.font; }}
            {{ const { border_unit, border_top, border_bottom, border_left, border_right, radius } = data.settings.style.border; }}
            {{ const { background_color } = data.settings.style.background; }}
            {{ const { margin, padding } = data.settings.advanced.layout; }}
            {{ const { cpo_slug } = data.settings.cpo_general.main; }}
			{{ let cpo_notice_text = data.settings.cpo_general.main.cpo_notice_text.replace(/\{#([\s\S]+?)#\}/g, ''); }}
            <div
                id="{{- id_name }}"
                class="uni-module uni-module-{{- type }} uni-node-{{- id }} {{- class_name }}"
                data-node="{{- id }}"
                data-type="{{- type }}">
	            <style>
					.uni-node-{{= id }} .{{- cpo_slug }}-field {
						{{ if ( color !== '' ) { }} color: {{= color }}; {{ } }}
						{{ if ( text_align !== '' ) { }} text-align: {{= text_align }}; {{ } }}
						{{ if ( font_family !== 'inherit' ) { }} font-family: {{= font_family }}; {{ } }}
						{{ if ( font_style !== 'inherit' ) { }} font-style: {{= font_style }}; {{ } }}
						{{ if ( font_size.value !== '' ) { }} font-size: {{= font_size.value+font_size.unit }}; {{ } }}
						{{ if ( font_weight !== '' ) { }} font-weight: {{= font_weight }}; {{ } }}
						{{ if ( letter_spacing !== '' ) { }} letter-spacing: {{= letter_spacing+'em' }}; {{ } }}
						{{ if ( line_height !== '' ) { }} line-height: {{= line_height+'px' }}; {{ } }}
						{{ if ( margin.top !== '' ) { }} margin-top: {{= margin.top + margin.unit }}; {{ } }}
						{{ if ( margin.bottom !== '' ) { }} margin-bottom: {{= margin.bottom + margin.unit }}; {{ } }}
						{{ if ( margin.left !== '' ) { }} margin-left: {{= margin.left + margin.unit }}; {{ } }}
						{{ if ( margin.right !== '' ) { }} margin-right: {{= margin.right + margin.unit }}; {{ } }}
						{{ if ( padding.top !== '' ) { }} padding-top: {{= padding.top + padding.unit }}; {{ } }}
						{{ if ( padding.bottom !== '' ) { }} padding-bottom: {{= padding.bottom + padding.unit }}; {{ } }}
						{{ if ( padding.left !== '' ) { }} padding-left: {{= padding.left + padding.unit }}; {{ } }}
						{{ if ( padding.right !== '' ) { }} padding-right: {{= padding.right + padding.unit }}; {{ } }}
						{{ if ( background_color !== '' ) { }} background-color: {{= background_color }}; {{ } }}
	                    {{ if ( border_top.style !== 'none' && border_top.color !== '' ) { }} border-top: {{= border_top.width + 'px '+ border_top.style +' '+ border_top.color }}; {{ } }}
	                    {{ if ( border_bottom.style !== 'none' && border_bottom.color !== '' ) { }} border-bottom: {{= border_bottom.width + 'px '+ border_bottom.style +' '+ border_bottom.color }}; {{ } }}
	                    {{ if ( border_left.style !== 'none' && border_left.color !== '' ) { }} border-left: {{= border_left.width + 'px '+ border_left.style +' '+ border_left.color }}; {{ } }}
	                    {{ if ( border_right.style !== 'none' && border_right.color !== '' ) { }} border-right: {{= border_right.width + 'px '+ border_right.style +' '+ border_right.color }}; {{ } }}
						{{ if ( radius.value !== '' ) { }} border-radius: {{= radius.value + radius.unit }}; {{ } }}
					}
	        	</style>
				<div
	                id="{{- cpo_slug }}-field"
	                class="{{- cpo_slug }}-field js-uni-cpo-field-{{- type }}">
					{{= cpo_notice_text }}</div>
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
		$cpo_notice_text      = $cpo_general_main['cpo_notice_text'];
		$wrapper_attributes   = array();
		$option               = false;
		$rules_data           = $data['settings']['cpo_conditional']['main'];
		$is_enabled           = ( 'yes' === $rules_data['cpo_is_fc'] ) ? true : false;
		$is_hidden            = ( 'hide' === $rules_data['cpo_fc_default'] ) ? true : false;

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
			<div
				id="<?php echo esc_attr( $slug ); ?>-field"
				class="<?php echo implode( ' ', array_map( function ( $el ) {
					return esc_attr( $el );
				}, $input_css_class ) ); ?>"></div>

				<script
					type="text/template"
					id="cpo-tmpl-<?php echo esc_attr( $slug ); ?>"><?php echo __( html_entity_decode( uni_cpo_sanitize_text( $cpo_notice_text ) ) ); ?></script>
				<?php if ( ! empty( $slug ) ) { ?>
					<script>
		                jQuery( document ).ready( function( $ ) {
		                    'use strict';

		                    try {

                                var <?php echo esc_attr( $slug ); ?>_notice = window.UniCpo.template('<?php echo esc_attr( $slug ); ?>');
                                var variables = $.extend({}, unicpo.formatted_vars, unicpo.price_vars);
                                if (typeof unicpo.errors !== undefined) {
                                    variables.errors = $.extend({}, unicpo.errors);
                                }
                                variables = uniData(variables);
                                variables.init();
                                var dNotices = document.getElementsByClassName('<?php echo esc_attr( $slug ); ?>-field');
                                jQuery(dNotices).each(function(i, el){
                                    jQuery(el).html(<?php echo esc_attr( $slug ); ?>_notice(variables));
                                });

                                jQuery(document.body).on('uni_cpo_options_data_ajax_success', function() {
                                    var variables = $.extend({}, unicpo.formatted_vars, unicpo.price_vars);
                                    if (typeof unicpo.errors !== undefined) {
                                        variables.errors = $.extend({}, unicpo.errors);
                                    }
                                    variables = uniData(variables);
                                    variables.init();
                                    var dNotices = document.getElementsByClassName('<?php echo esc_attr( $slug ); ?>-field');
                                    jQuery(dNotices).each(function(i, el){
                                        jQuery(el).html(<?php echo esc_attr( $slug ); ?>_notice(variables));
                                    });
                                });

                                jQuery(document.body).on('uni_cpo_options_data_after_validate_event uni_cpo_options_data_not_valid_event', function(e, fields) {
                                    var fieldsCopy = $.extend({}, fields);
                                    delete fieldsCopy.product_id;
                                    var variables = $.extend({}, unicpo.formatted_vars, unicpo.price_vars, fieldsCopy);
                                    if (typeof unicpo.errors !== undefined) {
                                        variables.errors = $.extend({}, unicpo.errors);
                                    }
                                    variables = uniData(variables);
                                    variables.init();
                                    var dNotices = document.getElementsByClassName('<?php echo esc_attr( $slug ); ?>-field');
                                    jQuery(dNotices).each(function(i, el){
                                        jQuery(el).html(<?php echo esc_attr( $slug ); ?>_notice(variables));
                                    });
                                });

                            } catch (err) {
		                        console.error(err);
		                    }
		                });
	                </script>
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
        .uni-node-<?php echo esc_attr( $id ); ?> .<?php echo esc_attr( $slug ); ?>-field {
			<?php if ( ! empty( $text['color'] ) ) { ?> color: <?php echo esc_attr( $text['color'] ); ?>;<?php } ?>
			<?php if ( ! empty( $text['text_align'] ) ) { ?> text-align: <?php echo esc_attr( $text['text_align'] ); ?>;<?php } ?>
			<?php if ( $font['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $font['font_family'] ); ?>;<?php } ?>
			<?php if ( $font['font_style'] !== 'inherit' ) { ?> font-style: <?php echo esc_attr( $font['font_style'] ); ?>;<?php } ?>
			<?php if ( ! empty( $font['font_weight'] ) ) { ?> font-weight: <?php echo esc_attr( $font['font_weight'] ); ?>;<?php } ?>
			<?php if ( ! empty( $font['font_size']['value'] ) ) { ?> font-size: <?php echo esc_attr( "{$font['font_size']['value']}{$font['font_size']['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $font['letter_spacing'] ) ) { ?> letter-spacing: <?php echo esc_attr( $font['letter_spacing'] ); ?>em;<?php } ?>
			<?php if ( ! empty( $font['line_height'] ) ) { ?> line-height: <?php echo esc_attr( $font['line_height'] ); ?>px;<?php } ?>
			<?php if ( ! empty( $margin['top'] ) ) { ?> margin-top: <?php echo esc_attr( "{$margin['top']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $margin['bottom'] ) ) { ?> margin-bottom: <?php echo esc_attr( "{$margin['bottom']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $margin['left'] ) ) { ?> margin-left: <?php echo esc_attr( "{$margin['left']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $margin['right'] ) ) { ?> margin-right: <?php echo esc_attr( "{$margin['right']}{$margin['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $padding['top'] ) ) { ?> padding-top: <?php echo esc_attr( "{$padding['top']}{$padding['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $padding['bottom'] ) ) { ?> padding-bottom: <?php echo esc_attr( "{$padding['bottom']}{$padding['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $padding['left'] ) ) { ?> padding-left: <?php echo esc_attr( "{$padding['left']}{$padding['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $padding['right'] ) ) { ?> padding-right: <?php echo esc_attr( "{$padding['right']}{$padding['unit']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $background['background_color'] ) ) { ?> background-color: <?php echo esc_attr( $background['background_color'] ); ?>;<?php } ?>
			<?php if ( $border_top['style'] !== 'none' && ! empty( $border_top['color'] ) ) { ?> border-top: <?php echo esc_attr( "{$border_top['width']}px {$border_top['style']} {$border_top['color']}" ) ?>; <?php } ?>
			<?php if ( $border_bottom['style'] !== 'none' && ! empty( $border_bottom['color'] ) ) { ?> border-bottom: <?php echo esc_attr( "{$border_bottom['width']}px {$border_bottom['style']} {$border_bottom['color']}" ) ?>; <?php } ?>
			<?php if ( $border_left['style'] !== 'none' && ! empty( $border_left['color'] ) ) { ?> border-left: <?php echo esc_attr( "{$border_left['width']}px {$border_left['style']} {$border_left['color']}" ) ?>; <?php } ?>
			<?php if ( $border_right['style'] !== 'none' && ! empty( $border_right['color'] ) ) { ?> border-right: <?php echo esc_attr( "{$border_right['width']}px {$border_right['style']} {$border_right['color']}" ) ?>; <?php } ?>
			<?php if ( ! empty( $radius['value'] ) ) { ?> border-radius: <?php echo esc_attr( "{$radius['value']}{$radius['unit']}" ) ?>; <?php } ?>
        }
		<?php
		return ob_get_clean();
	}

	public function calculate( $form_data ) {
        return false;
	}

}
