<?php

/**
 * Gestionnaire des limites de sécurité
 * Applique les paramètres max_execution_time, memory_limit et max_template_size
 */

namespace PDF_Builder\Security;

class Security_Limits_Handler
{
    /**
     * Initialise le gestionnaire des limites
     */
    public static function init()
    {
        // Appliquer les limites au chargement du plugin
        \add_action('plugins_loaded', [__CLASS__, 'applySecurityLimits'], 5);
// Valider la taille des fichiers uploadés
        \add_filter('upload_size_limit', [__CLASS__, 'validateUploadSize']);
// PDF generation system removed - validateTemplateSize no longer needed
    }

    /**
     * Applique les limites de sécurité depuis les settings
     */
    public static function applySecurityLimits()
    {
        // Appliquer le timeout maximum
        $max_execution_time = isset($settings['max_execution_time'])
            ? \intval($settings['max_execution_time'])
            : 300;
// 5 minutes par défaut

        if ($max_execution_time > 0 && $max_execution_time <= 3600) {
            set_time_limit($max_execution_time);
            
        }

        // Appliquer la limite mémoire depuis les paramètres canvas
        $memory_limit_mb = \intval(pdf_builder_get_option('pdf_builder_canvas_memory_limit_php', 256));
        $memory_limit = $memory_limit_mb . 'M';

        if (!empty($memory_limit)) {
            ini_set('memory_limit', $memory_limit);
            
        }
    }

    /**
     * Valide la taille du fichier uploadé
     */
    public static function validateUploadSize($size)
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $max_template_size = isset($settings['max_template_size'])
            ? \intval($settings['max_template_size'])
            : 52428800;
// 50MB par défaut

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
    public static function validateTemplateSize($template_data)
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $max_template_size = isset($settings['max_template_size'])
            ? \intval($settings['max_template_size'])
            : 52428800;
// 50MB par défaut

        // Sérialiser le template pour estimer sa taille
        $serialized = serialize($template_data);
        $size = strlen($serialized);
        if ($size > $max_template_size) {
            $size_mb = round($size / 1048576, 2);
            $max_mb = round($max_template_size / 1048576, 2);
            throw new \Exception( // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                sprintf(
                    'La taille du template (%s MB) dépasse la limite configurée (%s MB)',
                    $size_mb,
                    $max_mb
                )
            );
        }
    }

    /**
     * Retourne les informations des limites actuelles
     */
    public static function getLimitsInfo()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        return [
            'max_execution_time' => isset($settings['max_execution_time'])
                ? \intval($settings['max_execution_time'])
                : 300,
            'memory_limit' => \intval(pdf_builder_get_option('pdf_builder_canvas_memory_limit_php', 256)) . 'M',
            'max_template_size' => isset($settings['max_template_size'])
                ? \intval($settings['max_template_size'])
                : 52428800,
            'current_memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'current_execution_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2)
        ];
    }
}

// Initialiser au chargement
Security_Limits_Handler::init();




