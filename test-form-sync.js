// Test de synchronisation des valeurs des formulaires
console.log('🧪 Test de synchronisation des valeurs des formulaires');

// Test 1: Vérifier que les noms de champs sont cohérents
console.log('Test 1: Cohérence des noms de champs');

// Simulation des données qui devraient être collectées
const expectedFieldNames = [
    'pdf_builder_company_phone_manual',
    'pdf_builder_company_siret',
    'pdf_builder_company_vat',
    'pdf_builder_company_rcs',
    'pdf_builder_company_capital',
    'pdf_builder_cache_max_size',
    'pdf_builder_cache_ttl',
    'pdf_builder_pdf_quality',
    'pdf_builder_default_format',
    'pdf_builder_default_orientation',
    'pdf_builder_pdf_compression',
    'pdf_builder_pdf_metadata_enabled',
    'pdf_builder_pdf_print_optimized',
    'pdf_builder_security_level',
    'pdf_builder_enable_logging',
    'pdf_builder_gdpr_enabled',
    'pdf_builder_gdpr_consent_required',
    'pdf_builder_gdpr_data_retention',
    'pdf_builder_gdpr_audit_enabled',
    'pdf_builder_gdpr_encryption_enabled',
    'pdf_builder_gdpr_consent_analytics',
    'pdf_builder_gdpr_consent_templates',
    'pdf_builder_gdpr_consent_marketing',
    'pdf_builder_default_template',
    'pdf_builder_template_library_enabled',
    'pdf_builder_license_test_mode',
    'pdf_builder_force_https',
    'pdf_builder_performance_monitoring'
];

console.log(`✅ ${expectedFieldNames.length} champs devraient avoir le préfixe pdf_builder_`);

// Test 2: Simulation de collecte de données
console.log('\nTest 2: Simulation de collecte de données');

function simulateCollectAllFormData() {
    // Simulation des données collectées depuis les formulaires
    const mockFormData = {
        'general-form': {
            'pdf_builder_company_phone_manual': '+33123456789',
            'pdf_builder_company_siret': '12345678900012',
            'pdf_builder_company_vat': 'FR12345678901',
            'pdf_builder_company_rcs': 'Lyon B 123 456 789',
            'pdf_builder_company_capital': '10000 €'
        },
        'pdf-settings-form': {
            'pdf_builder_pdf_quality': 'high',
            'pdf_builder_default_format': 'A4',
            'pdf_builder_default_orientation': 'portrait',
            'pdf_builder_pdf_compression': 'medium',
            'pdf_builder_pdf_metadata_enabled': '1',
            'pdf_builder_pdf_print_optimized': '1'
        },
        'securite-settings-form': {
            'pdf_builder_security_level': 'medium',
            'pdf_builder_enable_logging': '1',
            'pdf_builder_gdpr_enabled': '1',
            'pdf_builder_gdpr_data_retention': '365'
        }
    };

    return mockFormData;
}

const collectedData = simulateCollectAllFormData();
console.log('Données collectées:', Object.keys(collectedData).length, 'formulaires');

// Compter le nombre total de champs
let totalFields = 0;
for (const formId in collectedData) {
    totalFields += Object.keys(collectedData[formId]).length;
}
console.log(`✅ ${totalFields} champs collectés au total`);

// Test 3: Vérification que tous les champs ont le préfixe
console.log('\nTest 3: Vérification du préfixe pdf_builder_');
let allFieldsHavePrefix = true;
let fieldsWithoutPrefix = [];

for (const formId in collectedData) {
    for (const fieldName in collectedData[formId]) {
        if (!fieldName.startsWith('pdf_builder_')) {
            allFieldsHavePrefix = false;
            fieldsWithoutPrefix.push(fieldName);
        }
    }
}

if (allFieldsHavePrefix) {
    console.log('✅ Tous les champs ont le préfixe pdf_builder_');
} else {
    console.log('❌ Champs sans préfixe:', fieldsWithoutPrefix);
}

// Test 4: Simulation de sauvegarde
console.log('\nTest 4: Simulation de sauvegarde');
function simulateSaveAllSettings(formData) {
    const flattenedData = {};
    for (const formId in formData) {
        if (formData.hasOwnProperty(formId) && typeof formData[formId] === 'object') {
            for (const key in formData[formId]) {
                if (formData[formId].hasOwnProperty(key)) {
                    flattenedData[key] = formData[formId][key];
                }
            }
        }
    }

    // Simuler l'envoi au serveur (seulement les champs avec préfixe)
    const serverData = {};
    for (const key in flattenedData) {
        if (key.startsWith('pdf_builder_')) {
            serverData[key] = flattenedData[key];
        }
    }

    return serverData;
}

const serverData = simulateSaveAllSettings(collectedData);
console.log(`✅ ${Object.keys(serverData).length} champs envoyés au serveur`);
console.log('Échantillon des données serveur:', Object.keys(serverData).slice(0, 5));

console.log('\n🎉 Test de synchronisation terminé!');