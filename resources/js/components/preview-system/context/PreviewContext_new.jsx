import React, { createContext, useContext, useReducer, useCallback } from 'react';

/**
 * Nouveau système de contexte pour l'aperçu - Entièrement refait
 * Version 2.0 - Architecture robuste et performante
 */

// Types d'actions disponibles
export const PREVIEW_ACTIONS = {
  // Gestion de l'état de base
  OPEN_PREVIEW: 'OPEN_PREVIEW',
  CLOSE_PREVIEW: 'CLOSE_PREVIEW',
  SET_LOADING: 'SET_LOADING',
  SET_ERROR: 'SET_ERROR',
  
  // Gestion des données
  SET_ELEMENTS: 'SET_ELEMENTS',
  SET_TEMPLATE_DATA: 'SET_TEMPLATE_DATA',
  SET_PREVIEW_DATA: 'SET_PREVIEW_DATA',
  UPDATE_ELEMENT: 'UPDATE_ELEMENT',
  
  // Gestion de l'affichage
  SET_MODE: 'SET_MODE',
  SET_SCALE: 'SET_SCALE',
  SET_VIEWPORT: 'SET_VIEWPORT',
  SET_FULLSCREEN: 'SET_FULLSCREEN',
  
  // Navigation
  SET_PAGE: 'SET_PAGE',
  SET_ZOOM: 'SET_ZOOM',
  
  // Réinitialisation
  RESET: 'RESET'
};

// Modes d'aperçu disponibles
export const PREVIEW_MODES = {
  CANVAS: 'canvas',
  METABOX: 'metabox',
  TABLE: 'table',
  JSON: 'json',
  PRINT: 'print'
};

// État initial du contexte
const initialState = {
  // État de base
  isOpen: false,
  loading: false,
  error: null,
  
  // Configuration
  mode: PREVIEW_MODES.CANVAS,
  scale: 1,
  viewport: {
    width: 800,
    height: 600
  },
  isFullscreen: false,
  
  // Données du template
  templateData: {
    width: 595,    // A4 width en points
    height: 842,   // A4 height en points
    orientation: 'portrait',
    margin: { top: 20, right: 20, bottom: 20, left: 20 }
  },
  
  // Éléments du canvas
  elements: [],
  
  // Données d'aperçu (données dynamiques à injecter)
  previewData: {},
  
  // Navigation
  currentPage: 1,
  totalPages: 1,
  zoom: 1,
  
  // Métadonnées
  lastUpdated: null,
  version: '2.0.0'
};

// Reducer principal - Gestion pure et prévisible de l'état
function previewReducer(state, action) {
  const timestamp = new Date().toISOString();
  
  switch (action.type) {
    case PREVIEW_ACTIONS.OPEN_PREVIEW:
      return {
        ...state,
        isOpen: true,
        mode: action.payload.mode || PREVIEW_MODES.CANVAS,
        elements: action.payload.elements || [],
        templateData: { ...state.templateData, ...action.payload.templateData },
        previewData: action.payload.previewData || {},
        loading: false,
        error: null,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.CLOSE_PREVIEW:
      return {
        ...state,
        isOpen: false,
        loading: false,
        error: null,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_LOADING:
      return {
        ...state,
        loading: action.payload,
        error: action.payload ? null : state.error,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_ERROR:
      return {
        ...state,
        error: action.payload,
        loading: false,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_ELEMENTS:
      return {
        ...state,
        elements: action.payload,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_TEMPLATE_DATA:
      return {
        ...state,
        templateData: { ...state.templateData, ...action.payload },
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_PREVIEW_DATA:
      return {
        ...state,
        previewData: { ...state.previewData, ...action.payload },
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.UPDATE_ELEMENT:
      return {
        ...state,
        elements: state.elements.map(el => 
          el.id === action.payload.id 
            ? { ...el, ...action.payload.updates }
            : el
        ),
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_MODE:
      return {
        ...state,
        mode: action.payload,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_SCALE:
      return {
        ...state,
        scale: Math.max(0.1, Math.min(3, action.payload)), // Limite entre 10% et 300%
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_VIEWPORT:
      return {
        ...state,
        viewport: { ...state.viewport, ...action.payload },
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_FULLSCREEN:
      return {
        ...state,
        isFullscreen: action.payload,
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_PAGE:
      return {
        ...state,
        currentPage: Math.max(1, Math.min(state.totalPages, action.payload)),
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.SET_ZOOM:
      return {
        ...state,
        zoom: Math.max(0.25, Math.min(4, action.payload)), // Entre 25% et 400%
        lastUpdated: timestamp
      };
      
    case PREVIEW_ACTIONS.RESET:
      return {
        ...initialState,
        lastUpdated: timestamp
      };
      
    default:
      console.warn(`Action non reconnue dans previewReducer: ${action.type}`);
      return state;
  }
}

// Création du contexte
const PreviewContext = createContext(null);

/**
 * Hook pour utiliser le contexte d'aperçu
 * Fournit l'état et les actions de manière typée
 */
export function usePreviewContext() {
  const context = useContext(PreviewContext);
  
  if (!context) {
    throw new Error('usePreviewContext doit être utilisé dans un PreviewProvider');
  }
  
  return context;
}

/**
 * Provider du contexte d'aperçu
 * Enveloppe l'application et fournit l'état global
 */
export function PreviewProvider({ children }) {
  const [state, dispatch] = useReducer(previewReducer, initialState);
  
  // Actions optimisées avec useCallback pour éviter les re-renders
  const actions = {
    // Ouvrir l'aperçu avec des données
    openPreview: useCallback((config) => {
      dispatch({
        type: PREVIEW_ACTIONS.OPEN_PREVIEW,
        payload: config
      });
    }, []),
    
    // Fermer l'aperçu
    closePreview: useCallback(() => {
      dispatch({ type: PREVIEW_ACTIONS.CLOSE_PREVIEW });
    }, []),
    
    // Définir l'état de chargement
    setLoading: useCallback((loading) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_LOADING,
        payload: loading
      });
    }, []),
    
    // Définir une erreur
    setError: useCallback((error) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_ERROR,
        payload: error
      });
    }, []),
    
    // Mettre à jour les éléments
    setElements: useCallback((elements) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_ELEMENTS,
        payload: elements
      });
    }, []),
    
    // Mettre à jour les données du template
    setTemplateData: useCallback((templateData) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_TEMPLATE_DATA,
        payload: templateData
      });
    }, []),
    
    // Mettre à jour les données d'aperçu
    setPreviewData: useCallback((previewData) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_PREVIEW_DATA,
        payload: previewData
      });
    }, []),
    
    // Mettre à jour un élément spécifique
    updateElement: useCallback((id, updates) => {
      dispatch({
        type: PREVIEW_ACTIONS.UPDATE_ELEMENT,
        payload: { id, updates }
      });
    }, []),
    
    // Changer de mode d'aperçu
    setMode: useCallback((mode) => {
      if (Object.values(PREVIEW_MODES).includes(mode)) {
        dispatch({
          type: PREVIEW_ACTIONS.SET_MODE,
          payload: mode
        });
      }
    }, []),
    
    // Définir l'échelle
    setScale: useCallback((scale) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_SCALE,
        payload: scale
      });
    }, []),
    
    // Définir le viewport
    setViewport: useCallback((viewport) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_VIEWPORT,
        payload: viewport
      });
    }, []),
    
    // Basculer le mode plein écran
    toggleFullscreen: useCallback(() => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_FULLSCREEN,
        payload: !state.isFullscreen
      });
    }, [state.isFullscreen]),
    
    // Navigation des pages
    setPage: useCallback((page) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_PAGE,
        payload: page
      });
    }, []),
    
    // Contrôle du zoom
    setZoom: useCallback((zoom) => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_ZOOM,
        payload: zoom
      });
    }, []),
    
    // Zoom prédéfinis
    zoomIn: useCallback(() => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_ZOOM,
        payload: state.zoom * 1.25
      });
    }, [state.zoom]),
    
    zoomOut: useCallback(() => {
      dispatch({
        type: PREVIEW_ACTIONS.SET_ZOOM,
        payload: state.zoom / 1.25
      });
    }, [state.zoom]),
    
    zoomToFit: useCallback(() => {
      // Calculer le zoom pour que le canvas tienne dans le viewport
      const scaleX = state.viewport.width / state.templateData.width;
      const scaleY = state.viewport.height / state.templateData.height;
      const optimalZoom = Math.min(scaleX, scaleY, 1);
      
      dispatch({
        type: PREVIEW_ACTIONS.SET_ZOOM,
        payload: optimalZoom
      });
    }, [state.viewport, state.templateData]),
    
    zoomToWidth: useCallback(() => {
      const optimalZoom = state.viewport.width / state.templateData.width;
      dispatch({
        type: PREVIEW_ACTIONS.SET_ZOOM,
        payload: optimalZoom
      });
    }, [state.viewport.width, state.templateData.width]),
    
    // Réinitialiser l'état
    reset: useCallback(() => {
      dispatch({ type: PREVIEW_ACTIONS.RESET });
    }, [])
  };
  
  // Valeur du contexte avec état et actions
  const contextValue = {
    // État actuel
    state,
    
    // Actions disponibles
    ...actions,
    
    // Helpers calculés
    computed: {
      // Échelle réelle appliquée au canvas
      actualScale: state.scale * state.zoom,
      
      // Dimensions calculées du canvas
      canvasDisplayWidth: state.templateData.width * state.scale * state.zoom,
      canvasDisplayHeight: state.templateData.height * state.scale * state.zoom,
      
      // État de l'interface
      isLoading: state.loading,
      hasError: !!state.error,
      isEmpty: state.elements.length === 0,
      
      // Mode actuel
      isCanvasMode: state.mode === PREVIEW_MODES.CANVAS,
      isMetaboxMode: state.mode === PREVIEW_MODES.METABOX,
      isTableMode: state.mode === PREVIEW_MODES.TABLE,
      isJsonMode: state.mode === PREVIEW_MODES.JSON,
      isPrintMode: state.mode === PREVIEW_MODES.PRINT
    }
  };
  
  return (
    <PreviewContext.Provider value={contextValue}>
      {children}
    </PreviewContext.Provider>
  );
}

// Export du contexte pour usage direct si nécessaire
export { PreviewContext };

// Export des constantes utiles
export { PREVIEW_MODES, PREVIEW_ACTIONS };