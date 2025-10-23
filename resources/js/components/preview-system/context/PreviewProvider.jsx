import React, { useReducer, useCallback, useMemo } from 'react';
import { PreviewContext, previewReducer, initialState, PREVIEW_ACTIONS } from './PreviewContext';

/**
 * Preview Provider - Fournisseur d'état global pour le système d'aperçu
 * Optimisé avec useCallback et useMemo pour éviter les re-renders inutiles
 */

export function PreviewProvider({ children }) {
  const [state, dispatch] = useReducer(previewReducer, initialState);

  // Actions optimisées avec useCallback
  const openPreview = useCallback((mode, data, config = {}) => {
    dispatch({
      type: PREVIEW_ACTIONS.OPEN,
      payload: { mode, data, config }
    });
  }, []);

  const closePreview = useCallback(() => {
    dispatch({ type: PREVIEW_ACTIONS.CLOSE });
  }, []);

  const setPage = useCallback((page) => {
    dispatch({
      type: PREVIEW_ACTIONS.SET_PAGE,
      payload: page
    });
  }, []);

  const setZoom = useCallback((zoom) => {
    dispatch({
      type: PREVIEW_ACTIONS.SET_ZOOM,
      payload: zoom
    });
  }, []);

  const setLoading = useCallback((loading) => {
    dispatch({
      type: PREVIEW_ACTIONS.SET_LOADING,
      payload: loading
    });
  }, []);

  const setError = useCallback((error) => {
    dispatch({
      type: PREVIEW_ACTIONS.SET_ERROR,
      payload: error
    });
  }, []);

  const setData = useCallback((data) => {
    dispatch({
      type: PREVIEW_ACTIONS.SET_DATA,
      payload: data
    });
  }, []);

  const setRotation = useCallback((rotation) => {
    dispatch({
      type: 'SET_ROTATION',
      payload: rotation
    });
  }, []);

  const toggleFullscreen = useCallback(() => {
    dispatch({
      type: 'TOGGLE_FULLSCREEN'
    });
  }, []);

  const loadPreview = useCallback((config) => {
    setLoading(true);
    // Additional logic can be added here if needed
  }, [setLoading]);

  const clearPreview = useCallback(() => {
    setLoading(false);
    setData(null);
    setError(null);
  }, [setLoading, setData, setError]);

  // Valeur du contexte optimisée avec useMemo
  const contextValue = useMemo(() => ({
    state,

    actions: {
      openPreview,
      closePreview,
      setPage,
      setZoom,
      setLoading,
      setError,
      setData,
      setRotation,
      toggleFullscreen,
      loadPreview,
      clearPreview
    },

    // Getters utilitaires
    isFirstPage: state.currentPage === 1,
    isLastPage: state.currentPage === state.totalPages,
    canNavigatePrev: state.currentPage > 1,
    canNavigateNext: state.currentPage < state.totalPages,
    zoomPercentage: Math.round(state.zoom * 100)
  }), [
    state,
    openPreview,
    closePreview,
    setPage,
    setZoom,
    setLoading,
    setError,
    setData,
    setRotation,
    toggleFullscreen,
    loadPreview,
    clearPreview
  ]);

  return (
    <PreviewContext.Provider value={contextValue}>
      {children}
    </PreviewContext.Provider>
  );
}

// Export par défaut
export default PreviewProvider;