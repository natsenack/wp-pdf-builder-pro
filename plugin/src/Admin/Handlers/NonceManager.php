<?php

/**
 * PDF Builder Pro - Gestionnaire centralisé des Nonces
 * Système unifié pour la génération, vérification et validation des nonces
 */

namespace PDF_Builder\Admin\Handlers;

/**
 * Classe de gestion centralisée des nonces
 * Assure une cohérence dans tout le système de sécurité
 */
class NonceManager
{
    /**
     * Action nonce unifié pour tous les appels AJAX
     */
    const NONCE_ACTION = 'pdf_builder_ajax';

    /**
     * Clé POST/GET pour le nonce
     */
    const NONCE_KEY = 'nonce';

    /**
     * Capacité minimale requise pour les opérations AJAX
     */
    const MIN_CAPABILITY = 'edit_posts';

    /**
     * Capacité requise pour les opérations d'administration
     */
    const ADMIN_CAPABILITY = 'manage_options';

    /**
     * TTL du nonce en secondes (12 heures par défaut)
     */
    const NONCE_TTL = 43200;

    /**
     * Créer un nonce valide
     *
     * @return string Le nonce généré
     */
    public static function createNonce(): string
    {
        return wp_create_nonce(self::NONCE_ACTION);
    }

    /**
     * Vérifier un nonce valide
     *
     * @param string|null $nonce Le nonce à vérifier
     * @return bool|int 1 si valide, 2 si valide mais ancien, false si invalide
     */
    public static function verifyNonce(?string $nonce = null)
    {
        if ($nonce === null) {
            $nonce = self::getNonceFromRequest();
        }

        if (empty($nonce)) {
            return false;
        }

        $result = wp_verify_nonce($nonce, self::NONCE_ACTION);
        return $result;
    }

    /**
     * Récupérer le nonce depuis la requête (GET ou POST)
     *
     * @return string|null Le nonce ou null
     */
    public static function getNonceFromRequest(): ?string
    {
        // Priorité: POST, puis GET
        if (!empty($_POST[self::NONCE_KEY])) {
            return sanitize_text_field($_POST[self::NONCE_KEY]);
        }

        if (!empty($_GET[self::NONCE_KEY])) {
            return sanitize_text_field($_GET[self::NONCE_KEY]);
        }

        return null;
    }

    /**
     * Vérifier les permissions AJAX de base
     *
     * @param string $capability La capacité requise (par défaut edit_posts)
     * @return bool True si l'utilisateur a les permissions
     */
    public static function checkPermissions(string $capability = self::MIN_CAPABILITY): bool
    {
        if (!\is_user_logged_in()) {
            self::logError('Utilisateur non connecté');
            return false;
        }

        if (!\current_user_can($capability)) {
            self::logError("Permissions insuffisantes pour la capacité: {$capability}");
            return false;
        }

        return true;
    }

    /**
     * Vérifier les permissions et le nonce en une seule opération
     *
     * @param string $capability La capacité requise
     * @return array|false Array success => true ou false avec message d'erreur
     */
    public static function validateRequest(string $capability = self::MIN_CAPABILITY)
    {
        // Vérifier les permissions
        if (!self::checkPermissions($capability)) {
            return [
                'success' => false,
                'message' => 'Permissions insuffisantes',
                'code' => 'permission_denied'
            ];
        }

        // Vérifier le nonce
        $nonce_result = self::verifyNonce();
        if (!$nonce_result) {
            return [
                'success' => false,
                'message' => 'Nonce invalide',
                'code' => 'nonce_invalid'
            ];
        }

        return [
            'success' => true,
            'message' => 'Validations réussies'
        ];
    }

    /**
     * Envoyer une réponse JSON d'erreur avec nonce expiré
     *
     * @return void
     */
    public static function sendNonceErrorResponse(): void
    {
        \wp_send_json_error([
            'message' => 'Nonce invalide',
            'code' => 'nonce_invalid',
            'nonce' => self::createNonce() // Fournir un nonce frais pour correction
        ]);
    }

    /**
     * Envoyer une réponse JSON d'erreur de permissions
     *
     * @return void
     */
    public static function sendPermissionErrorResponse(): void
    {
        \wp_send_json_error([
            'message' => 'Permissions insuffisantes',
            'code' => 'permission_denied'
        ]);
    }

    /**
     * Logger une erreur de sécurité
     *
     * @param string $message Le message à logger
     * @return void
     */
    private static function logError(string $message): void
    {
        $log_message = '[PDF Builder] [NonceManager] [ERROR] ' . $message;
        error_log($log_message);
    }

    /**
     * Logger une information
     *
     * @param string $message Le message à logger
     * @return void
     */
    public static function logInfo(string $message): void
    {
        $log_message = '[PDF Builder] [NonceManager] [INFO] ' . $message;
        error_log($log_message);
    }

    /**
     * Obtenir les données de nonce à envoyer au frontend
     *
     * @return array Données localisées pour le frontend
     */
    public static function getLocalizedData(): array
    {
        return [
            'nonce' => self::createNonce(),
            'action' => self::NONCE_ACTION,
            'capability_required' => self::MIN_CAPABILITY,
            'timestamp' => current_time('timestamp')
        ];
    }
}



