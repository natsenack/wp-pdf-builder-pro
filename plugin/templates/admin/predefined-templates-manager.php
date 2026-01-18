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
class PDF_Builder_Predefined_Templates_Manager
{
    private $templates_dir;
    public function __construct()
    {
        $this->templates_dir = plugin_dir_path(dirname(__FILE__)) . 'predefined/';
// Créer le dossier s'il n'existe pas
        if (!file_exists($this->templates_dir)) {
            wp_mkdir_p($this->templates_dir);
        }
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('wp_ajax_pdf_builder_save_predefined_template', [$this, 'ajaxSavePredefinedTemplate']);
        add_action('wp_ajax_pdf_builder_load_predefined_template', [$this, 'ajaxLoadPredefinedTemplate']);
        add_action('wp_ajax_pdf_builder_delete_predefined_template', [$this, 'ajaxDeletePredefinedTemplate']);
        add_action('wp_ajax_pdf_builder_generate_template_preview', [$this, 'ajaxGenerateTemplatePreview']);
        add_action('wp_ajax_pdf_builder_refresh_nonce', [$this, 'ajaxRefreshNonce']);
// Paramètres développeur
        add_action('wp_ajax_pdf_builder_developer_auth', [$this, 'ajaxDeveloperAuth']);
        add_action('wp_ajax_pdf_builder_developer_logout', [$this, 'ajaxDeveloperLogout']);
    }
    /**
     * Ajouter le menu admin pour les modèles prédéfinis
     * Le menu n'est visible que si le Mode Développeur est activé
     */
    public function addAdminMenu()
    {
        // Menu déplacé dans PDF_Builder_Admin.php pour contrôler l'ordre
        // Vérifier si le Mode Développeur est activé
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        
        // N'ajouter le menu que si le Mode Développeur est activé
        if (!empty($settings['pdf_builder_developer_enabled'])) {
            // Menu maintenant géré par PDF_Builder_Admin.php
            /*
            add_submenu_page(
                'pdf-builder-pro',
                __('Galerie de Modèles - PDF Builder Pro', 'pdf-builder-pro'),
                __('🖼️ Galerie', 'pdf-builder-pro'),
                'manage_options',
                'pdf-builder-predefined-templates',
                [$this, 'renderAdminPage']
            );
            */
        }
    }
    /**
     * Enqueue scripts et styles pour la page admin
     */
    public function enqueueAdminScripts($hook)
    {
        // Load only on predefined templates page
        if ($hook !== 'pdf-builder_page_pdf-builder-predefined-templates') {
            return;
        }
        wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/codemirror.min.js', [], '6.0.1', true);
        wp_enqueue_script('codemirror-mode-javascript', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/mode/javascript/javascript.min.js', ['codemirror'], '6.0.1', true);
        wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.0.1/codemirror.min.css', [], '6.0.1');
        
        // Styles personnalisés
        wp_enqueue_style('pdf-builder-predefined-templates', PDF_BUILDER_PRO_ASSETS_URL . 'css/predefined-templates.css', [], '1.1.0');
        
        // Scripts personnalisés - seulement si le fichier existe
        $predefined_templates_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/predefined-templates.js';
        if (file_exists($predefined_templates_js)) {
            wp_enqueue_script('pdf-builder-predefined-templates', PDF_BUILDER_PRO_ASSETS_URL . 'js/predefined-templates.js', ['jquery', 'codemirror'], '1.1.0', true);
            
            // Localize script seulement si chargé
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
        }
// Script pour gérer les paramètres URL (pour création automatique de template) - DÉPLACÉ VERS settings-main.php
    }
    /**
     * Enregistrer les paramètres développeur
     */
    public function registerDeveloperSettings()
    {
        // Cette méthode est maintenant gérée dans settings-page.php
    }
    /**
     * Callback pour la section développeur
     */
    public function developerSettingsSectionCallback()
    {
        // Cette méthode est maintenant gérée dans settings-page.php
    }
    /**
     * Callback pour le champ activation développeur
     */
    public function developerEnabledFieldCallback()
    {
        // Cette méthode est maintenant gérée dans settings-page.php
    }
    /**
     * Callback pour le champ mot de passe développeur
     */
    public function developerPasswordFieldCallback()
    {
        // Cette méthode est maintenant gérée dans settings-page.php
    }
    /**
     * Vérifier si l'utilisateur est authentifié en mode développeur
     */
    private function isDeveloperAuthenticated()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        if (empty($settings['pdf_builder_developer_enabled'])) {
            return false;
        }
        // Vérifier l'authentification développeur stockée en option
        $user_id = get_current_user_id();
        $dev_auth_key = 'pdf_builder_dev_auth_' . $user_id;
        $auth_data = get_option($dev_auth_key, array());
        
        // Vérifier si l'authentification a expiré (1 heure)
        if (!empty($auth_data['timestamp']) && (time() - $auth_data['timestamp']) < 3600) {
            return true;
        }
        
        // Nettoyer si expiré
        if (!empty($auth_data)) {
            delete_option($dev_auth_key);
        }
        
        return false;
    }
    /**
     * AJAX - Authentification développeur
     */
    public function ajaxDeveloperAuth()
    {
        try {
            
            
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                
                wp_send_json_error('Permissions insuffisantes');
            }
            

            // Vérifier le nonce
            if (!isset($_POST['nonce'])) {
                
                wp_send_json_error('Nonce manquant');
            }
            
            if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_developer_auth')) {
                
                wp_send_json_error('Vérification de sécurité échouée');
            }
            

            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            
            
            if (empty($settings['pdf_builder_developer_enabled'])) {

                wp_send_json_error('Mode développeur désactivé');
            }
            

            // Récupérer et sanitizer le mot de passe
            $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
            $stored_password = isset($settings['pdf_builder_developer_password']) ? $settings['pdf_builder_developer_password'] : '';

            // Fallback: si aucun mot de passe n'est stocké, utiliser une clé par défaut
            if (empty($stored_password)) {
                $default_password = '03T17h#20X!20@02_@31/?';
                
                $stored_password = $default_password;
            }

            // Debug: log DÉTAILLÉ pour troubleshooting
            
            
            
            
            
            
            

            // Vérifier le mot de passe (comparaison stricte)
            if (empty($password)) {
                
                wp_send_json_error('Veuillez entrer un mot de passe');
            }

            // Comparaison avec trim() pour enlever les espaces
            $password_trimmed = trim($password);
            $stored_password_trimmed = trim($stored_password);
            
            
            

            if ($password_trimmed !== $stored_password_trimmed) {
                
                
                wp_send_json_error('Mot de passe incorrect');
            }

            // Sauvegarder l'authentification en option WordPress
            $user_id = get_current_user_id();
            $dev_auth_key = 'pdf_builder_dev_auth_' . $user_id;
            $auth_data = [
                'authenticated' => true,
                'timestamp' => time()
            ];
            update_option($dev_auth_key, $auth_data);
            
            
            wp_send_json_success(['message' => 'Authentification réussie']);
        } catch (Exception $e) {
            
            
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
    /**
     * AJAX - Déconnexion développeur
     */
    public function ajaxDeveloperLogout()
    {
        try {
            $user_id = get_current_user_id();
            $dev_auth_key = 'pdf_builder_dev_auth_' . $user_id;
            delete_option($dev_auth_key);
            wp_send_json_success(['message' => 'Déconnexion réussie']);
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
    /**
     * Rendre la page admin
     */
    public function renderAdminPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }
        // Vérifier l'authentification développeur
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        if (empty($settings['pdf_builder_developer_enabled'])) {
            ?>
            <div class="wrap">
                <h1><?php _e('🔒 Accès Restreint', 'pdf-builder-pro'); ?></h1>
                <div class="notice notice-warning">
                    <p><?php _e('Le mode développeur n\'est pas activé. Allez dans <strong>PDF Builder → ⚙️ Paramètres → Mode Développeur</strong> pour l\'activer.', 'pdf-builder-pro'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        if (!$this->isDeveloperAuthenticated()) {
            $this->renderDeveloperLoginForm();
            return;
        }
        $templates = $this->getPredefinedTemplates();
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
            <div style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-pack: justify; -webkit-justify-content: space-between; -moz-box-pack: justify; -ms-flex-pack: justify; justify-content: space-between; -webkit-box-align: center; -webkit-align-items: center; -moz-box-align: center; -ms-flex-align: center; align-items: center; margin-bottom: 20px;">
                <h1 style="margin: 0;"><?php _e('📝 Gestion des Modèles Prédéfinis', 'pdf-builder-pro'); ?></h1>
                <button id="developer-logout-btn" class="button button-secondary" style="background: #dc3545; border-color: #dc3545; color: white;">
                    🚪 <?php _e('Déconnexion Développeur', 'pdf-builder-pro'); ?>
                </button>
            </div>
            <div class="pdf-builder-predefined-container">
                <!-- Liste des modèles -->
                <div class="templates-list-section">
                    <h2><?php _e('🎨 Galerie de Modèles Prédéfinis', 'pdf-builder-pro'); ?></h2>
                    <div class="templates-actions">
                        <button id="new-template-btn" class="button button-primary">
                            ➕ <?php _e('Nouveau Modèle', 'pdf-builder-pro'); ?>
                        </button>
                        <button id="refresh-templates-btn" class="button button-secondary">
                            🔄 <?php _e('Actualiser', 'pdf-builder-pro'); ?>
                        </button>
                    </div>
                    <div id="templates-list" class="templates-list">
                        <?php if (empty($templates)) :
                            ?>
                            <div class="no-templates">
                                <p><?php _e('Aucun modèle prédéfini trouvé.', 'pdf-builder-pro'); ?></p>
                                <p><?php _e('Cliquez sur "Nouveau Modèle" pour créer votre premier modèle.', 'pdf-builder-pro'); ?></p>
                            </div>
                            <?php
                        else :
                            ?>
                            <?php foreach ($templates as $template) :
                                ?>
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
                                    <div class="template-json">
                                        <details>
                                            <summary><?php _e('📄 Voir le JSON', 'pdf-builder-pro'); ?></summary>
                                            <pre><?php echo esc_html(wp_json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                        </details>
                                    </div>
                                    <div class="template-preview">
                                        <?php if (!empty($template['preview_svg'])) :
                                            ?>
                                            <img src="data:image/svg+xml;base64,<?php echo base64_encode($template['preview_svg']); ?>" alt="Aperçu" />
                                            <div class="preview-actions">
                                                <button class="regenerate-preview button button-small" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                                    🔄 <?php _e('Régénérer Aperçu', 'pdf-builder-pro'); ?>
                                                </button>
                                            </div>
                                            <?php
                                        else :
                                            ?>
                                            <div class="no-preview">
                                                <button class="generate-preview button button-small" data-slug="<?php echo esc_attr($template['slug']); ?>">
                                                    🎨 <?php _e('Générer Aperçu', 'pdf-builder-pro'); ?>
                                                </button>
                                            </div>
                                            <?php
                                        endif; ?>
                                    </div>
                                </div>
                                <?php
                            endforeach; ?>
                            <?php
                        endif; ?>
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
                                <!-- Catégories désactivées temporairement -->
                                <!-- <option value="commande"><?php _e('Bon de commande', 'pdf-builder-pro'); ?></option> -->
                                <!-- <option value="contrat"><?php _e('Contrat', 'pdf-builder-pro'); ?></option> -->
                                <!-- <option value="newsletter"><?php _e('Newsletter', 'pdf-builder-pro'); ?></option> -->
                                <!-- <option value="autre"><?php _e('Autre', 'pdf-builder-pro'); ?></option> -->
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
                                🚪 <?php _e('Fermer', 'pdf-builder-pro'); ?>
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
     * Rendre le formulaire de connexion développeur
     */
    private function renderDeveloperLoginForm()
    {
        ?>
        <style>
        .developer-login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .developer-login-container h1 {
            color: #23282d;
            margin-bottom: 10px;
        }
        .developer-login-container .description {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .developer-login-form .form-row {
            margin-bottom: 20px;
            text-align: left;
        }
        .developer-login-form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #23282d;
        }
        .developer-login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .developer-login-form .button {
            width: 100%;
            padding: 12px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .developer-login-form .button:hover {
            background: #005a87;
        }
        .login-message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .login-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .login-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        <div class="wrap">
            <div class="developer-login-container">
                <h1><?php _e('🔐 Accès Développeur', 'pdf-builder-pro'); ?></h1>
                <p class="description">
                    <?php _e('Cette section est réservée aux développeurs. Entrez le mot de passe développeur pour continuer.', 'pdf-builder-pro'); ?>
                </p>
                <form id="developer-login-form" class="developer-login-form">
                    <?php wp_nonce_field('pdf_builder_developer_auth', 'nonce'); ?>
                    <!-- Champ username caché pour l'accessibilité des gestionnaires de mots de passe -->
                    <input type="text" name="username" style="display: none; visibility: hidden;" autocomplete="username" />
                    <div class="form-row">
                        <label for="developer-password"><?php _e('Mot de passe développeur', 'pdf-builder-pro'); ?></label>
                        <div style="position: relative; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-align: center; -webkit-align-items: center; -moz-box-align: center; -ms-flex-align: center; align-items: center;">
                            <input type="password" id="developer-password" name="password" required
                                   placeholder="<?php _e('Entrez le mot de passe...', 'pdf-builder-pro'); ?>" autocomplete="current-password" style="flex: 1; padding-right: 40px;" />
                            <button type="button" id="toggle-password-visibility" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; font-size: 18px; padding: 0;">
                                👁️
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="button button-primary">
                        🔓 <?php _e('Se connecter', 'pdf-builder-pro'); ?>
                    </button>
                </form>
                <div id="login-message" class="login-message"></div>
            </div>
        </div>
        <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
        <?php
    }
    /**
     * Récupérer la liste des modèles prédéfinis
     */
    private function getPredefinedTemplates()
    {
        $templates = [];
        if (!is_dir($this->templates_dir)) {
            return $templates;
        }
        $files = glob($this->templates_dir . '*.json');
        foreach ($files as $file) {
            $slug = basename($file, '.json');
            $template_data = $this->loadTemplateFromFile($slug);
            if ($template_data) {
        // Désactiver les modèles qui ne sont pas des devis ou factures
                $category = $template_data['category'] ?? 'autre';
                if (!in_array($category, ['devis', 'facture'])) {
                    continue;
                }
                $templates[] = $template_data;
            }
        }
        return $templates;
    }
    /**
     * Charger un modèle depuis un fichier
     */
    private function loadTemplateFromFile($slug)
    {
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
        $clean_data = $this->cleanTemplateJsonForPredefined($data);
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
    public function ajaxSavePredefinedTemplate()
    {
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
            $old_slug = sanitize_key($_POST['old_slug'] ?? $_GET['old_slug'] ?? '');
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
            // Vérifier si c'est un renommage (changement de slug)
            $is_rename = !empty($old_slug) && $old_slug !== $slug;
            if ($is_rename) {
            // Vérifier que l'ancien fichier existe
                $old_file_path = $this->templates_dir . $old_slug . '.json';
                if (!file_exists($old_file_path)) {
                    wp_send_json_error('Le modèle original n\'existe pas');
                }
                // Vérifier que le nouveau slug n'est pas déjà utilisé
                $new_file_path = $this->templates_dir . $slug . '.json';
                if (file_exists($new_file_path)) {
                    wp_send_json_error('Un modèle avec ce slug existe déjà');
                }
            }
            // Validation du JSON
            $json_data = json_decode($json_config, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Configuration JSON invalide: ' . json_last_error_msg());
            }
            // Nettoyer le JSON pour en faire un modèle prédéfini réutilisable
            $cleaned_data = $this->cleanTemplateJsonForPredefined($json_data);
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
// Gestion du renommage si nécessaire
            if ($is_rename) {
                $old_file_path = $this->templates_dir . $old_slug . '.json';
                $new_file_path = $this->templates_dir . $slug . '.json';
// Renommer le fichier
                if (!rename($old_file_path, $new_file_path)) {
                    wp_send_json_error('Erreur lors du renommage du fichier');
                }
                
            }
            // Sauvegarde dans le fichier
            $file_path = $this->templates_dir . $slug . '.json';
            $result = file_put_contents($file_path, wp_json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($result === false) {
                wp_send_json_error('Erreur lors de la sauvegarde du fichier');
            }
            wp_send_json_success([
                'message' => 'Modèle sauvegardé avec succès',
                'slug' => $slug,
                'renamed' => $is_rename ? $old_slug : null
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
    /**
     * AJAX - Charger un modèle prédéfini
     */
    public function ajaxLoadPredefinedTemplate()
    {
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
            
            
            $template = $this->loadTemplateFromFile($slug);
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
    public function ajaxDeletePredefinedTemplate()
    {
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
     * AJAX - Actualiser le nonce
     */
    public function ajaxRefreshNonce()
    {
        try {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }
            $fresh_nonce = wp_create_nonce('pdf_builder_predefined_templates_' . time() . '_' . wp_rand());
            wp_send_json_success(['nonce' => $fresh_nonce]);
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
    public function ajaxGenerateTemplatePreview()
    {
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
            $template = $this->loadTemplateFromFile($slug);
            if (!$template) {
                wp_send_json_error('Modèle non trouvé');
            }
            // Générer un aperçu SVG simple basé sur les éléments du template
            $json_data = json_decode($template['json'], true);
            $svg_preview = $this->generateSvgPreview($json_data);
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
    private function generateSvgPreview($config)
    {
        $width = $config['canvasWidth'] ?? 794;
        $height = $config['canvasHeight'] ?? 1123;
// Calculer les proportions pour l'aperçu (max 300x300)
        $ratio = min(300 / $width, 300 / $height);
        $preview_width = $width * $ratio;
        $preview_height = $height * $ratio;
        $svg = '<svg width="' . $preview_width . '" height="' . $preview_height . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '">';
// Fond blanc
        $svg .= '<rect width="100%" height="100%" fill="white" stroke="#ddd" stroke-width="1"/>';
// Aperçu des éléments avec rendu réel
        if (isset($config['elements']) && is_array($config['elements'])) {
            foreach ($config['elements'] as $element) {
                $type = $element['type'] ?? 'text';
                $x = $element['x'] ?? 0;
                $y = $element['y'] ?? 0;
                $w = $element['width'] ?? 100;
                $h = $element['height'] ?? 20;
                if ($type === 'text' && isset($element['content'])) {
        // Rendu réel du texte
                    $content = $element['content'];
                    $fontSize = $element['fontSize'] ?? $element['style']['fontSize'] ?? 14;
                    $color = $element['color'] ?? $element['style']['color'] ?? '#000000';
                    $fontWeight = $element['fontWeight'] ?? $element['style']['fontWeight'] ?? 'normal';
                    $textAlign = $element['style']['textAlign'] ?? 'left';
        // Convertir la taille de police pour l'aperçu (réduire proportionnellement)
                    $scaledFontSize = max(8, $fontSize * $ratio);
        // Position Y ajustée (SVG text baseline)
                    $textY = $y + ($h * 0.7);
        // Gestion de l'alignement horizontal
                    $textAnchor = 'start';
                    if ($textAlign === 'center') {
                            $textAnchor = 'middle';
                    } elseif ($textAlign === 'right') {
                                $textAnchor = 'end';
                    }
                    $textX = $x;
                    if ($textAlign === 'center') {
                        $textX = $x + ($w / 2);
                    } elseif ($textAlign === 'right') {
                        $textX = $x + $w;
                    }
                    // Limiter le texte pour l'aperçu
                    $displayText = strlen($content) > 30 ? substr($content, 0, 27) . '...' : $content;
                    $svg .= '<text x="' . $textX . '" y="' . $textY . '" text-anchor="' . $textAnchor . '" font-family="Arial, sans-serif" font-size="' . $scaledFontSize . '" font-weight="' . $fontWeight . '" fill="' . $color . '">' . htmlspecialchars($displayText) . '</text>';
                } else {
    // Rendu simplifié pour les autres types d'éléments
                    $elementStyle = $this->getElementPreviewStyle($type);
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $elementStyle['color'] . '" stroke="#ccc" stroke-width="0.5" opacity="0.8"/>';
    // Ajouter une icône pour identifier le type
                    if ($elementStyle['icon']) {
                        $iconSize = min($w, $h) * 0.4;
                        $iconX = $x + ($w / 2);
                        $iconY = $y + ($h / 2) + ($iconSize * 0.3);
                        $svg .= '<text x="' . $iconX . '" y="' . $iconY . '" text-anchor="middle" font-size="' . $iconSize . '" fill="#666">' . $elementStyle['icon'] . '</text>';
                    }
                }
            }
        }
        $svg .= '</svg>';
        return $svg;
    }
    /**
     * Obtenir le style d'aperçu pour un type d'élément
     */
    private function getElementPreviewStyle($type)
    {
        $styles = [
            // Médias
            'image' => ['color' => '#fff3e0', 'icon' => '🖼️'],
            'logo' => ['color' => '#fff3e0', 'icon' => '🏷️'],
            'company_logo' => ['color' => '#fff3e0', 'icon' => '🏷️'],
            // Données structurées
            'table' => ['color' => '#f3e5f5', 'icon' => '📊'],
            'product_table' => ['color' => '#f3e5f5', 'icon' => '📋'],
            'customer_info' => ['color' => '#e8f5e8', 'icon' => '👤'],
            'company_info' => ['color' => '#e8f5e8', 'icon' => '🏢'],
            // Formulaires
            'barcode' => ['color' => '#e1f5fe', 'icon' => '📱'],
            'qr_code' => ['color' => '#e1f5fe', 'icon' => '📱'],
            'signature' => ['color' => '#fff8e1', 'icon' => '✍️'],
            'checkbox' => ['color' => '#f3e5f5', 'icon' => '☑️'],
            // Champs de données
            'date' => ['color' => '#e8f5e8', 'icon' => '📅'],
            'number' => ['color' => '#e8f5e8', 'icon' => '🔢'],
            'currency' => ['color' => '#e8f5e8', 'icon' => '💰'],
            'email' => ['color' => '#e8f5e8', 'icon' => '📧'],
            'phone' => ['color' => '#e8f5e8', 'icon' => '📞'],
            'address' => ['color' => '#e8f5e8', 'icon' => '🏠'],
            'order_number' => ['color' => '#e8f5e8', 'icon' => '🔢'],
            'dynamic-text' => ['color' => '#e8f5e8', 'icon' => '📝'],
            // Contenu
            'mentions' => ['color' => '#fce4ec', 'icon' => '📄'],
            'document_type' => ['color' => '#fce4ec', 'icon' => '📄'],
            // Layout
            'rectangle' => ['color' => '#fce4ec', 'icon' => '▭'],
            'line' => ['color' => '#f0f0f0', 'icon' => '━'],
            'header' => ['color' => '#fce4ec', 'icon' => '📄'],
            'footer' => ['color' => '#fce4ec', 'icon' => '📄'],
            // Défaut
            'default' => ['color' => '#f5f5f5', 'icon' => '']
        ];
        return $styles[$type] ?? $styles['default'];
    }
    /**
     * Nettoie le JSON d'un template pour en faire un modèle prédéfini réutilisable
     */
    private function cleanTemplateJsonForPredefined($json_data)
    {
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
// Initialisation gérée par PDF_Builder_Admin.php - v2
// new PDF_Builder_Predefined_Templates_Manager();


