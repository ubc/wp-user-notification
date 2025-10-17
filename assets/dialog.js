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
        const current = $( e.originalEvent.submitter ).closest('dialog' );

        if ( current[0] ) {
          current[0].close();
        }
      }

      if ($(this).attr('next')) {
        const next = $( '#dialog-' + $(this).attr('next') );

        if ( next[0] ) {
          next[0].showModal();
        }
      }
  });
});