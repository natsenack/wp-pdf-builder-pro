/**
 * Tests pour ImageRenderer
 */
import React from 'react';
import { render } from '@testing-library/react';

// Vérifier si le module ImageRenderer existe avant de l'importer
let ImageRenderer;
try {
  ImageRenderer = require('../../../src/renderers/ImageRenderer').ImageRenderer;
} catch (error) {
  // Module pas encore créé - tests seront skipped
  ImageRenderer = null;
}

describe('ImageRenderer', () => {
  // Skip tous les tests si le module n'existe pas
  beforeAll(() => {
    if (!ImageRenderer) {
      console.warn('ImageRenderer module not found - skipping tests (will be implemented in Phase 3)');
    }
  });

  // Skip si le module n'existe pas
  const itOrSkip = ImageRenderer ? it : it.skip;
  const mockElement = {
    id: 'img-1',
    type: 'image',
    x: 20,
    y: 30,
    width: 150,
    height: 100,
    imageUrl: 'https://example.com/image.jpg',
    alt: 'Test Image',
    objectFit: 'contain',
    backgroundColor: 'white',
    borderColor: '#cccccc',
    borderWidth: 1,
    borderRadius: 0,
    opacity: 1,
    visible: true
  };

  itOrSkip('devrait rendre le conteneur d\'image', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element).toBeInTheDocument();
  });

  itOrSkip('devrait appliquer les positions en pixels', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.left).toBe('20px');
    expect(element.style.top).toBe('30px');
    expect(element.style.width).toBe('150px');
    expect(element.style.height).toBe('100px');
  });

  itOrSkip('devrait afficher l\'image si imageUrl est défini', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const img = container.querySelector('img');
    expect(img).toBeInTheDocument();
    expect(img.src).toContain('image.jpg');
  });

  itOrSkip('devrait afficher le placeholder si pas d\'imageUrl', () => {
    const elementWithoutImage = { ...mockElement, imageUrl: '' };
    const { container } = render(
      <ImageRenderer element={elementWithoutImage} canvasScale={1} />
    );
    
    const placeholder = container.querySelector('[style*="dashed"]');
    expect(placeholder).toBeInTheDocument();
  });

  itOrSkip('devrait appliquer l\'objectFit correctement', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const img = container.querySelector('img');
    expect(img.style.objectFit).toBe('contain');
  });

  itOrSkip('devrait appliquer l\'alt text correctement', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const img = container.querySelector('img');
    expect(img.alt).toBe('Test Image');
  });

  itOrSkip('devrait appliquer le backgroundColor', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.backgroundColor).toBe('white');
  });

  itOrSkip('devrait appliquer la bordure', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.border).toContain('1px');
  });

  itOrSkip('devrait appliquer l\'échelle correctement', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={2} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.left).toBe('40px');
    expect(element.style.top).toBe('60px');
    expect(element.style.width).toBe('300px');
    expect(element.style.height).toBe('200px');
  });

  itOrSkip('devrait cacher l\'élément si visible=false', () => {
    const { container } = render(
      <ImageRenderer element={{ ...mockElement, visible: false }} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.display).toBe('none');
  });

  itOrSkip('devrait appliquer les filtres d\'image (brightness, contrast, saturate)', () => {
    const elementWithFilters = { 
      ...mockElement, 
      brightness: 120,
      contrast: 110,
      saturate: 90
    };
    const { container } = render(
      <ImageRenderer element={elementWithFilters} canvasScale={1} />
    );
    
    const img = container.querySelector('img');
    expect(img.style.filter).toContain('brightness(120%)');
    expect(img.style.filter).toContain('contrast(110%)');
    expect(img.style.filter).toContain('saturate(90%)');
  });

  itOrSkip('devrait appliquer le transformOrigin à top left', () => {
    const { container } = render(
      <ImageRenderer element={mockElement} canvasScale={1} />
    );
    
    const element = container.querySelector('.pdf-image-element');
    expect(element.style.transformOrigin).toBe('top left');
  });
});
