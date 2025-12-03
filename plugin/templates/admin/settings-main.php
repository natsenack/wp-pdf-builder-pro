<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');

// Include helper functions
require_once __DIR__ . '/settings-parts/settings-helpers.php';

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_nonce');
?>
<div class="wrap">
<h1>PDF Builder Pro Settings</h1>

<nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
<a href="#" class="nav-tab nav-tab-active" data-tab="general">General</a>
<a href="#" class="nav-tab" data-tab="licence">License</a>
<a href="#" class="nav-tab" data-tab="systeme">System</a>
<a href="#" class="nav-tab" data-tab="acces">Access</a>
<a href="#" class="nav-tab" data-tab="securite">Security</a>
<a href="#" class="nav-tab" data-tab="pdf">PDF</a>
<a href="#" class="nav-tab" data-tab="contenu">Content</a>
<a href="#" class="nav-tab" data-tab="templates">Templates</a>
<a href="#" class="nav-tab" data-tab="developpeur">Developer</a>
</nav>

<div id="pdf-builder-tab-content">
<div id="tab-content-general" class="tab-content active">
<?php include __DIR__ . '/settings-parts/settings-general.php'; ?>
</div>
<div id="tab-content-licence" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-licence.php'; ?>
</div>
<div id="tab-content-systeme" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-systeme.php'; ?>
</div>
<div id="tab-content-acces" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-acces.php'; ?>
</div>
<div id="tab-content-securite" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-securite.php'; ?>
</div>
<div id="tab-content-pdf" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-pdf.php'; ?>
</div>
<div id="tab-content-contenu" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-contenu.php'; ?>
</div>



            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <section class="contenu-canvas-section">
                <h3>
                    <span>
                        🎨 Canvas
                    </span>
                </h3>

                <p>Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

                <form method="post" id="canvas-form">
                    <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="submit_canvas" value="1">

                    <!-- Grille de cartes Canvas -->
                    <div class="canvas-settings-grid">
                        <!-- Carte Dimensions & Format -->
                        <article class="canvas-card" data-category="dimensions">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📐</span>
                                </div>
                                <h4>Dimensions & Format</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Définissez la taille, la résolution et le format de votre canvas de conception.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="preview-format">
                                    <div >
                                        <span id="card-canvas-width">794</span>×
                                        <span id="card-canvas-height">1123</span>px
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        96 DPI - A4 (210.0×297.0mm)
                                    </span>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚙️</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Apparence -->
                        <article class="canvas-card" data-category="apparence">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎨</span>
                                </div>
                                <h4>Apparence</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Personnalisez les couleurs, bordures et effets visuels du canvas.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="color-preview bg" title="Fond"></div>
                                <div class="color-preview border" title="Bordure"></div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎨</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Grille & Guides -->
                        <article class="canvas-card" data-category="grille">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📏</span>
                                </div>
                                <h4>Grille & Guides</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Configurez l'affichage et l'alignement sur la grille de conception.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="grid-preview-container">
                                    <div class="grid-canvas">
                                        <!-- Quadrillage principal -->
                                        <div class="grid-lines">
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line vertical"></div>
                                            <div class="grid-line vertical"></div>
                                            <div class="grid-line vertical"></div>
                                        </div>
                                        <!-- Points d'intersection -->
                                        <div class="grid-dots">
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                        </div>
                                        <!-- Guides d'alignement -->
                                        <div class="guide-lines">
                                            <div class="guide-line horizontal active"></div>
                                            <div class="guide-line vertical active"></div>
                                        </div>
                                        <!-- Élément d'exemple -->
                                        <div class="preview-element">
                                            <div class="element-box"></div>
                                        </div>
                                    </div>
                                    <div class="grid-legend">
                                        <span class="legend-item">📐 Grille</span>
                                        <span class="legend-item">📏 Guides</span>
                                        <span class="legend-item">📦 Élément</span>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📏</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Zoom -->
                        <article class="canvas-card" id="zoom-navigation-card" data-category="zoom">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🔍</span>
                                </div>
                                <h4>Zoom</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les niveaux de zoom et les options de navigation.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="zoom-preview-container">
                                    <div class="zoom-indicator">
                                        <button class="zoom-btn zoom-minus" disabled>−</button>
                                        <span class="zoom-level">100%</span>
                                        <button class="zoom-btn zoom-plus" disabled>+</button>
                                    </div>
                                    <div class="zoom-info">
                                        <span>10% - 500%</span>
                                        <span>Pas: 25%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🔍</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Interactions & Comportement -->
                        <article class="canvas-card" data-category="interactions">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎯</span>
                                </div>
                                <h4>Interactions & Comportement</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les interactions canvas, la sélection et les raccourcis clavier.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="interactions-preview-container">
                                    <!-- Canvas miniature avec éléments -->
                                    <div class="mini-canvas">
                                        <!-- Grille de fond -->
                                        <div class="mini-canvas-grid"></div>

                                        <!-- Éléments sur le canvas -->
                                        <div class="mini-element text-element" style="top: 15px; left: 20px; width: 40px; height: 20px;">
                                            <div class="mini-element-content">T</div>
                                        </div>
                                        <div class="mini-element shape-element selected" style="top: 45px; left: 15px; width: 35px; height: 25px;">
                                            <div class="mini-element-content">□</div>
                                            <!-- Poignées de sélection -->
                                            <div class="mini-handle nw"></div>
                                            <div class="mini-handle ne"></div>
                                            <div class="mini-handle sw"></div>
                                            <div class="mini-handle se"></div>
                                            <div class="mini-handle rotation" style="top: -8px; left: 50%; transform: translateX(-50%);"></div>
                                        </div>
                                        <div class="mini-element image-element" style="top: 20px; left: 70px; width: 30px; height: 30px;">
                                            <div class="mini-element-content">🖼</div>
                                        </div>

                                        <!-- Sélection rectangle en cours -->
                                        <div class="selection-rectangle" style="top: 10px; left: 10px; width: 60px; height: 40px;"></div>

                                        <!-- Curseur de souris -->
                                        <div class="mouse-cursor" style="top: 55px; left: 85px;">
                                            <div class="cursor-icon">👆</div>
                                        </div>
                                    </div>

                                    <!-- Contrôles en bas -->
                                    <div class="interactions-controls">
                                        <div class="selection-mode-indicator">
                                            <span class="mode-icon active" title="Rectangle">▭</span>
                                            <span class="mode-icon" title="Lasso">🪢</span>
                                            <span class="mode-icon" title="Clic">👆</span>
                                        </div>
                                        <div class="interaction-status">
                                            <span class="status-indicator selecting">Sélection en cours</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎯</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Export & Qualité -->
                        <article class="canvas-card" data-category="export">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📤</span>
                                </div>
                                <h4>Export & Qualité</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Configurez les formats et la qualité d'export des designs.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="export-preview" title="Export PNG/JPG/PDF activé"></div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📤</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Performance -->
                        <article class="canvas-card" data-category="performance">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚡</span>
                                </div>
                                <h4>Performance</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Optimisez les FPS, mémoire et temps de réponse.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="performance-preview-container">
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span class="metric-value">60</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM JS</span>
                                            <span class="metric-value">256MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value">256MB</span>
                                        </div>
                                    </div>
                                    <div class="performance-status">
                                        <div class="status-indicator">
                                            <span class="status-dot"></span>
                                            <span class="status-text">Lazy Loading</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚡</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Debug -->
                        <article class="canvas-card" data-category="debug">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🐛</span>
                                </div>
                                <h4>Debug</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Outils de débogage et monitoring des performances.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="debug-preview-container">
                                    <div class="debug-console">
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:15]</span>
                                            <span class="console-level info">INFO</span>
                                            <span class="console-message">Canvas initialized</span>
                                        </div>
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:16]</span>
                                            <span class="console-level warn">WARN</span>
                                            <span class="console-message">Memory usage: 85%</span>
                                        </div>
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:17]</span>
                                            <span class="console-level error">ERROR</span>
                                            <span class="console-message">Failed to load image</span>
                                        </div>
                                        <div class="console-cursor">_</div>
                                    </div>
                                    <div class="debug-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">FPS</span>
                                            <span class="stat-value">60</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">RAM</span>
                                            <span class="stat-value">85MB</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Errors</span>
                                            <span class="stat-value">2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🐛</span> Configurer
                                </button>
                            </div>
                        </article>
                    </div>
                </form>
            </section>

                <!-- Section Templates -->
            <section class="contenu-templates-section">
                <h3>
                    <span>
                        📋 Templates
                        <span id="template-library-indicator" class="template-library-indicator" style="background: <?php echo pdf_builder_safe_get_option('pdf_builder_template_library_enabled', true) ? '#28a745' : '#dc3545'; ?>;"><?php echo pdf_builder_safe_get_option('pdf_builder_template_library_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="default_template">Template par défaut</label></th>
                        <td>
                            <select id="default_template" name="default_template">
                                <option value="blank" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_template', 'blank'), 'blank'); ?>>Page blanche</option>
                                <option value="invoice" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_template', 'blank'), 'invoice'); ?>>Facture</option>
                                <option value="quote" <?php pdf_builder_safe_selected(pdf_builder_safe_get_option('pdf_builder_default_template', 'blank'), 'quote'); ?>>Devis</option>
                            </select>
                            <p class="description">Template utilisé par défaut pour nouveaux documents</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="template_library_enabled">Bibliothèque de templates</label></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" id="template_library_enabled" name="template_library_enabled" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_template_library_enabled', true)); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">Active la bibliothèque de templates prédéfinis</p>
                        </td>
                    </tr>
                </table>
            </section>
</div>
<div id="tab-content-templates" class="tab-content">
<?php
/**
 * Templates par statut tab content
 * Gestion des templates PDF par défaut selon le statut des commandes WooCommerce
 * Supporte les statuts personnalisés ajoutés par des plugins tiers
 * Updated: 2025-12-02 - Code réorganisé pour une meilleure lisibilité

/**
 * Safe wrapper for get_option that works even when WordPress is not fully loaded
 */
function pdf_builder_safe_get_option($option, $default = '') {
    if (function_exists('get_option')) {
        return get_option($option, $default);
    }
    return $default;
}

/**
 * Safe wrapper for checked function
 */
function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
    if (function_exists('checked')) {
        return checked($checked, $current, $echo);
    }
    $result = checked($checked, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for selected function
 */
function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
    if (function_exists('selected')) {
        return selected($selected, $current, $echo);
    }
    $result = selected($selected, $current, false);
    if ($echo) echo $result;
    return $result;
}
?>
 */

// =============================================================================
// CLASSE UTILITAIRE POUR LA GESTION DES STATUTS ET PLUGINS
// =============================================================================
class PDF_Template_Status_Manager {

    private static $instance = null;
    private $woocommerce_active = false;
    private $order_statuses = [];
    private $custom_status_plugins = [];
    private $status_plugins = [];
    private $templates = [];
    private $current_mappings = [];

    private function __construct() {
        $this->init_woocommerce_status();
        $this->load_templates();
        $this->load_mappings();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Initialisation des statuts WooCommerce
    private function init_woocommerce_status() {
        $this->woocommerce_active = class_exists('WooCommerce');

        if (!$this->woocommerce_active) {
            return;
        }

        $this->order_statuses = $this->get_order_statuses();
        $this->detect_custom_statuses();
    }

    // Récupération des statuts de commande
    public function get_order_statuses() {
        if (function_exists('wc_get_order_statuses')) {
            return wc_get_order_statuses();
        } elseif (class_exists('WC_Order') && method_exists('WC_Order', 'get_statuses')) {
            return WC_Order::get_statuses();
        } else {
            $statuses = pdf_builder_safe_get_option('wc_order_statuses', []);
            return !empty($statuses) ? $statuses : [
                'wc-pending' => 'En attente de paiement',
                'wc-processing' => 'En cours',
                'wc-on-hold' => 'En attente',
                'wc-completed' => 'Terminée',
                'wc-cancelled' => 'Annulée',
                'wc-refunded' => 'Remboursée',
                'wc-failed' => 'Échec'
            ];
        }
    }

    // Détection des statuts personnalisés
    private function detect_custom_statuses() {
        $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];

        foreach ($this->order_statuses as $status_key => $status_name) {
            $clean_status_key = str_replace('wc-', '', $status_key);

            if (!in_array($clean_status_key, $default_statuses)) {
                $plugin_name = $this->detect_custom_status_plugin($clean_status_key);
                if ($plugin_name) {
                    $this->custom_status_plugins[] = $plugin_name;
                    $this->status_plugins[$status_key] = $plugin_name;
                }
            }
        }

        $this->custom_status_plugins = array_unique($this->custom_status_plugins);
    }

    // Chargement des templates
    private function load_templates() {
        global $wpdb;

        // Templates WordPress
        $templates_wp = $wpdb->get_results("
            SELECT ID, post_title
            FROM {$wpdb->posts}
            WHERE post_type = 'pdf_template'
            AND post_status = 'publish'
            ORDER BY post_title ASC
        ", ARRAY_A);

        $wp_templates = [];
        if ($templates_wp) {
            foreach ($templates_wp as $template) {
                $wp_templates[$template['ID']] = $template['post_title'];
            }
        }

        // Templates personnalisés
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $templates_custom = $wpdb->get_results("
            SELECT id, name
            FROM {$table_templates}
            ORDER BY name ASC
        ", ARRAY_A);

        $custom_templates = [];
        if ($templates_custom) {
            foreach ($templates_custom as $template) {
                $custom_templates['custom_' . $template['id']] = $template['name'];
            }
        }

        $this->templates = array_merge($wp_templates, $custom_templates);
    }

    // Chargement des mappings
    private function load_mappings() {
        $this->current_mappings = pdf_builder_safe_get_option('pdf_builder_order_status_templates', []);

        // Nettoyer les mappings obsolètes
        if (!empty($this->current_mappings) && !empty($this->order_statuses)) {
            $valid_statuses = array_keys($this->order_statuses);
            $this->current_mappings = array_intersect_key($this->current_mappings, array_flip($valid_statuses));

            // Sauvegarder si nécessaire
            if (count($this->current_mappings) !== count(pdf_builder_safe_get_option('pdf_builder_order_status_templates', []))) {
                update_option('pdf_builder_order_status_templates', $this->current_mappings);
            }
        }
    }

    // Détection du plugin pour un statut personnalisé
    private function detect_custom_status_plugin($status_key) {
        // 1. Vérifier les options WooCommerce
        $custom_statuses = pdf_builder_safe_get_option('wc_order_statuses', []);
        if (!empty($custom_statuses) && isset($custom_statuses['wc-' . $status_key])) {
            $status_data = $custom_statuses['wc-' . $status_key];
            if (is_array($status_data) && isset($status_data['label'])) {
                return $this->detect_plugin_from_status_data($status_data, $status_key);
            }
        }

        // 2. Chercher dans les plugins actifs
        $detected_plugin = $this->detect_plugin_from_active_plugins($status_key);
        if ($detected_plugin) {
            return $detected_plugin;
        }

        // 3. Analyse des patterns (fallback)
        return $this->detect_plugin_from_patterns($status_key);
    }

    // Détection depuis les données du statut
    private function detect_plugin_from_status_data($status_data, $status_key) {
        global $wpdb;

        $plugin_indicators = [
            'wc_order_status_manager' => [
                'options' => ['wc_order_status_manager', 'wc_osm_'],
                'transient_prefix' => 'wc_osm_'
            ],
            'yith_custom_order_status' => [
                'options' => ['yith_wccos', 'yith_custom_order_status'],
                'transient_prefix' => 'yith_wccos_'
            ],
            'custom_order_status' => [
                'options' => ['custom_order_status', 'alg_wc_custom_order_status'],
                'transient_prefix' => 'alg_wc_cos_'
            ],
        ];

        foreach ($plugin_indicators as $plugin_key => $indicators) {
            foreach ($indicators['options'] as $option_pattern) {
                $option_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                    $option_pattern . '%'
                ));
                if ($option_exists) {
                    return $this->get_plugin_display_name($plugin_key);
                }
            }

            if (!empty($indicators['transient_prefix'])) {
                $transient_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                    '_transient_' . $indicators['transient_prefix'] . '%'
                ));
                if ($transient_exists) {
                    return $this->get_plugin_display_name($plugin_key);
                }
            }
        }

        return null;
    }

    // Détection depuis les plugins actifs
    private function detect_plugin_from_active_plugins($status_key) {
        global $wpdb;

        $active_plugins = pdf_builder_safe_get_option('active_plugins', []);
        $excluded_plugins = ['woocommerce/woocommerce.php'];
        $active_plugins = array_diff($active_plugins, $excluded_plugins);

        // Analyse des options de base de données
        foreach ($active_plugins as $plugin_file) {
            $plugin_slug = dirname($plugin_file);

            $plugin_options = $wpdb->get_results($wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND (option_name LIKE %s OR option_name LIKE %s)
                LIMIT 5",
                $plugin_slug . '%', '%status%', '%order%'
            ));

            if (!empty($plugin_options)) {
                $plugin_name = $this->get_plugin_display_name_from_file($plugin_file);
                if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                    return $plugin_name;
                }
            }
        }

        // Analyse des transients
        foreach ($active_plugins as $plugin_file) {
            $plugin_slug = dirname($plugin_file);

            $transient_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND (option_name LIKE %s OR option_name LIKE %s)
                LIMIT 1",
                '_transient_' . $plugin_slug . '%', '%status%', '%order%'
            ));

            if ($transient_exists) {
                $plugin_name = $this->get_plugin_display_name_from_file($plugin_file);
                if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                    return $plugin_name;
                }
            }
        }

        // Analyse des headers des plugins
        foreach ($active_plugins as $plugin_file) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
            $search_text = strtolower($plugin_data['Name'] . ' ' . $plugin_data['Description']);

            if (strpos($search_text, 'status') !== false && strpos($search_text, 'order') !== false) {
                return $plugin_data['Name'];
            }

            if (strpos($search_text, 'expédition') !== false || strpos($search_text, 'shipping') !== false) {
                return $plugin_data['Name'] . ' (Expédition)';
            }

            if (strpos($search_text, 'marketplace') !== false || strpos($search_text, 'vendor') !== false) {
                return $plugin_data['Name'] . ' (Marketplace)';
            }
        }

        return null;
    }

    // Détection par patterns
    private function detect_plugin_from_patterns($status_key) {
        $status_patterns = [
            // Expédition
            'shipped' => 'Plugin d\'expédition',
            'delivered' => 'Plugin de livraison',
            'ready_to_ship' => 'Plugin d\'expédition',
            'partial_shipment' => 'Plugin d\'expédition partielle',
            'in_transit' => 'Plugin de suivi d\'expédition',
            'out_for_delivery' => 'Plugin de livraison',
            'shipped_partial' => 'Plugin d\'expédition partielle',

            // Préparation
            'packed' => 'Plugin de préparation de commande',
            'packing' => 'Plugin de préparation de commande',
            'ready_for_pickup' => 'Plugin de préparation de commande',
            'prepared' => 'Plugin de préparation de commande',

            // Paiement
            'awaiting_payment' => 'Plugin de paiement personnalisé',
            'payment_pending' => 'Plugin de paiement personnalisé',
            'payment_confirmed' => 'Plugin de paiement personnalisé',
            'payment_failed' => 'Plugin de paiement personnalisé',
            'payment_cancelled' => 'Plugin de paiement personnalisé',

            // Retours
            'return_requested' => 'Plugin de gestion des retours',
            'return_approved' => 'Plugin de gestion des retours',
            'return_received' => 'Plugin de gestion des retours',
            'refund_pending' => 'Plugin de remboursement personnalisé',
            'refund_issued' => 'Plugin de remboursement personnalisé',

            // Marketplace
            'vendor_pending' => 'Plugin marketplace',
            'vendor_approved' => 'Plugin marketplace',
            'vendor_rejected' => 'Plugin marketplace',
            'commission_pending' => 'Plugin marketplace',
            'commission_paid' => 'Plugin marketplace',
        ];

        if (isset($status_patterns[$status_key])) {
            return $status_patterns[$status_key];
        }

        foreach ($status_patterns as $pattern => $plugin_type) {
            if (strpos($status_key, $pattern) !== false || strpos($pattern, $status_key) !== false) {
                return $plugin_type;
            }
        }

        return null;
    }

    // Utilitaires pour les noms de plugins
    private function get_plugin_display_name($plugin_key) {
        $plugin_names = [
            'wc_order_status_manager' => 'WooCommerce Order Status Manager',
            'yith_custom_order_status' => 'YITH WooCommerce Custom Order Status',
            'woobewoo_order_status' => 'WooBeWoo Order Status',
            'custom_order_status' => 'Custom Order Status for WooCommerce',
            'order_status_actions' => 'WooCommerce Order Status & Actions Manager',
            'table_rate_shipping' => 'WooCommerce Table Rate Shipping',
            'shipment_tracking' => 'WooCommerce Shipment Tracking',
            'dokan' => 'Dokan (Marketplace)',
            'wc_vendors' => 'WC Vendors (Marketplace)',
            'product_vendors' => 'WooCommerce Product Vendors',
        ];

        return isset($plugin_names[$plugin_key]) ? $plugin_names[$plugin_key] : ucfirst(str_replace('_', ' ', $plugin_key));
    }

    private function get_plugin_display_name_from_file($plugin_file) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

        if (!empty($plugin_data['Name'])) {
            return $plugin_data['Name'];
        }

        $plugin_slug = dirname($plugin_file);
        return ucwords(str_replace(['-', '_'], ' ', $plugin_slug));
    }

    // Getters publics
    public function is_woocommerce_active() {
        return $this->woocommerce_active;
    }

    public function get_custom_status_plugins() {
        return $this->custom_status_plugins;
    }

    public function get_status_plugins() {
        return $this->status_plugins;
    }

    public function get_templates() {
        return $this->templates;
    }

    public function get_current_mappings() {
        return $this->current_mappings;
    }

    public function is_custom_status($status_key) {
        $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];
        $clean_status_key = str_replace('wc-', '', $status_key);
        return !in_array($clean_status_key, $default_statuses);
    }
}

// =============================================================================
// INITIALISATION ET RÉCUPÉRATION DES DONNÉES
// =============================================================================

$status_manager = PDF_Template_Status_Manager::get_instance();
$woocommerce_active = $status_manager->is_woocommerce_active();
$order_statuses = $status_manager->get_order_statuses();
$custom_status_plugins = $status_manager->get_custom_status_plugins();
$status_plugins = $status_manager->get_status_plugins();
$templates = $status_manager->get_templates();
$current_mappings = $status_manager->get_current_mappings();
// =============================================================================
// AFFICHAGE HTML
// =============================================================================
?>
<section class="templates-status-wrapper">
    <!-- En-tête -->
    <header>
        <h2 style="display: flex; justify-content: space-between; align-items: center;">
            <span>[TEMPLATES] Templates par Statut de Commande</span>
            <?php if (!empty($custom_status_plugins)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #666;">
                    [PLUGINS] Plugins détectés: <?php echo esc_html(implode(', ', $custom_status_plugins)); ?>
                </span>
            <?php elseif ($woocommerce_active && !empty($order_statuses)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #28a745;">
                    ✅ Statuts WooCommerce standards uniquement
                </span>
            <?php endif; ?>
        </h2>
    </header>

    <!-- Contenu principal -->
    <main>
        <?php if (!$woocommerce_active): ?>
            <!-- Message d'avertissement WooCommerce -->
            <div class="notice notice-warning">
                <p><strong>[WARNING] WooCommerce n'est pas actif</strong></p>
                <p>Cette fonctionnalité nécessite WooCommerce pour fonctionner. Veuillez installer et activer WooCommerce.</p>
            </div>
        <?php else: ?>
            <!-- Formulaire de configuration -->
            <form method="post" action="" id="templates-status-form">
                <!-- Grille des statuts -->
                <div class="templates-status-grid">
                    <?php foreach ($order_statuses as $status_key => $status_label):
                        $is_custom_status = $status_manager->is_custom_status($status_key);
                    ?>
                        <article class="template-status-card <?php echo $is_custom_status ? 'custom-status-card' : ''; ?>">
                            <header>
                                <h4>
                                    <?php echo esc_html($status_label); ?>
                                    <?php if ($is_custom_status): ?>
                                        <?php
                                        $detected_plugin = isset($status_plugins[$status_key]) ? $status_plugins[$status_key] : 'Plugin inconnu';
                                        $tooltip_text = "Slug personnalisé détecté - ajouté par: {$detected_plugin}";
                                        ?>
                                        <span class="custom-status-indicator"
                                              data-tooltip="<?php echo esc_attr($tooltip_text); ?>"
                                              style="font-family: Arial, sans-serif;">[SEARCH]</span>
                                    <?php endif; ?>
                                </h4>
                            </header>

                            <!-- Sélecteur de template -->
                            <div class="template-selector">
                                <label for="template_<?php echo esc_attr($status_key); ?>">
                                    Template par défaut :
                                </label>
                                <select name="order_status_templates[<?php echo esc_attr($status_key); ?>]"
                                        id="template_<?php echo esc_attr($status_key); ?>"
                                        class="template-select">
                                    <option value="">-- Aucun template --</option>
                                    <?php foreach ($templates as $template_id => $template_title): ?>
                                        <option value="<?php echo esc_attr($template_id); ?>"
                                                <?php pdf_builder_safe_selected($current_mappings[$status_key] ?? '', $template_id); ?>>
                                            <?php echo esc_html($template_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Aperçu du template assigné -->
                            <div class="template-preview">
                                <?php if (!empty($current_mappings[$status_key]) && isset($templates[$current_mappings[$status_key]])): ?>
                                    <p class="current-template">
                                        <strong>Assigné :</strong> <?php echo esc_html($templates[$current_mappings[$status_key]]); ?>
                                        <span class="assigned-badge">✅</span>
                                    </p>
                                <?php else: ?>
                                    <p class="no-template">Aucun template assigné</p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Actions -->
                <section class="templates-status-actions">
                    <button type="button" class="button button-secondary" onclick="PDFBuilderTabsAPI.resetTemplatesStatus()">
                        [RESET] Réinitialiser
                    </button>
                </section>
            </form>
        <?php endif; ?>
    </main>
</section>

<!-- Styles CSS -->


<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
</div>
<div id="tab-content-developpeur" class="tab-content">
<?php // Developer tab content - Updated: 2025-11-18 20:20:00

/**
 * Safe wrapper for get_option that works even when WordPress is not fully loaded
 */
function pdf_builder_safe_get_option($option, $default = '') {
    if (function_exists('get_option')) {
        return get_option($option, $default);
    }
    return $default;
}

/**
 * Safe wrapper for checked function
 */
function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
    if (function_exists('checked')) {
        return checked($checked, $current, $echo);
    }
    $result = checked($checked, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for selected function
 */
function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
    if (function_exists('selected')) {
        return selected($selected, $current, $echo);
    }
    $result = selected($selected, $current, false);
    if ($echo) echo $result;
    return $result;
}

// Variables nécessaires pour l'onglet développeur
$license_test_mode = (isset($settings) && isset($settings['pdf_builder_license_test_mode_enabled'])) ? $settings['pdf_builder_license_test_mode_enabled'] : false;
$license_test_key = (isset($settings) && isset($settings['pdf_builder_license_test_key'])) ? $settings['pdf_builder_license_test_key'] : '';
?>
            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

         <form method="post" id="developpeur-form">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_developpeur_nonce'); ?>
                <input type="hidden" name="submit_developpeur" value="1">

                <h3 class="section-title">🔐 Contrôle d'Accès</h3>

             <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="pdf_builder_developer_enabled" value="1" <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                            <span class="developer-status-indicator" style="margin-left: 10px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'background: #28a745; color: white;' : 'background: #dc3545; color: white;'; ?>">
                                <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'ACTIF' : 'INACTIF'; ?>
                            </span>
                        </div>
                        <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <!-- Champ username caché pour l'accessibilité -->
                        <input type="text" autocomplete="username" style="display: none;" />
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" id="developer_password" name="pdf_builder_developer_password"
                                   placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                                   style="width: 250px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   value="<?php echo esc_attr($settings['pdf_builder_developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                        <?php if (!empty($settings['developer_password'])) :
                            ?>
                        <p class="description" style="color: #28a745;">✓ Mot de passe configuré et sauvegardé</p>
                            <?php
                        endif; ?>
                    </td>
                </tr>
             </table>

            <section id="dev-license-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔐 Test de Licence</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="license_test_mode">Mode Test Licence</label></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <button type="button" id="toggle_license_test_mode_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    🎚️ Basculer Mode Test
                                </button>
                                <span id="license_test_mode_status" style="font-weight: bold; padding: 8px 12px; border-radius: 4px; <?php echo $license_test_mode ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                                    <?php echo $license_test_mode ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                                </span>
                            </div>
                            <p class="description">Basculer le mode test pour développer et tester sans serveur de licence en production</p>
                            <input type="checkbox" id="license_test_mode" name="license_test_mode" value="1" <?php echo $license_test_mode ? 'checked' : ''; ?> style="display: none;" />
                            <input type="hidden" id="toggle_license_test_mode_nonce" value="<?php echo wp_create_nonce('pdf_builder_toggle_test_mode'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Clé de Test</label></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="license_test_key" readonly style="width: 350px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;" placeholder="Générer une clé..." value="<?php echo esc_attr($license_test_key); ?>" />
                                <button type="button" id="generate_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    🔑 Générer
                                </button>
                                <button type="button" id="copy_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    📋 Copier
                                </button>
                                <?php if ($license_test_key) :
                                    ?>
                                <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="padding: 8px 12px; height: auto;">
                                    🗑️ Supprimer
                                </button>
                                    <?php
                                endif; ?>
                            </div>
                            <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                            <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="generate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_generate_test_license_key'); ?>" />
                            <input type="hidden" id="delete_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_delete_test_license_key'); ?>" />
                            <input type="hidden" id="validate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_validate_test_license_key'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Nettoyage Complet</label></th>
                        <td>
                            <button type="button" id="cleanup_license_btn" class="button button-link-delete" style="padding: 10px 15px; height: auto; font-weight: bold;">
                                🧹 Nettoyer complètement la licence
                            </button>
                            <p class="description">Supprime tous les paramètres de licence et réinitialise à l'état libre. Utile pour les tests.</p>
                            <span id="cleanup_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="cleanup_license_nonce" value="<?php echo wp_create_nonce('pdf_builder_cleanup_license'); ?>" />
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-debug-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔍 Paramètres de Debug</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_php_errors" name="pdf_builder_debug_php_errors" value="1" <?php echo isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug PHP</span>
                            </div>
                            <div class="toggle-description">Affiche les erreurs/warnings PHP du plugin</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_javascript">Debug JavaScript</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_javascript" name="pdf_builder_debug_javascript" value="1" <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug JS</span>
                            </div>
                            <div class="toggle-description">Active les logs détaillés en console (emojis: 🚀 start, ✅ success, ❌ error, ⚠️ warn)</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_javascript_verbose">Logs Verbeux JS</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_javascript_verbose" name="pdf_builder_debug_javascript_verbose" value="1" <?php echo isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Logs détaillés</span>
                            </div>
                            <div class="toggle-description">Active les logs détaillés (rendu, interactions, etc.). À désactiver en production.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_ajax">Debug AJAX</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_ajax" name="pdf_builder_debug_ajax" value="1" <?php echo isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug AJAX</span>
                            </div>
                            <div class="toggle-description">Enregistre toutes les requêtes AJAX avec requête/réponse</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_performance">Métriques Performance</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_performance" name="pdf_builder_debug_performance" value="1" <?php echo isset($settings['pdf_builder_debug_performance']) && $settings['pdf_builder_debug_performance'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug perf.</span>
                            </div>
                            <div class="toggle-description">Affiche le temps d'exécution et l'utilisation mémoire des opérations</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_database">Requêtes BD</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_database" name="pdf_builder_debug_database" value="1" <?php echo isset($settings['pdf_builder_debug_database']) && $settings['pdf_builder_debug_database'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug DB</span>
                            </div>
                            <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-logs-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Fichiers Logs</h3>

                <table class="form-table">
                    <tr>
                    <th scope="row"><label for="log_level">Niveau de Log</label></th>
                        <td>
                            <select id="log_level" name="pdf_builder_log_level" style="width: 200px;">
                                <option value="0" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 0) ? 'selected' : ''; ?>>Aucun log</option>
                                <option value="1" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 1) ? 'selected' : ''; ?>>Erreurs uniquement</option>
                                <option value="2" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 2) ? 'selected' : ''; ?>>Erreurs + Avertissements</option>
                                <option value="3" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 3) ? 'selected' : ''; ?>>Info complète</option>
                                <option value="4" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 4) ? 'selected' : ''; ?>>Détails (Développement)</option>
                            </select>
                            <p class="description">0=Aucun, 1=Erreurs, 2=Warn, 3=Info, 4=Détails</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="log_file_size">Taille Max Log</label></th>
                        <td>
                            <input type="number" id="log_file_size" name="pdf_builder_log_file_size" value="<?php echo isset($settings['pdf_builder_log_file_size']) ? intval($settings['pdf_builder_log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                            <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="log_retention">Retention Logs</label></th>
                        <td>
                            <input type="number" id="log_retention" name="pdf_builder_log_retention" value="<?php echo isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : '30'; ?>" min="1" max="365" /> jours
                            <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-optimizations-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Optimisations Avancées</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="force_https" name="force_https" value="1" <?php echo isset($settings['pdf_builder_force_https']) && $settings['pdf_builder_force_https'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">HTTPS forcé</span>
                            </div>
                            <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-logs-viewer-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Visualiseur de Logs Temps Réel</h3>

                <div style="margin-bottom: 15px;">
                    <button type="button" id="refresh_logs_btn" class="button button-secondary">🔄 Actualiser Logs</button>
                    <button type="button" id="clear_logs_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Logs</button>
                    <select id="log_filter" style="margin-left: 10px;">
                        <option value="all">Tous les logs</option>
                        <option value="error">Erreurs uniquement</option>
                        <option value="warning">Avertissements</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>

                <div id="logs_container" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.4;">
                    <div id="logs_content" style="white-space: pre-wrap;">
                        <!-- Logs will be loaded here -->
                        <em style="color: #666;">Cliquez sur "Actualiser Logs" pour charger les logs récents...</em>
                    </div>
                </div>
            </section>

            <section id="dev-tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Outils de Développement</h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <button type="button" id="reload_cache_btn" class="button button-secondary">
                        🔄 Recharger Cache
                    </button>
                    <button type="button" id="clear_temp_btn" class="button button-secondary">
                        🗑️ Vider Temp
                    </button>
                    <button type="button" id="test_routes_btn" class="button button-secondary">
                        🛣️ Tester Routes
                    </button>
                    <button type="button" id="export_diagnostic_btn" class="button button-secondary">
                        📊 Exporter Diagnostic
                    </button>
                    <button type="button" id="view_logs_btn" class="button button-secondary">
                        📋 Voir Logs
                    </button>
                    <button type="button" id="system_info_shortcut_btn" class="button button-secondary">
                        ℹ️ Info Système
                    </button>
                </div>
            </section>

            <section id="dev-notifications-test-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔔 Test du Système de Notifications</h3>
                <p style="color: #666; margin-bottom: 15px;">Testez le système de notifications toast avec différents types et logs détaillés en console.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <button type="button" id="test_notification_success" class="button button-secondary" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; font-weight: bold;">
                        ✅ Succès
                    </button>
                    <button type="button" id="test_notification_error" class="button button-secondary" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; font-weight: bold;">
                        ❌ Erreur
                    </button>
                    <button type="button" id="test_notification_warning" class="button button-secondary" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; font-weight: bold;">
                        ⚠️ Avertissement
                    </button>
                    <button type="button" id="test_notification_info" class="button button-secondary" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; font-weight: bold;">
                        ℹ️ Info
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <button type="button" id="test_notification_all" class="button button-primary" style="font-weight: bold;">
                        🎯 Tester Tous les Types
                    </button>
                    <button type="button" id="test_notification_clear" class="button button-secondary" style="background: #6c757d; color: white; border: none;">
                        🗑️ Vider Toutes
                    </button>
                    <button type="button" id="test_notification_stats" class="button button-secondary" style="background: #17a2b8; color: white; border: none;">
                        📊 Statistiques
                    </button>
                </div>

                <div id="notification_test_logs" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.4; margin-top: 15px;">
                    <div style="color: #666; font-style: italic;">
                        Logs des tests de notifications apparaîtront ici...<br>
                        Ouvrez la console développeur (F12) pour voir les logs détaillés.
                    </div>
                </div>

                <div style="background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px; padding: 15px; margin-top: 15px;">
                    <h4 style="margin-top: 0; color: #2e7d32;">💡 Instructions :</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #2e7d32;">
                        <li>Cliquez sur les boutons pour tester chaque type de notification</li>
                        <li>Les notifications apparaissent en haut à droite par défaut</li>
                        <li>Elles se ferment automatiquement après 5 secondes</li>
                        <li>Survolez-les pour arrêter le timer d'auto-fermeture</li>
                        <li>Cliquez sur × pour les fermer manuellement</li>
                        <li>Les logs détaillés sont affichés en console (F12)</li>
                    </ul>
                </div>
            </section>

            <section id="dev-shortcuts-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Raccourcis Clavier Développeur</h3>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Raccourci</th>
                            <th style="width: 70%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd></td>
                            <td>Basculer le mode debug JavaScript</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>L</kbd></td>
                            <td>Ouvrir la console développeur du navigateur</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>R</kbd></td>
                            <td>Recharger la page (hard refresh)</td>
                        </tr>
                        <tr>
                            <td><kbd>F12</kbd></td>
                            <td>Ouvrir les outils développeur</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>U</kbd></td>
                            <td>Voir le code source de la page</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>I</kbd></td>
                            <td>Inspecter l'élément sous le curseur</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section id="dev-todo-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
             <!-- Accordéon pour la section À Faire - Développement -->
                <div class="accordion-container" style="margin-bottom: 20px;">
                    <button type="button" class="accordion-toggle" id="dev-todo-toggle" style="width: 100%; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: left; cursor: pointer; font-size: 16px; font-weight: bold; color: #495057; transition: all 0.3s ease;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            📋 À Faire - Développement
                            <span class="accordion-icon" style="margin-left: auto; transition: transform 0.3s ease;">▶️</span>
                        </span>
                    </button>
                    <div class="accordion-content" id="dev-todo-content" style="display: none; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px; padding: 20px; background: white;">
                    </div>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #856404; margin-top: 0;">🚧 Système de Cache - RÉIMPLÉMENTATION REQUISE</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #dc3545; font-weight: bold;">SUPPRIMÉ DU CODE ACTUEL</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #856404;">📂 Fichiers concernés :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><code>src/Cache/</code> - Répertoire complet du système de cache</li>
                                <li><code>src/Managers/PDF_Builder_Cache_Manager.php</code></li>
                                <li><code>src/Managers/PDF_Builder_Extended_Cache_Manager.php</code></li>
                                <li><code>templates/admin/settings-page.php</code> - Section système (lignes ~2133, ~276, ~349)</li>
                                <li><code>pdf-builder-pro.php</code> - Référence ligne 671</li>
                            </ul>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #1976d2;">🎯 Actions requises :</h5>
                            <ol style="margin-bottom: 0;">
                                <li><strong>Analyser les besoins :</strong> Déterminer si un système de cache est nécessaire pour les performances</li>
                                <li><strong>Concevoir l'architecture :</strong> Cache fichier/DB/transient selon les besoins</li>
                                <li><strong>Réimplémenter le Cache Manager :</strong> Classe principale de gestion du cache</li>
                                <li><strong>Réimplémenter l'Extended Cache Manager :</strong> Gestion avancée avec DB et nettoyage</li>
                                <li><strong>Mettre à jour l'interface :</strong> Section système avec contrôles fonctionnels</li>
                                <li><strong>Tester l'intégration :</strong> Vérifier que le cache améliore les performances sans bugs</li>
                            </ol>
                        </div>

                        <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0c5460;">⚠️ Impact actuel :</h5>
                            <ul style="margin-bottom: 0;">
                                <li>Les toggles de cache dans l'onglet Système ne font rien</li>
                                <li>Pas de cache des aperçus PDF (impact performance)</li>
                                <li>Options de cache sauvegardées mais non utilisées</li>
                                <li>Code de cache présent mais non chargé</li>
                            </ul>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ffc107; font-weight: bold;">MOYENNE</span> - Fonctionnalité non critique pour l'instant</p>
                    </div>

                    <div style="background: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #2e7d32; margin-top: 0;">📤 Carte "Export & Qualité" - EN ATTENTE</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - SYSTÈME D'APERÇU</span></p>

                        <div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">📋 Contexte :</h5>
                            <p style="margin-bottom: 0;">Cette carte devra être créée dans les paramètres canvas une fois que le système d'aperçu PDF sera complètement implémenté et fonctionnel.</p>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #1976d2;">🎯 Fonctionnalités à implémenter :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Formats d'export :</strong> PDF, PNG, JPG avec aperçu des formats disponibles</li>
                                <li><strong>Contrôle qualité :</strong> Slider/barre de qualité d'image (1-100%)</li>
                                <li><strong>Options de compression :</strong> Toggle pour compression intelligente</li>
                                <li><strong>Métadonnées :</strong> Option pour inclure/exclure les métadonnées</li>
                                <li><strong>Prévisualisation :</strong> Aperçu miniature du résultat d'export</li>
                                <li><strong>Taille estimée :</strong> Calcul automatique de la taille du fichier</li>
                            </ul>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">⏳ Condition préalable :</h5>
                            <p style="margin-bottom: 0;"><strong>Système d'aperçu PDF opérationnel requis avant de pouvoir créer cette carte.</strong> La carte aura besoin de pouvoir générer des aperçus des exports pour montrer à l'utilisateur le résultat avant l'export réel.</p>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ff9800; font-weight: bold;">ÉLEVÉE</span> - Fonctionnalité importante pour l'expérience utilisateur</p>
                        <p style="margin-top: 5px;"><strong>Dépend de :</strong> <span style="color: #2196f3; font-weight: bold;">Système d'aperçu PDF</span></p>
                    </div>

                    <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #0d47a1; margin-top: 0;">🔧 Corrections Mineures v1.1.0</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - FINALISATION</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0d47a1;">📋 Corrections identifiées :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Système d'Aperçu PDF/PNG/JPG :</strong> Implémentation complète du système d'aperçu multi-format</li>
                                <li><strong>Activation Onboarding Production :</strong> Vérifier que l'onboarding s'active en production (WP_DEBUG=false)</li>
                                <li><strong>Nettoyage Styles Temporaires :</strong> Supprimer/déplacer les styles inline temporaires vers debug.css</li>
                                <li><strong>Tests d'Intégration Complets :</strong> Validation Canvas/Metabox avec données réelles</li>
                                <li><strong>Tests Performance & Charge :</strong> Validation < 2s génération, cache hit ratio > 80%</li>
                                <li><strong>Tests Sécurité & Robustesse :</strong> Audit complet et gestion d'erreurs</li>
                                <li><strong>Tests Utilisateur & UX :</strong> Validation expérience utilisateur finale</li>
                                <li><strong>Tests Compatibilité Navigateurs :</strong> Chrome, Firefox, Safari, Edge</li>
                            </ul>
                        </div>

                        <div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">🎯 Actions requises :</h5>
                            <ol style="margin-bottom: 0;">
                                <li><strong>Implémenter système d'aperçu :</strong> PDF/PNG/JPG avec conversion côté serveur</li>
                                <li><strong>Vérifier l'onboarding :</strong> Tester activation en mode production</li>
                                <li><strong>Audit CSS :</strong> Identifier et nettoyer les styles temporaires</li>
                                <li><strong>Tests d'intégration :</strong> Validation transitions Canvas ↔ Metabox</li>
                                <li><strong>Tests performance :</strong> Mesure temps génération et cache efficiency</li>
                                <li><strong>Tests sécurité :</strong> Audit permissions, sanitisation, rate limiting</li>
                                <li><strong>Tests UX :</strong> Workflows intuitifs, gestion erreurs user-friendly</li>
                                <li><strong>Tests compatibilité :</strong> Validation cross-browser et responsive</li>
                            </ol>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">⚠️ Impact sur la release :</h5>
                            <p style="margin-bottom: 0;">Ces corrections sont critiques pour atteindre la version 1.1.0 stable. Le système d'aperçu PDF/PNG/JPG est essentiel pour l'expérience utilisateur, permettant aux clients de prévisualiser leurs documents avant génération finale.</p>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #dc3545; font-weight: bold;">CRITIQUE</span> - Bloque la release v1.1.0</p>
                        <p style="margin-top: 5px;"><strong>Échéance :</strong> <span style="color: #dc3545; font-weight: bold;">Janvier 2026</span></p>
                    </div>

                    <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #0d47a1; margin-top: 0;">🖼️ Menu "Galerie" - À CACHER EN PRODUCTION</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #2196f3; font-weight: bold;">NOTE POUR RELEASE FINALE</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0d47a1;">📍 Localisation :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Fichier :</strong> <code>templates/admin/predefined-templates-manager.php</code></li>
                                <li><strong>Ligne :</strong> 46 - Fonction <code>add_submenu_page()</code></li>
                                <li><strong>Slug :</strong> <code>pdf-builder-predefined-templates</code></li>
                                <li><strong>Label :</strong> <code>🖼️ Galerie</code></li>
                            </ul>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">🎯 Action requise :</h5>
                            <p style="margin-bottom: 0;"><strong>Cacher le menu "Galerie" du menu admin WordPress</strong> car il est exclusivement destiné au développeur pour gérer les modèles prédéfinis du système.</p>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">💡 Solution proposée :</h5>
                            <ul style="margin-bottom: 0;">
                                <li>Ajouter une condition <code>if (WP_DEBUG)</code> autour de l'appel <code>add_submenu_page()</code></li>
                                <li>Ou utiliser un filtre/capability personnalisé pour les développeurs uniquement</li>
                                <li>Ou commenter/supprimer complètement la ligne</li>
                            </ul>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ff9800; font-weight: bold;">FAIBLE</span> - Amélioration UX pour utilisateurs finaux</p>
                        <p style="margin-top: 5px;"><strong>Action :</strong> <span style="color: #2196f3; font-weight: bold;">À FAIRE AVANT RELEASE FINALE</span></p>
                    </div>

                </div>
            </section>

            <section id="dev-console-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Console Code</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="test_code">Code Test</label></th>
                        <td>
                            <textarea id="test_code" style="width: 100%; height: 150px; font-family: monospace; padding: 10px;"></textarea>
                            <p class="description">Zone d'essai pour du code JavaScript (exécution côté client)</p>
                            <div style="margin-top: 10px;">
                                <button type="button" id="execute_code_btn" class="button button-secondary">▶️ Exécuter Code JS</button>
                                <button type="button" id="clear_console_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Console</button>
                                <span id="code_result" style="margin-left: 20px; font-weight: bold;"></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-hooks-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <!-- Tableau de références des hooks disponibles -->
                <h3 class="section-title">Hooks Disponibles</h3>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Hook</th>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 25%;">Typage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>pdf_builder_before_generate</code></td>
                            <td>Avant la génération PDF</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_after_generate</code></td>
                            <td>Après la génération PDF réussie</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_template_data</code></td>
                            <td>Filtre les données de template</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_element_render</code></td>
                            <td>Rendu d'un élément du canvas</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_security_check</code></td>
                            <td>Vérifications de sécurité personnalisées</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_before_save</code></td>
                            <td>Avant sauvegarde des paramètres</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Section Monitoring des Performances -->
            <h3 class="section-title">📊 Monitoring des Performances</h3>
            <p style="color: #666; margin-bottom: 15px;">Outils pour mesurer et analyser les performances du système.</p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="performance_monitoring">Monitoring Performance</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="performance_monitoring" name="performance_monitoring" value="1" <?php echo isset($settings['pdf_builder_performance_monitoring']) && $settings['pdf_builder_performance_monitoring'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le monitoring des performances</span>
                        </div>
                        <div class="toggle-description">Active la collecte de métriques de performance (FPS, mémoire, etc.)</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Test FPS Canvas</th>
                    <td>
                        <button type="button" id="test_fps_btn" class="button button-secondary" style="background-color: #17a2b8; border-color: #17a2b8; color: white; font-weight: bold; padding: 10px 15px;">
                            🎯 Tester FPS
                        </button>
                        <span id="fps_test_result" style="margin-left: 10px; font-weight: bold;"></span>
                        <div id="fps_test_details" style="display: none; margin-top: 10px; padding: 15px; background: #e7f5ff; border-left: 4px solid #17a2b8; border-radius: 4px;">
                            <strong>Instructions :</strong><br>
                            1. Ouvrez l'éditeur PDF dans un nouvel onglet<br>
                            2. Cliquez sur "Tester FPS"<br>
                            3. Observez le FPS affiché (devrait être proche de la cible configurée : <?php echo intval(pdf_builder_safe_get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS)<br>
                            <strong>💡 Conseil :</strong> Utilisez les DevTools (F12 → Performance) pour un monitoring avancé
                        </div>
                        <p class="description">Teste la fluidité du canvas et vérifie que le FPS cible est atteint</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Informations Système</th>
                    <td>
                        <button type="button" id="system_info_btn" class="button button-secondary" style="background-color: #28a745; border-color: #28a745; color: white; font-weight: bold; padding: 10px 15px;">
                            ℹ️ Infos Système
                        </button>
                        <div id="system_info_result" style="display: none; margin-top: 10px; padding: 15px; background: #f8fff8; border-left: 4px solid #28a745; border-radius: 4px;">
                            <strong>Configuration actuelle :</strong><br>
                            • Mémoire PHP : <?php echo ini_get('memory_limit'); ?><br>
                            • Timeout max : <?php echo ini_get('max_execution_time'); ?>s<br>
                            • Upload max : <?php echo ini_get('upload_max_filesize'); ?><br>
                            • Post max : <?php echo ini_get('post_max_size'); ?><br>
                            <strong>Paramètres Performance :</strong><br>
                            • FPS cible : <?php echo intval(pdf_builder_safe_get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS<br>
                            • Mémoire JS : <?php echo intval(pdf_builder_safe_get_option('pdf_builder_canvas_memory_limit_js', 256)); ?> MB<br>
                            • Mémoire PHP : <?php echo intval(pdf_builder_safe_get_option('pdf_builder_canvas_memory_limit_php', 256)); ?> MB<br>
                            • Lazy Loading Éditeur : <?php echo pdf_builder_safe_get_option('pdf_builder_canvas_lazy_loading_editor', '1') == '1' ? 'Activé' : 'Désactivé'; ?><br>
                            • Lazy Loading Plugin : <?php echo pdf_builder_safe_get_option('pdf_builder_canvas_lazy_loading_plugin', '1') == '1' ? 'Activé' : 'Désactivé'; ?>
                        </div>
                        <p class="description">Affiche les informations système et configuration actuelle</p>
                    </td>
                </tr>
            </table>

            <!-- Avertissement production -->
            <div style="background: #ffebee; border-left: 4px solid #d32f2f; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #c62828;">🚨 Avertissement Production</h3>
                <ul style="margin: 0; padding-left: 20px; color: #c62828;">
                    <li>❌ Ne jamais laisser le mode développeur ACTIVÉ en production</li>
                    <li>❌ Ne jamais afficher les logs détaillés aux utilisateurs</li>
                    <li>❌ Désactivez le profiling et les hooks de debug après débogage</li>
                    <li>❌ N'exécutez pas de code arbitraire en production</li>
                    <li>✓ Utilisez des mots de passe forts pour protéger les outils dev</li>
                </ul>
            </div>

            <!-- Conseils développement -->
            <div style="background: #f3e5f5; border-left: 4px solid #7b1fa2; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #4a148c;">💻 Conseils Développement</h3>
                <ul style="margin: 0; padding-left: 20px; color: #4a148c;">
                    <li>Activez Debug JavaScript pour déboguer les interactions client</li>
                    <li>Utilisez Debug AJAX pour vérifier les requêtes serveur</li>
                    <li>Consultez Debug Performance pour optimiser les opérations lentes</li>
                    <li>Lisez les logs détaillés (niveau 4) pour comprendre le flux</li>
                    <li>Testez avec les différents niveaux de log</li>
                </ul>
            </div>

            <!-- Bouton de sauvegarde spécifique à l'onglet développeur -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #495057;">💾 Sauvegarde des Paramètres Développeur</h3>
                <p style="margin-bottom: 15px; color: #666;">Sauvegardez uniquement les paramètres de cette section développeur.</p>
                <button type="submit" class="button button-primary" style="background: #007cba; border-color: #007cba; color: white; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer;">
                    💾 Sauvegarder les Paramètres Développeur
                </button>
            </div>

         </form>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
</div>
</div>

<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>
</div>

<!-- JavaScript moved to settings-parts/settings-main.php to avoid conflicts -->
