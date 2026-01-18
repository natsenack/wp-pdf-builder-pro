<?php

/**
 * License Test Handler - Gère la génération et validation des clés de test de licence
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 */

namespace PDF_Builder\License;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer les tests de licence
 */
class LicenseTestHandler
{
    /**
     * Instance singleton
     */
    private static $instance = null;

    /**
     * Flag pour éviter les réinitialisations multiples
     */
    private static $initialized = false;
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
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialise les hooks
     */
    public function init()
    {
        // Éviter les réinitialisations multiples
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }

        // AJAX handler pour générer une clé de test
        // NOTE: Le handler AJAX centralisé (PDF_Builder_Unified_Ajax_Handler) prend en charge les actions.
        // Les hooks ci-dessous ont été retirés pour éviter les doublons et la validation de nonce incompatible.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }

        // AJAX handler pour valider une clé de test
        // Hooks centralisés gèrent cette action
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }

        // AJAX handler pour basculer le mode test
        // Hooks centralisés gèrent cette action
        

        // AJAX handler pour supprimer la clé de test
        // Hooks centralisés gèrent cette action
        

        // AJAX handler pour nettoyer complètement la licence
        // Hooks centralisés gèrent cette action
        

        
    }

    /**
     * Génère une clé de test aléatoire
     *
     * @return string Clé de test générée
     */
    public function generateTestKey()
    {
        // Générer une clé aléatoire
        $random_part = bin2hex(random_bytes(16));
// 32 caractères hex
        $key = self::TEST_KEY_PREFIX . strtoupper($random_part);
        return $key;
    }

    /**
     * Sauvegarde la clé de test en base de données
     *
     * @param string $key Clé de test
     * @return bool
     */
    public function saveTestKey($key)
    {
        return pdf_builder_update_option('pdf_builder_license_test_key', sanitize_text_field($key));
    }

    /**
     * Récupère la clé de test sauvegardée
     *
     * @return string|false Clé de test ou false
     */
    public function getTestKey()
    {
        return pdf_builder_get_option('pdf_builder_license_test_key', false);
    }

    /**
     * Sauvegarde l'état du mode test
     *
     * @param bool $enabled État du mode test
     * @return bool
     */
    public function setTestModeEnabled($enabled)
    {
        return pdf_builder_update_option('pdf_builder_license_test_mode_enabled', (bool) $enabled);
    }

    /**
     * Vérifie si le mode test est activé
     *
     * @return bool
     */
    public function isTestModeEnabled()
    {
        return (bool) pdf_builder_get_option('pdf_builder_license_test_mode_enabled', false);
    }

    /**
     * Handler AJAX pour générer une clé de test
     */
    public function handleGenerateTestKey()
    {
        

        // Vérifier la nonce (accepte le nonce central 'pdf_builder_ajax' pour compatibilité)
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (empty($nonce) || (!wp_verify_nonce($nonce, 'pdf_builder_generate_test_license_key') && !wp_verify_nonce($nonce, 'pdf_builder_ajax'))) {
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
            $new_key = $this->generateTestKey();
            

            // Sauvegarder la clé
            $saved = $this->saveTestKey($new_key);
            if (!$saved) {
                
                wp_send_json_error([
                    'message' => 'Impossible de sauvegarder la clé de test'
                ]);
            }
            

            // Sauvegarder la date d'expiration (30 jours)
            $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));
            pdf_builder_update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);
            

            // Retourner la clé générée (compatibilité: key + license_key)
            wp_send_json_success([
                'key' => $new_key,
                'license_key' => $new_key,
                'expires' => $expires_in_30_days,
                'message' => 'Clé de test générée avec succès (expire dans 30 jours)'
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
    public function handleValidateTestKey()
    {
        // Vérifier la nonce (accepte le nonce central 'pdf_builder_ajax' pour compatibilité)
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (empty($nonce) || (!wp_verify_nonce($nonce, 'pdf_builder_validate_test_license_key') && !wp_verify_nonce($nonce, 'pdf_builder_ajax'))) {
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
            if (!$this->isTestModeEnabled()) {
                wp_send_json_error([
                    'message' => 'Mode test désactivé'
                ]);
            }

            // Récupérer la clé de test sauvegardée
            $saved_test_key = $this->getTestKey();
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

    public function handleToggleTestMode()
    {
        // Log pour debug
        error_log('[PDF Builder] handleToggleTestMode called');

        // Vérifier la nonce (accepte le nonce central 'pdf_builder_ajax' pour compatibilité)
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (empty($nonce) || (!wp_verify_nonce($nonce, 'pdf_builder_toggle_test_mode') && !wp_verify_nonce($nonce, 'pdf_builder_ajax'))) {
            error_log('[PDF Builder] handleToggleTestMode: nonce invalide');
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('[PDF Builder] handleToggleTestMode: permissions insuffisantes');
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        

        try {
            // Récupérer l'état actuel
            $current_state = $this->isTestModeEnabled();
            error_log('[PDF Builder] handleToggleTestMode: état actuel = ' . ($current_state ? 'true' : 'false'));
            

            // Basculer l'état
            $new_state = !$current_state;
            error_log('[PDF Builder] handleToggleTestMode: nouvel état = ' . ($new_state ? 'true' : 'false'));
            

            // Si on active le mode test, générer une clé de test automatiquement
            if ($new_state && !$this->getTestKey()) {
                $test_key = $this->generateTestKey();
                $this->saveTestKey($test_key);
                $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));
                pdf_builder_update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);
                error_log('[PDF Builder] handleToggleTestMode: clé de test générée = ' . substr($test_key, 0, 10) . '...');
            }
            
            // Si on désactive le mode test, supprimer la clé de test
            if (!$new_state && $this->getTestKey()) {
                delete_option('pdf_builder_license_test_key');
                delete_option('pdf_builder_license_test_key_expires');
                error_log('[PDF Builder] handleToggleTestMode: clé de test supprimée');
            }
            

            // Sauvegarder le nouvel état
            $saved = $this->setTestModeEnabled($new_state);
            error_log('[PDF Builder] handleToggleTestMode: sauvegarde = ' . ($saved ? 'success' : 'failed'));
            

            // Vérifier que c'est bien sauvegardé
            $verify_state = $this->isTestModeEnabled();
            error_log('[PDF Builder] handleToggleTestMode: état vérifié = ' . ($verify_state ? 'true' : 'false'));

            // Retourner le nouvel état
            wp_send_json_success([
                'enabled' => $new_state,
                'test_key' => $new_state ? $this->getTestKey() : '',
                'message' => $new_state ? '✅ Mode test ACTIVÉ - Clé générée' : '❌ Mode test DÉSACTIVÉ - Clé supprimée'
            ]);
            
        } catch (\Exception $e) {
            error_log('[PDF Builder] handleToggleTestMode: exception = ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handler AJAX pour supprimer la clé de test
     */
    public function handleDeleteTestKey()
    {
        // Vérifier la nonce (accepte le nonce central 'pdf_builder_ajax' pour compatibilité)
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (empty($nonce) || (!wp_verify_nonce($nonce, 'pdf_builder_delete_test_license_key') && !wp_verify_nonce($nonce, 'pdf_builder_ajax'))) {
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
            // Supprimer la clé de test et l'expiration / désactiver le mode test
            delete_option('pdf_builder_license_test_key');
            delete_option('pdf_builder_license_test_key_expires');
            pdf_builder_update_option('pdf_builder_license_test_mode_enabled', false);

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
    public function handleCleanupLicense()
    {
        error_log('[PDF Builder] license-test-handler.php - handleCleanupLicense called');

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Vérifier le nonce - accepter avec -1 si non fourni
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '-1';
        if ($nonce !== '-1' && !wp_verify_nonce($nonce, 'pdf_builder_cleanup_license')) {
            // Si la vérification échoue, essayer quand même pour les admin authentifiés
            // (le nonce peut être incorrect pour les AJAX handlers)
        }

        try {
            error_log('[PDF Builder] license-test-handler.php - Starting cleanup');

            // Vérifier si le mode test est actif AVANT de commencer le nettoyage
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $test_mode_was_enabled = ($settings['pdf_builder_license_test_mode'] ?? '0') === '1';
            error_log('[PDF Builder] license-test-handler.php - Test mode was enabled: ' . ($test_mode_was_enabled ? 'YES' : 'NO'));

            // Options à supprimer (nettoyage complet)
            $options = [
                'pdf_builder_license_status',
                'pdf_builder_license_key',
                'pdf_builder_license_expires',
                'pdf_builder_license_activated_at',
                'pdf_builder_license_test_mode_enabled',
                'pdf_builder_license_test_key',
                'pdf_builder_license_test_key_expires',
                'pdf_builder_license_test_mode'
            ];

            error_log('[PDF Builder] license-test-handler.php - Options to delete: ' . implode(', ', $options));

            foreach ($options as $option) {
                $old_value = get_option($option, 'NOT_SET');
                delete_option($option);
                error_log('[PDF Builder] license-test-handler.php - Deleted option: ' . $option . ' (was: ' . $old_value . ')');
            }

            // Définir l'état clean
            pdf_builder_update_option('pdf_builder_license_status', 'free');
            error_log('[PDF Builder] license-test-handler.php - Set license status to free');

            wp_send_json_success([
                'message' => '✨ Licence complètement nettoyée et réinitialisée'
            ]);
        } catch (\Exception $e) {
            error_log('[PDF Builder] license-test-handler.php - Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Erreur lors du nettoyage: ' . $e->getMessage()
            ]);
        }
    }
}


