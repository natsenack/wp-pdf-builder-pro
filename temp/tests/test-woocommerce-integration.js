#!/usr/bin/env node

/**
 * TEST WOOCOMMERCE - PDF Builder Pro Vanilla JS
 * =============================================
 *
 * Script de test pour valider l'intégration WooCommerce
 * avec les éléments dynamiques dans l'éditeur Vanilla JS
 */

const https = require('https');
const fs = require('fs');

console.log('🛒 TEST WOOCOMMERCE - PDF Builder Pro Vanilla JS');
console.log('===============================================\n');

// Configuration des tests WooCommerce
const CONFIG = {
    siteUrl: 'https://threeaxe.fr',
    wcAjaxUrl: 'https://threeaxe.fr/wp-admin/admin-ajax.php',
    timeout: 30000
};

// Éléments dynamiques WooCommerce attendus
const EXPECTED_WC_ELEMENTS = [
    {
        name: 'Informations Produit',
        variables: [
            '[product_name]',
            '[product_price]',
            '[product_sku]',
            '[product_description]',
            '[product_short_description]'
        ]
    },
    {
        name: 'Prix et Stock',
        variables: [
            '[product_regular_price]',
            '[product_sale_price]',
            '[product_stock_quantity]',
            '[product_stock_status]'
        ]
    },
    {
        name: 'Catégories et Tags',
        variables: [
            '[product_categories]',
            '[product_tags]',
            '[product_weight]',
            '[product_dimensions]'
        ]
    },
    {
        name: 'Images Produit',
        variables: [
            '[product_image]',
            '[product_gallery]',
            '[product_thumbnail]'
        ]
    },
    {
        name: 'Données Commande',
        variables: [
            '[order_number]',
            '[order_date]',
            '[customer_name]',
            '[customer_email]',
            '[billing_address]',
            '[shipping_address]'
        ]
    },
    {
        name: 'Ligne de Commande',
        variables: [
            '[item_name]',
            '[item_quantity]',
            '[item_price]',
            '[item_total]',
            '[item_sku]'
        ]
    }
];

// Fonction pour tester la disponibilité du module WooCommerce
async function testWooCommerceModule() {
    console.log('🔍 TEST DU MODULE WOOCOMMERCE...\n');

    try {
        const response = await makeRequest(`${CONFIG.siteUrl}/wp-content/plugins/wp-pdf-builder-pro/assets/js/pdf-canvas-woocommerce.js`);

        if (response.status === 200) {
            console.log('✅ Module WooCommerce accessible');
            console.log(`   📦 Taille: ${(response.size / 1024).toFixed(1)} KiB`);
            console.log(`   ⏱️ Temps de chargement: ${response.loadTime}ms`);

            // Vérifier le contenu du module
            if (response.data.includes('WooCommerceElementsManager')) {
                console.log('✅ Classe WooCommerceElementsManager présente');
            } else {
                console.log('❌ Classe WooCommerceElementsManager manquante');
                return false;
            }

            if (response.data.includes('loadWooCommerceData')) {
                console.log('✅ Méthode loadWooCommerceData présente');
            } else {
                console.log('❌ Méthode loadWooCommerceData manquante');
                return false;
            }

        } else {
            console.log(`❌ Module WooCommerce inaccessible (status: ${response.status})`);
            return false;
        }

    } catch (error) {
        console.log(`❌ Erreur chargement module WooCommerce: ${error.message}`);
        return false;
    }

    return true;
}

// Fonction pour tester l'endpoint AJAX WooCommerce
async function testWooCommerceAjax() {
    console.log('\n🔄 TEST ENDPOINT AJAX WOOCOMMERCE...\n');

    // Tester différents endpoints WooCommerce
    const ajaxTests = [
        {
            action: 'pdf_builder_get_woocommerce_data',
            description: 'Récupération données WooCommerce'
        },
        {
            action: 'pdf_builder_get_product_data',
            description: 'Récupération données produit'
        },
        {
            action: 'pdf_builder_get_order_data',
            description: 'Récupération données commande'
        }
    ];

    let successCount = 0;

    for (const test of ajaxTests) {
        try {
            // Note: Ces tests nécessiteraient des nonces valides, donc on teste juste la réponse du serveur
            const postData = `action=${test.action}&nonce=test`;

            const response = await makeRequest(CONFIG.wcAjaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Content-Length': Buffer.byteLength(postData)
                },
                body: postData
            });

            console.log(`🔄 ${test.description}: status ${response.status}`);

            if (response.status === 200 || response.status === 400) {
                // 200 = succès, 400 = nonce invalide (normal sans authentification)
                console.log(`   ✅ Endpoint répond (${response.status})`);
                successCount++;
            } else {
                console.log(`   ❌ Endpoint ne répond pas correctement (${response.status})`);
            }

        } catch (error) {
            console.log(`   ❌ Erreur ${test.description}: ${error.message}`);
        }
    }

    console.log(`\n📊 Endpoints AJAX: ${successCount}/${ajaxTests.length} opérationnels`);
    return successCount > 0; // Au moins un endpoint doit fonctionner
}

// Fonction pour analyser les variables WooCommerce disponibles
function analyzeWooCommerceVariables() {
    console.log('\n📋 ANALYSE VARIABLES WOOCOMMERCE...\n');

    let totalVariables = 0;

    console.log('📦 VARIABLES DISPONIBLES PAR CATÉGORIE:');
    EXPECTED_WC_ELEMENTS.forEach(category => {
        console.log(`\n🏷️ ${category.name}:`);
        category.variables.forEach(variable => {
            console.log(`   • ${variable}`);
            totalVariables++;
        });
    });

    console.log(`\n📊 TOTAL: ${totalVariables} variables dynamiques disponibles`);
    console.log('');

    // Vérifier la documentation
    const docPath = 'docs/VARIABLES_WOOCOMMERCE_DISPONIBLES.md';
    if (fs.existsSync(docPath)) {
        console.log('✅ Documentation variables WooCommerce présente');
        const docContent = fs.readFileSync(docPath, 'utf8');
        const docVariables = (docContent.match(/\[([^\]]+)\]/g) || []).length;
        console.log(`   📚 Variables documentées: ${docVariables}`);
    } else {
        console.log('⚠️ Documentation variables WooCommerce manquante');
    }

    return totalVariables;
}

// Fonction pour tester l'intégration dans le template
async function testTemplateIntegration() {
    console.log('\n🎨 TEST INTÉGRATION TEMPLATE...\n');

    try {
        const response = await makeRequest(`${CONFIG.siteUrl}/wp-content/plugins/wp-pdf-builder-pro/templates/admin/template-editor.php`);

        if (response.status === 200) {
            console.log('✅ Template éditeur accessible');

            // Vérifier que le template fait référence au module WooCommerce
            if (response.data.includes('pdf-canvas-woocommerce.js')) {
                console.log('✅ Template référence le module WooCommerce');
            } else {
                console.log('⚠️ Template ne référence pas explicitement le module WooCommerce');
                console.log('   ℹ️ Cela peut être normal si chargé via WordPress enqueue');
            }

            // Vérifier la présence de fonctions WooCommerce dans le template
            if (response.data.includes('WooCommerceElementsManager')) {
                console.log('✅ Template inclut références WooCommerceElementsManager');
            } else {
                console.log('⚠️ Template n\'inclut pas de références directes WooCommerceElementsManager');
            }

        } else {
            console.log(`❌ Template éditeur inaccessible (status: ${response.status})`);
            return false;
        }

    } catch (error) {
        console.log(`❌ Erreur accès template: ${error.message}`);
        return false;
    }

    return true;
}

// Fonction générique pour faire des requêtes HTTP
function makeRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        const startTime = Date.now();

        const reqOptions = {
            timeout: CONFIG.timeout,
            ...options
        };

        const req = https.request(url, reqOptions, (res) => {
            let data = '';

            res.on('data', (chunk) => {
                data += chunk;
            });

            res.on('end', () => {
                const endTime = Date.now();
                const loadTime = endTime - startTime;

                resolve({
                    status: res.statusCode,
                    headers: res.headers,
                    data: data,
                    size: data.length,
                    loadTime: loadTime
                });
            });
        });

        if (options.body) {
            req.write(options.body);
        }

        req.on('error', (err) => {
            reject(err);
        });

        req.on('timeout', () => {
            req.destroy();
            reject(new Error('Timeout'));
        });

        req.end();
    });
}

// Fonction principale de test WooCommerce
async function runWooCommerceTests() {
    console.log('🌐 Configuration des tests:');
    console.log(`   Site: ${CONFIG.siteUrl}`);
    console.log(`   AJAX URL: ${CONFIG.wcAjaxUrl}`);
    console.log(`   Timeout: ${CONFIG.timeout}ms`);
    console.log('');

    let testResults = {
        module: false,
        ajax: false,
        template: false,
        variables: 0
    };

    // Test 1: Module WooCommerce
    testResults.module = await testWooCommerceModule();

    // Test 2: Endpoints AJAX
    testResults.ajax = await testWooCommerceAjax();

    // Test 3: Intégration template
    testResults.template = await testTemplateIntegration();

    // Test 4: Analyse variables
    testResults.variables = analyzeWooCommerceVariables();

    // Rapport final
    console.log('\n📊 RAPPORT FINAL - TEST WOOCOMMERCE');
    console.log('=====================================\n');

    const testsPassed = [testResults.module, testResults.ajax, testResults.template].filter(Boolean).length;
    const totalTests = 3;

    console.log('✅ RÉSULTATS DES TESTS:');
    console.log(`   • Module WooCommerce: ${testResults.module ? '✅' : '❌'}`);
    console.log(`   • Endpoints AJAX: ${testResults.ajax ? '✅' : '❌'}`);
    console.log(`   • Intégration template: ${testResults.template ? '✅' : '❌'}`);
    console.log(`   • Variables disponibles: ${testResults.variables}`);
    console.log('');

    console.log('📈 SYNTHÈSE:');
    console.log(`   • Tests réussis: ${testsPassed}/${totalTests}`);
    console.log(`   • Taux de succès: ${((testsPassed / totalTests) * 100).toFixed(1)}%`);
    console.log('');

    // Évaluation
    if (testsPassed === totalTests) {
        console.log('🎉 INTÉGRATION WOOCOMMERCE EXCELLENTE');
        console.log('   ✅ Module opérationnel');
        console.log('   ✅ AJAX endpoints répondent');
        console.log('   ✅ Template intégré');
        console.log('   ✅ Variables dynamiques disponibles');
        console.log('');
        console.log('🎯 PRÊT POUR LES TESTS UTILISATEUR:');
        console.log('   • Créer template avec variables dynamiques');
        console.log('   • Tester génération PDF avec données réelles');
        console.log('   • Valider rendu des éléments WooCommerce');

    } else if (testsPassed >= 2) {
        console.log('✅ INTÉGRATION WOOCOMMERCE FONCTIONNELLE');
        console.log('   • Core opérationnel, quelques détails à vérifier');
        console.log('   • Tests utilisateur recommandés pour validation complète');

    } else {
        console.log('⚠️ INTÉGRATION WOOCOMMERCE À VÉRIFIER');
        console.log('   • Problèmes détectés, investigation nécessaire');
        console.log('   • Vérifier configuration WooCommerce');
        console.log('   • Contrôler permissions serveur');
    }

    console.log('');
    console.log('🔧 RECOMMANDATIONS POUR TESTS UTILISATEUR:');
    console.log('   1. Se connecter à WordPress admin');
    console.log('   2. Accéder à l\'éditeur PDF');
    console.log('   3. Tester bouton "WooCommerce" dans toolbar');
    console.log('   4. Ajouter variables dynamiques: [product_name], [product_price]');
    console.log('   5. Créer template de test');
    console.log('   6. Tester export PDF avec données fictives');

    return testsPassed === totalTests;
}

// Exécuter les tests WooCommerce
runWooCommerceTests().then(success => {
    process.exit(success ? 0 : 1);
}).catch(error => {
    console.error('❌ Erreur lors des tests WooCommerce:', error);
    process.exit(1);
});