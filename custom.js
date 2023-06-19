jQuery(document).ready(function($) {
    // AJAX call on button click
    $('#postdata').click(function(e) {
        e.preventDefault();
        $('#loader').removeClass('d-none');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_all_post_data',
                security: ajax_object.ajax_nonce
            },
            success: function(response) {
                // Handle the response data
              //  console.log(response);
              $('#loader').addClass('d-none');
              $('#message-container').html('<p class="success-message">Data sent successfully.</p>');
            },
            error: function(xhr, status, error) {
                // Display error message
                $('#message-container').html('<p class="error-message">An error occurred: ' + error + '</p>');
              },
              complete: function() {
                // Clear messages after a delay
                setTimeout(function() {
                    $('#message-container').empty();
                }, 2000);
            }
        });
    });
});

