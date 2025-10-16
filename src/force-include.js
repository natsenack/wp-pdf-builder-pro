// Force include all components and hooks to prevent tree-shaking
import { useHistory } from './hooks/useHistory';
import { useRotation } from './hooks/useRotation';
import { useResize } from './hooks/useResize';
import { CanvasElement } from './components/CanvasElement';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// Store references to prevent tree-shaking
window._forceInclude = {
  useHistory,
  useRotation,
  useResize,
  CanvasElement,
  PDFCanvasEditor
};

// Export everything explicitly
export { useHistory, useRotation, useResize, CanvasElement, PDFCanvasEditor };

// Also export the main module
export { default } from './index';