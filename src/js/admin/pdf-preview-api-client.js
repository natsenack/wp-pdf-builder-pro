/**
 * PDF Builder - Preview API Client
 * Client for PDF preview API calls
 */
(function($) {
    'use strict';

    

    window.pdfPreviewApiClient = {
        // API client for PDF preview
        generatePreview: function(data, callback) {
            

            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_preview',
                    data: JSON.stringify(data),
                    nonce: window.pdfBuilderAjax?.nonce
                },
                success: function(response) {
                    if (callback) callback(null, response);
                },
                error: function(xhr, status, error) {
                    
                    if (callback) callback(error, null);
                }
            });
        },

        getPreviewStatus: function(previewId, callback) {
            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'pdf_builder_get_preview_status',
                    preview_id: previewId,
                    nonce: window.pdfBuilderAjax?.nonce
                },
                success: function(response) {
                    if (callback) callback(null, response);
                },
                error: function(xhr, status, error) {
                    if (callback) callback(error, null);
                }
            });
        }
    };

})(jQuery);

