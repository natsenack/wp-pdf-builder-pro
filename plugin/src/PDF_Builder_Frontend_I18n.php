<?php

namespace WP_PDF_Builder_Pro\Src;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Frontend Internationalization
 * Gestion de l'internationalisation pour le frontend
 */



class PdfBuilderFrontendI18n
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;
/**
     * Chaînes de traduction chargées
     */
    private $strings = array();

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        $this->initHooks();
        $this->loadStrings();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks
     */
    private function initHooks()
    {
        add_action('init', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'localize_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'localize_admin_scripts'));
    }

    /**
     * Charger le domaine de texte
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain(PDF_BUILDER_TEXT_DOMAIN, false, dirname(plugin_basename(PDF_BUILDER_PLUGIN_DIR)) . '/languages/');
    }

    /**
     * Charger les chaînes de traduction
     */
    private function loadStrings()
    {
        $this->strings = array(
            // Chaînes générales
            'loading' => __('Loading...', PDF_BUILDER_TEXT_DOMAIN),
            'error' => __('An error occurred', PDF_BUILDER_TEXT_DOMAIN),
            'success' => __('Success', PDF_BUILDER_TEXT_DOMAIN),
            'cancel' => __('Cancel', PDF_BUILDER_TEXT_DOMAIN),
            'save' => __('Save', PDF_BUILDER_TEXT_DOMAIN),
            'delete' => __('Delete', PDF_BUILDER_TEXT_DOMAIN),
            'edit' => __('Edit', PDF_BUILDER_TEXT_DOMAIN),
            'view' => __('View', PDF_BUILDER_TEXT_DOMAIN),
            'download' => __('Download', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes spécifiques au PDF
            'generating_pdf' => __('Generating PDF...', PDF_BUILDER_TEXT_DOMAIN),
            'pdf_generated' => __('PDF generated successfully', PDF_BUILDER_TEXT_DOMAIN),
            'pdf_generation_failed' => __('PDF generation failed', PDF_BUILDER_TEXT_DOMAIN),
            'download_pdf' => __('Download PDF', PDF_BUILDER_TEXT_DOMAIN),
            'view_pdf' => __('View PDF', PDF_BUILDER_TEXT_DOMAIN),
            'pdf_not_found' => __('PDF not found', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les templates
            'template_saved' => __('Template saved successfully', PDF_BUILDER_TEXT_DOMAIN),
            'template_deleted' => __('Template deleted successfully', PDF_BUILDER_TEXT_DOMAIN),
            'template_not_found' => __('Template not found', PDF_BUILDER_TEXT_DOMAIN),
            'invalid_template' => __('Invalid template', PDF_BUILDER_TEXT_DOMAIN),
            'template_name_required' => __('Template name is required', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour l'éditeur
            'add_element' => __('Add Element', PDF_BUILDER_TEXT_DOMAIN),
            'remove_element' => __('Remove Element', PDF_BUILDER_TEXT_DOMAIN),
            'duplicate_element' => __('Duplicate Element', PDF_BUILDER_TEXT_DOMAIN),
            'move_element' => __('Move Element', PDF_BUILDER_TEXT_DOMAIN),
            'element_properties' => __('Element Properties', PDF_BUILDER_TEXT_DOMAIN),
            'text_element' => __('Text Element', PDF_BUILDER_TEXT_DOMAIN),
            'image_element' => __('Image Element', PDF_BUILDER_TEXT_DOMAIN),
            'shape_element' => __('Shape Element', PDF_BUILDER_TEXT_DOMAIN),
            'barcode_element' => __('Barcode Element', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les propriétés
            'font_family' => __('Font Family', PDF_BUILDER_TEXT_DOMAIN),
            'font_size' => __('Font Size', PDF_BUILDER_TEXT_DOMAIN),
            'font_color' => __('Font Color', PDF_BUILDER_TEXT_DOMAIN),
            'font_weight' => __('Font Weight', PDF_BUILDER_TEXT_DOMAIN),
            'text_align' => __('Text Align', PDF_BUILDER_TEXT_DOMAIN),
            'position' => __('Position', PDF_BUILDER_TEXT_DOMAIN),
            'size' => __('Size', PDF_BUILDER_TEXT_DOMAIN),
            'width' => __('Width', PDF_BUILDER_TEXT_DOMAIN),
            'height' => __('Height', PDF_BUILDER_TEXT_DOMAIN),
            'background_color' => __('Background Color', PDF_BUILDER_TEXT_DOMAIN),
            'border' => __('Border', PDF_BUILDER_TEXT_DOMAIN),
            'border_width' => __('Border Width', PDF_BUILDER_TEXT_DOMAIN),
            'border_color' => __('Border Color', PDF_BUILDER_TEXT_DOMAIN),
            'border_style' => __('Border Style', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les erreurs
            'permission_denied' => __('Permission denied', PDF_BUILDER_TEXT_DOMAIN),
            'invalid_request' => __('Invalid request', PDF_BUILDER_TEXT_DOMAIN),
            'server_error' => __('Server error', PDF_BUILDER_TEXT_DOMAIN),
            'network_error' => __('Network error', PDF_BUILDER_TEXT_DOMAIN),
            'timeout_error' => __('Request timeout', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les confirmations
            'confirm_delete' => __('Are you sure you want to delete this item?', PDF_BUILDER_TEXT_DOMAIN),
            'confirm_save' => __('Do you want to save your changes?', PDF_BUILDER_TEXT_DOMAIN),
            'unsaved_changes' => __('You have unsaved changes. Are you sure you want to leave?', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour l'administration
            'settings_saved' => __('Settings saved successfully', PDF_BUILDER_TEXT_DOMAIN),
            'settings_reset' => __('Settings reset to defaults', PDF_BUILDER_TEXT_DOMAIN),
            'cache_cleared' => __('Cache cleared successfully', PDF_BUILDER_TEXT_DOMAIN),
            'logs_cleared' => __('Logs cleared successfully', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les dates
            'today' => __('Today', PDF_BUILDER_TEXT_DOMAIN),
            'yesterday' => __('Yesterday', PDF_BUILDER_TEXT_DOMAIN),
            'date_format' => __('Y-m-d H:i:s', PDF_BUILDER_TEXT_DOMAIN),
            'short_date_format' => __('Y-m-d', PDF_BUILDER_TEXT_DOMAIN),

            // Chaînes pour les unités
            'pixels' => __('pixels', PDF_BUILDER_TEXT_DOMAIN),
            'millimeters' => __('millimeters', PDF_BUILDER_TEXT_DOMAIN),
            'inches' => __('inches', PDF_BUILDER_TEXT_DOMAIN),
            'points' => __('points', PDF_BUILDER_TEXT_DOMAIN),
        );
// Filtrer les chaînes pour permettre la personnalisation
        $this->strings = apply_filters('pdf_builder_i18n_strings', $this->strings);
    }

    /**
     * Localiser les scripts frontend
     */
    public function localizeScripts()
    {
        if (did_action('wp_enqueue_scripts')) {
            wp_localize_script('pdf-builder-frontend', 'pdfBuilderI18n', $this->strings);
        }
    }

    /**
     * Localiser les scripts d'administration
     */
    public function localizeAdminScripts()
    {
        if (did_action('admin_enqueue_scripts')) {
            wp_localize_script('pdf-builder-admin', 'pdfBuilderI18n', $this->strings);
        }
    }

    /**
     * Obtenir une chaîne traduite
     */
    public function getString($key, $default = '')
    {
        return isset($this->strings[$key]) ? $this->strings[$key] : $default;
    }

    /**
     * Obtenir toutes les chaînes
     */
    public function getAllStrings()
    {
        return $this->strings;
    }

    /**
     * Ajouter une chaîne personnalisée
     */
    public function addString($key, $value)
    {
        $this->strings[$key] = $value;
    }

    /**
     * Supprimer une chaîne
     */
    public function removeString($key)
    {
        unset($this->strings[$key]);
    }

    /**
     * Fonction utilitaire pour traduire avec sprintf
     */
    public function sprintf($key, ...$args)
    {
        $string = $this->getString($key);
        if (!$string) {
            return '';
        }
        return sprintf($string, ...$args);
    }

    /**
     * Fonction utilitaire pour obtenir la traduction avec contexte
     */
    public function translateWithContext($text, $context = '', $domain = PDF_BUILDER_TEXT_DOMAIN)
    {
        return _x($text, $context, $domain);
    }

    /**
     * Fonction utilitaire pour obtenir la traduction plurielle
     */
    public function translatePlural($single, $plural, $number, $domain = PDF_BUILDER_TEXT_DOMAIN)
    {
        return _n($single, $plural, $number, $domain);
    }

    /**
     * Vérifier si une traduction existe pour la langue actuelle
     */
    public function hasTranslation($key)
    {
        $string = $this->getString($key);
        return $string && $string !== $key;
    }

    /**
     * Obtenir la langue actuelle
     */
    public function getCurrentLanguage()
    {
        return get_locale();
    }

    /**
     * Obtenir la liste des langues disponibles
     */
    public function getAvailableLanguages()
    {
        $languages = get_available_languages(PDF_BUILDER_LANGUAGES_DIR);
        return array_merge(array('en_US'), $languages);
    }

    /**
     * Charger des traductions supplémentaires depuis un fichier
     */
    public function loadAdditionalStrings($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        $additional_strings = include $file_path;
        if (!is_array($additional_strings)) {
            return false;
        }

        $this->strings = array_merge($this->strings, $additional_strings);
        return true;
    }
}

// Fonctions utilitaires globales pour l'i18n

/**
 * Obtenir une chaîne traduite (i18n)
 */
function pdfBuilderI18nTranslate($key, $default = '')
{

    $i18n = PDF_Builder_Frontend_I18n::get_instance();
    return $i18n->getString($key, $default);
}

/**
 * Traduction avec sprintf (i18n)
 */
function pdfBuilderI18nTranslateSprintf($key, ...$args)
{

    $i18n = PDF_Builder_Frontend_I18n::get_instance();
    return $i18n->sprintf($key, ...$args);
}

/**
 * Traduction avec contexte (i18n)
 */
function pdfBuilderI18nTranslateX($text, $context = '')
{

    $i18n = PDF_Builder_Frontend_I18n::get_instance();
    return $i18n->translateWithContext($text, $context);
}

/**
 * Traduction plurielle (i18n)
 */
function pdfBuilderI18nTranslateN($single, $plural, $number)
{

    $i18n = PDF_Builder_Frontend_I18n::get_instance();
    return $i18n->translatePlural($single, $plural, $number);
}
