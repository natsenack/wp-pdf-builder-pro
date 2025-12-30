<?php
/**
 * PDF Builder Pro - Asset Compression Module
 * Compression et optimisation des assets pour de meilleures performances
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// GESTIONNAIRE DE COMPRESSION DES ASSETS
// ============================================================================

class PDF_Builder_Asset_Compressor {

    private static $instance = null;
    private $cache_dir;
    private $cache_url;
    private $compression_enabled = true;
    private $cache_enabled = true;

    private function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/pdf-builder-assets/';
        $this->cache_url = content_url('/cache/pdf-builder-assets/');

        // Créer le répertoire de cache si nécessaire
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }

        $this->init_hooks();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_hooks() {
        // Hook pour servir les assets compressés
        add_action('init', array($this, 'serve_compressed_assets'));

        // Nettoyage périodique du cache
        add_action('wp_pdf_cleanup_asset_cache', array($this, 'cleanup_cache'));

        // Planifier le nettoyage du cache
        if (!wp_next_scheduled('wp_pdf_cleanup_asset_cache')) {
            wp_schedule_event(time(), 'daily', 'wp_pdf_cleanup_asset_cache');
        }

        // Optimiser les assets lors de l'enqueue
        add_action('wp_enqueue_scripts', array($this, 'optimize_scripts'), 999);
        add_action('admin_enqueue_scripts', array($this, 'optimize_scripts'), 999);
        add_action('wp_enqueue_style', array($this, 'optimize_styles'), 999);
        add_action('admin_enqueue_style', array($this, 'optimize_styles'), 999);
    }

    /**
     * Optimise les scripts enqueued
     */
    public function optimize_scripts() {
        if (!$this->compression_enabled) return;

        global $wp_scripts;

        foreach ($wp_scripts->queue as $handle) {
            if (isset($wp_scripts->registered[$handle])) {
                $script = $wp_scripts->registered[$handle];

                // Compresser seulement les scripts du plugin
                if (strpos($script->src, 'pdf-builder') !== false) {
                    $compressed_src = $this->get_compressed_asset($script->src, 'js');
                    if ($compressed_src) {
                        $script->src = $compressed_src;
                    }
                }
            }
        }
    }

    /**
     * Optimise les styles enqueued
     */
    public function optimize_styles() {
        if (!$this->compression_enabled) return;

        global $wp_styles;

        foreach ($wp_styles->queue as $handle) {
            if (isset($wp_styles->registered[$handle])) {
                $style = $wp_styles->registered[$handle];

                // Compresser seulement les styles du plugin
                if (strpos($style->src, 'pdf-builder') !== false) {
                    $compressed_src = $this->get_compressed_asset($style->src, 'css');
                    if ($compressed_src) {
                        $style->src = $compressed_src;
                    }
                }
            }
        }
    }

    /**
     * Obtient l'URL d'un asset compressé
     */
    private function get_compressed_asset($original_url, $type) {
        if (!$this->cache_enabled) return $original_url;

        // Générer une clé de cache basée sur l'URL et la date de modification
        $cache_key = $this->generate_cache_key($original_url);
        $cache_file = $this->cache_dir . $cache_key . '.min.' . $type;

        // Vérifier si le cache existe et est valide
        if ($this->is_cache_valid($cache_file, $original_url)) {
            return $this->cache_url . $cache_key . '.min.' . $type;
        }

        // Créer le cache
        if ($this->create_compressed_cache($original_url, $cache_file, $type)) {
            return $this->cache_url . $cache_key . '.min.' . $type;
        }

        return $original_url;
    }

    /**
     * Génère une clé de cache unique
     */
    private function generate_cache_key($url) {
        // Obtenir le chemin du fichier
        $file_path = $this->url_to_path($url);
        if (!$file_path || !file_exists($file_path)) {
            return md5($url);
        }

        // Inclure la date de modification dans la clé
        $mtime = filemtime($file_path);
        return md5($url . $mtime);
    }

    /**
     * Convertit une URL en chemin de fichier
     */
    private function url_to_path($url) {
        // Convertir l'URL en chemin absolu
        $site_url = site_url();
        if (strpos($url, $site_url) === 0) {
            return str_replace($site_url, ABSPATH, $url);
        }

        // Pour les URLs relatives
        if (strpos($url, '/') === 0) {
            return ABSPATH . ltrim($url, '/');
        }

        return false;
    }

    /**
     * Vérifie si le cache est valide
     */
    private function is_cache_valid($cache_file, $original_url) {
        if (!file_exists($cache_file)) {
            return false;
        }

        // Vérifier si le fichier original a été modifié
        $original_path = $this->url_to_path($original_url);
        if ($original_path && file_exists($original_path)) {
            $original_mtime = filemtime($original_path);
            $cache_mtime = filemtime($cache_file);

            if ($original_mtime > $cache_mtime) {
                return false;
            }
        }

        // Vérifier l'âge du cache (max 24h)
        $cache_age = time() - filemtime($cache_file);
        if ($cache_age > 86400) {
            return false;
        }

        return true;
    }

    /**
     * Crée un fichier compressé en cache
     */
    private function create_compressed_cache($original_url, $cache_file, $type) {
        $original_path = $this->url_to_path($original_url);
        if (!$original_path || !file_exists($original_path)) {
            return false;
        }

        // Lire le contenu original
        $content = file_get_contents($original_path);
        if (!$content) {
            return false;
        }

        // Compresser selon le type
        $compressed_content = $this->compress_content($content, $type);

        // Écrire dans le cache
        if (file_put_contents($cache_file, $compressed_content) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Compresse le contenu selon le type
     */
    private function compress_content($content, $type) {
        switch ($type) {
            case 'js':
                return $this->compress_javascript($content);
            case 'css':
                return $this->compress_css($content);
            default:
                return $content;
        }
    }

    /**
     * Compresse le JavaScript
     */
    private function compress_javascript($content) {
        // Suppression des commentaires
        $content = preg_replace('!/\*.*?\*/!s', '', $content);
        $content = preg_replace('!//.*?$!m', '', $content);

        // Suppression des espaces inutiles
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\s*([{}();,:])\s*/', '$1', $content);

        // Suppression des sauts de ligne
        $content = str_replace(["\r\n", "\r", "\n"], '', $content);

        return trim($content);
    }

    /**
     * Compresse le CSS
     */
    private function compress_css($content) {
        // Suppression des commentaires
        $content = preg_replace('!/\*.*?\*/!s', '', $content);

        // Suppression des espaces inutiles
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\s*([{}();,:])\s*/', '$1', $content);

        // Suppression des sauts de ligne
        $content = str_replace(["\r\n", "\r", "\n"], '', $content);

        return trim($content);
    }

    /**
     * Sert les assets compressés
     */
    public function serve_compressed_assets() {
        // Intercepter les requêtes vers les assets compressés
        if (isset($_GET['pdf_asset'])) {
            $this->serve_asset($_GET['pdf_asset']);
            exit;
        }
    }

    /**
     * Sert un asset spécifique
     */
    private function serve_asset($asset_name) {
        $asset_path = $this->cache_dir . $asset_name;

        if (!file_exists($asset_path)) {
            http_response_code(404);
            exit;
        }

        // Déterminer le type MIME
        $mime_type = $this->get_mime_type($asset_name);

        // Headers de cache
        header('Content-Type: ' . $mime_type);
        header('Cache-Control: public, max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        // Compression GZIP si supportée
        if ($this->supports_gzip()) {
            header('Content-Encoding: gzip');
            readfile($asset_path);
        } else {
            readfile($asset_path);
        }
    }

    /**
     * Détermine le type MIME d'un fichier
     */
    private function get_mime_type($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mime_types = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'min.js' => 'application/javascript',
            'min.css' => 'text/css'
        ];

        return $mime_types[$extension] ?? 'text/plain';
    }

    /**
     * Vérifie si le client supporte GZIP
     */
    private function supports_gzip() {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
               strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
    }

    /**
     * Nettoie le cache des assets
     */
    public function cleanup_cache() {
        if (!is_dir($this->cache_dir)) {
            return;
        }

        $files = glob($this->cache_dir . '*');
        $now = time();
        $max_age = 7 * 24 * 60 * 60; // 7 jours

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $max_age) {
                unlink($file);
            }
        }
    }

    /**
     * Active/désactive la compression
     */
    public function set_compression_enabled($enabled) {
        $this->compression_enabled = $enabled;
    }

    /**
     * Active/désactive le cache
     */
    public function set_cache_enabled($enabled) {
        $this->cache_enabled = $enabled;
    }

    /**
     * Obtient les statistiques de performance
     */
    public function get_stats() {
        $stats = [
            'cache_enabled' => $this->cache_enabled,
            'compression_enabled' => $this->compression_enabled,
            'cache_size' => $this->get_cache_size(),
            'cache_files' => $this->get_cache_file_count()
        ];

        return $stats;
    }

    /**
     * Calcule la taille du cache
     */
    private function get_cache_size() {
        if (!is_dir($this->cache_dir)) {
            return 0;
        }

        $size = 0;
        $files = glob($this->cache_dir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }

        return $size;
    }

    /**
     * Compte le nombre de fichiers en cache
     */
    private function get_cache_file_count() {
        if (!is_dir($this->cache_dir)) {
            return 0;
        }

        $files = glob($this->cache_dir . '*');
        return count($files);
    }
}

// ============================================================================
// INITIALISATION DU MODULE
// ============================================================================

// Initialiser le compresseur d'assets
add_action('plugins_loaded', function() {
    PDF_Builder_Asset_Compressor::get_instance();
});

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Obtient l'instance du compresseur d'assets
 */
function pdf_builder_get_asset_compressor() {
    return PDF_Builder_Asset_Compressor::get_instance();
}

/**
 * Active/désactive la compression des assets
 */
function pdf_builder_set_asset_compression($enabled) {
    $compressor = pdf_builder_get_asset_compressor();
    $compressor->set_compression_enabled($enabled);
}

/**
 * Active/désactive le cache des assets
 */
function pdf_builder_set_asset_cache($enabled) {
    $compressor = pdf_builder_get_asset_compressor();
    $compressor->set_cache_enabled($enabled);
}

/**
 * Obtient les statistiques du compresseur d'assets
 */
function pdf_builder_get_asset_stats() {
    $compressor = pdf_builder_get_asset_compressor();
    return $compressor->get_stats();
}