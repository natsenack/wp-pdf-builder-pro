import React, { useState, useRef, useEffect } from 'react';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur canvas original et basique
 * Version vraiment simple : canvas + ajout de texte par clic
 */
const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  const [elements, setElements] = useState(initialElements);
  const canvasRef = useRef(null);

  // Fonction pour ajouter du texte au clic
  const handleCanvasClick = (event) => {
    const canvas = canvasRef.current;
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

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

  // Fonction de rendu du canvas
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Bordure
    ctx.strokeStyle = '#cccccc';
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, canvas.width, canvas.height);

    // Dessiner les éléments
    elements.forEach(element => {
      if (element.type === 'text') {
        ctx.fillStyle = element.color || '#000000';
        ctx.font = `${element.fontSize || 16}px Arial`;
        ctx.fillText(element.text || 'Texte', element.x || 10, element.y || 30);
      }
    });
  }, [elements]);

  // Fonction de sauvegarde
  const handleSave = () => {
    if (onSave) {
      onSave(elements);
    }
    console.log('Éléments sauvegardés:', elements);
  };

  return (
    <div className="pdf-editor">
      <div className="editor-header">
        <h2>Éditeur PDF - Version Originale</h2>
        <button onClick={handleSave} className="save-button">
          💾 Sauvegarder
        </button>
      </div>

      <div className="canvas-container">
        <canvas
          ref={canvasRef}
          width={800}
          height={600}
          onClick={handleCanvasClick}
          style={{
            border: '2px solid #007cba',
            cursor: 'crosshair',
            backgroundColor: '#ffffff'
          }}
        />
        <p className="instructions">
          Cliquez sur le canvas pour ajouter du texte
        </p>
      </div>

      <div className="elements-list">
        <h3>Éléments ({elements.length})</h3>
        {elements.map((element, index) => (
          <div key={element.id} className="element-item">
            {element.type}: {element.text || `Élément ${index + 1}`}
          </div>
        ))}
      </div>
    </div>
  );
};

export default PDFEditor;