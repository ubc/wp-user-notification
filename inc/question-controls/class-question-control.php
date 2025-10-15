<?php
/**
 * The WP_User_Notification_Question_Control namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * Abstract class for form controls used in user notification questions.
 *
 * This abstract class provides a foundation for creating various types of form controls
 * that can be used in user notification questions. Each control type should extend this class
 * and implement the required abstract methods.
 */
abstract class Question_Control {
	/**
	 * The label text for this control.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * The unique name attribute for the form control.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The current value of the control.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Whether this control is required.
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Additional HTML attributes for the control.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * CSS classes for the control.
	 *
	 * @var array
	 */
	protected $css_classes = array();

	/**
	 * CSS classes string for the control.
	 *
	 * @var string
	 */
	protected $css_classes_string = '';

	/**
	 * Help text to display with the control.
	 *
	 * @var string
	 */
	protected $help_text = '';

	/**
	 * Whether to show the label.
	 *
	 * @var bool
	 */
	protected $show_label = true;

	/**
	 * Constructor.
	 *
	 * @param string $name The unique identifier for this control.
	 * @param string $label The label text for this control.
	 * @param string $value The value of the control.
	 * @param array  $args Optional arguments for the control.
	 */
	public function __construct( $name, $label, $value = '', $args = array() ) {
		$this->label = $label;
		$this->value = $value;
		$this->name  = $name;

		// Set default attributes.
		$this->attributes = wp_parse_args(
			$args['attributes'] ?? array(),
			array(
				'id'   => 'question-' . $this->name,
				'name' => 'question[' . $this->name . ']',
			)
		);

		// Set other properties from args.
		if ( isset( $args['required'] ) ) {
			$this->required = (bool) $args['required'];
		}

		if ( isset( $args['css_classes'] ) ) {
			$this->css_classes = is_array( $args['css_classes'] ) ? $args['css_classes'] : array( $args['css_classes'] );
		}

		if ( isset( $args['help_text'] ) ) {
			$this->help_text = sanitize_text_field( $args['help_text'] );
		}

		if ( isset( $args['show_label'] ) ) {
			$this->show_label = (bool) $args['show_label'];
		}

		// Add required class if needed.
		if ( $this->required ) {
			$this->css_classes[] = 'required';
		}
	}

	/**
	 * Get the control label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Set the control label.
	 *
	 * @param string $label The label text for this control.
	 */
	public function set_label( $label ) {
		$this->label = sanitize_text_field( $label );
	}

	/**
	 * Get the control name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the control name.
	 *
	 * @param string $name The unique identifier for this control.
	 */
	public function set_name( $name ) {
		$this->name               = sanitize_key( $name );
		$this->attributes['name'] = 'question[' . $this->name . ']';
	}

	/**
	 * Get the current value.
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Check if the control is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		return $this->required;
	}

	/**
	 * Set whether the control is required.
	 *
	 * @param bool $required Whether the control is required.
	 */
	public function set_required( $required ) {
		$this->required = (bool) $required;

		if ( $required ) {
			$this->css_classes[] = 'required';
		} else {
			$this->css_classes = array_diff( $this->css_classes, array( 'required' ) );
		}
	}

	/**
	 * Get CSS classes.
	 *
	 * @return array
	 */
	public function get_css_classes() {
		return $this->css_classes;
	}

	/**
	 * Add CSS class.
	 *
	 * @param string $class_names The CSS class to add.
	 */
	public function add_css_class( $class_names ) {
		if ( ! in_array( $class_names, $this->css_classes, true ) ) {
			$this->css_classes[] = $class_names;
		}
	}

	/**
	 * Remove CSS class.
	 *
	 * @param string $class_names The CSS class to remove.
	 */
	public function remove_css_class( $class_names ) {
		$this->css_classes = array_diff( $this->css_classes, array( $class_names ) );
	}

	/**
	 * Get HTML attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Set HTML attribute.
	 *
	 * @param string $key The HTML attribute key.
	 * @param mixed  $value The HTML attribute value.
	 */
	public function set_attribute( $key, $value ) {
		$this->attributes[ $key ] = $value;
	}

	/**
	 * Get HTML attribute.
	 *
	 * @param string $key The HTML attribute key.
	 * @return mixed
	 */
	public function get_attribute( $key ) {
		return $this->attributes[ $key ];
	}

	/**
	 * Get the attributes string for HTML output.
	 *
	 * @return string
	 */
	protected function get_attributes_string() {
		$attributes = $this->attributes;

		// Add required attribute.
		if ( $this->required ) {
			$attributes['required'] = 'required';
		}

		$attr_string = '';
		foreach ( $attributes as $key => $value ) {
			if ( true === $value ) {
				$attr_string .= ' ' . esc_attr( $key );
			} elseif ( false !== $value && null !== $value ) {
				$attr_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return $attr_string;
	}

	/**
	 * Get help text.
	 *
	 * @return string
	 */
	public function get_help_text() {
		return $this->help_text;
	}

	/**
	 * Set help text.
	 *
	 * @param string $help_text The help text to display with the control.
	 */
	public function set_help_text( $help_text ) {
		$this->help_text = sanitize_text_field( $help_text );
	}

	/**
	 * Render the label HTML.
	 *
	 * @return string
	 */
	protected function render_label() {
		$required_mark = $this->required ? ' <span class="required">*</span>' : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return '<label class="label_text" for="question-' . esc_attr( $this->name ) . '">' . esc_html( $this->label ) . $required_mark . '</label>';
	}

	/**
	 * Render the control HTML.
	 *
	 * @param string $notification_slug The notification slug.
	 */
	public function render( $notification_slug = null ) {
		// Add CSS classes.
		if ( ! empty( $this->css_classes ) ) {
			$this->css_classes_string = implode( ' ', $this->css_classes );
		}

		self::maybe_get_existing_value( $notification_slug );

		$output = '<div class="control-' . esc_attr( $this->name ) . ' ' . esc_attr( $this->css_classes_string ) . '">';

		// Render label.
		if ( $this->show_label ) {
			$output .= $this->render_label();
		}

		// Render control (which includes the fieldset with legend).
		$output .= $this->render_control();

		$output .= '</div>';

		return $output;
	}

	/**
	 * Get the existing value from the database.
	 *
	 * @param string $notification_slug The notification slug.
	 */
	protected function maybe_get_existing_value( $notification_slug ) {
		if ( null === $notification_slug ) {
			return;
		}

		$existing_value = WP_User_Notification_DB::get_user_notification_answer( $notification_slug, $this->name );

		if ( false !== $existing_value ) {
			$this->value = maybe_unserialize( $existing_value );
		}
	}

	/**
	 * Render the control HTML.
	 *
	 * @return string
	 */
	abstract protected function render_control();
}
