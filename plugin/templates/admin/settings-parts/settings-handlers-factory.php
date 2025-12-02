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
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vérifier le nonce - accepter les deux types (moderne et legacy)
            $nonce_valid = false;
            $nonce_received = isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT_PROVIDED';
            
            // DEBUG: Logs pour déboguer le problème de nonce
            error_log('PDF Builder AJAX Debug [' . $tab_id . ']:');
            error_log('  Nonce reçu: ' . $nonce_received);
            
            if (isset($_POST['nonce'])) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings');
                
                error_log('  Vérification (pdf_builder_settings): ' . var_export($nonce_valid, true));
            }
            
            error_log('  Résultat final: ' . ($nonce_valid ? 'VALID' : 'INVALID'));
            
            if (!$nonce_valid) {
                wp_send_json_error(['message' => 'Vérification de sécurité échouée']);
                return;
            }

            // Récupérer et nettoyer les données
            $data = [];

            // Si aucun champ spécifié, accepter tous les champs POST (sauf action et nonce)
            if (empty($fields)) {
                $allowed_posts = $_POST;
                unset($allowed_posts['action']);
                unset($allowed_posts['_wpnonce']);

                foreach ($allowed_posts as $key => $value) {
                    // Nettoyer la clé et la valeur
                    $clean_key = sanitize_key($key);
                    $clean_value = is_callable($sanitizer) ? call_user_func($sanitizer, $value) : sanitize_text_field($value);
                    $data[$clean_key] = $clean_value;
                }
            } else {
                // Traiter seulement les champs spécifiés
                foreach ($fields as $field) {
                    if (isset($_POST[$field])) {
                        $clean_value = is_callable($sanitizer) ? call_user_func($sanitizer, $_POST[$field]) : sanitize_text_field($_POST[$field]);
                        $data[$field] = $clean_value;
                    }
                }
            }

            // Sauvegarder les données
            $processed_count = 0;
            $saved_count = 0;

            foreach ($data as $field => $value) {
                $option_name = 'pdf_builder_' . $field;
                $result = update_option($option_name, $value);
                $processed_count++;
                if ($result !== false) {
                    $saved_count++;
                }
            }

            if ($processed_count > 0) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'tab_id' => $tab_id,
                    'fields_processed' => array_keys($data),
                    'fields_saved' => $saved_count,
                ]);
            } else {
                wp_send_json_error(['message' => 'Aucune donnée reçue']);
            }

        } catch (Exception $e) {
            // Log l'erreur pour le debugging
            error_log('PDF Builder Settings Save Error: ' . $e->getMessage());

            // Retourner une erreur générique à l'utilisateur
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde des paramètres']);
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

// Initialiser les handlers - IMPORTANT: Utiliser 'init' au lieu de 'admin_init'
// pour que les actions AJAX soient correctement enregistrées
add_action('init', 'pdf_builder_initialize_all_settings_handlers', 1);

// Handler pour la sauvegarde d'onglet spécifique (utilisé par le système JavaScript moderne)
// IMPORTANT: Enregistrer directement, pas dans un hook admin_init
add_action('wp_ajax_pdf_builder_save_tab_settings', function() {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vérifier le nonce - accepter les deux types (moderne et legacy)
            $nonce_valid = false;
            $nonce_received = isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT_PROVIDED';
            $tab_id = isset($_POST['tab']) ? sanitize_key($_POST['tab']) : 'UNKNOWN';
            
            // DEBUG: Logs pour déboguer le problème de nonce
            error_log('PDF Builder Tab Settings AJAX Debug [' . $tab_id . ']:');
            error_log('  Nonce reçu: ' . $nonce_received);
            
            if (isset($_POST['nonce'])) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings');
                
                error_log('  Vérification (pdf_builder_settings): ' . var_export($nonce_valid, true));
            }
            
            error_log('  Résultat final: ' . ($nonce_valid ? 'VALID' : 'INVALID'));
            
            if (!$nonce_valid) {
                wp_send_json_error(['message' => 'Vérification de sécurité échouée']);
                return;
            }

            // Récupérer l'onglet demandé
            $tab_id = isset($_POST['tab']) ? sanitize_key($_POST['tab']) : '';
            if (empty($tab_id)) {
                wp_send_json_error(['message' => 'Onglet non spécifié']);
                return;
            }

            // Récupérer et nettoyer les données
            $data = [];
            $allowed_posts = $_POST;
            unset($allowed_posts['action'], $allowed_posts['nonce'], $allowed_posts['tab']);

            foreach ($allowed_posts as $key => $value) {
                $clean_key = sanitize_key($key);
                $clean_value = sanitize_text_field($value);
                $data[$clean_key] = $clean_value;
            }

            // Sauvegarder les données
            $processed_count = 0;
            $saved_count = 0;

            foreach ($data as $field => $value) {
                $option_name = 'pdf_builder_' . $field;
                $result = update_option($option_name, $value);
                $processed_count++;
                if ($result !== false) {
                    $saved_count++;
                }
            }

            if ($processed_count > 0) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'tab_id' => $tab_id,
                    'fields_processed' => array_keys($data),
                    'fields_saved' => $saved_count,
                ]);
            } else {
                wp_send_json_error(['message' => 'Aucune donnée reçue']);
            }

        } catch (Exception $e) {
            error_log('PDF Builder Tab Settings Save Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde des paramètres']);
        }
});
?>
