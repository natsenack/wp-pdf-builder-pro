<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Endpoint AJAX pour diagnostiquer la bibliothèque média
 */

function pdf_builder_media_diagnostic_ajax() {
    // Vérifier nonce et permissions
    if (!pdf_builder_verify_request_or_json_error('pdf_builder_ajax')) {
        return;
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
    $diagnostic['uploads_writable'] = is_writable($upload_dir['basedir']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
    
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
    
    
    
    
    foreach ($diagnostic['attachments'] as $att) {
        
    }
    wp_send_json_success($diagnostic);
}

add_action('wp_ajax_pdf_builder_media_diagnostic', 'pdf_builder_media_diagnostic_ajax');


