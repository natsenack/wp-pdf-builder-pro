import React, { useState, useEffect } from 'react';
import { PDFTemplate, AjaxResponse, LoadingComponentProps } from '@/shared';

// Interface spécifique au composant
interface TemplateSelectorProps extends LoadingComponentProps {
  selectedTemplate?: PDFTemplate;
  onTemplateSelect: (template: PDFTemplate) => void;
  category?: string;
}

/**
 * Composant pour sélectionner un template PDF
 * Exemple d'utilisation de la structure TypeScript
 */
const TemplateSelector: React.FC<TemplateSelectorProps> = ({
  selectedTemplate,
  onTemplateSelect,
  category,
  isLoading = false,
  className = '',
  ...props
}) => {
  const [templates, setTemplates] = useState<PDFTemplate[]>([]);
  const [error, setError] = useState<string | null>(null);

  // Chargement des templates
  useEffect(() => {
    const loadTemplates = async () => {
      try {
        setError(null);

        // Exemple d'appel AJAX WordPress
        const response: AjaxResponse<PDFTemplate[]> = await fetch(window.ajaxurl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'pdf_builder_get_templates',
            category: category || '',
            nonce: window.pdfBuilderPro.nonce,
          }),
        }).then(res => res.json());

        if (response.success) {
          setTemplates(response.data);
        } else {
          setError(response.data?.message || 'Erreur lors du chargement des templates');
        }
      } catch (err) {
        setError('Erreur de connexion');
        console.error('Erreur lors du chargement des templates:', err);
      }
    };

    loadTemplates();
  }, [category]);

  // Gestionnaire de sélection
  const handleTemplateSelect = (template: PDFTemplate) => {
    onTemplateSelect(template);
  };

  // Rendu en cas d'erreur
  if (error) {
    return (
      <div className={`template-selector error ${className}`} {...props}>
        <div className="error-message">
          <p>{error}</p>
          <button
            type="button"
            onClick={() => window.location.reload()}
            className="button button-secondary"
          >
            Réessayer
          </button>
        </div>
      </div>
    );
  }

  // Rendu en cours de chargement
  if (isLoading) {
    return (
      <div className={`template-selector loading ${className}`} {...props}>
        <div className="loading-spinner">
          <span className="dashicons dashicons-update spin"></span>
          <p>Chargement des templates...</p>
        </div>
      </div>
    );
  }

  // Rendu normal
  return (
    <div className={`template-selector ${className}`} {...props}>
      <h3>Sélectionner un template</h3>

      <div className="template-grid">
        {templates.map((template) => (
          <div
            key={template.id}
            className={`template-card ${
              selectedTemplate?.id === template.id ? 'selected' : ''
            }`}
            onClick={() => handleTemplateSelect(template)}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleTemplateSelect(template);
              }
            }}
          >
            {template.thumbnail && (
              <img
                src={template.thumbnail}
                alt={template.name}
                className="template-thumbnail"
              />
            )}

            <div className="template-info">
              <h4>{template.name}</h4>
              <p>{template.description}</p>
              <span className="template-category">{template.category}</span>
            </div>

            {template.isDefault && (
              <span className="template-badge default">Par défaut</span>
            )}
          </div>
        ))}
      </div>

      {templates.length === 0 && (
        <div className="no-templates">
          <p>Aucun template disponible</p>
        </div>
      )}
    </div>
  );
};

export default TemplateSelector;