<?php
require_once 'plugin/bootstrap.php';
global $wpdb;
$results = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%pdf_builder_canvas%color%' ORDER BY option_name");
foreach ($results as $row) {
    echo $row->option_name . ' = ' . $row->option_value . "\n";
}
?>