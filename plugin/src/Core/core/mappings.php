<?php
/**
 * PDF Builder Core Mappings Index
 *
 * Fichier d'index pour inclure toutes les classes de mappings centralisés
 */

if (!defined('ABSPATH')) {
    exit;
}

// Inclure toutes les classes de mappings centralisés
require_once __DIR__ . '/defaults.php';
require_once __DIR__ . '/config-manager.php';
require_once __DIR__ . '/canvas-mappings.php';
require_once __DIR__ . '/template-mappings.php';
require_once __DIR__ . '/js-mappings.php';
require_once __DIR__ . '/validation-mappings.php';
require_once __DIR__ . '/error-mappings.php';
require_once __DIR__ . '/security-mappings.php';
require_once __DIR__ . '/performance-mappings.php';
require_once __DIR__ . '/compatibility-mappings.php';
require_once __DIR__ . '/i18n-mappings.php';
require_once __DIR__ . '/config-mappings.php';
require_once __DIR__ . '/api-mappings.php';

// Classe principale pour accéder à tous les mappings
class PDF_Builder_Core_Mappings {

    /**
     * Obtenir une instance de classe de mappings
     */
    public static function get($mapping_type) {
        switch ($mapping_type) {
            case 'defaults':
                return 'PDF_Builder_Defaults';
            case 'config_manager':
                return 'PDF_Builder_Config_Manager';
            case 'canvas':
                return 'PDF_Builder_Canvas_Mappings';
            case 'template':
                return 'PDF_Builder_Template_Mappings';
            case 'js':
                return 'PDF_Builder_JS_Mappings';
            case 'validation':
                return 'PDF_Builder_Validation_Mappings';
            case 'error':
                return 'PDF_Builder_Error_Mappings';
            case 'security':
                return 'PDF_Builder_Security_Mappings';
            case 'performance':
                return 'PDF_Builder_Performance_Mappings';
            case 'compatibility':
                return 'PDF_Builder_Compatibility_Mappings';
            case 'i18n':
                return 'PDF_Builder_I18n_Mappings';
            case 'config':
                return 'PDF_Builder_Config_Mappings';
            case 'api':
                return 'PDF_Builder_API_Mappings';
            default:
                return null;
        }
    }

    /**
     * Vérifier si un type de mapping existe
     */
    public static function exists($mapping_type) {
        return self::get($mapping_type) !== null;
    }

    /**
     * Obtenir la liste de tous les types de mappings disponibles
     */
    public static function get_available_mappings() {
        return [
            'defaults',
            'config_manager',
            'canvas',
            'template',
            'js',
            'validation',
            'error',
            'security',
            'performance',
            'compatibility',
            'i18n',
            'config',
            'api'
        ];
    }

    /**
     * Appeler une méthode sur une classe de mappings
     */
    public static function call($mapping_type, $method, ...$args) {
        $class_name = self::get($mapping_type);

        if (!$class_name || !class_exists($class_name)) {
            return null;
        }

        if (!method_exists($class_name, $method)) {
            return null;
        }

        return call_user_func_array([$class_name, $method], $args);
    }
}

