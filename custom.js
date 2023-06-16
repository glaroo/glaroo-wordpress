jQuery(document).ready(function($) {
    // AJAX call on button click
    $('#postdata').click(function(e) {
        e.preventDefault();
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
                console.log(response);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle the error
                console.error(textStatus + ': ' + errorThrown);
            }
        });
    });
});