<?php
/**
 * Test du système de tutoriels PDF Builder Pro
 */

// Inclure les fichiers nécessaires
require_once '../../../wp-load.php';
require_once '../src/Tutorial/TutorialManager.php';

// Initialiser le gestionnaire de tutoriels
$tutorialManager = \WP_PDF_Builder_Pro\Tutorial\TutorialManager::getInstance();
$tutorialManager->init();

// Afficher le wizard de bienvenue pour les tests
$tutorialManager->showWelcomeWizard();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Tutoriels PDF Builder Pro</title>
    <link rel="stylesheet" href="../assets/css/tutorial.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/tutorial.js"></script>
</head>
<body>
    <h1>Test du système de tutoriels</h1>
    <p>Le wizard de bienvenue devrait s'afficher automatiquement.</p>

    <button onclick="window.pdfBuilderTutorialManager.showWelcomeWizard()">
        Afficher le wizard manuellement
    </button>

    <button onclick="window.pdfBuilderTutorialManager.startTutorial('welcome')">
        Démarrer le tutoriel d'accueil
    </button>
</body>
</html>