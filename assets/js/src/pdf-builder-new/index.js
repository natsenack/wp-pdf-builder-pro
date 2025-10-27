/**
 * PDF Builder New - Point d'entrée principal
 * Export de tous les modules du nouveau builder PDF
 */

// Core modules
export { PDFBuilder } from './core/PDFBuilder.js';
export { CanvasEngine } from './core/CanvasEngine.js';
export { ElementManager } from './core/ElementManager.js';
export { TemplateManager } from './core/TemplateManager.js';

// UI modules (à implémenter)
export { UIManager } from './ui/UIManager.js';
export { Toolbar } from './ui/Toolbar.js';
export { PropertyPanel } from './ui/PropertyPanel.js';
export { CanvasContainer } from './ui/CanvasContainer.js';

// Utilities
export { UnitConverter, unitConverter } from './utils/UnitConverter.js';
export { EventEmitter, eventEmitter } from './utils/EventEmitter.js';
export { Validation, validation } from './utils/Validation.js';

// Plugins (à implémenter)
export { WooCommercePlugin } from './plugins/WooCommerce.js';
export { ExportPlugin } from './plugins/ExportPDF.js';

// Fonction d'initialisation rapide
export function createPDFBuilder(containerId, options = {}) {
    const builder = new PDFBuilder(containerId, options);
    return builder.init().then(() => builder);
}

// Version
export const VERSION = '1.0.0-alpha';