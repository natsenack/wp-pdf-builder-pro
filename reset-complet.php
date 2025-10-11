<?php
// RESET COMPLET - Supprimer toutes les instances et recharger
require_once('../../../wp-load.php');

echo "<h1>RESET COMPLET DU PLUGIN</h1>";

// 1. Supprimer toutes les instances globales
echo "<h2>1. Suppression des instances globales</h2>";
global $pdf_builder_admin_instance, $pdf_builder_core_instance;
unset($pdf_builder_admin_instance);
unset($pdf_builder_core_instance);
echo "<p>✅ Instances globales supprimées</p>";

// 2. Supprimer toutes les actions AJAX
echo "<h2>2. Suppression des actions AJAX</h2>";
remove_all_actions('wp_ajax_pdf_builder_load_canvas_elements');
remove_all_actions('admin_enqueue_scripts');
remove_all_actions('wp_enqueue_scripts');
echo "<p>✅ Actions AJAX supprimées</p>";

// 3. Vider les caches
echo "<h2>3. Vidage des caches</h2>";
wp_cache_flush();
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache vidé</p>";
}

// 4. Supprimer les classes déjà chargées (si possible)
echo "<h2>4. Rechargement des classes</h2>";
if (class_exists('PDF_Builder_Admin', false)) {
    echo "<p>⚠️ PDF_Builder_Admin était chargée</p>";
}
if (class_exists('PDF_Builder_Admin_New', false)) {
    echo "<p>⚠️ PDF_Builder_Admin_New était chargée</p>";
}

// Forcer le rechargement du fichier
$plugin_dir = plugin_dir_path(__FILE__);
$new_admin_file = $plugin_dir . 'includes/classes/class-pdf-builder-admin-new.php';
$core_file = $plugin_dir . 'includes/classes/PDF_Builder_Core.php';

if (file_exists($new_admin_file)) {
    require_once $new_admin_file;
    echo "<p>✅ class-pdf-builder-admin-new.php rechargé</p>";
}

if (file_exists($core_file)) {
    require_once $core_file;
    echo "<p>✅ PDF_Builder_Core.php rechargé</p>";
}

// 5. Recréer l'instance core
echo "<h2>5. Recréation de l'instance core</h2>";
if (class_exists('PDF_Builder_Core')) {
    $core = PDF_Builder_Core::getInstance();
    echo "<p>✅ Instance PDF_Builder_Core recréée</p>";
} else {
    echo "<p>❌ Classe PDF_Builder_Core non trouvée</p>";
}

// 6. Vérification finale
echo "<h2>6. Vérification finale</h2>";
if (class_exists('PDF_Builder_Admin_New')) {
    echo "<p>✅ PDF_Builder_Admin_New chargée</p>";
    $instance = PDF_Builder_Admin_New::getInstance(null);
    echo "<p>✅ Instance PDF_Builder_Admin_New créée</p>";
} else {
    echo "<p>❌ PDF_Builder_Admin_New NON chargée</p>";
}

if (class_exists('PDF_Builder_Admin')) {
    echo "<p>❌ PDF_Builder_Admin encore chargée (PROBLÈME)</p>";
} else {
    echo "<p>✅ PDF_Builder_Admin NON chargée</p>";
}

// 7. Générer le nouveau nonce
echo "<h2>7. Nouveau nonce généré</h2>";
$user_id = get_current_user_id();
$new_nonce = wp_create_nonce('pdf_builder_canvas_v4_' . $user_id);
echo "<p><strong>Nouveau nonce:</strong> $new_nonce</p>";
echo "<p><strong>Ancien nonce (JavaScript):</strong> 1cff71fef9</p>";
echo "<p><strong>Différent:</strong> " . ($new_nonce !== '1cff71fef9' ? "✅ OUI" : "❌ NON") . "</p>";

echo "<hr>";
echo "<h2>INSTRUCTIONS</h2>";
echo "<p>1. <strong>Redémarrez complètement votre navigateur</strong> (fermez et rouvrez)</p>";
echo "<p>2. Videz le cache du navigateur (Ctrl+Shift+R)</p>";
echo "<p>3. Allez à l'éditeur PDF Builder</p>";
echo "<p>4. Vérifiez que pdfBuilderAjax.nonce est maintenant '$new_nonce'</p>";
echo "<p>5. Testez le chargement des éléments canvas</p>";
?>