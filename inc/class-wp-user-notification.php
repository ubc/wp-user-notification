<?php
/**
 * This file handles the core notification object for the WP User Notification plugin.
 * The WP_User_Notification class is responsible for creating the notification object.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * The WP_User_Notification class.
 *
 * @package WP\User_Notification
 */
class WP_User_Notification {
	/**
	 * The label of the notification object.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * The unique identifier of the notification object.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * The priority of the notification object. This will determine the order of the notification pieces in the dashboard.
	 *
	 * @var int
	 */
	private $priority;

	/**
	 * The output of the notification object to the admin dashboard.
	 *
	 * @var string
	 */
	private $content = '';

	/**
	 * Whether the notification require user action.
	 *
	 * @var bool
	 */
	private $is_required = false;

	/**
	 * The message to be displayed when the notification is required.
	 *
	 * @var string
	 */
	private $required_message = 'I have read and agree to the terms and conditions.';

	/**
	 * The period of time after which the notification will be shown again if postponed.
	 *
	 * @var int
	 */
	private $postpone_period = DAY_IN_SECONDS;

	/**
	 * The user permissions required to view the notification object.
	 *
	 * @var array
	 */
	private $user_permissions_criteria = array( 'manage_options' );

	/**
	 * A custom call back function that takes the User ID and Site ID as parameters and returns a boolean. Default is null.
	 *
	 * @var array
	 */
	private $additional_criteria = null;

	/**
	 * The questions of the notification object.
	 *
	 * @var array
	 */
	private $questions = array();

	/**
	 * The version of the notification object.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * The constructor of the notification object.
	 *
	 * @param string $label The label of the notification object.
	 * @param string $slug The slug of the notification object.
	 * @param int    $priority The priority of the notification object.
	 */
	public function __construct( $label, $slug, $priority = 10 ) {
		$this->label    = $label;
		$this->slug     = sanitize_title( $slug );
		$this->priority = absint( $priority );
	}

	/**
	 * The setter for the postpone period of the notification object.
	 *
	 * @param int $postpone_period The postpone period of the notification object.
	 */
	public function set_postpone_period( $postpone_period ) {
		$this->postpone_period = $postpone_period;
	}

	/**
	 * The getter for the postpone period of the notification object.
	 *
	 * @return int The postpone period of the notification object.
	 */
	public function get_postpone_period() {
		return $this->postpone_period;
	}

	/**
	 * The getter for the priority of the notification object.
	 *
	 * @return int The priority of the notification object.
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * The setter for the priority of the notification object.
	 *
	 * @param int $priority The priority of the notification object.
	 */
	public function set_priority( $priority ) {
		$this->priority = absint( $priority );
	}

	/**
	 * The getter for the slug of the notification object.
	 *
	 * @return string The slug of the notification object.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * The getter for the label of the notification object.
	 *
	 * @return string The label of the notification object.
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * The setter for the label of the notification object.
	 *
	 * @param string $label The label of the notification object.
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * The getter for the output of the notification object.
	 *
	 * @return string The output of the notification object.
	 */
	public function get_output() {
		return $this->content;
	}

	/**
	 * The setter for the output of the notification object.
	 *
	 * @param string $content The output of the notification object.
	 */
	public function set_output( $content ) {
		$this->content = $content;
	}

	/**
	 * The getter for the required status of the notification object.
	 *
	 * @return bool The required status of the notification object.
	 */
	public function is_required() {
		return boolval( $this->is_required );
	}

	/**
	 * The setter for the required status of the notification object.
	 *
	 * @param bool $is_required The required status of the notification object.
	 */
	public function set_is_required( $is_required ) {
		$this->is_required = boolval( $is_required );
	}

	/**
	 * The getter for the required message of the notification object.
	 *
	 * @return string The required message of the notification object.
	 */
	public function get_required_message() {
		return $this->required_message;
	}

	/**
	 * The setter for the required message of the notification object.
	 *
	 * @param string $required_message The required message of the notification object.
	 */
	public function set_required_message( $required_message ) {
		$this->required_message = $required_message;
	}

	/**
	 * The getter for the questions of the notification object.
	 *
	 * @return array The questions of the notification object.
	 */
	public function get_questions() {
		return $this->questions;
	}

	/**
	 * The setter for the questions of the notification object.
	 *
	 * @param array $questions The questions of the notification object.
	 */
	public function set_questions( $questions ) {
		$this->questions = $questions;
	}

	/**
	 * The getter for the version of the notification object.
	 *
	 * @return string The version of the notification object.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * The setter for the version of the notification object.
	 *
	 * @param string $version The version of the notification object.
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}


	/**
	 * The getter for the user permissions criteria of the notification object.
	 *
	 * @return array The user permissions criteria of the notification object.
	 */
	public function get_user_permissions_criteria() {
		return $this->user_permissions_criteria;
	}

	/**
	 * The setter for the user permissions criteria of the notification object.
	 *
	 * @param array $user_permissions_criteria The user permissions criteria of the notification object.
	 */
	public function set_user_permissions_criteria( $user_permissions_criteria ) {
		$this->user_permissions_criteria = $user_permissions_criteria;
	}

	/**
	 * The getter for the additional criteria of the notification object.
	 *
	 * @return array The additional criteria of the notification object.
	 */
	public function get_additional_criteria() {
		return $this->additional_criteria;
	}

	/**
	 * The setter for the additional criteria of the notification object.
	 *
	 * @param array $additional_criteria The additional criteria of the notification object.
	 */
	public function set_additional_criteria( $additional_criteria ) {
		$this->additional_criteria = $additional_criteria;
	}

	/**
	 * The method to check if the notification is active.
	 *
	 * @return bool True if the notification is active, false otherwise.
	 */
	public function is_notification_active() {
		// Making sure current page is not a network admin page.
		if ( is_network_admin() ) {
			return false;
		}
		
		// Check if user has all of the required permissions.
		$has_permission = true;
		foreach ( $this->user_permissions_criteria as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				$has_permission = false;
				break;
			}
		}

		// Check if user has all of the required permissions, if not, then the notification is not active.
		if ( ! $has_permission ) {
			return false;
		}

		// Check if the additional criteria is met, if not met, then the notification is not active.
		if ( null !== $this->additional_criteria && is_callable( $this->additional_criteria ) ) {
			$result = call_user_func( $this->additional_criteria, $this );

			if ( ! $result ) {
				return false;
			}
		}

		// Check if the user has already completed the notification, if so, then the notification is not active.
		if ( WP_User_Notification_DB::is_notification_completed_or_pending( get_current_user_id(), $this->slug ) ) {
			return false;
		}

		return true;
	}
}
