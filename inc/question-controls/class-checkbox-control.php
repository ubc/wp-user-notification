<?php
/**
 * The WP_User_Notification_Checkbox_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Checkbox Control
 *
 * Creates a group of checkboxes for multiple selection from multiple options.
 */
class Checkbox_Control extends Question_Control {

	/**
	 * The checkbox options.
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
	 * @param string $name The name of the checkbox control.
	 * @param string $label The label of the checkbox control.
	 * @param string $value The value of the checkbox control.
	 * @param array  $args The arguments of the checkbox control.
	 */
	public function __construct( $name, $label, $value = '', $args = array() ) {
		parent::__construct( $name, $label, $value, $args );

		// Set options from args.
		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$this->options = $args['options'];
		}

		// Set horizontal layout if specified.
		if ( isset( $args['horizontal'] ) ) {
			$this->horizontal = (bool) $args['horizontal'];
		}

		$this->css_classes = array( 'inline' );
	}

	/**
	 * Get the checkbox options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Set the checkbox options.
	 *
	 * @param array $options The options of the checkbox control.
	 */
	public function set_options( $options ) {
		if ( is_array( $options ) ) {
			$this->options = $options;
		}
	}

	/**
	 * Add a single option.
	 *
	 * @param string $value The value of the checkbox control.
	 * @param string $label The label of the checkbox control.
	 */
	public function add_option( $value, $label ) {
		$this->options[ $value ] = $label;
	}

	/**
	 * Check if a specific option is selected.
	 *
	 * @param string $value The value of the checkbox control.
	 * @return bool
	 */
	public function is_option_selected( $value ) {
		if ( is_array( $this->value ) ) {
			return in_array( $value, $this->value, true );
		}
		return $this->value === $value;
	}

	/**
	 * Get selected values as array.
	 *
	 * @return array
	 */
	public function get_selected_values() {
		if ( is_array( $this->value ) ) {
			return $this->value;
		}
		return $this->value ? array( $this->value ) : array();
	}

	/**
	 * Set selected values.
	 *
	 * @param array $values The values of the checkbox control.
	 */
	public function set_selected_values( $values ) {
		if ( is_array( $values ) ) {
			$this->value = $values;
		} else {
			$this->value = array();
		}
	}

	/**
	 * Render the checkbox field control with accessibility features.
	 *
	 * @return string
	 */
	protected function render_control() {
		if ( empty( $this->options ) ) {
			return '<p class="error">No options available for checkbox field.</p>';
		}

		$required_mark = $this->required ? ' <span class="required">*</span>' : '';

		// Generate unique fieldset ID for ARIA reference.
		$fieldset_id = $this->name . '_fieldset';

		// Start fieldset with legend for proper grouping.
		$output = '<fieldset id="' . esc_attr( $fieldset_id ) . '" class="checkbox-group' . ( $this->horizontal ? ' horizontal' : '' ) . '" role="group" aria-labelledby="' . esc_attr( $fieldset_id ) . '_legend"';

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
		$legend_id       = $fieldset_id . '_legend';
		$legend_required = $this->required ? ' <span class="required" aria-label="required">*</span>' : '';

		if ( $this->show_label ) {
			$output .= '<legend id="' . esc_attr( $legend_id ) . '" class="checkbox-legend">' . esc_html( $this->label ) . $legend_required . '</legend>';
		}

		// Add help text inside fieldset if it exists.
		if ( ! empty( $this->help_text ) ) {
			$output .= '<div id="' . esc_attr( $help_id ) . '" class="control-help-text">' . esc_html( $this->help_text ) . '</div>';
		}

		// Render each checkbox option.
		foreach ( $this->options as $value => $label ) {
			$id       = $this->get_attribute( 'id' ) . '-' . sanitize_key( $value );
			$name     = 1 === count( $this->options ) ? $this->get_attribute( 'name' ) : $this->get_attribute( 'name' ) . '[]';
			$checked  = $this->is_option_selected( $value ) ? ' checked' : '';
			$required = $this->required ? ' required' : '';

			$output .= '<div class="checkbox-option">';
			$output .= '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $checked . $required . '>';
			$output .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			$output .= '</div>';
		}

		$output .= '</fieldset>';

		return $output;
	}

	/**
	 * Override the parent render method to handle checkbox field structure.
	 * Checkbox fields use fieldset/legend instead of separate label.
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
