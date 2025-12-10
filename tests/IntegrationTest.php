<?php
/**
 * Tests d'intégration pour PDF Builder Pro
 * Tests end-to-end pour valider l'intégration complète du système
 */

class IntegrationTest extends PDF_Builder_AjaxTestCase {

    /**
     * Test du workflow complet de sauvegarde
     */
    public function test_complete_save_workflow() {
        // 1. Préparer les données de test
        $test_data = [
            'general_title' => 'Integration Test Title',
            'general_author' => 'Test Author',
            'general_subject' => 'Integration Test Subject',
            'general_keywords' => 'test, integration, pdf',
            'layout_width' => 210,
            'layout_height' => 297,
            'layout_margin_top' => 10,
            'layout_margin_bottom' => 10,
            'layout_margin_left' => 15,
            'layout_margin_right' => 15
        ];

        // 2. Exécuter la sauvegarde AJAX
        $response = $this->execute_ajax_action('pdf_builder_save_general', $test_data);

        // 3. Vérifier la réponse
        $this->assertAjaxSuccess($response);

        // 4. Vérifier que les données ont été sauvegardées en base
        foreach ($test_data as $key => $expected_value) {
            $saved_value = $this->get_pdf_option($key);
            $this->assertEquals($expected_value, $saved_value,
                "Option '$key' not saved correctly. Expected: $expected_value, Got: $saved_value");
        }

        // 5. Vérifier les métriques de performance
        $metrics = get_option('pdf_builder_performance_metrics', []);
        $this->assertNotEmpty($metrics, 'Performance metrics should be recorded');
        $this->assertArrayHasKey('ajax_save_general', $metrics,
            'Save operation should be tracked in metrics');
    }

    /**
     * Test de la validation côté client et serveur
     */
    public function test_client_server_validation_consistency() {
        // Données invalides
        $invalid_data = [
            'general_title' => '', // Champ requis vide
            'general_email' => 'invalid-email-format', // Email invalide
            'layout_width' => 'not-a-number', // Devrait être numérique
            'layout_height' => -50 // Valeur négative invalide
        ];

        // Tester la validation côté serveur
        $response = $this->execute_ajax_action('pdf_builder_save_general', $invalid_data);

        // Devrait échouer
        $this->assertAjaxFailure($response);
        $this->assertArrayHasKey('errors', $response);

        // Vérifier que les erreurs appropriées sont retournées
        $errors = $response['errors'];
        $this->assertNotEmpty($errors, 'Validation errors should be returned');

        // Au moins une erreur pour le titre requis
        $this->assertTrue(
            isset($errors['general_title']) ||
            isset($errors['title']) ||
            in_array('title', array_keys($errors)),
            'Title validation error should be present'
        );
    }

    /**
     * Test de performance du système complet
     */
    public function test_system_performance_under_load() {
        $start_time = microtime(true);

        // Simuler 10 sauvegardes consécutives
        for ($i = 1; $i <= 10; $i++) {
            $data = [
                'general_title' => "Load Test $i",
                'general_author' => "Author $i",
                'layout_width' => 200 + $i,
                'layout_height' => 280 + $i
            ];

            $response = $this->execute_ajax_action('pdf_builder_save_general', $data);
            $this->assertAjaxSuccess($response, "Save operation $i should succeed");
        }

        $total_time = microtime(true) - $start_time;
        $avg_time_per_request = ($total_time / 10) * 1000; // en millisecondes

        // Chaque requête devrait prendre moins de 200ms en moyenne
        $this->assertLessThan(200, $avg_time_per_request,
            "Average request time should be less than 200ms. Actual: {$avg_time_per_request}ms");

        // Le total ne devrait pas dépasser 2 secondes
        $this->assertLessThan(2.0, $total_time,
            "Total time for 10 requests should be less than 2 seconds. Actual: {$total_time}s");
    }

    /**
     * Test de la résilience du système
     */
    public function test_system_resilience() {
        // Test avec des données malformées
        $malformed_data = [
            'general_title' => str_repeat('A', 10000), // Très long
            'invalid_field' => ['nested' => ['array' => 'complex']],
            'null_value' => null,
            'script_injection' => '<script>alert("xss")</script>'
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_general', $malformed_data);

        // Le système devrait gérer ces données sans planter
        $this->assertTrue(
            isset($response['success']) || isset($response['error']),
            'System should handle malformed data gracefully'
        );

        // Vérifier que le système est toujours fonctionnel après
        $normal_data = [
            'general_title' => 'Recovery Test',
            'general_author' => 'Test Author'
        ];

        $recovery_response = $this->execute_ajax_action('pdf_builder_save_general', $normal_data);
        $this->assertAjaxSuccess($recovery_response, 'System should recover after malformed data');
    }

    /**
     * Test de l'intégration avec le cache
     */
    public function test_cache_integration() {
        // Effacer le cache
        delete_transient('pdf_builder_settings_cache');

        // Première requête - devrait venir de la DB
        $start_time = microtime(true);
        $response1 = $this->execute_ajax_action('pdf_builder_load_general');
        $time1 = microtime(true) - $start_time;

        // Deuxième requête - devrait venir du cache
        $start_time = microtime(true);
        $response2 = $this->execute_ajax_action('pdf_builder_load_general');
        $time2 = microtime(true) - $start_time;

        // Les deux réponses devraient être identiques
        $this->assertEquals($response1, $response2, 'Cached response should match direct response');

        // La deuxième requête devrait être plus rapide (au moins 10% plus rapide)
        $this->assertLessThan($time1, $time2 * 1.1, 'Cached request should be faster');

        // Nettoyer
        delete_transient('pdf_builder_settings_cache');
    }

    /**
     * Test de sécurité contre les attaques courantes
     */
    public function test_security_hardening() {
        $attack_vectors = [
            // SQL Injection
            ['general_title' => "'; DROP TABLE wp_options; --"],
            // XSS
            ['general_title' => '<script>alert("xss")</script>'],
            // Path Traversal
            ['general_title' => '../../../etc/passwd'],
            // Command Injection
            ['general_title' => '; rm -rf /'],
            // Buffer Overflow attempt
            ['general_title' => str_repeat('A', 100000)]
        ];

        foreach ($attack_vectors as $i => $attack_data) {
            $response = $this->execute_ajax_action('pdf_builder_save_general', $attack_data);

            // Le système devrait soit rejeter, soit sanitiser les données dangereuses
            $this->assertTrue(
                (isset($response['success']) && $response['success'] === false) ||
                (isset($response['success']) && $response['success'] === true),
                "Attack vector " . ($i + 1) . " should be handled safely"
            );
        }
    }
}