import { useState, useCallback, useEffect } from 'react';
import { useHistory } from './useHistory';
import { useSelection } from './useSelection';
import { useClipboard } from './useClipboard';
import { useZoom } from './useZoom';
import { useContextMenu } from './useContextMenu';
import { useDragAndDrop } from './useDragAndDrop';

export const useCanvasState = ({
  initialElements = [],
  canvasWidth = 595, // A4 width in points
  canvasHeight = 842, // A4 height in points
  onSave,
  onPreview
}) => {
  const [elements, setElements] = useState(initialElements);
  const [nextId, setNextId] = useState(1);

  const history = useHistory();
  const selection = useSelection({
    onSelectionChange: useCallback((selectedIds) => {
      // Callback pour les changements de sélection
    }, [])
  });

  const clipboard = useClipboard({
    onPaste: useCallback((data) => {
      if (data.type === 'elements') {
        const pastedElements = data.elements.map(element => ({
          ...element,
          id: `element_${nextId + data.elements.indexOf(element)}`,
          x: element.x + 20, // Offset pour éviter la superposition
          y: element.y + 20
        }));

        setElements(prev => [...prev, ...pastedElements]);
        setNextId(prev => prev + pastedElements.length);
        selection.selectAll(pastedElements.map(el => el.id));
      }
    }, [nextId, selection])
  });

  const zoom = useZoom({
    initialZoom: 1,
    minZoom: 0.25,
    maxZoom: 3
  });

  const contextMenu = useContextMenu();

  // Fonction updateElement définie avant useDragAndDrop
  const updateElement = useCallback((elementId, updates) => {
    setElements(prev => prev.map(element =>
      element.id === elementId ? { ...element, ...updates } : element
    ));
  }, []);

  const dragAndDrop = useDragAndDrop({
    onElementMove: useCallback((elementId, position) => {
      updateElement(elementId, position);
    }, [updateElement]),
    onElementDrop: useCallback((elementId, position) => {
      updateElement(elementId, position);
      history.addToHistory({ elements: elements.map(el => 
        el.id === elementId ? { ...el, ...position } : el
      ), nextId });
    }, [updateElement, history, elements, nextId])
  });

  // Sauvegarder l'état dans l'historique à chaque changement
  useEffect(() => {
    if (elements.length > 0 || history.historySize === 0) {
      history.addToHistory({ elements, nextId });
    }
  }, [elements, nextId, history]);

  const addElement = useCallback((elementType, properties = {}) => {
    // Définir les propriétés par défaut selon le type d'élément
    const getDefaultProperties = (type) => {
      const defaults = {
        x: 50,
        y: 50,
        width: 100,
        height: 50,
        backgroundColor: '#ffffff',
        borderColor: '#dddddd',
        borderWidth: 1,
        borderRadius: 4,
        color: '#333333',
        fontSize: 14,
        fontFamily: 'Arial, sans-serif',
        padding: 8
      };

      // Propriétés spécifiques selon le type
      if (type.startsWith('woocommerce-')) {
        switch (type) {
          case 'woocommerce-billing-address':
          case 'woocommerce-shipping-address':
            defaults.width = 250;
            defaults.height = 120;
            break;
          case 'woocommerce-products-table':
            defaults.width = 500;
            defaults.height = 200;
            break;
          case 'woocommerce-invoice-number':
          case 'woocommerce-order-number':
          case 'woocommerce-quote-number':
            defaults.width = 180;
            defaults.height = 50;
            break;
          case 'woocommerce-customer-name':
          case 'woocommerce-customer-email':
          case 'woocommerce-payment-method':
          case 'woocommerce-order-status':
            defaults.width = 200;
            defaults.height = 50;
            break;
          case 'woocommerce-subtotal':
          case 'woocommerce-discount':
          case 'woocommerce-shipping':
          case 'woocommerce-taxes':
          case 'woocommerce-total':
          case 'woocommerce-refund':
          case 'woocommerce-fees':
            defaults.width = 150;
            defaults.height = 50;
            break;
          case 'woocommerce-invoice-date':
          case 'woocommerce-order-date':
          case 'woocommerce-quote-date':
          case 'woocommerce-quote-validity':
            defaults.width = 180;
            defaults.height = 50;
            break;
          case 'woocommerce-quote-notes':
            defaults.width = 400;
            defaults.height = 80;
            break;
          default:
            break;
        }
      } else {
        switch (type) {
          case 'text':
            defaults.content = 'Texte';
            break;
          case 'rectangle':
            defaults.backgroundColor = '#e5e7eb';
            break;
          case 'line':
            defaults.height = 2;
            defaults.backgroundColor = '#6b7280';
            break;
          case 'layout-header':
            defaults.width = 500;
            defaults.height = 80;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = '#e2e8f0';
            defaults.borderWidth = 1;
            break;
          case 'layout-footer':
            defaults.width = 500;
            defaults.height = 60;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = '#e2e8f0';
            defaults.borderWidth = 1;
            break;
          case 'layout-sidebar':
            defaults.width = 150;
            defaults.height = 300;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = '#e2e8f0';
            defaults.borderWidth = 1;
            break;
          case 'layout-section':
            defaults.width = 500;
            defaults.height = 200;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = '#e2e8f0';
            defaults.borderWidth = 1;
            break;
          case 'layout-container':
            defaults.width = 300;
            defaults.height = 150;
            defaults.backgroundColor = 'transparent';
            defaults.borderColor = '#cbd5e1';
            defaults.borderWidth = 2;
            defaults.borderStyle = 'dashed';
            break;
          default:
            break;
        }
      }

      return defaults;
    };

    const defaultProps = getDefaultProperties(elementType);
    const newElement = {
      id: `element_${nextId}`,
      type: elementType,
      ...defaultProps,
      ...properties
    };

    setElements(prev => [...prev, newElement]);
    setNextId(prev => prev + 1);
    selection.selectElement(newElement.id);
  }, [nextId, selection]);

  const deleteElement = useCallback((elementId) => {
    setElements(prev => prev.filter(element => element.id !== elementId));
    selection.clearSelection();
  }, [selection]);

  const deleteSelectedElements = useCallback(() => {
    const elementsToDelete = selection.deleteSelected();
    setElements(prev => prev.filter(element => !elementsToDelete.includes(element.id)));
    selection.clearSelection();
  }, [selection]);

  const duplicateElement = useCallback((elementId) => {
    const element = elements.find(el => el.id === elementId);
    if (element) {
      const duplicatedElement = {
        ...element,
        id: `element_${nextId}`,
        x: element.x + 20,
        y: element.y + 20
      };

      setElements(prev => [...prev, duplicatedElement]);
      setNextId(prev => prev + 1);
      selection.selectElement(duplicatedElement.id);
    }
  }, [elements, nextId, selection]);

  const duplicateSelectedElements = useCallback(() => {
    const elementsToDuplicate = selection.duplicateSelected();
    const duplicatedElements = [];

    elementsToDuplicate.forEach(elementId => {
      const element = elements.find(el => el.id === elementId);
      if (element) {
        const duplicatedElement = {
          ...element,
          id: `element_${nextId + duplicatedElements.length}`,
          x: element.x + 20,
          y: element.y + 20
        };
        duplicatedElements.push(duplicatedElement);
      }
    });

    if (duplicatedElements.length > 0) {
      setElements(prev => [...prev, ...duplicatedElements]);
      setNextId(prev => prev + duplicatedElements.length);
      selection.selectAll(duplicatedElements.map(el => el.id));
    }
  }, [elements, nextId, selection]);

  const copySelectedElements = useCallback(() => {
    const selectedIds = selection.selectedElements;
    const selectedElementsData = elements.filter(el => selectedIds.includes(el.id));

    if (selectedElementsData.length > 0) {
      clipboard.copy({
        type: 'elements',
        elements: selectedElementsData
      });
    }
  }, [elements, selection, clipboard]);

  const pasteElements = useCallback(() => {
    clipboard.paste();
  }, [clipboard]);

  const undo = useCallback(() => {
    const previousState = history.undo();
    if (previousState) {
      setElements(previousState.elements);
      setNextId(previousState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);

  const redo = useCallback(() => {
    const nextState = history.redo();
    if (nextState) {
      setElements(nextState.elements);
      setNextId(nextState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);

  const saveTemplate = useCallback(() => {
    const templateData = {
      elements,
      canvasWidth,
      canvasHeight,
      version: '1.0'
    };

    if (onSave) {
      onSave(templateData);
    }

    return templateData;
  }, [elements, canvasWidth, canvasHeight, onSave]);

  const loadTemplate = useCallback((templateData) => {
    if (templateData.elements) {
      setElements(templateData.elements);
      setNextId(templateData.nextId || Math.max(...templateData.elements.map(el => parseInt(el.id.split('_')[1])) || [0]) + 1);
      selection.clearSelection();
      history.clearHistory();
    }
  }, [selection, history]);

  const showContextMenu = useCallback((x, y, targetElementId = null) => {
    const menuItems = [];

    if (targetElementId) {
      menuItems.push(
        { label: 'Dupliquer', action: () => duplicateElement(targetElementId) },
        { label: 'Supprimer', action: () => deleteElement(targetElementId) },
        { type: 'separator' },
        { label: 'Copier', action: copySelectedElements },
        { label: 'Coller', action: pasteElements, disabled: !clipboard.hasData() }
      );
    } else if (selection.selectedElements.length > 0) {
      menuItems.push(
        { label: 'Dupliquer', action: duplicateSelectedElements },
        { label: 'Supprimer', action: deleteSelectedElements },
        { type: 'separator' },
        { label: 'Copier', action: copySelectedElements },
        { label: 'Coller', action: pasteElements, disabled: !clipboard.hasData() }
      );
    } else {
      menuItems.push(
        { label: 'Coller', action: pasteElements, disabled: !clipboard.hasData() }
      );
    }

    contextMenu.showContextMenu(x, y, menuItems);
  }, [selection, contextMenu, duplicateElement, deleteElement, copySelectedElements, pasteElements, clipboard, duplicateSelectedElements, deleteSelectedElements]);

  return {
    // État
    elements,
    canvasWidth,
    canvasHeight,

    // Hooks intégrés
    selection,
    zoom,
    contextMenu,
    dragAndDrop,

    // Actions sur les éléments
    addElement,
    updateElement,
    deleteElement,
    deleteSelectedElements,
    duplicateElement,
    duplicateSelectedElements,

    // Presse-papiers
    copySelectedElements,
    pasteElements,

    // Historique
    undo,
    redo,
    canUndo: history.canUndo(),
    canRedo: history.canRedo(),

    // Template
    saveTemplate,
    loadTemplate,

    // Menu contextuel
    showContextMenu,

    // Utilitaires
    getElementById: useCallback((id) => elements.find(el => el.id === id), [elements])
  };
};