/**
 * PDF Builder - Preview API Client
 * Client for PDF preview API calls
 */
(function($) {
    'use strict';

    

    window.pdfPreviewApiClient = {
        // API client for PDF preview
        generatePreview: function(data, callback) {
            console.log('[PDF Preview API] generatePreview called with data:', data);
            console.log('[PDF Preview API] pdfBuilderAjax available:', typeof window.pdfBuilderAjax);
            console.log('[PDF Preview API] ajaxurl:', window.pdfBuilderAjax ? window.pdfBuilderAjax.ajaxurl : 'undefined');
            console.log('[PDF Preview API] nonce:', window.pdfBuilderAjax ? window.pdfBuilderAjax.nonce : 'undefined');

            // Use FormData to ensure proper encoding
            const formData = new FormData();
            formData.append('action', 'pdf_builder_generate_preview');
            formData.append('template_data', JSON.stringify(data.template_data));
            formData.append('preview_type', data.context || 'editor');
            formData.append('format', data.format || 'png');
            formData.append('quality', (data.quality || 150).toString());
            if (data.order_id) {
                formData.append('order_id', data.order_id.toString());
            }
            formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (callback) callback(null, response);
                },
                error: function(xhr, status, error) {
                    
                    if (callback) callback(error, null);
                }
            });
        },

        getPreviewStatus: function(previewId, callback) {
            const formData = new FormData();
            formData.append('action', 'pdf_builder_get_preview_status');
            formData.append('preview_id', previewId);
            formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (callback) callback(null, response);
                },
                error: function(xhr, status, error) {
                    if (callback) callback(error, null);
                }
            });
        }
    };

    // Define the expected global API interface for React components
    window.pdfPreviewAPI = {
        generateEditorPreview: function(templateData, options) {
            return new Promise(function(resolve, reject) {
                window.pdfPreviewApiClient.generatePreview({
                    template_data: templateData,
                    context: 'editor',
                    format: options?.format || 'png',
                    quality: options?.quality || 150
                }, function(error, response) {
                    if (error) {
                        reject(new Error(error));
                    } else {
                        resolve(response);
                    }
                });
            });
        },

        generateOrderPreview: function(templateData, orderId, options) {
            return new Promise(function(resolve, reject) {
                window.pdfPreviewApiClient.generatePreview({
                    template_data: templateData,
                    order_id: orderId,
                    context: 'metabox',
                    format: options?.format || 'png',
                    quality: options?.quality || 150
                }, function(error, response) {
                    if (error) {
                        reject(new Error(error));
                    } else {
                        resolve(response);
                    }
                });
            });
        }
    };

})(jQuery);

