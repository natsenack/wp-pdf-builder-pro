<?php
/**
 * PDF Builder Pro - Classe de base pour les handlers AJAX
 * Centralise la validation commune et la gestion d'erreurs
 */

// Déclarations des fonctions WordPress pour l'IDE
if (!function_exists('wp_roles')) {
    function wp_roles() { return null; }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return $value; }
}

// error_log('PDF Builder: [AJAX_HANDLERS.PHP] File loaded at ' . current_time('Y-m-d H:i:s'));

/**
 * Fonction utilitaire pour sauvegarder les rôles autorisés
 * @param mixed $value Valeur brute des rôles
 * @return array Tableau des rôles traités
 */
function pdf_builder_save_allowed_roles($value) {
    // error_log("[PDF_BUILDER_SAVE_ALLOWED_ROLES] Processing value: " . print_r($value, true));

    $roles = array();

    // Si c'est déjà un tableau, l'utiliser directement
    if (is_array($value)) {
        $roles = $value;
    }
    // Si c'est un string JSON, le décoder
    elseif (is_string($value)) {
        if (strpos($value, '[') === 0 || strpos($value, '{') === 0) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $roles = $decoded;
            }
        } else {
            // Si c'est une liste séparée par des virgules
            $roles = array_map('trim', explode(',', $value));
        }
    }

    // Filtrer et valider les rôles
    $valid_roles = array();
    $wp_roles = wp_roles();
    $available_roles = $wp_roles ? array_keys($wp_roles->roles) : array();

    foreach ($roles as $role) {
        if (in_array($role, $available_roles)) {
            $valid_roles[] = $role;
        }
    }

    // error_log("[PDF_BUILDER_SAVE_ALLOWED_ROLES] Final roles: " . json_encode($valid_roles));
    return $valid_roles;
}

abstract class PDF_Builder_Ajax_Base {
    protected $required_capability = 'manage_options';
    protected $nonce_action = 'pdf_builder_ajax';

    /**
     * Valide la requête AJAX de base
     */
    protected function validate_request() {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !pdf_builder_verify_nonce($_POST['nonce'], $this->nonce_action)) {
            $this->send_error('Nonce invalide', 403);
        }

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
                $value = \sanitize_text_field($value);
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
            'timestamp' => \current_time('timestamp')
        ]);
        exit;
    }

    /**
     * Envoie une réponse de succès standardisée
     */
    protected function send_success($data = [], $message = 'Opération réussie') {
        wp_send_json_success(array_merge([
            'message' => $message,
            'timestamp' => \current_time('timestamp')
        ], $data));
        exit;
    }

    /**
     * Log une erreur pour le debugging
     */
    protected function log_error($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            // error_log('[PDF Builder AJAX] ' . $message . $context_str);
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
                // LOGS ACTIFS POUR DIAGNOSTIC RÉPONSE AJAX
                // Send updated settings
        error_log("[DEBUG AJAX] About to send success response with saved_settings");
                // Send updated settings
        error_log("[DEBUG AJAX] saved_settings count: " . count($result['saved_settings']));
                // Send updated settings
        error_log("[DEBUG AJAX] Canvas fields in response: " . json_encode(array_filter($result['saved_settings'], function($key) {
                    return strpos($key, 'pdf_builder_canvas_') === 0;
                }, ARRAY_FILTER_USE_KEY)));

                $response_data = [
                    'saved_count' => $result['saved_count'],
                    'saved_settings' => $result['saved_settings'],
                    'new_nonce' => wp_create_nonce($this->nonce_action),
                    // DEBUG INFO FOR JAVASCRIPT
                    'debug_info' => [
                        'saved_settings_count' => count($result['saved_settings']),
                        'has_saved_settings' => isset($result['saved_settings']) && is_array($result['saved_settings']),
                        'canvas_fields_count' => count(array_filter($result['saved_settings'], function($key) {
                            return strpos($key, 'pdf_builder_canvas_') === 0;
                        }, ARRAY_FILTER_USE_KEY)),
                        'all_keys' => array_keys($result['saved_settings'])
                    ]
                ];

                $this->send_success($response_data, 'Paramètres sauvegardés avec succès');
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
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // DEBUG: Log that this function is being executed
        // error_log("[AJAX HANDLER] process_all_settings called");
        // error_log("[AJAX HANDLER] POST data received: " . json_encode(array_keys($_POST)));

        // Check if form_data is sent as JSON (legacy) or flattened data
        if (isset($_POST['form_data'])) {
            $form_data_json = stripslashes($_POST['form_data']);
            $all_form_data = json_decode($form_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // error_log('JSON decode error: ' . json_last_error_msg() . ' for data: ' . substr($form_data_json, 0, 500));
                throw new Exception('Données JSON invalides: ' . json_last_error_msg());
            }
            // error_log("[AJAX HANDLER] Parsed legacy form_data successfully, forms: " . implode(', ', array_keys($all_form_data)));
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
            // error_log("[AJAX HANDLER] Using flattened data directly from POST, fields: " . count($flattened_data));
            // error_log("[AJAX HANDLER] ALL POST FIELDS: " . json_encode(array_keys($_POST)));
            // error_log("[AJAX HANDLER] pdf_builder_allowed_roles in POST: " . (isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : 'NOT_FOUND'));
        }

        // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
        // error_log("=== AJAX HANDLER DEBUG JAVASCRIPT ANALYSIS ===");
        // error_log("pdf_builder_debug_javascript in flattened_data: " . (isset($flattened_data['pdf_builder_debug_javascript']) ? $flattened_data['pdf_builder_debug_javascript'] : 'NOT_SET'));
        // error_log("debug_javascript in flattened_data: " . (isset($flattened_data['debug_javascript']) ? $flattened_data['debug_javascript'] : 'NOT_SET'));

        // DEBUG: Log all debug-related fields
        $debug_fields = array_filter($flattened_data, function($key) {
            return strpos($key, 'debug') !== false;
        }, ARRAY_FILTER_USE_KEY);
        // error_log("All debug fields in request: " . json_encode($debug_fields));

        // Définir les règles de validation des champs (même que dans settings-main.php)
        $field_rules = [
            'text_fields' => [
                'company_phone_manual', 'company_siret', 'company_vat', 'company_rcs', 'company_capital',
                'pdf_quality', 'default_format', 'default_orientation', 'default_template',
                'pdf_builder_developer_password',
                // Canvas text fields
                'canvas_bg_color', 'canvas_border_color', 'canvas_container_bg_color', 'canvas_selection_mode', 'canvas_format', 'canvas_export_format',
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
            // error_log("[AJAX HANDLER] Processing field: '$key' = '$value'");

            // Extract short key for validation
            $short_key = $key;
            $is_prefixed = false;
            if (strpos($key, 'pdf_builder_') === 0) {
                $short_key = substr($key, 13); // Remove 'pdf_builder_' prefix
                $is_prefixed = true;
            }

            $option_key = '';
            $option_value = null;

            if (in_array($short_key, $field_rules['text_fields'])) {
                if ($is_prefixed) {
                    $option_key = $key; // Already has prefix
                } else {
                    $option_key = 'pdf_builder_' . $key;
                }
                $option_value = \sanitize_text_field($value ?? '');
                if ($is_prefixed) {
                    $settings[$option_key] = $option_value;
                }
                $saved_settings[$option_key] = $option_value; // Add to saved_settings for AJAX response
            } elseif (in_array($short_key, $field_rules['int_fields'])) {
                if ($is_prefixed) {
                    $option_key = $key; // Already has prefix
                } else {
                    $option_key = 'pdf_builder_' . $key;
                }
                $option_value = intval($value ?? 0);
                if ($is_prefixed) {
                    $settings[$option_key] = $option_value;
                }
                $saved_settings[$option_key] = $option_value; // Add to saved_settings for AJAX response
            } elseif (in_array($short_key, $field_rules['bool_fields'])) {
                if ($is_prefixed) {
                    $option_key = $key; // Already has prefix
                } else {
                    $option_key = 'pdf_builder_' . $key;
                }
                $option_value = isset($flattened_data[$key]) && $flattened_data[$key] === '1' ? 1 : 0;
                if ($is_prefixed) {
                    $settings[$option_key] = $option_value;
                }
                $saved_settings[$option_key] = $option_value; // Add to saved_settings for AJAX response
                if (strpos($short_key, 'debug_javascript') !== false) {
                    // DEBUG SPECIFIC FOR JAVASCRIPT DEBUG
                    // error_log("[DEBUG JAVASCRIPT TOGGLE] Processing debug_javascript:");
                    // error_log("  - key: '$key'");
                    // error_log("  - option_key: '$option_key'");
                    // error_log("  - isset in flattened_data: " . (isset($flattened_data[$key]) ? 'YES' : 'NO'));
                    // error_log("  - value in flattened_data: '" . ($flattened_data[$key] ?? 'NULL') . "'");
                    // error_log("  - calculated option_value: $option_value");
                    // error_log("  - will save to settings['$option_key'] = $option_value");
                }
                if (strpos($short_key, 'canvas_') === 0 || strpos($short_key, 'zoom_') === 0 || strpos($short_key, 'default_canvas_') === 0) {
                    update_option($option_key, $option_value); // Canvas fields saved separately
                }
                $saved_count++;

                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                if (strpos($key, 'debug_javascript') !== false) {
                    // error_log("[AJAX DEBUG JAVASCRIPT] Processing debug_javascript field: key='$key', option_key='$option_key', value='$option_value'");
                    // VÉRIFIER LA SAUVEGARDE EN BDD APRÈS UPDATE
                    $db_value_after = get_option($option_key, 'NOT_FOUND_AFTER_UPDATE');
                    // error_log("[AJAX DEBUG JAVASCRIPT] VERIFICATION BDD APRES SAUVEGARDE: get_option('$option_key') = '$db_value_after'");
                }

                // MISE À JOUR DES PARAMÈTRES CANVAS POUR LES CHAMPS DEBUG
                if (preg_match('/debug_(.+)/', $key, $matches)) {
                    $debug_key = $matches[1];
                    $canvas_settings = pdf_builder_get_option('pdf_builder_canvas_settings', []);
                    if (!isset($canvas_settings['debug'])) {
                        $canvas_settings['debug'] = [];
                    }
                    $canvas_settings['debug'][$debug_key] = $option_value;
                    pdf_builder_update_option('pdf_builder_canvas_settings', $canvas_settings);
                    // error_log("[AJAX DEBUG] Updated canvas settings debug.$debug_key = $option_value");
                }
            } elseif (in_array($short_key, $field_rules['array_fields'])) {
                if (is_array($value)) {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = array_map('sanitize_text_field', $value);
                    $settings[$option_key] = $option_value;
                } else {
                    // Unslash the value first (WordPress slashes POST data)
                    $value = wp_unslash($value);
                    // Traiter les JSON strings pour les arrays
                    if (\is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
                        $decoded = \json_decode($value, true);
                        if (\json_last_error() === JSON_ERROR_NONE) {
                            $option_value = array_map('sanitize_text_field', $decoded);
                        } else {
                            $option_value = [];
                        }
                    } else {
                        $option_value = [];
                    }
                    $option_key = 'pdf_builder_' . $key;
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
                // error_log("[AJAX HANDLER] Array field processed: '$key' = " . json_encode($option_value));
            } else {
                // Pour les champs non définis, essayer de deviner le type
                if (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    
                    // TRAITEMENT SPÉCIAL POUR LES RÔLES AUTORISÉS
                    if ($key === 'pdf_builder_allowed_roles') {
                        // error_log("[AJAX HANDLER] SPECIAL HANDLING: Processing pdf_builder_allowed_roles");
                        // error_log("[AJAX HANDLER] SPECIAL HANDLING: Raw value received: '" . $value . "'");
                        // error_log("[AJAX HANDLER] SPECIAL HANDLING: Type of value: " . gettype($value));

                        // Utiliser la fonction spécialisée pour les rôles
                        if (function_exists('pdf_builder_save_allowed_roles')) {
                            // error_log("[AJAX HANDLER] SPECIAL HANDLING: pdf_builder_save_allowed_roles function exists, calling it");
                            $saved_roles = pdf_builder_save_allowed_roles($value);
                            $option_value = $saved_roles;
                            // error_log("[AJAX HANDLER] SPECIAL HANDLING: Saved roles: " . json_encode($saved_roles));

                            // Vérifier immédiatement si la sauvegarde a fonctionné
                            $settings_check = pdf_builder_get_option('pdf_builder_settings', array());
                            if (isset($settings_check['pdf_builder_allowed_roles'])) {
                                // error_log("[AJAX HANDLER] SPECIAL HANDLING: VERIFICATION - Roles saved in DB: " . json_encode($settings_check['pdf_builder_allowed_roles']));
                            } else {
                                // error_log("[AJAX HANDLER] SPECIAL HANDLING: VERIFICATION - Roles NOT found in DB after save!");
                            }
                        } else {
                            // error_log("[AJAX HANDLER] SPECIAL HANDLING: ERROR - pdf_builder_save_allowed_roles function NOT found!");
                            // Fallback si la fonction n'existe pas
                            if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
                                $decoded = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $option_value = $decoded;
                                } else {
                                    $option_value = [];
                                }
                            } else {
                                $option_value = is_array($value) ? $value : [];
                            }
                            $settings[$option_key] = $option_value;
                        }
                    } elseif (is_numeric($value)) {
                        $option_value = intval($value);
                        $settings[$option_key] = $option_value;
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                        $settings[$option_key] = $option_value;
                    } else {
                        $option_value = \sanitize_text_field($value ?? '');
                        $settings[$option_key] = $option_value;
                    }
                } else {
                    // Add prefix
                    $option_key = 'pdf_builder_' . $key;
                    if (is_numeric($value)) {
                        $option_value = intval($value);
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                    } else {
                        $option_value = \sanitize_text_field($value ?? '');
                    }
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            }

            // Ajouter à saved_settings si une clé a été définie
            if (!empty($option_key)) {
                $saved_settings[$option_key] = $option_value;
                // DEBUG: Log saved_settings addition
                // error_log("[AJAX DEBUG] Added to saved_settings: '$option_key' = '$option_value'");
                
                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT DANS SAVED_SETTINGS
                if (strpos($option_key, 'debug_javascript') !== false) {
                    // error_log("[AJAX DEBUG JAVASCRIPT] AJOUTÉ À SAVED_SETTINGS: '$option_key' => '$option_value'");
                    // error_log("[AJAX DEBUG JAVASCRIPT] TOTAL SAVED_SETTINGS: " . count($saved_settings));
                }
            }
        }

        // S'assurer que tous les champs debug sont dans saved_settings
        foreach (['pdf_builder_debug_javascript', 'pdf_builder_debug_javascript_verbose', 'pdf_builder_debug_ajax', 'pdf_builder_debug_performance', 'pdf_builder_debug_database', 'pdf_builder_debug_php_errors'] as $debug_field) {
            if (!isset($saved_settings[$debug_field])) {
                $db_value = get_option($debug_field, 0);
                $saved_settings[$debug_field] = $db_value;
                // error_log("[AJAX DEBUG RECOVERY] Récupéré depuis DB: '$debug_field' = '$db_value'");
            }
        }

        // Save the settings array
        pdf_builder_update_option('pdf_builder_settings', $settings);
        // error_log("[AJAX HANDLER] Saved " . count($settings) . " settings to pdf_builder_settings option");

        // DEBUG: Check if debug_javascript was saved
        $saved_settings_check = pdf_builder_get_option('pdf_builder_settings', array());
        if (isset($saved_settings_check['pdf_builder_debug_javascript'])) {
            // error_log("[DEBUG JAVASCRIPT TOGGLE] VERIFICATION: pdf_builder_debug_javascript = " . $saved_settings_check['pdf_builder_debug_javascript'] . " in saved settings");
        } else {
            // error_log("[DEBUG JAVASCRIPT TOGGLE] VERIFICATION: pdf_builder_debug_javascript NOT FOUND in saved settings");
        }

        // DEBUG: Log all saved debug fields
        $saved_debug_fields = array_filter($saved_settings_check, function($key) {
            return strpos($key, 'debug') !== false;
        }, ARRAY_FILTER_USE_KEY);
        // error_log("All saved debug fields: " . json_encode($saved_debug_fields));

        // LOGS ACTIFS POUR DIAGNOSTIC CANVAS
        // Send updated settings
        error_log("[DEBUG AJAX] Canvas fields in response: " . json_encode(array_filter($saved_settings, function($key) {
            return strpos($key, 'pdf_builder_canvas_') === 0;
        }, ARRAY_FILTER_USE_KEY)));
        // Send updated settings
        error_log("[DEBUG AJAX] Total canvas fields in saved_settings: " . count(array_filter($saved_settings, function($key) {
            return strpos($key, 'pdf_builder_canvas_') === 0;
        }, ARRAY_FILTER_USE_KEY)));
        // Send updated settings
        error_log("[DEBUG AJAX] Total saved_settings count: " . count($saved_settings));
        // Send updated settings
        error_log("[DEBUG AJAX] saved_settings keys: " . json_encode(array_keys($saved_settings)));

        return [
            'saved_count' => $saved_count,
            'saved_settings' => $this->format_saved_settings_for_js($saved_settings),
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
                'debug_javascript_value' => $saved_settings['pdf_builder_debug_javascript'] ?? 'NOT_IN_SAVED_SETTINGS',
                // LOGS POUR LES CHAMPS CANVAS
                'canvas_fields_in_saved_settings' => array_filter($saved_settings, function($key) {
                    return strpos($key, 'pdf_builder_canvas_') === 0;
                }, ARRAY_FILTER_USE_KEY),
                'total_canvas_fields' => count(array_filter($saved_settings, function($key) {
                    return strpos($key, 'pdf_builder_canvas_') === 0;
                }, ARRAY_FILTER_USE_KEY))
            ]
        ];
    }

    /**
     * Formate les paramètres sauvegardés pour le JavaScript (clés courtes)
     */
    private function format_saved_settings_for_js($saved_settings) {
        $formatted = [];

        // Mapping des clés pour le JavaScript
        $key_mapping = [
            'pdf_builder_canvas_width' => 'canvas_width',
            'pdf_builder_canvas_height' => 'canvas_height',
            'pdf_builder_canvas_dpi' => 'canvas_dpi',
            'pdf_builder_canvas_format' => 'canvas_format',
            'pdf_builder_canvas_bg_color' => 'canvas_bg_color',
            'pdf_builder_canvas_border_color' => 'canvas_border_color',
            'pdf_builder_canvas_border_width' => 'canvas_border_width',
            'pdf_builder_canvas_container_bg_color' => 'canvas_container_bg_color',
            'pdf_builder_canvas_shadow_enabled' => 'canvas_shadow_enabled',
            'pdf_builder_canvas_grid_enabled' => 'canvas_grid_enabled',
            'pdf_builder_canvas_grid_size' => 'canvas_grid_size',
            'pdf_builder_canvas_guides_enabled' => 'canvas_guides_enabled',
            'pdf_builder_canvas_snap_to_grid' => 'canvas_snap_to_grid',
            'pdf_builder_canvas_zoom_min' => 'canvas_zoom_min',
            'pdf_builder_canvas_zoom_max' => 'canvas_zoom_max',
            'pdf_builder_canvas_zoom_default' => 'canvas_zoom_default',
            'pdf_builder_canvas_zoom_step' => 'canvas_zoom_step',
            'pdf_builder_canvas_export_quality' => 'canvas_export_quality',
            'pdf_builder_canvas_export_format' => 'canvas_export_format',
            'pdf_builder_canvas_export_transparent' => 'canvas_export_transparent',
            'pdf_builder_canvas_drag_enabled' => 'canvas_drag_enabled',
            'pdf_builder_canvas_resize_enabled' => 'canvas_resize_enabled',
            'pdf_builder_canvas_rotate_enabled' => 'canvas_rotate_enabled',
            'pdf_builder_canvas_multi_select' => 'canvas_multi_select',
            'pdf_builder_canvas_selection_mode' => 'canvas_selection_mode',
            'pdf_builder_canvas_keyboard_shortcuts' => 'canvas_keyboard_shortcuts',
            'pdf_builder_canvas_fps_target' => 'canvas_fps_target',
            'pdf_builder_canvas_memory_limit_js' => 'canvas_memory_limit_js',
            'pdf_builder_canvas_response_timeout' => 'canvas_response_timeout',
            'pdf_builder_canvas_lazy_loading_editor' => 'canvas_lazy_loading_editor',
            'pdf_builder_canvas_preload_critical' => 'canvas_preload_critical',
            'pdf_builder_canvas_lazy_loading_plugin' => 'canvas_lazy_loading_plugin',
            'pdf_builder_canvas_debug_enabled' => 'canvas_debug_enabled',
            'pdf_builder_canvas_performance_monitoring' => 'canvas_performance_monitoring',
            'pdf_builder_canvas_error_reporting' => 'canvas_error_reporting',
            'pdf_builder_canvas_memory_limit_php' => 'canvas_memory_limit_php'
        ];

        // Convertir les clés pour le JavaScript
        foreach ($saved_settings as $full_key => $value) {
            if (isset($key_mapping[$full_key])) {
                $formatted[$key_mapping[$full_key]] = $value;
            }
        }

        error_log("[DEBUG AJAX] Formatted saved_settings for JS: " . json_encode($formatted));

        return $formatted;
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
                'updated_at' => \current_time('mysql')
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

// Inclure les fonctions utilitaires pour les paramètres
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
    // add_action('wp_ajax_pdf_builder_delete_template', [$template_handler, 'handle']); // Désactivé pour éviter conflit
}

// Initialiser les handlers unifiés
add_action('init', 'pdf_builder_init_ajax_handlers');

/**
 * AJAX Handler pour récupérer les rôles autorisés
 */
function pdf_builder_test_roles_handler() {
    // error_log('PDF Builder: [TEST ROLES HANDLER] ===== DÉBUT DU HANDLER =====');
    // error_log('PDF Builder: [TEST ROLES HANDLER] Timestamp: ' . current_time('Y-m-d H:i:s'));
    // error_log('PDF Builder: [TEST ROLES HANDLER] POST data: ' . print_r($_POST, true));
    // error_log('PDF Builder: [TEST ROLES HANDLER] REQUEST data: ' . print_r($_REQUEST, true));
    
    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        // error_log('PDF Builder: [TEST ROLES HANDLER] Nonce invalide ou manquant');
        wp_send_json_error(['message' => 'Nonce invalide'], 403);
        return;
    }
    // error_log('PDF Builder: [TEST ROLES HANDLER] Nonce vérifié avec succès');
    
    // Récupérer les rôles autorisés depuis les paramètres
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    // error_log('PDF Builder: [TEST ROLES HANDLER] Full settings from DB: ' . print_r($settings, true));
    $allowed_roles_raw = isset($settings['pdf_builder_allowed_roles']) ? $settings['pdf_builder_allowed_roles'] : ['administrator'];
    // error_log('PDF Builder: [TEST ROLES HANDLER] Raw allowed_roles from DB: ' . print_r($allowed_roles_raw, true));
    // error_log('PDF Builder: [TEST ROLES HANDLER] Type of allowed_roles_raw: ' . gettype($allowed_roles_raw));
    
    // S'assurer que c'est un tableau
    if (!is_array($allowed_roles_raw)) {
        // error_log('PDF Builder: [TEST ROLES HANDLER] Converting to array (was not array)');
        $allowed_roles = ['administrator'];
    } else {
        $allowed_roles = $allowed_roles_raw;
    }
    
    // Filtrer les rôles vides
    $allowed_roles = array_filter($allowed_roles);
    // error_log('PDF Builder: [TEST ROLES HANDLER] After array_filter: ' . print_r($allowed_roles, true));
    
    // Si aucun rôle, utiliser administrator par défaut
    if (empty($allowed_roles)) {
        // error_log('PDF Builder: [TEST ROLES HANDLER] Empty array, using default administrator');
        $allowed_roles = ['administrator'];
    }
    
    $final_roles = array_values($allowed_roles);
    // error_log('PDF Builder: [TEST ROLES HANDLER] Final roles to return: ' . print_r($final_roles, true));
    // error_log('PDF Builder: [TEST ROLES HANDLER] Count: ' . count($final_roles));
    
    $response = [
        'allowed_roles' => $final_roles,
        'count' => count($final_roles),
        'status' => 'handler_called',
        'timestamp' => \current_time('timestamp')
    ];
    
    // error_log('PDF Builder: [TEST ROLES HANDLER] Response: ' . print_r($response, true));
    // error_log('PDF Builder: [TEST ROLES HANDLER] ===== FIN DU HANDLER =====');
    
    wp_send_json_success($response);
}

/**
 * Handler AJAX pour réinitialiser les paramètres canvas par défaut
 */
function pdf_builder_reset_canvas_defaults_handler() {
    error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] ===== DÉBUT DU HANDLER =====');

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_nonce($_POST['nonce'], 'reset_canvas_defaults')) {
        error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Nonce invalide');
        wp_send_json_error(['message' => 'Nonce invalide'], 403);
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Permissions insuffisantes');
        wp_send_json_error(['message' => 'Permissions insuffisantes'], 403);
        return;
    }

    try {
        // Paramètres par défaut du canvas
        $default_canvas_settings = [
            // Dimensions & Format
            'pdf_builder_canvas_width' => '794',
            'pdf_builder_canvas_height' => '1123',
            'pdf_builder_canvas_dpi' => '96',
            'pdf_builder_canvas_format' => 'A4',

            // Apparence
            'pdf_builder_canvas_bg_color' => '#ffffff',
            'pdf_builder_canvas_border_color' => '#cccccc',
            'pdf_builder_canvas_border_width' => '1',
            'pdf_builder_canvas_shadow_enabled' => '0',
            'pdf_builder_canvas_container_bg_color' => '#f8f9fa',

            // Grille
            'pdf_builder_canvas_grid_enabled' => '1',
            'pdf_builder_canvas_grid_size' => '20',
            'pdf_builder_canvas_guides_enabled' => '1',
            'pdf_builder_canvas_snap_to_grid' => '1',

            // Zoom
            'pdf_builder_canvas_zoom_min' => '25',
            'pdf_builder_canvas_zoom_max' => '500',
            'pdf_builder_canvas_zoom_default' => '100',
            'pdf_builder_canvas_zoom_step' => '25',

            // Export
            'pdf_builder_canvas_export_quality' => '90',
            'pdf_builder_canvas_export_format' => 'png',
            'pdf_builder_canvas_export_transparent' => '0',

            // Interaction
            'pdf_builder_canvas_drag_enabled' => '1',
            'pdf_builder_canvas_resize_enabled' => '1',
            'pdf_builder_canvas_rotate_enabled' => '0',
            'pdf_builder_canvas_multi_select' => '1',
            'pdf_builder_canvas_selection_mode' => 'single',

            // Performance
            'pdf_builder_canvas_fps_target' => '60',
            'pdf_builder_canvas_memory_limit_js' => '256',
            'pdf_builder_canvas_memory_limit_php' => '256',
            'pdf_builder_canvas_response_timeout' => '30',

            // Optimisation
            'pdf_builder_canvas_lazy_loading_editor' => '1',
            'pdf_builder_canvas_lazy_loading_plugin' => '1',
            'pdf_builder_canvas_preload_critical' => '1',

            // Debug
            'pdf_builder_canvas_debug_enabled' => '0',
            'pdf_builder_canvas_performance_monitoring' => '0',
            'pdf_builder_canvas_error_reporting' => '0'
        ];

        // Récupérer les paramètres actuels
        $current_settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Fusionner avec les valeurs par défaut (conserver les autres paramètres non-canvas)
        $updated_settings = array_merge($current_settings, $default_canvas_settings);

        // Sauvegarder les paramètres individuellement (comme le fait le reste du système)
        $success_count = 0;
        foreach ($default_canvas_settings as $key => $value) {
            if (update_option($key, $value)) {
                $success_count++;
                // error_log("PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Reset $key to $value");
            } else {
                error_log("PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Failed to reset $key");
            }
        }

        // Aussi sauvegarder dans l'option globale pour compatibilité
        $global_result = pdf_builder_update_option('pdf_builder_settings', $updated_settings);

        if ($success_count > 0) {
            // error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Paramètres réinitialisés avec succès');
            wp_send_json_success([
                'message' => 'Paramètres canvas réinitialisés avec succès',
                'reset_count' => $success_count,
                'timestamp' => \current_time('timestamp')
            ]);
        } else {
            error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Échec de la sauvegarde - aucun paramètre n\'a pu être sauvegardé');
            wp_send_json_error(['message' => 'Échec de la sauvegarde des paramètres'], 500);
        }

    } catch (Exception $e) {
        error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] Exception: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()], 500);
    }

    error_log('PDF Builder: [RESET CANVAS DEFAULTS HANDLER] ===== FIN DU HANDLER =====');
}

/**
 * Handler AJAX pour récupérer les paramètres de debug actuels
 */
function pdf_builder_get_debug_settings_handler() {
    // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] ===== DÉBUT DU HANDLER =====');
    // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] Timestamp: ' . current_time('Y-m-d H:i:s'));
    
    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !pdf_builder_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] Nonce invalide');
        wp_send_json_error(['message' => 'Nonce invalide'], 403);
        return;
    }
    
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] Permissions insuffisantes');
        wp_send_json_error(['message' => 'Permissions insuffisantes'], 403);
        return;
    }
    
    // Récupérer les paramètres depuis la base de données
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] Settings from DB: ' . print_r($settings, true));
    
    // Extraire les paramètres de debug
    $debug_settings = [
        'debug_javascript' => isset($settings['pdf_builder_debug_javascript']) ? (bool)$settings['pdf_builder_debug_javascript'] : false,
        'debug_javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) ? (bool)$settings['pdf_builder_debug_javascript_verbose'] : false,
        'debug_ajax' => isset($settings['pdf_builder_debug_ajax']) ? (bool)$settings['pdf_builder_debug_ajax'] : false,
        'debug_performance' => isset($settings['pdf_builder_debug_performance']) ? (bool)$settings['pdf_builder_debug_performance'] : false,
        'debug_database' => isset($settings['pdf_builder_debug_database']) ? (bool)$settings['pdf_builder_debug_database'] : false,
        'debug_php_errors' => isset($settings['pdf_builder_debug_php_errors']) ? (bool)$settings['pdf_builder_debug_php_errors'] : false,
    ];
    
    // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] Debug settings to return: ' . print_r($debug_settings, true));
    // error_log('PDF Builder: [GET DEBUG SETTINGS HANDLER] ===== FIN DU HANDLER =====');
    
    wp_send_json_success($debug_settings);
}

/**
 * Handler AJAX pour vérifier la cohérence des paramètres canvas avec la base de données
 */
function pdf_builder_verify_canvas_settings_consistency_handler() {
    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Récupérer tous les paramètres canvas depuis la base de données
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Extraire seulement les paramètres canvas
        $canvas_settings = array();
        foreach ($settings as $key => $value) {
            if (strpos($key, 'pdf_builder_canvas_') === 0) {
                $canvas_settings[$key] = $value;
            }
        }

        // Log pour debug
        // Send updated settings
        error_log('PDF Builder: [VERIFY CONSISTENCY] Canvas settings from DB: ' . count($canvas_settings));

        wp_send_json_success($canvas_settings);

    } catch (Exception $e) {
        // Send updated settings
        error_log('PDF Builder: [VERIFY CONSISTENCY] Error: ' . $e->getMessage());
        wp_send_json_error('Erreur lors de la vérification: ' . $e->getMessage());
    }
}

// error_log('PDF Builder: [AJAX REGISTRATION] Registering pdf_builder_test_roles action');
add_action('wp_ajax_pdf_builder_test_roles', 'pdf_builder_test_roles_handler');

// error_log('PDF Builder: [AJAX REGISTRATION] Registering pdf_builder_get_debug_settings action');
add_action('wp_ajax_pdf_builder_get_debug_settings', 'pdf_builder_get_debug_settings_handler');
add_action('wp_ajax_pdf_builder_get_allowed_roles', 'pdf_builder_get_allowed_roles_ajax_handler');
add_action('wp_ajax_pdf_builder_reset_canvas_defaults', 'pdf_builder_reset_canvas_defaults_handler');
add_action('wp_ajax_verify_canvas_settings_consistency', 'pdf_builder_verify_canvas_settings_consistency_handler');








