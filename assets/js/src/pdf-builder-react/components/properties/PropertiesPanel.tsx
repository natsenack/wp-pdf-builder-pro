import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Element } from '../../types/elements';

interface PropertiesPanelProps {
  className?: string;
}

export function PropertiesPanel({ className }: PropertiesPanelProps) {
  const { state, updateElement, removeElement } = useBuilder();

  const selectedElements = state.elements.filter(el =>
    state.selection.selectedElements.includes(el.id)
  );

  const handlePropertyChange = (elementId: string, property: string, value: any) => {
    updateElement(elementId, { [property]: value });
  };

  const handleDeleteSelected = () => {
    state.selection.selectedElements.forEach(id => {
      removeElement(id);
    });
  };

  if (selectedElements.length === 0) {
    return (
      <div className={`pdf-builder-properties ${className || ''}`} style={{
        padding: '12px',
        backgroundColor: '#f9f9f9',
        border: '1px solid #ddd',
        borderRadius: '4px',
        minHeight: '200px'
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
      maxHeight: '600px',
      overflowY: 'auto'
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
        <h4 style={{ margin: '0', fontSize: '14px', fontWeight: 'bold' }}>
          Propriétés ({selectedElements.length})
        </h4>
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

      {selectedElements.map(element => (
        <div key={element.id} style={{
          marginBottom: '16px',
          padding: '12px',
          backgroundColor: '#ffffff',
          border: '1px solid #e0e0e0',
          borderRadius: '4px'
        }}>
          <h5 style={{ margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' }}>
            {element.type.charAt(0).toUpperCase() + element.type.slice(1)} - {element.id.slice(0, 8)}
          </h5>

          {/* Propriétés communes */}
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

            {/* Propriétés spécifiques selon le type */}
            {renderSpecificProperties(element, handlePropertyChange)}
          </div>
        </div>
      ))}
    </div>
  );
}

// Fonction pour rendre les propriétés spécifiques au type d'élément
function renderSpecificProperties(
  element: Element,
  onChange: (elementId: string, property: string, value: any) => void
) {
  switch (element.type) {
    case 'rectangle':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de remplissage
            </label>
            <input
              type="color"
              value={(element as any).fillColor || '#ffffff'}
              onChange={(e) => onChange(element.id, 'fillColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de bordure
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'circle':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de remplissage
            </label>
            <input
              type="color"
              value={(element as any).fillColor || '#ffffff'}
              onChange={(e) => onChange(element.id, 'fillColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de bordure
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'text':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Texte
            </label>
            <textarea
              value={(element as any).text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px',
                minHeight: '60px',
                resize: 'vertical'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur
            </label>
            <input
              type="color"
              value={(element as any).color || '#000000'}
              onChange={(e) => onChange(element.id, 'color', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Taille police
            </label>
            <input
              type="number"
              min="8"
              max="72"
              value={(element as any).fontSize || 16}
              onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 16)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'line':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur
            </label>
            <input
              type="number"
              min="1"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'product_table':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher les en-têtes
            </label>
            <input
              type="checkbox"
              checked={(element as any).showHeaders !== false}
              onChange={(e) => onChange(element.id, 'showHeaders', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche les noms des colonnes</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher les bordures
            </label>
            <input
              type="checkbox"
              checked={(element as any).showBorders !== false}
              onChange={(e) => onChange(element.id, 'showBorders', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche les bordures du tableau</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Lignes alternées
            </label>
            <input
              type="checkbox"
              checked={(element as any).showAlternatingRows !== false}
              onChange={(e) => onChange(element.id, 'showAlternatingRows', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Alterne les couleurs des lignes</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher les SKU
            </label>
            <input
              type="checkbox"
              checked={(element as any).showSku !== false}
              onChange={(e) => onChange(element.id, 'showSku', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Colonne des références produit</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher les descriptions
            </label>
            <input
              type="checkbox"
              checked={(element as any).showDescription !== false}
              onChange={(e) => onChange(element.id, 'showDescription', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Colonne des descriptions courtes</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher la quantité
            </label>
            <input
              type="checkbox"
              checked={(element as any).showQuantity !== false}
              onChange={(e) => onChange(element.id, 'showQuantity', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Colonne quantité des produits</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher les remises
            </label>
            <input
              type="checkbox"
              checked={(element as any).showDiscount !== false}
              onChange={(e) => onChange(element.id, 'showDiscount', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Colonne des remises appliquées</span>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Taille de police
            </label>
            <input
              type="number"
              min="8"
              max="24"
              value={(element as any).fontSize || 11}
              onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 11)}
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
              Couleur de fond
            </label>
            <input
              type="color"
              value={(element as any).backgroundColor || '#ffffff'}
              onChange={(e) => onChange(element.id, 'backgroundColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Fond des en-têtes
            </label>
            <input
              type="color"
              value={(element as any).headerBackgroundColor || '#f9fafb'}
              onChange={(e) => onChange(element.id, 'headerBackgroundColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur lignes alternées
            </label>
            <input
              type="color"
              value={(element as any).alternateRowColor || '#f9fafb'}
              onChange={(e) => onChange(element.id, 'alternateRowColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur des bordures
            </label>
            <input
              type="color"
              value={(element as any).borderColor || '#d1d5db'}
              onChange={(e) => onChange(element.id, 'borderColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur des bordures
            </label>
            <input
              type="number"
              min="0"
              max="5"
              step="0.5"
              value={(element as any).borderWidth || 1}
              onChange={(e) => onChange(element.id, 'borderWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <h6 style={{ margin: '0 0 8px 0', fontSize: '12px', fontWeight: 'bold', color: '#333' }}>
              � Éléments du tableau
            </h6>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher frais de commande
            </label>
            <input
              type="checkbox"
              checked={(element as any).showOrderFees !== false}
              onChange={(e) => onChange(element.id, 'showOrderFees', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Inclure les frais de commande dans le tableau</span>
            {(element as any).showOrderFees !== false && (
              <div style={{ marginTop: '8px' }}>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={(element as any).orderFees || 0}
                  onChange={(e) => onChange(element.id, 'orderFees', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                  placeholder="Montant (€)"
                />
              </div>
            )}
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher frais de port
            </label>
            <input
              type="checkbox"
              checked={(element as any).showShipping !== false}
              onChange={(e) => onChange(element.id, 'showShipping', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Inclure les frais de port dans les totaux</span>
            {(element as any).showShipping !== false && (
              <div style={{ marginTop: '8px' }}>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={(element as any).shippingCost || 0}
                  onChange={(e) => onChange(element.id, 'shippingCost', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                  placeholder="Montant (€)"
                />
              </div>
            )}
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher TVA
            </label>
            <input
              type="checkbox"
              checked={(element as any).showTax !== false}
              onChange={(e) => onChange(element.id, 'showTax', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Inclure la TVA dans les totaux</span>
            {(element as any).showTax !== false && (
              <div style={{ marginTop: '8px' }}>
                <input
                  type="number"
                  min="0"
                  max="100"
                  step="0.1"
                  value={(element as any).taxRate || 0}
                  onChange={(e) => onChange(element.id, 'taxRate', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                  placeholder="Taux (%)"
                />
              </div>
            )}
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Afficher remise globale
            </label>
            <input
              type="checkbox"
              checked={(element as any).showGlobalDiscount !== false}
              onChange={(e) => onChange(element.id, 'showGlobalDiscount', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Inclure une remise globale dans les totaux</span>
            {(element as any).showGlobalDiscount !== false && (
              <div style={{ marginTop: '8px' }}>
                <input
                  type="number"
                  min="0"
                  max="100"
                  step="0.1"
                  value={(element as any).globalDiscount || 0}
                  onChange={(e) => onChange(element.id, 'globalDiscount', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                  placeholder="Pourcentage (%)"
                />
              </div>
            )}
          </div>
        </>
      );

    default:
      return null;
  }
}