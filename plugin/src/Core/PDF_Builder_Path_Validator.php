<?php

/**
 * PDF Builder Pro - Validateur de Chemins
 *
 * Classe responsable de la validation des chemins de fichiers
 * pour prévenir les attaques Path Traversal
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 * @since   Phase 5.8 - Corrections Sécurité
 */

namespace WP_PDF_Builder_Pro\Core;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PdfBuilderPathValidator
{
    /**
     * Extensions de fichiers autorisées pour les PDFs
     */
    const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', // Images
        'pdf', 'txt', 'csv', 'json' // Documents
    ];

    /**
     * Répertoires autorisés (relatifs à wp-content/uploads)
     */
    const ALLOWED_DIRECTORIES = [
        'pdf-builder-pro',
        'pdf-builder-pro/templates',
        'pdf-builder-pro/images',
        'pdf-builder-pro/fonts',
        'pdf-builder-pro/temp'
    ];

    /**
     * Valide un chemin de fichier
     *
     * @param  string $path           Chemin à
     *                                valider
     * @param  bool   $allow_absolute Autoriser les chemins absolus (false par
     *                                défaut)
     * @return bool True si valide
     */
    public static function validateFilePath($path, $allow_absolute = false)
    {
        if (empty($path)) {
            return false;
        }

        // Normalisation du chemin
        $path = wp_normalize_path($path);

        // Vérification des traversées de répertoire
        if (self::containsDirectoryTraversal($path)) {
            self::logSecurityEvent(
                'path_traversal_attempt',
                [
                'path' => $path,
                'user_id' => get_current_user_id(),
                'ip' => self::getClientIp()
                ]
            );
            return false;
        }

        // Vérification des chemins absolus
        if (!$allow_absolute && self::isAbsolutePath($path)) {
            self::logSecurityEvent(
                'absolute_path_attempt',
                [
                'path' => $path,
                'user_id' => get_current_user_id(),
                'ip' => self::getClientIp()
                ]
            );
            return false;
        }

        // Vérification de l'extension
        if (!self::hasAllowedExtension($path)) {
            self::logSecurityEvent(
                'disallowed_extension',
                [
                'path' => $path,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'user_id' => get_current_user_id(),
                'ip' => self::getClientIp()
                ]
            );
            return false;
        }

        // Vérification du répertoire autorisé
        if (!self::isInAllowedDirectory($path)) {
            self::logSecurityEvent(
                'disallowed_directory',
                [
                'path' => $path,
                'user_id' => get_current_user_id(),
                'ip' => self::getClientIp()
                ]
            );
            return false;
        }

        return true;
    }

    /**
     * Vérifie si le chemin contient une traversée de répertoire
     *
     * @param  string $path Chemin à vérifier
     * @return bool True si contient une traversée
     */
    private static function containsDirectoryTraversal($path)
    {
        // Vérifications de base
        $traversal_patterns = [
            '..',
            '...',
            '....',
            '%2e%2e', // URL encoded ..
            '%2e%2e%2f', // URL encoded ../
            '%2e%2e/', // URL encoded ../
            '..%2f', // ..%2f
            '%2e%2e%5c', // URL encoded ..\
            '..\\', // Windows path traversal
            '..\\\\' // Double backslash
        ];

        foreach ($traversal_patterns as $pattern) {
            if (stripos($path, $pattern) !== false) {
                return true;
            }
        }

        // Vérification des chemins qui commencent par /
        if (strpos($path, '/') === 0 && !self::isSafeAbsolutePath($path)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si le chemin est absolu
     *
     * @param  string $path Chemin à vérifier
     * @return bool True si absolu
     */
    private static function isAbsolutePath($path)
    {
        // Windows absolute paths
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return true;
        }

        // Unix absolute paths
        if (strpos($path, '/') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si un chemin absolu est considéré comme sûr
     *
     * @param  string $path Chemin absolu
     * @return bool True si sûr
     */
    private static function isSafeAbsolutePath($path)
    {
        $upload_dir = wp_upload_dir();
        $allowed_base_paths = [
            $upload_dir['basedir'],
            WP_CONTENT_DIR,
            ABSPATH
        ];

        foreach ($allowed_base_paths as $base_path) {
            $normalized_base = wp_normalize_path($base_path);
            if (strpos($path, $normalized_base) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'extension du fichier est autorisée
     *
     * @param  string $path Chemin du fichier
     * @return bool True si autorisée
     */
    private static function hasAllowedExtension($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Pour les URLs data: (base64 encoded images)
        if (strpos($path, 'data:') === 0) {
            return self::isValidDataUrl($path);
        }

        return in_array($extension, self::ALLOWED_EXTENSIONS);
    }

    /**
     * Vérifie si une URL data: est valide
     *
     * @param  string $data_url URL data à vérifier
     * @return bool True si valide
     */
    private static function isValidDataUrl($data_url)
    {
        // Format: data:[<mediatype>][;base64],<data>
        if (!preg_match('/^data:([a-z]+\/[a-z]+(;base64)?)?,/', $data_url)) {
            return false;
        }

        // Extraction du type MIME
        preg_match('/^data:([a-z]+\/[a-z]+)/', $data_url, $matches);
        $mime_type = isset($matches[1]) ? $matches[1] : '';

        // Types MIME autorisés
        $allowed_mime_types = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'image/svg+xml', 'image/webp'
        ];

        return in_array($mime_type, $allowed_mime_types);
    }

    /**
     * Vérifie si le fichier est dans un répertoire autorisé
     *
     * @param  string $path Chemin du fichier
     * @return bool True si dans répertoire autorisé
     */
    private static function isInAllowedDirectory($path)
    {
        $upload_dir = wp_upload_dir();

        // Pour les chemins relatifs, vérifier par rapport à uploads
        foreach (self::ALLOWED_DIRECTORIES as $allowed_dir) {
            $full_allowed_path = wp_normalize_path($upload_dir['basedir'] . '/' . $allowed_dir);

            if (strpos($path, $full_allowed_path) === 0) {
                return true;
            }
        }

        // Pour les URLs data: et chemins spéciaux
        if (strpos($path, 'data:') === 0 || strpos($path, 'http') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Construit un chemin sûr vers un fichier
     *
     * @param  string $filename     Nom du fichier
     * @param  string $subdirectory Sous-répertoire (optionnel)
     * @return string Chemin complet sécurisé
     */
    public static function buildSafePath($filename, $subdirectory = '')
    {
        $upload_dir = wp_upload_dir();

        // Sanitisation du nom de fichier
        $filename = sanitize_file_name($filename);

        // Construction du chemin
        $path_parts = array_filter([$upload_dir['basedir'], 'pdf-builder-pro', $subdirectory, $filename]);
        $full_path = wp_normalize_path(implode('/', $path_parts));

        // Vérification finale
        if (!self::validateFilePath($full_path, true)) {
            throw new Exception('Chemin de fichier invalide généré');
        }

        return $full_path;
    }

    /**
     * Obtient l'adresse IP du client
     *
     * @return string Adresse IP
     */
    private static function getClientIp()
    {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip_parts = explode(',', $ip);
                    $ip = trim(end($ip_parts));
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Log un événement de sécurité
     *
     * @param string $event Type d'événement
     * @param array  $data  Données
     *                      supplémentaires
     */
    private static function logSecurityEvent($event, $data = [])
    {
        $log_data = array_merge(
            [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
            ],
            $data
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
        }
    }
}
