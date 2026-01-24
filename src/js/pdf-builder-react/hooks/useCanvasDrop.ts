import React, { useCallback, useState } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext';
import { Element } from '../types/elements';
import { debugLog, debugError, debugWarn } from '../utils/debug';

interface UseCanvasDropProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
  canvasWidth: number;
  canvasHeight: number;
  elements: Element[];
  dragEnabled?: boolean;
}

interface DragData {
  type: string;
  label: string;
  defaultProps: Record<string, unknown>;
}

export const useCanvasDrop = ({ canvasRef, canvasWidth, canvasHeight, elements, dragEnabled = true }: UseCanvasDropProps) => {
  const { state, dispatch } = useBuilder();
  const [isDragOver, setIsDragOver] = useState(false);

  // ✅ Validation des données de drag
  const validateDragData = useCallback((data: unknown): data is DragData => {
    if (!data || typeof data !== 'object') return false;

    const dragData = data as Record<string, unknown>;
    return (
      typeof dragData.type === 'string' &&
      typeof dragData.label === 'string' &&
      typeof dragData.defaultProps === 'object' &&
      dragData.defaultProps !== null
    );
  }, []);

  // ✅ Calcul correct des coordonnées avec zoom/pan
  const calculateDropPosition = useCallback((clientX: number, clientY: number, elementWidth: number = 100, elementHeight: number = 50) => {
    const canvas = canvasRef.current;
    if (!canvas) {
      throw new Error('Canvas ref not available');
    }

    const rect = canvas.getBoundingClientRect();

    // Validation du rectangle canvas
    if (rect.width <= 0 || rect.height <= 0) {
      throw new Error('Invalid canvas dimensions');
    }

    // Calcul des coordonnées dans l'espace canvas (avant transformation)
    const canvasX = clientX - rect.left;
    const canvasY = clientY - rect.top;

    // Validation des coordonnées
    if (canvasX < 0 || canvasY < 0 || canvasX > rect.width || canvasY > rect.height) {

    }

    // Appliquer la transformation inverse (zoom/pan)
    // Note: zoom est en pourcentage (100 = 100%), donc diviser par 100
    const zoomScale = state.canvas.zoom / 100;

    // Position dans l'espace canvas transformé
    const transformedX = (canvasX - state.canvas.pan.x) / zoomScale;
    const transformedY = (canvasY - state.canvas.pan.y) / zoomScale;

    // Centrer l'élément sur le point de drop
    const centeredX = Math.max(0, transformedX - elementWidth / 2);
    const centeredY = Math.max(0, transformedY - elementHeight / 2);

    // S'assurer que l'élément reste dans les limites du canvas
    const clampedX = Math.max(0, Math.min(centeredX, canvasWidth - elementWidth));
    const clampedY = Math.max(0, Math.min(centeredY, canvasHeight - elementHeight));

    return {
      x: clampedX,
      y: clampedY,
      originalCanvasX: canvasX,
      originalCanvasY: canvasY,
      transformedX,
      transformedY
    };
  }, [canvasRef, canvasWidth, canvasHeight, state.canvas]);

  // ✅ Génération d'ID unique pour les éléments
  const generateElementId = useCallback((type: string): string => {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substr(2, 9);
    return `element_${type}_${timestamp}_${random}`;
  }, []);

  // ✅ Création d'élément avec validation
  const createElementFromDragData = useCallback((dragData: DragData, position: { x: number; y: number }): Element => {
    const elementId = generateElementId(dragData.type);

    // S'assurer que width et height sont définis
    const width = (dragData.defaultProps.width as number) || 100;
    const height = (dragData.defaultProps.height as number) || 50;

    // Fusion des propriétés par défaut avec les propriétés calculées
    const element: Element = {
      id: elementId,
      type: dragData.type as Element['type'], // Type assertion sécurisé
      // Propriétés par défaut (peuvent être overriden par position)
      ...dragData.defaultProps,
      // Position calculée (override x, y des defaultProps)
      x: position.x,
      y: position.y,
      width,
      height,
      // Propriétés système requises
      visible: true,
      locked: false,
      createdAt: new Date(),
      updatedAt: new Date()
    };

    return element;
  }, [generateElementId]);

  const handleDrop = useCallback((e: React.DragEvent) => {
    if (!dragEnabled) {
      debugLog('[CanvasDrop] Drop ignored - drag disabled');
      return;
    }
    
    e.preventDefault();
    setIsDragOver(false);

    debugLog('[CanvasDrop] Processing drop event');

    try {

      // Parsing des données de drag
      const rawData = e.dataTransfer.getData('application/json');
      if (!rawData) {
        throw new Error('No drag data received');
      }

      const dragData = JSON.parse(rawData);
      debugLog(`[CanvasDrop] Parsed drag data: ${dragData.type} (${dragData.label})`);

      // Validation des données
      if (!validateDragData(dragData)) {
        throw new Error('Invalid drag data structure');
      }

      // Calcul de la position avec zoom/pan
      const elementWidth = (dragData.defaultProps.width as number) || 100;
      const elementHeight = (dragData.defaultProps.height as number) || 50;

      const position = calculateDropPosition(e.clientX, e.clientY, elementWidth, elementHeight);
      debugLog(`[CanvasDrop] Calculated drop position: (${position.x}, ${position.y}) from client coords (${e.clientX}, ${e.clientY})`);

      // Création de l'élément
      const newElement = createElementFromDragData(dragData, position);
      debugLog(`[CanvasDrop] Created element: ${newElement.id} (${newElement.type})`);

      // Vérification des conflits d'ID
      const existingElement = elements.find(el => el.id === newElement.id);
      if (existingElement) {
        newElement.id = generateElementId(dragData.type);
        debugWarn(`[CanvasDrop] ID conflict resolved, new ID: ${newElement.id}`);
      }

      // Ajout au state
      dispatch({ type: 'ADD_ELEMENT', payload: newElement });
      debugLog(`[CanvasDrop] Element added to canvas successfully`);

    } catch (error) {
      debugError(`[CanvasDrop] Drop failed:`, error);

    }
  }, [validateDragData, calculateDropPosition, createElementFromDragData, elements, dispatch, generateElementId, dragEnabled]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    if (!dragEnabled) {
      debugLog('[CanvasDrop] Drag over ignored - drag disabled');
      return;
    }
    
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';

    if (!isDragOver) {
      debugLog('[CanvasDrop] Drag over started');
      setIsDragOver(true);
    }
  }, [isDragOver, dragEnabled]);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    if (!dragEnabled) {
      debugLog('[CanvasDrop] Drag leave ignored - drag disabled');
      return;
    }
    
    // Simple check - if we have a relatedTarget, assume drag is leaving
    // This is a simplified approach to avoid DOM type issues
    if (e.relatedTarget) {
      debugLog('[CanvasDrop] Drag leave detected');
      setIsDragOver(false);
    }
  }, [dragEnabled]);

  return {
    handleDrop,
    handleDragOver,
    handleDragLeave,
    isDragOver
  };
};

