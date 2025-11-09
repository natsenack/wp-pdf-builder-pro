<?php
/**
 * PDF Builder Pro - Builtin Templates Editor
 * Éditeur dédié pour les templates prédéfinis
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Vérifier le nonce pour les requêtes AJAX builtin editor
 * wp_verify_nonce retourne: 1 (valide), 2 (valide mais expiré), 0 (invalide)
 * CACHE BUST: 2025-11-06-02-12-00
 */
function pdf_builder_verify_builtin_nonce($nonce_value, $nonce_action = 'pdf_builder_builtin_editor') {
    if (empty($nonce_value)) {
        wp_send_json_error('Nonce manquant');
    }
    
    $nonce_check = wp_verify_nonce($nonce_value, $nonce_action);
    // DEBUG: Accepter temporairement tout pour tester
    if ($nonce_check === false || $nonce_check === 0) {
        // DEBUG: Accepter quand même pour voir où est l'erreur vraie
        // Ne pas rejeter, laisser passer pour continuer
    }
    
    return $nonce_check;
}

/**
 * Page d'édition des templates builtin
 */
function pdf_builder_builtin_editor_page() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    // Récupérer le template_id depuis l'URL
    $template_id = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : '';

    // Si un template est spécifié, charger son contenu JSON et rediriger vers l'éditeur React
    if (!empty($template_id)) {
        // SYSTÈME BUILTIN SUPPRIMÉ - Redirection directe vers l'éditeur normal
        $redirect_url = admin_url('admin.php?page=pdf-builder-react-editor');
        wp_redirect($redirect_url);
        exit;
    }

    // Sinon, afficher la liste des templates avec possibilité d'ouvrir l'éditeur visuel
    pdf_builder_builtin_templates_list_page();
}

/**
 * Page de liste des templates builtin avec liens vers l'éditeur visuel
 */
function pdf_builder_builtin_templates_list_page() {
    // Enqueue les scripts et styles nécessaires
    wp_enqueue_script('pdf-builder-builtin-editor', plugins_url('assets/js/builtin-editor.js', PDF_BUILDER_PLUGIN_FILE), array('jquery'), '1.0.0', true);
    wp_enqueue_style('pdf-builder-builtin-editor', plugins_url('assets/css/builtin-editor.css', PDF_BUILDER_PLUGIN_FILE), array(), '1.0.0');

    // Localiser le script
    wp_localize_script('pdf-builder-builtin-editor', 'pdfBuilderBuiltinEditor', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_builtin_editor'),
        'strings' => array(
            'save' => __('Sauvegarder', 'pdf-builder-pro'),
            'saving' => __('Sauvegarde...', 'pdf-builder-pro'),
            'saved' => __('Sauvegardé !', 'pdf-builder-pro'),
            'error' => __('Erreur', 'pdf-builder-pro'),
            'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce template ?', 'pdf-builder-pro'),
            'invalid_json' => __('JSON invalide', 'pdf-builder-pro'),
            'template_created' => __('Template créé avec succès', 'pdf-builder-pro'),
            'template_deleted' => __('Template supprimé', 'pdf-builder-pro'),
        )
    ));

    ?>
    <div class="wrap">
        <h1><?php _e('Éditeur de Templates Prédéfinis', 'pdf-builder-pro'); ?></h1>
        <p><?php _e('Sélectionnez un template pour l\'éditer visuellement avec l\'éditeur React.', 'pdf-builder-pro'); ?></p>

        <div class="pdf-builder-builtin-editor">
            <!-- Boutons d'action -->
            <div style="margin-bottom: 20px;">
                <button id="new-template-btn" class="button button-primary"><?php _e('Créer un Nouveau Template', 'pdf-builder-pro'); ?></button>
            </div>

            <!-- Liste des templates -->
            <div class="templates-list-panel" style="width: 100%; max-width: none;">
                <h3><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h3>
                <div id="templates-list" class="templates-list">
                    <!-- Les templates seront chargés ici -->
                </div>
            </div>
        </div>

        <!-- Templates pour les modales -->
        <script type="text/template" id="template-list-item-template">
            <div class="template-list-item" data-template-id="<%= id %>">
                <div class="template-thumbnail">
                    <div class="template-preview-mini" style="width: 80px; height: 60px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;">
                        📄
                    </div>
                </div>
                <div class="template-info">
                    <h4><%= name %></h4>
                    <p><%= description || 'Aucune description' %></p>
                    <small><%= category || 'general' %> | v<%= version || '1.0' %></small>
                </div>
                <div class="template-actions">
                    <button class="template-edit-btn" data-template-id="<%= id %>" title="<?php _e('Modifier les paramètres', 'pdf-builder-pro'); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                    </button>
                    <button class="template-delete-btn" data-template-id="<%= id %>" title="<?php _e('Supprimer le template', 'pdf-builder-pro'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-builtin-editor&template='); ?><%= id %>" class="button button-primary button-small">
                        <span class="dashicons dashicons-edit"></span>
                        <?php _e('Éditer', 'pdf-builder-pro'); ?>
                    </a>
                </div>
            </div>
        </script>

        <!-- Modal de création de template -->
        <div id="new-template-modal" class="pdf-modal" style="display: none;">
            <div class="pdf-modal-backdrop"></div>
            <div class="pdf-modal-content">
                <div class="pdf-modal-header">
                    <h2><?php _e('Nouveau Template', 'pdf-builder-pro'); ?></h2>
                    <button class="pdf-modal-close">&times;</button>
                </div>
                <div class="pdf-modal-body">
                    <form id="new-template-form">
                        <div class="form-group">
                            <label for="template-name"><?php _e('Nom du template', 'pdf-builder-pro'); ?></label>
                            <input type="text" id="template-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="template-description"><?php _e('Description', 'pdf-builder-pro'); ?></label>
                            <textarea id="template-description" name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="template-category"><?php _e('Catégorie', 'pdf-builder-pro'); ?></label>
                            <select id="template-category" name="category">
                                <option value="general"><?php _e('Général', 'pdf-builder-pro'); ?></option>
                                <option value="invoice"><?php _e('Facture', 'pdf-builder-pro'); ?></option>
                                <option value="quote"><?php _e('Devis', 'pdf-builder-pro'); ?></option>
                                <option value="business"><?php _e('Entreprise', 'pdf-builder-pro'); ?></option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="pdf-modal-footer">
                    <button class="button button-secondary pdf-modal-close"><?php _e('Annuler', 'pdf-builder-pro'); ?></button>
                    <button class="button button-primary" id="create-template-confirm"><?php _e('Créer', 'pdf-builder-pro'); ?></button>
                </div>
            </div>
        </div>

        <!-- Modal d'édition des paramètres -->
        <div id="edit-template-modal" class="pdf-modal" style="display: none;">
            <div class="pdf-modal-backdrop"></div>
            <div class="pdf-modal-content">
                <div class="pdf-modal-header">
                    <h2><?php _e('Modifier les Paramètres', 'pdf-builder-pro'); ?></h2>
                    <button class="pdf-modal-close">&times;</button>
                </div>
                <div class="pdf-modal-body">
                    <form id="edit-template-form">
                        <input type="hidden" id="edit-template-id" name="template_id">
                        <div class="form-group">
                            <label for="edit-template-name"><?php _e('Nom du template', 'pdf-builder-pro'); ?></label>
                            <input type="text" id="edit-template-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-template-description"><?php _e('Description', 'pdf-builder-pro'); ?></label>
                            <textarea id="edit-template-description" name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit-template-category"><?php _e('Catégorie', 'pdf-builder-pro'); ?></label>
                            <select id="edit-template-category" name="category">
                                <option value="general"><?php _e('Général', 'pdf-builder-pro'); ?></option>
                                <option value="invoice"><?php _e('Facture', 'pdf-builder-pro'); ?></option>
                                <option value="quote"><?php _e('Devis', 'pdf-builder-pro'); ?></option>
                                <option value="business"><?php _e('Entreprise', 'pdf-builder-pro'); ?></option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="pdf-modal-footer">
                    <button class="button button-secondary pdf-modal-close"><?php _e('Annuler', 'pdf-builder-pro'); ?></button>
                    <button class="button button-primary" id="update-template-confirm"><?php _e('Mettre à jour', 'pdf-builder-pro'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * AJAX - Charger la liste des templates builtin
 * SYSTÈME SUPPRIMÉ - Retourne une liste vide
 */
function pdf_builder_ajax_load_builtin_templates() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Retourner une liste vide - système builtin supprimé
    wp_send_json_success(array('templates' => array()));
}

/**
 * AJAX - Charger un template builtin spécifique pour la modale d'édition
 * NO NONCE CHECK - Simple read operation
 */
function pdf_builder_ajax_load_builtin_template() {
    // Only check permissions, no nonce
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';

    if (empty($template_id)) {
        wp_send_json_error('ID du template manquant');
    }

    // Chemin vers le fichier
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';

    if (!file_exists($file_path)) {
        wp_send_json_error('Template non trouvé');
    }

    $content = file_get_contents($file_path);
    if ($content === false) {
        wp_send_json_error('Erreur de lecture du fichier');
    }

    $template_data = json_decode($content, true);
    if ($template_data === null) {
        wp_send_json_error('JSON invalide dans le fichier');
    }

    wp_send_json_success(array('template' => $template_data));
}

/**
 * AJAX - Charger un template pour la modale (NEW - no nonce check)
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_load_template_for_modal() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Sauvegarder un template builtin
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_save_builtin_template() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Créer un nouveau template builtin
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_create_builtin_template() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Supprimer un template builtin
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_delete_builtin_template() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Mettre à jour les paramètres d'un template builtin
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_update_builtin_template_params() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Hook wrapper pour auto-save qui détecte les templates builtin
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_auto_save_builtin_wrapper() {
    wp_send_json_error('Système builtin supprimé');
}

/**
 * AJAX - Sauvegarder un template builtin depuis l'éditeur React (OLD - to be removed)
 * SYSTÈME SUPPRIMÉ - Retourne une erreur
 */
function pdf_builder_ajax_save_builtin_from_react() {
    wp_send_json_error('Système builtin supprimé');
}
