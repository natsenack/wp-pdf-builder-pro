<?php
/**
 * Handlers AJAX pour les fonctionnalités de cache avancées
 * PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
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

    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_cache_actions')) {
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

    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_cache_actions')) {
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