<?php
/**
 * Gestionnaire des limites de sécurité
 * Applique les paramètres max_execution_time, memory_limit et max_template_size
 */

namespace WP_PDF_Builder_Pro\Security;

class Security_Limits_Handler {
    
    /**
     * Initialise le gestionnaire des limites
     */
    public static function init() {
        // Appliquer les limites au chargement du plugin
        add_action('plugins_loaded', [__CLASS__, 'apply_security_limits'], 5);
        
        // Valider la taille des fichiers uploadés
        add_filter('upload_size_limit', [__CLASS__, 'validate_upload_size']);
        
        // Ajouter un hook pour vérifier la taille des templates avant génération
        add_action('pdf_builder_before_generate', [__CLASS__, 'validate_template_size']);
    }
    
    /**
     * Applique les limites de sécurité depuis les settings
     */
    public static function apply_security_limits() {
        $settings = get_option('pdf_builder_settings', []);
        
        // Appliquer le timeout maximum
        $max_execution_time = isset($settings['max_execution_time']) 
            ? intval($settings['max_execution_time']) 
            : 300; // 5 minutes par défaut
        
        if ($max_execution_time > 0 && $max_execution_time <= 3600) {
            set_time_limit($max_execution_time);
            error_log("[PDF Builder] Security: set_time_limit({$max_execution_time})");
        }
        
        // Appliquer la limite mémoire
        $memory_limit = isset($settings['memory_limit']) 
            ? sanitize_text_field($settings['memory_limit']) 
            : '256M'; // 256MB par défaut
        
        if (!empty($memory_limit)) {
            // Valider le format (e.g. 256M, 512M, 1G)
            if (preg_match('/^(\d+)([MG])$/', strtoupper($memory_limit), $matches)) {
                ini_set('memory_limit', $memory_limit);
                error_log("[PDF Builder] Security: memory_limit set to {$memory_limit}");
            }
        }
    }
    
    /**
     * Valide la taille du fichier uploadé
     */
    public static function validate_upload_size($size) {
        $settings = get_option('pdf_builder_settings', []);
        $max_template_size = isset($settings['max_template_size']) 
            ? intval($settings['max_template_size']) 
            : 52428800; // 50MB par défaut
        
        // Retourner la limite plus petite
        return min($size, $max_template_size);
    }
    
    /**
     * Valide la taille du template avant génération
     * 
     * @hook pdf_builder_before_generate
     * @param array $template_data Données du template
     * @throws \Exception Si le template est trop gros
     */
    public static function validate_template_size($template_data) {
        $settings = get_option('pdf_builder_settings', []);
        $max_template_size = isset($settings['max_template_size']) 
            ? intval($settings['max_template_size']) 
            : 52428800; // 50MB par défaut
        
        // Sérialiser le template pour estimer sa taille
        $serialized = serialize($template_data);
        $size = strlen($serialized);
        
        if ($size > $max_template_size) {
            $size_mb = round($size / 1048576, 2);
            $max_mb = round($max_template_size / 1048576, 2);
            error_log("[PDF Builder] Security: Template size exceeds limit ({$size_mb}MB > {$max_mb}MB)");
            throw new \Exception(sprintf(
                'La taille du template (%s MB) dépasse la limite configurée (%s MB)',
                $size_mb,
                $max_mb
            ));
        }
    }
    
    /**
     * Retourne les informations des limites actuelles
     */
    public static function get_limits_info() {
        $settings = get_option('pdf_builder_settings', []);
        
        return [
            'max_execution_time' => isset($settings['max_execution_time']) 
                ? intval($settings['max_execution_time']) 
                : 300,
            'memory_limit' => isset($settings['memory_limit']) 
                ? sanitize_text_field($settings['memory_limit']) 
                : '256M',
            'max_template_size' => isset($settings['max_template_size']) 
                ? intval($settings['max_template_size']) 
                : 52428800,
            'current_memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'current_execution_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2)
        ];
    }
}

// Initialiser au chargement
Security_Limits_Handler::init();
