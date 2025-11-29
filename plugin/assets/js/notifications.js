/**
 * PDF Builder Pro - Système de Notifications Frontend
 * Gestion centralisée des notifications toast
 */

class PDFBuilderNotifications {
    constructor() {
        this.container = null;
        this.notifications = new Map();
        this.init();
    }

    init() {
        this.createContainer();
        this.bindEvents();
    }

    createContainer() {
        // Créer le conteneur principal s'il n'existe pas
        if (!document.getElementById('pdf-notifications-container')) {
            this.container = document.createElement('div');
            this.container.id = 'pdf-notifications-container';
            this.container.className = 'pdf-notifications-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('pdf-notifications-container');
        }
    }

    bindEvents() {
        // Gestionnaire pour fermer les notifications au clic
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('pdf-notification-close')) {
                const notification = e.target.closest('.pdf-notification');
                if (notification) {
                    this.close(notification.dataset.id);
                }
            }
        });

        // Fermeture automatique au survol (optionnel)
        this.container.addEventListener('mouseenter', (e) => {
            if (e.target.classList.contains('pdf-notification')) {
                // Pause du timer d'auto-fermeture
                const id = e.target.dataset.id;
                if (this.notifications.has(id)) {
                    clearTimeout(this.notifications.get(id).timer);
                }
            }
        });

        this.container.addEventListener('mouseleave', (e) => {
            if (e.target.classList.contains('pdf-notification')) {
                // Redémarrage du timer d'auto-fermeture
                const id = e.target.dataset.id;
                if (this.notifications.has(id)) {
                    this.restartTimer(id);
                }
            }
        });
    }

    show(message, type = 'info', duration = null) {
        if (!window.pdfBuilderNotifications || !window.pdfBuilderNotifications.enabled) {
            console.log('Notifications disabled or not configured');
            return null;
        }

        const id = 'notification_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const position = window.pdfBuilderNotifications.position || 'top-right';
        const autoDuration = duration || window.pdfBuilderNotifications.duration || 5000;

        // Créer l'élément de notification
        const notification = this.createNotificationElement(id, message, type, position);

        // Ajouter au conteneur
        this.container.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.classList.add('visible');
        }, 10);

        // Programmer la fermeture automatique
        const timer = setTimeout(() => {
            this.close(id);
        }, autoDuration);

        // Stocker la notification
        this.notifications.set(id, {
            element: notification,
            timer: timer,
            duration: autoDuration
        });

        return id;
    }

    createNotificationElement(id, message, type, position) {
        const notification = document.createElement('div');
        notification.className = `pdf-notification pdf-notification-${type}`;
        notification.dataset.id = id;

        const icon = this.getIcon(type);

        notification.innerHTML = `
            <div class="pdf-notification-content">
                <span class="pdf-notification-icon">${icon}</span>
                <span class="pdf-notification-message">${this.escapeHtml(message)}</span>
                <button class="pdf-notification-close" aria-label="Fermer">×</button>
            </div>
            <div class="pdf-notification-progress"></div>
        `;

        // Ajouter la classe de position
        notification.classList.add(`pdf-notification-${position}`);

        return notification;
    }

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    close(id) {
        const notificationData = this.notifications.get(id);
        if (!notificationData) return;

        const { element, timer } = notificationData;

        // Annuler le timer
        if (timer) {
            clearTimeout(timer);
        }

        // Animation de sortie
        element.classList.remove('visible');
        element.classList.add('closing');

        // Supprimer après l'animation
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
            this.notifications.delete(id);
        }, 300);
    }

    restartTimer(id) {
        const notificationData = this.notifications.get(id);
        if (!notificationData) return;

        // Annuler l'ancien timer
        if (notificationData.timer) {
            clearTimeout(notificationData.timer);
        }

        // Créer un nouveau timer
        const newTimer = setTimeout(() => {
            this.close(id);
        }, notificationData.duration);

        // Mettre à jour
        notificationData.timer = newTimer;
        this.notifications.set(id, notificationData);
    }

    // Méthodes de commodité
    success(message, duration = null) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = null) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = null) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = null) {
        return this.show(message, 'info', duration);
    }

    // Fermer toutes les notifications
    closeAll() {
        for (const [id] of this.notifications) {
            this.close(id);
        }
    }

    // Méthode AJAX pour afficher une notification depuis PHP
    static showFromAjax(message, type = 'info', duration = null) {
        if (typeof window.PDFBuilderNotificationsInstance === 'undefined') {
            window.PDFBuilderNotificationsInstance = new PDFBuilderNotifications();
        }
        return window.PDFBuilderNotificationsInstance.show(message, type, duration);
    }
}

// Initialisation globale
window.PDFBuilderNotifications = PDFBuilderNotifications;

// Instance globale
window.pdfBuilderNotificationsInstance = new PDFBuilderNotifications();

// Fonctions helper globales
window.showNotification = (message, type, duration) => {
    return window.pdfBuilderNotificationsInstance.show(message, type, duration);
};

window.showSuccessNotification = (message, duration) => {
    return window.pdfBuilderNotificationsInstance.success(message, duration);
};

window.showErrorNotification = (message, duration) => {
    return window.pdfBuilderNotificationsInstance.error(message, duration);
};

window.showWarningNotification = (message, duration) => {
    return window.pdfBuilderNotificationsInstance.warning(message, duration);
};

window.showInfoNotification = (message, duration) => {
    return window.pdfBuilderNotificationsInstance.info(message, duration);
};

// Initialisation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que l'instance est créée
    if (typeof window.pdfBuilderNotificationsInstance === 'undefined') {
        window.pdfBuilderNotificationsInstance = new PDFBuilderNotifications();
    }
});

console.log('🔔 PDF Builder Notifications system loaded');