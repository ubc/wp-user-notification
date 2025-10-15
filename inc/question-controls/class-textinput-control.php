<?php
/**
 * The WP_User_Notification_Text_Input_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Text Input Control
 */
class TextInput_Control extends Question_Control {

	/**
	 * The input type.
	 *
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the text input control.
	 * @param string $label The label of the text input control.
	 * @param array  $args The arguments of the text input control.
	 */
	public function __construct( $name, $label, $args = array() ) {
		parent::__construct( $name, $label, $args );
		$this->css_classes = array( 'inline' );

		if ( isset( $args['type'] ) ) {
			$this->type = sanitize_key( $args['type'] );
		}
	}

	/**
	 * Render the text input control.
	 *
	 * @return string
	 */
	protected function render_control() {
		return '<input type="' . esc_attr( $this->type ) . '"' . $this->get_attributes_string() . ' . value="' . esc_attr( $this->value ) . '">';
	}
}
