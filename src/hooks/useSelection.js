import { useState, useCallback } from '@wordpress/element';

export const useSelection = ({
  onSelectionChange,
  multiSelect = true
}) => {
  const [selectedElements, setSelectedElements] = useState([]);
  const [selectionBox, setSelectionBox] = useState(null);

  const selectElement = useCallback((elementId, addToSelection = false) => {
    if (multiSelect && addToSelection) {
      setSelectedElements(prev => {
        const isAlreadySelected = prev.includes(elementId);
        const newSelection = isAlreadySelected
          ? prev.filter(id => id !== elementId)
          : [...prev, elementId];

        onSelectionChange?.(newSelection);
        return newSelection;
      });
    } else {
      setSelectedElements([elementId]);
      onSelectionChange?.([elementId]);
    }
  }, [multiSelect, onSelectionChange]);

  const selectAll = useCallback((elementIds) => {
    setSelectedElements(elementIds);
    onSelectionChange?.(elementIds);
  }, [onSelectionChange]);

  const clearSelection = useCallback(() => {
    setSelectedElements([]);
    onSelectionChange?.([]);
  }, [onSelectionChange]);

  const isSelected = useCallback((elementId) => {
    return selectedElements.includes(elementId);
  }, [selectedElements]);

  const startSelectionBox = useCallback((startX, startY) => {
    setSelectionBox({
      startX,
      startY,
      endX: startX,
      endY: startY
    });
  }, []);

  const updateSelectionBox = useCallback((endX, endY) => {
    setSelectionBox(prev => prev ? {
      ...prev,
      endX,
      endY
    } : null);
  }, []);

  const endSelectionBox = useCallback((elements) => {
    if (!selectionBox) return;

    const { startX, startY, endX, endY } = selectionBox;
    const minX = Math.min(startX, endX);
    const maxX = Math.max(startX, endX);
    const minY = Math.min(startY, endY);
    const maxY = Math.max(startY, endY);

    const selectedInBox = elements.filter(element => {
      const elementCenterX = element.x + element.width / 2;
      const elementCenterY = element.y + element.height / 2;

      return elementCenterX >= minX && elementCenterX <= maxX &&
             elementCenterY >= minY && elementCenterY <= maxY;
    }).map(element => element.id);

    if (selectedInBox.length > 0) {
      if (multiSelect) {
        setSelectedElements(prev => {
          const newSelection = [...new Set([...prev, ...selectedInBox])];
          onSelectionChange?.(newSelection);
          return newSelection;
        });
      } else {
        setSelectedElements(selectedInBox);
        onSelectionChange?.(selectedInBox);
      }
    }

    setSelectionBox(null);
  }, [selectionBox, multiSelect, onSelectionChange]);

  const deleteSelected = useCallback(() => {
    // Cette fonction retourne les IDs à supprimer, la logique de suppression
    // sera gérée par le composant parent
    return [...selectedElements];
  }, [selectedElements]);

  const duplicateSelected = useCallback(() => {
    // Cette fonction retourne les IDs à dupliquer, la logique de duplication
    // sera gérée par le composant parent
    return [...selectedElements];
  }, [selectedElements]);

  return {
    selectedElements,
    selectionBox,
    selectElement,
    selectAll,
    clearSelection,
    isSelected,
    startSelectionBox,
    updateSelectionBox,
    endSelectionBox,
    deleteSelected,
    duplicateSelected
  };
};
