<?php
/**
 * TCPDF Autoload Wrapper - Version ultra-minimale pour éviter les problèmes de permissions
 */

// Définir les constantes essentielles AVANT tout chargement
if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
    define('K_TCPDF_EXTERNAL_CONFIG', true);
}

// Définir la version TCPDF (requise par tcpdf_font_data.php)
if (!defined('K_TCPDF_VERSION')) {
    define('K_TCPDF_VERSION', '6.6.2');
}

// Éviter les problèmes avec DOCUMENT_ROOT
if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/tmp/';
}

// Charger directement tcpdf.php sans passer par autoload.php qui peut causer des problèmes
require_once __DIR__ . '/tcpdf_static.php';
// require_once __DIR__ . '/tcpdf_font_data.php'; // Fichier manquant, commenté temporairement
require_once __DIR__ . '/tcpdf_fonts.php';
require_once __DIR__ . '/tcpdf_colors.php';
require_once __DIR__ . '/tcpdf_images.php';
require_once __DIR__ . '/tcpdf.php';

// Autoloader pour les classes optionnelles
if (!class_exists('TCPDF_Autoloader')) {
    class TCPDF_Autoloader {
        public static function load($class) {
            $classes = array(
                'TCPDF_PARSER' => 'tcpdf_parser.php',
                'TCPDF_IMPORT' => 'tcpdf_import.php',
                'TCPDF_1D_BARCODE' => 'tcpdf_barcodes_1d.php',
                'TCPDF_2D_BARCODE' => 'tcpdf_barcodes_2d.php',
            );

            if (isset($classes[$class])) {
                require_once __DIR__ . '/' . $classes[$class];
            }
        }
    }

    spl_autoload_register(array('TCPDF_Autoloader', 'load'));
}