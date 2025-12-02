<?php
/**
 * Base test case for PDF Builder tests
 */

class PDF_Builder_TestCase extends WP_UnitTestCase {

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Clean up any existing options
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%'");

        // Set up current user as admin
        $admin_user = $this->factory->user->create([
            'role' => 'administrator'
        ]);
        wp_set_current_user($admin_user);

        // Ensure our plugin functions are loaded
        if (!function_exists('pdf_builder_register_settings_handler')) {
            require_once plugin_dir_path(dirname(__DIR__)) . 'plugin/templates/admin/settings-parts/settings-handlers-factory.php';
        }
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Clean up options
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%'");

        parent::tearDown();
    }

    /**
     * Helper to create a valid nonce
     */
    protected function create_nonce($action = 'pdf_builder_settings') {
        return wp_create_nonce($action);
    }

    /**
     * Helper to simulate POST request
     */
    protected function set_post_data($data) {
        $_POST = array_merge($_POST, $data);
        return $this;
    }

    /**
     * Helper to clean POST data
     */
    protected function clean_post_data() {
        $_POST = [];
        return $this;
    }

    /**
     * Helper to get option with pdf_builder prefix
     */
    protected function get_pdf_option($key) {
        return get_option('pdf_builder_' . $key);
    }

    /**
     * Helper to set option with pdf_builder prefix
     */
    protected function set_pdf_option($key, $value) {
        return update_option('pdf_builder_' . $key, $value);
    }
}</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\TestCase.php