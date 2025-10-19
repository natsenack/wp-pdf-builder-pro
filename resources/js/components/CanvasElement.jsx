import { useRef, useCallback, useEffect, useMemo } from 'react';
import { useResize } from '../hooks/useResize';
import { useRotation } from '../hooks/useRotation.js';

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
  dragAndDrop,
  enableRotation = true,
  rotationStep = 15,
  rotationSnap = true,
  guides = { horizontal: [], vertical: [] },
  snapToGuides = true
}) => {
  const elementRef = useRef(null);
  const canvasRectRef = useRef(null);

  // DEBUG: Logger les propriétés des tableaux produits pour comparaison avec PHP
  useEffect(() => {
    if (element && element.type === 'product_table' && element.id) {
      // Logging removed for production
    }
  }, [element?.id, element?.type]); // Utiliser seulement id et type pour éviter les re-renders

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
    canvasHeight,
    guides,
    snapToGuides,
    elementType: element.type
  });

  const rotation = useRotation(
    (newRotation) => {
      onUpdate({ rotation: newRotation });
    },
    rotationStep,
    rotationSnap
  );

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
        headerBg: '#f8fafc',
        headerBorder: '#e2e8f0',
        rowBorder: '#000000',
        rowBg: 'transparent',
        altRowBg: '#fafbfc',
        borderWidth: 2,
        headerTextColor: '#334155',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
        borderRadius: '4px'
      },
      classic: {
        headerBg: '#1e293b',
        headerBorder: '#334155',
        rowBorder: '#334155',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e293b',
        headerFontWeight: '700',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
        borderRadius: '0px'
      },
      striped: {
        headerBg: '#3b82f6',
        headerBorder: '#2563eb',
        rowBorder: '#e2e8f0',
        rowBg: 'transparent',
        altRowBg: '#f8fafc',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 4px rgba(59, 130, 246, 0.2)',
        borderRadius: '6px'
      },
      bordered: {
        headerBg: '#ffffff',
        headerBorder: '#374151',
        rowBorder: '#d1d5db',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 2,
        headerTextColor: '#111827',
        rowTextColor: '#111827',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 0 0 1px #e5e7eb',
        borderRadius: '8px'
      },
      minimal: {
        headerBg: '#ffffff',
        headerBorder: '#d1d5db',
        rowBorder: '#f3f4f6',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 0.5,
        headerTextColor: '#6b7280',
        rowTextColor: '#6b7280',
        headerFontWeight: '500',
        headerFontSize: '10px',
        rowFontSize: '9px',
        shadow: 'none',
        borderRadius: '0px'
      },
      modern: {
        headerBg: '#6366f1',
        headerBorder: '#5b21b6',
        rowBorder: '#e9d5ff',
        rowBg: 'transparent',
        altRowBg: '#faf5ff',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#6b21a8',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
        borderRadius: '8px'
      },
      // Nouveaux styles colorés
      slate_gray: {
        headerBg: '#374151',
        headerBorder: '#4b5563',
        rowBorder: '#f3f4f6',
        rowBg: 'transparent',
        altRowBg: '#f9fafb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#374151'
      },
      coral: {
        headerBg: '#f87171',
        headerBorder: '#fca5a5',
        rowBorder: '#fef2f2',
        rowBg: 'transparent',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#dc2626',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
        borderRadius: '6px'
      },
      teal: {
        headerBg: '#14b8a6',
        headerBorder: '#0d9488',
        rowBorder: '#ccfbf1',
        rowBg: 'transparent',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0f766e'
      },
      indigo: {
        headerBg: '#6366f1',
        headerBorder: '#818cf8',
        rowBorder: '#e0e7ff',
        rowBg: 'transparent',
        altRowBg: '#eef2ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#3730a3'
      },
      amber: {
        headerBg: '#f59e0b',
        headerBorder: '#fbbf24',
        rowBorder: '#fef3c7',
        rowBg: 'transparent',
        altRowBg: '#fffbeb',
        borderWidth: 1.5,
        headerTextColor: '#92400e',
        rowTextColor: '#92400e'
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
      
      // Mettre à jour la référence du canvas
      canvasRectRef.current = canvasRect;
      
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom, element.type);
    }
  }, [isSelected, onSelect, element, zoom, resize, dragAndDrop]);

  // Gestionnaire de double-clic pour édition
  const handleDoubleClick = useCallback((e) => {
    e.stopPropagation();

    if (element.type === 'text') {
      const currentText = element.content || element.text || '';
      const newText = prompt('Modifier le texte:', currentText);

      // Annuler si l'utilisateur clique sur "Annuler" ou laisse vide
      if (newText === null) {
        return;
      }

      // Utiliser la même propriété que celle actuellement utilisée par l'élément
      const textProperty = element.content !== undefined ? 'content' : 'text';
      const updates = { [textProperty]: newText };

      onUpdate(updates);
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
  const getElementTypeStyles = (element, zoom, canvasWidth) => {
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
          borderTop: `${element.lineWidth || element.strokeWidth || 1}px solid ${element.lineColor || element.strokeColor || '#6b7280'}`,
          height: `${Math.max(element.lineWidth || element.strokeWidth || 1, 12)}px`, // Hauteur augmentée à 12px minimum pour faciliter le clic
          width: '100%',
          cursor: 'pointer',
          backgroundColor: 'transparent' // S'assurer qu'il n'y a pas de fond qui cache
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
          backgroundColor: element.backgroundColor || '#e5e7eb',
          height: '16px', // Hauteur fixe pour les séparateurs
          cursor: 'pointer',
          borderRadius: '2px'
        };

      case 'line':
        return {
          borderTop: `${element.lineWidth || element.strokeWidth || 1}px solid ${element.lineColor || element.strokeColor || '#6b7280'}`,
          height: `${Math.max(element.lineWidth || element.strokeWidth || 1, 12)}px`, // Hauteur augmentée à 12px minimum pour faciliter le clic
          left: 0, // Les lignes s'étendent toujours sur toute la largeur du canvas
          width: `${canvasWidth}px`,
          cursor: 'pointer',
          backgroundColor: 'transparent' // S'assurer qu'il n'y a pas de fond qui cache
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

  // Calcul du padding pour cohérence avec le PDF
  const elementPadding = element.padding || 0;

  // Styles élément optimisés avec useMemo pour éviter les recalculs inutiles
  const elementStyles = useMemo(() => ({
    position: 'absolute',
    left: (element.x + elementPadding) * zoom,
    top: (element.y + elementPadding) * zoom,
    width: Math.max(1, (element.width - (elementPadding * 2))) * zoom,
    height: Math.max(1, (element.height - (elementPadding * 2))) * zoom,
    cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
    userSelect: 'none',
    '--selection-border-width': '2px',
    '--selection-border-color': '#3b82f6',
    '--selection-border-spacing': '2px',
    '--selection-shadow-opacity': '0.1',
    '--show-resize-handles': isSelected ? 'block' : 'none',
    '--resize-handle-size': `${10 * zoom}px`,
    '--resize-handle-color': '#3b82f6',
    '--resize-handle-border-color': 'white',
    '--resize-handle-offset': `${-5 * zoom}px`,
    '--element-border-width': '2px',
    '--resize-zone-size': '16px',
    '--show-resize-zones': isSelected ? 'auto' : 'none',
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
    transform: `${dragAndDrop.draggedElementId === element.id ? `translate(${dragAndDrop.dragOffset.x * zoom}px, ${dragAndDrop.dragOffset.y * zoom}px) ` : ''}rotate(${element.rotation || 0}deg) scale(${element.scale || 100}%)`,
    filter: `brightness(${element.brightness || 100}%) contrast(${element.contrast || 100}%) saturate(${element.saturate || 100}%)`,
    boxShadow: element.boxShadowColor ?
      `0px ${element.boxShadowSpread || 0}px ${element.boxShadowBlur || 0}px ${element.boxShadowColor}` :
      (element.shadow ? `${element.shadowOffsetX || 2}px ${element.shadowOffsetY || 2}px 4px ${element.shadowColor || '#000000'}40` : 'none'),

    // Styles spécifiques selon le type d'élément
    ...getElementTypeStyles(element, zoom, canvasWidth)
  }), [
    element.x, element.y, element.width, element.height, element.rotation, element.scale,
    element.backgroundColor, element.backgroundOpacity, element.borderWidth, element.borderStyle, element.borderColor, element.borderRadius,
    element.opacity, element.brightness, element.contrast, element.saturate,
    element.boxShadowColor, element.boxShadowSpread, element.boxShadowBlur, element.shadow, element.shadowOffsetX, element.shadowOffsetY, element.shadowColor,
    element.color, element.fontSize, element.fontFamily, element.fontWeight, element.fontStyle, element.textAlign, element.textDecoration, element.lineHeight,
    element.type, elementPadding, zoom, isSelected, dragAndDrop.isDragging, dragAndDrop.draggedElementId, dragAndDrop.dragOffset
  ]);

  return (
    <>
      {/* Élément principal */}
      <div
        ref={elementRef}
        data-element-id={element.id}
        className={`canvas-element ${isSelected ? 'selected' : ''}`}
        style={elementStyles}
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
         element.type === 'dynamic-text' ? (() => {
           // Fonction pour obtenir le contenu selon le template
           const getTemplateContent = (template, customContent) => {
             const templates = {
               'total_only': '{{order_total}} €',
               'order_info': 'Commande {{order_number}} - {{order_date}}',
               'customer_info': '{{customer_name}} - {{customer_email}}',
               'customer_address': '{{customer_name}}\n{{billing_address}}',
               'full_header': 'Facture N° {{order_number}}\nClient: {{customer_name}}\nTotal: {{order_total}} €',
               'invoice_header': 'FACTURE N° {{order_number}}\nDate: {{date}}\nClient: {{customer_name}}\n{{billing_address}}',
               'order_summary': 'Sous-total: {{order_subtotal}} €\nFrais de port: {{order_shipping}} €\nTVA: {{order_tax}} €\nTotal: {{order_total}} €',
               'payment_info': 'Échéance: {{due_date}}\nMontant: {{order_total}} €',
               'payment_terms': 'Conditions de paiement: 30 jours\nÉchéance: {{due_date}}\nMontant dû: {{order_total}} €',
               'shipping_info': 'Adresse de livraison:\n{{shipping_address}}',
               'thank_you': 'Merci pour votre commande !\nNous vous remercions de votre confiance.',
               'legal_notice': 'TVA non applicable - art. 293 B du CGI\nPaiement à 30 jours fin de mois',
               'bank_details': 'Coordonnées bancaires:\nIBAN: FR76 1234 5678 9012 3456 7890 123\nBIC: BNPAFRPP',
               'contact_info': 'Contact: contact@monentreprise.com\nTél: 01 23 45 67 89',
               'order_confirmation': 'CONFIRMATION DE COMMANDE\nCommande {{order_number}} du {{order_date}}\nStatut: Confirmée',
               'delivery_note': 'BON DE LIVRAISON\nCommande {{order_number}}\nDestinataire: {{customer_name}}\n{{shipping_address}}',
               'warranty_info': 'Garantie: 2 ans pièces et main d\'œuvre\nService après-vente: sav@monentreprise.com',
               'return_policy': 'Droit de rétractation: 14 jours\nRetour sous 30 jours pour défauts',
               'signature_line': 'Signature du client:\n\n_______________________________\nDate: {{date}}',
               'invoice_footer': 'Facture générée automatiquement le {{date}}\nConservez cette facture pour vos archives',
               'terms_conditions': 'Conditions générales de vente disponibles sur notre site\nwww.monentreprise.com/conditions',
               'quality_guarantee': 'Tous nos produits sont garantis contre les défauts\nService qualité: qualite@monentreprise.com',
               'eco_friendly': 'Entreprise engagée pour l\'environnement\nEmballages recyclables et biodégradables',
               'follow_up': 'Suivi de commande: {{order_number}}\nContact: suivi@monentreprise.com',
               'custom': customContent || '{{order_total}} €'
             };
             return templates[template] || templates['total_only'];
           };

           const content = getTemplateContent(element.template, element.customContent);
           // Remplacement basique pour l'aperçu canvas
           return content
             .replace(/\{\{order_total\}\}/g, '125.99 €')
             .replace(/\{\{order_number\}\}/g, 'CMD-2025-001')
             .replace(/\{\{customer_name\}\}/g, 'Jean Dupont')
             .replace(/\{\{customer_email\}\}/g, 'jean@example.com')
             .replace(/\{\{date\}\}/g, '17/10/2025')
             .replace(/\{\{order_date\}\}/g, '15/10/2025')
             .replace(/\{\{due_date\}\}/g, '15/11/2025')
             .replace(/\{\{order_subtotal\}\}/g, '100.00 €')
             .replace(/\{\{order_tax\}\}/g, '25.99 €')
             .replace(/\{\{order_shipping\}\}/g, '15.00 €')
             .replace(/\{\{billing_address\}\}/g, '123 Rue de la Paix\n75001 Paris\nFrance')
             .replace(/\{\{shipping_address\}\}/g, '456 Avenue des Champs\n75008 Paris\nFrance');
         })() :
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
         element.type === 'mentions' ? null : // Le contenu sera rendu plus bas dans le même conteneur
         element.type !== 'image' && element.type !== 'rectangle' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'company_info' && element.type !== 'document_type' ? element.type : null}

        {/* Rendu spécial pour les tableaux de produits */}
        {element.type === 'product_table' && (() => {
          // Données des produits (utiliser sampleProducts si disponible, sinon données par défaut)
          const products = element.sampleProducts || [
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
          // Forcer les bordures pour les tableaux de produits (correction du bug d'affichage)
          const showBorders = element.showBorders !== false; // Utiliser la propriété showBorders de l'élément

          // Log de debug pour voir les propriétés utilisées - COMMENTÉ POUR ÉVITER LA BOUCLE INFINIE
          // console.log('CanvasElement product_table debug:', {
          //   element: element,
          //   products: products,
          //   showBorders: showBorders,
          //   tableStyles: tableStyles,
          //   subtotal: subtotal,
          //   shipping: shipping,
          //   tax: tax,
          //   discount: discount,
          //   total: total,
          //   lastVisibleColumn: lastVisibleColumn
          // });
          return (
            <div style={{
              width: '100%',
              height: '100%',
              display: 'flex',
              flexDirection: 'column',
              fontSize: 10 * zoom,
              fontFamily: '"Inter", "Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, sans-serif',
              // Utiliser les bordures du style de tableau si showBorders est activé
              border: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : (element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none'),
              borderRadius: `${tableStyles.borderRadius * zoom}px`,
              overflow: 'hidden',
              // Assurer que le background ne cache pas les bordures
              backgroundColor: element.backgroundColor || 'transparent',
              boxSizing: 'border-box',
              boxShadow: tableStyles.shadow,
              // Améliorer la lisibilité globale
              lineHeight: '1.4',
              color: tableStyles.rowTextColor
            }}>
              {/* En-tête du tableau */}
              {(element.showHeaders !== false) && (
                <div style={{
                  display: 'flex',
                  background: tableStyles.headerBg,
                  borderBottom: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                  fontWeight: tableStyles.headerFontWeight,
                  color: tableStyles.headerTextColor,
                  fontSize: `${tableStyles.headerFontSize * zoom}px`,
                  textTransform: 'uppercase',
                  letterSpacing: '0.025em'
                }}>
                {(element.columns?.image !== false) && (
                  <div key="header-image" style={{
                    flex: '0 0 40px',
                    padding: `${6 * zoom}px ${4 * zoom}px`,
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                    fontSize: `${tableStyles.headerFontSize * zoom * 0.9}px`,
                    opacity: 0.9
                  }}>
                    Img
                  </div>
                )}
                {(element.columns?.name !== false) && (
                  <div key="header-name" style={{
                    flex: 1,
                    padding: `${6 * zoom}px ${8 * zoom}px`,
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                    fontSize: `${tableStyles.headerFontSize * zoom}px`
                  }}>
                    Produit
                  </div>
                )}
                {(element.columns?.sku !== false) && (
                  <div key="header-sku" style={{
                    flex: '0 0 80px',
                    padding: `${6 * zoom}px ${8 * zoom}px`,
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                    fontSize: `${tableStyles.headerFontSize * zoom}px`
                  }}>
                    SKU
                  </div>
                )}
                {(element.columns?.quantity !== false) && (
                  <div key="header-quantity" style={{
                    flex: '0 0 60px',
                    padding: `${6 * zoom}px ${8 * zoom}px`,
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                    fontSize: `${tableStyles.headerFontSize * zoom}px`
                  }}>
                    Qté
                  </div>
                )}
                {(element.columns?.price !== false) && (
                  <div key="header-price" style={{
                    flex: '0 0 80px',
                    padding: `${6 * zoom}px ${8 * zoom}px`,
                    textAlign: 'right',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                    fontSize: `${tableStyles.headerFontSize * zoom}px`
                  }}>
                    Prix
                  </div>
                )}
                {(element.columns?.total !== false) && (
                  <div key="header-total" style={{
                    flex: '0 0 80px',
                    padding: `${6 * zoom}px ${8 * zoom}px`,
                    textAlign: 'right',
                    fontSize: `${tableStyles.headerFontSize * zoom}px`
                  }}>
                    Total
                  </div>
                )}
              </div>
            )}
            
            {/* Lignes de données d'exemple */}
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
              {products.map((product, index) => (
                <div key={`row-${index}`} style={{
                  display: 'flex',
                  borderBottom: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                  backgroundColor: index % 2 === 0
                    ? (element.evenRowBg || tableStyles.rowBg)
                    : (element.oddRowBg || tableStyles.altRowBg),
                  color: index % 2 === 0
                    ? (element.evenRowTextColor || tableStyles.rowTextColor)
                    : (element.oddRowTextColor || tableStyles.rowTextColor),
                  fontSize: `${tableStyles.rowFontSize * zoom}px`,
                  transition: 'background-color 0.15s ease'
                }}>
                  {(element.columns?.image !== false) && (
                    <div style={{
                      flex: '0 0 40px',
                      padding: `${5 * zoom}px ${4 * zoom}px`,
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                      color: tableStyles.rowTextColor,
                      opacity: 0.7,
                      fontSize: `${tableStyles.rowFontSize * zoom * 0.9}px`
                    }}>
                      📷
                    </div>
                  )}
                  {(element.columns?.name !== false) && (
                    <div style={{
                      flex: 1,
                      padding: `${5 * zoom}px ${8 * zoom}px`,
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                      color: tableStyles.rowTextColor,
                      fontWeight: '500',
                      lineHeight: '1.3'
                    }}>
                      {product.name}
                    </div>
                  )}
                  {(element.columns?.sku !== false) && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: `${5 * zoom}px ${8 * zoom}px`,
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                      color: tableStyles.rowTextColor,
                      opacity: 0.8,
                      fontFamily: 'monospace',
                      fontSize: `${tableStyles.rowFontSize * zoom * 0.9}px`
                    }}>
                      {product.sku}
                    </div>
                  )}
                  {(element.columns?.quantity !== false) && (
                    <div style={{
                      flex: '0 0 60px',
                      padding: `${5 * zoom}px ${8 * zoom}px`,
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                      color: tableStyles.rowTextColor,
                      fontWeight: '600'
                    }}>
                      {product.quantity}
                    </div>
                  )}
                  {(element.columns?.price !== false) && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: `${5 * zoom}px ${8 * zoom}px`,
                      textAlign: 'right',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none',
                      color: tableStyles.rowTextColor,
                      fontWeight: '500',
                      fontFamily: '"Inter", system-ui, sans-serif'
                    }}>
                      {product.price.toFixed(2)}€
                    </div>
                  )}
                  {(element.columns?.total !== false) && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: `${5 * zoom}px ${8 * zoom}px`,
                      textAlign: 'right',
                      color: tableStyles.rowTextColor,
                      fontWeight: '600',
                      fontFamily: '"Inter", system-ui, sans-serif'
                    }}>
                      {product.total.toFixed(2)}€
                    </div>
                  )}
                </div>
              ))}
            </div>

            {/* Lignes de totaux */}
            {(element.showSubtotal || element.showShipping || element.showTaxes || element.showDiscount || element.showTotal) && (
              <div style={{ borderTop: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none' }}>
                {element.showSubtotal && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'flex-end',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    fontWeight: 'bold'
                  }}>
                    <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                      <span>Sous-total:</span>
                      <span>{subtotal.toFixed(2)}€</span>
                    </div>
                  </div>
                )}
                {element.showShipping && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'flex-end',
                    padding: `${4 * zoom}px ${6 * zoom}px`
                  }}>
                    <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                      <span>Port:</span>
                      <span>{shipping.toFixed(2)}€</span>
                    </div>
                  </div>
                )}
                {element.showTaxes && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'flex-end',
                    padding: `${4 * zoom}px ${6 * zoom}px`
                  }}>
                    <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                      <span>TVA:</span>
                      <span>{tax.toFixed(2)}€</span>
                    </div>
                  </div>
                )}
                {element.showDiscount && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'flex-end',
                    padding: `${4 * zoom}px ${6 * zoom}px`
                  }}>
                    <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                      <span>Remise:</span>
                      <span>{Math.abs(discount).toFixed(2)}€</span>
                    </div>
                  </div>
                )}
                {element.showTotal && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'flex-end',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    fontWeight: 'bold',
                    background: tableStyles.gradient || tableStyles.headerBg,
                    color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
                    boxShadow: tableStyles.shadow ? `0 2px 4px ${tableStyles.shadow}` : 'none'
                  }}>
                    <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                      <span>TOTAL:</span>
                      <span>{total.toFixed(2)}€</span>
                    </div>
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
                    75001 Paris, France
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

              {/* SIRET */}
              {element.fields?.includes('siret') && (
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
                      SIRET :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333'
                  }}>
                    123 456 789 00012
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Rendu spécial pour les mentions légales */}
        {element.type === 'mentions' && (() => {
          const mentions = [];

          if (element.showEmail) mentions.push('contact@monsite.com');
          if (element.showPhone) mentions.push('01 23 45 67 89');
          if (element.showSiret) mentions.push('SIRET: 123 456 789 00012');
          if (element.showVat) mentions.push('TVA: FR 12 345 678 901');
          if (element.showAddress) mentions.push('123 Rue de la Paix, 75001 Paris');
          if (element.showWebsite) mentions.push('www.monsite.com');
          if (element.showCustomText && element.customText) mentions.push(element.customText);

          const content = mentions.join(element.separator || ' • ');

          return (
            <div style={{
              width: '100%',
              height: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: element.textAlign === 'center' ? 'center' :
                             element.textAlign === 'right' ? 'flex-end' : 'flex-start',
              padding: `${4 * zoom}px`,
              fontSize: `${(element.fontSize || 8) * zoom}px`,
              fontFamily: element.fontFamily || 'Arial, sans-serif',
              fontWeight: element.fontWeight || 'normal',
              color: element.color || '#666666',
              lineHeight: element.lineHeight || 1.2,
              backgroundColor: element.backgroundColor || 'transparent',
              border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * zoom * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
              borderRadius: element.borderRadius ? `${element.borderRadius * zoom}px` : '2px',
              boxSizing: 'border-box',
              wordBreak: 'break-word',
              overflow: 'hidden'
            }}>
              {element.layout === 'vertical' ? (
                <div style={{
                  display: 'flex',
                  flexDirection: 'column',
                  gap: `${2 * zoom}px`,
                  width: '100%',
                  textAlign: element.textAlign || 'center'
                }}>
                  {mentions.map((mention, index) => (
                    <div key={index} style={{ lineHeight: element.lineHeight || 1.2 }}>
                      {mention}
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{
                  textAlign: element.textAlign || 'center',
                  lineHeight: element.lineHeight || 1.2,
                  width: '100%'
                }}>
                  {content}
                </div>
              )}
            </div>
          );
        })()}

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
              {/* Nom de l'entreprise */}
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
                    Ma Société SARL
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
                    123 Rue de l'Entreprise<br />
                    75001 Paris, France
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
                    +33 1 23 45 67 89
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
                    contact@masociete.com
                  </div>
                </div>
              )}

              {/* Site web */}
              {element.fields?.includes('website') && (
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
                      Site web :
                    </div>
                  )}
                  <div style={{
                    color: '#1976d2'
                  }}>
                    www.masociete.com
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

              {/* RCS */}
              {element.fields?.includes('rcs') && (
                <div style={{
                  display: 'flex',
                  flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
                  alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
                  gap: `${4 * zoom}px`,
                  flex: element.layout === 'horizontal' ? '1' : 'none'
                }}>
                  {element.showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'bold',
                      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
                      color: element.color || '#666',
                      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
                      fontSize: `${11 * zoom}px`
                    }}>
                      RCS :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333'
                  }}>
                    Paris B 123 456 789
                  </div>
                </div>
              )}

              {/* SIRET */}
              {element.fields?.includes('siret') && (
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
                      SIRET :
                    </div>
                  )}
                  <div style={{
                    color: element.color || '#333'
                  }}>
                    123 456 789 00012
                  </div>
                </div>
              )}
            </div>
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

        {/* Poignées de redimensionnement - rendues à l'intérieur de l'élément pour un positionnement correct */}
        {isSelected && (
          <>
            {/* Coins - masqués pour les lignes */}
            {element.type !== 'line' && element.type !== 'divider' && (
              <>
                <div
                  key={`resize-handle-nw-${element.id}`}
                  className="resize-handle nw"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                    const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                    resize.handleResizeStart(e, 'nw', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    }, canvasRect, zoom);
                  }}
                  onContextMenu={handleContextMenuEvent}
                />
                <div
                  key={`resize-handle-ne-${element.id}`}
                  className="resize-handle ne"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                    const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                    resize.handleResizeStart(e, 'ne', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    }, canvasRect, zoom);
                  }}
                  onContextMenu={handleContextMenuEvent}
                />
                <div
                  key={`resize-handle-sw-${element.id}`}
                  className="resize-handle sw"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                    const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                    resize.handleResizeStart(e, 'sw', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    }, canvasRect, zoom);
                  }}
                  onContextMenu={handleContextMenuEvent}
                />
              </>
            )}
            <div
              key={`resize-handle-se-${element.id}`}
              className="resize-handle se"
              onMouseDown={(e) => {
                e.stopPropagation();
                const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                resize.handleResizeStart(e, 'se', {
                  x: element.x,
                  y: element.y,
                  width: element.width,
                  height: element.height
                }, canvasRect, zoom);
              }}
              onContextMenu={handleContextMenuEvent}
            />

            {/* Côtés - n et s masqués pour les lignes */}
            {element.type !== 'line' && element.type !== 'divider' && (
              <>
                <div
                  key={`resize-handle-n-${element.id}`}
                  className="resize-handle n"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                    const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                    resize.handleResizeStart(e, 'n', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    }, canvasRect, zoom);
                  }}
                  onContextMenu={handleContextMenuEvent}
                />
                <div
                  key={`resize-handle-s-${element.id}`}
                  className="resize-handle s"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                    const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                    resize.handleResizeStart(e, 's', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    }, canvasRect, zoom);
                  }}
                  onContextMenu={handleContextMenuEvent}
                />
              </>
            )}
            <div
              key={`resize-handle-w-${element.id}`}
              className="resize-handle w"
              onMouseDown={(e) => {
                e.stopPropagation();
                const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                resize.handleResizeStart(e, 'w', {
                  x: element.x,
                  y: element.y,
                  width: element.width,
                  height: element.height
                }, canvasRect, zoom);
              }}
              onContextMenu={handleContextMenuEvent}
            />
            <div
              key={`resize-handle-e-${element.id}`}
              className="resize-handle e"
              onMouseDown={(e) => {
                e.stopPropagation();
                const canvas = elementRef.current?.closest('.canvas-zoom-wrapper');
                const canvasRect = canvas ? canvas.getBoundingClientRect() : null;
                resize.handleResizeStart(e, 'e', {
                  x: element.x,
                  y: element.y,
                  width: element.width,
                  height: element.height
                }, canvasRect, zoom);
              }}
              onContextMenu={handleContextMenuEvent}
            />

            {/* Zones de redimensionnement sur les bords - n et s masqués pour les lignes */}
            {element.type !== 'line' && element.type !== 'divider' && (
              <>
                <div
                  key={`resize-zone-n-${element.id}`}
                  className="resize-zone resize-zone-n"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    resize.handleResizeStart(e, 'n', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    });
                  }}
                />
                <div
                  key={`resize-zone-s-${element.id}`}
                  className="resize-zone resize-zone-s"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    resize.handleResizeStart(e, 's', {
                      x: element.x,
                      y: element.y,
                      width: element.width,
                      height: element.height
                    });
                  }}
                />
              </>
            )}
            <div
              key={`resize-zone-w-${element.id}`}
              className="resize-zone resize-zone-w"
              onMouseDown={(e) => {
                e.stopPropagation();
                resize.handleResizeStart(e, 'w', {
                  x: element.x,
                  y: element.y,
                  width: element.width,
                  height: element.height
                });
              }}
            />
            <div
              key={`resize-zone-e-${element.id}`}
              className="resize-zone resize-zone-e"
              onMouseDown={(e) => {
                e.stopPropagation();
                resize.handleResizeStart(e, 'e', {
                  x: element.x,
                  y: element.y,
                  width: element.width,
                  height: element.height
                });
              }}
            />
          </>
        )}

        {/* Poignée de rotation */}
        {isSelected && enableRotation && (
          <div
            key={`rotation-handle-${element.id}`}
            className="rotation-handle"
            style={{
              position: 'absolute',
              top: `${-20 * zoom}px`,
              left: '50%',
              transform: 'translateX(-50%)',
              width: `${12 * zoom}px`,
              height: `${12 * zoom}px`,
              backgroundColor: '#3b82f6',
              border: `${2 * zoom}px solid white`,
              borderRadius: '50%',
              cursor: 'alias',
              zIndex: 1000,
              boxShadow: '0 2px 4px rgba(0,0,0,0.2)'
            }}
            onMouseDown={(e) => {
              e.stopPropagation();
              rotation.handleRotationStart(e, element);
            }}
            title="Faire pivoter l'élément"
          />
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
    </>
  );
};

