/**
 * Tests pour RectangleRenderer
 */
import React from 'react';
import { render } from '@testing-library/react';
import { RectangleRenderer } from '../../../resources/js/components/preview-system/renderers/RectangleRenderer';

describe('RectangleRenderer', () => {
  const mockElement = {
    id: 'rect-1',
    type: 'rectangle',
    x: 50,
    y: 100,
    width: 150,
    height: 75,
    backgroundColor: '#cccccc',
    borderColor: '#000000',
    borderWidth: 2,
    borderRadius: 5,
    opacity: 100,
    visible: true
  };

  test('devrait rendre le rectangle correctement', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element).toBeInTheDocument();
  });

  test('devrait appliquer les positions en pixels', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.left).toBe('50px');
    expect(element.style.top).toBe('100px');
    expect(element.style.width).toBe('150px');
    expect(element.style.height).toBe('75px');
  });

  test('devrait appliquer le backgroundColor', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.backgroundColor).toBe('#cccccc');
  });

  test('devrait appliquer la bordure correctement', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.border).toContain('2px');
    expect(element.style.border).toContain('solid');
    expect(element.style.border).toContain('#000000');
  });

  test('devrait appliquer le borderRadius', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.borderRadius).toBe('5px');
  });

  test('devrait convertir l\'opacité correctement (0-100 en 0-1)', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.opacity).toBe('1');
  });

  test('devrait appliquer l\'opacité 50%', () => {
    const elementHalfOpaque = { ...mockElement, opacity: 50 };
    const { container } = render(
      <RectangleRenderer element={elementHalfOpaque} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.opacity).toBe('0.5');
  });

  test('devrait cacher l\'élément si visible=false', () => {
    const { container } = render(
      <RectangleRenderer element={{ ...mockElement, visible: false }} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.display).toBe('none');
  });

  test('devrait appliquer l\'échelle correctement', () => {
    const { container } = render(
      <RectangleRenderer element={mockElement} canvasScale={2} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.left).toBe('100px');
    expect(element.style.top).toBe('200px');
    expect(element.style.width).toBe('300px');
    expect(element.style.height).toBe('150px');
  });

  test('devrait ne pas appliquer de bordure si borderWidth=0', () => {
    const { container } = render(
      <RectangleRenderer element={{ ...mockElement, borderWidth: 0 }} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.border).toBe('none');
  });

  test('devrait appliquer la transformation (rotation + scale)', () => {
    const { container } = render(
      <RectangleRenderer element={{ ...mockElement, rotation: 45, scale: 0.75 }} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.transform).toContain('rotate(45deg)');
    expect(element.style.transform).toContain('scale(0.75)');
    expect(element.style.transformOrigin).toBe('top left');
  });

  test('devrait appliquer l\'ombre si shadow=true', () => {
    const { container } = render(
      <RectangleRenderer element={{ 
        ...mockElement, 
        shadow: true, 
        shadowColor: '#000000',
        shadowOffsetX: 3,
        shadowOffsetY: 3
      }} canvasScale={1} />
    );
    
    const element = container.querySelector('.preview-rectangle-element');
    expect(element.style.boxShadow).toContain('3px');
  });
});
