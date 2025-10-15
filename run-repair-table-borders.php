<?php
// Wrapper pour la réparation des bordures des tableaux
define('ABSPATH', dirname(__FILE__) . '/');
require_once 'repair-table-borders.php';

// Exécuter la réparation
pdf_builder_repair_table_borders();