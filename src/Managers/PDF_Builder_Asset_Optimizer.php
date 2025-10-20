<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Asset Optimizer
 * Optimisation et compression des assets (JS, CSS, images)
 */

class PDF_Builder_Asset_Optimizer
{

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Répertoire des assets optimisés
     */
    private $optimized_dir;

    /**
     * Configuration d'optimisation
     */
    private $optimization_config = [
        'js_compression' => true,
        'css_compression' => true,
        'image_compression' => true,
        'image_quality' => 85,
        'cache_assets' => true,
        'minify_html' => true,
        'combine_files' => true,
        'preload_critical' => true
    ];

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initialize_optimizer();
    }

    /**
     * Initialiser l'optimiseur
     */
    private function initialize_optimizer()
    {
        $upload_dir = wp_upload_dir();
        $this->optimized_dir = $upload_dir['basedir'] . '/pdf-builder-optimized';
        if (!file_exists($this->optimized_dir)) {
            wp_mkdir_p($this->optimized_dir);
        }

        // Créer les sous-répertoires
        $subdirs = ['js', 'css', 'images', 'cache'];
        foreach ($subdirs as $subdir) {
            $dir = $this->optimized_dir . '/' . $subdir;
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
    }

    /**
     * Optimiser tous les assets du plugin
     */
    public function optimize_all_assets()
    {
        $results = [
            'js' => $this->optimize_javascript_assets(),
            'css' => $this->optimize_css_assets(),
            'images' => $this->optimize_image_assets(),
            'html' => $this->optimize_html_templates()
        ];

        $this->log_optimization_results($results);
        return $results;
    }

    /**
     * Optimiser les assets JavaScript
     */
    public function optimize_javascript_assets()
    {
        if (!$this->optimization_config['js_compression']) {
            return ['status' => 'disabled', 'files' => []];
        }

        $js_files = $this->get_plugin_js_files();
        $optimized_files = [];

        foreach ($js_files as $file) {
            $optimized = $this->optimize_javascript_file($file);
            if ($optimized) {
                $optimized_files[] = $optimized;
            }
        }

        // Combiner les fichiers si activé
        if ($this->optimization_config['combine_files'] && count($optimized_files) > 1) {
            $combined = $this->combine_javascript_files($optimized_files);
            if ($combined) {
                $optimized_files = [$combined];
            }
        }

        return [
            'status' => 'completed',
            'files' => $optimized_files,
            'original_count' => count($js_files),
            'optimized_count' => count($optimized_files)
        ];
    }

    /**
     * Optimiser les assets CSS
     */
    public function optimize_css_assets()
    {
        if (!$this->optimization_config['css_compression']) {
            return ['status' => 'disabled', 'files' => []];
        }

        $css_files = $this->get_plugin_css_files();
        $optimized_files = [];

        foreach ($css_files as $file) {
            $optimized = $this->optimize_css_file($file);
            if ($optimized) {
                $optimized_files[] = $optimized;
            }
        }

        // Combiner les fichiers si activé
        if ($this->optimization_config['combine_files'] && count($optimized_files) > 1) {
            $combined = $this->combine_css_files($optimized_files);
            if ($combined) {
                $optimized_files = [$combined];
            }
        }

        return [
            'status' => 'completed',
            'files' => $optimized_files,
            'original_count' => count($css_files),
            'optimized_count' => count($optimized_files)
        ];
    }

    /**
     * Optimiser les images
     */
    public function optimize_image_assets()
    {
        if (!$this->optimization_config['image_compression']) {
            return ['status' => 'disabled', 'files' => []];
        }

        $image_files = $this->get_plugin_image_files();
        $optimized_files = [];

        foreach ($image_files as $file) {
            $optimized = $this->optimize_image_file($file);
            if ($optimized) {
                $optimized_files[] = $optimized;
            }
        }

        return [
            'status' => 'completed',
            'files' => $optimized_files,
            'original_count' => count($image_files),
            'optimized_count' => count($optimized_files)
        ];
    }

    /**
     * Optimiser les templates HTML
     */
    public function optimize_html_templates()
    {
        if (!$this->optimization_config['minify_html']) {
            return ['status' => 'disabled', 'files' => []];
        }

        $html_files = $this->get_plugin_html_templates();
        $optimized_files = [];

        foreach ($html_files as $file) {
            $optimized = $this->optimize_html_file($file);
            if ($optimized) {
                $optimized_files[] = $optimized;
            }
        }

        return [
            'status' => 'completed',
            'files' => $optimized_files,
            'original_count' => count($html_files),
            'optimized_count' => count($optimized_files)
        ];
    }

    /**
     * Optimiser un fichier JavaScript
     */
    private function optimize_javascript_file($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $content = file_get_contents($file_path);
        if ($content === false) {
            return false;
        }

        // Minifier le JavaScript
        $minified = $this->minify_javascript($content);

        // Générer le nom du fichier optimisé
        $filename = basename($file_path, '.js') . '.min.js';
        $optimized_path = $this->optimized_dir . '/js/' . $filename;

        if (file_put_contents($optimized_path, $minified)) {
            return [
                'original' => $file_path,
                'optimized' => $optimized_path,
                'original_size' => strlen($content),
                'optimized_size' => strlen($minified),
                'compression_ratio' => strlen($content) > 0 ? (1 - strlen($minified) / strlen($content)) * 100 : 0
            ];
        }

        return false;
    }

    /**
     * Optimiser un fichier CSS
     */
    private function optimize_css_file($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $content = file_get_contents($file_path);
        if ($content === false) {
            return false;
        }

        // Minifier le CSS
        $minified = $this->minify_css($content);

        // Générer le nom du fichier optimisé
        $filename = basename($file_path, '.css') . '.min.css';
        $optimized_path = $this->optimized_dir . '/css/' . $filename;

        if (file_put_contents($optimized_path, $minified)) {
            return [
                'original' => $file_path,
                'optimized' => $optimized_path,
                'original_size' => strlen($content),
                'optimized_size' => strlen($minified),
                'compression_ratio' => strlen($content) > 0 ? (1 - strlen($minified) / strlen($content)) * 100 : 0
            ];
        }

        return false;
    }

    /**
     * Optimiser un fichier image
     */
    private function optimize_image_file($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $original_size = filesize($file_path);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $optimized_path = $this->optimized_dir . '/images/' . basename($file_path);

        $success = false;
        $optimized_size = $original_size;

        switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $success = $this->optimize_jpeg($file_path, $optimized_path);
            break;
        case 'png':
            $success = $this->optimize_png($file_path, $optimized_path);
            break;
        case 'gif':
            $success = $this->optimize_gif($file_path, $optimized_path);
            break;
        }

        if ($success && file_exists($optimized_path)) {
            $optimized_size = filesize($optimized_path);
        }

        return $success ? [
            'original' => $file_path,
            'optimized' => $optimized_path,
            'original_size' => $original_size,
            'optimized_size' => $optimized_size,
            'compression_ratio' => $original_size > 0 ? (1 - $optimized_size / $original_size) * 100 : 0
        ] : false;
    }

    /**
     * Optimiser un fichier HTML
     */
    private function optimize_html_file($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $content = file_get_contents($file_path);
        if ($content === false) {
            return false;
        }

        // Minifier le HTML
        $minified = $this->minify_html($content);

        // Générer le nom du fichier optimisé
        $filename = basename($file_path, '.html') . '.min.html';
        $optimized_path = $this->optimized_dir . '/cache/' . $filename;

        if (file_put_contents($optimized_path, $minified)) {
            return [
                'original' => $file_path,
                'optimized' => $optimized_path,
                'original_size' => strlen($content),
                'optimized_size' => strlen($minified),
                'compression_ratio' => strlen($content) > 0 ? (1 - strlen($minified) / strlen($content)) * 100 : 0
            ];
        }

        return false;
    }

    /**
     * Combiner plusieurs fichiers JavaScript
     */
    private function combine_javascript_files($files)
    {
        $combined_content = '';
        $total_original_size = 0;

        foreach ($files as $file_info) {
            if (file_exists($file_info['optimized'])) {
                $content = file_get_contents($file_info['optimized']);
                if ($content !== false) {
                    $combined_content .= $content . "\n";
                    $total_original_size += $file_info['original_size'];
                }
            }
        }

        $combined_path = $this->optimized_dir . '/js/combined.min.js';
        if (file_put_contents($combined_path, $combined_content)) {
            return [
                'original' => 'combined',
                'optimized' => $combined_path,
                'original_size' => $total_original_size,
                'optimized_size' => strlen($combined_content),
                'compression_ratio' => $total_original_size > 0 ? (1 - strlen($combined_content) / $total_original_size) * 100 : 0
            ];
        }

        return false;
    }

    /**
     * Combiner plusieurs fichiers CSS
     */
    private function combine_css_files($files)
    {
        $combined_content = '';
        $total_original_size = 0;

        foreach ($files as $file_info) {
            if (file_exists($file_info['optimized'])) {
                $content = file_get_contents($file_info['optimized']);
                if ($content !== false) {
                    $combined_content .= $content . "\n";
                    $total_original_size += $file_info['original_size'];
                }
            }
        }

        $combined_path = $this->optimized_dir . '/css/combined.min.css';
        if (file_put_contents($combined_path, $combined_content)) {
            return [
                'original' => 'combined',
                'optimized' => $combined_path,
                'original_size' => $total_original_size,
                'optimized_size' => strlen($combined_content),
                'compression_ratio' => $total_original_size > 0 ? (1 - strlen($combined_content) / $total_original_size) * 100 : 0
            ];
        }

        return false;
    }

    /**
     * Minifier JavaScript
     */
    private function minify_javascript($content)
    {
        // Suppression des commentaires
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
        $content = preg_replace('/\/\/.*$/m', '', $content);

        // Suppression des espaces et retours à la ligne inutiles
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\s*([{}();,])\s*/', '$1', $content);

        return trim($content);
    }

    /**
     * Minifier CSS
     */
    private function minify_css($content)
    {
        // Suppression des commentaires
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);

        // Suppression des espaces inutiles
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\s*([{}:;,])\s*/', '$1', $content);
        $content = preg_replace('/;}/', '}', $content);

        return trim($content);
    }

    /**
     * Minifier HTML
     */
    private function minify_html($content)
    {
        // Suppression des commentaires HTML
        $content = preg_replace('/<!--[\s\S]*?-->/', '', $content);

        // Suppression des espaces entre les balises
        $content = preg_replace('/>\s+</', '><', $content);

        // Suppression des espaces multiples
        $content = preg_replace('/\s+/', ' ', $content);

        return trim($content);
    }

    /**
     * Optimiser une image JPEG
     */
    private function optimize_jpeg($input_path, $output_path)
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return copy($input_path, $output_path);
        }

        $image = imagecreatefromjpeg($input_path);
        if (!$image) {
            return false;
        }

        $result = imagejpeg($image, $output_path, $this->optimization_config['image_quality']);
        imagedestroy($image);

        return $result;
    }

    /**
     * Optimiser une image PNG
     */
    private function optimize_png($input_path, $output_path)
    {
        if (!function_exists('imagecreatefrompng')) {
            return copy($input_path, $output_path);
        }

        $image = imagecreatefrompng($input_path);
        if (!$image) {
            return false;
        }

        // Activer la compression PNG
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $result = imagepng($image, $output_path, 9); // Compression maximale
        imagedestroy($image);

        return $result;
    }

    /**
     * Optimiser une image GIF
     */
    private function optimize_gif($input_path, $output_path)
    {
        // Pour GIF, on copie simplement (difficile à optimiser sans bibliothèques spéciales)
        return copy($input_path, $output_path);
    }

    /**
     * Obtenir les fichiers JS du plugin
     */
    private function get_plugin_js_files()
    {
        $js_files = [];

        // Assets du plugin
        $assets_js = glob(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/js/*.js');
        if ($assets_js) {
            $js_files = array_merge($js_files, $assets_js);
        }

        // Resources JS
        $resources_js = glob(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/resources/js/*.js');
        if ($resources_js) {
            $js_files = array_merge($js_files, $resources_js);
        }

        return $js_files;
    }

    /**
     * Obtenir les fichiers CSS du plugin
     */
    private function get_plugin_css_files()
    {
        $css_files = [];

        // Assets CSS
        $assets_css = glob(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/css/*.css');
        if ($assets_css) {
            $css_files = array_merge($css_files, $assets_css);
        }

        return $css_files;
    }

    /**
     * Obtenir les fichiers images du plugin
     */
    private function get_plugin_image_files()
    {
        $image_files = [];

        // Images dans assets
        $assets_images = glob(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/images/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        if ($assets_images) {
            $image_files = array_merge($image_files, $assets_images);
        }

        return $image_files;
    }

    /**
     * Obtenir les templates HTML
     */
    private function get_plugin_html_templates()
    {
        $html_files = [];

        // Templates
        $templates = glob(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/templates/**/*.html');
        if ($templates) {
            $html_files = array_merge($html_files, $templates);
        }

        return $html_files;
    }

    /**
     * Logger les résultats d'optimisation
     */
    private function log_optimization_results($results)
    {
        $logger = new PDF_Builder_Logger();

        $total_savings = 0;
        $total_original = 0;

        foreach ($results as $type => $result) {
            if ($result['status'] === 'completed' && isset($result['files'])) {
                foreach ($result['files'] as $file) {
                    if (isset($file['original_size']) && isset($file['optimized_size'])) {
                        $total_original += $file['original_size'];
                        $total_savings += ($file['original_size'] - $file['optimized_size']);
                    }
                }
            }
        }

        $compression_ratio = $total_original > 0 ? ($total_savings / $total_original) * 100 : 0;

        $message = sprintf(
            'Optimisation assets terminée - Économies: %s (%d%%)',
            size_format($total_savings),
            round($compression_ratio, 1)
        );

        $logger->log($message, 'info', 'asset_optimizer');
    }

    /**
     * Générer les URLs des assets optimisés
     */
    public function get_optimized_asset_urls()
    {
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'] . '/pdf-builder-optimized';

        return [
            'js' => $base_url . '/js/',
            'css' => $base_url . '/css/',
            'images' => $base_url . '/images/',
            'cache' => $base_url . '/cache/'
        ];
    }

    /**
     * Nettoyer les assets optimisés
     */
    public function cleanup_optimized_assets($older_than_days = 7)
    {
        $cutoff_time = time() - ($older_than_days * 24 * 60 * 60);

        $this->cleanup_directory($this->optimized_dir, $cutoff_time);
    }

    private function cleanup_directory($dir, $cutoff_time)
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoff_time) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Obtenir les statistiques d'optimisation
     */
    public function get_optimization_stats()
    {
        $stats = [
            'total_optimized_size' => $this->get_directory_size($this->optimized_dir),
            'config' => $this->optimization_config,
            'last_optimization' => get_option('pdf_builder_last_asset_optimization', false)
        ];

        return $stats;
    }

    private function get_directory_size($dir)
    {
        $size = 0;
        if (!file_exists($dir)) {
            return $size;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
