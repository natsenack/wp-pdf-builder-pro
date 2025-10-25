/**
 * TextRenderer - Rendu du texte
 */
export class TextRenderer {
  static render(element, context) {
    if (!context || !element) return;

    const {
      x, y, width, height, text, fontSize, fontFamily, fontWeight,
      color, textAlign, verticalAlign, lineHeight
    } = element;

    if (!text) return;

    // Sauvegarder le contexte
    context.save();

    // Appliquer les transformations
    context.translate(x || 0, y || 0);

    // Configuration du texte
    context.font = `${fontWeight || 'normal'} ${fontSize || 14}px ${fontFamily || 'Arial'}`;
    context.fillStyle = color || '#000000';
    context.textAlign = textAlign || 'left';
    context.textBaseline = verticalAlign === 'middle' ? 'middle' :
                          verticalAlign === 'bottom' ? 'bottom' : 'top';

    // Calculer les dimensions du texte
    const lines = text.split('\n');
    const lineHeightPx = (lineHeight || 1.2) * (fontSize || 14);

    // Position verticale
    let startY = 0;
    if (verticalAlign === 'middle') {
      startY = height / 2 - (lines.length - 1) * lineHeightPx / 2;
    } else if (verticalAlign === 'bottom') {
      startY = height - (lines.length - 1) * lineHeightPx;
    }

    // Position horizontale
    let startX = 0;
    if (textAlign === 'center') {
      startX = width / 2;
    } else if (textAlign === 'right') {
      startX = width;
    }

    // Dessiner chaque ligne
    lines.forEach((line, index) => {
      const yPos = startY + index * lineHeightPx;
      context.fillText(line, startX, yPos);
    });

    // Restaurer le contexte
    context.restore();
  }

  static measureText(element, context) {
    if (!context || !element || !element.text) return { width: 0, height: 0 };

    const { text, fontSize, fontFamily, fontWeight, lineHeight } = element;

    // Configuration temporaire du contexte
    const originalFont = context.font;
    context.font = `${fontWeight || 'normal'} ${fontSize || 14}px ${fontFamily || 'Arial'}`;

    const lines = text.split('\n');
    const lineHeightPx = (lineHeight || 1.2) * (fontSize || 14);

    let maxWidth = 0;
    lines.forEach(line => {
      const metrics = context.measureText(line);
      maxWidth = Math.max(maxWidth, metrics.width);
    });

    const height = lines.length * lineHeightPx;

    // Restaurer la police originale
    context.font = originalFont;

    return { width: maxWidth, height };
  }
}

// Composant React pour les tests
import React from 'react';

export const TextRendererComponent = ({ element, canvasScale = 1 }) => {
  const containerStyle = {
    position: 'absolute',
    left: `${(element.x || 0) * canvasScale}px`,
    top: `${(element.y || 0) * canvasScale}px`,
    width: `${(element.width || 100) * canvasScale}px`,
    height: `${(element.height || 100) * canvasScale}px`,
    position: 'relative',
    display: element.visible !== false ? 'flex' : 'none',
    alignItems: element.verticalAlign === 'middle' ? 'center' :
                element.verticalAlign === 'bottom' ? 'flex-end' : 'flex-start',
    justifyContent: element.textAlign === 'center' ? 'center' :
                   element.textAlign === 'right' ? 'flex-end' : 'flex-start',
    fontSize: `${(element.fontSize || 14) * canvasScale}px`,
    fontFamily: element.fontFamily || 'Arial',
    fontWeight: element.fontWeight || 'normal',
    color: element.color || '#000000',
    backgroundColor: element.backgroundColor || 'transparent',
    border: element.borderWidth && element.borderWidth > 0 ?
      `${element.borderWidth * canvasScale}px solid ${element.borderColor || '#000000'}` : 'none',
    borderRadius: element.borderRadius ? `${element.borderRadius * canvasScale}px` : '0px',
    opacity: element.opacity !== undefined ? element.opacity / 100 : 1,
    transform: element.rotation || element.scale ?
      `rotate(${element.rotation || 0}deg) scale(${element.scale || 1})` : 'none',
    transformOrigin: 'top left',
    lineHeight: element.lineHeight || 1.2,
    whiteSpace: 'pre-line',
    wordWrap: 'break-word',
    overflow: 'hidden',
    boxShadow: element.shadow ? '2px 2px 4px rgba(0,0,0,0.3)' : 'none'
  };

  return (
    <div
      className="pdf-text-element"
      style={containerStyle}
      data-testid="text-element"
    >
      {element.text || element.content || 'Sample Text'}
    </div>
  );
};