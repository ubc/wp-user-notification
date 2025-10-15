<?php
/**
 * The WP_User_Notification_Select_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Select Control
 *
 * Creates a dropdown select element for single selection from multiple options.
 */
class Select_Control extends Question_Control {

	/**
	 * The select options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Whether to include a placeholder option.
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * Whether the select allows multiple selections.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the select control.
	 * @param string $label The label of the select control.
	 * @param string $value The value of the select control.
	 * @param array  $args The arguments of the select control.
	 */
	public function __construct( $name, $label, $value = '', $args = array() ) {
		parent::__construct( $name, $label, $value, $args );

		// Set options from args.
		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$this->options = $args['options'];
		}

		// Set placeholder from args.
		if ( isset( $args['placeholder'] ) ) {
			$this->placeholder = sanitize_text_field( $args['placeholder'] );
		}

		// Set multiple selection from args.
		if ( isset( $args['multiple'] ) ) {
			$this->multiple = (bool) $args['multiple'];
		}

		// Add multiple attribute if needed.
		if ( $this->multiple ) {
			$this->set_attribute( 'multiple', 'multiple' );
			// Change name to array format for multiple selections.
			$this->set_attribute( 'name', $this->get_attribute( 'name' ) . '[]' );
		}
	}

	/**
	 * Get the select options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Set the select options.
	 *
	 * @param array $options The options of the select control.
	 */
	public function set_options( $options ) {
		if ( is_array( $options ) ) {
			$this->options = $options;
		}
	}

	/**
	 * Add a single option.
	 *
	 * @param string $value The value of the select control.
	 * @param string $label The label of the select control.
	 */
	public function add_option( $value, $label ) {
		$this->options[ $value ] = $label;
	}

	/**
	 * Check if a specific option is selected.
	 *
	 * @param string $value The value of the select control.
	 * @return bool
	 */
	public function is_option_selected( $value ) {
		if ( $this->multiple && is_array( $this->value ) ) {
			return in_array( $value, $this->value, true );
		}
		return $this->value === $value;
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
	 * Check if multiple selection is enabled.
	 *
	 * @return bool
	 */
	public function is_multiple() {
		return $this->multiple;
	}

	/**
	 * Set whether multiple selection is enabled.
	 *
	 * @param bool $multiple Whether multiple selection is enabled.
	 */
	public function set_multiple( $multiple ) {
		$this->multiple = (bool) $multiple;

		if ( $this->multiple ) {
			$this->set_attribute( 'multiple', 'multiple' );
			// Change name to array format for multiple selections.
			$this->set_attribute( 'name', $this->get_attribute( 'name' ) . '[]' );
		} else {
			$this->set_attribute( 'multiple', false );
			// Remove array format from name.
			$name = $this->get_attribute( 'name' );
			if ( substr( $name, -2 ) === '[]' ) {
				$this->set_attribute( 'name', substr( $name, 0, -2 ) );
			}
		}
	}

	/**
	 * Render the select field control with accessibility features.
	 *
	 * @return string
	 */
	protected function render_control() {
		if ( empty( $this->options ) && empty( $this->placeholder ) ) {
			return '<p class="error">No options available for select field.</p>';
		}

		// Generate unique select ID for ARIA reference.
		$select_id = $this->get_attribute( 'id' );

		// Start select element.
		$output = '<select id="' . esc_attr( $select_id ) . '" name="' . esc_attr( $this->get_attribute( 'name' ) ) . '" class="select-control"';

		// Add required attribute.
		if ( $this->required ) {
			$output .= ' required';
		}

		// Add multiple attribute if needed.
		if ( $this->multiple ) {
			$output .= ' multiple';
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

		// Add placeholder option if specified.
		if ( ! empty( $this->placeholder ) ) {
			$output .= '<option value="">' . esc_html( $this->placeholder ) . '</option>';
		}

		// Render each select option.
		foreach ( $this->options as $value => $label ) {
			$selected = $this->is_option_selected( $value ) ? ' selected' : '';
			$output  .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
