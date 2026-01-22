/**
 * PDF Builder - Preview API Client
 * Client for PDF preview API calls
 */

// LOG ABSOLU AU DÉBUT DU FICHIER
console.log('=== PDF PREVIEW API CLIENT LOADED ===');
console.log('Timestamp:', Date.now());
console.log('User Agent:', navigator.userAgent);
console.log('Location:', window.location.href);

console.log('[PDF PREVIEW API CLIENT] ===== FILE LOADED =====');
console.log('[PDF PREVIEW API CLIENT] Script loaded and executing at:', new Date().toISOString());
console.log('[PDF PREVIEW API CLIENT] Window object available:', typeof window);
console.log('[PDF PREVIEW API CLIENT] jQuery available:', typeof jQuery);
console.log('[PDF PREVIEW API CLIENT] ajaxurl available:', typeof ajaxurl);
console.log('[PDF PREVIEW API CLIENT] pdfBuilderAjax available:', typeof window.pdfBuilderAjax);
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
            
            // Ajouter template_id s'il est disponible
            if (data.template_id) {
                formData.append('template_id', data.template_id.toString());
                console.log('[PDF Preview API] Sending template_id:', data.template_id);
            }
            
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
                    console.log('[PDF PREVIEW API] ===== AJAX SUCCESS CALLBACK =====');
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Raw response received:', response);
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Response type:', typeof response);
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Response is object:', response && typeof response === 'object');
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Response is array:', Array.isArray(response));
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Response constructor:', response?.constructor?.name || 'Unknown');

                    if (response && typeof response === 'object') {
                        console.log('[PDF PREVIEW API] AJAX SUCCESS - Response keys:', Object.keys(response));
                        console.log('[PDF PREVIEW API] AJAX SUCCESS - Response success:', response.success);
                        console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data:', response.data);
                        console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data type:', typeof response.data);

                        if (response.data) {
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data keys:', Object.keys(response.data));
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data image_url:', response.data.image_url);
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data preview_url:', response.data.preview_url);
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Response data error:', response.data.error);
                        }
                    }

                    // Handle string responses (JSON)
                    if (typeof response === 'string') {
                        console.log('[PDF PREVIEW API] AJAX SUCCESS - Response is string, attempting JSON parse');
                        try {
                            const parsed = JSON.parse(response);
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Parsed response:', parsed);
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Parsed response type:', typeof parsed);
                            console.log('[PDF PREVIEW API] AJAX SUCCESS - Using parsed response');
                            response = parsed;
                        } catch (e) {
                            console.error('[PDF PREVIEW API] AJAX SUCCESS - Failed to parse response as JSON:', e);
                            console.error('[PDF PREVIEW API] AJAX SUCCESS - Raw string response:', response);
                        }
                    }

                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Final response object:', response);
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - About to call callback with null error and response');
                    if (callback) callback(null, response);
                    console.log('[PDF PREVIEW API] AJAX SUCCESS - Callback called successfully');
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
            console.log('[PDF PREVIEW API] ===== generateEditorPreview CALLED =====');
            console.log('[PDF PREVIEW API] Template data provided:', !!templateData);
            console.log('[PDF PREVIEW API] Template data type:', typeof templateData);
            console.log('[PDF PREVIEW API] Template data keys:', templateData ? Object.keys(templateData) : 'No data');
            console.log('[PDF PREVIEW API] Options:', options);
            console.log('[PDF PREVIEW API] Timestamp:', Date.now());
            console.log('[PDF PREVIEW API] Creating Promise');
            return new Promise(function(resolve, reject) {
                console.log('[PDF PREVIEW API] ===== PROMISE EXECUTOR CALLED =====');
                console.log('[PDF PREVIEW API] About to call pdfPreviewApiClient.generatePreview');
                console.log('[PDF PREVIEW API] pdfPreviewApiClient available:', typeof window.pdfPreviewApiClient);
                console.log('[PDF PREVIEW API] generatePreview method available:', typeof (window.pdfPreviewApiClient && window.pdfPreviewApiClient.generatePreview));

                if (!window.pdfPreviewApiClient || !window.pdfPreviewApiClient.generatePreview) {
                    console.error('[PDF PREVIEW API] pdfPreviewApiClient.generatePreview not available');
                    reject(new Error('pdfPreviewApiClient.generatePreview not available'));
                    return;
                }

                window.pdfPreviewApiClient.generatePreview({
                    template_data: templateData,
                    context: 'editor',
                    format: options?.format || 'png',
                    quality: options?.quality || 150
                }, function(error, response) {
                    console.log('[PDF PREVIEW API] ===== CALLBACK EXECUTED =====');
                    console.log('[PDF PREVIEW API] Callback timestamp:', Date.now());
                    console.log('[PDF PREVIEW API] Callback error:', error);
                    console.log('[PDF PREVIEW API] Callback response:', response);
                    console.log('[PDF PREVIEW API] Checking error condition');
                    console.log('[PDF PREVIEW API] Error exists:', !!error);
                    console.log('[PDF PREVIEW API] Response exists:', !!response);

                    if (error) {
                        console.error('[PDF PREVIEW API] ===== REJECTING PROMISE =====');
                        console.error('[PDF PREVIEW API] Error type:', typeof error);
                        console.error('[PDF PREVIEW API] Error details:', error);
                        console.error('[PDF PREVIEW API] About to reject Promise');
                        reject(new Error(error));
                    } else {
                        console.log('[PDF PREVIEW API] ===== RESOLVING PROMISE =====');
                        console.log('[PDF PREVIEW API] Response type:', typeof response);
                        console.log('[PDF PREVIEW API] Response keys:', response ? Object.keys(response) : 'No response');
                        console.log('[PDF PREVIEW API] Response success:', response ? response.success : 'No response');
                        console.log('[PDF PREVIEW API] Response data:', response ? response.data : 'No response');
                        console.log('[PDF PREVIEW API] Response data type:', response && response.data ? typeof response.data : 'No data');
                        console.log('[PDF PREVIEW API] Response data keys:', response && response.data ? Object.keys(response.data) : 'No data keys');
                        console.log('[PDF PREVIEW API] Response data image_url:', response && response.data ? response.data.image_url : 'No image_url');
                        console.log('[PDF PREVIEW API] About to resolve Promise');
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

