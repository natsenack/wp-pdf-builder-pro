<?php
/**
 * Security tests for the settings save system
 */

class SecurityTest extends PDF_Builder_AjaxTestCase {

    /**
     * Test nonce validation
     */
    public function test_nonce_validation() {
        // Test with no nonce
        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'Test'
        ]);

        $this->assertAjaxFailure($response);

        // Test with invalid nonce
        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'Test',
            '_wpnonce' => 'invalid_nonce_12345'
        ]);

        $this->assertAjaxFailure($response);

        // Test with valid nonce
        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'Test',
            '_wpnonce' => $this->create_nonce()
        ]);

        $this->assertAjaxSuccess($response);
    }

    /**
     * Test permission checks
     */
    public function test_permission_checks() {
        $roles_to_test = [
            'subscriber' => false,
            'contributor' => false,
            'author' => false,
            'editor' => false,
            'administrator' => true
        ];

        foreach ($roles_to_test as $role => $should_succeed) {
            $user = $this->factory->user->create(['role' => $role]);
            wp_set_current_user($user);

            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => 'Test Title'
            ]);

            if ($should_succeed) {
                $this->assertAjaxSuccess($response, "Administrator should be able to save settings");
            } else {
                $this->assertAjaxFailure($response, "Role '{$role}' should not be able to save settings");
            }
        }
    }

    /**
     * Test SQL injection prevention
     */
    public function test_sql_injection_prevention() {
        $malicious_inputs = [
            "'; DROP TABLE wp_options; --",
            "' OR '1'='1",
            "<script>alert('xss')</script>",
            "../../../../etc/passwd",
            "<?php phpinfo(); ?>",
            "javascript:alert('xss')",
            "data:text/html,<script>alert('xss')</script>"
        ];

        foreach ($malicious_inputs as $input) {
            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => $input,
                'general_author' => $input,
                'general_subject' => $input
            ]);

            // Should succeed (data gets sanitized) but malicious content should be cleaned
            $this->assertAjaxSuccess($response);

            // Verify the malicious content was sanitized
            $saved_title = $this->get_pdf_option('general_title');
            $this->assertStringNotContains('<script>', $saved_title);
            $this->assertStringNotContains('DROP TABLE', $saved_title);
            $this->assertStringNotContains('phpinfo', $saved_title);
        }
    }

    /**
     * Test XSS prevention
     */
    public function test_xss_prevention() {
        $xss_payloads = [
            '<script>alert("xss")</script>',
            '<img src=x onerror=alert("xss")>',
            '<iframe src="javascript:alert(\'xss\')"></iframe>',
            '<svg onload=alert("xss")>',
            '"><script>alert("xss")</script>',
            '<body onload=alert("xss")>'
        ];

        foreach ($xss_payloads as $payload) {
            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => $payload
            ]);

            $this->assertAjaxSuccess($response);

            $saved_title = $this->get_pdf_option('general_title');
            $this->assertStringNotContains('<script>', $saved_title);
            $this->assertStringNotContains('<img', $saved_title);
            $this->assertStringNotContains('<iframe', $saved_title);
            $this->assertStringNotContains('<svg', $saved_title);
            $this->assertStringNotContains('onerror=', $saved_title);
            $this->assertStringNotContains('onload=', $saved_title);
        }
    }

    /**
     * Test rate limiting simulation
     */
    public function test_rate_limiting_simulation() {
        // Simulate multiple rapid requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => 'Test ' . $i
            ]);

            // All should succeed (no rate limiting implemented yet)
            $this->assertAjaxSuccess($response);
        }
    }

    /**
     * Test data validation
     */
    public function test_data_validation() {
        // Test with oversized data
        $large_data = str_repeat('A', 100000); // 100KB string

        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => $large_data
        ]);

        // Should handle large data gracefully
        $this->assertAjaxSuccess($response);

        // Test with array data (should be handled)
        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => ['array', 'data']
        ]);

        $this->assertAjaxSuccess($response);
    }

    /**
     * Test CSRF protection
     */
    public function test_csrf_protection() {
        // Test with nonce from different action
        $wrong_nonce = wp_create_nonce('different_action');

        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'Test',
            '_wpnonce' => $wrong_nonce
        ]);

        $this->assertAjaxFailure($response);
    }
}</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\SecurityTest.php