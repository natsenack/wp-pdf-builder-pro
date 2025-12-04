<?php
/**
 * Point d'entrée pour exécuter le diagnostic HTML5
 * Accessible via: /wp-admin/admin-ajax.php?action=pdf_builder_diagnostic_html5
 */

add_action('wp_ajax_pdf_builder_diagnostic_html5', 'pdf_builder_diagnostic_html5_handler');

function pdf_builder_diagnostic_html5_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    // Inclure le script de diagnostic
    $diagnostic_file = plugin_dir_path(__FILE__) . 'diagnostic-html5.php';
    if (file_exists($diagnostic_file)) {
        ob_start();
        include $diagnostic_file;
        $output = ob_get_clean();

        wp_send_json_success(array(
            'message' => 'Diagnostic executed',
            'output' => $output
        ));
    } else {
        wp_send_json_error('Diagnostic file not found');
    }
}