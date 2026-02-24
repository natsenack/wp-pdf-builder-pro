<?php
/**
 * PDF Builder Pro - Validateur de Nonces Unifié
 * Remplacement unique pour tous les wp_verify_nonce directs
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Validateur de nonce unifié et centralisé
 */
class PDF_Builder_Nonce_Validator {
    
    /**
     * Vérifier un nonce de manière sécurisée et unifiée
     * 
     * @param string|null $nonce Le nonce à vérifier (null = depuis $_POST/$_GET)
     * @param string $action L'action attendue (peut être alias, sera résolue)
     * @param string $source 'post', 'get' ou 'any' (défaut: 'post')
     * @return int|false 1 si valide, 2 si valide mais ancien, 0 si invalide
     */
    public static function verify(?string $nonce = null, string $action = 'pdf_builder_ajax', string $source = 'post'): int|false {
        // Résoudre les alias
        $canonical_action = PDF_Builder_Nonce_Registry::resolve_action($action);
        
        // Récupérer le nonce si non fourni
        if ($nonce === null) {
            $nonce = self::get_nonce_from_request($source);
        }
        
        // Vérification
        if (empty($nonce)) {
            PDF_Builder_Nonce_Registry::log_nonce_event('MISSING_NONCE', $canonical_action);
            return 0;
        }
        
        // Vérifier avec WordPress
        $result = wp_verify_nonce($nonce, $canonical_action);
        
        // Logger l'événement
        if ($result) {
            PDF_Builder_Nonce_Registry::log_nonce_event('NONCE_VERIFIED', $canonical_action, [
                'result' => $result,
                'source' => $source,
            ]);
        } else {
            PDF_Builder_Nonce_Registry::log_nonce_event('NONCE_INVALID', $canonical_action, [
                'source' => $source,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Vérifier un nonce ou mourir (comportement classique WordPress)
     */
    public static function verify_or_die(?string $nonce = null, string $action = 'pdf_builder_ajax', string $source = 'post'): bool {
        if (!self::verify($nonce, $action, $source)) {
            wp_die(
                esc_html__('Vérification de sécurité échouée', 'pdf-builder-pro'),
                esc_html__('Erreur de sécurité', 'pdf-builder-pro'),
                ['response' => 403]
            );
        }
        return true;
    }
    
    /**
     * Vérifier un nonce et envoyer une erreur JSON si invalide
     */
    public static function verify_or_json_error(?string $nonce = null, string $action = 'pdf_builder_ajax', string $source = 'post'): bool {
        if (!self::verify($nonce, $action, $source)) {
            wp_send_json_error([
                'message' => __('Sécurité: Nonce invalide', 'pdf-builder-pro'),
                'code' => 'nonce_invalid',
            ], 403);
            return false;
        }
        return true;
    }
    
    /**
     * Vérifier les permissions et nonce ensemble
     */
    public static function verify_request(?string $nonce = null, string $action = 'pdf_builder_ajax', string $capability = ''): bool {
        // Vérifier les permissions
        $required_capability = $capability ?: PDF_Builder_Nonce_Registry::get_capability($action);
        if (!current_user_can($required_capability)) {
            PDF_Builder_Nonce_Registry::log_nonce_event('INSUFFICIENT_PERMISSIONS', $action, [
                'required' => $required_capability,
                'user_id' => get_current_user_id(),
            ]);
            return false;
        }
        
        // Vérifier le nonce
        return (bool) self::verify($nonce, $action);
    }
    
    /**
     * Vérifier la requête et envoyer erreur JSON si invalide
     */
    public static function verify_request_or_json_error(?string $nonce = null, string $action = 'pdf_builder_ajax', string $capability = ''): bool {
        if (!self::verify_request($nonce, $action, $capability)) {
            wp_send_json_error([
                'message' => __('Accès refusé ou sécurité échouée', 'pdf-builder-pro'),
                'code' => 'access_denied',
            ], 403);
            return false;
        }
        return true;
    }
    
    /**
     * Récupérer le nonce depuis la requête
     */
    private static function get_nonce_from_request(string $source = 'post'): ?string {
        $sources = [];
        
        if ($source === 'post' || $source === 'any') {
            $sources[] = $_POST['nonce'] ?? $_POST['_wpnonce'] ?? null;
        }
        
        if ($source === 'get' || $source === 'any') {
            $sources[] = $_GET['nonce'] ?? $_GET['_wpnonce'] ?? null;
        }
        
        foreach ($sources as $nonce) {
            if (!empty($nonce)) {
                return sanitize_text_field($nonce);
            }
        }
        
        return null;
    }
    
    /**
     * Obtenir toutes les clés possibles du nonce dans la requête
     */
    public static function get_all_nonce_values(): array {
        $values = [];
        
        if (isset($_POST['nonce'])) {
            $values['post_nonce'] = sanitize_text_field($_POST['nonce']);
        }
        if (isset($_POST['_wpnonce'])) {
            $values['post_wpnonce'] = sanitize_text_field($_POST['_wpnonce']);
        }
        if (isset($_GET['nonce'])) {
            $values['get_nonce'] = sanitize_text_field($_GET['nonce']);
        }
        if (isset($_GET['_wpnonce'])) {
            $values['get_wpnonce'] = sanitize_text_field($_GET['_wpnonce']);
        }
        
        return $values;
    }
}

/**
 * Alias court pour les utilisateurs convenances
 */
if (!function_exists('pdf_builder_verify_nonce')) {
    function pdf_builder_verify_nonce(?string $nonce = null, string $action = 'pdf_builder_ajax', string $source = 'post'): int|false {
        return PDF_Builder_Nonce_Validator::verify($nonce, $action, $source);
    }
}

if (!function_exists('pdf_builder_verify_request')) {
    function pdf_builder_verify_request(?string $nonce = null, string $action = 'pdf_builder_ajax', string $capability = ''): bool {
        return PDF_Builder_Nonce_Validator::verify_request($nonce, $action, $capability);
    }
}

if (!function_exists('pdf_builder_verify_request_or_json_error')) {
    function pdf_builder_verify_request_or_json_error(?string $nonce = null, string $action = 'pdf_builder_ajax', string $capability = ''): bool {
        return PDF_Builder_Nonce_Validator::verify_request_or_json_error($nonce, $action, $capability);
    }
}
