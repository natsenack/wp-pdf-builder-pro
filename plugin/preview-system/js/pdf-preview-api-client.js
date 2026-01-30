/**
 * PDF Builder - Preview API Client
 * Client-side API pour appeler le backend de génération PDF
 */

(function($) {
    'use strict';

    // Configuration
    const config = {
        ajaxUrl: window.ajaxurl || (window.pdfBuilderData?.ajaxurl) || '/wp-admin/admin-ajax.php',
        nonce: window.pdfBuilderData?.nonce || window.pdfBuilderNonce || '',
        endpoint: 'pdf_builder_generate_preview'
    };

    
    
    

    /**
     * Appelle le backend pour générer un aperçu PDF
     * @param {Object} templateData - Données du template (éléments, styles, etc.)
     * @param {Object} options - Options (format, quality, etc.)
     * @returns {Promise<Object>} Résultat avec image_url ou erreur
     */
    async function callGeneratorBackend(templateData, options = {}) {
        
        
        

        try {
            // Extraire template_id s'il est dans templateData (pour compatibilité)
            const template_id = templateData?.template_id || null;
            
            
            
            
            
            const params = {
                action: config.endpoint,
                nonce: config.nonce,
                data: JSON.stringify({
                    pageOptions: {
                        template: templateData
                    },
                    previewType: 'general',
                    orderNumberToPreview: ''
                }),
                format: options.format || 'png',
                quality: options.quality || 150
            };

            // Ajouter template_id s'il existe (pour compatibilité)
            if (template_id) {
                params.template_id = template_id;
                
            }

            

            // Ajouter template_id s'il existe
            if (template_id) {
                params.template_id = template_id;
                
            }

            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(params)
            });

            

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Handle different response types based on format
            const format = options.format || 'png';
            if (format === 'pdf') {
                // For PDF, expect binary content and create a blob URL
                const pdfBlob = await response.blob();
                

                // Check if blob has content
                if (pdfBlob.size === 0) {
                    
                    throw new Error('Le PDF généré est vide');
                }

                // Check if it's actually a PDF
                if (!pdfBlob.type.includes('pdf') && !pdfBlob.type.includes('application/octet-stream')) {
                    
                }

                const pdfUrl = URL.createObjectURL(pdfBlob);
                

                // Verify URL is valid
                if (!pdfUrl || pdfUrl === 'blob:') {
                    
                    throw new Error('URL blob invalide créée');
                }

                return {
                    success: true,
                    data: {
                        image_url: pdfUrl,
                        format: 'pdf'
                    }
                };
            } else {
                // For images (PNG/JPG), expect JSON response
                const result = await response.json();
                

                if (!result.success) {
                    throw new Error(result.error || 'Erreur lors de la génération du PDF');
                }

                return {
                    success: true,
                    data: {
                        image_url: result.data?.image_url || result.image_url,
                        format: format
                    }
                };
            }
        } catch (error) {
            
            throw new Error(`Erreur backend: ${error.message}`);
        }
    }

    /**
     * Génère une image placeholder en cas de failure
     * @param {string} format - Format d'image (png, jpg)
     * @returns {string} Data URL de l'image
     */
    function generatePlaceholderImage(format = 'png') {
        

        const canvas = document.createElement('canvas');
        canvas.width = 800;
        canvas.height = 600;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            
            return null;
        }

        // Fond blanc
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Bordure
        ctx.strokeStyle = '#cccccc';
        ctx.lineWidth = 2;
        ctx.strokeRect(0, 0, canvas.width, canvas.height);

        // Texte
        ctx.fillStyle = '#666666';
        ctx.font = 'bold 24px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Aperçu PDF', canvas.width / 2, canvas.height / 2 - 50);

        ctx.font = '16px Arial';
        ctx.fillText('Image placeholder (fallback)', canvas.width / 2, canvas.height / 2 + 20);

        // Convertir en data URL
        const dataUrl = canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png');
        

        return dataUrl;
    }

    // API publique
    window.pdfPreviewApiClient = {
        generatePreview: function(data, callback) {
            
            callGeneratorBackend(data).then(result => {
                if (callback) callback(result);
            }).catch(error => {
                if (callback) callback({ error: error.message });
            });
        }
    };

    window.pdfPreviewAPI = {
        /**
         * Génère un aperçu à partir des données d'éditeur
         */
        generateEditorPreview: async function(templateData, options = {}) {
            

            try {
                // Essayer d'abord le backend réel
                const result = await callGeneratorBackend(templateData, options);
                
                return result;
            } catch (error) {
                
                

                // Fallback: placeholder image
                const placeholderUrl = generatePlaceholderImage(options.format || 'png');
                if (placeholderUrl) {
                    return {
                        success: true,
                        data: {
                            image_url: placeholderUrl,
                            format: options.format || 'png',
                            fallback: true
                        }
                    };
                }

                throw error;
            }
        },

        /**
         * Génère un aperçu pour une commande
         */
        generateOrderPreview: async function(templateData, orderId, options = {}) {
            

            const data = {
                ...templateData,
                order_id: orderId
            };

            return this.generateEditorPreview(data, options);
        }
    };

    

})(jQuery);
