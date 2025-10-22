// Module pour exposer les objets globaux PDF Builder Pro
// Ce module doit être importé pour forcer webpack à l'inclure

import PDFBuilderPro from './index.js';

// Fonction pour initialiser les objets globaux
export function initializeGlobals() {
    if (typeof window !== 'undefined') {
        // Créer l'instance
        const instance = new PDFBuilderPro();

        // Exposer globalement
        window.PDFBuilderPro = PDFBuilderPro;
        window.pdfBuilderPro = instance;
        window.__pdfBuilderGlobal = {
            instance: instance,
            version: '2.0.0',
            timestamp: Date.now()
        };

        console.error('🚀 PDF Builder Pro: Global objects initialized via module');
        console.error('   - window.PDFBuilderPro:', !!window.PDFBuilderPro);
        console.error('   - window.pdfBuilderPro:', !!window.pdfBuilderPro);
        console.error('   - window.__pdfBuilderGlobal:', !!window.__pdfBuilderGlobal);

        return instance;
    }
    return null;
}

// Initialisation automatique
const globalInstance = initializeGlobals();

// Export pour forcer l'inclusion
export { globalInstance };
export default globalInstance;