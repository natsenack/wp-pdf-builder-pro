<?php
// Script de diagnostic pour vérifier les modifications du nonce
// Accessible via: https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/diagnostic.php?run=1

if (isset($_GET['run']) && $_GET['run'] === '1') {
    error_log('=== DIAGNOSTIC PDF BUILDER === START');

    // Vérifier si la fonction ajax_load_canvas_elements existe et contient nos modifications
    $admin_file = __DIR__ . '/includes/classes/class-pdf-builder-admin.php';
    if (file_exists($admin_file)) {
        $content = file_get_contents($admin_file);

        error_log('✅ Fichier admin trouvé');

        if (strpos($content, '_cachebust_') !== false) {
            error_log('✅ Modifications _cachebust_ présentes');
        } else {
            error_log('❌ Modifications _cachebust_ ABSENTES');
        }

        if (strpos($content, 'wp_create_nonce(\'pdf_builder_canvas_v3_') !== false) {
            error_log('✅ Nouveau nonce trouvé');
        } else {
            error_log('❌ Nouveau nonce ABSENT');
        }

        // Compter les occurrences
        $cachebust_count = substr_count($content, '_cachebust_');
        error_log("Nombre d'occurrences _cachebust_: $cachebust_count");

    } else {
        error_log("❌ Fichier admin NON trouvé: $admin_file");
    }

    error_log('=== TEST NONCE ===');
    // Tester la génération du nonce
    if (function_exists('wp_create_nonce')) {
        $test_nonce = wp_create_nonce('pdf_builder_canvas_v3_' . 1 . '_cachebust_' . time());
        error_log("Test nonce généré: " . substr($test_nonce, 0, 10) . "...");
        error_log("Longueur: " . strlen($test_nonce));
    } else {
        error_log('❌ Fonction wp_create_nonce non disponible');
    }

    error_log('=== FIN DIAGNOSTIC ===');

    // Afficher un message simple pour confirmer l'exécution
    echo 'Diagnostic executed - check WordPress logs at /wp-content/debug.log';
    exit;
}

// Si pas de paramètre run, afficher un message d'aide
echo '<h1>PDF Builder Diagnostic</h1>';
echo '<p>Ajoutez ?run=1 à l\'URL pour exécuter le diagnostic</p>';
echo '<p>Exemple: <code>' . $_SERVER['REQUEST_URI'] . '?run=1</code></p>';
?>