import React from 'react';
const { useState, useRef, useEffect, useCallback, useMemo } = React;
import { CanvasElement } from './CanvasElement';
import { useDragAndDrop } from '../hooks/useDragAndDrop';
import { Toolbar } from './Toolbar';
import { useCanvasState } from '../hooks/useCanvasState';
import { useKeyboardShortcuts } from '../hooks/useKeyboardShortcuts';
import { useGlobalSettings } from '../hooks/useGlobalSettings';
import { FPSCounter } from './FPSCounter';

// Import direct des composants (plus de lazy loading)
import ContextMenu from './ContextMenu';
import PreviewModal from './PreviewModal';
import ModalPDFViewer from './ModalPDFViewer';
import WooCommerceElement from './WooCommerceElements';
import ElementLibrary from './ElementLibrary';
import PropertiesPanel from './PropertiesPanel';

export const PDFCanvasEditor = ({ options }) => {
  const [tool, setTool] = useState('select');
  const [showPreviewModal, setShowPreviewModal] = useState(false);
  const [showPDFModal, setShowPDFModal] = useState(false);
  const [pdfModalUrl, setPdfModalUrl] = useState(null);
  const [isPropertiesCollapsed, setIsPropertiesCollapsed] = useState(false);

  // États pour le pan et la navigation
  const [panOffset, setPanOffset] = useState({ x: 0, y: 0 });
  const [isPanning, setIsPanning] = useState(false);
  const [lastPanPoint, setLastPanPoint] = useState({ x: 0, y: 0 });

  // États pour les guides
  const [guides, setGuides] = useState({ horizontal: [], vertical: [] });
  const [isCreatingGuide, setIsCreatingGuide] = useState(false);
  const [guideCreationType, setGuideCreationType] = useState(null); // 'horizontal' or 'vertical'

  // Hook pour les paramètres globaux
  const globalSettings = useGlobalSettings();

  // Fonctions pour gérer les guides
  const addHorizontalGuide = useCallback((y) => {
    if (!globalSettings.settings.lockGuides) {
      setGuides(prev => ({
        ...prev,
        horizontal: [...prev.horizontal, y].sort((a, b) => a - b)
      }));
    }
  }, [globalSettings.settings.lockGuides]);

  const addVerticalGuide = useCallback((x) => {
    if (!globalSettings.settings.lockGuides) {
      setGuides(prev => ({
        ...prev,
        vertical: [...prev.vertical, x].sort((a, b) => a - b)
      }));
    }
  }, [globalSettings.settings.lockGuides]);

  const removeGuide = useCallback((type, position) => {
    if (!globalSettings.settings.lockGuides) {
      setGuides(prev => ({
        ...prev,
        [type]: prev[type].filter(pos => pos !== position)
      }));
    }
  }, [globalSettings.settings.lockGuides]);

  // Données de commande WooCommerce (passées via options ou données de test)
  const orderData = options.orderData || {
    invoice_number: 'INV-001',
    invoice_date: '15/10/2025',
    order_number: '#12345',
    order_date: '15/10/2025',
    customer_name: 'John Doe',
    customer_email: 'john.doe@example.com',
    billing_address: '123 Rue de Test\n75001 Paris\nFrance',
    shipping_address: '456 Rue de Livraison\n75002 Paris\nFrance',
    payment_method: 'Carte bancaire',
    order_status: 'Traitée',
    subtotal: '45,00 €',
    discount: '-5,00 €',
    shipping: '5,00 €',
    tax: '9,00 €',
    total: '54,00 €',
    refund: '0,00 €',
    fees: '1,50 €',
    quote_number: 'QUO-001',
    quote_date: '15/10/2025',
    quote_validity: '30 jours',
    quote_notes: 'Conditions spéciales : paiement à 30 jours.',
    products: [
      { name: 'Produit Test 1', quantity: 1, price: '25,00 €', total: '25,00 €' },
      { name: 'Produit Test 2', quantity: 2, price: '10,00 €', total: '20,00 €' }
    ]
  };

  const canvasState = useCanvasState({
    initialElements: options.initialElements || [],
    templateId: options.templateId || null,
    canvasWidth: options.width || 595,
    canvasHeight: options.height || 842,
    globalSettings: globalSettings.settings
  });

  // Hook pour l'historique Undo/Redo - REMOVED: utilise maintenant canvasState.history
  // const history = useHistory({ maxHistorySize: globalSettings.settings.undoLevels || 50 });

  // Fonction wrapper pour les mises à jour avec historique
  const updateElementWithHistory = useCallback((elementId, updates, description = 'Modifier élément') => {
    // Sauvegarder l'état actuel avant modification
    const currentElements = canvasState.getAllElements();
    canvasState.history.addToHistory(currentElements, description);

    // Appliquer la mise à jour
    canvasState.updateElement(elementId, updates);
  }, [canvasState]);

  // Fonctions Undo/Redo
  const handleUndo = useCallback(() => {
    const previousState = canvasState.history.undo();
    if (previousState) {
      canvasState.setElements(previousState);
    }
  }, [canvasState]);

  const handleRedo = useCallback(() => {
    const nextState = canvasState.history.redo();
    if (nextState) {
      canvasState.setElements(nextState);
    }
  }, [canvasState]);

  // Handlers pour les paramètres de grille
  const handleShowGridChange = useCallback((showGrid) => {
    globalSettings.updateSettings({ showGrid });
  }, [globalSettings]);

  const handleSnapToGridChange = useCallback((snapToGrid) => {
    globalSettings.updateSettings({ snapToGrid });
  }, [globalSettings]);

  const editorRef = useRef(null);
  const canvasRef = useRef(null);
  const canvasContainerRef = useRef(null);

  // Hook pour le drag and drop
  const dragAndDrop = useDragAndDrop({
    onElementMove: (elementId, position) => {
      updateElementWithHistory(elementId, position, 'Déplacer élément');
    },
    onElementDrop: (elementId, position) => {
      updateElementWithHistory(elementId, position, 'Déposer élément');
    },
    snapToGrid: globalSettings.settings.snapToGrid,
    gridSize: globalSettings.settings.gridSize,
    zoom: canvasState.zoom.zoom,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight,
    guides: guides,
    snapToGuides: globalSettings.settings.snapToElements
  });

  // Gestion des raccourcis clavier
  useKeyboardShortcuts({
    onDelete: canvasState.deleteSelectedElements,
    onCopy: canvasState.copySelectedElements,
    onPaste: canvasState.pasteElements,
    onUndo: handleUndo,
    onRedo: handleRedo,
    onSave: canvasState.saveTemplate,
    onZoomIn: canvasState.zoom.zoomIn,
    onZoomOut: canvasState.zoom.zoomOut
  });

  // Gestionnaire pour ajouter un élément depuis la bibliothèque
  const handleAddElement = useCallback((elementType, properties = {}) => {
    canvasState.addElement(elementType, properties);
    setTool('select');
  }, [canvasState]);

  // Gestionnaire pour la sélection d'élément
  const handleElementSelect = useCallback((elementId, event) => {
    const addToSelection = event?.ctrlKey || event?.metaKey; // Ctrl ou Cmd pour multi-sélection
    canvasState.selection.selectElement(elementId, addToSelection);
  }, [canvasState.selection]);

  // Gestionnaire pour l'impression
  const handlePrint = useCallback(async () => {
    try {
      // Récupérer tous les éléments du canvas
      const elements = canvasState.getAllElements();

      if (elements.length === 0) {
        alert('Aucun élément à imprimer. Ajoutez des éléments au canvas d\'abord.');
        return;
      }

      // Vérifier la sérialisation JSON avant l'envoi
      let jsonString;
      try {
        jsonString = JSON.stringify(elements);
      } catch (jsonError) {
        console.error('❌ Erreur lors de JSON.stringify:', jsonError);
        console.error('Éléments problématiques:', elements);
        alert('Erreur de sérialisation des éléments. Vérifiez la console pour plus de détails.');
        return;
      }

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_pdf');
      formData.append('nonce', window.pdfBuilderAjax?.nonce);
      formData.append('elements', jsonString);

      // Faire l'appel AJAX
      const response = await fetch(window.pdfBuilderAjax?.ajaxurl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {

        // Ouvrir le PDF dans une modale
        // console.log('Ouverture du PDF dans une modale...');
        setPdfModalUrl(pdfDataUrl);
        setShowPDFModal(true);
      } else {
        console.error('Erreur serveur:', data.data);
        throw new Error(data.data?.message || 'Erreur lors de la génération du PDF');
      }

    } catch (error) {
      console.error('Erreur lors de l\'impression:', error);
      alert('Erreur lors de la génération du PDF: ' + error.message);
    }
  }, [canvasState]);

  // Gestionnaire pour la désélection et création d'éléments
  const handleCanvasClick = useCallback((e) => {
    // Vérifier si c'est un Ctrl+clic pour créer un guide
    if (e.ctrlKey && globalSettings.settings.showGuides && !globalSettings.settings.lockGuides) {
      const canvasRect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - canvasRect.left;
      const clickY = e.clientY - canvasRect.top;

      // Ajuster pour le zoom et le pan
      const adjustedX = (clickX - panOffset.x) / canvasState.zoom.zoom;
      const adjustedY = (clickY - panOffset.y) / canvasState.zoom.zoom;

      // Créer un guide horizontal ou vertical selon la position relative au centre
      const centerX = canvasState.canvasWidth / 2;
      const centerY = canvasState.canvasHeight / 2;

      if (Math.abs(adjustedX - centerX) < Math.abs(adjustedY - centerY)) {
        // Plus proche verticalement, créer guide horizontal
        addHorizontalGuide(Math.round(adjustedY));
      } else {
        // Plus proche horizontalement, créer guide vertical
        addVerticalGuide(Math.round(adjustedX));
      }
      return;
    }

    // Vérifier si le clic vient de la zone vide du canvas (pas d'un élément)
    const clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) {
      // Si on clique sur un élément, ne rien faire ici (laissé à CanvasElement)
      return;
    }

    // Si un outil d'ajout est sélectionné, créer l'élément
    if (tool.startsWith('add-')) {
      const canvasRect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - canvasRect.left;
      const clickY = e.clientY - canvasRect.top;

      // Ajuster pour le zoom
      const adjustedX = clickX / canvasState.zoom.zoom;
      const adjustedY = clickY / canvasState.zoom.zoom;

      let elementType = 'text';
      let defaultProps = {};

      // Déterminer le type d'élément selon l'outil
      switch (tool) {
        case 'add-text':
          elementType = 'text';
          break;
        case 'add-text-title':
          elementType = 'text';
          defaultProps = { fontSize: 24, fontWeight: 'bold' };
          break;
        case 'add-text-subtitle':
          elementType = 'text';
          defaultProps = { fontSize: 18, fontWeight: 'bold' };
          break;
        case 'add-rectangle':
          elementType = 'rectangle';
          break;
        case 'add-circle':
          elementType = 'shape-circle';
          break;
        case 'add-line':
          elementType = 'line';
          break;
        case 'add-arrow':
          elementType = 'shape-arrow';
          break;
        case 'add-triangle':
          elementType = 'shape-triangle';
          break;
        case 'add-star':
          elementType = 'shape-star';
          break;
        case 'add-divider':
          elementType = 'divider';
          break;
        case 'add-image':
          elementType = 'image';
          break;
        default:
          // Pour les autres outils de la bibliothèque
          if (tool.startsWith('add-')) {
            elementType = tool.replace('add-', '');
          }
          break;
      }

      canvasState.addElement(elementType, {
        x: Math.max(0, adjustedX - 50),
        y: Math.max(0, adjustedY - 25),
        ...defaultProps
      });

      // Remettre l'outil de sélection après ajout
      setTool('select');
      return;
    }

    // Sinon, désélectionner
    canvasState.selection.clearSelection();
  }, [canvasState, tool]);

  // Gestionnaire pour les changements de propriétés
  const handlePropertyChange = useCallback((elementId, property, value) => {
    // Récupérer l'élément actuel pour connaître les valeurs existantes
    const currentElement = canvasState.getElementById(elementId);
    if (!currentElement) return;
    
    // Gérer les propriétés imbriquées (ex: "columns.image" -> { columns: { image: value } })
    const updates = {};
    if (property.includes('.')) {
      // Fonction récursive pour mettre à jour les propriétés imbriquées
      // en préservant toutes les valeurs existantes
      const updateNestedProperty = (existingObj, path, val) => {
        const keys = path.split('.');
        const lastKey = keys.pop();
        
        // Commencer avec une copie complète de l'objet existant
        const result = { ...existingObj };
        let current = result;
        
        // Naviguer jusqu'à l'avant-dernier niveau en préservant les objets existants
        for (let i = 0; i < keys.length - 1; i++) {
          const key = keys[i];
          if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
          } else {
            current[key] = { ...current[key] };
          }
          current = current[key];
        }
        
        // Pour le dernier niveau (avant la propriété finale)
        const parentKey = keys[keys.length - 1];
        if (parentKey) {
          if (!current[parentKey] || typeof current[parentKey] !== 'object') {
            current[parentKey] = {};
          } else {
            current[parentKey] = { ...current[parentKey] };
          }
          current[parentKey][lastKey] = val;
        } else {
          // Propriété directement sur l'objet racine
          current[lastKey] = val;
        }
        
        return result;
      };

      // Créer l'update en préservant toutes les propriétés existantes
      const fullUpdate = updateNestedProperty(currentElement, property, value);
      Object.assign(updates, fullUpdate);
    } else {
      updates[property] = value;
    }

    updateElementWithHistory(elementId, updates, `Modifier ${property}`);
  }, [canvasState, updateElementWithHistory]);

  // Gestionnaire pour les mises à jour par lot
  const handleBatchUpdate = useCallback((updates) => {
    updates.forEach(({ elementId, property, value }) => {
      canvasState.updateElement(elementId, { [property]: value });
    });
  }, [canvasState]);

  // Gestionnaire du menu contextuel
  const handleContextMenu = useCallback((e, elementId = null) => {
    e.preventDefault();

    const menuItems = [];

    if (elementId) {
      // Menu contextuel pour un élément spécifique
      const element = canvasState.getElementById(elementId);
      if (element) {
        menuItems.push(
          { label: 'Copier', action: () => canvasState.copySelectedElements() },
          { label: 'Dupliquer', action: () => canvasState.duplicateElement(elementId) },
          { type: 'separator' },
          { label: 'Supprimer', action: () => canvasState.deleteElement(elementId) }
        );
      }
    } else {
      // Menu contextuel pour le canvas vide
      const hasSelection = canvasState.selection.selectedElements.length > 0;

      if (hasSelection) {
        menuItems.push(
          { label: 'Copier', action: () => canvasState.copySelectedElements() },
          { label: 'Dupliquer', action: () => canvasState.duplicateSelectedElements() },
          { type: 'separator' },
          { label: 'Supprimer', action: () => canvasState.deleteSelectedElements() }
        );
      }

      menuItems.push(
        { type: 'separator' },
        { label: 'Coller', action: () => canvasState.pasteElements() },
        { type: 'separator' },
        { label: 'Tout sélectionner', action: () => canvasState.selectAll() },
        { label: 'Désélectionner', action: () => canvasState.selection.clearSelection() }
      );
    }

    canvasState.showContextMenu(e.clientX, e.clientY, menuItems);
  }, [canvasState]);

  // Gestionnaire pour les actions du menu contextuel
  const handleContextMenuAction = useCallback((action) => {
    if (typeof action === 'function') {
      action();
    }
  }, []);

  // Fonction pour déterminer le curseur selon l'outil sélectionné
  const getCursorStyle = useCallback(() => {
    if (isPanning) return 'grabbing';

    switch (tool) {
      case 'select':
        return 'default';
      case 'add-text':
      case 'add-text-title':
      case 'add-text-subtitle':
        return 'text';
      case 'add-rectangle':
      case 'add-circle':
      case 'add-line':
      case 'add-arrow':
      case 'add-triangle':
      case 'add-star':
      case 'add-divider':
      case 'add-image':
        return 'crosshair';
      default:
        return 'default';
    }
  }, [tool, isPanning]);

  // Gestionnaire pour le drag over
  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  // Gestionnaire pour le drop
  const handleDrop = useCallback((e) => {
    e.preventDefault();

    try {
      const jsonData = e.dataTransfer.getData('application/json');

      // Vérifier si les données existent et ne sont pas vides
      if (!jsonData || jsonData.trim() === '') {
        // C'est probablement un drop normal (image, fichier, etc.) - ignorer silencieusement
        return;
      }

      const data = JSON.parse(jsonData);

      if (data.type === 'new-element') {
        const canvasRect = e.currentTarget.getBoundingClientRect();
        const dropX = e.clientX - canvasRect.left;
        const dropY = e.clientY - canvasRect.top;

        // Ajuster pour le zoom
        const adjustedX = dropX / canvasState.zoom.zoom;
        const adjustedY = dropY / canvasState.zoom.zoom;

        canvasState.addElement(data.elementType, {
          x: Math.max(0, adjustedX - 50), // Centrer l'élément sur le point de drop
          y: Math.max(0, adjustedY - 25),
          ...data.defaultProps
        });
      }
    } catch (error) {
      // Ne logger que les vraies erreurs (pas les drops normaux)
      if (error instanceof SyntaxError && e.dataTransfer.getData('application/json')) {
        console.error('Erreur lors du parsing des données de drop:', error);
      }
      // Pour les autres types de drop (fichiers, images, etc.), ignorer silencieusement
    }
  }, [canvasState]);

  // Gestionnaire pour le zoom avec la molette
  const handleWheel = useCallback((e) => {
    if (!globalSettings.settings.zoomWithWheel) return;

    e.preventDefault();

    // Calculer le facteur de zoom basé sur les paramètres globaux
    const zoomFactor = 1 + (globalSettings.settings.zoomStep / 100);

    // Déterminer si on zoome ou dézoome
    const delta = e.deltaY > 0 ? -1 : 1;

    // Calculer les coordonnées de la souris relatives au conteneur
    const container = canvasContainerRef.current;
    if (!container) return;

    const rect = container.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;

    // Appliquer le zoom vers le point de la souris
    const finalZoomFactor = delta > 0 ? zoomFactor : 1 / zoomFactor;
    canvasState.zoom.zoomToPoint(mouseX, mouseY, finalZoomFactor);
  }, [globalSettings.settings.zoomWithWheel, globalSettings.settings.zoomStep, canvasState.zoom]);

  // Attacher le gestionnaire de roue de manière non-passive pour permettre preventDefault
  useEffect(() => {
    const container = canvasContainerRef.current;
    if (!container || !globalSettings.settings.zoomWithWheel) return;

    const handleWheelEvent = (e) => {
      handleWheel(e);
    };

    container.addEventListener('wheel', handleWheelEvent, { passive: false });

    return () => {
      container.removeEventListener('wheel', handleWheelEvent);
    };
  }, [handleWheel, globalSettings.settings.zoomWithWheel]);

  // Gestionnaire pour le pan avec la souris (clic milieu ou espace + drag)
  const handleMouseDown = useCallback((e) => {
    if (!globalSettings.settings.panWithMouse) return;

    // Pan avec le bouton du milieu ou espace + clic gauche
    if (e.button === 1 || (e.button === 0 && e.altKey)) {
      e.preventDefault();
      setIsPanning(true);
      setLastPanPoint({ x: e.clientX, y: e.clientY });
    }
  }, [globalSettings.settings.panWithMouse]);

  const handleMouseMove = useCallback((e) => {
    if (!isPanning) return;

    const deltaX = e.clientX - lastPanPoint.x;
    const deltaY = e.clientY - lastPanPoint.y;

    setPanOffset(prev => ({
      x: prev.x + deltaX,
      y: prev.y + deltaY
    }));

    setLastPanPoint({ x: e.clientX, y: e.clientY });
  }, [isPanning, lastPanPoint]);

  const handleMouseUp = useCallback(() => {
    setIsPanning(false);
  }, []);

  // Gestionnaire pour double-clic
  const handleDoubleClick = useCallback((e) => {
    if (!globalSettings.settings.zoomToSelection) return;

    // Vérifier qu'il n'y a pas d'élément cliqué (double-clic sur le fond)
    const clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) return;

    // Si des éléments sont sélectionnés, zoomer dessus
    if (canvasState.selection.selectedElements.length > 0) {
      canvasState.zoomToSelection();
    }
  }, [globalSettings.settings.zoomToSelection, canvasState]);

  return (
    <div className="pdf-canvas-editor" ref={editorRef}>
      {/* Header avec titre et actions */}
      <header className="editor-header">
        <h2>Éditeur PDF - {options.isNew ? 'Nouveau Template' : options.templateName}</h2>
        <nav className="editor-actions">
          <button
            className="btn btn-secondary"
            onClick={() => setShowPreviewModal(true)}
          >
            👁️ Aperçu
          </button>
          <button
            className="btn btn-primary"
            onClick={() => canvasState.saveTemplate()}
            disabled={canvasState.isSaving}
          >
            {canvasState.isSaving ? '⏳ Sauvegarde...' : (options.isNew ? '💾 Sauvegarder' : '✏️ Modifier')}
          </button>
        </nav>
      </header>

      {/* Barre d'outils */}
      <Toolbar
        selectedTool={tool}
        onToolSelect={setTool}
        zoom={canvasState.zoom.zoom}
        onZoomChange={canvasState.zoom.setZoomLevel}
        showGrid={globalSettings.settings.showGrid}
        onShowGridChange={handleShowGridChange}
        snapToGrid={globalSettings.settings.snapToGrid}
        onSnapToGridChange={handleSnapToGridChange}
        onUndo={handleUndo}
        onRedo={handleRedo}
        canUndo={canvasState.history.canUndo()}
        canRedo={canvasState.history.canRedo()}
      />

      {/* Zone de travail principale - simplifiée */}
      <main className="editor-workspace">
        {/* Bibliothèque d'éléments - masquée en mode aperçu */}
        {!showPreviewModal && (
          <aside className="editor-sidebar left-sidebar">
            <ElementLibrary
              onAddElement={handleAddElement}
              selectedTool={tool}
              onToolSelect={setTool}
            />
          </aside>
        )}

        {/* Canvas avec éléments interactifs - structure simplifiée */}
        <section
          className="canvas-section"
          ref={canvasContainerRef}
          onContextMenu={handleContextMenu}
          onDragOver={handleDragOver}
          onDrop={handleDrop}
          onMouseDown={handleMouseDown}
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUp}
          onMouseLeave={handleMouseUp}
          onDoubleClick={handleDoubleClick}
          style={{
            cursor: getCursorStyle(),
            backgroundColor: globalSettings.settings.containerShowTransparency
              ? 'transparent'
              : (globalSettings.settings.containerBackgroundColor || '#f8f9fa'),
            backgroundImage: globalSettings.settings.containerShowTransparency
              ? `linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)`
              : 'none',
            backgroundSize: globalSettings.settings.containerShowTransparency
              ? '20px 20px'
              : 'auto',
            backgroundPosition: globalSettings.settings.containerShowTransparency
              ? '0 0, 0 10px, 10px -10px, -10px 0px'
              : '0 0'
          }}
        >
          <div
            className="canvas-zoom-wrapper"
            style={{
              transform: `translate(${panOffset.x}px, ${panOffset.y}px) scale(${canvasState.zoom.zoom})`,
              transformOrigin: 'center',
              cursor: isPanning ? 'grabbing' : 'default',
              transition: globalSettings.settings.smoothZoom ? 'transform 0.2s ease-out' : 'none',
              willChange: globalSettings.settings.enableHardwareAcceleration ? 'transform' : 'auto'
            }}
          >
            <div
              className="canvas"
              ref={canvasRef}
              onClick={handleCanvasClick}
              style={{
                width: canvasState.canvasWidth,
                height: 'auto', // Laisser le CSS contrôler la hauteur pour s'adapter au conteneur 130vh
                minHeight: canvasState.canvasHeight, // Hauteur minimale pour éviter la compression excessive
                position: 'relative',
                backgroundColor: globalSettings.settings.canvasShowTransparency
                  ? 'transparent'
                  : (globalSettings.settings.canvasBackgroundColor || '#ffffff'),
                backgroundImage: globalSettings.settings.canvasShowTransparency
                  ? `linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)`
                  : 'none',
                backgroundSize: globalSettings.settings.canvasShowTransparency
                  ? '20px 20px'
                  : 'auto',
                backgroundPosition: globalSettings.settings.canvasShowTransparency
                  ? '0 0, 0 10px, 10px -10px, -10px 0px'
                  : '0 0'
              }}
            >
              {/* Grille de fond */}
              {globalSettings.settings.showGrid && (
                <div
                  className="canvas-grid"
                  style={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    backgroundImage: `
                      linear-gradient(to right, ${globalSettings.settings.gridColor}${Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0')} 1px, transparent 1px),
                      linear-gradient(to bottom, ${globalSettings.settings.gridColor}${Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0')} 1px, transparent 1px)
                    `,
                    backgroundSize: `${globalSettings.settings.gridSize}px ${globalSettings.settings.gridSize}px`,
                    pointerEvents: 'none',
                    zIndex: 1
                  }}
                />
              )}

              {/* Guides */}
              {globalSettings.settings.showGuides && (
                <div className="canvas-guides">
                  {guides.horizontal.map((y, index) => (
                    <div
                      key={`h-guide-${index}`}
                      className="canvas-guide horizontal-guide"
                      onClick={(e) => {
                        e.stopPropagation();
                        if (!globalSettings.settings.lockGuides) {
                          removeGuide('horizontal', y);
                        }
                      }}
                      style={{
                        position: 'absolute',
                        top: `${y}px`,
                        left: 0,
                        width: '100%',
                        height: '2px',
                        backgroundColor: '#007cba',
                        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
                        zIndex: 2,
                        opacity: 0.7
                      }}
                      title={`Guide horizontal à ${y}px - ${globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer'}`}
                    />
                  ))}
                  {guides.vertical.map((x, index) => (
                    <div
                      key={`v-guide-${index}`}
                      className="canvas-guide vertical-guide"
                      onClick={(e) => {
                        e.stopPropagation();
                        if (!globalSettings.settings.lockGuides) {
                          removeGuide('vertical', x);
                        }
                      }}
                      style={{
                        position: 'absolute',
                        top: 0,
                        left: `${x}px`,
                        height: '100%',
                        width: '2px',
                        backgroundColor: '#007cba',
                        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
                        zIndex: 2,
                        opacity: 0.7
                      }}
                      title={`Guide vertical à ${x}px - ${globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer'}`}
                    />
                  ))}
                </div>
              )}

              {/* Éléments normaux rendus comme composants interactifs */}
              {canvasState.elements
                .filter(el => !el.type.startsWith('woocommerce-'))
                .map(element => (
                  <CanvasElement
                    key={element.id}
                    element={element}
                    isSelected={canvasState.selection.selectedElements.includes(element.id)}
                    zoom={1}
                    snapToGrid={globalSettings.settings.snapToGrid}
                    gridSize={globalSettings.settings.gridSize}
                    canvasWidth={canvasState.canvasWidth}
                    canvasHeight={canvasState.canvasHeight}
                    onSelect={() => handleElementSelect(element.id)}
                    onUpdate={(updates) => canvasState.updateElement(element.id, updates)}
                    onRemove={() => canvasState.deleteElement(element.id)}
                    onContextMenu={(e) => handleContextMenu(e, element.id)}
                    dragAndDrop={dragAndDrop}
                    enableRotation={globalSettings.settings.enableRotation}
                    rotationStep={globalSettings.settings.rotationStep}
                    rotationSnap={globalSettings.settings.rotationSnap}
                    guides={guides}
                    snapToGuides={globalSettings.settings.snapToElements}
                  />
                ))}

              {/* Éléments WooCommerce superposés */}
              {canvasState.elements
                .filter(el => el.type.startsWith('woocommerce-'))
                .map(element => (
                  <WooCommerceElement
                    key={element.id}
                    element={element}
                    isSelected={canvasState.selection.selectedElements.includes(element.id)}
                    onSelect={handleElementSelect}
                    onUpdate={canvasState.updateElement}
                    dragAndDrop={dragAndDrop}
                    zoom={1}
                    canvasWidth={canvasState.canvasWidth}
                    canvasHeight={canvasState.canvasHeight}
                    orderData={orderData}
                    onContextMenu={(e) => handleContextMenu(e, element.id)}
                    snapToGrid={globalSettings.settings.snapToGrid}
                    gridSize={globalSettings.settings.gridSize}
                    guides={guides}
                    snapToGuides={globalSettings.settings.snapToElements}
                  />
                ))}
            </div>
          </div>
        </section>

        {/* Panneau de propriétés - masqué en mode aperçu */}
        {!showPreviewModal && (
          <aside className={`editor-sidebar right-sidebar ${isPropertiesCollapsed ? 'collapsed' : ''}`}>
            {!isPropertiesCollapsed && (
              <PropertiesPanel
                selectedElements={canvasState.selection.selectedElements}
                elements={canvasState.elements}
                onPropertyChange={handlePropertyChange}
                onBatchUpdate={handleBatchUpdate}
              />
            )}
          </aside>
        )}
      </main>

      {/* Bouton de toggle repositionné à la fin pour être au-dessus de tout - masqué en mode aperçu */}
      {!showPreviewModal && (
        <button
          className="sidebar-toggle-fixed"
          onClick={() => setIsPropertiesCollapsed(!isPropertiesCollapsed)}
          title={isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau'}
          style={{
            position: 'fixed',
            top: '50%',
            right: isPropertiesCollapsed ? '80px' : '420px',
            transform: 'translateY(-50%)',
            zIndex: 999999
          }}
        >
          {isPropertiesCollapsed ? '◀' : '▶'}
        </button>
      )}

      {/* Menu contextuel */}
      {canvasState.contextMenu.contextMenu && (
        <ContextMenu
          menu={canvasState.contextMenu.contextMenu}
          onAction={handleContextMenuAction}
          isAnimating={canvasState.contextMenu.isAnimating || false}
          onClose={canvasState.contextMenu.hideContextMenu}
        />
      )}

      {/* Indicateur d'état */}
      <footer className="editor-status">
        <span>Éléments: {canvasState.elements.length}</span>
        <span>|</span>
        {globalSettings.settings.showZoomIndicator && (
          <>
            <span>Zoom: {Math.round(canvasState.zoom.zoom * 100)}%</span>
            <span>|</span>
          </>
        )}
        <span>Outil: {tool}</span>
        {canvasState.selection.selectedElements.length > 0 && (
          <>
            <span>|</span>
            <span>Éléments sélectionnés: {canvasState.selection.selectedElements.length}</span>
          </>
        )}
      </footer>

      {/* Modale d'aperçu */}
      <PreviewModal
        isOpen={showPreviewModal}
        onClose={() => {
          setShowPreviewModal(false);
        }}
        elements={canvasState.elements}
        canvasWidth={canvasState.canvasWidth}
        canvasHeight={canvasState.canvasHeight}
        ajaxurl={window.pdfBuilderAjax?.ajaxurl}
        pdfBuilderNonce={window.pdfBuilderAjax?.nonce}
        useServerPreview={false}
        onOpenPDFModal={(pdfUrl) => {
          setPdfModalUrl(pdfUrl);
          setShowPDFModal(true);
          setShowPreviewModal(false);
        }}
      />

      <ModalPDFViewer
        isOpen={showPDFModal}
        onClose={() => {
          setShowPDFModal(false);
          if (pdfModalUrl && pdfModalUrl.startsWith('blob:')) {
            setTimeout(() => {
              URL.revokeObjectURL(pdfModalUrl);
            }, 100);
          }
          setPdfModalUrl(null);
        }}
        pdfUrl={pdfModalUrl}
        title="PDF Généré"
      />

      {/* Compteur FPS */}
      <FPSCounter showFps={globalSettings.settings.showFps} />
    </div>
  );
};

// Optimisation : éviter les re-renders inutiles
export default React.memo(PDFCanvasEditor);
