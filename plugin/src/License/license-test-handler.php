<?php
/**
 * License Test Handler - Gère la génération et validation des clés de test de licence
 * 
 * @package PDF_Builder_Pro
 * @version 1.0.0
 */

namespace PDF_Builder_Pro\License;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer les tests de licence
 */
class License_Test_Handler {
    
    /**
     * Instance singleton
     */
    private static $instance = null;
    
    /**
     * Préfixe des clés de test
     */
    const TEST_KEY_PREFIX = 'TEST-';
    
    /**
     * Longueur d'une clé de test
     */
    const TEST_KEY_LENGTH = 32;
    
    /**
     * Initialise l'instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise les hooks
     */
    public function init() {
        // AJAX handler pour générer une clé de test
        add_action('wp_ajax_pdf_builder_generate_test_license_key', [$this, 'handle_generate_test_key']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_test_license_key', [$this, 'handle_generate_test_key']);
        
        // AJAX handler pour valider une clé de test
        add_action('wp_ajax_pdf_builder_validate_test_license_key', [$this, 'handle_validate_test_key']);
        add_action('wp_ajax_nopriv_pdf_builder_validate_test_license_key', [$this, 'handle_validate_test_key']);
        
        // AJAX handler pour basculer le mode test
        add_action('wp_ajax_pdf_builder_toggle_test_mode', [$this, 'handle_toggle_test_mode']);
        add_action('wp_ajax_nopriv_pdf_builder_toggle_test_mode', [$this, 'handle_toggle_test_mode']);
        
        // AJAX handler pour supprimer la clé de test
        add_action('wp_ajax_pdf_builder_delete_test_license_key', [$this, 'handle_delete_test_key']);
        add_action('wp_ajax_nopriv_pdf_builder_delete_test_license_key', [$this, 'handle_delete_test_key']);
        
        // AJAX handler pour nettoyer complètement la licence
        add_action('wp_ajax_pdf_builder_cleanup_license', [$this, 'handle_cleanup_license']);
        add_action('wp_ajax_nopriv_pdf_builder_cleanup_license', [$this, 'handle_cleanup_license']);
    }
    
    /**
     * Génère une clé de test aléatoire
     * 
     * @return string Clé de test générée
     */
    public function generate_test_key() {
        // Générer une clé aléatoire
        $random_part = bin2hex(random_bytes(16)); // 32 caractères hex
        $key = self::TEST_KEY_PREFIX . strtoupper($random_part);
        
        return $key;
    }
    
    /**
     * Sauvegarde la clé de test en base de données
     * 
     * @param string $key Clé de test
     * @return bool
     */
    public function save_test_key($key) {
        return update_option('pdf_builder_license_test_key', sanitize_text_field($key));
    }
    
    /**
     * Récupère la clé de test sauvegardée
     * 
     * @return string|false Clé de test ou false
     */
    public function get_test_key() {
        return get_option('pdf_builder_license_test_key', false);
    }
    
    /**
     * Sauvegarde l'état du mode test
     * 
     * @param bool $enabled État du mode test
     * @return bool
     */
    public function set_test_mode_enabled($enabled) {
        return update_option('pdf_builder_license_test_mode_enabled', (bool) $enabled);
    }
    
    /**
     * Vérifie si le mode test est activé
     * 
     * @return bool
     */
    public function is_test_mode_enabled() {
        return (bool) get_option('pdf_builder_license_test_mode_enabled', false);
    }
    
    /**
     * Handler AJAX pour générer une clé de test
     */
    public function handle_generate_test_key() {
        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_generate_license_key')) {
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        
        try {
            // Générer une nouvelle clé
            $new_key = $this->generate_test_key();
            
            // Sauvegarder la clé
            $saved = $this->save_test_key($new_key);
            
            if (!$saved) {
                wp_send_json_error([
                    'message' => 'Impossible de sauvegarder la clé de test'
                ]);
            }
            
            // Retourner la clé générée
            wp_send_json_success([
                'key' => $new_key,
                'message' => 'Clé de test générée avec succès'
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handler AJAX pour valider une clé de test
     */
    public function handle_validate_test_key() {
        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_validate_test_license_key')) {
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        
        try {
            // Récupérer la clé à valider
            $key_to_validate = isset($_REQUEST['key']) ? sanitize_text_field($_REQUEST['key']) : '';
            
            if (empty($key_to_validate)) {
                wp_send_json_error([
                    'message' => 'Aucune clé fournie'
                ]);
            }
            
            // Vérifier que le mode test est activé
            if (!$this->is_test_mode_enabled()) {
                wp_send_json_error([
                    'message' => 'Mode test désactivé'
                ]);
            }
            
            // Récupérer la clé de test sauvegardée
            $saved_test_key = $this->get_test_key();
            
            if (empty($saved_test_key)) {
                wp_send_json_error([
                    'message' => 'Aucune clé de test générée'
                ]);
            }
            
            // Vérifier que la clé correspond
            if ($key_to_validate !== $saved_test_key) {
                wp_send_json_error([
                    'message' => 'Clé invalide'
                ]);
            }
            
            // La clé est valide !
            wp_send_json_success([
                'message' => 'Clé de test valide ! ✅',
                'key' => $saved_test_key,
                'is_test_mode' => true
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handler AJAX pour basculer le mode test
     */
    public function handle_toggle_test_mode() {
        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_toggle_test_mode')) {
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        
        try {
            // Récupérer l'état actuel
            $current_state = $this->is_test_mode_enabled();
            
            // Basculer l'état
            $new_state = !$current_state;
            
            // Sauvegarder le nouvel état
            $this->set_test_mode_enabled($new_state);
            
            // Retourner le nouvel état
            wp_send_json_success([
                'enabled' => $new_state,
                'message' => $new_state ? '✅ Mode test ACTIVÉ' : '❌ Mode test DÉSACTIVÉ'
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handler AJAX pour supprimer la clé de test
     */
    public function handle_delete_test_key() {
        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_delete_test_license_key')) {
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        
        try {
            // Supprimer la clé de test
            delete_option('pdf_builder_license_test_key');
            
            // Retourner la confirmation
            wp_send_json_success([
                'message' => '✅ Clé de test supprimée'
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Nettoie complètement la licence (supprime tous les paramètres)
     * AJAX handler
     */
    public function handle_cleanup_license() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }
        
        // Vérifier le nonce
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_builder_cleanup_license')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }
        
        try {
            // Options à supprimer
            $options = [
                'pdf_builder_license_status',
                'pdf_builder_license_key',
                'pdf_builder_license_expires',
                'pdf_builder_license_activated_at',
                'pdf_builder_license_test_mode_enabled',
                'pdf_builder_license_test_key'
            ];
            
            foreach ($options as $option) {
                delete_option($option);
            }
            
            // Définir l'état clean
            update_option('pdf_builder_license_status', 'free');
            
            wp_send_json_success([
                'message' => '✨ Licence complètement nettoyée et réinitialisée'
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur lors du nettoyage: ' . $e->getMessage()
            ]);
        }
    }
}

// Initialiser le handler
$handler = License_Test_Handler::get_instance();
$handler->init();
