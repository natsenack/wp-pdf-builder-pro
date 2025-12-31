import { useState, useCallback } from 'react';
import { Element, CanvasState, BuilderMode } from '../types/elements';

// État initial simple
const initialCanvasState: CanvasState = {
  zoom: 1,
  pan: { x: 0, y: 0 },
  showGrid: true,
  gridSize: 20,
  snapToGrid: true,
  backgroundColor: '#ffffff'
};

export interface BuilderState {
  elements: Element[];
  canvas: CanvasState;
  selectedElements: string[];
  mode: BuilderMode;
  isDragging: boolean;
}

const initialState: BuilderState = {
  elements: [],
  canvas: initialCanvasState,
  selectedElements: [],
  mode: 'select',
  isDragging: false
};

export function usePDFBuilder() {
  const [state, setState] = useState<BuilderState>(initialState);

  // Actions pour les éléments
  const addElement = useCallback((element: Element) => {
    setState(prev => ({
      ...prev,
      elements: [...prev.elements, element]
    }));
  }, []);

  const updateElement = useCallback((id: string, updates: Partial<Element>) => {
    setState(prev => ({
      ...prev,
      elements: prev.elements.map(el =>
        el.id === id ? { ...el, ...updates, updatedAt: new Date() } : el
      )
    }));
  }, []);

  const removeElement = useCallback((id: string) => {
    setState(prev => ({
      ...prev,
      elements: prev.elements.filter(el => el.id !== id),
      selectedElements: prev.selectedElements.filter(selectedId => selectedId !== id)
    }));
  }, []);

  // Actions pour la sélection
  const setSelection = useCallback((ids: string[]) => {
    setState(prev => ({
      ...prev,
      selectedElements: ids
    }));
  }, []);

  const clearSelection = useCallback(() => {
    setState(prev => ({
      ...prev,
      selectedElements: []
    }));
  }, []);

  // Actions pour le canvas
  const setCanvas = useCallback((updates: Partial<CanvasState>) => {
    setState(prev => ({
      ...prev,
      canvas: { ...prev.canvas, ...updates }
    }));
  }, []);

  const setMode = useCallback((mode: BuilderMode) => {
    setState(prev => ({
      ...prev,
      mode
    }));
  }, []);

  // Actions pour le drag
  const setDragging = useCallback((isDragging: boolean) => {
    setState(prev => ({
      ...prev,
      isDragging
    }));
  }, []);

  // Utilitaires
  const getElement = useCallback((id: string) => {
    return state.elements.find(el => el.id === id);
  }, [state.elements]);

  const getSelectedElements = useCallback(() => {
    return state.elements.filter(el => state.selectedElements.includes(el.id));
  }, [state.elements, state.selectedElements]);

  const zoomIn = useCallback(() => {
    setCanvas({ zoom: Math.min(state.canvas.zoom * 1.2, 5) });
  }, [state.canvas.zoom, setCanvas]);

  const zoomOut = useCallback(() => {
    setCanvas({ zoom: Math.max(state.canvas.zoom / 1.2, 0.1) });
  }, [state.canvas.zoom, setCanvas]);

  const resetZoom = useCallback(() => {
    setCanvas({ zoom: 1 });
  }, [setCanvas]);

  return {
    // État
    state,

    // Actions éléments
    addElement,
    updateElement,
    removeElement,

    // Actions sélection
    setSelection,
    clearSelection,

    // Actions canvas
    setCanvas,
    zoomIn,
    zoomOut,
    resetZoom,

    // Actions mode
    setMode,

    // Actions drag
    setDragging,

    // Utilitaires
    getElement,
    getSelectedElements
  };
}
