<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
*   Uni_Cpo_Option_Select class
*
*/

class Uni_Cpo_Option_Matrix extends Uni_Cpo_Option implements Uni_Cpo_Option_Interface {

    /**
     * Stores extra (specific to this) option data.
     *
     * @var array
     */
    protected $extra_data = array(
        'cpo_matrix' => array()
    );

    /**
     * Constructor gets the post object and sets the ID for the loaded option.
     *
     */
    public function __construct( $option = 0 ) {

        parent::__construct( $option );

    }

    public static function get_type() {
        return 'matrix';
    }

    public static function get_title() {
        return __( 'Matrix', 'uni-cpo' );
    }

    /**
     * Returns an array of special vars associated with the option
     *
     * @return array
     */
    public static function get_special_vars() {
        return array( 'col', 'row' );
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
            'special_vars' => array(
                'col' => array(
                    'type'      => 'string',
                    'input'     => 'text',
                    'operators' => $operators
                ),
                'row' => array(
                    'type'      => 'string',
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

    /**
     * Get suboptions
     *
     * @param  string $context
     *
     * @return string
     */
    public function get_cpo_matrix( $context = 'view' ) {
        return $this->get_prop( 'cpo_matrix', $context );
    }

    public function get_cpo_rate() {
        $cpo_general = $this->get_cpo_general();

        return ( ! empty( $cpo_general['main']['cpo_rate'] ) ) ? floatval( $cpo_general['main']['cpo_rate'] ) : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

    /**
     * Set suboptions.
     *
     * @param string $options
     */
    public function set_cpo_matrix( $options ) {
        $this->set_prop( 'cpo_matrix', $options );
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
        $model['settings']['cpo_matrix']                      = $this->get_cpo_matrix();
        $model['settings']['cpo_conditional']                 = $this->get_cpo_conditional();

        return stripslashes_deep( $model );
    }

    public function get_edit_field( $data, $value, $context = 'cart' ) {
        // Silence
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
                    'table_head'                   => array(
                        'color'          => '#333333',
                        'font_family'    => 'inherit',
                        'font_style'     => 'inherit',
                        'font_weight'    => '',
                        'font_size_px'   => 14,
                        'letter_spacing' => ''
                    ),
                    'table_head_background'        => array(
                        'background_color'       => '',
                        'background_hover_color' => ''
                    ),
                    'table_body'                   => array(
                        'color'          => '#333333',
                        'font_family'    => 'inherit',
                        'font_style'     => 'inherit',
                        'font_weight'    => '',
                        'font_size_px'   => 14,
                        'letter_spacing' => ''
                    ),
                    'table_body_background'        => array(
                        'background_color'       => '',
                        'background_hover_color' => ''
                    ),
                    'table_tr_even_background'     => array(
                        'background_color'       => '',
                        'background_hover_color' => ''
                    ),
                    'table_cell_active_background' => array(
                        'background_color' => '#e8e8e8'
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
                        ),
                        'padding' => array(
                            'top'    => 10,
                            'right'  => 14,
                            'bottom' => 10,
                            'left'   => 14,
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
                        'cpo_is_required' => 'no'
                    ),
                    'advanced' => array(
                        'cpo_label'       => '',
                        'cpo_label_tag'   => 'label',
                        'cpo_order_label' => '',
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
                'cpo_matrix'      => array(
                    'data' => array(
                        'cpo_matrix_data' => array(
                            'in_col'   => '',
                            'in_row'   => '',
                            'template' => '{{{row}}}, {{{col}}}',
                            'json'     => ''
                        )
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
            {{ const { cpo_slug, cpo_is_required } = data.settings.cpo_general.main; }}

            {{ const color_label = uniGet( data.settings.style, 'label.color', '' ); }}
            {{ const text_align_label = uniGet( data.settings.style, 'label.text_align_label', 'inherit' ); }}
            {{ const font_family_label = uniGet( data.settings.style, 'label.font_family', '' ); }}
            {{ const font_weight_label = uniGet( data.settings.style, 'label.font_weight', '' ); }}
            {{ const font_size_label = uniGet( data.settings.style, 'label.font_size_label', {value:'',unit:'px'} ); }}

            {{ const { color:th_color, text_align:th_text_align, font_family:th_font_family, font_style:th_font_style, font_weight:th_font_weight, font_size_px:th_font_size_px, letter_spacing:th_letter_spacing } = data.settings.style.table_head; }}
            {{ const { background_color:th_background_color } = data.settings.style.table_head_background; }}
            {{ const { color:td_color, text_align:td_text_align, font_family:td_font_family, font_style:td_font_style, font_weight:td_font_weight, font_size_px:td_font_size_px, letter_spacing:td_letter_spacing } = data.settings.style.table_body; }}
            {{ const { background_color:td_background_color } = data.settings.style.table_body_background; }}
            {{ const { background_color:td_even_background_color } = data.settings.style.table_tr_even_background; }}

            {{ const { margin, padding } = data.settings.advanced.layout; }}

            {{ const { cpo_label_tag, cpo_label, cpo_is_tooltip, cpo_tooltip } = data.settings.cpo_general.advanced; }}
            {{ const cpo_tooltip_type = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_type', 'classic' ); }}
			{{ const cpo_tooltip_image = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_image', {url:''} ); }}
            {{ const cpo_tooltip_class = uniGet( data.settings.cpo_general, 'advanced.cpo_tooltip_class', '' ); }}
            {{ let cols = uniGet( data.settings.cpo_matrix, 'data.cpo_matrix_data.in_col', 'Please add matrix content'); }}
            {{ let rows = uniGet( data.settings.cpo_matrix, 'data.cpo_matrix_data.json', ''); }}
            {{ cols = cols.split('|'); if ( rows !== '' ) { rows = JSON.parse(rows); } else { rows = []; } }}
            <div
                    id="{{- id_name }}"
                    class="uni-module uni-module-{{- type }} uni-node-{{- id }} {{- class_name }}"
                    data-node="{{- id }}"
                    data-type="{{- type }}">
                <style>
                    .uni-node-{{= id }} {
                        {{ if ( margin.top ) { }} margin-top: {{= margin.top + margin.unit }}!important; {{ } }}
                        {{ if ( margin.bottom ) { }} margin-bottom: {{= margin.bottom + margin.unit }}!important; {{ } }}
                        {{ if ( margin.left ) { }} margin-left: {{= margin.left + margin.unit }}!important; {{ } }}
                        {{ if ( margin.right ) { }} margin-right: {{= margin.right + margin.unit }}!important; {{ } }}
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
                    .uni-node-{{= id }} table th, .uni-node-{{= id }} table td {
                        {{ if ( padding.top ) { }} padding-top: {{= padding.top + padding.unit }}!important; {{ } }}
                        {{ if ( padding.bottom ) { }} padding-bottom: {{= padding.bottom + padding.unit }}!important; {{ } }}
                        {{ if ( padding.left ) { }} padding-left: {{= padding.left + padding.unit }}!important; {{ } }}
                        {{ if ( padding.right ) { }} padding-right: {{= padding.right + padding.unit }}!important; {{ } }}
                    }
                    .uni-node-{{= id }} table th {
                        {{ if ( th_color !== '' ) { }} color: {{= th_color }}!important; {{ } }}
                        {{ if ( th_font_family !== 'inherit' ) { }} font-family: {{= th_font_family }}!important; {{ } }}
                        {{ if ( th_font_style !== 'inherit' ) { }} font-style: {{= th_font_style }}!important; {{ } }}
                        {{ if ( th_font_size_px !== '' ) { }} font-size: {{= th_font_size_px+'px' }}!important; {{ } }}
                        {{ if ( th_font_weight !== '' ) { }} font-weight: {{= th_font_weight }}!important; {{ } }}
                        {{ if ( th_letter_spacing !== '' ) { }} letter-spacing: {{= th_letter_spacing+'em' }}!important; {{ } }}
                        {{ if ( th_background_color !== '' ) { }} background-color: {{= th_background_color }}; {{ } }}
                    }
                    .uni-node-{{= id }} table td {
                        {{ if ( td_color !== '' ) { }} color: {{= td_color }}!important; {{ } }}
                        {{ if ( td_font_family !== 'inherit' ) { }} font-family: {{= td_font_family }}!important; {{ } }}
                        {{ if ( td_font_style !== 'inherit' ) { }} font-style: {{= td_font_style }}!important; {{ } }}
                        {{ if ( td_font_size_px !== '' ) { }} font-size: {{= td_font_size_px+'px' }}!important; {{ } }}
                        {{ if ( td_font_weight !== '' ) { }} font-weight: {{= td_font_weight }}!important; {{ } }}
                        {{ if ( td_letter_spacing !== '' ) { }} letter-spacing: {{= td_letter_spacing+'em' }}!important; {{ } }}
                        {{ if ( td_background_color !== '' ) { }} background-color: {{= td_background_color }}; {{ } }}
                    }
                    .uni-node-{{= id }} table tr:nth-child(even) td {
                        {{ if ( td_even_background_color !== '' ) { }} background-color: {{= td_even_background_color }}; {{ } }}
                    }
                </style>
            {{ if ( cpo_label_tag && cpo_label ) { }}
                <{{- cpo_label_tag }} class="uni-cpo-module-{{- type }}-label {{ if ( cpo_is_required === 'yes' ) { }} uni_cpo_field_required {{ } }}">
                {{- cpo_label }}
                {{ if ( cpo_is_tooltip === 'yes' && cpo_tooltip !== '' && cpo_tooltip_type === 'classic' ) { }} <span class="uni-cpo-tooltip" data-tip="{{- cpo_tooltip }}"></span> {{ } else if ( cpo_is_tooltip === 'yes' && cpo_tooltip_image.url !== '' && cpo_tooltip_type === 'lightbox' ) { }} <span class="uni-cpo-tooltip"></span> {{ } else if ( cpo_is_tooltip === 'yes' && cpo_tooltip_class !== '' && cpo_tooltip_type === 'popup' ) { }} <span class="uni-cpo-tooltip"></span> {{ } }}
            </{{- cpo_label_tag }}>
            {{ } }}
            <input
                    class="{{- cpo_slug }}-field js-uni-cpo-field-{{- type }}"
                    id="{{- cpo_slug }}-field-col"
                    name="{{- cpo_slug }}_col"
                    type="hidden"
                    value=""/>
            <input
                    class="{{- cpo_slug }}-field js-uni-cpo-field-{{- type }}"
                    id="{{- cpo_slug }}-field-row"
                    name="{{- cpo_slug }}_row"
                    type="hidden"
                    value=""/>
            <table>
                <tr>
                    <th></th>
                    {{ cols.forEach(function(col) { col = col.split(' : ').pop(); }}
                    <th>{{- col }}</th>
                    {{ }); }}
                </tr>
                {{ rows.forEach(function(row, i) { }}
                <tr>
                    {{ row.columns.pop(); }}
                    {{ row.columns.forEach(function(value) { }}
                        {{ if (typeof value === 'string' && value.indexOf(' : ') !== -1) { value = value.split(' : ').pop(); } }}
                        <td>{{- value }}</td>
                    {{ }); }}
                </tr>
                {{ }); }}
            </table>
            </div>
        </script>
        <?php
    }

    public static function template( $data, $post_data = array() ) {
        $id          = $data['id'];
        $type        = $data['type'];
        $selectors   = $data['settings']['advanced']['selectors'];
        $matrix_data = ( isset( $data['settings']['cpo_matrix']['data']['cpo_matrix_data'] ) )
            ? $data['settings']['cpo_matrix']['data']['cpo_matrix_data']
            : array();
        $cols        = explode( "|", $matrix_data['in_col'] );
        $rows        = explode( "|", $matrix_data['in_row'] );
        $json        = json_decode( $matrix_data['json'], true );

        $cpo_general_main     = $data['settings']['cpo_general']['main'];
        $cpo_general_advanced = $data['settings']['cpo_general']['advanced'];
        $cpo_validation_main  = ( isset( $data['settings']['cpo_validation']['main'] ) )
            ? $data['settings']['cpo_validation']['main']
            : array();
        $cpo_label_tag        = $cpo_general_advanced['cpo_label_tag'];
        $attributes           = array(
			'data-parsley-trigger'          => 'change submit',
			'data-parsley-errors-container' => '.uni-node-' . $id,
			'data-parsley-class-handler'    => '.uni-node-' . $id
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

        if ( ! empty( $data['pid'] ) ) {
            $option = uni_cpo_get_option( $data['pid'] );
        }

        $slug              = ( ! empty( $data['pid'] ) && is_object( $option ) && 'trash' !== $option->get_status() ) ? $option->get_slug() : '';
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
        $default_value     = ( ! empty( $post_data ) && ! empty( $slug ) && ! empty( $post_data[ $slug ] ) )
            ? $post_data[ $slug ]
            : '';
        $default_value_col = ( ! empty( $post_data ) && ! empty( $slug ) && ! empty( $post_data[ $slug . '_col' ] ) )
            ? $post_data[ $slug . '_col' ]
            : '';
        $default_value_row = ( ! empty( $post_data ) && ! empty( $slug ) && ! empty( $post_data[ $slug . '_row' ] ) )
            ? $post_data[ $slug . '_row' ]
            : '';
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
                value="<?php echo esc_attr( $default_value ) ?>"
            <?php echo self::get_custom_attribute_html( $attributes ); ?> />
        <input
                id="<?php echo esc_attr( $slug ); ?>-field-col"
                class="js-uni-cpo-field"
                name="<?php echo esc_attr( $slug ); ?>_col"
                type="hidden"
                value="<?php echo esc_attr( $default_value_col ) ?>" />
        <input
                id="<?php echo esc_attr( $slug ); ?>-field-row"
                class="js-uni-cpo-field"
                name="<?php echo esc_attr( $slug ); ?>_row"
                type="hidden"
                value="<?php echo esc_attr( $default_value_row ) ?>" />
        <table>
            <th></th>
            <?php
            foreach ( $cols as $col ) {
                $col_value = explode( ' : ', $col );
                ?>
                <th><?php echo ( ! empty( $col_value[1] ) ) ? esc_html_e( $col_value[1] ) : $col_value[0]; ?></th>
            <?php } ?>
            <?php foreach ( $rows as $key => $row ) : ?>
                <tr>
                    <?php $columns = $json[ $key ]['columns'];
                    array_pop( $columns );
                    foreach ( $columns as $key => $value ) {
                        if ( $key === 0 ) {
                            $row_value = explode( ' : ', $value );
                            ?>
                            <td><?php echo ( ! empty( $row_value[1] ) ) ? esc_html_e( $row_value[1] ) : $row_value[0]; ?></td>
                        <?php } else { ?>
                            <td class="uni-cell-with-span">
                                <span data-col="<?php echo $cols[ $key - 1 ]; ?>"
                                      data-row="<?php echo $row; ?>"><?php echo number_format( $value, 2 ); ?></span>
                            </td>
                        <?php }
                    }
                    ?>
                </tr>
            <?php endforeach; ?>
        </table>

        </div>
        <?php

        self::conditional_rules( $data );
    }

    public static function get_css( $data ) {
        $id            = $data['id'];
        $type          = $data['type'];
	    $label         = ( ! empty( $data['settings']['style']['label'] ) ) ? $data['settings']['style']['label'] : array();
        $th            = $data['settings']['style']['table_head'];
        $th_background = $data['settings']['style']['table_head_background'];
        $td            = $data['settings']['style']['table_body'];
        $td_background = $data['settings']['style']['table_body_background'];
        $tr_even       = $data['settings']['style']['table_tr_even_background'];
        $cell_active   = $data['settings']['style']['table_cell_active_background'];
        $margin        = $data['settings']['advanced']['layout']['margin'];
        $padding       = $data['settings']['advanced']['layout']['padding'];
        $cpo_general_advanced = $data['settings']['cpo_general']['advanced'];

        ob_start();
        ?>
        .uni-node-<?php echo esc_attr( $id ); ?> {
            <?php if ( ! empty( $margin['top'] ) ) { ?> margin-top: <?php echo esc_attr( "{$margin['top']}{$margin['unit']}" ) ?>!important; <?php } ?>
            <?php if ( ! empty( $margin['bottom'] ) ) { ?> margin-bottom: <?php echo esc_attr( "{$margin['bottom']}{$margin['unit']}" ) ?>!important; <?php } ?>
            <?php if ( ! empty( $margin['left'] ) ) { ?> margin-left: <?php echo esc_attr( "{$margin['left']}{$margin['unit']}" ) ?>!important; <?php } ?>
            <?php if ( ! empty( $margin['right'] ) ) { ?> margin-right: <?php echo esc_attr( "{$margin['right']}{$margin['unit']}" ) ?>!important; <?php } ?>
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
        .uni-node-<?php echo esc_attr( $id ); ?> table th, .uni-node-<?php echo esc_attr( $id ); ?> table td:not(.uni-cell-with-span), .uni-node-<?php echo esc_attr( $id ); ?> table td span {
            <?php if ( $padding['top'] !== '' ) { ?> padding-top: <?php echo esc_attr( "{$padding['top']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['bottom'] !== '' ) { ?> padding-bottom: <?php echo esc_attr( "{$padding['bottom']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['left'] !== '' ) { ?> padding-left: <?php echo esc_attr( "{$padding['left']}{$padding['unit']}" ) ?>; <?php } ?>
            <?php if ( $padding['right'] !== '' ) { ?> padding-right: <?php echo esc_attr( "{$padding['right']}{$padding['unit']}" ) ?>; <?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table th {
        <?php if ( ! empty( $th['color'] ) ) { ?> color: <?php echo esc_attr( $th['color'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $th['font_family'] ) && $th['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $th['font_family'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $th['font_style'] ) && $th['font_style'] !== 'inherit' ) { ?> font-style: <?php echo esc_attr( $th['font_style'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $th['font_weight'] ) ) { ?> font-weight: <?php echo esc_attr( $th['font_weight'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $th['font_size_px'] ) ) { ?> font-size: <?php echo esc_attr( $th['font_size_px'] ) ?>px!important; <?php } ?>
        <?php if ( ! empty( $th['letter_spacing'] ) ) { ?> letter-spacing: <?php echo esc_attr( $th['letter_spacing'] ); ?>em!important;<?php } ?>
        <?php if ( $th_background['background_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $th_background['background_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table th:hover {
        <?php if ( $th_background['background_hover_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $th_background['background_hover_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table td {
        <?php if ( ! empty( $td['color'] ) ) { ?> color: <?php echo esc_attr( $td['color'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $td['font_family'] ) && $td['font_family'] !== 'inherit' ) { ?> font-family: <?php echo esc_attr( $td['font_family'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $td['font_style'] ) && $td['font_style'] !== 'inherit' ) { ?> font-style: <?php echo esc_attr( $td['font_style'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $td['font_weight'] ) ) { ?> font-weight: <?php echo esc_attr( $td['font_weight'] ); ?>!important;<?php } ?>
        <?php if ( ! empty( $td['font_size_px'] ) ) { ?> font-size: <?php echo esc_attr( $td['font_size_px'] ) ?>px!important; <?php } ?>
        <?php if ( ! empty( $td['letter_spacing'] ) ) { ?> letter-spacing: <?php echo esc_attr( $td['letter_spacing'] ); ?>em!important;<?php } ?>
        <?php if ( $td_background['background_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $td_background['background_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table td:hover {
        <?php if ( $td_background['background_hover_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $td_background['background_hover_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table td span.uni-clicked {
        <?php if ( $cell_active['background_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $cell_active['background_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table tr:nth-child(even) td {
        <?php if ( $tr_even['background_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $tr_even['background_color'] ); ?>;<?php } ?>
        }
        .uni-node-<?php echo esc_attr( $id ); ?> table tr:nth-child(even) td:hover {
        <?php if ( $tr_even['background_hover_color'] !== '' ) { ?> background-color: <?php echo esc_attr( $tr_even['background_hover_color'] ); ?>;<?php } ?>
        }
        <?php
        return ob_get_clean();
    }

    public function calculate( $form_data ) {
        $post_name       = trim( $this->get_slug(), '{}' );
        $raw_matrix_data = $this->get_cpo_matrix();
        $matrix_data     = $raw_matrix_data['data']['cpo_matrix_data'];
        $template        = $matrix_data['template'];

        /*$cols_data = ( isset( $raw_data ) && ! empty( $raw_data ) && ! empty( $raw_data['data']['cpo_matrix_data']['in_col'] ) )
            ? explode( '|', $raw_data['data']['cpo_matrix_data']['in_col'] )
            : array();
        $rows_data = ( isset( $raw_data ) && ! empty( $raw_data ) && ! empty( $raw_data['data']['cpo_matrix_data']['in_row'] ) )
            ? explode( '|', $raw_data['data']['cpo_matrix_data']['in_row'] )
            : array();
		$json_data = ( isset( $raw_data ) && ! empty( $raw_data ) && ! empty( $raw_data['data']['cpo_matrix_data']['json'] ) )
            ? json_decode( $raw_data['data']['cpo_matrix_data']['json'], true )
            : array();*/

        $col_values = ( ! empty( $form_data[ $post_name . '_col' ] ) )
            ? explode( ' : ', $form_data[ $post_name . '_col' ] )
            : array();

        $row_values = ( ! empty( $form_data[ $post_name . '_row' ] ) )
            ? explode( ' : ', $form_data[ $post_name . '_row' ] )
            : array();

        if ( ! empty( $form_data[ $post_name ] ) ) {
            $col_slug   = ( isset( $col_values[0] ) ) ? $col_values[0] : '';
            $col_name   = ( ! empty( $col_values[1] ) ) ? __( $col_values[1] ) : __( $col_slug );
            $row_slug   = ( isset( $row_values[0] ) ) ? $row_values[0] : '';
            $row_name   = ( ! empty( $row_values[1] ) ) ? __( $row_values[1] ) : __( $row_slug );
            $cell_value = $form_data[ $post_name ];

            $formatted = str_replace( '{{{col}}}', $col_name, $template );
            $formatted = str_replace( '{{{row}}}', $row_name, $formatted );

            return array(
                $post_name          => array(
                    'calc'       => floatval( $cell_value ),
                    'cart_meta'  => $formatted,
                    'order_meta' => $formatted
                ),
                $post_name . '_col' => array(
                    'calc'       => ( is_numeric( $col_slug ) ) ? floatval( $col_slug ) : 0,
                    'cart_meta'  => $col_slug,
                    'order_meta' => $col_name
                ),
                $post_name . '_row' => array(
                    'calc'       => ( is_numeric( $row_slug ) ) ? floatval( $row_slug ) : 0,
                    'cart_meta'  => $row_slug,
                    'order_meta' => $row_name
                )
            );

        } else {
            return array(
                $post_name          => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                ),
                $post_name . '_col' => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                ),
                $post_name . '_row' => array(
                    'calc'       => 0,
                    'cart_meta'  => '',
                    'order_meta' => ''
                )
            );
        }
    }

}
