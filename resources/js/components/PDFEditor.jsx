import React, { useState, useRef, useEffect } from 'react';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur canvas simple et original
 * Version basique avec canvas interactif
 */
const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // État de l'éditeur
  const [zoom, setZoom] = useState(1.0);
  const [elements, setElements] = useState(initialElements);

  // Référence du canvas
  const canvasRef = useRef(null);

  // Gestionnaire de zoom
  const handleZoomChange = (newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  };

  // Gestionnaire de sauvegarde
  const handleSave = () => {
    if (onSave) {
      onSave(elements);
    }
    console.log('PDF sauvegardé:', elements);
  };

  // Effet pour dessiner sur le canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Effacer le canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Appliquer le zoom
    ctx.save();
    ctx.scale(zoom, zoom);

    // Dessiner les éléments
    elements.forEach((element, index) => {
      ctx.fillStyle = element.color || '#000000';
      ctx.font = `${element.fontSize || 16}px Arial`;

      if (element.type === 'text') {
        ctx.fillText(element.text || 'Texte', element.x || 10, element.y || 30);
      } else if (element.type === 'rectangle') {
        ctx.fillRect(element.x || 10, element.y || 10, element.width || 100, element.height || 50);
      }
    });

    ctx.restore();
  }, [elements, zoom]);

  // Gestionnaire de clic sur le canvas
  const handleCanvasClick = (e) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Ajouter un élément texte au clic
    const newElement = {
      id: Date.now(),
      type: 'text',
      text: 'Nouveau texte',
      x: x,
      y: y,
      fontSize: 16,
      color: '#000000'
    };

    setElements([...elements, newElement]);
  };

  return (
    <div className="pdf-editor">
      {/* Header simple */}
      <div className="editor-header">
        <div className="editor-header-left">
          <h2 className="editor-title">Éditeur PDF - {templateName || 'Nouveau template'}</h2>
        </div>
        <div className="editor-header-right">
          <button className="header-button" onClick={handleSave}>
            💾 Sauvegarder
          </button>
        </div>
      </div>

      {/* Toolbar simple */}
      <div className="toolbar">
        <div className="toolbar-section">
          <label>Zoom: </label>
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
        <div className="toolbar-section">
          <button onClick={() => setElements([])}>🗑️ Effacer tout</button>
        </div>
      </div>

      {/* Zone de canvas */}
      <div className="editor-workspace">
        <div className="canvas-container">
          <canvas
            ref={canvasRef}
            className="pdf-canvas"
            width={595}
            height={842}
            style={{
              transform: `scale(${zoom})`,
              transformOrigin: 'top left'
            }}
            onClick={handleCanvasClick}
          />
        </div>
      </div>

      {/* Informations */}
      <div className="editor-footer">
        <p>Éléments: {elements.length} | Clickez sur le canvas pour ajouter du texte</p>
      </div>
    </div>
  );
};

export { PDFEditor };