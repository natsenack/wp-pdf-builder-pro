/**
 * PDF Builder Pro - Notifications JavaScript
 * Système de notifications frontend moderne et responsive
 * Version: 1.0.0
 * Updated: 2025-11-29
 */

console.log('[PDF Builder] NOTIFICATIONS.JS - Script loaded and starting execution');

try {
    console.log('[PDF Builder] NOTIFICATIONS.JS - Script loaded and executing');

    (function($) {
        'use strict';

    // Fonction de debug conditionnel

    // LOG INCONDITIONNEL - toujours affiché

    /**
     * Classe principale pour la gestion des notifications frontend
     */
    class PDF_Builder_Notifications {

        constructor() {
            // Wait for localized data if not available yet
            this.settings = window.pdfBuilderNotifications?.settings || { enabled: true, position: 'top-right', duration: 5000 };
            this.strings = window.pdfBuilderNotifications?.strings || {};
            this.ajaxUrl = window.pdfBuilderNotifications?.ajax_url || '';
            this.nonce = window.pdfBuilderNotifications?.nonce || '';
            this.notifications = [];
            this.container = null;
            this.initialized = false;

            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }
            this.init();
        }

        /**
         * Initialisation du système
         */
        init() {
            if (this.initialized) return;

            this.createContainer();
            this.bindEvents();
            this.clear(); // Clear any existing notifications on init
            this.initialized = true;
        }

        /**
         * Créer le conteneur principal des notifications
         */
        createContainer() {
            if (this.container) return;

            this.container = document.createElement('div');
            this.container.className = 'pdf-builder-notifications-container';
            this.container.setAttribute('data-position', this.settings.position || 'top-right');
            this.container.setAttribute('aria-live', 'polite');
            this.container.setAttribute('aria-atomic', 'false');

            document.body.appendChild(this.container);

            // Appliquer les styles de positionnement
            this.updateContainerPosition();
        }

        /**
         * Mettre à jour la position du conteneur
         */
        updateContainerPosition() {
            if (!this.container) return;

            const position = this.settings.position || 'top-right';
            this.container.setAttribute('data-position', position);

            // Styles CSS pour le positionnement
            const styles = {
                'top-left': {
                    top: '35px',
                    left: '20px',
                    right: 'auto',
                    bottom: 'auto'
                },
                'top-right': {
                    top: '35px',
                    right: '20px',
                    left: 'auto',
                    bottom: 'auto'
                },
                'bottom-left': {
                    bottom: '20px',
                    left: '20px',
                    top: 'auto',
                    right: 'auto'
                },
                'bottom-right': {
                    bottom: '20px',
                    right: '20px',
                    top: 'auto',
                    left: 'auto'
                },
                'top-center': {
                    top: '20px',
                    left: '50%',
                    right: 'auto',
                    bottom: 'auto',
                    transform: 'translateX(-50%)'
                },
                'bottom-center': {
                    bottom: '20px',
                    left: '50%',
                    right: 'auto',
                    top: 'auto',
                    transform: 'translateX(-50%)'
                }
            };

            const style = styles[position] || styles['top-right'];
            Object.assign(this.container.style, style);
        }

        /**
         * Lier les événements
         */
        bindEvents() {
            // Délégation d'événements pour les boutons de fermeture
            document.addEventListener('click', (e) => {
                if (e.target.closest('.notification-close')) {
                    e.preventDefault();
                    const notification = e.target.closest('.pdf-builder-notification');
                    if (notification) {
                        this.dismiss(notification);
                    }
                }

                // Bouton "Tout fermer"
                if (e.target.closest('.notifications-dismiss-all')) {
                    e.preventDefault();
                    this.dismissAll();
                }
            });

            // Gestion du clavier (Échap pour fermer)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.dismissAll();
                }
            });

            // Gestion du redimensionnement de la fenêtre
            window.addEventListener('resize', () => {
                this.updateContainerPosition();
            });
        }

        /**
         * Afficher une notification
         */
        show(message, type = 'info', options = {}) {
            if (window.pdfBuilderDebugSettings?.javascript) {
                // Debug code removed
            }

            // Notifications enabled by default if not explicitly disabled
            if (this.settings.enabled === false) {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    
                }
                return;
            }

            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }

            const notificationOptions = Object.assign({
                message: message,
                type: type,
                duration: this.settings.duration || 5000,
                dismissible: true,
                persistent: false,
                sound: this.settings.sound_enabled || false
            }, options);

            const notification = this.createNotificationElement(notificationOptions);
            this.addToContainer(notification);
            this.playSoundIfEnabled(notificationOptions.sound);

            // Auto-dismiss si durée définie et pas persistent
            if (notificationOptions.duration > 0 && !notificationOptions.persistent) {
                this.scheduleAutoDismiss(notification, notificationOptions.duration);
            }

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.add('visible');
            }, 10);

            return notification;
        }

        /**
         * Créer l'élément DOM d'une notification
         */
        createNotificationElement(options) {
            const typeConfig = this.settings.types?.[options.type] || this.settings.types?.info || {
                icon: 'ℹ️',
                color: '#17a2b8',
                bg: '#d1ecf1'
            };

            const notification = document.createElement('div');
            notification.className = `pdf-builder-notification pdf-builder-notification-${options.type} pdf-builder-notification-${this.settings.animation || 'slide'}`;
            notification.setAttribute('role', 'alert');
            notification.setAttribute('aria-live', 'assertive');

            if (options.dismissible) {
                notification.classList.add('dismissible');
            }

            // Styles inline pour les couleurs
            notification.style.backgroundColor = typeConfig.bg;
            notification.style.color = typeConfig.color;
            notification.style.borderLeftColor = typeConfig.color;

            notification.innerHTML = `
                <div class="notification-content">
                    <span class="notification-icon">${typeConfig.icon}</span>
                    <span class="notification-message">${this.escapeHtml(options.message)}</span>
                    ${options.dismissible ? `
                        <button class="notification-close" aria-label="${this.strings.close || 'Fermer'}">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    ` : ''}
                </div>
                ${options.duration > 0 && !options.persistent ? `
                    <div class="notification-progress-bar">
                        <div class="notification-progress" style="background-color: ${typeConfig.color}"></div>
                    </div>
                ` : ''}
            `;

            // Stocker les options dans l'élément
            notification._notificationOptions = options;

            return notification;
        }

        /**
         * Ajouter une notification au conteneur
         */
        addToContainer(notification) {
            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }

            if (!this.container) {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    // console.error('PDF Builder Notifications: No container found!');
                }
                return;
            }

            // Limiter le nombre de notifications
            const maxNotifications = this.settings.max_notifications || 5;
            const existingNotifications = this.container.querySelectorAll('.pdf-builder-notification');

            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }

            if (existingNotifications.length >= maxNotifications) {
                // Supprimer la plus ancienne
                const oldest = existingNotifications[0];
                this.dismiss(oldest, false);
            }

            // Ajouter la nouvelle notification
            this.container.appendChild(notification);
            this.notifications.push(notification);

            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }

            // Ajouter le bouton "Tout fermer" si plusieurs notifications
            this.updateDismissAllButton();
        }

        /**
         * Mettre à jour le bouton "Tout fermer" - Désactivé
         */
        updateDismissAllButton() {
            // Fonctionnalité "Tout fermer" désactivée
            const existingButton = this.container.querySelector('.notifications-dismiss-all');
            if (existingButton) {
                existingButton.remove();
            }
        }

        /**
         * Programmer la fermeture automatique
         */
        scheduleAutoDismiss(notification, duration) {
            const progressBar = notification.querySelector('.notification-progress');
            if (progressBar) {
                progressBar.style.transition = `width ${duration}ms linear`;
                progressBar.style.width = '0%';
            }

            notification._dismissTimeout = setTimeout(() => {
                this.dismiss(notification);
            }, duration);
        }

        /**
         * Fermer une notification
         */
        dismiss(notification, animate = true) {
            if (!notification || notification.classList.contains('dismissing')) return;

            notification.classList.add('dismissing');

            if (animate) {
                // Animation de sortie
                notification.classList.remove('visible');
                notification.classList.add('dismissing');

                setTimeout(() => {
                    this.removeNotification(notification);
                }, 300);
            } else {
                this.removeNotification(notification);
            }
        }

        /**
         * Supprimer une notification du DOM
         */
        removeNotification(notification) {
            if (notification && notification.parentNode) {
                notification.remove();
            }

            // Retirer de la liste
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }

            // Nettoyer le timeout
            if (notification._dismissTimeout) {
                clearTimeout(notification._dismissTimeout);
            }

            // Mettre à jour le bouton "Tout fermer"
            this.updateDismissAllButton();
        }

        /**
         * Fermer toutes les notifications
         */
        dismissAll() {
            const notifications = Array.from(this.container.querySelectorAll('.pdf-builder-notification:not(.dismissing)'));
            notifications.forEach(notification => {
                this.dismiss(notification);
            });
        }

        /**
         * Jouer un son si activé
         */
        playSoundIfEnabled(enabled) {
            if (!enabled || !this.settings.sound_enabled) return;

            // Créer un son simple (peep)
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);

                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch {
                // Silencieux en cas d'erreur
            }
        }

        /**
         * Échapper le HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Méthodes publiques pour afficher différents types de notifications
         */
        success(message, options = {}) {
            return this.show(message, 'success', options);
        }

        error(message, options = {}) {
            return this.show(message, 'error', options);
        }

        warning(message, options = {}) {
            return this.show(message, 'warning', options);
        }

        info(message, options = {}) {
            return this.show(message, 'info', options);
        }

        /**
         * Afficher une notification via AJAX
         */
        showAjax(message, type = 'info', duration = null) {
            if (!this.ajaxUrl || !this.nonce) return;

            const data = {
                action: 'pdf_builder_show_notification',
                nonce: this.nonce,
                message: message,
                type: type,
                duration: duration || this.settings.duration
            };

            $.post(this.ajaxUrl, data)
                .done((response) => {
                    if (response.success) {
                        // Notification sent successfully
                    }
                })
                .fail((error) => {
                    // if (window.pdfBuilderDebugSettings?.javascript) console.error('Failed to send AJAX notification:', error);
                });
        }

        /**
         * Mettre à jour les paramètres
         */
        updateSettings(newSettings) {
            this.settings = Object.assign(this.settings, newSettings);
            this.updateContainerPosition();
        }

        /**
         * Obtenir les paramètres actuels
         */
        getSettings() {
            return this.settings;
        }

        /**
         * Vider toutes les notifications
         */
        clear() {
            this.dismissAll();
        }

        /**
         * Détruire l'instance
         */
        destroy() {
            this.clear();
            if (this.container) {
                this.container.remove();
                this.container = null;
            }
            this.initialized = false;
        }
    }

    // Instance globale
    window.PDF_Builder_Notifications = PDF_Builder_Notifications;

    // Fonctions globales pour un accès facile - définies immédiatement
    window.pdfBuilderNotify = {
        success: function(message, options) {
            if (!window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
            }
            return window.pdfBuilderNotificationsInstance.success(message, options);
        },
        error: function(message, options) {
            if (!window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
            }
            return window.pdfBuilderNotificationsInstance.error(message, options);
        },
        warning: function(message, options) {
            if (!window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
            }
            return window.pdfBuilderNotificationsInstance.warning(message, options);
        },
        info: function(message, options) {
            if (!window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
            }
            return window.pdfBuilderNotificationsInstance.info(message, options);
        },
        showAjax: function(message, type, duration) {
            if (!window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
            }
            return window.pdfBuilderNotificationsInstance.showAjax(message, type, duration);
        },
        clear: function() {
            if (window.pdfBuilderNotificationsInstance) {
                window.pdfBuilderNotificationsInstance.clear();
            }
        }
    };

    // Alias pour la compatibilité - définis immédiatement
    window.showSuccessNotification = function(message, options) {

        if (window.pdfBuilderNotificationsInstance) {
            return window.pdfBuilderNotificationsInstance.success(message, options);
        } else {
            // console.warn('Global: Instance not ready, initializing...');
            // Initialize if not ready - ensure data is available
            if (!window.pdfBuilderNotificationsInstance) {
                // Wait for DOM ready if not ready yet
                if (document.readyState === 'loading') {
                    $(document).ready(function() {
                        if (!window.pdfBuilderNotificationsInstance) {
                            window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                        }
                        window.pdfBuilderNotificationsInstance.success(message, options);
                    });
                    return; // Don't return anything, async call
                } else {
                    window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                }
            }
            return window.pdfBuilderNotificationsInstance.success(message, options);
        }
    };
    window.showErrorNotification = function(message, options) {
        // console.trace('showErrorNotification called with:', message);
        if (window.pdfBuilderNotificationsInstance) {
            return window.pdfBuilderNotificationsInstance.error(message, options);
        } else {
            // console.warn('PDF Builder Notifications: Instance not ready, initializing...');
            // Initialize if not ready - ensure data is available
            if (!window.pdfBuilderNotificationsInstance) {
                // Wait for DOM ready if not ready yet
                if (document.readyState === 'loading') {
                    $(document).ready(function() {
                        if (!window.pdfBuilderNotificationsInstance) {
                            window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                        }
                        window.pdfBuilderNotificationsInstance.error(message, options);
                    });
                    return; // Don't return anything, async call
                } else {
                    window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                }
            }
            return window.pdfBuilderNotificationsInstance.error(message, options);
        }
    };
    window.showWarningNotification = function(message, options) {
        // console.trace('showWarningNotification called with:', message);
        if (window.pdfBuilderNotificationsInstance) {
            return window.pdfBuilderNotificationsInstance.warning(message, options);
        } else {
            // console.warn('PDF Builder Notifications: Instance not ready, initializing...');
            // Initialize if not ready - ensure data is available
            if (!window.pdfBuilderNotificationsInstance) {
                // Wait for DOM ready if not ready yet
                if (document.readyState === 'loading') {
                    $(document).ready(function() {
                        if (!window.pdfBuilderNotificationsInstance) {
                            window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                        }
                        window.pdfBuilderNotificationsInstance.warning(message, options);
                    });
                    return; // Don't return anything, async call
                } else {
                    window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                }
            }
            return window.pdfBuilderNotificationsInstance.warning(message, options);
        }
    };
    window.showInfoNotification = function(message, options) {
        // console.trace('showInfoNotification called with:', message);
        if (window.pdfBuilderNotificationsInstance) {
            return window.pdfBuilderNotificationsInstance.info(message, options);
        } else {
            // console.warn('PDF Builder Notifications: Instance not ready, initializing...');
            // Initialize if not ready - ensure data is available
            if (!window.pdfBuilderNotificationsInstance) {
                // Wait for DOM ready if not ready yet
                if (document.readyState === 'loading') {
                    $(document).ready(function() {
                        if (!window.pdfBuilderNotificationsInstance) {
                            window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                        }
                        window.pdfBuilderNotificationsInstance.info(message, options);
                    });
                    return; // Don't return anything, async call
                } else {
                    window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
                }
            }
            return window.pdfBuilderNotificationsInstance.info(message, options);
        }
    };

    console.log('[PDF Builder] NOTIFICATIONS.JS - Global functions defined:');
    console.log('[PDF Builder] NOTIFICATIONS.JS - showSuccessNotification:', typeof window.showSuccessNotification);
    console.log('[PDF Builder] NOTIFICATIONS.JS - showErrorNotification:', typeof window.showErrorNotification);

    // Initialisation automatique
    $(document).ready(function() {
        window.pdfBuilderNotificationsInstance = new PDF_Builder_Notifications();
        console.log('[PDF Builder] NOTIFICATIONS.JS - Instance initialized in document ready');

        // Filtrer les notifications WordPress pour n'afficher que celles liées au PDF Builder
        filterWordPressNotifications();
    });

    /**
     * Filtre les notifications WordPress pour n'afficher que celles liées au PDF Builder
     */
    function filterWordPressNotifications() {
        console.log('[PDF Builder] NOTIFICATIONS.JS - Filtering WordPress notifications...');

        // Fonction pour marquer les notifications pertinentes
        function markRelevantNotifications() {
            // Sélecteurs étendus pour TOUS les types de notifications
            $('.notice, .notice-error, .notice-success, .notice-warning, .notice-info, .updated, .error, .update-nag, .update-message, .settings-error, .settings-updated, .message, .admin-notice, .plugin-notice, .theme-notice, .core-notice, .components-notice, .wp-notice, .fade, .auto-fold-up, .is-dismissible').each(function() {
                var $notice = $(this);

                // EXCLURE la notification de limite de templates du filtrage
                if ($notice.attr('id') === 'pdf-builder-template-limit-notice' || $notice.hasClass('pdf-builder-template-limit-notice')) {
                    console.log('[PDF Builder] NOTIFICATIONS.JS - Skipping template limit notice (always visible)');
                    return; // Ne pas traiter cette notification
                }

                var noticeText = $notice.text().toLowerCase();
                var noticeHtml = $notice.html().toLowerCase();
                var noticeClasses = $notice.attr('class') || '';

                // Liste étendue de mots-clés liés au PDF Builder
                var pdfBuilderKeywords = [
                    'pdf builder', 'pdf-builder', 'pdf_builder', 'wp-pdf-builder',
                    'template', 'license', 'licence', 'pdf', 'builder',
                    'canvas', 'unified', 'pro', 'premium', 'upgrade',
                    'duplicate', 'settings', 'configuration', 'preview',
                    'export', 'import', 'migration', 'backup'
                ];

                // Mots-clés pour identifier les notifications NON pertinentes (autres plugins)
                var nonRelevantKeywords = [
                    'elementor', 'element or', 'wpforms', 'contact form',
                    'woocommerce', 'woo commerce', 'wp mail', 'mailchimp',
                    'yoast', 'seo', 'google analytics', 'jetpack',
                    'akismet', 'backup', 'security', 'performance',
                    'cache', 'optimization', 'speed', 'gdpr', 'cookie',
                    'maintenance', 'update core', 'wordpress update',
                    'plugin update', 'theme update', 'database',
                    'server', 'hosting', 'ssl', 'https', 'admin bar',
                    'dashboard', 'widget', 'menu', 'customizer',
                    'editor', 'gutenberg', 'block', 'media library',
                    'comments', 'users', 'roles', 'permissions',
                    'multisite', 'network', 'translation', 'language',
                    'debug', 'error log', 'php', 'mysql', 'server error'
                ];

                // Vérifier si la notification contient des mots-clés liés au PDF Builder
                var isPdfBuilderRelated = false;

                // Vérification des mots-clés dans le texte
                for (var i = 0; i < pdfBuilderKeywords.length; i++) {
                    if (noticeText.indexOf(pdfBuilderKeywords[i]) !== -1 ||
                        noticeHtml.indexOf(pdfBuilderKeywords[i]) !== -1) {
                        isPdfBuilderRelated = true;
                        break;
                    }
                }

                // Vérifier si c'est une notification NON pertinente d'autres plugins
                var isNonRelevant = false;
                if (!isPdfBuilderRelated) {
                    for (var j = 0; j < nonRelevantKeywords.length; j++) {
                        if (noticeText.indexOf(nonRelevantKeywords[j]) !== -1 ||
                            noticeHtml.indexOf(nonRelevantKeywords[j]) !== -1 ||
                            noticeClasses.indexOf(nonRelevantKeywords[j].replace(/\s+/g, '-')) !== -1) {
                            isNonRelevant = true;
                            break;
                        }
                    }
                }

                // Vérification des classes existantes
                if (!isPdfBuilderRelated && !isNonRelevant) {
                    isPdfBuilderRelated = (
                        $notice.hasClass('pdf-builder-notice') ||
                        $notice.hasClass('pdf-builder-related') ||
                        $notice.hasClass('pdf-builder-critical') ||
                        noticeClasses.indexOf('pdf-builder') !== -1
                    );
                }

                // Vérification des liens
                if (!isPdfBuilderRelated && !isNonRelevant) {
                    isPdfBuilderRelated = (
                        $notice.find('[href*="pdf-builder"]').length > 0 ||
                        $notice.find('[href*="PDF Builder"]').length > 0 ||
                        $notice.find('[href*="pdf_builder"]').length > 0 ||
                        $notice.find('[href*="wp-pdf-builder"]').length > 0 ||
                        $notice.find('[href*="template"]').length > 0 ||
                        $notice.find('[href*="license"]').length > 0
                    );
                }

                // Vérification des attributs data
                if (!isPdfBuilderRelated && !isNonRelevant) {
                    isPdfBuilderRelated = (
                        $notice.attr('data-plugin') === 'pdf-builder' ||
                        $notice.attr('data-source') === 'pdf-builder' ||
                        $notice.attr('data-type') === 'pdf-builder'
                    );
                }

                if (isPdfBuilderRelated) {
                    $notice.addClass('pdf-builder-notice pdf-builder-related');
                    // S'assurer qu'elle n'a pas la classe de masquage
                    $notice.removeClass('pdf-builder-hide');
                    console.log('[PDF Builder] NOTIFICATIONS.JS - Showing PDF Builder related notice:', noticeText.substring(0, 100) + '...');
                } else {
                    // Marquer explicitement pour le masquage forcé
                    $notice.addClass('pdf-builder-hide');
                    console.log('[PDF Builder] NOTIFICATIONS.JS - Hiding non-PDF Builder notice:', noticeText.substring(0, 100) + '...');
                }
            });
        }

        // Appliquer immédiatement le filtrage
        markRelevantNotifications();

        // Observer les changements dans le DOM pour les nouvelles notifications
        var observer = new MutationObserver(function(mutations) {
            var shouldUpdate = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Vérifier si le noeud ajouté est une notification ou contient des notifications
                            if ($(node).is('.notice, .updated, .error, .update-nag, .update-message, .settings-error, .settings-updated, .message, .admin-notice, .plugin-notice, .theme-notice, .core-notice, .components-notice, .wp-notice, .fade, .auto-fold-up, .is-dismissible') ||
                                $(node).find('.notice, .updated, .error, .update-nag, .update-message, .settings-error, .settings-updated, .message, .admin-notice, .plugin-notice, .theme-notice, .core-notice, .components-notice, .wp-notice, .fade, .auto-fold-up, .is-dismissible').length > 0) {
                                shouldUpdate = true;
                            }
                        }
                    });
                }
                // Aussi vérifier les changements d'attributs (comme l'ajout de classes)
                else if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if ($(mutation.target).is('.notice, .updated, .error, .update-nag, .update-message, .settings-error, .settings-updated, .message, .admin-notice, .plugin-notice, .theme-notice, .core-notice, .components-notice, .wp-notice, .fade, .auto-fold-up, .is-dismissible')) {
                        shouldUpdate = true;
                    }
                }
            });

            if (shouldUpdate) {
                // Petit délai pour s'assurer que le contenu est complètement chargé
                setTimeout(markRelevantNotifications, 10);
            }
        });

        // Observer le corps de la page pour les nouvelles notifications
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });

        // Observer spécifiquement les zones communes où apparaissent les notifications
        var commonAreas = [
            document.getElementById('wpbody-content'),
            document.getElementById('wp-admin-bar'),
            document.getElementById('wpcontent'),
            document.querySelector('.wrap'),
            document.querySelector('#wpbody')
        ];

        commonAreas.forEach(function(area) {
            if (area) {
                observer.observe(area, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        });
    }

    /**
     * Gestion de la fermeture des notifications PDF Builder
     */
    function initNoticeDismissal() {
        console.log('[PDF Builder] NOTIFICATIONS.JS - Initializing notice dismissal...');

        // Vérifier si la notification a été fermée précédemment
        var dismissedNotices = localStorage.getItem('pdf_builder_dismissed_notices');
        if (dismissedNotices) {
            dismissedNotices = JSON.parse(dismissedNotices);
            if (dismissedNotices['template_limit']) {
                $('#pdf-builder-template-limit-notice').addClass('pdf-builder-dismissed');
                console.log('[PDF Builder] NOTIFICATIONS.JS - Template limit notice was previously dismissed');
            }
        }

        // Gérer le clic sur le bouton de fermeture
        $(document).on('click', '.pdf-builder-dismiss-btn', function(e) {
            console.log('[PDF Builder] NOTIFICATIONS.JS - Dismiss button clicked');
            e.preventDefault();
            e.stopPropagation();

            var $notice = $(this).closest('.pdf-builder-template-limit-notice');
            console.log('[PDF Builder] NOTIFICATIONS.JS - Found notice element:', $notice.length, $notice);

            if ($notice.length === 0) {
                console.error('[PDF Builder] NOTIFICATIONS.JS - Could not find parent notice element');
                return;
            }

            var noticeId = 'template_limit';

            // Masquer la notification avec une animation
            $notice.slideUp(300, function() {
                $notice.addClass('pdf-builder-dismissed');
                console.log('[PDF Builder] NOTIFICATIONS.JS - Notice hidden with animation');

                // Sauvegarder l'état dans localStorage
                var dismissedNotices = localStorage.getItem('pdf_builder_dismissed_notices') || '{}';
                dismissedNotices = JSON.parse(dismissedNotices);
                dismissedNotices[noticeId] = true;
                localStorage.setItem('pdf_builder_dismissed_notices', JSON.stringify(dismissedNotices));

                console.log('[PDF Builder] NOTIFICATIONS.JS - Template limit notice dismissed and saved to localStorage');
            });
        });

        // Vérifier que le bouton existe
        setTimeout(function() {
            var $dismissBtn = $('.pdf-builder-dismiss-btn');
            console.log('[PDF Builder] NOTIFICATIONS.JS - Dismiss button found:', $dismissBtn.length, $dismissBtn);
        }, 1000);

        console.log('[PDF Builder] NOTIFICATIONS.JS - Notice dismissal initialized');
    }

    // Initialiser la gestion des fermetures de notifications
    // Attendre que jQuery soit disponible
    if (typeof jQuery !== 'undefined') {
        initNoticeDismissal();
    } else {
        // Si jQuery n'est pas encore disponible, attendre qu'il le soit
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                initNoticeDismissal();
            }
        }, 100);
    }

    /**
     * Fonction pour réafficher une notification fermée (utile pour les tests ou reset)
     */
    window.pdfBuilderResetDismissedNotice = function(noticeId) {
        var dismissedNotices = localStorage.getItem('pdf_builder_dismissed_notices');
        if (dismissedNotices) {
            dismissedNotices = JSON.parse(dismissedNotices);
            delete dismissedNotices[noticeId];
            localStorage.setItem('pdf_builder_dismissed_notices', JSON.stringify(dismissedNotices));

            // Réafficher la notification
            if (noticeId === 'template_limit') {
                $('#pdf-builder-template-limit-notice').removeClass('pdf-builder-dismissed').show();
            }

            console.log('[PDF Builder] NOTIFICATIONS.JS - Notice reset:', noticeId);
        }
    };

// Force redeploy marker

