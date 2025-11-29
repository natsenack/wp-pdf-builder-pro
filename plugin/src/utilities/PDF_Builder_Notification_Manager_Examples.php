<?php
/**
 * PDF Builder Pro - Système de notifications unifié
 *
 * Ce fichier démontre l'utilisation du nouveau système de notifications unifié
 * qui remplace tous les anciens systèmes de notifications.
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

// Exemples d'utilisation du système de notifications

// =============================================================================
// ✅ NOTIFICATIONS TOAST (petites notifications en haut à droite)
// =============================================================================

// Méthode simple - toast de succès
PDF_Builder_Notification_Manager::success("Opération réussie !");

// Toast d'erreur
PDF_Builder_Notification_Manager::error("Une erreur s'est produite.");

// Toast d'avertissement
PDF_Builder_Notification_Manager::warning("Attention : configuration incomplète.");

// Toast d'information
PDF_Builder_Notification_Manager::info("Nouveau tutoriel disponible.");

// Toast personnalisé avec durée spécifique
PDF_Builder_Notification_Manager::show_toast("Message personnalisé", 'success', 10000);

// =============================================================================
// ✅ NOTIFICATIONS ADMIN (bannières WordPress standard)
// =============================================================================

// Notice de succès
PDF_Builder_Notification_Manager::success("Sauvegarde effectuée avec succès.", PDF_Builder_Notification_Manager::STYLE_ADMIN_NOTICE);

// Notice d'erreur
PDF_Builder_Notification_Manager::error("Erreur lors de la sauvegarde.", PDF_Builder_Notification_Manager::STYLE_ADMIN_NOTICE);

// =============================================================================
// ✅ NOTIFICATIONS SYSTÈME (avec stockage en base et canaux multiples)
// =============================================================================

// Notification système simple
pdf_builder_notify(
    PDF_Builder_Notification_Manager::TYPE_INFO,
    'Configuration mise à jour',
    'Les paramètres ont été sauvegardés.'
);

// Notification avec données supplémentaires
pdf_builder_notify(
    PDF_Builder_Notification_Manager::TYPE_SUCCESS,
    'Sauvegarde créée',
    'La sauvegarde #123 a été créée avec succès.',
    [], // canaux par défaut
    ['backup_id' => 123, 'size' => '2.5MB']
);

// Notification critique avec email
pdf_builder_notify(
    PDF_Builder_Notification_Manager::TYPE_CRITICAL,
    'Violation de sécurité',
    'Tentative d\'accès non autorisé détectée.',
    [PDF_Builder_Notification_Manager::CHANNEL_EMAIL, PDF_Builder_Notification_Manager::CHANNEL_LOG]
);

// =============================================================================
// ✅ RÉCUPÉRATION DES NOTIFICATIONS (pour les dashboards, etc.)
// =============================================================================

// Obtenir toutes les notifications
$notifications = pdf_builder_get_notifications();

// Obtenir seulement les notifications non lues
$unread = pdf_builder_get_notifications(50, 0, '', false);

// Obtenir le nombre de notifications non lues
$unread_count = pdf_builder_get_unread_notifications_count();

// Obtenir seulement les erreurs
$errors = pdf_builder_get_notifications(50, 0, PDF_Builder_Notification_Manager::TYPE_ERROR);

// =============================================================================
// ✅ UTILISATION EN JAVASCRIPT
// =============================================================================
/*
Dans JavaScript, vous pouvez utiliser :

// Toast simple
PDF_Builder_Notification_Manager.show_toast('Message', 'success');

// Toast avec durée personnalisée
PDF_Builder_Notification_Manager.show_toast('Message', 'error', 5000);

// Via AJAX
jQuery.ajax({
    url: pdfBuilderNotifications.ajax_url,
    type: 'POST',
    data: {
        action: 'pdf_builder_show_toast',
        message: 'Notification depuis AJAX',
        type: 'info',
        nonce: pdfBuilderNotifications.nonce
    }
});
*/

// =============================================================================
// ✅ CONSTANTES DISPONIBLES
// =============================================================================
/*
// Types de notification
PDF_Builder_Notification_Manager::TYPE_SUCCESS   // 'success'
PDF_Builder_Notification_Manager::TYPE_ERROR     // 'error'
PDF_Builder_Notification_Manager::TYPE_WARNING   // 'warning'
PDF_Builder_Notification_Manager::TYPE_INFO      // 'info'
PDF_Builder_Notification_Manager::TYPE_CRITICAL  // 'critical'

// Styles de notification
PDF_Builder_Notification_Manager::STYLE_TOAST         // 'toast'
PDF_Builder_Notification_Manager::STYLE_INLINE        // 'inline'
PDF_Builder_Notification_Manager::STYLE_ADMIN_NOTICE  // 'admin_notice'

// Canaux de notification
PDF_Builder_Notification_Manager::CHANNEL_ADMIN_NOTICE  // 'admin_notice'
PDF_Builder_Notification_Manager::CHANNEL_EMAIL         // 'email'
PDF_Builder_Notification_Manager::CHANNEL_LOG           // 'log'
PDF_Builder_Notification_Manager::CHANNEL_DASHBOARD     // 'dashboard'
*/

// =============================================================================
// ✅ HOOKS ET FILTRES DISPONIBLES
// =============================================================================
/*
// Filtres pour personnaliser les comportements
add_filter('pdf_builder_email_notifications_enabled', '__return_true');
add_filter('pdf_builder_notify_on_backups', '__return_true');
add_filter('pdf_builder_notify_on_errors', '__return_true');
add_filter('pdf_builder_notifications_default_toast_duration', function($duration) {
    return 8000; // 8 secondes
});

// Actions pour les notifications automatiques
add_action('pdf_builder_config_updated', function($new_config, $changed_config) {
    // Logique personnalisée lors de la mise à jour de config
});

add_action('pdf_builder_backup_created', function($backup_data) {
    // Logique personnalisée lors de la création de sauvegarde
});

add_action('pdf_builder_error_occurred', function($error_data) {
    // Logique personnalisée lors d'une erreur
});

add_action('pdf_builder_security_violation', function($violation_data) {
    // Logique personnalisée lors d'une violation de sécurité
});

add_action('pdf_builder_update_completed', function($update_data) {
    // Logique personnalisée lors d'une mise à jour
});
*/

// =============================================================================
// ✅ MIGRATION DEPUIS LES ANCIENS SYSTÈMES
// =============================================================================
/*
// Ancien code (à remplacer) :
PDF_Builder_UI_Notification_Manager::success("Message");

// Nouveau code :
PDF_Builder_Notification_Manager::success("Message");

// Ancien code avec Core (à remplacer) :
PDF_Builder\Core\PDF_Builder_Notification_Manager::get_instance()->send_notification(...);

// Nouveau code :
pdf_builder_notify(PDF_Builder_Notification_Manager::TYPE_SUCCESS, 'Titre', 'Message');
*/