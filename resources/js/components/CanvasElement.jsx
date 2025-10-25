import React, { useRef, useEffect, useCallback } from 'react';

export const CanvasElement = ({
  element,
  isSelected,
  zoom = 1,
  snapToGrid = false,
  gridSize = 10,
  canvasWidth = 595,
  canvasHeight = 842,
  onSelect = () => {},
  onUpdate = () => {},
  onRemove = () => {},
  onContextMenu = () => {},
  dragAndDrop = {}
}) => {
  const containerRef = useRef(null);
  const canvasRef = useRef(null);

  const handleClick = useCallback((e) => {
    e.stopPropagation();
    onSelect(element.id);
  }, [element.id, onSelect]);

  const handleContextMenu = useCallback((e) => {
    e.preventDefault();
    onContextMenu(e, element.id);
  }, [element.id, onContextMenu]);

  // Fonction de rendu Canvas
  const renderToCanvas = useCallback(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Effacer le canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Appliquer le zoom aux dimensions du canvas
    const scaledWidth = element.width * zoom;
    const scaledHeight = element.height * zoom;

    // Ajuster la taille du canvas
    canvas.width = scaledWidth;
    canvas.height = scaledHeight;

    // Sauvegarder le contexte
    ctx.save();

    // Appliquer les transformations
    if (element.rotation) {
      ctx.translate(scaledWidth / 2, scaledHeight / 2);
      ctx.rotate((element.rotation * Math.PI) / 180);
      ctx.translate(-scaledWidth / 2, -scaledHeight / 2);
    }

    // Rendre selon le type d'élément
    renderElementContent(ctx, element, scaledWidth, scaledHeight);

    // Bordure de sélection
    if (isSelected) {
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2;
      ctx.strokeRect(1, 1, scaledWidth - 2, scaledHeight - 2);
    }

    // Restaurer le contexte
    ctx.restore();
  }, [element, zoom, isSelected]);

  // Fonction de rendu du contenu selon le type
  const renderElementContent = (ctx, element, width, height) => {
    const {
      type,
      content = '',
      backgroundColor = '#ffffff',
      borderColor = '#000000',
      borderWidth = 1,
      borderRadius = 0,
      color = '#000000',
      fontSize = 12,
      fontFamily = 'Arial',
      fontWeight = 'normal',
      textAlign = 'center',
      src,
      alt = 'Image'
    } = element;

    switch (type) {
      case 'text':
        renderTextElement(ctx, content, width, height, {
          fontSize: fontSize * zoom,
          fontFamily,
          color,
          textAlign,
          fontWeight
        });
        break;

      case 'rectangle':
        renderRectangleElement(ctx, width, height, {
          backgroundColor,
          borderColor,
          borderWidth: borderWidth * zoom,
          borderRadius: borderRadius * zoom
        });
        break;

      case 'image':
        renderImageElement(ctx, width, height, src, alt);
        break;

      case 'barcode':
        renderBarcodeElement(ctx, content, width, height);
        break;

      case 'qrcode':
        renderQRCodeElement(ctx, content, width, height);
        break;

      default:
        renderDefaultElement(ctx, type, content, width, height, {
          fontSize: fontSize * zoom,
          color
        });
    }
  };

  // Rendu texte
  const renderTextElement = (ctx, text, width, height, style) => {
    if (!text) return;

    ctx.fillStyle = style.color;
    ctx.font = `${style.fontWeight} ${style.fontSize}px ${style.fontFamily}`;
    ctx.textAlign = style.textAlign;
    ctx.textBaseline = 'middle';

    const x = style.textAlign === 'center' ? width / 2 :
              style.textAlign === 'right' ? width - 5 : 5;
    const y = height / 2;

    ctx.fillText(text, x, y);
  };

  // Rendu rectangle
  const renderRectangleElement = (ctx, width, height, style) => {
    // Fond
    if (style.backgroundColor && style.backgroundColor !== 'transparent') {
      ctx.fillStyle = style.backgroundColor;
      if (style.borderRadius > 0) {
        roundRect(ctx, 0, 0, width, height, style.borderRadius);
        ctx.fill();
      } else {
        ctx.fillRect(0, 0, width, height);
      }
    }

    // Bordure
    if (style.borderWidth > 0) {
      ctx.strokeStyle = style.borderColor;
      ctx.lineWidth = style.borderWidth;
      if (style.borderRadius > 0) {
        roundRect(ctx, 0, 0, width, height, style.borderRadius);
        ctx.stroke();
      } else {
        ctx.strokeRect(0, 0, width, height);
      }
    }
  };

  // Rendu image
  const renderImageElement = (ctx, width, height, src, alt) => {
    if (src) {
      const img = new Image();
      img.onload = () => {
        ctx.drawImage(img, 0, 0, width, height);
        renderToCanvas(); // Re-rendre après chargement de l'image
      };
      img.src = src;
    } else {
      // Placeholder
      ctx.fillStyle = '#f0f0f0';
      ctx.fillRect(0, 0, width, height);
      ctx.fillStyle = '#666666';
      ctx.font = `${12 * zoom}px Arial`;
      ctx.textAlign = 'center';
      ctx.fillText(alt || '📷 Image', width / 2, height / 2);
    }
  };

  // Rendu code-barres
  const renderBarcodeElement = (ctx, content, width, height) => {
    ctx.fillStyle = '#000000';
    ctx.font = `${10 * zoom}px monospace`;
    ctx.textAlign = 'center';
    ctx.fillText(content ? `📊 ${content}` : '📊 Code-barres', width / 2, height / 2);
  };

  // Rendu QR code
  const renderQRCodeElement = (ctx, content, width, height) => {
    ctx.fillStyle = '#000000';
    ctx.font = `${10 * zoom}px Arial`;
    ctx.textAlign = 'center';
    ctx.fillText(content ? `📱 ${content}` : '📱 QR Code', width / 2, height / 2);
  };

  // Rendu par défaut
  const renderDefaultElement = (ctx, type, content, width, height, style) => {
    ctx.fillStyle = style.color;
    ctx.font = `${style.fontSize}px Arial`;
    ctx.textAlign = 'center';
    ctx.fillText(content || `${type || 'Élément'}`, width / 2, height / 2);
  };

  // Fonction utilitaire pour rectangle arrondi
  const roundRect = (ctx, x, y, width, height, radius) => {
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
  };

  // Effet pour rendre le canvas
  useEffect(() => {
    renderToCanvas();
  }, [renderToCanvas]);

  const containerStyle = {
    position: 'absolute',
    left: `${element.x * zoom}px`,
    top: `${element.y * zoom}px`,
    width: `${element.width * zoom}px`,
    height: `${element.height * zoom}px`,
    cursor: 'pointer',
    zIndex: isSelected ? 1000 : 1,
    boxSizing: 'border-box'
  };

  return (
    <div
      ref={containerRef}
      style={containerStyle}
      onClick={handleClick}
      onContextMenu={handleContextMenu}
      className={`canvas-element ${isSelected ? 'selected' : ''} ${element.type || 'unknown'}`}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      <canvas
        ref={canvasRef}
        style={{
          width: '100%',
          height: '100%',
          display: 'block'
        }}
      />
    </div>
  );
};