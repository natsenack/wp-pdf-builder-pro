<?php
// Wrapper pour le diagnostic des bordures des tableaux
define('ABSPATH', dirname(__FILE__) . '/');
require_once 'table-borders-diagnostic.php';

// Exécuter le diagnostic
pdf_builder_diagnose_table_borders();