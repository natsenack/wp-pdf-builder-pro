import React, { useState, useRef, useEffect } from 'react';
import { Toolbar } from './Toolbar';
import PreviewModal from './preview-system/components/PreviewModal';
import { PreviewProvider } from './preview-system/context/PreviewProvider';
import { usePreviewContext } from './preview-system/context/PreviewContext';
import ElementLibrary from './ElementLibrary';
import PropertiesPanel from './PropertiesPanel';
import TemplateHeader from './TemplateHeader';
import { SampleDataProvider } from './preview-system/data/SampleDataProvider';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur principal complet avec éléments et propriétés
 * Phase 2.2.4.1 - Implémentation complète du système d'éléments
 */
const PDFEditorContent = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // Contexte d'aperçu
  const { actions: { openPreview } } = usePreviewContext();

  // État des éléments
  const [elements, setElements] = useState(initialElements);
  const [selectedElementId, setSelectedElementId] = useState(null); // Maintenant c'est l'ID de l'élément

  // Fonction pour obtenir l'élément sélectionné
  const selectedElement = selectedElementId ? elements.find(el => el.id === selectedElementId) : null;

  // État de l'historique
  const [history, setHistory] = useState([initialElements]);
  const [historyIndex, setHistoryIndex] = useState(0);

  // État de l'interface
  const [zoom, setZoom] = useState(1.0);
  const [showGrid, setShowGrid] = useState(true);
  const [snapToGrid, setSnapToGrid] = useState(true);
  const [selectedTool, setSelectedTool] = useState('select');
  const [showElementLibrary, setShowElementLibrary] = useState(true);
  const [showPropertiesPanel, setShowPropertiesPanel] = useState(false);

  // Synchroniser selectedElement avec elements - REMOVED car on utilise maintenant selectedElementId

  // État pour le drag & drop
  const [isDragging, setIsDragging] = useState(false);
  const [dragStart, setDragStart] = useState({ x: 0, y: 0 });
  const [dragElement, setDragElement] = useState(null);

  // État pour le redimensionnement
  const [isResizing, setIsResizing] = useState(false);
  const [resizeHandle, setResizeHandle] = useState(null);
  const [resizeStart, setResizeStart] = useState({ x: 0, y: 0, width: 0, height: 0 });

  // Références
  const canvasRef = useRef(null);

  // Gestionnaire d'outils
  const handleToolSelect = (toolId) => {
    setSelectedTool(toolId);

    // Créer un élément selon l'outil sélectionné
    const elementDefaults = {
      'add-text': { type: 'text', text: 'Nouveau texte', x: 50, y: 50, fontSize: 16, color: '#000000' },
      'add-text-title': { type: 'text', text: 'Titre', x: 50, y: 50, fontSize: 24, fontWeight: 'bold', color: '#000000' },
      'add-text-subtitle': { type: 'text', text: 'Sous-titre', x: 50, y: 50, fontSize: 18, fontWeight: 'normal', color: '#666666' },
      'add-rectangle': { type: 'rectangle', x: 50, y: 50, width: 100, height: 60, backgroundColor: '#ffffff', borderWidth: 1, borderColor: '#000000' },
      'add-circle': { type: 'circle', x: 75, y: 75, radius: 30, backgroundColor: '#ffffff', borderWidth: 1, borderColor: '#000000' },
      'add-line': { type: 'line', x: 50, y: 50, width: 100, height: 2, lineColor: '#000000', lineWidth: 2 },
      'add-arrow': { type: 'line', x: 50, y: 50, width: 100, height: 2, lineColor: '#000000', lineWidth: 2 }, // TODO: Implémenter flèche
      'add-triangle': { type: 'shape-triangle', x: 50, y: 50, width: 60, height: 50, backgroundColor: '#ffffff', borderWidth: 1, borderColor: '#000000' },
      'add-star': { type: 'shape-star', x: 50, y: 50, width: 60, height: 60, backgroundColor: '#ffffff', borderWidth: 1, borderColor: '#000000' },
      'add-divider': { type: 'line', x: 50, y: 50, width: 200, height: 2, lineColor: '#cccccc', lineWidth: 1 },
      'add-image': { type: 'image', x: 50, y: 50, width: 100, height: 100, src: '' },
      'add-dynamic-text': { type: 'dynamic-text', x: 50, y: 50, width: 200, height: 30, template: 'total_only', customContent: '{{order_total}} €', fontSize: 14, color: '#333333' }
    };

    if (elementDefaults[toolId]) {
      handleAddElement(elementDefaults[toolId].type, elementDefaults[toolId]);
    }
  };

  // Gestionnaire de zoom
  const handleZoomChange = (newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  };

  // Gestionnaire de grille
  const handleShowGridChange = (show) => {
    setShowGrid(show);
  };

  const handleSnapToGridChange = (snap) => {
    setSnapToGrid(snap);
  };

  // Gestionnaire d'historique
  const handleUndo = () => {
    if (historyIndex > 0) {
      setHistoryIndex(historyIndex - 1);
      setElements(history[historyIndex - 1]);
    }
  };

  const handleRedo = () => {
    if (historyIndex < history.length - 1) {
      setHistoryIndex(historyIndex + 1);
      setElements(history[historyIndex + 1]);
    }
  };

  // Gestionnaire de sauvegarde
  const handleSave = async () => {
    if (onSave) {
      await onSave(elements);
    }
  };

  const canUndo = historyIndex > 0;
  const canRedo = historyIndex < history.length - 1;

  // Gestionnaire d'aperçu
  const handlePreview = () => {
    openPreview('canvas', null, { elements });
  };

  // Gestionnaire de création d'un nouveau template
  const handleCreateNew = (templateData) => {
    // Pour l'instant, on peut juste afficher un message ou rediriger
    console.log('Création d\'un nouveau template:', templateData);
    // Ici on pourrait rediriger vers une nouvelle page ou ouvrir un nouvel éditeur
    alert(`Nouveau template "${templateData.name}" créé avec succès!\nDimensions: ${templateData.width}x${templateData.height}px (${templateData.orientation})`);
  };

  // Gestionnaire d'ajout d'élément depuis la bibliothèque
  const handleAddElement = (elementType, defaultProperties = {}) => {
    const newElement = {
      id: Date.now(),
      type: elementType,
      x: Math.random() * 400 + 50, // Position aléatoire
      y: Math.random() * 600 + 50,
      ...defaultProperties
    };

    const newElements = [...elements, newElement];
    handleElementsChange(newElements);
    setSelectedElementId(newElement.id);
  };

  // Gestionnaire de sélection d'élément
  const handleElementSelect = (elementId) => {
    setSelectedElementId(elementId);
    if (elementId) {
      setShowPropertiesPanel(true); // Afficher automatiquement le panneau des propriétés
    }
  };

  // Gestionnaire de mise à jour des propriétés d'un élément
  const handleElementUpdate = (elementId, newProperties) => {
    console.log('[DEBUG] handleElementUpdate called:', { elementId, newProperties, template: newProperties.template });
    setElements(prevElements => {
      const newElements = prevElements.map(element =>
        element.id === elementId ? { ...element, ...newProperties } : element
      );
      console.log('[DEBUG] newElements element template:', newElements.find(el => el.id === elementId)?.template);
      // Update history
      const newHistory = history.slice(0, historyIndex + 1);
      newHistory.push(newElements);
      setHistory(newHistory);
      setHistoryIndex(newHistory.length - 1);
      return newElements;
    });
  };

  // Gestionnaire de suppression d'élément
  const handleElementDelete = (elementId) => {
    const newElements = elements.filter(element => element.id !== elementId);
    handleElementsChange(newElements);
    if (selectedElementId === elementId) {
      setSelectedElementId(null);
    }
  };

  // Gestionnaire de sauvegarde des éléments
  const handleElementsChange = (newElements) => {
    console.log('[DEBUG] handleElementsChange called with newElements length:', newElements.length);
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(newElements);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
    setElements(newElements);
    console.log('[DEBUG] setElements called');
  };

  // Gestionnaire de drag over
  const handleDragOver = (event) => {
    event.preventDefault(); // Permettre le drop
    event.dataTransfer.dropEffect = 'copy';
  };

  // Gestionnaire de drop
  const handleDrop = (event) => {
    event.preventDefault();

    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    try {
      const data = JSON.parse(event.dataTransfer.getData('application/json'));

      if (data.type === 'element') {
        // Créer un nouvel élément à la position du drop
        const newElement = {
          id: Date.now(),
          type: data.elementType,
          x: x,
          y: y,
          ...data.defaultProperties
        };

        const newElements = [...elements, newElement];
        handleElementsChange(newElements);
        setSelectedElementId(newElement.id);
      }
    } catch (error) {
      console.error('Erreur lors du drop:', error);
    }
  };

  // Gestionnaire de clic sur le canvas (pour création d'éléments seulement)
  const handleCanvasClick = (event) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    // Si un outil est sélectionné, créer un élément
    if (selectedTool !== 'select') {
      let newElement;

      switch (selectedTool) {
        case 'add-text':
          newElement = {
            id: Date.now(),
            type: 'text',
            text: 'Nouveau texte',
            x: x,
            y: y,
            fontSize: 16,
            color: '#000000',
            fontFamily: 'Arial'
          };
          break;
        case 'add-rectangle':
          newElement = {
            id: Date.now(),
            type: 'rectangle',
            x: x,
            y: y,
            width: 100,
            height: 50,
            backgroundColor: '#ffffff',
            borderColor: '#000000',
            borderWidth: 1
          };
          break;
        case 'add-circle':
          newElement = {
            id: Date.now(),
            type: 'circle',
            x: x,
            y: y,
            radius: 25,
            backgroundColor: '#ffffff',
            borderColor: '#000000',
            borderWidth: 1
          };
          break;
        default:
          return;
      }

      const newElements = [...elements, newElement];
      handleElementsChange(newElements);
      setSelectedElementId(newElement.id);
      setSelectedTool('select'); // Revenir à l'outil de sélection
    }
  };

  // Gestionnaire de pression souris (pour drag & resize)
  const handleCanvasMouseDown = (event) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    // Vérifier d'abord si on clique sur une poignée de redimensionnement
    if (selectedElement) {
      const element = elements.find(el => el.id === selectedElement?.id);
      if (element) {
        const handle = getResizeHandleAtPosition(element, x, y);
        if (handle) {
          // Démarrer le redimensionnement
          setIsResizing(true);
          setResizeHandle(handle);

          // Initialiser les dimensions selon le type d'élément
          let initWidth = element.width || 100;
          let initHeight = element.height || 30;

          if (element.type === 'circle') {
            initWidth = (element.radius || 25) * 2;
            initHeight = (element.radius || 25) * 2;
          }

          setResizeStart({
            x: x,
            y: y,
            width: initWidth,
            height: initHeight,
            elementX: element.x,
            elementY: element.y
          });
          return;
        }
      }
    }

    // Vérifier si on clique sur un élément pour le drag
    const clickedElement = elements.find(element => {
      if (element.type === 'text') {
        const ctx = canvas.getContext('2d');
        const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
        ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
        const metrics = ctx.measureText(element.text || 'Texte');
        return x >= element.x && x <= element.x + metrics.width &&
               y >= element.y - element.fontSize && y <= element.y;
      } else if (element.type === 'rectangle') {
        return x >= element.x && x <= element.x + (element.width || 100) &&
               y >= element.y && y <= element.y + (element.height || 50);
      } else if (element.type === 'circle') {
        const dx = x - element.x;
        const dy = y - element.y;
        return Math.sqrt(dx * dx + dy * dy) <= (element.radius || 25);
      } else if (element.type === 'company_logo' || element.type === 'dynamic-text' ||
                 element.type === 'order_number' || element.type === 'document_type' ||
                 element.type === 'customer_info' || element.type === 'company_info' ||
                 element.type === 'product_table' || element.type === 'mentions') {
        // Pour tous les éléments rectangulaires avec width/height
        return x >= element.x && x <= element.x + (element.width || 100) &&
               y >= element.y && y <= element.y + (element.height || 30);
      } else if (element.type === 'line') {
        // Pour les lignes, zone de tolérance autour de la ligne
        const tolerance = 5;
        return x >= element.x && x <= element.x + (element.width || 20) &&
               Math.abs(y - (element.y + (element.height || 12) / 2)) <= tolerance;
      }
      return false;
    });

    if (clickedElement) {
      setSelectedElementId(clickedElement.id);
      setShowPropertiesPanel(true); // Afficher automatiquement le panneau des propriétés
      // Démarrer le drag
      setIsDragging(true);
      setDragElement(clickedElement);
      setDragStart({ x: x, y: y }); // Position absolue du clic
    } else {
      // Désélectionner si on clique dans le vide
      setSelectedElementId(null);
      setShowPropertiesPanel(false); // Masquer le panneau des propriétés
    }
  };

  // Gestionnaire de mouvement de souris
  const handleCanvasMouseMove = (event) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    if (isResizing && selectedElement && resizeHandle) {
      const element = elements.find(el => el.id === selectedElement?.id);
      if (element) {
        let newWidth = resizeStart.width;
        let newHeight = resizeStart.height;
        let newX = element.x;
        let newY = element.y;
        let newRadius = element.radius;

        const deltaX = x - resizeStart.x;
        const deltaY = y - resizeStart.y;

        // Logique de redimensionnement améliorée - ancrage intelligent
        switch (resizeHandle) {
          case 'nw':
            // Redimensionner depuis le coin nord-ouest : maintenir le coin SE fixe
            newWidth = Math.max(20, resizeStart.width - deltaX);
            newHeight = Math.max(20, resizeStart.height - deltaY);
            newX = resizeStart.elementX + deltaX; // Se déplacer avec la souris
            newY = resizeStart.elementY + deltaY;
            break;
          case 'ne':
            // Redimensionner depuis le coin nord-est : maintenir le coin SW fixe
            newWidth = Math.max(20, resizeStart.width + deltaX);
            newHeight = Math.max(20, resizeStart.height - deltaY);
            newY = resizeStart.elementY + deltaY;
            break;
          case 'sw':
            // Redimensionner depuis le coin sud-ouest : maintenir le coin NE fixe
            newWidth = Math.max(20, resizeStart.width - deltaX);
            newHeight = Math.max(20, resizeStart.height + deltaY);
            newX = resizeStart.elementX + deltaX;
            break;
          case 'se':
            // Redimensionner depuis le coin sud-est : maintenir le coin NW fixe
            newWidth = Math.max(20, resizeStart.width + deltaX);
            newHeight = Math.max(20, resizeStart.height + deltaY);
            // Pas de changement de position
            break;
          case 'n':
            // Redimensionner depuis le côté nord : maintenir le côté sud fixe
            newHeight = Math.max(20, resizeStart.height - deltaY);
            newY = resizeStart.elementY + deltaY;
            break;
          case 's':
            // Redimensionner depuis le côté sud : maintenir le côté nord fixe
            newHeight = Math.max(20, resizeStart.height + deltaY);
            break;
          case 'w':
            // Redimensionner depuis le côté ouest : maintenir le côté est fixe
            newWidth = Math.max(20, resizeStart.width - deltaX);
            newX = resizeStart.elementX + deltaX;
            break;
          case 'e':
            // Redimensionner depuis le côté est : maintenir le côté ouest fixe
            newWidth = Math.max(20, resizeStart.width + deltaX);
            break;
        }

        // Adaptation selon le type d'élément
        if (element.type === 'circle') {
          // Pour les cercles, utiliser le minimum de width/height comme diamètre
          newRadius = Math.min(newWidth, newHeight) / 2;
          handleElementUpdate(selectedElement.id, {
            x: newX,
            y: newY,
            radius: Math.max(10, newRadius)
          });
        } else {
          handleElementUpdate(selectedElement.id, {
            x: newX,
            y: newY,
            width: newWidth,
            height: newHeight
          });
        }
      }
    } else if (isDragging && dragElement) {
      // Déplacer l'élément
      const deltaX = x - dragStart.x;
      const deltaY = y - dragStart.y;
      const newX = dragElement.x + deltaX;
      const newY = dragElement.y + deltaY;

      handleElementUpdate(dragElement.id, {
        x: Math.max(0, newX), // Empêcher de sortir du canvas
        y: Math.max(0, newY)
      });
    }
  };

  // Gestionnaire de relâchement de souris
  const handleCanvasMouseUp = () => {
    setIsDragging(false);
    setIsResizing(false);
    setDragElement(null);
    setResizeHandle(null);
  };

  // Fonction pour déterminer quelle poignée de redimensionnement est à une position
  const getResizeHandleAtPosition = (element, x, y) => {
    const handleSize = 10;
    const offset = 5;

    // Calculer les dimensions selon le type d'élément
    let elementWidth = element.width || 100;
    let elementHeight = element.height || 30;

    if (element.type === 'circle') {
      elementWidth = (element.radius || 25) * 2;
      elementHeight = (element.radius || 25) * 2;
    }

    // Calculer les positions des poignées
    const handles = {
      nw: { x: element.x - offset, y: element.y - offset },
      ne: { x: element.x + elementWidth - offset, y: element.y - offset },
      sw: { x: element.x - offset, y: element.y + elementHeight - offset },
      se: { x: element.x + elementWidth - offset, y: element.y + elementHeight - offset },
      n: { x: element.x + elementWidth / 2 - offset, y: element.y - offset },
      s: { x: element.x + elementWidth / 2 - offset, y: element.y + elementHeight - offset },
      w: { x: element.x - offset, y: element.y + elementHeight / 2 - offset },
      e: { x: element.x + elementWidth - offset, y: element.y + elementHeight / 2 - offset }
    };

    for (const [handle, pos] of Object.entries(handles)) {
      if (x >= pos.x && x <= pos.x + handleSize &&
          y >= pos.y && y <= pos.y + handleSize) {
        return handle;
      }
    }

    return null;
  };

  // Fonction de rendu du canvas
  const renderCanvas = () => {
    console.log('[DEBUG] PDFEditor renderCanvas called with elements:', elements.length, 'selectedElementId:', selectedElementId, 'selectedElement.template:', selectedElement?.template);
    const canvas = canvasRef.current;
    if (!canvas) {
      console.log('PDFEditor renderCanvas: No canvas ref');
      return;
    }

    if (elements.length > 0) {
    }

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Grille si activée
    if (showGrid) {
      ctx.strokeStyle = '#f0f0f0';
      ctx.lineWidth = 1;
      const gridSize = 20;

      for (let x = 0; x <= canvas.width; x += gridSize) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
      }

      for (let y = 0; y <= canvas.height; y += gridSize) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
      }
    }

    // Dessiner les éléments (order_number en dernier pour qu'ils apparaissent au-dessus)
    const sortedElements = [...elements].sort((a, b) => {
      if (a.type === 'order_number' && b.type !== 'order_number') return 1;
      if (b.type === 'order_number' && a.type !== 'order_number') return -1;
      return 0;
    });

    sortedElements.forEach((element, index) => {

      // Mettre en évidence l'élément sélectionné
      if (selectedElement?.id === element.id) {
        ctx.strokeStyle = '#007cba';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);

        if (element.type === 'text') {
          const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
          ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
          const metrics = ctx.measureText(element.text || 'Texte');
          ctx.strokeRect(element.x - 5, element.y - element.fontSize - 5,
                        metrics.width + 10, element.fontSize + 10);
        } else if (element.type === 'rectangle') {
          ctx.strokeRect(element.x - 5, element.y - 5,
                        (element.width || 100) + 10, (element.height || 50) + 10);
        } else if (element.type === 'circle') {
          ctx.beginPath();
          ctx.arc(element.x, element.y, (element.radius || 25) + 5, 0, 2 * Math.PI);
          ctx.stroke();
        } else if (element.type === 'company_logo' || element.type === 'dynamic-text' ||
                   element.type === 'order_number' || element.type === 'document_type' ||
                   element.type === 'customer_info' || element.type === 'company_info' ||
                   element.type === 'product_table' || element.type === 'mentions') {
          // Mise en évidence pour les éléments rectangulaires
          ctx.strokeRect(element.x - 5, element.y - 5,
                        (element.width || 100) + 10, (element.height || 30) + 10);
        } else if (element.type === 'line') {
          // Mise en évidence pour les lignes
          ctx.strokeRect(element.x - 5, element.y - 10,
                        (element.width || 20) + 10, (element.height || 12) + 20);
        }

        ctx.setLineDash([]);
      }

      // Dessiner les poignées de redimensionnement si l'élément est sélectionné
      if (selectedElement?.id === element.id && (element.type === 'rectangle' || element.type === 'circle' ||
          element.type === 'company_logo' || element.type === 'dynamic-text' || element.type === 'order_number' ||
          element.type === 'document_type' || element.type === 'customer_info' || element.type === 'company_info' ||
          element.type === 'product_table' || element.type === 'mentions' || element.type === 'line')) {
        const handleSize = 8;
        const offset = 4;

        ctx.fillStyle = '#007cba';
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 1;

        // Calculer les dimensions selon le type d'élément
        let handleWidth = element.width || 100;
        let handleHeight = element.height || 30;

        if (element.type === 'circle') {
          handleWidth = (element.radius || 25) * 2;
          handleHeight = (element.radius || 25) * 2;
        }

        // Poignées de coin et latérales
        const handles = [
          { x: element.x - offset, y: element.y - offset }, // nw
          { x: element.x + handleWidth - offset, y: element.y - offset }, // ne
          { x: element.x - offset, y: element.y + handleHeight - offset }, // sw
          { x: element.x + handleWidth - offset, y: element.y + handleHeight - offset }, // se
          { x: element.x + handleWidth / 2 - offset, y: element.y - offset }, // n
          { x: element.x + handleWidth / 2 - offset, y: element.y + handleHeight - offset }, // s
          { x: element.x - offset, y: element.y + handleHeight / 2 - offset }, // w
          { x: element.x + handleWidth - offset, y: element.y + handleHeight / 2 - offset } // e
        ];

        handles.forEach(handle => {
          ctx.fillRect(handle.x, handle.y, handleSize, handleSize);
          ctx.strokeRect(handle.x, handle.y, handleSize, handleSize);
        });
      }

      // Dessiner l'élément

      if (element.type === 'text') {
        // Appliquer la couleur du texte
        ctx.fillStyle = element.color || '#000000';

        // Appliquer le style de police (italic, etc.)
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';
        const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
        ctx.font = `${fontStyle}${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;

        // Appliquer l'alignement du texte
        ctx.textAlign = element.textAlign || 'left';

        const textX = element.x || 10;
        const textY = element.y || 30;
        ctx.fillText(element.text || 'Texte', textX, textY);
      } else if (element.type === 'rectangle') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        const rectX = element.x || 10;
        const rectY = element.y || 10;
        const rectWidth = element.width || 100;
        const rectHeight = element.height || 50;

        // Fond du rectangle
        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            // Rectangle avec coins arrondis
            ctx.beginPath();
            ctx.roundRect(rectX, rectY, rectWidth, rectHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(rectX, rectY, rectWidth, rectHeight);
          }
        }

        // Bordure du rectangle
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(rectX, rectY, rectWidth, rectHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(rectX, rectY, rectWidth, rectHeight);
          }
        }

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'circle') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        const centerX = element.x || 10;
        const centerY = element.y || 10;
        const radius = element.radius || 25;

        // Fond du cercle
        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          ctx.beginPath();
          ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
          ctx.fill();
        }

        // Bordure du cercle
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.beginPath();
          ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
          ctx.stroke();
        }

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'company_logo') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les filtres CSS si définis
        if (element.brightness !== undefined || element.contrast !== undefined || element.saturate !== undefined) {
          const brightness = element.brightness !== undefined ? element.brightness / 100 : 1;
          const contrast = element.contrast !== undefined ? element.contrast / 100 : 1;
          const saturate = element.saturate !== undefined ? element.saturate / 100 : 1;

          if (brightness !== 1) {
            ctx.filter = `brightness(${brightness})`;
          }
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = element.shadowBlur || 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        const imgX = element.x || 10;
        const imgY = element.y || 10;
        const imgWidth = element.width || 120;
        const imgHeight = element.height || 90;

        // Fond du logo avec un dégradé moderne si pas de couleur unie
        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          if (element.backgroundColor.includes('gradient')) {
            // Gestion des dégradés CSS simulés
            const gradient = ctx.createLinearGradient(imgX, imgY, imgX + imgWidth, imgY + imgHeight);
            if (element.backgroundColor.includes('linear-gradient')) {
              // Dégradé simple bleu moderne par défaut
              gradient.addColorStop(0, '#667eea');
              gradient.addColorStop(1, '#764ba2');
            }
            ctx.fillStyle = gradient;
          } else {
            ctx.fillStyle = element.backgroundColor;
          }

          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(imgX, imgY, imgWidth, imgHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(imgX, imgY, imgWidth, imgHeight);
          }
        }

        // Bordure du logo avec style moderne
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#e5e7eb';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(imgX, imgY, imgWidth, imgHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(imgX, imgY, imgWidth, imgHeight);
          }
        }

        // Rendu de l'image du logo ou placeholder élégant
        const imageUrl = element.src || element.imageUrl;

        if (imageUrl) {
          const img = new Image();
          img.onload = () => {
            // Calculer les dimensions pour maintenir le ratio d'aspect
            const aspectRatio = img.width / img.height;
            let drawWidth = imgWidth;
            let drawHeight = imgHeight;
            let drawX = imgX;
            let drawY = imgY;

            // Gestion des modes object-fit
            const objectFit = element.objectFit || 'contain';
            if (objectFit === 'cover') {
              if (imgWidth / imgHeight > aspectRatio) {
                drawHeight = imgHeight;
                drawWidth = imgHeight * aspectRatio;
                drawX = imgX + (imgWidth - drawWidth) / 2;
              } else {
                drawWidth = imgWidth;
                drawHeight = imgWidth / aspectRatio;
                drawY = imgY + (imgHeight - drawHeight) / 2;
              }
            } else if (objectFit === 'contain') {
              if (aspectRatio > imgWidth / imgHeight) {
                drawWidth = imgWidth;
                drawHeight = imgWidth / aspectRatio;
                drawY = imgY + (imgHeight - drawHeight) / 2;
              } else {
                drawHeight = imgHeight;
                drawWidth = imgHeight * aspectRatio;
                drawX = imgX + (imgWidth - drawWidth) / 2;
              }
            } else if (objectFit === 'fill') {
              // Étirer pour remplir complètement
              drawWidth = imgWidth;
              drawHeight = imgHeight;
              drawX = imgX;
              drawY = imgY;
            }

            // Appliquer un léger arrondi aux coins de l'image si borderRadius défini
            if (element.borderRadius > 0) {
              ctx.save();
              ctx.beginPath();
              ctx.roundRect(imgX, imgY, imgWidth, imgHeight, element.borderRadius);
              ctx.clip();
              ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
              ctx.restore();
            } else {
              ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
            }
          };

          img.onerror = () => {
            // Placeholder élégant en cas d'erreur de chargement
            renderLogoPlaceholder(ctx, imgX, imgY, imgWidth, imgHeight, element);
          };

          img.src = imageUrl;
        } else {
          // Placeholder quand aucune image n'est définie
          renderLogoPlaceholder(ctx, imgX, imgY, imgWidth, imgHeight, element);
        }

        // Restaurer les filtres et autres propriétés
        ctx.filter = 'none';
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'dynamic-text') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les filtres CSS si définis (simulation avec canvas)
        if (element.brightness !== undefined || element.contrast !== undefined || element.saturate !== undefined) {
          // Note: Les filtres canvas sont limités, nous simulons avec des ajustements de couleur
          const brightness = element.brightness !== undefined ? element.brightness / 100 : 1;
          const contrast = element.contrast !== undefined ? element.contrast / 100 : 1;
          const saturate = element.saturate !== undefined ? element.saturate / 100 : 1;

          // Appliquer un filtre de luminosité simple en ajustant la couleur
          if (brightness !== 1) {
            ctx.filter = `brightness(${brightness})`;
          }
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = element.shadowBlur || 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        // Fond du texte si défini
        const textX = element.x || 10;
        const textY = element.y || 30;
        const textWidth = element.width || 200;
        const textHeight = element.height || 30;

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(textX, textY, textWidth, textHeight);
          }
        }

        // Bordure du fond si définie
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(textX, textY, textWidth, textHeight);
          }
        }

        // Configuration du texte avec toutes les propriétés de typographie
        ctx.fillStyle = element.color || '#333333';
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';
        const fontWeight = element.fontWeight || 'normal';
        const fontSize = element.fontSize || 14;
        const fontFamily = element.fontFamily || 'Arial';
        const letterSpacing = element.letterSpacing || 0;
        const lineHeight = element.lineHeight || 1.2;

        ctx.font = `${fontStyle}${fontWeight} ${fontSize}px ${fontFamily}`;
        ctx.textAlign = element.textAlign || 'left';

        // Générer le contenu du texte dynamique
        let displayText = element.text || 'Texte dynamique';

        // Utiliser SampleDataProvider pour les templates prédéfinis
        if (element.template && element.template !== 'custom') {
          const sampleDataProvider = new SampleDataProvider();
          const templateData = sampleDataProvider.generateDynamicTextData({
            template: element.template,
            customContent: element.customContent || '',
            variables: element.variables || {}
          });
          displayText = templateData.content;
          console.log('[DEBUG] PDFEditor renderCanvas dynamic-text:', {
            id: element.id,
            template: element.template,
            customContent: element.customContent,
            displayText: displayText,
            timestamp: Date.now()
          });
        } else if (element.customContent) {
          displayText = element.customContent;
        }

        // Appliquer la transformation de texte
        if (element.textTransform === 'uppercase') {
          displayText = displayText.toUpperCase();
        } else if (element.textTransform === 'lowercase') {
          displayText = displayText.toLowerCase();
        } else if (element.textTransform === 'capitalize') {
          displayText = displayText.replace(/\b\w/g, l => l.toUpperCase());
        }

        // Gestion du texte multiligne avec espacement des lettres et hauteur de ligne
        const lines = displayText.split('\n');
        const lineSpacing = fontSize * lineHeight;
        let currentY = textY + fontSize;

        // Ajuster la position verticale selon l'alignement
        if (element.textAlign === 'center') {
          currentY = textY + (textHeight - (lines.length - 1) * lineSpacing) / 2 + fontSize / 2;
        } else if (element.textAlign === 'right') {
          currentY = textY + fontSize;
        }

        lines.forEach((line, index) => {
          const lineY = currentY + (index * lineSpacing);

          // Appliquer l'espacement des lettres en dessinant caractère par caractère
          if (letterSpacing > 0) {
            let charX = textX;
            if (element.textAlign === 'center') {
              const lineWidth = ctx.measureText(line).width + (line.length - 1) * letterSpacing;
              charX = textX + (textWidth - lineWidth) / 2;
            } else if (element.textAlign === 'right') {
              const lineWidth = ctx.measureText(line).width + (line.length - 1) * letterSpacing;
              charX = textX + textWidth - lineWidth;
            }

            for (let i = 0; i < line.length; i++) {
              ctx.fillText(line[i], charX, lineY);
              charX += ctx.measureText(line[i]).width + letterSpacing;
            }
          } else {
            // Texte normal sans espacement des lettres
            let textXPos = textX;
            if (element.textAlign === 'center') {
              textXPos = textX + textWidth / 2;
            } else if (element.textAlign === 'right') {
              textXPos = textX + textWidth;
            }
            ctx.fillText(line, textXPos, lineY);
          }

          // Appliquer la décoration de texte (souligné, barré) à chaque ligne
          if (element.textDecoration === 'underline' || element.textDecoration === 'line-through') {
            const lineWidth = letterSpacing > 0 ?
              line.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing) :
              ctx.measureText(line).width;

            let decorationX = textX;
            if (element.textAlign === 'center') {
              decorationX = textX + (textWidth - lineWidth) / 2;
            } else if (element.textAlign === 'right') {
              decorationX = textX + textWidth - lineWidth;
            }

            const decorationY = lineY + (element.textDecoration === 'underline' ? 2 : -fontSize * 0.2);

            ctx.strokeStyle = element.color || '#333333';
            ctx.lineWidth = Math.max(1, fontSize / 20);
            ctx.beginPath();
            ctx.moveTo(decorationX, decorationY);
            ctx.lineTo(decorationX + lineWidth, decorationY);
            ctx.stroke();
          }
        });

        // Restaurer les filtres et autres propriétés
        ctx.filter = 'none';
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'order_number') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = element.shadowBlur || 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        // Générer les données du numéro de commande avec SampleDataProvider
        const sampleDataProvider = new SampleDataProvider();
        // Utiliser le nouveau format par défaut si l'ancien contient une date (migration)
        const defaultFormat = 'Commande #{order_number}';
        const currentFormat = element.format;
        const formatToUse = currentFormat && currentFormat.includes('{order_date}')
          ? defaultFormat
          : (currentFormat || defaultFormat);

        const orderData = sampleDataProvider.generateOrderNumberData({
          format: formatToUse,
          previewOrderNumber: element.previewOrderNumber || '12345'
        });

        // Fond du texte si défini
        const textX = element.x || 10;
        const textY = element.y || 30;
        const textWidth = element.width || 270;
        const textHeight = element.height || 40;

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(textX, textY, textWidth, textHeight);
          }
        }

        // Bordure du fond si définie
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(textX, textY, textWidth, textHeight);
          }
        }

        // Configuration du texte avec toutes les propriétés typographiques
        ctx.fillStyle = element.color || '#000000';
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';
        const fontWeight = element.fontWeight || 'bold';
        const fontSize = element.fontSize || 14;
        const fontFamily = element.fontFamily || 'Arial';
        const letterSpacing = element.letterSpacing || 0;
        const textDecoration = element.textDecoration;

        ctx.font = `${fontStyle}${fontWeight} ${fontSize}px ${fontFamily}`;

        // Calculer les dimensions pour le positionnement
        // Positionner le texte avec une marge pour éviter les superpositions lors du redimensionnement
        const textMargin = 8; // Marge depuis les bords
        const labelBaselineY = textY + textMargin + fontSize;
        const numberBaselineY = labelBaselineY + fontSize + 4; // Ligne du numéro en dessous de l'étiquette
        let labelWidth = 0;
        let availableWidth = textWidth - (textMargin * 2);

        // Calculer les propriétés de l'étiquette si elle doit être affichée
        let processedLabel = '';
        if (element.showLabel && element.labelText) {
          processedLabel = element.labelText;
          if (element.textTransform === 'uppercase') {
            processedLabel = processedLabel.toUpperCase();
          } else if (element.textTransform === 'lowercase') {
            processedLabel = processedLabel.toLowerCase();
          } else if (element.textTransform === 'capitalize') {
            processedLabel = processedLabel.replace(/\b\w/g, l => l.toUpperCase());
          }

          labelWidth = letterSpacing > 0 ?
            processedLabel.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing) :
            ctx.measureText(processedLabel).width;
        }

        // Afficher l'étiquette si elle doit être affichée
        if (element.showLabel && element.labelText && processedLabel) {
          const labelX = textX + textMargin;
          const labelY = labelBaselineY;

          // Appliquer l'espacement des lettres pour l'étiquette si défini
          if (letterSpacing > 0) {
            let charX = labelX;
            for (let i = 0; i < processedLabel.length; i++) {
              ctx.fillText(processedLabel[i], charX, labelY);
              charX += ctx.measureText(processedLabel[i]).width + letterSpacing;
            }
          } else {
            ctx.textAlign = 'left';
            ctx.fillText(processedLabel, labelX, labelY);
          }

          // Appliquer la décoration de texte à l'étiquette
          if (textDecoration === 'underline' || textDecoration === 'line-through') {
            const decorationY = labelY + (textDecoration === 'underline' ? 2 : -fontSize * 0.2);
            ctx.strokeStyle = element.color || '#000000';
            ctx.lineWidth = Math.max(1, fontSize / 20);
            ctx.beginPath();
            ctx.moveTo(labelX, decorationY);
            ctx.lineTo(labelX + labelWidth, decorationY);
            ctx.stroke();
          }
        }

        // Position du numéro (à droite de l'étiquette avec espacement)
        const numberX = textX + textMargin + (element.showLabel && element.labelText ? labelWidth + 10 : 0);
        const numberWidth = availableWidth - (element.showLabel && element.labelText ? labelWidth + 10 : 0);

        // Afficher le numéro formaté
        const formattedText = orderData.formatted || 'Commande #12345';

        // Appliquer la transformation de texte
        let displayText = formattedText;
        if (element.textTransform === 'uppercase') {
          displayText = displayText.toUpperCase();
        } else if (element.textTransform === 'lowercase') {
          displayText = displayText.toLowerCase();
        } else if (element.textTransform === 'capitalize') {
          displayText = displayText.replace(/\b\w/g, l => l.toUpperCase());
        }

        ctx.textAlign = element.textAlign || 'right';

        // Ajouter un fond subtil au numéro pour le mettre en valeur
        if (element.highlightNumber) {
          const numberTextWidth = letterSpacing > 0 ?
            displayText.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing) :
            ctx.measureText(displayText).width;

          let highlightX = numberX + numberWidth - numberTextWidth;
          if (element.textAlign === 'center') {
            highlightX = numberX + (numberWidth - numberTextWidth) / 2;
          } else if (element.textAlign === 'left') {
            highlightX = numberX;
          }

          // Fond subtil
          ctx.fillStyle = element.numberBackground || 'rgba(59, 130, 246, 0.1)';
          ctx.fillRect(highlightX - 4, numberBaselineY - fontSize * 0.7, numberTextWidth + 8, fontSize * 1.2);
        }

        // Afficher le numéro avec espacement des lettres si défini
        if (letterSpacing > 0) {
          let startX;
          if (element.textAlign === 'center') {
            const totalWidth = displayText.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing);
            startX = numberX + (numberWidth - totalWidth) / 2 + ctx.measureText(displayText[0]).width / 2;
          } else if (element.textAlign === 'left') {
            startX = numberX + ctx.measureText(displayText[0]).width / 2;
          } else { // right (default)
            startX = numberX + numberWidth - ctx.measureText(displayText[displayText.length - 1]).width / 2;
            // Calculer la position de départ pour alignement à droite avec letterSpacing
            for (let i = displayText.length - 1; i >= 0; i--) {
              startX -= ctx.measureText(displayText[i]).width + (i > 0 ? letterSpacing : 0);
            }
            startX += ctx.measureText(displayText[0]).width / 2;
          }

          for (let i = 0; i < displayText.length; i++) {
            ctx.fillText(displayText[i], startX, numberBaselineY);
            startX += ctx.measureText(displayText[i]).width + letterSpacing;
          }
        } else {
          let textXPos;
          if (element.textAlign === 'center') {
            textXPos = numberX + numberWidth / 2;
            ctx.textAlign = 'center';
          } else if (element.textAlign === 'left') {
            textXPos = numberX;
            ctx.textAlign = 'left';
          } else { // right (default)
            textXPos = numberX + numberWidth;
            ctx.textAlign = 'right';
          }
          ctx.fillText(displayText, textXPos, numberBaselineY);
        }

        // Appliquer la décoration de texte au numéro
        if (textDecoration === 'underline' || textDecoration === 'line-through') {
          const numberTextWidth = letterSpacing > 0 ?
            displayText.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing) :
            ctx.measureText(displayText).width;

          let decorationX = numberX + numberWidth - numberTextWidth;
          if (element.textAlign === 'center') {
            decorationX = numberX + (numberWidth - numberTextWidth) / 2;
          } else if (element.textAlign === 'left') {
            decorationX = numberX;
          }

          const decorationY = numberBaselineY + (textDecoration === 'underline' ? 2 : -fontSize * 0.2);
          ctx.strokeStyle = element.color || '#000000';
          ctx.lineWidth = Math.max(1, fontSize / 20);
          ctx.beginPath();
          ctx.moveTo(decorationX, decorationY);
          ctx.lineTo(decorationX + numberTextWidth, decorationY);
          ctx.stroke();
        }

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'document_type') {
        // Appliquer la couleur du texte
        ctx.fillStyle = element.color || '#000000';

        const fontWeight = element.fontWeight || 'bold';
        ctx.font = `${fontWeight} ${element.fontSize || 18}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'center';

        const textX = element.x || 10;
        const textY = element.y || 30;
        const displayText = 'FACTURE';

        ctx.fillText(displayText, textX + (element.width || 120) / 2, textY + (element.height || 50) / 2);

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'line') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        ctx.strokeStyle = element.lineColor || element.color || '#64748b';
        ctx.lineWidth = element.lineWidth || 2;
        ctx.beginPath();
        const lineX = element.x || 10;
        const lineY = element.y || 110;
        const lineWidth = element.width || 20;
        ctx.moveTo(lineX, lineY + (element.height || 12) / 2);
        ctx.lineTo(lineX + lineWidth, lineY + (element.height || 12) / 2);
        ctx.stroke();

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'shape-triangle') {
        // Rendu spécifique pour le triangle
        const centerX = element.x || 10;
        const centerY = element.y || 10;
        const width = element.width || 60;
        const height = element.height || 50;

        ctx.beginPath();
        ctx.moveTo(centerX, centerY - height / 2); // Sommet
        ctx.lineTo(centerX - width / 2, centerY + height / 2); // Bas gauche
        ctx.lineTo(centerX + width / 2, centerY + height / 2); // Bas droite
        ctx.closePath();

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          ctx.fill();
        }

        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.stroke();
        }
      } else if (element.type === 'shape-star') {
        // Rendu spécifique pour l'étoile
        const centerX = element.x || 10;
        const centerY = element.y || 10;
        const outerRadius = (element.width || 60) / 2;
        const innerRadius = outerRadius * 0.5;
        const spikes = 5;

        ctx.beginPath();
        for (let i = 0; i < spikes * 2; i++) {
          const angle = (i * Math.PI) / spikes;
          const radius = i % 2 === 0 ? outerRadius : innerRadius;
          const x = centerX + Math.cos(angle) * radius;
          const y = centerY + Math.sin(angle) * radius;
          if (i === 0) ctx.moveTo(x, y);
          else ctx.lineTo(x, y);
        }
        ctx.closePath();

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          ctx.fill();
        }

        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.stroke();
        }
      } else if (element.type === 'mentions') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        // Fond du texte si défini
        const textX = element.x || 10;
        const textY = element.y || 10;
        const textWidth = element.width || 300;
        const textHeight = element.height || 40;

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(textX, textY, textWidth, textHeight);
          }
        }

        // Bordure du fond si définie
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, textY, textWidth, textHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(textX, textY, textWidth, textHeight);
          }
        }

        // Configuration du texte
        ctx.fillStyle = element.color || '#666666';
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';
        const fontWeight = element.fontWeight || 'normal';
        const textDecoration = element.textDecoration;
        ctx.font = `${fontStyle}${fontWeight} ${element.fontSize || 8}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'center';

        const parts = [];
        if (element.showEmail) parts.push('email@company.com');
        if (element.showPhone) parts.push('+33 1 23 45 67 89');
        if (element.showSiret) parts.push('SIRET: 123 456 789 00012');

        const displayText = parts.join(element.separator || ' • ');
        const textBaselineY = textY + textHeight / 2;

        // Appliquer la décoration de texte
        if (textDecoration === 'underline' || textDecoration === 'line-through') {
          const textMetrics = ctx.measureText(displayText);
          const lineY = textBaselineY + (textDecoration === 'underline' ? 2 : -2);

          ctx.strokeStyle = element.color || '#666666';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(textX + (textWidth - textMetrics.width) / 2, lineY);
          ctx.lineTo(textX + (textWidth + textMetrics.width) / 2, lineY);
          ctx.stroke();
        }

        ctx.fillText(displayText, textX + textWidth / 2, textBaselineY);

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'customer_info' || element.type === 'company_info') {
        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = element.shadowBlur || 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        // Générer les données avec SampleDataProvider
        const sampleDataProvider = new SampleDataProvider();
        let infoData;
        if (element.type === 'customer_info') {
          infoData = sampleDataProvider.generateCustomerInfoData({
            fields: element.fields || ['name', 'email', 'phone', 'address']
          });
        } else {
          infoData = sampleDataProvider.generateCompanyInfoData({
            fields: element.fields || ['name', 'address', 'phone', 'email']
          });
        }

        // Fond du bloc d'informations avec style moderne
        const textX = element.x || 10;
        let currentY = element.y || 10;
        const blockWidth = element.width || 250;
        const blockHeight = element.height || 120;

        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, currentY, blockWidth, blockHeight, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(textX, currentY, blockWidth, blockHeight);
          }
        }

        // Bordure du bloc avec style moderne
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#e2e8f0';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(textX, currentY, blockWidth, blockHeight, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(textX, currentY, blockWidth, blockHeight);
          }
        }

        // Configuration du texte avec toutes les propriétés typographiques
        ctx.fillStyle = element.color || '#1e293b';
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';
        const fontWeight = element.fontWeight || 'normal';
        const fontSize = element.fontSize || 12;
        const fontFamily = element.fontFamily || 'Arial';
        const lineHeight = element.lineHeight || 1.4;
        const letterSpacing = element.letterSpacing || 0;

        ctx.font = `${fontStyle}${fontWeight} ${fontSize}px ${fontFamily}`;
        ctx.textAlign = 'left';

        // Icônes pour chaque type de champ
        const fieldIcons = {
          name: '👤',
          email: '📧',
          phone: '📞',
          address: '🏠',
          company: '🏢',
          vat: '🆔',
          siret: '📋'
        };

        // Calculer la hauteur de ligne
        const lineSpacing = fontSize * lineHeight;

        // Rendu selon la disposition (verticale ou horizontale)
        const layout = element.layout || 'vertical';
        const showLabels = element.showLabels !== false;
        const labelStyle = element.labelStyle || 'normal';

        if (layout === 'vertical') {
          // Disposition verticale
          Object.entries(infoData).forEach(([field, value]) => {
            if (!value) return;

            const icon = fieldIcons[field] || '📄';
            let displayText = value;

            // Ajouter l'étiquette si demandée
            if (showLabels) {
              const label = field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');
              if (labelStyle === 'bold') {
                displayText = `${icon} ${label}: ${value}`;
              } else if (labelStyle === 'italic') {
                displayText = `${icon} ${label}: ${value}`;
              } else {
                displayText = `${icon} ${label}: ${value}`;
              }
            } else {
              displayText = `${icon} ${value}`;
            }

            // Appliquer la transformation de texte
            if (element.textTransform === 'uppercase') {
              displayText = displayText.toUpperCase();
            } else if (element.textTransform === 'lowercase') {
              displayText = displayText.toLowerCase();
            } else if (element.textTransform === 'capitalize') {
              displayText = displayText.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Position Y pour cette ligne
            const lineY = currentY + fontSize;

            // Appliquer l'espacement des lettres si défini
            if (letterSpacing > 0) {
              let charX = textX;
              for (let i = 0; i < displayText.length; i++) {
                ctx.fillText(displayText[i], charX, lineY);
                charX += ctx.measureText(displayText[i]).width + letterSpacing;
              }
            } else {
              ctx.fillText(displayText, textX, lineY);
            }

            // Appliquer la décoration de texte
            if (element.textDecoration === 'underline' || element.textDecoration === 'line-through') {
              const textWidth = letterSpacing > 0 ?
                displayText.split('').reduce((width, char) => width + ctx.measureText(char).width + letterSpacing, -letterSpacing) :
                ctx.measureText(displayText).width;

              const decorationY = lineY + (element.textDecoration === 'underline' ? 2 : -fontSize * 0.2);
              ctx.strokeStyle = element.color || '#1e293b';
              ctx.lineWidth = Math.max(1, fontSize / 20);
              ctx.beginPath();
              ctx.moveTo(textX, decorationY);
              ctx.lineTo(textX + textWidth, decorationY);
              ctx.stroke();
            }

            currentY += lineSpacing;
          });
        } else {
          // Disposition horizontale (2 colonnes)
          const fields = Object.entries(infoData);
          const midPoint = Math.ceil(fields.length / 2);

          for (let i = 0; i < midPoint; i++) {
            const leftField = fields[i];
            const rightField = fields[i + midPoint];

            // Colonne gauche
            if (leftField) {
              const [field, value] = leftField;
              const icon = fieldIcons[field] || '📄';
              let displayText = value;

              if (showLabels) {
                const label = field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');
                displayText = `${icon} ${label}: ${value}`;
              } else {
                displayText = `${icon} ${value}`;
              }

              // Appliquer la transformation de texte
              if (element.textTransform === 'uppercase') {
                displayText = displayText.toUpperCase();
              } else if (element.textTransform === 'lowercase') {
                displayText = displayText.toLowerCase();
              } else if (element.textTransform === 'capitalize') {
                displayText = displayText.replace(/\b\w/g, l => l.toUpperCase());
              }

              const lineY = currentY + fontSize;
              ctx.fillText(displayText, textX, lineY);
            }

            // Colonne droite
            if (rightField) {
              const [field, value] = rightField;
              const icon = fieldIcons[field] || '📄';
              let displayText = value;

              if (showLabels) {
                const label = field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');
                displayText = `${icon} ${label}: ${value}`;
              } else {
                displayText = `${icon} ${value}`;
              }

              // Appliquer la transformation de texte
              if (element.textTransform === 'uppercase') {
                displayText = displayText.toUpperCase();
              } else if (element.textTransform === 'lowercase') {
                displayText = displayText.toLowerCase();
              } else if (element.textTransform === 'capitalize') {
                displayText = displayText.replace(/\b\w/g, l => l.toUpperCase());
              }

              const lineY = currentY + fontSize;
              const rightX = textX + blockWidth / 2 + 10;
              ctx.fillText(displayText, rightX, lineY);
            }

            currentY += lineSpacing;
          }
        }

        // Restaurer l'opacité et les ombres
        ctx.globalAlpha = 1;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
      } else if (element.type === 'product_table') {
        // Rendu du tableau de produits avec toutes les propriétés depuis PropertiesPanel et SampleDataProvider
        const tableX = element.x || 30;
        let currentY = element.y || 270;
        const tableWidth = element.width || 530;
        const tableHeight = element.height || 100;

        // Appliquer l'opacité si définie
        if (element.opacity !== undefined && element.opacity < 1) {
            ctx.globalAlpha = element.opacity;
        }

        // Appliquer les filtres CSS si définis (simulation avec canvas)
        if (element.brightness !== undefined || element.contrast !== undefined || element.saturate !== undefined) {
          // Note: Les filtres canvas sont limités, nous simulons avec des ajustements de couleur
          const brightness = element.brightness !== undefined ? element.brightness / 100 : 1;
          const contrast = element.contrast !== undefined ? element.contrast / 100 : 1;
          const saturate = element.saturate !== undefined ? element.saturate / 100 : 1;

          // Appliquer un filtre de luminosité simple en ajustant la couleur
          if (brightness !== 1) {
            ctx.filter = `brightness(${brightness})`;
          }
        }

        // Appliquer les ombres si définies
        if (element.shadow) {
          ctx.shadowColor = element.shadowColor || '#000000';
          ctx.shadowBlur = element.shadowBlur || 5;
          ctx.shadowOffsetX = element.shadowOffsetX || 2;
          ctx.shadowOffsetY = element.shadowOffsetY || 2;
        }

        // Fond du tableau si défini
        if (element.backgroundColor && element.backgroundColor !== 'transparent') {
          ctx.fillStyle = element.backgroundColor;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(tableX - 5, currentY - 5, tableWidth + 10, tableHeight + 10, element.borderRadius);
            ctx.fill();
          } else {
            ctx.fillRect(tableX - 5, currentY - 5, tableWidth + 10, tableHeight + 10);
          }
        }

        // Bordure du tableau si définie
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          if (element.borderRadius > 0) {
            ctx.beginPath();
            ctx.roundRect(tableX - 5, currentY - 5, tableWidth + 10, tableHeight + 10, element.borderRadius);
            ctx.stroke();
          } else {
            ctx.strokeRect(tableX - 5, currentY - 5, tableWidth + 10, tableHeight + 10);
          }
        }

        // Appliquer la transformation (rotation, échelle) si définie
        if (element.rotation || element.scaleX || element.scaleY) {
          ctx.save();
          const centerX = tableX + tableWidth / 2;
          const centerY = currentY + tableHeight / 2;

          ctx.translate(centerX, centerY);
          if (element.rotation) {
            ctx.rotate((element.rotation * Math.PI) / 180);
          }
          if (element.scaleX || element.scaleY) {
            ctx.scale(element.scaleX || 1, element.scaleY || 1);
          }
          ctx.translate(-centerX, -centerY);
        }

        // Générer les données du tableau avec SampleDataProvider
        const sampleDataProvider = new SampleDataProvider();
        const tableData = sampleDataProvider.generateProductTableData({
          columns: element.columns || {},
          showSubtotal: element.showSubtotal || false,
          showShipping: element.showShipping || true,
          showTaxes: element.showTaxes || true,
          showDiscount: element.showDiscount || true,
          showTotal: element.showTotal || true,
          tableStyle: element.tableStyle || 'default'
        });

        // Récupérer les données de style du tableau
        const tableStyleData = tableData.tableStyleData || {
          header_bg: [248, 249, 250], // #f8f9fa
          header_border: [226, 232, 240], // #e2e8f0
          row_border: [241, 245, 249], // #f1f5f9
          alt_row_bg: [250, 251, 252], // #fafbfc
          headerTextColor: '#000000',
          rowTextColor: '#000000',
          border_width: 1,
          headerFontWeight: 'bold',
          headerFontSize: '12px',
          rowFontSize: '11px'
        };

        // Configuration de la police avec propriétés avancées
        const headerFontSize = parseInt(tableStyleData.headerFontSize) || element.fontSize || 12;
        const rowFontSize = parseInt(tableStyleData.rowFontSize) || element.fontSize || 11;
        const fontFamily = element.fontFamily || 'Arial';
        const fontWeight = element.fontWeight || tableStyleData.headerFontWeight || 'normal';
        const fontStyle = element.fontStyle === 'italic' ? 'italic ' : '';

        // Appliquer les propriétés de texte avancées
        const textTransform = element.textTransform || 'none';
        const letterSpacing = element.letterSpacing || 0;
        const lineHeight = element.lineHeight || 1.2;

        // Calculer la largeur des colonnes avec largeur fixe pour la colonne Qté
        const numColumns = tableData.headers.length;
        let columnWidths = new Array(numColumns).fill(0);
        let totalWidthUsed = 0;

        // Largeur fixe de 40px pour la colonne "Qté"
        const quantityColumnIndex = tableData.headers.indexOf('Qté');
        if (quantityColumnIndex !== -1) {
          columnWidths[quantityColumnIndex] = 40;
          totalWidthUsed += 40;
        }

        // Répartir le reste de l'espace entre les autres colonnes
        const remainingWidth = tableWidth - totalWidthUsed;
        const remainingColumns = numColumns - (quantityColumnIndex !== -1 ? 1 : 0);
        const defaultColumnWidth = remainingColumns > 0 ? remainingWidth / remainingColumns : remainingWidth / numColumns;

        for (let i = 0; i < numColumns; i++) {
          if (columnWidths[i] === 0) { // Pas encore défini (pas la colonne Qté)
            columnWidths[i] = defaultColumnWidth;
          }
        }

        // En-têtes du tableau
        if (element.showHeaders !== false && tableData.headers.length > 0) {
          const headerHeight = 32;

          // Fond de l'en-tête avec dégradé subtil
          if (tableStyleData.header_bg) {
            if (tableStyleData.header_gradient) {
              // Dégradé personnalisé depuis le style
              const gradient = ctx.createLinearGradient(tableX, currentY, tableX, currentY + headerHeight);
              gradient.addColorStop(0, tableStyleData.header_gradient[0]);
              gradient.addColorStop(1, tableStyleData.header_gradient[1]);
              ctx.fillStyle = gradient;
            } else {
              ctx.fillStyle = `rgb(${tableStyleData.header_bg.join(',')})`;
            }
          } else {
            // Dégradé par défaut moderne
            const gradient = ctx.createLinearGradient(tableX, currentY, tableX, currentY + headerHeight);
            gradient.addColorStop(0, '#f8fafc');
            gradient.addColorStop(1, '#e2e8f0');
            ctx.fillStyle = gradient;
          }

          // Dessiner le fond de l'en-tête avec coins arrondis
          const headerRadius = element.borderRadius || 4;
          ctx.beginPath();
          ctx.roundRect(tableX, currentY, tableWidth, headerHeight, headerRadius);
          ctx.fill();

          // Bordure supérieure de l'en-tête
          ctx.strokeStyle = tableStyleData.header_border ? `rgb(${tableStyleData.header_border.join(',')})` : '#cbd5e1';
          ctx.lineWidth = tableStyleData.border_width || 1;
          ctx.beginPath();
          ctx.moveTo(tableX, currentY + headerHeight);
          ctx.lineTo(tableX + tableWidth, currentY + headerHeight);
          ctx.stroke();

          // Texte des en-têtes
          ctx.fillStyle = tableStyleData.headerTextColor || '#1e293b';
          ctx.font = `${fontStyle}${fontWeight} ${headerFontSize}px ${fontFamily}`;
          ctx.textAlign = 'center';

          tableData.headers.forEach((header, index) => {
            let headerText = header;

            // Appliquer la transformation de texte
            if (textTransform === 'uppercase') {
              headerText = headerText.toUpperCase();
            } else if (textTransform === 'lowercase') {
              headerText = headerText.toLowerCase();
            } else if (textTransform === 'capitalize') {
              headerText = headerText.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Calculer la position X centrée dans la colonne
            let headerX;
            if (index === 0) {
              headerX = tableX + (columnWidths[0] / 2);
            } else {
              const previousWidth = columnWidths.slice(0, index).reduce((sum, w) => sum + w, 0);
              headerX = tableX + previousWidth + (columnWidths[index] / 2);
            }

            // Afficher le texte avec espacement des lettres si défini
            if (letterSpacing > 0) {
              let charX = headerX - (headerText.length * letterSpacing) / 2;
              for (let i = 0; i < headerText.length; i++) {
                ctx.fillText(headerText[i], charX, currentY + 22);
                charX += ctx.measureText(headerText[i]).width + letterSpacing;
              }
            } else {
              ctx.fillText(headerText, headerX, currentY + 22);
            }

            // Ligne verticale entre les colonnes si bordures activées
            if (element.showBorders !== false && index < tableData.headers.length - 1) {
              ctx.strokeStyle = tableStyleData.header_border ? `rgb(${tableStyleData.header_border.join(',')})` : '#e2e8f0';
              ctx.lineWidth = tableStyleData.border_width || 0.5;
              const lineX = tableX + columnWidths.slice(0, index + 1).reduce((sum, w) => sum + w, 0);
              ctx.beginPath();
              ctx.moveTo(lineX, currentY);
              ctx.lineTo(lineX, currentY + headerHeight);
              ctx.stroke();
            }
          });

          currentY += headerHeight;
        }

        // Lignes de données
        ctx.font = `${fontStyle}${fontWeight} ${rowFontSize}px ${fontFamily}`;

        tableData.rows.forEach((row, rowIndex) => {
          const rowHeight = 24; // Augmenté pour plus d'espacement
          const isEvenRow = rowIndex % 2 === 0;

          // Fond alterné des lignes avec dégradé subtil
          let bgColor;
          if (element.evenRowBg && element.oddRowBg) {
            // Utiliser les couleurs configurées depuis PropertiesPanel
            bgColor = isEvenRow ? element.evenRowBg : element.oddRowBg;
          } else {
            // Couleurs par défaut avec dégradé subtil
            bgColor = isEvenRow ?
              `rgb(${tableStyleData.alt_row_bg.join(',')})` :
              '#ffffff';
          }

          // Appliquer un dégradé subtil aux lignes paires pour plus de modernité
          if (isEvenRow && !element.evenRowBg) {
            const gradient = ctx.createLinearGradient(tableX, currentY, tableX, currentY + rowHeight);
            gradient.addColorStop(0, `rgb(${tableStyleData.alt_row_bg.join(',')})`);
            gradient.addColorStop(1, `rgba(${tableStyleData.alt_row_bg.join(',')}, 0.8)`);
            ctx.fillStyle = gradient;
          } else {
            ctx.fillStyle = bgColor;
          }

          // Dessiner le fond de la ligne avec coins légèrement arrondis
          const rowRadius = element.borderRadius ? Math.min(element.borderRadius * 0.3, 2) : 1;
          ctx.beginPath();
          ctx.roundRect(tableX, currentY, tableWidth, rowHeight, rowRadius);
          ctx.fill();

          // Bordure de la ligne si activée, avec style moderne
          if (element.showBorders !== false) {
            ctx.strokeStyle = `rgb(${tableStyleData.row_border.join(',')})`;
            ctx.lineWidth = tableStyleData.border_width || 0.5;
            ctx.beginPath();
            ctx.roundRect(tableX, currentY, tableWidth, rowHeight, rowRadius);
            ctx.stroke();
          }

          // Couleur du texte des cellules (configurable)
          const rowTextColor = isEvenRow && element.evenRowTextColor ?
            element.evenRowTextColor :
            (!isEvenRow && element.oddRowTextColor ? element.oddRowTextColor : tableStyleData.rowTextColor);
          ctx.fillStyle = rowTextColor;
          ctx.textAlign = 'center';

          row.forEach((cell, cellIndex) => {
            let cellText = String(cell);

            // Appliquer la transformation de texte
            if (textTransform === 'uppercase') {
              cellText = cellText.toUpperCase();
            } else if (textTransform === 'lowercase') {
              cellText = cellText.toLowerCase();
            } else if (textTransform === 'capitalize') {
              cellText = cellText.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Calculer la position X centrée dans la colonne
            let cellX;
            if (cellIndex === 0) {
              cellX = tableX + (columnWidths[0] / 2);
            } else {
              const previousWidth = columnWidths.slice(0, cellIndex).reduce((sum, w) => sum + w, 0);
              cellX = tableX + previousWidth + (columnWidths[cellIndex] / 2);
            }
            const cellY = currentY + rowHeight / 2 + (rowFontSize * 0.35); // Centrage vertical amélioré

            // Gestion spéciale pour les images (placeholder)
            if (cellText.startsWith('data:image') || cellText.includes('.jpg') || cellText.includes('.png')) {
              // Dessiner un placeholder pour l'image avec style moderne
              const imgSize = 16;
              const imgX = cellX - imgSize / 2;
              const imgY = currentY + (rowHeight - imgSize) / 2;

              // Fond du placeholder avec coins arrondis
              ctx.fillStyle = '#f3f4f6';
              ctx.beginPath();
              ctx.roundRect(imgX, imgY, imgSize, imgSize, 2);
              ctx.fill();

              // Bordure du placeholder
              ctx.strokeStyle = '#d1d5db';
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.roundRect(imgX, imgY, imgSize, imgSize, 2);
              ctx.stroke();

              // Icône image
              ctx.fillStyle = '#6b7280';
              ctx.font = '10px Arial';
              ctx.textAlign = 'center';
              ctx.fillText('🖼️', cellX, imgY + imgSize - 2);
            } else {
              // Texte normal avec espacement des lettres si défini
              ctx.font = `${fontStyle}${fontWeight} ${rowFontSize}px ${fontFamily}`;
              ctx.fillStyle = rowTextColor;
              ctx.textAlign = 'center';

              if (letterSpacing > 0) {
                let charX = cellX - (cellText.length * letterSpacing) / 2;
                for (let i = 0; i < cellText.length; i++) {
                  ctx.fillText(cellText[i], charX, cellY);
                  charX += ctx.measureText(cellText[i]).width + letterSpacing;
                }
              } else {
                ctx.fillText(cellText, cellX, cellY);
              }
            }

            // Ligne verticale entre les colonnes si bordures activées (style moderne)
            if (element.showBorders !== false && cellIndex < row.length - 1) {
              ctx.strokeStyle = `rgb(${tableStyleData.row_border.join(',')})`;
              ctx.lineWidth = tableStyleData.border_width || 0.3;
              const lineX = tableX + columnWidths.slice(0, cellIndex + 1).reduce((sum, w) => sum + w, 0);
              ctx.beginPath();
              ctx.moveTo(lineX, currentY + 2);
              ctx.lineTo(lineX, currentY + rowHeight - 2);
              ctx.stroke();
            }
          });

          currentY += rowHeight;
        });

        // Lignes de totaux - maintenant alignées avec les colonnes
        const totals = tableData.totals;
        if (Object.keys(totals).length > 0) {
          currentY += 8; // Espace avant les totaux

          Object.entries(totals).forEach(([key, value]) => {
            const totalHeight = 22; // Augmenté pour plus d'espacement

            // Fond du total avec dégradé moderne
            const isTotalRow = key === 'total';
            if (isTotalRow) {
              // Dégradé spécial pour le total final
              const gradient = ctx.createLinearGradient(tableX, currentY, tableX, currentY + totalHeight);
              gradient.addColorStop(0, `rgb(${tableStyleData.header_bg.join(',')})`);
              gradient.addColorStop(1, `rgba(${tableStyleData.header_bg.map(c => Math.max(0, c - 20)).join(',')}, 0.9)`);
              ctx.fillStyle = gradient;
            } else {
              // Fond normal pour les autres totaux
              ctx.fillStyle = `rgb(${tableStyleData.header_bg.join(',')})`;
            }

            // Dessiner le fond avec coins arrondis
            const totalRadius = element.borderRadius ? Math.min(element.borderRadius * 0.5, 3) : 2;
            ctx.beginPath();
            ctx.roundRect(tableX, currentY, tableWidth, totalHeight, totalRadius);
            ctx.fill();

            // Bordure du total si activée
            if (element.showBorders !== false) {
              ctx.strokeStyle = `rgb(${tableStyleData.header_border.join(',')})`;
              ctx.lineWidth = tableStyleData.border_width || 1;
              ctx.beginPath();
              ctx.roundRect(tableX, currentY, tableWidth, totalHeight, totalRadius);
              ctx.stroke();
            }

            // Style de texte spécial pour le total
            const totalFontWeight = isTotalRow ? 'bold' : fontWeight;
            const totalFontSize = isTotalRow ? headerFontSize + 1 : headerFontSize;
            ctx.font = `${fontStyle}${totalFontWeight} ${totalFontSize}px ${fontFamily}`;
            ctx.fillStyle = tableStyleData.headerTextColor;

            // Libellé du total (dans la première colonne)
            let label = key === 'subtotal' ? 'Sous-total' :
                       key === 'shipping' ? 'Frais de port' :
                       key === 'tax' ? 'TVA' :
                       key === 'discount' ? 'Remise' :
                       key === 'total' ? 'TOTAL' : key;

            // Appliquer la transformation de texte au libellé
            if (textTransform === 'uppercase') {
              label = label.toUpperCase();
            } else if (textTransform === 'lowercase') {
              label = label.toLowerCase();
            } else if (textTransform === 'capitalize') {
              label = label.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Positionner le libellé dans la première colonne (Produit/Nom)
            const labelX = tableX + (columnWidths[0] / 2);
            const labelY = currentY + totalHeight / 2 + (totalFontSize * 0.35);
            ctx.textAlign = 'center';

            if (letterSpacing > 0) {
              let charX = labelX - (label.length * letterSpacing) / 2;
              for (let i = 0; i < label.length; i++) {
                ctx.fillText(label[i], charX, labelY);
                charX += ctx.measureText(label[i]).width + letterSpacing;
              }
            } else {
              ctx.fillText(label, labelX, labelY);
            }

            // Valeur du total (dans la dernière colonne Total)
            let valueText = String(value);

            // Appliquer la transformation de texte à la valeur
            if (textTransform === 'uppercase') {
              valueText = valueText.toUpperCase();
            } else if (textTransform === 'lowercase') {
              valueText = valueText.toLowerCase();
            } else if (textTransform === 'capitalize') {
              valueText = valueText.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Positionner la valeur dans la colonne Total (dernière colonne)
            const totalColumnIndex = tableData.headers.indexOf('Total');
            if (totalColumnIndex !== -1) {
              let valueX;
              if (totalColumnIndex === 0) {
                valueX = tableX + (columnWidths[0] / 2);
              } else {
                const previousWidth = columnWidths.slice(0, totalColumnIndex).reduce((sum, w) => sum + w, 0);
                valueX = tableX + previousWidth + (columnWidths[totalColumnIndex] / 2);
              }
              const valueY = currentY + totalHeight / 2 + (totalFontSize * 0.35);
              ctx.textAlign = 'center';

              if (letterSpacing > 0) {
                let charX = valueX - (valueText.length * letterSpacing) / 2;
                for (let i = 0; i < valueText.length; i++) {
                  ctx.fillText(valueText[i], charX, valueY);
                  charX += ctx.measureText(valueText[i]).width + letterSpacing;
                }
              } else {
                ctx.fillText(valueText, valueX, valueY);
              }
            }

            // Lignes verticales entre les colonnes pour les totaux
            if (element.showBorders !== false) {
              ctx.strokeStyle = `rgb(${tableStyleData.header_border.join(',')})`;
              ctx.lineWidth = tableStyleData.border_width || 0.5;

              for (let i = 0; i < numColumns - 1; i++) {
                const lineX = tableX + columnWidths.slice(0, i + 1).reduce((sum, w) => sum + w, 0);
                ctx.beginPath();
                ctx.moveTo(lineX, currentY);
                ctx.lineTo(lineX, currentY + totalHeight);
                ctx.stroke();
              }
            }

            currentY += totalHeight;
          });
        }

        // Restaurer l'opacité, les filtres et les ombres
        ctx.globalAlpha = 1;
        ctx.filter = 'none';
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Restaurer la transformation si elle était appliquée
        if (element.rotation || element.scaleX || element.scaleY) {
          ctx.restore();
        }
      } else {
        console.log(`PDFEditor: UNKNOWN ELEMENT TYPE "${element.type}" for element ${element.id} - rendering as generic red rectangle`);
        console.log(`PDFEditor: Detailed properties for ${element.type}:`, JSON.stringify(element, null, 2));

        ctx.fillStyle = '#ff6b6b'; // Rouge pour indiquer un élément non rendu
        const genericX = element.x || 10;
        const genericY = element.y || 10;
        const genericWidth = element.width || 100;
        const genericHeight = element.height || 30;

        // Dessiner un rectangle rouge avec le type d'élément
        ctx.fillRect(genericX, genericY, genericWidth, genericHeight);
        ctx.strokeStyle = '#ff0000';
        ctx.lineWidth = 2;
        ctx.strokeRect(genericX, genericY, genericWidth, genericHeight);

        // Afficher le type d'élément
        ctx.fillStyle = '#ffffff';
        ctx.font = '12px Arial';
        ctx.fillText(element.type, genericX + 5, genericY + 20);
      }
    });
  };

  // Re-rendre à chaque changement
  useEffect(() => {
    renderCanvas();
  }, [elements, zoom, showGrid, selectedElement]);

  // S'assurer que le canvas se rend au montage initial
  useEffect(() => {
    renderCanvas();
  }, []);

  // Mettre à jour les éléments quand initialElements change
  useEffect(() => {
    if (initialElements && initialElements.length > 0) {
      setElements(initialElements);
      setHistory([initialElements]);
      setHistoryIndex(0);
    } else {
      // Si pas d'éléments initiaux, initialiser avec un tableau vide
      setElements([]);
      setHistory([[]]);
      setHistoryIndex(0);
    }
  }, [initialElements]);

  // Écouter les événements globaux pour le bouton aperçu du header
  useEffect(() => {
    const handleGlobalPreview = (event) => {
      if (event.type === 'pdfBuilderPreview') {
        handlePreview();
      }
    };

    // Écouter l'événement personnalisé
    document.addEventListener('pdfBuilderPreview', handleGlobalPreview);

    // Exposer la fonction globalement pour le bouton du header
    window.pdfBuilderPro = window.pdfBuilderPro || {};
    window.pdfBuilderPro.triggerPreview = handlePreview;

    // Nettoyer les écouteurs
    return () => {
      document.removeEventListener('pdfBuilderPreview', handleGlobalPreview);
    };
  }, [elements]);

  // Fonction utilitaire pour rendre un placeholder élégant pour le logo
  const renderLogoPlaceholder = (ctx, x, y, width, height, element) => {
    // Fond du placeholder avec un dégradé subtil
    const gradient = ctx.createLinearGradient(x, y, x + width, y + height);
    gradient.addColorStop(0, '#f8fafc');
    gradient.addColorStop(1, '#e2e8f0');
    ctx.fillStyle = gradient;
    ctx.fillRect(x, y, width, height);

    // Bordure subtile
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 1;
    ctx.strokeRect(x, y, width, height);

    // Icône de logo stylisée (simplifiée)
    const centerX = x + width / 2;
    const centerY = y + height / 2;
    const iconSize = Math.min(width, height) * 0.4;

    // Cercle principal
    ctx.fillStyle = '#64748b';
    ctx.beginPath();
    ctx.arc(centerX, centerY, iconSize / 2, 0, 2 * Math.PI);
    ctx.fill();

    // Lettres "LOGO" stylisées
    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${iconSize * 0.25}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('LOGO', centerX, centerY);

    // Texte d'aide en bas
    ctx.fillStyle = '#94a3b8';
    ctx.font = `${Math.max(8, iconSize * 0.08)}px Arial`;
    ctx.fillText('Cliquez pour ajouter', centerX, y + height - 8);
    ctx.fillText('votre logo', centerX, y + height - 2);
  };

  return (
    <div className="pdf-editor">
      {/* Header du template */}
      <TemplateHeader
        templateName={templateName}
        isNew={isNew}
        onSave={handleSave}
        onCreateNew={handleCreateNew}
        onPreview={handlePreview}
      />

      {/* Toolbar principale */}
      <Toolbar
        selectedTool={selectedTool}
        onToolSelect={handleToolSelect}
        zoom={zoom}
        onZoomChange={handleZoomChange}
        showGrid={showGrid}
        onShowGridChange={handleShowGridChange}
        snapToGrid={snapToGrid}
        onSnapToGridChange={handleSnapToGridChange}
        onUndo={handleUndo}
        onRedo={handleRedo}
        canUndo={canUndo}
        canRedo={canRedo}
      />

      {/* Zone de travail principale */}
      <div className="editor-workspace">
        {/* Bibliothèque d'éléments */}
        {showElementLibrary && (
          <div className="element-library-panel">
            <ElementLibrary
              onAddElement={handleAddElement}
              selectedTool={selectedTool}
              onToolSelect={setSelectedTool}
            />
          </div>
        )}

        {/* Canvas principal */}
        <div className="canvas-container">
          <canvas
            ref={canvasRef}
            className="pdf-canvas"
            width={595}
            height={842}
            style={{
              transform: `scale(${zoom})`,
              transformOrigin: 'top left',
              cursor: selectedTool === 'select' ? 'default' : 'crosshair'
            }}
            onClick={handleCanvasClick}
            onMouseDown={handleCanvasMouseDown}
            onMouseMove={handleCanvasMouseMove}
            onMouseUp={handleCanvasMouseUp}
            onMouseLeave={handleCanvasMouseUp}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
          />
        </div>

        {/* Panel des propriétés */}
        {showPropertiesPanel && selectedElement && (
          <div className="properties-panel-container">
            <PropertiesPanel
              selectedElements={selectedElement ? [selectedElement] : []}
              elements={elements}
              onPropertyChange={(elementId, property, value) => {
                handleElementUpdate(elementId, { [property]: value });
              }}
              onBatchUpdate={(updates) => {
                // Handle batch updates if needed
                updates.forEach(update => {
                  handleElementUpdate(update.elementId, update.properties);
                });
              }}
            />
          </div>
        )}
      </div>

      {/* Barre d'outils secondaire */}
      <div className="editor-toolbar-secondary">
        <button
          onClick={() => setShowElementLibrary(!showElementLibrary)}
          className={`tool-btn ${showElementLibrary ? 'active' : ''}`}
        >
          📚 Bibliothèque
        </button>
        <button
          onClick={() => setShowPropertiesPanel(!showPropertiesPanel)}
          className={`tool-btn ${showPropertiesPanel ? 'active' : ''}`}
        >
          ⚙️ Propriétés
        </button>
        <span className="status-info">
          Éléments: {elements.length} | Sélectionné: {selectedElement ? 'Oui' : 'Non'}
        </span>
      </div>

      {/* Modal d'aperçu */}
      <PreviewModal />
    </div>
  );
};

export const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  return (
    <PreviewProvider>
      <PDFEditorContent
        initialElements={initialElements}
        onSave={onSave}
        templateName={templateName}
        isNew={isNew}
      />
    </PreviewProvider>
  );
};
