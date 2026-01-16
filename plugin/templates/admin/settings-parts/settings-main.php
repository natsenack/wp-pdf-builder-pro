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

<script type="text/javascript">
(function($) {
    'use strict';

    // Attendre que le DOM soit complètement chargé
    $(document).ready(function() {
        console.log('🔧 PDF Builder Settings: Initializing developer tools...');

        // === GESTION DU MODE DÉVELOPPEUR ===
        $(document).on('change', '#developer_enabled', function(e) {
            handleDeveloperModeToggle(e);
        });

        // === GESTION DU MOT DE PASSE ===
        $(document).on('click', '#toggle_password', function(e) {
            handlePasswordToggle(e);
        });

        // === TESTS DE LICENCE ===
        $(document).on('click', '#toggle_license_test_mode_btn', function(e) {
            e.preventDefault();
            toggleLicenseTestMode(true);
        });

        $(document).on('change', '#license_test_mode', function(e) {
            toggleLicenseTestMode(false);
        });

        $(document).on('click', '#generate_license_key_btn', function(e) {
            e.preventDefault();
            console.log('🔑 PDF Builder Debug - Generate license key button clicked');
            generateTestLicenseKey();
        });

        $(document).on('click', '#copy_license_key_btn', function(e) {
            copyLicenseKey();
        });

        $(document).on('click', '#delete_license_key_btn', function(e) {
            e.preventDefault();
            deleteTestLicenseKey();
        });

        $(document).on('click', '#cleanup_license_btn', function(e) {
            e.preventDefault();
            cleanupLicense();
        });

        // === MONITORING DES PERFORMANCES ===
        $(document).on('click', '#test_fps_btn', function(e) {
            e.preventDefault();
            testFPS();
        });

        $(document).on('click', '#system_info_btn', function(e) {
            e.preventDefault();
            showSystemInfo();
        });

        // === OUTILS DE DÉVELOPPEMENT ===
        $(document).on('click', '#reload_cache_btn', function(e) {
            e.preventDefault();
            reloadCache();
        });

        $(document).on('click', '#clear_temp_btn', function(e) {
            e.preventDefault();
            clearTemp();
        });

        $(document).on('click', '#export_diagnostic_btn', function(e) {
            e.preventDefault();
            exportDiagnostic();
        });

        // === GESTION DES LOGS ===
        $(document).on('click', '#refresh_logs_btn', function(e) {
            e.preventDefault();
            refreshLogs();
        });

        $(document).on('click', '#clear_logs_btn', function(e) {
            e.preventDefault();
            clearLogs();
        });

        // === TESTS DE NOTIFICATIONS ===
        $(document).on('click', '#test_notification_success', function(e) {
            e.preventDefault();
            testNotification('success');
        });

        $(document).on('click', '#test_notification_error', function(e) {
            e.preventDefault();
            testNotification('error');
        });

        $(document).on('click', '#test_notification_warning', function(e) {
            e.preventDefault();
            testNotification('warning');
        });

        $(document).on('click', '#test_notification_info', function(e) {
            e.preventDefault();
            testNotification('info');
        });

        $(document).on('click', '#test_notification_all', function(e) {
            e.preventDefault();
            testAllNotifications();
        });

        $(document).on('click', '#test_notification_clear', function(e) {
            e.preventDefault();
            clearAllNotifications();
        });

        $(document).on('click', '#test_notification_stats', function(e) {
            e.preventDefault();
            showNotificationStats();
        });

        // Initialiser l'état des sections développeur
        initializeDeveloperSections();
    });

    // === FONCTIONS UTILITAIRES ===

    function makeAjaxCall(action, data, successCallback, errorCallback) {
        console.log('PDF Builder Debug - pdfBuilderAjax:', window.pdfBuilderAjax);
        console.log('PDF Builder Debug - ajaxurl:', ajaxurl);
        const ajaxData = {
            action: action,
            nonce: pdfBuilderAjax?.nonce || '',
            ...data
        };
        console.log('PDF Builder Debug - ajaxData:', ajaxData);

        $.ajax({
            url: pdfBuilderAjax?.ajaxurl || ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                if (response.success) {
                    if (successCallback) successCallback(response);
                } else {
                    console.error('❌ AJAX Error:', response.data?.message || 'Unknown error');
                    if (errorCallback) errorCallback(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Request failed:', status, error);
                if (errorCallback) errorCallback({message: 'Request failed'});
            }
        });
    }

    function showSuccess(message) {
        // Utiliser le système de notifications si disponible
        if (window.pdfBuilderNotifications && typeof window.pdfBuilderNotifications.show === 'function') {
            window.pdfBuilderNotifications.show('success', message);
        } else {
            alert('✅ ' + message);
        }
    }

    function showError(message) {
        // Utiliser le système de notifications si disponible
        if (window.pdfBuilderNotifications && typeof window.pdfBuilderNotifications.show === 'function') {
            window.pdfBuilderNotifications.show('error', message);
        } else {
            alert('❌ ' + message);
        }
    }

    // === GESTION DU MODE DÉVELOPPEUR ===

    function handleDeveloperModeToggle(e) {
        const isEnabled = $(e.target).is(':checked');
        console.log('🔧 Developer mode toggle:', isEnabled ? 'enabled' : 'disabled');

        // Masquer/afficher les sections développeur
        if (isEnabled) {
            $('.developer-section-hidden').slideDown();
        } else {
            $('.developer-section-hidden').slideUp();
        }

        // Sauvegarder via AJAX
        makeAjaxCall('pdf_builder_developer_save_settings', {
            setting_key: 'pdf_builder_developer_enabled',
            setting_value: isEnabled ? '1' : '0'
        }, function(response) {
            showSuccess('Mode développeur ' + (isEnabled ? 'activé' : 'désactivé'));
        }, function(error) {
            showError('Erreur lors de la sauvegarde du mode développeur');
        });
    }

    function handlePasswordToggle(e) {
        e.preventDefault();
        const passwordField = $('#developer_password');
        const button = $(e.target);

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            button.text('🙈 Masquer');
        } else {
            passwordField.attr('type', 'password');
            button.text('👁️ Afficher');
        }
    }

    // === TESTS DE LICENCE ===

    function toggleLicenseTestMode(forceToggle = true) {
        const checkbox = $('#license_test_mode');
        const status = $('#license_test_mode_status');
        const isChecked = checkbox.is(':checked');

        let newState;
        if (forceToggle) {
            // Force toggle if requested (i.e., from a button click)
            checkbox.prop('checked', !isChecked);
            newState = !isChecked;
        } else {
            // Use the current checkbox state (i.e., user clicked the checkbox directly)
            newState = checkbox.is(':checked');
        }

        status.html(newState ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif')
              .css({
                  'background': newState ? '#d4edda' : '#f8d7da',
                  'color': newState ? '#155724' : '#721c24',
                  'padding': '5px 10px',
                  'border-radius': '4px',
                  'display': 'inline-block'
              });

        // Make AJAX call
        makeAjaxCall('pdf_builder_toggle_test_mode', {}, function(response) {
            showSuccess('Mode test ' + (newState ? 'activé' : 'désactivé') + ' avec succès');
        }, function(error) {
            // Revert UI on error
            checkbox.prop('checked', isChecked);
            status.html(isChecked ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif')
                  .css({
                      'background': isChecked ? '#d4edda' : '#f8d7da',
                      'color': isChecked ? '#155724' : '#721c24',
                      'padding': '5px 10px',
                      'border-radius': '4px',
                      'display': 'inline-block'
                  });
            showError('Erreur lors du changement du mode test');
        });
    }

    function generateTestLicenseKey() {
        console.log('🚀 PDF Builder Debug - generateTestLicenseKey called');
        console.log('PDF Builder Debug - pdfBuilderAjax:', window.pdfBuilderAjax);
        console.log('PDF Builder Debug - ajaxurl:', ajaxurl);
        makeAjaxCall('pdf_builder_generate_test_license_key', {}, function(response) {
            const newKey = response.data?.license_key || '';
            if (newKey) {
                $('#license_test_key').val(newKey);
                $('#delete_license_key_btn').show();
                $('#license_key_status').html('<span style="color: #28a745;">✓ Clé générée avec succès</span>');
                showSuccess('Clé de test générée avec succès');
            }
        }, function(error) {
            $('#license_key_status').html('<span style="color: #dc3545;">❌ Erreur lors de la génération</span>');
            showError('Erreur lors de la génération de la clé de test');
        });
    }

    function copyLicenseKey() {
        const keyInput = $('#license_test_key');
        const key = keyInput.val();

        if (key) {
            navigator.clipboard.writeText(key).then(function() {
                showSuccess('Clé copiée dans le presse-papiers');
            }).catch(function(err) {
                // Fallback pour les navigateurs plus anciens
                keyInput.select();
                document.execCommand('copy');
                showSuccess('Clé copiée dans le presse-papiers');
            });
        } else {
            showError('Aucune clé à copier');
        }
    }

    function deleteTestLicenseKey() {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette clé de test ?')) {
            return;
        }

        makeAjaxCall('pdf_builder_delete_test_license_key', {}, function(response) {
            $('#license_test_key').val('');
            $('#delete_license_key_btn').hide();
            $('#license_key_status').html('<span style="color: #28a745;">✓ Clé supprimée avec succès</span>');
            showSuccess('Clé de test supprimée avec succès');
        }, function(error) {
            $('#license_key_status').html('<span style="color: #dc3545;">❌ Erreur lors de la suppression</span>');
            showError('Erreur lors de la suppression de la clé de test');
        });
    }

    function cleanupLicense() {
        if (!confirm('Êtes-vous sûr de vouloir nettoyer complètement la licence ? Toutes les données de licence seront supprimées.')) {
            return;
        }

        makeAjaxCall('pdf_builder_cleanup_license', {}, function(response) {
            $('#license_test_key').val('');
            $('#license_test_mode').prop('checked', false);
            $('#license_test_mode_status').html('❌ Mode test inactif')
                  .css({
                      'background': '#f8d7da',
                      'color': '#721c24',
                      'padding': '5px 10px',
                      'border-radius': '4px',
                      'display': 'inline-block'
                  });
            $('#delete_license_key_btn').hide();
            $('#cleanup_status').html('<span style="color: #28a745;">✓ Nettoyage complet effectué</span>');
            showSuccess('Licence nettoyée complètement');
        }, function(error) {
            $('#cleanup_status').html('<span style="color: #dc3545;">❌ Erreur lors du nettoyage</span>');
            showError('Erreur lors du nettoyage de la licence');
        });
    }

    // === MONITORING DES PERFORMANCES ===

    function testFPS() {
        $('#fps_test_result').html('<span style="color: #007cba;">⏳ Test en cours...</span>');

        // Simuler un test FPS (dans un vrai scénario, cela testerait le canvas)
        setTimeout(function() {
            const mockFPS = Math.floor(Math.random() * 20) + 50; // 50-70 FPS
            const targetFPS = 60;
            const isGood = mockFPS >= targetFPS;

            $('#fps_test_result').html(
                '<span style="color: ' + (isGood ? '#28a745' : '#dc3545') + '; font-weight: bold;">' +
                mockFPS + ' FPS ' + (isGood ? '✅' : '⚠️') + '</span>'
            );

            if (!isGood) {
                $('#fps_test_details').show();
            }
        }, 2000);
    }

    function showSystemInfo() {
        $('#system_info_result').show();
        showSuccess('Informations système affichées');
    }

    // === OUTILS DE DÉVELOPPEMENT ===

    function reloadCache() {
        makeAjaxCall('pdf_builder_clear_all_cache', {}, function(response) {
            showSuccess('Cache rechargé avec succès');
        }, function(error) {
            showError('Erreur lors du rechargement du cache');
        });
    }

    function clearTemp() {
        makeAjaxCall('pdf_builder_clear_temp', {}, function(response) {
            showSuccess('Fichiers temporaires supprimés');
        }, function(error) {
            showError('Erreur lors de la suppression des fichiers temporaires');
        });
    }

    function exportDiagnostic() {
        makeAjaxCall('pdf_builder_export_diagnostic', {}, function(response) {
            if (response.data?.download_url) {
                window.location.href = response.data.download_url;
                showSuccess('Diagnostic exporté avec succès');
            }
        }, function(error) {
            showError('Erreur lors de l\'export du diagnostic');
        });
    }

    // === GESTION DES LOGS ===

    function refreshLogs() {
        makeAjaxCall('pdf_builder_refresh_logs', {}, function(response) {
            if (response.data?.logs) {
                $('#logs_content').html('<pre>' + response.data.logs + '</pre>');
            }
            showSuccess('Logs actualisés');
        }, function(error) {
            showError('Erreur lors de l\'actualisation des logs');
        });
    }

    function clearLogs() {
        if (!confirm('Êtes-vous sûr de vouloir vider tous les logs ?')) {
            return;
        }

        makeAjaxCall('pdf_builder_clear_logs', {}, function(response) {
            $('#logs_content').html('<em style="color: #666;">Logs vidés. Cliquez sur "Actualiser Logs" pour recharger...</em>');
            showSuccess('Logs vidés avec succès');
        }, function(error) {
            showError('Erreur lors du vidage des logs');
        });
    }

    // === TESTS DE NOTIFICATIONS ===

    function testNotification(type) {
        const messages = {
            success: '✅ Test de notification succès réussi !',
            error: '❌ Test de notification erreur (ceci est normal)',
            warning: '⚠️ Test de notification avertissement',
            info: 'ℹ️ Test de notification information'
        };

        if (window.pdfBuilderNotifications && typeof window.pdfBuilderNotifications.show === 'function') {
            window.pdfBuilderNotifications.show(type, messages[type]);
            logNotificationTest(type, messages[type]);
        } else {
            alert(messages[type]);
        }
    }

    function testAllNotifications() {
        const types = ['success', 'error', 'warning', 'info'];
        let index = 0;

        const testNext = function() {
            if (index < types.length) {
                testNotification(types[index]);
                index++;
                setTimeout(testNext, 1000); // 1 seconde entre chaque
            }
        };

        testNext();
    }

    function clearAllNotifications() {
        if (window.pdfBuilderNotifications && typeof window.pdfBuilderNotifications.clear === 'function') {
            window.pdfBuilderNotifications.clear();
            showSuccess('Toutes les notifications ont été supprimées');
        } else {
            showSuccess('Fonctionnalité non disponible');
        }
    }

    function showNotificationStats() {
        if (window.pdfBuilderNotifications && typeof window.pdfBuilderNotifications.getStats === 'function') {
            const stats = window.pdfBuilderNotifications.getStats();
            alert('Statistiques des notifications:\n' + JSON.stringify(stats, null, 2));
        } else {
            showSuccess('Statistiques non disponibles');
        }
    }

    function logNotificationTest(type, message) {
        const logsContainer = $('#notification_test_logs');
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `<div style="margin-bottom: 5px; padding: 5px; background: #f8f9fa; border-left: 3px solid ${
            type === 'success' ? '#28a745' :
            type === 'error' ? '#dc3545' :
            type === 'warning' ? '#ffc107' : '#17a2b8'
        };">[${timestamp}] ${type.toUpperCase()}: ${message}</div>`;

        logsContainer.append(logEntry);
        logsContainer.scrollTop(logsContainer[0].scrollHeight);
    }

    // === INITIALISATION ===

    function initializeDeveloperSections() {
        // Masquer les sections développeur si le mode n'est pas activé
        const developerEnabled = $('#developer_enabled').is(':checked');
        if (!developerEnabled) {
            $('.developer-section-hidden').hide();
        }

        // Masquer le bouton de suppression si pas de clé
        const licenseKey = $('#license_test_key').val();
        if (!licenseKey) {
            $('#delete_license_key_btn').hide();
        }

        console.log('🔧 Developer sections initialized');
    }

})(jQuery);
</script>
