<?php
/**
 * PDF Builder Translation Utils
 * Utilitaires de traduction pour le plugin PDF Builder Pro
 */



class PDF_Builder_Translation_Utils {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Text domain du plugin
     */
    private $text_domain = 'pdf-builder-pro';

    /**
     * Constructeur privé
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Charger le domaine de traduction
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname(plugin_basename(PDF_BUILDER_PLUGIN_DIR)) . '/languages/'
        );
    }

    /**
     * Fonction de traduction avec fallback
     */
    public function translate($text, $context = '') {
        $translated = __($text, $this->text_domain);

        // Si la traduction est identique au texte original, essayer avec un contexte
        if ($translated === $text && !empty($context)) {
            $translated = _x($text, $context, $this->text_domain);
        }

        return $translated;
    }

    /**
     * Fonction de traduction plurielle
     */
    public function translate_plural($single, $plural, $number, $context = '') {
        if (!empty($context)) {
            return _nx($single, $plural, $number, $context, $this->text_domain);
        }
        return _n($single, $plural, $number, $this->text_domain);
    }

    /**
     * Obtenir le text domain
     */
    public function get_text_domain() {
        return $this->text_domain;
    }

    /**
     * Vérifier si une traduction existe
     */
    public function translation_exists($text, $context = '') {
        if (!empty($context)) {
            return __($text, $this->text_domain) !== $text || _x($text, $context, $this->text_domain) !== $text;
        }
        return __($text, $this->text_domain) !== $text;
    }
}

// Fonctions globales pour la traduction
function pdf_builder_translate($text, $context = '') {
    return PDF_Builder_Translation_Utils::get_instance()->translate($text, $context);
}

function pdf_builder_translate_plural($single, $plural, $number, $context = '') {
    return PDF_Builder_Translation_Utils::get_instance()->translate_plural($single, $plural, $number, $context);
}


