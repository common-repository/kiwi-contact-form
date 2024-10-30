<?php

class KiwiCfContactFormTemplate {

	public static function get_default( $prop = 'form' ) {
		if ( 'form' == $prop ) {
			$template = self::form();
		} elseif ( 'mail' == $prop ) {
			$template = self::mail();
		} elseif ( 'mail_2' == $prop ) {
			$template = self::mail_2();
		} elseif ( 'messages' == $prop ) {
			$template = self::messages();
		} else {
			$template = null;
		}

		return apply_filters( 'kiwi_cf_default_template', $template, $prop );
	}

	public static function form() {
		$template = sprintf(
			'
<label> %2$s %1$s
    [text* your-name] </label>

<label> %3$s %1$s
    [email* your-email] </label>

<label> %4$s
    [text your-subject] </label>

<label> %5$s
    [textarea your-message] </label>

[submit "%6$s"]',
			__( '(required)', ' kiwi-contact-form' ),
			__( 'Your Name', ' kiwi-contact-form' ),
			__( 'Your Email', ' kiwi-contact-form' ),
			__( 'Subject', ' kiwi-contact-form' ),
			__( 'Your Message', ' kiwi-contact-form' ),
			__( 'Send', ' kiwi-contact-form' ) );

		return trim( $template );
	}

	public static function mail() {
		$template = array(
			'subject' =>
				sprintf(
					/* translators: 1: blog name, 2: [your-subject] */
					_x( '%1$s "%2$s"', 'mail subject', ' kiwi-contact-form' ),
					get_bloginfo( 'name' ),
					'[your-subject]'
				),
			'sender' => sprintf( '%s <%s>',
				get_bloginfo( 'name' ), self::from_email() ),
			'body' =>
				/* translators: %s: [your-name] <[your-email]> */
				sprintf( __( 'From: %s', ' kiwi-contact-form' ),
					'[your-name] <[your-email]>' ) . "\n"
				/* translators: %s: [your-subject] */
				. sprintf( __( 'Subject: %s', ' kiwi-contact-form' ),
					'[your-subject]' ) . "\n\n"
				. __( 'Message Body:', ' kiwi-contact-form' )
					. "\n" . '[your-message]' . "\n\n"
				. '-- ' . "\n"
				. sprintf(
					/* translators: 1: blog name, 2: blog URL */
					__( 'This e-mail was sent from a contact form on %1$s (%2$s)', ' kiwi-contact-form' ),
					get_bloginfo( 'name' ),
					get_bloginfo( 'url' )
				),
			'recipient' => get_option( 'admin_email' ),
			'additional_headers' => 'Reply-To: [your-email]',
			'attachments' => '',
			'use_html' => 0,
			'exclude_blank' => 0,
		);

		return $template;
	}

	public static function mail_2() {
		$template = array(
			'active' => false,
			'subject' =>
				sprintf(
					/* translators: 1: blog name, 2: [your-subject] */
					_x( '%1$s "%2$s"', 'mail subject', ' kiwi-contact-form' ),
					get_bloginfo( 'name' ),
					'[your-subject]'
				),
			'sender' => sprintf( '%s <%s>',
				get_bloginfo( 'name' ), self::from_email() ),
			'body' =>
				__( 'Message Body:', ' kiwi-contact-form' )
					. "\n" . '[your-message]' . "\n\n"
				. '-- ' . "\n"
				. sprintf(
					/* translators: 1: blog name, 2: blog URL */
					__( 'This e-mail was sent from a contact form on %1$s (%2$s)', ' kiwi-contact-form' ),
					get_bloginfo( 'name' ),
					get_bloginfo( 'url' )
				),
			'recipient' => '[your-email]',
			'additional_headers' => sprintf( 'Reply-To: %s',
				get_option( 'admin_email' ) ),
			'attachments' => '',
			'use_html' => 0,
			'exclude_blank' => 0,
		);

		return $template;
	}

	public static function from_email() {
		$admin_email = get_option( 'admin_email' );
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );

		if ( kiwi_cf_is_localhost() ) {
			return $admin_email;
		}

		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
			return $admin_email;
		}

		return 'wordpress@' . $sitename;
	}

	public static function messages() {
		$messages = array();

		foreach ( kiwi_cf_messages() as $key => $arr ) {
			$messages[$key] = $arr['default'];
		}

		return $messages;
	}
}

function kiwi_cf_messages() {
	$messages = array(
		'mail_sent_ok' => array(
			'description'
				=> __( "Sender's message was sent successfully", ' kiwi-contact-form' ),
			'default'
				=> __( "Thank you for your message. It has been sent.", ' kiwi-contact-form' ),
		),

		'mail_sent_ng' => array(
			'description'
				=> __( "Sender's message failed to send", ' kiwi-contact-form' ),
			'default'
				=> __( "There was an error trying to send your message. Please try again later.", ' kiwi-contact-form' ),
		),

		'validation_error' => array(
			'description'
				=> __( "Validation errors occurred", ' kiwi-contact-form' ),
			'default'
				=> __( "One or more fields have an error. Please check and try again.", ' kiwi-contact-form' ),
		),

		'spam' => array(
			'description'
				=> __( "Submission was referred to as spam", ' kiwi-contact-form' ),
			'default'
				=> __( "There was an error trying to send your message. Please try again later.", ' kiwi-contact-form' ),
		),

		'accept_terms' => array(
			'description'
				=> __( "There are terms that the sender must accept", ' kiwi-contact-form' ),
			'default'
				=> __( "You must accept the terms and conditions before sending your message.", ' kiwi-contact-form' ),
		),

		'invalid_required' => array(
			'description'
				=> __( "There is a field that the sender must fill in", ' kiwi-contact-form' ),
			'default'
				=> __( "The field is required.", ' kiwi-contact-form' ),
		),

		'invalid_too_long' => array(
			'description'
				=> __( "There is a field with input that is longer than the maximum allowed length", ' kiwi-contact-form' ),
			'default'
				=> __( "The field is too long.", ' kiwi-contact-form' ),
		),

		'invalid_too_short' => array(
			'description'
				=> __( "There is a field with input that is shorter than the minimum allowed length", ' kiwi-contact-form' ),
			'default'
				=> __( "The field is too short.", ' kiwi-contact-form' ),
		)
	);

	return apply_filters( 'kiwi_cf_messages', $messages );
}
