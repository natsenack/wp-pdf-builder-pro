import React from 'react';
import { Element } from '../../types/elements';

interface ProductTablePropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function ProductTableProperties({ element, onChange, activeTab, setActiveTab }: ProductTablePropertiesProps) {
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
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
              gap: '8px',
              maxHeight: '200px',
              overflowY: 'auto',
              padding: '4px',
              border: '1px solid #e0e0e0',
              borderRadius: '4px',
              backgroundColor: '#fafafa'
            }}>
              {[
                {
                  id: 'classic',
                  name: 'Classique',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #e5e7eb',
                      borderRadius: '2px',
                      backgroundColor: '#ffffff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#f9fafb',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
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
                      height: '35px',
                      border: '1px solid #cbd5e1',
                      borderRadius: '4px',
                      backgroundColor: '#f8fafc',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#3b82f6',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#ffffff',
                        borderRadius: '1px'
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
                      height: '35px',
                      border: '1px solid #c4b5fd',
                      borderRadius: '6px',
                      backgroundColor: '#fefefe',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#8b5cf6',
                        borderRadius: '2px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#faf5ff',
                        borderRadius: '2px'
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
                      height: '35px',
                      border: '1px solid #f3f4f6',
                      borderRadius: '0px',
                      backgroundColor: '#ffffff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '1px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '2px',
                        backgroundColor: '#f9fafb'
                      }}></div>
                      <div style={{
                        width: '70%',
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
                      height: '35px',
                      border: '1px solid #374151',
                      borderRadius: '0px',
                      backgroundColor: '#ffffff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#1f2937'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
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
                },
                {
                  id: 'warm',
                  name: 'Chaud',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #fed7aa',
                      borderRadius: '4px',
                      backgroundColor: '#fff7ed',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#ea580c',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#ffedd5',
                        borderRadius: '1px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fff7ed',
                    headerBackgroundColor: '#ea580c',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#ffedd5',
                    borderColor: '#fed7aa',
                    textColor: '#9a3412'
                  }
                },
                {
                  id: 'nature',
                  name: 'Nature',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #bbf7d0',
                      borderRadius: '6px',
                      backgroundColor: '#f0fdf4',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#16a34a',
                        borderRadius: '2px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#dcfce7',
                        borderRadius: '2px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#f0fdf4',
                    headerBackgroundColor: '#16a34a',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#dcfce7',
                    borderColor: '#bbf7d0',
                    textColor: '#14532d'
                  }
                },
                {
                  id: 'dark',
                  name: 'Sombre',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #374151',
                      borderRadius: '4px',
                      backgroundColor: '#1f2937',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#111827',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#374151',
                        borderRadius: '1px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#1f2937',
                    headerBackgroundColor: '#111827',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#374151',
                    borderColor: '#4b5563',
                    textColor: '#f9fafb'
                  }
                },
                {
                  id: 'ocean',
                  name: 'Océan',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #0ea5e9',
                      borderRadius: '8px',
                      backgroundColor: '#f0f9ff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#0284c7',
                        borderRadius: '3px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#bae6fd',
                        borderRadius: '3px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#f0f9ff',
                    headerBackgroundColor: '#0284c7',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#bae6fd',
                    borderColor: '#0ea5e9',
                    textColor: '#0c4a6e'
                  }
                },
                {
                  id: 'sunset',
                  name: 'Coucher',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #f97316',
                      borderRadius: '12px',
                      backgroundColor: '#fff7ed',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#ea580c',
                        borderRadius: '4px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#fed7aa',
                        borderRadius: '4px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fff7ed',
                    headerBackgroundColor: '#ea580c',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#fed7aa',
                    borderColor: '#f97316',
                    textColor: '#9a3412'
                  }
                },
                {
                  id: 'forest',
                  name: 'Forêt',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #22c55e',
                      borderRadius: '6px',
                      backgroundColor: '#f0fdf4',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#16a34a',
                        borderRadius: '2px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#bbf7d0',
                        borderRadius: '2px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#f0fdf4',
                    headerBackgroundColor: '#16a34a',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#bbf7d0',
                    borderColor: '#22c55e',
                    textColor: '#14532d'
                  }
                },
                {
                  id: 'royal',
                  name: 'Royal',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #7c2d12',
                      borderRadius: '4px',
                      backgroundColor: '#fef2f2',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#991b1b',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#fecaca',
                        borderRadius: '1px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fef2f2',
                    headerBackgroundColor: '#991b1b',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#fecaca',
                    borderColor: '#7c2d12',
                    textColor: '#450a0a'
                  }
                },
                {
                  id: 'clean',
                  name: 'Propre',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #d1d5db',
                      borderRadius: '2px',
                      backgroundColor: '#ffffff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '1px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '2px',
                        backgroundColor: '#f3f4f6'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '2px',
                        backgroundColor: '#ffffff'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#ffffff',
                    headerBackgroundColor: '#f3f4f6',
                    alternateRowColor: '#f9fafb',
                    borderColor: '#d1d5db',
                    textColor: '#374151',
                    headerTextColor: '#111827'
                  }
                },
                {
                  id: 'tech',
                  name: 'Tech',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #6366f1',
                      borderRadius: '0px',
                      backgroundColor: '#f8fafc',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#4f46e5'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#e0e7ff'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#f8fafc',
                    headerBackgroundColor: '#4f46e5',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#e0e7ff',
                    borderColor: '#6366f1',
                    textColor: '#312e81'
                  }
                },
                {
                  id: 'vintage',
                  name: 'Vintage',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '2px solid #92400e',
                      borderRadius: '0px',
                      backgroundColor: '#fef3c7',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#b45309',
                        borderRadius: '0px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#fde68a',
                        borderRadius: '0px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fef3c7',
                    headerBackgroundColor: '#b45309',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#fde68a',
                    borderColor: '#92400e',
                    textColor: '#78350f'
                  }
                },
                {
                  id: 'berry',
                  name: 'Baies',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #be185d',
                      borderRadius: '10px',
                      backgroundColor: '#fdf2f8',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#db2777',
                        borderRadius: '5px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#fce7f3',
                        borderRadius: '5px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fdf2f8',
                    headerBackgroundColor: '#db2777',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#fce7f3',
                    borderColor: '#be185d',
                    textColor: '#831843'
                  }
                },
                {
                  id: 'mint',
                  name: 'Menthe',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #059669',
                      borderRadius: '8px',
                      backgroundColor: '#ecfdf5',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#047857',
                        borderRadius: '4px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#a7f3d0',
                        borderRadius: '4px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#ecfdf5',
                    headerBackgroundColor: '#047857',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#a7f3d0',
                    borderColor: '#059669',
                    textColor: '#064e3b'
                  }
                },
                {
                  id: 'lavender',
                  name: 'Lavande',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #7c3aed',
                      borderRadius: '12px',
                      backgroundColor: '#faf5ff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#6d28d9',
                        borderRadius: '6px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#e9d5ff',
                        borderRadius: '6px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#faf5ff',
                    headerBackgroundColor: '#6d28d9',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#e9d5ff',
                    borderColor: '#7c3aed',
                    textColor: '#581c87'
                  }
                },
                {
                  id: 'stone',
                  name: 'Pierre',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #6b7280',
                      borderRadius: '0px',
                      backgroundColor: '#f9fafb',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#4b5563'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#e5e7eb'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#f9fafb',
                    headerBackgroundColor: '#4b5563',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#e5e7eb',
                    borderColor: '#6b7280',
                    textColor: '#111827'
                  }
                },
                {
                  id: 'sunshine',
                  name: 'Soleil',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #f59e0b',
                      borderRadius: '16px',
                      backgroundColor: '#fffbeb',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '85%',
                        height: '3px',
                        backgroundColor: '#d97706',
                        borderRadius: '8px'
                      }}></div>
                      <div style={{
                        width: '70%',
                        height: '3px',
                        backgroundColor: '#fef3c7',
                        borderRadius: '8px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fffbeb',
                    headerBackgroundColor: '#d97706',
                    headerTextColor: '#ffffff',
                    alternateRowColor: '#fef3c7',
                    borderColor: '#f59e0b',
                    textColor: '#92400e'
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
                    padding: '6px',
                    border: '2px solid transparent',
                    borderRadius: '6px',
                    backgroundColor: '#ffffff',
                    cursor: 'pointer',
                    textAlign: 'center',
                    transition: 'all 0.2s ease',
                    minHeight: '70px',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '4px'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.borderColor = '#007bff';
                    e.currentTarget.style.backgroundColor = '#f8f9fa';
                    e.currentTarget.style.transform = 'translateY(-1px)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.borderColor = 'transparent';
                    e.currentTarget.style.backgroundColor = '#ffffff';
                    e.currentTarget.style.transform = 'translateY(0)';
                  }}
                  title={`Appliquer le thème ${theme.name}`}
                >
                  <div style={{
                    fontSize: '10px',
                    fontWeight: 'bold',
                    color: '#333',
                    textAlign: 'center',
                    lineHeight: '1.2'
                  }}>
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
              Famille de police
            </label>
            <select
              value={(element as any).fontFamily || 'Arial'}
              onChange={(e) => onChange(element.id, 'fontFamily', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="Arial">Arial</option>
              <option value="Helvetica">Helvetica</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Georgia">Georgia</option>
              <option value="Verdana">Verdana</option>
              <option value="Tahoma">Tahoma</option>
              <option value="Trebuchet MS">Trebuchet MS</option>
              <option value="Calibri">Calibri</option>
              <option value="Cambria">Cambria</option>
              <option value="Segoe UI">Segoe UI</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur de police
            </label>
            <select
              value={(element as any).fontWeight || 'normal'}
              onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal (400)</option>
              <option value="bold">Gras (700)</option>
              <option value="lighter">Fin (300)</option>
              <option value="bolder">Très gras (900)</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Style de police
            </label>
            <select
              value={(element as any).fontStyle || 'normal'}
              onChange={(e) => onChange(element.id, 'fontStyle', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal</option>
              <option value="italic">Italique</option>
              <option value="oblique">Oblique</option>
            </select>
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
              value={(element as any).borderColor || '#e5e7eb'}
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
              Couleur du texte
            </label>
            <input
              type="color"
              value={(element as any).textColor || '#111827'}
              onChange={(e) => onChange(element.id, 'textColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
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
              onChange={(e) => onChange(element.id, 'x', parseInt(e.target.value) || 0)}
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
              onChange={(e) => onChange(element.id, 'y', parseInt(e.target.value) || 0)}
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
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 100)}
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
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 100)}
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
              Alignement horizontal
            </label>
            <select
              value={(element as any).textAlign || 'left'}
              onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="left">Gauche</option>
              <option value="center">Centre</option>
              <option value="right">Droite</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement vertical
            </label>
            <select
              value={(element as any).verticalAlign || 'top'}
              onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="top">Haut</option>
              <option value="middle">Milieu</option>
              <option value="bottom">Bas</option>
            </select>
          </div>
        </>
      )}
    </>
  );
}