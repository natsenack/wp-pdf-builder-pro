<?php
/**
 * PDF Builder Pro - Settings Helpers
 * Fonctions utilitaires pour la gestion des paramètres
 */

/**
 * Sauvegarde les rôles autorisés
 */
function pdf_builder_save_allowed_roles($roles) {
    if (!is_array($roles)) {
        $roles = [];
    }

    // Nettoyer et valider les rôles
    $valid_roles = [];
    global $wp_roles;
    $all_roles = array_keys($wp_roles->roles);

    foreach ($roles as $role) {
        if (in_array($role, $all_roles)) {
            $valid_roles[] = $role;
        }
    }

    // Toujours inclure administrator
    if (!in_array('administrator', $valid_roles)) {
        $valid_roles[] = 'administrator';
    }

    $settings = get_option('pdf_builder_settings', []);
    $settings['pdf_builder_allowed_roles'] = $valid_roles;
    update_option('pdf_builder_settings', $settings);

    return $valid_roles;
}

/**
 * Récupère les rôles autorisés
 */
function pdf_builder_get_allowed_roles() {
    $settings = get_option('pdf_builder_settings', []);
    $roles = $settings['pdf_builder_allowed_roles'] ?? null;

    if (!is_array($roles) || empty($roles)) {
        // Valeurs par défaut
        return ['administrator', 'editor', 'shop_manager'];
    }

    // Toujours inclure administrator
    if (!in_array('administrator', $roles)) {
        $roles[] = 'administrator';
    }

    return array_unique($roles);
}

/**
 * Vérifie si un rôle est autorisé
 */
function pdf_builder_is_role_allowed($role) {
    $allowed_roles = pdf_builder_get_allowed_roles();
    return in_array($role, $allowed_roles);
}