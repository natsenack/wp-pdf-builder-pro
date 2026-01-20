import { useState, ReactNode } from 'react';
import { WoocommerceInvoiceNumberElement } from '../../types/elements';

// Composant Accordion personnalisé
const Accordion = ({ title, children, defaultOpen = false }: {
  title: string;
  children: ReactNode;
  defaultOpen?: boolean;
}) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);

  return (
    <div style={{ marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }}>
      <div
        onClick={() => setIsOpen(!isOpen)}
        style={{
          padding: '12px',
          backgroundColor: '#f8f9fa',
          cursor: 'pointer',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: isOpen ? '1px solid #e9ecef' : 'none'
        }}
      >
        <h4 style={{ margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
          {title}
        </h4>
        <span style={{
          fontSize: '12px',
          color: '#6c757d',
          transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
          transition: 'transform 0.2s ease'
        }}>
          ▼
        </span>
      </div>

      {isOpen && (
        <div style={{ padding: '12px', backgroundColor: '#ffffff' }}>
          {children}
        </div>
      )}
    </div>
  );
};

// Composant Toggle personnalisé
const Toggle = ({ checked, onChange, label, description }: {
  checked: boolean;
  onChange: (value: boolean) => void;
  label: string;
  description?: string;
}) => {
  return (
    <div style={{ marginBottom: '12px', display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
        style={{
          width: '18px',
          height: '18px',
          cursor: 'pointer',
          marginTop: '2px',
          accentColor: '#007bff'
        }}
      />
      <div style={{ flex: 1 }}>
        <label style={{ fontSize: '13px', fontWeight: '500', color: '#495057', cursor: 'pointer' }}>
          {label}
        </label>
        {description && (
          <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '4px' }}>
            {description}
          </div>
        )}
      </div>
    </div>
  );
};

// Composant Select personnalisé
const Select = ({ value, onChange, label, options, description }: {
  value: string;
  onChange: (value: string) => void;
  label: string;
  options: { label: string; value: string }[];
  description?: string;
}) => {
  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{ fontSize: '13px', fontWeight: '500', color: '#495057', display: 'block', marginBottom: '6px' }}>
        {label}
      </label>
      <select
        value={value}
        onChange={(e) => onChange(e.target.value)}
        style={{
          width: '100%',
          padding: '6px 8px',
          border: '1px solid #dee2e6',
          borderRadius: '4px',
          fontSize: '13px',
          color: '#495057',
          backgroundColor: '#ffffff',
          cursor: 'pointer'
        }}
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
      {description && (
        <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '4px' }}>
          {description}
        </div>
      )}
    </div>
  );
};

// Composant Input personnalisé
const Input = ({ value, onChange, label, type = 'text', description }: {
  value: string | number;
  onChange: (value: string | number) => void;
  label: string;
  type?: string;
  description?: string;
}) => {
  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{ fontSize: '13px', fontWeight: '500', color: '#495057', display: 'block', marginBottom: '6px' }}>
        {label}
      </label>
      <input
        type={type}
        value={value}
        onChange={(e) => onChange(type === 'number' ? parseInt(e.target.value) || 0 : e.target.value)}
        style={{
          width: '100%',
          padding: '6px 8px',
          border: '1px solid #dee2e6',
          borderRadius: '4px',
          fontSize: '13px',
          color: '#495057',
          boxSizing: 'border-box'
        }}
      />
      {description && (
        <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '4px' }}>
          {description}
        </div>
      )}
    </div>
  );
};

// Composant ColorPicker personnalisé
const ColorPicker = ({ value, onChange, label, description }: {
  value: string;
  onChange: (value: string) => void;
  label: string;
  description?: string;
}) => {
  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{ fontSize: '13px', fontWeight: '500', color: '#495057', display: 'block', marginBottom: '6px' }}>
        {label}
      </label>
      <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
        <input
          type="color"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          style={{
            width: '40px',
            height: '32px',
            border: '1px solid #dee2e6',
            borderRadius: '4px',
            cursor: 'pointer',
            padding: '2px'
          }}
        />
        <input
          type="text"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          style={{
            flex: 1,
            padding: '6px 8px',
            border: '1px solid #dee2e6',
            borderRadius: '4px',
            fontSize: '13px',
            color: '#495057'
          }}
        />
      </div>
      {description && (
        <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '4px' }}>
          {description}
        </div>
      )}
    </div>
  );
};

interface WoocommerceInvoiceNumberPropertiesProps {
  element: WoocommerceInvoiceNumberElement;
  onUpdate: (element: WoocommerceInvoiceNumberElement) => void;
}

export const WoocommerceInvoiceNumberProperties = ({
  element,
  onUpdate,
}: WoocommerceInvoiceNumberPropertiesProps) => {
  const [activeTab, setActiveTab] = useState<'features' | 'styling' | 'position'>('features');

  const handlePrefixChange = (prefix: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        prefix,
      },
    });
  };

  const handleSuffixChange = (suffix: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        suffix,
      },
    });
  };

  const handleFontFamilyChange = (fontFamily: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        fontFamily,
      },
    });
  };

  const handleFontSizeChange = (fontSize: number) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        fontSize,
      },
    });
  };

  const handleColorChange = (color: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        color,
      },
    });
  };

  const handleFontWeightChange = (fontWeight: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        fontWeight,
      },
    });
  };

  const handleFontStyleChange = (fontStyle: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        fontStyle,
      },
    });
  };

  const handleTextAlignChange = (textAlign: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        textAlign,
      },
    });
  };

  const handlePaddingChange = (field: 'top' | 'right' | 'bottom' | 'left', value: number) => {
    const currentPadding = element.properties?.padding || { top: 0, right: 0, bottom: 0, left: 0 };
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        padding: {
          ...currentPadding,
          [field]: value,
        },
      },
    });
  };

  const handleBorderChange = (field: keyof any, value: any) => {
    const currentBorder = element.properties?.border || { width: 0, style: 'solid', color: '#000000' };
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        border: {
          ...currentBorder,
          [field]: value,
        },
      },
    });
  };

  const handleBackgroundColorChange = (backgroundColor: string) => {
    onUpdate({
      ...element,
      properties: {
        ...element.properties,
        backgroundColor,
      },
    });
  };

  const tabStyle = {
    padding: '10px 15px',
    cursor: 'pointer',
    borderBottom: '2px solid transparent',
    fontSize: '13px',
    fontWeight: '500' as const,
    color: '#6c757d',
    transition: 'all 0.2s ease',
  };

  const activeTabStyle = {
    ...tabStyle,
    color: '#007bff',
    borderBottomColor: '#007bff',
  };

  return (
    <div style={{ padding: '10px 0' }}>
      <div style={{ display: 'flex', borderBottom: '1px solid #dee2e6', marginBottom: '16px' }}>
        <button
          onClick={() => setActiveTab('features')}
          style={activeTab === 'features' ? activeTabStyle : tabStyle}
        >
          Fonctionnalités
        </button>
        <button
          onClick={() => setActiveTab('styling')}
          style={activeTab === 'styling' ? activeTabStyle : tabStyle}
        >
          Personnalisation
        </button>
        <button
          onClick={() => setActiveTab('position')}
          style={activeTab === 'position' ? activeTabStyle : tabStyle}
        >
          Positionnement
        </button>
      </div>

      <div style={{ marginBottom: '16px' }}>
        {activeTab === 'features' && (
          <>
            <Accordion title="Format du numéro de facture" defaultOpen={true}>
              <Input
                label="Préfixe"
                value={element.properties?.prefix || ''}
                onChange={handlePrefixChange}
                description="Texte avant le numéro de facture"
              />
              <Input
                label="Suffixe"
                value={element.properties?.suffix || ''}
                onChange={handleSuffixChange}
                description="Texte après le numéro de facture"
              />
            </Accordion>
          </>
        )}

        {activeTab === 'styling' && (
          <>
            <Accordion title="Propriétés de texte générales" defaultOpen={true}>
              <Select
                label="Police"
                value={element.properties?.fontFamily || 'Arial'}
                onChange={handleFontFamilyChange}
                options={[
                  { label: 'Arial', value: 'Arial' },
                  { label: 'Helvetica', value: 'Helvetica' },
                  { label: 'Times New Roman', value: 'Times New Roman' },
                  { label: 'Courier New', value: 'Courier New' },
                  { label: 'Georgia', value: 'Georgia' },
                  { label: 'Verdana', value: 'Verdana' },
                ]}
              />
              <Input
                label="Taille"
                type="number"
                value={element.properties?.fontSize || 12}
                onChange={handleFontSizeChange}
                description="Taille de la police en pixels"
              />
              <ColorPicker
                label="Couleur"
                value={element.properties?.color || '#000000'}
                onChange={handleColorChange}
              />
              <Select
                label="Poids de la police"
                value={element.properties?.fontWeight || 'normal'}
                onChange={handleFontWeightChange}
                options={[
                  { label: 'Normal', value: 'normal' },
                  { label: 'Gras', value: 'bold' },
                ]}
              />
              <Select
                label="Style"
                value={element.properties?.fontStyle || 'normal'}
                onChange={handleFontStyleChange}
                options={[
                  { label: 'Normal', value: 'normal' },
                  { label: 'Italique', value: 'italic' },
                ]}
              />
              <Select
                label="Alignement"
                value={element.properties?.textAlign || 'left'}
                onChange={handleTextAlignChange}
                options={[
                  { label: 'Gauche', value: 'left' },
                  { label: 'Centre', value: 'center' },
                  { label: 'Droite', value: 'right' },
                  { label: 'Justifié', value: 'justify' },
                ]}
              />
            </Accordion>

            <Accordion title="Couleur de fond">
              <ColorPicker
                label="Couleur de fond"
                value={element.properties?.backgroundColor || '#ffffff'}
                onChange={handleBackgroundColorChange}
              />
            </Accordion>

            <Accordion title="Bordure">
              <Input
                label="Largeur"
                type="number"
                value={element.properties?.border?.width || 0}
                onChange={(value) => handleBorderChange('width', value)}
              />
              <Select
                label="Style"
                value={element.properties?.border?.style || 'solid'}
                onChange={(value) => handleBorderChange('style', value)}
                options={[
                  { label: 'Solide', value: 'solid' },
                  { label: 'Pointillé', value: 'dotted' },
                  { label: 'Tiré', value: 'dashed' },
                ]}
              />
              <ColorPicker
                label="Couleur"
                value={element.properties?.border?.color || '#000000'}
                onChange={(value) => handleBorderChange('color', value)}
              />
            </Accordion>
          </>
        )}

        {activeTab === 'position' && (
          <>
            <Accordion title="Remplissage">
              <Input
                label="Haut"
                type="number"
                value={element.properties?.padding?.top || 0}
                onChange={(value) => handlePaddingChange('top', value as number)}
              />
              <Input
                label="Droite"
                type="number"
                value={element.properties?.padding?.right || 0}
                onChange={(value) => handlePaddingChange('right', value as number)}
              />
              <Input
                label="Bas"
                type="number"
                value={element.properties?.padding?.bottom || 0}
                onChange={(value) => handlePaddingChange('bottom', value as number)}
              />
              <Input
                label="Gauche"
                type="number"
                value={element.properties?.padding?.left || 0}
                onChange={(value) => handlePaddingChange('left', value as number)}
              />
            </Accordion>
          </>
        )}
      </div>
    </div>
  );
};
