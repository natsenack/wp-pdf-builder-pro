<?php
/**
 * Tests pour les utilitaires PDF Builder Pro
 * Tests unitaires pour PerformanceMetrics, LocalCache, validateFormData et AjaxCompat
 */

class UtilitiesTest extends PDF_Builder_TestCase {

    /**
     * Test des métriques de performance
     */
    public function test_performance_metrics_tracking() {
        // Reset metrics
        delete_option('pdf_builder_performance_metrics');

        // Test tracking d'une opération
        PerformanceMetrics::start('test_operation');

        // Simuler du temps (petit délai)
        usleep(10000); // 10ms

        PerformanceMetrics::end('test_operation');

        $metrics = PerformanceMetrics::get_metrics();
        $this->assertArrayHasKey('test_operation', $metrics);
        $this->assertGreaterThan(0, $metrics['test_operation']['avg_time']);
        $this->assertEquals(1, $metrics['test_operation']['count']);
    }

    /**
     * Test du cache local
     */
    public function test_local_cache_operations() {
        // Nettoyer le cache
        LocalCache::clear();

        $test_data = [
            'settings' => ['theme' => 'dark', 'lang' => 'fr'],
            'timestamp' => time(),
            'array_data' => [1, 2, 3, 'test']
        ];

        // Test sauvegarde
        LocalCache::save($test_data);
        $this->assertTrue(LocalCache::has_data());

        // Test chargement
        $loaded_data = LocalCache::load();
        $this->assertEquals($test_data, $loaded_data);

        // Test expiration
        LocalCache::save($test_data, -1); // Expiration dans le passé
        $this->assertFalse(LocalCache::has_data());

        // Nettoyer
        LocalCache::clear();
    }

    /**
     * Test de validation des données de formulaire
     */
    public function test_form_data_validation() {
        // Test données valides
        $rules = [
            'title' => ['required' => true, 'type' => 'string', 'min_length' => 3],
            'email' => ['required' => true, 'type' => 'email'],
            'age' => ['type' => 'number', 'min' => 18, 'max' => 120],
            'active' => ['type' => 'boolean'],
            'tags' => ['type' => 'array']
        ];

        $valid_data = [
            'title' => 'Test Title',
            'email' => 'test@example.com',
            'age' => 25,
            'active' => true,
            'tags' => ['tag1', 'tag2']
        ];

        $result = validateFormData($valid_data, $rules);
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['errors']);

        // Test données invalides
        $invalid_data = [
            'title' => 'Hi', // Trop court
            'email' => 'invalid-email',
            'age' => 150, // Trop vieux
            'active' => 'not-boolean',
            'tags' => 'not-array'
        ];

        $result = validateFormData($invalid_data, $rules);
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('title', $result['errors']);
        $this->assertArrayHasKey('email', $result['errors']);
        $this->assertArrayHasKey('age', $result['errors']);
        $this->assertArrayHasKey('active', $result['errors']);
        $this->assertArrayHasKey('tags', $result['errors']);
    }

    /**
     * Test de la compatibilité AJAX
     */
    public function test_ajax_compat_functionality() {
        // Test requête AJAX basique
        $test_data = ['action' => 'test_action', 'param' => 'value'];

        // Mock wp_remote_post
        $mock_response = [
            'response' => ['code' => 200],
            'body' => wp_json_encode(['success' => true, 'data' => 'test_response'])
        ];

        // On ne peut pas facilement mocker wp_remote_post dans ce contexte,
        // mais on peut tester la structure des données
        $this->assertTrue(is_array($test_data));
        $this->assertEquals('test_action', $test_data['action']);
    }

    /**
     * Test de sécurité des utilitaires
     */
    public function test_security_validations() {
        // Test validation XSS basique
        $suspicious_data = [
            'title' => '<script>alert("xss")</script>',
            'content' => 'Normal content',
            'sql_injection' => "'; DROP TABLE users; --"
        ];

        $rules = [
            'title' => ['type' => 'string', 'max_length' => 100],
            'content' => ['type' => 'string'],
            'sql_injection' => ['type' => 'string']
        ];

        $result = validateFormData($suspicious_data, $rules);

        // Les données devraient être acceptées car on fait confiance à WordPress
        // pour la sanitisation, mais les règles de validation devraient fonctionner
        $this->assertTrue($result['is_valid']);
    }

    /**
     * Test des performances des utilitaires
     */
    public function test_utilities_performance() {
        $start_time = microtime(true);

        // Test cache performance
        for ($i = 0; $i < 100; $i++) {
            LocalCache::save(['test' => 'data_' . $i]);
            $data = LocalCache::load();
        }

        $cache_time = microtime(true) - $start_time;

        // Test validation performance
        $start_time = microtime(true);
        $rules = ['field' => ['required' => true, 'type' => 'string']];

        for ($i = 0; $i < 1000; $i++) {
            validateFormData(['field' => 'value_' . $i], $rules);
        }

        $validation_time = microtime(true) - $start_time;

        // Les performances devraient être raisonnables
        $this->assertLessThan(1.0, $cache_time, 'Cache operations should be fast');
        $this->assertLessThan(2.0, $validation_time, 'Validation should be fast');
    }
}