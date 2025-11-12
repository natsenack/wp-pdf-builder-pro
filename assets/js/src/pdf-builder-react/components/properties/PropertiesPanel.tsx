import React, { useState, useMemo, useCallback, memo } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { ProductTableProperties } from './ProductTableProperties';
import { CustomerInfoProperties } from './CustomerInfoProperties';
import { CompanyInfoProperties } from './CompanyInfoProperties';
import { CompanyLogoProperties } from './CompanyLogoProperties';
import { OrderNumberProperties } from './OrderNumberProperties';
import { DynamicTextProperties } from './DynamicTextProperties';
import { MentionsProperties } from './MentionsProperties';
import { DocumentTypeProperties } from './DocumentTypeProperties';
import { TextProperties } from './TextProperties';
import { ShapeProperties } from './ShapeProperties';
import { ImageProperties } from './ImageProperties';
import { LineProperties } from './LineProperties';
import { ElementProperties } from './ElementProperties';

interface PropertiesPanelProps {
  className?: string;
}

export const PropertiesPanel = memo(function PropertiesPanel({ className }: PropertiesPanelProps) {
  const { state, updateElement, removeElement } = useBuilder();
  const [activeTab, setActiveTab] = useState<{ [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }>({});

  // Optimisation: mémoriser les éléments sélectionnés
  const selectedElements = useMemo(() =>
    state.elements.filter(el => state.selection.selectedElements.includes(el.id)),
    [state.elements, state.selection.selectedElements]
  );

  // Optimisation: mémoriser les handlers
  const handlePropertyChange = useCallback((elementId: string, property: string, value: unknown) => {
    updateElement(elementId, { [property]: value });
  }, [updateElement]);

  const handleDeleteSelected = useCallback(() => {
    state.selection.selectedElements.forEach(id => {
      removeElement(id);
    });
  }, [state.selection.selectedElements, removeElement]);

  if (selectedElements.length === 0) {
    return (
      <div className={`pdf-builder-properties ${className || ''}`} style={{
        padding: '12px',
        backgroundColor: '#f9f9f9',
        border: '1px solid #ddd',
        borderRadius: '4px',
        minHeight: '200px',
        maxHeight: 'calc(100vh - 32px)',
        overflowY: 'auto'
      }}>
        <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Propriétés
        </h4>
        <p style={{ color: '#999', fontSize: '14px', margin: '0' }}>
          Sélectionnez un élément pour voir ses propriétés
        </p>
      </div>
    );
  }

  return (
    <div className={`pdf-builder-properties ${className || ''}`} style={{
      padding: '12px',
      backgroundColor: '#f9f9f9',
      border: '1px solid #ddd',
      borderRadius: '4px',
      maxHeight: 'calc(100vh - 32px)',
      overflowY: 'auto'
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
        <h4 style={{ margin: '0', fontSize: '14px', fontWeight: 'bold' }}>
          Propriétés ({selectedElements.length})
        </h4>
        <div style={{ display: 'flex', gap: '4px' }}>
          <button
            onClick={handleDeleteSelected}
            style={{
              padding: '4px 8px',
              border: '1px solid #dc3545',
              borderRadius: '4px',
              backgroundColor: '#dc3545',
              color: '#ffffff',
              cursor: 'pointer',
              fontSize: '12px'
            }}
          >
            🗑️ Supprimer
          </button>
        </div>
      </div>

      {selectedElements.map(element => (
        <div key={element.id} style={{
          marginBottom: '16px',
          padding: '12px',
          backgroundColor: '#ffffff',
          border: '1px solid #e0e0e0',
          borderRadius: '4px',
          maxHeight: 'calc(100vh - 120px)',
          overflowY: 'auto'
        }}>
          <h5 style={{ margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' }}>
            {element.type.charAt(0).toUpperCase() + element.type.slice(1)} - {element.id.slice(0, 8)}
          </h5>

          {/* Propriétés communes - masquées pour les éléments WooCommerce et les éléments de base qui ont leurs propres onglets */}
          {element.type !== 'product_table' && element.type !== 'customer_info' && element.type !== 'company_info' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'document_type' && element.type !== 'dynamic-text' && element.type !== 'mentions' && element.type !== 'text' && element.type !== 'rectangle' && element.type !== 'circle' && element.type !== 'image' && element.type !== 'line' && (
          <div style={{ display: 'grid', gap: '8px' }}>
            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Position X
              </label>
              <input
                type="number"
                value={element.x}
                onChange={(e) => handlePropertyChange(element.id, 'x', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Position Y
              </label>
              <input
                type="number"
                value={element.y}
                onChange={(e) => handlePropertyChange(element.id, 'y', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Largeur
              </label>
              <input
                type="number"
                value={element.width}
                onChange={(e) => handlePropertyChange(element.id, 'width', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Hauteur
              </label>
              <input
                type="number"
                value={element.height}
                onChange={(e) => handlePropertyChange(element.id, 'height', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Rotation (°)
              </label>
              <input
                type="number"
                value={element.rotation || 0}
                onChange={(e) => handlePropertyChange(element.id, 'rotation', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Opacité
              </label>
              <input
                type="range"
                min="0"
                max="1"
                step="0.1"
                value={element.opacity || 1}
                onChange={(e) => handlePropertyChange(element.id, 'opacity', parseFloat(e.target.value))}
                style={{ width: '100%' }}
              />
              <span style={{ fontSize: '11px', color: '#666' }}>
                {Math.round((element.opacity || 1) * 100)}%
              </span>
            </div>
          </div>
          )}

          {element.type === 'product_table' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <ProductTableProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'customer_info' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <CustomerInfoProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'company_info' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <CompanyInfoProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'company_logo' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <CompanyLogoProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'order_number' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <OrderNumberProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'document_type' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <DocumentTypeProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'dynamic-text' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <DynamicTextProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'mentions' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <MentionsProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'text' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <TextProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {(element.type === 'rectangle' || element.type === 'circle') && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <ShapeProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'image' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <ImageProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {element.type === 'line' && (
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            <LineProperties
              element={element as any}
              onChange={handlePropertyChange}
              activeTab={activeTab}
              setActiveTab={setActiveTab}
            />
          )}
          {(element.type !== 'product_table' && element.type !== 'customer_info' && element.type !== 'company_info' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'document_type' && element.type !== 'dynamic-text' && element.type !== 'mentions' && element.type !== 'text' && element.type !== 'rectangle' && element.type !== 'circle' && element.type !== 'image' && element.type !== 'line') && (
            <ElementProperties
              element={element}
              onChange={handlePropertyChange}
            />
          )}
        </div>
      ))}
    </div>
  );
});
