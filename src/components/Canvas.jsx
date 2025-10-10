import React, { useRef, useEffect, useCallback, useState } from 'react';

export const Canvas = ({
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

    // Dessiner la sélection
    if (selectedElements.includes(element.id)) {
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(-2, -2, element.width + 4, element.height + 4);
      ctx.setLineDash([]);

      // Poignées de redimensionnement
      ctx.fillStyle = '#007cba';
      const handles = [
        [0, 0], [element.width, 0], [element.width, element.height], [0, element.height],
        [element.width / 2, 0], [element.width, element.height / 2],
        [element.width / 2, element.height], [0, element.height / 2]
      ];

      handles.forEach(([x, y]) => {
        ctx.fillRect(x - 3, y - 3, 6, 6);
      });
    }

    ctx.restore();
  }, [selectedElements]);

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
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

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

  // Gestionnaire de mouse down pour commencer le drag ou le redimensionnement
  const handleMouseDown = useCallback((e) => {
    if (tool !== 'select') return;

    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Trouver l'élément cliqué
    let clickedElement = null;
    for (let i = elements.length - 1; i >= 0; i--) {
      const element = elements[i];
      if (x >= element.x && x <= element.x + element.width &&
          y >= element.y && y <= element.y + element.height) {
        clickedElement = element;
        break;
      }
    }

    if (clickedElement && selectedElements.includes(clickedElement.id)) {
      // Vérifier si on clique sur une poignée de redimensionnement
      const handleSize = 6 / zoom; // Taille correspondant au dessin (6x6 pixels)
      const handles = [
        { name: 'nw', x: clickedElement.x, y: clickedElement.y },
        { name: 'ne', x: clickedElement.x + clickedElement.width, y: clickedElement.y },
        { name: 'sw', x: clickedElement.x, y: clickedElement.y + clickedElement.height },
        { name: 'se', x: clickedElement.x + clickedElement.width, y: clickedElement.y + clickedElement.height },
        { name: 'n', x: clickedElement.x + clickedElement.width / 2, y: clickedElement.y },
        { name: 's', x: clickedElement.x + clickedElement.width / 2, y: clickedElement.y + clickedElement.height },
        { name: 'w', x: clickedElement.x, y: clickedElement.y + clickedElement.height / 2 },
        { name: 'e', x: clickedElement.x + clickedElement.width, y: clickedElement.y + clickedElement.height / 2 }
      ];

      const clickedHandle = handles.find(handle =>
        x >= handle.x - handleSize/2 && x <= handle.x + handleSize/2 &&
        y >= handle.y - handleSize/2 && y <= handle.y + handleSize/2
      );

      if (clickedHandle) {
        // Commencer le redimensionnement
        setIsResizing(true);
        setResizedElement(clickedElement);
        setResizeHandle(clickedHandle.name);
        setResizeStartPos({ x, y });
        setResizeStartSize({ width: clickedElement.width, height: clickedElement.height });
        return;
      }
    }

    if (clickedElement) {
      // Si l'élément n'est pas déjà sélectionné, le sélectionner
      if (!selectedElements.includes(clickedElement.id)) {
        onElementSelect(clickedElement.id);
      }

      setIsDragging(true);
      setDraggedElement(clickedElement);
      setDragOffset({
        x: x - clickedElement.x,
        y: y - clickedElement.y
      });
    }
  }, [tool, zoom, elements, selectedElements, selection, onElementSelect]);

  // Gestionnaire de mouse move pour le drag et le redimensionnement
  const handleMouseMove = useCallback((e) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Détecter le hover sur les poignées de redimensionnement
    let newHoveredHandle = null;
    if (tool === 'select' && !isDragging && !isResizing) {
      for (const element of elements) {
        if (selectedElements.includes(element.id)) {
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

          const hovered = handles.find(handle =>
            x >= handle.x - handleSize/2 && x <= handle.x + handleSize/2 &&
            y >= handle.y - handleSize/2 && y <= handle.y + handleSize/2
          );

          if (hovered) {
            newHoveredHandle = hovered.name;
            break;
          }
        }
      }
    }
    setHoveredHandle(newHoveredHandle);

    if (isResizing && resizedElement) {
      // Gérer le redimensionnement
      const deltaX = x - resizeStartPos.x;
      const deltaY = y - resizeStartPos.y;

      let newX = resizedElement.x;
      let newY = resizedElement.y;
      let newWidth = resizeStartSize.width;
      let newHeight = resizeStartSize.height;

      // Calculer les nouvelles dimensions selon la poignée
      switch (resizeHandle) {
        case 'nw':
          newX = resizedElement.x + deltaX;
          newY = resizedElement.y + deltaY;
          newWidth = resizeStartSize.width - deltaX;
          newHeight = resizeStartSize.height - deltaY;
          break;
        case 'ne':
          newY = resizedElement.y + deltaY;
          newWidth = resizeStartSize.width + deltaX;
          newHeight = resizeStartSize.height - deltaY;
          break;
        case 'sw':
          newX = resizedElement.x + deltaX;
          newWidth = resizeStartSize.width - deltaX;
          newHeight = resizeStartSize.height + deltaY;
          break;
        case 'se':
          newWidth = resizeStartSize.width + deltaX;
          newHeight = resizeStartSize.height + deltaY;
          break;
        case 'n':
          newY = resizedElement.y + deltaY;
          newHeight = resizeStartSize.height - deltaY;
          break;
        case 's':
          newHeight = resizeStartSize.height + deltaY;
          break;
        case 'w':
          newX = resizedElement.x + deltaX;
          newWidth = resizeStartSize.width - deltaX;
          break;
        case 'e':
          newWidth = resizeStartSize.width + deltaX;
          break;
      }

      // Contraindre les dimensions minimales
      newWidth = Math.max(10, newWidth);
      newHeight = Math.max(10, newHeight);

      // Appliquer le snap to grid si activé
      if (snapToGrid) {
        newX = Math.round(newX / gridSize) * gridSize;
        newY = Math.round(newY / gridSize) * gridSize;
        newWidth = Math.round(newWidth / gridSize) * gridSize;
        newHeight = Math.round(newHeight / gridSize) * gridSize;
      }

      onElementUpdate(resizedElement.id, { x: newX, y: newY, width: newWidth, height: newHeight });

    } else if (isDragging && draggedElement) {
      // Gérer le drag
      let newX = x - dragOffset.x;
      let newY = y - dragOffset.y;

      // Contraindre aux limites du canvas
      newX = Math.max(0, Math.min(newX, canvasWidth - draggedElement.width));
      newY = Math.max(0, Math.min(newY, canvasHeight - draggedElement.height));

      // Appliquer le snap to grid si activé
      if (snapToGrid) {
        newX = Math.round(newX / gridSize) * gridSize;
        newY = Math.round(newY / gridSize) * gridSize;
      }

      onElementUpdate(draggedElement.id, { x: newX, y: newY });
    }
  }, [isDragging, draggedElement, dragOffset, isResizing, resizedElement, resizeHandle, resizeStartPos, resizeStartSize, zoom, canvasWidth, canvasHeight, snapToGrid, gridSize, onElementUpdate]);

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

  // Gestionnaire de clic sur le canvas
  const handleCanvasClick = useCallback((e) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Trouver l'élément cliqué (en ordre inverse pour les éléments superposés)
    let clickedElement = null;
    for (let i = elements.length - 1; i >= 0; i--) {
      const element = elements[i];
      if (x >= element.x && x <= element.x + element.width &&
          y >= element.y && y <= element.y + element.height) {
        clickedElement = element;
        break;
      }
    }

    if (tool === 'select') {
      if (clickedElement) {
        selection.selectElement(clickedElement.id);
        onElementSelect(clickedElement.id);
      } else {
        selection.clearSelection();
      }
    } else if (tool.startsWith('add-')) {
      const elementType = tool.replace('add-', '');
      const newElement = {
        id: Date.now().toString(),
        type: elementType,
        x: x - 50, // Centrer l'élément
        y: y - 25,
        width: 100,
        height: 50,
        content: elementType === 'text' ? 'Nouveau texte' : '',
        backgroundColor: '#ffffff',
        borderColor: '#6b7280',
        borderWidth: 1,
        color: '#1e293b',
        fontSize: 14,
        fontFamily: 'Arial',
        textAlign: 'left'
      };

      onElementUpdate(newElement.id, newElement);
    }
  }, [tool, zoom, elements, selection, onElementSelect, onElementUpdate]);

  // Gestionnaire de clic droit
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();

    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Trouver l'élément sous le curseur
    let clickedElement = null;
    for (let i = elements.length - 1; i >= 0; i--) {
      const element = elements[i];
      if (x >= element.x && x <= element.x + element.width &&
          y >= element.y && y <= element.y + element.height) {
        clickedElement = element;
        break;
      }
    }

    if (onContextMenu) {
      onContextMenu(e, clickedElement?.id);
    }
  }, [elements, zoom, onContextMenu]);

  return (
    <div className="canvas-wrapper">
      <canvas
        ref={canvasRef}
        width={canvasWidth * zoom}
        height={canvasHeight * zoom}
        className="canvas"
        style={{
          border: '1px solid #ccc',
          cursor: getCursor(),
          backgroundColor: 'white'
        }}
        onClick={handleCanvasClick}
        onContextMenu={handleContextMenuEvent}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
        onDragOver={(e) => e.preventDefault()}
        onDrop={(e) => {
          e.preventDefault();
          // Forward to parent drop handler if available
          if (onElementUpdate) {
            // Handle drop of existing elements
            const elementId = e.dataTransfer.getData('text/plain');
            if (elementId) {
              const rect = e.currentTarget.getBoundingClientRect();
              const dropX = (e.clientX - rect.left) / zoom;
              const dropY = (e.clientY - rect.top) / zoom;
              onElementUpdate(elementId, { x: dropX, y: dropY });
            }
          }
        }}
      />
    </div>
  );
};