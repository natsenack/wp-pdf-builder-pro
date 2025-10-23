/**
 * Index principal du système d'aperçu unifié PDF Builder Pro
 * Point d'entrée pour tous les composants, hooks et utilitaires
 */

// Context & Provider
export { PreviewProvider, usePreviewContext as usePreview } from './context/PreviewProvider';
export { PreviewContext, previewReducer, initialState, PREVIEW_MODES, PREVIEW_ACTIONS } from './context/PreviewContext';

// Composants principaux
export { default as PreviewModal } from './components/PreviewModal';
export { default as ModalSkeleton } from './components/ModalSkeleton';
export { default as NavigationControls } from './NavigationControls';

// Modes
export { default as CanvasMode } from './modes/CanvasMode';
export { default as MetaboxMode } from './modes/MetaboxMode';

// Renderers (à implémenter)
export { default as PDFRenderer } from './renderers/PDFRenderer';
export { default as CanvasRenderer } from './renderers/CanvasRenderer';
export { default as ImageRenderer } from './renderers/ImageRenderer';

// Hooks
export { default as usePerformanceMonitor } from './hooks/usePerformanceMonitor';
export { default as useLazyLoad, useLazyPageLoad } from './hooks/useLazyLoad';

// Utilitaires
export * from './utils/previewUtils';
export * from './utils/securityUtils';

// Types et constantes
export const SYSTEM_VERSION = '8.1.0';
export const SYSTEM_NAME = 'PDF Builder Pro Preview System';

// Fonction d'initialisation du système
export function initializePreviewSystem(config = {}) {
  console.log(`[PreviewSystem] Initializing ${SYSTEM_NAME} v${SYSTEM_VERSION}`);

  // Validation de la configuration
  const validatedConfig = {
    enablePerformanceMonitoring: config.enablePerformanceMonitoring ?? true,
    enableLazyLoading: config.enableLazyLoading ?? true,
    enableSecurityValidation: config.enableSecurityValidation ?? true,
    maxConcurrentRenders: config.maxConcurrentRenders ?? 3,
    defaultMode: config.defaultMode ?? 'canvas',
    ...config
  };

  // Vérification du support navigateur
  const browserSupport = checkBrowserSupport();
  if (!browserSupport.promises || !browserSupport.fetch) {
    console.warn('[PreviewSystem] Browser may not support all features. Consider upgrading.');
  }

  console.log('[PreviewSystem] Configuration:', validatedConfig);
  console.log('[PreviewSystem] Browser support:', browserSupport);

  return {
    version: SYSTEM_VERSION,
    config: validatedConfig,
    browserSupport,
    initialized: true
  };
}

// Import des utilitaires pour les fonctions helper
import { checkBrowserSupport } from './utils/previewUtils';