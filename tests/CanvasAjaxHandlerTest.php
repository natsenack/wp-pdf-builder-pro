<?php
/**
 * Tests pour les handlers AJAX spécifiques aux paramètres canvas
 * Tests unitaires pour valider les fonctionnalités AJAX canvas
 */

class CanvasAjaxHandlerTest extends PDF_Builder_AjaxTestCase {

    /**
     * Test du handler de vérification de cohérence des paramètres canvas
     */
    public function test_verify_canvas_settings_consistency_handler() {
        // Préparer des données de test
        $test_settings = [
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

        // Sauvegarder les paramètres dans la base de données
        update_option('pdf_builder_settings', $test_settings);

        // Simuler la requête AJAX
        $_POST['action'] = 'verify_canvas_settings_consistency';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');

        // Se connecter en tant qu'admin
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        // Capturer la sortie
        ob_start();
        try {
            pdf_builder_verify_canvas_settings_consistency_handler();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        // Décoder la réponse JSON
        $response = json_decode($output, true);

        // Vérifications
        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertTrue($response['success'], 'Handler should return success');

        $canvas_data = $response['data'];
        $this->assertIsArray($canvas_data, 'Canvas data should be an array');

        // Vérifier que tous les paramètres canvas sont présents
        foreach ($test_settings as $key => $expected_value) {
            $this->assertArrayHasKey($key, $canvas_data,
                "Canvas setting '$key' should be present in response");
            $this->assertEquals($expected_value, $canvas_data[$key],
                "Canvas setting '$key' should have correct value");
        }

        // Vérifier qu'aucun paramètre non-canvas n'est présent
        foreach ($canvas_data as $key => $value) {
            $this->assertStringStartsWith('pdf_builder_canvas_', $key,
                "Only canvas settings should be returned, found: $key");
        }
    }

    /**
     * Test du handler avec permissions insuffisantes
     */
    public function test_verify_canvas_settings_consistency_handler_insufficient_permissions() {
        // Se connecter en tant qu'utilisateur non-admin
        $subscriber_user = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber_user);

        $_POST['action'] = 'verify_canvas_settings_consistency';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');

        ob_start();
        try {
            pdf_builder_verify_canvas_settings_consistency_handler();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertFalse($response['success'], 'Handler should fail with insufficient permissions');
        $this->assertEquals('Permissions insuffisantes', $response['data'],
            'Error message should indicate insufficient permissions');
    }

    /**
     * Test du handler de sauvegarde des paramètres canvas
     */
    public function test_save_canvas_settings_handler() {
        $canvas_data = [
            'pdf_builder_canvas_width' => 1024,
            'pdf_builder_canvas_height' => 768,
            'pdf_builder_canvas_dpi' => 200,
            'pdf_builder_canvas_bg_color' => '#f5f5f5',
            'pdf_builder_canvas_theme' => 'dark'
        ];

        // Se connecter en tant qu'admin
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        $_POST['action'] = 'pdf_builder_save_canvas_settings';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');
        $_POST = array_merge($_POST, $canvas_data);

        ob_start();
        try {
            // Note: Cette fonction devrait exister dans Ajax_Handlers.php
            // Si elle n'existe pas, il faudra l'ajouter
            if (function_exists('pdf_builder_save_canvas_settings_handler')) {
                pdf_builder_save_canvas_settings_handler();
                $output = ob_get_clean();
            } else {
                ob_end_clean();
                $this->markTestSkipped('pdf_builder_save_canvas_settings_handler function not found');
                return;
            }
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertTrue($response['success'], 'Canvas save should succeed');

        // Vérifier que les données ont été sauvegardées
        $saved_settings = get_option('pdf_builder_settings', []);
        foreach ($canvas_data as $key => $expected_value) {
            $this->assertEquals($expected_value, $saved_settings[$key],
                "Canvas setting '$key' should be saved correctly");
        }
    }

    /**
     * Test de validation des données canvas
     */
    public function test_canvas_data_validation() {
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        $invalid_data = [
            'pdf_builder_canvas_width' => -100,  // Négatif
            'pdf_builder_canvas_height' => 0,    // Zéro
            'pdf_builder_canvas_dpi' => 10000,   // Trop élevé
            'pdf_builder_canvas_bg_color' => 'not-a-color',
            'pdf_builder_canvas_border_width' => -1
        ];

        $_POST['action'] = 'pdf_builder_save_canvas_settings';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');
        $_POST = array_merge($_POST, $invalid_data);

        ob_start();
        if (function_exists('pdf_builder_save_canvas_settings_handler')) {
            pdf_builder_save_canvas_settings_handler();
            $output = ob_get_clean();
        } else {
            ob_end_clean();
            $this->markTestSkipped('pdf_builder_save_canvas_settings_handler function not found');
            return;
        }

        $response = json_decode($output, true);

        // Le système devrait rejeter les données invalides
        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertFalse($response['success'], 'Invalid canvas data should be rejected');

        // Vérifier que les données invalides n'ont pas été sauvegardées
        $saved_settings = get_option('pdf_builder_settings', []);
        foreach ($invalid_data as $key => $invalid_value) {
            if (isset($saved_settings[$key])) {
                $this->assertNotEquals($invalid_value, $saved_settings[$key],
                    "Invalid value should not be saved for '$key'");
            }
        }
    }

    /**
     * Test de performance du handler de vérification
     */
    public function test_verify_consistency_performance() {
        // Créer un grand nombre de paramètres canvas
        $large_settings = [];
        for ($i = 0; $i < 1000; $i++) {
            $large_settings["pdf_builder_canvas_param_$i"] = "value_$i";
        }

        update_option('pdf_builder_settings', $large_settings);

        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        $_POST['action'] = 'verify_canvas_settings_consistency';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');

        $start_time = microtime(true);

        ob_start();
        pdf_builder_verify_canvas_settings_consistency_handler();
        $output = ob_get_clean();

        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertTrue($response['success'], 'Handler should succeed with large dataset');

        // Vérifier que l'opération prend moins de 1 seconde
        $this->assertLessThan(1.0, $execution_time,
            "Consistency check should complete in less than 1 second, took: $execution_time");

        // Vérifier que tous les paramètres canvas sont retournés
        $canvas_data = $response['data'];
        $this->assertCount(1000, $canvas_data, 'All canvas parameters should be returned');
    }

    /**
     * Test de l'handler de réinitialisation des paramètres canvas par défaut
     */
    public function test_reset_canvas_defaults_handler() {
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        // Sauvegarder d'abord des paramètres personnalisés
        $custom_settings = [
            'pdf_builder_canvas_width' => 1500,
            'pdf_builder_canvas_height' => 1000,
            'pdf_builder_canvas_bg_color' => '#ff0000'
        ];

        update_option('pdf_builder_settings', $custom_settings);

        $_POST['action'] = 'pdf_builder_reset_canvas_defaults';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');

        ob_start();
        if (function_exists('pdf_builder_reset_canvas_defaults_handler')) {
            pdf_builder_reset_canvas_defaults_handler();
            $output = ob_get_clean();
        } else {
            ob_end_clean();
            $this->markTestSkipped('pdf_builder_reset_canvas_defaults_handler function not found');
            return;
        }

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertTrue($response['success'], 'Reset should succeed');

        // Vérifier que les paramètres ont été remis aux valeurs par défaut
        $saved_settings = get_option('pdf_builder_settings', []);
        $this->assertEquals(800, $saved_settings['pdf_builder_canvas_width'], 'Width should be reset to default');
        $this->assertEquals(600, $saved_settings['pdf_builder_canvas_height'], 'Height should be reset to default');
        $this->assertEquals('#ffffff', $saved_settings['pdf_builder_canvas_bg_color'], 'Background color should be reset to default');
    }

    /**
     * Test de validation des permissions pour tous les handlers canvas
     */
    public function test_canvas_handlers_permissions() {
        $handlers_to_test = [
            'pdf_builder_verify_canvas_settings_consistency',
            'pdf_builder_save_canvas_settings',
            'pdf_builder_reset_canvas_defaults'
        ];

        foreach ($handlers_to_test as $action) {
            // Test avec utilisateur non-admin
            $subscriber_user = $this->factory->user->create(['role' => 'subscriber']);
            wp_set_current_user($subscriber_user);

            $_POST['action'] = $action;
            $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');

            ob_start();
            $function_name = $action . '_handler';
            if (function_exists($function_name)) {
                call_user_func($function_name);
                $output = ob_get_clean();
            } else {
                ob_end_clean();
                continue; // Skip if function doesn't exist
            }

            $response = json_decode($output, true);

            $this->assertNotNull($response, "Response for $action should be valid JSON");
            $this->assertFalse($response['success'], "$action should fail with insufficient permissions");
        }
    }

    /**
     * Test de gestion des erreurs AJAX pour les handlers canvas
     */
    public function test_canvas_handlers_error_handling() {
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        // Test avec nonce invalide
        $_POST['action'] = 'pdf_builder_verify_canvas_settings_consistency';
        $_POST['nonce'] = 'invalid-nonce';

        ob_start();
        pdf_builder_verify_canvas_settings_consistency_handler();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertFalse($response['success'], 'Handler should fail with invalid nonce');

        // Test avec données manquantes
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');
        unset($_POST['action']);

        ob_start();
        pdf_builder_verify_canvas_settings_consistency_handler();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        // Le système devrait gérer gracieusement les données manquantes
        $this->assertNotNull($response, 'Response should be valid JSON');
    }

    /**
     * Test de performance des handlers canvas sous charge
     */
    public function test_canvas_handlers_bulk_operations() {
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);

        // Test de sauvegarde en masse
        $bulk_data = [];
        for ($i = 0; $i < 100; $i++) {
            $bulk_data["pdf_builder_canvas_param_$i"] = "bulk_value_$i";
        }

        $_POST['action'] = 'pdf_builder_save_canvas_settings';
        $_POST['nonce'] = wp_create_nonce('pdf_builder_ajax_nonce');
        $_POST = array_merge($_POST, $bulk_data);

        $start_time = microtime(true);

        ob_start();
        if (function_exists('pdf_builder_save_canvas_settings_handler')) {
            pdf_builder_save_canvas_settings_handler();
            $output = ob_get_clean();
        } else {
            ob_end_clean();
            $this->markTestSkipped('pdf_builder_save_canvas_settings_handler function not found');
            return;
        }

        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        $response = json_decode($output, true);

        $this->assertNotNull($response, 'Response should be valid JSON');
        $this->assertTrue($response['success'], 'Bulk save should succeed');

        // Vérifier que l'opération prend moins de 0.5 seconde
        $this->assertLessThan(0.5, $execution_time,
            "Bulk save should complete in less than 0.5 seconds, took: $execution_time");

        // Vérifier que les données ont été sauvegardées
        $saved_settings = get_option('pdf_builder_settings', []);
        for ($i = 0; $i < 100; $i++) {
            $this->assertEquals("bulk_value_$i", $saved_settings["pdf_builder_canvas_param_$i"],
                "Bulk parameter $i should be saved correctly");
        }
    }
}