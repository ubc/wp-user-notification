<?php
/**
 * This file handles the core database operations for the WP User Notification plugin.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * The WP_User_Notification_DB class.
 *
 * @package WP\User_Notification
 */
class WP_User_Notification_DB {
	/**
	 * The name of the User Notification table.
	 *
	 * @var string
	 */
	private static $notification_table_name = 'user_notification';

	/**
	 * The name of the User Notification Question Answers table.
	 *
	 * @var string
	 */
	private static $notification_question_answers_table_name = 'user_notification_question_answers';

	/**
	 * The version of the database tables.
	 *
	 * @var string
	 */
	private static $table_version = '1.0.0';

	/**
	 * The key of the option that stores the database table version.
	 *
	 * @var string
	 */
	private static $option_name = 'wp_user_notification_table_version';

	/**
	 * Create the database tables if they don't exist. If they do exist, check if they need to be updated.
	 */
	public static function maybe_create_global_table() {
		global $wpdb;

		$table_version = self::get_table_version();

		if ( ! empty( $table_version ) && $table_version === self::$table_version ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Create Notification Table.
		$table_name      = $wpdb->base_prefix . self::$notification_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				slug varchar(50) NOT NULL,
				status varchar(20) NOT NULL,
				version varchar(20) NOT NULL,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY user_id_slug (user_id, slug)
		) $charset_collate;";

		dbDelta( $sql );

		// Create Notification Question Answers Table.
		$table_name      = $wpdb->base_prefix . self::$notification_question_answers_table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				notification_id bigint(20) unsigned NOT NULL,
                question_slug varchar(255) NOT NULL,
                answer varchar(255),
                PRIMARY KEY (id),
                UNIQUE KEY unique_notification_question (notification_id, question_slug)
        ) $charset_collate;";

		dbDelta( $sql );

		self::set_table_version();
	}

	/**
	 * Get the table version.
	 */
	private static function get_table_version() {
		return get_site_option( self::$option_name, '' );
	}

	/**
	 * Set the table version.
	 */
	private static function set_table_version() {
		update_site_option( self::$option_name, self::$table_version );
	}

	/**
	 * Insert an notification into the table.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $slug The notification slug.
	 * @param string $status The notification status.
	 * @return int|false insert id, or false on error.
	 */
	public static function insert_notification( $user_id, $slug, $status ) {
		global $wpdb;

		$registry     = WP_User_Notification_Registry::get_instance();
		$notification = $registry->get_notification( $slug );

		if ( null === $notification ) {
			return false;
		}

		$table_name = $wpdb->base_prefix . self::$notification_table_name;

		$result = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO $table_name (user_id, slug, status, version, updated_at) 
				VALUES (%d, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE 
				id = LAST_INSERT_ID(id),
				slug = VALUES(slug),
				status = VALUES(status),
				version = VALUES(version),
				updated_at = VALUES(updated_at)",
				$user_id,
				$slug,
				$status,
				$notification->get_version(),
				current_time( 'mysql' )
			)
		);

		$cache_key = 'wp_user_notification_' . $user_id . '_' . $slug;
		wp_cache_delete( $cache_key, 'wp-user-notification' );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Check if an notification is completed or pending.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $slug The notification slug.
	 * @return bool True if the notification is completed or pending, false otherwise.
	 */
	public static function is_notification_completed_or_pending( $user_id, $slug ) {
		global $wpdb;

		$registry     = WP_User_Notification_Registry::get_instance();
		$notification = $registry->get_notification( $slug );

		// Notification object not available, usually means the notification is not active anymore. We consider it completed.
		if ( null === $notification ) {
			return true;
		}

		// Try to get notification complete record from cache first if the notification has already been completed by current user.
		// So we don't hit the database on every page load.
		$cache_key = 'wp_user_notification_' . $user_id . '_' . $slug;
		$result    = wp_cache_get( $cache_key, 'wp-user-notification' );

		if ( false === $result ) {
			$table_name = $wpdb->base_prefix . self::$notification_table_name;
			$result     = $wpdb->get_row(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM `$table_name` WHERE user_id = %d AND slug = %s",
					$user_id,
					$slug
				)
			);

			// If the notification is not found, return false.
			if ( empty( $result ) || null === $result ) {
				return false;
			}

			// Cache the result.
			// Note: Expiration only matters if a persistent cache plugin (like Redis) is installed.
			// Without persistent cache, data only lives until the end of this page load.
			// Set the cache only if the notification is found and status is complete.
			if ( 'completed' === $result->status ) {
				wp_cache_set( $cache_key, $result, 'wp-user-notification', DAY_IN_SECONDS );
			}
		}

		$postpone_period = $notification->get_postpone_period();
		// Convert both timestamps to UTC for accurate comparison.
		$updated_at_utc   = get_gmt_from_date( $result->updated_at, 'U' );
		$current_time_utc = time();

		// If the notification is found, check if it is the newest version.
		// If the notification is found, check if it has been completed.
		// If the notification is pending, check if is has passed the postpone period.
		return version_compare( $notification->get_version(), $result->version, '<=' ) && ( 'completed' === $result->status || ( 'postponed' === $result->status && $updated_at_utc + $postpone_period > $current_time_utc ) );
	}

	/**
	 * Insert an notification answer into the table.
	 *
	 * @param int    $notification_id The notification ID.
	 * @param string $question_slug The question slug.
	 * @param string $answer The answer.
	 * @return int|false insert id, or false on error.
	 */
	public static function insert_answer( $notification_id, $question_slug, $answer ) {
		global $wpdb;

		$table_name = $wpdb->base_prefix . self::$notification_question_answers_table_name;

		$result = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO $table_name (notification_id, question_slug, answer) 
				VALUES (%d, %s, %s)
				ON DUPLICATE KEY UPDATE 
				id = LAST_INSERT_ID(id),
				question_slug = VALUES(question_slug),
				answer = VALUES(answer)",
				$notification_id,
				$question_slug,
				$answer
			)
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get the user notification answers.
	 *
	 * @param string $notification_slug The notification slug.
	 * @param string $question_slug The question slug.
	 * @return array The user notification answers.
	 */
	public static function get_user_notification_answer( $notification_slug, $question_slug ) {
		global $wpdb;

		// Get notification.
		$table_name = $wpdb->base_prefix . self::$notification_table_name;
		$result     = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id FROM `$table_name` WHERE user_id = %d AND slug = %s",
				get_current_user_id(),
				$notification_slug
			)
		);

		if ( empty( $result ) || null === $result ) {
			return false;
		}

		$table_name = $wpdb->base_prefix . self::$notification_question_answers_table_name;
		$result     = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT answer FROM `$table_name` WHERE notification_id = %d AND question_slug = %s",
				$result->id,
				$question_slug
			)
		);

		return $result ? $result->answer : false;
	}

	/**
	 * Get an answer for a specific notification and question.
	 *
	 * @param int    $notification_id The notification ID.
	 * @param string $question_slug The question slug.
	 * @return string|false The answer or false if not found.
	 */
	public static function get_answer( $notification_id, $question_slug ) {
		global $wpdb;

		if ( empty( $notification_id ) || empty( $question_slug ) ) {
			return false;
		}

		$table_name = $wpdb->base_prefix . self::$notification_question_answers_table_name;
		$result     = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `$table_name` WHERE notification_id = %d AND question_slug = %s",
				$notification_id,
				$question_slug
			)
		);

		return $result ? $result->answer : false;
	}

	/**
	 * Delete all answeres related to a specific notification.
	 *
	 * @param int $notification_id The notification ID.
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function delete_answers( $notification_id ) {
		global $wpdb;

		if ( empty( $notification_id ) ) {
			return false;
		}

		$table_name = $wpdb->base_prefix . self::$notification_question_answers_table_name;

		return $wpdb->delete(
			$table_name,
			array(
				'notification_id' => $notification_id,
			),
			array( '%d' )
		);
	}
}

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\WP_User_Notification_DB', 'maybe_create_global_table' ) );
