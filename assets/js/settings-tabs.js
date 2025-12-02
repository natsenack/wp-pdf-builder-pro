/**
 * Paramètres PDF Builder Pro - JavaScript principal
 * Gestion centralisée des onglets, validation, sauvegarde et aperçus
 */

'use strict';

// Configuration globale depuis PHP (via wp_localize_script)
const PDF_BUILDER_CONFIG = typeof window.PDF_BUILDER_CONFIG !== 'undefined' ? window.PDF_BUILDER_CONFIG : {};

console.log('✅ settings-tabs.js CHARGÉ');
console.log('Configuration:', PDF_BUILDER_CONFIG);

// Initialiser les fonctions de notification si pas déjà définies
if (typeof window.showSuccessNotification === 'undefined') {
    // Initialiser le système de notification centralisé
    window.simpleNotificationSystem = {
        container: null,

        init: function() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'pdf-builder-notifications';
                this.container.style.cssText = `
                    position: fixed;
                    top: 40px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 400px;
                    pointer-events: none;
                `;
                document.body.appendChild(this.container);
            }
        },

        show: function(message, type = 'info') {
            this.init();

            const notification = document.createElement('div');
            notification.className = `pdf-notification pdf-notification-${type}`;
            notification.style.cssText = `
                background: white;
                border-left: 4px solid ${this.getColor(type)};
                border-radius: 4px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                margin-bottom: 10px;
                padding: 12px 16px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
                pointer-events: auto;
                position: relative;
            `;

            notification.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="flex: 1; font-size: 14px; color: #333;">${message}</span>
                    <button class="pdf-notification-close" style="
                        background: none;
                        border: none;
                        color: #999;
                        cursor: pointer;
                        font-size: 18px;
                        line-height: 1;
                        margin-left: 10px;
                        padding: 0;
                    ">&times;</button>
                </div>
            `;

            this.container.appendChild(notification);

            // Animer l'entrée
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 10);

            // Suppression automatique
            const removeNotification = () => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            };

            // Bouton de fermeture
            notification.querySelector('.pdf-notification-close').addEventListener('click', removeNotification);

            // Suppression automatique après 5 secondes
            setTimeout(removeNotification, 5000);
        },

        getColor: function(type) {
            const colors = {
                success: '#46b450',
                error: '#dc3232',
                warning: '#f56e28',
                info: '#00a0d2'
            };
            return colors[type] || colors.info;
        },

        success: function(message) {
            this.show(message, 'success');
        },

        error: function(message) {
            this.show(message, 'error');
        },

        warning: function(message) {
            this.show(message, 'warning');
        },

        info: function(message) {
            this.show(message, 'info');
        }
    };

    window.showSuccessNotification = function(message) {
        console.log('✅ Succès:', message);
        window.simpleNotificationSystem.success(message);
    };

    window.showErrorNotification = function(message) {
        console.error('❌ Erreur:', message);
        window.simpleNotificationSystem.error(message);
    };

    window.showWarningNotification = function(message) {
        console.warn('⚠️ Avertissement:', message);
        window.simpleNotificationSystem.warning(message);
    };

    window.showInfoNotification = function(message) {
        console.log('ℹ️ Info:', message);
        window.simpleNotificationSystem.info(message);
    };
}

/**
 * CLASSE CONTRÔLEUR PRINCIPAL DES PARAMÈTRES
 * Contrôle centralisé pour toutes les fonctionnalités des paramètres
 */
class PDF_Builder_Settings_Controller {
    constructor() {
        this.config = PDF_BUILDER_CONFIG;
        this.Validator = new SettingsValidator();
        this.ShapeManager = new SettingsUI();
        this.SaveManager = new SettingsSaver();
        this.PreviewManager = new SettingsPreview();
    }

    init() {
        console.log('🎯 Initialisation du système centralisé des paramètres PDF Builder');

        // Restaurer l'onglet actif depuis localStorage
        this.restoreActiveTab();
        this.restoreActiveSubTab();

        // Initialiser tous les composants
        this.bindTabEvents();
        this.bindSubTabEvents();
        this.bindSaveEvents();
        this.initializePreviews();

        console.log('✅ Système de paramètres PDF Builder initialisé');
    }

    restoreActiveTab() {
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                console.log('📂 Onglet actif restauré depuis localStorage:', savedTab);

                // Vérifier que l'onglet existe
                const tabElement = document.querySelector(`[data-tab="${savedTab}"]`);
                const contentElement = document.getElementById(savedTab);

                if (tabElement && contentElement) {
                    // Désactiver tous les onglets
                    document.querySelectorAll('.nav-tab').forEach(t => {
                        t.classList.remove('nav-tab-active');
                    });
                    document.querySelectorAll('.tab-content').forEach(c => {
                        c.classList.remove('active');
                    });

                    // Activer l'onglet sauvegardé
                    tabElement.classList.add('nav-tab-active');
                    contentElement.classList.add('active');

                    console.log('✅ Onglet restauré avec succès:', savedTab);
                    return;
                } else {
                    console.warn('⚠️ Onglet sauvegardé non trouvé, utilisation de l\'onglet par défaut');
                }
            }
        } catch (e) {
            console.warn('⚠️ Erreur lors de la restauration de l\'onglet:', e);
        }

        // Si aucun onglet sauvegardé ou erreur, utiliser l'onglet par défaut (general)
        console.log('🔄 Utilisation de l\'onglet par défaut');
        this.switchTab('general');
    }

    restoreActiveSubTab() {
        try {
            const savedSubTab = localStorage.getItem('pdf_builder_active_sub_tab');
            if (savedSubTab) {
                console.log('📂 Sous-onglet actif restauré depuis localStorage:', savedSubTab);

                // Vérifier que le sous-onglet existe
                const subTabElement = document.querySelector(`#general-sub-tabs [data-tab="${savedSubTab}"]`);
                const subContentElement = document.getElementById(savedSubTab);

                if (subTabElement && subContentElement) {
                    // Désactiver tous les sous-onglets
                    document.querySelectorAll('#general-sub-tabs .nav-tab').forEach(t => {
                        t.classList.remove('nav-tab-active');
                    });
                    document.querySelectorAll('#general-tab-content .tab-content').forEach(c => {
                        c.classList.remove('active');
                    });

                    // Activer le sous-onglet sauvegardé
                    subTabElement.classList.add('nav-tab-active');
                    subContentElement.classList.add('active');

                    console.log('✅ Sous-onglet restauré avec succès:', savedSubTab);
                    return;
                } else {
                    console.warn('⚠️ Sous-onglet sauvegardé non trouvé, utilisation du sous-onglet par défaut');
                }
            }
        } catch (e) {
            console.warn('⚠️ Erreur lors de la restauration du sous-onglet:', e);
        }

        // Si aucun sous-onglet sauvegardé ou erreur, utiliser le sous-onglet par défaut (general-company)
        console.log('🔄 Utilisation du sous-onglet par défaut');
        this.switchSubTab('general-company');
    }

    bindTabEvents() {
        const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(tab.dataset.tab);
            });
        });
    }

    bindSubTabEvents() {
        const subTabs = document.querySelectorAll('#general-sub-tabs .nav-tab');
        subTabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchSubTab(tab.dataset.tab);
            });
        });
    }

    switchTab(tabId) {
        console.log('🔄 Changement d\'onglet vers:', tabId);

        // Update tabs
        document.querySelectorAll('#pdf-builder-tabs .nav-tab').forEach(t => {
            t.classList.remove('nav-tab-active');
        });
        const activeTab = document.querySelector(`#pdf-builder-tabs [data-tab="${tabId}"]`);
        if (activeTab) {
            activeTab.classList.add('nav-tab-active');
        }

        // Update content
        document.querySelectorAll('#pdf-builder-tab-content .tab-content').forEach(c => {
            c.classList.remove('active');
        });
        const activeContent = document.getElementById(tabId);
        if (activeContent) {
            activeContent.classList.add('active');
        }

        // Show/hide sub-tabs based on main tab
        const subTabsWrapper = document.getElementById('general-sub-tabs');
        if (subTabsWrapper) {
            if (tabId === 'general') {
                subTabsWrapper.style.display = 'block';
            } else {
                subTabsWrapper.style.display = 'none';
            }
        }

        // Sauvegarder l'onglet actif dans localStorage
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
            console.log('💾 Onglet actif sauvegardé:', tabId);
        } catch (e) {
            console.warn('⚠️ Impossible de sauvegarder l\'onglet actif:', e);
        }

        // Update save button text
        if (this.SaveManager) {
            this.SaveManager.updateButtonText(tabId);
        }
    }

    switchSubTab(subTabId) {
        console.log('🔄 Changement de sous-onglet vers:', subTabId);

        // Update sub-tabs
        document.querySelectorAll('#general-sub-tabs .nav-tab').forEach(t => {
            t.classList.remove('nav-tab-active');
        });
        const activeSubTab = document.querySelector(`#general-sub-tabs [data-tab="${subTabId}"]`);
        if (activeSubTab) {
            activeSubTab.classList.add('nav-tab-active');
        }

        // Update sub-content
        document.querySelectorAll('#general-tab-content .tab-content').forEach(c => {
            c.classList.remove('active');
        });
        const activeSubContent = document.getElementById(subTabId);
        if (activeSubContent) {
            activeSubContent.classList.add('active');
        }

        // Sauvegarder le sous-onglet actif dans localStorage
        try {
            localStorage.setItem('pdf_builder_active_sub_tab', subTabId);
        } catch (e) {
            console.warn('⚠️ Impossible de sauvegarder le sous-onglet actif:', e);
        }
    }

    bindSaveEvents() {
        this.SaveManager.bindEvents();
    }

    initializePreviews() {
        this.PreviewManager.init();
    }
}

/**
 * CLASSE VALIDATEUR DES PARAMÈTRES
 * Système de validation centralisé
 */
class SettingsValidator {
    constructor() {
        this.rules = {
            phone: {
                pattern: /^[\d\s\-\+\(\)]{10,}$/,
                message: 'Numéro de téléphone invalide'
            },
            siret: {
                pattern: /^\d{14}$/,
                message: 'Le SIRET doit contenir exactement 14 chiffres'
            },
            vat: {
                pattern: /^[A-Z]{2}[\w\d]{8,12}$/i,
                message: 'Format de numéro TVA invalide'
            }
        };
    }

    validateField(field, value) {
        const rule = this.rules[field];
        if (!rule) return true;

        return rule.pattern.test(value) || value === '';
    }

    getValidationMessage(field) {
        return this.rules[field]?.message || '';
    }

    validateTab(tabId) {
        const form = document.querySelector(`#${tabId} form`);
        if (!form) return true;

        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            if (input.name && input.value) {
                if (!this.validateField(input.name.replace('company_', ''), input.value)) {
                    this.showFieldError(input, this.getValidationMessage(input.name.replace('company_', '')));
                    isValid = false;
                } else {
                    this.clearFieldError(input);
                }
            }
        });

        return isValid;
    }

    showFieldError(input, message) {
        input.classList.add('field-error');
        let errorDiv = input.parentNode.querySelector('.field-error-msg');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error-msg';
            input.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    clearFieldError(input) {
        input.classList.remove('field-error');
        const errorDiv = input.parentNode.querySelector('.field-error-msg');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

/**
 * GESTIONNAIRE UI DES PARAMÈTRES
 * Gère toutes les mises à jour et interactions UI
 */
class SettingsUI {
    constructor() {
        this.buttons = {};
    }

    setButtonState(button, state) {
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner');

        button.disabled = state === 'loading';

        if (btnText) {
            btnText.style.display = state === 'loading' ? 'none' : 'inline';
        }
        if (spinner) {
            spinner.style.display = state === 'loading' ? 'inline-block' : 'none';
        }
    }
}

/**
 * SAUVEGARDEUR DES PARAMÈTRES
 * Fonctionnalité de sauvegarde centralisée pour tous les onglets
 */
class SettingsSaver {
    constructor() {
        this.validator = null;
        this.ui = null;
        this.saveButton = document.getElementById('pdf-builder-save-button');
    }

    setDependencies(validator, ui) {
        this.validator = validator;
        this.ui = ui;
    }

    bindEvents() {
        if (this.saveButton) {
            this.saveButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveAllSettings();
            });
        }

        // Bind individual form submits
        document.addEventListener('submit', (e) => {
            if (e.target.closest('.tab-content')) {
                e.preventDefault();
                this.saveTabSettings(e.target.closest('.tab-content').id);
            }
        });
    }

    async saveAllSettings() {
        console.log('💾 Sauvegarde de tous les paramètres...');

        if (this.ui && this.saveButton) {
            this.ui.setButtonState(this.saveButton, 'loading');
        }

        const showSuccess = window.showSuccessNotification;
        const showError = window.showErrorNotification;

        try {
            const formData = this.collectAllSettings();

            const response = await new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: PDF_BUILDER_CONFIG.ajax_url,
                    type: 'POST',
                    data: {
                        'action': 'pdf_builder_save_all_settings',
                        'nonce': PDF_BUILDER_CONFIG.nonce,
                        ...formData
                    },
                    dataType: 'json',
                    success: (data) => {
                        console.log('[AJAX Succès] Réponse reçue:', data);
                        resolve(data);
                    },
                    error: (xhr, status, error) => {
                        console.error('[AJAX Erreur]:', {status, error});
                        reject(new Error(error || 'Échec de la requête AJAX'));
                    }
                });
            });

            if (response.success) {
                console.log('✅ Tous les paramètres sauvegardés avec succès!');
                if (typeof showSuccess === 'function') {
                    showSuccess('✅ Tous les paramètres sauvegardés avec succès!');
                }
            } else {
                throw new Error(response.data?.message || 'Échec de la sauvegarde');
            }

        } catch (error) {
            console.error('Erreur de sauvegarde:', error);
            if (typeof showError === 'function') {
                showError('❌ Erreur lors de la sauvegarde des paramètres: ' + error.message);
            }
        } finally {
            if (this.ui && this.saveButton) {
                this.ui.setButtonState(this.saveButton, 'reset');
            }
        }
    }

    async saveTabSettings(tabId) {
        console.log('💾 Sauvegarde des paramètres de l\'onglet:', tabId);

        if (this.validator && !this.validator.validateTab(tabId)) {
            if (window.showErrorNotification) {
                window.showErrorNotification('❌ Veuillez corriger les erreurs de validation');
            }
            return;
        }

        const showSuccess = window.showSuccessNotification;
        const showError = window.showErrorNotification;
        const formData = this.collectTabSettings(tabId);

        try {
            const response = await new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: PDF_BUILDER_CONFIG.ajax_url,
                    type: 'POST',
                    data: {
                        'action': 'pdf_builder_save_tab_settings',
                        'tab': tabId,
                        'nonce': PDF_BUILDER_CONFIG.nonce,
                        ...formData
                    },
                    dataType: 'json',
                    success: (data) => {
                        console.log('[AJAX Succès] Réponse de sauvegarde d\'onglet:', data);
                        resolve(data);
                    },
                    error: (xhr, status, error) => {
                        console.error('[AJAX Erreur] Échec de la sauvegarde d\'onglet:', {status, error});
                        reject(new Error(error || 'Échec de la requête AJAX'));
                    }
                });
            });

            if (response.success && typeof showSuccess === 'function') {
                showSuccess(`✅ Paramètres ${tabId.charAt(0).toUpperCase() + tabId.slice(1)} sauvegardés!`);
            }

        } catch (error) {
            console.error('Erreur de sauvegarde d\'onglet:', error);
            if (typeof showError === 'function') {
                showError('❌ Erreur lors de la sauvegarde des paramètres');
            }
        }
    }

    collectAllSettings() {
        const formData = {};

        document.querySelectorAll('.tab-content input, .tab-content select, .tab-content textarea').forEach(field => {
            if (field.name) {
                if (field.type === 'checkbox') {
                    formData[field.name] = field.checked ? '1' : '0';
                } else {
                    formData[field.name] = field.value;
                }
            }
        });

        return formData;
    }

    collectTabSettings(tabId) {
        const formData = {};
        const tabContent = document.getElementById(tabId);

        if (tabContent) {
            const fields = tabContent.querySelectorAll('input, select, textarea');
            fields.forEach(field => {
                if (field.name) {
                    if (field.type === 'checkbox') {
                        formData[field.name] = field.checked ? '1' : '0';
                    } else {
                        formData[field.name] = field.value;
                    }
                }
            });
        }

        return formData;
    }

    updateButtonText(tabId) {
        const tabNames = {
            'general': 'Enregistrer Général',
            'licence': 'Enregistrer Licence',
            'systeme': 'Enregistrer Système',
            'acces': 'Enregistrer Accès',
            'securite': 'Enregistrer Sécurité',
            'pdf': 'Enregistrer PDF',
            'contenu': 'Enregistrer Contenu',
            'templates': 'Enregistrer Modèles',
            'developpeur': 'Enregistrer Développeur'
        };

        const btnText = this.saveButton?.querySelector('.btn-text');
        if (btnText) {
            btnText.textContent = tabNames[tabId] || 'Enregistrer Tout';
        }
    }
}

/**
 * GESTIONNAIRE D'APERÇU DES PARAMÈTRES
 * Gère les aperçus en direct pour les paramètres
 */
class SettingsPreview {
    constructor() {
        this.previews = {};
    }

    init() {
        // Aperçu téléphone
        this.bindPreview('company_phone_manual', '.company-phone-preview', (val) => val);

        // Aperçu SIRET
        this.bindPreview('company_siret', '.company-siret-preview', (val) => val);

        // Aperçu TVA
        this.bindPreview('company_vat', '.company-vat-preview', (val) => val);

        // Aperçu RCS
        this.bindPreview('company_rcs', '.company-rcs-preview', (val) => val);

        // Aperçu capital
        this.bindPreview('company_capital', '.company-capital-preview', (val) => val ? val + ' €' : '');
    }

    bindPreview(fieldName, selector, formatter = (val) => val) {
        const input = document.querySelector(`input[name="${fieldName}"]`);
        const preview = document.querySelector(selector);

        if (!input || !preview) return;

        preview.textContent = formatter(input.value);

        input.addEventListener('input', () => {
            preview.textContent = formatter(input.value);
        });
    }
}

// Initialize the system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation du système PDF Builder Settings');

    const settingsController = new PDF_Builder_Settings_Controller();
    settingsController.init();

    // Connect components
    settingsController.SaveManager.setDependencies(
        settingsController.Validator,
        settingsController.ShapeManager
    );

    console.log('✅ Système PDF Builder Settings initialisé');
});
