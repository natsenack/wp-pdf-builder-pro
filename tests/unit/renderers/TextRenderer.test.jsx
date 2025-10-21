/**
 * Tests pour TextRenderer
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import { TextRenderer } from '../../../src/renderers/TextRenderer';

describe('TextRenderer', () => {
  const mockElement = {
    id: 'text-1',
    type: 'text',
    x: 10,
    y: 20,
    width: 200,
    height: 50,
    content: 'Texte de test',
    text: 'Texte de test',
    fontSize: 14,
    fontFamily: 'Arial',
    color: '#333333',
    backgroundColor: 'transparent',
    borderWidth: 0,
    borderColor: '#000000',
    borderRadius: 0,
    opacity: 1,
    visible: true
  };

  test('devrait rendre le texte correctement', () => {
    render(
      <TextRenderer element={mockElement} canvasScale={1} />
    );
    
    expect(screen.getByText('Texte de test')).toBeInTheDocument();
  });

  test('devrait appliquer les styles de position correctement', () => {
    const { container } = render(
      <TextRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    const style = window.getComputedStyle(element);
    
    expect(element.style.left).toBe('10px');
    expect(element.style.top).toBe('20px');
    expect(element.style.width).toBe('200px');
  });

  test('devrait respecter la visibilité', () => {
    const hiddenElement = { ...mockElement, visible: false };
    const { container } = render(
      <TextRenderer element={hiddenElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.display).toBe('none');
  });

  test('devrait utiliser content au lieu de text si text manque', () => {
    const elementWithoutText = { ...mockElement, text: undefined };
    render(
      <TextRenderer element={elementWithoutText} canvasScale={1} />
    );
    
    expect(screen.getByText('Texte de test')).toBeInTheDocument();
  });

  test('devrait appliquer les styles de couleur et fond', () => {
    const { container } = render(
      <TextRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.color).toBe('#333333');
    expect(element.style.backgroundColor).toBe('transparent');
  });

  test('devrait appliquer la bordure si borderWidth > 0', () => {
    const elementWithBorder = { ...mockElement, borderWidth: 2, borderColor: '#000000' };
    const { container } = render(
      <TextRenderer element={elementWithBorder} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.border).toContain('2px');
    expect(element.style.border).toContain('solid');
  });

  test('devrait appliquer l\'échelle correctement', () => {
    const { container } = render(
      <TextRenderer element={mockElement} canvasScale={2} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.left).toBe('20px');
    expect(element.style.top).toBe('40px');
    expect(element.style.fontSize).toBe('28px');
  });

  test('devrait gérer la rotation et l\'échelle de transformation', () => {
    const elementWithTransform = { ...mockElement, rotation: 45, scale: 0.5 };
    const { container } = render(
      <TextRenderer element={elementWithTransform} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.transform).toContain('rotate(45deg)');
    expect(element.style.transform).toContain('scale(0.5)');
  });

  test('devrait applier l\'ombre correctement', () => {
    const elementWithShadow = { 
      ...mockElement, 
      shadow: true, 
      shadowColor: '#000000',
      shadowOffsetX: 2,
      shadowOffsetY: 2
    };
    const { container } = render(
      <TextRenderer element={elementWithShadow} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-text-element');
    expect(element.style.boxShadow).toContain('2px');
  });
});
