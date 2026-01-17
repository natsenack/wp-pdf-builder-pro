<?php

/**
 * PDF Builder Pro - Utilitaires
 * Fonctions utilitaires pour permissions, validation, etc.
 */

namespace PDF_Builder\Admin\Utils;

/**
 * Classe utilitaire pour les permissions
 */
class Permissions
{
    /**
     * Vérifier si l'utilisateur peut gérer les options
     */
    public static function canManageOptions()
    {
        return is_user_logged_in() && current_user_can('manage_options');
    }

    /**
     * Vérifier si l'utilisateur peut éditer les posts
     */
    public static function canEditPosts()
    {
        return is_user_logged_in() && current_user_can('edit_posts');
    }

    /**
     * Vérifier si l'utilisateur peut uploader des fichiers
     */
    public static function canUploadFiles()
    {
        return is_user_logged_in() && current_user_can('upload_files');
    }

    /**
     * Vérifier les permissions générales pour PDF Builder
     */
    public static function checkGeneralAccess()
    {
        if (!is_user_logged_in()) {
            return new \WP_Error('not_logged_in', 'Utilisateur non connecté');
        }

        if (!current_user_can('manage_options')) {
            return new \WP_Error('insufficient_permissions', 'Permissions insuffisantes');
        }

        return true;
    }
}

/**
 * Classe utilitaire pour la validation
 */
class Validation
{
    /**
     * Valider un nonce
     */
    public static function verifyNonce($nonce, $action = 'pdf_builder_ajax')
    {
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Valider et nettoyer les données POST
     */
    public static function sanitizePostData($data, $rules = [])
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                switch ($rule) {
                    case 'text':
                        $sanitized[$key] = sanitize_text_field($value);
                        break;
                    case 'textarea':
                        $sanitized[$key] = sanitize_textarea_field($value);
                        break;
                    case 'email':
                        $sanitized[$key] = sanitize_email($value);
                        break;
                    case 'int':
                        $sanitized[$key] = intval($value);
                        break;
                    case 'float':
                        $sanitized[$key] = floatval($value);
                        break;
                    case 'boolean':
                        $sanitized[$key] = $value ? '1' : '0';
                        break;
                    default:
                        $sanitized[$key] = sanitize_text_field($value);
                }
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Valider les données d'un template
     */
    public static function validateTemplateData($data)
    {
        if (!is_array($data)) {
            return new \WP_Error('invalid_data', 'Données de template invalides');
        }

        if (!isset($data['elements']) || !is_array($data['elements'])) {
            return new \WP_Error('missing_elements', 'Éléments manquants dans le template');
        }

        return true;
    }

    /**
     * Valider un ID de template
     */
    public static function validateTemplateId($id)
    {
        $id = intval($id);
        if ($id <= 0) {
            return new \WP_Error('invalid_id', 'ID de template invalide');
        }
        return $id;
    }
}

/**
 * Classe utilitaire pour les helpers généraux
 */
class Helpers
{
    /**
     * Générer un nom de fichier unique
     */
    public static function generateUniqueFilename($prefix = 'pdf_builder', $extension = 'pdf')
    {
        return $prefix . '_' . time() . '_' . wp_generate_password(6, false) . '.' . $extension;
    }

    /**
     * Créer un répertoire s'il n'existe pas
     */
    public static function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            wp_mkdir_p($path);
            return is_dir($path);
        }
        return true;
    }

    /**
     * Obtenir le chemin des uploads PDF Builder
     */
    public static function getUploadsPath()
    {
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/pdf-builder';

        if (self::ensureDirectoryExists($pdf_path)) {
            return $pdf_path;
        }

        return false;
    }

    /**
     * Obtenir l'URL des uploads PDF Builder
     */
    public static function getUploadsUrl()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/pdf-builder';
    }

    /**
     * Nettoyer les fichiers temporaires
     */
    public static function cleanupTempFiles($older_than_hours = 24)
    {
        $temp_dir = self::getUploadsPath() . '/temp';

        if (!is_dir($temp_dir)) {
            return;
        }

        $files = glob($temp_dir . '/*');
        $now = time();
        $max_age = $older_than_hours * 3600;

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $max_age) {
                unlink($file);
            }
        }
    }

    /**
     * Formater la taille d'un fichier
     */
    public static function formatFileSize($bytes)
    {
        return size_format($bytes);
    }

    /**
     * Obtenir les informations de débogage
     */
    public static function getDebugInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];
    }

    /**
     * Logger un message de débogage
     */
    public static function logDebug($message, $data = null)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[' . current_time('Y-m-d H:i:s') . '] ' . $message;
            if ($data !== null) {
                $log_message .= ' - Data: ' . wp_json_encode($data);
            }

            // error_log($log_message);
        }
    }

    /**
     * Vérifier si une fonction est disponible
     */
    public static function isFunctionAvailable($function_name)
    {
        return function_exists($function_name);
    }

    /**
     * Obtenir la valeur d'une option avec une valeur par défaut
     */
    public static function getOption($key, $default = '')
    {
        return get_option($key, $default);
    }

    /**
     * Définir une option
     */
    public static function setOption($key, $value)
    {
        return update_option($key, $value);
    }
}
