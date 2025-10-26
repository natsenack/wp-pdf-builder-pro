#!/usr/bin/env node

/**
 * Script de validation pré-déploiement
 * Vérifie que tous les fichiers nécessaires sont présents et valides
 */

const fs = require('fs');
const path = require('path');

console.log('🚀 VALIDATION PRÉ-DÉPLOIEMENT - PDF Builder Pro Vanilla JS');
console.log('==========================================================');

const checks = {
    passed: 0,
    failed: 0,
    warnings: 0
};

function checkFile(filePath, description, required = true) {
    const fullPath = path.join(__dirname, filePath);
    const exists = fs.existsSync(fullPath);

    if (exists) {
        const stats = fs.statSync(fullPath);
        const size = stats.size;
        console.log(`✅ ${description}: ${filePath} (${(size / 1024).toFixed(1)} KiB)`);
        checks.passed++;
        return true;
    } else if (required) {
        console.log(`❌ ${description}: ${filePath} (MANQUANT)`);
        checks.failed++;
        return false;
    } else {
        console.log(`⚠️  ${description}: ${filePath} (optionnel, absent)`);
        checks.warnings++;
        return false;
    }
}

function checkBundle() {
    const bundlePath = path.join(__dirname, 'assets', 'js', 'dist', 'pdf-builder-admin-debug.js');
    if (fs.existsSync(bundlePath)) {
        const stats = fs.statSync(bundlePath);
        const sizeKB = (stats.size / 1024).toFixed(1);

        // Vérifier que le bundle n'est pas trop gros (doit être < 200 KiB)
        if (stats.size < 200 * 1024) {
            console.log(`✅ Bundle JavaScript: assets/js/dist/pdf-builder-admin-debug.js (${sizeKB} KiB)`);
            checks.passed++;
            return true;
        } else {
            console.log(`❌ Bundle JavaScript trop gros: ${sizeKB} KiB (max 200 KiB)`);
            checks.failed++;
            return false;
        }
    } else {
        console.log('❌ Bundle JavaScript manquant: assets/js/dist/pdf-builder-admin-debug.js');
        checks.failed++;
        return false;
    }
}

function checkModules() {
    const modules = [
        'pdf-canvas-vanilla.js',
        'pdf-canvas-elements.js',
        'pdf-canvas-woocommerce.js',
        'pdf-canvas-customization.js',
        'pdf-canvas-renderer.js',
        'pdf-canvas-events.js',
        'pdf-canvas-render-utils.js',
        'pdf-canvas-selection.js',
        'pdf-canvas-properties.js',
        'pdf-canvas-layers.js',
        'pdf-canvas-export.js',
        'pdf-canvas-optimizer.js',
        'pdf-canvas-tests.js'
    ];

    let moduleCount = 0;
    modules.forEach(module => {
        const modulePath = path.join(__dirname, 'assets', 'js', module);
        if (fs.existsSync(modulePath)) {
            moduleCount++;
        }
    });

    if (moduleCount === modules.length) {
        console.log(`✅ Modules Vanilla JS: ${moduleCount}/${modules.length} présents`);
        checks.passed++;
        return true;
    } else {
        console.log(`❌ Modules Vanilla JS incomplets: ${moduleCount}/${modules.length} présents`);
        checks.failed++;
        return false;
    }
}

function checkTemplate() {
    const templatePath = path.join(__dirname, 'templates', 'admin', 'template-editor.php');
    if (fs.existsSync(templatePath)) {
        const content = fs.readFileSync(templatePath, 'utf8');

        // Vérifier que le template utilise l'approche hybride Vanilla JS
        // (attend que les scripts soient chargés par WordPress enqueue)
        if (content.includes('pdfBuilderInitVanilla') &&
            content.includes('waitForScripts') &&
            content.includes('PDFCanvasVanilla')) {
            console.log('✅ Template WordPress: Utilise l\'approche hybride Vanilla JS');
            checks.passed++;
            return true;
        } else {
            console.log('❌ Template WordPress: N\'utilise pas l\'approche hybride Vanilla JS');
            checks.failed++;
            return false;
        }
    } else {
        console.log('❌ Template WordPress manquant');
        checks.failed++;
        return false;
    }
}

function checkConfig() {
    const webpackPath = path.join(__dirname, 'config', 'build', 'webpack.config.js');
    if (fs.existsSync(webpackPath)) {
        const content = fs.readFileSync(webpackPath, 'utf8');

        // Vérifier que webpack utilise les fichiers Vanilla JS
        if (content.includes('pdf-canvas-vanilla.js')) {
            console.log('✅ Configuration Webpack: Utilise les modules Vanilla JS');
            checks.passed++;
            return true;
        } else {
            console.log('❌ Configuration Webpack: N\'utilise pas les modules Vanilla JS');
            checks.failed++;
            return false;
        }
    } else {
        console.log('❌ Configuration Webpack manquante');
        checks.failed++;
        return false;
    }
}

// Exécuter tous les checks
console.log('\n📦 VÉRIFICATION DES FICHIERS...');
checkBundle();
checkModules();

console.log('\n🔧 VÉRIFICATION DE LA CONFIGURATION...');
checkTemplate();
checkConfig();

console.log('\n📚 VÉRIFICATION DE LA DOCUMENTATION...');
checkFile('README.md', 'Documentation principale', true);
checkFile('docs/MIGRATION_VANILLA_JS.md', 'Guide de migration', true);

console.log('\n🎨 VÉRIFICATION DES ASSETS...');
checkFile('assets/css/editor.css', 'Styles CSS éditeur', true);
checkFile('assets/css/pdf-builder-admin.css', 'Styles CSS admin', true);

console.log('\n⚙️  VÉRIFICATION DES SCRIPTS DE DÉPLOIEMENT...');
checkFile('tools/ftp-deploy-simple.ps1', 'Script déploiement FTP', true);
checkFile('tools/DEPLOYMENT-GUIDE.md', 'Guide déploiement', true);

// Résumé final
console.log('\n' + '='.repeat(60));
console.log('📊 RÉSULTATS DE VALIDATION');
console.log('='.repeat(60));

console.log(`✅ Checks réussis: ${checks.passed}`);
console.log(`❌ Checks échoués: ${checks.failed}`);
console.log(`⚠️  Avertissements: ${checks.warnings}`);

const totalChecks = checks.passed + checks.failed + checks.warnings;
const successRate = ((checks.passed / totalChecks) * 100).toFixed(1);

if (checks.failed === 0) {
    console.log(`\n🎉 VALIDATION RÉUSSIE (${successRate}%) - PRÊT POUR LE DÉPLOIEMENT !`);
    console.log('\n🚀 Commandes de déploiement:');
    console.log('   cd tools/');
    console.log('   .\\ftp-deploy-simple.ps1');
    process.exit(0);
} else {
    console.log(`\n❌ VALIDATION ÉCHOUÉE (${successRate}%) - CORRECTIONS REQUISES`);
    console.log('\n🔧 Corrigez les erreurs ci-dessus avant le déploiement.');
    process.exit(1);
}