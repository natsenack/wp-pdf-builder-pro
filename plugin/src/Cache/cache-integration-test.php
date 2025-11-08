<?php
/**
 * Script de test pour vérifier l'intégration des paramètres de cache
 * Ce script teste que tous les systèmes de cache respectent les paramètres configurés
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class CacheIntegrationTest {

    public static function runTests() {
        echo "<h2>Test d'intégration du système de cache</h2>";

        // Test 1: Vérifier les paramètres de cache
        self::testCacheSettings();

        // Test 2: Tester PDF_Builder_Cache_Manager
        self::testPDFBuilderCacheManager();

        // Test 3: Tester RendererCache
        self::testRendererCache();

        // Test 4: Tester WooCommerceCache
        self::testWooCommerceCache();

        echo "<p><strong>Tous les tests sont terminés.</strong></p>";
    }

    private static function testCacheSettings() {
        echo "<h3>Test 1: Paramètres de cache</h3>";

        $settings = get_option('pdf_builder_settings', []);
        $cache_enabled = !empty($settings['cache_enabled']);
        $cache_ttl = intval($settings['cache_ttl'] ?? 3600);

        echo "<p>Cache activé: <strong>" . ($cache_enabled ? 'OUI' : 'NON') . "</strong></p>";
        echo "<p>TTL configuré: <strong>{$cache_ttl} secondes</strong></p>";
    }

    private static function testPDFBuilderCacheManager() {
        echo "<h3>Test 2: PDF_Builder_Cache_Manager</h3>";

        $cache_manager = PDF_Builder_Cache_Manager::getInstance();

        echo "<p>Cache activé dans le manager: <strong>" . ($cache_manager->isEnabled() ? 'OUI' : 'NON') . "</strong></p>";

        // Test set/get
        $test_key = 'test_integration_' . time();
        $test_value = 'valeur de test ' . time();

        $set_result = $cache_manager->set($test_key, $test_value);
        echo "<p>Set opération: <strong>" . ($set_result ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";

        $get_result = $cache_manager->get($test_key);
        echo "<p>Get opération: <strong>" . ($get_result === $test_value ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";

        // Nettoyer
        $cache_manager->delete($test_key);
    }

    private static function testRendererCache() {
        echo "<h3>Test 3: RendererCache</h3>";

        echo "<p>Cache activé dans RendererCache: <strong>" . (\PDF_Builder\Cache\RendererCache::isCacheEnabled() ? 'OUI' : 'NON') . "</strong></p>";
        echo "<p>TTL dans RendererCache: <strong>" . \PDF_Builder\Cache\RendererCache::getCacheTTL() . " secondes</strong></p>";

        // Test set/get
        $test_key = 'renderer_test_' . time();
        $test_value = ['test' => 'data', 'timestamp' => time()];

        $set_result = \PDF_Builder\Cache\RendererCache::set($test_key, $test_value);
        echo "<p>Set opération: <strong>" . ($set_result ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";

        $get_result = \PDF_Builder\Cache\RendererCache::get($test_key);
        $get_success = $get_result && isset($get_result['test']) && $get_result['test'] === 'data';
        echo "<p>Get opération: <strong>" . ($get_success ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";
    }

    private static function testWooCommerceCache() {
        echo "<h3>Test 4: WooCommerceCache</h3>";

        echo "<p>Cache activé dans WooCommerceCache: <strong>" . (\PDF_Builder\Cache\WooCommerceCache::isCacheEnabled() ? 'OUI' : 'NON') . "</strong></p>";
        echo "<p>TTL dans WooCommerceCache: <strong>" . \PDF_Builder\Cache\WooCommerceCache::getCacheTTL() . " secondes</strong></p>";

        // Test set/get customer data
        $test_user_id = 999999; // ID qui n'existe probablement pas
        $test_data = ['name' => 'Test User', 'email' => 'test@example.com'];

        $set_result = \PDF_Builder\Cache\WooCommerceCache::setCustomerData($test_user_id, $test_data);
        echo "<p>Set customer data: <strong>" . ($set_result ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";

        $get_result = \PDF_Builder\Cache\WooCommerceCache::getCustomerData($test_user_id);
        $get_success = $get_result && isset($get_result['name']) && $get_result['name'] === 'Test User';
        echo "<p>Get customer data: <strong>" . ($get_success ? 'SUCCÈS' : 'ÉCHEC') . "</strong></p>";
    }
}

// Fonction de test AJAX simplifiée
function pdf_builder_simple_test_ajax() {
    wp_send_json_success('<p>✅ Test AJAX simplifié réussi !</p>');
}
// Hook pour utilisateurs authentifiés ET non-authentifiés
add_action('wp_ajax_pdf_builder_simple_test', 'pdf_builder_simple_test_ajax');
add_action('wp_ajax_nopriv_pdf_builder_simple_test', 'pdf_builder_simple_test_ajax');

// Fonction pour exécuter les tests via AJAX
function pdf_builder_cache_test_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_cache_test')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    ob_start();
    CacheIntegrationTest::runTests();
    $output = ob_get_clean();

    wp_send_json_success($output);
}
add_action('wp_ajax_pdf_builder_cache_test', 'pdf_builder_cache_test_ajax');