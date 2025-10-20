<?php
define("ABSPATH", __DIR__ . "/../");
define("PHPUNIT_RUNNING", true);

echo "<h1>🧪 Test Phase 7: Status Manager</h1>";
echo "<p>Test de la détection automatique des statuts WooCommerce.</p>";

// Simuler les fonctions WordPress de base
function get_option($key, $default = null) { return $default; }
function apply_filters($tag, $value) { return $value; }
function add_action() { return true; }
function add_filter() { return true; }
function wp_schedule_event() { return true; }
function wp_next_scheduled() { return false; }
function __($text, $domain = null) { return $text; }

try {
    // Charger la classe StatusManager
    require_once __DIR__ . "/../src/Managers/PDF_Builder_Status_Manager.php";

    $main_mock = new stdClass();
    $status_manager = new PDF_Builder_Status_Manager($main_mock);

    $detected_statuses = $status_manager->detect_woocommerce_statuses();
    echo "<p>Statuts détectés: " . count($detected_statuses) . "</p>";

    if (count($detected_statuses) >= 7) {
        echo "<div style=\"color: green;\">✅ SUCCÈS: Détection des statuts fonctionne!</div>";
    } else {
        echo "<div style=\"color: red;\">❌ ÉCHEC: Seulement " . count($detected_statuses) . " statuts détectés</div>";
    }
} catch (Exception $e) {
    echo "<div style=\"color: red;\">❌ ERREUR: " . $e->getMessage() . "</div>";
}
?>
