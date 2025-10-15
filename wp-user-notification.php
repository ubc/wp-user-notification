<?php
/**
 * Plugin Name:       WP User Notification
 * Description:       A plugin that allows network administrators to create notifications.
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Kelvin Xu
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-user-notification
 *
 * @package           wp-user-notification
 */

namespace WP\User_Notification;

define( 'WP_USER_NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_USER_NOTIFICATION_DIR', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/inc/class-wp-user-notification.php';
require_once __DIR__ . '/inc/class-wp-user-notification-registry.php';
require_once __DIR__ . '/inc/class-wp-user-notification-ajax.php';
require_once __DIR__ . '/inc/class-wp-user-notification-db.php';

require_once __DIR__ . '/inc/question-controls/class-question-control.php';
require_once __DIR__ . '/inc/question-controls/class-textinput-control.php';
require_once __DIR__ . '/inc/question-controls/class-radio-control.php';
require_once __DIR__ . '/inc/question-controls/class-checkbox-control.php';
require_once __DIR__ . '/inc/question-controls/class-select-control.php';
require_once __DIR__ . '/inc/question-controls/class-textarea-control.php';

$registry = WP_User_Notification_Registry::get_instance();

// Initialize AJAX handler.
new WP_User_Notification_Ajax();

// Render the active notifications.
add_action(
	'admin_init',
	function () use ( $registry ) {
		$registry->render_active_notifications();
	}
);
