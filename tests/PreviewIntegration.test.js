/**
 * Test d'intégration de l'aperçu des templates
 * Vérifie que l'aperçu traite correctement tous les éléments chargés
 */

import { PreviewRenderer } from '../assets/js/src/pdf-builder-react/renderers/PreviewRenderer';

// Mock du DataProvider
class MockDataProvider {
  getVariableValue(variable) {
    const mockData = {
      '{{customer_name}}': 'Jean Dupont',
      '{{order_number}}': 'CMD-2025-001'
    };
    return mockData[variable] || variable;
  }

  getMode() {
    return 'canvas';
  }
}

describe('Preview Integration', () => {
  let canvas;
  let dataProvider;

  beforeEach(() => {
    // Créer un canvas pour les tests
    canvas = {
      width: 794,
      height: 1123,
      getContext: jest.fn(() => ({
        fillStyle: '',
        strokeStyle: '',
        lineWidth: 1,
        fillRect: jest.fn(),
        strokeRect: jest.fn(),
        fillText: jest.fn(),
        beginPath: jest.fn(),
        arc: jest.fn(),
        fill: jest.fn(),
        stroke: jest.fn(),
        save: jest.fn(),
        restore: jest.fn(),
        translate: jest.fn(),
        rotate: jest.fn(),
        scale: jest.fn(),
        clearRect: jest.fn(),
        font: '',
        textAlign: 'left',
        textBaseline: 'top'
      }))
    };
    dataProvider = new MockDataProvider();
  });

  test('devrait appeler render avec les bons paramètres', () => {
    const elements = [{
      id: "rect_1",
      type: "rectangle",
      x: 50,
      y: 50,
      width: 200,
      height: 100,
      fillColor: "#ff0000",
      strokeColor: "#000000",
      strokeWidth: 2,
      rotation: 0,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date(),
      updatedAt: new Date()
    }];

    // Le test vérifie simplement que la fonction ne throw pas d'erreur
    expect(() => {
      PreviewRenderer.render({
        canvas,
        elements,
        dataProvider
      });
    }).not.toThrow();
  });

  test('devrait gérer les éléments texte avec variables', () => {
    const elements = [{
      id: "text_1",
      type: "text",
      x: 100,
      y: 200,
      width: 150,
      height: 30,
      text: "Bonjour {{customer_name}}",
      fontSize: 18,
      color: "#333333",
      align: "center",
      rotation: 0,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date(),
      updatedAt: new Date()
    }];

    expect(() => {
      PreviewRenderer.render({
        canvas,
        elements,
        dataProvider
      });
    }).not.toThrow();
  });

  test('devrait gérer les cercles', () => {
    const elements = [{
      id: "circle_1",
      type: "circle",
      x: 300,
      y: 50,
      width: 80,
      height: 80,
      fillColor: "#00ff00",
      strokeColor: "#000000",
      strokeWidth: 1,
      rotation: 0,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date(),
      updatedAt: new Date()
    }];

    expect(() => {
      PreviewRenderer.render({
        canvas,
        elements,
        dataProvider
      });
    }).not.toThrow();
  });

  test('devrait gérer plusieurs éléments simultanément', () => {
    const elements = [
      {
        id: "rect_1",
        type: "rectangle",
        x: 50,
        y: 50,
        width: 200,
        height: 100,
        fillColor: "#ff0000",
        strokeColor: "#000000",
        strokeWidth: 2,
        rotation: 0,
        opacity: 1,
        visible: true,
        locked: false,
        createdAt: new Date(),
        updatedAt: new Date()
      },
      {
        id: "text_1",
        type: "text",
        x: 100,
        y: 200,
        width: 150,
        height: 30,
        text: "Test Preview",
        fontSize: 18,
        color: "#333333",
        align: "center",
        rotation: 0,
        opacity: 1,
        visible: true,
        locked: false,
        createdAt: new Date(),
        updatedAt: new Date()
      }
    ];

    expect(() => {
      PreviewRenderer.render({
        canvas,
        elements,
        dataProvider
      });
    }).not.toThrow();
  });

  test('devrait gérer les éléments avec rotation', () => {
    const elements = [{
      id: "rotated_rect",
      type: "rectangle",
      x: 100,
      y: 100,
      width: 150,
      height: 80,
      fillColor: "#ff0000",
      strokeColor: "#000000",
      strokeWidth: 2,
      rotation: 45,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date(),
      updatedAt: new Date()
    }];

    expect(() => {
      PreviewRenderer.render({
        canvas,
        elements,
        dataProvider
      });
    }).not.toThrow();
  });
});