import React, { useRef, useEffect, useCallback } from 'react';

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
    ctx.strokeStyle = '#e0e0e0';
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
    ctx.strokeStyle = element.borderColor || '#000000';
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
        ctx.fillStyle = element.color || '#000000';
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
    if (selection.isSelected(element.id)) {
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
  }, [selection]);

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
        borderColor: '#000000',
        borderWidth: 1,
        color: '#000000',
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
          cursor: tool === 'select' ? 'default' : 'crosshair',
          backgroundColor: 'white'
        }}
        onClick={handleCanvasClick}
        onContextMenu={handleContextMenuEvent}
      />
    </div>
  );
};