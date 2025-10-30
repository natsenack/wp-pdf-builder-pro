import { useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

interface UseCanvasDropProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
  canvasWidth: number;
  canvasHeight: number;
}

export const useCanvasDrop = ({ canvasRef, canvasWidth, canvasHeight }: UseCanvasDropProps) => {
  const { dispatch } = useBuilder();

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();

    try {
      const elementData = JSON.parse(e.dataTransfer.getData('application/json'));

      // Calculer la position relative au canvas
      const canvas = canvasRef.current;
      if (!canvas) return;

      const rect = canvas.getBoundingClientRect();
      const scaleX = canvasWidth / rect.width;
      const scaleY = canvasHeight / rect.height;

      const x = (e.clientX - rect.left) * scaleX;
      const y = (e.clientY - rect.top) * scaleY;

      // Créer un nouvel élément avec les propriétés par défaut
      const newElement = {
        id: `element_${Date.now()}`,
        type: elementData.type,
        ...elementData.defaultProps,
        x: Math.max(0, x - 50), // Centrer l'élément sur le point de drop
        y: Math.max(0, y - 25)
      };

      // Ajouter l'élément au state
      dispatch({ type: 'ADD_ELEMENT', payload: newElement });

    } catch (error) {
      // Erreur silencieuse lors du drop
    }
  }, [canvasRef, canvasWidth, canvasHeight, dispatch]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  return {
    handleDrop,
    handleDragOver
  };
};