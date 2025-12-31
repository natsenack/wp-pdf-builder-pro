import React from 'react';
import TemplateSelector from '@/ts/components/TemplateSelector';

/**
 * Main PDF Builder component
 */
const PDFBuilder: React.FC = () => {
  return (
    <div className="pdf-builder-container">
      <div className="pdf-builder-header">
        <h1>PDF Builder Pro</h1>
        <p>Créez vos templates PDF personnalisés</p>
      </div>

      <div className="pdf-builder-content">
        <TemplateSelector
          onTemplateSelect={(template) => {
            console.log('Template selected:', template);
            // TODO: Implement template selection logic
          }}
        />
      </div>
    </div>
  );
};

export default PDFBuilder;