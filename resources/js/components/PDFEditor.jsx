import React, { useState, useRef, useEffect } from 'react';
import { Toolbar } from './Toolbar';
import PreviewModal from './preview-system/components/PreviewModal';
import { PreviewProvider } from './preview-system/context/PreviewProvider';
import { usePreviewContext } from './preview-system/context/PreviewContext';
import ElementLibrary from './ElementLibrary';
import PropertiesPanel from './PropertiesPanel';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur principal complet avec éléments et propriétés
 * Phase 2.2.4.1 - Implémentation complète du système d'éléments
 */
const PDFEditorContent = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // Contexte d'aperçu
  const { actions: { openPreview } } = usePreviewContext();

  // État de l'éditeur
  const [selectedTool, setSelectedTool] = useState('select');
  const [zoom, setZoom] = useState(1.0);
  const [showGrid, setShowGrid] = useState(true);
  const [snapToGrid, setSnapToGrid] = useState(true);
  const [elements, setElements] = useState(initialElements || []);
  const [history, setHistory] = useState([initialElements || []]);
  const [historyIndex, setHistoryIndex] = useState(0);
  const [selectedElement, setSelectedElement] = useState(null);
  const [showElementLibrary, setShowElementLibrary] = useState(true);
  const [showPropertiesPanel, setShowPropertiesPanel] = useState(true);

  // Références
  const canvasRef = useRef(null);

  // Gestionnaire d'outils
  const handleToolSelect = (toolId) => {
    setSelectedTool(toolId);
  };

  // Gestionnaire de zoom
  const handleZoomChange = (newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  };

  // Gestionnaire de grille
  const handleShowGridChange = (show) => {
    setShowGrid(show);
  };

  const handleSnapToGridChange = (snap) => {
    setSnapToGrid(snap);
  };

  // Gestionnaire d'historique
  const handleUndo = () => {
    if (historyIndex > 0) {
      setHistoryIndex(historyIndex - 1);
      setElements(history[historyIndex - 1]);
    }
  };

  const handleRedo = () => {
    if (historyIndex < history.length - 1) {
      setHistoryIndex(historyIndex + 1);
      setElements(history[historyIndex + 1]);
    }
  };

  const canUndo = historyIndex > 0;
  const canRedo = historyIndex < history.length - 1;

  // Gestionnaire d'aperçu
  const handlePreview = () => {
    openPreview('canvas', null, { elements });
  };

  // Gestionnaire d'ajout d'élément depuis la bibliothèque
  const handleAddElement = (elementType, defaultProperties = {}) => {
    const newElement = {
      id: Date.now(),
      type: elementType,
      x: Math.random() * 400 + 50, // Position aléatoire
      y: Math.random() * 600 + 50,
      ...defaultProperties
    };

    const newElements = [...elements, newElement];
    handleElementsChange(newElements);
    setSelectedElement(newElement.id);
  };

  // Gestionnaire de sélection d'élément
  const handleElementSelect = (elementId) => {
    setSelectedElement(elementId);
  };

  // Gestionnaire de mise à jour des propriétés d'un élément
  const handleElementUpdate = (elementId, newProperties) => {
    const newElements = elements.map(element =>
      element.id === elementId ? { ...element, ...newProperties } : element
    );
    handleElementsChange(newElements);
  };

  // Gestionnaire de suppression d'élément
  const handleElementDelete = (elementId) => {
    const newElements = elements.filter(element => element.id !== elementId);
    handleElementsChange(newElements);
    if (selectedElement === elementId) {
      setSelectedElement(null);
    }
  };

  // Gestionnaire de sauvegarde des éléments
  const handleElementsChange = (newElements) => {
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(newElements);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
    setElements(newElements);

    // Sauvegarder automatiquement si callback fourni
    if (onSave) {
      onSave(newElements);
    }
  };

  // Gestionnaire de drag over
  const handleDragOver = (event) => {
    event.preventDefault(); // Permettre le drop
    event.dataTransfer.dropEffect = 'copy';
  };

  // Gestionnaire de drop
  const handleDrop = (event) => {
    event.preventDefault();

    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    try {
      const data = JSON.parse(event.dataTransfer.getData('application/json'));

      if (data.type === 'element') {
        // Créer un nouvel élément à la position du drop
        const newElement = {
          id: Date.now(),
          type: data.elementType,
          x: x,
          y: y,
          ...data.defaultProperties
        };

        const newElements = [...elements, newElement];
        handleElementsChange(newElements);
        setSelectedElement(newElement.id);
      }
    } catch (error) {
      console.error('Erreur lors du drop:', error);
    }
  };

  // Gestionnaire de clic sur le canvas
  const handleCanvasClick = (event) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left) / zoom;
    const y = (event.clientY - rect.top) / zoom;

    // Si un outil est sélectionné, créer un élément
    if (selectedTool !== 'select') {
      let newElement;

      switch (selectedTool) {
        case 'add-text':
          newElement = {
            id: Date.now(),
            type: 'text',
            text: 'Nouveau texte',
            x: x,
            y: y,
            fontSize: 16,
            color: '#000000',
            fontFamily: 'Arial'
          };
          break;
        case 'add-rectangle':
          newElement = {
            id: Date.now(),
            type: 'rectangle',
            x: x,
            y: y,
            width: 100,
            height: 50,
            backgroundColor: '#ffffff',
            borderColor: '#000000',
            borderWidth: 1
          };
          break;
        case 'add-circle':
          newElement = {
            id: Date.now(),
            type: 'circle',
            x: x,
            y: y,
            radius: 25,
            backgroundColor: '#ffffff',
            borderColor: '#000000',
            borderWidth: 1
          };
          break;
        default:
          return;
      }

      const newElements = [...elements, newElement];
      handleElementsChange(newElements);
      setSelectedElement(newElement.id);
      setSelectedTool('select'); // Revenir à l'outil de sélection
    } else {
      // Sélectionner l'élément sous le curseur
      const clickedElement = elements.find(element => {
        if (element.type === 'text') {
          const ctx = canvas.getContext('2d');
          const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
          ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
          const metrics = ctx.measureText(element.text || 'Texte');
          return x >= element.x && x <= element.x + metrics.width &&
                 y >= element.y - element.fontSize && y <= element.y;
        } else if (element.type === 'rectangle') {
          return x >= element.x && x <= element.x + (element.width || 100) &&
                 y >= element.y && y <= element.y + (element.height || 50);
        } else if (element.type === 'circle') {
          const dx = x - element.x;
          const dy = y - element.y;
          return Math.sqrt(dx * dx + dy * dy) <= (element.radius || 25);
        }
        return false;
      });

      setSelectedElement(clickedElement ? clickedElement.id : null);
    }
  };

  // Fonction de rendu du canvas
  const renderCanvas = () => {
    const canvas = canvasRef.current;
    if (!canvas) {
      console.log('PDFEditor renderCanvas: No canvas ref');
      return;
    }

    console.log('PDFEditor renderCanvas called - canvas dimensions:', canvas.width, 'x', canvas.height, '- elements count:', elements.length);
    if (elements.length > 0) {
      console.log('PDFEditor rendering elements:', elements);
    }

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Grille si activée
    if (showGrid) {
      ctx.strokeStyle = '#f0f0f0';
      ctx.lineWidth = 1;
      const gridSize = 20;

      for (let x = 0; x <= canvas.width; x += gridSize) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
      }

      for (let y = 0; y <= canvas.height; y += gridSize) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
      }
    }

    // Dessiner les éléments
    elements.forEach((element, index) => {
      console.log(`PDFEditor rendering element ${index}:`, element);

      // Mettre en évidence l'élément sélectionné
      if (selectedElement === element.id) {
        ctx.strokeStyle = '#007cba';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);

        if (element.type === 'text') {
          const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
          ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
          const metrics = ctx.measureText(element.text || 'Texte');
          ctx.strokeRect(element.x - 5, element.y - element.fontSize - 5,
                        metrics.width + 10, element.fontSize + 10);
        } else if (element.type === 'rectangle') {
          ctx.strokeRect(element.x - 5, element.y - 5,
                        (element.width || 100) + 10, (element.height || 50) + 10);
        } else if (element.type === 'circle') {
          ctx.beginPath();
          ctx.arc(element.x, element.y, (element.radius || 25) + 5, 0, 2 * Math.PI);
          ctx.stroke();
        }

        ctx.setLineDash([]);
      }

      // Dessiner l'élément
      if (element.type === 'text') {
        ctx.fillStyle = element.color || '#000000';
        const fontWeight = element.fontWeight ? `${element.fontWeight} ` : '';
        ctx.font = `${fontWeight}${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
        const textX = element.x || 10;
        const textY = element.y || 30;
        console.log(`PDFEditor drawing text at (${textX}, ${textY}): "${element.text || 'Texte'}"`);
        ctx.fillText(element.text || 'Texte', textX, textY);
      } else if (element.type === 'rectangle') {
        ctx.fillStyle = element.backgroundColor || '#ffffff';
        const rectX = element.x || 10;
        const rectY = element.y || 10;
        const rectWidth = element.width || 100;
        const rectHeight = element.height || 50;
        console.log(`PDFEditor drawing rectangle at (${rectX}, ${rectY}) size (${rectWidth}, ${rectHeight})`);
        ctx.fillRect(rectX, rectY, rectWidth, rectHeight);
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.strokeRect(rectX, rectY, rectWidth, rectHeight);
        }
      } else if (element.type === 'circle') {
        ctx.fillStyle = element.backgroundColor || '#ffffff';
        ctx.beginPath();
        ctx.arc(element.x || 10, element.y || 10, element.radius || 25, 0, 2 * Math.PI);
        ctx.fill();
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.stroke();
        }
      }
    });
  };

  // Re-rendre à chaque changement
  useEffect(() => {
    renderCanvas();
  }, [elements, zoom, showGrid, selectedElement]);

  // S'assurer que le canvas se rend au montage initial
  useEffect(() => {
    renderCanvas();
  }, []);

  // Mettre à jour les éléments quand initialElements change
  useEffect(() => {
    console.log('PDFEditor useEffect triggered - initialElements:', initialElements, 'length:', initialElements ? initialElements.length : 'undefined');
    if (initialElements && initialElements.length > 0) {
      console.log('PDFEditor: Setting elements from initialElements:', initialElements.length, 'elements');
      console.log('PDFEditor: Elements data:', initialElements);
      setElements(initialElements);
      setHistory([initialElements]);
      setHistoryIndex(0);
    } else {
      console.log('PDFEditor: No initialElements provided or empty array');
      // Si pas d'éléments initiaux, initialiser avec un tableau vide
      setElements([]);
      setHistory([[]]);
      setHistoryIndex(0);
    }
  }, [initialElements]);

  // Écouter les événements globaux pour le bouton aperçu du header
  useEffect(() => {
    const handleGlobalPreview = (event) => {
      if (event.type === 'pdfBuilderPreview') {
        handlePreview();
      }
    };

    // Écouter l'événement personnalisé
    document.addEventListener('pdfBuilderPreview', handleGlobalPreview);

    // Exposer la fonction globalement pour le bouton du header
    window.pdfBuilderPro = window.pdfBuilderPro || {};
    window.pdfBuilderPro.triggerPreview = handlePreview;

    // Nettoyer les écouteurs
    return () => {
      document.removeEventListener('pdfBuilderPreview', handleGlobalPreview);
    };
  }, [elements]);

  return (
    <div className="pdf-editor">
      {/* Toolbar principale */}
      <Toolbar
        selectedTool={selectedTool}
        onToolSelect={handleToolSelect}
        zoom={zoom}
        onZoomChange={handleZoomChange}
        showGrid={showGrid}
        onShowGridChange={handleShowGridChange}
        snapToGrid={snapToGrid}
        onSnapToGridChange={handleSnapToGridChange}
        onUndo={handleUndo}
        onRedo={handleRedo}
        canUndo={canUndo}
        canRedo={canRedo}
        onPreview={handlePreview}
      />

      {/* Zone de travail principale */}
      <div className="editor-workspace">
        {/* Bibliothèque d'éléments */}
        {showElementLibrary && (
          <div className="element-library-panel">
            <ElementLibrary
              onAddElement={handleAddElement}
              selectedTool={selectedTool}
              onToolSelect={setSelectedTool}
            />
          </div>
        )}

        {/* Canvas principal */}
        <div className="canvas-container">
          <canvas
            ref={canvasRef}
            className="pdf-canvas"
            width={595}
            height={842}
            style={{
              transform: `scale(${zoom})`,
              transformOrigin: 'top left',
              cursor: selectedTool === 'select' ? 'default' : 'crosshair'
            }}
            onClick={handleCanvasClick}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
          />
        </div>

        {/* Panel des propriétés */}
        {showPropertiesPanel && selectedElement && (
          <div className="properties-panel-container">
            <PropertiesPanel
              selectedElements={selectedElement ? [selectedElement] : []}
              elements={elements}
              onPropertyChange={(elementId, property, value) => {
                handleElementUpdate(elementId, { [property]: value });
              }}
              onBatchUpdate={(updates) => {
                // Handle batch updates if needed
                updates.forEach(update => {
                  handleElementUpdate(update.elementId, update.properties);
                });
              }}
            />
          </div>
        )}
      </div>

      {/* Barre d'outils secondaire */}
      <div className="editor-toolbar-secondary">
        <button
          onClick={() => setShowElementLibrary(!showElementLibrary)}
          className={`tool-btn ${showElementLibrary ? 'active' : ''}`}
        >
          📚 Bibliothèque
        </button>
        <button
          onClick={() => setShowPropertiesPanel(!showPropertiesPanel)}
          className={`tool-btn ${showPropertiesPanel ? 'active' : ''}`}
        >
          ⚙️ Propriétés
        </button>
        <span className="status-info">
          Éléments: {elements.length} | Sélectionné: {selectedElement ? 'Oui' : 'Non'}
        </span>
      </div>

      {/* Modal d'aperçu */}
      <PreviewModal />
    </div>
  );
};

export const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  return (
    <PreviewProvider>
      <PDFEditorContent
        initialElements={initialElements}
        onSave={onSave}
        templateName={templateName}
        isNew={isNew}
      />
    </PreviewProvider>
  );
};
