<?php
/**
 * The WP_User_Notification_Textarea_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Textarea Control
 *
 * Creates a textarea element for multi-line text input.
 */
class Textarea_Control extends Question_Control {

	/**
	 * The number of visible text lines for the textarea.
	 *
	 * @var int
	 */
	protected $rows = 4;

	/**
	 * The visible width of the textarea.
	 *
	 * @var int
	 */
	protected $cols = 50;

	/**
	 * The placeholder text for the textarea.
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * The maximum number of characters allowed.
	 *
	 * @var int
	 */
	protected $maxlength = 0;

	/**
	 * Whether the textarea should wrap text.
	 *
	 * @var string
	 */
	protected $wrap = 'soft';

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the textarea control.
	 * @param string $label The label of the textarea control.
	 * @param string $value The value of the textarea control.
	 * @param array  $args The arguments of the textarea control.
	 */
	public function __construct( $name, $label, $value = '', $args = array() ) {
		parent::__construct( $name, $label, $args );

		// Set textarea-specific properties from args.
		if ( isset( $args['rows'] ) ) {
			$this->rows = (int) $args['rows'];
		}

		if ( isset( $args['cols'] ) ) {
			$this->cols = (int) $args['cols'];
		}

		if ( isset( $args['placeholder'] ) ) {
			$this->placeholder = sanitize_text_field( $args['placeholder'] );
		}

		if ( isset( $args['maxlength'] ) ) {
			$this->maxlength = (int) $args['maxlength'];
		}

		if ( isset( $args['wrap'] ) ) {
			$this->wrap = sanitize_key( $args['wrap'] );
		}

		// Set the value.
		$this->value = $value;

		$this->css_classes = array( 'inline' );
	}

	/**
	 * Get the number of rows.
	 *
	 * @return int
	 */
	public function get_rows() {
		return $this->rows;
	}

	/**
	 * Set the number of rows.
	 *
	 * @param int $rows The number of visible text lines.
	 */
	public function set_rows( $rows ) {
		$this->rows = (int) $rows;
	}

	/**
	 * Get the number of columns.
	 *
	 * @return int
	 */
	public function get_cols() {
		return $this->cols;
	}

	/**
	 * Set the number of columns.
	 *
	 * @param int $cols The visible width of the textarea.
	 */
	public function set_cols( $cols ) {
		$this->cols = (int) $cols;
	}

	/**
	 * Get the placeholder text.
	 *
	 * @return string
	 */
	public function get_placeholder() {
		return $this->placeholder;
	}

	/**
	 * Set the placeholder text.
	 *
	 * @param string $placeholder The placeholder text.
	 */
	public function set_placeholder( $placeholder ) {
		$this->placeholder = sanitize_text_field( $placeholder );
	}

	/**
	 * Get the maximum length.
	 *
	 * @return int
	 */
	public function get_maxlength() {
		return $this->maxlength;
	}

	/**
	 * Set the maximum length.
	 *
	 * @param int $maxlength The maximum number of characters allowed.
	 */
	public function set_maxlength( $maxlength ) {
		$this->maxlength = (int) $maxlength;
	}

	/**
	 * Get the wrap attribute.
	 *
	 * @return string
	 */
	public function get_wrap() {
		return $this->wrap;
	}

	/**
	 * Set the wrap attribute.
	 *
	 * @param string $wrap The wrap attribute value (soft, hard, or off).
	 */
	public function set_wrap( $wrap ) {
		$allowed_wraps = array( 'soft', 'hard', 'off' );
		if ( in_array( $wrap, $allowed_wraps, true ) ) {
			$this->wrap = $wrap;
		}
	}

	/**
	 * Render the textarea field control with accessibility features.
	 *
	 * @return string
	 */
	protected function render_control() {
		// Generate unique textarea ID for ARIA reference.
		$textarea_id = $this->get_attribute( 'id' );

		// Start textarea element.
		$output = '<textarea id="' . esc_attr( $textarea_id ) . '" name="' . esc_attr( $this->get_attribute( 'name' ) ) . '" class="textarea-control"';

		// Add rows and cols attributes.
		$output .= ' rows="' . esc_attr( $this->rows ) . '" cols="' . esc_attr( $this->cols ) . '"';

		// Add placeholder if specified.
		if ( ! empty( $this->placeholder ) ) {
			$output .= ' placeholder="' . esc_attr( $this->placeholder ) . '"';
		}

		// Add maxlength if specified.
		if ( $this->maxlength > 0 ) {
			$output .= ' maxlength="' . esc_attr( $this->maxlength ) . '"';
		}

		// Add wrap attribute.
		$output .= ' wrap="' . esc_attr( $this->wrap ) . '"';

		// Add required attribute.
		if ( $this->required ) {
			$output .= ' required';
		}

		// Add ARIA attributes for accessibility.
		if ( $this->required ) {
			$output .= ' aria-required="true"';
		}

		// Add ARIA describedby if help text exists.
		$help_id = $this->name . '_help';
		if ( ! empty( $this->help_text ) ) {
			$output .= ' aria-describedby="' . esc_attr( $help_id ) . '"';
		}

		// Add any additional attributes.
		$additional_attrs = $this->get_attributes_string();
		$output          .= $additional_attrs;

		$output .= '>';

		// Add the current value.
		$output .= esc_textarea( $this->value );

		$output .= '</textarea>';

		// Add help text if it exists.
		if ( ! empty( $this->help_text ) ) {
			$output .= '<div id="' . esc_attr( $help_id ) . '" class="control-help-text">' . esc_html( $this->help_text ) . '</div>';
		}

		return $output;
	}
}
