jQuery(document).ready(function($) {
    $('.dialog_form').submit( async function(e) {
        e.preventDefault();

        const submitType = e.originalEvent.submitter.value;

        // Firing ajax request to the server.
        const response = await $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $(this).serialize() + '&action=wp_user_notification&nonce=' + wp_user_notification.wp_user_notification_nonce + '&submit_type=' + submitType,
        });

        if ( ! response.success ) {
            console.log('Response:', response.data.message);
            return;
        }

        if ( 'confirm' === submitType || 'remind_me_later' === submitType ) {
            closeDialog( e.originalEvent.submitter );
        }

        if ($(this).attr('next')) {
            openDialog( 'dialog-' + $(this).attr('next'), document.body, null );
        }
    });
});