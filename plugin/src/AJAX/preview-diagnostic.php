<?php
/**
 * Diagnostic: Vérifier le contenu JSON en BDD et la structure
 * 
 * Usage: http://website.com/wp-admin/?action=pdf_builder_diagnostic
 */

if (!defined('ABSPATH')) {
    die('No direct access');
}

add_action('wp_ajax_pdf_builder_diagnostic', function() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Permission denied']);
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';
    
    // Récupérer tous les templates
    $templates = $wpdb->get_results("SELECT id, name, LENGTH(template_data) as data_length FROM $table ORDER BY id DESC LIMIT 5");
    
    $result = [];
    
    foreach ($templates as $t) {
        $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $t->id));
        
        if (!$template) continue;
        
        $template_data = json_decode($template->template_data, true);
        
        if (!$template_data) {
            $result[] = [
                'id' => $t->id,
                'name' => $t->name,
                'data_length' => $t->data_length,
                'error' => 'Invalid JSON: ' . json_last_error_msg()
            ];
            continue;
        }
        
        $elements = $template_data['elements'] ?? [];
        $canvas = $template_data['canvas'] ?? [];
        
        $result[] = [
            'id' => $t->id,
            'name' => $t->name,
            'data_length' => $t->data_length,
            'elements_count' => count($elements),
            'element_types' => array_map(function($el) { 
                return $el['type'] . ' (x=' . $el['x'] . ', y=' . $el['y'] . ')'; 
            }, array_slice($elements, 0, 5)),
            'canvas' => $canvas,
            'first_element_raw' => isset($elements[0]) ? $elements[0] : null
        ];
    }
    
    wp_send_json_success(['templates' => $result]);
});

add_action('wp_ajax_nopriv_pdf_builder_diagnostic', function() {
    wp_send_json_error(['message' => 'Permission denied']);
});
