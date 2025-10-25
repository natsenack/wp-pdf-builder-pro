/**
 * RectangleRenderer - Rendu des rectangles
 */
export class RectangleRenderer {
  static render(element, context) {
    if (!context || !element) return;

    const { x, y, width, height, backgroundColor, borderColor, borderWidth, borderRadius } = element;

    // Sauvegarder le contexte
    context.save();

    // Appliquer les transformations
    context.translate(x || 0, y || 0);

    // Fond
    if (backgroundColor && backgroundColor !== 'transparent') {
      context.fillStyle = backgroundColor;
      if (borderRadius && borderRadius > 0) {
        this.roundRect(context, 0, 0, width || 100, height || 100, borderRadius);
        context.fill();
      } else {
        context.fillRect(0, 0, width || 100, height || 100);
      }
    }

    // Bordure
    if (borderWidth && borderWidth > 0 && borderColor) {
      context.strokeStyle = borderColor;
      context.lineWidth = borderWidth;
      if (borderRadius && borderRadius > 0) {
        this.roundRect(context, 0, 0, width || 100, height || 100, borderRadius);
        context.stroke();
      } else {
        context.strokeRect(0, 0, width || 100, height || 100);
      }
    }

    // Restaurer le contexte
    context.restore();
  }

  static roundRect(ctx, x, y, width, height, radius) {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
  }
}

// Composant React pour les tests - rendu DOM au lieu de canvas
import React from 'react';

export const RectangleRendererComponent = ({ element, canvasScale = 1 }) => {
  const testElementStyle = {
    position: 'absolute',
    left: `${(element.x || 0) * canvasScale}px`,
    top: `${(element.y || 0) * canvasScale}px`,
    width: `${(element.width || 100) * canvasScale}px`,
    height: `${(element.height || 100) * canvasScale}px`,
    backgroundColor: element.backgroundColor || 'transparent',
    border: element.borderWidth && element.borderWidth > 0 ?
      `${element.borderWidth}px solid ${element.borderColor || '#000000'}` : 'none',
    borderRadius: element.borderRadius ? `${element.borderRadius}px` : '0px',
    opacity: element.opacity !== undefined ? element.opacity / 100 : 1,
    display: element.visible !== false ? 'block' : 'none',
    transform: element.rotation || element.scale ?
      `rotate(${element.rotation || 0}deg) scale(${element.scale || 1})` : 'none',
    transformOrigin: 'top left',
    boxShadow: element.shadow ? '3px 3px 5px rgba(0,0,0,0.3)' : 'none'
  };

  return (
    <div className="pdf-rectangle-element" style={testElementStyle} data-testid="rectangle-element" />
  );
};