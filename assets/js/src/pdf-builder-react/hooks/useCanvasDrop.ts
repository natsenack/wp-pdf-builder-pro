import React, { useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

interface UseCanvasDropProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
  canvasWidth: number;
  canvasHeight: number;
  elements: unknown[]; // Éléments existants pour calcul dynamique des positions
}

export const useCanvasDrop = ({ canvasRef, canvasWidth: _canvasWidth, canvasHeight: _canvasHeight, elements: _elements }: UseCanvasDropProps) => {
  const { dispatch } = useBuilder();

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();

    try {
      const elementData = JSON.parse(e.dataTransfer.getData('application/json'));
      console.log('Drop detected:', elementData);

      // Calculer la position relative au canvas
      const canvas = canvasRef.current;
      if (!canvas) {
        console.error('Canvas ref not available');
        return;
      }

      // Calculer la position relative au canvas
      const rect = canvas.getBoundingClientRect();
      const scaleX = _canvasWidth / rect.width;
      const scaleY = _canvasHeight / rect.height;
      
      const x = Math.max(0, (e.clientX - rect.left) * scaleX - (elementData.defaultProps?.width ? elementData.defaultProps.width / 2 : 50));
      const y = Math.max(0, (e.clientY - rect.top) * scaleY - (elementData.defaultProps?.height ? elementData.defaultProps.height / 2 : 25));

      console.log('Drop position:', { x, y, clientX: e.clientX, clientY: e.clientY, rect, scaleX, scaleY });

      // Créer un nouvel élément avec l'ordre correct de fusion
      const newElement = {
        id: `element_${Date.now()}`,
        type: elementData.type,
        // D'abord : les defaultProps complètes (largeur, hauteur, styles, etc.)
        ...elementData.defaultProps,
        // Ensuite : les positions calculées dynamiquement (x, y uniquement, peuvent overrider les defaultProps)
        x: x,
        y: y,
        // Propriétés requises par BaseElement
        visible: true,
        locked: false,
        createdAt: new Date(),
        updatedAt: new Date()
      };

      console.log('New element created:', newElement);

      // Ajouter l'élément au state
      dispatch({ type: 'ADD_ELEMENT', payload: newElement });
      console.log('Element added to state');

    } catch (error) {
      console.error('Drop error:', error);
    }
  }, [canvasRef, _canvasWidth, _canvasHeight, dispatch]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  return {
    handleDrop,
    handleDragOver
  };
};