// Force include all components and hooks by actually using them
import React from '@wordpress/element';
import ReactDOM from 'react-dom';
import { useHistory } from './hooks/useHistory.js';
import { useRotation } from './hooks/useRotation.js';
import { useResize } from './hooks/useResize.js';
import { CanvasElement } from './components/CanvasElement.jsx';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';

// Store direct references to constructors to prevent tree-shaking
const _forceIncludeRefs = {
  CanvasElement,
  PDFCanvasEditor,
  useHistory,
  useRotation,
  useResize
};

// Actually use the hooks to prevent tree-shaking
const forceUseHooks = () => {
  const history = useHistory();
  const rotation = useRotation(() => {});
  const resize = useResize({ onElementResize: () => {} });

  // Call methods to ensure they're used
  history.addToHistory([]);
  rotation.handleRotationStart({
    preventDefault: () => {},
    stopPropagation: () => {},
    clientX: 0,
    clientY: 0,
    currentTarget: { getBoundingClientRect: () => ({ left: 0, top: 0, width: 100, height: 100 }) }
  }, { rotation: 0 });
  resize.handleResizeStart({
    preventDefault: () => {},
    clientX: 0,
    clientY: 0
  }, { x: 0, y: 0, width: 100, height: 100 });

  return { history, rotation, resize };
};

// Actually render the components to prevent tree-shaking
const forceRenderComponents = () => {
  const element = {
    id: 'test-element',
    type: 'rectangle',
    x: 0,
    y: 0,
    width: 100,
    height: 100,
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    borderWidth: 0,
    rotation: 0
  };

  // Create actual React elements to force rendering
  const canvasElementJSX = React.createElement(_forceIncludeRefs.CanvasElement, {
    element: element,
    isSelected: true,
    zoom: 1,
    snapToGrid: false,
    gridSize: 10,
    canvasWidth: 595,
    canvasHeight: 842,
    onSelect: () => {},
    onUpdate: () => {},
    onRemove: () => {},
    onContextMenu: () => {},
    dragAndDrop: {
      isDragging: false,
      dragOffset: { x: 0, y: 0 },
      handleMouseDown: () => {},
      handleDragStart: () => {},
      handleDragOver: () => {},
      handleDrop: () => {}
    }
  });

  const pdfCanvasEditorJSX = React.createElement(_forceIncludeRefs.PDFCanvasEditor, {
    options: {
      initialElements: [element],
      templateId: null,
      width: 595,
      height: 842,
      orderData: {},
      isNew: true,
      templateName: 'Test Template'
    }
  });

  return { canvasElementJSX, pdfCanvasEditorJSX, element };
};

// Execute the functions to ensure they're called
const hooksResult = forceUseHooks();
const componentsResult = forceRenderComponents();

// Create separate hidden DOM elements for each component
const canvasElementContainer = document.createElement('div');
canvasElementContainer.style.display = 'none';
canvasElementContainer.style.position = 'absolute';
canvasElementContainer.style.left = '-9999px';
canvasElementContainer.style.top = '-9999px';
document.body.appendChild(canvasElementContainer);

const pdfCanvasEditorContainer = document.createElement('div');
pdfCanvasEditorContainer.style.display = 'none';
pdfCanvasEditorContainer.style.position = 'absolute';
pdfCanvasEditorContainer.style.left = '-9999px';
pdfCanvasEditorContainer.style.top = '-9999px';
document.body.appendChild(pdfCanvasEditorContainer);

// Actually render the components to separate DOM containers to force webpack inclusion
ReactDOM.render(componentsResult.canvasElementJSX, canvasElementContainer);
ReactDOM.render(componentsResult.pdfCanvasEditorJSX, pdfCanvasEditorContainer);

// Store references to prevent tree-shaking
window._forceInclude = {
  hooks: hooksResult,
  components: componentsResult,
  canvasElementContainer: canvasElementContainer,
  pdfCanvasEditorContainer: pdfCanvasEditorContainer,
  refs: _forceIncludeRefs
};

// Export everything
export { useHistory, useRotation, useResize, CanvasElement, PDFCanvasEditor };
