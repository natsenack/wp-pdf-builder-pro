<?php
/**
 * PDF Builder Pro V2 - Page de paramètres
 */

if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap">
    <h1><?php echo esc_html(__('Paramètres PDF Builder Pro', 'pdf-builder-pro')); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=pdf-builder-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Général', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-settings&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Avancé', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-settings&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>">
            <?php _e('À propos', 'pdf-builder-pro'); ?>
        </a>
    </nav>
    
    <div class="tab-content">
        <?php if ($active_tab === 'general'): ?>
            <div class="tab-pane">
                <h2><?php _e('Paramètres généraux', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Configurez les options générales du PDF Builder Pro.', 'pdf-builder-pro'); ?></p>
                
                <form id="pdf-builder-settings-form-general" method="post" action="options.php">
                    <?php settings_fields('pdf_builder_general'); ?>
                    <?php do_settings_sections('pdf_builder_general'); ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php elseif ($active_tab === 'advanced'): ?>
            <div class="tab-pane">
                <h2><?php _e('Paramètres avancés', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Configurer les options avancées du PDF Builder Pro.', 'pdf-builder-pro'); ?></p>
                
                <form id="pdf-builder-settings-form-advanced" method="post" action="options.php">
                    <?php settings_fields('pdf_builder_advanced'); ?>
                    <?php do_settings_sections('pdf_builder_advanced'); ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php else: ?>
            <div class="tab-pane">
                <h2><?php _e('À propos de PDF Builder Pro', 'pdf-builder-pro'); ?></h2>
                <div class="about-box">
                    <h3>Version 2.0.0</h3>
                    <p><strong><?php _e('PDF Builder Pro V2', 'pdf-builder-pro'); ?></strong></p>
                    <p><?php _e('Refonte complète avec architecture moderne et React 18', 'pdf-builder-pro'); ?></p>
                    
                    <h4><?php _e('Améliorations', 'pdf-builder-pro'); ?></h4>
                    <ul>
                        <li>✅ Architecture modulaire</li>
                        <li>✅ TypeScript strict</li>
                        <li>✅ Bundle 4x plus petit</li>
                        <li>✅ Gestion d'erreurs robuste</li>
                        <li>✅ React 18 natif</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bouton flottant Enregistrer -->
<div id="pdf-builder-floating-save" class="pdf-builder-floating-save">
    <button type="button" id="pdf-builder-save-settings" class="pdf-builder-save-btn">
        <span class="dashicons dashicons-yes"></span>
        <?php _e('Enregistrer', 'pdf-builder-pro'); ?>
    </button>
    <div id="pdf-builder-save-status" class="pdf-builder-save-status"></div>
</div>

<style>
.pdf-builder-floating-save {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
}

.pdf-builder-save-btn {
    background: #2271b1;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.pdf-builder-save-btn:hover {
    background: #135e96;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.pdf-builder-save-btn:active {
    transform: translateY(0);
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

.tab-content {
    background: white;
    padding: 20px;
    margin-top: 0;
}

.tab-pane {
    display: block;
}

.about-box {
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 20px;
}

.about-box ul {
    margin-left: 20px;
}

.about-box li {
    margin-bottom: 8px;
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
    var currentTab = '<?php echo $active_tab; ?>';
    
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
        var formData = new FormData();
        formData.append('action', 'pdf_builder_save_settings');
        formData.append('tab', currentTab);
        // Utiliser le nonce depuis pdf_builder_ajax.nonce (localisé via wp_localize_script dans settings-loader.php)
        // et l'envoyer en tant que _wpnonce pour correspondre au handler
        var nonce = (typeof pdf_builder_ajax !== 'undefined' && pdf_builder_ajax.nonce) ? pdf_builder_ajax.nonce : '';
        formData.append('_wpnonce', nonce);

        // Collecter les champs du formulaire actif
        var $activeForm = $('#pdf-builder-settings-form-' + currentTab);
        console.log('PDF Builder Settings: Active form:', $activeForm.length, 'for tab:', currentTab);
        
        if ($activeForm.length > 0) {
            var fieldCount = 0;
            $activeForm.find('input, select, textarea').each(function() {
                var $field = $(this);
                var fieldName = $field.attr('name');
                if (fieldName) {
                    if ($field.attr('type') === 'checkbox') {
                        formData.append(fieldName, $field.is(':checked') ? '1' : '0');
                    } else if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            formData.append(fieldName, $field.val());
                        }
                    } else {
                        formData.append(fieldName, $field.val() || '');
                    }
                    fieldCount++;
                }
            });
            console.log('PDF Builder Settings: Collected', fieldCount, 'fields');
        }

        console.log('PDF Builder Settings: Sending AJAX request to:', ajaxurl);

        // Envoyer la requête AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                console.log('PDF Builder Settings: AJAX success:', response);
                if (response.success) {
                    showStatus('<?php _e("Paramètres sauvegardés", "pdf-builder-pro"); ?>', 'success');
                    $btn.removeClass('saving').addClass('saved');
                } else {
                    showStatus(response.data || '<?php _e("Erreur lors de la sauvegarde", "pdf-builder-pro"); ?>', 'error');
                    $btn.removeClass('saving').addClass('error');
                }
            },
            error: function(xhr, status, error) {
                console.log('PDF Builder Settings: AJAX error:', xhr, status, error);
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


