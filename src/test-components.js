// Test file to check if components are accessible
import React from 'react';
import { CanvasElement } from './components/CanvasElement.jsx';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';
import { useRotation } from './hooks/useRotation.js';

// Test basic functionality
const testElement = {
  id: 'test-element',
  type: 'rectangle',
  x: 10,
  y: 10,
  width: 100,
  height: 50,
  backgroundColor: '#ff0000'
};

// Test hook
const rotationHook = useRotation();

export { CanvasElement, PDFCanvasEditor, useRotation, testElement };