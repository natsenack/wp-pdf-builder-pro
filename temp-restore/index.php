<?php
/**
 * PDF Builder Pro - Sécurité
 * Blocage de l'accès direct au répertoire
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Redirection vers l'admin WordPress
if (is_admin()) {
    wp_redirect(admin_url('admin.php?page=pdf-builder-main'));
    exit;
}

// Redirection par défaut
wp_redirect(home_url());
exit;
