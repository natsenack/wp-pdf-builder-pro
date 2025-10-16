// Import global fallbacks first
import './globalFallback.js';

// Main application entry point that actually uses all components
import React from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';
import { CanvasElement } from './components/CanvasElement.jsx';
import { useHistory } from './hooks/useHistory.js';
import { useRotation } from './hooks/useRotation.js';
import { useResize } from './hooks/useResize.js';

// Initialize the application
const init = () => {
  // Create a test element to render
  const testElement = {
    id: 'test-element',
    type: 'rectangle',
    x: 50,
    y: 50,
    width: 100,
    height: 50,
    backgroundColor: '#e5e7eb',
    borderColor: '#374151',
    borderWidth: 1,
    rotation: 0,
    visible: true
  };

  // Create container for the editor
  const editorContainer = document.createElement('div');
  editorContainer.id = 'pdf-canvas-editor';
  editorContainer.style.width = '100%';
  editorContainer.style.height = '600px';
  document.body.appendChild(editorContainer);

  // Render the PDF Canvas Editor with test data
  ReactDOM.render(
    React.createElement(PDFCanvasEditor, {
      options: {
        initialElements: [testElement],
        templateId: null,
        width: 595,
        height: 842,
        orderData: {},
        isNew: true,
        templateName: 'Test Template'
      }
    }),
    editorContainer
  );

  // Also render a standalone CanvasElement to ensure it's included
  const elementContainer = document.createElement('div');
  elementContainer.id = 'canvas-element-test';
  elementContainer.style.position = 'absolute';
  elementContainer.style.left = '-9999px';
  elementContainer.style.top = '-9999px';
  elementContainer.style.width = '100px';
  elementContainer.style.height = '50px';
  document.body.appendChild(elementContainer);

  ReactDOM.render(
    React.createElement(CanvasElement, {
      element: testElement,
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
    }),
    elementContainer
  );

  // Store references globally to prevent tree-shaking
  window._forceInclude = {
    PDFCanvasEditor,
    CanvasElement,
    useHistory,
    useRotation,
    useResize,
    editorContainer,
    elementContainer
  };
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

// Export everything to ensure webpack includes them
export { PDFCanvasEditor, CanvasElement, useHistory, useRotation, useResize };
