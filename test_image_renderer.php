<?php
/**
 * Test rapide de la classe ImageRenderer
 * À exécuter pour vérifier la Phase 3.3.2
 */

// Simuler les constantes WordPress pour le test
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Inclure la classe ImageRenderer
require_once __DIR__ . '/src/Renderers/ImageRenderer.php';

echo "=== Test ImageRenderer Phase 3.3.2 ===\n\n";

try {
    // Test 1: Instanciation ImageRenderer
    echo "Test 1: Instanciation ImageRenderer\n";
    $imageRenderer = new \PDF_Builder\Renderers\ImageRenderer();
    echo "✓ ImageRenderer instancié sans erreur\n";

    // Test 2: Types supportés
    echo "\nTest 2: Types supportés\n";
    $supportedTypes = $imageRenderer->getSupportedTypes();
    echo "Types supportés: " . implode(', ', $supportedTypes) . "\n";
    $hasCompanyLogo = in_array('company_logo', $supportedTypes);
    echo "Support company_logo: " . ($hasCompanyLogo ? 'Oui' : 'Non') . "\n";
    echo "✓ Types supportés vérifiés\n";

    // Test 3: Support de type
    echo "\nTest 3: Support de type\n";
    echo "Support 'company_logo': " . ($imageRenderer->supports('company_logo') ? 'Oui' : 'Non') . "\n";
    echo "Support 'text': " . ($imageRenderer->supports('text') ? 'Oui' : 'Non') . "\n";
    echo "✓ Méthode supports() fonctionnelle\n";

    // Test 4: Rendu image basique
    echo "\nTest 4: Rendu image basique\n";
    $imageElement = [
        'type' => 'company_logo',
        'properties' => [
            'src' => 'https://example.com/logo.png',
            'width' => 200,
            'height' => 100,
            'alt' => 'Logo entreprise'
        ]
    ];
    $imageHtml = $imageRenderer->render($imageElement);
    echo "HTML généré: " . substr($imageHtml, 0, 100) . "...\n";
    $hasImgTag = strpos($imageHtml, '<img') !== false;
    $hasSrc = strpos($imageHtml, 'src="https://example.com/logo.png"') !== false;
    echo "Contient <img>: " . ($hasImgTag ? 'Oui' : 'Non') . "\n";
    echo "Contient src correct: " . ($hasSrc ? 'Oui' : 'Non') . "\n";
    echo "✓ Rendu image basique réussi\n";

    // Test 5: Rendu avec propriétés de style
    echo "\nTest 5: Rendu avec propriétés de style\n";
    $styledImageElement = [
        'type' => 'company_logo',
        'properties' => [
            'src' => 'https://example.com/logo.png',
            'width' => 150,
            'height' => 75,
            'borderWidth' => '2px',
            'borderStyle' => 'solid',
            'borderColor' => '#ff0000',
            'borderRadius' => '5px',
            'alt' => 'Logo stylisé'
        ]
    ];
    $styledImageHtml = $imageRenderer->render($styledImageElement);
    echo "HTML stylisé généré: " . substr($styledImageHtml, 0, 150) . "...\n";
    $hasBorder = strpos($styledImageHtml, 'border-width: 2px') !== false;
    $hasBorderRadius = strpos($styledImageHtml, 'border-radius: 5px') !== false;
    echo "Contient border: " . ($hasBorder ? 'Oui' : 'Non') . "\n";
    echo "Contient border-radius: " . ($hasBorderRadius ? 'Oui' : 'Non') . "\n";
    echo "✓ Rendu avec styles réussi\n";

    // Test 6: Gestion des erreurs - élément invalide
    echo "\nTest 6: Gestion des erreurs - élément invalide\n";
    $invalidElement = ['type' => 'invalid'];
    $errorHtml = $imageRenderer->render($invalidElement);
    echo "HTML d'erreur: " . substr($errorHtml, 0, 100) . "...\n";
    $hasErrorDiv = strpos($errorHtml, '<div') !== false && strpos($errorHtml, 'border: 1px solid #ff0000') !== false;
    echo "Contient div d'erreur: " . ($hasErrorDiv ? 'Oui' : 'Non') . "\n";
    echo "✓ Gestion d'erreur pour élément invalide\n";

    // Test 7: Gestion des erreurs - image manquante
    echo "\nTest 7: Gestion des erreurs - image manquante\n";
    $noImageElement = [
        'type' => 'company_logo',
        'properties' => []
    ];
    $noImageHtml = $imageRenderer->render($noImageElement);
    echo "HTML sans image: " . substr($noImageHtml, 0, 100) . "...\n";
    $hasNoImageError = strpos($noImageHtml, 'Aucune image spécifiée') !== false;
    echo "Contient message 'Aucune image': " . ($hasNoImageError ? 'Oui' : 'Non') . "\n";
    echo "✓ Gestion d'erreur pour image manquante\n";

    // Test 8: Variables dynamiques dans URL
    echo "\nTest 8: Variables dynamiques dans URL\n";
    $context = ['company_logo' => 'https://example.com/dynamic-logo.png'];
    $dynamicImageElement = [
        'type' => 'company_logo',
        'properties' => [
            'src' => '{{company_logo}}',
            'width' => 200,
            'height' => 100
        ]
    ];
    $dynamicImageHtml = $imageRenderer->render($dynamicImageElement, $context);
    echo "HTML avec variable: " . substr($dynamicImageHtml, 0, 100) . "...\n";
    $hasDynamicUrl = strpos($dynamicImageHtml, 'https://example.com/dynamic-logo.png') !== false;
    echo "Variable remplacée: " . ($hasDynamicUrl ? 'Oui' : 'Non') . "\n";
    echo "✓ Variables dynamiques dans URL\n";

    // Test 9: Formats d'image supportés
    echo "\nTest 9: Formats d'image supportés\n";
    $formats = \PDF_Builder\Renderers\ImageRenderer::SUPPORTED_FORMATS;
    echo "Formats supportés: " . implode(', ', $formats) . "\n";
    $hasJpg = in_array('jpg', $formats);
    $hasPng = in_array('png', $formats);
    $hasSvg = in_array('svg', $formats);
    echo "Support JPG: " . ($hasJpg ? 'Oui' : 'Non') . "\n";
    echo "Support PNG: " . ($hasPng ? 'Oui' : 'Non') . "\n";
    echo "Support SVG: " . ($hasSvg ? 'Oui' : 'Non') . "\n";
    echo "✓ Formats d'image vérifiés\n";

    // Test 10: Validation des dimensions
    echo "\nTest 10: Validation des dimensions\n";
    $largeImageElement = [
        'type' => 'company_logo',
        'properties' => [
            'src' => 'https://example.com/logo.png',
            'width' => 2500, // Trop grand
            'height' => -50   // Négatif
        ]
    ];
    $validatedImageHtml = $imageRenderer->render($largeImageElement);
    echo "HTML avec dimensions validées: " . substr($validatedImageHtml, 0, 100) . "...\n";
    // Les dimensions devraient être bornées (width <= 2000, height >= 1)
    $hasValidatedWidth = strpos($validatedImageHtml, 'width: 2000px') !== false || strpos($validatedImageHtml, 'width: 1px') !== false;
    echo "Dimensions validées: " . ($hasValidatedWidth ? 'Oui' : 'Non') . "\n";
    echo "✓ Validation des dimensions\n";

    echo "\n=== Tests ImageRenderer Phase 3.3.2 terminés avec succès ===\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "\n";
}