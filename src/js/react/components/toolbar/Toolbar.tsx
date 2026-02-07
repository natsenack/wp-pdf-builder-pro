import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext';
import { BuilderMode } from '../../types/elements';

interface ToolbarProps {
  className?: string;
}

export function Toolbar({ className }: ToolbarProps) {
  console.log('🔧 [TOOLBAR] Composant Toolbar rendu');

  const { state, dispatch, setMode, undo, redo, reset, toggleGrid, toggleGuides, setCanvas, zoomIn, zoomOut, resetZoom } = useBuilder();
  const canvasSettings = useCanvasSettings();

  // Vérifications de sécurité
  if (!state) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: État Builder non disponible
    </div>;
  }

  if (!state.history) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: Historique non disponible
    </div>;
  }

  if (!state.canvas) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: Canvas non disponible
    </div>;
  }

  const tools: { mode: BuilderMode; label: string; icon: string }[] = [
    { mode: 'select', label: 'Sélection', icon: '🖱️' },
    { mode: 'rectangle', label: 'Rectangle', icon: '▭' },
    { mode: 'circle', label: 'Cercle', icon: '○' },
    { mode: 'text', label: 'Texte', icon: 'T' },
    { mode: 'line', label: 'Ligne', icon: '━' },
    { mode: 'image', label: 'Image', icon: '🖼️' },
  ];

  const handleModeChange = (mode: BuilderMode) => {
    if (setMode) {
      setMode(mode);
    }
  };

  const handleUndo = () => {
    if (undo) {
      undo();
    }
  };

  const handleRedo = () => {
    if (redo) {
      redo();
    }
  };

  const handleReset = () => {
    if (reset) {
      reset();
    }
  };

  const handleToggleGrid = () => {
    if (toggleGrid && canvasSettings.gridShow) {
      toggleGrid();
    }
  };

  const handleToggleGuides = () => {
    if (toggleGuides && canvasSettings.guidesEnabled) {
      toggleGuides();
    }
  };

  const handleHTMLPreview = () => {
    console.log('🌐 [HTML PREVIEW FUNCTION] handleHTMLPreview appelée !');
    console.log('🌐🔍 [HTML PREVIEW] Début de handleHTMLPreview côté client');

    try {
      // Générer l'HTML directement côté client
      const html = generateHTMLFromElements(state.elements, state.canvas);
      console.log('🌐✅ [HTML PREVIEW] HTML généré côté client, longueur:', html.length);

      // Stocker le contenu HTML et ouvrir le modal
      dispatch({ type: 'SET_HTML_PREVIEW_CONTENT', payload: html });
      dispatch({ type: 'SET_SHOW_PREVIEW_MODAL', payload: true });
      console.log('🌐✅ [HTML PREVIEW] Contenu HTML stocké et modal ouvert');

    } catch (error) {
      console.error('🌐❌ [HTML PREVIEW] Erreur lors de la génération côté client:', error);
      alert('Erreur lors de la génération de l\'aperçu HTML: ' + error.message);
    }
  };

  // Fonction pour générer l'HTML côté client
  const generateHTMLFromElements = (elements: any[], canvas: any) => {
    console.log('🌐🔧 [HTML GENERATION] Génération HTML pour', elements.length, 'éléments');

    // Paramètres par défaut (similaires au PHP)
    const margins = { top: 28, bottom: 28, left: 20, right: 20 };
    const colors = { primary: '#007cba', secondary: '#666666', text: '#333333' };
    const fonts = { family: 'Arial', size: 12 };

    const canvasWidth = canvas.width || 794;
    const canvasHeight = canvas.height || 1123;

    // Extraire les éléments par type
    const logoElement = elements.find(el => el.type === 'company_logo');
    const docTypeElement = elements.find(el => el.type === 'document_type');
    const companyInfoElement = elements.find(el => el.type === 'company_info');
    const customerInfoElement = elements.find(el => el.type === 'customer_info');
    const orderNumberElement = elements.find(el => el.type === 'order_number');
    const orderDateElement = elements.find(el => el.type === 'woocommerce_order_date');
    const productTableElement = elements.find(el => el.type === 'product_table');
    const dynamicTextElements = elements.filter(el => el.type === 'dynamic_text');
    const mentionsElement = elements.find(el => el.type === 'mentions');

    let html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .pdf-container {
            width: ${canvasWidth}px;
            min-height: ${canvasHeight}px;
            margin: 20px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: ${margins.top}px ${margins.right}px ${margins.bottom}px ${margins.left}px;
            font-family: ${fonts.family}, sans-serif;
            font-size: ${fonts.size}px;
            color: ${colors.text};
            position: relative;
        }
        .pdf-content {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            min-height: ${canvasHeight - margins.top - margins.bottom}px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 15px;
            align-items: flex-start;
        }
        .header-col {
            flex: 1;
        }
        .logo-container {
            text-align: center;
            max-width: 200px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            border: 1px solid #e0e0e0;
            background-color: #fafafa;
        }
        .logo-container img {
            max-width: 100%;
            max-height: 70px;
            width: auto;
            height: auto;
        }
        .document-type-title {
            font-size: 32px;
            font-weight: bold;
            color: ${colors.primary};
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .separator-line {
            border-top: 2px solid ${colors.primary};
            margin: 15px 0 20px 0;
        }
        .two-col {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }
        .two-col > div {
            flex: 1;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 11px;
            line-height: 1.5;
        }
        .info-box-title {
            font-weight: bold;
            color: ${colors.primary};
            margin-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        .info-item {
            color: ${colors.text};
            margin-bottom: 4px;
        }
        .order-info {
            text-align: right;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .order-number-label {
            font-weight: bold;
            color: ${colors.primary};
            font-size: 14px;
        }
        .order-date {
            font-size: 11px;
            color: ${colors.secondary};
            margin-top: 4px;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        .product-table th {
            background-color: ${colors.primary};
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        .product-table td {
            padding: 8px 10px;
            border: 1px solid #e0e0e0;
            color: ${colors.text};
        }
        .product-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .signature-section {
            margin-top: 30px;
            font-size: 11px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .signature-text {
            white-space: pre-wrap;
            color: ${colors.text};
            line-height: 1.6;
        }
        .mentions-line {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: ${colors.secondary};
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <div class="pdf-content">`;

    // En-tête avec logo et titre
    html += '<div class="header-row">';

    // Colonne gauche: infos entreprise
    html += '<div class="header-col">';
    if (companyInfoElement) {
        html += `<div class="info-box">
            <div class="info-box-title">Infos Entreprise</div>
            <div class="info-item">[Company Name]</div>
            <div class="info-item">[Company Address]</div>
            <div class="info-item">Email: [Company Email]</div>
            <div class="info-item">Tél: [Company Phone]</div>
            <div class="info-item">SIRET: [SIRET]</div>
            <div class="info-item">TVA: [VAT]</div>
        </div>`;
    }
    html += '</div>';

    // Colonne milieu: logo
    html += '<div class="header-col" style="text-align: center;">';
    if (logoElement && logoElement.src) {
        html += `<div class="logo-container">
            <img src="${logoElement.src}" alt="Logo">
        </div>`;
    } else {
        html += '<div style="color: #999; font-size: 10px;">Logo</div>';
    }
    html += '</div>';

    // Colonne droite: titre document + commande
    html += '<div class="header-col">';
    if (docTypeElement) {
        html += `<div class="document-type-title">${(docTypeElement.title || 'DOCUMENT')}</div>`;
    }
    if (orderNumberElement) {
        html += `<div class="order-info">
            <div class="order-number-label">Commande: [Order #]</div>`;
        if (orderDateElement) {
            html += `<div class="order-date">Date: ${new Date().toLocaleDateString('fr-FR')}</div>`;
        }
        html += '</div>';
    }
    html += '</div>';

    html += '</div>';

    // Ligne séparatrice
    html += '<div class="separator-line"></div>';

    // Infos client
    html += '<div class="two-col">';
    if (customerInfoElement) {
        html += `<div class="info-box">
            <div class="info-box-title">Informations Client</div>
            <div class="info-item">Nom: [Customer Name]</div>
            <div class="info-item">Adresse: [Customer Address]</div>
            <div class="info-item">Email: [Customer Email]</div>
            <div class="info-item">Téléphone: [Customer Phone]</div>
        </div>`;
    }
    html += '</div>';

    // Table des produits
    if (productTableElement) {
        html += `<table class="product-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th width="15%">Qty</th>
                    <th width="20%">Prix Unit.</th>
                    <th width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Produit exemple</td>
                    <td>1</td>
                    <td>100.00 €</td>
                    <td>100.00 €</td>
                </tr>
            </tbody>
        </table>`;
    }

    // Signature/Dynamic Text
    dynamicTextElements.forEach(element => {
        const textContent = element.text || element.content || element.value || 'Texte dynamique non défini';
        html += `<div class="signature-section">
            <div class="signature-text">${textContent.replace(/\n/g, '<br>')}</div>
        </div>`;
    });

    // Mentions
    if (mentionsElement) {
        html += `<div class="mentions-line">
            Email • Téléphone • SIRET • TVA
        </div>`;
    }

    html += `
        </div>
    </div>
</body>
</html>`;

    console.log('🌐✅ [HTML GENERATION] HTML généré avec succès');
    return html;
  };

  const handleToggleSnapToGrid = () => {
    // Vérifier que la grille globale est activée avant d'autoriser l'accrochage
    if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled) {
      const newSnapToGrid = !state.canvas.snapToGrid;
      if (setCanvas) {
        setCanvas({ snapToGrid: newSnapToGrid });
      }
    }
  };

  return (
    <div
      className={`pdf-builder-toolbar ${className || ''}`}
    >
      <div style={{
        display: 'flex',
        flexDirection: 'column',
        gap: '12px',
        padding: '16px',
        backgroundColor: '#ffffff',
        border: '1px solid #e1e5e9',
        borderRadius: '8px',
        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
        maxHeight: '140px',
        width: '100%'
      }}>
        {/* Première ligne : Outils + Actions principales + Informations */}
        <div style={{
          display: 'flex',
          gap: '16px',
          alignItems: 'flex-start',
          flexDirection: 'row',
          minWidth: '220px'
        }}>
          {/* Outils de création */}
          <section style={{
            display: 'flex',
            flexDirection: 'column',
            gap: '8px',
            minWidth: '220px',
            flex: 'none'
          }}>
            <div style={{
              fontSize: '13px',
              fontWeight: '600',
              color: '#374151',
              textTransform: 'uppercase',
              letterSpacing: '0.5px',
              borderLeft: '3px solid #3b82f6',
              paddingLeft: '8px',
              display: 'block'
            }}>
              Outils
            </div>
            <div style={{
              display: 'flex',
            flexWrap: 'wrap',
            gap: '6px',
            maxHeight: '80px',
            alignContent: 'flex-start'
          }}>
            {tools.map(tool => (
              <button
                key={tool.mode}
                onClick={() => handleModeChange(tool.mode)}
                style={{
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '6px',
                  backgroundColor: state.mode === tool.mode ? '#3b82f6' : '#ffffff',
                  color: state.mode === tool.mode ? '#ffffff' : '#374151',
                  cursor: 'pointer',
                  fontSize: '13px',
                  fontWeight: '500',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  transition: 'all 0.2s ease',
                  boxShadow: state.mode === tool.mode ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                  minWidth: '90px',
                  justifyContent: 'center'
                }}
                onMouseEnter={(e) => {
                  if (state.mode !== tool.mode) {
                    e.currentTarget.style.backgroundColor = '#f8fafc';
                    e.currentTarget.style.borderColor = '#9ca3af';
                  }
                }}
                onMouseLeave={(e) => {
                  if (state.mode !== tool.mode) {
                    e.currentTarget.style.backgroundColor = '#ffffff';
                    e.currentTarget.style.borderColor = '#d1d5db';
                  }
                }}
              >
                <span style={{ fontSize: '14px' }}>{tool.icon}</span>
                <span>{tool.label}</span>
              </button>
            ))}
          </div>
        </section>

        {/* Actions principales */}
        <section style={{ display: 'flex', flexDirection: 'column', gap: '8px', flex: 1 }}>
          <div style={{
            fontSize: '13px',
            fontWeight: '600',
            color: '#374151',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            borderLeft: '3px solid #10b981',
            paddingLeft: '8px'
          }}>
            Actions
          </div>
          <div style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '6px',
            maxHeight: '80px',
            alignContent: 'flex-start'
          }}>
            {/* Historique */}
            <button
              onClick={handleUndo}
              disabled={!state.history.canUndo}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.history.canUndo ? '#ffffff' : '#f9fafb',
                color: state.history.canUndo ? '#374151' : '#9ca3af',
                cursor: state.history.canUndo ? 'pointer' : 'not-allowed',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (state.history.canUndo) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (state.history.canUndo) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              ↶ Annuler
            </button>
            <button
              onClick={handleRedo}
              disabled={!state.history.canRedo}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.history.canRedo ? '#ffffff' : '#f9fafb',
                color: state.history.canRedo ? '#374151' : '#9ca3af',
                cursor: state.history.canRedo ? 'pointer' : 'not-allowed',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (state.history.canRedo) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (state.history.canRedo) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              ↷ Rétablir
            </button>

            {/* Grille */}
            <button
              onClick={handleToggleGrid}
              disabled={!canvasSettings.gridShow}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: !canvasSettings.gridShow ? '#f9fafb' : (state.canvas.showGrid ? '#3b82f6' : '#ffffff'),
                color: !canvasSettings.gridShow ? '#9ca3af' : (state.canvas.showGrid ? '#ffffff' : '#374151'),
                cursor: !canvasSettings.gridShow ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.showGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: !canvasSettings.gridShow ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.gridShow && !state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.gridShow && !state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              {state.canvas.showGrid ? '⬜ Grille' : '▦ Grille'}
            </button>
            <button
              onClick={handleToggleSnapToGrid}
              disabled={!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#f9fafb' : (state.canvas.snapToGrid ? '#3b82f6' : '#ffffff'),
                color: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#9ca3af' : (state.canvas.snapToGrid ? '#ffffff' : '#374151'),
                cursor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.snapToGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              🧲 Snap
            </button>
            <button
              onClick={handleToggleGuides}
              disabled={!canvasSettings.guidesEnabled}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: !canvasSettings.guidesEnabled ? '#f9fafb' : (state.template.showGuides ? '#3b82f6' : '#ffffff'),
                color: !canvasSettings.guidesEnabled ? '#9ca3af' : (state.template.showGuides ? '#ffffff' : '#374151'),
                cursor: !canvasSettings.guidesEnabled ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.template.showGuides ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: !canvasSettings.guidesEnabled ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              {state.template.showGuides ? '📏 Guides' : '📐 Guides'}
            </button>

            {/* Aperçu HTML */}
            {(() => {
              console.log('🌐 [TOOLBAR] Rendu du bouton HTML preview');
              return (
                <button
                  onClick={() => {
                    console.log('🚀 [HTML PREVIEW BUTTON] Bouton HTML cliqué !');
                    handleHTMLPreview();
                  }}
                  style={{
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    backgroundColor: '#ffffff',
                    color: '#374151',
                    cursor: 'pointer',
                    fontSize: '13px',
                    fontWeight: '500',
                    transition: 'all 0.2s ease',
                    minWidth: '90px'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.backgroundColor = '#f8fafc';
                    e.currentTarget.style.borderColor = '#9ca3af';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = '#ffffff';
                    e.currentTarget.style.borderColor = '#d1d5db';
                  }}
                  title="Générer un aperçu HTML du template"
                >
                  🌐 HTML
                </button>
              );
            })()}

            {/* Zoom - Toujours affiché */}
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: '4px',
              padding: '6px 10px',
              backgroundColor: '#f8fafc',
              borderRadius: '6px',
              border: '1px solid #e2e8f0'
            }}>
              <span style={{ fontSize: '12px', color: '#64748b', fontWeight: '500' }}>🔍</span>
              <button
                onClick={() => {
                  // Zoom out
                  if (zoomOut) {
                    zoomOut();
                  }
                }}
                style={{
                  padding: '2px 6px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '12px',
                  fontWeight: '600',
                  minWidth: '24px'
                }}
                title="Zoom arrière"
              >
                ➖
              </button>
              <span style={{
                fontSize: '12px',
                fontWeight: '600',
                color: '#374151',
                minWidth: '40px',
                textAlign: 'center'
              }}>
                {state.canvas.zoom}%
              </span>
              <button
                onClick={() => {
                  // Zoom in
                  if (zoomIn) {
                    zoomIn();
                  }
                }}
                style={{
                  padding: '2px 6px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '12px',
                  fontWeight: '600',
                  minWidth: '24px'
                }}
                title="Zoom avant"
              >
                ➕
              </button>
              <span style={{ fontSize: '10px', color: '#94a3b8', margin: '0 2px' }}>|</span>
              <button
                onClick={() => {
                  // Fit to screen (reset to default zoom)
                  if (resetZoom) {
                    resetZoom();
                  }
                }}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '11px',
                  fontWeight: '500'
                }}
                title="Adapter à l'écran"
              >
                🔄
              </button>
            </div>
          </div>
        </section>

        {/* Informations - intégrées dans la première ligne */}
        <section style={{ display: 'flex', flexDirection: 'column', gap: '6px', minWidth: '160px', marginLeft: 'auto' }}>
          <div style={{
            fontSize: '13px',
            fontWeight: '600',
            color: '#374151',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            borderLeft: '3px solid #f59e0b',
            paddingLeft: '8px'
          }}>
            Infos
          </div>
          <div style={{
            fontSize: '12px',
            color: '#6b7280',
            display: 'flex',
            flexDirection: 'column',
            gap: '2px',
            backgroundColor: '#f9fafb',
            padding: '6px',
            borderRadius: '6px',
            border: '1px solid #e5e7eb'
          }}>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Éléments:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.elements.length}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Sélection:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.selection.selectedElements.length}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Mode:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.mode}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Zoom:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.canvas.zoom}%</span>
            </div>
          </div>
        </section>
      </div>
    </div>
    </div>
  );
}



