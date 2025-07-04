jQuery(document).ready(function($) {
    $('#generate-llms-txt').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text(nt_llms_txt_builder.generate_text);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ntllms_txt_builder_generate_file',
                nonce: nt_llms_txt_builder.generate_nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#llms-txt-result').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    $('#llms-txt-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                $('#llms-txt-result').html('<div class="notice notice-error"><p>' + nt_llms_txt_builder.error_text + '</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text(nt_llms_txt_builder.generate_button_text);
            }
        });
    });
    
    $('#clear-llms-txt-cache').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text(nt_llms_txt_builder.clearing_text);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ntllms_txt_builder_clear_cache_data',
                nonce: nt_llms_txt_builder.clear_cache_nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#llms-txt-result').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    $('#llms-txt-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                $('#llms-txt-result').html('<div class="notice notice-error"><p>' + nt_llms_txt_builder.error_text + '</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text(nt_llms_txt_builder.clear_cache_button_text);
            }
        });
    });
}); 