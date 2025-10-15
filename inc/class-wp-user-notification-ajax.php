<?php
/**
 * The WP_User_Notification_Ajax namespace.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * The WP_User_Notification_Ajax class.
 *
 * @package WP\User_Notification
 */
class WP_User_Notification_Ajax {

	/**
	 * Initialize the AJAX handlers.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wp_user_notification', array( $this, 'handle_notification' ) );
	}

	/**
	 * Handle the notification confirm AJAX request.
	 */
	public function handle_notification() {
		// Verify nonce for security.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'wp_user_notification_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wp-user-notification' ) ) );
		}

		$submit_type       = sanitize_text_field( wp_unslash( $_POST['submit_type'] ?? false ) );
		$notification_slug = sanitize_text_field( wp_unslash( $_POST['notification_slug'] ?? false ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$questions = isset( $_POST['question'] ) ? $this->sanitize_nested_array( wp_unslash( $_POST['question'] ) ) : array();

		if ( ! $notification_slug || ! $submit_type ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields.', 'wp-user-notification' ) ) );
		}

		// Get the registry instance.
		$registry      = WP_User_Notification_Registry::get_instance();
		$notifications = $registry->all();

		// Check if the notification exists.
		if ( ! isset( $notifications[ $notification_slug ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Notification not found.', 'wp-user-notification' ) ) );
		}

		$notification = $notifications[ $notification_slug ];

		if ( 'confirm' === $submit_type ) {
			$this->handle_confirm_action( $notification, $questions );
		}

		if ( 'remind_me_later' === $submit_type ) {
			$this->handle_remind_later_action( $notification );
		}

		wp_send_json_error( array( 'message' => __( 'Invalid submit type.', 'wp-user-notification' ) ) );
	}

	/**
	 * Handle the confirm action.
	 *
	 * @param WP_User_Notification $notification The notification object.
	 * @param array                $questions  The questions.
	 */
	private function handle_confirm_action( $notification, $questions ) {
		$notification_id = WP_User_Notification_DB::insert_notification( get_current_user_id(), $notification->get_slug(), 'completed' );
		foreach ( $questions as $key => $question ) {
			WP_User_Notification_DB::insert_answer( $notification_id, $key, maybe_serialize( $question ) );
		}

		do_action( 'wp_user_notification_completed', $notification, $questions );

		if ( false === $notification_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to complete notification.', 'wp-user-notification' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Notification completed successfully.', 'wp-user-notification' ) ) );
	}

	/**
	 * Handle the remind me later action.
	 *
	 * @param WP_User_Notification $notification The notification object.
	 */
	private function handle_remind_later_action( $notification ) {
		$notification_id = WP_User_Notification_DB::insert_notification( get_current_user_id(), $notification->get_slug(), 'postponed' );

		do_action( 'wp_user_notification_postponed', $notification );

		if ( false === $notification_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to postpone notification.', 'wp-user-notification' ) ) );
		}

		// Save the notification data with postponed status.
		wp_send_json_success( array( 'message' => __( 'Notification postponed. You will be reminded later.', 'wp-user-notification' ) ) );
	}

	/**
	 * Sanitize a nested array.
	 *
	 * @param array  $input_data The array to sanitize.
	 * @param string $sanitize_callback The sanitize callback.
	 * @return array The sanitized array.
	 */
	private function sanitize_nested_array( $input_data, $sanitize_callback = 'sanitize_text_field' ) {
		foreach ( $input_data as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->sanitize_nested_array( $value, $sanitize_callback );
			} else {
				$value = call_user_func( $sanitize_callback, $value );
			}
		}
		return $input_data;
	}
}
