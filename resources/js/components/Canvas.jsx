import React, { useRef, useEffect, useCallback, useState, useMemo } from 'react';
import { useGlobalSettings } from '../hooks/useGlobalSettings';
import { renderCanvas, renderElement } from '../utils/canvasRenderer';

const Canvas = ({
  elements,
  selectedElements,
  tool,
  zoom,
  showGrid,
  snapToGrid,
  gridSize,
  canvasWidth,
  canvasHeight,
  onElementSelect,
  onElementUpdate,
  onElementRemove,
  onContextMenu,
  selection,
  zoomHook
}) => {
  const canvasRef = useRef(null);
  const contextRef = useRef(null);
  const { settings } = useGlobalSettings();

  // Initialiser le contexte canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (canvas) {
      contextRef.current = canvas.getContext('2d');
      // Activer l'anti-aliasing pour de meilleurs rendus
      contextRef.current.imageSmoothingEnabled = true;
      contextRef.current.imageSmoothingQuality = 'high';
    }
  }, []);

  // Fonction pour dessiner la grille
  const drawGrid = useCallback((ctx) => {
    if (!showGrid) return;

    ctx.save();
    ctx.strokeStyle = '#f1f3f4';
    ctx.lineWidth = 1;

    const step = gridSize;

    // Lignes verticales
    for (let x = 0; x <= canvasWidth; x += step) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, canvasHeight);
      ctx.stroke();
    }

    // Lignes horizontales
    for (let y = 0; y <= canvasHeight; y += step) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(canvasWidth, y);
      ctx.stroke();
    }

    ctx.restore();
  }, [showGrid, gridSize, canvasWidth, canvasHeight]);

  // Fonction pour dessiner un élément
  const drawElement = useCallback((ctx, element) => {
    ctx.save();

    // Positionner l'élément
    ctx.translate(element.x, element.y);

    // Style de base
    ctx.fillStyle = element.backgroundColor || '#ffffff';
    ctx.strokeStyle = element.borderColor || '#6b7280';
    ctx.lineWidth = element.borderWidth || 1;

    // Dessiner selon le type d'élément
    switch (element.type) {
      case 'rectangle':
        ctx.fillRect(0, 0, element.width, element.height);
        if (element.borderWidth > 0) {
          ctx.strokeRect(0, 0, element.width, element.height);
        }
        break;

      case 'circle':
        ctx.beginPath();
        ctx.arc(element.width / 2, element.height / 2, Math.min(element.width, element.height) / 2, 0, 2 * Math.PI);
        ctx.fill();
        if (element.borderWidth > 0) {
          ctx.stroke();
        }
        break;

      case 'text':
        ctx.fillStyle = element.color || '#1e293b';
        ctx.font = `${element.fontSize || 14}px ${element.fontFamily || 'Arial'}`;
        ctx.textAlign = element.textAlign || 'left';
        ctx.textBaseline = 'top';
        ctx.fillText(element.content || 'Texte', 0, 0);
        break;

      case 'line':
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(element.width, element.height);
        ctx.stroke();
        break;

      default:
        // Rectangle par défaut
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
    }

    // Dessiner la sélection si les bordures sont activées
    if (selectedElements.includes(element.id) && settings.showResizeZones) {
      ctx.strokeStyle = settings.selectionBorderColor;
      ctx.lineWidth = settings.selectionBorderWidth;
      ctx.setLineDash([5, 5]);
      const spacing = settings.selectionBorderSpacing;
      ctx.strokeRect(-spacing, -spacing, element.width + (spacing * 2), element.height + (spacing * 2));
      ctx.setLineDash([]);

      // Poignées de redimensionnement si activées
      if (settings.showResizeHandles) {
        ctx.fillStyle = settings.resizeHandleColor;
        const handleSize = settings.resizeHandleSize / 2;
        const handles = [
          [0, 0], [element.width, 0], [element.width, element.height], [0, element.height],
          [element.width / 2, 0], [element.width, element.height / 2],
          [element.width / 2, element.height], [0, element.height / 2]
        ];

        handles.forEach(([x, y]) => {
          ctx.fillRect(x - handleSize/2, y - handleSize/2, handleSize, handleSize);
          // Bordure blanche des poignées
          ctx.strokeStyle = settings.resizeHandleBorderColor;
          ctx.lineWidth = 1;
          ctx.strokeRect(x - handleSize/2, y - handleSize/2, handleSize, handleSize);
        });
      }
    }

    ctx.restore();
  }, [selectedElements, settings]);

  // Fonction principale de rendu
  const renderCanvas = useCallback(() => {
    const canvas = canvasRef.current;
    const ctx = contextRef.current;
    if (!canvas || !ctx) return;

    // Effacer le canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Appliquer le zoom
    ctx.save();
    ctx.scale(zoom, zoom);

    // Dessiner le fond
    if (settings.canvasShowTransparency) {
      // Fond transparent avec motif de damier
      const patternSize = 20;
      const patternCanvas = document.createElement('canvas');
      patternCanvas.width = patternSize * 2;
      patternCanvas.height = patternSize * 2;
      const patternCtx = patternCanvas.getContext('2d');

      // Créer le motif de damier
      patternCtx.fillStyle = '#ffffff';
      patternCtx.fillRect(0, 0, patternSize * 2, patternSize * 2);
      patternCtx.fillStyle = '#f0f0f0';
      patternCtx.fillRect(0, 0, patternSize, patternSize);
      patternCtx.fillRect(patternSize, patternSize, patternSize, patternSize);

      const pattern = ctx.createPattern(patternCanvas, 'repeat');
      ctx.fillStyle = pattern;
      ctx.fillRect(0, 0, canvasWidth, canvasHeight);
    } else {
      // Fond uni avec la couleur choisie
      ctx.fillStyle = settings.canvasBackgroundColor || '#ffffff';
      ctx.fillRect(0, 0, canvasWidth, canvasHeight);
    }

    // Dessiner la grille
    drawGrid(ctx);

    // Dessiner tous les éléments
    elements.forEach(element => {
      drawElement(ctx, element);
    });

    ctx.restore();
  }, [elements, zoom, canvasWidth, canvasHeight, drawGrid, drawElement]);

  // Redessiner à chaque changement
  useEffect(() => {
    renderCanvas();
  }, [renderCanvas]);

  // Optimiser le rendu avec useMemo pour éviter les re-renders inutiles
  const memoizedCanvasStyle = useMemo(() => ({
    border: '1px solid #ccc',
    cursor: getCursor(),
    backgroundColor: settings.canvasShowTransparency ? 'transparent' : (settings.canvasBackgroundColor || '#ffffff'),
    // Assurer que le canvas garde ses proportions A4
    aspectRatio: `${canvasWidth}/${canvasHeight}`
  }), [getCursor, canvasWidth, canvasHeight, settings.canvasBackgroundColor, settings.canvasShowTransparency]);

  // Optimiser les dimensions du canvas avec useMemo
  const canvasDimensions = useMemo(() => ({
    width: canvasWidth * zoom,
    height: canvasHeight * zoom
  }), [canvasWidth, canvasHeight, zoom]);

  // Fonction pour obtenir les coordonnées souris de manière sécurisée
  const getMouseCoordinates = useCallback((e) => {
    const canvas = canvasRef.current;
    if (!canvas) return { x: 0, y: 0 };

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Contraindre aux limites du canvas A4
    return {
      x: Math.max(0, Math.min(x, canvasWidth)),
      y: Math.max(0, Math.min(y, canvasHeight))
    };
  }, [zoom, canvasWidth, canvasHeight]);

  // Fonction pour trouver l'élément sous le curseur (optimisée)
  const findElementAtPosition = useCallback((x, y) => {
    // Recherche en ordre inverse pour les éléments superposés (dernier = dessus)
    for (let i = elements.length - 1; i >= 0; i--) {
      const element = elements[i];
      if (element &&
          x >= element.x &&
          x <= element.x + (element.width || 0) &&
          y >= element.y &&
          y <= element.y + (element.height || 0)) {
        return element;
      }
    }
    return null;
  }, [elements]);

  // Fonction pour vérifier si une poignée de redimensionnement est cliquée
  const findClickedHandle = useCallback((element, x, y) => {
    if (!element) return null;

    const handleSize = 6 / zoom;
    const handles = [
      { name: 'nw', x: element.x, y: element.y },
      { name: 'ne', x: element.x + element.width, y: element.y },
      { name: 'sw', x: element.x, y: element.y + element.height },
      { name: 'se', x: element.x + element.width, y: element.y + element.height },
      { name: 'n', x: element.x + element.width / 2, y: element.y },
      { name: 's', x: element.x + element.width / 2, y: element.y + element.height },
      { name: 'w', x: element.x, y: element.y + element.height / 2 },
      { name: 'e', x: element.x + element.width, y: element.y + element.height / 2 }
    ];

    return handles.find(handle =>
      x >= handle.x - handleSize/2 &&
      x <= handle.x + handleSize/2 &&
      y >= handle.y - handleSize/2 &&
      y <= handle.y + handleSize/2
    );
  }, [zoom]);

  // Fonction pour déterminer le curseur approprié
  const getCursor = useCallback(() => {
    if (isDragging) return 'grabbing';
    if (isResizing) {
      switch (resizeHandle) {
        case 'nw':
        case 'se':
          return 'nw-resize';
        case 'ne':
        case 'sw':
          return 'ne-resize';
        case 'n':
        case 's':
          return 'ns-resize';
        case 'w':
        case 'e':
          return 'ew-resize';
        default:
          return 'grabbing';
      }
    }
    if (hoveredHandle) {
      switch (hoveredHandle) {
        case 'nw':
        case 'se':
          return 'nw-resize';
        case 'ne':
        case 'sw':
          return 'ne-resize';
        case 'n':
        case 's':
          return 'ns-resize';
        case 'w':
        case 'e':
          return 'ew-resize';
        default:
          return 'grab';
      }
    }
    return tool === 'select' ? 'grab' : 'crosshair';
  }, [isDragging, isResizing, resizeHandle, hoveredHandle, tool]);

  // Gestionnaire de mouse down amélioré
  const handleMouseDown = useCallback((e) => {
    if (tool !== 'select') return;

    const { x, y } = getMouseCoordinates(e);
    const clickedElement = findElementAtPosition(x, y);

    if (!clickedElement) {
      // Clic dans le vide - désélectionner
      selection.clearSelection();
      return;
    }

    // Vérifier si l'élément est déjà sélectionné
    const isSelected = selectedElements.includes(clickedElement.id);

    if (isSelected) {
      // Vérifier si on clique sur une poignée de redimensionnement
      const clickedHandle = findClickedHandle(clickedElement, x, y);

      if (clickedHandle) {
        // Commencer le redimensionnement
        e.preventDefault();
        setIsResizing(true);
        setResizedElement(clickedElement);
        setResizeHandle(clickedHandle.name);
        setResizeStartPos({ x, y });
        setResizeStartSize({
          width: clickedElement.width || 0,
          height: clickedElement.height || 0
        });
        return;
      }
    } else {
      // Sélectionner le nouvel élément
      selection.selectElement(clickedElement.id);
      onElementSelect(clickedElement.id);
    }

    // Commencer le drag si l'élément est sélectionné
    if (selectedElements.includes(clickedElement.id)) {
      e.preventDefault();
      setIsDragging(true);
      setDraggedElement(clickedElement);
      setDragOffset({
        x: x - (clickedElement.x || 0),
        y: y - (clickedElement.y || 0)
      });
    }
  }, [tool, getMouseCoordinates, findElementAtPosition, selectedElements, selection, onElementSelect, findClickedHandle]);

  // Gestionnaire de mouse move amélioré
  const handleMouseMove = useCallback((e) => {
    const { x, y } = getMouseCoordinates(e);

    // Détecter le hover sur les poignées de redimensionnement
    let newHoveredHandle = null;
    if (tool === 'select' && !isDragging && !isResizing) {
      for (const element of elements) {
        if (selectedElements.includes(element.id)) {
          const clickedHandle = findClickedHandle(element, x, y);
          if (clickedHandle) {
            newHoveredHandle = clickedHandle.name;
            break;
          }
        }
      }
    }
    setHoveredHandle(newHoveredHandle);

    if (isResizing && resizedElement) {
      // Gérer le redimensionnement avec contraintes A4
      const deltaX = x - resizeStartPos.x;
      const deltaY = y - resizeStartPos.y;

      let newX = resizedElement.x || 0;
      let newY = resizedElement.y || 0;
      let newWidth = resizeStartSize.width;
      let newHeight = resizeStartSize.height;

      // Calculer les nouvelles dimensions selon la poignée
      switch (resizeHandle) {
        case 'nw':
          newX = Math.max(0, resizedElement.x + deltaX);
          newY = Math.max(0, resizedElement.y + deltaY);
          newWidth = Math.max(10, resizeStartSize.width - deltaX);
          newHeight = Math.max(10, resizeStartSize.height - deltaY);
          break;
        case 'ne':
          newY = Math.max(0, resizedElement.y + deltaY);
          newWidth = Math.max(10, resizeStartSize.width + deltaX);
          newHeight = Math.max(10, resizeStartSize.height - deltaY);
          break;
        case 'sw':
          newX = Math.max(0, resizedElement.x + deltaX);
          newWidth = Math.max(10, resizeStartSize.width - deltaX);
          newHeight = Math.max(10, resizeStartSize.height + deltaY);
          break;
        case 'se':
          newWidth = Math.max(10, resizeStartSize.width + deltaX);
          newHeight = Math.max(10, resizeStartSize.height + deltaY);
          break;
        case 'n':
          newY = Math.max(0, resizedElement.y + deltaY);
          newHeight = Math.max(10, resizeStartSize.height - deltaY);
          break;
        case 's':
          newHeight = Math.max(10, resizeStartSize.height + deltaY);
          break;
        case 'w':
          newX = Math.max(0, resizedElement.x + deltaX);
          newWidth = Math.max(10, resizeStartSize.width - deltaX);
          break;
        case 'e':
          newWidth = Math.max(10, resizeStartSize.width + deltaX);
          break;
      }

      // Contraindre aux limites du canvas A4
      newWidth = Math.min(newWidth, canvasWidth - newX);
      newHeight = Math.min(newHeight, canvasHeight - newY);

      // Appliquer le snap to grid si activé
      if (snapToGrid) {
        newX = Math.round(newX / gridSize) * gridSize;
        newY = Math.round(newY / gridSize) * gridSize;
        newWidth = Math.round(newWidth / gridSize) * gridSize;
        newHeight = Math.round(newHeight / gridSize) * gridSize;
      }

      onElementUpdate(resizedElement.id, {
        x: Math.max(0, newX),
        y: Math.max(0, newY),
        width: Math.max(10, newWidth),
        height: Math.max(10, newHeight)
      });

    } else if (isDragging && draggedElement) {
      // Gérer le drag avec contraintes A4
      let newX = x - dragOffset.x;
      let newY = y - dragOffset.y;

      // Contraindre aux limites du canvas A4
      newX = Math.max(0, Math.min(newX, canvasWidth - (draggedElement.width || 0)));
      newY = Math.max(0, Math.min(newY, canvasHeight - (draggedElement.height || 0)));

      // Appliquer le snap to grid si activé
      if (snapToGrid) {
        newX = Math.round(newX / gridSize) * gridSize;
        newY = Math.round(newY / gridSize) * gridSize;
      }

      onElementUpdate(draggedElement.id, { x: newX, y: newY });
    }
  }, [
    getMouseCoordinates, tool, isDragging, isResizing, elements, selectedElements,
    findClickedHandle, resizedElement, resizeHandle, resizeStartPos, resizeStartSize,
    canvasWidth, canvasHeight, snapToGrid, gridSize, onElementUpdate, dragOffset, draggedElement
  ]);

  // Gestionnaire de mouse up pour finir le drag et le redimensionnement
  const handleMouseUp = useCallback(() => {
    if (isDragging) {
      setIsDragging(false);
      setDraggedElement(null);
      setDragOffset({ x: 0, y: 0 });
    }

    if (isResizing) {
      setIsResizing(false);
      setResizedElement(null);
      setResizeHandle(null);
      setResizeStartPos({ x: 0, y: 0 });
      setResizeStartSize({ width: 0, height: 0 });
    }
  }, [isDragging, draggedElement, isResizing, resizedElement]);

  // Gestionnaire pour quand la souris quitte le canvas
  const handleMouseLeave = useCallback(() => {
    // Terminer le drag ou resize si la souris quitte le canvas
    if (isDragging) {
      setIsDragging(false);
      setDraggedElement(null);
      setDragOffset({ x: 0, y: 0 });
    }

    if (isResizing) {
      setIsResizing(false);
      setResizedElement(null);
      setResizeHandle(null);
      setResizeStartPos({ x: 0, y: 0 });
      setResizeStartSize({ width: 0, height: 0 });
    }
  }, [isDragging, draggedElement, isResizing, resizedElement]);

  // Gestionnaire de mouse leave pour annuler le drag

  // Gestionnaire de clic sur le canvas amélioré
  const handleCanvasClick = useCallback((e) => {
    const { x, y } = getMouseCoordinates(e);
    const clickedElement = findElementAtPosition(x, y);

    if (tool === 'select') {
      if (clickedElement) {
        // Vérifier que l'élément a un ID valide
        if (clickedElement.id) {
          selection.selectElement(clickedElement.id);
          onElementSelect(clickedElement.id);
        }
      } else {
        selection.clearSelection();
      }
    } else if (tool.startsWith('add-')) {
      // Créer un nouvel élément avec des propriétés sécurisées
      const elementType = tool.replace('add-', '');
      const newElementId = `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

      const newElement = {
        id: newElementId,
        type: elementType,
        x: Math.max(0, Math.min(x - 50, canvasWidth - 100)), // Centrer et contraindre
        y: Math.max(0, Math.min(y - 25, canvasHeight - 50)),
        width: Math.min(100, canvasWidth), // Ne pas dépasser la largeur A4
        height: Math.min(50, canvasHeight), // Ne pas dépasser la hauteur A4
        content: elementType === 'text' ? 'Nouveau texte' : '',
        backgroundColor: 'transparent',
        borderColor: '#6b7280',
        borderWidth: elementType === 'text' ? 0 : 1, // Pas de bordure par défaut pour le texte
        color: '#1e293b',
        fontSize: 14,
        fontFamily: 'Arial',
        textAlign: 'left',
        // Propriétés de sécurité par défaut
        opacity: 1,
        zIndex: 0,
        borderRadius: 0,
        padding: 0
      };

      // Propriétés spécifiques selon le type
      if (elementType === 'product_table') {
        newElement.showHeaders = true;
        newElement.showBorders = true;
        newElement.tableStyle = 'default';
        newElement.columns = {
          name: true,
          price: true,
          quantity: true,
          total: true
        };
      }

      onElementUpdate(newElementId, newElement);
    }
  }, [tool, getMouseCoordinates, findElementAtPosition, selection, onElementSelect, onElementUpdate, canvasWidth, canvasHeight]);

  // Gestionnaire de clic droit amélioré
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();

    const { x, y } = getMouseCoordinates(e);
    const clickedElement = findElementAtPosition(x, y);

    if (onContextMenu && clickedElement?.id) {
      onContextMenu(e, clickedElement.id);
    }
  }, [getMouseCoordinates, findElementAtPosition, onContextMenu]);

  // Gestionnaire de drop amélioré avec contraintes A4
  const handleDrop = useCallback((e) => {
    e.preventDefault();

    if (!onElementUpdate) return;

    try {
      const elementId = e.dataTransfer.getData('text/plain');
      if (!elementId) return;

      const { x, y } = getMouseCoordinates(e);

      // Trouver l'élément déposé
      const droppedElement = elements.find(el => el.id === elementId);
      if (!droppedElement) return;

      // Calculer la nouvelle position en centrant l'élément
      let newX = x - (droppedElement.width || 0) / 2;
      let newY = y - (droppedElement.height || 0) / 2;

      // Contraindre aux limites A4
      newX = Math.max(0, Math.min(newX, canvasWidth - (droppedElement.width || 0)));
      newY = Math.max(0, Math.min(newY, canvasHeight - (droppedElement.height || 0)));

      // Appliquer le snap to grid si activé
      if (snapToGrid) {
        newX = Math.round(newX / gridSize) * gridSize;
        newY = Math.round(newY / gridSize) * gridSize;
      }

      onElementUpdate(elementId, { x: newX, y: newY });
    } catch (error) {
      // Error during drop operation
    }
  }, [onElementUpdate, getMouseCoordinates, elements, canvasWidth, canvasHeight, snapToGrid, gridSize]);

  // État pour le drag and drop
  const [isDragging, setIsDragging] = useState(false);
  const [draggedElement, setDraggedElement] = useState(null);
  const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });

  // État pour le redimensionnement
  const [isResizing, setIsResizing] = useState(false);
  const [resizedElement, setResizedElement] = useState(null);
  const [resizeHandle, setResizeHandle] = useState(null);
  const [resizeStartPos, setResizeStartPos] = useState({ x: 0, y: 0 });
  const [resizeStartSize, setResizeStartSize] = useState({ width: 0, height: 0 });
  const [hoveredHandle, setHoveredHandle] = useState(null);

  return (
    <div className="canvas-wrapper" style={{ position: 'relative' }}>
      <canvas
        ref={canvasRef}
        width={canvasDimensions.width}
        height={canvasDimensions.height}
        className="canvas"
        style={memoizedCanvasStyle}
        onClick={handleCanvasClick}
        onContextMenu={handleContextMenuEvent}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
        onDragOver={(e) => e.preventDefault()}
        onDrop={handleDrop}
      />
      {/* Indicateur de dimensions A4 pour le debug */}
      {process.env.NODE_ENV === 'development' && (
        <div
          style={{
            position: 'absolute',
            top: 5,
            right: 5,
            background: 'rgba(0,0,0,0.7)',
            color: 'white',
            padding: '2px 6px',
            borderRadius: 3,
            fontSize: '11px',
            fontFamily: 'monospace'
          }}
        >
          A4: {canvasWidth}×{canvasHeight}pt
        </div>
      )}
    </div>
  );
};

export default Canvas;
