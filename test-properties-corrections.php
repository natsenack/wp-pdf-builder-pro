<?php
// Wrapper pour exécuter les tests des corrections de propriétés
define('ABSPATH', dirname(__FILE__) . '/');
require_once 'includes/properties-corrections-test.php';

// Exécuter les tests
pdf_builder_test_properties_corrections();