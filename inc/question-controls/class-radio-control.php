<?php
/**
 * The WP_User_Notification_Radio_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Radio Field Control
 *
 * Creates a group of radio buttons for single selection from multiple options.
 */
class Radio_Control extends Question_Control {

	/**
	 * The radio options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Whether to display options horizontally or vertically.
	 *
	 * @var bool
	 */
	protected $horizontal = false;

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the radio control.
	 * @param string $label The label of the radio control.
	 * @param string $value The value of the radio control.
	 * @param array  $args The arguments of the radio control.
	 */
	public function __construct( $name, $label, $value = '', $args = array() ) {
		parent::__construct( $name, $label, $value, $args );

		// Set options from args.
		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$this->options = $args['options'];
		}

		$this->css_classes = array( 'inline' );
	}

	/**
	 * Get the radio options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Set the radio options.
	 *
	 * @param array $options The options of the radio control.
	 */
	public function set_options( $options ) {
		if ( is_array( $options ) ) {
			$this->options = $options;
		}
	}

	/**
	 * Add a single option.
	 *
	 * @param string $value The value of the radio control.
	 * @param string $label The label of the radio control.
	 */
	public function add_option( $value, $label ) {
		$this->options[ $value ] = $label;
	}

	/**
	 * Check if a specific option is selected.
	 *
	 * @param string $value The value of the radio control.
	 * @return bool
	 */
	public function is_option_selected( $value ) {
		return $this->value === $value;
	}

	/**
	 * Render the radio field control with accessibility features.
	 *
	 * @return string
	 */
	protected function render_control() {
		if ( empty( $this->options ) ) {
			return '<p class="error">No options available for radio field.</p>';
		}

		$required_mark = $this->required ? ' <span class="required">*</span>' : '';

		// Generate unique fieldset ID for ARIA reference.
		$fieldset_id = $this->name . '_fieldset';

		// Start fieldset with legend for proper grouping.
		$output = '<fieldset id="' . esc_attr( $fieldset_id ) . '" class="radio-group" role="radiogroup"';

		// Add ARIA attributes for accessibility.
		if ( $this->required ) {
			$output .= ' aria-required="true"';
		}

		// Add ARIA describedby if help text exists.
		$help_id = $this->name . '_help';
		if ( ! empty( $this->help_text ) ) {
			$output .= ' aria-describedby="' . esc_attr( $help_id ) . '"';
		}

		$output .= '>';

		// Add legend for the fieldset.
		$legend_required = $this->required ? ' <span class="required" aria-label="required">*</span>' : '';
		$output         .= '<legend class="radio-legend">' . esc_html( $this->label ) . $legend_required . '</legend>';

		// Add help text inside fieldset if it exists.
		if ( ! empty( $this->help_text ) ) {
			$output .= '<div id="' . esc_attr( $help_id ) . '" class="control-help-text">' . esc_html( $this->help_text ) . '</div>';
		}

		// Render each radio option.
		foreach ( $this->options as $value => $label ) {
			$radio_id      = $this->name . '_' . sanitize_key( $value );
			$checked       = $this->is_option_selected( $value ) ? ' checked' : '';
			$required_attr = $this->required ? ' required' : '';

			$output .= '<div class="radio-option">';
			$output .= '<input type="radio" id="' . esc_attr( $radio_id ) . '" name="question[' . esc_attr( $this->name ) . ']" value="' . esc_attr( $value ) . '"' . $checked . $required_attr . '>';
			$output .= '<label for="' . esc_attr( $radio_id ) . '">' . esc_html( $label ) . '</label>';
			$output .= '</div>';
		}

		$output .= '</fieldset>';

		return $output;
	}

	/**
	 * Override the parent render method to handle radio field structure.
	 * Radio fields use fieldset/legend instead of separate label.
	 *
	 * @param string|null $notification_slug The notification slug.
	 * @return string The HTML output.
	 */
	public function render( $notification_slug = null ) {
		if ( ! empty( $this->css_classes ) ) {
			$this->css_classes_string = implode( ' ', $this->css_classes );
		}

		self::maybe_get_existing_value( $notification_slug );

		$output = '<div class="control-' . esc_attr( $this->name ) . ' ' . esc_attr( $this->css_classes_string ) . '">';

		// Render control (which includes the fieldset with legend).
		$output .= $this->render_control();

		$output .= '</div>';

		return $output;
	}
}
