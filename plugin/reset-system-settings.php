<?php
/**
 * Script pour remettre les paramètres système aux valeurs par défaut
 */

// Inclure WordPress
require_once('../../../wp-load.php');

header('Content-Type: application/json');

try {
    // Valeurs par défaut
    $defaults = array(
        'pdf_builder_cache_enabled' => '1',
        'pdf_builder_cache_expiry' => '24',
        'pdf_builder_max_cache_size' => '100',
        'pdf_builder_auto_maintenance' => '0',
        'pdf_builder_auto_backup' => '0',
        'pdf_builder_backup_retention' => '30'
    );

    foreach ($defaults as $key => $value) {
        update_option($key, $value);
    }

    echo json_encode(array('success' => true, 'message' => 'Valeurs remises par défaut'));
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
?>