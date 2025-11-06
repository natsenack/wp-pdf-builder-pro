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
        $template_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';

        if (file_exists($template_file)) {
            $template_content = file_get_contents($template_file);
            $template_data = json_decode($template_content, true);

            if ($template_data && isset($template_data['elements'])) {
                // Stocker temporairement le template builtin dans une session/transient pour l'éditeur React
                $transient_key = 'pdf_builder_builtin_template_' . session_id() . '_' . $template_id;
                set_transient($transient_key, $template_data, 3600); // 1 heure

                // Rediriger vers l'éditeur React avec le paramètre builtin
                $redirect_url = admin_url('admin.php?page=pdf-builder-react-editor&builtin_template=' . $template_id . '&transient_key=' . $transient_key);
                wp_redirect($redirect_url);
                exit;
            }
        }
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
            <div class="template-list-item" data-template-id="<%= id %>" style="display: flex; gap: 15px; padding: 15px; border: 1px solid #ddd; margin: 10px 0; border-radius: 4px; background: #fff;">
                <div class="template-thumbnail" style="flex-shrink: 0;">
                    <div class="template-preview-mini" style="width: 80px; height: 60px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 30px;">
                        📄
                    </div>
                </div>
                <div class="template-info" style="flex: 1;">
                    <h4 style="margin: 0 0 5px 0;"><%= name %></h4>
                    <p style="margin: 0 0 5px 0; color: #666;"><%= description || 'Aucune description' %></p>
                    <small style="color: #999;"><%= category || 'general' %> | v<%= version || '1.0' %></small>
                </div>
                <div class="template-actions" style="display: flex; gap: 8px; flex-direction: column; justify-content: center;">
                    <form method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>" style="display: inline-block;">
                        <input type="hidden" name="action" value="pdf_builder_delete_builtin_template">
                        <input type="hidden" name="template_id" value="<%= id %>">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('pdf_builder_builtin_editor'); ?>">
                        <button type="submit" class="button button-small button-link-delete" style="background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; font-size: 12px;" onclick="return confirm('<?php _e('Êtes-vous sûr ?', 'pdf-builder-pro'); ?>');">🗑️ Supprimer</button>
                    </form>
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-builtin-editor&template='); ?><%= id %>" class="button button-small" style="text-align: center; background: #0073aa; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; text-decoration: none; font-size: 12px; display: inline-block;">✏️ Éditer</a>
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
 */
function pdf_builder_ajax_load_builtin_templates() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
    }

    $templates = array();

    // Chemin vers le dossier builtin
    $builtin_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/';

    if (is_dir($builtin_dir)) {
        $files = glob($builtin_dir . '*.json');

        foreach ($files as $file) {
            $filename = basename($file, '.json');
            $content = file_get_contents($file);

            if ($content !== false) {
                $template_data = json_decode($content, true);

                if ($template_data !== null && isset($template_data['name'])) {
                    $templates[] = array(
                        'id' => $filename,
                        'name' => $template_data['name'],
                        'description' => $template_data['description'] ?? '',
                        'category' => $template_data['category'] ?? 'general',
                        'elements' => $template_data['elements'] ?? array(),
                        'canvasWidth' => $template_data['canvasWidth'] ?? 794,
                        'canvasHeight' => $template_data['canvasHeight'] ?? 1123,
                        'version' => $template_data['version'] ?? '1.0'
                    );
                }
            }
        }
    }

    wp_send_json_success(array('templates' => $templates));
}

/**
 * AJAX - Charger un template builtin spécifique
 */
function pdf_builder_ajax_load_builtin_template() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
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
 * AJAX - Sauvegarder un template builtin
 */
function pdf_builder_ajax_save_builtin_template() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
    }

    $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
    $content = isset($_POST['content']) ? stripslashes($_POST['content']) : '';

    if (empty($template_id) || empty($content)) {
        wp_send_json_error('Données manquantes');
    }

    // Vérifier que le contenu n'est pas vide
    if (trim($content) === '') {
        wp_send_json_error('Le contenu JSON ne peut pas être vide');
    }

    // Valider le JSON
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_msg = json_last_error_msg();
        // Améliorer les messages d'erreur pour plus de clarté
        switch (json_last_error()) {
            case JSON_ERROR_SYNTAX:
                $error_msg = 'Erreur de syntaxe JSON (parenthèses, virgules ou guillemets manquants)';
                break;
            case JSON_ERROR_UTF8:
                $error_msg = 'Erreur d\'encodage UTF-8';
                break;
            case JSON_ERROR_DEPTH:
                $error_msg = 'Profondeur maximale atteinte (trop de niveaux imbriqués)';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error_msg = 'JSON mal formé (problème de structure)';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error_msg = 'Caractère de contrôle inattendu';
                break;
            case JSON_ERROR_UTF16:
                $error_msg = 'Erreur d\'encodage UTF-16';
                break;
        }
        wp_send_json_error('JSON invalide: ' . $error_msg);
    }

    // Vérifier que c'est un template valide
    if (!isset($data['name']) || !isset($data['elements'])) {
        wp_send_json_error('Structure du template invalide');
    }

    // Chemin vers le fichier
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';

    // Créer le dossier s'il n'existe pas
    $dir = dirname($file_path);
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    // Sauvegarder le fichier
    if (file_put_contents($file_path, $content) === false) {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }

    wp_send_json_success(array(
        'message' => 'Template sauvegardé avec succès',
        'template_id' => $template_id
    ));
}

/**
 * AJAX - Créer un nouveau template builtin
 */
function pdf_builder_ajax_create_builtin_template() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
    }

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'general';

    if (empty($name)) {
        wp_send_json_error('Le nom du template est requis');
    }

    // Générer un ID unique
    $template_id = sanitize_title($name) . '-' . time();

    // Créer la structure du template
    $template_data = array(
        'id' => $template_id,
        'name' => $name,
        'description' => $description,
        'category' => $category,
        'version' => '1.0',
        'canvasWidth' => 794,
        'canvasHeight' => 1123,
        'elements' => array(
            array(
                'id' => 'titre',
                'type' => 'text',
                'x' => 50,
                'y' => 50,
                'width' => 200,
                'height' => 40,
                'properties' => array(
                    'text' => $name,
                    'color' => '#007cba',
                    'fontSize' => 24,
                    'fontWeight' => 'bold'
                )
            )
        )
    );

    // Chemin vers le fichier
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';

    // Créer le dossier s'il n'existe pas
    $dir = dirname($file_path);
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    // Sauvegarder le fichier
    $json_content = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($file_path, $json_content) === false) {
        wp_send_json_error('Erreur lors de la création du template');
    }

    wp_send_json_success(array(
        'message' => 'Template créé avec succès',
        'template_id' => $template_id,
        'template' => $template_data
    ));
}

/**
 * AJAX - Supprimer un template builtin
 */
function pdf_builder_ajax_delete_builtin_template() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
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

    // Supprimer le fichier
    if (!unlink($file_path)) {
        wp_send_json_error('Erreur lors de la suppression');
    }

    wp_send_json_success(array(
        'message' => 'Template supprimé avec succès',
        'template_id' => $template_id
    ));
}

/**
 * AJAX - Mettre à jour les paramètres d'un template builtin
 */
function pdf_builder_ajax_update_builtin_template_params() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_builtin_editor')) {
        wp_send_json_error('Sécurité: Nonce invalide');
    }

    $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'general';

    if (empty($template_id) || empty($name)) {
        wp_send_json_error('Données manquantes');
    }

    // Chemin vers le fichier
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';

    if (!file_exists($file_path)) {
        wp_send_json_error('Template non trouvé');
    }

    // Lire le contenu actuel
    $content = file_get_contents($file_path);
    if ($content === false) {
        wp_send_json_error('Erreur de lecture du fichier');
    }

    $template_data = json_decode($content, true);
    if ($template_data === null) {
        wp_send_json_error('JSON invalide dans le fichier');
    }

    // Mettre à jour les paramètres
    $template_data['name'] = $name;
    $template_data['description'] = $description;
    $template_data['category'] = $category;

    // Sauvegarder le fichier
    $new_content = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($file_path, $new_content) === false) {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }

    wp_send_json_success(array(
        'message' => 'Paramètres mis à jour avec succès',
        'template_id' => $template_id
    ));
}

/**
 * AJAX - Sauvegarder un template builtin depuis l'éditeur React
 */
function pdf_builder_ajax_save_builtin_from_react() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error('Sécurité: Nonce invalide');
    }

    $builtin_id = isset($_POST['builtin_id']) ? sanitize_text_field($_POST['builtin_id']) : '';
    $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

    if (empty($builtin_id) || empty($template_data)) {
        wp_send_json_error('Données manquantes');
    }

    // Valider que template_data est un tableau
    if (!is_array($template_data)) {
        wp_send_json_error('Format de données invalide');
    }

    // Nettoyer les données du template
    $clean_template = array(
        'id' => $builtin_id,
        'name' => sanitize_text_field($template_data['name'] ?? 'Template sans nom'),
        'description' => sanitize_textarea_field($template_data['description'] ?? ''),
        'category' => sanitize_text_field($template_data['category'] ?? 'general'),
        'version' => sanitize_text_field($template_data['version'] ?? '1.0'),
        'canvasWidth' => intval($template_data['canvasWidth'] ?? 794),
        'canvasHeight' => intval($template_data['canvasHeight'] ?? 1123),
        'elements' => array()
    );

    // Nettoyer les éléments
    if (isset($template_data['elements']) && is_array($template_data['elements'])) {
        foreach ($template_data['elements'] as $element) {
            if (is_array($element)) {
                $clean_element = array(
                    'id' => sanitize_text_field($element['id'] ?? ''),
                    'type' => sanitize_text_field($element['type'] ?? 'text'),
                    'x' => intval($element['x'] ?? 0),
                    'y' => intval($element['y'] ?? 0),
                    'width' => intval($element['width'] ?? 100),
                    'height' => intval($element['height'] ?? 50),
                    'properties' => array()
                );

                // Nettoyer les propriétés
                if (isset($element['properties']) && is_array($element['properties'])) {
                    foreach ($element['properties'] as $key => $value) {
                        $clean_key = sanitize_text_field($key);
                        if (is_string($value)) {
                            $clean_element['properties'][$clean_key] = sanitize_text_field($value);
                        } elseif (is_numeric($value)) {
                            $clean_element['properties'][$clean_key] = $value;
                        } elseif (is_bool($value)) {
                            $clean_element['properties'][$clean_key] = $value;
                        }
                        // Ignorer les autres types
                    }
                }

                $clean_template['elements'][] = $clean_element;
            }
        }
    }

    // Chemin vers le fichier
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $builtin_id . '.json';

    // Créer le dossier s'il n'existe pas
    $dir = dirname($file_path);
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    // Encoder en JSON et sauvegarder
    $json_content = json_encode($clean_template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json_content === false) {
        wp_send_json_error('Erreur lors de l\'encodage JSON');
    }

    if (file_put_contents($file_path, $json_content) === false) {
        wp_send_json_error('Erreur lors de la sauvegarde du fichier');
    }

    wp_send_json_success(array(
        'message' => 'Template builtin sauvegardé avec succès',
        'template_id' => $builtin_id,
        'template' => $clean_template
    ));
}