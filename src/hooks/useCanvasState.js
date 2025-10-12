import { useState, useCallback, useEffect, useMemo } from 'react';
import { useHistory } from './useHistory';
import { useSelection } from './useSelection';
import { useClipboard } from './useClipboard';
import { useZoom } from './useZoom';
import { useContextMenu } from './useContextMenu';
import { useDragAndDrop } from './useDragAndDrop';

export const useCanvasState = ({
  initialElements = [],
  templateId = null,
  canvasWidth = 595, // A4 width in points
  canvasHeight = 842, // A4 height in points
  onSave,
  onPreview,
  globalSettings = null
}) => {
  const [elements, setElements] = useState(initialElements);
  const [nextId, setNextId] = useState(1);
  const [isLoading, setIsLoading] = useState(false);

    // Hooks intégrés
    selection,
    zoom,
    contextMenu,
    dragAndDrop,useState(false);

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

  // Charger les éléments du template si un templateId est fourni
  useEffect(() => {
    if (templateId && templateId !== null) {
      setIsLoading(true);
      
      // Attendre que pdfBuilderAjax soit disponible
      const waitForPdfBuilderAjax = () => {
        return new Promise((resolve, reject) => {
          const checkPdfBuilderAjax = () => {
            if (typeof pdfBuilderAjax !== 'undefined' && pdfBuilderAjax && pdfBuilderAjax.nonce) {
              console.log('PDF Builder: pdfBuilderAjax disponible:', pdfBuilderAjax);
              resolve();
            } else {
              console.log('PDF Builder: Attente de pdfBuilderAjax...');
              setTimeout(checkPdfBuilderAjax, 100);
            }
          };
          // Timeout après 5 secondes
          setTimeout(() => reject(new Error('pdfBuilderAjax n\'a pas été chargé')), 5000);
          checkPdfBuilderAjax();
        });
      };

      waitForPdfBuilderAjax()
        .then(() => {
          console.log('PDF Builder: Envoi AJAX avec nonce:', pdfBuilderAjax.nonce);
          console.log('PDF Builder: Action utilisée: pdf_builder_load_canvas_elements');
          console.log('PDF Builder: Template ID:', templateId);
          
          // Faire un appel AJAX pour charger les éléments du template
          fetch(pdfBuilderAjax.ajaxurl, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
              action: "pdf_builder_load_canvas_elements",
              template_id: templateId,
              nonce: pdfBuilderAjax.nonce
            })
          })
          .then(response => response.json())
          .then(data => {
        if (data.success && Array.isArray(data.data.elements)) {
          setElements(data.data.elements);
          // Calculer le prochain ID basé sur les éléments chargés
          const maxId = data.data.elements.length > 0 
            ? Math.max(...data.data.elements.map(el => parseInt(el.id.split('_')[1] || 0)))
            : 0;
          setNextId(maxId + 1);
          console.log('Éléments chargés avec succès:', data.data.elements.length, 'éléments');
        } else {
          const errorMessage = data.data?.message || 'Erreur inconnue lors du chargement des éléments';
          console.error('Erreur de chargement des éléments:', errorMessage, '- data complète:', data);
          // Afficher une alerte à l'utilisateur avec le message d'erreur détaillé
          alert('Erreur lors du chargement du template:\n' + errorMessage);
        }
      })
      .catch(error => {
        console.error('Erreur lors du chargement des éléments du template:', error);
      })
      .finally(() => {
        setIsLoading(false);
      });
        })
        .catch(error => {
          console.error('Erreur d\'attente pdfBuilderAjax:', error);
          alert('Erreur: Les scripts AJAX ne sont pas chargés correctement. Actualisez la page.');
          setIsLoading(false);
        });
    }
  }, [templateId]);

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
        borderColor: 'transparent',
        borderWidth: 0,
        borderRadius: 4,
        color: '#000000',
        fontSize: 14,
        fontFamily: 'Arial, sans-serif',
        padding: 8
      };

      // Appliquer les paramètres globaux du canvas si disponibles
      if (globalSettings) {
        // Couleur de bordure par défaut pour les éléments
        if (globalSettings.selectionBorderColor && globalSettings.selectionBorderColor !== 'var(--primary-color)') {
          defaults.borderColor = globalSettings.selectionBorderColor;
        }
        // Largeur de bordure par défaut pour les éléments
        if (globalSettings.selectionBorderWidth && globalSettings.selectionBorderWidth > 0) {
          defaults.borderWidth = globalSettings.selectionBorderWidth;
        }
        // Couleur de texte par défaut pour les éléments
        if (globalSettings.defaultTextColor) {
          defaults.color = globalSettings.defaultTextColor;
        }
        // Couleur de fond par défaut pour les éléments
        if (globalSettings.defaultBackgroundColor) {
          defaults.backgroundColor = globalSettings.defaultBackgroundColor;
        }
        // Taille de police par défaut pour les éléments
        if (globalSettings.defaultFontSize) {
          defaults.fontSize = globalSettings.defaultFontSize;
        }
      }

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
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'layout-footer':
            defaults.width = 500;
            defaults.height = 60;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'layout-sidebar':
            defaults.width = 150;
            defaults.height = 300;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'layout-section':
            defaults.width = 500;
            defaults.height = 200;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'layout-container':
            defaults.width = 300;
            defaults.height = 150;
            defaults.backgroundColor = 'transparent';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderStyle = 'dashed';
            break;

          // Éléments spécialisés pour factures et devis
          case 'invoice-header':
            defaults.width = 500;
            defaults.height = 100;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.content = 'ENTREPRISE\n123 Rue de l\'Entreprise\n75000 Paris\nTéléphone: 01 23 45 67 89\nEmail: contact@entreprise.com';
            defaults.fontSize = 12;
            defaults.fontWeight = 'normal';
            break;

          case 'invoice-address-block':
            defaults.width = 240;
            defaults.height = 120;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderRadius = 4;
            break;

          case 'invoice-info-block':
            defaults.width = 300;
            defaults.height = 80;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderRadius = 4;
            break;

          case 'invoice-products-table':
            defaults.width = 500;
            defaults.height = 200;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;

          case 'product_table':
            defaults.width = 500;
            defaults.height = 200;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.showHeaders = true;
            defaults.showBorders = true;
            defaults.headers = ['Produit', 'Qté', 'Prix'];
            defaults.dataSource = 'order_items';
            defaults.columns = {
              image: true,
              name: true,
              sku: false,
              quantity: true,
              price: true,
              total: true
            };
            defaults.showSubtotal = false;
            defaults.showShipping = true;
            defaults.showTaxes = true;
            defaults.showDiscount = false;
            defaults.showTotal = false;
            break;

          case 'customer_info':
            defaults.width = 300;
            defaults.height = 200;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.fields = ['name', 'email', 'phone', 'address', 'company', 'vat'];
            defaults.layout = 'vertical';
            defaults.showLabels = true;
            defaults.labelStyle = 'bold';
            defaults.spacing = 8;
            defaults.fontSize = 12;
            defaults.fontFamily = 'Arial, sans-serif';
            defaults.fontWeight = 'normal';
            defaults.fontStyle = 'normal';
            defaults.textDecoration = 'none';
            break;

          case 'invoice-totals-block':
            defaults.width = 200;
            defaults.height = 150;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderRadius = 4;
            break;

          case 'invoice-payment-terms':
            defaults.width = 250;
            defaults.height = 100;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderRadius = 4;
            defaults.content = 'Conditions de paiement:\n- Paiement à 30 jours\n- Pénalités de retard: 1.5% par mois\n- Escompte: 2% à 10 jours';
            defaults.fontSize = 10;
            break;

          case 'invoice-legal-footer':
            defaults.width = 500;
            defaults.height = 60;
            defaults.backgroundColor = '#f8fafc';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.content = 'SARL au capital de 10 000€ - RCS Paris 123 456 789 - TVA FR 12 345 678 901 - IBAN: FR76 1234 5678 9012 3456 7890 123';
            defaults.fontSize = 8;
            break;

          case 'invoice-signature-block':
            defaults.width = 200;
            defaults.height = 80;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderRadius = 4;
            defaults.content = 'Signature:\n\nDate: ____________________\n\nCachet de l\'entreprise';
            break;

          case 'layout-section-divider':
            defaults.width = 500;
            defaults.height = 2;
            defaults.backgroundColor = '#e2e8f0';
            break;

          case 'layout-spacer':
            defaults.width = 500;
            defaults.height = 20;
            defaults.backgroundColor = 'transparent';
            break;

          case 'layout-two-column':
            defaults.width = 500;
            defaults.height = 150;
            defaults.backgroundColor = 'transparent';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderStyle = 'dashed';
            break;

          case 'layout-three-column':
            defaults.width = 500;
            defaults.height = 150;
            defaults.backgroundColor = 'transparent';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            defaults.borderStyle = 'dashed';
            break;
          // Formes et Graphiques
          case 'shape-rectangle':
            defaults.width = 100;
            defaults.height = 60;
            defaults.backgroundColor = '#e5e7eb';
            defaults.borderRadius = 0;
            break;
          case 'shape-circle':
            defaults.width = 60;
            defaults.height = 60;
            defaults.backgroundColor = '#e5e7eb';
            defaults.borderRadius = 30;
            break;
          case 'shape-line':
            defaults.width = 100;
            defaults.height = 2;
            defaults.backgroundColor = '#6b7280';
            break;
          case 'shape-arrow':
            defaults.width = 80;
            defaults.height = 20;
            defaults.backgroundColor = '#374151';
            break;
          case 'shape-triangle':
            defaults.width = 60;
            defaults.height = 52;
            defaults.backgroundColor = '#e5e7eb';
            break;
          case 'shape-star':
            defaults.width = 60;
            defaults.height = 60;
            defaults.backgroundColor = '#fbbf24';
            break;
          case 'divider':
            defaults.width = 400;
            defaults.height = 1;
            defaults.backgroundColor = '#d1d5db';
            break;
          // Médias
          case 'image':
          case 'image-upload':
            defaults.width = 150;
            defaults.height = 100;
            defaults.backgroundColor = '#f3f4f6';
            defaults.content = 'Image';
            break;
          case 'logo':
            defaults.width = 120;
            defaults.height = 60;
            defaults.backgroundColor = '#f3f4f6';
            defaults.content = 'Logo';
            break;
          case 'barcode':
            defaults.width = 120;
            defaults.height = 40;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'qrcode':
          case 'qrcode-dynamic':
            defaults.width = 60;
            defaults.height = 60;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'icon':
            defaults.width = 40;
            defaults.height = 40;
            defaults.backgroundColor = 'transparent';
            defaults.content = '🎯';
            break;
          // Données Dynamiques
          case 'dynamic-text':
            defaults.content = '{{variable}}';
            break;
          case 'formula':
            defaults.content = '{{prix * quantite}}';
            break;
          case 'conditional-text':
            defaults.content = '{{condition ? "Oui" : "Non"}}';
            break;
          case 'counter':
            defaults.content = '1';
            break;
          case 'date-dynamic':
            defaults.content = '{{date|format:Y-m-d}}';
            break;
          case 'currency':
            defaults.content = '{{montant|currency:EUR}}';
            break;
          case 'table-dynamic':
            defaults.width = 400;
            defaults.height = 150;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          // Éléments Avancés
          case 'gradient-box':
            defaults.width = 200;
            defaults.height = 100;
            defaults.backgroundColor = 'linear-gradient(45deg, #667eea 0%, #764ba2 100%)';
            break;
          case 'shadow-box':
            defaults.width = 200;
            defaults.height = 100;
            defaults.backgroundColor = '#ffffff';
            defaults.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
            break;
          case 'rounded-box':
            defaults.width = 200;
            defaults.height = 100;
            defaults.backgroundColor = '#ffffff';
            defaults.borderRadius = 12;
            break;
          case 'border-box':
            defaults.width = 200;
            defaults.height = 100;
            defaults.backgroundColor = '#ffffff';
            defaults.borderColor = 'transparent';
            defaults.borderWidth = 0;
            break;
          case 'background-pattern':
            defaults.width = 200;
            defaults.height = 100;
            defaults.backgroundColor = '#f8fafc';
            defaults.backgroundImage = 'repeating-linear-gradient(45deg, #e2e8f0, #e2e8f0 10px, #f1f5f9 10px, #f1f5f9 20px)';
            break;
          case 'watermark':
            defaults.width = 300;
            defaults.height = 200;
            defaults.backgroundColor = 'transparent';
            defaults.content = 'CONFIDENTIEL';
            defaults.color = '#9ca3af';
            defaults.fontSize = 48;
            defaults.opacity = 0.1;
            break;
          case 'progress-bar':
            defaults.width = 200;
            defaults.height = 20;
            defaults.backgroundColor = '#e5e7eb';
            defaults.progressColor = '#3b82f6';
            defaults.progressValue = 75;
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