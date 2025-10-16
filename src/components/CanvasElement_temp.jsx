const CanvasElement = React.memo(({
  element,
  isSelected,
  zoom = 1,
  onSelect,
  onUpdate,
  onDelete,
  rotation,
  canvasRef
}) => {
  // Styles élément optimisés avec useMemo
  const elementStyles = useMemo(() => ({
    position: 'absolute',
    left: `${element.x * zoom}px`,
    top: `${element.y * zoom}px`,
    width: `${element.width * zoom}px`,
    height: element.type === 'line' ? `${Math.max(1, element.strokeWidth * zoom)}px` : `${element.height * zoom}px`,
    transform: `rotate(${element.rotation || 0}deg)`,
    zIndex: element.zIndex || 1,
    cursor: isSelected ? 'move' : 'pointer',
    // Styles visuels
    backgroundColor: element.backgroundColor || 'transparent',
    border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
    borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '0px',
    boxShadow: element.shadow ? `0 2px 4px rgba(0,0,0,0.1)` : 'none',
    // Styles de texte
    fontSize: `${(element.fontSize || 12) * zoom}px`,
    fontFamily: element.fontFamily || 'Arial, sans-serif',
    fontWeight: element.fontWeight || 'normal',
    fontStyle: element.fontStyle || 'normal',
    textDecoration: element.textDecoration || 'none',
    color: element.color || '#333',
    textAlign: element.textAlign || 'left',
    // Gestion des débordements
    overflow: 'hidden',
    whiteSpace: element.type === 'text' ? 'pre-wrap' : 'normal',
    wordWrap: 'break-word',
    // Optimisations de performance
    willChange: 'transform',
    backfaceVisibility: 'hidden'
  }), [element.x, element.y, element.width, element.height, element.rotation, element.zIndex, element.backgroundColor, element.borderWidth, element.borderColor, element.borderRadius, element.shadow, element.fontSize, element.fontFamily, element.fontWeight, element.fontStyle, element.textDecoration, element.color, element.textAlign, element.strokeWidth, element.type, zoom, isSelected]);

  // Contenu élément optimisé avec useMemo
  const elementContent = useMemo(() => {
    switch (element.type) {
      case 'text':
        return element.content || element.text || 'Texte';
      case 'product_table':
        return null; // Le contenu sera rendu plus bas pour les tableaux
      case 'image':
        return !element.src ? '📷 Image' : null;
      case 'line':
        return null;
      default:
        return element.content || element.text || `[${element.type}]`;
    }
  }, [element.type, element.content, element.text, element.src]);

  return (
    <div
      style={elementStyles}
    >
      {elementContent}
    </div>
  );
});

export default CanvasElement;
