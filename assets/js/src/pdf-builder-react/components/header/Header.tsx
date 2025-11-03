import React, { useState, useEffect, useCallback, memo, useDeferredValue } from 'react';
import { TemplateState } from '../../types/elements';
import { useBuilder } from '../../contexts/builder/BuilderContext';
import { debugLog } from '../../utils/debug';

// Extension de Window pour l'API Preview
declare global {
  interface Window {
    pdfPreviewAPI?: {
      generateEditorPreview: (templateData: any, options?: { format?: string; quality?: number }) => Promise<any>;
      generateOrderPreview: (templateData: any, orderId: number, options?: { format?: string; quality?: number }) => Promise<any>;
    };
  }
}

interface HeaderProps {
  templateName: string;
  templateDescription: string;
  templateTags: string[];
  canvasWidth: number;
  canvasHeight: number;
  marginTop: number;
  marginBottom: number;
  showGuides: boolean;
  snapToGrid: boolean;
  isNewTemplate: boolean;
  isModified: boolean;
  isSaving: boolean;
  isEditingExistingTemplate: boolean;
  onSave: () => void;
  onPreview: () => void;
  onNewTemplate: () => void;
  onUpdateTemplateSettings: (settings: Partial<TemplateState>) => void;
}

export const Header = memo(function Header({
  templateName,
  templateDescription,
  templateTags,
  canvasWidth,
  canvasHeight,
  marginTop,
  marginBottom,
  showGuides,
  snapToGrid,
  isNewTemplate,
  isModified,
  isSaving,
  isEditingExistingTemplate,
  onSave,
  onPreview,
  onNewTemplate,
  onUpdateTemplateSettings
}: HeaderProps) {
  // Use deferred values for frequently changing props to prevent cascading re-renders
  const deferredIsModified = useDeferredValue(isModified);
  const deferredIsSaving = useDeferredValue(isSaving);
  const deferredIsEditingExistingTemplate = useDeferredValue(isEditingExistingTemplate);
    // Debug logging
  useEffect(() => {
    debugLog('🔧 Header component mounted/updated');
  }, []);

  debugLog('🎯 Rendering Header component');
  const { state, dispatch } = useBuilder();
  const [hoveredButton, setHoveredButton] = useState<string | null>(null);
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [showJsonModal, setShowJsonModal] = useState(false);
  const [copySuccess, setCopySuccess] = useState(false);
  const [isHeaderFixed, setIsHeaderFixed] = useState(false);
  const [showPreviewModal, setShowPreviewModal] = useState(false);
  const [previewFormat, setPreviewFormat] = useState<'png' | 'jpg' | 'pdf'>('png');
  const [isGeneratingPreview, setIsGeneratingPreview] = useState(false);
  const [previewImageUrl, setPreviewImageUrl] = useState<string | null>(null);
  const [previewError, setPreviewError] = useState<string | null>(null);
  const [editedTemplateName, setEditedTemplateName] = useState(templateName);
  const [editedTemplateDescription, setEditedTemplateDescription] = useState(templateDescription);
  const [editedTemplateTags, setEditedTemplateTags] = useState<string[]>(templateTags);
  const [editedCanvasWidth, setEditedCanvasWidth] = useState(canvasWidth);
  const [editedCanvasHeight, setEditedCanvasHeight] = useState(canvasHeight);
  const [editedMarginTop, setEditedMarginTop] = useState(marginTop);
  const [editedMarginBottom, setEditedMarginBottom] = useState(marginBottom);
  const [editedShowGuides, setEditedShowGuides] = useState(showGuides);
  const [editedSnapToGrid, setEditedSnapToGrid] = useState(snapToGrid);
  const [newTag, setNewTag] = useState('');

  // Debug logging
  useEffect(() => {
    debugLog('🔧 Header component mounted/updated');
  }, []);

  useEffect(() => {
    debugLog('🪟 Preview modal state changed:', showPreviewModal);
  }, [showPreviewModal]);

  // Synchroniser les états locaux avec les props quand elles changent
  useEffect(() => {
    setEditedTemplateName(templateName);
  }, [templateName]);

  useEffect(() => {
    setEditedTemplateDescription(templateDescription);
  }, [templateDescription]);

  useEffect(() => {
    setEditedTemplateTags(templateTags);
  }, [templateTags]);

  useEffect(() => {
    setEditedCanvasWidth(canvasWidth);
  }, [canvasWidth]);

  useEffect(() => {
    setEditedCanvasHeight(canvasHeight);
  }, [canvasHeight]);

  useEffect(() => {
    setEditedMarginTop(marginTop);
  }, [marginTop]);

  useEffect(() => {
    setEditedMarginBottom(marginBottom);
  }, [marginBottom]);

  useEffect(() => {
    setEditedShowGuides(showGuides);
  }, [showGuides]);

  useEffect(() => {
    setEditedSnapToGrid(snapToGrid);
  }, [snapToGrid]);

  // Optimisation: mémoriser le handler de scroll
  const handleScroll = useCallback(() => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    // Le header devient fixe après 100px de scroll
    setIsHeaderFixed(scrollTop > 100);
  }, []);

  // Effet pour gérer le scroll et rendre le header fixe
  useEffect(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, [handleScroll]);

  const buttonBaseStyles = {
    padding: '10px 16px',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px',
    fontWeight: '500',
    transition: 'all 0.2s ease',
    display: 'flex',
    alignItems: 'center',
    gap: '6px',
    whiteSpace: 'nowrap' as const
  };

  const primaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: '#4CAF50',
    color: '#fff',
    boxShadow: hoveredButton === 'save' ? '0 4px 12px rgba(76, 175, 80, 0.3)' : 'none',
    transform: hoveredButton === 'save' ? 'translateY(-2px)' : 'translateY(0)'
  };

  const secondaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: '#fff',
    border: '1px solid #ddd',
    color: '#333',
    boxShadow: hoveredButton === 'preview' || hoveredButton === 'new' ? '0 2px 8px rgba(0, 0, 0, 0.1)' : 'none',
    transform: hoveredButton === 'preview' || hoveredButton === 'new' ? 'translateY(-1px)' : 'translateY(0)'
  };

  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '16px',
      backgroundColor: '#ffffff',
      borderBottom: '2px solid #e0e0e0',
      borderRadius: isHeaderFixed ? '0' : '8px 8px 0 0',
      boxShadow: isHeaderFixed
        ? '0 4px 12px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1)'
        : '0 2px 8px rgba(0, 0, 0, 0.05)',
      gap: '16px',
      position: isHeaderFixed ? 'fixed' : 'relative',
      top: isHeaderFixed ? '32px' : 'auto', // En dessous de la barre admin WordPress
      left: isHeaderFixed ? '160px' : 'auto', // Après le menu WordPress replié
      right: isHeaderFixed ? 0 : 'auto',
      zIndex: 1000,
      transition: 'all 0.3s ease, box-shadow 0.2s ease, border-radius 0.3s ease'
    }}>
      {/* Left Section - Title and Status */}
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        minWidth: 0,
        flex: 1
      }}>
        <div style={{
          display: 'flex',
          alignItems: 'baseline',
          gap: '12px',
          minWidth: 0
        }}>
          <h2 style={{
            margin: 0,
            fontSize: '20px',
            fontWeight: '600',
            color: '#1a1a1a',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
          }}>
            {templateName || 'Sans titre'}
          </h2>

          {/* Status Badges */}
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            flexShrink: 0
          }}>
            {deferredIsModified && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#fff3cd',
                color: '#856404',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #ffeaa7',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <span style={{ fontSize: '16px' }}>●</span>
                Modifié
              </span>
            )}
            {isNewTemplate && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#d1ecf1',
                color: '#0c5460',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #bee5eb'
              }}>
                Nouveau
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Right Section - Action Buttons */}
      <div style={{
        display: 'flex',
        gap: '10px',
        flexShrink: 0,
        alignItems: 'center'
      }}>
        <button
          onClick={onNewTemplate}
          onMouseEnter={() => setHoveredButton('new')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Créer un nouveau template"
        >
          <span>➕</span>
          <span>Nouveau</span>
        </button>

        <button
          onClick={() => {
            // Ouvrir la modale JSON et aussi l'aperçu du preview
            setShowJsonModal(true);
            onPreview();
          }}
          onMouseEnter={() => setHoveredButton('preview')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Aperçu du template (JSON brut)"
        >
          <span>👁️</span>
          <span>Aperçu</span>
        </button>

        <button
          onClick={() => {
            debugLog('📸 Aperçu Image button clicked!');
            setShowPreviewModal(true);
          }}
          onMouseEnter={() => setHoveredButton('preview-image')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Générer un aperçu image du PDF"
        >
          <span>📸</span>
          <span>Aperçu Image</span>
        </button>

        <div style={{ width: '1px', height: '24px', backgroundColor: '#e0e0e0' }} />

        <button
          onClick={() => setShowSettingsModal(true)}
          onMouseEnter={() => setHoveredButton('settings')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Paramètres du template"
        >
          <span>⚙️</span>
          <span>Paramètres</span>
        </button>

        <button
          onClick={async () => {
            try {
              await onSave();
            } catch (error) {
              alert('Erreur lors de la sauvegarde: ' + (error instanceof Error ? error.message : 'Erreur inconnue'));
            }
          }}
          disabled={deferredIsSaving || !deferredIsModified}
          onMouseEnter={() => setHoveredButton('save')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...primaryButtonStyles,
            opacity: (deferredIsSaving || !deferredIsModified) ? 0.6 : 1,
            pointerEvents: (deferredIsSaving || !deferredIsModified) ? 'none' : 'auto'
          }}
          title={deferredIsModified ? (deferredIsEditingExistingTemplate ? 'Modifier le template' : 'Enregistrer les modifications') : 'Aucune modification'}
        >
          <span>{deferredIsSaving ? '⟳' : '💾'}</span>
          <span>{deferredIsSaving ? 'Enregistrement...' : (deferredIsEditingExistingTemplate ? 'Modifier' : 'Enregistrer')}</span>
        </button>
      </div>

      {/* Modale des paramètres du template */}
      {showSettingsModal && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000
        }}>
          <div style={{
            backgroundColor: '#ffffff',
            borderRadius: '8px',
            padding: '24px',
            maxWidth: '500px',
            width: '90%',
            maxHeight: '80vh',
            overflowY: 'auto',
            boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)'
          }}>
            <div style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: '20px',
              borderBottom: '1px solid #e0e0e0',
              paddingBottom: '16px'
            }}>
              <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600', color: '#1a1a1a' }}>
                Paramètres du template
              </h3>
              <button
                onClick={() => setShowSettingsModal(false)}
                style={{
                  background: 'none',
                  border: 'none',
                  fontSize: '24px',
                  cursor: 'pointer',
                  color: '#666',
                  padding: '4px'
                }}
                title="Fermer"
              >
                ×
              </button>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Nom du modèle
                </label>
                <input
                  type="text"
                  value={editedTemplateName}
                  onChange={(e) => setEditedTemplateName(e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: '14px',
                    backgroundColor: '#ffffff'
                  }}
                  placeholder="Entrez le nom du template"
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Description
                </label>
                <textarea
                  value={editedTemplateDescription}
                  onChange={(e) => setEditedTemplateDescription(e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: '14px',
                    minHeight: '60px',
                    resize: 'vertical'
                  }}
                  placeholder="Description du template..."
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Étiquettes (Tags)
                </label>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '8px', flexWrap: 'wrap' }}>
                  {editedTemplateTags.map((tag, index) => (
                    <span
                      key={index}
                      style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '4px 8px',
                        backgroundColor: '#e3f2fd',
                        color: '#1565c0',
                        borderRadius: '12px',
                        fontSize: '12px',
                        fontWeight: '500'
                      }}
                    >
                      {tag}
                      <button
                        onClick={() => setEditedTemplateTags(editedTemplateTags.filter((_, i) => i !== index))}
                        style={{
                          background: 'none',
                          border: 'none',
                          color: '#1565c0',
                          cursor: 'pointer',
                          fontSize: '14px',
                          padding: '0',
                          lineHeight: '1'
                        }}
                        title="Supprimer cette étiquette"
                      >
                        ×
                      </button>
                    </span>
                  ))}
                </div>
                <div style={{ display: 'flex', gap: '8px' }}>
                  <input
                    type="text"
                    value={newTag}
                    onChange={(e) => setNewTag(e.target.value)}
                    onKeyPress={(e) => {
                      if (e.key === 'Enter' && newTag.trim()) {
                        e.preventDefault();
                        setEditedTemplateTags([...editedTemplateTags, newTag.trim()]);
                        setNewTag('');
                      }
                    }}
                    style={{
                      flex: 1,
                      padding: '8px 12px',
                      border: '1px solid #ddd',
                      borderRadius: '4px',
                      fontSize: '14px'
                    }}
                    placeholder="Ajouter une étiquette..."
                  />
                  <button
                    onClick={() => {
                      if (newTag.trim()) {
                        setEditedTemplateTags([...editedTemplateTags, newTag.trim()]);
                        setNewTag('');
                      }
                    }}
                    style={{
                      padding: '8px 12px',
                      border: '1px solid #007bff',
                      borderRadius: '4px',
                      backgroundColor: '#007bff',
                      color: '#ffffff',
                      cursor: 'pointer',
                      fontSize: '14px'
                    }}
                  >
                    Ajouter
                  </button>
                </div>
                <div style={{ fontSize: '12px', color: '#666', marginTop: '4px' }}>
                  Appuyez sur Entrée ou cliquez sur "Ajouter" pour ajouter une étiquette
                </div>
              </div>

              <div style={{ borderTop: '1px solid #e0e0e0', paddingTop: '16px', marginTop: '16px' }}>
                <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#333' }}>
                  Paramètres avancés
                </h4>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }}>
                      Largeur du canvas (px)
                    </label>
                    <input
                      type="number"
                      value={editedCanvasWidth}
                      disabled={true}
                      style={{
                        width: '100%',
                        padding: '6px 8px',
                        border: '1px solid #ccc',
                        borderRadius: '3px',
                        fontSize: '12px',
                        backgroundColor: '#f5f5f5',
                        color: '#999',
                        cursor: 'not-allowed'
                      }}
                    />
                    <div style={{ fontSize: '10px', color: '#999', marginTop: '2px' }}>
                      Non modifiable
                    </div>
                  </div>

                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }}>
                      Hauteur du canvas (px)
                    </label>
                    <input
                      type="number"
                      value={editedCanvasHeight}
                      disabled={true}
                      style={{
                        width: '100%',
                        padding: '6px 8px',
                        border: '1px solid #ccc',
                        borderRadius: '3px',
                        fontSize: '12px',
                        backgroundColor: '#f5f5f5',
                        color: '#999',
                        cursor: 'not-allowed'
                      }}
                    />
                    <div style={{ fontSize: '10px', color: '#999', marginTop: '2px' }}>
                      Non modifiable
                    </div>
                  </div>

                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }}>
                      Marge supérieure (px)
                    </label>
                    <input
                      type="number"
                      value={editedMarginTop}
                      onChange={(e) => setEditedMarginTop(Number(e.target.value))}
                      style={{
                        width: '100%',
                        padding: '6px 8px',
                        border: '1px solid #ddd',
                        borderRadius: '3px',
                        fontSize: '12px'
                      }}
                    />
                  </div>

                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#555' }}>
                      Marge inférieure (px)
                    </label>
                    <input
                      type="number"
                      value={editedMarginBottom}
                      onChange={(e) => setEditedMarginBottom(Number(e.target.value))}
                      style={{
                        width: '100%',
                        padding: '6px 8px',
                        border: '1px solid #ddd',
                        borderRadius: '3px',
                        fontSize: '12px'
                      }}
                    />
                  </div>
                </div>

                <div style={{ marginTop: '12px' }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', fontWeight: '500', color: '#555' }}>
                    <input
                      type="checkbox"
                      checked={editedShowGuides}
                      onChange={(e) => setEditedShowGuides(e.target.checked)}
                      style={{ margin: 0 }}
                    />
                    Afficher les guides d'alignement
                  </label>
                </div>

                <div style={{ marginTop: '8px' }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', fontWeight: '500', color: '#555' }}>
                    <input
                      type="checkbox"
                      checked={editedSnapToGrid}
                      onChange={(e) => setEditedSnapToGrid(e.target.checked)}
                      style={{ margin: 0 }}
                    />
                    Mode grille magnétique
                  </label>
                </div>
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Statut
                </label>
                <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                  {isNewTemplate && (
                    <span style={{
                      padding: '4px 8px',
                      backgroundColor: '#e3f2fd',
                      color: '#1565c0',
                      borderRadius: '12px',
                      fontSize: '12px',
                      fontWeight: '500'
                    }}>
                      Nouveau template
                    </span>
                  )}
                  {deferredIsModified && (
                    <span style={{
                      padding: '4px 8px',
                      backgroundColor: '#fff3e0',
                      color: '#f57c00',
                      borderRadius: '12px',
                      fontSize: '12px',
                      fontWeight: '500'
                    }}>
                      Modifié
                    </span>
                  )}
                  {isEditingExistingTemplate && (
                    <span style={{
                      padding: '4px 8px',
                      backgroundColor: '#f3e5f5',
                      color: '#7b1fa2',
                      borderRadius: '12px',
                      fontSize: '12px',
                      fontWeight: '500'
                    }}>
                      Édition existante
                    </span>
                  )}
                </div>
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Informations système
                </label>
                <div style={{ fontSize: '13px', color: '#666', lineHeight: '1.5' }}>
                  <div>Template ID: {templateName || 'N/A'}</div>
                  <div>Dernière modification: {new Date().toLocaleString('fr-FR')}</div>
                  <div>État: {deferredIsSaving ? 'Enregistrement...' : deferredIsModified ? 'Modifié' : 'Sauvegardé'}</div>
                </div>
              </div>

              <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '12px', marginTop: '20px' }}>
                <button
                  onClick={() => setShowSettingsModal(false)}
                  style={{
                    padding: '8px 16px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    backgroundColor: '#f8f8f8',
                    color: '#333',
                    cursor: 'pointer',
                    fontSize: '14px'
                  }}
                >
                  Annuler
                </button>
                <button
                  onClick={() => {
                    // Sauvegarder les paramètres du template
                    onUpdateTemplateSettings({
                      name: editedTemplateName,
                      description: editedTemplateDescription,
                      tags: editedTemplateTags,
                      marginTop: editedMarginTop,
                      marginBottom: editedMarginBottom,
                      showGuides: editedShowGuides,
                      snapToGrid: editedSnapToGrid
                    });
                    setShowSettingsModal(false);
                  }}
                  style={{
                    padding: '8px 16px',
                    border: 'none',
                    borderRadius: '4px',
                    backgroundColor: '#4CAF50',
                    color: '#ffffff',
                    cursor: 'pointer',
                    fontSize: '14px',
                    fontWeight: '500'
                  }}
                >
                  Sauvegarder
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modale JSON brut du template */}
      {showJsonModal && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1001
        }}>
          <div style={{
            backgroundColor: '#ffffff',
            borderRadius: '8px',
            padding: '24px',
            maxWidth: '90vw',
            width: '100%',
            maxHeight: '85vh',
            display: 'flex',
            flexDirection: 'column',
            boxShadow: '0 10px 40px rgba(0, 0, 0, 0.3)'
          }}>
            {/* Header */}
            <div style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: '16px',
              borderBottom: '1px solid #e0e0e0',
              paddingBottom: '12px'
            }}>
              <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600', color: '#1a1a1a' }}>
                📋 JSON Brut du Template (ID: {templateName || 'N/A'})
              </h3>
              <button
                onClick={() => setShowJsonModal(false)}
                style={{
                  background: 'none',
                  border: 'none',
                  fontSize: '24px',
                  cursor: 'pointer',
                  color: '#666',
                  padding: '4px'
                }}
                title="Fermer"
              >
                ×
              </button>
            </div>

            {/* JSON Content */}
            <div style={{
              flex: 1,
              overflow: 'auto',
              backgroundColor: '#f5f5f5',
              borderRadius: '6px',
              padding: '16px',
              fontFamily: "'Courier New', monospace",
              fontSize: '12px',
              lineHeight: '1.5',
              color: '#333',
              whiteSpace: 'pre-wrap',
              wordBreak: 'break-word',
              border: '1px solid #ddd',
              marginBottom: '16px'
            }}>
              {JSON.stringify({ 
                ...state.template, 
                elements: state.elements 
              }, null, 2)}
            </div>

            {/* Footer with Buttons */}
            <div style={{
              display: 'flex',
              gap: '12px',
              justifyContent: 'flex-end',
              alignItems: 'center'
            }}>
              <button
                onClick={() => {
                  navigator.clipboard.writeText(JSON.stringify({ 
                    ...state.template, 
                    elements: state.elements 
                  }, null, 2));
                  setCopySuccess(true);
                  setTimeout(() => setCopySuccess(false), 2000);
                }}
                style={{
                  padding: '8px 16px',
                  backgroundColor: '#0073aa',
                  color: '#ffffff',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: 'pointer',
                  fontSize: '14px',
                  fontWeight: '500',
                  transition: 'all 0.2s ease',
                  opacity: copySuccess ? 0.7 : 1
                }}
                title="Copier le JSON"
              >
                {copySuccess ? '✅ Copié!' : '📋 Copier JSON'}
              </button>
              <button
                onClick={() => {
                  const jsonString = JSON.stringify({ 
                    ...state.template, 
                    elements: state.elements 
                  }, null, 2);
                  const blob = new Blob([jsonString], { type: 'application/json' });
                  const url = URL.createObjectURL(blob);
                  const link = document.createElement('a');
                  link.href = url;
                  link.download = `template-${templateName || 'export'}-${new Date().getTime()}.json`;
                  link.click();
                  URL.revokeObjectURL(url);
                }}
                style={{
                  padding: '8px 16px',
                  backgroundColor: '#10a37f',
                  color: '#ffffff',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: 'pointer',
                  fontSize: '14px',
                  fontWeight: '500',
                  transition: 'all 0.2s ease'
                }}
                title="Télécharger le JSON"
              >
                💾 Télécharger
              </button>
              <button
                onClick={() => setShowJsonModal(false)}
                style={{
                  padding: '8px 16px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  backgroundColor: '#f8f8f8',
                  color: '#333',
                  cursor: 'pointer',
                  fontSize: '14px',
                  fontWeight: '500',
                  transition: 'all 0.2s ease'
                }}
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modale d'aperçu PDF */}
      {showPreviewModal && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1001
        }}>
          <div style={{
            backgroundColor: '#ffffff',
            borderRadius: '8px',
            padding: '24px',
            maxWidth: '600px',
            width: '90%',
            maxHeight: '80vh',
            overflow: 'auto',
            boxShadow: '0 4px 20px rgba(0, 0, 0, 0.15)'
          }}>
            <div style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: '20px'
            }}>
              <h3 style={{
                margin: 0,
                fontSize: '18px',
                fontWeight: '600',
                color: '#1a1a1a'
              }}>
                Aperçu du PDF
              </h3>
              <button
                onClick={() => {
                  setShowPreviewModal(false);
                  setPreviewImageUrl(null);
                  setPreviewError(null);
                }}
                style={{
                  background: 'none',
                  border: 'none',
                  fontSize: '24px',
                  cursor: 'pointer',
                  color: '#666',
                  padding: '0',
                  width: '30px',
                  height: '30px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
                title="Fermer"
              >
                ×
              </button>
            </div>

            {/* Options de format */}
            <div style={{ marginBottom: '20px' }}>
              <label style={{
                display: 'block',
                fontSize: '14px',
                fontWeight: '500',
                color: '#333',
                marginBottom: '8px'
              }}>
                Format d'export :
              </label>
              <div style={{ display: 'flex', gap: '10px' }}>
                {[
                  { value: 'png', label: 'PNG', icon: '🖼️' },
                  { value: 'jpg', label: 'JPG', icon: '📷' },
                  { value: 'pdf', label: 'PDF', icon: '📄' }
                ].map(format => (
                  <button
                    key={format.value}
                    onClick={() => setPreviewFormat(format.value as 'png' | 'jpg' | 'pdf')}
                    style={{
                      padding: '8px 16px',
                      border: `2px solid ${previewFormat === format.value ? '#007cba' : '#ddd'}`,
                      borderRadius: '6px',
                      backgroundColor: previewFormat === format.value ? '#f0f8ff' : '#fff',
                      color: previewFormat === format.value ? '#007cba' : '#333',
                      cursor: 'pointer',
                      fontSize: '14px',
                      fontWeight: '500',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '6px',
                      transition: 'all 0.2s ease'
                    }}
                  >
                    <span>{format.icon}</span>
                    <span>{format.label}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* Bouton de génération */}
            <div style={{ marginBottom: '20px' }}>
              <button
                onClick={async () => {
                  setIsGeneratingPreview(true);
                  setPreviewError(null);
                  setPreviewImageUrl(null);

                  try {
                    // Utiliser l'API Preview
                    if (typeof window.pdfPreviewAPI !== 'undefined') {
                      const result = await window.pdfPreviewAPI.generateEditorPreview({
                        ...state.template,
                        elements: state.elements
                      }, {
                        format: previewFormat,
                        quality: 150
                      });

                      if (result && result.success && result.image_url) {
                        if (previewFormat === 'pdf') {
                          // Pour PDF, ouvrir dans un nouvel onglet
                          window.open(result.image_url, '_blank');
                        } else {
                          // Pour PNG/JPG, afficher dans la modale
                          setPreviewImageUrl(result.image_url);
                        }
                      } else {
                        setPreviewError(result?.error || 'Erreur lors de la génération');
                      }
                    } else {
                      setPreviewError('API Preview non disponible. Vérifiez que les scripts sont chargés.');
                    }
                  } catch (error) {
                    setPreviewError('Erreur: ' + (error instanceof Error ? error.message : 'Erreur inconnue'));
                  } finally {
                    setIsGeneratingPreview(false);
                  }
                }}
                disabled={isGeneratingPreview}
                style={{
                  padding: '12px 24px',
                  backgroundColor: isGeneratingPreview ? '#ccc' : '#007cba',
                  color: '#fff',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: isGeneratingPreview ? 'not-allowed' : 'pointer',
                  fontSize: '16px',
                  fontWeight: '500',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px',
                  transition: 'all 0.2s ease'
                }}
              >
                {isGeneratingPreview ? (
                  <>
                    <span style={{ animation: 'spin 1s linear infinite' }}>⟳</span>
                    <span>Génération en cours...</span>
                  </>
                ) : (
                  <>
                    <span>🎨</span>
                    <span>Générer l'aperçu</span>
                  </>
                )}
              </button>
            </div>

            {/* Affichage de l'erreur */}
            {previewError && (
              <div style={{
                padding: '12px',
                backgroundColor: '#f8d7da',
                border: '1px solid #f5c6cb',
                borderRadius: '4px',
                color: '#721c24',
                marginBottom: '20px'
              }}>
                <strong>Erreur:</strong> {previewError}
              </div>
            )}

            {/* Affichage de l'aperçu */}
            {previewImageUrl && (
              <div style={{ textAlign: 'center' }}>
                <img
                  src={previewImageUrl}
                  alt="Aperçu du PDF"
                  style={{
                    maxWidth: '100%',
                    maxHeight: '400px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)'
                  }}
                />
                <div style={{ marginTop: '10px' }}>
                  <a
                    href={previewImageUrl}
                    download={`apercu-${templateName || 'template'}.${previewFormat}`}
                    style={{
                      padding: '8px 16px',
                      backgroundColor: '#28a745',
                      color: '#fff',
                      textDecoration: 'none',
                      borderRadius: '4px',
                      fontSize: '14px',
                      fontWeight: '500'
                    }}
                  >
                    💾 Télécharger
                  </a>
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
});
