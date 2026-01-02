import React, { useState } from 'react';
import './TemplateHeader.css';

const TemplateHeader = ({
  templateName,
  isNew,
  onSave,
  onCreateNew,
  onPreview
}) => {
  const [showNewTemplateModal, setShowNewTemplateModal] = useState(false);
  const [showTemplateSettingsModal, setShowTemplateSettingsModal] = useState(false);
  const [newTemplateData, setNewTemplateData] = useState({
    name: '',
    width: 595,
    height: 842,
    orientation: 'portrait'
  });
  const [templateSettings, setTemplateSettings] = useState({
    name: templateName || '',
    description: '',
    category: 'autre'
  });
  const [isSaving, setIsSaving] = useState(false);
  const [saveMessage, setSaveMessage] = useState('');

  const handleSave = async () => {
    if (onSave) {
      setIsSaving(true);
      setSaveMessage('');

      try {
        await onSave();
        setSaveMessage('Template sauvegardé avec succès !');
        setTimeout(() => setSaveMessage(''), 3000); // Masquer le message après 3 secondes
      } catch (error) {
        setSaveMessage('Erreur lors de la sauvegarde');
        setTimeout(() => setSaveMessage(''), 3000);
      } finally {
        setIsSaving(false);
      }
    }
  };

  const handleCreateNew = () => {
    if (onCreateNew) {
      onCreateNew(newTemplateData);
    }
    setShowNewTemplateModal(false);
    // Reset form
    setNewTemplateData({
      name: '',
      width: 595,
      height: 842,
      orientation: 'portrait'
    });
  };

  const handleOpenTemplateSettings = async () => {
    try {
      const templateId = window.pdfBuilderData?.templateId;
      
      if (!templateId) {
        console.error('ID du template non disponible');
        // Utiliser les valeurs par défaut
        setTemplateSettings({
          name: templateName || '',
          description: '',
          category: 'autre'
        });
        setShowTemplateSettingsModal(true);
        return;
      }

      // Charger les paramètres actuels du template
      const formData = new FormData();
      formData.append('action', 'pdf_builder_load_template_settings');
      formData.append('template_id', templateId);
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      console.log('Chargement des paramètres du template:', templateId);

      const response = await fetch(window.pdfBuilderData?.ajaxUrl || window.ajaxurl, {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success && result.data) {
        console.log('Paramètres chargés:', result.data);
        setTemplateSettings({
          name: result.data.name || templateName || '',
          description: result.data.description || '',
          category: result.data.category || 'autre'
        });
      } else {
        console.log('Aucun paramètre trouvé, utilisation des valeurs par défaut');
        // Utiliser les valeurs par défaut si pas de données
        setTemplateSettings({
          name: templateName || '',
          description: '',
          category: 'autre'
        });
      }
    } catch (error) {
      console.error('Erreur lors du chargement des paramètres:', error);
      // Utiliser les valeurs par défaut en cas d'erreur
      setTemplateSettings({
        name: templateName || '',
        description: '',
        category: 'autre'
      });
    }
    
    setShowTemplateSettingsModal(true);
  };

  const handleSaveTemplateSettings = async () => {
    try {
      const templateId = window.pdfBuilderData?.templateId;
      
      if (!templateId) {
        console.error('ID du template non disponible');
        alert('Erreur: ID du template non trouvé');
        return;
      }

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_save_template_settings');
      formData.append('template_id', templateId);
      formData.append('name', templateSettings.name);
      formData.append('description', templateSettings.description);
      formData.append('category', templateSettings.category);
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      console.log('Sauvegarde des paramètres du template:', {
        templateId,
        name: templateSettings.name,
        description: templateSettings.description,
        category: templateSettings.category
      });

      // Faire l'appel AJAX
      const response = await fetch(window.pdfBuilderData?.ajaxUrl || window.ajaxurl, {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        console.log('Paramètres du template sauvegardés avec succès');
        alert('Paramètres du template sauvegardés avec succès !');
        setShowTemplateSettingsModal(false);
      } else {
        console.error('Erreur lors de la sauvegarde:', result.data);
        alert('Erreur lors de la sauvegarde: ' + (result.data || 'Erreur inconnue'));
      }
    } catch (error) {
      console.error('Erreur AJAX:', error);
      alert('Erreur lors de la sauvegarde des paramètres');
    }
  };

  return (
    <>
      <style>
        {`
          @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
          }
          
          @keyframes modalFadeIn {
            0% { 
              opacity: 0; 
              transform: scale(0.9) translateY(-20px); 
            }
            100% { 
              opacity: 1; 
              transform: scale(1) translateY(0); 
            }
          }
          
          @keyframes modalBackdropFadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
          }          
          @keyframes modalSlideIn {
            0% { 
              opacity: 0; 
              transform: scale(0.9) translateY(-20px); 
            }
            100% { 
              opacity: 1; 
              transform: scale(1) translateY(0); 
            }
          }        `}
      </style>
      <div className="template-header">
        <div className="header-left">
          <button
            className="header-btn new-template-btn"
            onClick={() => setShowNewTemplateModal(true)}
            title="Créer un nouveau template"
          >
            <span className="btn-icon">📄</span>
            <span className="btn-text">Nouveau template</span>
          </button>
        </div>

        <div className="header-center">
          <h2 className="template-title">
            {isNew ? 'Nouveau template' : `Modifier: ${templateName || 'Template sans nom'}`}
          </h2>
          {saveMessage && (
            <div className={`save-message ${saveMessage.includes('succès') ? 'success' : 'error'}`}>
              {saveMessage}
            </div>
          )}
        </div>

        <div className="header-right">
          <button
            className="header-btn settings-btn"
            onClick={handleOpenTemplateSettings}
            title="Paramètres du template"
          >
            <span className="btn-icon">⚙️</span>
            <span className="btn-text">Paramètres</span>
          </button>

          <button
            className={`header-btn save-btn ${isSaving ? 'saving' : ''}`}
            onClick={handleSave}
            disabled={isSaving}
            title={isNew ? 'Sauvegarder le template' : 'Modifier le template'}
          >
            <span className="btn-icon">
              {isSaving ? '⏳' : (isNew ? '💾' : '✏️')}
            </span>
            <span className="btn-text">
              {isSaving ? 'Sauvegarde...' : (isNew ? 'Sauvegarder' : 'Modifier')}
            </span>
          </button>

          <button
            className="header-btn preview-btn"
            onClick={onPreview}
            title="Aperçu du PDF"
          >
            <span className="btn-icon">👁️</span>
            <span className="btn-text">Aperçu</span>
          </button>
        </div>
      </div>

      {/* Modal Nouveau Template */}
      {showNewTemplateModal && (
        <div className="modal-overlay" onClick={() => setShowNewTemplateModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3>Créer un nouveau template</h3>
              <button
                className="modal-close"
                onClick={() => setShowNewTemplateModal(false)}
              >
                ×
              </button>
            </div>

            <div className="modal-body">
              <div className="form-group">
                <label htmlFor="template-name">Nom du template</label>
                <input
                  id="template-name"
                  type="text"
                  value={newTemplateData.name}
                  onChange={(e) => setNewTemplateData({...newTemplateData, name: e.target.value})}
                  placeholder="Entrez le nom du template"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-width">Largeur (px)</label>
                <input
                  id="template-width"
                  type="number"
                  value={newTemplateData.width}
                  onChange={(e) => setNewTemplateData({...newTemplateData, width: parseInt(e.target.value)})}
                  min="100"
                  max="2000"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-height">Hauteur (px)</label>
                <input
                  id="template-height"
                  type="number"
                  value={newTemplateData.height}
                  onChange={(e) => setNewTemplateData({...newTemplateData, height: parseInt(e.target.value)})}
                  min="100"
                  max="3000"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-orientation">Orientation</label>
                <select
                  id="template-orientation"
                  value={newTemplateData.orientation}
                  onChange={(e) => setNewTemplateData({...newTemplateData, orientation: e.target.value})}
                >
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Paysage</option>
                </select>
              </div>
            </div>

            <div className="modal-footer">
              <button
                className="btn-secondary"
                onClick={() => setShowNewTemplateModal(false)}
              >
                Annuler
              </button>
              <button
                className="btn-primary"
                onClick={handleCreateNew}
                disabled={!newTemplateData.name.trim()}
              >
                Créer le template
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal Paramètres du Template */}
      {showTemplateSettingsModal && (
        <div 
          className="modal-overlay" 
          onClick={() => setShowTemplateSettingsModal(false)}
        >
          <div
            className="modal-content template-settings-modal"
            onClick={(e) => e.stopPropagation()}
          >
            <div
              className="modal-header"
              style={{
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%)',
                color: 'white',
                borderBottom: 'none',
                borderRadius: '16px 16px 0 0',
                padding: '24px 28px',
                position: 'relative',
                overflow: 'hidden'
              }}
            >
              <div style={{
                position: 'absolute',
                top: '-50%',
                left: '-50%',
                width: '200%',
                height: '200%',
                background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
                animation: 'shine 3s ease-in-out infinite'
              }}></div>
              <div style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                position: 'relative',
                zIndex: 2
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{
                    width: '40px',
                    height: '40px',
                    borderRadius: '12px',
                    background: 'rgba(255, 255, 255, 0.2)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: '18px'
                  }}>
                    ⚙️
                  </div>
                  <div>
                    <h3 style={{
                      color: 'white',
                      fontSize: '22px',
                      margin: '0',
                      fontWeight: '700',
                      letterSpacing: '-0.5px'
                    }}>
                      Paramètres du template
                    </h3>
                    <p style={{
                      color: 'rgba(255, 255, 255, 0.8)',
                      fontSize: '14px',
                      margin: '4px 0 0 0',
                      fontWeight: '400'
                    }}>
                      Personnalisez votre template
                    </p>
                  </div>
                </div>
                <button
                  className="modal-close"
                  onClick={() => setShowTemplateSettingsModal(false)}
                  style={{
                    color: 'rgba(255, 255, 255, 0.9)',
                    background: 'rgba(255, 255, 255, 0.15)',
                    border: '1px solid rgba(255, 255, 255, 0.2)',
                    borderRadius: '10px',
                    width: '36px',
                    height: '36px',
                    cursor: 'pointer',
                    fontSize: '20px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                    backdropFilter: 'blur(10px)',
                    position: 'relative',
                    zIndex: 2
                  }}
                  onMouseEnter={(e) => {
                    e.target.style.color = 'white';
                    e.target.style.background = 'rgba(255, 255, 255, 0.25)';
                    e.target.style.transform = 'scale(1.05) rotate(90deg)';
                    e.target.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                  }}
                  onMouseLeave={(e) => {
                    e.target.style.color = 'rgba(255, 255, 255, 0.9)';
                    e.target.style.background = 'rgba(255, 255, 255, 0.15)';
                    e.target.style.transform = 'scale(1) rotate(0deg)';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  ×
                </button>
              </div>
            </div>

            <div
              className="modal-body"
              style={{
                padding: '32px',
                background: 'linear-gradient(180deg, #f8fafc 0%, #ffffff 100%)',
                borderRadius: '0 0 16px 16px'
              }}
            >
              <div
                className="form-group"
                style={{
                  marginBottom: '24px',
                  background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                  padding: '20px',
                  borderRadius: '12px',
                  border: '1px solid rgba(226, 232, 240, 0.8)',
                  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  position: 'relative',
                  overflow: 'hidden'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = 'rgba(102, 126, 234, 0.3)';
                  e.target.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                  e.target.style.transform = 'translateY(-2px)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
                  e.target.style.transform = 'translateY(0)';
                }}
              >
                <div style={{
                  position: 'absolute',
                  top: '0',
                  left: '0',
                  width: '4px',
                  height: '100%',
                  background: 'linear-gradient(180deg, #667eea 0%, #764ba2 100%)',
                  borderRadius: '12px 0 0 12px'
                }}></div>
                <div style={{ marginLeft: '16px' }}>
                  <label
                    htmlFor="settings-template-name"
                    style={{
                      color: '#1a202c',
                      fontWeight: '600',
                      marginBottom: '10px',
                      fontSize: '15px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px'
                    }}
                  >
                    <span style={{
                      width: '20px',
                      height: '20px',
                      borderRadius: '6px',
                      background: 'linear-gradient(135deg, #667eea, #764ba2)',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '12px',
                      color: 'white'
                    }}>
                      📝
                    </span>
                    Nom du template
                  </label>
                  <input
                    id="settings-template-name"
                    type="text"
                    value={templateSettings.name}
                    onChange={(e) => setTemplateSettings({...templateSettings, name: e.target.value})}
                    placeholder="Entrez le nom du template"
                    style={{
                      border: '2px solid rgba(226, 232, 240, 0.8)',
                      borderRadius: '10px',
                      padding: '14px 16px',
                      fontSize: '15px',
                      transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                      width: '100%',
                      boxSizing: 'border-box',
                      background: 'rgba(255, 255, 255, 0.8)',
                      backdropFilter: 'blur(10px)',
                      fontFamily: 'inherit',
                      outline: 'none'
                    }}
                    onFocus={(e) => {
                      e.target.style.borderColor = '#667eea';
                      e.target.style.boxShadow = '0 0 0 4px rgba(102, 126, 234, 0.1), inset 0 2px 4px rgba(0, 0, 0, 0.05)';
                      e.target.style.transform = 'translateY(-1px)';
                      e.target.style.background = 'rgba(255, 255, 255, 0.95)';
                    }}
                    onBlur={(e) => {
                      e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                      e.target.style.boxShadow = 'none';
                      e.target.style.transform = 'none';
                      e.target.style.background = 'rgba(255, 255, 255, 0.8)';
                    }}
                  />
                </div>
              </div>

              <div
                className="form-group"
                style={{
                  marginBottom: '24px',
                  background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                  padding: '20px',
                  borderRadius: '12px',
                  border: '1px solid rgba(226, 232, 240, 0.8)',
                  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  position: 'relative',
                  overflow: 'hidden'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = 'rgba(102, 126, 234, 0.3)';
                  e.target.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                  e.target.style.transform = 'translateY(-2px)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
                  e.target.style.transform = 'translateY(0)';
                }}
              >
                <div style={{
                  position: 'absolute',
                  top: '0',
                  left: '0',
                  width: '4px',
                  height: '100%',
                  background: 'linear-gradient(180deg, #48bb78 0%, #38a169 100%)',
                  borderRadius: '12px 0 0 12px'
                }}></div>
                <div style={{ marginLeft: '16px' }}>
                  <label
                    htmlFor="settings-template-description"
                    style={{
                      color: '#1a202c',
                      fontWeight: '600',
                      marginBottom: '10px',
                      fontSize: '15px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px'
                    }}
                  >
                    <span style={{
                      width: '20px',
                      height: '20px',
                      borderRadius: '6px',
                      background: 'linear-gradient(135deg, #48bb78, #38a169)',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '12px',
                      color: 'white'
                    }}>
                      📄
                    </span>
                    Description
                  </label>
                  <textarea
                    id="settings-template-description"
                    value={templateSettings.description}
                    onChange={(e) => setTemplateSettings({...templateSettings, description: e.target.value})}
                    placeholder="Entrez une description du template"
                    rows="4"
                    style={{
                      border: '2px solid rgba(226, 232, 240, 0.8)',
                      borderRadius: '10px',
                      padding: '14px 16px',
                      fontSize: '15px',
                      transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                      width: '100%',
                      boxSizing: 'border-box',
                      background: 'rgba(255, 255, 255, 0.8)',
                      backdropFilter: 'blur(10px)',
                      fontFamily: 'inherit',
                      outline: 'none',
                      resize: 'vertical',
                      minHeight: '80px'
                    }}
                    onFocus={(e) => {
                      e.target.style.borderColor = '#48bb78';
                      e.target.style.boxShadow = '0 0 0 4px rgba(72, 187, 120, 0.1), inset 0 2px 4px rgba(0, 0, 0, 0.05)';
                      e.target.style.transform = 'translateY(-1px)';
                      e.target.style.background = 'rgba(255, 255, 255, 0.95)';
                    }}
                    onBlur={(e) => {
                      e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                      e.target.style.boxShadow = 'none';
                      e.target.style.transform = 'none';
                      e.target.style.background = 'rgba(255, 255, 255, 0.8)';
                    }}
                  />
                </div>
              </div>

              <div
                className="form-group"
                style={{
                  marginBottom: '24px',
                  background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                  padding: '20px',
                  borderRadius: '12px',
                  border: '1px solid rgba(226, 232, 240, 0.8)',
                  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  position: 'relative',
                  overflow: 'hidden'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = 'rgba(102, 126, 234, 0.3)';
                  e.target.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
                  e.target.style.transform = 'translateY(-2px)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
                  e.target.style.transform = 'translateY(0)';
                }}
              >
                <div style={{
                  position: 'absolute',
                  top: '0',
                  left: '0',
                  width: '4px',
                  height: '100%',
                  background: 'linear-gradient(180deg, #ed8936 0%, #dd6b20 100%)',
                  borderRadius: '12px 0 0 12px'
                }}></div>
                <div style={{ marginLeft: '16px' }}>
                  <label
                    htmlFor="settings-template-category"
                    style={{
                      color: '#1a202c',
                      fontWeight: '600',
                      marginBottom: '10px',
                      fontSize: '15px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px'
                    }}
                  >
                    <span style={{
                      width: '20px',
                      height: '20px',
                      borderRadius: '6px',
                      background: 'linear-gradient(135deg, #ed8936, #dd6b20)',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '12px',
                      color: 'white'
                    }}>
                      🏷️
                    </span>
                    Catégorie
                  </label>
                  <div style={{ position: 'relative' }}>
                    <select
                      id="settings-template-category"
                      value={templateSettings.category}
                      onChange={(e) => setTemplateSettings({...templateSettings, category: e.target.value})}
                      style={{
                        border: '2px solid rgba(226, 232, 240, 0.8)',
                        borderRadius: '10px',
                        padding: '14px 16px',
                        fontSize: '15px',
                        transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                        width: '100%',
                        boxSizing: 'border-box',
                        background: 'rgba(255, 255, 255, 0.8)',
                        backdropFilter: 'blur(10px)',
                        fontFamily: 'inherit',
                        outline: 'none',
                        cursor: 'pointer',
                        appearance: 'none',
                        backgroundImage: 'url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6,9 12,15 18,9\'%3e%3c/polyline%3e%3c/svg%3e")',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'right 12px center',
                        backgroundSize: '16px',
                        paddingRight: '40px'
                      }}
                      onFocus={(e) => {
                        e.target.style.borderColor = '#ed8936';
                        e.target.style.boxShadow = '0 0 0 4px rgba(237, 137, 54, 0.1), inset 0 2px 4px rgba(0, 0, 0, 0.05)';
                        e.target.style.transform = 'translateY(-1px)';
                        e.target.style.background = 'rgba(255, 255, 255, 0.95)';
                      }}
                      onBlur={(e) => {
                        e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                        e.target.style.boxShadow = 'none';
                        e.target.style.transform = 'none';
                        e.target.style.background = 'rgba(255, 255, 255, 0.8)';
                      }}
                    >
                      <option value="facture">📄 Facture</option>
                      <option value="devis">📋 Devis</option>
                      <option value="commande">🛒 Bon de commande</option>
                      <option value="contrat">📑 Contrat</option>
                      <option value="newsletter">📧 Newsletter</option>
                      <option value="autre">📁 Autre</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div
              className="modal-footer"
              style={{
                padding: '24px 32px',
                background: 'linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)',
                borderTop: '1px solid rgba(226, 232, 240, 0.6)',
                borderRadius: '0 0 20px 20px',
                display: 'flex',
                justifyContent: 'flex-end',
                gap: '12px',
                position: 'relative',
                overflow: 'hidden'
              }}
            >
              <div style={{
                position: 'absolute',
                top: '0',
                left: '0',
                right: '0',
                bottom: '0',
                background: 'linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.02) 50%, transparent 100%)',
                animation: 'shine 3s ease-in-out infinite'
              }}></div>
              <button
                className="btn-secondary"
                onClick={() => setShowTemplateSettingsModal(false)}
                style={{
                  padding: '12px 24px',
                  border: '2px solid rgba(226, 232, 240, 0.8)',
                  borderRadius: '10px',
                  background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                  color: '#64748b',
                  fontSize: '15px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  boxShadow: '0 2px 8px rgba(0, 0, 0, 0.04)',
                  position: 'relative',
                  zIndex: '1'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = '#cbd5e0';
                  e.target.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.08)';
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.background = 'linear-gradient(145deg, #f8fafc 0%, #ffffff 100%)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = 'rgba(226, 232, 240, 0.8)';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.04)';
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.background = 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)';
                }}
                onMouseDown={(e) => {
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.boxShadow = '0 1px 4px rgba(0, 0, 0, 0.06)';
                }}
                onMouseUp={(e) => {
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.08)';
                }}
              >
                Annuler
              </button>
              <button
                className="btn-primary"
                onClick={handleSaveTemplateSettings}
                style={{
                  padding: '12px 24px',
                  border: 'none',
                  borderRadius: '10px',
                  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                  color: 'white',
                  fontSize: '15px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  boxShadow: '0 4px 16px rgba(102, 126, 234, 0.3)',
                  position: 'relative',
                  zIndex: '1',
                  overflow: 'hidden',
                  letterSpacing: '0.5px'
                }}
                onMouseEnter={(e) => {
                  e.target.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.4)';
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.background = 'linear-gradient(135deg, #764ba2 0%, #667eea 100%)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.boxShadow = '0 4px 16px rgba(102, 126, 234, 0.3)';
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                }}
                onMouseDown={(e) => {
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.boxShadow = '0 2px 8px rgba(102, 126, 234, 0.2)';
                }}
                onMouseUp={(e) => {
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.4)';
                }}
              >
                <span style={{
                  position: 'relative',
                  zIndex: '1',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px'
                }}>
                  <span>💾</span>
                  Enregistrer
                </span>
                <div style={{
                  position: 'absolute',
                  top: '0',
                  left: '-100%',
                  width: '100%',
                  height: '100%',
                  background: 'linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent)',
                  transition: 'left 0.5s ease',
                  zIndex: '0'
                }}
                onMouseEnter={(e) => {
                  e.target.style.left = '100%';
                }}
                onMouseLeave={(e) => {
                  e.target.style.left = '-100%';
                }}
                ></div>
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default TemplateHeader;