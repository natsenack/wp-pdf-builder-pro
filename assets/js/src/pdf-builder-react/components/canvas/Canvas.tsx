import React, { useRef, useEffect, useCallback, memo } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasDrop } from '../../hooks/useCanvasDrop.ts';
import { useCanvasInteraction } from '../../hooks/useCanvasInteraction.ts';
import { Element } from '../../types/elements';

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

// Fonctions utilitaires de dessin (définies en dehors du composant)
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

const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const fillColor = String(props.backgroundColor || props.fillColor || '#ffffff');
  const strokeColor = String(props.borderColor || props.strokeColor || '#000000');
  const strokeWidth = Number(props.borderWidth || props.strokeWidth || 1);
  const borderRadius = Number(props.borderRadius || 0);

  ctx.fillStyle = fillColor;
  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  if (borderRadius > 0) {
    // Rectangle avec coins arrondis
    ctx.beginPath();
    ctx.roundRect(0, 0, element.width, element.height, borderRadius);
    ctx.fill();
    ctx.stroke();
  } else {
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeRect(0, 0, element.width, element.height);
  }
};

const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const fillColor = String(props.fillColor || '#ffffff');
  const strokeColor = String(props.strokeColor || '#000000');
  const strokeWidth = Number(props.strokeWidth || 1);

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
  const props = element as Record<string, unknown>;
  const text = String(props.text || 'Sample Text');
  const fontSize = Number(props.fontSize || 16);
  const fontFamily = String(props.fontFamily || 'Arial');
  const color = String(props.textColor || props.color || '#000000');
  const align = String(props.align || 'left');

  ctx.fillStyle = color;
  ctx.font = fontSize + 'px ' + fontFamily;
  ctx.textAlign = align as CanvasTextAlign;

  const lines = text.split('\n');
  const lineHeight = fontSize * 1.2;
  let y = fontSize;

  lines.forEach((line: string) => {
    ctx.fillText(line, element.width / 2, y);
    y += lineHeight;
  });
};

const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const strokeColor = String(props.strokeColor || '#000000');
  const strokeWidth = Number(props.strokeWidth || 1);

  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  ctx.beginPath();
  ctx.moveTo(0, element.height / 2);
  ctx.lineTo(element.width, element.height / 2);
  ctx.stroke();
};

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

const drawSelection = (ctx: CanvasRenderingContext2D, selectedElements: string[], elements: Element[]) => {
  selectedElements.forEach((elementId) => {
    const element = elements.find(el => el.id === elementId);
    if (element) {
      ctx.save();
      ctx.translate(element.x, element.y);

      // Dessiner rectangle de sélection
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(-2, -2, element.width + 4, element.height + 4);
      ctx.setLineDash([]);

      ctx.restore();
    }
  });
};

export const Canvas = memo(function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state } = useBuilder();

  // Utiliser les hooks pour les interactions
  const { handleDrop, handleDragOver } = useCanvasDrop({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height,
    elements: state.elements || []
  });

  const { handleCanvasClick, handleMouseDown, handleMouseMove, handleMouseUp } = useCanvasInteraction({
    canvasRef
  });

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
    if (state.elements && state.elements.length > 0) {
      state.elements.forEach((element) => {
        drawElement(ctx, element);
      });
    }

    // Dessiner la sélection
    if (state.selection && state.selection.selectedElements && state.selection.selectedElements.length > 0 && state.elements) {
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();
  }, [state, width, height]);

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
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onDrop={handleDrop}
      onDragOver={handleDragOver}
      style={{
        border: '1px solid #ccc',
        cursor: 'crosshair',
        backgroundColor: '#ffffff'
      }}
    />
  );
});