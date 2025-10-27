import React, { useRef, useEffect, useCallback } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Point, Element } from '../../types/elements';

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

export function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state, dispatch } = useBuilder();

  // Log des dimensions pour debug
  console.log('Canvas dimensions:', { width, height, expectedA4: width === 794 && height === 1123 });

  // Fonction de rendu du canvas
  const renderCanvas = useCallback(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Appliquer transformation (zoom, pan)
    ctx.save();
    ctx.translate(state.canvas.pan.x, state.canvas.pan.y);
    ctx.scale(state.canvas.zoom, state.canvas.zoom);

    // Dessiner la grille si activée
    if (state.canvas.showGrid) {
      drawGrid(ctx, width, height, state.canvas.gridSize);
    }

    // Dessiner les éléments
    state.elements.forEach(element => {
      drawElement(ctx, element);
    });

    // Dessiner la sélection
    if (state.selection.selectedElements.length > 0) {
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();
  }, [state, width, height]);

  // Fonction pour dessiner la grille
  const drawGrid = (ctx: CanvasRenderingContext2D, w: number, h: number, size: number) => {
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;

    for (let x = 0; x <= w; x += size) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, h);
      ctx.stroke();
    }

    for (let y = 0; y <= h; y += size) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }
  };

  // Fonction pour dessiner un élément
  const drawElement = (ctx: CanvasRenderingContext2D, element: Element) => {
    ctx.save();

    // Appliquer transformation de l'élément
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    // Dessiner selon le type d'élément
    switch (element.type) {
      case 'rectangle':
        drawRectangle(ctx, element);
        break;
      case 'circle':
        drawCircle(ctx, element);
        break;
      case 'text':
        drawText(ctx, element);
        break;
      case 'line':
        drawLine(ctx, element);
        break;
      default:
        // Élément générique - dessiner un rectangle simple
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.restore();
  };

  // Fonctions de dessin spécifiques
  const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
    const fillColor = (element as any).fillColor || '#ffffff';
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 1;
    const borderRadius = (element as any).borderRadius || 0;

    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    if (borderRadius > 0) {
      roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
      ctx.fill();
      ctx.stroke();
    } else {
      ctx.fillRect(0, 0, element.width, element.height);
      ctx.strokeRect(0, 0, element.width, element.height);
    }
  };

  const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
    const fillColor = (element as any).fillColor || '#ffffff';
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 1;

    const centerX = element.width / 2;
    const centerY = element.height / 2;
    const radius = Math.min(centerX, centerY);

    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.fill();
    ctx.stroke();
  };

  const drawText = (ctx: CanvasRenderingContext2D, element: Element) => {
    const text = (element as any).text || 'Text';
    const fontSize = (element as any).fontSize || 16;
    const color = (element as any).color || '#000000';
    const align = (element as any).align || 'left';

    ctx.fillStyle = color;
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = align as CanvasTextAlign;

    ctx.fillText(text, 0, fontSize);
  };

  const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 1;
    const x2 = (element as any).x2 || element.width;
    const y2 = (element as any).y2 || element.height;

    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(x2, y2);
    ctx.stroke();
  };

  // Fonction utilitaire pour rectangle arrondi
  const roundedRect = (ctx: CanvasRenderingContext2D, x: number, y: number, width: number, height: number, radius: number) => {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
  };

  // Fonction pour dessiner la sélection
  const drawSelection = (ctx: CanvasRenderingContext2D, selectedIds: string[], elements: Element[]) => {
    const selectedElements = elements.filter(el => selectedIds.includes(el.id));
    if (selectedElements.length === 0) return;

    // Calculer les bounds de sélection
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

    selectedElements.forEach(el => {
      minX = Math.min(minX, el.x);
      minY = Math.min(minY, el.y);
      maxX = Math.max(maxX, el.x + el.width);
      maxY = Math.max(maxY, el.y + el.height);
    });

    // Dessiner le rectangle de sélection
    ctx.strokeStyle = '#007acc';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(minX - 5, minY - 5, (maxX - minX) + 10, (maxY - minY) + 10);
    ctx.setLineDash([]);
  };

  // Gestionnaire de clic pour la sélection
  const handleCanvasClick = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left - state.canvas.pan.x) / state.canvas.zoom;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / state.canvas.zoom;

    // Trouver l'élément cliqué
    const clickedElement = state.elements.find(el =>
      x >= el.x && x <= el.x + el.width &&
      y >= el.y && y <= el.y + el.height
    );

    if (clickedElement) {
      dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
    } else {
      dispatch({ type: 'CLEAR_SELECTION' });
    }
  }, [state, dispatch]);

  // Redessiner quand l'état change
  useEffect(() => {
    renderCanvas();
  }, [renderCanvas]);

  return (
    <canvas
      ref={canvasRef}
      width={width}
      height={height}
      className={className}
      onClick={handleCanvasClick}
      style={{
        border: '3px solid #007acc', // Bordure bleue distinctive pour identifier le canvas A4
        cursor: 'crosshair',
        backgroundColor: '#ffffff', // Fond blanc pour simuler le papier
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)' // Ombre légère
      }}
    />
  );
}