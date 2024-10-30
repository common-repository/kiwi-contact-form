<?php
/**
** A base module for the following types of tags:
** 	[number] and [number*]		# Number
** 	[range] and [range*]		# Range
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_number', 10, 0 );

function kiwi_cf_add_form_tag_number() {
	kiwi_cf_add_form_tag( array( 'number', 'number*', 'range', 'range*' ),
		'kiwi_cf_number_form_tag_handler', array( 'name-attr' => true ) );
}

function kiwi_cf_number_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = kiwi_cf_get_validation_error( $tag->name );

	$class = kiwi_cf_form_controls_class( $tag->type );

	$class .= ' kiwi-validates-as-number';

	if ( $validation_error ) {
		$class .= ' kiwi-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['min'] = $tag->get_option( 'min', 'signed_int', true );
	$atts['max'] = $tag->get_option( 'max', 'signed_int', true );
	$atts['step'] = $tag->get_option( 'step', 'int', true );

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' )
	or $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = kiwi_cf_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( kiwi_cf_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts = kiwi_cf_format_atts( $atts );

	$html = sprintf(
		'<span class="kiwi-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'kiwi_cf_validate_number', 'kiwi_cf_number_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_number*', 'kiwi_cf_number_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_range', 'kiwi_cf_number_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_range*', 'kiwi_cf_number_validation_filter', 10, 2 );

function kiwi_cf_number_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? sanitize_text_field( trim( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	$min = $tag->get_option( 'min', 'signed_int', true );
	$max = $tag->get_option( 'max', 'signed_int', true );

	if ( $tag->is_required() and '' == $value ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_required' ) );
	} elseif ( '' != $value and ! kiwi_cf_is_number( $value ) ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_number' ) );
	} elseif ( '' != $value and '' != $min and (float) $value < (float) $min ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'number_too_small' ) );
	} elseif ( '' != $value and '' != $max and (float) $max < (float) $value ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'number_too_large' ) );
	}

	return $result;
}


/* Messages */

add_filter( 'kiwi_cf_messages', 'kiwi_cf_number_messages', 10, 1 );

function kiwi_cf_number_messages( $messages ) {
	return array_merge( $messages, array(
		'invalid_number' => array(
			'description' => __( "Number format that the sender entered is invalid", 'kiwi-contact-form' ),
			'default' => __( "The number format is invalid.", 'kiwi-contact-form' )
		),

		'number_too_small' => array(
			'description' => __( "Number is smaller than minimum limit", 'kiwi-contact-form' ),
			'default' => __( "The number is smaller than the minimum allowed.", 'kiwi-contact-form' )
		),

		'number_too_large' => array(
			'description' => __( "Number is larger than maximum limit", 'kiwi-contact-form' ),
			'default' => __( "The number is larger than the maximum allowed.", 'kiwi-contact-form' )
		),
	) );
}


/* Tag generator */

add_action( 'kiwi_cf_admin_init', 'kiwi_cf_add_tag_generator_number', 18, 0 );

function kiwi_cf_add_tag_generator_number() {
	$tag_generator = KiwiCfTagGenerator::get_instance();
	$tag_generator->add( 'number', __( 'number', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_number' );
}

function kiwi_cf_tag_generator_number( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'number';

	$description = __( "Generate a form-tag for a field for numeric value input. For more details, see %s.", 'kiwi-contact-form' );

	$desc_link = kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Number Fields', 'kiwi-contact-form' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field type', 'kiwi-contact-form' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'kiwi-contact-form' ) ); ?></legend>
		<select name="tagtype">
			<option value="number" selected="selected"><?php echo esc_html( __( 'Spinbox', 'kiwi-contact-form' ) ); ?></option>
			<option value="range"><?php echo esc_html( __( 'Slider', 'kiwi-contact-form' ) ); ?></option>
		</select>
		<br />
		<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'kiwi-contact-form' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
	<label><input type="checkbox" name="placeholder" class="option" /> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'kiwi-contact-form' ) ); ?></label></td>
	</tr>

	<tr>
	<th scope="row"><?php echo esc_html( __( 'Range', 'kiwi-contact-form' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Range', 'kiwi-contact-form' ) ); ?></legend>
		<label>
		<?php echo esc_html( __( 'Min', 'kiwi-contact-form' ) ); ?>
		<input type="number" name="min" class="numeric option" />
		</label>
		&ndash;
		<label>
		<?php echo esc_html( __( 'Max', 'kiwi-contact-form' ) ); ?>
		<input type="number" name="max" class="numeric option" />
		</label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>
</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'kiwi-contact-form' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'kiwi-contact-form' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
}
