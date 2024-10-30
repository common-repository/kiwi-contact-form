<?php

class KiwiCfTagGenerator {

	private static $instance;

	private $panels = array();

	private function __construct() {}

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add( $id, $title, $callback, $options = array() ) {
		$id = trim( $id );

		if ( '' === $id
		or ! kiwi_cf_is_name( $id ) ) {
			return false;
		}

		$this->panels[$id] = array(
			'title' => $title,
			'content' => 'tag-generator-panel-' . $id,
			'options' => $options,
			'callback' => $callback,
		);

		return true;
	}

	public function print_buttons() {
		echo '<div class="dropdown">
                <button type="button" class=" btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Modules
                </button>
              <div class="dropdown-menu">';

		foreach ( (array) $this->panels as $panel ) {
			echo sprintf(
				'<a href="#TB_inline?width=900&height=500&inlineId=%1$s" class="dropdown-item thickbox" title="%2$s">%3$s</a>',
				esc_attr( $panel['content'] ),
				esc_attr( sprintf(
					/* translators: %s: title of form-tag like 'email' or 'checkboxes' */
					__( 'Form-tag Generator: %s', 'kiwi-contact-form' ),
					$panel['title'] ) ),
				esc_html( ucfirst( $panel['title'] ) )
			);
		}
        echo '</div></div>';
	}

	public function print_panels( KiwiCfContactForm $contact_form ) {
		foreach ( (array) $this->panels as $id => $panel ) {
			$callback = $panel['callback'];

			$options = wp_parse_args( $panel['options'], array() );
			$options = array_merge( $options, array(
				'id' => $id,
				'title' => $panel['title'],
				'content' => $panel['content'],
			) );

			if ( is_callable( $callback ) ) {
				echo sprintf( '<div id="%s" class="hidden">',
					esc_attr( $options['content'] ) );
				echo sprintf(
					'<form action="" class="tag-generator-panel" data-id="%s">',
					$options['id'] );

				call_user_func( $callback, $contact_form, $options );

				echo '</form></div>';
			}
		}
	}

}
