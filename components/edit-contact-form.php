<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

function kiwi_cf_admin_save_button( $post_id ) {
	static $button = '';

	if ( ! empty( $button ) ) {
		echo $button;
		return;
	}

	$nonce = wp_create_nonce( 'kiwi-save-contact-form_' . $post_id );

	$onclick = sprintf(
		"this.form._wpnonce.value = '%s';"
		. " this.form.action.value = 'save';"
		. " return true;",
		$nonce );

	$button = sprintf(
		'<input type="submit" class="button-primary" name="kiwi-save" value="%1$s" onclick="%2$s" />',
		esc_attr( __( 'Save', 'kiwi-contact-form' ) ),
		$onclick );

	echo $button;
}

?><div class="wrap" id="kiwi-contact-form-editor">

<h1 class="wp-heading-inline"><?php
	if ( $post->initial() ) {
		echo esc_html( __( 'Add New Contact Form', 'kiwi-contact-form' ) );
	} else {
		echo esc_html( __( 'Edit Contact Form', 'kiwi-contact-form' ) );
	}
?></h1>

<?php
	if ( ! $post->initial()
	and current_user_can( 'kiwi_cf_edit_contact_forms' ) ) {
		echo kiwi_cf_link(
			menu_page_url( 'kiwi-new', false ),
			__( 'Add New', 'kiwi-contact-form' ),
			array( 'class' => 'page-title-action' )
		);
	}
?>

<hr class="wp-header-end">

<?php
	do_action( 'kiwi_cf_admin_warnings',
		$post->initial() ? 'kiwi-new' : 'kiwi',
		kiwi_cf_current_action(),
		$post
	);

	do_action( 'kiwi_cf_admin_notices',
		$post->initial() ? 'kiwi-new' : 'kiwi',
		kiwi_cf_current_action(),
		$post
	);
?>

<?php
if ( $post ) :

	if ( current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ) {
		$disabled = '';
	} else {
		$disabled = ' disabled="disabled"';
	}
?>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'kiwi', false ) ) ); ?>" id="kiwi-admin-form-element"<?php do_action( 'kiwi_cf_post_edit_form_tag' ); ?>>
<?php
	if ( current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ) {
		wp_nonce_field( 'kiwi-save-contact-form_' . $post_id );
	}
?>
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
<input type="hidden" id="kiwi-locale" name="kiwi-locale" value="<?php echo esc_attr( $post->locale() ); ?>" />
<input type="hidden" id="hiddenaction" name="action" value="save" />
<input type="hidden" id="active-tab" name="active-tab" value="<?php echo isset( $_GET['active-tab'] ) ? (int) $_GET['active-tab'] : '0'; ?>" />

<div id="poststuff">
<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content">
<div id="titlediv">
<div id="titlewrap">
<?php
	$posttitle_atts = array(
		'type' => 'text',
		'name' => 'post_title',
		'placeholder' => 'Enter Your Title',
		'size' => 30,
		'value' => $post->initial() ? '' : $post->title(),
		'id' => 'title',
		'spellcheck' => 'true',
		'autocomplete' => 'off',
		'disabled' =>
			current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ? '' : 'disabled',
	);

	echo sprintf( '<input %s />', kiwi_cf_format_atts( $posttitle_atts ) );
?>
</div><!-- #titlewrap -->

<div class="inside">
<?php
	if ( ! $post->initial() ) :
?>
	<p class="description">
	<label for="kiwi-shortcode"><?php echo esc_html( __( "Copy this shortcode and paste it into your post, page, or text widget content:", 'kiwi-contact-form' ) ); ?></label>
	<span class="shortcode wp-ui-highlight"><input type="text" id="kiwi-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?php echo esc_attr( $post->shortcode() ); ?>" /></span>
	</p>
<?php
		if ( $old_shortcode = $post->shortcode( array( 'use_old_format' => true ) ) ) :
?>
	<p class="description">
	<label for="kiwi-shortcode-old"><?php echo esc_html( __( "You can also use this old-style shortcode:", 'kiwi-contact-form' ) ); ?></label>
	<span class="shortcode old"><input type="text" id="kiwi-shortcode-old" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?php echo esc_attr( $old_shortcode ); ?>" /></span>
	</p>
<?php
		endif;
	endif;
?>
</div>
</div><!-- #titlediv -->
</div><!-- #post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<?php if ( current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ) : ?>
<div id="submitdiv" class="postbox">
<h3><?php echo esc_html( __( 'Status', 'kiwi-contact-form' ) ); ?></h3>
<div class="inside">
<div class="submitbox" id="submitpost">

<div id="minor-publishing-actions">

<div class="hidden">
	<input type="submit" class="button-primary" name="kiwi-save" value="<?php echo esc_attr( __( 'Save', 'kiwi-contact-form' ) ); ?>" />
</div>

<?php
	if ( ! $post->initial() ) :
		$copy_nonce = wp_create_nonce( 'kiwi-copy-contact-form_' . $post_id );
?>
	<input type="submit" name="kiwi-copy" class="copy button" value="<?php echo esc_attr( __( 'Duplicate', 'kiwi-contact-form' ) ); ?>" <?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; this.form.action.value = 'copy'; return true;\""; ?> />
<?php endif; ?>
</div><!-- #minor-publishing-actions -->

<div id="misc-publishing-actions">
<?php do_action( 'kiwi_cf_admin_misc_pub_section', $post_id ); ?>
</div><!-- #misc-publishing-actions -->

<div id="major-publishing-actions">

<?php
	if ( ! $post->initial() ) :
		$delete_nonce = wp_create_nonce( 'kiwi-delete-contact-form_' . $post_id );
?>
<div id="delete-action">
	<input type="submit" name="kiwi-delete" class="delete submitdelete" value="<?php echo esc_attr( __( 'Delete', 'kiwi-contact-form' ) ); ?>" <?php echo "onclick=\"if (confirm('" . esc_js( __( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", 'kiwi-contact-form' ) ) . "')) {this.form._wpnonce.value = '$delete_nonce'; this.form.action.value = 'delete'; return true;} return false;\""; ?> />
</div><!-- #delete-action -->
<?php endif; ?>

<div id="publishing-action">
	<span class="spinner"></span>
	<?php kiwi_cf_admin_save_button( $post_id ); ?>
</div>
<div class="clear"></div>
</div><!-- #major-publishing-actions -->
</div><!-- #submitpost -->
</div>
</div><!-- #submitdiv -->
<?php endif; ?>

<div id="informationdiv" class="postbox">
<h3><?php echo esc_html( __( "Do you need help?", 'kiwi-contact-form' ) ); ?></h3>
<div class="inside">
	<p><?php echo esc_html( __( "Here are some available options to help solve your problems.", 'kiwi-contact-form' ) ); ?></p>
	<ol>
		<li><?php echo sprintf(
			/* translators: 1: FAQ, 2: Docs ("FAQ & Docs") */
			__( '%1$s &#38; %2$s', 'kiwi-contact-form' ),
			kiwi_cf_link(
				__( ' ', 'kiwi-contact-form' ),
				__( 'FAQ', 'kiwi-contact-form' )
			),
			kiwi_cf_link(
				__( ' ', 'kiwi-contact-form' ),
				__( 'Docs', 'kiwi-contact-form' )
			)
		); ?></li>
		<li><?php echo kiwi_cf_link(
			__( 'https://wordpress.org/support/plugin/kiwi-contact-form/', 'kiwi-contact-form' ),
			__( 'Support Forums', 'kiwi-contact-form' )
		); ?></li>
		<li><?php echo kiwi_cf_link(
			__( ' ', 'kiwi-contact-form' ),
			__( 'Professional Services', 'kiwi-contact-form' )
		); ?></li>
	</ol>
</div>
</div><!-- #informationdiv -->

</div><!-- #postbox-container-1 -->

<div id="postbox-container-2" class="postbox-container">
<div id="contact-form-editor">
<div class="keyboard-interaction"><?php
	echo sprintf(
		/* translators: 1: ◀ ▶ dashicon, 2: screen reader text for the dashicon */
		esc_html( __( '%1$s %2$s keys switch panels', 'kiwi-contact-form' ) ),
		'<span class="dashicons dashicons-leftright" aria-hidden="true"></span>',
		sprintf(
			'<span class="screen-reader-text">%s</span>',
			/* translators: screen reader text */
			esc_html( __( '(left and right arrow)', 'kiwi-contact-form' ) )
		)
	);
?></div>

<?php

	$editor = new Kiwi_CF_Editor( $post );
	$panels = array();

	if ( current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ) {
		$panels = array(
			'form-panel' => array(
				'title' => __( 'Form', 'kiwi-contact-form' ),
				'callback' => 'kiwi_cf_editor_panel_form',
			),
			'mail-panel' => array(
				'title' => __( 'Mail', 'kiwi-contact-form' ),
				'callback' => 'kiwi_cf_editor_panel_mail',
			),
			'messages-panel' => array(
				'title' => __( 'Messages', 'kiwi-contact-form' ),
				'callback' => 'kiwi_cf_editor_panel_messages',
			),
		);

		$additional_settings = trim( $post->prop( 'additional_settings' ) );
		$additional_settings = explode( "\n", $additional_settings );
		$additional_settings = array_filter( $additional_settings );
		$additional_settings = count( $additional_settings );

		$panels['additional-settings-panel'] = array(
			'title' => $additional_settings
				? sprintf(
					/* translators: %d: number of additional settings */
					__( 'Additional Settings (%d)', 'kiwi-contact-form' ),
					$additional_settings )
				: __( 'Additional Settings', 'kiwi-contact-form' ),
			'callback' => 'kiwi_cf_editor_panel_additional_settings',
		);
	}

	$panels = apply_filters( 'kiwi_cf_editor_panels', $panels );

	foreach ( $panels as $id => $panel ) {
		$editor->add_panel( $id, $panel['title'], $panel['callback'] );
	}

	$editor->display();
?>
</div><!-- #contact-form-editor -->

<?php if ( current_user_can( 'kiwi_cf_edit_contact_form', $post_id ) ) : ?>
<p class="submit"><?php kiwi_cf_admin_save_button( $post_id ); ?></p>
<?php endif; ?>

</div><!-- #postbox-container-2 -->

</div><!-- #post-body -->
<br class="clear" />
</div><!-- #poststuff -->
</form>

<?php endif; ?>

</div><!-- .wrap -->

<?php

	$tag_generator = KiwiCfTagGenerator::get_instance();
	$tag_generator->print_panels( $post );

	do_action( 'kiwi_cf_admin_footer', $post );
