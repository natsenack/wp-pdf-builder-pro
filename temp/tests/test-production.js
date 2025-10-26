#!/usr/bin/env node

/**
 * TEST DE PRODUCTION - PDF Builder Pro Vanilla JS
 * ===============================================
 *
 * Script de validation post-déploiement pour vérifier que
 * le système Vanilla JS fonctionne correctement en production.
 */

const fs = require('fs');
const path = require('path');
const https = require('https');

console.log('🧪 TEST DE PRODUCTION - PDF Builder Pro Vanilla JS');
console.log('================================================\n');

// Configuration du test
const CONFIG = {
    siteUrl: 'https://threeaxe.fr',
    pluginPath: '/wp-content/plugins/wp-pdf-builder-pro',
    timeout: 30000,
    retries: 3
};

// Tests à effectuer
const TESTS = [
    {
        name: 'Bundle JavaScript accessible',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/dist/pdf-builder-admin-debug.js`,
        expectedStatus: 200,
        expectedContent: 'PDFCanvasVanilla'
    },
    {
        name: 'Module principal Vanilla JS',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/pdf-canvas-vanilla.js`,
        expectedStatus: 200,
        expectedContent: 'class PDFCanvasVanilla'
    },
    {
        name: 'Module éléments',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/pdf-canvas-elements.js`,
        expectedStatus: 200,
        expectedContent: 'ELEMENT_PROPERTY_RESTRICTIONS'
    },
    {
        name: 'Module rendu Canvas',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/pdf-canvas-renderer.js`,
        expectedStatus: 200,
        expectedContent: 'class PDFCanvasRenderer'
    },
    {
        name: 'Module événements',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/pdf-canvas-events.js`,
        expectedStatus: 200,
        expectedContent: 'class PDFCanvasEventManager'
    },
    {
        name: 'Module WooCommerce',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/assets/js/pdf-canvas-woocommerce.js`,
        expectedStatus: 200,
        expectedContent: 'class WooCommerceElementsManager'
    },
    {
        name: 'Template éditeur mis à jour',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/templates/admin/template-editor.php`,
        expectedStatus: 200,
        expectedContent: 'pdf-canvas-vanilla.js'
    },
    {
        name: 'Configuration Webpack',
        url: `${CONFIG.siteUrl}${CONFIG.pluginPath}/config/build/webpack.config.js`,
        expectedStatus: 200,
        expectedContent: 'pdf-canvas-vanilla'
    }
];

// Fonction pour effectuer une requête HTTP
function makeRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        const req = https.get(url, { timeout: CONFIG.timeout, ...options }, (res) => {
            let data = '';

            res.on('data', (chunk) => {
                data += chunk;
            });

            res.on('end', () => {
                resolve({
                    status: res.statusCode,
                    headers: res.headers,
                    data: data
                });
            });
        });

        req.on('error', (err) => {
            reject(err);
        });

        req.on('timeout', () => {
            req.destroy();
            reject(new Error('Timeout'));
        });
    });
}

// Fonction pour effectuer un test avec retry
async function performTest(test, retryCount = 0) {
    try {
        console.log(`🔍 Test: ${test.name}`);

        const response = await makeRequest(test.url);

        // Vérifier le status code
        if (response.status !== test.expectedStatus) {
            throw new Error(`Status code inattendu: ${response.status} (attendu: ${test.expectedStatus})`);
        }

        // Vérifier le contenu si spécifié
        if (test.expectedContent && !response.data.includes(test.expectedContent)) {
            throw new Error(`Contenu attendu non trouvé: "${test.expectedContent}"`);
        }

        console.log(`   ✅ ${test.name} - SUCCÈS\n`);
        return { success: true, test: test.name };

    } catch (error) {
        console.log(`   ❌ ${test.name} - ÉCHEC: ${error.message}`);

        if (retryCount < CONFIG.retries - 1) {
            console.log(`   🔄 Retry ${retryCount + 1}/${CONFIG.retries} dans 2 secondes...`);
            await new Promise(resolve => setTimeout(resolve, 2000));
            return performTest(test, retryCount + 1);
        }

        console.log(`   ❌ ${test.name} - ÉCHEC DÉFINITIF\n`);
        return { success: false, test: test.name, error: error.message };
    }
}

// Fonction principale
async function runProductionTests() {
    console.log('🌐 Configuration des tests:');
    console.log(`   Site: ${CONFIG.siteUrl}`);
    console.log(`   Plugin: ${CONFIG.pluginPath}`);
    console.log(`   Timeout: ${CONFIG.timeout}ms`);
    console.log(`   Retries: ${CONFIG.retries}\n`);

    console.log('🚀 Démarrage des tests de production...\n');

    const results = [];
    let successCount = 0;

    for (const test of TESTS) {
        const result = await performTest(test);
        results.push(result);
        if (result.success) successCount++;
    }

    // Résumé des résultats
    console.log('📊 RÉSULTATS DES TESTS DE PRODUCTION');
    console.log('=====================================');
    console.log(`✅ Tests réussis: ${successCount}`);
    console.log(`❌ Tests échoués: ${results.length - successCount}`);
    console.log(`📈 Taux de succès: ${((successCount / results.length) * 100).toFixed(1)}%\n`);

    if (successCount === results.length) {
        console.log('🎉 TOUS LES TESTS SONT RÉUSSIS !');
        console.log('   Le système Vanilla JS fonctionne correctement en production.');
        console.log('\n📋 PROCHAINES ÉTAPES:');
        console.log('   1. Tester l\'éditeur PDF dans l\'admin WordPress');
        console.log('   2. Créer un PDF de test');
        console.log('   3. Vérifier l\'intégration WooCommerce');
        console.log('   4. Monitorer les performances');
    } else {
        console.log('⚠️  CERTAINS TESTS ONT ÉCHOUÉ');
        console.log('   Vérifiez les erreurs ci-dessus et corrigez les problèmes.');
        console.log('\n🔧 TESTS ÉCHOUÉS:');
        results.filter(r => !r.success).forEach(result => {
            console.log(`   • ${result.test}: ${result.error}`);
        });
    }

    return successCount === results.length;
}

// Exécuter les tests
runProductionTests().then(success => {
    process.exit(success ? 0 : 1);
}).catch(error => {
    console.error('❌ Erreur lors de l\'exécution des tests:', error);
    process.exit(1);
});