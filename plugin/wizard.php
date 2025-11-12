<?php
/**
 * Wizard d'installation guidé pour PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Classe du wizard d'installation
 */
class PDF_Builder_Installation_Wizard {

    private $steps = array(
        'welcome' => 'Bienvenue',
        'dependencies' => 'Vérification des dépendances',
        'company' => 'Informations entreprise',
        'template' => 'Template par défaut',
        'complete' => 'Installation terminée'
    );

    private $current_step = 'welcome';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_wizard_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_pdf_builder_wizard_step', array($this, 'handle_ajax_step'));
        add_action('admin_init', array($this, 'check_first_install'));

        // Créer les tables nécessaires au premier chargement
        $this->create_tables_if_needed();
    }

    /**
     * Vérifier si c'est une première installation
     */
    public function check_first_install() {
        if (!get_option('pdf_builder_installed') && current_user_can('manage_options')) {
            // Rediriger vers le wizard si première installation
            if (!isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-wizard') {
                wp_redirect(admin_url('admin.php?page=pdf-builder-wizard'));
                exit;
            }
        }
    }

    /**
     * Ajouter la page du wizard dans l'admin
     */
    public function add_wizard_page() {
        add_menu_page(
            'Installation PDF Builder',
            'PDF Builder Wizard',
            'manage_options',
            'pdf-builder-wizard',
            array($this, 'render_wizard_page'),
            'dashicons-admin-tools',
            2
        );
    }

    /**
     * Charger les scripts et styles
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_pdf-builder-wizard') {
            return;
        }

        wp_enqueue_style('pdf-builder-wizard', plugins_url('assets/css/wizard.css', PDF_BUILDER_PLUGIN_FILE), array(), PDF_BUILDER_VERSION);
        wp_enqueue_script('pdf-builder-wizard', plugins_url('assets/js/wizard.js', PDF_BUILDER_PLUGIN_FILE), array('jquery'), PDF_BUILDER_VERSION, true);

        wp_localize_script('pdf-builder-wizard', 'pdfBuilderWizard', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'admin_url' => admin_url('admin.php?page=pdf-builder'),
            'nonce' => wp_create_nonce('pdf_builder_wizard_nonce'),
            'strings' => array(
                'next' => 'Suivant',
                'previous' => 'Précédent',
                'finish' => 'Terminer',
                'loading' => 'Chargement...',
                'error' => 'Une erreur est survenue'
            )
        ));
    }

    /**
     * Rendre la page du wizard
     */
    public function render_wizard_page() {
        $current_step = isset($_GET['step']) ? sanitize_text_field($_GET['step']) : 'welcome';

        ?>
        <div class="wrap pdf-builder-wizard">
            <div class="wizard-header">
                <h1>Installation de PDF Builder Pro</h1>
                <div class="wizard-progress">
                    <?php $this->render_progress_bar($current_step); ?>
                </div>
            </div>

            <div class="wizard-content">
                <?php $this->render_step_content($current_step); ?>
            </div>

            <div class="wizard-footer">
                <?php $this->render_navigation($current_step); ?>
            </div>
        </div>

        <style>
        .pdf-builder-wizard { max-width: 800px; margin: 20px auto; }
        .wizard-header { text-align: center; margin-bottom: 30px; }
        .wizard-progress { margin: 20px 0; }
        .wizard-progress .step { display: inline-block; width: 18%; margin: 0 1%; text-align: center; }
        .wizard-progress .step.active { color: #007cba; font-weight: bold; }
        .wizard-progress .step.completed { color: #46b450; }
        .wizard-content { background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 4px; }
        .wizard-footer { margin-top: 30px; text-align: center; }
        .wizard-footer .button { margin: 0 10px; }
        </style>
        <?php
    }

    /**
     * Rendre la barre de progression
     */
    private function render_progress_bar($current_step) {
        $steps = array_keys($this->steps);
        $current_index = array_search($current_step, $steps);

        foreach ($this->steps as $step_key => $step_name) {
            $step_index = array_search($step_key, $steps);
            $class = 'step';

            if ($step_index < $current_index) {
                $class .= ' completed';
            } elseif ($step_index === $current_index) {
                $class .= ' active';
            }

            echo '<div class="' . esc_attr($class) . '">' . esc_html($step_name) . '</div>';
        }
    }

    /**
     * Rendre le contenu de l'étape
     */
    private function render_step_content($step) {
        switch ($step) {
            case 'welcome':
                ?>
                <h2>Bienvenue dans PDF Builder Pro !</h2>
                <p>Ce wizard va vous guider à travers l'installation et la configuration initiale de votre générateur de PDF professionnel.</p>
                <p>Nous allons :</p>
                <ul>
                    <li>Vérifier que toutes les dépendances sont installées</li>
                    <li>Configurer les informations de votre entreprise</li>
                    <li>Créer un template PDF par défaut</li>
                    <li>Tester la génération de PDF</li>
                </ul>
                <p><strong>Temps estimé : 5 minutes</strong></p>
                <?php
                break;

            case 'dependencies':
                $this->render_dependencies_step();
                break;

            case 'company':
                $this->render_company_step();
                break;

            case 'template':
                $this->render_template_step();
                break;

            case 'complete':
                $this->render_complete_step();
                break;
        }
    }

    /**
     * Étape de vérification des dépendances
     */
    private function render_dependencies_step() {
        ?>
        <h2>Vérification des dépendances</h2>
        <p>Vérifions que votre environnement est prêt pour PDF Builder Pro.</p>

        <div class="dependency-check">
            <div class="dependency-item">
                <span class="status <?php echo $this->check_woocommerce() ? 'success' : 'error'; ?>">
                    <?php echo $this->check_woocommerce() ? '✓' : '✗'; ?>
                </span>
                <span>WooCommerce</span>
                <small><?php echo $this->check_woocommerce() ? 'Activé' : 'Requis pour les fonctionnalités e-commerce'; ?></small>
            </div>

            <div class="dependency-item">
                <span class="status <?php echo $this->check_php_version() ? 'success' : 'error'; ?>">
                    <?php echo $this->check_php_version() ? '✓' : '✗'; ?>
                </span>
                <span>PHP 7.4+</span>
                <small>Version actuelle : <?php echo PHP_VERSION; ?></small>
            </div>

            <div class="dependency-item">
                <span class="status <?php echo $this->check_mysql_version() ? 'success' : 'error'; ?>">
                    <?php echo $this->check_mysql_version() ? '✓' : '✗'; ?>
                </span>
                <span>MySQL 5.6+</span>
                <small>Version actuelle : <?php echo $this->get_mysql_version(); ?></small>
            </div>
        </div>

        <style>
        .dependency-check { margin: 20px 0; }
        .dependency-item { display: flex; align-items: center; margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 4px; }
        .dependency-item .status { margin-right: 15px; font-size: 18px; }
        .dependency-item .status.success { color: #46b450; }
        .dependency-item .status.error { color: #dc3232; }
        .dependency-item small { color: #666; margin-left: auto; }
        </style>
        <?php
    }

    /**
     * Étape de configuration entreprise
     */
    private function render_company_step() {
        // Récupérer les informations WooCommerce disponibles
        $company_info = $this->get_woocommerce_company_info();

        ?>
        <h2>Informations de l'entreprise</h2>
        <p>Configurez les informations de base de votre entreprise pour les PDF.</p>
        <?php if (!empty($company_info)): ?>
        <div class="notice notice-info">
            <p><strong>ℹ️ Informations détectées automatiquement depuis WooCommerce :</strong></p>
            <p>Ces champs ont été pré-remplis avec les données de votre boutique. Vous pouvez les modifier si nécessaire.</p>
        </div>
        <?php endif; ?>

        <form id="company-form">
            <table class="form-table">
                <tr>
                    <th><label for="company_name">Nom de l'entreprise</label></th>
                    <td><input type="text" id="company_name" name="company_name" class="regular-text" value="<?php echo esc_attr($company_info['name'] ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="company_address">Adresse</label></th>
                    <td><textarea id="company_address" name="company_address" rows="3" class="regular-text"><?php echo esc_textarea($company_info['address'] ?? ''); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="company_phone">Téléphone</label></th>
                    <td><input type="tel" id="company_phone" name="company_phone" class="regular-text" value="<?php echo esc_attr($company_info['phone'] ?? ''); ?>"></td>
                </tr>
                <tr>
                    <th><label for="company_email">Email</label></th>
                    <td><input type="email" id="company_email" name="company_email" class="regular-text" value="<?php echo esc_attr($company_info['email'] ?? ''); ?>"></td>
                </tr>
                <tr>
                    <th><label for="company_logo">Logo (URL)</label></th>
                    <td>
                        <input type="url" id="company_logo" name="company_logo" class="regular-text" value="<?php echo esc_attr($company_info['logo'] ?? ''); ?>">
                        <button type="button" class="button" id="upload-logo">Choisir un logo</button>
                        <div id="logo-preview" class="logo-preview" style="margin-top: 10px; <?php echo empty($company_info['logo']) ? 'display: none;' : ''; ?>">
                            <img id="logo-preview-img" src="<?php echo esc_url($company_info['logo'] ?? ''); ?>" alt="Aperçu du logo" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px; background: #fff;">
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    /**
     * Étape de création du template
     */
    private function render_template_step() {
        ?>
        <h2>Template par défaut</h2>
        <p>Créons un template PDF de base pour vos documents.</p>

        <div class="template-preview">
            <h3>Aperçu du template</h3>
            <div class="template-structure">
                <div class="template-element">En-tête entreprise</div>
                <div class="template-element">Informations client</div>
                <div class="template-element">Tableau des produits</div>
                <div class="template-element">Totaux</div>
            </div>
        </div>

        <p><em>Le template sera créé avec des éléments de base. Vous pourrez le personnaliser plus tard dans l'éditeur.</em></p>

        <style>
        .template-preview { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 4px; }
        .template-structure { margin: 15px 0; }
        .template-element { padding: 10px; margin: 5px 0; background: #fff; border: 1px solid #ddd; border-radius: 3px; }
        </style>
        <?php
    }

    /**
     * Étape de finalisation
     */
    private function render_complete_step() {
        ?>
        <h2>Installation terminée !</h2>
        <p>Félicitations ! PDF Builder Pro est maintenant configuré et prêt à utiliser.</p>

        <div class="setup-summary">
            <h3>Résumé de l'installation</h3>
            <ul>
                <li>✓ Tables de base de données créées</li>
                <li>✓ Informations entreprise configurées</li>
                <li>✓ Template par défaut créé</li>
                <li>✓ Dépendances vérifiées</li>
            </ul>
        </div>

        <div class="next-steps">
            <h3>Prochaines étapes</h3>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=pdf-builder'); ?>">Accéder à l'éditeur PDF</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>">Gérer vos templates</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=pdf-builder-settings'); ?>">Configurer les paramètres</a></li>
            </ul>
        </div>

        <style>
        .setup-summary, .next-steps { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .next-steps ul li { margin: 5px 0; }
        .next-steps ul li a { color: #007cba; text-decoration: none; }
        .next-steps ul li a:hover { text-decoration: underline; }
        </style>
        <?php
    }

    /**
     * Rendre la navigation
     */
    private function render_navigation($current_step) {
        $steps = array_keys($this->steps);
        $current_index = array_search($current_step, $steps);
        $prev_step = $current_index > 0 ? $steps[$current_index - 1] : null;
        $next_step = $current_index < count($steps) - 1 ? $steps[$current_index + 1] : null;

        ?>
        <div class="wizard-navigation">
            <?php if ($prev_step): ?>
                <button class="button button-secondary" onclick="pdfBuilderWizard.previousStep('<?php echo esc_attr($prev_step); ?>')">
                    Précédent
                </button>
            <?php endif; ?>

            <?php if ($current_step === 'company'): ?>
                <button class="button button-secondary" onclick="pdfBuilderWizard.skipStep('<?php echo esc_attr($next_step); ?>')">
                    Passer cette étape
                </button>
            <?php endif; ?>

            <?php if ($next_step): ?>
                <button class="button button-primary" onclick="pdfBuilderWizard.nextStep('<?php echo esc_attr($next_step); ?>')">
                    Suivant
                </button>
            <?php elseif ($current_step === 'complete'): ?>
                <button class="button button-primary" onclick="pdfBuilderWizard.finish()">
                    Commencer à utiliser PDF Builder
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Vérifier WooCommerce
     */
    private function check_woocommerce() {
        return class_exists('WooCommerce');
    }

    /**
     * Vérifier la version PHP
     */
    private function check_php_version() {
        return version_compare(PHP_VERSION, '7.4', '>=');
    }

    /**
     * Vérifier la version MySQL
     */
    private function check_mysql_version() {
        global $wpdb;
        $version = $wpdb->get_var("SELECT VERSION()");
        return version_compare($version, '5.6', '>=');
    }

    /**
     * Obtenir la version MySQL
     */
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }

    /**
     * Récupérer les informations entreprise depuis WooCommerce
     */
    private function get_woocommerce_company_info() {
        $info = array();

        if (!class_exists('WooCommerce')) {
            return $info;
        }

        // Nom de l'entreprise (nom de la boutique WooCommerce ou nom du site)
        $store_name = get_option('woocommerce_store_name', '');
        if (empty($store_name)) {
            $store_name = get_option('blogname', '');
        }
        $info['name'] = $store_name;

        // Adresse de la boutique
        $address_parts = array(
            get_option('woocommerce_store_address', ''),
            get_option('woocommerce_store_address_2', ''),
            get_option('woocommerce_store_city', ''),
            get_option('woocommerce_store_postcode', ''),
            get_option('woocommerce_store_country', '')
        );

        // Filtrer les parties vides et construire l'adresse
        $address_parts = array_filter($address_parts);
        if (!empty($address_parts)) {
            $info['address'] = implode("\n", $address_parts);
        }

        // Téléphone (si configuré dans WooCommerce)
        $info['phone'] = get_option('woocommerce_store_phone', '');

        // Email (email WooCommerce ou admin email)
        $info['email'] = get_option('woocommerce_email_from_address', '');
        if (empty($info['email'])) {
            $info['email'] = get_option('admin_email', '');
        }

        // Logo (logo WooCommerce personnalisé ou site icon WordPress)
        $logo_url = get_option('woocommerce_store_logo', '');
        if (empty($logo_url)) {
            // Essayer le custom logo WordPress
            $custom_logo_id = get_option('site_logo', '');
            if (!empty($custom_logo_id)) {
                $logo_url = wp_get_attachment_url($custom_logo_id);
            }
            // Fallback vers le site icon
            if (empty($logo_url)) {
                $site_icon_id = get_option('site_icon', '');
                if (!empty($site_icon_id)) {
                    $logo_url = wp_get_attachment_url($site_icon_id);
                }
            }
        }
        $info['logo'] = $logo_url;

        return $info;
    }

    /**
     * Créer les tables nécessaires si elles n'existent pas
     */
    private function create_tables_if_needed() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
            $sql_templates = "CREATE TABLE $table_templates (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                template_data longtext NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_templates);
        }

        // Table des éléments (si nécessaire pour le futur)
        $table_elements = $wpdb->prefix . 'pdf_builder_elements';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_elements'") != $table_elements) {
            $sql_elements = "CREATE TABLE $table_elements (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                template_id mediumint(9) NOT NULL,
                element_type varchar(50) NOT NULL,
                element_data longtext NOT NULL,
                position_x int(11) DEFAULT 0,
                position_y int(11) DEFAULT 0,
                width int(11) DEFAULT 0,
                height int(11) DEFAULT 0,
                z_index int(11) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY template_id (template_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_elements);
        }
    }

    /**
     * Gérer les requêtes AJAX
     */
    public function handle_ajax_step() {
        try {
            check_ajax_referer('pdf_builder_wizard_nonce', 'nonce');

            $step = sanitize_text_field($_POST['step']);
            $data = isset($_POST['data']) ? $_POST['data'] : array();

            $response = array('success' => false);

            switch ($step) {
                case 'save_company':
                    $response = $this->save_company_data($data);
                    break;

                case 'create_template':
                    $response = $this->create_default_template();
                    break;

                case 'complete':
                    update_option('pdf_builder_installed', true);
                    $response = array('success' => true, 'message' => 'Installation terminée');
                    break;

                default:
                    $response = array('success' => false, 'message' => 'Étape inconnue: ' . $step);
            }

        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            );
        }

        wp_send_json($response);
    }

    /**
     * Sauvegarder les données entreprise
     */
    private function save_company_data($data) {
        try {
            // Validation des données
            if (empty($data['company_name'])) {
                return array('success' => false, 'message' => 'Le nom de l\'entreprise est obligatoire');
            }

            $company_data = array(
                'name' => sanitize_text_field($data['company_name']),
                'address' => sanitize_textarea_field($data['company_address'] ?? ''),
                'phone' => sanitize_text_field($data['company_phone'] ?? ''),
                'email' => sanitize_email($data['company_email'] ?? ''),
                'logo' => esc_url_raw($data['company_logo'] ?? '')
            );

            // Sauvegarder dans les options WordPress
            $result = update_option('pdf_builder_company_info', $company_data);

            if ($result === false) {
                return array('success' => false, 'message' => 'Erreur lors de la sauvegarde en base de données');
            }

            return array('success' => true, 'message' => 'Informations entreprise sauvegardées avec succès');

        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Créer le template par défaut
     */
    private function create_default_template() {
        try {
            global $wpdb;

            // Vérifier si la table existe
            $table_name = $wpdb->prefix . 'pdf_builder_templates';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                return array('success' => false, 'message' => 'La table des templates n\'existe pas. Veuillez vérifier l\'installation du plugin.');
            }

            $template_data = array(
                'name' => 'Template par défaut',
                'elements' => array(
                    array('type' => 'company_logo', 'x' => 20, 'y' => 20),
                    array('type' => 'company_info', 'x' => 20, 'y' => 80),
                    array('type' => 'customer_info', 'x' => 300, 'y' => 20),
                    array('type' => 'product_table', 'x' => 20, 'y' => 150),
                    array('type' => 'order_total', 'x' => 400, 'y' => 400)
                ),
                'settings' => array(
                    'width' => 595,
                    'height' => 842,
                    'margin_top' => 20,
                    'margin_bottom' => 20
                )
            );

            $result = $wpdb->insert(
                $table_name,
                array(
                    'name' => $template_data['name'],
                    'template_data' => wp_json_encode($template_data)
                ),
                array('%s', '%s')
            );

            if ($result === false) {
                return array('success' => false, 'message' => 'Erreur lors de la création du template: ' . $wpdb->last_error);
            }

            return array('success' => true, 'message' => 'Template par défaut créé avec succès');

        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Erreur lors de la création du template: ' . $e->getMessage());
        }
    }
}

// Initialiser le wizard
new PDF_Builder_Installation_Wizard();