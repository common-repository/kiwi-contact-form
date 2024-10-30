<?php

add_action( 'kiwi_admin_menu', 'kiwi_cf_admin_init_bulk_cv', 10, 0 );

function kiwi_cf_admin_init_bulk_cv() {
	if ( ! kiwi_cf_validate_configuration()
	or ! current_user_can( 'kiwi_cf_edit_contact_forms' ) ) {
		return;
	}

	$result = KIWICF::get_option( 'bulk_validate' );
	$last_important_update = '5.1.5';

	if ( ! empty( $result['version'] )
	and version_compare( $last_important_update, $result['version'], '<=' ) ) {
		return;
	}

	add_filter( 'kiwi_cf_admin_menu_change_notice',
		'kiwi_cf_admin_menu_change_notice_bulk_cv', 10, 1 );

	add_action( 'kiwi_cf_admin_warnings',
		'kiwi_cf_admin_warnings_bulk_cv', 5, 3 );
}

function kiwi_cf_admin_menu_change_notice_bulk_cv( $counts ) {
	$counts['kiwi'] += 1;
	return $counts;
}

function kiwi_cf_admin_warnings_bulk_cv( $page, $action, $object ) {
	if ( 'kiwi' === $page and 'validate' === $action ) {
		return;
	}

	$link = kiwi_cf_link(
		add_query_arg(
			array( 'action' => 'validate' ),
			menu_page_url( 'kiwi', false )
		),
		__( 'Validate Kiwi Contact Form Configuration', 'kiwi-contact-form' )
	);

	$message = __( "Misconfiguration leads to mail delivery failure or other troubles. Validate your contact forms now.", 'kiwi-contact-form' );

	echo sprintf(
		'<div class="notice notice-warning"><p>%1$s &raquo; %2$s</p></div>',
		esc_html( $message ),
		$link
	);
}

add_action( 'kiwi_cf_admin_load', 'kiwi_cf_load_bulk_validate_page', 10, 2 );

function kiwi_cf_load_bulk_validate_page( $page, $action ) {
	if ( 'kiwi' != $page
	or 'validate' != $action
	or ! kiwi_cf_validate_configuration()
	or 'POST' != $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	check_admin_referer( 'kiwi-bulk-validate' );

	if ( ! current_user_can( 'kiwi_cf_edit_contact_forms' ) ) {
		wp_die( __( "You are not allowed to validate configuration.", 'kiwi-contact-form' ) );
	}

	$contact_forms = KiwiCfContactForm::find();

	$result = array(
		'timestamp' => current_time( 'timestamp' ),
		'version' => KIWI_CF_VERSION,
		'count_valid' => 0,
		'count_invalid' => 0,
	);

	foreach ( $contact_forms as $contact_form ) {
		$config_validator = new KiwiCfConfigValidator( $contact_form );
		$config_validator->validate();
		$config_validator->save();

		if ( $config_validator->is_valid() ) {
			$result['count_valid'] += 1;
		} else {
			$result['count_invalid'] += 1;
		}
	}

	KIWICF::update_option( 'bulk_validate', $result );

	$redirect_to = add_query_arg(
		array(
			'message' => 'validated',
		),
		menu_page_url( 'kiwi', false )
	);

	wp_safe_redirect( $redirect_to );
	exit();
}

function kiwi_cf_admin_bulk_validate_page() {
	$contact_forms = KiwiCfContactForm::find();
	$count = KiwiCfContactForm::count();

	$submit_text = sprintf(
		_n(
			/* translators: %s: number of contact forms */
			"Validate %s Contact Form Now",
			"Validate %s Contact Forms Now",
			$count, 'kiwi-contact-form'
		),
		number_format_i18n( $count )
	);

?>
<div class="wrap">

<h1><?php echo esc_html( __( 'Validate Configuration', 'kiwi-contact-form' ) ); ?></h1>

<form method="post" action="">
	<input type="hidden" name="action" value="validate" />
	<?php wp_nonce_field( 'kiwi-bulk-validate' ); ?>
	<p><input type="submit" class="button" value="<?php echo esc_attr( $submit_text ); ?>" /></p>
</form>

<?php
	echo kiwi_cf_link(
		__( '', 'kiwi-contact-form' ),
		__( 'FAQ about Configuration Validator', 'kiwi-contact-form' )
	);
?>

</div>
<?php
}
