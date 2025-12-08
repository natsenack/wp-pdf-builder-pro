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
$settings = get_option('pdf_builder_settings', array());
$current_user = wp_get_current_user();

// Informations de diagnostic pour le débogage (uniquement en mode debug)
$debug_info = defined('WP_DEBUG') && WP_DEBUG ? [
    'version' => PDF_BUILDER_PRO_VERSION ?? 'unknown',
    'php' => PHP_VERSION,
    'wordpress' => get_bloginfo('version'),
    'user' => $current_user->display_name,
    'time' => current_time('mysql')
] : null;

?>
<div class="wrap pdf-builder-settings-page" id="pdf-builder-settings-wrapper">
    <!-- En-tête simple -->
    <div class="settings-header-simple">
        <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
        <p class="description"><?php _e('Configurez les paramètres de génération de vos documents PDF.', 'pdf-builder-pro'); ?></p>
    </div>

    <!-- Navigation par onglets -->
    <nav class="settings-navigation" role="navigation" aria-label="<?php esc_attr_e('Navigation des paramètres', 'pdf-builder-pro'); ?>">
        <div class="nav-tab-wrapper" id="pdf-builder-tabs" role="tablist">
            <?php
                // Définition des onglets disponibles
                $tabs = [
                    'general' => [
                        'title' => __('Général', 'pdf-builder-pro'),
                        'icon' => 'dashicons-admin-settings',
                        'description' => __('Informations entreprise et configuration de base', 'pdf-builder-pro')
                    ],
                    'licence' => [
                        'title' => __('Licence', 'pdf-builder-pro'),
                        'icon' => 'dashicons-admin-network',
                        'description' => __('Gestion de la licence et activation', 'pdf-builder-pro')
                    ],
                    'systeme' => [
                        'title' => __('Système', 'pdf-builder-pro'),
                        'icon' => 'dashicons-admin-tools',
                        'description' => __('Configuration système et compatibilité', 'pdf-builder-pro')
                    ],
                    'securite' => [
                        'title' => __('Sécurité', 'pdf-builder-pro'),
                        'icon' => 'dashicons-shield',
                        'description' => __('Paramètres de sécurité et permissions', 'pdf-builder-pro')
                    ],
                    'pdf' => [
                        'title' => __('Configuration PDF', 'pdf-builder-pro'),
                        'icon' => 'dashicons-media-document',
                        'description' => __('Paramètres de génération PDF', 'pdf-builder-pro')
                    ],
                    'contenu' => [
                        'title' => __('Canvas & Design', 'pdf-builder-pro'),
                        'icon' => 'dashicons-art',
                        'description' => __('Configuration du canvas et design', 'pdf-builder-pro')
                    ],
                    'templates' => [
                        'title' => __('Templates', 'pdf-builder-pro'),
                        'icon' => 'dashicons-layout',
                        'description' => __('Gestion des templates par statut', 'pdf-builder-pro')
                    ],
                    'developpeur' => [
                        'title' => __('Développeur', 'pdf-builder-pro'),
                        'icon' => 'dashicons-editor-code',
                        'description' => __('Outils et options développeur', 'pdf-builder-pro')
                    ]
                ];

                // Génération des onglets
                foreach ($tabs as $tab_id => $tab_config):
                    $tab_title = $tab_config['title'];
                    $tab_icon = $tab_config['icon'];
                    $tab_desc = $tab_config['description'];
            ?>
            <button
                id="tab-<?php echo esc_attr($tab_id); ?>"
                class="nav-tab"
                data-tab="<?php echo esc_attr($tab_id); ?>"
                role="tab"
                aria-selected="false"
                aria-controls="<?php echo esc_attr($tab_id); ?>"
                title="<?php echo esc_attr($tab_desc); ?>"
                type="button">
                <span class="tab-icon <?php echo esc_attr($tab_icon); ?>" aria-hidden="true"></span>
                <span class="tab-label"><?php echo esc_html($tab_title); ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Indicateur de chargement pour les onglets -->
        <div class="tab-loading-indicator" id="tab-loading" style="display: none;">
            <span class="spinner is-active"></span>
            <span class="loading-text"><?php _e('Chargement...', 'pdf-builder-pro'); ?></span>
        </div>
    </nav>

    <!-- Contenu des onglets -->
    <main class="settings-content" id="pdf-builder-tab-content" role="main" aria-live="polite">
        <?php
            // Chargement des fichiers d'onglets avec gestion d'erreurs
            $tab_files = [
                'general' => 'settings-general.php',
                'licence' => 'settings-licence.php',
                'systeme' => 'settings-systeme.php',
                'securite' => 'settings-securite.php',
                'pdf' => 'settings-pdf.php',
                'contenu' => 'settings-contenu.php',
                'templates' => 'settings-templates.php',
                'developpeur' => 'settings-developpeur.php'
            ];

            foreach ($tab_files as $tab_id => $file):
                $file_path = __DIR__ . '/' . $file;
        ?>
        <section
            id="<?php echo esc_attr($tab_id); ?>"
            class="settings-section tab-content"
            role="tabpanel"
            aria-labelledby="tab-<?php echo esc_attr($tab_id); ?>"
            style="display: none;">
            <?php
                if (file_exists($file_path)) {
                    try {
                        require_once $file_path;
                    } catch (Exception $e) {
                        echo '<div class="notice notice-error">';
                        echo '<p>' . sprintf(__('Erreur lors du chargement de l\'onglet %s: %s', 'pdf-builder-pro'), esc_html($tabs[$tab_id]['title']), esc_html($e->getMessage())) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="notice notice-warning">';
                    echo '<p>' . sprintf(__('Fichier manquant: %s', 'pdf-builder-pro'), esc_html($file)) . '</p>';
                    echo '</div>';
                }
            ?>
        </section>
        <?php endforeach; ?>
    </main>

    <!-- Bouton de sauvegarde flottant -->
    <div class="floating-save-button" id="pdf-builder-save-floating" role="region" aria-label="<?php esc_attr_e('Actions de sauvegarde', 'pdf-builder-pro'); ?>">
        <div class="save-button-container">
            <button
                type="button"
                id="pdf-builder-save-floating-btn"
                class="button button-primary button-hero save-button"
                aria-describedby="save-status-text">
                <span class="dashicons dashicons-yes" aria-hidden="true"></span>
                <span class="button-text"><?php _e('Enregistrer les modifications', 'pdf-builder-pro'); ?></span>
            </button>

            <div class="save-status" id="save-status-indicator" role="status" aria-live="polite">
                <span id="save-status-text" class="status-text">
                    <?php _e('Prêt à enregistrer', 'pdf-builder-pro'); ?>
                </span>
                <div class="status-spinner spinner" style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Modales de configuration -->
    <?php
        $modals_file = __DIR__ . '/settings-modals.php';
        if (file_exists($modals_file)) {
            require_once $modals_file;
        }
    ?>

</div>

<style>
    /* Styles principaux pour la page des paramètres */
    .pdf-builder-settings-page {
        margin: 20px 0 0 0;
        background: #fff;
        min-height: calc(100vh - 100px);
    }

    /* Header simple */
    .settings-header-simple {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dcdcde;
    }

    .settings-header-simple h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2em;
        font-weight: 400;
        color: #1d2327;
    }

    .settings-header-simple .description {
        margin: 0;
        color: #646970;
        font-size: 1.1em;
    }

    /* Navigation par onglets */
    .settings-navigation {
        margin-bottom: 2rem;
    }

    .nav-tab-wrapper {
        background: #f8f9fa;
        border: 1px solid #dcdcde;
        border-radius: 8px;
        padding: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .nav-tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: transparent;
        border: none;
        border-radius: 6px;
        color: #646970;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
    }

    .nav-tab:hover {
        background: #e9ecef;
        color: #2271b1;
    }

    .nav-tab[aria-selected="true"] {
        background: #2271b1;
        color: white;
        box-shadow: 0 2px 4px rgba(34, 113, 177, 0.3);
    }

    .nav-tab .tab-icon {
        font-size: 1.1em;
        width: 1.1em;
        height: 1.1em;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-tab[aria-selected="true"] .tab-icon {
        color: #ffd700;
    }

    .tab-loading-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        color: #646970;
        font-style: italic;
    }

    /* Contenu */
    .settings-content {
        min-height: 400px;
    }

    .settings-section {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Bouton flottant */
    .floating-save-button {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
    }

    .save-button-container {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
        max-width: 300px;
    }

    .save-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        font-size: 1.1em;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(34, 113, 177, 0.3);
        transition: all 0.2s ease;
    }

    .save-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(34, 113, 177, 0.4);
    }

    .save-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #dcdcde;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-size: 0.9em;
        backdrop-filter: blur(10px);
    }

    .status-spinner {
        width: 16px;
        height: 16px;
    }

    /* Responsive */
    @media (max-width: 782px) {
        .settings-header {
            padding: 1.5rem;
            margin: -10px -10px 1.5rem -10px;
        }

        .header-content {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .header-main h1 {
            font-size: 1.5em;
        }

        .nav-tab-wrapper {
            flex-direction: column;
            gap: 0;
        }

        .nav-tab {
            justify-content: flex-start;
            padding: 1rem;
            border-radius: 0;
            border-bottom: 1px solid #e9ecef;
        }

        .nav-tab:last-child {
            border-bottom: none;
        }

        .floating-save-button {
            bottom: 1rem;
            right: 1rem;
        }

        .save-button-container {
            max-width: 250px;
        }

        .save-button {
            padding: 0.875rem 1.25rem;
            font-size: 1em;
        }
    }

    @media (max-width: 480px) {
        .header-actions {
            flex-direction: column;
        }

        .header-actions .button {
            width: 100%;
            text-align: center;
        }

        .save-button .button-text {
            display: none;
        }

        .save-button {
            padding: 1rem;
            min-width: auto;
        }
    }

    /* Accessibilité */
    @media (prefers-reduced-motion: reduce) {
        .settings-section,
        .save-button {
            animation: none;
        }

        .nav-tab,
        .save-button {
            transition: none;
        }
    }

    /* Focus visible */
    .nav-tab:focus-visible,
    .save-button:focus-visible {
        outline: 2px solid #2271b1;
        outline-offset: 2px;
    }

    /* High contrast mode */
    @media (prefers-contrast: high) {
        .settings-header {
            background: #000;
            color: #fff;
        }

        .nav-tab[aria-selected="true"] {
            background: #fff;
            color: #000;
            border: 2px solid #000;
        }
    }
</style>

<script>
    // Initialisation JavaScript pour la page des paramètres
    document.addEventListener('DOMContentLoaded', function() {
        const settingsPage = document.getElementById('pdf-builder-settings-wrapper');

        if (!settingsPage) {
            console.warn('[PDF Builder] Page des paramètres non trouvée');
            return;
        }

        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S pour sauvegarder
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const saveButton = document.getElementById('pdf-builder-save-floating-btn');
                if (saveButton) {
                    saveButton.click();
                }
            }

            // Échap pour fermer les modales
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal[style*="display: block"], .modal.show');
                openModals.forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Message de confirmation avant quitter si des modifications sont en cours
        let hasUnsavedChanges = false;

        // Surveiller les changements dans les formulaires
        const forms = settingsPage.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    hasUnsavedChanges = true;
                });
            });
        });

        // Avertissement avant de quitter
        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        console.log('[PDF Builder] Page des paramètres initialisée');
    });
</script>
