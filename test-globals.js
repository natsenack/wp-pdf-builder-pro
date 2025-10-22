// Script de test pour vérifier les objets globaux PDF Builder Pro
const fs = require('fs');
const path = require('path');

// Simuler un environnement global
global.window = {};
global.self = global.window; // self fait référence à window
global.document = {
    getElementById: () => null,
    addEventListener: () => {},
    createElement: () => ({}),
    readyState: 'complete'
};
global.console = console;
global.React = {};
global.ReactDOM = {};
global.navigator = { userAgent: 'Node.js Test' };
global.setTimeout = setTimeout;
global.clearTimeout = clearTimeout;

// Charger le bundle
const bundlePath = path.join(__dirname, 'assets', 'js', 'dist', 'pdf-builder-admin.js');
const bundle = fs.readFileSync(bundlePath, 'utf8');

console.log('=== TEST DES OBJETS GLOBAUX PDF BUILDER PRO ===');

// Exécuter le bundle dans le contexte global simulé
try {
    eval(bundle);

    console.log('✅ Bundle exécuté avec succès');
    console.log('window.PDFBuilderPro:', typeof window.PDFBuilderPro);
    console.log('window.pdfBuilderPro:', typeof window.pdfBuilderPro);
    console.log('window.__pdfBuilderGlobal:', typeof window.__pdfBuilderGlobal);
    console.log('window.initializePDFBuilderPro:', typeof window.initializePDFBuilderPro);

    // Tester la fonction initializePDFBuilderPro
    if (window.initializePDFBuilderPro) {
        console.log('🔧 Test de la fonction initializePDFBuilderPro...');
        try {
            const result = window.initializePDFBuilderPro();
            console.log('✅ initializePDFBuilderPro appelée avec succès');
            console.log('Résultat:', result);

            // Vérifier si les objets globaux ont été créés après l'appel
            console.log('Après initializePDFBuilderPro:');
            console.log('window.PDFBuilderPro:', typeof window.PDFBuilderPro);
            console.log('window.pdfBuilderPro:', typeof window.pdfBuilderPro);
            console.log('window.__pdfBuilderGlobal:', typeof window.__pdfBuilderGlobal);

        } catch (error) {
            console.error('❌ Erreur lors de l\'appel de initializePDFBuilderPro:', error.message);
        }
    } else {
        console.log('❌ window.initializePDFBuilderPro n\'est pas définie');
    }

    if (window.pdfBuilderPro) {
        console.log('window.pdfBuilderPro.version:', window.pdfBuilderPro.version);
        console.log('window.pdfBuilderPro.init:', typeof window.pdfBuilderPro.init);
        console.log('window.pdfBuilderPro.editors:', typeof window.pdfBuilderPro.editors);
    }

    if (window.__pdfBuilderGlobal) {
        console.log('window.__pdfBuilderGlobal.version:', window.__pdfBuilderGlobal.version);
        console.log('window.__pdfBuilderGlobal.timestamp:', window.__pdfBuilderGlobal.timestamp);
    }

} catch (error) {
    console.error('❌ Erreur lors de l\'exécution du bundle:', error.message);
}

console.log('=== FIN DU TEST ===');