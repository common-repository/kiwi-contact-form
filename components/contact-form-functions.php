<?php

function kiwi_cf_contact_form( $id ) {
	return KiwiCfContactForm::get_instance( $id );
}

function kiwi_cf_get_contact_form_by_old_id( $old_id ) {
	global $wpdb;

	$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_old_cf7_unit_id'"
		. $wpdb->prepare( " AND meta_value = %d", $old_id );

	if ( $new_id = $wpdb->get_var( $q ) ) {
		return kiwi_cf_contact_form( $new_id );
	}
}

function kiwi_cf_get_contact_form_by_title( $title ) {
	$page = get_page_by_title( $title, OBJECT, KiwiCfContactForm::post_type );

	if ( $page ) {
		return kiwi_cf_contact_form( $page->ID );
	}

	return null;
}

function kiwi_cf_get_current_contact_form() {
	if ( $current = KiwiCfContactForm::get_current() ) {
		return $current;
	}
}

function kiwi_cf_is_posted() {
	if ( ! $contact_form = kiwi_cf_get_current_contact_form() ) {
		return false;
	}

	return $contact_form->is_posted();
}

function kiwi_cf_get_hangover( $name, $default = null ) {
	if ( ! kiwi_cf_is_posted() ) {
		return $default;
	}

	$submission = Kiwi_CF_Submission::get_instance();

	if ( ! $submission
	or $submission->is( 'mail_sent' ) ) {
		return $default;
	}

	return isset( $_POST[$name] ) ? wp_unslash( $_POST[$name] ) : $default;
}

function kiwi_cf_get_validation_error( $name ) {
	if ( ! $contact_form = kiwi_cf_get_current_contact_form() ) {
		return '';
	}

	return $contact_form->validation_error( $name );
}

function kiwi_cf_get_message( $status ) {
	if ( ! $contact_form = kiwi_cf_get_current_contact_form() ) {
		return '';
	}

	return $contact_form->message( $status );
}

function kiwi_cf_form_controls_class( $type, $default = '' ) {
	$type = trim( $type );
	$default = array_filter( explode( ' ', $default ) );

	$classes = array_merge( array( 'kiwi-form-control' ), $default );

	$typebase = rtrim( $type, '*' );
	$required = ( '*' == substr( $type, -1 ) );

	$classes[] = 'kiwi-' . $typebase;

	if ( $required ) {
		$classes[] = 'kiwi-validates-as-required';
	}

	$classes = array_unique( $classes );

	return implode( ' ', $classes );
}

function kiwi_cf_contact_form_tag_func( $atts, $content = null, $code = '' ) {
	if ( is_feed() ) {
		return '[kiwi-contact-form]';
	}

	if ( 'kiwi-contact-form' == $code ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
				'title' => '',
				'html_id' => '',
				'html_name' => '',
				'html_class' => '',
				'output' => 'form',
			),
			$atts, 'kiwi'
		);

		$id = (int) $atts['id'];
		$title = trim( $atts['title'] );

		if ( ! $contact_form = kiwi_cf_contact_form( $id ) ) {
			$contact_form = kiwi_cf_get_contact_form_by_title( $title );
		}

	} else {
		if ( is_string( $atts ) ) {
			$atts = explode( ' ', $atts, 2 );
		}

		$id = (int) array_shift( $atts );
		$contact_form = kiwi_cf_get_contact_form_by_old_id( $id );
	}

	if ( ! $contact_form ) {
		return '[kiwi-contact-form 404 "Not Found"]';
	}

	return $contact_form->form_html( $atts );
}

function kiwi_cf_save_contact_form( $args = '', $context = 'save' ) {
	$args = wp_parse_args( $args, array(
		'id' => -1,
		'title' => null,
		'locale' => null,
		'form' => null,
		'mail' => null,
		'mail_2' => null,
		'messages' => null,
		'additional_settings' => null,
	) );

	$args = wp_unslash( $args );

	$args['id'] = (int) $args['id'];

	if ( -1 == $args['id'] ) {
		$contact_form = KiwiCfContactForm::get_template();
	} else {
		$contact_form = kiwi_cf_contact_form( $args['id'] );
	}

	if ( empty( $contact_form ) ) {
		return false;
	}

	if ( null !== $args['title'] ) {
		$contact_form->set_title( $args['title'] );
	}

	if ( null !== $args['locale'] ) {
		$contact_form->set_locale( $args['locale'] );
	}

	$properties = array();

	if ( null !== $args['form'] ) {
		$properties['form'] = kiwi_cf_sanitize_form( $args['form'] );
	}

	if ( null !== $args['mail'] ) {
		$properties['mail'] = kiwi_cf_sanitize_mail( $args['mail'] );
		$properties['mail']['active'] = true;
	}

	if ( null !== $args['mail_2'] ) {
		$properties['mail_2'] = kiwi_cf_sanitize_mail( $args['mail_2'] );
	}

	if ( null !== $args['messages'] ) {
		$properties['messages'] = kiwi_cf_sanitize_messages( $args['messages'] );
	}

	if ( null !== $args['additional_settings'] ) {
		$properties['additional_settings'] = kiwi_cf_sanitize_additional_settings(
			$args['additional_settings']
		);
	}

	$contact_form->set_properties( $properties );

	do_action( 'kiwi_cf_save_contact_form', $contact_form, $args, $context );

	if ( 'save' == $context ) {
		$contact_form->save();
	}

	return $contact_form;
}

function kiwi_cf_sanitize_form( $input, $default = '' ) {
	if ( null === $input ) {
		return $default;
	}

	$output = trim( $input );
	return $output;
}

function kiwi_cf_sanitize_mail( $input, $defaults = array() ) {
	$input = wp_parse_args( $input, array(
		'active' => false,
		'subject' => '',
		'sender' => '',
		'recipient' => '',
		'body' => '',
		'additional_headers' => '',
		'attachments' => '',
		'use_html' => false,
		'exclude_blank' => false,
	) );

	$input = wp_parse_args( $input, $defaults );

	$output = array();
	$output['active'] = (bool) $input['active'];
	$output['subject'] = trim( $input['subject'] );
	$output['sender'] = trim( $input['sender'] );
	$output['recipient'] = trim( $input['recipient'] );
	$output['body'] = trim( $input['body'] );
	$output['additional_headers'] = '';

	$headers = str_replace( "\r\n", "\n", $input['additional_headers'] );
	$headers = explode( "\n", $headers );

	foreach ( $headers as $header ) {
		$header = trim( $header );

		if ( '' !== $header ) {
			$output['additional_headers'] .= $header . "\n";
		}
	}

	$output['additional_headers'] = trim( $output['additional_headers'] );
	$output['attachments'] = trim( $input['attachments'] );
	$output['use_html'] = (bool) $input['use_html'];
	$output['exclude_blank'] = (bool) $input['exclude_blank'];

	return $output;
}

function kiwi_cf_sanitize_messages( $input, $defaults = array() ) {
	$output = array();

	foreach ( kiwi_cf_messages() as $key => $val ) {
		if ( isset( $input[$key] ) ) {
			$output[$key] = trim( $input[$key] );
		} elseif ( isset( $defaults[$key] ) ) {
			$output[$key] = $defaults[$key];
		}
	}

	return $output;
}

function kiwi_cf_sanitize_additional_settings( $input, $default = '' ) {
	if ( null === $input ) {
		return $default;
	}

	$output = trim( $input );
	return $output;
}
