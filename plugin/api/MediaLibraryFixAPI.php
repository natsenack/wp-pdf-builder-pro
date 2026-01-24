<?php
/**
 * Fix pour afficher les images dans la bibliothèque média WordPress
 * Même sans GD/ImageMagick, on forcer les URL des images
 */

function pdf_builder_fix_media_library_display() {
    // Hook pour filtrer la réponse media library
    add_filter('wp_prepare_attachment_for_js', function($response, $attachment, $meta) {
        // Forcer l'URL de la thumbnail si elle existe pas
        if (!isset($response['thumb']) || empty($response['thumb'])) {
            // Utiliser l'URL complète comme fallback
            $response['thumb'] = $response['url'];
        }
        
        // Ajouter les sizes si manquantes
        if (!isset($response['sizes'])) {
            $response['sizes'] = array(
                'full' => array(
                    'url' => $response['url'],
                    'width' => isset($meta['width']) ? $meta['width'] : 0,
                    'height' => isset($meta['height']) ? $meta['height'] : 0,
                )
            );
        }
        
        return $response;
    }, 10, 3);
    
    // Force les métadonnées d'image si manquantes
    add_filter('wp_get_attachment_metadata', function($data, $attachment_id) {
        if (empty($data) || !isset($data['width'])) {
            $url = wp_get_attachment_url($attachment_id);
            if ($url) {
                // Essayer de déterminer les dimensions si possible
                $size = @getimagesize($url);
                if ($size) {
                    $data['width'] = $size[0];
                    $data['height'] = $size[1];
                } else {
                    // Valeurs par défaut si on peut pas les déterminer
                    $data['width'] = 800;
                    $data['height'] = 600;
                }
            }
        }
        return $data;
    }, 10, 2);
}

// Appeler le hook au chargement admin
add_action('admin_init', 'pdf_builder_fix_media_library_display');

// Aussi charger côté frontend si besoin
add_action('init', 'pdf_builder_fix_media_library_display');
