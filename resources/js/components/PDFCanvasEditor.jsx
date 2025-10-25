import React, { useState, useRef, useEffect } from 'react';
import { Toolbar } from './Toolbar.jsx';
import { CanvasElement } from './CanvasElement.jsx';

export const PDFCanvasEditor = ({
  options = {}
}) => {
  // initialElements is an object with { elements: [...], settings: {...} }
  const initialElements = Array.isArray(options.initialElements?.elements) ? options.initialElements.elements : [];
  const [elements, setElements] = useState(initialElements);
  const [selectedElement, setSelectedElement] = useState(null);
  const [selectedTool, setSelectedTool] = useState('select');
  const [zoom, setZoom] = useState(1);
  const [showGrid, setShowGrid] = useState(true);
  const [snapToGrid, setSnapToGrid] = useState(true);
  const canvasRef = useRef(null);

  const canvasWidth = 595; // A4 width in pixels at 72 DPI
  const canvasHeight = 842; // A4 height in pixels at 72 DPI

  const handleToolSelect = (tool) => {
    setSelectedTool(tool);
  };

  const handleZoomChange = (newZoom) => {
    setZoom(newZoom);
  };

  const handleElementSelect = (elementId) => {
    setSelectedElement(elementId);
  };

  const handleCanvasClick = (e) => {
    if (selectedTool !== 'select') {
      // Add new element
      const rect = canvasRef.current.getBoundingClientRect();
      const x = (e.clientX - rect.left) / zoom;
      const y = (e.clientY - rect.top) / zoom;

      const newElement = {
        id: Date.now().toString(),
        type: selectedTool.replace('add-', ''),
        x: x,
        y: y,
        width: 100,
        height: 50,
        content: `Nouveau ${selectedTool.replace('add-', '')}`,
        backgroundColor: '#ffffff',
        color: '#000000',
        borderWidth: 1,
        borderColor: '#e5e7eb',
        visible: true
      };

      setElements([...elements, newElement]);
      setSelectedElement(newElement.id);
      setSelectedTool('select');
    } else {
      setSelectedElement(null);
    }
  };

  const canvasStyle = {
    width: `${canvasWidth * zoom}px`,
    height: `${canvasHeight * zoom}px`,
    backgroundColor: 'white',
    border: '1px solid #e5e7eb',
    position: 'relative',
    margin: '20px auto',
    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
    overflow: 'hidden'
  };

  const gridStyle = {
    backgroundImage: showGrid ? `
      linear-gradient(to right, #f1f5f9 1px, transparent 1px),
      linear-gradient(to bottom, #f1f5f9 1px, transparent 1px)
    ` : 'none',
    backgroundSize: `${10 * zoom}px ${10 * zoom}px`
  };

  return (
    <div style={{ width: '100%', height: '100vh', display: 'flex', flexDirection: 'column' }}>
      {/* Toolbar */}
      <div style={{ borderBottom: '1px solid #e5e7eb', padding: '10px', backgroundColor: '#f8fafc' }}>
        <Toolbar
          selectedTool={selectedTool}
          onToolSelect={handleToolSelect}
          zoom={zoom}
          onZoomChange={handleZoomChange}
          showGrid={showGrid}
          onShowGridChange={setShowGrid}
          snapToGrid={snapToGrid}
          onSnapToGridChange={setSnapToGrid}
          snapToElements={true}
          onSnapToElementsChange={() => {}}
          onUndo={() => {}}
          onRedo={() => {}}
          canUndo={false}
          canRedo={false}
          settings={{ showGrid: showGrid, snapToGrid: snapToGrid, snapToElements: true }}
        />
      </div>

      {/* Main Editor Area */}
      <div style={{ display: 'flex', flex: 1, overflow: 'hidden' }}>
        {/* Canvas */}
        <div style={{ flex: 1, padding: '20px', backgroundColor: '#f1f5f9' }}>
          <div
            ref={canvasRef}
            style={{ ...canvasStyle, ...gridStyle }}
            onClick={handleCanvasClick}
          >
            {elements.map(element => (
              <CanvasElement
                key={element.id}
                element={element}
                isSelected={selectedElement === element.id}
                zoom={zoom}
                snapToGrid={snapToGrid}
                gridSize={10}
                canvasWidth={canvasWidth}
                canvasHeight={canvasHeight}
                onSelect={handleElementSelect}
                onUpdate={() => {}}
                onRemove={() => {}}
                onContextMenu={() => {}}
              />
            ))}
          </div>
        </div>

        {/* Properties Panel */}
        <div style={{
          width: '300px',
          borderLeft: '1px solid #e5e7eb',
          backgroundColor: '#f8fafc',
          padding: '20px',
          overflowY: 'auto'
        }}>
          <h3 style={{ marginTop: 0, color: '#374151' }}>Propriétés</h3>
          {selectedElement ? (
            <div>
              <p><strong>Élément sélectionné:</strong> {selectedElement}</p>
              <div style={{ marginTop: '20px' }}>
                <label style={{ display: 'block', marginBottom: '5px' }}>
                  Contenu:
                  <input
                    type="text"
                    style={{
                      width: '100%',
                      padding: '8px',
                      border: '1px solid #d1d5db',
                      borderRadius: '4px',
                      marginTop: '5px'
                    }}
                    value={elements.find(el => el.id === selectedElement)?.content || ''}
                    onChange={(e) => {
                      setElements(elements.map(el =>
                        el.id === selectedElement
                          ? { ...el, content: e.target.value }
                          : el
                      ));
                    }}
                  />
                </label>
              </div>
            </div>
          ) : (
            <p style={{ color: '#6b7280' }}>Sélectionnez un élément pour modifier ses propriétés</p>
          )}

          {/* Element Library */}
          <div style={{ marginTop: '30px' }}>
            <h4 style={{ color: '#374151', marginBottom: '10px' }}>Bibliothèque d'éléments</h4>
            <div style={{ display: 'grid', gap: '10px' }}>
              {[
                { id: 'add-text', label: 'Texte', icon: '📝' },
                { id: 'add-rectangle', label: 'Rectangle', icon: '▭' },
                { id: 'add-image', label: 'Image', icon: '🖼️' }
              ].map(tool => (
                <button
                  key={tool.id}
                  onClick={() => setSelectedTool(tool.id)}
                  style={{
                    padding: '10px',
                    border: selectedTool === tool.id ? '2px solid #007cba' : '1px solid #d1d5db',
                    borderRadius: '4px',
                    backgroundColor: selectedTool === tool.id ? '#eff6ff' : 'white',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                  }}
                >
                  <span>{tool.icon}</span>
                  <span>{tool.label}</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
