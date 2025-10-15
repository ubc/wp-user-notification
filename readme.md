# WP User Notification

A powerful notification system that allows network administrators to create and manage user notifications with custom forms and criteria.

- Contributors: LTIC WordPress
- Tags: notifications, user notifications, admin notifications, multisite
- Requires at least: 6.5
- Tested up to: 6.5
- Requires PHP: 7.4
- Stable tag: 1.0.0
- License: GPL-2.0-or-later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description

WP User Notification is a flexible notification system that enables network administrators to create and manage notifications for WordPress users. The plugin provides a robust framework for displaying important messages, announcements, and collecting user responses through customizable forms.

### Key Features

* Create custom notifications with rich content
* Set notification priority levels
* Configure notification visibility based on user permissions
* Add custom forms with various input types (text, radio, checkbox, select, textarea)
* Set required acknowledgments with custom messages
* Configure postpone periods for notifications
* Add custom criteria for notification display
* Version control for notifications

### Use Cases

* Important announcements for site administrators
* Terms of service acknowledgments
* User surveys and feedback collection
* Policy updates and confirmations
* Custom questionnaires

## Installation

1. Upload the `wp-user-notification` folder to the `/wp-content/plugins/` directory
2. Find and activate `WP User Notification` plugin
3. Configure notifications inside a separate plugin

## Usage

The WP User Notification plugin provides a flexible API for creating custom notifications. Here's how to create and customize notifications:

### Basic Notification

```php
use WP\User_Notification\WP_User_Notification;
use WP\User_Notification\WP_User_Notification_Registry;

// Get the registry instance
$registry = WP_User_Notification_Registry::get_instance();

// Create a basic notification
$notification = new WP_User_Notification(
    'Welcome Message',     // Label
    'welcome-message',    // Unique slug
    10                    // Priority (lower numbers show first)
);

// Add content
$notification->set_output( '<p>Welcome to our platform!</p>' );

// Register the notification
$registry->register( $notification );
```

### Required Acknowledgment

```php
// Make the notification require acknowledgment
$notification->set_is_required( true );
$notification->set_required_message( 'I have read and understood this message' );

// Set how long until the notification appears again if postponed
$notification->set_postpone_period( WEEK_IN_SECONDS );
```

### User Permission Control

```php
// Show only to users with specific capabilities
$notification->set_user_permissions_criteria( array( 'manage_options', 'edit_posts' ) );
```

### Custom Display Criteria

```php
// Add custom logic for when to show the notification
$notification->set_additional_criteria( function( $notification ) {
    // Show only to users who haven't completed their profile
    $user = wp_get_current_user();
    return empty( get_user_meta( $user->ID, 'biography', true ) );
} );
```

### Adding Form Fields

The plugin supports various form fields for collecting user input:

```php
// Create form questions
$questions = array(
    array(
        'type' => 'text',
        'label' => 'Your Name',
        'required' => true
    ),
    array(
        'type' => 'select',
        'label' => 'Department',
        'options' => array(
            'hr' => 'Human Resources',
            'it' => 'Information Technology',
            'marketing' => 'Marketing'
        )
    ),
    array(
        'type' => 'radio',
        'label' => 'Preferred Contact Method',
        'options' => array(
            'email' => 'Email',
            'phone' => 'Phone'
        )
    ),
    array(
        'type' => 'checkbox',
        'label' => 'Subscribe to newsletter'
    ),
    array(
        'type' => 'textarea',
        'label' => 'Additional Comments'
    )
);

$notification->set_questions( $questions );
```

### Version Control

```php
// Set version to track notification updates
$notification->set_version( '1.1.0' );
```

## Frequently Asked Questions

### Can I create notifications for specific user roles?

Yes, you can set user permission criteria for each notification to target specific user roles or capabilities.

### Can users dismiss notifications permanently?

Yes, notifications can be configured to be dismissed permanently or temporarily with a custom postpone period.

### Can I add custom forms to notifications?

Yes, the plugin supports various form input types including text input, radio buttons, checkboxes, select dropdowns, and textareas.

### Can I add custom criteria for when notifications should appear?

Yes, you can add custom callback functions to determine when notifications should be displayed based on any criteria you define.

## Changelog

### 1.0.0
* Initial release

## Development

* [GitHub Repository](git@github.com:ubc/wp-user-notification.git)
* [Issue Tracker](https://github.com/ubc/wp-user-notification/issues)

## Credits

* Developed by LTIC WordPress