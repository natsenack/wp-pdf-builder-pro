import React, { useState } from 'react';

interface HeaderProps {
  templateName: string;
  isNewTemplate: boolean;
  isModified: boolean;
  isSaving: boolean;
  isEditingExistingTemplate: boolean;
  onSave: () => void;
  onPreview: () => void;
  onNewTemplate: () => void;
}

export function Header({
  templateName,
  isNewTemplate,
  isModified,
  isSaving,
  isEditingExistingTemplate,
  onSave,
  onPreview,
  onNewTemplate
}: HeaderProps) {
  const [hoveredButton, setHoveredButton] = useState<string | null>(null);
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [editedTemplateName, setEditedTemplateName] = useState(templateName);
  const [templateDescription, setTemplateDescription] = useState('');
  const [templateTags, setTemplateTags] = useState<string[]>([]);
  const [newTag, setNewTag] = useState('');

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
      borderRadius: '8px 8px 0 0',
      boxShadow: '0 2px 8px rgba(0, 0, 0, 0.05)',
      gap: '16px'
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
            {isModified && (
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
            {isSaving && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#e8f4f8',
                color: '#0056b3',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #b8daff',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <span style={{
                  display: 'inline-block',
                  animation: 'spin 1s linear infinite',
                  transformOrigin: 'center'
                }}>
                  ⟳
                </span>
                Enregistrement...
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
          title="Créer un nouveau modèle"
        >
          <span>➕</span>
          <span>Nouveau</span>
        </button>

        <button
          onClick={onPreview}
          onMouseEnter={() => setHoveredButton('preview')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Aperçu du modèle"
        >
          <span>👁️</span>
          <span>Aperçu</span>
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
          title="Paramètres du modèle"
        >
          <span>⚙️</span>
          <span>Paramètres</span>
        </button>

        <button
          onClick={async () => {
            try {
              await onSave();
            } catch (error) {
              console.error('Erreur lors de la sauvegarde:', error);
              alert('Erreur lors de la sauvegarde: ' + (error instanceof Error ? error.message : 'Erreur inconnue'));
            }
          }}
          disabled={isSaving || !isModified}
          onMouseEnter={() => setHoveredButton('save')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...primaryButtonStyles,
            opacity: (isSaving || !isModified) ? 0.6 : 1,
            pointerEvents: (isSaving || !isModified) ? 'none' : 'auto'
          }}
          title={isModified ? (isEditingExistingTemplate ? 'Modifier le modèle' : 'Enregistrer les modifications') : 'Aucune modification'}
        >
          <span>{isSaving ? '⟳' : '💾'}</span>
          <span>{isSaving ? 'Enregistrement...' : (isEditingExistingTemplate ? 'Modifier' : 'Enregistrer')}</span>
        </button>
      </div>

      {/* Modale des paramètres */}
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
                Paramètres du modèle
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
                  placeholder="Entrez le nom du modèle"
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Description
                </label>
                <textarea
                  value={templateDescription}
                  onChange={(e) => setTemplateDescription(e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: '14px',
                    minHeight: '60px',
                    resize: 'vertical'
                  }}
                  placeholder="Description du modèle..."
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '14px', fontWeight: '600', marginBottom: '8px', color: '#333' }}>
                  Étiquettes (Tags)
                </label>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '8px', flexWrap: 'wrap' }}>
                  {templateTags.map((tag, index) => (
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
                        onClick={() => setTemplateTags(templateTags.filter((_, i) => i !== index))}
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
                        setTemplateTags([...templateTags, newTag.trim()]);
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
                        setTemplateTags([...templateTags, newTag.trim()]);
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
                      defaultValue="595"
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
                      Hauteur du canvas (px)
                    </label>
                    <input
                      type="number"
                      defaultValue="842"
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
                      Marge supérieure (px)
                    </label>
                    <input
                      type="number"
                      defaultValue="20"
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
                      defaultValue="20"
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
                      defaultChecked={true}
                      style={{ margin: 0 }}
                    />
                    Afficher les guides d'alignement
                  </label>
                </div>

                <div style={{ marginTop: '8px' }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', fontWeight: '500', color: '#555' }}>
                    <input
                      type="checkbox"
                      defaultChecked={false}
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
                      Nouveau modèle
                    </span>
                  )}
                  {isModified && (
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
                  <div>Modèle ID: {templateName || 'N/A'}</div>
                  <div>Dernière modification: {new Date().toLocaleString('fr-FR')}</div>
                  <div>État: {isSaving ? 'Enregistrement...' : isModified ? 'Modifié' : 'Sauvegardé'}</div>
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
                    // Ici on pourrait sauvegarder les paramètres
                    console.log('Sauvegarde des paramètres:', {
                      name: editedTemplateName,
                      description: templateDescription,
                      tags: templateTags
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

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}
