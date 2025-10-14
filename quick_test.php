<?php
/**
 * Script de débogage rapide - Version simplifiée
 */

// Simuler un environnement WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

require_once('../../../wp-load.php');

echo "<h1>🔧 Test rapide - Erreur 500 PDF Preview</h1>";

// Test direct de la méthode
echo "<h2>Test de generate_order_pdf</h2>";

try {
    // Créer une instance de PDF_Builder_Admin
    $admin = PDF_Builder_Admin::getInstance();

    if ($admin && method_exists($admin, 'generate_order_pdf')) {
        echo "<p class='success'>✅ Méthode generate_order_pdf existe dans PDF_Builder_Admin</p>";

        // Tester avec la commande 9275
        $result = $admin->generate_order_pdf(9275, 0, true);

        if (is_wp_error($result)) {
            echo "<p class='error'>❌ Erreur méthode: " . $result->get_error_message() . "</p>";
        } else {
            echo "<p class='success'>✅ Méthode fonctionne: <a href='$result' target='_blank'>Voir PDF</a></p>";
        }
    } else {
        echo "<p class='error'>❌ Méthode generate_order_pdf n'existe pas</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p class='error'>❌ Erreur fatale: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Si la méthode fonctionne, l'erreur 500 est corrigée !</strong></p>";
?>

<style>
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
</style>