import React from 'react';
import { render } from '@testing-library/react';
import { PDFCanvasEditor } from '../resources/js/components/PDFCanvasEditor';
import { CanvasElement } from '../resources/js/components/CanvasElement';
import PropertiesPanel from '../resources/js/components/PropertiesPanel';

// Test de base pour vérifier que les composants se rendent sans erreur
describe('Canvas Components', () => {
  test('PDFCanvasEditor renders without crashing', () => {
    const mockProps = {
      elements: [],
      onElementsChange: jest.fn(),
      selectedElements: [],
      onSelectionChange: jest.fn(),
      canvasWidth: 595,
      canvasHeight: 842,
      zoom: 1,
      panOffset: { x: 0, y: 0 },
      showGuides: true,
      snapToGrid: true,
      gridSize: 10,
      backgroundColor: '#ffffff',
      options: {
        orderData: {
          invoice_number: 'INV-001',
          invoice_date: '15/10/2025',
          order_number: '#12345',
          order_date: '15/10/2025',
          customer_name: 'John Doe',
          customer_email: 'john.doe@example.com',
          billing_address: '123 Rue de Test\n75001 Paris\nFrance',
          shipping_address: '456 Rue de Livraison\n75002 Paris\nFrance',
          payment_method: 'Carte bancaire',
          order_status: 'Traitée',
          subtotal: '45,00 €',
          discount: '-5,00 €',
          shipping: '5,00 €',
          tax: '9,00 €',
          total: '54,00 €',
          refund: '0,00 €'
        }
      }
    };

    expect(() => {
      render(<PDFCanvasEditor {...mockProps} />);
    }).not.toThrow();
  });

  test('CanvasElement renders text element without crashing', () => {
    const mockElement = {
      id: 'test-text',
      type: 'text',
      x: 100,
      y: 100,
      width: 200,
      height: 50,
      text: 'Test Text',
      fontSize: 16,
      fontFamily: 'Arial',
      color: '#000000',
      textAlign: 'left',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      rotation: 0,
      opacity: 1
    };

    const mockProps = {
      element: mockElement,
      isSelected: false,
      zoom: 1,
      snapToGrid: true,
      gridSize: 10,
      canvasWidth: 595,
      canvasHeight: 842,
      onSelect: jest.fn(),
      onUpdate: jest.fn(),
      onRemove: jest.fn(),
      onContextMenu: jest.fn(),
      dragAndDrop: {
        isDragging: false,
        draggedElementId: null,
        dragOffset: { x: 0, y: 0 }
      },
      enableRotation: true,
      rotationStep: 15,
      rotationSnap: true,
      guides: { horizontal: [], vertical: [] },
      snapToGuides: true,
      canvasRect: { left: 0, top: 0, width: 595, height: 842 }
    };

    expect(() => {
      render(<CanvasElement {...mockProps} />);
    }).not.toThrow();
  });

  test('CanvasElement renders shape element without crashing', () => {
    const mockElement = {
      id: 'test-shape',
      type: 'rectangle',
      x: 100,
      y: 100,
      width: 200,
      height: 100,
      fillColor: '#3b82f6',
      strokeColor: '#1e40af',
      strokeWidth: 2,
      borderRadius: 0,
      rotation: 0,
      opacity: 1
    };

    const mockProps = {
      element: mockElement,
      isSelected: false,
      zoom: 1,
      snapToGrid: true,
      gridSize: 10,
      canvasWidth: 595,
      canvasHeight: 842,
      onSelect: jest.fn(),
      onUpdate: jest.fn(),
      onRemove: jest.fn(),
      onContextMenu: jest.fn(),
      dragAndDrop: {
        isDragging: false,
        draggedElementId: null,
        dragOffset: { x: 0, y: 0 }
      },
      enableRotation: true,
      rotationStep: 15,
      rotationSnap: true,
      guides: { horizontal: [], vertical: [] },
      snapToGuides: true,
      canvasRect: { left: 0, top: 0, width: 595, height: 842 }
    };

    expect(() => {
      render(<CanvasElement {...mockProps} />);
    }).not.toThrow();
  });

  test('PropertiesPanel renders without crashing', () => {
    const mockProps = {
      selectedElements: [],
      elements: [],
      onPropertyChange: jest.fn(),
      onBatchUpdate: jest.fn()
    };

    expect(() => {
      render(<PropertiesPanel {...mockProps} />);
    }).not.toThrow();
  });

  test('PropertiesPanel renders with selected element', () => {
    const mockElement = {
      id: 'test-element',
      type: 'text',
      x: 100,
      y: 100,
      width: 200,
      height: 50,
      color: '#333333',
      backgroundColor: 'transparent',
      fontSize: 14
    };

    const mockProps = {
      selectedElements: [mockElement],
      elements: [mockElement],
      onPropertyChange: jest.fn(),
      onBatchUpdate: jest.fn()
    };

    expect(() => {
      render(<PropertiesPanel {...mockProps} />);
    }).not.toThrow();
  });
});