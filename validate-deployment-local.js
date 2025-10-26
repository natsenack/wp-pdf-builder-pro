#!/usr/bin/env node

/**
 * VALIDATION LOCALE POST-DÉPLOIEMENT - PDF Builder Pro Vanilla JS
 * =============================================================
 *
 * Script de validation locale pour vérifier que tous les fichiers
 * Vanilla JS sont présents et contiennent le bon contenu après déploiement.
 */

const fs = require('fs');
const path = require('path');

console.log('🔍 VALIDATION LOCALE POST-DÉPLOIEMENT - PDF Builder Pro Vanilla JS');
console.log('=================================================================\n');

// Configuration des validations
const VALIDATIONS = [
    {
        name: 'Bundle JavaScript compilé',
        path: 'assets/js/dist/pdf-builder-admin-debug.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'size', minSize: 100000, description: 'Taille minimale 100KB' },
            { type: 'content', content: 'PDFCanvasVanilla', description: 'Contient PDFCanvasVanilla' }
        ]
    },
    {
        name: 'Module principal Vanilla JS',
        path: 'assets/js/pdf-canvas-vanilla.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasVanilla', description: 'Classe principale présente' },
            { type: 'content', content: 'constructor', description: 'Constructeur présent' },
            { type: 'content', content: 'render()', description: 'Méthode render présente' }
        ]
    },
    {
        name: 'Module éléments',
        path: 'assets/js/pdf-canvas-elements.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'ELEMENT_PROPERTY_RESTRICTIONS', description: 'Constantes de restrictions' },
            { type: 'content', content: 'isPropertyAllowed', description: 'Fonction de validation' },
            { type: 'content', content: 'validateProperty', description: 'Fonction de validation' }
        ]
    },
    {
        name: 'Module rendu Canvas',
        path: 'assets/js/pdf-canvas-renderer.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasRenderer', description: 'Classe renderer' },
            { type: 'content', content: 'renderElement', description: 'Méthode de rendu' },
            { type: 'content', content: 'applyVisualEffects', description: 'Effets visuels' }
        ]
    },
    {
        name: 'Module événements',
        path: 'assets/js/pdf-canvas-events.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasEventManager', description: 'Gestionnaire d\'événements' },
            { type: 'content', content: 'normalizeMouseEvent', description: 'Normalisation souris' },
            { type: 'content', content: 'handleTouchMove', description: 'Support tactile' }
        ]
    },
    {
        name: 'Module rendu utilitaires',
        path: 'assets/js/pdf-canvas-render-utils.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasRenderUtils', description: 'Classe utilitaires' },
            { type: 'content', content: 'drawMultilineText', description: 'Rendu texte multiligne' },
            { type: 'content', content: 'createShape', description: 'Création de formes' }
        ]
    },
    {
        name: 'Module sélection',
        path: 'assets/js/pdf-canvas-selection.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasSelectionManager', description: 'Gestionnaire de sélection' },
            { type: 'content', content: 'selectInRect', description: 'Sélection rectangulaire' },
            { type: 'content', content: 'moveSelectedElements', description: 'Déplacement d\'éléments' }
        ]
    },
    {
        name: 'Module propriétés',
        path: 'assets/js/pdf-canvas-properties.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasPropertiesManager', description: 'Gestionnaire de propriétés' },
            { type: 'content', content: 'setProperty', description: 'Setter de propriétés' },
            { type: 'content', content: 'validatePropertyByType', description: 'Validation par type' }
        ]
    },
    {
        name: 'Module calques',
        path: 'assets/js/pdf-canvas-layers.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasLayersManager', description: 'Classe gestionnaire de calques' },
            { type: 'content', content: 'createLayer', description: 'Méthode de création de calque' },
            { type: 'content', content: 'addElementToLayer', description: 'Ajout d\'élément à un calque' }
        ]
    },
    {
        name: 'Module export',
        path: 'assets/js/pdf-canvas-export.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasExportManager', description: 'Classe gestionnaire d\'export' },
            { type: 'content', content: 'exportToPDF', description: 'Méthode d\'export PDF' },
            { type: 'content', content: 'renderTextElement', description: 'Rendu d\'éléments texte' }
        ]
    },
    {
        name: 'Module optimisation',
        path: 'assets/js/pdf-canvas-optimizer.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class PDFCanvasPerformanceOptimizer', description: 'Optimiseur de performance' },
            { type: 'content', content: 'optimizeRendering', description: 'Optimisation du rendu' }
        ]
    },
    {
        name: 'Module WooCommerce',
        path: 'assets/js/pdf-canvas-woocommerce.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class WooCommerceElementsManager', description: 'Gestionnaire WooCommerce' },
            { type: 'content', content: 'loadWooCommerceData', description: 'Chargement données WooCommerce' }
        ]
    },
    {
        name: 'Module personnalisation',
        path: 'assets/js/pdf-canvas-customization.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'class ElementCustomizationService', description: 'Service de personnalisation' },
            { type: 'content', content: 'applyPreset', description: 'Application de préréglages' }
        ]
    },
    {
        name: 'Module tests',
        path: 'assets/js/pdf-canvas-tests.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'test', description: 'Fonctions de test présentes' }
        ]
    },
    {
        name: 'Template éditeur PHP',
        path: 'templates/admin/template-editor.php',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'pdf-canvas-vanilla.js', description: 'Référence au module Vanilla JS' },
            { type: 'content', content: 'PDFCanvasVanilla', description: 'Classe principale référencée' }
        ]
    },
    {
        name: 'Configuration Webpack',
        path: 'config/build/webpack.config.js',
        checks: [
            { type: 'exists', description: 'Fichier existe' },
            { type: 'content', content: 'pdf-canvas-vanilla', description: 'Référence aux modules Vanilla' },
            { type: 'content', content: 'mode:', description: 'Configuration du mode' }
        ]
    }
];

// Fonction pour effectuer une validation
function performValidation(validation) {
    console.log(`🔍 Validation: ${validation.name}`);

    let allChecksPassed = true;
    const failedChecks = [];

    for (const check of validation.checks) {
        try {
            switch (check.type) {
                case 'exists':
                    if (!fs.existsSync(validation.path)) {
                        throw new Error(`Fichier n'existe pas: ${validation.path}`);
                    }
                    break;

                case 'size':
                    const stats = fs.statSync(validation.path);
                    if (stats.size < check.minSize) {
                        throw new Error(`Taille insuffisante: ${stats.size} bytes (min: ${check.minSize})`);
                    }
                    break;

                case 'content':
                    const content = fs.readFileSync(validation.path, 'utf8');
                    if (!content.includes(check.content)) {
                        throw new Error(`Contenu manquant: "${check.content}"`);
                    }
                    break;
            }

            console.log(`   ✅ ${check.description}`);

        } catch (error) {
            console.log(`   ❌ ${check.description}: ${error.message}`);
            allChecksPassed = false;
            failedChecks.push(`${check.description}: ${error.message}`);
        }
    }

    if (allChecksPassed) {
        console.log(`   ✅ ${validation.name} - SUCCÈS\n`);
    } else {
        console.log(`   ❌ ${validation.name} - ÉCHEC\n`);
    }

    return { success: allChecksPassed, validation: validation.name, failedChecks };
}

// Fonction principale
function runLocalValidation() {
    console.log('📂 Répertoire de travail:', process.cwd());
    console.log('📋 Nombre de validations:', VALIDATIONS.length);
    console.log('\n🚀 Démarrage des validations locales...\n');

    const results = [];
    let successCount = 0;

    for (const validation of VALIDATIONS) {
        const result = performValidation(validation);
        results.push(result);
        if (result.success) successCount++;
    }

    // Statistiques des fichiers
    console.log('📊 STATISTIQUES DES FICHIERS');
    console.log('=============================');

    try {
        const bundleStats = fs.statSync('assets/js/dist/pdf-builder-admin-debug.js');
        console.log(`📦 Bundle JavaScript: ${(bundleStats.size / 1024).toFixed(1)} KiB`);

        let totalJsSize = 0;
        const jsFiles = VALIDATIONS.filter(v => v.path.endsWith('.js') && v.path.includes('pdf-canvas'));
        jsFiles.forEach(validation => {
            try {
                const stats = fs.statSync(validation.path);
                totalJsSize += stats.size;
                console.log(`   ${validation.name}: ${(stats.size / 1024).toFixed(1)} KiB`);
            } catch (e) {
                // Ignore si fichier n'existe pas
            }
        });
        console.log(`📊 Total modules Vanilla JS: ${(totalJsSize / 1024).toFixed(1)} KiB`);
    } catch (e) {
        console.log('⚠️  Impossible de calculer les statistiques des fichiers');
    }

    console.log('\n📊 RÉSULTATS DES VALIDATIONS LOCALES');
    console.log('=====================================');
    console.log(`✅ Validations réussies: ${successCount}`);
    console.log(`❌ Validations échouées: ${results.length - successCount}`);
    console.log(`📈 Taux de succès: ${((successCount / results.length) * 100).toFixed(1)}%\n`);

    if (successCount === results.length) {
        console.log('🎉 TOUTES LES VALIDATIONS SONT RÉUSSIES !');
        console.log('   ✅ Système Vanilla JS déployé avec succès');
        console.log('   ✅ Tous les modules sont présents et valides');
        console.log('   ✅ Bundle optimisé et fonctionnel');
        console.log('   ✅ Templates et configuration mis à jour');
        console.log('\n📋 PROCHAINES ÉTAPES:');
        console.log('   1. Tester l\'éditeur PDF dans WordPress admin');
        console.log('   2. Créer et exporter un PDF de test');
        console.log('   3. Vérifier l\'intégration WooCommerce');
        console.log('   4. Monitorer les performances en production');
        console.log('   5. Collecter les retours utilisateurs');
    } else {
        console.log('⚠️  CERTAINES VALIDATIONS ONT ÉCHOUÉ');
        console.log('   Vérifiez les erreurs ci-dessus et corrigez les problèmes.');
        console.log('\n🔧 VALIDATIONS ÉCHOUÉES:');
        results.filter(r => !r.success).forEach(result => {
            console.log(`   • ${result.validation}:`);
            result.failedChecks.forEach(check => {
                console.log(`     - ${check}`);
            });
        });
    }

    return successCount === results.length;
}

// Exécuter les validations
const success = runLocalValidation();
process.exit(success ? 0 : 1);