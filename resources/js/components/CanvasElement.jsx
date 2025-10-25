import React from 'react';

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
  const elementRef = React.useRef(null);

  const handleClick = React.useCallback((e) => {
    e.stopPropagation();
    onSelect(element.id);
  }, [element.id, onSelect]);

  const handleContextMenu = React.useCallback((e) => {
    e.preventDefault();
    onContextMenu(e, element.id);
  }, [element.id, onContextMenu]);

  const elementStyle = {
    position: 'absolute',
    left: `${element.x * zoom}px`,
    top: `${element.y * zoom}px`,
    width: `${element.width * zoom}px`,
    height: `${element.height * zoom}px`,
    backgroundColor: element.backgroundColor || '#ffffff',
    border: isSelected ? '2px solid #007cba' : element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#000000'}` : '1px solid #e5e7eb',
    borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '0px',
    cursor: 'pointer',
    transform: element.rotation ? `rotate(${element.rotation}deg)` : 'none',
    transformOrigin: 'center center',
    zIndex: isSelected ? 1000 : 1,
    boxSizing: 'border-box',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: `${12 * zoom}px`,
    color: element.color || '#000000',
    fontWeight: element.fontWeight || 'normal',
    textAlign: 'center',
    overflow: 'hidden'
  };

  const renderElementContent = () => {
    switch (element.type) {
      case 'text':
        return element.content || 'Texte';
      case 'rectangle':
        return element.content || '';
      case 'image':
        return element.src ? (
          <img
            src={element.src}
            alt={element.alt || 'Image'}
            style={{
              width: '100%',
              height: '100%',
              objectFit: 'cover',
              borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '0px'
            }}
          />
        ) : '📷 Image';
      case 'barcode':
        return element.content ? `📊 ${element.content}` : '📊 Code-barres';
      case 'qrcode':
        return element.content ? `📱 ${element.content}` : '📱 QR Code';
      default:
        return element.content || `${element.type || 'Élément'} ${element.id}`;
    }
  };

  return (
    <div
      ref={elementRef}
      style={elementStyle}
      onClick={handleClick}
      onContextMenu={handleContextMenu}
      className={`canvas-element ${isSelected ? 'selected' : ''} ${element.type || 'unknown'}`}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      {renderElementContent()}
    </div>
  );
};