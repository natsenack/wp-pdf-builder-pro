#!/usr/bin/env php
<?php

/**
 * Script d'exécution des tests pour PDF Builder Pro
 *
 * Usage: php run-tests.php [test-class]
 * Exemple: php run-tests.php ImageConverterTest
 */

echo "🚀 PDF Builder Pro - Exécution des Tests\n";
echo "=======================================\n\n";

// Vérifier si PHPUnit est installé
$phpunit_path = __DIR__ . '/plugin/vendor/bin/phpunit';
if (!file_exists($phpunit_path)) {
    echo "❌ PHPUnit n'est pas trouvé dans plugin/vendor/bin/phpunit\n";
    echo "   Veuillez installer les dépendances avec: composer install\n";
    exit(1);
}

// Vérifier si le fichier de configuration existe
$config_file = __DIR__ . '/phpunit.xml';
if (!file_exists($config_file)) {
    echo "❌ Fichier de configuration phpunit.xml non trouvé\n";
    exit(1);
}

echo "✅ PHPUnit trouvé: $phpunit_path\n";
echo "✅ Configuration trouvée: $config_file\n\n";

// Préparer la commande
$cmd = "php \"$phpunit_path\" --configuration=\"$config_file\"";

// Ajouter le nom de la classe de test si spécifié
if ($argc > 1) {
    $test_class = $argv[1];
    $cmd .= " --filter $test_class";
    echo "🎯 Test ciblé: $test_class\n\n";
} else {
    echo "🎯 Exécution de tous les tests\n\n";
}

// Exécuter les tests
echo "📊 Résultats des tests:\n";
echo "----------------------\n";

$exit_code = 0;
passthru($cmd, $exit_code);

echo "\n" . str_repeat("=", 50) . "\n";

if ($exit_code === 0) {
    echo "✅ Tous les tests sont passés avec succès!\n";
} else {
    echo "❌ Certains tests ont échoué (code: $exit_code)\n";
}

echo "📁 Tests disponibles:\n";
$test_files = glob(__DIR__ . '/plugin/tests/*Test.php');
foreach ($test_files as $file) {
    $class_name = basename($file, '.php');
    echo "   - $class_name\n";
}

echo "\n💡 Pour exécuter un test spécifique:\n";
echo "   php run-tests.php NomDuTest\n";

exit($exit_code);