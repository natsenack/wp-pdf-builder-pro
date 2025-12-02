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
 * @param array $fields Liste des IDs des champs HTML à gérer (optionnel - si vide, accepte tous)
 * @param callable $sanitizer Fonction de nettoyage personnalisée (optionnel)
 */
function pdf_builder_register_settings_handler($tab_id, $fields = [], $sanitizer = null) {
    if (empty($tab_id)) {
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
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Récupérer et nettoyer les données
        $data = [];
        
        // Si aucun champ spécifié, accepter tous les champs POST (sauf action et nonce)
        if (empty($fields)) {
            $allowed_posts = $_POST;
            unset($allowed_posts['action']);
            unset($allowed_posts['nonce']);
            $fields = array_keys($allowed_posts);
        }

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
    // Onglet Général (accepte tous les champs)
    pdf_builder_register_settings_handler('general', []);

    // Onglet Licence (accepte tous les champs)
    pdf_builder_register_settings_handler('licence', []);

    // Onglet Accès (accepte tous les champs)
    pdf_builder_register_settings_handler('acces', []);

    // Onglet Sécurité (accepte tous les champs)
    pdf_builder_register_settings_handler('securite', []);

    // Onglet PDF (accepte tous les champs)
    pdf_builder_register_settings_handler('pdf', []);

    // Onglet Contenu (accepte tous les champs)
    pdf_builder_register_settings_handler('contenu', []);

    // Onglet Développeur (accepte tous les champs)
    pdf_builder_register_settings_handler('developpeur', []);

    // Onglet Système (accepte tous les champs)
    pdf_builder_register_settings_handler('systeme', []);

    // Onglet Modèles (accepte tous les champs)
    pdf_builder_register_settings_handler('templates', []);
}

// Initialiser les handlers au chargement du plugin admin
if (is_admin()) {
    add_action('admin_init', 'pdf_builder_initialize_all_settings_handlers', 1);
}
?>
