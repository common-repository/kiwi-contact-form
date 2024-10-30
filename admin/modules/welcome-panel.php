<?php

function kiwi_cf_welcome_panel() {
	$classes = 'welcome-panel';

	$vers = (array) get_user_meta( get_current_user_id(),
		'kiwi_cf_hide_welcome_panel_on', true );

?>
<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
	<?php wp_nonce_field( 'kiwi-welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
	<a class="welcome-panel-close" href="<?php echo esc_url( menu_page_url( 'kiwi', false ) ); ?>"><?php echo esc_html( __( 'Dismiss', 'kiwi-contact-form' ) ); ?></a>

	<div class="welcome-panel-content">
		<div class="welcome-panel-column-container">

			<div class="welcome-panel-column">
				<h3><span class="dashicons dashicons-shield" aria-hidden="true"></span> <?php echo esc_html( __( "Getting spammed? You have protection.", 'kiwi-contact-form' ) ); ?></h3>

				<p><?php echo esc_html( __( "As any contact form can be spammed, Kiwi Contact Form provides you its digital doctors and prescriptions that guarantee to protect your contact forms from being spammed.", 'kiwi-contact-form' ) ); ?></p>

				<p><?php
	echo sprintf(
		/* translators: links labeled 1: 'Akismet', 2: 'reCAPTCHA', 3: 'comment blacklist' */
		esc_html( __( 'Kiwi Contact Form supports you Akismet WordPress Anti-Spam Plugin. Plus, Smart reCAPTCHA blocks annoying spambots. Else, using comment blacklist, you can block messages which contain specified keywords or those sent from specified IP addresses.', 'kiwi-contact-form' ) ),
		kiwi_cf_link(
			__( ' ', 'kiwi-contact-form' ),
			__( 'Akismet', 'kiwi-contact-form' )
		),
		kiwi_cf_link(
			__( ' ', 'kiwi-contact-form' ),
			__( 'reCAPTCHA', 'kiwi-contact-form' )
		),
		kiwi_cf_link(
			__( ' ', 'kiwi-contact-form' ),
			__( 'comment blacklist', 'kiwi-contact-form' )
		)
	);
				?></p>
			</div>



		</div>
	</div>
</div>
<?php
}

add_action( 'wp_ajax_kiwi-update-welcome-panel',
	'kiwi_cf_admin_ajax_welcome_panel', 10, 0 );

function kiwi_cf_admin_ajax_welcome_panel() {
	check_ajax_referer( 'kiwi-welcome-panel-nonce', 'welcomepanelnonce' );

	$vers = get_user_meta( get_current_user_id(),
		'kiwi_cf_hide_welcome_panel_on', true );

	if ( empty( $vers ) or ! is_array( $vers ) ) {
		$vers = array();
	}

	if ( empty( $_POST['visible'] ) ) {
		$vers[] = kiwi_cf_version( 'only_major=1' );
	}

	$vers = array_unique( $vers );

	update_user_meta( get_current_user_id(),
		'kiwi_cf_hide_welcome_panel_on', $vers );

	wp_die( 1 );
}
