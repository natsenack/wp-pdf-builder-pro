/**
 * PDF Builder - Preview API Client
 * Client for PDF preview API calls
 */
console.log('[PDF PREVIEW API CLIENT] Script loaded and executing');
(function($) {
    'use strict';

    

    window.pdfPreviewApiClient = {
        // API client for PDF preview
        generatePreview: function(data, callback) {
            console.log('[PDF Preview API] generatePreview called with data:', data);
            console.log('[PDF Preview API] generatePreview callback function provided:', typeof callback);
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
            formData.append('_wpnonce', window.pdfBuilderAjax?.nonce || '');

            console.log('[PDF Preview API] About to make AJAX call to:', ajaxurl || '/wp-admin/admin-ajax.php');

            return $.ajax({
                url: ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('[PDF Preview API] AJAX SUCCESS - Raw response:', response);
                    console.log('[PDF Preview API] Response type:', typeof response);
                    console.log('[PDF Preview API] Response is object:', response && typeof response === 'object');
                    if (response && typeof response === 'object') {
                        console.log('[PDF Preview API] Response keys:', Object.keys(response));
                        console.log('[PDF Preview API] Response success:', response.success);
                        console.log('[PDF Preview API] Response data:', response.data);
                        if (response.data) {
                            console.log('[PDF Preview API] Response data type:', typeof response.data);
                            console.log('[PDF Preview API] Response data keys:', Object.keys(response.data));
                            console.log('[PDF Preview API] Response data image_url:', response.data.image_url);
                            console.log('[PDF Preview API] Response data preview_url:', response.data.preview_url);
                        }
                    }
                    if (typeof response === 'string') {
                        try {
                            const parsed = JSON.parse(response);
                            console.log('[PDF Preview API] Parsed response:', parsed);
                            response = parsed;
                        } catch (e) {
                            console.error('[PDF Preview API] Failed to parse response as JSON:', e);
                        }
                    }
                    if (callback) callback(null, response);
                },
                error: function(xhr, status, error) {
                    console.error('[PDF Preview API] AJAX ERROR - Status:', status);
                    console.error('[PDF Preview API] AJAX ERROR - Error:', error);
                    console.error('[PDF Preview API] AJAX ERROR - Response text:', xhr.responseText);
                    console.error('[PDF Preview API] AJAX ERROR - Status code:', xhr.status);
                    console.error('[PDF Preview API] AJAX ERROR - Headers:', xhr.getAllResponseHeaders());
                    console.error('[PDF Preview API] AJAX ERROR - Ready state:', xhr.readyState);
                    console.error('[PDF Preview API] AJAX ERROR - Full xhr object:', xhr);
                    
                    if (callback) {
                        console.log('[PDF Preview API] Calling callback with error');
                        callback(error, null);
                    }
                }
            });
        },

        getPreviewStatus: function(previewId, callback) {
            const formData = new FormData();
            formData.append('action', 'pdf_builder_get_preview_status');
            formData.append('preview_id', previewId);
            formData.append('_wpnonce', window.pdfBuilderAjax?.nonce || '');

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
            console.log('[PDF Preview API] generateEditorPreview called with templateData:', templateData);
            console.log('[PDF Preview API] generateEditorPreview options:', options);
            return new Promise(function(resolve, reject) {
                console.log('[PDF Preview API] Creating Promise for generateEditorPreview');
                window.pdfPreviewApiClient.generatePreview({
                    template_data: templateData,
                    context: 'editor',
                    format: options?.format || 'png',
                    quality: options?.quality || 150
                }, function(error, response) {
                    console.log('[PDF Preview API] generateEditorPreview callback called - error:', error, 'response:', response);
                    console.log('[PDF Preview API] Callback execution - checking error condition');
                    console.log('[PDF Preview API] Error exists:', !!error);
                    console.log('[PDF Preview API] Response exists:', !!response);
                    if (error) {
                        console.error('[PDF Preview API] generateEditorPreview rejecting with error:', error);
                        console.error('[PDF Preview API] Error type:', typeof error);
                        console.error('[PDF Preview API] Error details:', error);
                        console.error('[PDF Preview API] About to reject Promise with new Error(error)');
                        reject(new Error(error));
                    } else {
                        console.log('[PDF Preview API] generateEditorPreview resolving with response:', response);
                        console.log('[PDF Preview API] Response type:', typeof response);
                        console.log('[PDF Preview API] Response keys:', response ? Object.keys(response) : 'No response');
                        console.log('[PDF Preview API] Response success:', response ? response.success : 'No response');
                        console.log('[PDF Preview API] Response data:', response ? response.data : 'No response');
                        console.log('[PDF Preview API] About to resolve Promise with response');
                        resolve(response);
                    }
                });
            });
        },

        generateOrderPreview: function(templateData, orderId, options) {
            console.log('[PDF Preview API] generateOrderPreview called with templateData:', templateData, 'orderId:', orderId);
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

