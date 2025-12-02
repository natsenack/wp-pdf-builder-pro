<?php
/**
 * Performance tests for the settings save system
 */

class PerformanceTest extends PDF_Builder_AjaxTestCase {

    /**
     * Test response time for single save
     */
    public function test_single_save_performance() {
        $start_time = microtime(true);

        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'Performance Test',
            'general_author' => 'Test Author',
            'general_subject' => 'Performance Subject'
        ]);

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

        $this->assertAjaxSuccess($response);
        $this->assertLessThan(500, $execution_time, 'Single save should complete in less than 500ms');
    }

    /**
     * Test bulk save performance
     */
    public function test_bulk_save_performance() {
        $bulk_data = [];
        for ($i = 1; $i <= 50; $i++) {
            $bulk_data['field_' . $i] = 'Value ' . $i;
        }

        $start_time = microtime(true);

        $response = $this->execute_ajax_action('pdf_builder_save_general', $bulk_data);

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000;

        $this->assertAjaxSuccess($response);
        $this->assertLessThan(1000, $execution_time, 'Bulk save should complete in less than 1000ms');

        // Verify all data was saved
        for ($i = 1; $i <= 50; $i++) {
            $this->assertEquals('Value ' . $i, $this->get_pdf_option('field_' . $i));
        }
    }

    /**
     * Test concurrent tab saves performance
     */
    public function test_concurrent_tabs_performance() {
        $tabs_data = [
            'general' => [
                'general_title' => 'General Title',
                'general_author' => 'General Author'
            ],
            'appearance' => [
                'appearance_font_size' => '12px',
                'appearance_color' => '#000000'
            ],
            'security' => [
                'security_enabled' => '1',
                'security_timeout' => '30'
            ],
            'advanced' => [
                'advanced_debug' => '0',
                'advanced_cache' => '1'
            ]
        ];

        $start_time = microtime(true);

        foreach ($tabs_data as $tab => $data) {
            $response = $this->execute_ajax_action('pdf_builder_save_' . $tab, $data);
            $this->assertAjaxSuccess($response);
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000;

        $this->assertLessThan(2000, $execution_time, 'Concurrent tab saves should complete in less than 2000ms');

        // Verify all data was saved
        foreach ($tabs_data as $tab => $data) {
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $this->get_pdf_option($key));
            }
        }
    }

    /**
     * Test memory usage during saves
     */
    public function test_memory_usage() {
        $initial_memory = memory_get_usage();

        // Perform multiple saves
        for ($i = 0; $i < 10; $i++) {
            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => 'Memory Test ' . $i
            ]);
            $this->assertAjaxSuccess($response);
        }

        $final_memory = memory_get_usage();
        $memory_used = ($final_memory - $initial_memory) / 1024 / 1024; // Convert to MB

        $this->assertLessThan(10, $memory_used, 'Memory usage should be less than 10MB for 10 saves');
    }

    /**
     * Test database query efficiency
     */
    public function test_database_efficiency() {
        global $wpdb;

        // Clear any existing queries log
        $wpdb->queries = [];

        $response = $this->execute_ajax_action('pdf_builder_save_general', [
            'general_title' => 'DB Test',
            'general_author' => 'DB Author',
            'general_subject' => 'DB Subject'
        ]);

        $this->assertAjaxSuccess($response);

        // Count database queries (should be minimal)
        $query_count = count($wpdb->queries);
        $this->assertLessThanOrEqual(5, $query_count, 'Save operation should use 5 or fewer database queries');
    }

    /**
     * Test large data set handling
     */
    public function test_large_dataset_handling() {
        $large_dataset = [];
        for ($i = 0; $i < 100; $i++) {
            $large_dataset['setting_' . $i] = 'This is a test value for setting number ' . $i . ' with some additional text to make it longer and test memory handling.';
        }

        $start_time = microtime(true);
        $initial_memory = memory_get_usage();

        $response = $this->execute_ajax_action('pdf_builder_save_general', $large_dataset);

        $end_time = microtime(true);
        $final_memory = memory_get_usage();

        $execution_time = ($end_time - $start_time) * 1000;
        $memory_used = ($final_memory - $initial_memory) / 1024 / 1024;

        $this->assertAjaxSuccess($response);
        $this->assertLessThan(2000, $execution_time, 'Large dataset save should complete in less than 2000ms');
        $this->assertLessThan(50, $memory_used, 'Large dataset should use less than 50MB memory');

        // Verify a sample of the data
        $this->assertEquals($large_dataset['setting_0'], $this->get_pdf_option('setting_0'));
        $this->assertEquals($large_dataset['setting_99'], $this->get_pdf_option('setting_99'));
    }

    /**
     * Test repeated saves don't cause degradation
     */
    public function test_repeated_saves_performance() {
        $times = [];

        for ($i = 0; $i < 20; $i++) {
            $start_time = microtime(true);

            $response = $this->execute_ajax_action('pdf_builder_save_general', [
                'general_title' => 'Repeated Test ' . $i
            ]);

            $end_time = microtime(true);
            $times[] = ($end_time - $start_time) * 1000;

            $this->assertAjaxSuccess($response);
        }

        // Calculate average and check for degradation
        $average_time = array_sum($times) / count($times);
        $first_quarter_avg = array_sum(array_slice($times, 0, 5)) / 5;
        $last_quarter_avg = array_sum(array_slice($times, -5)) / 5;

        $this->assertLessThan(100, $average_time, 'Average save time should be less than 100ms');
        $this->assertLessThan($first_quarter_avg * 2, $last_quarter_avg, 'Performance should not degrade significantly');
    }
}</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\tests\PerformanceTest.php