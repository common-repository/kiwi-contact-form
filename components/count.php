<?php
/**
** A base module for [count], Twitter-like character count
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_count', 10, 0 );

function kiwi_cf_add_form_tag_count() {
	kiwi_cf_add_form_tag( 'count',
		'kiwi_cf_count_form_tag_handler',
		array(
			'name-attr' => true,
			'zero-controls-container' => true,
			'not-for-mail' => true,
		)
	);
}

function kiwi_cf_count_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$targets = kiwi_cf_scan_form_tags( array( 'name' => $tag->name ) );
	$maxlength = $minlength = null;

	while ( $targets ) {
		$target = array_shift( $targets );

		if ( 'count' != $target->type ) {
			$maxlength = $target->get_maxlength_option();
			$minlength = $target->get_minlength_option();
			break;
		}
	}

	if ( $maxlength and $minlength
	and $maxlength < $minlength ) {
		$maxlength = $minlength = null;
	}

	if ( $tag->has_option( 'down' ) ) {
		$value = (int) $maxlength;
		$class = 'kiwi-character-count down';
	} else {
		$value = '0';
		$class = 'kiwi-character-count up';
	}

	$atts = array();
	$atts['id'] = $tag->get_id_option();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['data-target-name'] = $tag->name;
	$atts['data-starting-value'] = $value;
	$atts['data-current-value'] = $value;
	$atts['data-maximum-value'] = $maxlength;
	$atts['data-minimum-value'] = $minlength;
	$atts = kiwi_cf_format_atts( $atts );

	$html = sprintf( '<span %1$s>%2$s</span>', $atts, $value );

	return $html;
}
