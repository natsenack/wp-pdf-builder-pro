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
 * Sauvegarde en batch les options WordPress (beaucoup plus rapide que update_option() en boucle)
 * Utilise une transaction pour être atomique
 * 
 * @param array $options_data Tableau ['option_name' => 'value']
 * @return int Nombre d'options sauvegardées
 */
function pdf_builder_batch_save_options($options_data) {
    global $wpdb;
    
    if (empty($options_data)) {
        return 0;
    }
    
    $start_time = microtime(true);
    $saved = 0;
    
    // Désactiver les autocommit pour traiter en bloc
    $wpdb->query('START TRANSACTION');
    
    try {
        foreach ($options_data as $option_name => $value) {
            // Vérifier si l'option existe déjà
            $existing = get_option($option_name);
            
            if ($existing === false) {
                // INSERT si n'existe pas
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'yes')",
                    $option_name,
                    maybe_serialize($value)
                ));
                $saved++;
            } elseif ($existing != $value) {
                // UPDATE si valeur différente
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s",
                    maybe_serialize($value),
                    $option_name
                ));
                $saved++;
            }
        }
        
        $wpdb->query('COMMIT');
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('PDF Builder Batch Save Error: ' . $e->getMessage());
        return 0;
    }
    
    $elapsed = microtime(true) - $start_time;
    error_log('PDF Builder Batch Save: ' . $saved . '/' . count($options_data) . ' en ' . round($elapsed * 1000, 2) . 'ms');
    
    // Clear cache une seule fois à la fin
    wp_cache_flush();
    
    return $saved;
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

            // Sauvegarder les données EN BATCH (beaucoup plus rapide)
            $options_to_save = [];
            foreach ($data as $field => $value) {
                $option_name = 'pdf_builder_' . $field;
                $options_to_save[$option_name] = $value;
            }

            $saved_count = pdf_builder_batch_save_options($options_to_save);
            $processed_count = count($data);

            if ($processed_count > 0) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'tab_id' => $tab_id,
                    'fields_processed' => array_keys($data),
                    'fields_saved' => $saved_count,
                    'elapsed_ms' => round($elapsed * 1000, 2)
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

/**
 * Handler AJAX ultra-rapide pour sauvegarder TOUS les paramètres en UNE SEULE requête
 * Beaucoup plus rapide que d'appeler les handlers individuels
 */
function pdf_builder_register_bulk_save_handler() {
    add_action('wp_ajax_pdf_builder_save_all_direct', function() {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce'])) {
                wp_send_json_error(['message' => 'Vérification de sécurité échouée']);
                return;
            }

            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings');
            if (!$nonce_valid) {
                wp_send_json_error(['message' => 'Vérification de sécurité échouée']);
                return;
            }

            // Récupérer et nettoyer TOUS les paramètres en une seule opération
            $data = [];
            $allowed_keys = $_POST;
            unset($allowed_keys['action']);
            unset($allowed_keys['nonce']);

            // Nettoyer et valider en une seule boucle
            foreach ($allowed_keys as $key => $value) {
                $clean_key = sanitize_key($key);
                $clean_value = is_array($value) ? implode(',', array_map('sanitize_text_field', $value)) : sanitize_text_field($value);
                $data['pdf_builder_' . $clean_key] = $clean_value;
            }

            if (empty($data)) {
                wp_send_json_error(['message' => 'Aucune donnée']);
                return;
            }

            // Sauvegarder EN BATCH (ultra rapide!)
            $saved_count = pdf_builder_batch_save_options($data);

            wp_send_json_success([
                'message' => 'Tous les paramètres sauvegardés',
                'fields_saved' => $saved_count,
                'count' => count($data)
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder Bulk Save Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde']);
        }
    });
}

// Enregistrer le handler bulk
add_action('init', 'pdf_builder_register_bulk_save_handler');

// Pour permettre l'accès non-authentifié (optionnel)
add_action('wp_ajax_nopriv_pdf_builder_save_all_direct', function() {
    wp_send_json_error(['message' => 'Non autorisé']);
});
?>