<?php
/**
 * Autoload optimisé pour TCPDF - Version allégée
 * Charge seulement les classes essentielles
 */

if (!class_exists('TCPDF')) {
    // Classes principales
    require_once __DIR__ . '/tcpdf.php';

    // Classes optionnelles (chargées à la demande)
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
