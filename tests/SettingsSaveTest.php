<?php
/**
 * Tests for the settings save system
 */

class SettingsSaveTest extends PDF_Builder_AjaxTestCase {

    /**
     * Test that the factory creates handlers correctly
     */
    public function test_factory_creates_handlers() {
        // Test that the factory function exists
        $this->assertTrue(function_exists('pdf_builder_register_settings_handler'));

        // Test creating a handler for 'general' tab
        $handler_created = pdf_builder_register_settings_handler('general');
        $this->assertTrue($handler_created);

        // Verify the action hook is registered
        $this->assertTrue(has_action('wp_ajax_pdf_builder_save_general'));
    }

    /**
     * Test successful save of general settings
     */
    public function test_save_general_settings_success() {
        $test_data = [
            'general_title' => 'Test PDF Title',
            'general_author' => 'Test Author',
            'general_subject' => 'Test Subject'
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_general', $test_data);

        $data = $this->assertAjaxSuccess($response);
        $this->assertEquals('Settings saved successfully', $data['data']['message']);

        // Verify data was saved
        $this->assertEquals('Test PDF Title', $this->get_pdf_option('general_title'));
        $this->assertEquals('Test Author', $this->get_pdf_option('general_author'));
        $this->assertEquals('Test Subject', $this->get_pdf_option('general_subject'));
    }

    /**
     * Test save with invalid nonce
     */
    public function test_save_with_invalid_nonce() {
        $test_data = [
            'general_title' => 'Test Title',
            '_wpnonce' => 'invalid_nonce'
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_general', $test_data);

        $data = $this->assertAjaxFailure($response);
        $this->assertStringContains('Security check failed', $data['data']);
    }

    /**
     * Test save without proper permissions
     */
    public function test_save_without_permissions() {
        // Switch to subscriber user
        $subscriber = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber);

        $test_data = [
            'general_title' => 'Test Title'
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_general', $test_data);

        $data = $this->assertAjaxFailure($response);
        $this->assertStringContains('Insufficient permissions', $data['data']);
    }

    /**
     * Test save with empty data
     */
    public function test_save_empty_data() {
        $response = $this->execute_ajax_action('pdf_builder_save_general', []);

        $data = $this->assertAjaxSuccess($response);
        $this->assertEquals('Settings saved successfully', $data['data']['message']);
    }

    /**
     * Test multiple tabs can be registered
     */
    public function test_multiple_tabs_registration() {
        $tabs = ['general', 'appearance', 'security', 'advanced'];

        foreach ($tabs as $tab) {
            $handler_created = pdf_builder_register_settings_handler($tab);
            $this->assertTrue($handler_created);
            $this->assertTrue(has_action('wp_ajax_pdf_builder_save_' . $tab));
        }
    }

    /**
     * Test data sanitization
     */
    public function test_data_sanitization() {
        $malicious_data = [
            'general_title' => '<script>alert("xss")</script>Test Title',
            'general_author' => 'Author<script>',
            'general_subject' => 'Subject' . PHP_EOL . 'Extra line'
        ];

        $response = $this->execute_ajax_action('pdf_builder_save_general', $malicious_data);

        $this->assertAjaxSuccess($response);

        // Verify XSS was prevented
        $saved_title = $this->get_pdf_option('general_title');
        $this->assertStringNotContains('<script>', $saved_title);
        $this->assertStringContains('Test Title', $saved_title);
    }

    /**
     * Test concurrent saves don't interfere
     */
    public function test_concurrent_saves() {
        // Simulate two different tabs saving simultaneously
        $general_data = ['general_title' => 'General Title'];
        $appearance_data = ['appearance_font_size' => '12px'];

        // Save general settings
        $response1 = $this->execute_ajax_action('pdf_builder_save_general', $general_data);
        $this->assertAjaxSuccess($response1);

        // Save appearance settings
        $response2 = $this->execute_ajax_action('pdf_builder_save_appearance', $appearance_data);
        $this->assertAjaxSuccess($response2);

        // Verify both were saved correctly
        $this->assertEquals('General Title', $this->get_pdf_option('general_title'));
        $this->assertEquals('12px', $this->get_pdf_option('appearance_font_size'));
    }
}</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\SettingsSaveTest.php