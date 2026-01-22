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

            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_preview',
                    template_data: JSON.stringify(data.template_data),
                    preview_type: data.context || 'editor',
                    format: data.format || 'png',
                    quality: data.quality || 150,
                    order_id: data.order_id || null,
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

