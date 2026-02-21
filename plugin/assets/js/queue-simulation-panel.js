/**
 * PDF Builder Pro - Queue Simulation Control Panel
 * Permet d'activer/désactiver et configurer le mode simulation de queue pour les tests
 */

(function() {
    'use strict';

    const QueueSimulationPanel = {
        /**
         * Initialise le panneau de contrôle quand il est disponible
         */
        init: function() {
            const panel = document.getElementById('pdf-builder-queue-simulation-panel');
            if (!panel) {
                return; // Panel non présent
            }

            this.setupEventListeners();
            this.loadStatus();
        },

        /**
         * Configure les event listeners
         */
        setupEventListeners: function() {
            const toggleBtn = document.getElementById('pdf-queue-sim-toggle');
            const configBtn = document.getElementById('pdf-queue-sim-config');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => this.toggleSimulation());
            }
            
            if (configBtn) {
                configBtn.addEventListener('click', () => this.saveConfiguration());
            }
        },

        /**
         * Charge l'état actuel du mode simulation
         */
        loadStatus: function() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pdf_builder_toggle_queue_simulation&action_type=status&nonce=' + ajaxNonce
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateUI(data.data);
                }
            })
            .catch(error => console.error('[Queue Simulation] Error loading status:', error));
        },

        /**
         * Bascule le mode simulation
         */
        toggleSimulation: function() {
            const btn = document.getElementById('pdf-queue-sim-toggle');
            btn.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pdf_builder_toggle_queue_simulation&action_type=toggle&nonce=' + ajaxNonce
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    console.log('[Queue Simulation] Toggled:', data.data);
                    this.updateUI(data.data);
                    this.showNotice(data.data.message, data.success ? 'success' : 'error');
                } else {
                    this.showNotice(data.data?.message || 'Erreur', 'error');
                }
            })
            .catch(error => {
                btn.disabled = false;
                console.error('[Queue Simulation] Error:', error);
                this.showNotice('Erreur réseau', 'error');
            });
        },

        /**
         * Sauvegarde les paramètres de configuration
         */
        saveConfiguration: function() {
            const positionInput = document.getElementById('pdf-queue-sim-position');
            const waitTimeInput = document.getElementById('pdf-queue-sim-wait-time');
            const btn = document.getElementById('pdf-queue-sim-config');
            
            if (!positionInput || !waitTimeInput) {
                return;
            }
            
            const initialPosition = parseInt(positionInput.value) || 5;
            const waitTime = parseInt(waitTimeInput.value) || 30;
            
            btn.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pdf_builder_toggle_queue_simulation&action_type=configure&' +
                      'initial_position=' + initialPosition + '&' +
                      'wait_time=' + waitTime + '&' +
                      'nonce=' + ajaxNonce
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    console.log('[Queue Simulation] Configuration saved:', data.data);
                    this.showNotice('Paramètres sauvegardés', 'success');
                } else {
                    this.showNotice(data.data?.message || 'Erreur', 'error');
                }
            })
            .catch(error => {
                btn.disabled = false;
                console.error('[Queue Simulation] Error:', error);
                this.showNotice('Erreur réseau', 'error');
            });
        },

        /**
         * Met à jour l'interface avec le nouvel état
         */
        updateUI: function(data) {
            const statusEl = document.getElementById('pdf-queue-sim-status');
            const toggleBtn = document.getElementById('pdf-queue-sim-toggle');
            const configSection = document.querySelector('.pdf-queue-sim-config');
            
            if (statusEl) {
                const status = data.enabled ? '✅ ACTIF' : '⚫ INACTIF';
                statusEl.textContent = status;
                statusEl.style.color = data.enabled ? '#28a745' : '#6c757d';
            }
            
            if (toggleBtn) {
                toggleBtn.textContent = data.enabled ? '🔴 Désactiver' : '🟢 Activer';
            }
            
            if (configSection) {
                configSection.style.opacity = data.enabled ? '1' : '0.5';
                configSection.style.pointerEvents = data.enabled ? 'auto' : 'none';
            }
            
            if (data.initial_position !== undefined) {
                const positionInput = document.getElementById('pdf-queue-sim-position');
                if (positionInput) {
                    positionInput.value = data.initial_position;
                }
            }
            
            if (data.wait_time !== undefined) {
                const waitTimeInput = document.getElementById('pdf-queue-sim-wait-time');
                if (waitTimeInput) {
                    waitTimeInput.value = data.wait_time;
                }
            }
        },

        /**
         * Affiche un message de notification
         */
        showNotice: function(message, type = 'info') {
            const notice = document.createElement('div');
            notice.className = 'notice notice-' + type + ' is-dismissible';
            notice.innerHTML = '<p>' + message + '</p>';
            
            const target = document.getElementById('pdf-builder-queue-simulation-panel');
            if (target && target.parentNode) {
                target.parentNode.insertBefore(notice, target);
                
                // Auto-dismiss après 3 secondes
                setTimeout(() => {
                    notice.remove();
                }, 3000);
            }
        }
    };

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => QueueSimulationPanel.init());
    } else {
        QueueSimulationPanel.init();
    }
    
    // Exporter pour utilisation globale
    window.PdfBuilderQueueSimulationPanel = QueueSimulationPanel;
})();
