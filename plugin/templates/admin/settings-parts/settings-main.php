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

    // LOG pour déboguer la soumission du formulaire
    error_log('[PDF Builder] === SETTINGS PAGE LOADED ===');
    error_log('[PDF Builder] Settings page loaded - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
    error_log('[PDF Builder] Current tab: ' . $current_tab);
    
    // Injecter l'API JavaScript dans le head
    add_action('admin_head', function() {
        ?>
        <script>
        // PDFBuilderTabsAPI is now defined inline in the page
        console.log('PDFBuilderTabsAPI admin_head placeholder');
        </script>
        <?php
    });
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('[PDF Builder] POST data received: ' . print_r($_POST, true));
        
        // Vérifier spécifiquement les données templates
        if (isset($_POST['pdf_builder_settings'])) {
            $posted_settings = $_POST['pdf_builder_settings'];
            error_log('[PDF Builder] pdf_builder_settings received: ' . print_r($posted_settings, true));
            
            if (isset($posted_settings['pdf_builder_default_template'])) {
                error_log('[PDF Builder] Template par défaut POST: ' . $posted_settings['pdf_builder_default_template']);
            }
            if (isset($posted_settings['pdf_builder_template_library_enabled'])) {
                error_log('[PDF Builder] Bibliothèque templates POST: ' . $posted_settings['pdf_builder_template_library_enabled']);
            }
        }

        // Message visible de debug
        echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 4px; color: #155724;">';
        echo '<strong>✅ FORMULAIRE SOUMIS:</strong> ' . current_time('H:i:s') . '<br>';
        echo 'Méthode: ' . $_SERVER['REQUEST_METHOD'] . '<br>';
        if (isset($_POST['pdf_builder_settings'])) {
            echo 'Paramètres reçus: ' . count($_POST['pdf_builder_settings']) . '<br>';
            if (isset($_POST['pdf_builder_settings']['pdf_builder_default_template'])) {
                echo 'Template: ' . $_POST['pdf_builder_settings']['pdf_builder_default_template'] . '<br>';
            }
            if (isset($_POST['pdf_builder_settings']['pdf_builder_template_library_enabled'])) {
                echo 'Bibliothèque: ' . $_POST['pdf_builder_settings']['pdf_builder_template_library_enabled'] . '<br>';
            }
        }
        echo '</div>';
    }

    // Gestion des onglets via URL
    $current_tab = $_GET['tab'] ?? 'general';
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
    <!-- PDF Builder Tabs API - Inline definition for immediate availability -->
    <script>
    // PDF Builder Tabs API - Défini immédiatement pour éviter les erreurs
    (function() {
        window.PDFBuilderTabsAPI = {
            /**
             * Change d'onglet
             * @param {string} tabName - Nom de l'onglet (general, licence, systeme, securite, pdf, contenu, templates, developpeur)
             */
            switchToTab: function(tabName) {
                var tabLink = document.querySelector('a[href*="tab=' + tabName + '"]');
                if (tabLink) {
                    // Simuler un clic sur le lien de l'onglet
                    tabLink.click();
                } else {
                    // Fallback: changer l'URL
                    var currentUrl = window.location.href;
                    var newUrl = currentUrl.replace(/tab=[^&]*/, 'tab=' + tabName);
                    if (newUrl === currentUrl) {
                        // Si tab n'était pas dans l'URL, l'ajouter
                        newUrl = currentUrl + (currentUrl.indexOf('?') > -1 ? '&' : '?') + 'tab=' + tabName;
                    }
                    window.location.href = newUrl;
                }
            },

            /**
             * Bascule la section avancée dans l'onglet PDF
             */
            toggleAdvancedSection: function() {
                var advancedSection = document.getElementById('advanced-section');
                var toggleIcon = document.getElementById('advanced-toggle');

                if (advancedSection && toggleIcon) {
                    if (advancedSection.classList.contains('hidden-element')) {
                        // Afficher la section
                        advancedSection.classList.remove('hidden-element');
                        toggleIcon.textContent = '▲';
                    } else {
                        // Masquer la section
                        advancedSection.classList.add('hidden-element');
                        toggleIcon.textContent = '▼';
                    }
                }
            },

            /**
             * Réinitialise les paramètres des templates par statut
             */
            resetTemplatesStatus: function() {
                if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les templates par statut de commande ? Cette action ne peut pas être annulée.')) {
                    // Réinitialiser tous les selects
                    var selects = document.querySelectorAll('.template-select');
                    selects.forEach(function(select) {
                        select.value = '';
                        // Déclencher l'événement change pour mettre à jour les prévisualisations
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    // Afficher un message de succès
                    alert('Les paramètres des templates ont été réinitialisés.');
                }
            }
        };

        console.log('PDFBuilderTabsAPI defined successfully:', typeof window.PDFBuilderTabsAPI, window.PDFBuilderTabsAPI);
    })();
    </script>

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
        error_log('[PDF Builder] About to call settings_fields for pdf_builder_settings');
        settings_fields('pdf_builder_settings'); 
        error_log('[PDF Builder] settings_fields called');
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

</div> <!-- Fin du .wrap -->

