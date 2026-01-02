import React, { useState } from 'react';

const NewTemplateModal = ({ isOpen, onClose, onCreateTemplate }) => {
  const [formData, setFormData] = useState({
    name: '',
    defaultModel: 'Facture',
    description: '',
    isPublic: false,
    paperFormat: 'A4 (210 × 297 mm)',
    orientation: 'Portrait',
    category: 'Facture'
  });

  const [showAdvanced, setShowAdvanced] = useState(false);
  const [errors, setErrors] = useState({});

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.name.trim()) {
      newErrors.name = 'Le nom du template est obligatoire';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validateForm()) {
      onCreateTemplate(formData);
      onClose();
      // Reset form
      setFormData({
        name: '',
        defaultModel: 'Facture',
        description: '',
        isPublic: false,
        paperFormat: 'A4 (210 × 297 mm)',
        orientation: 'Portrait',
        category: 'Facture'
      });
      setShowAdvanced(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content new-template-modal" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <h3>Nouveau template</h3>
          <button className="modal-close" onClick={onClose}>×</button>
        </div>

        <form onSubmit={handleSubmit} className="modal-body">
          <div className="form-group">
            <label htmlFor="template-name">Nom du template *</label>
            <input
              id="template-name"
              type="text"
              value={formData.name}
              onChange={(e) => handleInputChange('name', e.target.value)}
              className={errors.name ? 'error' : ''}
              placeholder="Ex: Facture Standard"
            />
            {errors.name && <span className="error-message">{errors.name}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="default-model">Modèle par défaut</label>
            <select
              id="default-model"
              value={formData.defaultModel}
              onChange={(e) => handleInputChange('defaultModel', e.target.value)}
            >
              <option value="Facture">Facture</option>
              <option value="Devis">Devis</option>
              <option value="Bon de commande">Bon de commande</option>
              <option value="Bon de livraison">Bon de livraison</option>
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="description">Description</label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => handleInputChange('description', e.target.value)}
              placeholder="Description du template..."
              rows={3}
            />
          </div>

          <div className="form-group">
            <button
              type="button"
              className="advanced-toggle"
              onClick={() => setShowAdvanced(!showAdvanced)}
            >
              Paramètres avancés {showAdvanced ? '▼' : '▶'}
            </button>
          </div>

          {showAdvanced && (
            <div className="advanced-settings">
              <div className="form-group checkbox-group">
                <label className="checkbox-label">
                  <input
                    type="checkbox"
                    checked={formData.isPublic}
                    onChange={(e) => handleInputChange('isPublic', e.target.checked)}
                  />
                  <span>Template public (visible par tous les utilisateurs)</span>
                </label>
              </div>

              <div className="form-group">
                <label htmlFor="paper-format">Format de papier</label>
                <select
                  id="paper-format"
                  value={formData.paperFormat}
                  onChange={(e) => handleInputChange('paperFormat', e.target.value)}
                >
                  <option value="A4 (210 × 297 mm)">A4 (210 × 297 mm)</option>
                  <option value="A5 (148 × 210 mm)">A5 (148 × 210 mm)</option>
                  <option value="Lettre (8.5 × 11 pouces)">Lettre (8.5 × 11 pouces)</option>
                  <option value="Legal (8.5 × 14 pouces)">Legal (8.5 × 14 pouces)</option>
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="orientation">Orientation</label>
                <select
                  id="orientation"
                  value={formData.orientation}
                  onChange={(e) => handleInputChange('orientation', e.target.value)}
                >
                  <option value="Portrait">Portrait</option>
                  <option value="Paysage">Paysage</option>
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="category">Catégorie</label>
                <select
                  id="category"
                  value={formData.category}
                  onChange={(e) => handleInputChange('category', e.target.value)}
                >
                  <option value="Facture">Facture</option>
                  <option value="Devis">Devis</option>
                  <option value="Bon de commande">Bon de commande</option>
                  <option value="Bon de livraison">Bon de livraison</option>
                  <option value="Reçu">Reçu</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
            </div>
          )}

          <div className="modal-footer">
            <button type="button" className="btn-secondary" onClick={onClose}>
              Annuler
            </button>
            <button type="submit" className="btn-primary">
              Ouvrir le template
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default NewTemplateModal;