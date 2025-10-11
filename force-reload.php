<?php
// FORCE RELOAD - Vider tous les caches et redémarrer
require_once('../../../wp-load.php');

echo "<h1>FORCE RELOAD - Vidage de tous les caches</h1>";

// 1. Vider le cache objet de WordPress
wp_cache_flush();
echo "<p>✅ Cache objet WordPress vidé</p>";

// 2. Nettoyer les transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
echo "<p>✅ Transients nettoyés</p>";

// 3. Forcer le rechargement des classes
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache vidé</p>";
} else {
    echo "<p>⚠️ OPcache non disponible</p>";
}

// 4. Vider les actions et les recharger
remove_all_actions('wp_ajax_pdf_builder_load_canvas_elements');
echo "<p>✅ Actions AJAX supprimées</p>";

// 5. Forcer le rechargement du plugin
if (function_exists('pdf_builder_load_core')) {
    pdf_builder_load_core();
    echo "<p>✅ Plugin rechargé</p>";
}

// 6. Vérifier que la bonne classe est chargée
if (class_exists('PDF_Builder_Admin_New')) {
    echo "<p>✅ PDF_Builder_Admin_New chargée</p>";
} else {
    echo "<p>❌ PDF_Builder_Admin_New NON chargée</p>";
}

if (class_exists('PDF_Builder_Admin')) {
    echo "<p>⚠️ PDF_Builder_Admin encore chargée (ancienne)</p>";
} else {
    echo "<p>✅ PDF_Builder_Admin NON chargée (ancienne)</p>";
}

// 7. Vérifier les actions enregistrées
global $wp_filter;
$action = 'wp_ajax_pdf_builder_load_canvas_elements';
if (isset($wp_filter[$action])) {
    echo "<p>✅ Action AJAX enregistrée</p>";
    foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                echo "<p>   - Classe: $class</p>";
            }
        }
    }
} else {
    echo "<p>❌ Action AJAX NON enregistrée</p>";
}

echo "<hr>";
echo "<p><strong>Redémarrez votre navigateur avec Ctrl+F5 pour forcer le rechargement complet.</strong></p>";
echo "<p><a href='../wp-admin/admin.php?page=pdf-builder-editor&template_id=1'>Aller à l'éditeur PDF</a></p>";
?>