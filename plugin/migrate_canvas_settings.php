<?php
/**
 * Migration script: Move canvas settings to separate table
 * This script moves all canvas-related settings from wp_options to wp_pdf_builder_settings table
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Canvas_Settings_Migration {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'pdf_builder_settings';
    }

    /**
     * Run the migration
     */
    public function migrate() {
        global $wpdb;

        // Create the table if it doesn't exist
        $this->create_table();

        // Get existing settings
        $existing_settings = get_option('pdf_builder_settings', []);

        // Extract canvas settings
        $canvas_settings = [];
        foreach ($existing_settings as $key => $value) {
            if (strpos($key, 'pdf_builder_canvas_') === 0) {
                $canvas_settings[$key] = $value;
            }
        }

        if (!empty($canvas_settings)) {
            // Insert canvas settings into the new table
            foreach ($canvas_settings as $setting_key => $setting_value) {
                $wpdb->replace(
                    $this->table_name,
                    [
                        'setting_key' => $setting_key,
                        'setting_value' => maybe_serialize($setting_value),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s', '%s']
                );
            }

            error_log('[PDF Builder Migration] Moved ' . count($canvas_settings) . ' canvas settings to separate table');
            return count($canvas_settings);
        }

        return 0;
    }

    /**
     * Create the settings table
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        error_log('[PDF Builder Migration] Created table: ' . $this->table_name);
    }

    /**
     * Get a canvas setting from the new table
     */
    public function get_canvas_setting($key, $default = '') {
        global $wpdb;

        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_key = %s",
            $key
        ));

        if ($result !== null) {
            return maybe_unserialize($result);
        }

        return $default;
    }

    /**
     * Set a canvas setting in the new table
     */
    public function set_canvas_setting($key, $value) {
        global $wpdb;

        return $wpdb->replace(
            $this->table_name,
            [
                'setting_key' => $key,
                'setting_value' => maybe_serialize($value),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s']
        );
    }

    /**
     * Get all canvas settings
     */
    public function get_all_canvas_settings() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM {$this->table_name}", ARRAY_A);

        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = maybe_unserialize($result['setting_value']);
        }

        return $settings;
    }

    /**
     * Set multiple canvas settings
     */
    public function set_canvas_settings($settings_array) {
        global $wpdb;

        $updated_count = 0;
        foreach ($settings_array as $key => $value) {
            $result = $wpdb->replace(
                $this->table_name,
                [
                    'setting_key' => $key,
                    'setting_value' => maybe_serialize($value),
                    'updated_at' => current_time('mysql')
                ],
                ['%s', '%s', '%s']
            );

            if ($result !== false) {
                $updated_count++;
            }
        }

        return $updated_count;
    }
}