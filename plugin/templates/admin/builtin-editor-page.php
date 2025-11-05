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

        <div class="pdf-builder-builtin-editor">
            <!-- Barre d'outils -->
            <div class="editor-toolbar">
                <button id="new-template-btn" class="button button-primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e('Nouveau Template', 'pdf-builder-pro'); ?>
                </button>
                <button id="save-template-btn" class="button button-primary">
                    <span class="dashicons dashicons-save"></span>
                    <?php _e('Sauvegarder', 'pdf-builder-pro'); ?>
                </button>
                <button id="delete-template-btn" class="button button-secondary" disabled>
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Supprimer', 'pdf-builder-pro'); ?>
                </button>
                <div class="template-info">
                    <span id="current-template-name"><?php _e('Aucun template sélectionné', 'pdf-builder-pro'); ?></span>
                </div>
            </div>

            <div class="editor-layout">
                <!-- Liste des templates -->
                <div class="templates-list-panel">
                    <h3><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h3>
                    <div id="templates-list" class="templates-list">
                        <!-- Les templates seront chargés ici -->
                    </div>
                </div>

                <!-- Éditeur JSON -->
                <div class="json-editor-panel">
                    <h3><?php _e('Éditeur JSON', 'pdf-builder-pro'); ?></h3>
                    <div id="json-editor-container">
                        <div id="json-editor"></div>
                    </div>
                    <div class="json-validation">
                        <span id="json-status" class="json-status-valid">
                            <span class="dashicons dashicons-yes"></span>
                            <?php _e('JSON valide', 'pdf-builder-pro'); ?>
                        </span>
                    </div>
                </div>

                <!-- Aperçu -->
                <div class="preview-panel">
                    <h3><?php _e('Aperçu', 'pdf-builder-pro'); ?></h3>
                    <div class="preview-container">
                        <div id="template-preview" class="template-preview">
                            <!-- L'aperçu sera rendu ici -->
                        </div>
                    </div>
                    <div class="preview-info">
                        <small><?php _e('Dimensions: 794x1123px (A4)', 'pdf-builder-pro'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template pour les éléments de la liste -->
        <script type="text/template" id="template-list-item-template">
            <div class="template-list-item" data-template-id="<%= id %>">
                <div class="template-thumbnail">
                    <div class="template-preview-mini" style="width: 80px; height: 60px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;">
                        <!-- Mini aperçu -->
                    </div>
                </div>
                <div class="template-info">
                    <h4><%= name %></h4>
                    <p><%= description %></p>
                    <small><%= elements ? elements.length : 0 %> éléments</small>
                </div>
            </div>
        </script>

        <!-- Template pour nouveau template -->
        <script type="text/template" id="new-template-modal-template">
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
                                <textarea id="template-description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="template-category"><?php _e('Catégorie', 'pdf-builder-pro'); ?></label>
                                <select id="template-category" name="category">
                                    <option value="general"><?php _e('Général', 'pdf-builder-pro'); ?></option>
                                    <option value="business"><?php _e('Entreprise', 'pdf-builder-pro'); ?></option>
                                    <option value="modern"><?php _e('Moderne', 'pdf-builder-pro'); ?></option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="pdf-modal-footer">
                        <button class="button" id="cancel-new-template"><?php _e('Annuler', 'pdf-builder-pro'); ?></button>
                        <button class="button button-primary" id="create-new-template"><?php _e('Créer', 'pdf-builder-pro'); ?></button>
                    </div>
                </div>
            </div>
        </script>
    </div>

    <style>
        .pdf-builder-builtin-editor {
            margin-top: 20px;
        }

        .editor-toolbar {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .editor-toolbar .button {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .template-info {
            margin-left: auto;
            font-weight: 600;
            color: #1d2327;
        }

        .editor-layout {
            display: grid;
            grid-template-columns: 300px 1fr 400px;
            gap: 20px;
            min-height: 600px;
        }

        .templates-list-panel,
        .json-editor-panel,
        .preview-panel {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }

        .templates-list-panel h3,
        .json-editor-panel h3,
        .preview-panel h3 {
            margin: 0;
            padding: 15px;
            border-bottom: 1px solid #ccd0d4;
            background: #f8f9fa;
            font-size: 14px;
            font-weight: 600;
        }

        .templates-list {
            padding: 15px;
            max-height: 500px;
            overflow-y: auto;
        }

        .template-list-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .template-list-item:hover {
            background: #f8f9fa;
            border-color: #007cba;
        }

        .template-list-item.selected {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }

        .template-thumbnail {
            margin-right: 10px;
        }

        .template-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .template-info p {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #666;
        }

        .template-info small {
            font-size: 11px;
            color: #888;
        }

        #json-editor-container {
            height: 500px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 15px;
            margin-top: 0;
        }

        #json-editor {
            width: 100%;
            height: 100%;
        }

        .json-validation {
            padding: 0 15px 15px 15px;
        }

        .json-status-valid {
            color: #46b450;
            font-size: 12px;
        }

        .json-status-invalid {
            color: #dc3232;
            font-size: 12px;
        }

        .preview-container {
            padding: 15px;
            display: flex;
            justify-content: center;
        }

        .template-preview {
            width: 794px;
            height: 1123px;
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transform: scale(0.3);
            transform-origin: top left;
            position: relative;
        }

        .preview-info {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-top: 1px solid #ccd0d4;
        }

        /* Modal styles */
        .pdf-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100000;
        }

        .pdf-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
        }

        .pdf-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }

        .pdf-modal-header {
            padding: 20px;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-modal-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .pdf-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .pdf-modal-body {
            padding: 20px;
        }

        .pdf-modal-footer {
            padding: 20px;
            border-top: 1px solid #e1e1e1;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }

        .form-group textarea {
            resize: vertical;
        }

        @media (max-width: 1200px) {
            .editor-layout {
                grid-template-columns: 250px 1fr 350px;
            }
        }

        @media (max-width: 768px) {
            .editor-layout {
                grid-template-columns: 1fr;
            }

            .templates-list-panel,
            .json-editor-panel,
            .preview-panel {
                margin-bottom: 20px;
            }
        }
    </style>
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

    wp_send_json_success(array(
        'template_id' => $template_id,
        'content' => $content
    ));
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

    // Valider le JSON
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('JSON invalide: ' . json_last_error_msg());
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
    $description = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'general';

    if (empty($name)) {
        wp_send_json_error('Nom du template requis');
    }

    // Générer un ID unique
    $template_id = sanitize_title($name);

    // Vérifier que l'ID n'existe pas déjà
    $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '.json';
    if (file_exists($file_path)) {
        // Ajouter un suffixe numérique si nécessaire
        $counter = 1;
        while (file_exists(plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/builtin/' . $template_id . '-' . $counter . '.json')) {
            $counter++;
        }
        $template_id = $template_id . '-' . $counter;
    }

    // Template de base
    $template_data = array(
        'id' => $template_id,
        'name' => $name,
        'description' => $description,
        'version' => '1.0',
        'category' => $category,
        'canvasWidth' => 794,
        'canvasHeight' => 1123,
        'elements' => array(
            array(
                'id' => 'sample-text',
                'type' => 'text',
                'x' => 50,
                'y' => 50,
                'width' => 200,
                'height' => 40,
                'properties' => array(
                    'text' => 'Nouveau Template',
                    'color' => '#007cba',
                    'fontSize' => 24,
                    'fontWeight' => 'bold'
                )
            )
        )
    );

    $content = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Sauvegarder le fichier
    if (file_put_contents($file_path, $content) === false) {
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