<?php

add_action( 'parse_request', 'kiwi_cf_control_init', 20, 0 );

function kiwi_cf_control_init() {
	if ( Kiwi_CF_Submission::is_restful() ) {
		return;
	}

	if ( isset( $_POST['_kiwi'] ) ) {
		$contact_form = kiwi_cf_contact_form( (int) $_POST['_kiwi'] );

		if ( $contact_form ) {
			$contact_form->submit();
		}
	}
}

add_action( 'wp_enqueue_scripts', 'kiwi_cf_do_enqueue_scripts', 10, 0 );

function kiwi_cf_do_enqueue_scripts() {
	if ( kiwi_cf_load_js() ) {
		kiwi_cf_enqueue_scripts();
	}

	if ( kiwi_cf_load_css() ) {
		kiwi_cf_enqueue_styles();
	}
}

function kiwi_cf_enqueue_scripts() {
	$in_footer = true;

	if ( 'header' === kiwi_cf_load_js() ) {
		$in_footer = false;
	}

	wp_enqueue_script( 'kiwi-contact-form',
		kiwi_cf_plugin_url( 'client/view/js/scripts.js' ),
		array( 'jquery' ), KIWI_CF_VERSION, $in_footer );

	$kiwi = array(
		'apiSettings' => array(
			'root' => esc_url_raw( rest_url( 'kiwi-contact-form/v1' ) ),
			'namespace' => 'kiwi-contact-form/v1',
		),
	);

	if ( defined( 'WP_CACHE' ) and WP_CACHE ) {
		$kiwi['cached'] = 1;
	}

	if ( kiwi_cf_support_html5_fallback() ) {
		$kiwi['jqueryUi'] = 1;
	}

	wp_localize_script( 'kiwi-contact-form', 'kiwi', $kiwi );

	do_action( 'kiwi_cf_enqueue_scripts' );
}

function kiwi_cf_script_is() {
	return wp_script_is( 'kiwi-contact-form' );
}

function kiwi_cf_enqueue_styles() {
	wp_enqueue_style( 'kiwi-contact-form',
		kiwi_cf_plugin_url( 'client/views/css/styles.css' ),
		array(), KIWI_CF_VERSION, 'all' );

	if ( kiwi_cf_is_rtl() ) {
		wp_enqueue_style( 'kiwi-contact-form-rtl',
			kiwi_cf_plugin_url( 'client/views/styles-rtl.css' ),
			array(), KIWI_CF_VERSION, 'all' );
	}

	do_action( 'kiwi_cf_enqueue_styles' );
}

function kiwi_cf_style_is() {
	return wp_style_is( 'kiwi-contact-form' );
}

/* HTML5 Fallback */

add_action( 'wp_enqueue_scripts', 'kiwi_cf_html5_fallback', 20, 0 );

function kiwi_cf_html5_fallback() {
	if ( ! kiwi_cf_support_html5_fallback() ) {
		return;
	}

	if ( kiwi_cf_script_is() ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-spinner' );
	}

	if ( kiwi_cf_style_is() ) {
		wp_enqueue_style( 'jquery-ui-smoothness',
			kiwi_cf_plugin_url(
				'includes/js/jquery-ui/themes/smoothness/jquery-ui.min.css' ),
			array(), '1.11.4', 'screen' );
	}
}
