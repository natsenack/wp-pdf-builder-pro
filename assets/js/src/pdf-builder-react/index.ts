// Export principal du PDF Builder React
export { PDFBuilder } from './PDFBuilder';

// Export des composants individuels
export { Canvas } from './components/canvas/Canvas';
export { Toolbar } from './components/toolbar/Toolbar';
export { PropertiesPanel } from './components/properties/PropertiesPanel';

// Export du contexte et des hooks
export { BuilderProvider, useBuilder } from './contexts/builder/BuilderContext';
export { useElements, useSelection, useCanvas } from './contexts/builder/BuilderContext';

// Export des types
export type {
  Element,
  BuilderState,
  BuilderAction,
  CanvasState,
  SelectionState,
  DragState,
  BuilderMode,
  Point,
  Size,
  Bounds
} from './types/elements';

// Export du point d'entrée WordPress
export { initPDFBuilderReact } from './wordpress-entry';