<?php
/**
 * This file handles the core registry for the WP User Notification plugin.
 * The Notification Registry is responsible for managing and registering notification objects.
 *
 * @package WP\User_Notification
 */

namespace WP\User_Notification;

/**
 * The WP_User_Notification_Registry class.
 *
 * @package WP\User_Notification
 */
class WP_User_Notification_Registry {
	/**
	 * The single instance of the class.
	 *
	 * @var WP_User_Notification_Registry
	 */
	private static $instance = null;

	/**
	 * The notifications collection.
	 *
	 * @var array
	 */
	protected $notifications = array();

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of the instance.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing of the instance.
	 *
	 * @throws \Exception Always throws an exception.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize WP_User_Notification_Registry singleton' );
	}

	/**
	 * Get the single instance of the class.
	 *
	 * @return WP_User_Notification_Registry
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register an notification object.
	 *
	 * @param WP_User_Notification $notification The notification object to register.
	 * @return void
	 */
	public function register( $notification ) {
		if ( ! $notification instanceof WP_User_Notification ) {
			return;
		}

		// Use hashtable to store unique notification objects by their slug.
		$this->notifications[ $notification->get_slug() ] = $notification;
	}

	/**
	 * Unregister an notification object.
	 *
	 * @param string $slug The notification slug.
	 * @return void
	 */
	public function unregister( $slug ) {
		unset( $this->notifications[ $slug ] );
	}

	/**
	 * Get an notification object by slug.
	 *
	 * @param string $slug The notification slug.
	 * @return WP_User_Notification|null The notification object or null if not found.
	 */
	public function get_notification( $slug ) {
		return $this->notifications[ $slug ] ?? null;
	}

	/**
	 * Get all registered notifications.
	 *
	 * @return array
	 */
	public function all(): array {
		return $this->notifications;
	}

	/**
	 * Get all active notifications.
	 *
	 * @return array
	 */
	public function get_active_notifications(): array {
		$active_notifications = array_filter(
			$this->notifications,
			function ( $notification ) {
				return $notification->is_notification_active();
			}
		);

		// Sort the active notifications by priority. If same priority, sort by label alphabetically.
		usort(
			$active_notifications,
			function ( $a, $b ) {
				if ( $a->get_priority() === $b->get_priority() ) {
					return strcmp( $a->get_label(), $b->get_label() );
				}
				return $a->get_priority() - $b->get_priority();
			}
		);

		return $active_notifications;
	}

	/**
	 * Render the active notifications.
	 *
	 * @return void
	 */
	public function render_active_notifications(): void {
		$active_notifications = $this->get_active_notifications();

		if ( empty( $active_notifications ) ) {
			return;
		}

		$allowed_tags_for_form_controls = array(
			'div'      => array(
				'class'           => array(),
				'id'              => array(),
				'role'            => array(),
				'aria-labelledby' => array(),
			),
			'h1'       => array(
				'id'    => array(),
				'class' => array(),
			),
			'p'        => array(
				'class' => array(),
			),
			'span'     => array(
				'class'      => array(),
				'aria-label' => array(),
			),
			'label'    => array(
				'class' => array(),
				'for'   => array(),
			),
			'input'    => array(
				'type'           => array(),
				'id'             => array(),
				'name'           => array(),
				'value'          => array(),
				'class'          => array(),
				'required'       => array(),
				'checked'        => array(),
				'aria-required'  => array(),
				'formnovalidate' => array(),
			),
			'button'   => array(
				'type'           => array(),
				'class'          => array(),
				'name'           => array(),
				'value'          => array(),
				'formnovalidate' => array(),
			),
			'fieldset' => array(
				'id'               => array(),
				'class'            => array(),
				'role'             => array(),
				'aria-labelledby'  => array(),
				'aria-required'    => array(),
				'aria-describedby' => array(),
			),
			'legend'   => array(
				'id'    => array(),
				'class' => array(),
			),
			'select'   => array(
				'class'            => array(),
				'id'               => array(),
				'name'             => array(),
				'required'         => array(),
				'aria-required'    => array(),
				'aria-describedby' => array(),
				'aria-label'       => array(),
			),
			'option'   => array(
				'value'    => array(),
				'selected' => array(),
			),
			'textarea' => array(
				'id'               => array(),
				'name'             => array(),
				'class'            => array(),
				'rows'             => array(),
				'cols'             => array(),
				'placeholder'      => array(),
				'wrap'             => array(),
				'required'         => array(),
				'aria-required'    => array(),
				'aria-describedby' => array(),
			),
		);

		add_action(
			'admin_footer',
			function () use ( $active_notifications, $allowed_tags_for_form_controls ) {
				?>
				<div id="wp-user-notification-dialog">
					<?php
					$notification_count = count( $active_notifications );
					for ( $i = 0; $i < $notification_count; $i++ ) :
						?>
					<dialog id="dialog-<?php echo esc_attr( $active_notifications[ $i ]->get_slug() ); ?>" aria-labelledby="dialog_<?php echo esc_attr( $active_notifications[ $i ]->get_slug() ); ?>_label">
						<h2 id="dialog_<?php echo esc_attr( $active_notifications[ $i ]->get_slug() ); ?>_label" class="dialog_label" autofocus tabindex="-1"><?php echo esc_html( $active_notifications[ $i ]->get_label() ); ?></h2>
						<?php echo wp_kses_post( $active_notifications[ $i ]->get_output() ); ?>

						<form class="dialog_form" next="<?php echo isset( $active_notifications[ $i + 1 ] ) ? esc_attr( $active_notifications[ $i + 1 ]->get_slug() ) : ''; ?>">
						<?php if ( ! empty( $active_notifications[ $i ]->get_questions() ) ) : ?>
							<?php foreach ( $active_notifications[ $i ]->get_questions() as $question ) : ?>
								<?php
								// Check if question is a control object or array.
								if ( $question instanceof \WP\User_Notification\Question_Control ) {
									echo '<div class="dialog_form_item">';
									echo wp_kses( $question->render( $active_notifications[ $i ]->get_slug() ), $allowed_tags_for_form_controls );
									echo '</div>';
								}
								?>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php
						if ( $active_notifications[ $i ]->is_required() ) {
							$consent_form_field = new Checkbox_Control(
								'consent',
								'Consent',
								'',
								array(
									'options'    => array( 'yes' => $active_notifications[ $i ]->get_required_message() ),
									'show_label' => false,
								)
							);
							$consent_form_field->set_attribute( 'name', 'consent' );
							$consent_form_field->set_required( true );
							echo '<div class="dialog_form_item">';
							echo wp_kses( $consent_form_field->render(), $allowed_tags_for_form_controls );
							echo '</div>';
						}
						?>
							<div class="dialog_actions">
								<input type="hidden" name="notification_slug" value="<?php echo esc_attr( $active_notifications[ $i ]->get_slug() ); ?>">
								<button type="submit" class="button button-primary" name="action" value="confirm"><?php esc_html_e( 'Confirm', 'wp-user-notification' ); ?></button>
								<button type="submit" class="button button-secondary" name="action" value="remind_me_later" formnovalidate><?php esc_html_e( 'Remind me later', 'wp-user-notification' ); ?></button>
							</div>
						</form>
					</dialog>
				<?php endfor; ?>
				</div>

				<?php if ( count( $active_notifications ) > 0 ) : ?>
				<script>
					jQuery(document).ready(function($) {
						document.getElementById( 'dialog-<?php echo esc_attr( $active_notifications[0]->get_slug() ); ?>' ).showModal();
					});
				</script>
					<?php
				endif;

				wp_enqueue_script( 'wp-user-notification-dialog', WP_USER_NOTIFICATION_URL . 'assets/dialog.js', array(), filemtime( WP_USER_NOTIFICATION_DIR . 'assets/dialog.js' ), true );
				wp_enqueue_style( 'wp-user-notification-dialog', WP_USER_NOTIFICATION_URL . 'assets/dialog.css', array(), filemtime( WP_USER_NOTIFICATION_DIR . 'assets/dialog.css' ) );

				wp_localize_script( 'wp-user-notification-dialog', 'wp_user_notification', array( 'wp_user_notification_nonce' => wp_create_nonce( 'wp_user_notification_nonce' ) ) );
			}
		);
	}
}