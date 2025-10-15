import { useState, useCallback, useEffect, useMemo, useRef } from 'react';
import { useHistory } from './useHistory';
import { useSelection } from './useSelection';
import { useClipboard } from './useClipboard';
import { useZoom } from './useZoom';
import { useContextMenu } from './useContextMenu';
import { useDragAndDrop } from './useDragAndDrop';
import { ELEMENT_TYPE_MAPPING, fixInvalidProperty } from '../utilities/elementPropertyRestrictions';

// Fallback notification system in case Toastr is not available
if (typeof window !== 'undefined' && typeof window.toastr === 'undefined') {
  // Simple notification system
  const createNotification = (type, title, message) => {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 999999;
      padding: 15px 20px;
      margin-bottom: 10px;
      border-radius: 5px;
      color: white;
      font-family: Arial, sans-serif;
      font-size: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      max-width: 300px;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s ease;
    `;

    // Set colors based on type
    switch (type) {
      case 'success':
        notification.style.backgroundColor = '#51A351';
        break;
      case 'error':
        notification.style.backgroundColor = '#BD362F';
        break;
      case 'warning':
        notification.style.backgroundColor = '#F89406';
        break;
      case 'info':
      default:
        notification.style.backgroundColor = '#2F96B4';
        break;
    }

    // Create content
    const titleElement = title ? `<strong>${title}</strong><br>` : '';
    notification.innerHTML = `${titleElement}${message}`;

    // Add close button
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '×';
    closeButton.style.cssText = `
      position: absolute;
      top: 5px;
      right: 10px;
      background: none;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      opacity: 0.8;
    `;
    closeButton.onclick = () => removeNotification(notification);
    notification.appendChild(closeButton);

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
      notification.style.opacity = '1';
      notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(() => removeNotification(notification), 5000);

    function removeNotification(el) {
      el.style.opacity = '0';
      el.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (el.parentNode) {
          el.parentNode.removeChild(el);
        }
      }, 300);
    }
  };

  // Create fallback toastr object
  window.toastr = {
    success: (message, title) => {
      createNotification('success', title, message);
    },
    error: (message, title) => {
      createNotification('error', title, message);
    },
    warning: (message, title) => {
      createNotification('warning', title, message);
    },
    info: (message, title) => {
      createNotification('info', title, message);
    },
    options: {} // Placeholder for options
  };
}

export const useCanvasState = ({
  initialElements = [],
  templateId = null,
  canvasWidth = 595, // A4 width in points
  canvasHeight = 842, // A4 height in points
  globalSettings = null
}) => {
  const [elements, setElements] = useState(initialElements);
  const [nextId, setNextId] = useState(1);
  const [isSaving, setIsSaving] = useState(false);

  const history = useHistory();
  const selection = useSelection({
    onSelectionChange: useCallback((selectedIds) => {
      // Callback pour les changements de sélection
    }, [])
  });

  const historyRef = useRef(history);
  const selectionRef = useRef(selection);

  // Mettre à jour les refs quand les valeurs changent
  useEffect(() => {
    historyRef.current = history;
  }, [history]);

  useEffect(() => {
    selectionRef.current = selection;
  }, [selection]);

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

  // Fonction updateElement définie après history
  const updateElement = useCallback((elementId, updates) => {
    setElements(prev => {
      const newElements = prev.map(element =>
        element.id === elementId ? { ...element, ...updates } : element
      );
      // Sauvegarder dans l'historique
      history.addToHistory({ elements: newElements, nextId });
      return newElements;
    });
  }, [history, nextId]);

  // Calculer le prochain ID basé sur les éléments initiaux
  useEffect(() => {
    if (initialElements && initialElements.length > 0) {
      const maxId = Math.max(...initialElements.map(el => {
        const idParts = el.id?.split('_') || [];
        return parseInt(idParts[1] || 0);
      }));
      setNextId(maxId + 1);
    } else {
      setNextId(1);
    }
  }, [initialElements]);

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

  // Fonction utilitaire pour nettoyer les éléments avant sauvegarde (éviter les références DOM)
  const cleanElementsForHistory = useCallback((elementsToClean) => {
    return elementsToClean.map(element => {
      const cleaned = { ...element };
      // Supprimer les propriétés non sérialisables qui pourraient contenir des références DOM
      const nonSerializableProps = ['domElement', 'ref', 'eventListeners', 'component', 'render'];
      nonSerializableProps.forEach(prop => {
        delete cleaned[prop];
      });
      return cleaned;
    });
  }, []);

  // Sauvegarder l'état dans l'historique à chaque changement
  useEffect(() => {
    if (elements.length > 0 || history.historySize === 0) {
      const cleanedElements = cleanElementsForHistory(elements);
      history.addToHistory({ elements: cleanedElements, nextId });
    }
  }, [elements, nextId, history, cleanElementsForHistory]);

  // Correction automatique des éléments spéciaux existants
  useEffect(() => {
    const specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];
    const needsCorrection = elements.some(element => 
      specialElements.includes(element.type) && element.backgroundColor !== 'transparent'
    );

    if (needsCorrection) {
      setElements(prevElements =>
        prevElements.map(element => {
          if (specialElements.includes(element.type) && element.backgroundColor !== 'transparent') {
            return {
              ...element,
              backgroundColor: 'transparent'
            };
          }
          return element;
        })
      );
    }
  }, []); // Uniquement au montage du composant

  const addElement = useCallback((elementType, properties = {}) => {
    // Utiliser les refs pour accéder aux valeurs actuelles
    const currentHistory = historyRef.current;
    const currentSelection = selectionRef.current;

    // Vérifications de sécurité
    if (!currentSelection || !currentHistory) {
      return;
    }

    if (typeof currentSelection.selectElement !== 'function') {
      return;
    }

    if (typeof currentHistory.addToHistory !== 'function') {
      return;
    }

    // Propriétés par défaut simplifiées
    const defaultProps = {
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      color: '#000000',
      fontSize: 14,
      backgroundColor: 'transparent'
    };

    const newElement = {
      id: `element_${nextId}`,
      type: elementType,
      ...defaultProps,
      ...properties
    };

    setElements(prev => {
      const newElements = [...prev, newElement];
      // Sauvegarder dans l'historique
      try {
        if (currentHistory && typeof currentHistory.addToHistory === 'function') {
          currentHistory.addToHistory({ elements: newElements, nextId: nextId + 1 });
        }
      } catch (error) {
      }
      return newElements;
    });

    setNextId(prev => prev + 1);

    try {
      if (currentSelection && typeof currentSelection.selectElement === 'function') {
        currentSelection.selectElement(newElement.id);
      }
    } catch (error) {
    }
  }, [nextId]); // Retirer selection et history des dépendances

  const deleteElement = useCallback((elementId) => {
    setElements(prev => {
      const newElements = prev.filter(element => element.id !== elementId);
      // Sauvegarder dans l'historique
      history.addToHistory({ elements: newElements, nextId });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);

  const deleteSelectedElements = useCallback(() => {
    const elementsToDelete = selection.deleteSelected();
    setElements(prev => {
      const newElements = prev.filter(element => !elementsToDelete.includes(element.id));
      // Sauvegarder dans l'historique
      history.addToHistory({ elements: newElements, nextId });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);

  const duplicateElement = useCallback((elementId) => {
    const element = elements.find(el => el.id === elementId);
    if (element) {
      const duplicatedElement = {
        ...element,
        id: `element_${nextId}`,
        x: element.x + 20,
        y: element.y + 20
      };

      setElements(prev => {
        const newElements = [...prev, duplicatedElement];
        // Sauvegarder dans l'historique
        history.addToHistory({ elements: newElements, nextId: nextId + 1 });
        return newElements;
      });
      setNextId(prev => prev + 1);
      selection.selectElement(duplicatedElement.id);
    }
  }, [elements, nextId, selection, history]);

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
      setElements(prev => {
        const newElements = [...prev, ...duplicatedElements];
        // Sauvegarder dans l'historique
        history.addToHistory({ elements: newElements, nextId: nextId + duplicatedElements.length });
        return newElements;
      });
      setNextId(prev => prev + duplicatedElements.length);
      selection.selectAll(duplicatedElements.map(el => el.id));
    }
  }, [elements, nextId, selection, history]);

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

  const saveTemplate = useCallback(async () => {
    if (isSaving) {
      return;
    }

    setIsSaving(true);

    // Déterminer si c'est un template existant
    const isExistingTemplate = templateId && templateId !== '0' && templateId !== 0;

    // Fonction pour vérifier la disponibilité de Toastr avec retry
    const checkToastrAvailability = () => {
      return Promise.resolve(true); // Toastr is now always available (real or fallback)
    };

    const toastrAvailable = await checkToastrAvailability();

    try {
      // Fonction pour nettoyer les données avant sérialisation
      const cleanElementForSerialization = (element) => {
        // Liste des propriétés à exclure car elles ne sont pas sérialisables
        const excludedProps = [
          'domElement', 'eventListeners', 'ref', 'onClick', 'onMouseDown',
          'onMouseUp', 'onMouseMove', 'onContextMenu', 'onDoubleClick',
          'onDragStart', 'onDragEnd', 'onResize', 'component', 'render',
          'props', 'state', 'context', 'refs', '_reactInternalInstance',
          '_reactInternals', '$$typeof', 'constructor', 'prototype'
        ];

        const cleaned = {};

        for (const [key, value] of Object.entries(element)) {
          // Exclure les propriétés problématiques
          if (excludedProps.includes(key)) {
            continue;
          }

          // Vérifier le type de valeur
          if (value === null || value === undefined) {
            cleaned[key] = value;
          } else if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
            cleaned[key] = value;
          } else if (Array.isArray(value)) {
            // Pour les tableaux, vérifier chaque élément
            try {
              const cleanedArray = value.map(item => {
                if (typeof item === 'object' && item !== null) {
                  return cleanElementForSerialization(item);
                }
                return item;
              });
              JSON.stringify(cleanedArray); // Test de sérialisation
              cleaned[key] = cleanedArray;
            } catch (e) {
            }
          } else if (typeof value === 'object') {
            // Pour les objets, nettoyer récursivement
            try {
              const cleanedObj = cleanElementForSerialization(value);
              cleaned[key] = cleanedObj;
            } catch (e) {
            }
          } else {
            // Pour les autres types (functions, symbols, etc.), ignorer
          }
        }

        return cleaned;
      };

      // Nettoyer tous les éléments
      const cleanedElements = elements.map(cleanElementForSerialization);

      // Log détaillé des propriétés de chaque élément
      elements.forEach((element, index) => {
        console.log(`Élément ${index} (${element.type}) propriétés avant nettoyage:`, Object.keys(element));
        if (element.type === 'product_table') {
          console.log(`Tableau ${index} - paramètres:`, {
            showHeaders: element.showHeaders,
            showBorders: element.showBorders,
            columns: element.columns,
            tableStyle: element.tableStyle,
            showSubtotal: element.showSubtotal,
            showShipping: element.showShipping,
            showTaxes: element.showTaxes,
            showDiscount: element.showDiscount,
            showTotal: element.showTotal
          });
        }
      });

      console.log('Éléments nettoyés pour sauvegarde:', cleanedElements);

      const templateData = {
        elements: cleanedElements,
        canvasWidth,
        canvasHeight,
        version: '1.0'
      };

      console.log('Données template à sauvegarder:', templateData);

      // Valider le JSON avant envoi
      let jsonString;
      try {
        jsonString = JSON.stringify(templateData);

        // Tester le parsing pour valider
        const testParse = JSON.parse(jsonString);
      } catch (jsonError) {
        throw new Error('Données JSON invalides côté client: ' + jsonError.message);
      }

      // Sauvegarde directe via AJAX avec FormData pour les données volumineuses

      const formData = new FormData();
      formData.append('action', 'pdf_builder_pro_save_template');
      formData.append('template_data', jsonString);
      formData.append('template_name', window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`);
      formData.append('template_id', window.pdfBuilderData?.templateId || '0');
      formData.append('nonce', window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '');

      const response = await fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data?.message || 'Erreur lors de la sauvegarde');
      }

      // Notification de succès pour les templates existants
      if (isExistingTemplate) {
        if (toastrAvailable) {
          toastr.success('Modifications du canvas sauvegardées avec succès !');
        } else {
          alert('Modifications du canvas sauvegardées avec succès !');
        }
      }

      return templateData;
    } catch (error) {

      // Notification d'erreur
      const errorMessage = error.message || 'Erreur inconnue lors de la sauvegarde';
      if (toastrAvailable) {
        toastr.error(`Erreur lors de la sauvegarde: ${errorMessage}`);
      } else {
        alert(`Erreur lors de la sauvegarde: ${errorMessage}`);
      }

      throw error; // Re-throw pour permettre la gestion d'erreur en amont si nécessaire
    } finally {
      setIsSaving(false);
    }
  }, [elements, canvasWidth, canvasHeight, isSaving, templateId]);

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

  return useMemo(() => ({
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
    isSaving,

    // Menu contextuel
    showContextMenu,

    // Utilitaires
    getAllElements: useCallback(() => elements, [elements]),
    getElementById: useCallback((id) => elements.find(el => el.id === id), [elements])
  }), [
    elements,
    canvasWidth,
    canvasHeight,
    selection,
    zoom,
    contextMenu,
    dragAndDrop,
    addElement,
    updateElement,
    deleteElement,
    deleteSelectedElements,
    duplicateElement,
    duplicateSelectedElements,
    copySelectedElements,
    pasteElements,
    undo,
    redo,
    history,
    showContextMenu
  ]);
};