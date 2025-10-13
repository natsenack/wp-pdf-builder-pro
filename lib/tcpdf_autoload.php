<?php
/**
 * Autoload personnalisé pour TCPDF allégé
 * Remplace vendor/autoload.php
 */

// Chemins vers les classes TCPDF
$tcpdf_paths = array(
    __DIR__ . '/tcpdf/tcpdf.php',
    __DIR__ . '/tcpdf/tcpdf_parser.php',
    __DIR__ . '/tcpdf/tcpdf_import.php',
    __DIR__ . '/tcpdf/tcpdf_static.php',
    __DIR__ . '/tcpdf/tcpdf_colors.php',
    __DIR__ . '/tcpdf/tcpdf_fonts.php',
    __DIR__ . '/tcpdf/tcpdf_images.php',
    __DIR__ . '/tcpdf/tcpdf_autoconfig.php'
);

// Inclure toutes les classes nécessaires
foreach ($tcpdf_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
    } else {
        die("Fichier TCPDF manquant: $path\n");
    }
}

// Constantes TCPDF essentielles
if (!defined('PDF_PAGE_FORMAT')) {
    define('PDF_PAGE_FORMAT', 'A4');
}
if (!defined('PDF_PAGE_ORIENTATION')) {
    define('PDF_PAGE_ORIENTATION', 'P');
}
if (!defined('PDF_UNIT')) {
    define('PDF_UNIT', 'mm');
}

