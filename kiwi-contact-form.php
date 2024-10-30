<?php

define( 'KIWI_CF_VERSION', '1.0.1' );
define( 'KIWI_CF_REQUIRED_WP_VERSION', '1.9' );
define( 'KIWI_CF_PLUGIN', __FILE__ );
define( 'KIWI_CF_PLUGIN_BASENAME', plugin_basename( KIWI_CF_PLUGIN ) );
define( 'KIWI_CF_PLUGIN_NAME', trim( dirname( KIWI_CF_PLUGIN_BASENAME ), '/' ) );
define( 'KIWI_CF_PLUGIN_DIR', untrailingslashit( dirname( KIWI_CF_PLUGIN ) ) );
define( 'KIWI_CF_PLUGIN_COMPONENTS_DIR', KIWI_CF_PLUGIN_DIR . '/components' );
define( 'KIWI_CF_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

if ( ! defined( 'KIWI_CF_LOAD_JS' ) ) {
    define( 'KIWI_CF_LOAD_JS', true );
}

if ( ! defined( 'KIWI_CF_LOAD_CSS' ) ) {
    define( 'KIWI_CF_LOAD_CSS', true );
}

if ( ! defined( 'KIWI_CF_AUTOP' ) ) {
    define( 'KIWI_CF_AUTOP', true );
}

if ( ! defined( 'KIWI_CF_USE_PIPE' ) ) {
    define( 'KIWI_CF_USE_PIPE', true );
}

if ( ! defined( 'KIWI_CF_ADMIN_READ_CAPABILITY' ) ) {
    define( 'KIWI_CF_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

if ( ! defined( 'KIWI_CF_VERIFY_NONCE' ) ) {
    define( 'KIWI_CF_VERIFY_NONCE', false );
}

if ( ! defined( 'KIWI_CF_USE_REALLY_SIMPLE_CAPTCHA' ) ) {
    define( 'KIWI_CF_USE_REALLY_SIMPLE_CAPTCHA', false );
}

if ( ! defined( 'KIWI_CF_VALIDATE_CONFIGURATION' ) ) {
    define( 'KIWI_CF_VALIDATE_CONFIGURATION', true );
}

require_once KIWI_CF_PLUGIN_DIR . '/common.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/integration.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/submission.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/pipe.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/mail.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/form-tag.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/contact-form-template.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/config-validator.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/capabilities.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/formatting.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/contact-form-functions.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/functions.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/l10n.php';
require_once KIWI_CF_PLUGIN_DIR . '/components/form-tags-manager.php';


function kiwi_cf_admin_styles_and_scripts() {
    wp_enqueue_style( 'bootstrap4',
        kiwi_cf_plugin_url( 'admin/views/css/bootstrap.min.css' ),
        array(), KIWI_CF_VERSION, 'all'
    );
    wp_enqueue_script( 'boot3',
        kiwi_cf_plugin_url( 'admin/views/js/popper.min.js' ),
        array( 'jquery' ),
        KIWI_CF_VERSION, true
    );
    wp_enqueue_script( 'boot4',
        kiwi_cf_plugin_url( '/admin/views/js/bootstrap.min.js' ),
        array( 'jquery' ),
        KIWI_CF_VERSION, true
    );
}

add_action( 'admin_enqueue_scripts', 'kiwi_cf_admin_styles_and_scripts' );

if ( is_admin() ) {
    require_once KIWI_CF_PLUGIN_DIR . '/admin/index.php';
} else {
    require_once KIWI_CF_PLUGIN_DIR . '/client/index.php';
}

require_once KIWI_CF_PLUGIN_DIR . '/kiwi.php';

