<?php

function kiwi_cf_current_action() {
	if ( isset( $_REQUEST['action'] ) and -1 != $_REQUEST['action'] ) {
		return $_REQUEST['action'];
	}

	if ( isset( $_REQUEST['action2'] ) and -1 != $_REQUEST['action2'] ) {
		return $_REQUEST['action2'];
	}

	return false;
}

function kiwi_cf_admin_has_edit_cap() {
	return current_user_can( 'kiwi_cf_edit_contact_forms' );
}

function kiwi_cf_add_tag_generator( $name, $title, $elm_id, $callback, $options = array() ) {
	$tag_generator = KiwiCfTagGenerator::get_instance();
	return $tag_generator->add( $name, $title, $callback, $options );
}
