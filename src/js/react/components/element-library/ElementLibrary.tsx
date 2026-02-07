import { DragEvent } from "react";
import { useIsMobile, useIsTablet } from "../../hooks/useResponsive";
import { ResponsiveContainer } from "../ui/Responsive";

// Définition des éléments WooCommerce (migration depuis l'ancien éditeur)
const WOOCOMMERCE_ELEMENTS = [
  {
    type: "product_table",
    label: "Tableau Produits",
    icon: "📋",
    description: "Tableau des produits commandés avec quantités et prix",
    category: "woocommerce",
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
      showShipping: false,
      showTax: false,
      showGlobalDiscount: false,
      fontSize: 11,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      currency: "€",
      tableStyle: "default",
      textAlign: "left",
      verticalAlign: "top",
      // ===== STYLES GLOBAUX =====
      globalFontSize: 11,
      globalFontFamily: "Arial",
      globalFontWeight: "normal",
      globalFontStyle: "normal",
      // ===== STYLES ENTÊTE =====
      headerFontSize: 12,
      headerFontFamily: "Arial",
      headerFontWeight: "bold",
      headerFontStyle: "normal",
      // ===== STYLES LIGNES =====
      rowFontSize: 11,
      rowFontFamily: "Arial",
      rowFontWeight: "normal",
      rowFontStyle: "normal",
      rowTextColor: "#374151",
      // ===== STYLES TOTAUX =====
      totalFontSize: 12,
      totalFontFamily: "Arial",
      totalFontWeight: "bold",
      totalFontStyle: "normal",
      totalTextColor: "#111827",
      backgroundColor: "#ffffff",
      headerBackgroundColor: "#f9fafb",
      headerTextColor: "#111827",
      alternateRowColor: "#f9fafb",
      borderColor: "#e5e7eb",
      borderWidth: 1,
      textColor: "#374151",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "customer_info",
    label: "Fiche Client",
    icon: "👤",
    description: "Informations détaillées du client (nom, adresse, email)",
    category: "woocommerce",
    defaultProps: {
      x: 50,
      y: 220,
      width: 250,
      height: 120,
      showHeaders: true,
      showBackground: true,
      showBorders: false,
      showName: true,
      showFullName: true,
      showAddress: true,
      showEmail: true,
      showPhone: true,
      showPaymentMethod: false,
      showTransactionId: false,
      layout: "vertical",
      // ===== STYLES ENTÊTE =====
      headerFontSize: 12,
      headerFontFamily: "Arial",
      headerFontWeight: "bold",
      headerFontStyle: "normal",
      // ===== STYLES CORPS =====
      bodyFontSize: 11,
      bodyFontFamily: "Arial",
      bodyFontWeight: "normal",
      bodyFontStyle: "normal",
      fontSize: 12,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      backgroundColor: "#e5e7eb",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#374151",
      headerTextColor: "#111827",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "company_info",
    label: "Informations Entreprise",
    icon: "[D]",
    description: "Nom, adresse, contact et TVA de l'entreprise",
    category: "woocommerce",
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
      layout: "vertical",
      fontSize: 12,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      headerFontStyle: "normal",
      bodyFontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      backgroundColor: "#e5e7eb",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#374151",
      headerTextColor: "#111827",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      showBackground: true,
      visible: true,
    },
  },
  {
    type: "company_logo",
    label: "Logo Entreprise",
    icon: "🏢",
    description: "Logo et identité visuelle de l'entreprise",
    category: "woocommerce",
    defaultProps: {
      x: 350,
      y: 50,
      width: 150,
      height: 80,
      fit: "contain",
      objectFit: "contain",
      alignment: "left",
      src: "", // URL du logo à définir
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 1,
      borderRadius: 0,
      opacity: 1,
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "order_number",
    label: "Numéro de Commande",
    icon: "🔢",
    description: "Référence de commande avec date",
    category: "woocommerce",
    defaultProps: {
      x: 350,
      y: 130,
      width: 100,
      height: 30,
      // ===== OPTIONS D'AFFICHAGE =====
      showHeaders: true,
      showBackground: false,
      showBorders: false,
      showLabel: true,
      showDate: false,
      labelText: "Commande #",
      labelPosition: "before",
      dateFormat: "DD/MM/YYYY",
      // ===== STYLES TEXTE =====
      fontSize: 14,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      textAlign: "right",
      // ===== STYLES ENTÊTE =====
      headerFontSize: 12,
      headerFontFamily: "Arial",
      headerFontWeight: "bold",
      headerFontStyle: "normal",
      headerTextColor: "#111827",
      // ===== STYLES NUMÉRO =====
      numberFontSize: 14,
      // ===== STYLES DATE =====
      dateFontSize: 12,
      // ===== STYLES CORPS =====
      bodyFontFamily: "Arial",
      bodyFontWeight: "normal",
      bodyFontStyle: "normal",
      // ===== APPARENCE =====
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#374151",
      color: "#374151",
      contentAlign: "left",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
      format: "CMD-{order_number}",
    },
  },
  {
    type: "woocommerce_order_date",
    label: "Date de Commande",
    icon: "📅",
    description: "Date de création de la commande",
    category: "woocommerce",
    defaultProps: {
      x: 350,
      y: 160,
      width: 100,
      height: 30,
      fontSize: 12,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      textAlign: "right",
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#374151",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "woocommerce_invoice_number",
    label: "Numéro de Facture",
    icon: "📄",
    description: "Numéro de facture généré",
    category: "woocommerce",
    defaultProps: {
      x: 350,
      y: 190,
      width: 100,
      height: 30,
      fontSize: 12,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      textAlign: "right",
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#374151",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "document_type",
    label: "Type de Document",
    icon: "📄",
    description: "Type du document (Facture, Devis, Bon de commande, etc.)",
    category: "woocommerce",
    defaultProps: {
      x: 50,
      y: 430,
      width: 150,
      height: 40,
      title: "FACTURE",
      fontSize: 18,
      fontFamily: "Arial",
      fontWeight: "bold",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      textAlign: "left",
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      textColor: "#111827",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "dynamic_text",
    label: "Texte Dynamique",
    icon: "📝",
    description: "Texte avec variables dynamiques",
    category: "woocommerce",
    defaultProps: {
      x: 50,
      y: 550,
      width: 200,
      height: 40,
      text: "Texte personnalisable",
      textTemplate: "custom",
      autoWrap: true,
      theme: "clean",
      fontSize: 14,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.3",
      backgroundColor: "transparent",
      rotation: 0,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
    },
  },
  {
    type: "mentions",
    label: "Mentions légales",
    icon: "📄",
    description: "Informations légales (email, SIRET, téléphone, etc.)",
    category: "woocommerce",
    defaultProps: {
      x: 50,
      y: 480,
      width: 500,
      height: 60,
      // ===== OPTIONS DE CONTENU =====
      showEmail: true,
      showPhone: true,
      showSiret: true,
      showVat: true,
      separator: " • ",
      text: "", // Contenu personnalisé (vide par défaut = génération automatique)
      mentionType: "dynamic", // Type: "dynamic", "custom", "medley"
      selectedMentions: [], // Pour type "medley" - liste des mentions sélectionnées
      medleySeparator: "\n\n", // Séparateur entre mentions pour medley
      
      // ===== OPTIONS VISUELLES =====
      showSeparator: true, // Affiche la barre horizontale
      separatorStyle: "solid", // Style: "solid" ou "dashed"
      showBackground: false,
      
      // ===== STYLES TEXTE =====
      fontSize: 10,
      fontFamily: "Arial",
      fontWeight: "normal",
      fontStyle: "normal",
      textDecoration: "none",
      textTransform: "none",
      letterSpacing: "normal",
      wordSpacing: "normal",
      lineHeight: "1.2",
      textAlign: "left",
      textColor: "#6b7280",
      
      // ===== STYLES FOND & BORDURES =====
      backgroundColor: "transparent",
      borderColor: "#e5e7eb",
      borderWidth: 0,
      borderStyle: "solid",
      borderRadius: 0,
      
      // ===== EFFETS & VISIBILITÉ =====
      rotation: 0,
      opacity: 100,
      shadowColor: "#000000",
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      shadowBlur: 0,
      visible: true,
      locked: false,
    },
  },
];

interface ElementLibraryProps {
  onElementSelect?: (elementType: string) => void;
  className?: string;
}

export function ElementLibrary({
  onElementSelect,
  className,
}: ElementLibraryProps) {
  const isMobile = useIsMobile();
  const isTablet = useIsTablet();

  const handleElementClick = (elementType: string) => {
    if (onElementSelect) {
      onElementSelect(elementType);
    }
  };

  const handleDragStart = (e: DragEvent, element: Record<string, unknown>) => {
    // Stocker les données de l'élément dans le transfert
    const dragData = {
      type: element.type,
      label: element.label,
      defaultProps: element.defaultProps,
    };
    const jsonData = JSON.stringify(dragData);
    
    e.dataTransfer?.setData("application/json", jsonData);
    if (e.dataTransfer) {
      e.dataTransfer.effectAllowed = "copy";
    }
  };

  const handleDragEnd = (_e: DragEvent) => {
    // Drag terminé
    
  };

  return (
    <ResponsiveContainer
      className={`pdf-element-library ${className || ""}`}
      mobileClass="element-library-mobile"
      tabletClass="element-library-tablet"
      desktopClass="element-library-desktop"
    >
      <div
        style={{
          width: isMobile ? "100%" : isTablet ? "240px" : "280px",
          height: "100%",
          backgroundColor: "#f8f9fa",
          borderRight: isMobile ? "none" : "1px solid #e9ecef",
          borderBottom: isMobile ? "1px solid #e9ecef" : "none",
          display: "flex",
          flexDirection: "column",
          overflow: "hidden",
        }}
      >
        {/* Header de la sidebar */}
        <div
          style={{
            padding: isMobile ? "12px" : "16px",
            borderBottom: "1px solid #e9ecef",
            backgroundColor: "#ffffff",
          }}
        >
          <h3
            style={{
              margin: 0,
              fontSize: isMobile ? "14px" : "16px",
              fontWeight: "600",
              color: "#495057",
            }}
          >
            📦 Éléments WooCommerce
          </h3>
          <p
            style={{
              margin: "4px 0 0 0",
              fontSize: isMobile ? "11px" : "12px",
              color: "#6c757d",
              display: isMobile ? "none" : "block",
            }}
          >
            Glissez les éléments sur le canvas
          </p>
        </div>

        {/* Liste des éléments */}
        <div
          style={{
            flex: 1,
            overflowY: "auto",
            padding: isMobile ? "12px" : "8px",
          }}
        >
          <div
            style={{
              display: "grid",
              gap: "8px",
            }}
          >
            {WOOCOMMERCE_ELEMENTS.map((element) => (
              <div
                key={element.type}
                draggable
                onClick={() => handleElementClick(element.type)}
                onDragStart={(e) => handleDragStart(e, element)}
                onDragEnd={handleDragEnd}
                style={{
                  padding: "12px",
                  backgroundColor: "#ffffff",
                  border: "1px solid #dee2e6",
                  borderRadius: "6px",
                  cursor: "grab",
                  transition: "all 0.2s ease",
                  display: "flex",
                  alignItems: "center",
                  gap: "12px",
                  userSelect: "none",
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.borderColor = "#007acc";
                  e.currentTarget.style.boxShadow =
                    "0 2px 4px rgba(0, 122, 204, 0.1)";
                  e.currentTarget.style.cursor = "grabbing";
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.borderColor = "#dee2e6";
                  e.currentTarget.style.boxShadow = "none";
                  e.currentTarget.style.cursor = "grab";
                }}
              >
                {/* Icône */}
                <div
                  style={{
                    fontSize: "20px",
                    width: "32px",
                    height: "32px",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    backgroundColor: "#f8f9fa",
                    borderRadius: "4px",
                  }}
                >
                  {element.icon}
                </div>

                {/* Contenu */}
                <div style={{ flex: 1 }}>
                  <div
                    style={{
                      fontSize: "14px",
                      fontWeight: "500",
                      color: "#495057",
                      marginBottom: "2px",
                    }}
                  >
                    {element.label}
                  </div>
                  <div
                    style={{
                      fontSize: "12px",
                      color: "#6c757d",
                      lineHeight: "1.3",
                    }}
                  >
                    {element.description}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Footer avec info */}
        <div
          style={{
            padding: "12px 16px",
            borderTop: "1px solid #e9ecef",
            backgroundColor: "#ffffff",
            fontSize: "11px",
            color: "#6c757d",
            textAlign: "center",
          }}
        >
          Cliquez sur un élément pour l&apos;ajouter
        </div>
      </div>
    </ResponsiveContainer>
  );
}


