<?php
/**
 * AJAX test case for PDF Builder AJAX handlers
 */

class PDF_Builder_AjaxTestCase extends PDF_Builder_TestCase {

    /**
     * Set up AJAX test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Enable AJAX
        if (!defined('DOING_AJAX')) {
            define('DOING_AJAX', true);
        }

        // Start output buffering for AJAX responses
        ob_start();

        // Register AJAX handlers
        if (function_exists('pdf_builder_register_ajax_handlers')) {
            pdf_builder_register_ajax_handlers();
        }
    }

    /**
     * Tear down AJAX test environment
     */
    public function tearDown(): void {
        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        parent::tearDown();
    }

    /**
     * Helper to simulate AJAX request
     */
    protected function simulate_ajax_request($action, $data = []) {
        // Set up AJAX action
        $_REQUEST['action'] = $action;
        $_POST['action'] = $action;

        // Set up data
        foreach ($data as $key => $value) {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }

        // Create nonce if not provided
        if (!isset($_POST['_wpnonce']) && !isset($data['_wpnonce'])) {
            $_POST['_wpnonce'] = $this->create_nonce();
            $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];
        }

        return $this;
    }

    /**
     * Helper to execute AJAX action and get response
     */
    protected function execute_ajax_action($action, $data = []) {
        $this->simulate_ajax_request($action, $data);

        // Execute the action
        do_action('wp_ajax_' . $action);

        // Get response
        $response = ob_get_clean();

        // Restart output buffering
        ob_start();

        return $response;
    }

    /**
     * Helper to parse JSON response
     */
    protected function parse_json_response($response) {
        $json_start = strpos($response, '{');
        if ($json_start !== false) {
            $json_response = substr($response, $json_start);
            return json_decode($json_response, true);
        }
        return null;
    }

    /**
     * Helper to assert successful AJAX response
     */
    protected function assertAjaxSuccess($response) {
        $data = $this->parse_json_response($response);
        $this->assertNotNull($data, 'Response should be valid JSON');
        $this->assertTrue($data['success'], 'AJAX response should be successful');
        return $data;
    }

    /**
     * Helper to assert failed AJAX response
     */
    protected function assertAjaxFailure($response) {
        $data = $this->parse_json_response($response);
        $this->assertNotNull($data, 'Response should be valid JSON');
        $this->assertFalse($data['success'], 'AJAX response should fail');
        return $data;
    }
}</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\AjaxTestCase.php