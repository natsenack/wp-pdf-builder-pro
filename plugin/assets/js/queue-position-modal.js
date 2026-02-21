/**
 * PDF Builder Pro - Queue Position Modal
 * Affiche la position de l'utilisateur dans la queue d'attente pour la génération PDF
 */

(function() {
    'use strict';

    const QueuePositionModal = {
        // Configuration
        pollInterval: 2000, // ms
        maxWaitTime: 600000, // 10 minutes max
        
        /**
         * Initialise la modal et commence le polling
         */
        init: function(jobId, templateId, orderId, options = {}) {
            console.log('[PDF Builder Queue] Initializing modal for job:', jobId);
            
            this.jobId = jobId;
            this.templateId = templateId;
            this.orderId = orderId;
            this.startTime = Date.now();
            this.pollInterval = options.pollInterval || 2000;
            this.maxWaitTime = options.maxWaitTime || 600000;
            
            // Créer et afficher la modal
            this.createModal();
            this.startPolling();
        },

        /**
         * Crée l'HTML de la modal
         */
        createModal: function() {
            const modalHTML = `
                <div id="pdf-builder-queue-modal" class="pdf-builder-queue-modal-overlay">
                    <div class="pdf-builder-queue-modal">
                        <div class="pdf-builder-queue-modal-header">
                            <h2>⏳ Génération PDF en attente</h2>
                        </div>
                        
                        <div class="pdf-builder-queue-modal-body">
                            <div class="queue-position-display">
                                <div class="queue-position-text">
                                    Position dans la queue: <span id="queue-position">...</span>
                                </div>
                                <div class="queue-wait-time">
                                    Temps d'attente estimé: <span id="queue-wait-time">...</span>
                                </div>
                            </div>
                            
                            <div class="queue-progress-bar">
                                <div class="progress-fill" id="queue-progress"></div>
                            </div>
                            
                            <div id="queue-status-message" class="queue-status-message">
                                Votre demande a été ajoutée à la queue. Merci de patienter...
                            </div>
                        </div>
                        
                        <div class="pdf-builder-queue-modal-footer">
                            <button id="cancel-queue-btn" class="button button-secondary">Fermer</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Event listeners
            document.getElementById('cancel-queue-btn').addEventListener('click', () => {
                this.closeModal();
            });
        },

        /**
         * Compose le style CSS pour la modal
         */
        injectStyles: function() {
            if (document.getElementById('pdf-builder-queue-styles')) {
                return; // Déjà injecté
            }
            
            const styles = `
                /* Queue Modal Styles */
                .pdf-builder-queue-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                    font-family: sans-serif;
                }
                
                .pdf-builder-queue-modal {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                    width: 90%;
                    max-width: 500px;
                    overflow: hidden;
                    animation: slideIn 0.3s ease-out;
                }
                
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .pdf-builder-queue-modal-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 24px;
                    border-bottom: 1px solid #e0e0e0;
                }
                
                .pdf-builder-queue-modal-header h2 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .pdf-builder-queue-modal-body {
                    padding: 32px 24px;
                    text-align: center;
                }
                
                .queue-position-display {
                    margin-bottom: 24px;
                }
                
                .queue-position-text {
                    font-size: 18px;
                    font-weight: 500;
                    color: #333;
                    margin-bottom: 12px;
                }
                
                .queue-position-text span {
                    color: #667eea;
                    font-weight: 700;
                    font-size: 24px;
                }
                
                .queue-wait-time {
                    font-size: 14px;
                    color: #666;
                }
                
                .queue-wait-time span {
                    color: #764ba2;
                    font-weight: 600;
                }
                
                .queue-progress-bar {
                    height: 8px;
                    background: #e0e0e0;
                    border-radius: 4px;
                    overflow: hidden;
                    margin: 24px 0;
                }
                
                .progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #667eea, #764ba2);
                    width: 0%;
                    animation: indeterminate 2s infinite;
                    border-radius: 4px;
                }
                
                @keyframes indeterminate {
                    0% {
                        transform: translateX(-100%);
                    }
                    100% {
                        transform: translateX(100%);
                    }
                }
                
                .queue-status-message {
                    font-size: 14px;
                    color: #666;
                    line-height: 1.6;
                    margin-top: 16px;
                }
                
                .pdf-builder-queue-modal-footer {
                    padding: 16px 24px;
                    background: #f9f9f9;
                    border-top: 1px solid #e0e0e0;
                    text-align: right;
                }
                
                .pdf-builder-queue-modal-footer button {
                    padding: 10px 20px;
                    border-radius: 4px;
                    font-size: 14px;
                    font-weight: 500;
                }
                
                .button.button-secondary {
                    background: #6c757d;
                    color: white;
                    border: none;
                    cursor: pointer;
                }
                
                .button.button-secondary:hover {
                    background: #5a6268;
                }
                
                .button.button-primary {
                    background: #667eea;
                    color: white;
                    border: none;
                    cursor: pointer;
                }
                
                .button.button-primary:hover {
                    background: #5568d3;
                }
                
                /* Success state */
                .queue-ready {
                    color: #28a745;
                }
                
                /* Error state */
                .queue-error {
                    color: #dc3545;
                }
            `;
            
            const styleSheet = document.createElement('style');
            styleSheet.id = 'pdf-builder-queue-styles';
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        },

        /**
         * Commence le polling pour checker l'état du job
         */
        startPolling: function() {
            console.log('[PDF Builder Queue] Starting polling for job:', this.jobId);
            this.injectStyles();
            this.pollJob();
        },

        /**
         * Vérifie l'état du job une fois
         */
        pollJob: function() {
            const elapsed = Date.now() - this.startTime;
            
            // Check timeout
            if (elapsed > this.maxWaitTime) {
                this.showError('Timeout: La génération a dépassé le délai maximal');
                return;
            }
            
            // AJAX call
            const queueData = (typeof pdfBuilderQueueData !== 'undefined') ? pdfBuilderQueueData : { ajaxurl: ajaxurl, nonce: '' };
            fetch(queueData.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pdf_builder_check_queue_position&job_id=' + encodeURIComponent(this.jobId) + '&_wpnonce=' + queueData.nonce
            })
            .then(response => response.json())
            .then(data => {
                console.log('[PDF Builder Queue] Poll response:', data);
                
                if (!data.success) {
                    this.showError(data.data?.message || 'Erreur lors de la vérification');
                    return;
                }
                
                const status = data.data.status;
                const position = data.data.position;
                const waitTime = data.data.wait_time;
                
                // Update display
                this.updateDisplay(position, waitTime);
                
                if (status === 200) {
                    // PDF is ready!
                    this.downloadPDF();
                    return;
                }
                
                if (status === 202 || status === 409) {
                    // Still in queue, poll again
                    setTimeout(() => this.pollJob(), this.pollInterval);
                    return;
                }
                
                // Other status
                if (status >= 500) {
                    this.showError('Erreur serveur (HTTP ' + status + ')');
                } else {
                    this.showError('Erreur lors de la génération (HTTP ' + status + ')');
                }
            })
            .catch(error => {
                console.error('[PDF Builder Queue] Fetch error:', error);
                this.showError('Erreur réseau: ' + error.message);
            });
        },

        /**
         * Met à jour l'affichage avec la position actuelle
         */
        updateDisplay: function(position, waitTime) {
            const positionEl = document.getElementById('queue-position');
            const waitTimeEl = document.getElementById('queue-wait-time');
            
            if (positionEl) {
                if (position !== null) {
                    positionEl.textContent = position + ' en attente';
                } else {
                    positionEl.textContent = '...';
                }
            }
            
            if (waitTimeEl) {
                if (waitTime !== null) {
                    waitTimeEl.textContent = this.formatSeconds(waitTime);
                } else {
                    waitTimeEl.textContent = 'calcul...';
                }
            }
            
            // Update progress
            const elapsed = Date.now() - this.startTime;
            const progress = Math.min(85, (elapsed / this.maxWaitTime) * 100);
            const progressEl = document.getElementById('queue-progress');
            if (progressEl) {
                progressEl.style.width = progress + '%';
            }
        },

        /**
         * Format seconds to readable format
         */
        formatSeconds: function(seconds) {
            if (seconds < 60) {
                return seconds + ' sec';
            }
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return minutes + 'min ' + secs + 'sec';
        },

        /**
         * Télécharge le PDF une fois prêt
         */
        downloadPDF: function() {
            console.log('[PDF Builder Queue] PDF is ready, opening in new tab...');
            
            const messageEl = document.getElementById('queue-status-message');
            if (messageEl) {
                messageEl.textContent = 'Génération terminée, ouverture du PDF...';
                messageEl.className = 'queue-status-message queue-ready';
            }
            
            const progressEl = document.getElementById('queue-progress');
            if (progressEl) {
                progressEl.style.width = '100%';
            }
            
            // Ouvrir le PDF dans un nouvel onglet via fetch + blob URL
            const dlData = (typeof pdfBuilderQueueData !== 'undefined') ? pdfBuilderQueueData : { ajaxurl: ajaxurl, nonce: '' };
            fetch(dlData.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=pdf_builder_download_queued_pdf' +
                      '&job_id=' + encodeURIComponent(this.jobId) +
                      '&order_id=' + encodeURIComponent(this.orderId) +
                      '&template_id=' + encodeURIComponent(this.templateId) +
                      '&_wpnonce=' + dlData.nonce
            })
            .then(async response => {
                const buffer = await response.arrayBuffer();
                const blob = new Blob([buffer], { type: 'application/pdf' });
                const url = URL.createObjectURL(blob);
                window.open(url, '_blank');
                // Révoquer après un délai pour laisser le temps au navigateur de charger
                setTimeout(() => URL.revokeObjectURL(url), 10000);
            })
            .catch(err => console.error('[PDF Builder Queue] Open error:', err))
            .finally(() => {
                setTimeout(() => this.closeModal(), 1000);
            });
        },

        /**
         * Affiche un message d'erreur
         */
        showError: function(message) {
            console.error('[PDF Builder Queue] Error:', message);
            
            const messageEl = document.getElementById('queue-status-message');
            if (messageEl) {
                messageEl.textContent = '❌ ' + message;
                messageEl.className = 'queue-status-message queue-error';
            }
            
            const bodyEl = document.querySelector('.pdf-builder-queue-modal-body');
            if (bodyEl) {
                bodyEl.style.color = '#dc3545';
            }
            
            const headerEl = document.querySelector('.pdf-builder-queue-modal-header');
            if (headerEl) {
                headerEl.style.background = '#dc3545';
            }
        },

        /**
         * Ferme la modal
         */
        closeModal: function() {
            const modal = document.getElementById('pdf-builder-queue-modal');
            if (modal) {
                modal.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    modal.remove();
                }, 300);
            }
        }
    };

    // Export pour utilisation
    window.PdfBuilderQueueModal = QueuePositionModal;
})();
