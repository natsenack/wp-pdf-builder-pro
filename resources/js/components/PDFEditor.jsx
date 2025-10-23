import React, { useState, useRef, useEffect } from 'react';
import { Toolbar } from './Toolbar';
import PreviewModal from './preview-system/components/PreviewModal';
import { PreviewProvider } from './preview-system/context/PreviewProvider';
import { usePreviewContext } from './preview-system/context/PreviewContext';
import ElementLibrary from './ElementLibrary';
import PropertiesPanel from './PropertiesPanel';
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
  const [selectedElement, setSelectedElement] = useState(null);

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

  const canUndo = historyIndex > 0;
  const canRedo = historyIndex < history.length - 1;

  // Gestionnaire d'aperçu
  const handlePreview = () => {
    openPreview('canvas', null, { elements });
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
    setSelectedElement(newElement.id);
  };

  // Gestionnaire de sélection d'élément
  const handleElementSelect = (elementId) => {
    setSelectedElement(elementId);
    if (elementId) {
      setShowPropertiesPanel(true); // Afficher automatiquement le panneau des propriétés
    }
  };

  // Gestionnaire de mise à jour des propriétés d'un élément
  const handleElementUpdate = (elementId, newProperties) => {
    const newElements = elements.map(element =>
      element.id === elementId ? { ...element, ...newProperties } : element
    );
    handleElementsChange(newElements);
  };

  // Gestionnaire de suppression d'élément
  const handleElementDelete = (elementId) => {
    const newElements = elements.filter(element => element.id !== elementId);
    handleElementsChange(newElements);
    if (selectedElement === elementId) {
      setSelectedElement(null);
    }
  };

  // Gestionnaire de sauvegarde des éléments
  const handleElementsChange = (newElements) => {
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(newElements);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
    setElements(newElements);

    // Sauvegarder automatiquement si callback fourni
    if (onSave) {
      onSave(newElements);
    }
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
        setSelectedElement(newElement.id);
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
      setSelectedElement(newElement.id);
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
      const element = elements.find(el => el.id === selectedElement);
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
      setSelectedElement(clickedElement.id);
      setShowPropertiesPanel(true); // Afficher automatiquement le panneau des propriétés
      // Démarrer le drag
      setIsDragging(true);
      setDragElement(clickedElement);
      setDragStart({ x: x, y: y }); // Position absolue du clic
    } else {
      // Désélectionner si on clique dans le vide
      setSelectedElement(null);
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
      const element = elements.find(el => el.id === selectedElement);
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
          handleElementUpdate(selectedElement, {
            x: newX,
            y: newY,
            radius: Math.max(10, newRadius)
          });
        } else {
          handleElementUpdate(selectedElement, {
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
    const canvas = canvasRef.current;
    if (!canvas) {
      console.log('PDFEditor renderCanvas: No canvas ref');
      return;
    }

    console.log('PDFEditor renderCanvas called - canvas dimensions:', canvas.width, 'x', canvas.height, '- elements count:', elements.length);
    if (elements.length > 0) {
      console.log('PDFEditor rendering elements:', elements);
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

    // Dessiner les éléments
    elements.forEach((element, index) => {
      console.log(`PDFEditor rendering element ${index}:`, element);

      // Mettre en évidence l'élément sélectionné
      if (selectedElement === element.id) {
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
      if (selectedElement === element.id && (element.type === 'rectangle' || element.type === 'circle' ||
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
      console.log(`PDFEditor: Starting to render element ${index} of type "${element.type}" with id ${element.id}`);

      if (element.type === 'text') {
        console.log(`PDFEditor: Rendering TEXT element ${element.id} at (${element.x}, ${element.y}): "${element.text}"`);
        ctx.fillStyle = element.color || '#000000';
        const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
        ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
        const textX = element.x || 10;
        const textY = element.y || 30;
        console.log(`PDFEditor drawing text at (${textX}, ${textY}): "${element.text || 'Texte'}"`);
        ctx.fillText(element.text || 'Texte', textX, textY);
      } else if (element.type === 'rectangle') {
        ctx.fillStyle = element.backgroundColor || '#ffffff';
        const rectX = element.x || 10;
        const rectY = element.y || 10;
        const rectWidth = element.width || 100;
        const rectHeight = element.height || 50;
        console.log(`PDFEditor drawing rectangle at (${rectX}, ${rectY}) size (${rectWidth}, ${rectHeight})`);
        ctx.fillRect(rectX, rectY, rectWidth, rectHeight);
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.strokeRect(rectX, rectY, rectWidth, rectHeight);
        }
      } else if (element.type === 'circle') {
        console.log(`PDFEditor: Rendering CIRCLE element ${element.id} at (${element.x || 10}, ${element.y || 10}) radius ${element.radius || 25}`);
        ctx.fillStyle = element.backgroundColor || '#ffffff';
        ctx.beginPath();
        ctx.arc(element.x || 10, element.y || 10, element.radius || 25, 0, 2 * Math.PI);
        ctx.fill();
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.stroke();
        }
      } else if (element.type === 'company_logo') {
        console.log(`PDFEditor: Rendering COMPANY_LOGO element ${element.id} at (${element.x || 10}, ${element.y || 10})`);
        // Rendu spécifique pour le logo de l'entreprise
        const imageUrl = element.src || element.imageUrl;
        if (imageUrl) {
          const img = new Image();
          img.onload = () => {
            const imgX = element.x || 10;
            const imgY = element.y || 10;
            const imgWidth = element.width || 120;
            const imgHeight = element.height || 90;

            // Calculer les dimensions pour maintenir le ratio d'aspect
            const aspectRatio = img.width / img.height;
            let drawWidth = imgWidth;
            let drawHeight = imgHeight;
            let drawX = imgX;
            let drawY = imgY;

            if (element.objectFit === 'cover') {
              if (imgWidth / imgHeight > aspectRatio) {
                drawHeight = imgHeight;
                drawWidth = imgHeight * aspectRatio;
                drawX = imgX + (imgWidth - drawWidth) / 2;
              } else {
                drawWidth = imgWidth;
                drawHeight = imgWidth / aspectRatio;
                drawY = imgY + (imgHeight - drawHeight) / 2;
              }
            }

            ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
            console.log(`PDFEditor drawing company logo at (${drawX}, ${drawY}) size (${drawWidth}, ${drawHeight})`);
          };
          img.src = imageUrl;
        }
      } else if (element.type === 'dynamic-text') {
        console.log(`PDFEditor: Rendering DYNAMIC_TEXT element ${element.id} at (${element.x || 10}, ${element.y || 30}): "${element.customContent || element.text || 'Texte'}"`);
        // Rendu spécifique pour le texte dynamique
        ctx.fillStyle = element.color || '#333333';
        const fontWeight = element.fontWeight || 'normal';
        ctx.font = `${fontWeight} ${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'left';

        const textX = element.x || 10;
        const textY = element.y || 30;
        let displayText = element.text || 'Texte';

        // Utiliser customContent si disponible
        if (element.customContent) {
          displayText = element.customContent;
        }

        // Pour les templates prédéfinis
        if (element.template === 'signature_line') {
          displayText = 'Signature: _______________________________';
        }

        ctx.fillText(displayText, textX, textY + (element.height || 30) / 2);
        console.log(`PDFEditor drawing dynamic text at (${textX}, ${textY}): "${displayText}"`);
      } else if (element.type === 'order_number') {
        console.log(`PDFEditor: Rendering ORDER_NUMBER element ${element.id} at (${element.x || 10}, ${element.y || 30})`);
        // Rendu spécifique pour le numéro de commande
        ctx.fillStyle = element.color || '#333333';
        const fontWeight = element.fontWeight || 'bold';
        ctx.font = `${fontWeight} ${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'right';

        const textX = element.x || 10;
        const textY = element.y || 30;

        // Afficher le label si demandé
        if (element.showLabel && element.labelText) {
          ctx.textAlign = 'left';
          ctx.fillText(element.labelText, textX, textY + (element.height || 40) / 2);
        }

        // Afficher le numéro formaté
        const formattedText = element.format || 'Commande #{order_number}';
        ctx.textAlign = element.textAlign || 'right';
        ctx.fillText(formattedText, textX + (element.width || 270), textY + (element.height || 40) / 2);
        console.log(`PDFEditor drawing order number: "${formattedText}"`);
      } else if (element.type === 'document_type') {
        console.log(`PDFEditor: Rendering DOCUMENT_TYPE element ${element.id} at (${element.x || 10}, ${element.y || 30}): "FACTURE"`);
        // Rendu spécifique pour le type de document
        ctx.fillStyle = element.color || '#1e293b';
        const fontWeight = element.fontWeight || 'bold';
        ctx.font = `${fontWeight} ${element.fontSize || 18}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'center';

        const textX = element.x || 10;
        const textY = element.y || 30;
        const displayText = 'FACTURE';

        ctx.fillText(displayText, textX + (element.width || 120) / 2, textY + (element.height || 50) / 2);
        console.log(`PDFEditor drawing document type: "${displayText}"`);
      } else if (element.type === 'line') {
        console.log(`PDFEditor: Rendering LINE element ${element.id} at (${element.x || 10}, ${element.y || 110}) width ${element.width || 20}`);
        // Rendu spécifique pour la ligne
        ctx.strokeStyle = element.lineColor || '#64748b';
        ctx.lineWidth = element.lineWidth || 2;
        ctx.beginPath();
        const lineX = element.x || 10;
        const lineY = element.y || 110;
        const lineWidth = element.width || 20;
        ctx.moveTo(lineX, lineY + (element.height || 12) / 2);
        ctx.lineTo(lineX + lineWidth, lineY + (element.height || 12) / 2);
        ctx.stroke();
        console.log(`PDFEditor drawing line at (${lineX}, ${lineY}) width: ${lineWidth}`);
      } else if (element.type === 'mentions') {
        console.log(`PDFEditor: Rendering MENTIONS element ${element.id} at (${element.x || 10}, ${element.y || 10})`);
        // Rendu spécifique pour les mentions légales
        ctx.fillStyle = element.color || '#666666';
        ctx.font = `${element.fontSize || 8}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'center';

        const textX = element.x || 10;
        const textY = element.y || 10;
        const parts = [];

        if (element.showEmail) parts.push('email@company.com');
        if (element.showPhone) parts.push('+33 1 23 45 67 89');
        if (element.showSiret) parts.push('SIRET: 123 456 789 00012');

        const displayText = parts.join(element.separator || ' • ');
        ctx.fillText(displayText, textX + (element.width || 300) / 2, textY + (element.height || 40) / 2);
        console.log(`PDFEditor drawing mentions: "${displayText}"`);
      } else if (element.type === 'customer_info' || element.type === 'company_info') {
        console.log(`PDFEditor: Rendering ${element.type.toUpperCase()} element ${element.id} at (${element.x || 10}, ${element.y || 10}) with ${element.fields?.length || 0} fields`);
        // Rendu spécifique pour les informations client/entreprise
        ctx.fillStyle = element.color || '#1e293b';
        ctx.font = `${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = 'left';

        const textX = element.x || 10;
        let currentY = element.y || 10;

        if (element.fields && Array.isArray(element.fields)) {
          element.fields.forEach(field => {
            let fieldText = field;
            if (element.showLabels !== false) {
              fieldText = `${field.charAt(0).toUpperCase() + field.slice(1)}:`;
            }

            // Valeurs d'exemple selon le type
            if (element.type === 'customer_info') {
              const sampleData = {
                name: 'Jean Dupont',
                phone: '+33 6 12 34 56 78',
                address: '123 Rue de la Paix, 75001 Paris',
                email: 'jean.dupont@email.com'
              };
              if (sampleData[field]) {
                fieldText += element.showLabels !== false ? ` ${sampleData[field]}` : sampleData[field];
              }
            } else if (element.type === 'company_info') {
              const sampleData = {
                name: 'Ma Société SARL',
                address: '456 Avenue des Champs, 75008 Paris',
                phone: '+33 1 98 76 54 32',
                rcs: 'RCS Paris 123 456 789'
              };
              if (sampleData[field]) {
                fieldText += element.showLabels !== false ? ` ${sampleData[field]}` : sampleData[field];
              }
            }

            ctx.fillText(fieldText, textX, currentY + 15);
            currentY += (element.spacing || 5) + 15;
          });
        }
        console.log(`PDFEditor drawing ${element.type} with ${element.fields?.length || 0} fields`);
      } else if (element.type === 'product_table') {
        console.log(`PDFEditor: Rendering PRODUCT_TABLE element ${element.id} at (${element.x || 30}, ${element.y || 270})`);
        // Rendu basique pour le tableau de produits
        ctx.fillStyle = element.color || '#475569';
        ctx.font = `${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;

        const tableX = element.x || 30;
        let currentY = element.y || 270;

        // En-têtes du tableau
        if (element.showHeaders && element.headers) {
          ctx.font = `bold ${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
          let headerX = tableX;
          element.headers.forEach(header => {
            ctx.fillText(header, headerX, currentY + 15);
            headerX += 150; // Espacement fixe pour l'exemple
          });
          currentY += 25;
        }

        // Lignes d'exemple
        ctx.font = `${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
        const sampleRows = [
          ['Produit A', '2', '25.00 €'],
          ['Produit B', '1', '15.50 €'],
          ['Sous-total', '', '65.50 €']
        ];

        sampleRows.forEach((row, index) => {
          let cellX = tableX;
          const bgColor = index % 2 === 0 ? (element.evenRowBg || '#ffffff') : (element.oddRowBg || '#ebebeb');
          ctx.fillStyle = bgColor;
          ctx.fillRect(tableX, currentY - 5, element.width || 530, 20);
          ctx.fillStyle = index % 2 === 0 ? element.color : (element.oddRowTextColor || '#666666');

          row.forEach(cell => {
            ctx.fillText(cell, cellX, currentY + 15);
            cellX += 150;
          });
          currentY += 25;
        });

        console.log(`PDFEditor drawing product table with ${sampleRows.length} rows`);
      } else {
        console.log(`PDFEditor: UNKNOWN ELEMENT TYPE "${element.type}" for element ${element.id} - rendering as generic red rectangle`);
        console.log(`PDFEditor: Detailed properties for ${element.type}:`, JSON.stringify(element, null, 2));

        ctx.fillStyle = '#ff6b6b'; // Rouge pour indiquer un élément non rendu
        const genericX = element.x || 10;
        const genericY = element.y || 10;
        const genericWidth = element.width || 100;
        const genericHeight = element.height || 30;
        console.log(`PDFEditor drawing generic element (${element.type}) at (${genericX}, ${genericY}) size (${genericWidth}, ${genericHeight})`);

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
    console.log('PDFEditor useEffect triggered - initialElements:', initialElements, 'length:', initialElements ? initialElements.length : 'undefined');
    if (initialElements && initialElements.length > 0) {
      console.log('PDFEditor: Setting elements from initialElements:', initialElements.length, 'elements');
      console.log('PDFEditor: Elements data:', initialElements);
      setElements(initialElements);
      setHistory([initialElements]);
      setHistoryIndex(0);
    } else {
      console.log('PDFEditor: No initialElements provided or empty array');
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

  return (
    <div className="pdf-editor">
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
        onPreview={handlePreview}
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
