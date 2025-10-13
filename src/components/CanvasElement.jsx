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

  // Fonction helper pour déterminer si un élément est spécial
  const isSpecialElement = (type) => {
    return [
      'product_table', 'customer_info', 'company_logo', 'company_info',
      'order_number', 'document_type', 'progress-bar'
    ].includes(type);
  };

  // Fonction helper pour gérer les styles de bordure des éléments spéciaux
  const getSpecialElementBorderStyle = (element) => {
    // Pour les éléments spéciaux, forcer toujours un fond transparent
    // indépendamment des propriétés de l'élément
    return {
      backgroundColor: 'transparent',
      // Utiliser box-sizing pour que les bordures soient incluses dans les dimensions
      boxSizing: 'border-box',
      // Appliquer les bordures si elles sont définies
      ...(element.borderWidth && element.borderWidth > 0 ? {
        border: `${element.borderWidth * zoom}px ${element.borderStyle || 'solid'} ${element.borderColor || '#e5e7eb'}`
      } : {})
    };
  };

  // Fonction helper pour obtenir les styles de tableau selon le style choisi
  const getTableStyles = (tableStyle = 'default') => {
    const baseStyles = {
      default: {
        headerBg: '#f5f5f5',
        headerBorder: '#ddd',
        rowBorder: '#eee',
        altRowBg: '#fafafa',
        borderWidth: 1
      },
      classic: {
        headerBg: '#ffffff',
        headerBorder: '#000000',
        rowBorder: '#000000',
        altRowBg: '#ffffff',
        borderWidth: 1
      },
      striped: {
        headerBg: '#f8f9fa',
        headerBorder: '#dee2e6',
        rowBorder: '#dee2e6',
        altRowBg: '#e9ecef',
        borderWidth: 1
      },
      bordered: {
        headerBg: '#ffffff',
        headerBorder: '#dee2e6',
        rowBorder: '#dee2e6',
        altRowBg: '#ffffff',
        borderWidth: 2
      },
      minimal: {
        headerBg: '#ffffff',
        headerBorder: '#f1f1f1',
        rowBorder: '#f8f8f8',
        altRowBg: '#ffffff',
        borderWidth: 0.5
      },
      modern: {
        headerBg: '#007bff',
        headerBorder: '#007bff',
        rowBorder: '#e3f2fd',
        altRowBg: '#f8f9ff',
        borderWidth: 1
      }
    };
    return baseStyles[tableStyle] || baseStyles.default;
  };

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
      const newText = prompt('Modifier le texte:', element.content || element.text || '');
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

  // Fonction helper pour obtenir les styles spécifiques au type d'élément
  const getElementTypeStyles = (element, zoom) => {
    switch (element.type) {
      case 'text':
        return {
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
        };

      case 'rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius}px` : '0'
        };

      case 'image':
        if (element.src || element.imageUrl) {
          return {
            backgroundImage: `url(${element.src || element.imageUrl})`,
            backgroundSize: element.objectFit || element.fit || 'cover',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat'
          };
        }
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: '#9ca3af',
          fontSize: 12 * zoom
        };

      case 'line':
        return {
          borderTop: `${element.lineWidth || 1}px solid ${element.lineColor || '#6b7280'}`,
          height: '0px',
          width: '100%'
        };

      case 'layout-header':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 14 * zoom,
          fontWeight: 'bold',
          color: element.color || '#64748b'
        };

      case 'layout-footer':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };

      case 'layout-sidebar':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };

      case 'layout-section':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };

      case 'layout-container':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#94a3b8'
        };

      case 'shape-rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '0'
        };

      case 'shape-circle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: '50%'
        };

      case 'shape-line':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          height: '100%'
        };

      case 'shape-arrow':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(0% 50%, 70% 0%, 70% 40%, 100% 40%, 100% 60%, 70% 60%, 70% 100%)'
        };

      case 'shape-triangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)'
        };

      case 'shape-star':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
        };

      case 'divider':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          height: '1px'
        };

      // Styles par défaut pour les autres types
      default:
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#333333'
        };
    }
  };

  return (
    <>
      {/* Élément principal */}
      <div
        ref={elementRef}
        data-element-id={element.id}
        className={`canvas-element ${isSelected ? 'selected' : ''}`}
        style={{
          position: 'absolute',
          left: element.x * zoom,
          top: element.y * zoom,
          width: element.width * zoom,
          height: element.height * zoom,
          cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
          userSelect: 'none',
          // Pour les éléments spéciaux, utiliser une gestion différente des bordures
          ...(isSpecialElement(element.type) ? getSpecialElementBorderStyle(element) : {
            // Styles de base communs à tous les éléments non-spéciaux
            backgroundColor: element.backgroundOpacity && element.backgroundColor && element.backgroundColor !== 'transparent' ? 
              element.backgroundColor + Math.round(element.backgroundOpacity * 255).toString(16).padStart(2, '0') : 
              (element.backgroundColor || 'transparent'),
            border: element.borderWidth ? `${element.borderWidth * zoom}px ${element.borderStyle || 'solid'} ${element.borderColor || 'transparent'}` : 'none',
          }),
          borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '0px',
          opacity: (element.opacity || 100) / 100,
          transform: `rotate(${element.rotation || 0}deg) scale(${element.scale || 100}%)`,
          filter: `brightness(${element.brightness || 100}%) contrast(${element.contrast || 100}%) saturate(${element.saturate || 100}%)`,
          boxShadow: element.boxShadowColor ? 
            `0px ${element.boxShadowSpread || 0}px ${element.boxShadowBlur || 0}px ${element.boxShadowColor}` : 
            (element.shadow ? `${element.shadowOffsetX || 2}px ${element.shadowOffsetY || 2}px 4px ${element.shadowColor || '#000000'}40` : 'none'),

          // Styles spécifiques selon le type d'élément
          ...getElementTypeStyles(element, zoom)
        }}
        onMouseDown={handleMouseDown}
        onDoubleClick={handleDoubleClick}
        onContextMenu={handleContextMenuEvent}
        draggable={false}
      >
        {element.type === 'text' ? (element.content || element.text || 'Texte') : 
         element.type === 'product_table' ? null : // Le contenu sera rendu plus bas pour les tableaux
         element.type === 'image' && !element.src ? '📷 Image' :
         element.type === 'line' ? null :
         element.type === 'layout-header' ? '[H] En-tête' :
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
         element.type === 'product_table' ? null : // Le contenu sera rendu plus bas dans le même conteneur
         element.type === 'customer_info' ? null : // Le contenu sera rendu plus bas dans le même conteneur
         element.type !== 'image' && element.type !== 'rectangle' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'company_info' && element.type !== 'document_type' ? element.type : null}

        {/* Rendu spécial pour les tableaux de produits */}
        {element.type === 'product_table' && (() => {
          // Données des produits (pourrait venir de props ou d'un état global)
          const products = [
            { name: 'Produit A - Description du produit', sku: 'SKU001', quantity: 2, price: 19.99, total: 39.98 },
            { name: 'Produit B - Un autre article', sku: 'SKU002', quantity: 1, price: 29.99, total: 29.99 }
          ];

          // Calcul des totaux dynamiques
          const subtotal = products.reduce((sum, product) => sum + product.total, 0);
          const shipping = element.showShipping ? 5.00 : 0;
          const tax = element.showTaxes ? 2.25 : 0;
          const discount = element.showDiscount ? -5.00 : 0;
          const total = subtotal + shipping + tax + discount;

          // Déterminer la dernière colonne visible pour afficher les totaux
          const getLastVisibleColumn = () => {
            const columns = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
            for (let i = columns.length - 1; i >= 0; i--) {
              if (element.columns?.[columns[i]] !== false) {
                return columns[i];
              }
            }
            return 'total'; // fallback
          };
          const lastVisibleColumn = getLastVisibleColumn();
          const tableStyles = getTableStyles(element.tableStyle);
          return (
            <div style={{
              width: '100%',
              height: '100%',
              display: 'flex',
              flexDirection: 'column',
              fontSize: 10 * zoom,
              fontFamily: 'Arial, sans-serif',
              // Utiliser des bordures subtiles qui ne sont pas affectées par les paramètres globaux
              border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
              borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
              overflow: 'hidden',
              // Assurer que le background ne cache pas les bordures
              backgroundColor: element.backgroundColor || 'transparent',
              boxSizing: 'border-box'
            }}>
              {/* En-tête du tableau */}
              {(element.showHeaders !== false) && (
                <div style={{
                  display: 'flex',
                  backgroundColor: tableStyles.headerBg,
                  borderBottom: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`,
                  fontWeight: 'bold',
                  color: element.tableStyle === 'modern' ? '#ffffff' : '#000000'
                }}>
                {(element.columns?.image !== false) && (
                  <div style={{
                    flex: '0 0 40px',
                    padding: `${4 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`
                  }}>
                    Img
                  </div>
                )}
                {(element.columns?.name !== false) && (
                  <div style={{
                    flex: 1,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'left',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`
                  }}>
                    Produit
                  </div>
                )}
                {(element.columns?.sku !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'left',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`
                  }}>
                    SKU
                  </div>
                )}
                {(element.columns?.quantity !== false) && (
                  <div style={{
                    flex: '0 0 60px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`
                  }}>
                    Qté
                  </div>
                )}
                {(element.columns?.price !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}`
                  }}>
                    Prix
                  </div>
                )}
                {(element.columns?.total !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
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
                borderBottom: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
              }}>
                {(element.columns?.image !== false) && (
                  <div style={{
                    flex: '0 0 40px',
                    padding: `${4 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    📷
                  </div>
                )}
                {(element.columns?.name !== false) && (
                  <div style={{
                    flex: 1,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    Produit A - Description du produit
                  </div>
                )}
                {(element.columns?.sku !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    SKU001
                  </div>
                )}
                {(element.columns?.quantity !== false) && (
                  <div style={{
                    flex: '0 0 60px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    2
                  </div>
                )}
                {(element.columns?.price !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    €19.99
                  </div>
                )}
                {(element.columns?.total !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right'
                  }}>
                    €39.98
                  </div>
                )}
              </div>
              
              {/* Ligne 2 */}
              <div style={{
                display: 'flex',
                borderBottom: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`,
                backgroundColor: tableStyles.altRowBg
              }}>
                {(element.columns?.image !== false) && (
                  <div style={{
                    flex: '0 0 40px',
                    padding: `${4 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    📷
                  </div>
                )}
                {(element.columns?.name !== false) && (
                  <div style={{
                    flex: 1,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    Produit B - Un autre article
                  </div>
                )}
                {(element.columns?.sku !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    SKU002
                  </div>
                )}
                {(element.columns?.quantity !== false) && (
                  <div style={{
                    flex: '0 0 60px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'center',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    1
                  </div>
                )}
                {(element.columns?.price !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right',
                    borderRight: `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}`
                  }}>
                    €29.99
                  </div>
                )}
                {(element.columns?.total !== false) && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right'
                  }}>
                    €29.99
                  </div>
                )}
              </div>
            </div>

            {/* Lignes de totaux */}
            {(element.showSubtotal || element.showShipping || element.showTaxes || element.showDiscount || element.showTotal) && (
              <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
                {/* Ligne de séparation */}
                <div style={{
                  display: 'flex',
                  borderTop: element.borderWidth ? '2px solid #ddd' : 'none',
                  marginTop: `${8 * zoom}px`,
                  paddingTop: `${8 * zoom}px`
                }}>
                  <div style={{
                    flex: 1,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right',
                    fontWeight: 'bold',
                    color: element.color || '#666'
                  }}>
                    {/* Colonne vide pour l'alignement */}
                  </div>
                  {(element.columns?.quantity !== false) && (
                    <div style={{
                      flex: '0 0 60px',
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'center'
                    }}>
                      {/* Quantité vide */}
                    </div>
                  )}
                  {(element.columns?.price !== false) && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      fontWeight: 'bold',
                      color: element.color || '#666'
                    }}>
                      {/* Prix vide */}
                    </div>
                  )}
                  {(element.columns?.total !== false) && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      fontWeight: 'bold',
                      color: element.color || '#666'
                    }}>
                      Total
                    </div>
                  )}
                </div>

                {/* Sous-total */}
                {element.showSubtotal && (
                  <div style={{
                    display: 'flex',
                    borderBottom: element.borderWidth ? '1px solid #eee' : 'none',
                    backgroundColor: '#f9f9f9'
                  }}>
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      fontWeight: 'bold'
                    }}>
                      Sous-total
                    </div>
                    {(element.columns?.quantity !== false) && (
                      <div style={{
                        flex: '0 0 60px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'center'
                      }}>
                        {/* Quantité vide */}
                      </div>
                    )}
                    {(element.columns?.price !== false) && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        {/* Prix vide */}
                      </div>
                    )}
                    {(lastVisibleColumn === 'total') && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right',
                        fontWeight: 'bold'
                      }}>
                        €{subtotal.toFixed(2)}
                      </div>
                    )}
                  </div>
                )}

                {/* Frais de port */}
                {element.showShipping && (
                  <div style={{
                    display: 'flex',
                    borderBottom: element.borderWidth ? '1px solid #eee' : 'none'
                  }}>
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right'
                    }}>
                      Frais de port
                    </div>
                    {(element.columns?.quantity !== false) && (
                      <div style={{
                        flex: '0 0 60px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'center'
                      }}>
                        {/* Quantité vide */}
                      </div>
                    )}
                    {(element.columns?.price !== false) && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        {/* Prix vide */}
                      </div>
                    )}
                    {(lastVisibleColumn === 'total') && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        €{shipping.toFixed(2)}
                      </div>
                    )}
                  </div>
                )}

                {/* Taxes */}
                {element.showTaxes && (
                  <div style={{
                    display: 'flex',
                    borderBottom: element.borderWidth ? '1px solid #eee' : 'none'
                  }}>
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right'
                    }}>
                      Taxes (TVA 20%)
                    </div>
                    {(element.columns?.quantity !== false) && (
                      <div style={{
                        flex: '0 0 60px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'center'
                      }}>
                        {/* Quantité vide */}
                      </div>
                    )}
                    {(element.columns?.price !== false) && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        {/* Prix vide */}
                      </div>
                    )}
                    {(lastVisibleColumn === 'total') && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        €{tax.toFixed(2)}
                      </div>
                    )}
                  </div>
                )}

                {/* Remise */}
                {element.showDiscount && (
                  <div style={{
                    display: 'flex',
                    borderBottom: element.borderWidth ? '1px solid #eee' : 'none'
                  }}>
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      color: '#d32f2f'
                    }}>
                      Remise
                    </div>
                    {(element.columns?.quantity !== false) && (
                      <div style={{
                        flex: '0 0 60px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'center'
                      }}>
                        {/* Quantité vide */}
                      </div>
                    )}
                    {(element.columns?.price !== false) && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        {/* Prix vide */}
                      </div>
                    )}
                    {(lastVisibleColumn === 'total') && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right',
                        color: '#d32f2f'
                      }}>
                        €{discount.toFixed(2)}
                      </div>
                    )}
                  </div>
                )}

                {/* Total général */}
                {element.showTotal && (
                  <div style={{
                    display: 'flex',
                    borderTop: element.borderWidth ? '2px solid #333' : 'none',
                    backgroundColor: '#f5f5f5',
                    marginTop: `${4 * zoom}px`,
                    paddingTop: `${4 * zoom}px`
                  }}>
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      fontWeight: 'bold',
                      fontSize: `${12 * zoom}px`
                    }}>
                      TOTAL TTC
                    </div>
                    {(element.columns?.quantity !== false) && (
                      <div style={{
                        flex: '0 0 60px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'center'
                      }}>
                        {/* Quantité vide */}
                      </div>
                    )}
                    {(element.columns?.price !== false) && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right'
                      }}>
                        {/* Prix vide */}
                      </div>
                    )}
                    {(lastVisibleColumn === 'total') && (
                      <div style={{
                        flex: '0 0 80px',
                        padding: `${4 * zoom}px ${6 * zoom}px`,
                        textAlign: 'right',
                        fontWeight: 'bold',
                        fontSize: `${12 * zoom}px`,
                        color: '#1976d2'
                      }}>
                        €{total.toFixed(2)}
                      </div>
                    )}
                  </div>
                )}
              </div>
            )}
          </div>
        );
        })()}

        {/* Rendu spécial pour les informations client */}
        {element.type === 'customer_info' && (
          <div style={{
            width: '100%',
            height: '100%',
            padding: `${8 * zoom}px`,
            fontSize: `${(element.fontSize || 12) * zoom}px`,
            fontFamily: element.fontFamily || 'Arial, sans-serif',
            fontWeight: element.fontWeight || 'normal',
            fontStyle: element.fontStyle || 'normal',
            textDecoration: element.textDecoration || 'none',
            color: element.color || '#333',
            backgroundColor: element.backgroundColor || 'transparent',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
            boxSizing: 'border-box'
          }}>
            <div style={{
              display: 'flex',
              flexDirection: element.layout === 'horizontal' ? 'row' : 'column',
              gap: `${element.spacing * zoom || 8 * zoom}px`,
              height: '100%'
            }}>
              {/* Nom */}
              {element.fields?.includes('name') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      Nom :
                    </div>
                  )}
                  <div style={{
                    fontWeight: 'bold',
                    color: element.color || '#333'
                  }}>
                    Jean Dupont
                  </div>
                </div>
              )}

              {/* Email */}
              {element.fields?.includes('email') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      Email :
                    </div>
                  )}
                  <div style={{
                    color: '#1976d2'
                  }}>
                    jean.dupont@email.com
                  </div>
                </div>
              )}

              {/* Téléphone */}
              {element.fields?.includes('phone') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      Téléphone :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333'
                  }}>
                    +33 6 12 34 56 78
                  </div>
                </div>
              )}

              {/* Adresse */}
              {element.fields?.includes('address') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      Adresse :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333',
                    lineHeight: '1.4'
                  }}>
                    123 Rue de la Paix<br />
                    75001 Paris<br />
                    France
                  </div>
                </div>
              )}

              {/* Société */}
              {element.fields?.includes('company') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      Société :
                    </div>
                  )}
                  <div style={{
                    fontWeight: 'bold',
                    color: element.color || '#333'
                  }}>
                    ABC Company SARL
                  </div>
                </div>
              )}

              {/* TVA */}
              {element.fields?.includes('vat') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      N° TVA :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333'
                  }}>
                    FR 12 345 678 901
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Rendu spécial pour le logo entreprise */}
        {element.type === 'company_logo' && (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: element.alignment === 'center' ? 'center' : element.alignment === 'right' ? 'flex-end' : 'flex-start',
            padding: '8px',
            backgroundColor: element.backgroundColor || 'transparent',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
            boxSizing: 'border-box'
          }}>
            {element.imageUrl ? (
              <img
                src={element.imageUrl}
                alt="Logo entreprise"
                style={{
                  width: `${element.width || 150}px`,
                  height: `${element.height || 80}px`,
                  objectFit: element.fit || 'contain',
                  borderRadius: element.borderRadius || 0,
                  border: element.borderWidth ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || 'transparent'}` : (element.showBorder ? '1px solid transparent' : 'none')
                }}
              />
            ) : (
              <div style={{
                width: `${element.width || 150}px`,
                height: `${element.height || 80}px`,
                backgroundColor: '#f5f5f5',
                border: element.borderWidth ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || 'transparent'}` : (element.showBorder ? '1px solid transparent' : 'none'),
                borderRadius: element.borderRadius || '4px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#999',
                fontSize: `${12 * zoom}px`
              }}>
                🏢 Logo
              </div>
            )}
          </div>
        )}

        {/* Rendu spécial pour les informations entreprise */}
        {element.type === 'company_info' && (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            padding: `${8 * zoom}px`,
            fontSize: `${(element.fontSize || 12) * zoom}px`,
            fontFamily: element.fontFamily || 'Arial',
            fontWeight: element.fontWeight || 'normal',
            textAlign: element.textAlign || 'left',
            color: element.color || '#333',
            lineHeight: '1.4',
            backgroundColor: element.backgroundColor || 'transparent',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
            boxSizing: 'border-box'
          }}>
            {/* Nom de l'entreprise */}
            {element.fields?.includes('name') && (
              <div style={{ fontWeight: 'bold', marginBottom: `${2 * zoom}px` }}>
                Ma Société SARL
              </div>
            )}

            {/* Adresse */}
            {element.fields?.includes('address') && (
              <div style={{ marginBottom: `${2 * zoom}px` }}>
                123 Rue de l'Entreprise<br />
                75001 Paris<br />
                France
              </div>
            )}

            {/* Téléphone */}
            {element.fields?.includes('phone') && (
              <div style={{ marginBottom: `${2 * zoom}px` }}>
                Tél: +33 1 23 45 67 89
              </div>
            )}

            {/* Email */}
            {element.fields?.includes('email') && (
              <div style={{ marginBottom: `${2 * zoom}px` }}>
                contact@masociete.com
              </div>
            )}

            {/* Site web */}
            {element.fields?.includes('website') && (
              <div style={{ marginBottom: `${2 * zoom}px` }}>
                www.masociete.com
              </div>
            )}

            {/* TVA */}
            {element.fields?.includes('vat') && (
              <div>
                TVA: FR 12 345 678 901
              </div>
            )}
          </div>
        )}

        {/* Rendu spécial pour le numéro de commande */}
        {element.type === 'order_number' && (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
            padding: `${8 * zoom}px`,
            fontSize: `${(element.fontSize || 14) * zoom}px`,
            fontFamily: element.fontFamily || 'Arial',
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#333333',
            textAlign: element.textAlign || 'right',
            backgroundColor: element.backgroundColor || 'transparent',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
            boxSizing: 'border-box'
          }}>
            {element.showLabel && (
              <div style={{
                fontSize: `${12 * zoom}px`,
                fontWeight: 'normal',
                color: element.color || '#666',
                marginBottom: `${4 * zoom}px`
              }}>
                {element.labelText || 'N° de commande:'}
              </div>
            )}
            <div>
              {(() => {
                // Utiliser le format défini ou une valeur par défaut
                const format = element.format || 'Commande #{order_number} - {order_date}';

                // Données de test pour l'aperçu (seront remplacées par les vraies données lors de la génération)
                const testData = {
                  order_number: '12345',
                  order_date: '15/10/2025'
                };

                // Remplacer les variables dans le format
                return format
                  .replace(/{order_number}/g, testData.order_number)
                  .replace(/{order_date}/g, testData.order_date);
              })()}
            </div>
          </div>
        )}

        {/* Rendu spécial pour le type de document */}
        {element.type === 'document_type' && (
          <div style={{
            display: 'inline-block',
            padding: `${8 * zoom}px`,
            fontSize: `${(element.fontSize || 18) * zoom}px`,
            fontFamily: element.fontFamily || 'Arial',
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#1e293b',
            textAlign: element.textAlign || 'center',
            backgroundColor: element.backgroundColor || 'transparent',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '4px',
            whiteSpace: 'nowrap',
            boxSizing: 'border-box'
          }}>
            {element.documentType === 'invoice' ? 'FACTURE' :
             element.documentType === 'quote' ? 'DEVIS' :
             element.documentType === 'receipt' ? 'REÇU' :
             element.documentType === 'order' ? 'COMMANDE' :
             element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT'}
          </div>
        )}
      </div>



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
            transition: 'width 0.3s ease',
            // Bordures subtiles pour les éléments spéciaux
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            boxSizing: 'border-box'
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
              left: element.x * zoom - 6,
              top: element.y * zoom - 6,
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
            onContextMenu={handleContextMenuEvent}
          />
          <div
            className="resize-handle ne"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: element.y * zoom - 6,
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
            onContextMenu={handleContextMenuEvent}
          />
          <div
            className="resize-handle sw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 6,
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
            onContextMenu={handleContextMenuEvent}
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
            onContextMenu={handleContextMenuEvent}
          />

          {/* Côtés */}
          <div
            className="resize-handle n"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: element.y * zoom - 6,
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
            onContextMenu={handleContextMenuEvent}
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
            onContextMenu={handleContextMenuEvent}
          />
          <div
            className="resize-handle w"
            style={{
              position: 'absolute',
              left: element.x * zoom - 6,
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
            onContextMenu={handleContextMenuEvent}
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
            onContextMenu={handleContextMenuEvent}
          />

          {/* Zones de redimensionnement sur les bords */}
          <div
            className="resize-zone resize-zone-n"
            onMouseDown={(e) => resize.handleResizeStart(e, 'n', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-zone resize-zone-s"
            onMouseDown={(e) => resize.handleResizeStart(e, 's', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-zone resize-zone-w"
            onMouseDown={(e) => resize.handleResizeStart(e, 'w', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-zone resize-zone-e"
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
