<?php
/**
 * PDF Builder Pro - Système de thème et personnalisation
 * Gère les thèmes, couleurs, polices et personnalisation de l'interface
 */

class PDF_Builder_Theme_Customizer {
    private static $instance = null;

    // Thèmes prédéfinis
    const THEME_DEFAULT = 'default';
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';
    const THEME_BLUE = 'blue';
    const THEME_GREEN = 'green';
    const THEME_PURPLE = 'purple';

    // Couleurs par défaut
    private $default_colors = [
        'primary' => '#007cba',
        'secondary' => '#6c757d',
        'success' => '#28a745',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'light' => '#f8f9fa',
        'dark' => '#343a40'
    ];

    // Polices disponibles
    private $available_fonts = [
        'system' => 'System Font',
        'arial' => 'Arial',
        'helvetica' => 'Helvetica',
        'times' => 'Times New Roman',
        'georgia' => 'Georgia',
        'verdana' => 'Verdana',
        'tahoma' => 'Tahoma',
        'trebuchet' => 'Trebuchet MS',
        'impact' => 'Impact',
        'comic' => 'Comic Sans MS'
    ];

    // Tailles de police
    private $font_sizes = [
        'xs' => '0.75rem',
        'sm' => '0.875rem',
        'base' => '1rem',
        'lg' => '1.125rem',
        'xl' => '1.25rem',
        '2xl' => '1.5rem',
        '3xl' => '1.875rem',
        '4xl' => '2.25rem'
    ];

    // Configuration actuelle du thème
    private $current_theme_config = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_theme_config();
    }

    private function init_hooks() {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_save_theme', [$this, 'save_theme_ajax']);
        add_action('wp_ajax_pdf_builder_reset_theme', [$this, 'reset_theme_ajax']);
        add_action('wp_ajax_pdf_builder_get_theme_config', [$this, 'get_theme_config_ajax']);
        add_action('wp_ajax_pdf_builder_preview_theme', [$this, 'preview_theme_ajax']);

        // Enqueue des styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_theme_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);

        // Filtres de personnalisation
        add_filter('pdf_builder_admin_css', [$this, 'apply_theme_css']);
        add_filter('pdf_builder_frontend_css', [$this, 'apply_theme_css']);

        // Actions d'administration
        add_action('admin_init', [$this, 'register_theme_settings']);
        add_action('admin_menu', [$this, 'add_theme_menu']);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_theme_cache']);
    }

    /**
     * Enregistre les paramètres du thème
     */
    public function register_theme_settings() {
        register_setting('pdf_builder_theme', 'pdf_builder_theme_preset', [
            'type' => 'string',
            'default' => self::THEME_DEFAULT,
            'sanitize_callback' => [$this, 'sanitize_theme_preset']
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_custom_colors', [
            'type' => 'array',
            'default' => [],
            'sanitize_callback' => [$this, 'sanitize_color_array']
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_font_family', [
            'type' => 'string',
            'default' => 'system',
            'sanitize_callback' => [$this, 'sanitize_font_family']
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_font_size', [
            'type' => 'string',
            'default' => 'base',
            'sanitize_callback' => [$this, 'sanitize_font_size']
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_border_radius', [
            'type' => 'string',
            'default' => '4px',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_enable_animations', [
            'type' => 'boolean',
            'default' => true
        ]);

        register_setting('pdf_builder_theme', 'pdf_builder_enable_shadows', [
            'type' => 'boolean',
            'default' => true
        ]);
    }

    /**
     * Ajoute le menu du thème
     */
    public function add_theme_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Personnalisation du thème', 'theme'),
            pdf_builder_translate('Thème', 'theme'),
            'manage_options',
            'pdf-builder-theme',
            [$this, 'render_theme_page']
        );
    }

    /**
     * Rend la page du thème
     */
    public function render_theme_page() {
        if (!current_user_can('manage_options')) {
            wp_die(pdf_builder_translate('Accès refusé', 'theme'));
        }

        $themes = $this->get_available_themes();
        $current_theme = $this->get_current_theme_preset();
        $custom_colors = $this->get_custom_colors();
        $font_family = $this->get_font_family();
        $font_size = $this->get_font_size();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/theme-customizer.php';
    }

    /**
     * Charge la configuration du thème
     */
    private function load_theme_config() {
        $this->current_theme_config = [
            'preset' => get_option('pdf_builder_theme_preset', self::THEME_DEFAULT),
            'colors' => array_merge($this->default_colors, get_option('pdf_builder_custom_colors', [])),
            'font_family' => get_option('pdf_builder_font_family', 'system'),
            'font_size' => get_option('pdf_builder_font_size', 'base'),
            'border_radius' => get_option('pdf_builder_border_radius', '4px'),
            'animations' => get_option('pdf_builder_enable_animations', true),
            'shadows' => get_option('pdf_builder_enable_shadows', true)
        ];
    }

    /**
     * Obtient les thèmes disponibles
     */
    public function get_available_themes() {
        return [
            self::THEME_DEFAULT => [
                'name' => pdf_builder_translate('Par défaut', 'theme'),
                'description' => pdf_builder_translate('Thème WordPress standard', 'theme'),
                'colors' => $this->default_colors
            ],
            self::THEME_DARK => [
                'name' => pdf_builder_translate('Sombre', 'theme'),
                'description' => pdf_builder_translate('Thème sombre moderne', 'theme'),
                'colors' => array_merge($this->default_colors, [
                    'primary' => '#bb86fc',
                    'light' => '#1e1e1e',
                    'dark' => '#ffffff'
                ])
            ],
            self::THEME_LIGHT => [
                'name' => pdf_builder_translate('Clair', 'theme'),
                'description' => pdf_builder_translate('Thème clair et aéré', 'theme'),
                'colors' => array_merge($this->default_colors, [
                    'primary' => '#007cba',
                    'light' => '#ffffff',
                    'dark' => '#2c3338'
                ])
            ],
            self::THEME_BLUE => [
                'name' => pdf_builder_translate('Bleu', 'theme'),
                'description' => pdf_builder_translate('Thème bleu professionnel', 'theme'),
                'colors' => array_merge($this->default_colors, [
                    'primary' => '#0066cc',
                    'info' => '#0099ff'
                ])
            ],
            self::THEME_GREEN => [
                'name' => pdf_builder_translate('Vert', 'theme'),
                'description' => pdf_builder_translate('Thème vert naturel', 'theme'),
                'colors' => array_merge($this->default_colors, [
                    'primary' => '#28a745',
                    'success' => '#20c997'
                ])
            ],
            self::THEME_PURPLE => [
                'name' => pdf_builder_translate('Violet', 'theme'),
                'description' => pdf_builder_translate('Thème violet créatif', 'theme'),
                'colors' => array_merge($this->default_colors, [
                    'primary' => '#6f42c1',
                    'secondary' => '#e83e8c'
                ])
            ]
        ];
    }

    /**
     * Obtient le thème actuel
     */
    public function get_current_theme_preset() {
        return $this->current_theme_config['preset'];
    }

    /**
     * Obtient les couleurs personnalisées
     */
    public function get_custom_colors() {
        return $this->current_theme_config['colors'];
    }

    /**
     * Obtient la police actuelle
     */
    public function get_font_family() {
        return $this->current_theme_config['font_family'];
    }

    /**
     * Obtient la taille de police actuelle
     */
    public function get_font_size() {
        return $this->current_theme_config['font_size'];
    }

    /**
     * Applique le CSS du thème
     */
    public function apply_theme_css($css) {
        $theme_css = $this->generate_theme_css();

        return $css . "\n" . $theme_css;
    }

    /**
     * Génère le CSS du thème
     */
    public function generate_theme_css() {
        $config = $this->current_theme_config;
        $css = [];

        // Variables CSS pour les couleurs
        $css[] = ":root {";
        foreach ($config['colors'] as $name => $color) {
            $css[] = "  --pdf-{$name}-color: {$color};";
        }
        $css[] = "}";

        // Police
        $font_family = $this->get_font_family_css($config['font_family']);
        if ($font_family) {
            $css[] = "body.pdf-builder-admin, .pdf-builder-frontend {";
            $css[] = "  font-family: {$font_family};";
            $css[] = "}";
        }

        // Taille de police
        $font_size = $this->font_sizes[$config['font_size']] ?? $this->font_sizes['base'];
        $css[] = ".pdf-builder-container {";
        $css[] = "  font-size: {$font_size};";
        $css[] = "}";

        // Rayon des bordures
        $css[] = ".pdf-builder-button, .pdf-builder-input, .pdf-builder-card {";
        $css[] = "  border-radius: {$config['border_radius']};";
        $css[] = "}";

        // Animations
        if (!$config['animations']) {
            $css[] = ".pdf-builder-container * {";
            $css[] = "  transition: none !important;";
            $css[] = "  animation: none !important;";
            $css[] = "}";
        }

        // Ombres
        if (!$config['shadows']) {
            $css[] = ".pdf-builder-container * {";
            $css[] = "  box-shadow: none !important;";
            $css[] = "}";
        }

        // Classes utilitaires pour les couleurs
        foreach ($config['colors'] as $name => $color) {
            $css[] = ".pdf-text-{$name} { color: {$color}; }";
            $css[] = ".pdf-bg-{$name} { background-color: {$color}; }";
            $css[] = ".pdf-border-{$name} { border-color: {$color}; }";
        }

        return implode("\n", $css);
    }

    /**
     * Obtient le CSS de la police
     */
    private function get_font_family_css($font_key) {
        $font_map = [
            'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'arial' => 'Arial, sans-serif',
            'helvetica' => 'Helvetica, Arial, sans-serif',
            'times' => '"Times New Roman", Times, serif',
            'georgia' => 'Georgia, "Times New Roman", serif',
            'verdana' => 'Verdana, Geneva, sans-serif',
            'tahoma' => 'Tahoma, Geneva, sans-serif',
            'trebuchet' => '"Trebuchet MS", Helvetica, sans-serif',
            'impact' => 'Impact, Charcoal, sans-serif',
            'comic' => '"Comic Sans MS", cursive, sans-serif'
        ];

        return $font_map[$font_key] ?? $font_map['system'];
    }

    /**
     * Enqueue les styles du thème pour l'admin
     */
    public function enqueue_theme_styles($hook) {
        if (strpos($hook, 'pdf-builder') === false) {
            return;
        }

        wp_enqueue_style(
            'pdf-builder-theme',
            admin_url('admin-ajax.php?action=pdf_builder_get_theme_css'),
            [],
            PDF_BUILDER_VERSION
        );

        wp_add_inline_style('pdf-builder-theme', $this->generate_theme_css());
    }

    /**
     * Enqueue les styles du thème pour le frontend
     */
    public function enqueue_frontend_styles() {
        if (!is_singular() || !has_shortcode(get_post()->post_content, 'pdf_builder')) {
            return;
        }

        wp_enqueue_style(
            'pdf-builder-frontend-theme',
            admin_url('admin-ajax.php?action=pdf_builder_get_theme_css&frontend=1'),
            [],
            PDF_BUILDER_VERSION
        );

        wp_add_inline_style('pdf-builder-frontend-theme', $this->generate_theme_css());
    }

    /**
     * Sauvegarde la configuration du thème
     */
    public function save_theme_config($config) {
        $sanitized_config = $this->sanitize_theme_config($config);

        foreach ($sanitized_config as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        $this->load_theme_config();

        // Vider le cache CSS
        $this->clear_css_cache();

        return true;
    }

    /**
     * Sanitise la configuration du thème
     */
    private function sanitize_theme_config($config) {
        return [
            'theme_preset' => $this->sanitize_theme_preset($config['theme_preset'] ?? self::THEME_DEFAULT),
            'custom_colors' => $this->sanitize_color_array($config['custom_colors'] ?? []),
            'font_family' => $this->sanitize_font_family($config['font_family'] ?? 'system'),
            'font_size' => $this->sanitize_font_size($config['font_size'] ?? 'base'),
            'border_radius' => sanitize_text_field($config['border_radius'] ?? '4px'),
            'enable_animations' => (bool) ($config['enable_animations'] ?? true),
            'enable_shadows' => (bool) ($config['enable_shadows'] ?? true)
        ];
    }

    /**
     * Sanitise un preset de thème
     */
    public function sanitize_theme_preset($preset) {
        $available = array_keys($this->get_available_themes());
        return in_array($preset, $available) ? $preset : self::THEME_DEFAULT;
    }

    /**
     * Sanitise un tableau de couleurs
     */
    public function sanitize_color_array($colors) {
        $sanitized = [];

        foreach ($colors as $key => $color) {
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                $sanitized[$key] = $color;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitise une police
     */
    public function sanitize_font_family($font) {
        return isset($this->available_fonts[$font]) ? $font : 'system';
    }

    /**
     * Sanitise une taille de police
     */
    public function sanitize_font_size($size) {
        return isset($this->font_sizes[$size]) ? $size : 'base';
    }

    /**
     * Réinitialise le thème
     */
    public function reset_theme() {
        delete_option('pdf_builder_theme_preset');
        delete_option('pdf_builder_custom_colors');
        delete_option('pdf_builder_font_family');
        delete_option('pdf_builder_font_size');
        delete_option('pdf_builder_border_radius');
        delete_option('pdf_builder_enable_animations');
        delete_option('pdf_builder_enable_shadows');

        $this->load_theme_config();
        $this->clear_css_cache();

        return true;
    }

    /**
     * Vide le cache CSS
     */
    private function clear_css_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/pdf-builder/';

        if (is_dir($cache_dir)) {
            $this->delete_directory_recursive($cache_dir);
        }
    }

    /**
     * Supprime un dossier récursivement
     */
    private function delete_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->delete_directory_recursive($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Prévisualise un thème
     */
    public function preview_theme($config) {
        $original_config = $this->current_theme_config;

        // Appliquer temporairement la configuration
        $this->current_theme_config = array_merge($this->current_theme_config, $this->sanitize_theme_config($config));

        $css = $this->generate_theme_css();

        // Restaurer la configuration originale
        $this->current_theme_config = $original_config;

        return $css;
    }

    /**
     * Nettoie le cache du thème
     */
    public function cleanup_theme_cache() {
        $this->clear_css_cache();
    }

    /**
     * Obtient les polices disponibles
     */
    public function get_available_fonts() {
        return $this->available_fonts;
    }

    /**
     * Obtient les tailles de police disponibles
     */
    public function get_available_font_sizes() {
        return $this->font_sizes;
    }

    /**
     * AJAX - Sauvegarde le thème
     */
    public function save_theme_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $config = $_POST['theme_config'] ?? [];

            if (empty($config)) {
                wp_send_json_error(['message' => 'Configuration manquante']);
                return;
            }

            $success = $this->save_theme_config($config);

            if ($success) {
                wp_send_json_success([
                    'message' => pdf_builder_translate('Thème sauvegardé avec succès', 'theme'),
                    'css' => $this->generate_theme_css()
                ]);
            } else {
                wp_send_json_error(['message' => pdf_builder_translate('Erreur lors de la sauvegarde', 'theme')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Réinitialise le thème
     */
    public function reset_theme_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $success = $this->reset_theme();

            if ($success) {
                wp_send_json_success([
                    'message' => pdf_builder_translate('Thème réinitialisé avec succès', 'theme'),
                    'css' => $this->generate_theme_css()
                ]);
            } else {
                wp_send_json_error(['message' => pdf_builder_translate('Erreur lors de la réinitialisation', 'theme')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient la configuration du thème
     */
    public function get_theme_config_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $config = $this->current_theme_config;
            $config['available_themes'] = $this->get_available_themes();
            $config['available_fonts'] = $this->get_available_fonts();
            $config['available_font_sizes'] = $this->get_available_font_sizes();

            wp_send_json_success([
                'message' => 'Configuration récupérée',
                'config' => $config
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Prévisualise un thème
     */
    public function preview_theme_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $config = $_POST['theme_config'] ?? [];

            $css = $this->preview_theme($config);

            wp_send_json_success([
                'message' => 'Aperçu généré',
                'css' => $css
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_theme_customizer() {
    return PDF_Builder_Theme_Customizer::get_instance();
}

function pdf_builder_get_theme_css() {
    return PDF_Builder_Theme_Customizer::get_instance()->generate_theme_css();
}

function pdf_builder_get_available_themes() {
    return PDF_Builder_Theme_Customizer::get_instance()->get_available_themes();
}

function pdf_builder_save_theme_config($config) {
    return PDF_Builder_Theme_Customizer::get_instance()->save_theme_config($config);
}

function pdf_builder_reset_theme() {
    return PDF_Builder_Theme_Customizer::get_instance()->reset_theme();
}

function pdf_builder_preview_theme($config) {
    return PDF_Builder_Theme_Customizer::get_instance()->preview_theme($config);
}

// Classes utilitaires CSS
function pdf_builder_get_color_class($color_name) {
    return "pdf-text-{$color_name}";
}

function pdf_builder_get_bg_class($color_name) {
    return "pdf-bg-{$color_name}";
}

function pdf_builder_get_border_class($color_name) {
    return "pdf-border-{$color_name}";
}

// Initialiser le système de thème
add_action('plugins_loaded', function() {
    PDF_Builder_Theme_Customizer::get_instance();
});
