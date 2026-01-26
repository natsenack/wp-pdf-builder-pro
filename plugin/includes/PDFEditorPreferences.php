<?php

namespace PDFBuilderPro\V2;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Système de préférences utilisateur pour l'éditeur PDF
 * Remplace wp-preferences qui cause des conflits API REST
 */
class PDFEditorPreferences {

    private static $instance = null;
    private $user_id;
    private $option_key = 'pdf_builder_editor_preferences';

    private function __construct() {
        $this->user_id = get_current_user_id();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Récupère une préférence utilisateur
     */
    public function get($key, $default = null) {
        $preferences = $this->getAll();
        return isset($preferences[$key]) ? $preferences[$key] : $default;
    }

    /**
     * Définit une préférence utilisateur
     */
    public function set($key, $value) {
        $preferences = $this->getAll();
        $preferences[$key] = $value;
        return $this->saveAll($preferences);
    }

    /**
     * Supprime une préférence utilisateur
     */
    public function delete($key) {
        $preferences = $this->getAll();
        unset($preferences[$key]);
        return $this->saveAll($preferences);
    }

    /**
     * Récupère toutes les préférences
     */
    public function getAll() {
        if (!$this->user_id) {
            return array();
        }

        $all_preferences = get_user_meta($this->user_id, $this->option_key, true);
        return is_array($all_preferences) ? $all_preferences : array();
    }

    /**
     * Sauvegarde toutes les préférences
     */
    private function saveAll($preferences) {
        if (!$this->user_id) {
            return false;
        }

        return update_user_meta($this->user_id, $this->option_key, $preferences);
    }

    /**
     * AJAX handler pour sauvegarder une préférence
     */
    public static function ajax_save_preference() {
        // Vérification de sécurité
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_editor_preferences')) {
            wp_send_json_error('Nonce invalide');
        }

        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
        $value = isset($_POST['value']) ? $_POST['value'] : null;

        if (empty($key)) {
            wp_send_json_error('Clé manquante');
        }

        $instance = self::getInstance();

        if ($value === null) {
            // Supprimer la préférence
            $result = $instance->delete($key);
        } else {
            // Sauvegarder la préférence
            $result = $instance->set($key, $value);
        }

        if ($result) {
            wp_send_json_success(array('key' => $key, 'value' => $value));
        } else {
            wp_send_json_error('Erreur lors de la sauvegarde');
        }
    }

    /**
     * AJAX handler pour récupérer une préférence
     */
    public static function ajax_get_preference() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_editor_preferences')) {
            wp_send_json_error('Nonce invalide');
        }

        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
        $default = isset($_POST['default']) ? $_POST['default'] : null;

        if (empty($key)) {
            wp_send_json_error('Clé manquante');
        }

        $instance = self::getInstance();
        $value = $instance->get($key, $default);

        wp_send_json_success(array('key' => $key, 'value' => $value));
    }

    /**
     * Enregistre les handlers AJAX
     */
    public static function register_ajax_handlers() {
        add_action('wp_ajax_pdf_editor_save_preference', array(self::class, 'ajax_save_preference'));
        add_action('wp_ajax_pdf_editor_get_preference', array(self::class, 'ajax_get_preference'));
    }

    /**
     * Injecte le script JavaScript pour les préférences
     */
    public static function inject_script() {
        if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-react-editor') {
            return;
        }

        $nonce = wp_create_nonce('pdf_editor_preferences');
        ?>
        <script type="text/javascript">
        (function() {
            'use strict';

            // Système de préférences propre pour l'éditeur PDF
            window.PDFEditorPreferences = {
                nonce: '<?php echo $nonce; ?>',
                ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',

                /**
                 * Sauvegarde une préférence
                 */
                set: function(key, value) {
                    return this._ajaxRequest('pdf_editor_save_preference', {
                        key: key,
                        value: value
                    });
                },

                /**
                 * Récupère une préférence
                 */
                get: function(key, defaultValue) {
                    return this._ajaxRequest('pdf_editor_get_preference', {
                        key: key,
                        default: defaultValue
                    }).then(function(response) {
                        return response.data.value;
                    });
                },

                /**
                 * Supprime une préférence
                 */
                delete: function(key) {
                    return this._ajaxRequest('pdf_editor_save_preference', {
                        key: key,
                        value: null
                    });
                },

                /**
                 * Effectue une requête AJAX
                 */
                _ajaxRequest: function(action, data) {
                    return jQuery.ajax({
                        url: this.ajaxUrl,
                        type: 'POST',
                        data: jQuery.extend({
                            action: action,
                            nonce: this.nonce
                        }, data),
                        dataType: 'json'
                    });
                }
            };

            console.log('[PDF Editor] Preferences system initialized');
        })();
        </script>
        <?php
    }
}

// Enregistrement des handlers AJAX
PDFEditorPreferences::register_ajax_handlers();

// Injection du script sur la page de l'éditeur
add_action('admin_head', array(PDFEditorPreferences::class, 'inject_script'));