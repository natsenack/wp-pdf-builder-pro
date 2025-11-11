<?php
/**
 * Endpoint AJAX pour diagnostiquer la bibliothèque média
 */

function pdf_builder_media_diagnostic_ajax() {
    // Vérifier nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error('Nonce verification failed');
    }
    
    // Vérifier permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    $diagnostic = array(
        'gd_library' => extension_loaded('gd'),
        'imagick' => extension_loaded('imagick'),
        'uploads_dir' => wp_upload_dir(),
        'attachments' => array(),
    );
    
    // Vérifier si uploads est writable
    $upload_dir = wp_upload_dir();
    $diagnostic['uploads_writable'] = is_writable($upload_dir['basedir']);
    
    // Lister les attachments
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => 10,
        'post_status'    => 'inherit'
    );
    $attachments = get_posts($args);
    
    foreach ($attachments as $att) {
        $url = wp_get_attachment_url($att->ID);
        $meta = wp_get_attachment_metadata($att->ID);
        
        $diagnostic['attachments'][] = array(
            'id' => $att->ID,
            'title' => $att->post_title,
            'url' => $url,
            'filename' => basename($url),
            'metadata' => $meta,
        );
    }
    
    // Log everything
    error_log('=== PDF BUILDER MEDIA DIAGNOSTIC ===');
    error_log('GD Library: ' . ($diagnostic['gd_library'] ? 'YES' : 'NO'));
    error_log('ImageMagick: ' . ($diagnostic['imagick'] ? 'YES' : 'NO'));
    error_log('Upload dir writable: ' . ($diagnostic['uploads_writable'] ? 'YES' : 'NO'));
    error_log('Upload dir: ' . $upload_dir['basedir']);
    error_log('Attachments count: ' . count($diagnostic['attachments']));
    foreach ($diagnostic['attachments'] as $att) {
        error_log('  - ' . $att['title'] . ' (' . $att['filename'] . ')');
    }
    error_log('=== END DIAGNOSTIC ===');
    
    wp_send_json_success($diagnostic);
}

add_action('wp_ajax_pdf_builder_media_diagnostic', 'pdf_builder_media_diagnostic_ajax');
