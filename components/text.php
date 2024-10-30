<?php
/**
** A base module for the following types of tags:
** 	[text] and [text*]		# Single-line text
** 	[email] and [email*]	# Email address
** 	[url] and [url*]		# URL
** 	[tel] and [tel*]		# Telephone number
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_text', 10, 0 );

function kiwi_cf_add_form_tag_text() {
	kiwi_cf_add_form_tag(
		array( 'text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*' ),
		'kiwi_cf_text_form_tag_handler', array( 'name-attr' => true ) );
}

function kiwi_cf_text_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = kiwi_cf_get_validation_error( $tag->name );

	$class = kiwi_cf_form_controls_class( $tag->type, 'kiwi-text' );

	if ( in_array( $tag->basetype, array( 'email', 'url', 'tel' ) ) ) {
		$class .= ' kiwi-validates-as-' . $tag->basetype;
	}

	if ( $validation_error ) {
		$class .= ' kiwi-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if ( $atts['maxlength'] and $atts['minlength']
	and $atts['maxlength'] < $atts['minlength'] ) {
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$atts['autocomplete'] = $tag->get_option( 'autocomplete',
		'[-0-9a-zA-Z]+', true );

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

add_filter( 'kiwi_cf_validate_text', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_text*', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_email', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_email*', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_url', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_url*', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_tel', 'kiwi_cf_text_validation_filter', 10, 2 );
add_filter( 'kiwi_cf_validate_tel*', 'kiwi_cf_text_validation_filter', 10, 2 );

function kiwi_cf_text_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	if ( 'text' == $tag->basetype ) {
		if ( $tag->is_required() and '' == $value ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_required' ) );
		}
	}

	if ( 'email' == $tag->basetype ) {
		if ( $tag->is_required() and '' == $value ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_required' ) );
		} elseif ( '' != $value and ! kiwi_cf_is_email( $value ) ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_email' ) );
		}
	}

	if ( 'url' == $tag->basetype ) {
		if ( $tag->is_required() and '' == $value ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_required' ) );
		} elseif ( '' != $value and ! kiwi_cf_is_url( $value ) ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_url' ) );
		}
	}

	if ( 'tel' == $tag->basetype ) {
		if ( $tag->is_required() and '' == $value ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_required' ) );
		} elseif ( '' != $value and ! kiwi_cf_is_tel( $value ) ) {
			$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_tel' ) );
		}
	}

	if ( '' !== $value ) {
		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();

		if ( $maxlength and $minlength and $maxlength < $minlength ) {
			$maxlength = $minlength = null;
		}

		$code_units = kiwi_cf_count_code_units( stripslashes( $value ) );

		if ( false !== $code_units ) {
			if ( $maxlength and $maxlength < $code_units ) {
				$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_too_long' ) );
			} elseif ( $minlength and $code_units < $minlength ) {
				$result->invalidate( $tag, kiwi_cf_get_message( 'invalid_too_short' ) );
			}
		}
	}

	return $result;
}


/* Messages */

add_filter( 'kiwi_cf_messages', 'kiwi_cf_text_messages', 10, 1 );

function kiwi_cf_text_messages( $messages ) {
	$messages = array_merge( $messages, array(
		'invalid_email' => array(
			'description' =>
				__( "Email address that the sender entered is invalid", 'kiwi-contact-form' ),
			'default' =>
				__( "The e-mail address entered is invalid.", 'kiwi-contact-form' ),
		),

		'invalid_url' => array(
			'description' =>
				__( "URL that the sender entered is invalid", 'kiwi-contact-form' ),
			'default' =>
				__( "The URL is invalid.", 'kiwi-contact-form' ),
		),

		'invalid_tel' => array(
			'description' =>
				__( "Telephone number that the sender entered is invalid", 'kiwi-contact-form' ),
			'default' =>
				__( "The telephone number is invalid.", 'kiwi-contact-form' ),
		),
	) );

	return $messages;
}


/* Tag generator */

add_action( 'kiwi_cf_admin_init', 'kiwi_cf_add_tag_generator_text', 15, 0 );

function kiwi_cf_add_tag_generator_text() {
	$tag_generator = KiwiCfTagGenerator::get_instance();
	$tag_generator->add( 'text', __( 'text', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_text' );
	$tag_generator->add( 'email', __( 'email', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_text' );
	$tag_generator->add( 'url', __( 'URL', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_text' );
	$tag_generator->add( 'tel', __( 'tel', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_text' );
}

function kiwi_cf_tag_generator_text( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = $args['id'];

	if ( ! in_array( $type, array( 'email', 'url', 'tel' ) ) ) {
		$type = 'text';
	}

	if ( 'text' == $type ) {
		$description = __( "Generate a form-tag for a single-line plain text input field. For more details, see %s.", 'kiwi-contact-form' );
	} elseif ( 'email' == $type ) {
		$description = __( "Generate a form-tag for a single-line email address input field. For more details, see %s.", 'kiwi-contact-form' );
	} elseif ( 'url' == $type ) {
		$description = __( "Generate a form-tag for a single-line URL input field. For more details, see %s.", 'kiwi-contact-form' );
	} elseif ( 'tel' == $type ) {
		$description = __( "Generate a form-tag for a single-line telephone number input field. For more details, see %s.", 'kiwi-contact-form' );
	}

	$desc_link = kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Text Fields', 'kiwi-contact-form' ) );

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

<?php if ( in_array( $type, array( 'text', 'email', 'url' ) ) ) : ?>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Akismet', 'kiwi-contact-form' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Akismet', 'kiwi-contact-form' ) ); ?></legend>

<?php if ( 'text' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author" class="option" />
			<?php echo esc_html( __( "This field requires author's name", 'kiwi-contact-form' ) ); ?>
		</label>
<?php elseif ( 'email' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author_email" class="option" />
			<?php echo esc_html( __( "This field requires author's email address", 'kiwi-contact-form' ) ); ?>
		</label>
<?php elseif ( 'url' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author_url" class="option" />
			<?php echo esc_html( __( "This field requires author's URL", 'kiwi-contact-form' ) ); ?>
		</label>
<?php endif; ?>

		</fieldset>
	</td>
	</tr>
<?php endif; ?>

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
