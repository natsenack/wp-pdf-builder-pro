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

      // Calculer la position relative au canvas
      const canvas = canvasRef.current;
      if (!canvas) return;

      // Créer un nouvel élément avec l'ordre correct de fusion
      const newElement = {
        id: `element_${Date.now()}`,
        type: elementData.type,
        // D'abord : les defaultProps complètes (largeur, hauteur, styles, etc.)
        ...elementData.defaultProps,
        // Ensuite : les positions calculées dynamiquement (x, y uniquement, peuvent overrider les defaultProps)
        // Les autres propriétés calculées (width, height) sont ignorées si déjà dans defaultProps
        x: elementData.defaultProps?.x ?? 50,
        y: elementData.defaultProps?.y ?? 50,
        // Propriétés requises par BaseElement
        visible: true,
        locked: false,
        createdAt: new Date(),
        updatedAt: new Date()
      };

      // Ajouter l'élément au state
      dispatch({ type: 'ADD_ELEMENT', payload: newElement });

    } catch {
      // Erreur silencieuse lors du drop
    }
  }, [canvasRef, dispatch]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  return {
    handleDrop,
    handleDragOver
  };
};