import React, { useState, useRef, useEffect, useCallback } from 'react';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur Canvas Simple et Fonctionnel
 * Version simplifiée pour éviter les erreurs de dépendances
 */
const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // États simples
  const [elements, setElements] = useState(initialElements);
  const [zoom, setZoom] = useState(1.0);
  const [showGrid, setShowGrid] = useState(true);
  const canvasRef = useRef(null);

  // Dimensions du canvas
  const canvasWidth = 595;
  const canvasHeight = 842;

  // Gestionnaire de zoom simple
  const handleZoomChange = useCallback((newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  }, []);

  // Gestionnaire de sauvegarde
  const handleSave = useCallback(() => {
    if (onSave) {
      onSave(elements);
    }
    console.log('Éléments sauvegardés:', elements);
  }, [elements, onSave]);

  // Gestionnaire d'ajout d'élément texte
  const handleAddText = useCallback(() => {
    const newElement = {
      id: Date.now(),
      type: 'text',
      text: 'Nouveau texte',
      x: 50,
      y: 50,
      fontSize: 16,
      color: '#000000',
      fontFamily: 'Arial'
    };
    setElements([...elements, newElement]);
  }, [elements]);

  // Gestionnaire d'ajout de rectangle
  const handleAddRectangle = useCallback(() => {
    const newElement = {
      id: Date.now(),
      type: 'rectangle',
      x: 100,
      y: 100,
      width: 100,
      height: 50,
      backgroundColor: '#ffffff',
      borderColor: '#000000',
      borderWidth: 1
    };
    setElements([...elements, newElement]);
  }, [elements]);

  // Fonction de rendu du canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

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

    // Appliquer le zoom
    ctx.save();
    ctx.scale(zoom, zoom);

    // Dessiner les éléments
    elements.forEach(element => {
      if (element.type === 'text') {
        ctx.fillStyle = element.color || '#000000';
        ctx.font = `${element.fontSize || 16}px ${element.fontFamily || 'Arial'}`;
        ctx.fillText(element.text || 'Texte', element.x || 10, element.y || 30);
      } else if (element.type === 'rectangle') {
        ctx.fillStyle = element.backgroundColor || '#ffffff';
        ctx.fillRect(element.x || 10, element.y || 10, element.width || 100, element.height || 50);
        if (element.borderWidth > 0) {
          ctx.strokeStyle = element.borderColor || '#000000';
          ctx.lineWidth = element.borderWidth || 1;
          ctx.strokeRect(element.x || 10, element.y || 10, element.width || 100, element.height || 50);
        }
      }
    });

    ctx.restore();
  }, [elements, zoom, showGrid]);

  return (
    <div className="pdf-editor">
      {/* Header simple */}
      <div className="editor-header">
        <h2>Éditeur PDF - {templateName || 'Nouveau template'}</h2>
        <div className="header-actions">
          <button onClick={handleSave} className="save-btn">💾 Sauvegarder</button>
        </div>
      </div>

      {/* Toolbar simple */}
      <div className="editor-toolbar">
        <div className="toolbar-group">
          <button onClick={handleAddText} className="tool-btn">📝 Texte</button>
          <button onClick={handleAddRectangle} className="tool-btn">▭ Rectangle</button>
        </div>

        <div className="toolbar-group">
          <label>Zoom:</label>
          <input
            type="range"
            min="0.1"
            max="3.0"
            step="0.1"
            value={zoom}
            onChange={(e) => handleZoomChange(parseFloat(e.target.value))}
          />
          <span>{Math.round(zoom * 100)}%</span>
        </div>

        <div className="toolbar-group">
          <label>
            <input
              type="checkbox"
              checked={showGrid}
              onChange={(e) => setShowGrid(e.target.checked)}
            />
            Grille
          </label>
        </div>
      </div>

      {/* Zone de canvas */}
      <div className="canvas-container">
        <canvas
          ref={canvasRef}
          width={canvasWidth}
          height={canvasHeight}
          style={{
            border: '2px solid #007cba',
            backgroundColor: '#ffffff',
            maxWidth: '100%',
            height: 'auto'
          }}
        />
      </div>

      {/* Informations */}
      <div className="editor-footer">
        <div className="status-info">
          <span>Éléments: {elements.length}</span>
          <span>Zoom: {Math.round(zoom * 100)}%</span>
          <span>Grille: {showGrid ? 'Activée' : 'Désactivée'}</span>
        </div>
      </div>

      {/* Liste des éléments */}
      <div className="elements-panel">
        <h3>Éléments ({elements.length})</h3>
        <div className="elements-list">
          {elements.map((element, index) => (
            <div key={element.id} className="element-item">
              <span className="element-type">{element.type}</span>
              <span className="element-content">
                {element.type === 'text' ? element.text : `${element.width}x${element.height}`}
              </span>
              <button
                onClick={() => setElements(elements.filter(e => e.id !== element.id))}
                className="delete-btn"
              >
                🗑️
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export { PDFEditor };
