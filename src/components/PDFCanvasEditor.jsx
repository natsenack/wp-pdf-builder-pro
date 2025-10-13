import React, { useState, useRef, useEffect, useCallback } from 'react';
import { CanvasElement } from './CanvasElement';
import { useDragAndDrop } from '../hooks/useDragAndDrop';
import { Toolbar } from './Toolbar';
import { useCanvasState } from '../hooks/useCanvasState';
import { useKeyboardShortcuts } from '../hooks/useKeyboardShortcuts';
import { useGlobalSettings } from '../hooks/useGlobalSettings';

// Chargement lazy des composants conditionnels
const ContextMenu = React.lazy(() => import('./ContextMenu'));
const PreviewModal = React.lazy(() => import('./PreviewModal'));
const ModalPDFViewer = React.lazy(() => import('./ModalPDFViewer'));
const WooCommerceElement = React.lazy(() => import('./WooCommerceElements'));
const ElementLibrary = React.lazy(() => import('./ElementLibrary'));
const PropertiesPanel = React.lazy(() => import('./PropertiesPanel'));

// LOG DE TEST POUR VÉRIFIER QUE LES NOUVEAUX ASSETS SONT CHARGÉS
console.log('🎉 PDF BUILDER PRO - NOUVELLE VERSION CHARGÉE - VERSION DEBUG 4.0 - FORCE RELOAD 🎉');
console.log('Timestamp de chargement:', Date.now());

export const PDFCanvasEditor = ({ options }) => {
  const [tool, setTool] = useState('select');
  const [showGrid, setShowGrid] = useState(false);
  const [snapToGrid, setSnapToGrid] = useState(true); // Aimantation activée par défaut
  const [showPreviewModal, setShowPreviewModal] = useState(false);
  const [showPDFModal, setShowPDFModal] = useState(false);
  const [pdfModalUrl, setPdfModalUrl] = useState(null);
  const [isPropertiesCollapsed, setIsPropertiesCollapsed] = useState(false);

  // Hook pour les paramètres globaux
  const globalSettings = useGlobalSettings();

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
    canvasHeight: options.height || 842
  });

  const editorRef = useRef(null);
  const canvasRef = useRef(null);

  // Hook pour le drag and drop
  const dragAndDrop = useDragAndDrop({
    onElementMove: (elementId, position) => {
      canvasState.updateElement(elementId, position);
    },
    onElementDrop: (elementId, position) => {
      canvasState.updateElement(elementId, position);
    },
    snapToGrid: snapToGrid,
    zoom: canvasState.zoom.zoom,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight
  });

  // Gestion des raccourcis clavier
  useKeyboardShortcuts({
    onDelete: canvasState.deleteSelectedElements,
    onCopy: canvasState.copySelectedElements,
    onPaste: canvasState.pasteElements,
    onUndo: canvasState.undo,
    onRedo: canvasState.redo,
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
  const handleElementSelect = useCallback((elementId) => {
    canvasState.selection.selectElement(elementId);
  }, [canvasState.selection]);

  // Gestionnaire pour l'impression
  const handlePrint = useCallback(async () => {
    console.log('� HANDLE PRINT DÉCLENCHÉ - FONCTION IMPRIMER CLIQUEE 🔥');
    console.log('�🚀 NOUVEAUX LOGS DE DEBUG - VERSION 2.0 - DÉBUT');
    console.log('🔍 Début de la génération PDF avec logs détaillés');

    try {
      console.log('Génération PDF pour impression...');

      // Récupérer tous les éléments du canvas
      const elements = canvasState.getAllElements();
      console.log('Éléments récupérés:', elements);
      console.log('Nombre d\'éléments récupérés:', elements.length);

      if (elements.length === 0) {
        alert('Aucun élément à imprimer. Ajoutez des éléments au canvas d\'abord.');
        return;
      }

      // Vérifier la structure des éléments
      elements.forEach((element, index) => {
        console.log(`Élément ${index}:`, element);
        console.log(`- Type: ${element.type}`);
        console.log(`- ID: ${element.id}`);
        console.log(`- Content/Text: ${element.content || element.text}`);
        console.log(`- Position: x=${element.x}, y=${element.y}`);
        console.log(`- Dimensions: width=${element.width}, height=${element.height}`);
      });

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_pdf');
      formData.append('nonce', window.pdfBuilderAjax?.nonce);
      formData.append('elements', JSON.stringify(elements));

      console.log('Envoi de', elements.length, 'éléments au serveur...');
      console.log('Données JSON envoyées:', JSON.stringify(elements, null, 2));

      console.log('Envoi de', elements.length, 'éléments au serveur...');

      // Faire l'appel AJAX
      const response = await fetch(window.pdfBuilderAjax?.ajaxurl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();
      console.log('Données complètes reçues du serveur:', data);
      console.log('🔍 FIN DES LOGS DE DEBUG - VERSION 2.0');

      if (data.success) {
        console.log('PDF généré avec succès côté serveur');
        console.log('Logs de debug serveur:', data.data?.debug_logs || []);
        console.log('Nombre d\'éléments traités:', data.data?.elements_count || 0);
        console.log('Taille du PDF:', data.data?.pdf_size || 0, 'octets');

        // Créer l'URL du PDF
        const pdfDataUrl = `data:application/pdf;base64,${data.data.pdf}`;
        console.log('URL du PDF créée:', pdfDataUrl.substring(0, 100) + '...');

        // Ouvrir le PDF dans une modale
        console.log('Ouverture du PDF dans une modale...');
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

    canvasState.updateElement(elementId, updates);
  }, [canvasState]);

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
  }, [tool]);

  // Gestionnaire pour le drag over
  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  // Gestionnaire pour le drop
  const handleDrop = useCallback((e) => {
    e.preventDefault();
    
    try {
      const data = JSON.parse(e.dataTransfer.getData('application/json'));
      
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
      console.error('Erreur lors du drop:', error);
    }
  }, [canvasState]);

  return (
    <div className="pdf-canvas-editor" ref={editorRef}>
      {/* Barre d'outils principale */}
      <div className="editor-header">
        <div className="editor-title">
          <h2>Éditeur PDF - {options.isNew ? 'Nouveau Template' : options.templateName}</h2>
        </div>
        <div className="editor-actions">
          <button
            className="btn btn-secondary"
            onClick={() => {
              setShowPreviewModal(true);
            }}
          >
            👁️ Aperçu
          </button>
          <button
            className="btn btn-primary"
            onClick={canvasState.saveTemplate}
            disabled={canvasState.isSaving}
          >
            {canvasState.isSaving ? '⏳ Sauvegarde...' : (options.isNew ? '💾 Sauvegarder' : '✏️ Modifier')}
          </button>
        </div>
      </div>

      {/* Barre d'outils - déplacée sous le header pour prendre toute la largeur */}
      <Toolbar
        selectedTool={tool}
        onToolSelect={setTool}
        zoom={canvasState.zoom.zoom}
        onZoomChange={canvasState.zoom.setZoomLevel}
        showGrid={showGrid}
        onShowGridChange={setShowGrid}
        snapToGrid={snapToGrid}
        onSnapToGridChange={setSnapToGrid}
        onUndo={canvasState.undo}
        onRedo={canvasState.redo}
        canUndo={canvasState.canUndo}
        canRedo={canvasState.canRedo}
      />

      <div className="editor-workspace">
        {/* Bibliothèque d'éléments - masquée en mode aperçu */}
        {!showPreviewModal && (
          <div className="editor-sidebar left-sidebar">
            <React.Suspense fallback={<div className="loading">Chargement...</div>}>
              <ElementLibrary
                onAddElement={handleAddElement}
                selectedTool={tool}
                onToolSelect={setTool}
              />
            </React.Suspense>
          </div>
        )}

        {/* Zone de travail principale */}
        <div className="editor-main">
          {/* Canvas avec éléments interactifs */}
          <div
            className="canvas-container"
            onContextMenu={handleContextMenu}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
            style={{ cursor: getCursorStyle() }}
          >
            <div
              className="canvas-zoom-wrapper"
              style={{
                transform: `scale(${canvasState.zoom.zoom})`,
                transformOrigin: 'center'
              }}
            >
              <div
                className="canvas"
                ref={canvasRef}
                onClick={handleCanvasClick}
                style={{
                  width: canvasState.canvasWidth,
                  height: canvasState.canvasHeight,
                  position: 'relative'
                }}
              >
                {/* Grille de fond */}
                {showGrid && (
                  <div
                    className="canvas-grid"
                    style={{
                      position: 'absolute',
                      top: 0,
                      left: 0,
                      width: '100%',
                      height: '100%',
                      backgroundImage: `
                        linear-gradient(to right, #f1f5f9 1px, transparent 1px),
                        linear-gradient(to bottom, #f1f5f9 1px, transparent 1px)
                      `,
                      backgroundSize: '10px 10px',
                      pointerEvents: 'none'
                    }}
                  />
                )}

                {/* Éléments normaux rendus comme composants interactifs */}
                {canvasState.elements
                  .filter(el => !el.type.startsWith('woocommerce-'))
                  .map(element => {
                    return (
                      <CanvasElement
                        key={element.id}
                        element={element}
                        isSelected={canvasState.selection.selectedElements.includes(element.id)}
                        zoom={1} // Le zoom est géré au niveau du wrapper
                        snapToGrid={snapToGrid}
                        gridSize={10}
                        canvasWidth={canvasState.canvasWidth}
                        canvasHeight={canvasState.canvasHeight}
                        onSelect={() => handleElementSelect(element.id)}
                        onUpdate={(updates) => canvasState.updateElement(element.id, updates)}
                        onRemove={() => canvasState.deleteElement(element.id)}
                        onContextMenu={handleContextMenu}
                        dragAndDrop={dragAndDrop}
                      />
                    );
                  })}

                {/* Éléments WooCommerce superposés */}
                {canvasState.elements
                  .filter(el => el.type.startsWith('woocommerce-'))
                  .map(element => (
                    <React.Suspense key={element.id} fallback={null}>
                      <WooCommerceElement
                        element={element}
                        isSelected={canvasState.selection.selectedElements.includes(element.id)}
                        onSelect={handleElementSelect}
                        onUpdate={canvasState.updateElement}
                        dragAndDrop={dragAndDrop}
                        zoom={1} // Le zoom est géré au niveau du wrapper
                        canvasWidth={canvasState.canvasWidth}
                        canvasHeight={canvasState.canvasHeight}
                        orderData={orderData}
                      />
                    </React.Suspense>
                  ))}
              </div>
            </div>
          </div>
        </div>

        {/* Panneau de propriétés - masqué en mode aperçu */}
        {!showPreviewModal && (
          <div className={`editor-sidebar right-sidebar ${isPropertiesCollapsed ? 'collapsed' : ''}`}>
            {!isPropertiesCollapsed && (
              <React.Suspense fallback={<div className="loading">Chargement...</div>}>
                <PropertiesPanel
                  selectedElements={canvasState.selection.selectedElements}
                  elements={canvasState.elements}
                  onPropertyChange={handlePropertyChange}
                  onBatchUpdate={handleBatchUpdate}
                />
              </React.Suspense>
            )}
          </div>
        )}
      </div>

      {/* Bouton de toggle repositionné à la fin pour être au-dessus de tout - masqué en mode aperçu */}
      {!showPreviewModal && (
        <button
          className="sidebar-toggle-fixed"
          onClick={() => setIsPropertiesCollapsed(!isPropertiesCollapsed)}
          title={isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau'}
          style={{
            position: 'fixed',
            top: '50%',
            right: isPropertiesCollapsed ? '70px' : '430px',
            transform: 'translateY(-50%)',
            zIndex: 999999
          }}
        >
          {isPropertiesCollapsed ? '◀' : '▶'}
        </button>
      )}

      {/* Menu contextuel */}
      {canvasState.contextMenu.contextMenu && (
        <React.Suspense fallback={null}>
          <ContextMenu
            menu={canvasState.contextMenu.contextMenu}
            onAction={handleContextMenuAction}
            isAnimating={canvasState.contextMenu.isAnimating || false}
          />
        </React.Suspense>
      )}

      {/* Indicateur d'état */}
      <div className="editor-status">
        <span>Éléments: {canvasState.elements.length}</span>
        <span>|</span>
        <span>Zoom: {Math.round(canvasState.zoom.zoom * 100)}%</span>
        <span>|</span>
        <span>Outil: {tool}</span>
        {canvasState.selection.selectedElements.length > 0 && (
          <>
            <span>|</span>
            <span>Éléments sélectionnés: {canvasState.selection.selectedElements.length}</span>
          </>
        )}
      </div>

      {/* Modale d'aperçu */}
      <React.Suspense fallback={null}>
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
          onOpenPDFModal={(pdfUrl) => {
            setPdfModalUrl(pdfUrl);
            setShowPDFModal(true);
            setShowPreviewModal(false); // Fermer la modale d'aperçu
          }}
        />
      </React.Suspense>

      <React.Suspense fallback={null}>
        <ModalPDFViewer
          isOpen={showPDFModal}
          onClose={() => {
            setShowPDFModal(false);
            // Libérer l'URL du blob après la fermeture
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
      </React.Suspense>
    </div>
  );
};