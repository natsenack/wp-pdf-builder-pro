<?php
/**
 * Page principale des paramètres PDF Builder Pro - VERSION SIMPLIFIÉE
 */

if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
}

// Récupération des paramètres
$settings = pdf_builder_get_option('pdf_builder_settings', array());
$current_tab = sanitize_text_field($_GET['tab'] ?? 'general');
$valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
if (!in_array($current_tab, $valid_tabs)) {
    $current_tab = 'general';
}

// Enregistrer les paramètres - UTILISE LE SYSTÈME PERSONNALISÉ
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings'])) {
    // Déterminer si c'est une sauvegarde flottante
    $is_floating_save = isset($_POST['pdf_builder_floating_save']) && $_POST['pdf_builder_floating_save'] == '1';
    $save_type = $is_floating_save ? 'FLOATING SAVE' : 'REGULAR SAVE';

    error_log("[$save_type] Sauvegarde démarrée");

    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pdf_builder_settings-options')) {
        error_log("[$save_type] ERREUR: Nonce invalide");
        wp_die('Sécurité: Nonce invalide');
    }

    if (!current_user_can('manage_options')) {
        error_log("[$save_type] ERREUR: Permissions insuffisantes");
        wp_die('Accès refusé');
    }

    // Sanitize and save settings
    $settings = array_map('sanitize_text_field', $_POST['pdf_builder_settings']);
    $save_result = pdf_builder_update_option('pdf_builder_settings', $settings);

    if ($save_result) {
        error_log("[$save_type] Sauvegarde réussie");
    } else {
        error_log("[$save_type] ERREUR: Échec de la sauvegarde");
    }

    // Redirection pour éviter la resoumission avec message de succès
    $redirect_url = add_query_arg([
        'page' => 'pdf-builder-settings',
        'tab' => $current_tab,
        'updated' => '1'
    ], admin_url('admin.php'));

    wp_redirect($redirect_url);
    exit;
}

// Afficher le message de succès si la mise à jour a réussi
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>Paramètres sauvegardés avec succès !</p></div>';
    });
}

?>

<div class="wrap">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <form method="post" action="" id="pdf-builder-settings-form">
        <?php wp_nonce_field('pdf_builder_settings-options'); ?>
        <!-- Champ caché pour la soumission manuelle du formulaire -->
        <input type="hidden" name="submit" value="1">

        <!-- Navigation par onglets -->
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

            <!-- Bouton flottant Enregistrer -->
            <div id="pdf-builder-floating-save" class="pdf-builder-floating-save">
                <button type="button" id="pdf-builder-save-settings" class="pdf-builder-save-btn">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Enregistrer', 'pdf-builder-pro'); ?>
                </button>
                <div id="pdf-builder-save-status" class="pdf-builder-save-status"></div>
            </div>
        </div>
    </form>
</div>

<style>
.pdf-builder-floating-save {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
}

.pdf-builder-save-btn {
    background: #007cba;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    min-width: 120px;
    justify-content: center;
}

.pdf-builder-save-btn:hover {
    background: #005a87;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.pdf-builder-save-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pdf-builder-save-btn.saving {
    background: #f39c12;
    cursor: not-allowed;
}

.pdf-builder-save-btn.saved {
    background: #27ae60;
}

.pdf-builder-save-btn.error {
    background: #e74c3c;
}

.pdf-builder-save-status {
    position: absolute;
    top: -40px;
    right: 0;
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    white-space: nowrap;
}

.pdf-builder-save-status.show {
    opacity: 1;
    transform: translateY(0);
}

.pdf-builder-save-status.success {
    background: #27ae60;
}

.pdf-builder-save-status.error {
    background: #e74c3c;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('PDF Builder Settings: JavaScript loaded');
    
    // S'assurer qu'ajaxurl est défini
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
        console.log('PDF Builder Settings: ajaxurl was undefined, set to:', ajaxurl);
    }
    
    var $saveBtn = $('#pdf-builder-save-settings');
    var $saveStatus = $('#pdf-builder-save-status');
    var currentTab = '<?php echo $current_tab; ?>';
    
    console.log('PDF Builder Settings: Button found:', $saveBtn.length);
    console.log('PDF Builder Settings: ajaxurl available:', ajaxurl);
    
    // Fonction pour afficher le statut
    function showStatus(message, type) {
        console.log('PDF Builder Settings: Showing status:', message, type);
        $saveStatus.removeClass('success error').addClass(type + ' show').text(message);

        setTimeout(function() {
            $saveStatus.removeClass('show');
        }, 3000);
    }

    // Gestionnaire du clic sur le bouton Enregistrer
    $saveBtn.on('click', function(event) {
        console.log('PDF Builder Settings: Button clicked');
        event.preventDefault();
        event.stopImmediatePropagation(); // Arrêter tous les autres gestionnaires
        
        var $btn = $(this);
        console.log('PDF Builder Settings: Starting AJAX save');

        // Vérifier si déjà en cours de sauvegarde
        if ($btn.hasClass('saving')) {
            console.log('PDF Builder Settings: Already saving, ignoring click');
            return false;
        }

        // Désactiver le bouton pendant la sauvegarde
        $btn.addClass('saving').prop('disabled', true).find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-update dashicons-spin');

        // Collecter les données du formulaire actif
        var ajaxData = {
            action: 'pdf_builder_save_settings',
            tab: currentTab,
            _wpnonce: window.pdfBuilderNonce
        };

        // Collecter les champs du formulaire actif
        var $activeForm = $('#pdf-builder-settings-form');
        console.log('PDF Builder Settings: Active form:', $activeForm.length, 'for tab:', currentTab);
        
        if ($activeForm.length > 0) {
            var fieldCount = 0;
            $activeForm.find('input, select, textarea').each(function() {
                var $field = $(this);
                var fieldName = $field.attr('name');
                if (fieldName && fieldName !== '_wpnonce') { // Skip _wpnonce as we set it explicitly
                    if ($field.attr('type') === 'checkbox') {
                        ajaxData[fieldName] = $field.is(':checked') ? '1' : '0';
                    } else if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            ajaxData[fieldName] = $field.val();
                        }
                    } else {
                        ajaxData[fieldName] = $field.val() || '';
                    }
                    fieldCount++;
                }
            });
        console.log('PDF Builder Settings: Collected', fieldCount, 'fields');
        }

        // Log détaillé des données AJAX avant envoi
        console.log('PDF Builder Settings: AJAX data to send:', {
            action: ajaxData.action,
            tab: ajaxData.tab,
            _wpnonce: ajaxData._wpnonce ? ajaxData._wpnonce.substring(0, 10) + '...' : 'NOT SET',
            fieldCount: Object.keys(ajaxData).length - 3, // Soustraire action, tab, _wpnonce
            windowPdfBuilderNonce: window.pdfBuilderNonce ? window.pdfBuilderNonce.substring(0, 10) + '...' : 'NOT SET',
            nonceMatch: ajaxData._wpnonce === window.pdfBuilderNonce
        });

        console.log('PDF Builder Settings: Sending AJAX request to:', ajaxurl);

        // Envoyer la requête AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                console.log('PDF Builder Settings: AJAX success - Full response:', response);
                console.log('PDF Builder Settings: Response success:', response.success);
                console.log('PDF Builder Settings: Response data:', response.data);
                if (response.success) {
                    showStatus('<?php _e("Paramètres sauvegardés", "pdf-builder-pro"); ?>', 'success');
                    $btn.removeClass('saving').addClass('saved');
                } else {
                    console.log('PDF Builder Settings: Error details:', {
                        message: response.data.message || 'Unknown error',
                        debug: response.data.debug || 'No debug info'
                    });
                    showStatus(response.data.message || '<?php _e("Erreur lors de la sauvegarde", "pdf-builder-pro"); ?>', 'error');
                    $btn.removeClass('saving').addClass('error');
                }
            },
            error: function(xhr, status, error) {
                console.log('PDF Builder Settings: AJAX error - Full details:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                var errorMsg = '<?php _e("Erreur de connexion", "pdf-builder-pro"); ?>';
                if (status === 'timeout') {
                    errorMsg = '<?php _e("Timeout - Réessayez", "pdf-builder-pro"); ?>';
                }
                showStatus(errorMsg, 'error');
                $btn.removeClass('saving').addClass('error');
            },
            complete: function() {
                // Réactiver le bouton après un délai
                setTimeout(function() {
                    $btn.removeClass('saving saved error').prop('disabled', false)
                        .find('.dashicons').removeClass('dashicons-update dashicons-spin').addClass('dashicons-yes');
                }, 2000);
            }
        });
        
        return false; // Sécurité supplémentaire
    });

    // Changer d'onglet
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).attr('href').split('tab=')[1];
        if (tab) {
            currentTab = tab;
            window.location.href = $(this).attr('href');
        }
    });
    
    console.log('PDF Builder Settings: Initialization complete');
});
</script>

<?php
// Inclure les modales canvas à la fin pour éviter les conflits de structure
require_once __DIR__ . '/settings-modals.php';
?>
