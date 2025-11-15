<?php
/**
 * Test rapide de l'onboarding - Version simplifiée
 */

// Simuler un environnement WordPress minimal
define('WP_PLUGIN_DIR', dirname(__FILE__) . '/plugin');
define('ABSPATH', dirname(__FILE__) . '/');

// Fonctions WordPress simulées
function get_option($key, $default = null) { return $default; }
function update_option() { return true; }
function wp_create_nonce() { return 'test_nonce'; }
function current_time() { return time(); }
function __() { return func_get_arg(0); }
function add_action() {}
function wp_enqueue_script() {}
function wp_enqueue_style() {}
function wp_localize_script() {}
function admin_url($path) { return '/wp-admin/' . $path; }
function get_current_screen() { return (object)['id' => 'toplevel_page_pdf-builder-pro']; }
function is_admin() { return true; }
function current_user_can() { return true; }
function wp_die() { die('Test terminé'); }

// Charger les classes nécessaires
require_once WP_PLUGIN_DIR . '/src/utilities/PDF_Builder_Onboarding_Manager.php';

echo "<h1>🧪 Test Rapide Onboarding</h1>";

// Tester la création de l'instance
try {
    $onboarding = PDF_Builder_Onboarding_Manager::get_instance();
    echo "<p style='color:green;'>✅ Instance créée avec succès</p>";

    // Tester les méthodes principales
    $steps = $onboarding->get_onboarding_steps();
    echo "<p>Nombre d'étapes: " . count($steps) . "</p>";

    // Tester le rendu du wizard
    ob_start();
    $onboarding->render_onboarding_wizard();
    $output = ob_get_clean();

    if (strpos($output, 'pdf-builder-onboarding-modal') !== false) {
        echo "<p style='color:green;'>✅ Modal rendu correctement</p>";
    } else {
        echo "<p style='color:red;'>❌ Problème avec le rendu du modal</p>";
    }

    echo "<details><summary>Voir le HTML généré</summary><pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre></details>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='diagnostic-onboarding.php'>← Retour au diagnostic complet</a></p>";