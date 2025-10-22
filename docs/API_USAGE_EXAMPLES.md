# 📚 Exemples d'Utilisation des APIs - Phase 2.5.4

**📅 Date** : Décembre 2024
**📊 Progression** : Phase 2.5.4 en cours (0/4 sous-étapes)

---

## 🎯 Vue d'ensemble

Cette phase fournit des exemples pratiques d'utilisation des 4 endpoints AJAX du système d'aperçu unifié. Chaque exemple inclut :

- Code JavaScript complet et fonctionnel
- Gestion des erreurs et cas limites
- Scénarios d'usage réels
- Tests d'intégration simples

---

## 📋 Endpoints disponibles

| Endpoint | Action | Sécurité |
|----------|--------|----------|
| `wp_ajax_pdf_generate_preview` | Génère un aperçu | Rate limiting (10/min), nonce, permissions |
| `wp_ajax_pdf_validate_license` | Valide licence premium | Rate limiting (5/min), nonce |
| `wp_ajax_pdf_get_template_variables` | Récupère variables dynamiques | Rate limiting (20/min), nonce |
| `wp_ajax_pdf_export_canvas` | Exporte canvas (PDF/PNG/JPG) | Rate limiting (5/min), nonce, permissions |

---

## 🔧 Configuration commune

### Génération du nonce WordPress

```javascript
// Dans PHP (functions.php ou plugin)
function pdf_builder_get_nonce() {
    return wp_create_nonce('pdf_builder_preview_nonce');
}

// Dans JavaScript
const pdfNonce = '<?php echo pdf_builder_get_nonce(); ?>';
```

### Configuration AJAX commune

```javascript
// Configuration globale AJAX
const PDF_API_CONFIG = {
    baseUrl: ajaxurl, // WordPress ajaxurl
    timeout: 30000,   // 30 secondes
    retries: 2        // Nombre de tentatives
};

// Fonction utilitaire pour les appels AJAX
function pdfApiCall(action, data = {}, options = {}) {
    const ajaxData = {
        action: action,
        nonce: pdfNonce,
        ...data
    };

    return $.ajax({
        url: PDF_API_CONFIG.baseUrl,
        type: 'POST',
        data: ajaxData,
        timeout: options.timeout || PDF_API_CONFIG.timeout,
        dataType: 'json'
    }).fail(function(xhr, status, error) {
        console.error(`PDF API Error [${action}]:`, error);

        // Retry logic
        if (options.retryCount < PDF_API_CONFIG.retries) {
            options.retryCount = (options.retryCount || 0) + 1;
            console.log(`Retrying ${action} (attempt ${options.retryCount})`);
            return pdfApiCall(action, data, options);
        }

        throw error;
    });
}
```

---

## 📖 Exemples d'utilisation

### 1. 🎨 Génération d'aperçu (Canvas/Metabox)

#### Scénario : Aperçu depuis l'éditeur Canvas

```javascript
/**
 * Génère un aperçu depuis l'éditeur Canvas
 * Utilise des données fictives mais cohérentes
 */
function generateCanvasPreview() {
    // Récupérer les données du canvas depuis Fabric.js
    const canvasData = canvas.toJSON();
    const templateData = {
        customer_name: "Jean Dupont",
        customer_email: "jean.dupont@email.com",
        order_number: "CMD-2024-001",
        order_total: 299.99,
        company_name: "Ma Boutique",
        company_logo: "https://example.com/logo.png"
    };

    // Afficher loader
    showPreviewLoader();

    pdfApiCall('pdf_generate_preview', {
        mode: 'canvas',
        template_data: JSON.stringify(templateData),
        canvas_data: JSON.stringify(canvasData),
        format: 'html'
    })
    .done(function(response) {
        if (response.success) {
            // Ouvrir l'aperçu dans une modal
            openPreviewModal(response.data.preview_url, response.data.expires);
        } else {
            showErrorToast(response.data.message || 'Erreur de génération d\'aperçu');
        }
    })
    .fail(function(error) {
        showErrorToast('Erreur réseau lors de la génération d\'aperçu');
    })
    .always(function() {
        hidePreviewLoader();
    });
}

// Gestionnaire d'événement pour le bouton aperçu
$('#canvas-preview-btn').on('click', generateCanvasPreview);
```

#### Scénario : Aperçu depuis Metabox WooCommerce

```javascript
/**
 * Génère un aperçu depuis une commande WooCommerce
 * Utilise les données réelles de la commande
 */
function generateMetaboxPreview(orderId) {
    // Récupérer les données WooCommerce
    const orderData = getOrderData(orderId);

    const templateData = {
        customer_name: orderData.billing.first_name + ' ' + orderData.billing.last_name,
        customer_email: orderData.billing.email,
        order_number: orderData.number,
        order_total: parseFloat(orderData.total),
        order_date: orderData.date_created,
        company_name: wcSettings.companyName,
        company_logo: wcSettings.companyLogo
    };

    showPreviewLoader();

    pdfApiCall('pdf_generate_preview', {
        mode: 'metabox',
        template_data: JSON.stringify(templateData),
        order_id: orderId,
        format: 'html'
    })
    .done(function(response) {
        if (response.success) {
            openPreviewModal(response.data.preview_url, response.data.expires);
        } else {
            showErrorToast(response.data.message);
        }
    })
    .fail(function() {
        showErrorToast('Erreur lors de la génération d\'aperçu');
    })
    .always(function() {
        hidePreviewLoader();
    });
}

// Intégration WooCommerce
$(document).on('click', '.pdf-preview-btn', function() {
    const orderId = $(this).data('order-id');
    generateMetaboxPreview(orderId);
});
```

### 2. 🔑 Validation de licence premium

#### Scénario : Validation au chargement de l'admin

```javascript
/**
 * Valide la licence premium au chargement de l'admin
 * Active/désactive les fonctionnalités selon le résultat
 */
function validateLicenseOnLoad() {
    const savedLicense = localStorage.getItem('pdf_builder_license');

    if (!savedLicense) {
        // Mode freemium
        enableFreemiumFeatures();
        return;
    }

    pdfApiCall('pdf_validate_license', {
        license_key: savedLicense
    })
    .done(function(response) {
        if (response.success && response.data.valid) {
            // Licence valide - activer fonctionnalités premium
            enablePremiumFeatures(response.data);
            showSuccessToast('Licence premium activée');
        } else {
            // Licence invalide - revenir au freemium
            localStorage.removeItem('pdf_builder_license');
            enableFreemiumFeatures();
            showWarningToast('Licence invalide - fonctionnalités limitées activées');
        }
    })
    .fail(function() {
        // En cas d'erreur réseau, garder la licence actuelle
        console.warn('Impossible de valider la licence - garder le mode actuel');
    });
}

// Gestionnaire pour la saisie de licence
$('#license-input').on('input', function() {
    const licenseKey = $(this).val().toUpperCase();
    $(this).val(licenseKey); // Format automatique

    // Validation basique côté client
    const isValidFormat = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/.test(licenseKey);
    $('#license-submit-btn').prop('disabled', !isValidFormat);
});

$('#license-form').on('submit', function(e) {
    e.preventDefault();

    const licenseKey = $('#license-input').val();

    pdfApiCall('pdf_validate_license', {
        license_key: licenseKey
    })
    .done(function(response) {
        if (response.success && response.data.valid) {
            localStorage.setItem('pdf_builder_license', licenseKey);
            enablePremiumFeatures(response.data);
            showSuccessToast('Licence activée avec succès !');
            $('#license-modal').hide();
        } else {
            showErrorToast('Licence invalide ou expirée');
        }
    })
    .fail(function() {
        showErrorToast('Erreur de validation de licence');
    });
});
```

### 3. 📊 Récupération des variables dynamiques

#### Scénario : Chargement des variables dans l'éditeur

```javascript
/**
 * Charge les variables dynamiques disponibles pour l'autocomplétion
 */
function loadTemplateVariables() {
    const templateId = $('#template-selector').val();

    pdfApiCall('pdf_get_template_variables', {
        template_id: templateId,
        mode: 'canvas'
    })
    .done(function(response) {
        if (response.success) {
            populateVariableSelector(response.data.variables);
            updateVariableCategories(response.data.categories);
        } else {
            showErrorToast('Erreur de chargement des variables');
        }
    })
    .fail(function() {
        showErrorToast('Erreur réseau lors du chargement des variables');
    });
}

/**
 * Remplit le sélecteur de variables avec autocomplétion
 */
function populateVariableSelector(variables) {
    const $selector = $('#variable-selector');

    // Vider les options existantes
    $selector.empty();

    // Grouper par catégories
    const groupedVars = {};
    Object.keys(variables).forEach(varName => {
        const category = variables[varName].category || 'other';
        if (!groupedVars[category]) {
            groupedVars[category] = [];
        }
        groupedVars[category].push({
            name: varName,
            ...variables[varName]
        });
    });

    // Créer les options groupées
    Object.keys(groupedVars).forEach(category => {
        const $optgroup = $('<optgroup>').attr('label', category.toUpperCase());

        groupedVars[category].forEach(variable => {
            const $option = $('<option>')
                .val(variable.name)
                .text(`${variable.name} - ${variable.description}`)
                .data('variable', variable);

            $optgroup.append($option);
        });

        $selector.append($optgroup);
    });

    // Initialiser Select2 pour l'autocomplétion
    $selector.select2({
        placeholder: 'Rechercher une variable...',
        allowClear: true
    });
}

/**
 * Gestionnaire d'insertion de variable dans le canvas
 */
$('#insert-variable-btn').on('click', function() {
    const selectedVar = $('#variable-selector').val();

    if (selectedVar) {
        // Insérer {{variable}} dans le texte actif du canvas
        insertVariableIntoCanvas(selectedVar);
    }
});
```

#### Scénario : Variables dynamiques en mode Metabox

```javascript
/**
 * Charge les variables selon le contexte WooCommerce
 */
function loadMetaboxVariables(orderId) {
    pdfApiCall('pdf_get_template_variables', {
        mode: 'metabox'
    })
    .done(function(response) {
        if (response.success) {
            // Filtrer les variables selon les données disponibles
            const availableVars = filterAvailableVariables(response.data.variables, orderId);
            showVariableHelper(availableVars);
        }
    });
}

/**
 * Filtre les variables selon les données disponibles dans la commande
 */
function filterAvailableVariables(allVariables, orderId) {
    const orderData = getOrderData(orderId);
    const availableVars = {};

    Object.keys(allVariables).forEach(varName => {
        const variable = allVariables[varName];

        // Vérifier si les données requises sont disponibles
        if (isVariableAvailable(variable, orderData)) {
            availableVars[varName] = {
                ...variable,
                available: true
            };
        } else {
            availableVars[varName] = {
                ...variable,
                available: false,
                reason: 'Données manquantes dans la commande'
            };
        }
    });

    return availableVars;
}
```

### 4. 📤 Export du canvas

#### Scénario : Export PNG/JPG depuis l'éditeur

```javascript
/**
 * Exporte le canvas en image haute qualité
 */
function exportCanvasAsImage(format = 'png') {
    const canvasData = canvas.toJSON();
    const templateData = getCurrentTemplateData();

    // Demander le nom du fichier
    const filename = prompt('Nom du fichier (sans extension):', 'canvas-export');
    if (!filename) return;

    showExportLoader();

    pdfApiCall('pdf_export_canvas', {
        template_data: JSON.stringify(templateData),
        canvas_data: JSON.stringify(canvasData),
        format: format,
        quality: 95, // Qualité maximale
        filename: filename
    })
    .done(function(response) {
        if (response.success) {
            // Télécharger automatiquement
            const link = document.createElement('a');
            link.href = response.data.download_url;
            link.download = response.data.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showSuccessToast(`Export ${format.toUpperCase()} réussi !`);
        } else {
            showErrorToast(response.data.message || 'Erreur d\'export');
        }
    })
    .fail(function() {
        showErrorToast('Erreur réseau lors de l\'export');
    })
    .always(function() {
        hideExportLoader();
    });
}

// Gestionnaires pour les boutons d'export
$('#export-png-btn').on('click', () => exportCanvasAsImage('png'));
$('#export-jpg-btn').on('click', () => exportCanvasAsImage('jpg'));
```

#### Scénario : Export PDF depuis Metabox

```javascript
/**
 * Exporte la commande en PDF depuis la metabox
 */
function exportOrderAsPDF(orderId) {
    const orderData = getOrderData(orderId);
    const templateData = prepareOrderTemplateData(orderData);

    const filename = `${orderData.billing.last_name}_${orderData.number}`;

    showExportLoader();

    pdfApiCall('pdf_export_canvas', {
        template_data: JSON.stringify(templateData),
        format: 'pdf',
        quality: 100,
        filename: filename
    })
    .done(function(response) {
        if (response.success) {
            // Ouvrir dans un nouvel onglet pour prévisualisation
            window.open(response.data.download_url, '_blank');

            // Ou télécharger automatiquement
            // const link = document.createElement('a');
            // link.href = response.data.download_url;
            // link.download = response.data.filename;
            // document.body.appendChild(link);
            // link.click();
            // document.body.removeChild(link);

            showSuccessToast('PDF généré avec succès !');
        } else {
            showErrorToast(response.data.message);
        }
    })
    .fail(function() {
        showErrorToast('Erreur lors de la génération du PDF');
    })
    .always(function() {
        hideExportLoader();
    });
}
```

---

## 🧪 Tests d'intégration

### Test unitaire simple (dans console développeur)

```javascript
// Test de l'endpoint generate_preview
function testGeneratePreview() {
    console.log('🧪 Testing pdf_generate_preview endpoint...');

    pdfApiCall('pdf_generate_preview', {
        mode: 'canvas',
        template_data: JSON.stringify({
            customer_name: 'Test User',
            order_total: 100
        }),
        format: 'html'
    })
    .done(function(response) {
        if (response.success) {
            console.log('✅ Preview generated successfully:', response.data);
        } else {
            console.error('❌ Preview generation failed:', response.data);
        }
    })
    .fail(function(error) {
        console.error('❌ Network error:', error);
    });
}

// Test de validation de licence
function testLicenseValidation() {
    console.log('🧪 Testing pdf_validate_license endpoint...');

    pdfApiCall('pdf_validate_license', {
        license_key: 'TEST-1234-ABCD-5678'
    })
    .done(function(response) {
        console.log('✅ License validation result:', response.data);
    })
    .fail(function(error) {
        console.error('❌ License validation error:', error);
    });
}

// Exécuter les tests
$(document).ready(function() {
    if (window.location.hostname === 'localhost') {
        setTimeout(() => {
            testGeneratePreview();
            testLicenseValidation();
        }, 1000);
    }
});
```

### Test de charge basique

```javascript
/**
 * Test de charge pour vérifier le rate limiting
 */
function stressTestEndpoint(endpoint, data = {}, iterations = 15) {
    console.log(`🔥 Stress testing ${endpoint} with ${iterations} requests...`);

    const promises = [];
    for (let i = 0; i < iterations; i++) {
        promises.push(
            pdfApiCall(endpoint, data)
                .done(() => console.log(`✅ Request ${i + 1} successful`))
                .fail((error) => console.log(`❌ Request ${i + 1} failed:`, error.status))
        );
    }

    Promise.allSettled(promises).then(results => {
        const successful = results.filter(r => r.status === 'fulfilled').length;
        const failed = results.filter(r => r.status === 'rejected').length;

        console.log(`📊 Stress test results: ${successful} successful, ${failed} failed`);

        if (failed > 0) {
            console.log('🎯 Rate limiting is working correctly');
        }
    });
}

// Test rate limiting pour generate_preview (limite: 10/min)
$('#stress-test-btn').on('click', function() {
    stressTestEndpoint('pdf_generate_preview', {
        mode: 'canvas',
        template_data: JSON.stringify({test: true}),
        format: 'html'
    });
});
```

---

## 🚨 Gestion des erreurs

### Gestion centralisée des erreurs

```javascript
/**
 * Gestionnaire d'erreurs centralisé pour tous les appels API
 */
function handleApiError(action, error, xhr) {
    let message = 'Une erreur inattendue s\'est produite';

    if (xhr.status === 429) {
        message = 'Trop de requêtes. Veuillez patienter avant de réessayer.';
    } else if (xhr.status === 403) {
        message = 'Permissions insuffisantes pour cette action.';
    } else if (xhr.status === 400) {
        message = 'Données invalides envoyées.';
    } else if (xhr.status >= 500) {
        message = 'Erreur du serveur. Veuillez réessayer plus tard.';
    }

    // Logger l'erreur pour le debugging
    console.error(`PDF API Error [${action}]:`, {
        status: xhr.status,
        statusText: xhr.statusText,
        response: xhr.responseJSON,
        timestamp: new Date().toISOString()
    });

    return message;
}

/**
 * Wrapper AJAX avec gestion d'erreurs améliorée
 */
function pdfApiCallWithErrorHandling(action, data = {}) {
    return pdfApiCall(action, data)
        .fail(function(xhr, status, error) {
            const message = handleApiError(action, error, xhr);
            showErrorToast(message);

            // Loguer les erreurs de sécurité
            if (xhr.status === 429 || xhr.status === 403) {
                console.warn(`Security event [${action}]: ${message}`);
            }
        });
}
```

### Messages d'erreur spécifiques

```javascript
const ERROR_MESSAGES = {
    'pdf_generate_preview': {
        'rate_limit_exceeded': 'Trop d\'aperçus générés. Attendez 1 minute.',
        'invalid_mode': 'Mode d\'aperçu invalide.',
        'invalid_format': 'Format d\'aperçu non supporté.',
        'template_error': 'Erreur dans les données du template.'
    },
    'pdf_validate_license': {
        'invalid_format': 'Format de clé de licence invalide.',
        'license_expired': 'Votre licence a expiré.',
        'license_invalid': 'Clé de licence invalide.'
    },
    'pdf_get_template_variables': {
        'template_not_found': 'Template non trouvé.',
        'variables_unavailable': 'Variables temporairment indisponibles.'
    },
    'pdf_export_canvas': {
        'export_failed': 'Échec de l\'export.',
        'invalid_filename': 'Nom de fichier invalide.',
        'quota_exceeded': 'Quota d\'export dépassé.'
    }
};
```

---

## 📱 Intégration mobile/responsive

### Adaptation pour mobile

```javascript
/**
 * Adapte les appels API selon le contexte mobile
 */
function getMobileOptimizedSettings() {
    const isMobile = window.innerWidth < 768;

    return {
        timeout: isMobile ? 45000 : 30000, // Timeout plus long sur mobile
        format: isMobile ? 'png' : 'html',  // Préférer PNG sur mobile
        quality: isMobile ? 80 : 95        // Qualité réduite sur mobile
    };
}

/**
 * Version mobile de generateCanvasPreview
 */
function generateMobileCanvasPreview() {
    const mobileSettings = getMobileOptimizedSettings();

    pdfApiCall('pdf_generate_preview', {
        mode: 'canvas',
        template_data: JSON.stringify(getCurrentTemplateData()),
        format: mobileSettings.format,
        mobile: true // Flag pour traitement spécial côté serveur
    }, {
        timeout: mobileSettings.timeout
    })
    .done(function(response) {
        if (response.success) {
            // Ouvrir dans une nouvelle fenêtre sur mobile
            window.open(response.data.preview_url, '_blank');
        }
    });
}
```

---

## 🔄 Prochaines étapes

Une fois les exemples validés, la **Phase 2.5** sera complètement terminée :

- ✅ **2.5.1** : Endpoints AJAX définis
- ✅ **2.5.2** : Formats de données spécifiés  
- ✅ **2.5.3** : Méthodes de sécurité documentées
- 🔄 **2.5.4** : Exemples d'utilisation créés

**Phase 3** pourra alors commencer avec l'implémentation de l'infrastructure de base (PreviewRenderer, CanvasMode, MetaboxMode).

---

*Phase 2.5.4 en cours - Exemples d'utilisation des APIs en cours de création* 📚✨