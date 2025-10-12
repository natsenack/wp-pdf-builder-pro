import { useState, useCallback, useRef, useEffect } from 'react';

/**
 * Hook pour gérer la sélection d'éléments sur le canvas
 * @param {Object} options - Options du hook
 * @param {Function} options.onSelectionChange - Callback appelé quand la sélection change
 * @param {boolean} options.multiSelect - Si la sélection multiple est activée
 * @returns {Object} - État et méthodes de sélection
 */
export const useSelection = ({
  onSelectionChange,
  multiSelect = true
} = {}) => {
  const [selectedElements, setSelectedElements] = useState(new Set());
  const [isSelecting, setIsSelecting] = useState(false);
  const selectionBoxRef = useRef(null);
  const startPosRef = useRef({ x: 0, y: 0 });

  // Vérifier si un élément est sélectionné
  const isSelected = useCallback((elementId) => {
    return selectedElements.has(elementId);
  }, [selectedElements]);

  // Sélectionner un élément
  const selectElement = useCallback((elementId, addToSelection = false) => {
    setSelectedElements(prev => {
      const newSelection = addToSelection ? new Set(prev) : new Set();

      if (newSelection.has(elementId)) {
        // Désélectionner si déjà sélectionné
        newSelection.delete(elementId);
      } else {
        // Sélectionner
        newSelection.add(elementId);
      }

      onSelectionChange?.(Array.from(newSelection));
      return newSelection;
    });
  }, [onSelectionChange]);

  // Sélectionner plusieurs éléments
  const selectElements = useCallback((elementIds) => {
    setSelectedElements(new Set(elementIds));
    onSelectionChange?.(elementIds);
  }, [onSelectionChange]);

  // Désélectionner tous les éléments
  const clearSelection = useCallback(() => {
    setSelectedElements(new Set());
    onSelectionChange?.([]);
  }, [onSelectionChange]);

  // Obtenir les éléments sélectionnés
  const getSelectedElements = useCallback(() => {
    return Array.from(selectedElements);
  }, [selectedElements]);

  // Démarrer une sélection par zone (drag)
  const startSelectionBox = useCallback((startX, startY) => {
    setIsSelecting(true);
    startPosRef.current = { x: startX, y: startY };
  }, []);

  // Mettre à jour la boîte de sélection
  const updateSelectionBox = useCallback((currentX, currentY, elements) => {
    if (!isSelecting) return;

    const startX = startPosRef.current.x;
    const startY = startPosRef.current.y;

    // Calculer les limites de la boîte de sélection
    const left = Math.min(startX, currentX);
    const top = Math.min(startY, currentY);
    const right = Math.max(startX, currentX);
    const bottom = Math.max(startY, currentY);

    // Trouver les éléments dans la boîte
    const elementsInBox = elements.filter(element => {
      const elementLeft = element.x;
      const elementTop = element.y;
      const elementRight = element.x + element.width;
      const elementBottom = element.y + element.height;

      return !(elementRight < left || elementLeft > right ||
               elementBottom < top || elementTop > bottom);
    });

    selectElements(elementsInBox.map(el => el.id));
  }, [isSelecting, selectElements]);

  // Terminer la sélection par zone
  const endSelectionBox = useCallback(() => {
    setIsSelecting(false);
  }, []);

  // Gestionnaire de clic sur un élément
  const handleElementClick = useCallback((elementId, event) => {
    const addToSelection = multiSelect && (event.ctrlKey || event.metaKey);
    selectElement(elementId, addToSelection);
  }, [multiSelect, selectElement]);

  // Gestionnaire de clic sur le canvas (désélection)
  const handleCanvasClick = useCallback((event) => {
    // Ne pas désélectionner si on clique sur un élément
    if (event.target.closest('.canvas-element')) {
      return;
    }
    clearSelection();
  }, [clearSelection]);

  return {
    // État
    selectedElements: getSelectedElements(),
    isSelected,
    isSelecting,
    selectionBox: selectionBoxRef.current,

    // Méthodes
    selectElement,
    selectElements,
    clearSelection,
    getSelectedElements,

    // Gestionnaires d'événements
    handleElementClick,
    handleCanvasClick,
    startSelectionBox,
    updateSelectionBox,
    endSelectionBox
  };
};