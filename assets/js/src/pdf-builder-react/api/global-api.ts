/**
 * PDF Builder React - API Globale
 * Expose l'interface de communication entre WordPress et le bundle React
 */

import { debugError, debugWarn } from '../utils/debug';
import { TemplateState, BuilderState } from '../types/elements';

// Stocker les références globales
let editorInstance: unknown = null;
let currentTemplate: TemplateState | null = null;
let editorState: BuilderState | null = null;

/**
 * Enregistre l'instance de l'éditeur
 */
export function registerEditorInstance(instance: unknown) {
  editorInstance = instance;

}

/**
 * Charge un template dans l'éditeur
 */
// @ts-expect-error - Debug logs, types not important for debugging
export async function loadTemplate(templateData: any) {
  console.log('[Global API] loadTemplate called with data:', templateData);
  console.log('[Global API] Template data type:', typeof templateData);
  console.log('[Global API] Template has elements:', templateData.elements ? 'YES' : 'NO');

  if (templateData.elements) {
    console.log('[Global API] Elements count:', templateData.elements.length);
    // @ts-expect-error - Debug logs
    templateData.elements.forEach((element, index) => {
      console.log(`[Global API] Element ${index}:`, {
        type: element.type,
        contentAlign: element.contentAlign,
        labelPosition: element.labelPosition,
        id: element.id
      });
    });
  }

  try {
    currentTemplate = templateData;

    // Dispatcher un événement personnalisé que PDFBuilder écoutera
    const event = new CustomEvent('pdfBuilderLoadTemplate', {
      detail: templateData
    });

    console.log('[Global API] Dispatching pdfBuilderLoadTemplate event');
    document.dispatchEvent(event);

    console.log('[Global API] Template loaded successfully');
    return true;
  } catch (error) {
    console.error('[Global API] Error loading template', error);
    return false;
  }
}

/**
 * Récupère l'état actuel de l'éditeur
 */
export function getEditorState() {

  
  if (!editorInstance) {
    debugWarn('[Global API] Editor instance not available for state');
    return null;
  }
  
  return editorState;
}

/**
 * Met à jour l'état de l'éditeur
 */
export function setEditorState(state: BuilderState) {

  editorState = state;
}

/**
 * Récupère le template actuel
 */
export function getCurrentTemplate() {
  return currentTemplate;
}

/**
 * Exporte les données du template
 */
export function exportTemplate() {

  
  if (!editorInstance) {
    debugError('[Global API] Editor instance not available');
    return null;
  }

  return {
    template: currentTemplate,
    state: editorState,
    timestamp: new Date().toISOString()
  };
}

/**
 * Sauvegarde un template
 */
export async function saveTemplate(templateData: TemplateState) {

  
  try {
    currentTemplate = templateData;
    
    // Dispatcher un événement personnalisé
    const event = new CustomEvent('pdfBuilderSaveTemplate', {
      detail: templateData
    });
    
    document.dispatchEvent(event);

    
    return true;
  } catch (error) {
    debugError('[Global API] Error saving template', error);
    return false;
  }
}

/**
 * Réinitialise l'API
 */
export function resetAPI() {

  editorInstance = null;
  currentTemplate = null;
  editorState = null;
}

/**
 * Interface de l'API globale
 */
export interface GlobalAPI {
  registerEditorInstance: typeof registerEditorInstance;
  loadTemplate: typeof loadTemplate;
  getEditorState: typeof getEditorState;
  setEditorState: typeof setEditorState;
  getCurrentTemplate: typeof getCurrentTemplate;
  exportTemplate: typeof exportTemplate;
  saveTemplate: typeof saveTemplate;
  resetAPI: typeof resetAPI;
}
