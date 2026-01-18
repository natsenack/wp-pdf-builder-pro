<?php
/**
 * Migration AJAX Handler - Version simplifiée pour diagnostic
 */

// Gestionnaire d'erreur minimal
function pdf_builder_error_handler($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error: ' . $errstr,
        'debug' => ['file' => $errfile, 'line' => $errline]
    ]);
    exit;
}

function pdf_builder_exception_handler($exception) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $exception->getMessage(),
        'debug' => ['file' => $exception->getFile(), 'line' => $exception->getLine()]
    ]);
    exit;
}

// Définir les gestionnaires immédiatement
set_error_handler('pdf_builder_error_handler');
set_exception_handler('pdf_builder_exception_handler');

add_action('wp_ajax_pdf_builder_migrate_canvas_settings', 'pdf_builder_migrate_canvas_settings_ajax');

function pdf_builder_migrate_canvas_settings_ajax() {
    try {
        // Réponse de test simple
        wp_send_json_success([
            'message' => 'Test de migration réussi',
            'timestamp' => time()
        ]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
    }
}

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