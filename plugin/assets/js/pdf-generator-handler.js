/**
 * PDF Builder Pro - PDF Generation Handler
 * Gère le déclenchement de la génération PDF et affiche la modal de queue si nécessaire
 */

(function() {
    'use strict';

    const PDFGenerator = {
        /**
         * Initialise les listeners sur les boutons de génération PDF
         */
        init: function() {
            // Écouter les clics sur les boutons de génération (admin et frontend)
            document.addEventListener('click', (e) => {
                // Bouton admin pour générer PDF
                if (e.target.closest('[data-action="generate-pdf"]') || 
                    e.target.closest('.pdf-generate-btn') ||
                    e.target.closest('[class*="pdf-generate"]')) {
                    e.preventDefault();
                    this.handleGeneratePDFClick(e);
                }
            });
            
            // Écouter les soumissions de formulaires AJAX
            document.addEventListener('submit', (e) => {
                if (e.target.closest('[data-action*="pdf"]')) {
                    // Potentiellement un formulaire PDF, vérifier si c'est lié à la génération
                }
            });
            
            console.log('[PDF Generator] Initialized');
        },

        /**
         * Gère le clic sur le bouton de génération PDF
         */
        handleGeneratePDFClick: function(event) {
            console.log('[PDF Generator] PDF generation click detected');
            
            const btn = event.target.closest('[data-action="generate-pdf"], .pdf-generate-btn, [class*="pdf-generate"]');
            if (!btn) return;
            
            const templateId = btn.dataset.templateId || btn.getAttribute('data-template-id');
            const orderId = btn.dataset.orderId || btn.getAttribute('data-order-id');
            
            if (!templateId || !orderId) {
                console.warn('[PDF Generator] Missing template_id or order_id');
                return;
            }
            
            console.log('[PDF Generator] Generating PDF for order:', orderId, 'template:', templateId);
            
            // Afficher un loader
            this.showLoading();
            
            // Appeler l'endpoint AJAX
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pdf_builder_generate_pdf&template_id=' + encodeURIComponent(templateId) + 
                      '&order_id=' + encodeURIComponent(orderId) + 
                      '&nonce=' + encodeURIComponent(ajaxNonce || '')
            })
            .then(response => {
                // D'abord, vérifier si c'est du JSON ou du binaire
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(json => ({ json, response }));
                } else {
                    return response.blob().then(blob => ({ blob, response }));
                }
            })
            .then(({ json, blob, response }) => {
                this.hideLoading();
                
                // Si JSON avec queue_active
                if (json && json.data && json.data.queue_active) {
                    console.log('[PDF Generator] Queue active, showing modal for job:', json.data.job_id);
                    this.showQueueModal(json.data);
                    return;
                }
                
                // Si JSON avec erreur
                if (json && !json.success) {
                    alert('Erreur: ' + (json.data?.message || 'Erreur lors de la génération'));
                    return;
                }
                
                // Si PDF binaire
                if (blob) {
                    console.log('[PDF Generator] PDF received, downloading...');
                    this.downloadPDF(blob);
                    return;
                }
                
                // Fallback pour le vieux comportement GET/redirection
                if (response.status === 200 && response.url) {
                    window.location.href = response.url;
                    return;
                }
                
                console.warn('[PDF Generator] Unexpected response:', { json, blob, response });
            })
            .catch(error => {
                this.hideLoading();
                console.error('[PDF Generator] Fetch error:', error);
                alert('Erreur réseau: ' + error.message);
            });
        },

        /**
         * Affiche la modal de queue position
         */
        showQueueModal: function(data) {
            if (window.PdfBuilderQueueModal && window.PdfBuilderQueueModal.init) {
                window.PdfBuilderQueueModal.init(
                    data.job_id, 
                    data.template_id, 
                    data.order_id,
                    { pollInterval: 2000, maxWaitTime: 600000 }
                );
            } else {
                console.error('[PDF Generator] Queue modal library not loaded');
                alert('Votre demande est en queue: ' + data.job_id);
            }
        },

        /**
         * Télécharge le PDF reçu en blob
         */
        downloadPDF: function(blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'document.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        },

        /**
         * Affiche un indicateur de chargement
         */
        showLoading: function() {
            let loader = document.getElementById('pdf-generator-loader');
            if (!loader) {
                loader = document.createElement('div');
                loader.id = 'pdf-generator-loader';
                loader.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    padding: 20px 40px;
                    border-radius: 8px;
                    z-index: 9999;
                    font-family: sans-serif;
                    font-size: 16px;
                `;
                loader.innerHTML = '⏳ Génération en cours...';
                document.body.appendChild(loader);
            }
            loader.style.display = 'block';
        },

        /**
         * Masque l'indicateur de chargement
         */
        hideLoading: function() {
            const loader = document.getElementById('pdf-generator-loader');
            if (loader) {
                loader.style.display = 'none';
            }
        }
    };

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => PDFGenerator.init());
    } else {
        PDFGenerator.init();
    }
    
    // Exporter pour utilisation globale
    window.PdfBuilderGenerator = PDFGenerator;
})();
