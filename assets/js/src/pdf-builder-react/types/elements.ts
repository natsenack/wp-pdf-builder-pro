// Types simplifiés pour éviter les erreurs TypeScript complexes
export interface Point {
  x: number;
  y: number;
}

export interface Size {
  width: number;
  height: number;
}

export interface Bounds extends Point, Size {}

export interface TemplateState {
  id?: string;
  name?: string;
  description?: string;
  tags?: string[];
  canvasWidth?: number;
  canvasHeight?: number;
  marginTop?: number;
  marginBottom?: number;
  showGuides?: boolean;
  snapToGrid?: boolean;
  isNew: boolean;
  isModified: boolean;
  isSaving: boolean;
  lastSaved?: Date;
}

export interface BaseElement {
  id: string;
  type: string;
  x: number;
  y: number;
  width: number;
  height: number;
  rotation?: number;
  opacity?: number;
  visible: boolean;
  locked: boolean;
  createdAt: Date;
  updatedAt: Date;
  [key: string]: any; // Propriétés dynamiques
}

export type Element = BaseElement;

export interface ElementProperties {
  [key: string]: any;
}

export interface CanvasState {
  zoom: number;
  pan: Point;
  showGrid: boolean;
  gridSize: number;
  snapToGrid: boolean;
  backgroundColor: string;
}

export interface SelectionState {
  selectedElements: string[];
  selectionBounds?: Bounds;
  isSelecting: boolean;
  selectionStart?: Point;
  selectionEnd?: Point;
}

export interface DragState {
  isDragging: boolean;
  dragStart?: Point;
  dragOffset?: Point;
  draggedElements: string[];
}

export interface BuilderState {
  elements: Element[];
  canvas: CanvasState;
  selection: SelectionState;
  drag: DragState;
  mode: BuilderMode;
  history: HistoryState;
  // Propriétés du template
  template: TemplateState;
  // Mode de prévisualisation (éditeur vs commande)
  previewMode: 'editor' | 'command';
  orderId?: string;
}

export type BuilderMode =
  | 'select'
  | 'rectangle'
  | 'circle'
  | 'text'
  | 'image'
  | 'line'
  | 'pan'
  | 'zoom';

export interface HistoryState {
  past: BuilderState[];
  present: BuilderState;
  future: BuilderState[];
  canUndo: boolean;
  canRedo: boolean;
}

export interface BuilderConfig {
  containerId: string;
  initialState?: Partial<BuilderState>;
  enableHistory?: boolean;
  maxHistorySize?: number;
  enableKeyboard?: boolean;
  enableContextMenu?: boolean;
  theme?: 'light' | 'dark';
}

// Événements
export interface BuilderEvents {
  onElementAdded: (element: Element) => void;
  onElementUpdated: (element: Element) => void;
  onElementRemoved: (elementId: string) => void;
  onSelectionChanged: (selectedIds: string[]) => void;
  onCanvasChanged: (canvas: CanvasState) => void;
  onModeChanged: (mode: BuilderMode) => void;
  onHistoryChanged: (history: HistoryState) => void;
}

// Actions pour le reducer
export type BuilderAction =
  | { type: 'ADD_ELEMENT'; payload: Element }
  | { type: 'UPDATE_ELEMENT'; payload: { id: string; updates: Partial<Element> } }
  | { type: 'REMOVE_ELEMENT'; payload: string }
  | { type: 'SET_ELEMENTS'; payload: Element[] }
  | { type: 'SET_SELECTION'; payload: string[] }
  | { type: 'CLEAR_SELECTION' }
  | { type: 'SET_CANVAS'; payload: Partial<CanvasState> }
  | { type: 'SET_MODE'; payload: BuilderMode }
  | { type: 'SET_DRAG_STATE'; payload: Partial<DragState> }
  | { type: 'SET_PREVIEW_MODE'; payload: 'editor' | 'command' }
  | { type: 'SET_ORDER_ID'; payload: string | undefined }
  | { type: 'UNDO' }
  | { type: 'REDO' }
  | { type: 'RESET' }
  | { type: 'SAVE_TEMPLATE'; payload?: { id?: string; name?: string } }
  | { type: 'SET_TEMPLATE_MODIFIED'; payload: boolean }
  | { type: 'SET_TEMPLATE_SAVING'; payload: boolean }
  | { type: 'UPDATE_TEMPLATE_SETTINGS'; payload: Partial<TemplateState> }
  | { type: 'LOAD_TEMPLATE'; payload: { id?: string; name?: string; elements?: Element[]; lastSaved?: Date } }
  | { type: 'NEW_TEMPLATE' };

// Props des composants
export interface CanvasProps {
  width: number;
  height: number;
  state: BuilderState;
  onStateChange: (action: BuilderAction) => void;
  className?: string;
}

export interface ToolbarProps {
  mode: BuilderMode;
  onModeChange: (mode: BuilderMode) => void;
  onAction: (action: string) => void;
  disabled?: boolean;
}

export interface PropertiesPanelProps {
  selectedElements: Element[];
  onUpdateElement: (id: string, updates: Partial<Element>) => void;
  onDeleteElements: (ids: string[]) => void;
}

export interface LoadTemplatePayload {
  id?: string;
  name?: string;
  elements?: BaseElement[];
  canvas?: Partial<CanvasState>;
  lastSaved?: Date;
}