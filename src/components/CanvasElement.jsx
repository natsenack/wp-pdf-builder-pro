import React, { useRef, useCallback } from 'react';
import { useResize } from '../hooks/useResize';

export const CanvasElement = ({
  element,
  isSelected,
  zoom,
  snapToGrid,
  gridSize,
  canvasWidth,
  canvasHeight,
  onSelect,
  onUpdate,
  onRemove,
  onContextMenu,
  dragAndDrop
}) => {
  const elementRef = useRef(null);

  const resize = useResize({
    onElementResize: (newRect) => {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid,
    gridSize,
    canvasWidth,
    canvasHeight
  });

  // Gestionnaire de clic sur l'élément
  const handleMouseDown = useCallback((e) => {
    e.stopPropagation();

    if (!isSelected) {
      onSelect();
      return;
    }

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;

    const canvasRect = canvas.getBoundingClientRect();
    const elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    const relativeRect = {
      left: (elementRect.left - canvasRect.left) / zoom,
      top: (elementRect.top - canvasRect.top) / zoom,
      width: elementRect.width / zoom,
      height: elementRect.height / zoom
    };

    // Vérifier si on clique sur une poignée de redimensionnement
    const clickX = (e.clientX - canvasRect.left) / zoom;
    const clickY = (e.clientY - canvasRect.top) / zoom;

    const handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom
    const elementLeft = element.x;
    const elementTop = element.y;
    const elementRight = element.x + element.width;
    const elementBottom = element.y + element.height;

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    const handles = [
      { name: 'nw', x: elementLeft, y: elementTop },
      { name: 'ne', x: elementRight, y: elementTop },
      { name: 'sw', x: elementLeft, y: elementBottom },
      { name: 'se', x: elementRight, y: elementBottom },
      { name: 'n', x: elementLeft + element.width / 2, y: elementTop },
      { name: 's', x: elementLeft + element.width / 2, y: elementBottom },
      { name: 'w', x: elementLeft, y: elementTop + element.height / 2 },
      { name: 'e', x: elementRight, y: elementTop + element.height / 2 }
    ];

    const clickedHandle = handles.find(handle =>
      clickX >= handle.x - handleSize/2 && clickX <= handle.x + handleSize/2 &&
      clickY >= handle.y - handleSize/2 && clickY <= handle.y + handleSize/2
    );

    if (clickedHandle) {
      const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      const canvasRect = canvas.getBoundingClientRect();
      resize.handleResizeStart(e, clickedHandle.name, {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    } else {
      // Démarrer le drag avec les coordonnées relatives au canvas
      const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      const canvasRect = canvas.getBoundingClientRect();
      
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    }
  }, [isSelected, onSelect, element, zoom, resize, dragAndDrop]);

  // Gestionnaire de double-clic pour édition
  const handleDoubleClick = useCallback((e) => {
    e.stopPropagation();

    if (element.type === 'text') {
      const newText = prompt('Modifier le texte:', element.text || '');
      if (newText !== null) {
        onUpdate({ text: newText });
      }
    }
  }, [element, onUpdate]);

  // Gestionnaire de clic droit
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();
    e.stopPropagation();
    if (onContextMenu) {
      onContextMenu(e, element.id);
    }
  }, [onContextMenu, element.id]);

  return (
    <>
      {/* Élément principal */}
      <div
        ref={elementRef}
        className={`canvas-element ${isSelected ? 'selected' : ''}`}
        style={{
          position: 'absolute',
          left: element.x * zoom,
          top: element.y * zoom,
          width: element.width * zoom,
          height: element.height * zoom,
          cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
          userSelect: 'none',
          // Styles pour l'élément selon son type
          ...(element.type === 'text' ? {
            fontSize: (element.fontSize || 14) * zoom,
            fontFamily: element.fontFamily || 'Arial',
            color: element.color || '#1e293b',
            fontWeight: element.fontWeight || 'normal',
            fontStyle: element.fontStyle || 'normal',
            textAlign: element.textAlign || 'left',
            textDecoration: element.textDecoration || 'none',
            lineHeight: element.lineHeight || 'normal',
            display: 'flex',
            alignItems: 'center',
            justifyContent: element.textAlign === 'center' ? 'center' : 
                           element.textAlign === 'right' ? 'flex-end' : 'flex-start',
            wordBreak: 'break-word',
            overflow: 'hidden'
          } : element.type === 'rectangle' ? {
            backgroundColor: element.backgroundColor || 'transparent',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#000'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '0'
          } : element.type === 'image' && element.src ? {
            backgroundImage: `url(${element.src})`,
            backgroundSize: element.backgroundSize || 'contain',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat'
          } : element.type === 'line' ? {
            borderTop: `${element.lineWidth || 1}px solid ${element.lineColor || '#6b7280'}`,
            height: '0px',
            width: '100%'
          } : element.type === 'layout-header' ? {
            backgroundColor: element.backgroundColor || '#f8fafc',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 14 * zoom,
            fontWeight: 'bold',
            color: '#64748b'
          } : element.type === 'layout-footer' ? {
            backgroundColor: element.backgroundColor || '#f8fafc',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom,
            color: '#64748b'
          } : element.type === 'layout-sidebar' ? {
            backgroundColor: element.backgroundColor || '#f8fafc',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom,
            color: '#64748b'
          } : element.type === 'layout-section' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e2e8f0'}` : '1px solid #e2e8f0',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom,
            color: '#64748b'
          } : element.type === 'layout-container' ? {
            backgroundColor: element.backgroundColor || 'transparent',
            border: element.border ? `${element.borderWidth || 2}px ${element.borderStyle || 'dashed'} ${element.borderColor || '#cbd5e1'}` : '2px dashed #cbd5e1',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom,
            color: '#94a3b8'
          } : element.type === 'shape-rectangle' ? {
            backgroundColor: element.backgroundColor || '#e5e7eb',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#000'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '0'
          } : element.type === 'shape-circle' ? {
            backgroundColor: element.backgroundColor || '#e5e7eb',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#000'}` : 'none',
            borderRadius: '50%'
          } : element.type === 'shape-line' ? {
            backgroundColor: element.backgroundColor || '#6b7280',
            height: '100%'
          } : element.type === 'shape-arrow' ? {
            backgroundColor: element.backgroundColor || '#374151',
            clipPath: 'polygon(0% 50%, 70% 0%, 70% 40%, 100% 40%, 100% 60%, 70% 60%, 70% 100%)'
          } : element.type === 'shape-triangle' ? {
            backgroundColor: element.backgroundColor || '#e5e7eb',
            clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)'
          } : element.type === 'shape-star' ? {
            backgroundColor: element.backgroundColor || '#fbbf24',
            clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
          } : element.type === 'divider' ? {
            backgroundColor: element.backgroundColor || '#d1d5db',
            height: '1px'
          } : element.type === 'image-upload' ? {
            backgroundColor: element.backgroundColor || '#f3f4f6',
            border: '2px dashed #d1d5db',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'logo' ? {
            backgroundColor: element.backgroundColor || '#f3f4f6',
            border: '1px solid #e5e7eb',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 14 * zoom,
            fontWeight: 'bold'
          } : element.type === 'barcode' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#000000'}` : '1px solid #000000',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 10 * zoom,
            fontFamily: 'monospace'
          } : element.type === 'qrcode' || element.type === 'qrcode-dynamic' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#000000'}` : '1px solid #000000',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 8 * zoom
          } : element.type === 'icon' ? {
            backgroundColor: element.backgroundColor || 'transparent',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 20 * zoom
          } : element.type === 'dynamic-text' ? {
            backgroundColor: element.backgroundColor || '#f8fafc',
            border: '1px solid #e2e8f0',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-start',
            fontSize: 12 * zoom,
            fontFamily: 'monospace',
            color: '#059669',
            padding: '4px'
          } : element.type === 'formula' ? {
            backgroundColor: element.backgroundColor || '#fef3c7',
            border: '1px solid #f59e0b',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-start',
            fontSize: 12 * zoom,
            fontFamily: 'monospace',
            color: '#d97706',
            padding: '4px'
          } : element.type === 'conditional-text' ? {
            backgroundColor: element.backgroundColor || '#ecfdf5',
            border: '1px solid #10b981',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-start',
            fontSize: 12 * zoom,
            fontFamily: 'monospace',
            color: '#059669',
            padding: '4px'
          } : element.type === 'counter' ? {
            backgroundColor: element.backgroundColor || '#f0f9ff',
            border: '1px solid #0ea5e9',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 14 * zoom,
            fontWeight: 'bold',
            color: '#0284c7'
          } : element.type === 'date-dynamic' ? {
            backgroundColor: element.backgroundColor || '#f3f4f6',
            border: '1px solid #d1d5db',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-start',
            fontSize: 12 * zoom,
            fontFamily: 'monospace',
            color: '#374151',
            padding: '4px'
          } : element.type === 'currency' ? {
            backgroundColor: element.backgroundColor || '#f0fdf4',
            border: '1px solid #22c55e',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-end',
            fontSize: 14 * zoom,
            fontWeight: 'bold',
            color: '#16a34a',
            padding: '4px'
          } : element.type === 'table-dynamic' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e5e7eb'}` : '1px solid #e5e7eb',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'gradient-box' ? {
            background: element.backgroundColor || 'linear-gradient(45deg, #667eea 0%, #764ba2 100%)',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '8px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom,
            color: 'white',
            fontWeight: 'bold'
          } : element.type === 'shadow-box' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '8px',
            boxShadow: element.boxShadow || '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'rounded-box' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 1}px solid ${element.borderColor || '#e5e7eb'}` : '1px solid #e5e7eb',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '12px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'border-box' ? {
            backgroundColor: element.backgroundColor || '#ffffff',
            border: element.border ? `${element.borderWidth || 3}px solid ${element.borderColor || '#3b82f6'}` : '3px solid #3b82f6',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '4px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'background-pattern' ? {
            backgroundColor: element.backgroundColor || '#f8fafc',
            backgroundImage: element.backgroundImage || 'repeating-linear-gradient(45deg, #e2e8f0, #e2e8f0 10px, #f1f5f9 10px, #f1f5f9 20px)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          } : element.type === 'watermark' ? {
            backgroundColor: element.backgroundColor || 'transparent',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: (element.fontSize || 48) * zoom,
            color: element.color || '#9ca3af',
            opacity: element.opacity || 0.1,
            fontWeight: 'bold',
            transform: 'rotate(-45deg)',
            pointerEvents: 'none'
          } : element.type === 'progress-bar' ? {
            backgroundColor: element.backgroundColor || '#e5e7eb',
            borderRadius: '10px',
            overflow: 'hidden',
            position: 'relative'
          } : {
            backgroundColor: '#f1f3f4',
            border: '1px solid #ccc',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12 * zoom
          })
        }}
        onMouseDown={handleMouseDown}
        onDoubleClick={handleDoubleClick}
        onContextMenu={handleContextMenuEvent}
        draggable={false}
      >
        {element.type === 'text' ? (element.text || 'Texte') : 
         element.type === 'product_table' ? null : // Le contenu sera rendu plus bas pour les tableaux
         element.type === 'image' && !element.src ? '📷 Image' :
         element.type === 'line' ? null :
         element.type === 'layout-header' ? '📄 En-tête' :
         element.type === 'layout-footer' ? '📄 Pied de Page' :
         element.type === 'layout-sidebar' ? '📄 Barre Latérale' :
         element.type === 'layout-section' ? '📄 Section' :
         element.type === 'layout-container' ? '📦 Conteneur' :
         element.type === 'shape-rectangle' ? '▭' :
         element.type === 'shape-circle' ? '○' :
         element.type === 'shape-line' ? null :
         element.type === 'shape-arrow' ? '→' :
         element.type === 'shape-triangle' ? '△' :
         element.type === 'shape-star' ? '⭐' :
         element.type === 'divider' ? null :
         element.type === 'image-upload' ? '📤 Télécharger' :
         element.type === 'logo' ? '🏷️ Logo' :
         element.type === 'barcode' ? '📊 123456' :
         element.type === 'qrcode' || element.type === 'qrcode-dynamic' ? '📱 QR' :
         element.type === 'icon' ? (element.content || '🎯') :
         element.type === 'dynamic-text' ? (element.content || '{{variable}}') :
         element.type === 'formula' ? (element.content || '{{prix * quantite}}') :
         element.type === 'conditional-text' ? (element.content || '{{condition ? "Oui" : "Non"}}') :
         element.type === 'counter' ? (element.content || '1') :
         element.type === 'date-dynamic' ? (element.content || '{{date|format:Y-m-d}}') :
         element.type === 'currency' ? (element.content || '{{montant|currency:EUR}}') :
         element.type === 'table-dynamic' ? '📊 Tableau' :
         element.type === 'gradient-box' ? '🌈 Dégradé' :
         element.type === 'shadow-box' ? '📦 Ombre' :
         element.type === 'rounded-box' ? '🔄 Arrondi' :
         element.type === 'border-box' ? '🔲 Bordure' :
         element.type === 'background-pattern' ? '🎨 Motif' :
         element.type === 'watermark' ? (element.content || 'CONFIDENTIEL') :
         element.type === 'progress-bar' ? null :
         element.type !== 'image' && element.type !== 'rectangle' ? element.type : null}
      </div>

      {/* Rendu spécial pour les tableaux de produits */}
      {element.type === 'product_table' && (
        <div style={{
          width: '100%',
          height: '100%',
          display: 'flex',
          flexDirection: 'column',
          fontSize: 10,
          fontFamily: 'Arial, sans-serif',
          border: (element.showBorders !== false) ? '1px solid #ddd' : 'none',
          borderRadius: '2px',
          overflow: 'hidden'
        }}>
          {console.log('📊 Rendering product_table:', { element, zoom })}
          {/* En-tête du tableau */}
          {(element.showHeaders !== false) && (
            <div style={{
              display: 'flex',
              backgroundColor: '#f5f5f5',
              borderBottom: '1px solid #ddd',
              fontWeight: 'bold'
            }}>
              {(element.columns?.image !== false) && (
                <div style={{
                  flex: '0 0 40px',
                  padding: '4px',
                  textAlign: 'center',
                  borderRight: '1px solid #ddd'
                }}>
                  Img
                </div>
              )}
              {(element.columns?.name !== false) && (
                <div style={{
                  flex: 1,
                  padding: '4px 6px',
                  textAlign: 'left',
                  borderRight: (element.columns?.sku !== false) || (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #ddd' : 'none'
                }}>
                  Produit
                </div>
              )}
              {(element.columns?.sku !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'left',
                  borderRight: (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #ddd' : 'none'
                }}>
                  SKU
                </div>
              )}
              {(element.columns?.quantity !== false) && (
                <div style={{
                  flex: '0 0 60px',
                  padding: '4px 6px',
                  textAlign: 'center',
                  borderRight: (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #ddd' : 'none'
                }}>
                  Qté
                </div>
              )}
              {(element.columns?.price !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right',
                  borderRight: (element.columns?.total !== false) ? '1px solid #ddd' : 'none'
                }}>
                  Prix
                </div>
              )}
              {(element.columns?.total !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right'
                }}>
                  Total
                </div>
              )}
            </div>
          )}
          
          {/* Lignes de données d'exemple */}
          <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
            {/* Ligne 1 */}
            <div style={{
              display: 'flex',
              borderBottom: '1px solid #eee'
            }}>
              {(element.columns?.image !== false) && (
                <div style={{
                  flex: '0 0 40px',
                  padding: '4px',
                  textAlign: 'center',
                  borderRight: '1px solid #eee'
                }}>
                  📷
                </div>
              )}
              {(element.columns?.name !== false) && (
                <div style={{
                  flex: 1,
                  padding: '4px 6px',
                  borderRight: (element.columns?.sku !== false) || (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  Produit A - Description du produit
                </div>
              )}
              {(element.columns?.sku !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  borderRight: (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  SKU001
                </div>
              )}
              {(element.columns?.quantity !== false) && (
                <div style={{
                  flex: '0 0 60px',
                  padding: '4px 6px',
                  textAlign: 'center',
                  borderRight: (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  2
                </div>
              )}
              {(element.columns?.price !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right',
                  borderRight: (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  €19.99
                </div>
              )}
              {(element.columns?.total !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right'
                }}>
                  €39.98
                </div>
              )}
            </div>
            
            {/* Ligne 2 */}
            <div style={{
              display: 'flex',
              borderBottom: '1px solid #eee',
              backgroundColor: '#fafafa'
            }}>
              {(element.columns?.image !== false) && (
                <div style={{
                  flex: '0 0 40px',
                  padding: '4px',
                  textAlign: 'center',
                  borderRight: '1px solid #eee'
                }}>
                  📷
                </div>
              )}
              {(element.columns?.name !== false) && (
                <div style={{
                  flex: 1,
                  padding: '4px 6px',
                  borderRight: (element.columns?.sku !== false) || (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  Produit B - Un autre article
                </div>
              )}
              {(element.columns?.sku !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  borderRight: (element.columns?.quantity !== false) || (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  SKU002
                </div>
              )}
              {(element.columns?.quantity !== false) && (
                <div style={{
                  flex: '0 0 60px',
                  padding: '4px 6px',
                  textAlign: 'center',
                  borderRight: (element.columns?.price !== false) || (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  1
                </div>
              )}
              {(element.columns?.price !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right',
                  borderRight: (element.columns?.total !== false) ? '1px solid #eee' : 'none'
                }}>
                  €29.99
                </div>
              )}
              {(element.columns?.total !== false) && (
                <div style={{
                  flex: '0 0 80px',
                  padding: '4px 6px',
                  textAlign: 'right'
                }}>
                  €29.99
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Rendu spécial pour la barre de progression */}
      {element.type === 'progress-bar' && (
        <div
          style={{
            position: 'absolute',
            top: 0,
            left: 0,
            height: '100%',
            width: `${element.progressValue || 75}%`,
            backgroundColor: element.progressColor || '#3b82f6',
            borderRadius: '10px',
            transition: 'width 0.3s ease'
          }}
        />
      )}

      {/* Poignées de redimensionnement */}
      {isSelected && (
        <>
          {/* Coins */}
          <div
            className="resize-handle nw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'nw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'nw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle ne"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'ne-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'ne', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle sw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'sw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'sw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle se"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'se-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'se', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Côtés */}
          <div
            className="resize-handle n"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'n-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'n', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle s"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 's-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 's', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle w"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'w-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'w', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle e"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'e-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'e', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
        </>
      )}
    </>
  );
};