/**
 * PDF Builder - Système de Notifications Unifié
 * @version 2.0.0
 */
(function ($) {
  "use strict";

  // Configuration
  const config = {
    position: "top-right",
    duration: 5000,
    maxNotifications: 5,
    animation: "slide",
    types: {
      success: { icon: "✅", color: "#27ae60", bg: "#d4edda" },
      error: { icon: "❌", color: "#e74c3c", bg: "#f8d7da" },
      warning: { icon: "⚠️", color: "#f39c12", bg: "#fff3cd" },
      info: { icon: "ℹ️", color: "#667eea", bg: "#e3e8ff" },
    },
  };

  // Container de notifications
  let $container = null;
  const notifications = [];

  // Initialiser le container
  function initContainer() {
    if ($container) return;

    $container = $('<div class="pdfb-notifications-container"></div>')
      .attr("data-position", config.position)
      .appendTo("body");
  }

  // Créer une notification
  function createNotification(message, type, duration) {
    const typeConfig = config.types[type] || config.types.info;
    const id =
      "notif-" + Date.now() + "-" + Math.random().toString(36).substring(7);

    const $notification = $(`
            <div class="pdfb-notification pdfb-notification-${type} pdfb-notification-${config.animation}" data-id="${id}">
                <div class="pdfb-notification-content">
                    <span class="pdfb-notification-icon">${typeConfig.icon}</span>
                    <span class="pdfb-notification-message">${message}</span>
                    <button class="pdfb-notification-close" aria-label="Fermer">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
                <div class="pdfb-notification-progress-bar">
                    <div class="pdfb-notification-progress"></div>
                </div>
            </div>
        `).css({
      "background-color": typeConfig.bg,
      color: typeConfig.color,
      "border-left-color": typeConfig.color,
    });

    // Animation de la barre de progression
    if (duration > 0) {
      const $progress = $notification.find(".pdfb-notification-progress");
      $progress.css({
        width: "100%",
        "background-color": typeConfig.color,
        transition: `width ${duration}ms linear`,
      });

      setTimeout(() => $progress.css("width", "0%"), 50);
    }

    // Événement de fermeture
    $notification.find(".pdfb-notification-close").on("click", function () {
      dismissNotification($notification, id);
    });

    return { $element: $notification, id, duration, type };
  }

  // Afficher une notification
  function show(message, type = "info", duration = config.duration) {
    initContainer();

    // Limiter le nombre de notifications
    if (notifications.length >= config.maxNotifications) {
      const oldest = notifications.shift();
      oldest.$element.remove();
    }

    const notification = createNotification(message, type, duration);
    notifications.push(notification);

    // Ajouter au container
    $container.prepend(notification.$element);

    // Animer l'entrée
    setTimeout(() => notification.$element.addClass("visible"), 10);

    // Auto-fermeture
    if (duration > 0) {
      setTimeout(
        () => dismissNotification(notification.$element, notification.id),
        duration,
      );
    }

    return notification.id;
  }

  // Fermer une notification
  function dismissNotification($element, id) {
    if (!$element || !$element.hasClass("visible")) return;

    $element.removeClass("visible").addClass("dismissing");

    setTimeout(() => {
      $element.remove();
      const index = notifications.findIndex((n) => n.id === id);
      if (index !== -1) notifications.splice(index, 1);
    }, 300);
  }

  // Fermer toutes les notifications
  function dismissAll() {
    notifications.forEach((n) => dismissNotification(n.$element, n.id));
  }

  // API Publique
  window.pdfBuilderNotifications = {
    show: show,
    success: (msg, duration) => show(msg, "success", duration),
    error: (msg, duration) => show(msg, "error", duration),
    warning: (msg, duration) => show(msg, "warning", duration),
    info: (msg, duration) => show(msg, "info", duration),
    dismiss: dismissNotification,
    dismissAll: dismissAll,
    setPosition: (pos) => {
      config.position = pos;
      if ($container) $container.attr("data-position", pos);
    },
    setDuration: (dur) => {
      config.duration = dur;
    },
  };

  // Fonctions globales pour compatibilité
  window.showSuccessNotification = (msg, dur) => show(msg, "success", dur);
  window.showErrorNotification = (msg, dur) => show(msg, "error", dur);
  window.showWarningNotification = (msg, dur) => show(msg, "warning", dur);
  window.showInfoNotification = (msg, dur) => show(msg, "info", dur);
  window.showSystemNotification = show;

  // Auto-init
  $(document).ready(initContainer);
})(jQuery);
