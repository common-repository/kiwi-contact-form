<?php

require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/admin-functions.php';
require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/config-validator.php';
require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/welcome-panel.php';
require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/help-tabs.php';
require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/tag-generator.php';
require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/contact-form-functions.php';

add_action( 'admin_init', 'kiwi_cf_admin_init', 10, 0 );

function kiwi_cf_admin_init() {
    do_action( 'kiwi_cf_admin_init' );
}

add_action( 'admin_menu', 'kiwi_cf_admin_menu', 9, 0 );

function kiwi_cf_admin_menu() {
    global $_wp_last_object_menu;

    $_wp_last_object_menu++;

    do_action( 'kiwi_cf_admin_menu' );

    add_menu_page( __( 'Kiwi Contact Form', 'kiwi-contact-form' ),
        __( 'Kiwi Contact', 'kiwi-contact-form' ),
        'kiwi_cf_read_contact_forms', 'kiwi',
        'kiwi_cf_admin_management_page', 'dashicons-email-alt2',
        $_wp_last_object_menu );

    $edit = add_submenu_page( 'kiwi',
        __( 'Edit Contact Form', 'kiwi-contact-form' ),
        __( 'Contact Forms', 'kiwi-contact-form' ),
        'kiwi_cf_read_contact_forms', 'kiwi',
        'kiwi_cf_admin_management_page' );

    add_action( 'load-' . $edit, 'kiwi_cf_load_contact_form_admin', 10, 0 );

    $addnew = add_submenu_page( 'kiwi',
        __( 'Add New Contact Form', 'kiwi-contact-form' ),
        __( 'Add New', 'kiwi-contact-form' ),
        'kiwi_cf_edit_contact_forms', 'kiwi-new',
        'kiwi_cf_admin_add_new_page' );

    add_action( 'load-' . $addnew, 'kiwi_cf_load_contact_form_admin', 10, 0 );

    $integration = KiwiCfIntegration::get_instance();

    if ( $integration->service_exists() ) {
        $integration = add_submenu_page( 'kiwi',
            __( 'Integration with Other Services', 'kiwi-contact-form' ),
            __( 'Integration', 'kiwi-contact-form' ),
            'kiwi_cf_manage_integration', 'kiwi-integration',
            'kiwi_cf_admin_integration_page' );

        add_action( 'load-' . $integration, 'kiwi_cf_load_integration_page', 10, 0 );
    }

    add_submenu_page( 'kiwi',
        'Kiwi Contact Form FAQ',
        'FAQ',
        'administrator',
        'kiwi-faq',
        'kiwi_cf_faq'
    );
}

function kiwi_cf_faq() {
    wp_enqueue_script( 'faq-script', plugins_url( '/views/js/faq.js', __FILE__ ), ['jquery'], '', TRUE );
    wp_enqueue_style( 'faq-style', plugins_url( '/views/css/faq.css', __FILE__ ), [], '' );

    require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/faq.php';
}

add_action( 'admin_enqueue_scripts', 'kiwi_cf_admin_enqueue_scripts', 10, 1 );

function kiwi_cf_admin_enqueue_scripts( $hook_suffix ) {
    if ( false === strpos( $hook_suffix, 'kiwi' ) ) {
        return;
    }

    wp_enqueue_style( 'kiwi-contact-form-admin',
        kiwi_cf_plugin_url( 'admin/views/css/styles.css' ),
        array(), KIWI_CF_VERSION, 'all'
    );

    if ( kiwi_cf_is_rtl() ) {
        wp_enqueue_style( 'kiwi-contact-form-admin-rtl',
            kiwi_cf_plugin_url( 'admin/views/css/styles-rtl.css' ),
            array(), KIWI_CF_VERSION, 'all'
        );
    }

    wp_enqueue_script( 'kiwi-admin',
        kiwi_cf_plugin_url( 'admin/views/js/scripts.js' ),
        array( 'jquery', 'jquery-ui-tabs' ),
        KIWI_CF_VERSION, true
    );

    $args = array(
        'apiSettings' => array(
            'root' => esc_url_raw( rest_url( 'kiwi-contact-form/v1' ) ),
            'namespace' => 'kiwi-contact-form/v1',
            'nonce' => ( wp_installing() && ! is_multisite() )
                ? '' : wp_create_nonce( 'wp_rest' ),
        ),
        'pluginUrl' => kiwi_cf_plugin_url(),
        'saveAlert' => __(
            "The changes you made will be lost if you navigate away from this page.",
            'kiwi-contact-form' ),
        'activeTab' => isset( $_GET['active-tab'] )
            ? (int) $_GET['active-tab'] : 0,
        'configValidator' => array(
            'errors' => array(),
            'howToCorrect' => __( "How to resolve?", 'kiwi-contact-form' ),
            'oneError' => __( '1 configuration error detected', 'kiwi-contact-form' ),
            'manyErrors' => __( '%d configuration errors detected', 'kiwi-contact-form' ),
            'oneErrorInTab' => __( '1 configuration error detected in this tab panel', 'kiwi-contact-form' ),
            'manyErrorsInTab' => __( '%d configuration errors detected in this tab panel', 'kiwi-contact-form' ),
            'docUrl' => KiwiCfConfigValidator::get_doc_link(),
            /* translators: screen reader text */
            'iconAlt' => __( '(configuration error)', 'kiwi-contact-form' ),
        ),
    );

    if ( $post = kiwi_cf_get_current_contact_form()
        and current_user_can( 'kiwi_cf_edit_contact_form', $post->id() )
        and kiwi_cf_validate_configuration() ) {
        $config_validator = new KiwiCfConfigValidator( $post );
        $config_validator->restore();
        $args['configValidator']['errors'] =
            $config_validator->collect_error_messages();
    }

    wp_localize_script( 'kiwi-admin', 'kiwi', $args );

    add_thickbox();

    wp_enqueue_script( 'kiwi-admin-taggenerator',
        kiwi_cf_plugin_url( 'admin/js/tag-generator.js' ),
        array( 'jquery', 'thickbox', 'kiwi-admin' ), KIWI_CF_VERSION, true );
}

add_action( 'doing_dark_mode', 'kiwi_cf_dark_mode_support', 10, 1 );

function kiwi_cf_dark_mode_support( $user_id ) {
    wp_enqueue_style( 'kiwi-contact-form-admin-dark-mode',
        kiwi_cf_plugin_url( 'admin/css/styles-dark-mode.css' ),
        array( 'kiwi-contact-form-admin' ), KIWI_CF_VERSION, 'screen' );
}

add_filter( 'set-screen-option', 'kiwi_cf_set_screen_options', 10, 3 );

function kiwi_cf_set_screen_options( $result, $option, $value ) {
    $kiwi_cf_screens = array(
        'cfseven_contact_forms_per_page',
    );

    if ( in_array( $option, $kiwi_cf_screens ) ) {
        $result = $value;
    }

    return $result;
}

function kiwi_cf_load_contact_form_admin() {
    global $plugin_page;

    $action = kiwi_cf_current_action();

    do_action( 'kiwi_cf_admin_load',
        isset( $_GET['page'] ) ? sanitize_text_field ( trim( $_GET['page'] ) ) : '',
        $action
    );

    if ( 'save' == $action ) {
        $id = isset( $_POST['post_ID'] ) ? (int)$_POST['post_ID'] : '-1';
        check_admin_referer( 'kiwi-save-contact-form_' . $id );

        if ( ! current_user_can( 'kiwi_cf_edit_contact_form', $id ) ) {
            wp_die( __( 'You are not allowed to edit this item.', 'kiwi-contact-form' ) );
        }

        $args = $_REQUEST;
        $args['id'] = $id;

    // Input validation & sanitization process

        $args['title'] = isset( $_POST['post_title'] ) || !empty( $_POST['post_title'] )
            ? sanitize_text_field( $_POST['post_title'] ) : null;

        $args['locale'] = isset( $_POST['kiwi-locale'] ) || !empty( $_POST['kiwi-locale'] )
            ? sanitize_text_field( $_POST['kiwi-locale'] ) : null;

        $args['form'] = isset( $_POST['kiwi-form'] ) || ! empty( $_POST['kiwi-form'] )
            ? wp_kses_post( $_POST['kiwi-form'] ) : '';

        $args['mail'] = isset( $_POST['kiwi-mail'] ) || !empty( $_POST['kiwi-mail'] )
            ? kiwi_cf_sanitize_array( $_POST['kiwi-mail'] ) : array();

        $args['mail_2'] = isset( $_POST['kiwi-mail-2'] ) || !empty( $_POST['kiwi-mail-2'] )
            ? kiwi_cf_sanitize_array( $_POST['kiwi-mail-2'] ) : array();

        $args['messages'] = isset( $_POST['kiwi-messages'] ) || !empty( $_POST['kiwi-messages'] )
            ? kiwi_cf_sanitize_array( $_POST['kiwi-messages'] ) : array();

        $args['additional_settings'] = isset( $_POST['kiwi-additional-settings'] ) || !empty($_POST['kiwi-additional-settings'])
            ? sanitize_text_field( $_POST['kiwi-additional-settings'] ) : '';

        $args['mail']['body'] = esc_html($args['mail']['body']);
        $args['mail_2']['body'] = esc_html($args['mail_2']['body']);

        $contact_form = kiwi_cf_save_contact_form( $args );

        if ( $contact_form and kiwi_cf_validate_configuration() ) {
            $config_validator = new KiwiCfConfigValidator( $contact_form );
            $config_validator->validate();
            $config_validator->save();
        }

        $query = array(
            'post' => $contact_form ? $contact_form->id() : 0,
            'active-tab' => isset( $_POST['active-tab'] )
                ? (int) $_POST['active-tab'] : 0,
        );

        if ( ! $contact_form ) {
            $query['message'] = 'failed';
        } elseif ( -1 == $id ) {
            $query['message'] = 'created';
        } else {
            $query['message'] = 'saved';
        }

        $redirect_to = add_query_arg( $query, menu_page_url( 'kiwi', false ) );
        wp_safe_redirect( $redirect_to );
        exit();
    }

    if ( 'copy' == $action ) {
        $id = empty( $_POST['post_ID'] )
            ? absint( $_REQUEST['post'] )
            : absint( $_POST['post_ID'] );

        check_admin_referer( 'kiwi-copy-contact-form_' . $id );

        if ( ! current_user_can( 'kiwi_cf_edit_contact_form', $id ) ) {
            wp_die( __( 'You are not allowed to edit this item.', 'kiwi-contact-form' ) );
        }

        $query = array();

        if ( $contact_form = kiwi_cf_contact_form( $id ) ) {
            $new_contact_form = $contact_form->copy();
            $new_contact_form->save();

            $query['post'] = $new_contact_form->id();
            $query['message'] = 'created';
        }

        $redirect_to = add_query_arg( $query, menu_page_url( 'kiwi', false ) );

        wp_safe_redirect( $redirect_to );
        exit();
    }

    if ( 'delete' == $action ) {
        if ( ! empty( $_POST['post_ID'] ) ) {
            $_POST['post_ID'] = (int)$_POST['post_ID'];
            check_admin_referer( 'kiwi-delete-contact-form_' . $_POST['post_ID'] );
        } elseif ( ! is_array( $_REQUEST['post'] ) ) {
            $_REQUEST['post'] = (int)$_REQUEST['post'];
            check_admin_referer( 'kiwi-delete-contact-form_' . $_REQUEST['post'] );
        } else {
            check_admin_referer( 'bulk-posts' );
        }

        $posts = empty( $_POST['post_ID'] )
            ? (array) $_REQUEST['post']
            : (array) $_POST['post_ID'];

        $deleted = 0;

        foreach ( $posts as $post ) {
            $post = KiwiCfContactForm::get_instance( $post );

            if ( empty( $post ) ) {
                continue;
            }

            if ( ! current_user_can( 'kiwi_cf_delete_contact_form', $post->id() ) ) {
                wp_die( __( 'You are not allowed to delete this item.', 'kiwi-contact-form' ) );
            }

            if ( ! $post->delete() ) {
                wp_die( __( 'Error in deleting.', 'kiwi-contact-form' ) );
            }

            $deleted += 1;
        }

        $query = array();

        if ( ! empty( $deleted ) ) {
            $query['message'] = 'deleted';
        }

        $redirect_to = add_query_arg( $query, menu_page_url( 'kiwi', false ) );

        wp_safe_redirect( $redirect_to );
        exit();
    }

    $post = null;

    if ( 'kiwi-new' == $plugin_page ) {
        $post = KiwiCfContactForm::get_template( array(
            'locale' => isset( $_GET['locale'] ) ? $_GET['locale'] : null,
        ) );
    } elseif ( ! empty( $_GET['post'] ) ) {
        $post = KiwiCfContactForm::get_instance( $_GET['post'] );
    }

    $current_screen = get_current_screen();

    $help_tabs = new Kiwi_CF_Help_Tabs( $current_screen );

    if ( $post
        and current_user_can( 'kiwi_cf_edit_contact_form', $post->id() ) ) {
        $help_tabs->set_help_tabs( 'edit' );
    } else {
        $help_tabs->set_help_tabs( 'list' );

        if ( ! class_exists( 'Kiwi__CF_Contact_Form_List_Table' ) ) {
            require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/class-contact-forms-list-table.php';
        }

        add_filter( 'manage_' . $current_screen->id . '_columns',
            array( 'Kiwi_CF_Contact_Form_List_Table', 'define_columns' ), 10, 0 );

        add_screen_option( 'per_page', array(
            'default' => 20,
            'option' => 'cfseven_contact_forms_per_page',
        ) );
    }
}

function kiwi_cf_admin_management_page() {
    if ( $post = kiwi_cf_get_current_contact_form() ) {
        $post_id = $post->initial() ? -1 : $post->id();

        require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/editor.php';
        require_once KIWI_CF_PLUGIN_DIR . '/components/edit-contact-form.php';
        return;
    }

    if ( 'validate' == kiwi_cf_current_action()
        and kiwi_cf_validate_configuration()
        and current_user_can( 'kiwi_cf_edit_contact_forms' ) ) {
        kiwi_cf_admin_bulk_validate_page();
        return;
    }

    $list_table = new Kiwi_CF_Contact_Form_List_Table();
    $list_table->prepare_items();

    ?>
    <div class="wrap" id="kiwi-contact-form-list-table">

        <h1 class="wp-heading-inline"><?php
            echo esc_html( __( 'Contact Forms', 'kiwi-contact-form' ) );
            ?></h1>

        <?php
        if ( current_user_can( 'kiwi_cf_edit_contact_forms' ) ) {
            echo kiwi_cf_link(
                menu_page_url( 'kiwi-new', false ),
                __( 'Add New', 'kiwi-contact-form' ),
                array( 'class' => 'page-title-action' )
            );
        }

        if ( ! empty( $_REQUEST['s'] ) ) {
            echo sprintf( '<span class="subtitle">'
                /* translators: %s: search keywords */
                . __( 'Search results for &#8220;%s&#8221;', 'kiwi-contact-form' )
                . '</span>', esc_html( $_REQUEST['s'] )
            );
        }
        ?>

        <hr class="wp-header-end">

        <?php
        do_action( 'kiwi_cf_admin_warnings',
            'kiwi', kiwi_cf_current_action(), null );

        kiwi_cf_welcome_panel();

        do_action( 'kiwi_cf_admin_notices',
            'kiwi', kiwi_cf_current_action(), null );
        ?>

        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php $list_table->search_box( __( 'Search Contact Forms', 'kiwi-contact-form' ), 'kiwi-contact' ); ?>
            <?php $list_table->display(); ?>
        </form>

    </div>
    <?php
}

function kiwi_cf_admin_add_new_page() {
    $post = kiwi_cf_get_current_contact_form();

    if ( ! $post ) {
        $post = KiwiCfContactForm::get_template();
    }

    $post_id = -1;

    require_once KIWI_CF_PLUGIN_DIR . '/admin/modules/editor.php';
    require_once KIWI_CF_PLUGIN_DIR . '/components/edit-contact-form.php';
}

function kiwi_cf_load_integration_page() {
    do_action( 'kiwi_cf_admin_load',
        isset( $_GET['page'] ) ? sanitize_text_field( trim( $_GET['page'] ) ) : '',
        kiwi_cf_current_action()
    );

    $integration = KiwiCfIntegration::get_instance();

    if ( isset( $_REQUEST['service'] )
        and $integration->service_exists( $_REQUEST['service'] ) ) {
        $service = $integration->get_service( $_REQUEST['service'] );
        $service->load( kiwi_cf_current_action() );
    }

    $help_tabs = new Kiwi_CF_Help_Tabs( get_current_screen() );
    $help_tabs->set_help_tabs( 'integration' );
}

function kiwi_cf_admin_integration_page() {
    $integration = KiwiCfIntegration::get_instance();

    $service = isset( $_REQUEST['service'] )
        ? $integration->get_service( $_REQUEST['service'] )
        : null;

    ?>
    <div class="wrap" id="kiwi-integration">

        <h1><?php echo esc_html( __( 'Integration with Other Services', 'kiwi-contact-form' ) ); ?></h1>

        <?php
        do_action( 'kiwi_cf_admin_warnings',
            'kiwi-integration', kiwi_cf_current_action(), $service );

        do_action( 'kiwi_cf_admin_notices',
            'kiwi-integration', kiwi_cf_current_action(), $service );

        if ( $service ) {
            $message = isset( $_REQUEST['message'] ) ? $_REQUEST['message'] : '';
            $service->admin_notice( $message );
            $integration->list_services( array( 'include' => $_REQUEST['service'] ) );
        } else {
            $integration->list_services();
        }
        ?>

    </div>
    <?php
}

/* Misc */

add_action( 'kiwi_cf_admin_notices', 'kiwi_cf_admin_updated_message', 10, 3 );

function kiwi_cf_admin_updated_message( $page, $action, $object ) {
    if ( ! in_array( $page, array( 'kiwi', 'kiwi-new' ) ) ) {
        return;
    }

    if ( empty( $_REQUEST['message'] ) ) {
        return;
    }

    if ( 'created' == $_REQUEST['message'] ) {
        $updated_message = __( "Contact form created.", 'kiwi-contact-form' );
    } elseif ( 'saved' == $_REQUEST['message'] ) {
        $updated_message = __( "Contact form saved.", 'kiwi-contact-form' );
    } elseif ( 'deleted' == $_REQUEST['message'] ) {
        $updated_message = __( "Contact form deleted.", 'kiwi-contact-form' );
    }

    if ( ! empty( $updated_message ) ) {
        echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
        return;
    }

    if ( 'failed' == $_REQUEST['message'] ) {
        $updated_message = __( "There was an error saving the contact form.",
            'kiwi-contact-form' );

        echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
        return;
    }

    if ( 'validated' == $_REQUEST['message'] ) {
        $bulk_validate = KIWICF::get_option( 'bulk_validate', array() );
        $count_invalid = isset( $bulk_validate['count_invalid'] )
            ? absint( $bulk_validate['count_invalid'] ) : 0;

        if ( $count_invalid ) {
            $updated_message = sprintf(
                _n(
                /* translators: %s: number of contact forms */
                    "Configuration validation completed. %s invalid contact form was found.",
                    "Configuration validation completed. %s invalid contact forms were found.",
                    $count_invalid, 'kiwi-contact-form'
                ),
                number_format_i18n( $count_invalid )
            );

            echo sprintf( '<div id="message" class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
        } else {
            $updated_message = __( "Configuration validation completed. No invalid contact form was found.", 'kiwi-contact-form' );

            echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
        }

        return;
    }
}

add_filter( 'plugin_action_links', 'kiwi_cf_plugin_action_links', 10, 2 );

function kiwi_cf_plugin_action_links( $links, $file ) {
    if ( $file != KIWI_CF_PLUGIN_BASENAME ) {
        return $links;
    }

    if ( ! current_user_can( 'kiwi_cf_read_contact_forms' ) ) {
        return $links;
    }

    $settings_link = kiwi_cf_link(
        menu_page_url( 'kiwi', false ),
        __( 'Settings', 'kiwi-contact-form' )
    );

    array_unshift( $links, $settings_link );

    return $links;
}

add_action( 'kiwi_cf_admin_warnings', 'kiwi_cf_old_wp_version_error', 10, 3 );

function kiwi_cf_old_wp_version_error( $page, $action, $object ) {
    $wp_version = get_bloginfo( 'version' );

    if ( ! version_compare( $wp_version, KIWI_CF_REQUIRED_WP_VERSION, '<' ) ) {
        return;
    }

    ?>
    <div class="notice notice-warning">
        <p><?php
            echo sprintf(
            /* translators: 1: version of Kiwi Contact Form, 2: version of WordPress, 3: URL */
                __( '<strong>Kiwi Contact Form %1$s requires WordPress %2$s or higher.</strong> Please <a href="%3$s">update WordPress</a> first.', 'kiwi-contact-form' ),
                KIWI_CF_VERSION,
                KIWI_CF_REQUIRED_WP_VERSION,
                admin_url( 'update-core.php' )
            );
            ?></p>
    </div>
    <?php
}

add_action( 'kiwi_cf_admin_warnings', 'kiwi_cf_not_allowed_to_edit', 10, 3 );

function kiwi_cf_not_allowed_to_edit( $page, $action, $object ) {
    if ( $object instanceof KiwiCfContactForm ) {
        $contact_form = $object;
    } else {
        return;
    }

    if ( current_user_can( 'kiwi_cf_edit_contact_form', $contact_form->id() ) ) {
        return;
    }

    $message = __( "You are not allowed to edit this contact form.",
        'kiwi-contact-form' );

    echo sprintf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html( $message ) );
}

function kiwi_cf_sanitize_array($array = array(), $sanitize_rule = array()) {
    if ( !is_array($array) || count($array) == 0 ) {
        return array();
    }

    foreach ( $array as $k => $v ) {
        if ( !is_array($v) ) {
            if (is_email($v)) {
                $default_sanitize_rule = 'email';
            } elseif ($v != strip_tags($v)) {
                $default_sanitize_rule = 'html';
            } elseif (is_numeric($v)) {
                $default_sanitize_rule = 'number';
            } else {
                $default_sanitize_rule = 'text';
            }

            $sanitize_type = isset($sanitize_rule[ $k ]) ? $sanitize_rule[ $k ] : $default_sanitize_rule;
            $array[ $k ] = kiwi_cf_sanitize_value($v, $sanitize_type);
        }
        if ( is_array($v) ) {
            $array[ $k ] = kiwi_cf_sanitize_array($v, $sanitize_rule);
        }
    }

    return $array;
}

function kiwi_cf_sanitize_value($value = '', $sanitize_type = 'text') {
    switch ( $sanitize_type ) {
        case 'html':
            return $value;
            break;
        case 'email':
            return sanitize_email($value);
            break;
        case 'text':
            return sanitize_text_field($value);
            break;
    }
}
