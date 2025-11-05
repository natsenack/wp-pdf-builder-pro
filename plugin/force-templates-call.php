<?php
/**
 * Script pour forcer l'appel à get_builtin_templates et voir les logs
 */

require_once '../../../wp-load.php';

echo "Forcing get_builtin_templates call...\n";

// Simuler l'appel AJAX
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}

require_once 'src/Managers/PDF_Builder_Template_Manager.php';

$template_manager = new PDF_Builder_Template_Manager(null);
$templates = $template_manager->get_builtin_templates();

echo "Done. Check error logs for debug information.\n";
echo "Templates found: " . count($templates) . "\n";