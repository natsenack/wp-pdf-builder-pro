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
        exit('Direct access not allowed');
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

    // === LOGIQUE DE SAUVEGARDE CENTRALISÉE ===
    
    // Enregistrer les paramètres principaux
    add_action('admin_init', function() {
        // Paramètre principal pour les settings
        \register_setting('pdf_builder_settings', 'pdf_builder_settings', array(
            'type' => 'array',
            'description' => 'Paramètres principaux PDF Builder Pro',
            'sanitize_callback' => function($input) {
                // Log détaillé pour déboguer la sauvegarde
                if (class_exists('PDF_Builder_Logger')) { 
                    PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input type: ' . gettype($input)); 
                }
                if (is_array($input)) {
                    if (class_exists('PDF_Builder_Logger')) { 
                        PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input count: ' . count($input)); 
                        PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input keys: ' . implode(', ', array_keys($input))); 
                    }
                    
                    // Log spécifique pour les paramètres templates
                    if (isset($input['pdf_builder_default_template'])) {
                        if (class_exists('PDF_Builder_Logger')) { 
                            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template par défaut: ' . $input['pdf_builder_default_template']); 
                        }
                    }
                    if (isset($input['pdf_builder_template_library_enabled'])) {
                        if (class_exists('PDF_Builder_Logger')) { 
                            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Bibliothèque templates: ' . $input['pdf_builder_template_library_enabled']); 
                        }
                    }
                } else {
                    if (class_exists('PDF_Builder_Logger')) { 
                        PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input is not array: ' . print_r($input, true)); 
                    }
                }
                
                // Retourner les données nettoyées
                return $input;
            }
        ));

        // Paramètres généraux
        \register_setting('pdf_builder_settings', 'pdf_builder_allowed_roles');
        \register_setting('pdf_builder_settings', 'pdf_builder_company_vat');
        \register_setting('pdf_builder_settings', 'pdf_builder_company_rcs');
        \register_setting('pdf_builder_settings', 'pdf_builder_company_siret');

        // Paramètres des templates par statut de commande
        \register_setting('pdf_builder_order_status_templates', 'pdf_builder_order_status_templates');

        // Paramètres de localisation
        \register_setting('pdf_builder_settings', 'pdf_builder_default_locale', [
            'type' => 'string',
            'description' => 'Locale par défaut',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        \register_setting('pdf_builder_settings', 'pdf_builder_rtl_support', [
            'type' => 'boolean',
            'description' => 'Support RTL',
            'sanitize_callback' => function($value) { return (bool) $value; }
        ]);
        \register_setting('pdf_builder_settings', 'pdf_builder_date_format', [
            'type' => 'string',
            'description' => 'Format de date',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        \register_setting('pdf_builder_settings', 'pdf_builder_time_format', [
            'type' => 'string',
            'description' => 'Format d\'heure',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        \register_setting('pdf_builder_settings', 'pdf_builder_number_format', [
            'type' => 'string',
            'description' => 'Format des nombres',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        if (class_exists('PDF_Builder_Logger')) { 
            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] All settings registered in settings-main.php'); 
        }
    });

    // Gestion de la soumission du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['option_page']) && $_POST['option_page'] === 'pdf_builder_settings') {
        if (class_exists('PDF_Builder_Logger')) { 
            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Form submitted - processing settings save'); 
        }
        
        // La sauvegarde est gérée automatiquement par WordPress via register_setting
        // Ajouter un message de succès
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . '</p></div>';
        });
        
        if (class_exists('PDF_Builder_Logger')) { 
            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Settings save completed successfully'); 
        }
    }

    // Hook pour la sauvegarde personnalisée via admin-post.php
    add_action('admin_post_pdf_builder_save_settings', function() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Accès refusé.', 'pdf-builder-pro'));
        }

        // Vérifier le nonce de sécurité
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_save_settings')) {
            wp_die(__('Erreur de sécurité.', 'pdf-builder-pro'));
        }

        if (class_exists('PDF_Builder_Logger')) { 
            PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Admin post save function called'); 
        }

        // Traiter les données du formulaire
        $settings = isset($_POST['pdf_builder_settings']) ? $_POST['pdf_builder_settings'] : array();
        
        // Nettoyer et valider les données
        $sanitized_settings = array();
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $sanitized_settings[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_settings[$key] = sanitize_text_field($value);
            }
        }

        // Sauvegarder dans la base de données
        update_option('pdf_builder_settings', $sanitized_settings);

        // Sauvegarder les autres paramètres individuels
        $individual_settings = [
            'pdf_builder_allowed_roles',
            'pdf_builder_company_vat', 
            'pdf_builder_company_rcs',
            'pdf_builder_company_siret',
            'pdf_builder_default_locale',
            'pdf_builder_rtl_support',
            'pdf_builder_date_format',
            'pdf_builder_time_format',
            'pdf_builder_number_format'
        ];

        foreach ($individual_settings as $setting_key) {
            if (isset($_POST[$setting_key])) {
                if (is_array($_POST[$setting_key])) {
                    update_option($setting_key, array_map('sanitize_text_field', $_POST[$setting_key]));
                } else {
                    update_option($setting_key, sanitize_text_field($_POST[$setting_key]));
                }
            }
        }

        // Redirection avec message de succès
        $redirect_url = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_safe_redirect($redirect_url);
        exit;
    });

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

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="pdf-builder-settings-form">
        <input type="hidden" name="action" value="pdf_builder_save_settings" />
        <?php wp_nonce_field('pdf_builder_save_settings'); ?>

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

        <!-- Bouton flottant de sauvegarde optimisé -->
        <div id="pdf-builder-floating-save-container" class="pdf-builder-floating-save-container">
            <div class="pdf-builder-floating-save-wrapper">
                <button type="submit"
                        name="pdf_builder_save_settings"
                        id="pdf-builder-floating-save-btn"
                        class="pdf-builder-floating-save-btn"
                        data-action="save"
                        data-nonce="<?php echo wp_create_nonce('pdf_builder_save_settings'); ?>"
                        title="<?php esc_attr_e('Enregistrer tous les paramètres', 'pdf-builder-pro'); ?>">
                    <span class="pdf-builder-save-icon">💾</span>
                    <span class="pdf-builder-save-text"><?php _e('Enregistrer', 'pdf-builder-pro'); ?></span>
                    <span class="pdf-builder-save-spinner" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" opacity="0.3"></circle>
                            <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
                        </svg>
                    </span>
                </button>

                <!-- Indicateur de statut -->
                <div class="pdf-builder-save-status" id="pdf-builder-save-status">
                    <div class="pdf-builder-save-status-content">
                        <span class="pdf-builder-status-icon">✓</span>
                        <span class="pdf-builder-status-text"><?php _e('Paramètres enregistrés', 'pdf-builder-pro'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

</div> <!-- Fin du .wrap -->

<?php
// Inclure les modales canvas à la fin pour éviter les conflits de structure
require_once __DIR__ . '/settings-modals.php';
?>

</body>
</html>
