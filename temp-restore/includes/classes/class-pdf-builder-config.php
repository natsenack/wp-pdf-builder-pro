<?php
/**
 * PDF Builder Pro - Configuration simplifiée
 * Version 5.1.0 - Canvas uniquement
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe de configuration PDF Builder Pro - Version Simplifiée
 */
class PDF_Builder_Config {

    // Version du plugin
    const VERSION = '5.1.0';

    // Chemins et URLs
    public static function get_plugin_file() {
        return __FILE__;
    }

    public static function get_plugin_dir() {
        return __DIR__;
    }

    public static function get_plugin_url() {
        return plugin_dir_url(__FILE__);
    }

    // Capacités WordPress
    const CAPABILITY = 'manage_options';

    // Pages d'administration
    const ADMIN_PAGE = 'pdf-builder-pro';
    const ADMIN_MENU_TITLE = 'PDF Builder';
    const ADMIN_PAGE_TITLE = 'PDF Builder Pro - Constructeur Canvas';

    // Types d'éléments supportés (simplifié pour canvas)
    const ELEMENT_TYPES = [
        'text' => 'Texte',
        'rectangle' => 'Rectangle',
        'image' => 'Image'
    ];

    // Formats PDF supportés
    const PDF_FORMATS = [
        'A4' => ['width' => 595, 'height' => 842], // Portrait A4
        'LETTER' => ['width' => 612, 'height' => 792]
    ];

    // Paramètres par défaut pour le canvas
    const DEFAULT_SETTINGS = [
        'pdf_format' => 'A4',
        'canvas_width' => 595,
        'canvas_height' => 842,
        'grid_size' => 10,
        'snap_to_grid' => true,
        'show_grid' => true
    ];

    /**
     * Obtient un paramètre par défaut
     */
    public static function get_default_setting($key) {
        return isset(self::DEFAULT_SETTINGS[$key]) ? self::DEFAULT_SETTINGS[$key] : null;
    }

    /**
     * Obtient les dimensions du format PDF
     */
    public static function get_pdf_dimensions($format = 'A4') {
        return isset(self::PDF_FORMATS[$format]) ? self::PDF_FORMATS[$format] : self::PDF_FORMATS['A4'];
    }

    /**
     * Vérifie si un type d'élément est supporté
     */
    public static function is_element_type_supported($type) {
        return isset(self::ELEMENT_TYPES[$type]);
    }
}

