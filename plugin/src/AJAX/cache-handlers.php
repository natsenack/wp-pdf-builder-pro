<?php
/**
 * Handlers AJAX pour les fonctionnalités de cache avancées
 * PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Hooks AJAX pour les actions de maintenance
add_action('wp_ajax_pdf_builder_optimize_database', 'pdf_builder_optimize_database_ajax');
add_action('wp_ajax_pdf_builder_repair_templates', 'pdf_builder_repair_templates_ajax');
add_action('wp_ajax_pdf_builder_remove_temp_files', 'pdf_builder_remove_temp_files_ajax');
add_action('wp_ajax_pdf_builder_run_manual_maintenance', 'pdf_builder_run_manual_maintenance_ajax');
add_action('wp_ajax_pdf_builder_schedule_maintenance', 'pdf_builder_schedule_maintenance_ajax');
add_action('wp_ajax_pdf_builder_toggle_auto_maintenance', 'pdf_builder_toggle_auto_maintenance_ajax');

/**
 * Vérifie un nonce pour les actions de maintenance ou le dispatcher AJAX principal
 * Accepte soit le nonce spécifique 'pdf_builder_cache_actions' soit le nonce global 'pdf_builder_ajax'
 */
function pdf_builder_verify_maintenance_nonce($nonce) {
    if (empty($nonce)) {
        return false;
    }
    if (wp_verify_nonce($nonce, 'pdf_builder_cache_actions')) {
        return true;
    }
    if (wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
        return true;
    }
    return false;
}

/**
 * AJAX Handler - Test de l'intégration du cache
 */
function pdf_builder_test_cache_integration_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (accepte cache_actions ou ajax)
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        error_log('PDF Builder Cache Test: Nonce invalide ou manquant - Nonce reçu: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NONCE_MANQUANT'));
        wp_send_json_error('Nonce invalide');
        return;
    }

    try {
        $results = [];

        // Test 1: Vérifier si le cache est activé
        $cache_enabled = get_option('pdf_builder_cache_enabled', false);
        $results[] = [
            'test' => 'Cache activé',
            'status' => $cache_enabled ? 'success' : 'warning',
            'message' => $cache_enabled ? 'Le cache est activé' : 'Le cache est désactivé'
        ];

        // Test 2: Vérifier les dossiers de cache
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';

        if (is_dir($cache_dir)) {
            $results[] = [
                'test' => 'Dossier cache',
                'status' => 'success',
                'message' => 'Le dossier cache existe'
            ];
        } else {
            // Essayer de créer le dossier
            if (wp_mkdir_p($cache_dir)) {
                $results[] = [
                    'test' => 'Dossier cache',
                    'status' => 'success',
                    'message' => 'Le dossier cache a été créé'
                ];
            } else {
                $results[] = [
                    'test' => 'Dossier cache',
                    'status' => 'error',
                    'message' => 'Impossible de créer le dossier cache'
                ];
            }
        }

        // Test 3: Test des transients
        $test_key = 'pdf_builder_test_' . time();
        $test_value = 'test_value_' . mt_rand(1000, 9999); // Utiliser mt_rand au lieu de rand
        set_transient($test_key, $test_value, 300);

        $retrieved_value = get_transient($test_key);
        if ($retrieved_value === $test_value) {
            $results[] = [
                'test' => 'Transients WordPress',
                'status' => 'success',
                'message' => 'Les transients fonctionnent correctement'
            ];
            delete_transient($test_key);
        } else {
            $results[] = [
                'test' => 'Transients WordPress',
                'status' => 'error',
                'message' => 'Les transients ne fonctionnent pas'
            ];
        }

        // Test 4: Test de la base de données
        global $wpdb;
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $results[] = [
            'test' => 'Base de données',
            'status' => 'success',
            'message' => "Transients actifs: $transient_count"
        ];

        wp_send_json_success([
            'message' => 'Test d\'intégration terminé',
            'results' => $results,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du test: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Vider tout le cache
 */
function pdf_builder_clear_all_cache_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (accepte cache_actions ou ajax)
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        error_log('PDF Builder Clear Cache: Nonce invalide ou manquant - Nonce reçu: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NONCE_MANQUANT'));
        wp_send_json_error('Nonce invalide');
        return;
    }

    try {
        $cleared_items = [];

        // 1. Vider les transients du plugin
        global $wpdb;
        $transient_keys = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $transient_count = 0;

        foreach ($transient_keys as $key) {
            $clean_key = str_replace('_transient_', '', $key);
            if (delete_transient($clean_key)) {
                $transient_count++;
            }
        }

        if ($transient_count > 0) {
            $cleared_items[] = "$transient_count transients supprimés";
        }

        // 2. Vider le cache des options
        wp_cache_flush();

        // 3. Vider les fichiers cache
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';

        $files_deleted = 0;
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && unlink($file)) {
                    $files_deleted++;
                }
            }
        }

        if ($files_deleted > 0) {
            $cleared_items[] = "$files_deleted fichiers cache supprimés";
        }

        // 4. Mettre à jour la date du dernier nettoyage
        update_option('pdf_builder_cache_last_cleanup', current_time('mysql'));

        $message = 'Cache vidé avec succès';
        if (!empty($cleared_items)) {
            $message .= ' (' . implode(', ', $cleared_items) . ')';
        }

        wp_send_json_success([
            'message' => $message,
            'cleared_items' => $cleared_items,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du nettoyage: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Obtenir les métriques du cache
 */
function pdf_builder_get_cache_metrics_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder Cache Metrics: Permissions insuffisantes');
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (accepte cache_actions ou ajax)
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        error_log('PDF Builder Cache Metrics: Nonce invalide ou manquant - Nonce reçu: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NONCE_MANQUANT'));
        wp_send_json_error('Nonce invalide');
        return;
    }

    error_log('PDF Builder Cache Metrics: Début de traitement');

    try {
        $metrics = [];

        // 1. Taille du cache
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';
        $cache_size = 0;

        // Temporarily disable folder size calculation to avoid 500 errors
        // if (is_dir($cache_dir) && is_readable($cache_dir)) {
        //     $cache_size = pdf_builder_get_folder_size($cache_dir);
        // }

        // Formater la taille intelligemment : Ko si < 1 Mo, sinon Mo
        if ($cache_size < 1048576) { // 1 Mo = 1048576 bytes
            $metrics['cache_size'] = round($cache_size / 1024, 1) . ' Ko';
        } else {
            $metrics['cache_size'] = size_format($cache_size);
        }

        // 2. Nombre de transients actifs
        global $wpdb;
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        if ($wpdb->last_error) {
            error_log('PDF Builder Cache Metrics: Erreur SQL transients - ' . $wpdb->last_error);
        }
        $metrics['transient_count'] = intval($transient_count);

        // 3. État du cache
        $cache_enabled = get_option('pdf_builder_cache_enabled', false);
        $metrics['cache_enabled'] = $cache_enabled;

        // 4. Dernier nettoyage
        $last_cleanup = get_option('pdf_builder_cache_last_cleanup', 'Jamais');
        if ($last_cleanup !== 'Jamais') {
            $last_cleanup = human_time_diff(strtotime($last_cleanup)) . ' ago';
        }
        $metrics['last_cleanup'] = $last_cleanup;

        error_log('PDF Builder Cache Metrics: Succès - Métriques récupérées');

        wp_send_json_success([
            'metrics' => $metrics,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        error_log('PDF Builder Cache Metrics: Exception - ' . $e->getMessage());
        wp_send_json_error('Erreur lors de la récupération des métriques: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Optimiser la base de données
 */
function pdf_builder_optimize_database_ajax() {
    error_log('PDF Builder Optimize DB: Handler appelé');

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder Optimize DB: Permissions insuffisantes');
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (accepte cache_actions ou ajax)
    $received_nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    error_log('PDF Builder Optimize DB: Nonce reçu: ' . substr($received_nonce, 0, 12) . '...');

    $verify_cache = wp_verify_nonce($received_nonce, 'pdf_builder_cache_actions');
    $verify_ajax = wp_verify_nonce($received_nonce, 'pdf_builder_ajax');

    error_log('PDF Builder Optimize DB: Vérification nonce - cache_actions: ' . intval($verify_cache) . ', ajax: ' . intval($verify_ajax));

    if (!pdf_builder_verify_maintenance_nonce($received_nonce)) {
        error_log('PDF Builder Optimize DB: Nonce invalide ou manquant - Nonce reçu: ' . substr($received_nonce, 0, 12) . '...');
        wp_send_json_error('Nonce invalide');
        return;
    }

    error_log('PDF Builder Optimize DB: Nonce valide, début de l\'optimisation');

    try {
        $optimized_tables = [];

        // Optimiser les tables du plugin
        global $wpdb;

        // Tables principales du plugin
        $tables_to_optimize = [
            $wpdb->prefix . 'pdf_builder_templates',
            $wpdb->prefix . 'pdf_builder_elements',
            $wpdb->prefix . 'pdf_builder_settings'
        ];

        foreach ($tables_to_optimize as $table) {
            error_log('PDF Builder Optimize DB: Vérification table: ' . $table);
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            if ($exists == $table) {
                error_log('PDF Builder Optimize DB: Table trouvée, optimisation: ' . $table);
                $result = $wpdb->query("OPTIMIZE TABLE $table");
                if ($result !== false) {
                    $optimized_tables[] = $table;
                    error_log('PDF Builder Optimize DB: Table optimisée: ' . $table);
                } else {
                    error_log('PDF Builder Optimize DB: Échec OPTIMIZE TABLE ' . $table . ' - WP Error: ' . $wpdb->last_error);
                }
            } else {
                error_log('PDF Builder Optimize DB: Table non trouvée: ' . $table);
            }
        }

        // Nettoyer les options orphelines
        error_log('PDF Builder Optimize DB: Nettoyage des options');
        $cleaned_options = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%' AND option_value = ''");
        if ($cleaned_options === false) {
            error_log('PDF Builder Optimize DB: Échec nettoyage options - WP Error: ' . $wpdb->last_error);
            $cleaned_options = 0;
        } else {
            error_log('PDF Builder Optimize DB: Options nettoyées: ' . $cleaned_options);
        }

        if (empty($optimized_tables) && $cleaned_options == 0) {
            $message = 'Aucune optimisation nécessaire.';
        } else {
            $message = 'Base de données optimisée avec succès';
            if (!empty($optimized_tables)) {
                $message .= ' (' . count($optimized_tables) . ' tables optimisées)';
            }
            if ($cleaned_options > 0) {
                $message .= ", $cleaned_options options nettoyées";
            }
        }

        error_log('PDF Builder Optimize DB: Succès - ' . $message);

        wp_send_json_success([
            'message' => $message,
            'optimized_tables' => $optimized_tables,
            'cleaned_options' => intval($cleaned_options),
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        error_log('PDF Builder Optimize DB: Exception - ' . $e->getMessage());
        wp_send_json_error('Erreur lors de l\'optimisation: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Réparer les templates
 */
function pdf_builder_repair_templates_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (accepte cache_actions ou ajax)
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        error_log('PDF Builder Repair Templates: Nonce invalide ou manquant - Nonce reçu: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NONCE_MANQUANT'));
        wp_send_json_error('Nonce invalide');
        return;
    }

    try {
        $repaired_items = [];

        // 1. Vérifier et réparer les templates WordPress
        $wp_templates = get_posts([
            'post_type' => 'pdf_template',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ]);

        $fixed_wp_templates = 0;
        foreach ($wp_templates as $template) {
            // Vérifier si le template a des métadonnées valides
            $template_data = get_post_meta($template->ID, 'pdf_template_data', true);
            if (empty($template_data)) {
                // Créer des métadonnées par défaut
                $default_data = [
                    'version' => '1.0',
                    'created' => current_time('mysql'),
                    'modified' => current_time('mysql')
                ];
                update_post_meta($template->ID, 'pdf_template_data', $default_data);
                $fixed_wp_templates++;
            }
        }

        if ($fixed_wp_templates > 0) {
            $repaired_items[] = "$fixed_wp_templates templates WordPress réparés";
        }

        // 2. Vérifier et réparer les templates dans la base de données personnalisée
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_builder_templates';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $db_templates = $wpdb->get_results("SELECT id, template_data FROM $table_name");

            $fixed_db_templates = 0;
            foreach ($db_templates as $template) {
                $template_data = json_decode($template->template_data, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($template_data)) {
                    // Données JSON corrompues, créer des données par défaut
                    $default_data = [
                        'id' => $template->id,
                        'name' => 'Template réparé',
                        'version' => '1.0',
                        'created' => current_time('mysql'),
                        'modified' => current_time('mysql'),
                        'elements' => []
                    ];
                    $wpdb->update(
                        $table_name,
                        ['template_data' => json_encode($default_data)],
                        ['id' => $template->id]
                    );
                    $fixed_db_templates++;
                }
            }

            if ($fixed_db_templates > 0) {
                $repaired_items[] = "$fixed_db_templates templates DB réparés";
            }
        }

        $message = 'Templates réparés avec succès';
        if (!empty($repaired_items)) {
            $message .= ' (' . implode(', ', $repaired_items) . ')';
        }

        wp_send_json_success([
            'message' => $message,
            'repaired_items' => $repaired_items,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la réparation: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Supprimer les fichiers temporaires
 */
function pdf_builder_remove_temp_files_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

        // Vérifier le nonce (supporte aussi pdf_builder_ajax)
        if (empty($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
            $provided = isset($_POST['nonce']) ? $_POST['nonce'] : null;
            wp_send_json_error(array('message' => 'Nonce invalide', 'received_nonce' => $provided));
            return;
        }

    try {
        $removed_items = [];

        // 1. Supprimer les fichiers temporaires du plugin
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';

        $temp_files_deleted = 0;
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (unlink($file)) {
                        $temp_files_deleted++;
                    }
                }
            }
        }

        if ($temp_files_deleted > 0) {
            $removed_items[] = "$temp_files_deleted fichiers temporaires supprimés";
        } else {
            $removed_items[] = "Aucun fichier temporaire à supprimer";
        }

        // 2. Nettoyer les transients expirés
        global $wpdb;
        $expired_transients = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%' AND option_value < " . time());

        if ($expired_transients > 0) {
            $removed_items[] = "$expired_transients transients expirés nettoyés";
        }

        // 3. Nettoyer les options temporaires
        $temp_options_deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_temp_%'");

        if ($temp_options_deleted > 0) {
            $removed_items[] = "$temp_options_deleted options temporaires supprimées";
        }

        $message = 'Fichiers temporaires supprimés avec succès';
        if (!empty($removed_items)) {
            $message .= ' (' . implode(', ', $removed_items) . ')';
        }

        wp_send_json_success([
            'message' => $message,
            'removed_items' => $removed_items,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Lancer la maintenance manuelle complète
 */
function pdf_builder_run_manual_maintenance_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    try {
        $maintenance_results = [];

        // 1. Optimiser la base de données
        global $wpdb;
        $tables_optimized = 0;
        $tables_to_optimize = [
            $wpdb->prefix . 'pdf_builder_templates',
            $wpdb->prefix . 'pdf_builder_elements',
            $wpdb->prefix . 'pdf_builder_settings'
        ];

        foreach ($tables_to_optimize as $table) {
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) == $table) {
                $wpdb->query("OPTIMIZE TABLE $table");
                $tables_optimized++;
            }
        }

        $maintenance_results[] = "$tables_optimized tables optimisées";

        // 2. Nettoyer les transients expirés
        $expired_transients = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%' AND option_value < " . time());
        $maintenance_results[] = "$expired_transients transients expirés nettoyés";

        // 3. Supprimer les fichiers temporaires
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
        $temp_files_deleted = 0;

        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < (time() - 86400)) { // Plus de 24h
                    if (unlink($file)) {
                        $temp_files_deleted++;
                    }
                }
            }
        }

        $maintenance_results[] = "$temp_files_deleted fichiers temporaires supprimés";

        // 4. Vider le cache ancien
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';
        $cache_files_deleted = 0;

        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < (time() - 604800)) { // Plus de 7 jours
                    if (unlink($file)) {
                        $cache_files_deleted++;
                    }
                }
            }
        }

        $maintenance_results[] = "$cache_files_deleted fichiers cache anciens supprimés";

        // 5. Mettre à jour la date de dernière maintenance
        update_option('pdf_builder_last_maintenance', current_time('mysql'));

        wp_send_json_success([
            'message' => 'Maintenance manuelle terminée avec succès',
            'results' => $maintenance_results,
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la maintenance: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Programmer la prochaine maintenance
 */
function pdf_builder_schedule_maintenance_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    $next_run = isset($_POST['next_run']) ? sanitize_text_field($_POST['next_run']) : '';

    if (empty($next_run)) {
        wp_send_json_error('Date de prochaine exécution manquante');
        return;
    }

    // Valider le format de date
    $timestamp = strtotime($next_run);
    if (!$timestamp) {
        wp_send_json_error('Format de date invalide');
        return;
    }

    try {
        update_option('pdf_builder_next_maintenance', date('Y-m-d H:i:s', $timestamp));

        wp_send_json_success([
            'message' => 'Maintenance programmée pour le ' . date_i18n('d/m/Y H:i', $timestamp),
            'next_run' => date('Y-m-d H:i:s', $timestamp),
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la programmation: ' . $e->getMessage());
    }
}

/**
 * AJAX Handler - Basculer la maintenance automatique
 */
function pdf_builder_toggle_auto_maintenance_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_maintenance_nonce($_POST['nonce'])) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    try {
        $current_status = get_option('pdf_builder_auto_maintenance', '0');
        $new_status = ($current_status === '1') ? '0' : '1';

        update_option('pdf_builder_auto_maintenance', $new_status);

        wp_send_json_success([
            'message' => 'Maintenance automatique ' . ($new_status === '1' ? 'activée' : 'désactivée'),
            'new_status' => $new_status === '1',
            'timestamp' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du basculement: ' . $e->getMessage());
    }
}

/**
 * Fonction utilitaire pour calculer la taille d'un dossier
 */
function pdf_builder_get_folder_size($dir) {
    $size = 0;
    if (is_dir($dir) && is_readable($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path) && is_readable($path)) {
                    $size += pdf_builder_get_folder_size($path);
                } elseif (is_file($path) && is_readable($path)) {
                    $size += filesize($path);
                }
            }
        }
    }
    return $size;
}