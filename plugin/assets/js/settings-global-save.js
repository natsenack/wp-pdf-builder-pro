/**
 * PDF Builder Settings Global Save System
 * Handles global save functionality for all settings tabs
 */

(function($) {
    'use strict';

    // Global settings save system
    window.PDFBuilderSettingsSaver = {

        /**
         * Collect all settings from a specific tab
         * @param {string} tabId - The tab ID to collect settings from
         * @returns {Object} Collected settings data
         */
        collectTabSettings: function(tabId) {
            const settings = {};
            const tabContainer = document.getElementById('pdf-builder-tab-' + tabId);

            if (!tabContainer) {
                console.warn('[PDF Builder] Tab container not found:', tabId);
                return settings;
            }

            // Find all input, select, and textarea elements within the tab
            const inputs = tabContainer.querySelectorAll('input, select, textarea');

            inputs.forEach(function(element) {
                const name = element.name;
                if (!name) return;

                let value;

                // Handle different input types
                switch (element.type) {
                    case 'checkbox':
                        value = element.checked ? '1' : '0';
                        break;
                    case 'radio':
                        if (element.checked) {
                            value = element.value;
                        } else {
                            return; // Skip unchecked radio buttons
                        }
                        break;
                    case 'number':
                    case 'range':
                        value = element.value ? parseFloat(element.value) : '';
                        break;
                    default:
                        value = element.value || '';
                }

                // Remove 'pdf_builder_' prefix if present and add tab prefix
                const cleanName = name.replace(/^pdf_builder_/, '');
                const prefixedName = tabId + '_' + cleanName;

                settings[prefixedName] = value;
            });

            return settings;
        },

        /**
         * Collect all settings from all tabs
         * @returns {Object} All collected settings data
         */
        collectAllSettings: function() {
            const allSettings = {};
            const tabs = ['general', 'appearance', 'security', 'advanced'];

            tabs.forEach(function(tabId) {
                const tabSettings = this.collectTabSettings(tabId);
                Object.assign(allSettings, tabSettings);
            }.bind(this));

            return allSettings;
        },

        /**
         * Save settings for a specific tab
         * @param {string} tabId - The tab ID to save
         * @param {Object} settings - Settings data to save
         * @returns {Promise} Promise that resolves with the AJAX response
         */
        saveTabSettings: function(tabId, settings) {
            return new Promise(function(resolve, reject) {
                const nonce = window.pdfBuilderAjax ? window.pdfBuilderAjax.nonce : '';

                $.ajax({
                    url: window.ajaxurl || window.pdfBuilderAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_save_' + tabId,
                        _wpnonce: nonce,
                        ...settings
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(new Error(response.data || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error('AJAX Error: ' + error));
                    }
                });
            });
        },

        /**
         * Save all settings globally across all tabs
         * @returns {Promise} Promise that resolves when all saves are complete
         */
        saveAllSettingsGlobally: function() {
            const self = this;
            const tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
            const promises = [];

            // Show loading state
            this.showGlobalSaveLoading();

            tabs.forEach(function(tabId) {
                const settings = self.collectTabSettings(tabId);
                if (Object.keys(settings).length > 0) {
                    promises.push(self.saveTabSettings(tabId, settings));
                }
            });

            return Promise.all(promises)
                .then(function(results) {
                    self.hideGlobalSaveLoading();
                    self.showGlobalSaveSuccess();
                    return results;
                })
                .catch(function(error) {
                    self.hideGlobalSaveLoading();
                    self.showGlobalSaveError(error.message);
                    throw error;
                });
        },

        /**
         * Show global save loading state
         */
        showGlobalSaveLoading: function() {
            const button = document.getElementById('pdf-builder-global-save-btn');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner is-active"></span> Sauvegarde...';
            }
        },

        /**
         * Hide global save loading state
         */
        hideGlobalSaveLoading: function() {
            const button = document.getElementById('pdf-builder-global-save-btn');
            if (button) {
                button.disabled = false;
                button.innerHTML = 'Sauvegarder Tout';
            }
        },

        /**
         * Show global save success message
         */
        showGlobalSaveSuccess: function() {
            this.showGlobalSaveMessage('Paramètres sauvegardés avec succès!', 'success');
        },

        /**
         * Show global save error message
         */
        showGlobalSaveError: function(message) {
            this.showGlobalSaveMessage('Erreur lors de la sauvegarde: ' + message, 'error');
        },

        /**
         * Show global save message
         */
        showGlobalSaveMessage: function(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.pdf-builder-global-save-message');
            existingMessages.forEach(function(msg) {
                msg.remove();
            });

            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = 'pdf-builder-global-save-message notice notice-' + type + ' is-dismissible';
            messageDiv.innerHTML = '<p>' + message + '</p>';

            // Add close button functionality
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'notice-dismiss';
            closeBtn.addEventListener('click', function() {
                messageDiv.remove();
            });
            messageDiv.appendChild(closeBtn);

            // Insert after the global save button
            const button = document.getElementById('pdf-builder-global-save-btn');
            if (button && button.parentNode) {
                button.parentNode.insertBefore(messageDiv, button.nextSibling);
            }

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 5000);
            }
        },

        /**
         * Initialize the global save system
         */
        init: function() {
            const self = this;

            // Bind global save button
            const globalSaveBtn = document.getElementById('pdf-builder-global-save-btn');
            if (globalSaveBtn) {
                globalSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.saveAllSettingsGlobally().catch(function(error) {
                        console.error('[PDF Builder] Global save failed:', error);
                    });
                });
            }

            // Bind individual tab save buttons (if they exist)
            const tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
            tabs.forEach(function(tabId) {
                const tabSaveBtn = document.getElementById('pdf-builder-save-' + tabId + '-btn');
                if (tabSaveBtn) {
                    tabSaveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const settings = self.collectTabSettings(tabId);
                        self.saveTabSettings(tabId, settings)
                            .then(function() {
                                self.showTabSaveSuccess(tabId);
                            })
                            .catch(function(error) {
                                self.showTabSaveError(tabId, error.message);
                            });
                    });
                }
            });
        },

        /**
         * Show tab-specific save success
         */
        showTabSaveSuccess: function(tabId) {
            this.showTabSaveMessage(tabId, 'Paramètres sauvegardés!', 'success');
        },

        /**
         * Show tab-specific save error
         */
        showTabSaveError: function(tabId, message) {
            this.showTabSaveMessage(tabId, 'Erreur: ' + message, 'error');
        },

        /**
         * Show tab-specific save message
         */
        showTabSaveMessage: function(tabId, message, type) {
            const tabContainer = document.getElementById('pdf-builder-tab-' + tabId);
            if (!tabContainer) return;

            // Remove existing messages in this tab
            const existingMessages = tabContainer.querySelectorAll('.pdf-builder-tab-save-message');
            existingMessages.forEach(function(msg) {
                msg.remove();
            });

            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = 'pdf-builder-tab-save-message notice notice-' + type;
            messageDiv.innerHTML = '<p>' + message + '</p>';

            // Insert at the top of the tab
            const firstElement = tabContainer.firstElementChild;
            tabContainer.insertBefore(messageDiv, firstElement);

            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(function() {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 3000);
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        window.PDFBuilderSettingsSaver.init();
    });

})(jQuery);