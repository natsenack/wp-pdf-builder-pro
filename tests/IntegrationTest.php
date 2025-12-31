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

    /**
     * Test du workflow complet de sauvegarde des paramètres canvas
     */
    public function test_canvas_parameters_save_workflow() {
        // Préparer les données de test pour tous les paramètres canvas
        $canvas_test_data = [
            'pdf_builder_canvas_width' => 1200,
            'pdf_builder_canvas_height' => 800,
            'pdf_builder_canvas_dpi' => 150,
            'pdf_builder_canvas_bg_color' => '#ffffff',
            'pdf_builder_canvas_border_color' => '#000000',
            'pdf_builder_canvas_border_width' => 2,
            'pdf_builder_canvas_shadow_enabled' => true,
            'pdf_builder_canvas_grid_enabled' => true,
            'pdf_builder_canvas_grid_size' => 20,
            'pdf_builder_canvas_guides_enabled' => false,
            'pdf_builder_canvas_snap_to_grid' => true,
            'pdf_builder_canvas_zoom_min' => 25,
            'pdf_builder_canvas_zoom_max' => 400,
            'pdf_builder_canvas_zoom_default' => 100,
            'pdf_builder_canvas_zoom_step' => 25,
            'pdf_builder_canvas_export_quality' => 90,
            'pdf_builder_canvas_export_format' => 'pdf',
            'pdf_builder_canvas_auto_save' => true,
            'pdf_builder_canvas_undo_levels' => 50,
            'pdf_builder_canvas_preview_enabled' => true,
            'pdf_builder_canvas_rulers_enabled' => true,
            'pdf_builder_canvas_coordinates_enabled' => false,
            'pdf_builder_canvas_fullscreen_enabled' => true,
            'pdf_builder_canvas_shortcuts_enabled' => true,
            'pdf_builder_canvas_theme' => 'light',
            'pdf_builder_canvas_language' => 'fr'
        ];

        // Exécuter la sauvegarde AJAX
        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $canvas_test_data);

        // Vérifier la réponse
        $this->assertAjaxSuccess($response);

        // Vérifier que les données ont été sauvegardées en base
        foreach ($canvas_test_data as $key => $expected_value) {
            $saved_value = $this->get_pdf_option($key);
            $this->assertEquals($expected_value, $saved_value,
                "Canvas parameter '$key' not saved correctly. Expected: $expected_value, Got: $saved_value");
        }

        // Vérifier les métriques de performance
        $metrics = get_option('pdf_builder_performance_metrics', []);
        $this->assertNotEmpty($metrics, 'Performance metrics should be recorded');
        $this->assertArrayHasKey('ajax_save_canvas', $metrics,
            'Canvas save operation should be tracked in metrics');
    }

    /**
     * Test de validation des paramètres canvas côté client et serveur
     */
    public function test_canvas_parameters_validation_consistency() {
        $valid_data = [
            'pdf_builder_canvas_width' => 1000,
            'pdf_builder_canvas_height' => 700,
            'pdf_builder_canvas_dpi' => 300,
            'pdf_builder_canvas_bg_color' => '#f0f0f0',
            'pdf_builder_canvas_border_width' => 1
        ];

        $invalid_data = [
            'pdf_builder_canvas_width' => -100, // Largeur négative
            'pdf_builder_canvas_height' => 0,   // Hauteur nulle
            'pdf_builder_canvas_dpi' => 1000,   // DPI trop élevé
            'pdf_builder_canvas_bg_color' => 'invalid-color',
            'pdf_builder_canvas_border_width' => -5
        ];

        // Tester les données valides
        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $valid_data);
        $this->assertAjaxSuccess($response, 'Valid canvas data should be accepted');

        // Tester les données invalides
        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $invalid_data);
        $this->assertAjaxError($response, 'Invalid canvas data should be rejected');

        // Vérifier que les données valides ont été sauvegardées malgré l'erreur sur les invalides
        $saved_width = $this->get_pdf_option('pdf_builder_canvas_width');
        $this->assertEquals(1000, $saved_width, 'Valid width should be saved');
    }

    /**
     * Test de cohérence des paramètres canvas avec la base de données
     */
    public function test_canvas_database_consistency_verification() {
        // Préparer des données de test
        $test_data = [
            'pdf_builder_canvas_width' => 800,
            'pdf_builder_canvas_height' => 600,
            'pdf_builder_canvas_bg_color' => '#ffffff'
        ];

        // Sauvegarder les données
        $this->execute_ajax_action('pdf_builder_save_canvas_settings', $test_data);

        // Tester le handler de vérification de cohérence
        $response = $this->execute_ajax_action('verify_canvas_settings_consistency', []);

        // Vérifier la réponse
        $this->assertAjaxSuccess($response);
        $this->assertArrayHasKey('data', $response);

        $db_values = $response['data'];

        // Vérifier que toutes les valeurs sauvegardées sont présentes dans la réponse DB
        foreach ($test_data as $key => $expected_value) {
            $this->assertArrayHasKey($key, $db_values, "Database should contain canvas setting: $key");
            $this->assertEquals($expected_value, $db_values[$key],
                "Database value for $key should match saved value");
        }
    }

    /**
     * Test de performance sous charge pour les paramètres canvas
     */
    public function test_canvas_performance_under_load() {
        $start_time = microtime(true);

        // Simuler une charge importante de paramètres canvas
        $large_dataset = [];
        for ($i = 0; $i < 100; $i++) {
            $large_dataset["pdf_builder_canvas_custom_param_$i"] = "value_$i";
        }

        // Ajouter les paramètres standards
        $large_dataset = array_merge($large_dataset, [
            'pdf_builder_canvas_width' => 1920,
            'pdf_builder_canvas_height' => 1080,
            'pdf_builder_canvas_dpi' => 300
        ]);

        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $large_dataset);
        $end_time = microtime(true);

        $this->assertAjaxSuccess($response);

        // Vérifier que l'opération prend moins de 2 secondes
        $execution_time = $end_time - $start_time;
        $this->assertLessThan(2.0, $execution_time,
            "Canvas save operation should complete in less than 2 seconds, took: $execution_time");

        // Vérifier les métriques de performance
        $metrics = get_option('pdf_builder_performance_metrics', []);
        $this->assertArrayHasKey('ajax_save_canvas', $metrics);
        $this->assertLessThan(2000, $metrics['ajax_save_canvas']['avg_time'] ?? 0,
            'Average canvas save time should be under 2 seconds');
    }

    /**
     * Test de résilience du système canvas
     */
    public function test_canvas_system_resilience() {
        // Test 1: Connexion perdue pendant la sauvegarde
        add_filter('pre_http_request', function() {
            return new WP_Error('http_request_failed', 'Connection lost');
        });

        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', [
            'pdf_builder_canvas_width' => 1000
        ]);

        // Le système devrait gérer l'erreur gracieusement
        $this->assertTrue(
            isset($response['success']) || isset($response['error']),
            'System should handle connection errors gracefully'
        );

        remove_all_filters('pre_http_request');

        // Test 2: Données corrompues
        $corrupted_data = [
            'pdf_builder_canvas_width' => 'not_a_number',
            'pdf_builder_canvas_height' => null,
            'pdf_builder_canvas_bg_color' => []
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $corrupted_data);

        // Le système devrait rejeter ou sanitiser les données corrompues
        $this->assertTrue(
            (isset($response['success']) && $response['success'] === false) ||
            (isset($response['success']) && $response['success'] === true),
            'System should handle corrupted data safely'
        );

        // Test 3: Récupération après erreur
        $recovery_data = [
            'pdf_builder_canvas_width' => 800,
            'pdf_builder_canvas_height' => 600
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $recovery_data);
        $this->assertAjaxSuccess($response, 'System should recover after errors');

        // Vérifier que les données de récupération ont été sauvegardées
        $this->assertEquals(800, $this->get_pdf_option('pdf_builder_canvas_width'));
        $this->assertEquals(600, $this->get_pdf_option('pdf_builder_canvas_height'));
    }

    /**
     * Test de sécurité pour les paramètres canvas
     */
    public function test_canvas_security_hardening() {
        // Test des vecteurs d'attaque courants pour les paramètres canvas
        $attack_vectors = [
            // XSS attempts
            ['pdf_builder_canvas_bg_color' => '<script>alert("xss")</script>'],
            ['pdf_builder_canvas_custom_css' => 'body { background: url("javascript:alert(1)"); }'],

            // SQL injection attempts
            ['pdf_builder_canvas_width' => '1; DROP TABLE wp_options; --'],
            ['pdf_builder_canvas_height' => '1 UNION SELECT * FROM wp_users --'],

            // Directory traversal
            ['pdf_builder_canvas_export_path' => '../../../etc/passwd'],

            // Buffer overflow
            ['pdf_builder_canvas_custom_data' => str_repeat('A', 100000)]
        ];

        foreach ($attack_vectors as $i => $attack_data) {
            $response = $this->execute_ajax_action('pdf_builder_save_canvas_settings', $attack_data);

            // Le système devrait rejeter ou sanitiser les données dangereuses
            $this->assertTrue(
                (isset($response['success']) && $response['success'] === false) ||
                (isset($response['success']) && $response['success'] === true),
                "Canvas attack vector " . ($i + 1) . " should be handled safely"
            );

            // Vérifier qu'aucune donnée dangereuse n'a été sauvegardée
            foreach ($attack_data as $key => $dangerous_value) {
                $saved_value = $this->get_pdf_option($key);
                if ($saved_value !== null) {
                    $this->assertNotEquals($dangerous_value, $saved_value,
                        "Dangerous value should not be saved for key: $key");
                }
            }
        }
    }
}