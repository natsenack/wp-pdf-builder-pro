<?php
namespace PDF_Builder\Admin;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Predefined Templates Manager - PDF Builder Pro
 * Gestion des modèles prédéfinis pour la galerie
 */

class PDF_Builder_Predefined_Templates_Manager {

    private $templates_dir;

    public function __construct() {
        $this->templates_dir = plugin_dir_path(dirname(__FILE__)) . 'predefined/';

        // Créer le dossier s'il n'existe pas
        if (!file_exists($this->templates_dir)) {
            wp_mkdir_p($this->templates_dir);
        }

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_pdf_builder_save_predefined_template', [$this, 'ajax_save_predefined_template']);
        add_action('wp_ajax_pdf_builder_load_predefined_template', [$this, 'ajax_load_predefined_template']);
        add_action('wp_ajax_pdf_builder_delete_predefined_template', [$this, 'ajax_delete_predefined_template']);
        add_action('wp_ajax_pdf_builder_generate_template_preview', [$this, 'ajax_generate_template_preview']);
    }

    /**
     * Ajouter le menu admin
     */
    public function add_admin_menu() {
        add_submenu_page(
            'pdf-builder-pro',
            __('Modèles Prédéfinis', 'pdf-builder-pro'),
            __('📝 Modèles Prédéfinis', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-predefined-templates',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Enqueue scripts et styles pour la page admin
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'pdf-builder-pro_page_pdf-builder-predefined-templates') {
            return;
        }

        // CodeMirror pour l'éditeur JSON
        wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/codemirror.min.js', [], '6.0.1', true);
        wp_enqueue_script('codemirror-mode-javascript', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/mode/javascript/javascript.min.js', ['codemirror'], '6.0.1', true);
        wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/codemirror.min.css', [], '6.0.1');

        // Scripts personnalisés
        wp_enqueue_script(
            'pdf-builder-predefined-templates',
            plugins_url('templates/admin/js/predefined-templates.js', dirname(__FILE__, 2)),
            ['jquery', 'codemirror'],
            '1.0.0',
            true
        );

        // Styles personnalisés
        wp_enqueue_style(
            'pdf-builder-predefined-templates',
            plugins_url('templates/admin/css/predefined-templates.css', dirname(__FILE__, 2)),
            [],
            '1.0.0'
        );

        // Localize script
        wp_localize_script('pdf-builder-predefined-templates', 'pdfBuilderPredefined', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_predefined_templates'),
            'strings' => [
                'confirmDelete' => __('Êtes-vous sûr de vouloir supprimer ce modèle ?', 'pdf-builder-pro'),
                'saveSuccess' => __('Modèle sauvegardé avec succès !', 'pdf-builder-pro'),
                'deleteSuccess' => __('Modèle supprimé avec succès !', 'pdf-builder-pro'),
                'loadError' => __('Erreur lors du chargement du modèle.', 'pdf-builder-pro'),
                'saveError' => __('Erreur lors de la sauvegarde.', 'pdf-builder-pro'),
                'deleteError' => __('Erreur lors de la suppression.', 'pdf-builder-pro'),
                'invalidJson' => __('JSON invalide. Veuillez vérifier la syntaxe.', 'pdf-builder-pro'),
                'previewError' => __('Erreur lors de la génération de l\'aperçu.', 'pdf-builder-pro')
            ]
        ]);

        // Script pour gérer les paramètres URL (pour création automatique de template)
        wp_add_inline_script('pdf-builder-predefined-templates', '
            jQuery(document).ready(function($) {
                // Vérifier si des paramètres URL sont présents pour création automatique
                const urlParams = new URLSearchParams(window.location.search);
                const slug = urlParams.get("slug");
                const name = urlParams.get("name");
                const category = urlParams.get("category");
                const description = urlParams.get("description");
                const icon = urlParams.get("icon");
                const json = urlParams.get("json");

                if (slug && name && category && json) {
                    // Remplir automatiquement le formulaire
                    $("#template-slug").val(slug);
                    $("#template-name").val(name);
                    $("#template-category").val(category);
                    $("#template-description").val(description);
                    $("#template-icon").val(icon || "📄");

                    // Décoder le JSON depuis l\'URL
                    try {
                        const decodedJson = decodeURIComponent(json);
                        $("#template-json").val(decodedJson);

                        // Auto-valider et sauvegarder
                        setTimeout(function() {
                            $("#save-template-btn").trigger("click");
                        }, 500);
                    } catch (e) {
                        alert("Erreur lors du décodage du JSON depuis l\'URL: " + e.message);
                    }
                }
            });
        ');
    }

    /**
     * Rendre la page admin
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        $templates = $this->get_predefined_templates();
        ?>
        <style>
        /* Styles de base inline pour la page des modèles prédéfinis */
        .pdf-builder-predefined-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .templates-list-section, .template-editor-section {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .templates-list-section { max-width: 400px; }
        .template-editor-section { max-width: 600px; }
        .templates-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        .templates-list { max-height: 600px; overflow-y: auto; }
        .template-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        .template-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .template-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .template-header h3 { margin: 0; font-size: 16px; color: #23282d; }
        .template-actions { display: flex; gap: 5px; }
        .template-meta { margin-bottom: 10px; }
        .category {
            display: inline-block;
            background: #007cba;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .description { display: block; color: #666; font-size: 13px; line-height: 1.4; }
        .template-preview { text-align: center; margin-top: 10px; }
        .template-preview img { max-width: 100%; height: auto; border-radius: 4px; }
        .no-preview {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-style: italic;
        }
        .form-row { margin-bottom: 20px; }
        .form-row label { display: block; font-weight: bold; margin-bottom: 5px; color: #23282d; }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .json-editor-container {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .json-editor-container textarea {
            border: none;
            border-radius: 0;
            margin: 0;
            padding: 12px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.4;
            resize: none;
            width: 100%;
            min-height: 300px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .no-templates {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .pdf-builder-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #fff;
            border-radius: 8px;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        .modal-header h3 { margin: 0; }
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .modal-body { padding: 20px; }
        </style>
        <div class="wrap">
            <h1><?php _e('📝 Gestion des Modèles Prédéfinis', 'pdf-builder-pro'); ?></h1>

            <div class="pdf-builder-predefined-container">
                <!-- Liste des modèles -->
                <div class="templates-list-section">
                    <h2><?php _e('Modèles Existants', 'pdf-builder-pro'); ?></h2>

                    <div class="templates-actions">
                        <button id="new-template-btn" class="button button-primary">
                            ➕ <?php _e('Nouveau Modèle', 'pdf-builder-pro'); ?>
                        </button>
                        <button id="refresh-templates-btn" class="button button-secondary">
                            🔄 <?php _e('Actualiser', 'pdf-builder-pro'); ?>
                        </button>
                    </div>

                    <div id="templates-list" class="templates-list">
                        <?php if (empty($templates)): ?>
                            <div class="no-templates">
                                <p><?php _e('Aucun modèle prédéfini trouvé.', 'pdf-builder-pro'); ?></p>
                                <p><?php _e('Cliquez sur "Nouveau Modèle" pour créer votre premier modèle.', 'pdf-builder-pro'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <div class="template-item" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                    <div class="template-header">
                                        <h3><?php echo esc_html($template['name']); ?></h3>
                                        <div class="template-actions">
                                            <button class="button button-small edit-template" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                                ✏️ <?php _e('Éditer', 'pdf-builder-pro'); ?>
                                            </button>
                                            <button class="button button-small button-danger delete-template" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                                🗑️ <?php _e('Supprimer', 'pdf-builder-pro'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="template-meta">
                                        <span class="category"><?php echo esc_html($template['category']); ?></span>
                                        <span class="description"><?php echo esc_html($template['description']); ?></span>
                                    </div>
                                    <div class="template-preview">
                                        <?php if (!empty($template['preview_svg'])): ?>
                                            <img src="data:image/svg+xml;base64,<?php echo base64_encode($template['preview_svg']); ?>" alt="Aperçu" />
                                        <?php else: ?>
                                            <div class="no-preview">
                                                <button class="generate-preview button button-small" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                                    🎨 <?php _e('Générer Aperçu', 'pdf-builder-pro'); ?>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Éditeur de modèle -->
                <div class="template-editor-section">
                    <h2 id="editor-title"><?php _e('Éditeur de Modèle', 'pdf-builder-pro'); ?></h2>

                    <form id="template-form">
                        <div class="form-row">
                            <label for="template-slug"><?php _e('Slug du modèle', 'pdf-builder-pro'); ?> *</label>
                            <input type="text" id="template-slug" name="slug" required
                                   placeholder="ex: facture-moderne" />
                            <small><?php _e('Identifiant unique (lettres minuscules, tirets)', 'pdf-builder-pro'); ?></small>
                        </div>

                        <div class="form-row">
                            <label for="template-name"><?php _e('Nom du modèle', 'pdf-builder-pro'); ?> *</label>
                            <input type="text" id="template-name" name="name" required
                                   placeholder="ex: Facture Moderne" />
                        </div>

                        <div class="form-row">
                            <label for="template-category"><?php _e('Catégorie', 'pdf-builder-pro'); ?> *</label>
                            <select id="template-category" name="category" required>
                                <option value="facture"><?php _e('Facture', 'pdf-builder-pro'); ?></option>
                                <option value="devis"><?php _e('Devis', 'pdf-builder-pro'); ?></option>
                                <option value="commande"><?php _e('Bon de commande', 'pdf-builder-pro'); ?></option>
                                <option value="contrat"><?php _e('Contrat', 'pdf-builder-pro'); ?></option>
                                <option value="newsletter"><?php _e('Newsletter', 'pdf-builder-pro'); ?></option>
                                <option value="autre"><?php _e('Autre', 'pdf-builder-pro'); ?></option>
                            </select>
                        </div>

                        <div class="form-row">
                            <label for="template-description"><?php _e('Description', 'pdf-builder-pro'); ?> *</label>
                            <textarea id="template-description" name="description" rows="3" required
                                      placeholder="Description du modèle..."></textarea>
                        </div>

                        <div class="form-row">
                            <label for="template-icon"><?php _e('Icône (emoji)', 'pdf-builder-pro'); ?></label>
                            <input type="text" id="template-icon" name="icon" placeholder="ex: 🧾" />
                        </div>

                        <div class="form-row">
                            <label for="template-json"><?php _e('Configuration JSON', 'pdf-builder-pro'); ?> *</label>
                            <div class="json-editor-container">
                                <textarea id="template-json" name="json" rows="20" required
                                          placeholder='{
  "elements": [],
  "canvasWidth": 794,
  "canvasHeight": 1123,
  "version": "1.0"
}'></textarea>
                            </div>
                            <small><?php _e('Collez ici le JSON exporté depuis l\'éditeur de templates', 'pdf-builder-pro'); ?></small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" id="save-template-btn" class="button button-primary">
                                💾 <?php _e('Sauvegarder', 'pdf-builder-pro'); ?>
                            </button>
                            <button type="button" id="cancel-edit-btn" class="button button-secondary">
                                ❌ <?php _e('Annuler', 'pdf-builder-pro'); ?>
                            </button>
                            <button type="button" id="validate-json-btn" class="button button-secondary">
                                ✅ <?php _e('Valider JSON', 'pdf-builder-pro'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modale d'aperçu -->
        <div id="preview-modal" class="pdf-builder-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php _e('Aperçu du Modèle', 'pdf-builder-pro'); ?></h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="preview-container"></div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Récupérer la liste des modèles prédéfinis
     */
    private function get_predefined_templates() {
        $templates = [];

        if (!is_dir($this->templates_dir)) {
            return $templates;
        }

        $files = glob($this->templates_dir . '*.json');
        foreach ($files as $file) {
            $slug = basename($file, '.json');
            $template_data = $this->load_template_from_file($slug);

            if ($template_data) {
                $templates[] = $template_data;
            }
        }

        return $templates;
    }

    /**
     * Charger un modèle depuis un fichier
     */
    private function load_template_from_file($slug) {
        $file_path = $this->templates_dir . $slug . '.json';

        if (!file_exists($file_path)) {
            return false;
        }

        $content = file_get_contents($file_path);
        $data = json_decode($content, true);

        if (!$data || !isset($data['name'])) {
            return false;
        }

        // Nettoyer les données pour s'assurer qu'elles sont dans le bon format
        $clean_data = $this->clean_template_json_for_predefined($data);

        return [
            'slug' => $slug,
            'name' => $data['name'] ?? '',
            'category' => $data['category'] ?? 'autre',
            'description' => $data['description'] ?? '',
            'icon' => $data['icon'] ?? '📄',
            'json' => wp_json_encode($clean_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'preview_svg' => $data['preview_svg'] ?? ''
        ];
    }

    /**
     * AJAX - Sauvegarder un modèle prédéfini
     */
    public function ajax_save_predefined_template() {
        try {
            // Vérifications de sécurité
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérifier le nonce (obligatoire pour POST, optionnel pour GET depuis URL)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                check_ajax_referer('pdf_builder_predefined_templates', 'nonce');
            }

            // Récupération des données (POST en priorité, GET en fallback pour gros JSON)
            $slug = sanitize_key($_POST['slug'] ?? $_GET['slug'] ?? '');
            $name = sanitize_text_field($_POST['name'] ?? $_GET['name'] ?? '');
            $category = sanitize_key($_POST['category'] ?? $_GET['category'] ?? '');
            $description = sanitize_textarea_field($_POST['description'] ?? $_GET['description'] ?? '');
            $icon = sanitize_text_field($_POST['icon'] ?? $_GET['icon'] ?? '📄');
            $json_config = stripslashes($_POST['json'] ?? $_GET['json'] ?? '');

            // Validation
            if (empty($slug) || empty($name) || empty($category) || empty($json_config)) {
                wp_send_json_error('Tous les champs obligatoires doivent être remplis');
            }

            // Validation du JSON
            $json_data = json_decode($json_config, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Configuration JSON invalide: ' . json_last_error_msg());
            }

            // Nettoyer le JSON pour en faire un modèle prédéfini réutilisable
            $cleaned_data = $this->clean_template_json_for_predefined($json_data);

            // Le JSON nettoyé contient déjà la structure complète (template + elements)
            // On l'utilise directement comme contenu du fichier
            $template_data = $cleaned_data;

            // Ajouter les métadonnées du modèle prédéfini
            $template_data['name'] = $name;
            $template_data['category'] = $category;
            $template_data['description'] = $description;
            $template_data['icon'] = $icon;
            $template_data['created_at'] = current_time('mysql');
            $template_data['updated_at'] = current_time('mysql');

            // Sauvegarde dans le fichier
            $file_path = $this->templates_dir . $slug . '.json';
            $result = file_put_contents($file_path, wp_json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($result === false) {
                wp_send_json_error('Erreur lors de la sauvegarde du fichier');
            }

            wp_send_json_success([
                'message' => 'Modèle sauvegardé avec succès',
                'slug' => $slug
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Charger un modèle prédéfini
     */
    public function ajax_load_predefined_template() {
        try {
            // Vérifications de sécurité
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            check_ajax_referer('pdf_builder_predefined_templates', 'nonce');

            $slug = sanitize_key($_POST['slug'] ?? '');

            if (empty($slug)) {
                wp_send_json_error('Slug du modèle manquant');
            }

            $template = $this->load_template_from_file($slug);

            if (!$template) {
                wp_send_json_error('Modèle non trouvé');
            }

            wp_send_json_success($template);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Supprimer un modèle prédéfini
     */
    public function ajax_delete_predefined_template() {
        try {
            // Vérifications de sécurité
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            check_ajax_referer('pdf_builder_predefined_templates', 'nonce');

            $slug = sanitize_key($_POST['slug'] ?? '');

            if (empty($slug)) {
                wp_send_json_error('Slug du modèle manquant');
            }

            $file_path = $this->templates_dir . $slug . '.json';

            if (!file_exists($file_path)) {
                wp_send_json_error('Modèle non trouvé');
            }

            if (!unlink($file_path)) {
                wp_send_json_error('Erreur lors de la suppression du fichier');
            }

            wp_send_json_success([
                'message' => 'Modèle supprimé avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Générer un aperçu SVG du modèle
     */
    public function ajax_generate_template_preview() {
        try {
            // Vérifications de sécurité
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            check_ajax_referer('pdf_builder_predefined_templates', 'nonce');

            $slug = sanitize_key($_POST['slug'] ?? '');

            if (empty($slug)) {
                wp_send_json_error('Slug du modèle manquant');
            }

            $template = $this->load_template_from_file($slug);

            if (!$template) {
                wp_send_json_error('Modèle non trouvé');
            }

            // Générer un aperçu SVG simple basé sur les éléments du template
            $json_data = json_decode($template['json'], true);
            $svg_preview = $this->generate_svg_preview($json_data['config']);

            // Mettre à jour le fichier avec l'aperçu
            $json_data['preview_svg'] = $svg_preview;
            $file_path = $this->templates_dir . $slug . '.json';
            file_put_contents($file_path, wp_json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            wp_send_json_success([
                'message' => 'Aperçu généré avec succès',
                'preview_svg' => $svg_preview
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Générer un aperçu SVG simple du template
     */
    private function generate_svg_preview($config) {
        $width = $config['canvasWidth'] ?? 794;
        $height = $config['canvasHeight'] ?? 1123;

        // Calculer les proportions pour l'aperçu (max 200x200)
        $ratio = min(200 / $width, 200 / $height);
        $preview_width = $width * $ratio;
        $preview_height = $height * $ratio;

        $svg = '<svg width="' . $preview_width . '" height="' . $preview_height . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '">';

        // Fond blanc
        $svg .= '<rect width="100%" height="100%" fill="white" stroke="#ddd" stroke-width="1"/>';

        // Aperçu simple des éléments
        if (isset($config['elements']) && is_array($config['elements'])) {
            foreach ($config['elements'] as $element) {
                $x = $element['x'] ?? 0;
                $y = $element['y'] ?? 0;
                $w = $element['width'] ?? 100;
                $h = $element['height'] ?? 20;

                // Couleur basée sur le type d'élément
                $color = '#e3f2fd';
                switch ($element['type'] ?? 'text') {
                    case 'text': $color = '#e8f5e8'; break;
                    case 'image': $color = '#fff3e0'; break;
                    case 'table': $color = '#f3e5f5'; break;
                    case 'rectangle': $color = '#fce4ec'; break;
                }

                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $color . '" stroke="#ccc" stroke-width="0.5" opacity="0.7"/>';
            }
        }

        // Texte "APERÇU"
        $svg .= '<text x="' . ($width/2) . '" y="' . ($height/2) . '" text-anchor="middle" dominant-baseline="middle" font-family="Arial" font-size="12" fill="#666">APERÇU</text>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Nettoie le JSON d'un template pour en faire un modèle prédéfini réutilisable
     */
    private function clean_template_json_for_predefined($json_data) {
        // Supprimer les propriétés spécifiques à la session d'édition
        $session_properties = ['id', 'isNew', 'isModified', 'isSaving', 'lastSaved'];
        foreach ($session_properties as $prop) {
            unset($json_data[$prop]);
        }

        // Nettoyer les éléments
        if (isset($json_data['elements']) && is_array($json_data['elements'])) {
            foreach ($json_data['elements'] as &$element) {
                // Supprimer les propriétés spécifiques à la session
                $element_session_props = ['createdAt', 'updatedAt'];
                foreach ($element_session_props as $prop) {
                    unset($element[$prop]);
                }

                // Régénérer l'ID de l'élément pour éviter les conflits lors de la réutilisation
                // Garder le préfixe "element_" mais régénérer la partie aléatoire
                if (isset($element['id'])) {
                    $timestamp = time();
                    $random = substr(md5(uniqid('', true)), 0, 10);
                    $element['id'] = 'element_' . $timestamp . '_' . $random;
                }

                // S'assurer que les propriétés essentielles sont présentes avec des valeurs par défaut
                $element['visible'] = $element['visible'] ?? true;
                $element['locked'] = $element['locked'] ?? false;
                $element['opacity'] = $element['opacity'] ?? 100;
                $element['rotation'] = $element['rotation'] ?? 0;
                $element['scale'] = $element['scale'] ?? 100;
            }
        }

        // Ajouter des propriétés de template par défaut si elles n'existent pas
        $json_data['canvasWidth'] = $json_data['canvasWidth'] ?? 794;
        $json_data['canvasHeight'] = $json_data['canvasHeight'] ?? 1123;
        $json_data['version'] = $json_data['version'] ?? '1.0';

        return $json_data;
    }
}

// Initialiser le gestionnaire
new PDF_Builder_Predefined_Templates_Manager();