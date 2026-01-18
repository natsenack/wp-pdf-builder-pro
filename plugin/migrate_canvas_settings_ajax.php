<?php
/**
 * Migration AJAX Handler pour créer la table des paramètres canvas
 * À utiliser depuis l'interface admin WordPress
 */

add_action('wp_ajax_pdf_builder_migrate_canvas_settings', 'pdf_builder_migrate_canvas_settings_ajax');

// Action de test pour vérifier l'enregistrement AJAX
add_action('wp_ajax_pdf_builder_test_ajax_registration', 'pdf_builder_test_ajax_registration');

function pdf_builder_test_ajax_registration() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission refusée', 'pdf-builder-pro')]);
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_test_ajax')) {
        wp_send_json_error(['message' => __('Nonce invalide', 'pdf-builder-pro')]);
        return;
    }

    $response = [
        'migration_action_registered' => has_action('wp_ajax_pdf_builder_migrate_canvas_settings'),
        'test_action_registered' => has_action('wp_ajax_pdf_builder_test_ajax_registration'),
        'function_exists' => function_exists('pdf_builder_migrate_canvas_settings_ajax'),
        'class_exists' => class_exists('PDF_Builder_Database_Updater'),
        'actions' => []
    ];

    // Lister toutes les actions AJAX enregistrées pour pdf_builder
    global $wp_filter;
    foreach ($wp_filter as $hook => $filters) {
        if (strpos($hook, 'wp_ajax_pdf_builder') === 0) {
            $response['actions'][$hook] = count($filters->callbacks);
        }
    }

    wp_send_json_success($response);
}

function pdf_builder_migrate_canvas_settings_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission refusée', 'pdf-builder-pro')]);
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_migrate_canvas_settings')) {
        wp_send_json_error(['message' => __('Nonce invalide', 'pdf-builder-pro')]);
        return;
    }

    try {
        // Vérifier que nous sommes dans un environnement WordPress valide
        if (!defined('ABSPATH') || !function_exists('get_option')) {
            throw new Exception('Environnement WordPress non valide');
        }

        // Log pour déboguer
        error_log('PDF Builder Migration: Début de la migration AJAX');

        // Inclure le gestionnaire de base de données
        if (!class_exists('PDF_Builder_Database_Updater')) {
            $updater_path = dirname(__FILE__) . '/src/Core/PDF_Builder_Database_Updater.php';
            error_log('PDF Builder Migration: Tentative de chargement du fichier: ' . $updater_path);
            if (!file_exists($updater_path)) {
                throw new Exception('Fichier PDF_Builder_Database_Updater.php introuvable: ' . $updater_path);
            }
            require_once $updater_path;
            error_log('PDF Builder Migration: Fichier chargé');
        }

        if (!class_exists('PDF_Builder_Database_Updater')) {
            throw new Exception('Classe PDF_Builder_Database_Updater non trouvée après inclusion');
        }

        error_log('PDF Builder Migration: Classe trouvée, tentative d\'instanciation');

        // Tenter d'instancier le gestionnaire
        $updater = PDF_Builder_Database_Updater::get_instance();

        if (!$updater) {
            throw new Exception('Impossible d\'instancier PDF_Builder_Database_Updater');
        }

        error_log('PDF Builder Migration: Instance créée avec succès');

        $response = [
            'success' => false,
            'message' => '',
            'details' => []
        ];

        // Vérifier les migrations en attente
        $pending = $updater->get_pending_migrations();

        if (empty($pending)) {
            $response['message'] = 'Aucune migration en attente.';
            $response['success'] = true;
        } else {
            $response['details']['pending_migrations'] = array_keys($pending);

            // Exécuter la migration 1.4.0 si elle est en attente
            if (isset($pending['1.4.0'])) {
                $result = $updater->run_migration('1.4.0', PDF_Builder_Database_Updater::MIGRATION_UP);

                if ($result) {
                    $response['success'] = true;
                    $response['message'] = 'Migration 1.4.0 exécutée avec succès!';

                    // Vérifier que la table a été créée
                    global $wpdb;
                    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_settings'") === $wpdb->prefix . 'pdf_builder_settings';

                    if ($table_exists) {
                        $response['details']['table_created'] = true;

                        // Migrer les paramètres existants
                        $existing_settings = get_option('pdf_builder_settings', []);
                        $canvas_settings_count = 0;

                        foreach ($existing_settings as $key => $value) {
                            if (strpos($key, 'pdf_builder_canvas_') === 0) {
                                $canvas_settings_count++;
                            }
                        }

                        $response['details']['canvas_settings_migrated'] = $canvas_settings_count;

                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Erreur: La table n\'a pas été créée!';
                    }

                } else {
                    $response['message'] = 'Erreur lors de l\'exécution de la migration!';
                }
            }
        }

        // Vérifier l'état final
        $current_version = get_option(PDF_Builder_Database_Updater::DB_VERSION_OPTION, '0.0.0');
        $response['details']['current_db_version'] = $current_version;

        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_settings'") === $wpdb->prefix . 'pdf_builder_settings';
        $response['details']['table_exists'] = $table_exists;

        if ($table_exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_settings WHERE setting_group = 'canvas'");
            $response['details']['canvas_settings_count'] = intval($count);
        }

    } catch (Exception $e) {
        error_log('PDF Builder Migration: Erreur capturée: ' . $e->getMessage());
        error_log('PDF Builder Migration: Trace: ' . $e->getTraceAsString());
        $response['success'] = false;
        $response['message'] = 'Erreur: ' . $e->getMessage();
        $response['debug'] = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }

    wp_send_json($response);
}