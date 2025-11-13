<?php
/**
 * AJAX Handler for PDF Builder Pro
 * Direct AJAX endpoint to bypass WordPress admin-ajax.php loading issues
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
$step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
$data = isset($_POST['data']) ? $_POST['data'] : array();

error_log('PDF Builder Pro: Direct AJAX handler called - action: ' . $action . ', step: ' . $step);

$response = array('success' => false);

if ($action === 'pdf_builder_wizard_step') {
    switch ($step) {
        case 'save_company':
            $response = pdf_builder_ajax_save_company_data($data);
            break;
        case 'create_template':
            $response = pdf_builder_ajax_create_template();
            break;
    }
} elseif ($action === 'test_ajax') {
    $response = array('success' => true, 'message' => 'Direct AJAX works');
}

wp_send_json($response);
?>