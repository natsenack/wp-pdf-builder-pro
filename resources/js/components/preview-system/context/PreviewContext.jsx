import React, { createContext, useContext } from 'react';

/**
 * Preview Context - État global optimisé pour le système d'aperçu
 * Utilise React Context pour partager l'état entre tous les composants
 */

// Types pour TypeScript (si utilisé)
export const PREVIEW_MODES = {
  CANVAS: 'canvas',
  METABOX: 'metabox'
};

export const PREVIEW_ACTIONS = {
  OPEN: 'OPEN',
  CLOSE: 'CLOSE',
  SET_PAGE: 'SET_PAGE',
  SET_ZOOM: 'SET_ZOOM',
  SET_LOADING: 'SET_LOADING',
  SET_ERROR: 'SET_ERROR',
  SET_DATA: 'SET_DATA'
};

// État initial
const initialState = {
  isOpen: false,
  mode: PREVIEW_MODES.CANVAS,
  currentPage: 1,
  totalPages: 1,
  zoom: 1,
  loading: false,
  error: null,
  data: null,
  config: {}
};

// Reducer pour gérer l'état
function previewReducer(state, action) {
  switch (action.type) {
    case PREVIEW_ACTIONS.OPEN:
      return {
        ...state,
        isOpen: true,
        mode: action.payload.mode || PREVIEW_MODES.CANVAS,
        data: action.payload.data || null,
        config: action.payload.config || {},
        loading: false,
        error: null,
        currentPage: 1,
        zoom: 1
      };

    case PREVIEW_ACTIONS.CLOSE:
      return {
        ...initialState
      };

    case PREVIEW_ACTIONS.SET_PAGE:
      return {
        ...state,
        currentPage: Math.max(1, Math.min(action.payload, state.totalPages))
      };

    case PREVIEW_ACTIONS.SET_ZOOM:
      return {
        ...state,
        zoom: Math.max(0.5, Math.min(2, action.payload))
      };

    case PREVIEW_ACTIONS.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };

    case PREVIEW_ACTIONS.SET_ERROR:
      return {
        ...state,
        error: action.payload,
        loading: false
      };

    case PREVIEW_ACTIONS.SET_DATA:
      return {
        ...state,
        data: action.payload,
        totalPages: action.payload?.totalPages || 1
      };

    default:
      return state;
  }
}

// Création du contexte
const PreviewContext = createContext();

// Hook personnalisé pour utiliser le contexte
export function usePreview() {
  const context = useContext(PreviewContext);
  if (!context) {
    throw new Error('usePreview must be used within a PreviewProvider');
  }
  return context;
}

// Export du contexte pour les tests ou usage avancé
export { PreviewContext, previewReducer, initialState };