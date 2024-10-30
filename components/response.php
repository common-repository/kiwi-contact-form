<?php
/**
** A base module for [response]
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_response', 10, 0 );

function kiwi_cf_add_form_tag_response() {
	kiwi_cf_add_form_tag( 'response', 'kiwi_cf_response_form_tag_handler',
		array( 'display-block' => true ) );
}

function kiwi_cf_response_form_tag_handler( $tag ) {
	if ( $contact_form = kiwi_cf_get_current_contact_form() ) {
		return $contact_form->form_response_output();
	}
}
