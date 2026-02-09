<?php

/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/natsenack/wp-pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
 * Version: 1.0.1.0
 * Author: Natsenack
 * Author URI: https://github.com/natsenack
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

// Direct access protection
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

// Définir les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PDF_BUILDER_PRO_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('PDF_BUILDER_PRO_ASSETS_PATH', plugin_dir_path(__FILE__) . 'assets/');
define('PDF_BUILDER_VERSION', '1.0.1.0');
define('PDF_BUILDER_PRO_VERSION', '1.0.1.0');

// Premium features constant (set to false for free version)
if (!defined('PDF_BUILDER_PREMIUM')) {
    define('PDF_BUILDER_PREMIUM', false);
}

// Charger le fichier bootstrap avec les fonctions utilitaires
require_once PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
