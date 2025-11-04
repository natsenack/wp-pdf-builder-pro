/**
 * Script de test JavaScript pour vérifier les fonctions AJAX des templates
 * À exécuter dans la console du navigateur sur la page d'administration des templates
 */

// Fonction utilitaire pour faire des appels AJAX
function testAjax(action, data = {}) {
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                nonce: pdf_builder_templates_nonce, // Assurez-vous que cette variable existe
                ...data
            },
            success: function(response) {
                console.log(`✅ ${action}:`, response);
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error(`❌ ${action}:`, error, xhr.responseText);
                reject(error);
            }
        });
    });
}

// Test 1: Récupérer les templates builtin
async function testGetBuiltinTemplates() {
    console.log('🧪 Test 1: Récupération des templates builtin');
    try {
        const response = await testAjax('get_builtin_templates');
        if (response.success && response.data) {
            console.log(`📋 ${response.data.length} templates builtin trouvés`);
            response.data.forEach(template => {
                console.log(`  - ${template.name} (${template.key})`);
            });
        }
    } catch (error) {
        console.error('Erreur lors du test des templates builtin');
    }
}

// Test 2: Créer un template à partir d'un builtin
async function testCreateFromBuiltin() {
    console.log('🧪 Test 2: Création d\'un template à partir d\'un builtin');
    try {
        const response = await testAjax('pdf_builder_install_builtin_template', {
            template_name: 'classic',
            custom_name: 'Test Classic Template'
        });

        if (response.success) {
            console.log(`📝 Template créé avec succès - ID: ${response.data.template_id}`);
            return response.data.template_id;
        }
    } catch (error) {
        console.error('Erreur lors de la création du template');
    }
    return null;
}

// Test 3: Charger un template
async function testLoadTemplate(templateId) {
    console.log('🧪 Test 3: Chargement d\'un template');
    try {
        const response = await testAjax('pdf_builder_load_template', {
            template_id: templateId
        });

        if (response.success && response.data) {
            console.log(`📖 Template chargé - Nom: ${response.data.name}`);
            console.log(`📊 ${response.data.elements ? response.data.elements.length : 0} éléments`);
        }
    } catch (error) {
        console.error('Erreur lors du chargement du template');
    }
}

// Test 4: Sauvegarder un template
async function testSaveTemplate(templateId) {
    console.log('🧪 Test 4: Sauvegarde d\'un template');
    try {
        // Créer des données de test
        const testData = {
            elements: [
                {
                    type: 'text',
                    content: 'Test element',
                    x: 10,
                    y: 10,
                    width: 100,
                    height: 20
                }
            ],
            page_settings: {
                size: 'A4',
                orientation: 'portrait'
            }
        };

        const response = await testAjax('pdf_builder_save_template', {
            template_id: templateId,
            template_name: 'Test Template Updated',
            template_data: JSON.stringify(testData)
        });

        if (response.success) {
            console.log(`💾 Template sauvegardé - ${response.data.element_count} éléments`);
        }
    } catch (error) {
        console.error('Erreur lors de la sauvegarde du template');
    }
}

// Fonction principale de test
async function runAllTests() {
    console.log('🚀 Démarrage des tests du cycle de vie des templates PDF Builder Pro');
    console.log('================================================');

    await testGetBuiltinTemplates();

    const templateId = await testCreateFromBuiltin();

    if (templateId) {
        await testLoadTemplate(templateId);
        await testSaveTemplate(templateId);
    }

    console.log('================================================');
    console.log('✅ Tests terminés');
}

// Exposer la fonction globale pour l'exécuter dans la console
window.testPDFTemplates = runAllTests;

// Message d'instruction
console.log('📝 Pour exécuter les tests, tapez: testPDFTemplates()');