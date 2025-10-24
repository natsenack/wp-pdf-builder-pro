import { useState, useCallback, useEffect, useMemo, useRef } from 'react';
import { useHistory } from './useHistory';
import { useSelection } from './useSelection';
import { useClipboard } from './useClipboard';
import { useZoom } from './useZoom';
import { useContextMenu } from './useContextMenu';
import { useDragAndDrop } from './useDragAndDrop';
import { ELEMENT_TYPE_MAPPING, fixInvalidProperty } from '../utils/elementPropertyRestrictions';

// Hook utilitaire pour synchroniser les refs
const useLatest = (value) => {
  const ref = useRef(value);
  ref.current = value;
  return ref;
};

// Fallback notification system in case Toastr is not available
if (typeof window !== 'undefined' && typeof window.toastr === 'undefined') {
  // Simple notification system
  const createNotification = (type, title, message) => {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 100;
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
  // Logs conditionnels selon l'environnement
  const isDevelopment = process.env.NODE_ENV === 'development';
  const [elements, setElements] = useState(initialElements);
  const [nextId, setNextId] = useState(1);
  const [isSaving, setIsSaving] = useState(false);

  // États de chargement granulaires pour meilleure UX
  const [loadingStates, setLoadingStates] = useState({
    saving: false,
    loading: false,
    duplicating: false,
    deleting: false
  });

  const history = useHistory();
  const selection = useSelection({
    onSelectionChange: useCallback((selectedIds) => {
      // Callback pour les changements de sélection
    }, []),
    multiSelect: globalSettings?.multiSelect ?? true
  });

  // Synchronisation parfaite des refs avec useLatest
  const historyRef = useLatest(history);
  const selectionRef = useLatest(selection);

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
    initialZoom: globalSettings ? parseFloat(globalSettings.defaultZoom) / 100 : 1,
    minZoom: globalSettings ? globalSettings.minZoom / 100 : 0.25,
    maxZoom: globalSettings ? globalSettings.maxZoom / 100 : 3,
    zoomStep: globalSettings ? globalSettings.zoomStep / 100 : 0.1
  });

  const contextMenu = useContextMenu();

  // Fonction updateElement définie après history
  const updateElement = useCallback((elementId, updates) => {
    setElements(prev => {
      const newElements = prev.map(element => {
        if (element.id === elementId) {
          const updatedElement = { ...element, ...updates };
          // Pour les dividers, forcer la hauteur à 16px
          if (element.type === 'divider') {
            updatedElement.height = 16;
          }
          // Pour les lignes, forcer la hauteur à 12px
          if (element.type === 'line') {
            updatedElement.height = 12;
          }
          return updatedElement;
        }
        return element;
      });
      return newElements;
    });
  }, []); // Retirer les dépendances pour éviter les re-renders inutiles

  // Effet séparé pour l'historique - optimisation des performances
  useEffect(() => {
    if (elements.length > 0) {
      try {
        if (historyRef.current && typeof historyRef.current.addToHistory === 'function') {
          historyRef.current.addToHistory({ elements, nextId });
        }
      } catch (error) {
        // Continuer l'exécution malgré l'erreur d'historique
      }
    }
  }, [elements, nextId]);

  // Validation des données d'entrée (initialElements)
  const validateInitialElements = useCallback((elements) => {
    if (!Array.isArray(elements)) {
      return [];
    }

    return elements.map(element => {
      if (!element.id || !element.type) {
        return null;
      }
      return element;
    }).filter(Boolean);
  }, []);

  // Calculer le prochain ID basé sur les éléments initiaux validés
  useEffect(() => {
    const validatedElements = validateInitialElements(initialElements);
    setElements(validatedElements);

    if (validatedElements && validatedElements.length > 0) {
      const maxId = Math.max(...validatedElements.map(el => {
        const idParts = el.id?.split('_') || [];
        return parseInt(idParts[1] || 0);
      }));
      setNextId(maxId + 1);
    } else {
      setNextId(1);
    }
  }, [initialElements, validateInitialElements]);

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

    // Propriétés par défaut complètes et synchronisées
    const defaultProps = {
      // Position et dimensions
      x: 50,
      y: 50,
      width: 100,
      height: 50,

      // Apparence de base
      backgroundColor: 'transparent',
      borderColor: 'transparent',
      borderWidth: 0,
      borderStyle: 'solid',
      borderRadius: 0,

      // Typographie
      color: '#1e293b',
      fontFamily: 'Inter, sans-serif',
      fontSize: 14,
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',

      // Contenu
      text: 'Texte',

      // Propriétés avancées
      opacity: 100,
      rotation: 0,
      scale: 100,
      visible: true,

      // Images et médias
      src: '',
      alt: '',
      objectFit: 'cover',
      brightness: 100,
      contrast: 100,
      saturate: 100,

      // Effets
      shadow: false,
      shadowColor: '#000000',
      shadowOffsetX: 2,
      shadowOffsetY: 2,

      // Propriétés spécifiques aux tableaux
      showHeaders: true,
      showBorders: true,
      dataSource: 'order_items',
      showSubtotal: false,
      showShipping: true,
      showTaxes: true,
      showDiscount: false,
      showTotal: false,
      tableStyleChoice: Math.random() < 0.5 ? 'minimal' : 'striped', // Initialiser une seule fois
      tablePrimaryColor: '#667eea', // Couleur primaire par défaut
      tableSecondaryColor: '#f5f5f5', // Couleur secondaire par défaut

      // Propriétés de barre de progression
      progressColor: '#3b82f6',
      progressValue: 75,

      // Propriétés de code et lignes
      lineColor: '#64748b',
      lineWidth: 2,

      // Propriétés de document
      documentType: 'invoice',
      imageUrl: '',

      // Propriétés de mise en page
      spacing: 8,
      layout: 'vertical',
      alignment: 'left',
      fit: 'contain'
    };

    const newElement = {
      id: `element_${nextId}`,
      type: elementType,
      ...defaultProps,
      ...properties
    };

    // Ajustements spécifiques selon le type d'élément
    if (elementType === 'line') {
      newElement.height = 12; // Hauteur fixe pour les lignes
      // Supprimé: newElement.width = 200; // Les lignes utilisent maintenant la largeur complète
    } else if (elementType === 'divider') {
      newElement.height = 16; // Hauteur fixe pour les séparateurs
      newElement.width = 200; // Largeur par défaut pour les séparateurs
    }

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
    // console.log('🔄 PDF Builder SAVE - Bouton "modifier" cliqué');
    // console.log('📊 PDF Builder SAVE - État actuel:', {
    //   templateId,
    //   elementsCount: elements.length,
    //   isSaving: loadingStates.saving,
    //   canvasWidth,
    //   canvasHeight
    // });

    if (loadingStates.saving) {
      // console.log('⚠️ PDF Builder SAVE - Sauvegarde déjà en cours, annulation');
      return;
    }

    // console.log('🚀 PDF Builder SAVE - Démarrage de la sauvegarde');
    setLoadingStates(prev => ({ ...prev, saving: true }));

    // Déterminer si c'est un template existant
    const isExistingTemplate = templateId && templateId !== '0' && templateId !== 0;

    // Fonction pour vérifier la disponibilité de Toastr avec retry
    const checkToastrAvailability = () => {
      return Promise.resolve(true); // Toastr is now always available (real or fallback)
    };

    const toastrAvailable = await checkToastrAvailability();

    try {
      // Fonction pour nettoyer et valider les données avant sérialisation
      const cleanElementForSerialization = (element) => {
        // Liste des propriétés à exclure car elles ne sont pas sérialisables
        const excludedProps = [
          'domElement', 'eventListeners', 'ref', 'onClick', 'onMouseDown',
          'onMouseUp', 'onMouseMove', 'onContextMenu', 'onDoubleClick',
          'onDragStart', 'onDragEnd', 'onResize', 'component', 'render',
          'props', 'state', 'context', 'refs', '_reactInternalInstance',
          '_reactInternals', '$$typeof', 'constructor', 'prototype',
          // Propriétés React spécifiques
          '_owner', '_store', 'key', 'ref', '_self', '_source'
        ];

        const cleaned = {};

        for (const [key, value] of Object.entries(element)) {
          // Exclure les propriétés problématiques
          if (excludedProps.includes(key)) {
            continue;
          }

          // Exclure les propriétés qui commencent par underscore (privées React)
          if (key.startsWith('_')) {
            continue;
          }

          // Validation et correction selon le type de propriété
          let validatedValue = value;

          // Propriétés numériques
          const numericProps = [
            'x', 'y', 'width', 'height', 'fontSize', 'opacity',
            'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth',
            'borderRadius', 'rotation', 'padding', 'scale',
            'shadowOffsetX', 'shadowOffsetY', 'brightness', 'contrast', 'saturate',
            'progressValue', 'lineWidth', 'spacing'
          ];

          if (numericProps.includes(key)) {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              // Valeurs par défaut
              const defaults = {
                x: 0, y: 0, width: 100, height: 50, fontSize: 14,
                opacity: 1, lineHeight: 1.2, letterSpacing: 0, zIndex: 0,
                borderWidth: 0, borderRadius: 0, rotation: 0, padding: 0
              };
              validatedValue = defaults[key] || 0;
            }
          }

          // Propriétés de couleur
          const colorProps = ['color', 'backgroundColor', 'borderColor', 'shadowColor', 'progressColor', 'lineColor'];
          if (colorProps.includes(key)) {
            if (value && value !== 'transparent') {
              // Normaliser les couleurs
              if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                // Couleurs nommées communes
                const namedColors = {
                  'black': '#000000', 'white': '#ffffff', 'red': '#ff0000',
                  'green': '#008000', 'blue': '#0000ff', 'gray': '#808080',
                  'grey': '#808080', 'transparent': 'transparent'
                };
                validatedValue = namedColors[value.toLowerCase()] || '#000000';
              }
            }
          }

          // Propriétés de style de texte
          if (key === 'fontWeight') {
            const validWeights = ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
            if (!validWeights.includes(value)) {
              validatedValue = 'normal';
            }
          }

          if (key === 'textAlign') {
            const validAligns = ['left', 'center', 'right', 'justify'];
            if (!validAligns.includes(value)) {
              validatedValue = 'left';
            }
          }

          if (key === 'textDecoration') {
            const validDecorations = ['none', 'underline', 'overline', 'line-through'];
            if (!validDecorations.includes(value)) {
              validatedValue = 'none';
            }
          }

          if (key === 'textTransform') {
            const validTransforms = ['none', 'capitalize', 'uppercase', 'lowercase'];
            if (!validTransforms.includes(value)) {
              validatedValue = 'none';
            }
          }

          if (key === 'borderStyle') {
            const validStyles = ['solid', 'dashed', 'dotted', 'double', 'none'];
            if (!validStyles.includes(value)) {
              validatedValue = 'solid';
            }
          }

          // Propriétés de texte et contenu
          if (key === 'text' || key === 'content') {
            if (typeof value !== 'string') {
              validatedValue = '';
            }
          }

          // Propriétés de police
          if (key === 'fontFamily') {
            if (typeof value !== 'string' || value.trim() === '') {
              validatedValue = 'Inter, sans-serif';
            }
          }

          if (key === 'fontStyle') {
            const validStyles = ['normal', 'italic', 'oblique'];
            if (!validStyles.includes(value)) {
              validatedValue = 'normal';
            }
          }

          // Propriétés de visibilité et transformation
          if (key === 'visible') {
            if (typeof value !== 'boolean') {
              validatedValue = true;
            }
          }

          if (key === 'scale') {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 100;
            }
            // Limiter la scale entre 10 et 500
            validatedValue = Math.max(10, Math.min(500, validatedValue));
          }

          // Propriétés d'ombre
          if (key === 'shadow') {
            if (typeof value !== 'boolean') {
              validatedValue = false;
            }
          }

          if (key === 'shadowColor') {
            if (value && value !== 'transparent') {
              if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                const namedColors = {
                  'black': '#000000', 'white': '#ffffff', 'red': '#ff0000',
                  'green': '#008000', 'blue': '#0000ff', 'gray': '#808080',
                  'grey': '#808080', 'transparent': 'transparent'
                };
                validatedValue = namedColors[value.toLowerCase()] || '#000000';
              }
            }
          }

          const shadowOffsetProps = ['shadowOffsetX', 'shadowOffsetY'];
          if (shadowOffsetProps.includes(key)) {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 2;
            }
          }

          // Propriétés d'image et médias
          if (key === 'brightness' || key === 'contrast' || key === 'saturate') {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 100;
            }
            // Limiter entre 0 et 200
            validatedValue = Math.max(0, Math.min(200, validatedValue));
          }

          if (key === 'objectFit') {
            const validFits = ['fill', 'contain', 'cover', 'none', 'scale-down'];
            if (!validFits.includes(value)) {
              validatedValue = 'cover';
            }
          }

          // Propriétés de tableau
          const booleanTableProps = [
            'showHeaders', 'showBorders', 'showSubtotal', 'showShipping',
            'showTaxes', 'showDiscount', 'showTotal'
          ];
          if (booleanTableProps.includes(key)) {
            if (typeof value !== 'boolean') {
              validatedValue = false;
            }
          }

          if (key === 'dataSource') {
            const validSources = ['order_items', 'cart_items', 'custom'];
            if (!validSources.includes(value)) {
              validatedValue = 'order_items';
            }
          }

          // Propriétés de barre de progression
          if (key === 'progressValue') {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 0;
            }
            validatedValue = Math.max(0, Math.min(100, validatedValue));
          }

          // Propriétés de ligne/code
          if (key === 'lineWidth') {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 2;
            }
            validatedValue = Math.max(1, Math.min(10, validatedValue));
          }

          // Propriétés de mise en page
          if (key === 'spacing') {
            if (typeof value === 'string' && value !== '' && !isNaN(value)) {
              validatedValue = parseFloat(value);
            } else if (typeof value !== 'number') {
              validatedValue = 8;
            }
          }

          if (key === 'layout') {
            const validLayouts = ['vertical', 'horizontal', 'grid'];
            if (!validLayouts.includes(value)) {
              validatedValue = 'vertical';
            }
          }

          if (key === 'alignment') {
            const validAlignments = ['left', 'center', 'right', 'justify'];
            if (!validAlignments.includes(value)) {
              validatedValue = 'left';
            }
          }

          if (key === 'fit') {
            const validFits = ['contain', 'cover', 'fill', 'none'];
            if (!validFits.includes(value)) {
              validatedValue = 'contain';
            }
          }

          // Propriétés de document
          if (key === 'documentType') {
            const validTypes = ['invoice', 'quote', 'receipt', 'order'];
            if (!validTypes.includes(value)) {
              validatedValue = 'invoice';
            }
          }

          // Propriétés d'objet complexes
          if (key === 'columns') {
            if (typeof value === 'object' && value !== null) {
              validatedValue = {
                image: value.image ?? true,
                name: value.name ?? true,
                sku: value.sku ?? false,
                quantity: value.quantity ?? true,
                price: value.price ?? true,
                total: value.total ?? true
              };
            } else {
              validatedValue = {
                image: true, name: true, sku: false,
                quantity: true, price: true, total: true
              };
            }
          }

          // Vérifier le type de valeur
          if (validatedValue === null || validatedValue === undefined) {
            // Ne pas inclure les propriétés null/undefined
            continue; // Skip this property but continue cleaning the rest
          } else if (typeof validatedValue === 'string' || typeof validatedValue === 'number' || typeof validatedValue === 'boolean') {
            cleaned[key] = validatedValue;
          } else if (Array.isArray(validatedValue)) {
            // Pour les tableaux, nettoyer chaque élément de manière très stricte
            try {
              const cleanedArray = validatedValue
                .filter(item => item !== null && item !== undefined) // Filtrer les valeurs null/undefined
                .map(item => {
                  if (typeof item === 'object' && item !== null) {
                    // Pour les objets dans les tableaux, seulement garder les propriétés primitives
                    const cleanedItem = {};
                    for (const [itemKey, itemValue] of Object.entries(item)) {
                      if (typeof itemValue === 'string' || typeof itemValue === 'number' || typeof itemValue === 'boolean') {
                        cleanedItem[itemKey] = itemValue;
                      }
                    }
                    return cleanedItem;
                  }
                  return (typeof item === 'string' || typeof item === 'number' || typeof item === 'boolean') ? item : null;
                })
                .filter(item => item !== null); // Retirer les éléments null

              // Test final de sérialisation du tableau complet
              JSON.stringify(cleanedArray);
              cleaned[key] = cleanedArray;
            } catch (e) {
              cleaned[key] = [];
            }
          } else if (typeof validatedValue === 'object') {
            // Pour les objets, nettoyer récursivement mais de manière très stricte
            try {
              const cleanedObj = {};
              for (const [objKey, objValue] of Object.entries(validatedValue)) {
                // Exclure les propriétés problématiques des objets imbriqués
                if (objKey.startsWith('_') || excludedProps.includes(objKey)) {
                  continue;
                }
                if (typeof objValue === 'string' || typeof objValue === 'number' || typeof objValue === 'boolean') {
                  cleanedObj[objKey] = objValue;
                }
              }
              // Test de sérialisation de l'objet nettoyé
              JSON.stringify(cleanedObj);
              cleaned[key] = cleanedObj;
            } catch (e) {
              cleaned[key] = {};
            }
          } else {
            // Pour les autres types (functions, symbols, etc.), ignorer silencieusement
          }
        }

        return cleaned;
      };

      // Nettoyer tous les éléments avec protection contre les erreurs
      let cleanedElements = [];
      try {
        // console.log('🧹 PDF Builder SAVE - Nettoyage des éléments commencé, éléments bruts:', elements.length);

        // Log détaillé de chaque élément avant filtrage
        elements.forEach((element, index) => {
          const hasElement = element && typeof element === 'object';
          const hasId = element && element.id;
          const hasType = element && element.type;
          // console.log(`📋 Élément ${index} - valide: ${hasElement}, id: ${hasId ? element.id : 'MISSING'}, type: ${hasType ? element.type : 'MISSING'}`);
          if (!hasElement || !hasId || !hasType) {
            // console.log(`❌ Élément ${index} sera filtré:`, element);
          }
        });

        cleanedElements = elements
          .filter(element => element && typeof element === 'object' && element.id && element.type) // Filtrer les éléments invalides
          .map(cleanElementForSerialization)
          .filter(element => element && element.id && element.type); // Filtrer après nettoyage

        // console.log('✅ PDF Builder SAVE - Nettoyage terminé, éléments nettoyés:', cleanedElements.length);

        // Test de sérialisation de tous les éléments
        JSON.stringify(cleanedElements);
        // console.log('✅ PDF Builder SAVE - Test de sérialisation réussi');
      } catch (e) {
        // En cas d'erreur, utiliser un tableau vide pour éviter les crashes
        cleanedElements = [];
      }

      // Log détaillé des propriétés de chaque élément (mode développement uniquement)
      if (isDevelopment) {
        elements.forEach((element, index) => {
          // console.log(`Élément ${index} (${element.type}) propriétés avant nettoyage:`, Object.keys(element));
          if (element.type === 'product_table') {
            // console.log(`Tableau ${index} - paramètres:`, {
            //   showHeaders: element.showHeaders,
            //   showBorders: element.showBorders,
            //   columns: element.columns,
            //   tableStyle: element.tableStyle,
            //   showSubtotal: element.showSubtotal,
            //   showShipping: element.showShipping,
            //   showTaxes: element.showTaxes,
            //   showDiscount: element.showDiscount,
            //   showTotal: element.showTotal
            // });
          }
        });

        // console.log('Éléments nettoyés pour sauvegarde:', cleanedElements);
      }

      const templateData = {
        elements: cleanedElements,
        canvasWidth,
        canvasHeight,
        version: '1.0'
      };

      // Log des données en mode développement uniquement
      if (isDevelopment) {
        // console.log('Données template à sauvegarder:', templateData);
      }

      // Valider le JSON avant envoi avec protection renforcée
      let jsonString;
      try {
        jsonString = JSON.stringify(templateData);

        // Tester le parsing pour valider
        const testParse = JSON.parse(jsonString);

        // Vérifier que les données essentielles sont présentes
        if (!testParse.elements || !Array.isArray(testParse.elements)) {
          throw new Error('Structure de données invalide: éléments manquants ou incorrects');
        }

        // Vérifier que chaque élément a au moins un ID et un type
        for (const element of testParse.elements) {
          if (!element.id || !element.type) {
            throw new Error(`Élément invalide détecté: ID ou type manquant pour ${JSON.stringify(element)}`);
          }
        }

        // Log détaillé pour débogage
        // console.log('PDF Builder SAVE - Données validées côté client:', {
        //   elementCount: testParse.elements.length,
        //   firstElement: testParse.elements[0],
        //   jsonLength: jsonString.length,
        //   canvasWidth: testParse.canvasWidth,
        //   canvasHeight: testParse.canvasHeight
        // });

        // Log des données brutes envoyées au serveur pour debug
        // console.log('PDF Builder SAVE - Données JSON brutes envoyées au serveur (premiers 500 chars):', jsonString.substring(0, 500));

      } catch (jsonError) {
        throw new Error('Données JSON invalides côté client: ' + jsonError.message);
      }

      // Sauvegarde directe via AJAX avec FormData pour les données volumineuses
      // console.log('📤 PDF Builder SAVE - Préparation des données pour envoi au serveur');
      const formData = new FormData();
      formData.append('action', 'pdf_builder_pro_save_template');
      formData.append('template_data', jsonString);
      formData.append('template_name', window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`);
      formData.append('template_id', window.pdfBuilderData?.templateId || '0');
      // Obtenir un nonce frais avant la sauvegarde
      try {
        const nonceResponse = await fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'pdf_builder_get_fresh_nonce'
          })
        });

        if (nonceResponse.ok) {
          const nonceData = await nonceResponse.json();
          if (nonceData.success) {
            formData.append('nonce', nonceData.data.nonce);
          } else {
            formData.append('nonce', window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '');
          }
        } else {
          formData.append('nonce', window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '');
        }
      } catch (error) {
        formData.append('nonce', window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '');
      }

      // console.log('📤 PDF Builder SAVE - Données FormData préparées:', {
      //   action: 'pdf_builder_pro_save_template',
      //   templateName: window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`,
      //   templateId: window.pdfBuilderData?.templateId || '0',
      //   nonce: window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '',
      //   jsonLength: jsonString.length
      // });

      // console.log('🌐 PDF Builder SAVE - Envoi de la requête AJAX...');
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
      // console.log('🏁 PDF Builder SAVE - Fin du processus de sauvegarde');
      setLoadingStates(prev => ({ ...prev, saving: false }));
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

  const zoomToSelection = useCallback(() => {
    const selectedElementIds = selection.selectedElements;
    if (selectedElementIds.length === 0) return;

    const selectedElements = elements.filter(el => selectedElementIds.includes(el.id));
    if (selectedElements.length === 0) return;

    // Obtenir les dimensions du conteneur (viewport du canvas)
    const containerElement = document.querySelector('.canvas-container');
    if (!containerElement) return;

    const containerRect = containerElement.getBoundingClientRect();
    const containerWidth = containerRect.width;
    const containerHeight = containerRect.height;

    zoom.zoomToSelection(selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight);
  }, [selection.selectedElements, elements, zoom, canvasWidth, canvasHeight]);

  // Conversion points vers pixels pour le rendu CSS
  // 1 point = 1/72 pouce, 1 pixel = 1/96 pouce → pixels = points × (96/72) = points × 1.333...
  const pointsToPixels = (points) => Math.round(points * (96 / 72));

  return useMemo(() => ({
    // État
    elements,
    canvasWidth: pointsToPixels(canvasWidth), // Conversion points → pixels
    canvasHeight: pointsToPixels(canvasHeight), // Conversion points → pixels

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
    history,

    // Zoom
    zoomToSelection,

    // Sauvegarde
    saveTemplate,

    // États de chargement pour feedback visuel
    loadingStates,
    isSaving: loadingStates.saving, // Alias pour compatibilité

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
    showContextMenu,
    zoomToSelection,
    saveTemplate
  ]);

  // Nettoyage mémoire au démontage
  useEffect(() => {
    return () => {
      // Nettoyer les timers/intervalles si nécessaire
      if (window.pdfBuilderTimeouts) {
        window.pdfBuilderTimeouts.forEach(clearTimeout);
        window.pdfBuilderTimeouts = [];
      }
      // Nettoyer les listeners d'événements locaux si nécessaire
      if (window.pdfBuilderEventListeners) {
        window.pdfBuilderEventListeners.forEach(({ element, event, handler }) => {
          element.removeEventListener(event, handler);
        });
        window.pdfBuilderEventListeners = [];
      }
    };
  }, []);

  // Synchronisation temps réel entre onglets via localStorage
  useEffect(() => {
    const handleStorageChange = (e) => {
      if (e.key === 'pdfBuilderTemplateUpdate' && e.newValue) {
        try {
          const updatedData = JSON.parse(e.newValue);
          if (updatedData.templateId === templateId && updatedData.elements) {
            setElements(updatedData.elements);
            setNextId(updatedData.nextId || nextId);
            // Notification discrète de synchronisation
            if (isDevelopment) {
              // Logging removed for production
            }
          }
        } catch (error) {
        }
      }
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, [templateId, nextId, isDevelopment]);

  return canvasState;
};
