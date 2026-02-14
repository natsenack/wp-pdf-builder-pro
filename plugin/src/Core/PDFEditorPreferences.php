<?php
/**
 * PDF Editor Preferences Manager
 * Remplace wp-preferences pour éviter les erreurs REST API
 */

namespace PDF_Builder\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Déclarations de fonctions WordPress pour Intelephense
 */
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) { return true; }
}

/**
 * Déclarations de fonctions WordPress pour Intelephense
 */
if (!function_exists('update_user_meta')) {
    function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {}
}
if (!function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key = '', $single = false) {}
}
if (!function_exists('wp_dequeue_script')) {
    function wp_dequeue_script($handle) {}
}
if (!function_exists('wp_deregister_script')) {
    function wp_deregister_script($handle) {}
}
if (!function_exists('wp_dequeue_style')) {
    function wp_dequeue_style($handle) {}
}
if (!function_exists('wp_deregister_style')) {
    function wp_deregister_style($handle) {}
}
if (!function_exists('wp_add_inline_script')) {
    function wp_add_inline_script($handle, $data, $position = 'after') {}
}
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {}
}
if (!function_exists('esc_js')) {
    function esc_js($text) {}
}
if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $_context = 'display') {}
}
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {}
}

class PDFEditorPreferences {

    private static $instance = null;
    private $user_id;
    private $preferences_key = 'pdf_editor_preferences';

    /**
     * Constructeur privé pour singleton
     */
    private function __construct() {
        // Différer l'initialisation de l'user_id jusqu'à ce que WordPress soit prêt
        add_action('init', array($this, 'init_user_and_hooks'));
    }

    /**
     * Initialiser l'utilisateur et les hooks une fois WordPress chargé
     */
    public function init_user_and_hooks() {
        $this->user_id = get_current_user_id();
        $this->init_hooks();
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_pdf_editor_save_preferences', array($this, 'ajax_save_preferences'));
        add_action('wp_ajax_pdf_editor_get_preferences', array($this, 'ajax_get_preferences'));
        // Enregistrer wp-preferences TRÈS TÔT - avant que WordPress n'enregistre ses propres scripts
        add_action('plugins_loaded', array($this, 'register_empty_wp_preferences'), -1000);
        // Désactiver les scripts wp-preferences par défaut sur les pages admin
        add_action('admin_enqueue_scripts', array($this, 'dequeue_wp_preferences'), -1000);
        // Charger AVANT les scripts wp-preferences par défaut
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 1);
    }

    /**
     * Enregistrer les préférences utilisateur
     */
    public function save_preferences($preferences) {
        // S'assurer que user_id est défini
        if (!$this->user_id) {
            $this->user_id = get_current_user_id();
        }

        if (!$this->user_id) {
            return false;
        }

        $sanitized = $this->sanitize_preferences($preferences);

        $result = update_user_meta($this->user_id, $this->preferences_key, $sanitized);

        // Vérifier si la sauvegarde a réussi en comparant la valeur sauvegardée
        $saved_value = get_user_meta($this->user_id, $this->preferences_key, true);
        if ($saved_value == $sanitized) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Récupérer les préférences utilisateur
     */
    public function get_preferences() {
        // S'assurer que user_id est défini
        if (!$this->user_id) {
            $this->user_id = get_current_user_id();
        }

        if (!$this->user_id) {
            return $this->get_default_preferences();
        }

        $preferences = get_user_meta($this->user_id, $this->preferences_key, true);

        if (empty($preferences)) {
            return $this->get_default_preferences();
        }

        return array_merge($this->get_default_preferences(), $preferences);
    }

    /**
     * Préférences par défaut
     */
    private function get_default_preferences() {
        return array(
            'canvas_zoom' => 100,
            'canvas_grid_visible' => true,
            'canvas_snap_to_grid' => true,
            'toolbar_position' => 'top',
            'theme' => 'light',
            'auto_save_enabled' => true,
            'auto_save_interval' => 30,
            'keyboard_shortcuts_enabled' => true,
            'show_element_outlines' => true,
            'show_element_handles' => true,
            'last_used_elements' => array(),
            'recent_templates' => array(),
            'editor_layout' => 'default'
        );
    }

    /**
     * Nettoyer les préférences
     */
    private function sanitize_preferences($preferences) {
        if (!is_array($preferences)) {
            return array();
        }

        $sanitized = array();

        // Zoom
        if (isset($preferences['canvas_zoom'])) {
            $sanitized['canvas_zoom'] = max(10, min(500, intval($preferences['canvas_zoom'])));
        }

        // Booléens
        $boolean_fields = array(
            'canvas_grid_visible',
            'canvas_snap_to_grid',
            'auto_save_enabled',
            'keyboard_shortcuts_enabled',
            'show_element_outlines',
            'show_element_handles'
        );

        foreach ($boolean_fields as $field) {
            if (isset($preferences[$field])) {
                $sanitized[$field] = (bool) $preferences[$field];
            }
        }

        // Chaînes
        $string_fields = array('toolbar_position', 'theme', 'editor_layout');
        foreach ($string_fields as $field) {
            if (isset($preferences[$field])) {
                $sanitized[$field] = sanitize_text_field($preferences[$field]);
            }
        }

        // Nombres
        if (isset($preferences['auto_save_interval'])) {
            $sanitized['auto_save_interval'] = max(5, min(300, intval($preferences['auto_save_interval'])));
        }

        // Tableaux
        $array_fields = array('last_used_elements', 'recent_templates');
        foreach ($array_fields as $field) {
            if (isset($preferences[$field]) && is_array($preferences[$field])) {
                $sanitized[$field] = array_map('sanitize_text_field', $preferences[$field]);
            }
        }

        return $sanitized;
    }

    /**
     * Handler AJAX pour sauvegarder les préférences
     */
    public function ajax_save_preferences() {
        try {
            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !\pdf_builder_verify_nonce($_POST['nonce'], 'pdf_editor_preferences')) {
                wp_send_json_error(array('message' => 'Sécurité: nonce invalide'));
                return;
            }

            // Vérifier les permissions
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array('message' => 'Permissions insuffisantes'));
                return;
            }

            // Récupérer les données
            $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : array();

            if (!is_array($preferences)) {
                $preferences = json_decode(stripslashes($preferences), true);
            }

            if (!is_array($preferences)) {
                wp_send_json_error(array('message' => 'Données de préférences invalides'));
                return;
            }

            // Sauvegarder
            $result = $this->save_preferences($preferences);

            if ($result) {
                wp_send_json_success(array(
                    'message' => 'Préférences sauvegardées',
                    'preferences' => $this->get_preferences()
                ));
            } else {
                wp_send_json_error(array('message' => 'Erreur lors de la sauvegarde'));
            }

        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'Erreur: ' . $e->getMessage()));
        }
    }

    /**
     * Handler AJAX pour récupérer les préférences
     */
    public function ajax_get_preferences() {
        try {
            // Vérifier le nonce
            if (!isset($_GET['nonce']) || !\pdf_builder_verify_nonce($_GET['nonce'], 'pdf_editor_preferences')) {
                wp_send_json_error(array('message' => 'Sécurité: nonce invalide'));
                return;
            }

            // Vérifier les permissions
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(array('message' => 'Permissions insuffisantes'));
                return;
            }

            // Récupérer les préférences
            $preferences = $this->get_preferences();

            wp_send_json_success(array('preferences' => $preferences));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'Erreur: ' . $e->getMessage()));
        }
    }

    /**
     * Désactiver les scripts wp-preferences par défaut
     */
    /**
     * Enregistrer les scripts wp-preferences vides pour éviter les erreurs de dépendance
     * Appelé très tôt (plugins_loaded avec priorité -1000) pour devancer WordPress
     */
    public function register_empty_wp_preferences() {
        // Enregistrer les scripts vides avec un source vide
        wp_register_script('wp-preferences', '', array(), null);
        wp_register_script('wp-preferences-persistence', '', array(), null);
    }

    public function dequeue_wp_preferences($hook) {
        // Désactiver les scripts wp-preferences qui causeraient des conflits
        wp_dequeue_script('wp-preferences');
        wp_dequeue_script('wp-preferences-persistence');
        wp_dequeue_style('wp-preferences');
    }

    /**
     * Enregistrer les scripts JavaScript
     */
    public function enqueue_scripts($hook) {
        // Charger sur TOUTES les pages admin pour remplacer wp-preferences
        // Plus de restriction à la page de l'éditeur seulement

        // Ajouter le script des préférences
        wp_add_inline_script('jquery', $this->get_javascript_code(), 'after');
    }

    /**
     * Générer le code JavaScript pour les préférences
     */
    private function get_javascript_code() {
        $nonce = wp_create_nonce('pdf_editor_preferences');
        $ajax_url = admin_url('admin-ajax.php');
        $current_prefs = $this->get_preferences();

        ob_start();
        ?>
        (function($) {
            'use strict';

            // Classe PDF Editor Preferences
            window.PDFEditorPreferences = {

                // Propriétés
                nonce: '<?php echo esc_js($nonce); ?>',
                ajaxUrl: '<?php echo esc_url($ajax_url); ?>',
                preferences: <?php echo wp_json_encode($current_prefs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,

                // Initialisation
                init: function() {
                    this.overrideWPPreferences();
                    this.bindEvents();
                    this.loadPreferences();
                },

                // Override WordPress preferences to use our AJAX system
                overrideWPPreferences: function() {
                    if (typeof wp !== 'undefined' && wp.preferences) {
                        var self = this;

                        // Override the transport to use AJAX instead of REST API
                        if (wp.preferences.__internalSetTransport) {
                            wp.preferences.__internalSetTransport({
                                get: function(key, defaultValue) {
                                    return Promise.resolve(self.getPreference(key, defaultValue));
                                },
                                set: function(key, value) {
                                    return new Promise(function(resolve) {
                                        self.setPreference(key, value);
                                        resolve();
                                    });
                                }
                            });
                        } else {
                            // Fallback: override direct methods
                            var originalGet = wp.preferences.get;
                            var originalSet = wp.preferences.set;

                            wp.preferences.get = function(key, defaultValue) {
                                return self.getPreference(key, defaultValue);
                            };

                            wp.preferences.set = function(key, value) {
                                self.setPreference(key, value);
                            };
                        }

                        console.log('[PDF Editor Preferences] wp.preferences transport overridden successfully');
                    } else {
                        // If wp.preferences is not ready yet, wait a bit and try again
                        var self = this;
                        setTimeout(function() {
                            self.overrideWPPreferences();
                        }, 10);
                    }
                },

                // Lier les événements
                bindEvents: function() {
                    var self = this;

                    // Écouter les changements de préférences
                    $(document).on('pdf-editor-preference-changed', function(e, key, value) {
                        self.setPreference(key, value);
                    });

                    // Sauvegarde automatique (optionnelle)
                    if (this.preferences.auto_save_enabled) {
                        setInterval(function() {
                            self.savePreferences();
                        }, this.preferences.auto_save_interval * 1000);
                    }
                },

                // Charger les préférences depuis le serveur
                loadPreferences: function() {
                    var self = this;

                    $.ajax({
                        url: this.ajaxUrl,
                        type: 'GET',
                        data: {
                            action: 'pdf_editor_get_preferences',
                            nonce: this.nonce
                        },
                        success: function(response) {
                            if (response.success && response.data.preferences) {
                                self.preferences = response.data.preferences;
                                $(document).trigger('pdf-editor-preferences-loaded', [self.preferences]);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.warn('[PDF Editor Preferences] Erreur chargement:', error);
                        }
                    });
                },

                // Sauvegarder les préférences
                savePreferences: function(callback) {
                    var self = this;

                    $.ajax({
                        url: this.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'pdf_editor_save_preferences',
                            nonce: this.nonce,
                            preferences: JSON.stringify(this.preferences)
                        },
                        success: function(response) {
                            if (response.success) {
                                $(document).trigger('pdf-editor-preferences-saved', [self.preferences]);
                                if (callback) callback(true);
                            } else {
                                console.error('[PDF Editor Preferences] Erreur sauvegarde:', response.data.message);
                                if (callback) callback(false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[PDF Editor Preferences] Erreur AJAX:', error);
                            if (callback) callback(false);
                        }
                    });
                },

                // Définir une préférence
                setPreference: function(key, value) {
                    this.preferences[key] = value;
                    $(document).trigger('pdf-editor-preference-updated', [key, value]);

                    // Sauvegarde automatique si activée
                    if (this.preferences.auto_save_enabled) {
                        this.savePreferences();
                    }
                },

                // Obtenir une préférence
                getPreference: function(key, defaultValue) {
                    return this.preferences[key] !== undefined ? this.preferences[key] : defaultValue;
                },

                // Obtenir toutes les préférences
                getAllPreferences: function() {
                    return this.preferences;
                }
            };

            // Override wp.preferences immediately when script loads
            window.PDFEditorPreferences.overrideWPPreferences();

            // Initialiser quand le DOM est prêt
            $(document).ready(function() {
                window.PDFEditorPreferences.init();
            });

        })(jQuery);
        <?php
        return ob_get_clean();
    }
}


