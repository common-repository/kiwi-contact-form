<?php

class KIWICF {

    public static function load_modules() {
        self::load_module( 'acceptance' );
        self::load_module( 'akismet' );
        self::load_module( 'checkbox' );
        self::load_module( 'constant-contact' );
        self::load_module( 'count' );
        self::load_module( 'date' );
        self::load_module( 'file' );
        self::load_module( 'flamingo' );
        self::load_module( 'hidden' );
        self::load_module( 'listo' );
        self::load_module( 'number' );
        self::load_module( 'quiz' );
        self::load_module( 'really-simple-captcha' );
        self::load_module( 'recaptcha' );
        self::load_module( 'response' );
        self::load_module( 'select' );
        self::load_module( 'submit' );
        self::load_module( 'text' );
        self::load_module( 'textarea' );
    }

    protected static function load_module( $mod ) {
        $dir = KIWI_CF_PLUGIN_COMPONENTS_DIR;

        if ( empty( $dir ) or ! is_dir( $dir ) ) {
            return false;
        }

        $file = path_join( $dir, $mod . '.php' );

        if ( file_exists( $file ) ) {
            include_once $file;
        }
    }

    public static function get_option( $name, $default = false ) {
        $option = get_option( 'kiwi' );

        if ( false === $option ) {
            return $default;
        }

        if ( isset( $option[$name] ) ) {
            return $option[$name];
        } else {
            return $default;
        }
    }

    public static function update_option( $name, $value ) {
        $option = get_option( 'kiwi' );
        $option = ( false === $option ) ? array() : (array) $option;
        $option = array_merge( $option, array( $name => $value ) );
        update_option( 'kiwi', $option );
    }
}

add_action( 'plugins_loaded', 'kiwi', 10, 0 );

function kiwi() {
    kiwi_cf_load_textdomain();
    KIWICF::load_modules();

    /* Shortcodes */
    add_shortcode( 'kiwi-contact-form', 'kiwi_cf_contact_form_tag_func' );
    add_shortcode( 'contact-form', 'kiwi_cf_contact_form_tag_func' );
}

add_action( 'init', 'kiwi_cf_init', 10, 0 );

function kiwi_cf_init() {
    kiwi_cf_get_request_uri();
    kiwi_cf_register_post_types();

    do_action( 'kiwi_cf_init' );
}

add_action( 'admin_init', 'kiwi_cf_upgrade', 10, 0 );

function kiwi_cf_upgrade() {
    $old_ver = KIWICF::get_option( 'version', '0' );
    $new_ver = KIWI_CF_VERSION;

    if ( $old_ver == $new_ver ) {
        return;
    }

    do_action( 'kiwi_cf_upgrade', $new_ver, $old_ver );

    KIWICF::update_option( 'version', $new_ver );
}

/* Install and default settings */

add_action( 'activate_' . KIWI_CF_PLUGIN_BASENAME, 'kiwi_cf_install', 10, 0 );

function kiwi_cf_install() {
    if ( $opt = get_option( 'kiwi' ) ) {
        return;
    }

    kiwi_cf_load_textdomain();
    kiwi_cf_register_post_types();
    kiwi_cf_upgrade();

    if ( get_posts( array( 'post_type' => 'kiwi_cf_contact_form' ) ) ) {
        return;
    }

    $contact_form = KiwiCfContactForm::get_template(
        array(
            'title' =>
            /* translators: title of your first contact form. %d: number fixed to '1' */
                sprintf( __( 'Contact form %d', 'kiwi-contact-form' ), 1 ),
        )
    );

    $contact_form->save();

    KIWICF::update_option( 'bulk_validate',
        array(
            'timestamp' => current_time( 'timestamp' ),
            'version' => KIWI_CF_VERSION,
            'count_valid' => 1,
            'count_invalid' => 0,
        )
    );
}