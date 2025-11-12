import React from 'react';

// Définition des éléments WooCommerce (migration depuis l'ancien éditeur)
const WOOCOMMERCE_ELEMENTS = [
  {
    type: 'product_table',
    label: 'Tableau Produits',
    icon: '📋',
    description: 'Tableau des produits commandés avec quantités et prix',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 50,
      width: 500,
      height: 200,
      showHeaders: true,
      showBorders: true,
      showAlternatingRows: true,
      showSku: false,
      showDescription: false,
      showQuantity: true,
      fontSize: 11,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      currency: '€',
      tableStyle: 'default',
      textAlign: 'left',
      verticalAlign: 'top',
      backgroundColor: '#ffffff',
      headerBackgroundColor: '#f9fafb',
      headerTextColor: '#111827',
      alternateRowColor: '#f9fafb',
      borderColor: '#e5e7eb',
      borderWidth: 1,
      textColor: '#374151',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'customer_info',
    label: 'Fiche Client',
    icon: '👤',
    description: 'Informations détaillées du client (nom, adresse, email)',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 220,
      width: 250,
      height: 120,
      showHeaders: true,
      showBorders: false,
      showFullName: true,
      showAddress: true,
      showEmail: true,
      showPhone: true,
      showPaymentMethod: false,
      showTransactionId: false,
      showCompanyName: false,
      showVatNumber: false,
      showCompanyAddress: false,
      layout: 'vertical',
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      headerFontStyle: 'normal',
      bodyFontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#374151',
      headerTextColor: '#111827',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'company_info',
    label: 'Informations Entreprise',
    icon: '[D]',
    description: 'Nom, adresse, contact et TVA de l\'entreprise',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 340,
      width: 250,
      height: 120,
      showBorders: false,
      showFullName: true,
      showAddress: true,
      showEmail: true,
      showPhone: true,
      showSiret: true,
      showVat: true,
      showRcs: true,
      showCapital: true,
      showCompanyName: true,
      layout: 'vertical',
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      headerFontStyle: 'normal',
      bodyFontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#374151',
      headerTextColor: '#111827',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'company_logo',
    label: 'Logo Entreprise',
    icon: '🏢',
    description: 'Logo et identité visuelle de l\'entreprise',
    category: 'woocommerce',
    defaultProps: {
      x: 350,
      y: 50,
      width: 150,
      height: 80,
      fit: 'contain',
      objectFit: 'contain',
      alignment: 'left',
      src: '', // URL du logo à définir
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 1,
      borderRadius: 0,
      opacity: 1,
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'order_number',
    label: 'Numéro de Commande',
    icon: '🔢',
    description: 'Référence de commande avec date',
    category: 'woocommerce',
    defaultProps: {
      x: 350,
      y: 130,
      width: 100,
      height: 30,
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      textAlign: 'right',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#374151',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'woocommerce-order-date',
    label: 'Date de Commande',
    icon: '📅',
    description: 'Date de création de la commande',
    category: 'woocommerce',
    defaultProps: {
      x: 350,
      y: 160,
      width: 100,
      height: 30,
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      textAlign: 'right',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#374151',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'woocommerce-invoice-number',
    label: 'Numéro de Facture',
    icon: '📄',
    description: 'Numéro de facture généré',
    category: 'woocommerce',
    defaultProps: {
      x: 350,
      y: 190,
      width: 100,
      height: 30,
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      textAlign: 'right',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#374151',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'document_type',
    label: 'Type de Document',
    icon: '📄',
    description: 'Type du document (Facture, Devis, Bon de commande, etc.)',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 430,
      width: 150,
      height: 40,
      title: 'FACTURE',
      fontSize: 18,
      fontFamily: 'Arial',
      fontWeight: 'bold',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      textAlign: 'left',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#111827',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'dynamic-text',
    label: 'Texte Dynamique',
    icon: '📝',
    description: 'Texte avec variables dynamiques',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 550,
      width: 200,
      height: 40,
      text: 'Texte personnalisable',
      textTemplate: 'custom',
      autoWrap: true,
      theme: 'clean',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.3',
      backgroundColor: 'transparent',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  },
  {
    type: 'mentions',
    label: 'Mentions légales',
    icon: '📄',
    description: 'Informations légales (email, SIRET, téléphone, etc.)',
    category: 'woocommerce',
    defaultProps: {
      x: 50,
      y: 480,
      width: 500,
      height: 60,
      showEmail: true,
      showPhone: true,
      showSiret: true,
      showVat: true,
      separator: ' • ',
      fontSize: 10,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textTransform: 'none',
      letterSpacing: 'normal',
      wordSpacing: 'normal',
      lineHeight: '1.2',
      textAlign: 'left',
      backgroundColor: 'transparent',
      borderColor: '#e5e7eb',
      borderWidth: 0,
      textColor: '#6b7280',
      rotation: 0,
      shadowColor: '#000000',
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true
    }
  }
];

interface ElementLibraryProps {
  onElementSelect?: (elementType: string) => void;
  className?: string;
}

export function ElementLibrary({ onElementSelect, className }: ElementLibraryProps) {
  const handleElementClick = (elementType: string) => {
    if (onElementSelect) {
      onElementSelect(elementType);
    }
  };

  const handleDragStart = (e: React.DragEvent, element: Record<string, unknown>) => {
    // Stocker les données de l'élément dans le transfert
    e.dataTransfer.setData('application/json', JSON.stringify({
      type: element.type,
      label: element.label,
      defaultProps: element.defaultProps
    }));
    e.dataTransfer.effectAllowed = 'copy';
  };

  const handleDragEnd = (_e: React.DragEvent) => {
    // Drag terminé
  };

  return (
    <div className={`pdf-element-library ${className || ''}`} style={{
      width: '280px',
      height: '100%',
      backgroundColor: '#f8f9fa',
      borderRight: '1px solid #e9ecef',
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden'
    }}>
      {/* Header de la sidebar */}
      <div style={{
        padding: '16px',
        borderBottom: '1px solid #e9ecef',
        backgroundColor: '#ffffff'
      }}>
        <h3 style={{
          margin: 0,
          fontSize: '16px',
          fontWeight: '600',
          color: '#495057'
        }}>
          📦 Éléments WooCommerce
        </h3>
        <p style={{
          margin: '4px 0 0 0',
          fontSize: '12px',
          color: '#6c757d'
        }}>
          Glissez les éléments sur le canvas
        </p>
      </div>

      {/* Liste des éléments */}
      <div style={{
        flex: 1,
        overflowY: 'auto',
        padding: '8px'
      }}>
        <div style={{
          display: 'grid',
          gap: '8px'
        }}>
          {WOOCOMMERCE_ELEMENTS.map((element) => (
            <div
              key={element.type}
              draggable
              onClick={() => handleElementClick(element.type)}
              onDragStart={(e) => handleDragStart(e, element)}
              onDragEnd={handleDragEnd}
              style={{
                padding: '12px',
                backgroundColor: '#ffffff',
                border: '1px solid #dee2e6',
                borderRadius: '6px',
                cursor: 'grab',
                transition: 'all 0.2s ease',
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                userSelect: 'none'
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.borderColor = '#007acc';
                e.currentTarget.style.boxShadow = '0 2px 4px rgba(0, 122, 204, 0.1)';
                e.currentTarget.style.cursor = 'grabbing';
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.borderColor = '#dee2e6';
                e.currentTarget.style.boxShadow = 'none';
                e.currentTarget.style.cursor = 'grab';
              }}
            >
              {/* Icône */}
              <div style={{
                fontSize: '20px',
                width: '32px',
                height: '32px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                backgroundColor: '#f8f9fa',
                borderRadius: '4px'
              }}>
                {element.icon}
              </div>

              {/* Contenu */}
              <div style={{ flex: 1 }}>
                <div style={{
                  fontSize: '14px',
                  fontWeight: '500',
                  color: '#495057',
                  marginBottom: '2px'
                }}>
                  {element.label}
                </div>
                <div style={{
                  fontSize: '12px',
                  color: '#6c757d',
                  lineHeight: '1.3'
                }}>
                  {element.description}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Footer avec info */}
      <div style={{
        padding: '12px 16px',
        borderTop: '1px solid #e9ecef',
        backgroundColor: '#ffffff',
        fontSize: '11px',
        color: '#6c757d',
        textAlign: 'center'
      }}>
        Cliquez sur un élément pour l&apos;ajouter
      </div>
    </div>
  );
}
