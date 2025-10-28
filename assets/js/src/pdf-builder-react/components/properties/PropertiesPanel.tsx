import React, { useState } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Element } from '../../types/elements';

interface PropertiesPanelProps {
  className?: string;
}

export function PropertiesPanel({ className }: PropertiesPanelProps) {
  const { state, updateElement, removeElement } = useBuilder();
  const [activeTab, setActiveTab] = useState<{ [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }>({});

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

          {/* Propriétés communes - masquées pour product_table qui a ses propres onglets */}
          {element.type !== 'product_table' && (
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

            {/* Propriétés spécifiques selon le type */}
            {renderSpecificProperties(element, handlePropertyChange, activeTab, setActiveTab)}
        </div>
      ))}
    </div>
  );
}

// Fonction pour rendre les propriétés spécifiques au type d'élément
function renderSpecificProperties(
  element: Element,
  onChange: (elementId: string, property: string, value: any) => void,
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' },
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void
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
      const currentTab = activeTab[element.id] || 'fonctionnalites';
      const setCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
        setActiveTab({ ...activeTab, [element.id]: tab });
      };
      
      return (
        <>
          {/* Système d'onglets pour Product Table */}
          <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
            <button
              onClick={() => setCurrentTab('fonctionnalites')}
              style={{
                flex: '1 1 30%',
                padding: '8px 6px',
                backgroundColor: currentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
                color: currentTab === 'fonctionnalites' ? '#fff' : '#333',
                border: 'none',
                cursor: 'pointer',
                fontSize: '11px',
                fontWeight: 'bold',
                borderRadius: '3px 3px 0 0',
                minWidth: '0',
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis'
              }}
              title="Fonctionnalités"
            >
              Fonctionnalités
            </button>
            <button
              onClick={() => setCurrentTab('personnalisation')}
              style={{
                flex: '1 1 30%',
                padding: '8px 6px',
                backgroundColor: currentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
                color: currentTab === 'personnalisation' ? '#fff' : '#333',
                border: 'none',
                cursor: 'pointer',
                fontSize: '11px',
                fontWeight: 'bold',
                borderRadius: '3px 3px 0 0',
                minWidth: '0',
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis'
              }}
              title="Personnalisation"
            >
              Personnalisation
            </button>
            <button
              onClick={() => setCurrentTab('positionnement')}
              style={{
                flex: '1 1 30%',
                padding: '8px 6px',
                backgroundColor: currentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
                color: currentTab === 'positionnement' ? '#fff' : '#333',
                border: 'none',
                cursor: 'pointer',
                fontSize: '11px',
                fontWeight: 'bold',
                borderRadius: '3px 3px 0 0',
                minWidth: '0',
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis'
              }}
              title="Positionnement"
            >
              Positionnement
            </button>
          </div>

          {/* Onglet Fonctionnalités */}
          {currentTab === 'fonctionnalites' && (
            <>
              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

              <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Afficher les frais de port
                </label>
                <input
                  type="checkbox"
                  checked={(element as any).showShipping !== false}
                  onChange={(e) => onChange(element.id, 'showShipping', e.target.checked)}
                  style={{ marginRight: '8px' }}
                />
                <span style={{ fontSize: '11px', color: '#666' }}>Affiche les frais de livraison</span>
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Afficher la TVA
                </label>
                <input
                  type="checkbox"
                  checked={(element as any).showTax !== false}
                  onChange={(e) => onChange(element.id, 'showTax', e.target.checked)}
                  style={{ marginRight: '8px' }}
                />
                <span style={{ fontSize: '11px', color: '#666' }}>Affiche les taxes sur le total</span>
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Afficher la remise globale
                </label>
                <input
                  type="checkbox"
                  checked={(element as any).showGlobalDiscount !== false}
                  onChange={(e) => onChange(element.id, 'showGlobalDiscount', e.target.checked)}
                  style={{ marginRight: '8px' }}
                />
                <span style={{ fontSize: '11px', color: '#666' }}>Affiche la remise globale appliquée</span>
              </div>
            </>
          )}

          {/* Onglet Personnalisation */}
          {currentTab === 'personnalisation' && (
            <>
              {/* Section Thèmes avec aperçus */}
              <div style={{ marginBottom: '16px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
                  Thèmes prédéfinis
                </label>
                <div style={{ display: 'grid', gap: '8px' }}>
                  {[
                    {
                      id: 'classic',
                      name: 'Classique',
                      preview: (
                        <div style={{
                          width: '100%',
                          height: '40px',
                          border: '1px solid #e5e7eb',
                          borderRadius: '3px',
                          backgroundColor: '#ffffff',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: '10px',
                          color: '#6b7280'
                        }}>
                          <div style={{
                            width: '80%',
                            height: '4px',
                            backgroundColor: '#f9fafb',
                            marginBottom: '2px',
                            borderRadius: '1px'
                          }}></div>
                          <div style={{
                            width: '60%',
                            height: '4px',
                            backgroundColor: '#ffffff',
                            borderRadius: '1px'
                          }}></div>
                        </div>
                      ),
                      styles: {
                        backgroundColor: '#ffffff',
                        headerBackgroundColor: '#f9fafb',
                        alternateRowColor: '#f9fafb',
                        borderColor: '#e5e7eb',
                        textColor: '#111827',
                        headerTextColor: '#374151'
                      }
                    },
                    {
                      id: 'modern',
                      name: 'Moderne',
                      preview: (
                        <div style={{
                          width: '100%',
                          height: '40px',
                          border: '1px solid #d1d5db',
                          borderRadius: '6px',
                          backgroundColor: '#f8fafc',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: '10px',
                          color: '#475569'
                        }}>
                          <div style={{
                            width: '80%',
                            height: '4px',
                            backgroundColor: '#3b82f6',
                            marginBottom: '2px',
                            borderRadius: '2px'
                          }}></div>
                          <div style={{
                            width: '60%',
                            height: '4px',
                            backgroundColor: '#ffffff',
                            borderRadius: '2px'
                          }}></div>
                        </div>
                      ),
                      styles: {
                        backgroundColor: '#f8fafc',
                        headerBackgroundColor: '#3b82f6',
                        headerTextColor: '#ffffff',
                        alternateRowColor: '#f1f5f9',
                        borderColor: '#cbd5e1',
                        textColor: '#334155'
                      }
                    },
                    {
                      id: 'elegant',
                      name: 'Élégant',
                      preview: (
                        <div style={{
                          width: '100%',
                          height: '40px',
                          border: '2px solid #8b5cf6',
                          borderRadius: '8px',
                          backgroundColor: '#fefefe',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: '10px',
                          color: '#7c3aed'
                        }}>
                          <div style={{
                            width: '80%',
                            height: '4px',
                            backgroundColor: '#8b5cf6',
                            marginBottom: '2px',
                            borderRadius: '3px'
                          }}></div>
                          <div style={{
                            width: '60%',
                            height: '4px',
                            backgroundColor: '#faf5ff',
                            borderRadius: '3px'
                          }}></div>
                        </div>
                      ),
                      styles: {
                        backgroundColor: '#fefefe',
                        headerBackgroundColor: '#8b5cf6',
                        headerTextColor: '#ffffff',
                        alternateRowColor: '#faf5ff',
                        borderColor: '#c4b5fd',
                        textColor: '#581c87'
                      }
                    },
                    {
                      id: 'minimal',
                      name: 'Minimal',
                      preview: (
                        <div style={{
                          width: '100%',
                          height: '40px',
                          border: '1px solid #f3f4f6',
                          borderRadius: '0px',
                          backgroundColor: '#ffffff',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: '10px',
                          color: '#9ca3af'
                        }}>
                          <div style={{
                            width: '80%',
                            height: '2px',
                            backgroundColor: '#f9fafb',
                            marginBottom: '1px'
                          }}></div>
                          <div style={{
                            width: '60%',
                            height: '2px',
                            backgroundColor: '#ffffff'
                          }}></div>
                        </div>
                      ),
                      styles: {
                        backgroundColor: '#ffffff',
                        headerBackgroundColor: '#f9fafb',
                        alternateRowColor: '#f9fafb',
                        borderColor: '#f3f4f6',
                        textColor: '#374151',
                        headerTextColor: '#111827'
                      }
                    },
                    {
                      id: 'corporate',
                      name: 'Corporate',
                      preview: (
                        <div style={{
                          width: '100%',
                          height: '40px',
                          border: '1px solid #374151',
                          borderRadius: '0px',
                          backgroundColor: '#ffffff',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: '10px',
                          color: '#374151'
                        }}>
                          <div style={{
                            width: '80%',
                            height: '4px',
                            backgroundColor: '#1f2937',
                            marginBottom: '2px'
                          }}></div>
                          <div style={{
                            width: '60%',
                            height: '4px',
                            backgroundColor: '#f9fafb'
                          }}></div>
                        </div>
                      ),
                      styles: {
                        backgroundColor: '#ffffff',
                        headerBackgroundColor: '#1f2937',
                        headerTextColor: '#ffffff',
                        alternateRowColor: '#f9fafb',
                        borderColor: '#374151',
                        textColor: '#111827'
                      }
                    }
                  ].map(theme => (
                    <button
                      key={theme.id}
                      onClick={() => {
                        // Appliquer toutes les propriétés du thème
                        Object.entries(theme.styles).forEach(([property, value]) => {
                          onChange(element.id, property, value);
                        });
                      }}
                      style={{
                        padding: '8px',
                        border: '2px solid #e0e0e0',
                        borderRadius: '6px',
                        backgroundColor: '#ffffff',
                        cursor: 'pointer',
                        textAlign: 'left',
                        transition: 'all 0.2s ease'
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.borderColor = '#007bff';
                        e.currentTarget.style.backgroundColor = '#f8f9fa';
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.borderColor = '#e0e0e0';
                        e.currentTarget.style.backgroundColor = '#ffffff';
                      }}
                    >
                      <div style={{ fontSize: '11px', fontWeight: 'bold', marginBottom: '4px', color: '#333' }}>
                        {theme.name}
                      </div>
                      {theme.preview}
                    </button>
                  ))}
                </div>
              </div>

              <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

              <div style={{ marginBottom: '12px' }}>
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

              <div style={{ marginBottom: '12px' }}>
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

              <div style={{ marginBottom: '12px' }}>
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

              <div style={{ marginBottom: '12px' }}>
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

              <div style={{ marginBottom: '12px' }}>
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

              <div style={{ marginBottom: '12px' }}>
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
            </>
          )}

          {/* Onglet Positionnement */}
          {currentTab === 'positionnement' && (
            <>
              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Position X
                </label>
                <input
                  type="number"
                  value={element.x}
                  onChange={(e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                />
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Position Y
                </label>
                <input
                  type="number"
                  value={element.y}
                  onChange={(e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                />
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Largeur
                </label>
                <input
                  type="number"
                  value={element.width}
                  onChange={(e) => onChange(element.id, 'width', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                />
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Hauteur
                </label>
                <input
                  type="number"
                  value={element.height}
                  onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                />
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Rotation (°)
                </label>
                <input
                  type="number"
                  value={element.rotation || 0}
                  onChange={(e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                />
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Opacité
                </label>
                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.1"
                  value={element.opacity || 1}
                  onChange={(e) => onChange(element.id, 'opacity', parseFloat(e.target.value))}
                  style={{ width: '100%' }}
                />
                <span style={{ fontSize: '11px', color: '#666' }}>
                  {Math.round((element.opacity || 1) * 100)}%
                </span>
              </div>
            </>
          )}
        </>
      );

    default:
      return null;
  }
}
