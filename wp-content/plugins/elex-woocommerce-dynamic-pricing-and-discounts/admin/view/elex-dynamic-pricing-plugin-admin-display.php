<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( isset( $_REQUEST['tab'] ) ) {
	$active_tab = sanitize_text_field( $_REQUEST['tab'] );
} else {
	$active_tab = 'product_rules';
}
if ( isset( $_REQUEST['submit'] ) ) {
	//check_admin_referer( 'save_rule_'.$_REQUEST['update'] );
	$dp_path = ELEX_DP_ROOT_PATH_BASIC . 'admin/data/settings_page/elex-save-options.php';
	if ( file_exists( $dp_path ) == true ) {
		include_once( $dp_path );
	}
}
if ( isset( $_REQUEST['cancel_btn'] ) ) {
	wp_safe_redirect( admin_url( 'admin.php?page=dynamic-pricing-main-page&tab=' . $active_tab ) );
	die();
}

if ( isset( $_REQUEST['update'] ) && empty( $_REQUEST['update'] ) ) {    //Submit And Not Edit Then Saving New Record
	check_admin_referer( 'save_rule' );
	$current_tab_loc = ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) ? sanitize_text_field( $_REQUEST['tab'] ) . '/' : 'product_rules/';
	$dp_path = ELEX_DP_ROOT_PATH_BASIC . 'admin/data/' . $current_tab_loc . 'elex-save-options.php';
	if ( file_exists( $dp_path ) == true ) {
		include_once( $dp_path );
	}
} elseif ( isset( $_REQUEST['edit'] ) && ! empty( $_REQUEST['edit'] ) ) {    //Loading Edit Form Or Updating Data
	$old_option = get_option( 'xa_dp_rules', array( $active_tab => array() ) );
	$old_option = $old_option [ $active_tab ];
	$_REQUEST = array_merge( $_REQUEST, $old_option [ sanitize_text_field( $_REQUEST['edit'] ) ] );
	$current_tab_loc = isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) . '/' : 'product_rules/';
	$dp_path = ELEX_DP_ROOT_PATH_BASIC . 'admin/data/' . $current_tab_loc . 'elex-load-edit.php';
	include_once( $dp_path );
} elseif ( ! empty( $_REQUEST['update'] ) ) {
	check_admin_referer( 'update_rule_' . sanitize_text_field( $_REQUEST['update'] ) );
	$dp_path = isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) . '/' : 'product_rules/';
	$dp_path = ELEX_DP_ROOT_PATH_BASIC . 'admin/data/' . $dp_path . 'elex-update-options.php';
	include_once( $dp_path );
} else {
	$active_tab = 'product_rules';
}
require( 'tabs/elex-tabs-html-render.php' );
