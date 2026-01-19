<?php
    // Log PHP pour vérifier que le fichier s'exécute
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] settings-main.php LOADED at line ' . __LINE__); }
    echo '<!-- PHP DEBUG: settings-main.php loaded at ' . current_time('H:i:s') . ' -->';
?>

<?php
    /**
     * Page principale des paramètres PDF Builder Pro
     *
     * Interface d'administration principale avec système d'onglets
     * pour la configuration complète du générateur de PDF.
     *
     * @version 2.1.0
     * @since 2025-12-08
     */

    // Sécurité WordPress
    if (!defined('ABSPATH')) {
        exit('Direct access forbidden');
    }

    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
    }

    // Récupération des paramètres généraux
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    $current_user = wp_get_current_user();

    // LOG pour déboguer la soumission du formulaire
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === SETTINGS PAGE LOADED ==='); }
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Settings page loaded - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']); }
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Current tab: ' . $current_tab); }
    
    // Gestion des onglets via URL
    $current_tab = sanitize_text_field($_GET['tab'] ?? 'general');
    $valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
    if (!in_array($current_tab, $valid_tabs)) {
        $current_tab = 'general';
    }

    // Informations de diagnostic pour le débogage (uniquement en mode debug)
    $debug_info = defined('WP_DEBUG') && WP_DEBUG ? [
        'version' => PDF_BUILDER_PRO_VERSION ?? 'unknown',
        'php' => PHP_VERSION,
        'wordpress' => get_bloginfo('version'),
        'user' => $current_user->display_name,
        'time' => current_time('mysql')
    ] : null;

?>

<div class="wrap">
    <style>
    .hidden-element {
        display: none !important;
    }
    </style>

    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    <p><?php _e('Configurez les paramètres de génération de vos documents PDF.', 'pdf-builder-pro'); ?></p>

    <!-- DEBUG MESSAGE -->
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 4px;">
        <strong>🔍 DEBUG:</strong> Page chargée à <?php echo current_time('H:i:s'); ?> - Tab: <?php echo $current_tab; ?> - Settings count: <?php echo count($settings); ?>
    </div>

    <form method="post" action="options.php">
        <?php 
        if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] About to call settings_fields for pdf_builder_settings'); }
        settings_fields('pdf_builder_settings'); 
        if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] settings_fields called'); }
        ?>

        <!-- Navigation par onglets moderne -->
    <h2 class="nav-tab-wrapper">
        <div class="tabs-container">
            <a href="?page=pdf-builder-settings&tab=general" class="nav-tab<?php echo $current_tab === 'general' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=licence" class="nav-tab<?php echo $current_tab === 'licence' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">🔑</span>
                <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=systeme" class="nav-tab<?php echo $current_tab === 'systeme' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">🖥️</span>
                <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=securite" class="nav-tab<?php echo $current_tab === 'securite' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">🔒</span>
                <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=pdf" class="nav-tab<?php echo $current_tab === 'pdf' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">📄</span>
                <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=contenu" class="nav-tab<?php echo $current_tab === 'contenu' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">🎨</span>
                <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=templates" class="nav-tab<?php echo $current_tab === 'templates' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">📋</span>
                <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
            </a>

            <a href="?page=pdf-builder-settings&tab=developpeur" class="nav-tab<?php echo $current_tab === 'developpeur' ? ' nav-tab-active' : ''; ?>">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
            </a>
        </div>
    </h2>

    <!-- Canvas Cards - Always present in DOM for modal functionality -->
    <div id="canvas-cards-container" style="<?php echo $current_tab === 'contenu' ? '' : 'display: none;'; ?>">
        <?php
        // Include canvas cards from settings-contenu.php
        // This ensures the buttons are always in DOM for JavaScript to work
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Fonction helper pour récupérer les valeurs Canvas depuis les options individuelles
        function get_canvas_option_main($key, $default = '') {
            $option_key = 'pdf_builder_' . $key;
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

            if ($value === null) {
                $value = $default;
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] PAGE_LOAD - {$key}: OPTION_NOT_FOUND - using default '{$default}' - KEY: {$option_key}"); }
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] PAGE_LOAD - {$key}: FOUND_DB_VALUE '{$value}' - KEY: {$option_key}"); }
            }

            return $value;
        }
        ?>

        <!-- Grille de cartes Canvas -->
        <div class="canvas-settings-grid">
            <!-- Carte Affichage (fusion Dimensions + Apparence) -->
            <article class="canvas-card" data-category="affichage">
                <header class="canvas-card-header">
                    <div class="canvas-card-header-left">
                        <span class="canvas-card-icon">🎨</span>
                    </div>
                    <h4>Affichage & Dimensions</h4>
                </header>
                <main class="canvas-card-content">
                    <p>Configurez les dimensions, le format, les couleurs et l'apparence générale du canvas.</p>
                </main>
                <aside class="canvas-card-preview">
                    <div class="preview-format">
                        <div >
                            <span id="card-canvas-width"><?php echo esc_html(get_canvas_option_main('canvas_width', '794')); ?></span>×
                            <span id="card-canvas-height"><?php echo esc_html(get_canvas_option_main('canvas_height', '1123')); ?></span>px
                        </div>
                        <span class="preview-size" id="card-canvas-dpi">
                            <?php
                            $width = intval(get_canvas_option_main('canvas_width', '794'));
                            $height = intval(get_canvas_option_main('canvas_height', '1123'));
                            $dpi = intval(get_canvas_option_main('canvas_dpi', '96'));
                            $format = get_canvas_option_main('canvas_format', 'A4');
                            $dpi = max(1, $dpi);
                            $widthMM = round(($width / $dpi) * 25.4, 1);
                            $heightMM = round(($height / $dpi) * 25.4, 1);
                            echo esc_html("{$dpi} DPI - {$format} ({$widthMM}×{$heightMM}mm)");
                            ?>
                        </span>
                    </div>
                </aside>
                <footer class="canvas-card-actions">
                    <button type="button" class="canvas-configure-btn">
                        <span>⚙️</span> Configurer
                    </button>
                </footer>
            </article>

            <!-- Carte Navigation (fusion Grille + Zoom) -->
            <article class="canvas-card" data-category="navigation">
                <header class="canvas-card-header">
                    <div class="canvas-card-header-left">
                        <span class="canvas-card-icon">🧭</span>
                    </div>
                    <h4>Navigation & Zoom</h4>
                </header>
                <main class="canvas-card-content">
                    <p>Configurez la grille, les guides, le zoom et les options de navigation du canvas.</p>
                </main>
                <aside class="canvas-card-preview">
                    <div id="card-grid-preview" class="grid-preview-container">
                        <div class="grid-canvas">
                            <div class="grid-lines">
                                <div class="grid-line horizontal"></div>
                                <div class="grid-line horizontal"></div>
                                <div class="grid-line horizontal"></div>
                                <div class="grid-line vertical"></div>
                                <div class="grid-line vertical"></div>
                                <div class="grid-line vertical"></div>
                            </div>
                            <div class="grid-dots">
                                <div class="grid-dot"></div><div class="grid-dot"></div><div class="grid-dot"></div>
                                <div class="grid-dot"></div><div class="grid-dot"></div><div class="grid-dot"></div>
                                <div class="grid-dot"></div><div class="grid-dot"></div><div class="grid-dot"></div>
                            </div>
                            <div class="guide-lines">
                                <div class="guide-line horizontal active"></div>
                                <div class="guide-line vertical active"></div>
                            </div>
                            <div class="preview-element">
                                <div class="element-box"></div>
                            </div>
                        </div>
                        <div class="grid-legend">
                            <span class="legend-item">📐 Grille</span>
                            <span class="legend-item">📏 Guides</span>
                            <span class="legend-item">📦 Élément</span>
                            <span class="snap-indicator">🔗 Snap activé</span>
                        </div>
                    </div>
                </aside>
                <footer class="canvas-card-actions">
                    <button type="button" class="canvas-configure-btn">
                        <span>📏</span> Configurer
                    </button>
                </footer>
            </article>

            <!-- Carte Comportement (fusion Interactions + Export) -->
            <article class="canvas-card" data-category="comportement">
                <header class="canvas-card-header">
                    <div class="canvas-card-header-left">
                        <span class="canvas-card-icon">🎯</span>
                    </div>
                    <h4>Comportement & Export</h4>
                </header>
                <main class="canvas-card-content">
                    <p>Configurez les interactions, la sélection, les raccourcis et les options d'export du canvas.</p>
                </main>
                <aside class="canvas-card-preview">
                    <div class="interactions-preview-container">
                        <div class="mini-canvas">
                            <div class="mini-canvas-grid"></div>
                            <div class="mini-element text-element" style="top: 15px; left: 20px; width: 35px; height: 18px;">
                                <div class="mini-element-content">T</div>
                            </div>
                            <div class="mini-element shape-element selected" style="top: 40px; left: 15px; width: 32px; height: 22px;">
                                <div class="mini-element-content">□</div>
                                <div class="mini-handle nw"></div><div class="mini-handle ne"></div>
                                <div class="mini-handle sw"></div><div class="mini-handle se"></div>
                                <div class="mini-handle rotation" style="top: -6px; left: 50%; transform: translateX(-50%);"></div>
                            </div>
                            <div class="mini-element image-element" style="top: 18px; left: 75px; width: 28px; height: 28px;">
                                <div class="mini-element-content">🖼</div>
                            </div>
                            <div class="selection-rectangle" style="top: 10px; left: 10px; width: 55px; height: 35px;"></div>
                            <div class="mouse-cursor" style="top: 50px; left: 95px;">
                                <div class="cursor-icon">👆</div>
                            </div>
                            <div class="zoom-indicator">
                                <span class="zoom-level">100%</span>
                            </div>
                            <div class="performance-indicator">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 85%"></div>
                                </div>
                                <span class="performance-text">85%</span>
                            </div>
                        </div>
                        <div class="interactions-controls">
                            <div class="selection-mode-indicator">
                                <span class="mode-icon active">▭</span>
                                <span class="mode-icon">🪢</span>
                                <span class="mode-icon">👆</span>
                            </div>
                            <div class="interaction-status">
                                <span class="status-indicator selecting">Sélection active</span>
                                <div class="keyboard-status">
                                    <span class="keyboard-icon">⌨️</span>
                                </div>
                            </div>
                        </div>
                        <div class="interaction-progress">
                            <div class="progress-label">Fluidité</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 92%"></div>
                            </div>
                            <div class="progress-value">92%</div>
                        </div>
                    </div>
                </aside>
                <footer class="canvas-card-actions">
                    <button type="button" class="canvas-configure-btn">
                        <span>🎯</span> Configurer
                    </button>
                </footer>
            </article>

            <!-- Carte Système (fusion Performance + Debug) -->
            <article class="canvas-card" data-category="systeme">
                <header class="canvas-card-header">
                    <div class="canvas-card-header-left">
                        <span class="canvas-card-icon">⚙️</span>
                    </div>
                    <h4>Performance & Système</h4>
                </header>
                <main class="canvas-card-content">
                    <p>Optimisez les performances, la mémoire et configurez les options de debug et monitoring.</p>
                </main>
                <aside class="canvas-card-preview">
                    <div class="performance-preview-container">
                        <div class="performance-metrics">
                            <div class="metric-item">
                                <span class="metric-label">FPS</span>
                                <span id="card-perf-preview" class="metric-value"><?php echo esc_html(get_canvas_option_main('canvas_fps_target', '60')); ?></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">RAM JS</span>
                                <span class="metric-value"><?php echo esc_html(get_canvas_option_main('canvas_memory_limit_js', '50')); ?>MB</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">RAM PHP</span>
                                <span class="metric-value"><?php echo esc_html(get_canvas_option_main('canvas_memory_limit_php', '128')); ?>MB</span>
                            </div>
                        </div>
                        <div class="performance-status">
                            <div class="status-indicator">
                                <span class="status-dot"></span>
                                <span class="status-text">Lazy Loading</span>
                            </div>
                        </div>
                    </div>
                </aside>
                <footer class="canvas-card-actions">
                    <button type="button" class="canvas-configure-btn">
                        <span>⚡</span> Configurer
                    </button>
                </footer>
            </article>
        </div>
    </div>

    <!-- contenu des onglets moderne -->
    <div class="settings-content-wrapper">
        <?php
        switch ($current_tab) {
            case 'general':
                include __DIR__ . '/settings-general.php';
                break;

            case 'licence':
                do_settings_sections('pdf_builder_licence');
                break;

            case 'systeme':
                include __DIR__ . '/settings-systeme.php';
                break;

            case 'securite':
                include __DIR__ . '/settings-securite.php';
                break;

            case 'pdf':
                include __DIR__ . '/settings-pdf.php';
                break;

            case 'contenu':
                include __DIR__ . '/settings-contenu.php';
                break;

            case 'templates':
                include __DIR__ . '/settings-templates.php';
                break;

            case 'developpeur':
                include __DIR__ . '/settings-developpeur.php';
                break;

            default:
                echo '<p>' . __('Onglet non valide.', 'pdf-builder-pro') . '</p>';
                break;
        }
        ?>

        <?php submit_button(); ?>

        <!-- Bouton flottant de sauvegarde -->
        <div id="pdf-builder-save-floating" class="pdf-builder-save-floating-container">
            <button type="submit" name="submit" id="pdf-builder-save-floating-btn" class="pdf-builder-floating-save">
                💾 Enregistrer
            </button>
        </div>
    </div>
    </form>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

    <!-- Inclusion des modales Canvas (toujours disponibles peu importe l'onglet) -->
    <?php require_once __DIR__ . '/settings-modals.php'; ?>

    <!-- Le script canvas-settings.js est maintenant chargé via settings-loader.php -->

</div> <!-- Fin du .wrap -->

</body>
</html>
