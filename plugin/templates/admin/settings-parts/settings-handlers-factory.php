<?php
/**
 * Settings AJAX Factory - Générateur centralisé de handlers AJAX
 * 
 * Crée automatiquement des handlers AJAX robustes pour tous les onglets de paramètres
 * sans avoir à coder chaque handler individuellement
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Enregistre automatiquement un handler AJAX pour sauvegarder des champs
 * 
 * @param string $tab_id Identifiant unique de l'onglet (ex: 'licence', 'acces', 'securite')
 * @param array $fields Liste des IDs des champs HTML à gérer
 * @param callable $sanitizer Fonction de nettoyage personnalisée (optionnel)
 */
function pdf_builder_register_settings_handler($tab_id, $fields = [], $sanitizer = null) {
    if (empty($tab_id) || !is_array($fields)) {
        return false;
    }

    // Utiliser la fonction par défaut si aucune n'est fournie
    if ($sanitizer === null) {
        $sanitizer = 'sanitize_text_field';
    }

    // Enregistrer l'action AJAX pour la sauvegarde
    add_action('wp_ajax_pdf_builder_save_' . $tab_id, function() use ($tab_id, $fields, $sanitizer) {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Récupérer et nettoyer les données
        $data = [];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                // Appliquer la fonction de nettoyage
                if (is_callable($sanitizer)) {
                    $data[$field] = call_user_func($sanitizer, $_POST[$field]);
                } else {
                    $data[$field] = sanitize_text_field($_POST[$field]);
                }
            }
        }

        // Sauvegarder dans les options WordPress
        $saved_count = 0;
        foreach ($data as $field => $value) {
            $option_name = 'pdf_builder_' . $field;
            if (update_option($option_name, $value) !== false) {
                $saved_count++;
            }
        }

        if ($saved_count > 0) {
            wp_send_json_success([
                'message' => $saved_count . ' paramètre(s) sauvegardé(s)',
                'tab_id' => $tab_id,
                'fields_saved' => array_keys($data),
            ]);
        } else {
            wp_send_json_error(['message' => 'Aucun changement effectué']);
        }
    });

    return true;
}

/**
 * Enregistre les handlers AJAX pour TOUS les onglets de paramètres
 * Appelée une seule fois au chargement du plugin
 */
function pdf_builder_initialize_all_settings_handlers() {
    // Onglet Licence
    pdf_builder_register_settings_handler('licence', [
        'licence_key',
        'licence_email',
    ]);

    // Onglet Accès
    pdf_builder_register_settings_handler('acces', [
        'acces_admin_only',
        'acces_user_level',
        'acces_custom_roles',
    ]);

    // Onglet Sécurité
    pdf_builder_register_settings_handler('securite', [
        'securite_two_factor',
        'securite_encryption',
        'securite_audit_log',
    ]);

    // Onglet PDF
    pdf_builder_register_settings_handler('pdf', [
        'pdf_compression',
        'pdf_quality',
        'pdf_metadata',
    ]);

    // Onglet Contenu
    pdf_builder_register_settings_handler('contenu', [
        'contenu_theme',
        'contenu_layout',
        'contenu_custom_css',
    ]);

    // Onglet Développeur
    pdf_builder_register_settings_handler('developpeur', [
        'developpeur_debug',
        'developpeur_api_key',
        'developpeur_webhook_url',
    ]);

    // Onglet Système
    pdf_builder_register_settings_handler('systeme', [
        'systeme_cache_enabled',
        'systeme_maintenance_mode',
        'systeme_backup_auto',
    ]);

    // Onglet Modèles
    pdf_builder_register_settings_handler('templates', [
        'templates_default',
        'templates_custom_dir',
        'templates_public',
    ]);
}

// Initialiser les handlers au chargement du plugin admin
if (is_admin()) {
    add_action('admin_init', 'pdf_builder_initialize_all_settings_handlers', 1);
}
?>
