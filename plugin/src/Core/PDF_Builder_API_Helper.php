<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Pro - Helper pour API REST
 * Fournit des méthodes utilitaires pour les vérifications de licence et les réponses
 */

namespace PDF_Builder\Core;

class PDF_Builder_API_Helper {

    /**
     * Vérifier si la licence premium est active
     * @return bool
     */
    public static function is_premium() {
        return pdf_builder_is_premium();
    }

    /**
     * Vérifier une permission de licence et retourner une WP_Error si non autorisée
     * @param string $feature Nom de la fonctionnalité (pour le message d'erreur)
     * @return \WP_Error|null Retourne WP_Error si non autorisée, null si OK
     */
    public static function check_premium_license($feature = 'Fonctionnalité premium') {
        if (!self::is_premium()) {
            return new \WP_Error(
                'premium_required',
                sprintf(
                    // translators: 1: feature name, 2: upgrade URL
                    __('%1$s nécessite une licence PDF Builder Pro Premium. Veuillez <a href="%2$s" target="_blank">passer en Premium</a>.', 'pdf-builder-pro'),
                    $feature,
                    'https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/'
                ),
                array('status' => 403)
            );
        }
        return null;
    }

    /**
     * Retourner une réponse JSON pour une vérification de licence échouée
     * @param string $feature Nom de la fonctionnalité
     * @return void (sort avec wp_send_json_error)
     */
    public static function send_premium_required_error($feature = 'Fonctionnalité premium') {
        wp_send_json_error(
            [
                'message' => sprintf(
                    // translators: 1: feature name, 2: upgrade URL
                    __('%1$s nécessite une licence PDF Builder Pro Premium. Veuillez <a href="%2$s" target="_blank">passer en Premium</a>.', 'pdf-builder-pro'),
                    $feature,
                    'https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/'
                ),
                'code' => 'premium_required',
                'status' => 403
            ],
            403
        );
    }

    /**
     * Vérifier le nonce et retourner une erreur si invalide
     * @param string $nonce_field Le champ du nonce
     * @param string $security Le nonce à vérifier
     * @param string $action L'action nonce
     * @return bool|void Retourne true si OK, sinon sort avec wp_send_json_error
     */
    public static function verify_nonce($nonce_field, $security, $action = 'pdf_builder_nonce') {
        if (empty($security) || !wp_verify_nonce($security, $action)) {
            wp_send_json_error(
                array(
                    'message' => __('Nonce invalide ou expiré. Veuillez rafraîchir la page.', 'pdf-builder-pro'),
                    'code' => 'invalid_nonce'
                ),
                403
            );
            return false;
        }
        return true;
    }

    /**
     * Vérifier les permissions utilisateur (admin ou capacité spécifique)
     * @param string $capability Capacité WordPress (défaut: 'manage_options')
     * @return bool|void Retourne true si OK, sinon sort avec wp_send_json_error
     */
    public static function verify_capability($capability = 'manage_options') {
        if (!current_user_can($capability)) {
            wp_send_json_error(
                array(
                    'message' => __('Vous n\'avez pas la permission d\'accéder à cette ressource.', 'pdf-builder-pro'),
                    'code' => 'access_denied'
                ),
                403
            );
            return false;
        }
        return true;
    }

    /**
     * Journaliser une tentative d'accès à une fonctionnalité premium
     * @param string $feature Nom de la fonctionnalité
     * @param int $user_id ID utilisateur
     */
    public static function log_premium_attempt($feature, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $message = sprintf(
            '[PDF Builder API] Premium feature attempted: %s | User: %d | Premium: %s',
            $feature,
            $user_id,
            self::is_premium() ? 'YES' : 'NO'
        );

        error_log($message);
    }

    /**
     * Obtenir les informations de licence pour la réponse API
     * @return array
     */
    public static function get_license_info() {
        $license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        $license_key = pdf_builder_get_option('pdf_builder_license_key', '');
        
        return array(
            'is_premium' => self::is_premium(),
            'status' => $license_status,
            'has_key' => !empty($license_key),
            'upgrade_url' => 'https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/',
        );
    }
}
