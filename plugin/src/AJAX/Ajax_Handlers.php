<?php
/**
 * PDF Builder Pro - Classe de base pour les handlers AJAX
 * Centralise la validation commune et la gestion d'erreurs
 */

abstract class PDF_Builder_Ajax_Base {
    protected $required_capability = 'manage_options';
    protected $nonce_action = 'pdf_builder_ajax';

    /**
     * Valide la requête AJAX de base
     */
    protected function validate_request() {
        // Vérifier le nonce (temporarily disabled for debugging)
        // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->nonce_action)) {
        //     $this->send_error('Nonce invalide', 403);
        // }

        // Vérifier les permissions
        if (!current_user_can($this->required_capability)) {
            $this->send_error('Permissions insuffisantes', 403);
        }

        // Vérifier que c'est une requête POST pour les modifications
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send_error('Méthode HTTP non autorisée', 405);
        }
    }

    /**
     * Valide et nettoie un paramètre requis
     */
    protected function validate_required_param($param_name, $type = 'string') {
        if (!isset($_POST[$param_name])) {
            $this->send_error("Paramètre manquant: {$param_name}", 400);
        }

        $value = $_POST[$param_name];

        switch ($type) {
            case 'int':
                $value = intval($value);
                if ($value <= 0) {
                    $this->send_error("Paramètre invalide: {$param_name}", 400);
                }
                break;
            case 'string':
                $value = sanitize_text_field($value);
                if (empty($value)) {
                    $this->send_error("Paramètre vide: {$param_name}", 400);
                }
                break;
            case 'json':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->send_error("JSON invalide pour: {$param_name}", 400);
                }
                $value = $decoded;
                break;
        }

        return $value;
    }

    /**
     * Envoie une réponse d'erreur standardisée
     */
    protected function send_error($message, $code = 400) {
        wp_send_json_error([
            'message' => $message,
            'code' => $code,
            'timestamp' => current_time('timestamp')
        ]);
        exit;
    }

    /**
     * Envoie une réponse de succès standardisée
     */
    protected function send_success($data = [], $message = 'Opération réussie') {
        wp_send_json_success(array_merge([
            'message' => $message,
            'timestamp' => current_time('timestamp')
        ], $data));
        exit;
    }

    /**
     * Log une erreur pour le debugging
     */
    protected function log_error($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            error_log('[PDF Builder AJAX] ' . $message . $context_str);
        }
    }

    /**
     * Méthode abstraite que les classes enfants doivent implémenter
     */
    abstract public function handle();
}

/**
 * Handler AJAX pour les paramètres
 */
class PDF_Builder_Settings_Ajax_Handler extends PDF_Builder_Ajax_Base {
    public function handle() {
        try {
            $this->validate_request();

            // Traiter tous les paramètres envoyés
            $result = $this->process_all_settings();

            if ($result['saved_count'] > 0) {
                $this->send_success([
                    'saved_count' => $result['saved_count'],
                    'saved_settings' => $result['saved_settings'],
                    'new_nonce' => wp_create_nonce($this->nonce_action)
                ], 'Paramètres sauvegardés avec succès');
            } else {
                $this->send_error('Aucun paramètre sauvegardé', 400);
            }

        } catch (Exception $e) {
            $this->log_error('Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
            $this->send_error('Erreur interne du serveur', 500);
        }
    }

    private function process_all_settings() {
        $saved_count = 0;
        $saved_settings = [];

        // Get current settings
        $settings = get_option('pdf_builder_settings', []);

        // DEBUG: Log that this function is being executed
        error_log("[AJAX HANDLER] process_all_settings called");
        error_log("[AJAX HANDLER] POST data received: " . json_encode(array_keys($_POST)));

        // Check if form_data is sent as JSON (legacy) or flattened data
        if (isset($_POST['form_data'])) {
            $form_data_json = stripslashes($_POST['form_data']);
            $all_form_data = json_decode($form_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error: ' . json_last_error_msg() . ' for data: ' . substr($form_data_json, 0, 500));
                throw new Exception('Données JSON invalides: ' . json_last_error_msg());
            }
            error_log("[AJAX HANDLER] Parsed legacy form_data successfully, forms: " . implode(', ', array_keys($all_form_data)));
            // Flatten the data
            $flattened_data = [];
            foreach ($all_form_data as $form_id => $form_fields) {
                if (is_array($form_fields)) {
                    foreach ($form_fields as $key => $value) {
                        $flattened_data[$key] = $value;
                    }
                }
            }
        } else {
            // Use flattened data directly from POST
            $flattened_data = $_POST;
            error_log("[AJAX HANDLER] Using flattened data directly from POST, fields: " . count($flattened_data));
        }

        // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
        error_log("=== AJAX HANDLER DEBUG JAVASCRIPT ANALYSIS ===");
        error_log("pdf_builder_debug_javascript in flattened_data: " . (isset($flattened_data['pdf_builder_debug_javascript']) ? $flattened_data['pdf_builder_debug_javascript'] : 'NOT_SET'));
        error_log("debug_javascript in flattened_data: " . (isset($flattened_data['debug_javascript']) ? $flattened_data['debug_javascript'] : 'NOT_SET'));

        // DEBUG: Log all debug-related fields
        $debug_fields = array_filter($flattened_data, function($key) {
            return strpos($key, 'debug') !== false;
        }, ARRAY_FILTER_USE_KEY);
        error_log("All debug fields in request: " . json_encode($debug_fields));

        // Définir les règles de validation des champs (même que dans settings-main.php)
        $field_rules = [
            'text_fields' => [
                'company_phone_manual', 'company_siret', 'company_vat', 'company_rcs', 'company_capital',
                'pdf_quality', 'default_format', 'default_orientation', 'default_template',
                'pdf_builder_developer_password',
                // Canvas text fields
                'canvas_bg_color', 'canvas_border_color', 'canvas_container_bg_color', 'canvas_selection_mode', 'canvas_export_format',
                'default_canvas_format', 'default_canvas_orientation', 'default_canvas_unit'
            ],
            'int_fields' => [
                'cache_max_size', 'cache_ttl',
                // Canvas int fields
                'zoom_min', 'zoom_max', 'zoom_default', 'zoom_step', 'canvas_grid_size', 'canvas_export_quality',
                'canvas_fps_target', 'canvas_memory_limit_js', 'canvas_memory_limit_php', 'canvas_dpi',
                'canvas_width', 'canvas_height', 'canvas_border_width'
            ],
            'bool_fields' => [
                'pdf_builder_cache_enabled', 'cache_compression', 'cache_auto_cleanup', 'performance_auto_optimization',
                'systeme_auto_maintenance', 'template_library_enabled',
                'pdf_builder_developer_enabled', 'pdf_builder_license_test_mode_enabled', 'pdf_builder_canvas_debug_enabled',
                // Debug fields - AJOUTÉ POUR CORRIGER LE TOGGLE DEBUG JAVASCRIPT
                'debug_javascript', 'pdf_builder_debug_javascript', 'debug_javascript_verbose', 'pdf_builder_debug_javascript_verbose',
                'debug_ajax', 'pdf_builder_debug_ajax', 'debug_performance', 'pdf_builder_debug_performance',
                'debug_database', 'pdf_builder_debug_database', 'debug_php_errors', 'pdf_builder_debug_php_errors',
                // Canvas bool fields
                'canvas_grid_enabled', 'canvas_snap_to_grid', 'canvas_guides_enabled', 'canvas_drag_enabled',
                'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts',
                'canvas_export_transparent', 'canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin',
                'canvas_debug_enabled', 'canvas_performance_monitoring', 'canvas_error_reporting', 'canvas_shadow_enabled'
            ],
            'array_fields' => ['order_status_templates']
        ];

        // Traiter tous les champs POST
        foreach ($flattened_data as $key => $value) {
            // Sauter les champs WordPress internes
            if (in_array($key, ['action', 'nonce', 'current_tab'])) {
                continue;
            }

            // DEBUG: Log each field being processed
            error_log("[AJAX HANDLER] Processing field: '$key' = '$value'");

            $option_key = '';
            $option_value = null;

            if (in_array($key, $field_rules['text_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    update_option($option_key, $option_value); // Canvas fields saved separately
                } elseif (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                } elseif (strpos($key, 'debug_') === 0) {
                    // Debug fields need pdf_builder_ prefix
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            } elseif (in_array($key, $field_rules['int_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $key;
                    $option_value = intval($value ?? 0);
                    update_option($option_key, $option_value); // Canvas fields saved separately
                } elseif (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                } elseif (strpos($key, 'debug_') === 0) {
                    // Debug fields need pdf_builder_ prefix
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                }
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    update_option($option_key, $option_value); // Canvas fields saved separately
                }
                $saved_count++;
            } elseif (in_array($key, $field_rules['bool_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $key;
                    $option_value = isset($flattened_data[$key]) && $flattened_data[$key] === '1' ? 1 : 0;
                    update_option($option_key, $option_value); // Canvas fields saved separately
                } elseif (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    $option_value = isset($flattened_data[$key]) && $flattened_data[$key] === '1' ? 1 : 0;
                    $settings[$option_key] = $option_value;
                } elseif (strpos($key, 'debug_') === 0) {
                    // Debug fields need pdf_builder_ prefix
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = isset($flattened_data[$key]) && $flattened_data[$key] === '1' ? 1 : 0;
                    $settings[$option_key] = $option_value;
                    // DEBUG SPECIFIC FOR JAVASCRIPT DEBUG
                    if ($key === 'debug_javascript') {
                        error_log("[DEBUG JAVASCRIPT TOGGLE] Processing debug_javascript:");
                        error_log("  - key: '$key'");
                        error_log("  - option_key: '$option_key'");
                        error_log("  - isset in flattened_data: " . (isset($flattened_data[$key]) ? 'YES' : 'NO'));
                        error_log("  - value in flattened_data: '" . ($flattened_data[$key] ?? 'NULL') . "'");
                        error_log("  - calculated option_value: $option_value");
                        error_log("  - will save to settings['$option_key'] = $option_value");
                    }
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = isset($flattened_data[$key]) && $flattened_data[$key] === '1' ? 1 : 0;
                    $settings[$option_key] = $option_value;
                }
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    update_option($option_key, $option_value); // Canvas fields saved separately
                }
                $saved_count++;
                // DEBUG: Log bool field processing
                error_log("[AJAX DEBUG] Bool field processed: key='$key', option_key='$option_key', value='$option_value', isset=" . (isset($flattened_data[$key]) ? 'true' : 'false') . ", data_value='" . ($flattened_data[$key] ?? 'null') . "'");

                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                if (strpos($key, 'debug_javascript') !== false) {
                    error_log("[AJAX DEBUG JAVASCRIPT] Processing debug_javascript field: key='$key', option_key='$option_key', value='$option_value'");
                    // VÉRIFIER LA SAUVEGARDE EN BDD APRÈS UPDATE
                    $db_value_after = get_option($option_key, 'NOT_FOUND_AFTER_UPDATE');
                    error_log("[AJAX DEBUG JAVASCRIPT] VERIFICATION BDD APRES SAUVEGARDE: get_option('$option_key') = '$db_value_after'");
                }

                // MISE À JOUR DES PARAMÈTRES CANVAS POUR LES CHAMPS DEBUG
                if (preg_match('/debug_(.+)/', $key, $matches)) {
                    $debug_key = $matches[1];
                    $canvas_settings = get_option('pdf_builder_canvas_settings', []);
                    if (!isset($canvas_settings['debug'])) {
                        $canvas_settings['debug'] = [];
                    }
                    $canvas_settings['debug'][$debug_key] = $option_value;
                    update_option('pdf_builder_canvas_settings', $canvas_settings);
                    error_log("[AJAX DEBUG] Updated canvas settings debug.$debug_key = $option_value");
                }
            } elseif (in_array($key, $field_rules['array_fields'])) {
                if (is_array($value)) {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = array_map('sanitize_text_field', $value);
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = [];
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            } else {
                // Pour les champs non définis, essayer de deviner le type
                if (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    if (is_numeric($value)) {
                        $option_value = intval($value);
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                    } else {
                        $option_value = sanitize_text_field($value ?? '');
                    }
                } else {
                    // Add prefix
                    $option_key = 'pdf_builder_' . $key;
                    if (is_numeric($value)) {
                        $option_value = intval($value);
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                    } else {
                        $option_value = sanitize_text_field($value ?? '');
                    }
                }
                $settings[$option_key] = $option_value;
                $saved_count++;
            }

            // Ajouter à saved_settings si une clé a été définie
            if (!empty($option_key)) {
                $saved_settings[$option_key] = $option_value;
                // DEBUG: Log saved_settings addition
                error_log("[AJAX DEBUG] Added to saved_settings: '$option_key' = '$option_value'");
                
                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT DANS SAVED_SETTINGS
                if (strpos($option_key, 'debug_javascript') !== false) {
                    error_log("[AJAX DEBUG JAVASCRIPT] AJOUTÉ À SAVED_SETTINGS: '$option_key' => '$option_value'");
                    error_log("[AJAX DEBUG JAVASCRIPT] TOTAL SAVED_SETTINGS: " . count($saved_settings));
                }
            }
        }

        // S'assurer que tous les champs debug sont dans saved_settings
        foreach (['pdf_builder_debug_javascript', 'pdf_builder_debug_javascript_verbose', 'pdf_builder_debug_ajax', 'pdf_builder_debug_performance', 'pdf_builder_debug_database', 'pdf_builder_debug_php_errors'] as $debug_field) {
            if (!isset($saved_settings[$debug_field])) {
                $db_value = get_option($debug_field, 0);
                $saved_settings[$debug_field] = $db_value;
                error_log("[AJAX DEBUG RECOVERY] Récupéré depuis DB: '$debug_field' = '$db_value'");
            }
        }

        // Save the settings array
        update_option('pdf_builder_settings', $settings);
        error_log("[AJAX HANDLER] Saved " . count($settings) . " settings to pdf_builder_settings option");

        // DEBUG: Check if debug_javascript was saved
        $saved_settings_check = get_option('pdf_builder_settings', []);
        if (isset($saved_settings_check['pdf_builder_debug_javascript'])) {
            error_log("[DEBUG JAVASCRIPT TOGGLE] VERIFICATION: pdf_builder_debug_javascript = " . $saved_settings_check['pdf_builder_debug_javascript'] . " in saved settings");
        } else {
            error_log("[DEBUG JAVASCRIPT TOGGLE] VERIFICATION: pdf_builder_debug_javascript NOT FOUND in saved settings");
        }

        // DEBUG: Log all saved debug fields
        $saved_debug_fields = array_filter($saved_settings_check, function($key) {
            return strpos($key, 'debug') !== false;
        }, ARRAY_FILTER_USE_KEY);
        error_log("All saved debug fields: " . json_encode($saved_debug_fields));

        return [
            'saved_count' => $saved_count,
            'saved_settings' => $saved_settings,
            'debug_logs' => [
                'processed_fields' => array_keys($saved_settings),
                'total_processed' => count($saved_settings),
                'pdf_builder_canvas_debug_enabled_present' => isset($saved_settings['pdf_builder_canvas_debug_enabled']),
                'pdf_builder_canvas_debug_enabled_value' => $saved_settings['pdf_builder_canvas_debug_enabled'] ?? 'not_set',
                'all_post_fields' => array_keys($_POST),
                'handler_executed' => true,
                // LOGS SPÉCIFIQUES POUR DEBUG
                'debug_fields_in_saved_settings' => array_filter($saved_settings, function($key) {
                    return strpos($key, 'debug') !== false;
                }, ARRAY_FILTER_USE_KEY),
                'debug_javascript_value' => $saved_settings['pdf_builder_debug_javascript'] ?? 'NOT_IN_SAVED_SETTINGS'
            ]
        ];
    }
}

/**
 * Handler AJAX pour les templates
 */
class PDF_Builder_Template_Ajax_Handler extends PDF_Builder_Ajax_Base {
    public function handle() {
        try {
            $this->validate_request();

            $action = $this->validate_required_param('template_action');

            switch ($action) {
                case 'save':
                    $this->handle_save_template();
                    break;
                case 'load':
                    $this->handle_load_template();
                    break;
                case 'delete':
                    $this->handle_delete_template();
                    break;
                default:
                    $this->send_error('Action template inconnue', 400);
            }

        } catch (Exception $e) {
            $this->log_error('Erreur template AJAX: ' . $e->getMessage());
            $this->send_error('Erreur interne du serveur', 500);
        }
    }

    private function handle_save_template() {
        $template_id = $this->validate_required_param('template_id', 'int');
        $template_data = $this->validate_required_param('template_data', 'json');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->update(
            $table_templates,
            [
                'template_data' => wp_json_encode($template_data),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            $this->send_error('Erreur lors de la sauvegarde', 500);
        }

        $this->send_success(['template_id' => $template_id], 'Template sauvegardé avec succès');
    }

    private function handle_load_template() {
        $template_id = $this->validate_required_param('template_id', 'int');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            $this->send_error('Template non trouvé', 404);
        }

        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->send_error('Erreur de décodage JSON', 500);
        }

        $this->send_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);
    }

    private function handle_delete_template() {
        $template_id = $this->validate_required_param('template_id', 'int');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->delete($table_templates, ['id' => $template_id], ['%d']);

        if ($result === false) {
            $this->send_error('Erreur lors de la suppression', 500);
        }

        $this->send_success([], 'Template supprimé avec succès');
    }
}

// Fonction d'initialisation des handlers AJAX
/**
 * Initialise les handlers AJAX unifiés pour PDF Builder Pro
 *
 * SYSTÈME CENTRALISÉ DE SAUVEGARDE :
 * ================================
 *
 * 1. PDF_Builder_Settings_Ajax_Handler (settings-tabs.js)
 *    - Action: pdf_builder_save_all_settings
 *    - Gère: Tous les paramètres principaux via le bouton flottant
 *    - Stockage: pdf_builder_settings (array), pdf_builder_canvas_settings (array)
 *
 * 2. PDF_Builder_Template_Ajax_Handler (templates)
 *    - Actions: pdf_builder_save_template, pdf_builder_load_template, pdf_builder_delete_template
 *    - Gère: Sauvegarde/chargement/suppression de templates
 *    - Stockage: Tables wp_pdf_builder_templates
 *
 * 3. Handlers spécialisés (cache-handlers.php, etc.)
 *    - Gèrent leurs domaines spécifiques (cache, maintenance, etc.)
 *    - Stockage: Leurs propres options WordPress
 */
function pdf_builder_init_ajax_handlers() {
    // Settings handler - Système unifié principal
    $settings_handler = new PDF_Builder_Settings_Ajax_Handler();
    add_action('wp_ajax_pdf_builder_save_all_settings', [$settings_handler, 'handle']);

    // Template handler - Gestion des templates
    $template_handler = new PDF_Builder_Template_Ajax_Handler();
    add_action('wp_ajax_pdf_builder_save_template', [$template_handler, 'handle']);
    add_action('wp_ajax_pdf_builder_load_template', [$template_handler, 'handle']);
    add_action('wp_ajax_pdf_builder_delete_template', [$template_handler, 'handle']);
}

// Initialiser les handlers unifiés
add_action('init', 'pdf_builder_init_ajax_handlers');
?>