<?php
/**
 * Diagnostic pour la bibliothèque média WordPress
 * Ajouter ce code au bootstrap.php pour debugger les problèmes de thumbs
 */

function pdf_builder_check_media_library() {
    error_log('=== PDF BUILDER MEDIA DIAGNOSTIC ===');
    
    // Vérifier si GD est disponible
    $gd_info = extension_loaded('gd');
    error_log('GD Library: ' . ($gd_info ? 'YES' : 'NO'));
    
    // Vérifier ImageMagick
    $imagick = extension_loaded('imagick');
    error_log('ImageMagick: ' . ($imagick ? 'YES' : 'NO'));
    
    // Vérifier uploads writable
    $upload_dir = wp_upload_dir();
    error_log('Upload basedir: ' . $upload_dir['basedir']);
    error_log('Upload dir writable: ' . (is_writable($upload_dir['basedir']) ? 'YES' : 'NO'));
    
    // Lister les fichiers uploadés
    $files = glob($upload_dir['basedir'] . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    error_log('Uploaded files count: ' . count($files));
    if (!empty($files)) {
        foreach (array_slice($files, 0, 5) as $file) {
            error_log('  - ' . basename($file));
        }
    }
    
    // Vérifier les attachments en DB
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => 5,
        'post_status'    => 'inherit'
    );
    $attachments = get_posts($args);
    error_log('Attachments in DB: ' . count($attachments));
    foreach ($attachments as $attachment) {
        error_log('  - ID: ' . $attachment->ID . ', Title: ' . $attachment->post_title);
        $meta = wp_get_attachment_metadata($attachment->ID);
        if ($meta) {
            error_log('    Metadata: ' . print_r($meta, true));
        }
    }
}

// Appeler au chargement de la page du PDF builder
add_action('wp_enqueue_scripts', function() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false) {
        pdf_builder_check_media_library();
    }
}, 5);
