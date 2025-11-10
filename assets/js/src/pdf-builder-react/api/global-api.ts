/**
 * PDF Builder React - API Globale
 * Expose l'interface de communication entre WordPress et le bundle React
 */

import { debugLog, debugError, debugWarn } from '../utils/debug';
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
  debugLog('[Global API] Editor instance registered', { hasInstance: !!instance });
}

/**
 * Charge un template dans l'éditeur
 */
export async function loadTemplate(templateData: TemplateState) {
  debugLog('[Global API] loadTemplate called', { templateId: templateData?.id });

  try {
    currentTemplate = templateData;

    // Dispatcher un événement personnalisé que PDFBuilder écoutera
    const event = new CustomEvent('pdfBuilderLoadTemplate', {
      detail: templateData
    });

    document.dispatchEvent(event);
    debugLog('[Global API] Template load event dispatched', { templateId: templateData?.id });

    return true;
  } catch (error) {
    debugError('[Global API] Error loading template', error);
    return false;
  }
}

/**
 * Récupère l'état actuel de l'éditeur
 */
export function getEditorState() {
  debugLog('[Global API] getEditorState called');
  
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
  debugLog('[Global API] setEditorState called', { hasState: !!state });
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
  debugLog('[Global API] exportTemplate called');
  
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
  debugLog('[Global API] saveTemplate called', { templateId: templateData?.id });
  
  try {
    currentTemplate = templateData;
    
    // Dispatcher un événement personnalisé
    const event = new CustomEvent('pdfBuilderSaveTemplate', {
      detail: templateData
    });
    
    document.dispatchEvent(event);
    debugLog('[Global API] Template save event dispatched');
    
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
  debugLog('[Global API] API reset');
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
