/**
 * ImageRenderer - Rendu des images
 */
export class ImageRenderer {
  static render(element, context) {
    if (!context || !element) return;

    const { x, y, width, height, imageUrl, objectFit, borderRadius } = element;

    if (!imageUrl) return;

    // Créer une image
    const img = new Image();
    img.onload = () => {
      context.save();
      context.translate(x || 0, y || 0);

      const drawWidth = width || img.width;
      const drawHeight = height || img.height;

      // Appliquer objectFit
      let drawX = 0, drawY = 0, scaleX = 1, scaleY = 1;

      switch (objectFit) {
        case 'cover':
          const scale = Math.max(drawWidth / img.width, drawHeight / img.height);
          scaleX = scaleY = scale;
          drawX = (drawWidth - img.width * scale) / 2;
          drawY = (drawHeight - img.height * scale) / 2;
          break;
        case 'contain':
          const containScale = Math.min(drawWidth / img.width, drawHeight / img.height);
          scaleX = scaleY = containScale;
          drawX = (drawWidth - img.width * containScale) / 2;
          drawY = (drawHeight - img.height * containScale) / 2;
          break;
        case 'fill':
        default:
          scaleX = drawWidth / img.width;
          scaleY = drawHeight / img.height;
          break;
      }

      // Appliquer les transformations
      context.scale(scaleX, scaleY);

      // Dessiner l'image avec borderRadius si nécessaire
      if (borderRadius && borderRadius > 0) {
        this.roundRect(context, drawX / scaleX, drawY / scaleY, img.width, img.height, borderRadius / Math.min(scaleX, scaleY));
        context.clip();
      }

      context.drawImage(img, drawX / scaleX, drawY / scaleY);

      context.restore();
    };
    img.src = imageUrl;
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

// Composant React pour les tests
import React from 'react';

export const ImageRendererComponent = ({ element, canvasScale = 1 }) => {
  const containerRef = React.useRef(null);
  const [imageLoaded, setImageLoaded] = React.useState(false);

  React.useEffect(() => {
    if (containerRef.current && element.imageUrl) {
      const img = new Image();
      img.onload = () => {
        setImageLoaded(true);
      };
      img.src = element.imageUrl;
    }
  }, [element.imageUrl]);

  const containerStyle = {
    position: 'absolute',
    left: `${(element.x || 0) * canvasScale}px`,
    top: `${(element.y || 0) * canvasScale}px`,
    width: `${(element.width || 100) * canvasScale}px`,
    height: `${(element.height || 100) * canvasScale}px`,
    position: 'relative',
    overflow: 'hidden',
    border: element.borderWidth ? `${element.borderWidth * canvasScale}px solid ${element.borderColor || '#000'}` : 'none',
    borderRadius: element.borderRadius ? `${element.borderRadius * canvasScale}px` : '0',
    backgroundColor: element.backgroundColor || 'transparent',
    display: element.visible !== false ? 'block' : 'none',
    opacity: element.opacity !== undefined ? element.opacity / 100 : 1,
    transform: element.rotation || element.scale ?
      `rotate(${element.rotation || 0}deg) scale(${element.scale || 1})` : 'none',
    transformOrigin: element.transformOrigin || 'top left'
  };

  const imageStyle = {
    width: '100%',
    height: '100%',
    objectFit: element.objectFit || 'fill',
    display: imageLoaded ? 'block' : 'none',
    filter: element.brightness || element.contrast || element.saturate ?
      `brightness(${element.brightness || 100}%) contrast(${element.contrast || 100}%) saturate(${element.saturate || 100}%)` : 'none'
  };

  const placeholderStyle = {
    width: '100%',
    height: '100%',
    backgroundColor: '#f0f0f0',
    border: '2px dashed #ccc',
    display: !imageLoaded && !element.imageUrl ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    color: '#999',
    fontSize: '12px'
  };

  return (
    <div ref={containerRef} className="pdf-image-element" style={containerStyle}>
      {element.imageUrl ? (
        <img src={element.imageUrl} alt={element.alt || ''} style={imageStyle} />
      ) : (
        <div style={placeholderStyle}>
          {element.alt || 'Image'}
        </div>
      )}
    </div>
  );
};