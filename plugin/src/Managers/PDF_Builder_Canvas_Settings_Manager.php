<?php
/**
 * PDF Builder Pro - Gestionnaire des paramètres canvas
 * Gère la sauvegarde et le chargement des paramètres canvas dans une table dédiée
 */

class PDF_Builder_Canvas_Settings_Manager {

    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'pdf_builder_settings';
    }

    /**
     * Vérifie si la table existe
     */
    public function table_exists() {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
    }

    /**
     * Sauvegarde un paramètre canvas
     */
    public function set_setting($key, $value, $group = 'canvas') {
        global $wpdb;

        if (!$this->table_exists()) {
            error_log("[PDF Builder] Canvas settings table does not exist, falling back to wp_options");
            return $this->fallback_set_setting($key, $value);
        }

        $setting_type = $this->detect_setting_type($value);

        $result = $wpdb->replace(
            $this->table_name,
            [
                'setting_key' => $key,
                'setting_value' => maybe_serialize($value),
                'setting_group' => $group,
                'setting_type' => $setting_type,
                'is_public' => 0
            ],
            ['%s', '%s', '%s', '%s', '%d']
        );

        if ($result === false) {
            error_log("[PDF Builder] Failed to save canvas setting: $key");
            return false;
        }

        error_log("[PDF Builder] Canvas setting saved: $key = " . (is_array($value) ? json_encode($value) : $value));
        return true;
    }

    /**
     * Récupère un paramètre canvas
     */
    public function get_setting($key, $default = '') {
        global $wpdb;

        if (!$this->table_exists()) {
            error_log("[PDF Builder] Canvas settings table does not exist, falling back to wp_options");
            return $this->fallback_get_setting($key, $default);
        }

        $row = $wpdb->get_row($wpdb->prepare("
            SELECT setting_value FROM {$this->table_name}
            WHERE setting_key = %s AND setting_group = 'canvas'
        ", $key), ARRAY_A);

        if ($row) {
            $value = maybe_unserialize($row['setting_value']);
            error_log("[PDF Builder] Canvas setting loaded: $key = " . (is_array($value) ? json_encode($value) : $value));
            return $value;
        }

        error_log("[PDF Builder] Canvas setting not found: $key, using default: $default");
        return $default;
    }

    /**
     * Sauvegarde plusieurs paramètres canvas
     */
    public function set_multiple_settings($settings_array, $group = 'canvas') {
        $success_count = 0;

        foreach ($settings_array as $key => $value) {
            if ($this->set_setting($key, $value, $group)) {
                $success_count++;
            }
        }

        error_log("[PDF Builder] Saved $success_count canvas settings out of " . count($settings_array));
        return $success_count;
    }

    /**
     * Récupère tous les paramètres canvas
     */
    public function get_all_settings($group = 'canvas') {
        global $wpdb;

        if (!$this->table_exists()) {
            error_log("[PDF Builder] Canvas settings table does not exist, falling back to wp_options");
            return $this->fallback_get_all_settings();
        }

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT setting_key, setting_value FROM {$this->table_name}
            WHERE setting_group = %s
        ", $group), ARRAY_A);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = maybe_unserialize($row['setting_value']);
        }

        error_log("[PDF Builder] Loaded " . count($settings) . " canvas settings from database");
        return $settings;
    }

    /**
     * Supprime un paramètre canvas
     */
    public function delete_setting($key, $group = 'canvas') {
        global $wpdb;

        if (!$this->table_exists()) {
            return false;
        }

        $result = $wpdb->delete(
            $this->table_name,
            ['setting_key' => $key, 'setting_group' => $group],
            ['%s', '%s']
        );

        error_log("[PDF Builder] Deleted canvas setting: $key");
        return $result !== false;
    }

    /**
     * Fallback vers wp_options si la table n'existe pas
     */
    private function fallback_set_setting($key, $value) {
        $existing_settings = get_option('pdf_builder_settings', []);
        $existing_settings[$key] = $value;
        return update_option('pdf_builder_settings', $existing_settings);
    }

    private function fallback_get_setting($key, $default = '') {
        $settings = get_option('pdf_builder_settings', []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    private function fallback_get_all_settings() {
        $all_settings = get_option('pdf_builder_settings', []);
        $canvas_settings = [];

        foreach ($all_settings as $key => $value) {
            if (strpos($key, 'pdf_builder_canvas_') === 0) {
                $canvas_settings[$key] = $value;
            }
        }

        return $canvas_settings;
    }

    /**
     * Détecte le type d'un paramètre
     */
    private function detect_setting_type($value) {
        if (is_array($value)) {
            return 'array';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_string($value) && strlen($value) > 100) {
            return 'textarea';
        } else {
            return 'string';
        }
    }
}