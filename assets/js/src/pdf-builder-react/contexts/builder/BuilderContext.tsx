import React, { createContext, useContext, useReducer, ReactNode, useEffect } from 'react';
import {
  BuilderState,
  BuilderAction,
  CanvasState,
  SelectionState,
  DragState,
  Element,
  BuilderMode,
  HistoryState,
  LoadTemplatePayload
} from '../../types/elements';

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
      isSaving: false
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
    description: '',
    tags: [],
    canvasWidth: 794,  // A4 width at 96 DPI for proper screen display
    canvasHeight: 1123, // A4 height at 96 DPI for proper screen display
    marginTop: 20,
    marginBottom: 20,
    showGuides: true,
    snapToGrid: false
  },
  previewMode: 'editor',
  showPreviewModal: false,
  history: initialHistoryState
};

// Reducer
function builderReducer(state: BuilderState, action: BuilderAction): BuilderState {
  switch (action.type) {
    case 'ADD_ELEMENT':
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

    case 'UPDATE_ELEMENT':
      return {
        ...state,
        elements: state.elements.map(el =>
          el.id === action.payload.id
            ? { ...el, ...action.payload.updates, updatedAt: new Date() }
            : el
        ),
        template: {
          ...state.template,
          isModified: true
        },
        history: updateHistory(state, {
          ...state,
          elements: state.elements.map(el =>
            el.id === action.payload.id
              ? { ...el, ...action.payload.updates, updatedAt: new Date() }
              : el
          )
        })
      };

    case 'REMOVE_ELEMENT':
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

    case 'SET_ELEMENTS':
      return {
        ...state,
        elements: action.payload,
        history: updateHistory(state, { ...state, elements: action.payload })
      };

    case 'SET_SELECTION':
      return {
        ...state,
        selection: {
          ...state.selection,
          selectedElements: action.payload,
          selectionBounds: calculateSelectionBounds(action.payload, state.elements)
        }
      };

    case 'CLEAR_SELECTION':
      return {
        ...state,
        selection: {
          ...state.selection,
          selectedElements: [],
          selectionBounds: undefined
        }
      };

    case 'SET_CANVAS':
      return {
        ...state,
        canvas: { ...state.canvas, ...action.payload }
      };

    case 'SET_MODE':
      return {
        ...state,
        mode: action.payload
      };

    case 'SET_DRAG_STATE':
      return {
        ...state,
        drag: { ...state.drag, ...action.payload }
      };

    case 'SET_PREVIEW_MODE':
      return {
        ...state,
        previewMode: action.payload
      };

    case 'SET_SHOW_PREVIEW_MODAL':
      return {
        ...state,
        showPreviewModal: action.payload
      };

    case 'SET_ORDER_ID':
      return {
        ...state,
        orderId: action.payload
      };

    case 'UNDO':
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

    case 'REDO':
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

    case 'RESET':
      return initialState;

    case 'SAVE_TEMPLATE':
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

    case 'SET_TEMPLATE_MODIFIED':
      return {
        ...state,
        template: {
          ...state.template,
          isModified: action.payload
        }
      };

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

    case 'LOAD_TEMPLATE':
      return {
        ...state,
        elements: (action.payload as any).elements || [],
        canvas: (action.payload as any).canvas ? { ...state.canvas, ...(action.payload as any).canvas } : state.canvas,
        template: {
          id: (action.payload as any).id,
          name: (action.payload as any).name,
          isNew: false,
          isModified: true, // Template chargé est considéré comme modifiable
          isSaving: false,
          lastSaved: (action.payload as any).lastSaved
        },
        history: updateHistory(state, {
          ...state,
          elements: (action.payload as any).elements || [],
          canvas: (action.payload as any).canvas ? { ...state.canvas, ...(action.payload as any).canvas } : state.canvas
        })
      };

    case 'NEW_TEMPLATE':
      return {
        ...initialState,
        template: {
          isNew: true,
          isModified: false,
          isSaving: false
        }
      };

    default:
      return state;
  }
}

// Fonctions utilitaires
function updateHistory(currentState: BuilderState, newState: BuilderState): HistoryState {
  return {
    past: [...currentState.history.past, currentState],
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

  // Fonction de sauvegarde automatique
  const autoSaveTemplate = async (): Promise<void> => {
    if (!state.template.id || state.template.isSaving) return;

    console.log('🔄 [AUTO SAVE] Début de la sauvegarde automatique');

    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      const ajaxUrl = (window as any).ajaxurl || '/wp-admin/admin-ajax.php';
      const nonce = (window as any).pdfBuilderData?.nonce ||
                   (window as any).pdfBuilderNonce ||
                   (window as any).pdfBuilderReactData?.nonce || '';

      console.log('📊 [AUTO SAVE] État des éléments avant nettoyage:', {
        elementCount: state.elements.length,
        elements: state.elements.map(el => ({
          id: el.id,
          type: el.type,
          hasComplexProps: typeof el === 'object' && el !== null && Object.keys(el).length > 10
        }))
      });

      // Fonction de nettoyage ultra-robuste pour JSON
      const deepCleanForJSON = (obj: any, visited = new WeakSet(), path = ''): any => {
        // Éviter les références circulaires
        if (obj === null || typeof obj !== 'object') {
          return obj;
        }

        if (visited.has(obj)) {
          console.warn(`🔄 [CLEAN JSON] Référence circulaire détectée à ${path}`);
          return '[Circular Reference]';
        }

        visited.add(obj);

        try {
          if (Array.isArray(obj)) {
            return obj.map((item, index) => deepCleanForJSON(item, visited, `${path}[${index}]`));
          }

          if (obj instanceof Date) {
            return obj.toISOString();
          }

          if (typeof obj === 'function') {
            console.warn(`⚠️ [CLEAN JSON] Fonction supprimée à ${path}`);
            return undefined;
          }

          // Pour les objets, créer une copie propre
          const cleanObj: any = {};
          for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
              const value = obj[key];

              // Skip les propriétés problématiques
              if (typeof value === 'function' ||
                  key === 'canvas' ||
                  key === 'context' ||
                  key === '_reactInternalInstance' ||
                  key === '_reactInternals' ||
                  key.startsWith('__react')) {
                console.warn(`⚠️ [CLEAN JSON] Propriété problématique supprimée: ${key} à ${path}`);
                continue;
              }

              // Nettoyer récursivement les valeurs
              const cleanValue = deepCleanForJSON(value, visited, `${path}.${key}`);
              if (cleanValue !== undefined) {
                cleanObj[key] = cleanValue;
              }
            }
          }

          return cleanObj;
        } finally {
          visited.delete(obj);
        }
      };

      // Nettoyer tous les éléments
      const cleanElements = state.elements.map((element, index) => {
        try {
          console.log(`🧹 [CLEAN ELEMENT] Nettoyage élément ${index}:`, {
            id: element.id,
            type: element.type,
            keys: Object.keys(element)
          });

          const cleaned = deepCleanForJSON(element, new WeakSet(), `element[${index}]`);

          // S'assurer que les propriétés essentielles sont présentes
          const finalElement = {
            id: cleaned.id || `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            type: cleaned.type || 'text',
            x: cleaned.x || 0,
            y: cleaned.y || 0,
            width: cleaned.width || 100,
            height: cleaned.height || 50,
            visible: cleaned.visible !== false,
            locked: cleaned.locked || false,
            createdAt: cleaned.createdAt || new Date().toISOString(),
            updatedAt: new Date().toISOString(),
            ...cleaned // Garder toutes les autres propriétés nettoyées
          };

          console.log(`✅ [CLEAN ELEMENT] Élément ${index} nettoyé avec succès:`, {
            id: finalElement.id,
            type: finalElement.type,
            keyCount: Object.keys(finalElement).length
          });

          return finalElement;
        } catch (error) {
          console.error(`❌ [CLEAN ELEMENT] Erreur lors du nettoyage d'un élément ${index}:`, error, element);
          // Retourner un élément minimal en cas d'échec
          return {
            id: element.id || `fallback_${Date.now()}`,
            type: element.type || 'text',
            x: element.x || 0,
            y: element.y || 0,
            width: element.width || 100,
            height: element.height || 50,
            visible: true,
            locked: false,
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString()
          };
        }
      });

      console.log('🔍 [AUTO SAVE] Test de sérialisation JSON...');

      // Test final de sérialisation avant envoi
      let serializedElements: string;
      try {
        serializedElements = JSON.stringify(cleanElements);
        // Vérifier que c'est du JSON valide
        JSON.parse(serializedElements);
        console.log('✅ [AUTO SAVE] Sérialisation JSON réussie, taille:', serializedElements.length);
      } catch (jsonError) {
        console.error('❌ [AUTO SAVE] Erreur JSON même après nettoyage:', jsonError);
        console.error('🔍 [AUTO SAVE] Éléments nettoyés qui causent le problème:', cleanElements);
        throw new Error('Impossible de sérialiser les éléments même après nettoyage');
      }

      console.log('📡 [AUTO SAVE] Envoi de la requête AJAX...');

      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'pdf_builder_auto_save_template',
          template_id: state.template.id.toString(),
          elements: serializedElements,
          nonce: nonce
        })
      });

      const data = await response.json();

      if (data.success) {
        dispatch({
          type: 'SAVE_TEMPLATE',
          payload: {
            id: state.template.id,
            lastSaved: new Date()
          }
        });
      } else {
        console.error('Erreur sauvegarde automatique:', data.data);
      }
    } catch (error) {
      console.error('Erreur réseau sauvegarde automatique:', error);
    } finally {
      dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
    }
  };

  // Sauvegarde automatique toutes les 2.5 secondes
  useEffect(() => {
    if (!state.elements.length || !state.template.id) return;

    const saveTimer = setTimeout(async () => {
      try {
        await autoSaveTemplate();
      } catch (error) {
        console.error('Erreur lors de la sauvegarde automatique:', error);
      }
    }, 2500); // 2.5 secondes

    return () => clearTimeout(saveTimer);
  }, [state.elements, state.template.id]);

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

  return {
    canvas: state.canvas,
    setCanvas,
    zoomIn: () => setCanvas({ zoom: Math.min(state.canvas.zoom * 1.2, 5) }),
    zoomOut: () => setCanvas({ zoom: Math.max(state.canvas.zoom / 1.2, 0.1) }),
    setZoom: (zoom: number) => setCanvas({ zoom: Math.max(0.1, Math.min(zoom, 5)) }),
    resetZoom: () => setCanvas({ zoom: 1 }),
    toggleGrid: () => setCanvas({ showGrid: !state.canvas.showGrid }),
    setBackgroundColor: (color: string) => setCanvas({ backgroundColor: color })
  };
}

export { BuilderContext };