<?php
/**
 * PDF Builder Pro - Data Migration Function
 * Migre les options PDF Builder de wp_options vers wp_pdf_builder_settings
 */

namespace PDF_Builder\Database;

class Settings_Migration {
    
    /**
     * Migrer les données existantes de wp_options vers wp_pdf_builder_settings
     * Appelé lors de l'activation du plugin
     */
    public static function migrate_from_wp_options() {
        global $wpdb;
        
        // Liste des options PDF Builder à migrer
        $options_to_migrate = [
            'pdf_builder_settings',
            'pdf_builder_canvas_orientations',
            'pdf_builder_canvas_orientation',
            'pdf_builder_template_library_enabled',
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_canvas_dpi',
            'pdf_builder_canvas_format',
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_border_width',
            'pdf_builder_canvas_shadow_enabled',
            'pdf_builder_canvas_container_bg_color',
            'pdf_builder_canvas_grid_enabled',
            'pdf_builder_canvas_grid_size',
            'pdf_builder_canvas_guides_enabled',
            'pdf_builder_canvas_snap_to_grid',
            'pdf_builder_canvas_memory_limit_php',
            'pdf_builder_puppeteer_url',
            'pdf_builder_puppeteer_token',
            'pdf_builder_puppeteer_timeout',
            'pdf_builder_puppeteer_fallback',
            'pdf_builder_debug_enabled',
            'pdf_builder_developer_enabled',
            'pdf_builder_engine',
            'pdf_builder_company_siret',
            'pdf_builder_company_rcs',
            'pdf_builder_company_capital',
            'pdf_builder_company_vat',
            'pdf_builder_company_phone',
            'pdf_builder_onboarding',
            'pdf_builder_gdpr',
            'pdf_builder_woocommerce_integration',
            'pdf_builder_woocommerce',
            'pdf_builder_license_enable_notifications',
            'pdf_builder_license_reminder_email',
            'pdf_builder_license_test_mode_enabled',
            'pdf_builder_license_test_key',
            'pdf_builder_license_test_key_expires',
        ];
        
        // Paramètres de licence à migrer
        for ($i = 1; $i <= 10; $i++) {
            $options_to_migrate[] = "pdf_builder_license_{$i}";
            $options_to_migrate[] = "pdf_builder_license_{$i}_status";
            $options_to_migrate[] = "pdf_builder_license_{$i}_expiration";
        }
        
        // Migrer les templates individuels
        $template_ids = range(1, 50);
        foreach ($template_ids as $id) {
            $option_key = "pdf_builder_template_{$id}";
            // Vérifier si l'option existe dans wp_options
            $value = \get_option($option_key);
            if ($value !== false) {
                $options_to_migrate[] = $option_key;
            }
        }
        
        $migrated_count = 0;
        $errors = [];
        
        foreach ($options_to_migrate as $option_name) {
            // Récupérer la valeur depuis wp_options
            $value = \get_option($option_name);
            
            if ($value !== false) {
                // Vérifier si l'option existe déjà dans wp_pdf_builder_settings
                $existing = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT 1 FROM {$wpdb->prefix}pdf_builder_settings WHERE option_name = %s",
                        $option_name
                    )
                );
                
                // Migrer uniquement si elle n'existe pas en BDD
                if (!$existing) {
                    $result = Settings_Table_Manager::update_option($option_name, $value);
                    if ($result) {
                        $migrated_count++;
                        error_log("[PDF Builder Migration] Migrated option: {$option_name}");
                    } else {
                        $errors[] = "Failed to migrate: {$option_name}";
                        error_log("[PDF Builder Migration ERROR] Failed to migrate: {$option_name}");
                    }
                }
            }
        }
        
        // Enregistrer le résultat de la migration
        \update_option('pdf_builder_migration_completed', true);
        \update_option('pdf_builder_migration_date', date('Y-m-d H:i:s'));
        \update_option('pdf_builder_migration_count', $migrated_count);
        
        if ($errors) {
            \update_option('pdf_builder_migration_errors', $errors);
        }
        
        error_log("[PDF Builder Migration] Completed: {$migrated_count} options migrated");
        
        return [
            'success' => true,
            'migrated' => $migrated_count,
            'errors' => $errors
        ];
    }
    
    /**
     * Vérifier et afficher le statut de la migration
     */
    public static function get_migration_status() {
        return [
            'completed' => (bool)\get_option('pdf_builder_migration_completed'),
            'date' => \get_option('pdf_builder_migration_date'),
            'count' => (int)\get_option('pdf_builder_migration_count'),
            'errors' => \get_option('pdf_builder_migration_errors', [])
        ];
    }
    
    /**
     * Nettoyer les anciennes options WordPress après migration réussie
     * À appeler avec prudence!
     */
    public static function cleanup_old_wp_options() {
        if (!\current_user_can('manage_options')) {
            return false;
        }
        
        // Options PDF Builder qui peuvent être supprimées de wp_options
        $options_to_clean = [
            'pdf_builder_settings',
            'pdf_builder_canvas_orientations',
            'pdf_builder_canvas_orientation',
            'pdf_builder_template_library_enabled',
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_debug_enabled',
            'pdf_builder_developer_enabled',
            'pdf_builder_company_siret',
            'pdf_builder_company_rcs',
            'pdf_builder_company_capital',
            'pdf_builder_company_vat',
            'pdf_builder_company_phone',
            'pdf_builder_onboarding',
            'pdf_builder_gdpr',
        ];
        
        $cleaned_count = 0;
        foreach ($options_to_clean as $option_name) {
            if (\delete_option($option_name)) {
                $cleaned_count++;
            }
        }
        
        error_log("[PDF Builder Cleanup] Removed {$cleaned_count} old options from wp_options");
        \update_option('pdf_builder_cleanup_completed', true);
        
        return $cleaned_count;
    }
}
