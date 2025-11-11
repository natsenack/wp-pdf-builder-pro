import React, { createContext, useContext, useReducer, ReactNode, useEffect } from 'react';
import { useCanvasSettings } from '../CanvasSettingsContext.tsx';
import {
  BuilderState,
  BuilderAction,
  CanvasState,
  SelectionState,
  DragState,
  Element,
  BuilderMode,
  HistoryState
} from '../../types/elements';
import { debugError } from '../../utils/debug';

// Type pour les propriétés des éléments product_table
interface ProductTableProperties {
  showHeaders: boolean;
  showBorders: boolean;
  showAlternatingRows: boolean;
  showSku: boolean;
  showDescription: boolean;
  showQuantity: boolean;
  fontSize: number;
  currency: string;
  tableStyle: string;
  textAlign: string;
  verticalAlign: string;
  backgroundColor: string;
  headerBackgroundColor: string;
  headerTextColor: string;
  alternateRowColor: string;
  borderColor: string;
  textColor: string;
}

// Fonction helper pour corriger les positions des éléments hors limites
// ✅ BUGFIX-014: Accept canvas dimensions as parameters for dynamic sizing
const clampElementPositions = (elements: Element[], canvasWidth: number = 794, canvasHeight: number = 1123): Element[] => {
  return elements.map(element => {
    let newX = element.x;
    let newY = element.y;

    // Clamp X position (laisser au moins 5px visible)
    const minVisibleWidth = Math.min(15, element.width * 0.3);
    if (newX < 0) newX = 0;
    if (newX + minVisibleWidth > canvasWidth) newX = Math.max(0, canvasWidth - minVisibleWidth);

    // Clamp Y position (laisser au moins 5px visible)
    const minVisibleHeight = Math.min(10, element.height * 0.3);
    if (newY < 0) newY = 0;
    if (newY + minVisibleHeight > canvasHeight) newY = Math.max(0, canvasHeight - minVisibleHeight);

    if (newX !== element.x || newY !== element.y) {
      return { ...element, x: newX, y: newY };
    }

    return element;
  });
};

// Fonction helper pour réparer les propriétés des éléments product_table
const repairProductTableProperties = (elements: Element[]): Element[] => {
  const defaultProperties: ProductTableProperties = {
    // Fonctionnalités de base
    showHeaders: true,
    showBorders: true,
    showAlternatingRows: true,
    showSku: true,
    showDescription: true,
    showQuantity: true,
    
    // Style et apparence
    fontSize: 11,
    currency: '€',
    tableStyle: 'default',
    
    // Alignements
    textAlign: 'left',
    verticalAlign: 'top',
    
    // Couleurs
    backgroundColor: '#ffffff',
    headerBackgroundColor: '#f9fafb',
    headerTextColor: '#111827',
    alternateRowColor: '#f9fafb',
    borderColor: '#e5e7eb',
    textColor: '#374151'
  };

  return elements.map(element => {
    if (element.type !== 'product_table') return element;

    const repairedElement = { ...element } as Element & ProductTableProperties;

    // Ajouter les propriétés manquantes
    Object.keys(defaultProperties).forEach(prop => {
      if (!(prop in repairedElement)) {
        (repairedElement as Record<string, unknown>)[prop] = defaultProperties[prop as keyof ProductTableProperties];
      }
    });

    // Validation des booléens
    const booleanProps: (keyof ProductTableProperties)[] = ['showHeaders', 'showBorders', 'showAlternatingRows', 'showSku', 'showDescription', 'showQuantity'];
    booleanProps.forEach(prop => {
      if (typeof repairedElement[prop] !== 'boolean') {
        (repairedElement as Record<string, unknown>)[prop] = defaultProperties[prop];
      }
    });

    // Validation des nombres
    const numberProps: (keyof ProductTableProperties)[] = ['fontSize'];
    numberProps.forEach(prop => {
      if (typeof repairedElement[prop] !== 'number') {
        (repairedElement as Record<string, unknown>)[prop] = defaultProperties[prop];
      }
    });

    // Validation des alignements
    const validHorizontalAligns = ['left', 'center', 'right'];
    if (!validHorizontalAligns.includes(repairedElement.textAlign as string)) {
      repairedElement.textAlign = defaultProperties.textAlign;
    }

    const validVerticalAligns = ['top', 'middle', 'bottom'];
    if (!validVerticalAligns.includes(repairedElement.verticalAlign as string)) {
      repairedElement.verticalAlign = defaultProperties.verticalAlign;
    }

    // Validation des couleurs (format hexadécimal)
    const colorProperties: (keyof ProductTableProperties)[] = ['backgroundColor', 'headerBackgroundColor', 'alternateRowColor', 'borderColor', 'headerTextColor', 'textColor'];
    colorProperties.forEach(prop => {
      if (repairedElement[prop] && !/^#[0-9A-Fa-f]{6}$/.test(repairedElement[prop] as string)) {
        (repairedElement as Record<string, unknown>)[prop] = defaultProperties[prop];
      }
    });

    // Validation de la devise
    if (!repairedElement.currency || typeof repairedElement.currency !== 'string') {
      repairedElement.currency = defaultProperties.currency;
    }

    return repairedElement;
  });
};

// État initial
const initialCanvasState: CanvasState = {
  zoom: 1,
  pan: { x: 0, y: 0 },
  showGrid: false,
  gridSize: 20,
  snapToGrid: true,
  backgroundColor: '#ffffff'
};

const initialSelectionState: SelectionState = {
  selectedElements: [],
  isSelecting: false
};

const initialDragState: DragState = {
  isDragging: false,
  draggedElements: []
};

const initialHistoryState: HistoryState = {
  past: [],
  present: {
    elements: [],
    canvas: initialCanvasState,
    selection: initialSelectionState,
    drag: initialDragState,
    mode: 'select',
    template: {
      isNew: true,
      isModified: false,
      isSaving: false,
      isLoading: true // ✅ Start as loading
    },
    previewMode: 'editor',
    history: {} as HistoryState // Sera défini récursivement
  } as BuilderState,
  future: [],
  canUndo: false,
  canRedo: false
};

// Correction de la récursion
initialHistoryState.present.history = initialHistoryState;

const initialState: BuilderState = {
  elements: [],
  canvas: initialCanvasState,
  selection: initialSelectionState,
  drag: initialDragState,
  mode: 'select',
  template: {
    isNew: true,
    isModified: false,
    isSaving: false,
    isLoading: true, // ✅ Start as loading until template is fetched
    description: '',
    tags: [],
    canvasWidth: 794,  // A4 width in PX (plus large)
    canvasHeight: 1123, // A4 height in PX
    marginTop: 28,     // ~10mm in PX
    marginBottom: 28,  // ~10mm in PX
    showGuides: true,
    snapToGrid: false
  },
  previewMode: 'editor',
  history: initialHistoryState
};

// Reducer
function builderReducer(state: BuilderState, action: BuilderAction): BuilderState {
  switch (action.type) {
    case 'ADD_ELEMENT': {
      return {
        ...state,
        elements: [...state.elements, action.payload],
        template: {
          ...state.template,
          isModified: true
        },
        history: updateHistory(state, {
          ...state,
          elements: [...state.elements, action.payload]
        })
      };
    }

    case 'UPDATE_ELEMENT': {
      // ✅ BUGFIX-003: Comprehensive property preservation
      // Ensure ALL existing properties are retained when updating
      const updateElement = (element: Element): Element => {
        if (element.id !== action.payload.id) return element;
        
        // Merge updates while preserving all existing properties
        const updated: Element = {
          ...element,  // First spread all existing properties (including dynamic ones)
          ...action.payload.updates,  // Then apply updates (only specified properties)
          updatedAt: new Date()  // Always update timestamp
        };
        
        return updated;
      };

      const updatedElements = state.elements.map(updateElement);
      
      return {
        ...state,
        elements: updatedElements,
        template: {
          ...state.template,
          isModified: true
        },
        history: updateHistory(state, {
          ...state,
          elements: updatedElements
        })
      };
    }

    case 'REMOVE_ELEMENT': {
      return {
        ...state,
        elements: state.elements.filter(el => el.id !== action.payload),
        selection: {
          ...state.selection,
          selectedElements: state.selection.selectedElements.filter(id => id !== action.payload)
        },
        template: {
          ...state.template,
          isModified: true
        },
        history: updateHistory(state, {
          ...state,
          elements: state.elements.filter(el => el.id !== action.payload),
          selection: {
            ...state.selection,
            selectedElements: state.selection.selectedElements.filter(id => id !== action.payload)
          }
        })
      };
    }

    case 'SET_ELEMENTS': {
      return {
        ...state,
        elements: action.payload,
        history: updateHistory(state, { ...state, elements: action.payload })
      };
    }

    case 'SET_SELECTION': {
      return {
        ...state,
        selection: {
          ...state.selection,
          selectedElements: action.payload,
          selectionBounds: calculateSelectionBounds(action.payload, state.elements)
        }
      };
    }

    case 'CLEAR_SELECTION': {
      return {
        ...state,
        selection: {
          ...state.selection,
          selectedElements: [],
          selectionBounds: undefined
        }
      };
    }

    case 'SET_CANVAS': {
      return {
        ...state,
        canvas: { ...state.canvas, ...action.payload }
      };
    }

    case 'SET_MODE': {
      return {
        ...state,
        mode: action.payload
      };
    }

    case 'SET_DRAG_STATE': {
      return {
        ...state,
        drag: { ...state.drag, ...action.payload }
      };
    }

    case 'SET_PREVIEW_MODE': {
      return {
        ...state,
        previewMode: action.payload
      };
    }

    case 'SET_ORDER_ID': {
      return {
        ...state,
        orderId: action.payload
      };
    }

    case 'UNDO': {
      if (!state.history.canUndo) return state;
      const previousState = state.history.past[state.history.past.length - 1];
      return {
        ...previousState,
        history: {
          past: state.history.past.slice(0, -1),
          present: previousState,
          future: [state, ...state.history.future],
          canUndo: state.history.past.length > 1,
          canRedo: true
        }
      };
    }

    case 'REDO': {
      if (!state.history.canRedo) return state;
      const nextState = state.history.future[0];
      return {
        ...nextState,
        history: {
          past: [...state.history.past, state],
          present: nextState,
          future: state.history.future.slice(1),
          canUndo: true,
          canRedo: state.history.future.length > 1
        }
      };
    }

    case 'RESET': {
      return initialState;
    }

    case 'SAVE_TEMPLATE': {
      return {
        ...state,
        template: {
          ...state.template,
          isNew: false,
          isModified: false,
          isSaving: false,
          lastSaved: new Date(),
          id: action.payload?.id || state.template.id,
          name: action.payload?.name || state.template.name
        }
      };
    }

    case 'SET_TEMPLATE_MODIFIED': {
      return {
        ...state,
        template: {
          ...state.template,
          isModified: action.payload
        }
      };
    }

    case 'SET_TEMPLATE_SAVING':
      return {
        ...state,
        template: {
          ...state.template,
          isSaving: action.payload
        }
      };

    case 'UPDATE_TEMPLATE_SETTINGS':
      return {
        ...state,
        template: {
          ...state.template,
          ...action.payload,
          isModified: true
        }
      };

    case 'LOAD_TEMPLATE': {
      console.log('[BuilderContext] LOAD_TEMPLATE action received:', action);
      console.log('[BuilderContext] Action payload:', action.payload);

      const rawElements = (action.payload as Record<string, unknown>).elements as Element[] || [];
      console.log('[BuilderContext] Raw elements from payload:', rawElements);
      console.log('[BuilderContext] Raw elements count:', rawElements.length);

      const repairedElements = repairProductTableProperties(rawElements);
      console.log('[BuilderContext] Elements after repair:', repairedElements.length);

      // Ne pas convertir, garder les PX directement
      const clampedElements = clampElementPositions(repairedElements);
      console.log('[BuilderContext] Elements after clamping:', clampedElements.length);

      // Garder les dimensions du canvas si présentes
      const canvasData = (action.payload as Record<string, unknown>).canvas ?
        { ...state.canvas, ...(action.payload as Record<string, unknown>).canvas as Partial<CanvasState> } :
        state.canvas;
      console.log('[BuilderContext] Canvas data:', canvasData);

      const newState = {
        ...state,
        elements: clampedElements,
        canvas: canvasData,
        template: {
          id: (action.payload as Record<string, unknown>).id as string,
          name: (action.payload as Record<string, unknown>).name as string,
          isNew: false,
          isModified: false, // ✅ Template chargé de la DB n'est PAS modifié
          isSaving: false,
          isLoading: false, // ✅ Template is loaded
          lastSaved: (action.payload as Record<string, unknown>).lastSaved as Date
        },
        history: updateHistory(state, {
          ...state,
          elements: clampedElements,
          canvas: canvasData
        })
      };

      console.log('[BuilderContext] New state after LOAD_TEMPLATE:', {
        elementsCount: newState.elements.length,
        template: newState.template,
        canvas: newState.canvas
      });

      return newState;
    }

    case 'NEW_TEMPLATE': {
      return {
        ...initialState,
        template: {
          isNew: true,
          isModified: false,
          isSaving: false,
          isLoading: false // ✅ New template ready immediately
        }
      };
    }

    default: {
      return state;
    }
  }
}

// Fonctions utilitaires
function updateHistory(currentState: BuilderState, newState: BuilderState): HistoryState {
  // ✅ BUGFIX-011: Deep copy the state before storing in history to ensure immutability
  const stateCopy: BuilderState = {
    ...currentState,
    elements: currentState.elements.map(el => ({ ...el })),  // Shallow copy of array + elements
    canvas: { ...currentState.canvas },
    selection: { ...currentState.selection, selectedElements: [...currentState.selection.selectedElements] },
    drag: { ...currentState.drag },
    template: { ...currentState.template },
    history: currentState.history  // Don't deep copy history recursively
  };
  
  return {
    past: [...currentState.history.past, stateCopy],
    present: newState,
    future: [],
    canUndo: true,
    canRedo: false
  };
}

function calculateSelectionBounds(selectedIds: string[], elements: Element[]) {
  if (selectedIds.length === 0) return undefined;

  const selectedElements = elements.filter(el => selectedIds.includes(el.id));
  if (selectedElements.length === 0) return undefined;

  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

  selectedElements.forEach(el => {
    minX = Math.min(minX, el.x);
    minY = Math.min(minY, el.y);
    maxX = Math.max(maxX, el.x + el.width);
    maxY = Math.max(maxY, el.y + el.height);
  });

  return {
    x: minX,
    y: minY,
    width: maxX - minX,
    height: maxY - minY
  };
}

// Contexte
interface BuilderContextType {
  state: BuilderState;
  dispatch: React.Dispatch<BuilderAction>;
  // Actions helpers
  addElement: (element: Element) => void;
  updateElement: (id: string, updates: Partial<Element>) => void;
  removeElement: (id: string) => void;
  setSelection: (ids: string[]) => void;
  clearSelection: () => void;
  setCanvas: (canvas: Partial<CanvasState>) => void;
  setMode: (mode: BuilderMode) => void;
  undo: () => void;
  redo: () => void;
  reset: () => void;
  toggleGrid: () => void;
}

const BuilderContext = createContext<BuilderContextType | undefined>(undefined);

// Provider
interface BuilderProviderProps {
  children: ReactNode;
  initialState?: Partial<BuilderState>;
}

export function BuilderProvider({ children, initialState: initialStateProp }: BuilderProviderProps) {
  // fusionne l'état par défaut avec l'éventuel initialState passé en prop
  const mergedInitialState: BuilderState = {
    ...initialState,
    ...(initialStateProp || {})
  };

  const [state, dispatch] = useReducer(builderReducer, mergedInitialState);
  const canvasSettings = useCanvasSettings();

  // Appliquer les paramètres de zoom depuis Canvas Settings au démarrage
  useEffect(() => {
    // Appliquer le zoom par défaut depuis les paramètres
    if (canvasSettings.zoomDefault && canvasSettings.zoomDefault !== state.canvas.zoom) {
      dispatch({ 
        type: 'SET_CANVAS', 
        payload: { 
          zoom: Math.max(canvasSettings.zoomMin, Math.min(canvasSettings.zoomDefault, canvasSettings.zoomMax))
        } 
      });
    }
  }, [canvasSettings.zoomDefault, canvasSettings.zoomMax, canvasSettings.zoomMin, state.canvas.zoom]);

  // ✅ DISABLED: Template loading is now EXCLUSIVELY handled by useTemplate hook
  // which reads template_id from URL/localized data and calls AJAX GET
  // This prevents duplicate/race condition loads which caused double canvas renders
  // Previously: BuilderContext useEffect loaded → dispatch → Canvas renders
  //             useTemplate hook also loaded → dispatch → Canvas renders AGAIN (1/10th sec later)
  // Now: Only useTemplate.ts loads the template, ensuring single source of truth

  // Écouteur pour le chargement de template via API globale
  useEffect(() => {
    const handleLoadTemplate = (event: CustomEvent) => {
      console.log('[BuilderContext] pdfBuilderLoadTemplate event received:', event);
      console.log('[BuilderContext] Event detail:', event.detail);

      const templateData = event.detail;
      if (templateData) {
        console.log('[BuilderContext] Template data received:', templateData);
        console.log('[BuilderContext] Template has elements:', templateData.elements ? 'YES' : 'NO');

        if (templateData.elements) {
          console.log('[BuilderContext] Elements count:', templateData.elements.length);
        }

        console.log('[BuilderContext] Dispatching LOAD_TEMPLATE action');
        dispatch({
          type: 'LOAD_TEMPLATE',
          payload: templateData
        });
        console.log('[BuilderContext] LOAD_TEMPLATE action dispatched');
      } else {
        console.warn('[BuilderContext] No template data in event detail');
      }
    };

    console.log('[BuilderContext] Adding pdfBuilderLoadTemplate event listener');
    document.addEventListener('pdfBuilderLoadTemplate', handleLoadTemplate as EventListener);
    return () => {
      console.log('[BuilderContext] Removing pdfBuilderLoadTemplate event listener');
      document.removeEventListener('pdfBuilderLoadTemplate', handleLoadTemplate as EventListener);
    };
  }, []);





  // Actions helpers
  const addElement = (element: Element) => {
    dispatch({ type: 'ADD_ELEMENT', payload: element });
  };

  const updateElement = (id: string, updates: Partial<Element>) => {
    dispatch({ type: 'UPDATE_ELEMENT', payload: { id, updates } });
  };

  const removeElement = (id: string) => {
    dispatch({ type: 'REMOVE_ELEMENT', payload: id });
  };

  const setSelection = (ids: string[]) => {
    dispatch({ type: 'SET_SELECTION', payload: ids });
  };

  const clearSelection = () => {
    dispatch({ type: 'CLEAR_SELECTION' });
  };

  const setCanvas = (canvas: Partial<CanvasState>) => {
    dispatch({ type: 'SET_CANVAS', payload: canvas });
  };

  const setMode = (mode: BuilderMode) => {
    dispatch({ type: 'SET_MODE', payload: mode });
  };

  const undo = () => {
    dispatch({ type: 'UNDO' });
  };

  const redo = () => {
    dispatch({ type: 'REDO' });
  };

  const reset = () => {
    dispatch({ type: 'RESET' });
  };

  const toggleGrid = () => {
    setCanvas({ showGrid: !state.canvas.showGrid });
  };

  const value: BuilderContextType = {
    state,
    dispatch,
    addElement,
    updateElement,
    removeElement,
    setSelection,
    clearSelection,
    setCanvas,
    setMode,
    undo,
    redo,
    reset,
    toggleGrid
  };

  return (
    <BuilderContext.Provider value={value}>
      {children}
    </BuilderContext.Provider>
  );
}

// Hook pour utiliser le contexte
export function useBuilder(): BuilderContextType {
  const context = useContext(BuilderContext);
  if (context === undefined) {
    throw new Error('useBuilder must be used within a BuilderProvider');
  }
  return context;
}

// Hook spécialisé pour les éléments
export function useElements() {
  const { state, addElement, updateElement, removeElement } = useBuilder();

  return {
    elements: state.elements,
    addElement,
    updateElement,
    removeElement,
    getElement: (id: string) => state.elements.find(el => el.id === id),
    getElementsByType: (type: Element['type']) =>
      state.elements.filter(el => el.type === type)
  };
}

// Hook spécialisé pour la sélection
export function useSelection() {
  const { state, setSelection, clearSelection } = useBuilder();

  return {
    selectedElements: state.selection.selectedElements,
    selectionBounds: state.selection.selectionBounds,
    isSelecting: state.selection.isSelecting,
    setSelection,
    clearSelection,
    isSelected: (id: string) => state.selection.selectedElements.includes(id),
    toggleSelection: (id: string) => {
      const isSelected = state.selection.selectedElements.includes(id);
      if (isSelected) {
        setSelection(state.selection.selectedElements.filter(selectedId => selectedId !== id));
      } else {
        setSelection([...state.selection.selectedElements, id]);
      }
    }
  };
}

// Hook spécialisé pour le canvas
export function useCanvas() {
  const { state, setCanvas } = useBuilder();
  const canvasSettings = useCanvasSettings();

  return {
    canvas: state.canvas,
    setCanvas,
    zoomIn: () => setCanvas({ zoom: Math.min(state.canvas.zoom * 1.2, canvasSettings.zoomMax) }),
    zoomOut: () => setCanvas({ zoom: Math.max(state.canvas.zoom / 1.2, canvasSettings.zoomMin) }),
    setZoom: (zoom: number) => setCanvas({ zoom: Math.max(canvasSettings.zoomMin, Math.min(zoom, canvasSettings.zoomMax)) }),
    resetZoom: () => setCanvas({ zoom: canvasSettings.zoomDefault }),
    toggleGrid: () => setCanvas({ showGrid: !state.canvas.showGrid }),
    setBackgroundColor: (color: string) => setCanvas({ backgroundColor: color })
  };
}

export { BuilderContext };
// Pas d'export de conversion MM/PX - on utilise que PX
