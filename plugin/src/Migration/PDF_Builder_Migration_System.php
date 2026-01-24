<?php

/**
 * PDF Builder Pro Migration System
 * Handles plugin upgrades and data migrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Migration_System
{
    private static $instance = null;
    private $current_version;
    private $option_key = 'pdf_builder_migration_version';

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->current_version = PDF_BUILDER_VERSION;
        add_action('plugins_loaded', [$this, 'checkAndRunMigrations'], 5);
    }

    /**
     * Check if migrations need to be run
     */
    public function checkAndRunMigrations()
    {
        $last_migration = get_option($this->option_key, '0.0.0');

        if (version_compare($last_migration, $this->current_version, '<')) {
            $this->runMigrations($last_migration);
            update_option($this->option_key, $this->current_version);
        }
    }

    /**
     * Run migrations from a specific version
     */
    private function runMigrations($from_version)
    {
        $migrations = [
            '1.0.0' => [$this, 'migrateTo_1_0_0'],
            '1.0.1' => [$this, 'migrateTo_1_0_1'],
            '1.0.2' => [$this, 'migrateTo_1_0_2'],
            '1.1.0' => [$this, 'migrateTo_1_1_0'],
        ];

        foreach ($migrations as $version => $callback) {
            if (version_compare($from_version, $version, '<')) {
                try {
                    call_user_func($callback);
                    // // // // error_log("PDF Builder: Migration to {$version} completed successfully");
                } catch (Exception $e) {
                    // // // // error_log("PDF Builder: Migration to {$version} failed: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Migration to version 1.0.0
     * - Initialize basic settings
     * - Create necessary database tables
     */
    private function migrateTo_1_0_0()
    {
        // Initialize default settings
        $default_settings = [
            'cache_enabled' => false,
            'cache_ttl' => 3600,
            'developer_enabled' => false,
            'license_test_mode_enabled' => false,
        ];

        foreach ($default_settings as $key => $value) {
            if (get_option('pdf_builder_' . $key) === false) {
                add_option('pdf_builder_' . $key, $value);
            }
        }

        // Create templates table if it doesn't exist
        $this->createTemplatesTable();
    }

    /**
     * Migration to version 1.0.1
     * - Security improvements
     * - Add missing indexes
     */
    private function migrateTo_1_0_1()
    {
        global $wpdb;

        // Add indexes for better performance
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $wpdb->query("ALTER TABLE {$table_templates} ADD INDEX idx_name (name)");
        $wpdb->query("ALTER TABLE {$table_templates} ADD INDEX idx_created_at (created_at)");

        // Clean up any orphaned data
        $this->cleanupOrphanedData();
    }

    /**
     * Migration to version 1.0.2
     * - Analytics system initialization
     * - GDPR compliance settings
     */
    private function migrateTo_1_0_2()
    {
        // Initialize GDPR settings
        if (get_option('pdf_builder_gdpr_enabled') === false) {
            add_option('pdf_builder_gdpr_enabled', true);
        }

        // Initialize analytics settings (disabled by default)
        if (get_option('pdf_builder_analytics_enabled') === false) {
            add_option('pdf_builder_analytics_enabled', false);
        }

        // Clean up old transient data
        $this->cleanupOldTransients();
    }

    /**
     * Migration to version 1.1.0
     * - WooCommerce integration improvements
     * - Performance optimizations
     */
    private function migrateTo_1_1_0()
    {
        // Update WooCommerce integration settings
        $this->updateWooCommerceSettings();

        // Optimize database queries
        $this->optimizeDatabaseQueries();

        // Clear all caches after major update
        $this->clearAllCaches();
    }

    /**
     * Create templates database table
     */
    private function createTemplatesTable()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Clean up orphaned data
     */
    private function cleanupOrphanedData()
    {
        global $wpdb;

        // Remove templates with invalid JSON
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $invalid_templates = $wpdb->get_results(
            "SELECT id, template_data FROM {$table_templates}",
            ARRAY_A
        );

        foreach ($invalid_templates as $template) {
            $data = json_decode($template['template_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $wpdb->delete($table_templates, ['id' => $template['id']]);
            }
        }
    }

    /**
     * Clean up old transient data
     */
    private function cleanupOldTransients()
    {
        global $wpdb;

        // Remove old PDF Builder transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_pdf_builder_') . '%'
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_timeout_pdf_builder_') . '%'
            )
        );
    }

    /**
     * Update WooCommerce integration settings
     */
    private function updateWooCommerceSettings()
    {
        // Ensure WooCommerce order status mappings exist
        $default_mappings = [
            'wc-pending' => 0,
            'wc-processing' => 0,
            'wc-on-hold' => 0,
            'wc-completed' => 0,
            'wc-cancelled' => 0,
            'wc-refunded' => 0,
            'wc-failed' => 0,
        ];

        $current_mappings = get_option('pdf_builder_settings', [])['pdf_builder_order_status_templates'] ?? [];
        $updated_mappings = array_merge($default_mappings, $current_mappings);

        $settings = get_option('pdf_builder_settings', []);
        $settings['pdf_builder_order_status_templates'] = $updated_mappings;
        update_option('pdf_builder_settings', $settings);
    }

    /**
     * Optimize database queries
     */
    private function optimizeDatabaseQueries()
    {
        global $wpdb;

        // Add composite indexes for better query performance
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Check if indexes exist before adding
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$table_templates}", ARRAY_A);
        $existing_indexes = array_column($indexes, 'Key_name');

        if (!in_array('idx_name_updated', $existing_indexes)) {
            $wpdb->query("ALTER TABLE {$table_templates} ADD INDEX idx_name_updated (name, updated_at)");
        }
    }

    /**
     * Clear all caches after migration
     */
    private function clearAllCaches()
    {
        // Clear WordPress object cache
        wp_cache_flush();

        // Clear any PDF Builder specific caches
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('pdf_builder_cache_') . '%'
            )
        );

        // Clear transients
        $this->cleanupOldTransients();
    }
}

// Initialization will be done in bootstrap.php after constants are loaded
// PDF_Builder_Migration_System::getInstance();
