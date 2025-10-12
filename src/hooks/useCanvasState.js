import { useState, useCallback, useEffect, useMemo } from 'react';
import { useHistory } from './useHistory';
import { useSelection } from './useSelection';
import { useClipboard } from './useClipboard';
import { useZoom } from './useZoom';
import { useContextMenu } from './useContextMenu';
import { useDragAndDrop } from './useDragAndDrop';
import { ELEMENT_TYPE_MAPPING, fixInvalidProperty } from '../utilities/elementPropertyRestrictions';

// Fallback notification system in case Toastr is not available
if (typeof window !== 'undefined' && typeof window.toastr === 'undefined') {
  console.log('📋 PDF Builder - Toastr non disponible, initialisation du système de fallback...');

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
      console.log('✅ PDF Builder - Notification succès (fallback):', message);
      createNotification('success', title, message);
    },
    error: (message, title) => {
      console.log('❌ PDF Builder - Notification erreur (fallback):', message);
      createNotification('error', title, message);
    },
    warning: (message, title) => {
      console.log('⚠️ PDF Builder - Notification avertissement (fallback):', message);
      createNotification('warning', title, message);
    },
    info: (message, title) => {
      console.log('ℹ️ PDF Builder - Notification info (fallback):', message);
      createNotification('info', title, message);
    },
    options: {} // Placeholder for options
  };

  console.log('✅ PDF Builder - Système de notification fallback initialisé');
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

  // Calculer le prochain ID basé sur les éléments initiaux
  useEffect(() => {
    if (initialElements && initialElements.length > 0) {
      const maxId = Math.max(...initialElements.map(el => {
        const idParts = el.id?.split('_') || [];
        return parseInt(idParts[1] || 0);
      }));
      setNextId(maxId + 1);
      console.log('PDF Builder: Prochain ID calculé:', maxId + 1, 'basé sur', initialElements.length, 'éléments initiaux');
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

  // Sauvegarder l'état dans l'historique à chaque changement
  useEffect(() => {
    if (elements.length > 0 || history.historySize === 0) {
      history.addToHistory({ elements, nextId });
    }
  }, [elements, nextId, history]);

  // Correction automatique des éléments spéciaux existants
  useEffect(() => {
    const specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];
    const needsCorrection = elements.some(element => 
      specialElements.includes(element.type) && element.backgroundColor !== 'transparent'
    );

    if (needsCorrection) {
      console.log('🔧 Correction automatique des éléments spéciaux existants...');
      setElements(prevElements => 
        prevElements.map(element => {
          if (specialElements.includes(element.type) && element.backgroundColor !== 'transparent') {
            console.log(`🔧 Correction de ${element.type} (id: ${element.id}): backgroundColor '${element.backgroundColor}' -> 'transparent'`);
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
    // Système intelligent de propriétés par défaut
    const getDefaultProperties = (type) => {
      // Propriétés de base pour tous les éléments
      const baseDefaults = {
        x: 50,
        y: 50,
        width: 100,
        height: 50,
        color: '#000000',
        fontSize: 14,
        fontFamily: 'Arial, sans-serif',
        fontWeight: 'normal',
        fontStyle: 'normal',
        textDecoration: 'none',
        textAlign: 'left',
        lineHeight: 1.2,
        letterSpacing: 0,
        borderColor: 'transparent',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 4,
        padding: 8
      };

      // Éléments spéciaux qui n'ont pas de fond contrôlable
      const specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];

      // Propriétés spécifiques selon le type
      const typeSpecificDefaults = {
        // Éléments de mise en page
        'layout-header': {
          width: 500,
          height: 80,
          backgroundColor: getOption('default_layout_background', '#f8fafc'),
          borderColor: 'transparent',
          borderWidth: 0
        },
        'layout-footer': {
          width: 500,
          height: 60,
          backgroundColor: getOption('default_layout_background', '#f8fafc'),
          borderColor: 'transparent',
          borderWidth: 0
        },
        'layout-sidebar': {
          width: 150,
          height: 300,
          backgroundColor: getOption('default_layout_background', '#f8fafc'),
          borderColor: 'transparent',
          borderWidth: 0
        },
        'layout-section': {
          width: 500,
          height: 200,
          backgroundColor: getOption('default_layout_background', '#ffffff'),
          borderColor: 'transparent',
          borderWidth: 0
        },
        'layout-container': {
          width: 300,
          height: 150,
          backgroundColor: 'transparent',
          borderColor: 'transparent',
          borderWidth: 0,
          borderStyle: 'dashed'
        },

        // Éléments de texte
        'text': {
          content: 'Texte',
          backgroundColor: getOption('default_text_background', 'transparent'),
          width: 200,
          height: 50
        },

        // Éléments graphiques
        'rectangle': {
          backgroundColor: getOption('default_shape_background', '#e5e7eb'),
          width: 150,
          height: 100
        },
        'line': {
          height: 2,
          backgroundColor: getOption('default_shape_background', '#6b7280'),
          width: 200
        },

        // Éléments spécialisés pour factures
        'invoice-header': {
          width: 500,
          height: 100,
          backgroundColor: getOption('default_special_background', 'transparent'), // Spéciaux = transparent
          borderColor: 'transparent',
          borderWidth: 0,
          content: 'ENTREPRISE\n123 Rue de l\'Entreprise\n75000 Paris\nTéléphone: 01 23 45 67 89\nEmail: contact@entreprise.com',
          fontSize: 12,
          fontWeight: 'normal'
        },
        'invoice-address-block': {
          width: 240,
          height: 120,
          backgroundColor: getOption('default_special_background', 'transparent'),
          borderColor: 'transparent',
          borderWidth: 0,
          borderRadius: 4
        },
        'invoice-info-block': {
          width: 300,
          height: 80,
          backgroundColor: getOption('default_special_background', 'transparent'),
          borderColor: 'transparent',
          borderWidth: 0,
          borderRadius: 4
        },
        'invoice-products-table': {
          width: 500,
          height: 200,
          backgroundColor: getOption('default_special_background', 'transparent'),
          borderColor: 'transparent',
          borderWidth: 0
        },

        // Éléments spéciaux
        'product_table': {
          width: 500,
          height: 200,
          backgroundColor: 'transparent', // Toujours transparent pour les spéciaux
          borderColor: 'transparent',
          borderWidth: 0,
          showHeaders: true,
          showBorders: true,
          headers: ['Produit', 'Qté', 'Prix'],
          dataSource: 'order_items',
          columns: {
            image: true,
            name: true,
            sku: false,
            quantity: true,
            price: true,
            total: true
          },
          showSubtotal: false,
          showShipping: true,
          showTaxes: true,
          showDiscount: false,
          showTotal: false
        },
        'customer_info': {
          width: 300,
          height: 200,
          backgroundColor: 'transparent', // Toujours transparent pour les spéciaux
          borderColor: 'transparent',
          borderWidth: 0,
          fields: ['name', 'email', 'phone', 'address', 'company', 'vat'],
          layout: 'vertical',
          showLabels: true,
          labelStyle: 'bold',
          spacing: 8,
          fontSize: 12,
          fontFamily: 'Arial, sans-serif',
          fontWeight: 'normal',
          fontStyle: 'normal',
          textDecoration: 'none'
        }
      };

      // Fonction helper pour récupérer les options WordPress
      const getOption = (key, defaultValue) => {
        // En attendant l'intégration complète, utiliser des valeurs par défaut intelligentes
        const defaults = {
          default_text_background: 'transparent',
          default_shape_background: '#e5e7eb',
          default_layout_background: '#f8fafc',
          default_special_background: 'transparent'
        };
        return defaults[key] || defaultValue;
      };

      // Fusionner les propriétés de base avec les propriétés spécifiques du type
      const mergedDefaults = { ...baseDefaults, ...(typeSpecificDefaults[type] || {}) };

      // Pour les éléments spéciaux, forcer backgroundColor à transparent
      if (specialElements.includes(type)) {
        mergedDefaults.backgroundColor = 'transparent';
      }

      return mergedDefaults;
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

  const saveTemplate = useCallback(async () => {
    if (isSaving) {
      console.log('🔄 PDF Builder - Sauvegarde déjà en cours, ignorée');
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
            console.log(`🔍 PDF Builder - Propriété exclue: ${key}`);
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
              console.warn(`PDF Builder - Propriété tableau non-sérialisable ignorée: ${key}`, value);
            }
          } else if (typeof value === 'object') {
            // Pour les objets, nettoyer récursivement
            try {
              const cleanedObj = cleanElementForSerialization(value);
              cleaned[key] = cleanedObj;
            } catch (e) {
              console.warn(`PDF Builder - Propriété objet non-sérialisable ignorée: ${key}`, value);
            }
          } else {
            // Pour les autres types (functions, symbols, etc.), ignorer
            console.log(`🔍 PDF Builder - Propriété de type ${typeof value} ignorée: ${key}`);
          }
        }

        return cleaned;
      };

      // Nettoyer tous les éléments
      const cleanedElements = elements.map(cleanElementForSerialization);

      const templateData = {
        elements: cleanedElements,
        canvasWidth,
        canvasHeight,
        version: '1.0'
      };

      console.log('🔍 PDF Builder - Données nettoyées à sauvegarder:', templateData);
      console.log('🔍 PDF Builder - Nombre d\'éléments nettoyés:', cleanedElements.length);

      // Valider le JSON avant envoi
      let jsonString;
      try {
        jsonString = JSON.stringify(templateData);
        console.log('🔍 PDF Builder - JSON stringifié, longueur:', jsonString.length);

        // Tester le parsing pour valider
        const testParse = JSON.parse(jsonString);
        console.log('🔍 PDF Builder - JSON validé côté client');
      } catch (jsonError) {
        console.error('🔍 PDF Builder - ERREUR JSON côté client:', jsonError);
        throw new Error('Données JSON invalides côté client: ' + jsonError.message);
      }

      // Sauvegarde directe via AJAX avec URLSearchParams au lieu de FormData
      console.log('📤 PDF Builder - Tentative avec URLSearchParams au lieu de FormData');

      const requestData = {
        action: 'pdf_builder_pro_save_template',
        template_data: jsonString,
        template_name: window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`,
        template_id: window.pdfBuilderData?.templateId || '0',
        nonce: window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || ''
      };

      console.log('📤 PDF Builder - Données de requête:', requestData);

      const response = await fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(requestData).toString()
      });

      const result = await response.json();
      console.log('📥 PDF Builder - Réponse AJAX:', result);

      if (!result.success) {
        throw new Error(result.data?.message || 'Erreur lors de la sauvegarde');
      }

      // Notification de succès pour les templates existants
      if (isExistingTemplate) {
        console.log('✅ PDF Builder - Affichage notification succès');
        if (toastrAvailable) {
          toastr.success('Modifications du canvas sauvegardées avec succès !');
          console.log('🎉 PDF Builder - Notification toastr affichée');
        } else {
          console.warn('⚠️ PDF Builder - Toastr non disponible, utilisation alert');
          alert('Modifications du canvas sauvegardées avec succès !');
        }
      } else {
        console.log('ℹ️ PDF Builder - Template nouveau, pas de notification');
      }

      return templateData;
    } catch (error) {
      console.error('❌ PDF Builder - Erreur lors de la sauvegarde:', error);

      // Notification d'erreur
      const errorMessage = error.message || 'Erreur inconnue lors de la sauvegarde';
      console.log('🚨 PDF Builder - Affichage notification erreur');
      if (toastrAvailable) {
        toastr.error(`Erreur lors de la sauvegarde: ${errorMessage}`);
        console.log('🚨 PDF Builder - Notification d\'erreur toastr affichée');
      } else {
        console.warn('⚠️ PDF Builder - Toastr non disponible pour erreur, utilisation alert');
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