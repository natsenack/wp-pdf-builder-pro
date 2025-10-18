<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Translation Utils Manager
 * Gestionnaire des utilitaires de traduction
 */



/**
 * Classe utilitaire pour la gestion des traductions
 */
class PDF_Builder_Translation_Utils {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Domaine de traduction du plugin
     */
    const TEXT_DOMAIN = 'pdf-builder-pro';

    /**
     * Langue actuelle
     */
    private $current_language = 'fr';

    /**
     * Cache des traductions
     */
    private $translation_cache = array();

    /**
     * Constructeur privé pour singleton
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation des utilitaires de traduction
     */
    public function init() {
        // Détecter la langue actuelle
        $this->current_language = $this->detect_language();

        // Charger les fichiers de traduction
        $this->load_translations();

        // Hooks pour les traductions
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_filter('locale', array($this, 'set_locale'));
    }

    /**
     * Détecter la langue actuelle
     */
    private function detect_language() {
        // Utiliser la locale WordPress
        $locale = get_locale();

        // Extraire le code de langue (ex: 'fr_FR' -> 'fr')
        $language_code = substr($locale, 0, 2);

        return $language_code;
    }

    /**
     * Charger les fichiers de traduction
     */
    private function load_translations() {
        // Charger le textdomain
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname(plugin_basename(PDF_BUILDER_PLUGIN_FILE)) . '/languages/'
        );
    }

    /**
     * Hook pour charger le textdomain
     */
    public function load_textdomain() {
        $this->load_translations();
    }

    /**
     * Filtre pour définir la locale
     */
    public function set_locale($locale) {
        // Vous pouvez ajouter une logique personnalisée ici
        // pour forcer une langue spécifique si nécessaire
        return $locale;
    }

    /**
     * Traduire une chaîne avec cache
     */
    public function translate($text, $context = '') {
        $cache_key = md5($text . $context);

        if (!isset($this->translation_cache[$cache_key])) {
            if ($context) {
                $this->translation_cache[$cache_key] = _x($text, $context, self::TEXT_DOMAIN);
            } else {
                $this->translation_cache[$cache_key] = __($text, self::TEXT_DOMAIN);
            }
        }

        return $this->translation_cache[$cache_key];
    }

    /**
     * Traduire une chaîne avec variables
     */
    public function translate_with_vars($text, $vars = array(), $context = '') {
        $translated = $this->translate($text, $context);

        if (!empty($vars)) {
            $translated = vsprintf($translated, $vars);
        }

        return $translated;
    }

    /**
     * Obtenir la langue actuelle
     */
    public function get_current_language() {
        return $this->current_language;
    }

    /**
     * Vérifier si une langue est supportée
     */
    public function is_language_supported($language_code) {
        $supported_languages = array('fr', 'en', 'es', 'de', 'it');
        return in_array($language_code, $supported_languages);
    }

    /**
     * Obtenir les langues supportées
     */
    public function get_supported_languages() {
        return array(
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano'
        );
    }

    /**
     * Formater une date selon la locale
     */
    public function format_date($date, $format = null) {
        if (!$format) {
            $format = get_option('date_format', 'd/m/Y');
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        return date_i18n($format, $date);
    }

    /**
     * Formater un nombre selon la locale
     */
    public function format_number($number, $decimals = 2) {
        return number_format_i18n($number, $decimals);
    }

    /**
     * Obtenir les informations de locale
     */
    public function get_locale_info() {
        return array(
            'language' => $this->current_language,
            'locale' => get_locale(),
            'text_domain' => self::TEXT_DOMAIN,
            'is_rtl' => is_rtl()
        );
    }

    /**
     * Nettoyer le cache des traductions
     */
    public function clear_cache() {
        $this->translation_cache = array();
    }
}

// Fonction helper globale pour accéder aux utilitaires de traduction
function pdf_builder_translate($text, $context = '') {
    return PDF_Builder_Translation_Utils::getInstance()->translate($text, $context);
}

// Fonction helper pour traduire avec variables
function pdf_builder_translate_vars($text, $vars = array(), $context = '') {
    return PDF_Builder_Translation_Utils::getInstance()->translate_with_vars($text, $vars, $context);
}


