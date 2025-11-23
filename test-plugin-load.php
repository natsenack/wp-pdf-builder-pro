<?php
// Test script pour vérifier le chargement du plugin
define('ABSPATH', __DIR__ . '/');
define('WP_DEBUG', true);

try {
    require_once 'plugin/bootstrap.php';
    echo 'Bootstrap chargé avec succès\n';

    if (class_exists('PDF_Builder\Admin\PdfBuilderAdmin')) {
        echo 'Classe PdfBuilderAdmin disponible\n';
    } else {
        echo 'Classe PdfBuilderAdmin NON disponible\n';
    }

    if (class_exists('PDF_Builder_Pro\Managers\PdfBuilderTemplateManager')) {
        echo 'Classe PdfBuilderTemplateManager disponible\n';
    } else {
        echo 'Classe PdfBuilderTemplateManager NON disponible\n';
    }

    echo 'Test terminé\n';
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . '\n';
}