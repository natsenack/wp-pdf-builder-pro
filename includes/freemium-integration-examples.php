<?php
/**
 * PDF Builder Freemium Integration
 * Exemples d'intégration des restrictions freemium
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// =============================================================================
// EXEMPLE 1 : Restriction dans le générateur PDF
// =============================================================================

class PDF_Generator_Freemium extends PDF_Generator {

    public function generate_pdf($template_data) {
        // Vérifier si l'utilisateur peut générer des PDFs
        if (!PDF_Builder_Feature_Manager::can_use_feature('pdf_generation')) {
            $current_usage = PDF_Builder_Feature_Manager::get_current_usage('pdf_generation');
            $limit = PDF_Builder_Feature_Manager::get_feature_limit('pdf_generation');

            throw new Exception(
                sprintf(
                    __('Limite de génération PDF atteinte (%d/%d ce mois-ci). Passez à Premium pour génération illimitée.', 'pdf-builder-pro'),
                    $current_usage,
                    $limit
                )
            );
        }

        // Générer le PDF
        $pdf = parent::generate_pdf($template_data);

        // Incrémenter le compteur d'usage
        PDF_Builder_Feature_Manager::increment_usage('pdf_generation');

        return $pdf;
    }
}

// =============================================================================
// EXEMPLE 2 : Restriction dans le gestionnaire de templates
// =============================================================================

class PDF_Template_Manager_Freemium extends PDF_Template_Manager {

    public function get_available_templates() {
        $all_templates = parent::get_available_templates();

        if (PDF_Builder_Feature_Manager::can_use_feature('advanced_templates')) {
            return $all_templates; // Tous les templates pour premium
        }

        // Seulement les templates de base pour free
        return array_filter($all_templates, function($template) {
            return isset($template['is_basic']) && $template['is_basic'];
        });
    }

    public function create_template($template_data) {
        if (!PDF_Builder_Feature_Manager::can_use_feature('advanced_templates')) {
            throw new Exception(
                __('La création de templates personnalisés est une fonctionnalité Premium.', 'pdf-builder-pro')
            );
        }

        return parent::create_template($template_data);
    }
}

// =============================================================================
// EXEMPLE 3 : Restriction dans l'éditeur canvas
// =============================================================================

class PDF_Canvas_Editor_Freemium extends PDF_Canvas_Editor {

    public function get_available_elements() {
        $all_elements = parent::get_available_elements();

        if (PDF_Builder_Feature_Manager::can_use_feature('premium_elements')) {
            return $all_elements; // Tous les éléments pour premium
        }

        // Seulement les éléments de base pour free
        return array_filter($all_elements, function($element) {
            return isset($element['is_basic']) && $element['is_basic'];
        });
    }

    public function add_element($element_type, $properties) {
        // Vérifier si l'élément est premium
        $premium_elements = ['barcode', 'qrcode', 'chart', 'signature'];

        if (in_array($element_type, $premium_elements)) {
            if (!PDF_Builder_Feature_Manager::can_use_feature('premium_elements')) {
                throw new Exception(
                    sprintf(
                        __('L\'élément "%s" est une fonctionnalité Premium.', 'pdf-builder-pro'),
                        ucfirst($element_type)
                    )
                );
            }
        }

        return parent::add_element($element_type, $properties);
    }
}

// =============================================================================
// EXEMPLE 4 : Restriction dans l'API
// =============================================================================

class PDF_API_Controller_Freemium extends PDF_API_Controller {

    public function __construct() {
        parent::__construct();

        // Vérifier l'accès API pour les utilisateurs free
        if (!PDF_Builder_Feature_Manager::can_use_feature('api_access')) {
            $this->disable_api_endpoints();
        }
    }

    private function disable_api_endpoints() {
        // Désactiver certains endpoints pour les utilisateurs free
        remove_action('rest_api_init', array($this, 'register_bulk_generation_endpoint'));
        remove_action('rest_api_init', array($this, 'register_advanced_templates_endpoint'));
    }

    public function generate_pdf(WP_REST_Request $request) {
        // Vérifier les limites pour les utilisateurs free
        if (!PDF_Builder_Feature_Manager::can_use_feature('pdf_generation')) {
            return new WP_Error(
                'pdf_generation_limit_reached',
                __('Limite de génération PDF atteinte. Passez à Premium pour continuer.', 'pdf-builder-pro'),
                array('status' => 429)
            );
        }

        $result = parent::generate_pdf($request);

        // Incrémenter le compteur si succès
        if (!is_wp_error($result)) {
            PDF_Builder_Feature_Manager::increment_usage('pdf_generation');
        }

        return $result;
    }
}

// =============================================================================
// EXEMPLE 5 : Notifications d'upgrade dans l'interface
// =============================================================================

class PDF_Admin_Notifications_Freemium {

    public static function init() {
        add_action('admin_notices', array(__CLASS__, 'show_upgrade_notices'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }

    public static function show_upgrade_notices() {
        $license_manager = PDF_Builder_License_Manager::getInstance();

        if ($license_manager->is_premium()) {
            return;
        }

        // Notice générale (masquable)
        if (!get_option('pdf_builder_upgrade_notice_dismissed')) {
            ?>
            <div class="notice notice-info is-dismissible pdf-builder-upgrade-notice">
                <p>
                    <strong>PDF Builder Pro</strong> : Découvrez les fonctionnalités Premium !
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=license'); ?>" class="button button-small">
                        <?php _e('En savoir plus', 'pdf-builder-pro'); ?>
                    </a>
                </p>
            </div>
            <?php
        }

        // Notice de limite atteinte
        $pdf_usage = PDF_Builder_Feature_Manager::get_current_usage('pdf_generation');
        $pdf_limit = PDF_Builder_Feature_Manager::get_feature_limit('pdf_generation');

        if ($pdf_limit > 0 && $pdf_usage >= ($pdf_limit * 0.9)) { // Alerte à 90%
            $remaining = $pdf_limit - $pdf_usage;
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>Attention :</strong> Il ne vous reste que <?php echo $remaining; ?> génération(s) PDF ce mois-ci.
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=license'); ?>">
                        Passez à Premium pour génération illimitée
                    </a>
                </p>
            </div>
            <?php
        }
    }

    public static function enqueue_scripts($hook) {
        if (strpos($hook, 'pdf-builder') === false) {
            return;
        }

        wp_enqueue_script(
            'pdf-builder-freemium',
            plugin_dir_url(__FILE__) . 'js/freemium.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('pdf-builder-freemium', 'pdf_builder_freemium', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_freemium'),
            'is_premium' => PDF_Builder_License_Manager::getInstance()->is_premium()
        ));
    }
}

// =============================================================================
// EXEMPLE 6 : Fonction utilitaire pour les restrictions UI
// =============================================================================

class PDF_UI_Restrictions {

    /**
     * Ajouter des badges "Premium" aux éléments de l'interface
     */
    public static function render_element_button($element) {
        $is_premium = !isset($element['is_basic']) || !$element['is_basic'];
        $can_use = PDF_Builder_Feature_Manager::can_use_feature(
            $is_premium ? 'premium_elements' : 'basic_elements'
        );

        $classes = 'pdf-element-button';
        if (!$can_use) {
            $classes .= ' premium-locked';
        }

        echo '<button class="' . esc_attr($classes) . '" data-element="' . esc_attr($element['type']) . '">';

        if ($is_premium && !$can_use) {
            echo '<span class="premium-badge">PREMIUM</span>';
        }

        echo '<span class="element-icon">' . esc_html($element['icon']) . '</span>';
        echo '<span class="element-name">' . esc_html($element['name']) . '</span>';

        echo '</button>';

        if (!$can_use) {
            echo '<div class="premium-tooltip">';
            echo '<p>' . esc_html($element['name']) . ' est une fonctionnalité Premium</p>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=pdf-builder-settings&tab=license')) . '" class="upgrade-link">Passer à Premium</a>';
            echo '</div>';
        }
    }

    /**
     * Afficher un compteur d'usage
     */
    public static function render_usage_counter($feature_name) {
        $current = PDF_Builder_Feature_Manager::get_current_usage($feature_name);
        $limit = PDF_Builder_Feature_Manager::get_feature_limit($feature_name);

        if ($limit <= 0) {
            return; // Pas de limite
        }

        $percentage = ($current / $limit) * 100;
        $color = $percentage > 90 ? 'red' : ($percentage > 70 ? 'orange' : 'green');

        echo '<div class="usage-counter" style="margin: 10px 0;">';
        echo '<div class="usage-bar" style="background: #f0f0f0; border-radius: 4px; height: 20px; overflow: hidden;">';
        echo '<div class="usage-fill" style="background: ' . $color . '; height: 100%; width: ' . $percentage . '%; transition: width 0.3s ease;"></div>';
        echo '</div>';
        echo '<div class="usage-text" style="font-size: 12px; color: #666; margin-top: 5px;">';
        printf(
            __('Utilisation : %d/%d ce mois', 'pdf-builder-pro'),
            $current,
            $limit
        );
        echo '</div>';
        echo '</div>';
    }
}

// =============================================================================
// EXEMPLE 7 : Intégration dans le Core
// =============================================================================

class PDF_Builder_Core_Freemium extends PDF_Builder_Core {

    public function init() {
        parent::init();

        // Initialiser les restrictions freemium
        $this->init_freemium_restrictions();

        // Ajouter les notifications d'upgrade
        PDF_Admin_Notifications_Freemium::init();
    }

    private function init_freemium_restrictions() {
        // Remplacer les classes par leurs versions freemium
        if (!PDF_Builder_License_Manager::getInstance()->is_premium()) {
            // Remplacer le générateur PDF
            if (class_exists('PDF_Generator_Freemium')) {
                $this->pdf_generator = new PDF_Generator_Freemium();
            }

            // Remplacer le gestionnaire de templates
            if (class_exists('PDF_Template_Manager_Freemium')) {
                $this->template_manager = new PDF_Template_Manager_Freemium();
            }

            // Remplacer l'éditeur canvas
            if (class_exists('PDF_Canvas_Editor_Freemium')) {
                $this->canvas_editor = new PDF_Canvas_Editor_Freemium();
            }
        }
    }

    /**
     * Vérifier les restrictions avant d'exécuter une action
     */
    public function check_feature_access($feature_name, $error_message = '') {
        if (!PDF_Builder_Feature_Manager::can_use_feature($feature_name)) {
            if (empty($error_message)) {
                $feature_details = PDF_Builder_Feature_Manager::get_feature_details($feature_name);
                $error_message = sprintf(
                    __('Cette fonctionnalité (%s) est réservée aux utilisateurs Premium.', 'pdf-builder-pro'),
                    $feature_details['name']
                );
            }

            wp_die(
                '<div class="pdf-builder-upgrade-prompt">' .
                '<h2>' . __('Fonctionnalité Premium', 'pdf-builder-pro') . '</h2>' .
                '<p>' . $error_message . '</p>' .
                '<p><a href="' . admin_url('admin.php?page=pdf-builder-settings&tab=license') . '" class="button button-primary">' .
                __('Passer à Premium', 'pdf-builder-pro') . '</a></p>' .
                '</div>'
            );
        }
    }
}

// =============================================================================
// INITIALISATION
// =============================================================================

// Fonction d'initialisation freemium
function pdf_builder_init_freemium() {
    // Charger les classes freemium
    $freemium_classes = [
        'PDF_Builder_License_Manager',
        'PDF_Builder_Feature_Manager',
        'PDF_Admin_Notifications_Freemium'
    ];

    foreach ($freemium_classes as $class) {
        $file = plugin_dir_path(__FILE__) . 'classes/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }

    // Initialiser les notifications
    if (class_exists('PDF_Admin_Notifications_Freemium')) {
        PDF_Admin_Notifications_Freemium::init();
    }
}

// Hook d'initialisation
add_action('plugins_loaded', 'pdf_builder_init_freemium', 15);