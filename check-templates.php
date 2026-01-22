<?php
/**
 * Script pour vérifier les templates disponibles
 */

// Charger WordPress
require_once '/var/www/nats/data/www/threeaxe.fr/wp-load.php';

// Vérifier les posts de type pdf_template
$templates = get_posts([
    'post_type' => 'pdf_template',
    'posts_per_page' => -1,
]);

echo "=== Templates PDF Builder (post_type: pdf_template) ===\n";
if (empty($templates)) {
    echo "❌ Aucun template de type 'pdf_template' trouvé!\n";
} else {
    foreach ($templates as $template) {
        echo "✓ ID: {$template->ID}, Title: {$template->post_title}\n";
        $meta = get_post_meta($template->ID, '_pdf_template_data', true);
        if ($meta) {
            echo "  - Meta key '_pdf_template_data' existe\n";
        } else {
            echo "  - ❌ Meta key '_pdf_template_data' manquante\n";
        }
    }
}

echo "\n=== Vérification du post ID=2 ===\n";
$post = get_post(2, ARRAY_A);
if ($post) {
    echo "Post trouvé:\n";
    echo "- Type: {$post['post_type']}\n";
    echo "- Title: {$post['post_title']}\n";
    echo "- Status: {$post['post_status']}\n";
    
    $metas = get_post_meta(2);
    echo "\nClés de métadonnées du post ID=2:\n";
    foreach (array_keys($metas) as $meta_key) {
        echo "- $meta_key\n";
    }
} else {
    echo "Post ID=2 non trouvé\n";
}
?>
