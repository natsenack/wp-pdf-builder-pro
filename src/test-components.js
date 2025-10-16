// Test file to check if components are accessible
import React from 'react';
import { CanvasElement } from './components/CanvasElement.jsx';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';
import { useRotation } from './hooks/useRotation.js';

// console.log('Testing component imports...');
// console.log('CanvasElement:', CanvasElement);
// console.log('PDFCanvasEditor:', PDFCanvasEditor);
// console.log('useRotation:', useRotation);

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

// console.log('Test element created:', testElement);

// Test hook
const rotationHook = useRotation();
// console.log('useRotation hook:', rotationHook);

export { CanvasElement, PDFCanvasEditor, useRotation, testElement };