<?php
/**
 * PDF Builder Pro V2 - Page d'accueil du plugin
 * Redirection automatique vers l'éditeur
 */

if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

// Redirection automatique vers l'éditeur
wp_redirect(admin_url('admin.php?page=pdf-builder-react-editor'));
exit;


