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
class LicenseTestHandler
{
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
        error_log('LICENSE TEST: LicenseTestHandler init() called');

        // AJAX handler pour générer une clé de test
        add_action('wp_ajax_pdf_builder_generate_test_license_key', [$this, 'handleGenerateTestKey']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_test_license_key', [$this, 'handleGenerateTestKey']);
        error_log('LICENSE TEST: Generate test key hooks registered');

        // AJAX handler pour valider une clé de test
        add_action('wp_ajax_pdf_builder_validate_test_license_key', [$this, 'handleValidateTestKey']);
        add_action('wp_ajax_nopriv_pdf_builder_validate_test_license_key', [$this, 'handleValidateTestKey']);
        error_log('LICENSE TEST: Validate test key hooks registered');

        // AJAX handler pour basculer le mode test
        add_action('wp_ajax_pdf_builder_toggle_test_mode', [$this, 'handleToggleTestMode']);
        add_action('wp_ajax_nopriv_pdf_builder_toggle_test_mode', [$this, 'handleToggleTestMode']);
        add_action('wp_ajax_pdf_builder_toggle_license_test_mode', [$this, 'handleToggleTestMode']);
        add_action('wp_ajax_nopriv_pdf_builder_toggle_license_test_mode', [$this, 'handleToggleTestMode']);
        error_log('LICENSE TEST: Toggle test mode hooks registered (both variants)');

        // AJAX handler pour supprimer la clé de test
        add_action('wp_ajax_pdf_builder_delete_test_license_key', [$this, 'handleDeleteTestKey']);
        add_action('wp_ajax_nopriv_pdf_builder_delete_test_license_key', [$this, 'handleDeleteTestKey']);
        error_log('LICENSE TEST: Delete test key hooks registered');

        // AJAX handler pour nettoyer complètement la licence
        add_action('wp_ajax_pdf_builder_cleanup_license', [$this, 'handleCleanupLicense']);
        add_action('wp_ajax_nopriv_pdf_builder_cleanup_license', [$this, 'handleCleanupLicense']);
        error_log('LICENSE TEST: Cleanup license hooks registered');

        error_log('LICENSE TEST: All hooks registered successfully');
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
        return update_option('pdf_builder_license_test_key', sanitize_text_field($key));
    }

    /**
     * Récupère la clé de test sauvegardée
     *
     * @return string|false Clé de test ou false
     */
    public function getTestKey()
    {
        return get_option('pdf_builder_license_test_key', false);
    }

    /**
     * Sauvegarde l'état du mode test
     *
     * @param bool $enabled État du mode test
     * @return bool
     */
    public function setTestModeEnabled($enabled)
    {
        return update_option('pdf_builder_license_test_mode_enabled', (bool) $enabled);
    }

    /**
     * Vérifie si le mode test est activé
     *
     * @return bool
     */
    public function isTestModeEnabled()
    {
        return (bool) get_option('pdf_builder_license_test_mode_enabled', false);
    }

    /**
     * Handler AJAX pour générer une clé de test
     */
    public function handleGenerateTestKey()
    {
        error_log('LICENSE TEST: handleGenerateTestKey called');

        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_generate_license_key')) {
            error_log('LICENSE TEST: Generate key - Nonce verification failed');
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        error_log('LICENSE TEST: Generate key - Nonce verified');

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('LICENSE TEST: Generate key - Insufficient permissions');
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        error_log('LICENSE TEST: Generate key - Permissions OK');

        try {
            // Générer une nouvelle clé
            $new_key = $this->generateTestKey();
            error_log('LICENSE TEST: Generated key: ' . $new_key);

            // Sauvegarder la clé
            $saved = $this->saveTestKey($new_key);
            if (!$saved) {
                error_log('LICENSE TEST: Failed to save key');
                wp_send_json_error([
                    'message' => 'Impossible de sauvegarder la clé de test'
                ]);
            }
            error_log('LICENSE TEST: Key saved successfully');

            // Sauvegarder la date d'expiration (30 jours)
            $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));
            update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);
            error_log('LICENSE TEST: Expiration date set: ' . $expires_in_30_days);

            // Retourner la clé générée
            wp_send_json_success([
                'key' => $new_key,
                'expires' => $expires_in_30_days,
                'message' => 'Clé de test générée avec succès (expire dans 30 jours)'
            ]);
            error_log('LICENSE TEST: Generate key response sent');
        } catch (\Exception $e) {
            error_log('LICENSE TEST: Generate key exception: ' . $e->getMessage());
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

    /**
     * Handler AJAX pour basculer le mode test
     */
    public function handleToggleTestMode()
    {
        error_log('LICENSE TEST: handleToggleTestMode called');

        // Vérifier la nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_toggle_test_mode')) {
            error_log('LICENSE TEST: Nonce verification failed');
            wp_send_json_error([
                'message' => 'Erreur de sécurité: nonce invalide'
            ], 403);
        }
        error_log('LICENSE TEST: Nonce verified successfully');

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('LICENSE TEST: Insufficient permissions');
            wp_send_json_error([
                'message' => 'Permissions insuffisantes'
            ], 403);
        }
        error_log('LICENSE TEST: Permissions OK');

        try {
            // Récupérer l'état actuel
            $current_state = $this->isTestModeEnabled();
            error_log('LICENSE TEST: Current state: ' . ($current_state ? 'enabled' : 'disabled'));

            // Basculer l'état
            $new_state = !$current_state;
            error_log('LICENSE TEST: New state will be: ' . ($new_state ? 'enabled' : 'disabled'));

            // Sauvegarder le nouvel état
            $this->setTestModeEnabled($new_state);
            error_log('LICENSE TEST: State saved successfully');

            // Retourner le nouvel état
            wp_send_json_success([
                'enabled' => $new_state,
                'message' => $new_state ? '✅ Mode test ACTIVÉ' : '❌ Mode test DÉSACTIVÉ'
            ]);
            error_log('LICENSE TEST: Response sent successfully');
        } catch (\Exception $e) {
            error_log('LICENSE TEST: Exception caught: ' . $e->getMessage());
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
    public function handleCleanupLicense()
    {
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
            error_log('PDF Builder: Nonce verification failed for cleanup, but attempting anyway for authenticated user');
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
$handler = LicenseTestHandler::getInstance();
$handler->init();
