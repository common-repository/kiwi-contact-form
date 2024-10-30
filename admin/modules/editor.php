<?php

class Kiwi_CF_Editor {

    private $contact_form;
    private $panels = array();

    public function __construct( KiwiCfContactForm $contact_form ) {
        $this->contact_form = $contact_form;
    }

    public function add_panel( $id, $title, $callback ) {
        if ( kiwi_cf_is_name( $id ) ) {
            $this->panels[$id] = array(
                'title' => $title,
                'callback' => $callback,
            );
        }
    }

    public function display() {
        if ( empty( $this->panels ) ) {
            return;
        }

        echo '<ul id="contact-form-editor-tabs" class="nav nav-tabs">';

        foreach ( $this->panels as $id => $panel ) {
            echo sprintf( '<li id="%1$s-tab" class="nav-item"><a href="#%1$s" class="nav-link" data-toggle="tab">%2$s</a></li>',
                esc_attr( $id ), esc_html( $panel['title'] ) );
        }

        echo '</ul>';

        echo '<div class="tab-content">';
        foreach ( $this->panels as $id => $panel ) {
            echo sprintf( '<div class="tab-pane contact-form-editor-panel" id="%1$s">',
                esc_attr( $id ) );

            if ( is_callable( $panel['callback'] ) ) {
                $this->notice( $id, $panel );
                call_user_func( $panel['callback'], $this->contact_form );
            }

            echo '</div>';
        }
        echo '</div>';

    }

    public function notice( $id, $panel ) {
        echo '<div class="config-error"></div>';
    }
}

function kiwi_cf_editor_panel_form( $post ) {
    $desc_link = kiwi_cf_link(
        __( '', ' kiwi-contact-form' ),
        __( 'Editing Form Template', ' kiwi-contact-form' ) );
    $description = __( "You can edit the form template here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );
    ?>

    <fieldset>
        <legend><?php echo $description; ?></legend>

        <?php
        $tag_generator = KiwiCfTagGenerator::get_instance();
        $tag_generator->print_buttons();
        ?>

        <textarea id="kiwi-form" name="kiwi-form" cols="100" rows="24" class="large-text code" data-config-field="form.body"><?php echo esc_textarea( $post->prop( 'form' ) ); ?></textarea>
    </fieldset>
    <?php
}

function kiwi_cf_editor_panel_mail( $post ) {
    kiwi_cf_editor_box_mail( $post );

    echo '<br class="clear" />';

    kiwi_cf_editor_box_mail( $post, array(
        'id' => 'kiwi-mail-2',
        'name' => 'mail_2',
        'title' => __( 'Mail (2)', ' kiwi-contact-form' ),
        'use' => __( 'Use Mail (2)', ' kiwi-contact-form' ),
    ) );
}

function kiwi_cf_editor_box_mail( $post, $args = '' ) {
    $args = wp_parse_args( $args, array(
        'id' => 'kiwi-mail',
        'name' => 'mail',
        'title' => __( 'Mail', ' kiwi-contact-form' ),
        'use' => null,
    ) );

    $id = esc_attr( $args['id'] );

    $mail = wp_parse_args( $post->prop( $args['name'] ), array(
        'active' => false,
        'recipient' => '',
        'sender' => '',
        'subject' => '',
        'body' => '',
        'additional_headers' => '',
        'attachments' => '',
        'use_html' => false,
        'exclude_blank' => false,
    ) );

    ?>
    <div class="contact-form-editor-box-mail" id="<?php echo $id; ?>">
        <?php
        if ( ! empty( $args['use'] ) ) :
            ?>
            <label for="<?php echo $id; ?>-active"><input type="checkbox" id="<?php echo $id; ?>-active" name="<?php echo $id; ?>[active]" class="toggle-form-table" value="1"<?php echo ( $mail['active'] ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( $args['use'] ); ?></label>
            <p class="description"><?php echo esc_html( __( "Mail (2) is an additional mail template often used as an autoresponder.", ' kiwi-contact-form' ) ); ?></p>
        <?php
        endif;
        ?>

        <fieldset>
            <legend>
                <?php
                $desc_link = kiwi_cf_link(
                    __( '', ' kiwi-contact-form' ),
                    __( 'Setting Up Mail', ' kiwi-contact-form' ) );
                $description = __( "You can edit the mail template here. For details, see %s.", ' kiwi-contact-form' );
                $description = sprintf( esc_html( $description ), $desc_link );
                echo $description;
                echo '<br />';

                echo esc_html( __( "In the following fields, you can use these mail-tags:",
                    ' kiwi-contact-form' ) );
                echo '<br />';
                $post->suggest_mail_tags( $args['name'] );
                ?>
            </legend>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-recipient"><?php echo esc_html( __( 'To', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-recipient" name="<?php echo $id; ?>[recipient]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['recipient'] ); ?>" data-config-field="<?php echo sprintf( '%s.recipient', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-sender"><?php echo esc_html( __( 'From', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-sender" name="<?php echo $id; ?>[sender]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['sender'] ); ?>" data-config-field="<?php echo sprintf( '%s.sender', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-subject"><?php echo esc_html( __( 'Subject', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-subject" name="<?php echo $id; ?>[subject]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['subject'] ); ?>" data-config-field="<?php echo sprintf( '%s.subject', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-additional-headers"><?php echo esc_html( __( 'Additional Headers', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-additional-headers" name="<?php echo $id; ?>[additional_headers]" cols="100" rows="4" class="large-text code" data-config-field="<?php echo sprintf( '%s.additional_headers', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['additional_headers'] ); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-body"><?php echo esc_html( __( 'Message Body', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-body" name="<?php echo $id; ?>[body]" cols="100" rows="18" class="large-text code" data-config-field="<?php echo sprintf( '%s.body', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['body'] ); ?></textarea>

                        <p><label for="<?php echo $id; ?>-exclude-blank"><input type="checkbox" id="<?php echo $id; ?>-exclude-blank" name="<?php echo $id; ?>[exclude_blank]" value="1"<?php echo ( ! empty( $mail['exclude_blank'] ) ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( __( 'Exclude lines with blank mail-tags from output', ' kiwi-contact-form' ) ); ?></label></p>

                        <p><label for="<?php echo $id; ?>-use-html"><input type="checkbox" id="<?php echo $id; ?>-use-html" name="<?php echo $id; ?>[use_html]" value="1"<?php echo ( $mail['use_html'] ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( __( 'Use HTML content type', ' kiwi-contact-form' ) ); ?></label></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-attachments"><?php echo esc_html( __( 'File Attachments', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-attachments" name="<?php echo $id; ?>[attachments]" cols="100" rows="4" class="large-text code" data-config-field="<?php echo sprintf( '%s.attachments', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['attachments'] ); ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <?php
}

function kiwi_cf_editor_panel_messages( $post ) {
    $desc_link = kiwi_cf_link(
        __( '', ' kiwi-contact-form' ),
        __( 'Editing Messages', ' kiwi-contact-form' ) );
    $description = __( "You can edit messages used in various situations here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );

    $messages = kiwi_cf_messages();

    if ( isset( $messages['captcha_not_match'] )
        and ! kiwi_cf_use_really_simple_captcha() ) {
        unset( $messages['captcha_not_match'] );
    }

    ?>
    <fieldset>
        <legend><?php echo $description; ?></legend>
        <?php

        foreach ( $messages as $key => $arr ) {
            $field_id = sprintf( 'kiwi-message-%s', strtr( $key, '_', '-' ) );
            $field_name = sprintf( 'kiwi-messages[%s]', $key );

            ?>
            <p class="description">
                <label for="<?php echo $field_id; ?>"><?php echo esc_html( $arr['description'] ); ?><br />
                    <input type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="large-text" size="70" value="<?php echo esc_attr( $post->message( $key, false ) ); ?>" data-config-field="<?php echo sprintf( 'messages.%s', esc_attr( $key ) ); ?>" />
                </label>
            </p>
            <?php
        }
        ?>
    </fieldset>
    <?php
}

function kiwi_cf_editor_panel_additional_settings( $post ) {
    $desc_link = kiwi_cf_link(
        __( ' ', ' kiwi-contact-form' ),
        __( 'Additional Settings', ' kiwi-contact-form' ) );
    $description = __( "You can add customization code snippets here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );

    ?>
    <fieldset>
        <legend><?php echo $description; ?></legend>
        <textarea id="kiwi-additional-settings" name="kiwi-additional-settings" cols="100" rows="8" class="large-text" data-config-field="additional_settings.body"><?php echo esc_textarea( $post->prop( 'additional_settings' ) ); ?></textarea>
    </fieldset>
    <?php
}
