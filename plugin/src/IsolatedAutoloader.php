<?php
/**
 * PDF Builder Pro - Autoload Isolée
 * Système d'autoload indépendant pour éviter les conflits avec autres plugins
 */

namespace PDF_Builder;

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class IsolatedAutoloader
{
    private static $instance = null;
    private $vendor_dir;
    private $class_map = array();

    private function __construct()
    {
        $this->vendor_dir = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/plugin/vendor/';
        $this->buildClassMap();
        $this->register();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construit la map des classes nécessaires
     */
    private function buildClassMap()
    {
        // Classes Dompdf essentielles seulement
        $this->class_map = array(
            // Classes de base Dompdf
            'Dompdf\\Dompdf' => 'dompdf/src/Dompdf.php',
            'Dompdf\\Options' => 'dompdf/src/Options.php',
            'Dompdf\\Canvas' => 'dompdf/src/Canvas.php',
            'Dompdf\\Css\\Stylesheet' => 'dompdf/src/Css/Stylesheet.php',
            'Dompdf\\Frame' => 'dompdf/src/Frame.php',
            'Dompdf\\Frame\\FrameTree' => 'dompdf/src/Frame/FrameTree.php',

            // Adapters
            'Dompdf\\Adapter\\CPDF' => 'dompdf/src/Adapter/CPDF.php',

            // Helpers et utilitaires essentiels
            'Dompdf\\Helpers' => 'dompdf/src/Helpers.php',
            'Dompdf\\Css\\Color' => 'dompdf/src/Css/Color.php',

            // Parsers (sans HTML5 pour éviter les conflits)
            'Dompdf\\Css\\Style' => 'dompdf/src/Css/Style.php',

            // Seulement les classes que nous utilisons réellement
            'Dompdf\\Exception' => 'dompdf/src/Exception.php',
            'Dompdf\\FontMetrics' => 'dompdf/src/FontMetrics.php',
        );
    }

    /**
     * Enregistre l'autoload
     */
    private function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true, true);
    }

    /**
     * Charge une classe de manière isolée
     */
    public function loadClass($class_name)
    {
        // Ne charger que les classes de notre map
        if (!isset($this->class_map[$class_name])) {
            return false;
        }

        $file_path = $this->vendor_dir . $this->class_map[$class_name];

        if (file_exists($file_path)) {
            require_once $file_path;
            return true;
        }

        return false;
    }

    /**
     * Vérifie si une classe peut être chargée
     */
    public function canLoadClass($class_name)
    {
        return isset($this->class_map[$class_name]);
    }

    /**
     * Charge manuellement une classe
     */
    public function loadClassManually($class_name)
    {
        return $this->loadClass($class_name);
    }
}

// Fonction globale pour accéder à l'autoload isolé
function pdf_builder_get_isolated_autoloader()
{
    return IsolatedAutoloader::getInstance();
}