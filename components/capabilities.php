<?php

add_filter( 'map_meta_cap', 'kiwi_cf_map_meta_cap', 10, 4 );

function kiwi_cf_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'kiwi_cf_edit_contact_form' => KIWI_CF_ADMIN_READ_WRITE_CAPABILITY,
		'kiwi_cf_edit_contact_forms' => KIWI_CF_ADMIN_READ_WRITE_CAPABILITY,
		'kiwi_cf_read_contact_form' => KIWI_CF_ADMIN_READ_CAPABILITY,
		'kiwi_cf_read_contact_forms' => KIWI_CF_ADMIN_READ_CAPABILITY,
		'kiwi_cf_delete_contact_form' => KIWI_CF_ADMIN_READ_WRITE_CAPABILITY,
		'kiwi_cf_delete_contact_forms' => KIWI_CF_ADMIN_READ_WRITE_CAPABILITY,
		'kiwi_cf_manage_integration' => 'manage_options',
		'kiwi_cf_submit' => 'read',
	);

	$meta_caps = apply_filters( 'kiwi_cf_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	return $caps;
}
