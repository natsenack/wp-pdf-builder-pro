import React, { useState, useMemo, useCallback, memo } from "react";
import { useBuilder } from "../../contexts/builder/BuilderContext";
import { useIsMobile, useIsTablet } from "../../hooks/useResponsive";
import { ResponsiveContainer } from "../ui/Responsive";
import { NumericPropertyInput } from "../ui/NumericPropertyInput";
import { ProductTableProperties } from "./ProductTableProperties";
import { CustomerInfoProperties } from "./CustomerInfoProperties";
import { CompanyInfoProperties } from "./CompanyInfoProperties";
import { CompanyLogoProperties } from "./CompanyLogoProperties";
import { WoocommerceOrderDateProperties } from "./WoocommerceOrderDateProperties";
import { WoocommerceInvoiceNumberProperties } from "./WoocommerceInvoiceNumberProperties";
import { DynamicTextProperties } from "./DynamicTextProperties";
import { MentionsProperties } from "./MentionsProperties";
import { DocumentTypeProperties } from "./DocumentTypeProperties";
import { TextProperties } from "./TextProperties";
import { ShapeProperties } from "./ShapeProperties";
import { ImageProperties } from "./ImageProperties";
import { LineProperties } from "./LineProperties";
import { ElementProperties } from "./ElementProperties";

interface PropertiesPanelProps {
  className?: string;
}

// Éléments qui ont leurs propres composants de propriétés spécialisés
const ELEMENTS_WITH_CUSTOM_PROPERTIES = new Set([
  "product_table",
  "customer_info",
  "company_info",
  "company_logo",
  "woocommerce_order_date",
  "woocommerce_invoice_number",
  "document_type",
  "dynamic_text",
  "mentions",
  "text",
  "rectangle",
  "circle",
  "image",
  "line",
]);

// Mapping des types d'éléments vers leurs composants de propriétés
const ELEMENT_PROPERTY_COMPONENTS: Record<
  string,
  React.ComponentType<{
    element: any;
    onChange: (elementId: string, property: string, value: unknown) => void;
    activeTab: {
      [key: string]: "fonctionnalites" | "personnalisation" | "positionnement";
    };
    setActiveTab: (tabs: {
      [key: string]: "fonctionnalites" | "personnalisation" | "positionnement";
    }) => void;
  }>
> = {
  product_table: ProductTableProperties,
  customer_info: CustomerInfoProperties,
  company_info: CompanyInfoProperties,
  company_logo: CompanyLogoProperties,
  woocommerce_order_date: WoocommerceOrderDateProperties,
  woocommerce_invoice_number: WoocommerceInvoiceNumberProperties,
  document_type: DocumentTypeProperties,
  dynamic_text: DynamicTextProperties,
  mentions: MentionsProperties,
  text: TextProperties,
  rectangle: ShapeProperties,
  circle: ShapeProperties,
  image: ImageProperties,
  line: LineProperties,
};

export const PropertiesPanel = memo(function PropertiesPanel({
  className,
}: PropertiesPanelProps) {
  const { state, updateElement, removeElement } = useBuilder();
  const [activeTab, setActiveTab] = useState<{
    [key: string]: "fonctionnalites" | "personnalisation" | "positionnement";
  }>({});

  const isMobile = useIsMobile();
  const isTablet = useIsTablet();

  // Optimisation: mémoriser les éléments sélectionnés
  const selectedElements = useMemo(() => {
    const selected = state.elements.filter((el) =>
      state.selection.selectedElements.includes(el.id),
    );
    console.log(
      "[PropertiesPanel] selectedElements recalculated:",
      selected.map((el) => ({
        id: el.id,
        type: el.type,
        layout: (el as any).layout,
        textAlign: (el as any).textAlign,
      })),
    );
    return selected;
  }, [state.elements, state.selection.selectedElements]);

  // Optimisation: mémoriser les handlers
  const handlePropertyChange = useCallback(
    (elementId: string, property: string, value: unknown) => {
      console.log("[PropertiesPanel] handlePropertyChange called:", {
        elementId,
        property,
        value,
      });
      updateElement(elementId, { [property]: value });
    },
    [updateElement],
  );

  const handleDeleteSelected = useCallback(() => {
    state.selection.selectedElements.forEach((id) => {
      removeElement(id);
    });
  }, [state.selection.selectedElements, removeElement]);

  if (selectedElements.length === 0) {
    return (
      <ResponsiveContainer
        className={`pdf-builder-properties ${className || ""}`}
        mobileClass="properties-panel-mobile"
        tabletClass="properties-panel-tablet"
        desktopClass="properties-panel-desktop"
      >
        <div
          onWheel={(e) => e.stopPropagation()}
          style={{
            padding: isMobile ? "8px" : "12px",
            backgroundColor: "#f9f9f9",
            border: "1px solid #ddd",
            borderRadius: "4px",
            minHeight: isMobile ? "150px" : "200px",
            maxHeight: isMobile ? "calc(50vh - 16px)" : "calc(100vh - 32px)",
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            textAlign: "center",
          }}
        >
          <div
            style={{
              fontSize: isMobile ? "24px" : "32px",
              marginBottom: "8px",
              opacity: 0.5,
            }}
          >
            🎯
          </div>
          <div
            style={{
              fontSize: isMobile ? "12px" : "14px",
              color: "#666",
              fontWeight: "500",
            }}
          >
            {isMobile
              ? "Sélectionnez un élément"
              : "Sélectionnez un élément pour modifier ses propriétés"}
          </div>
          <div
            style={{
              fontSize: isMobile ? "10px" : "12px",
              color: "#999",
              marginTop: "4px",
              display: isMobile ? "none" : "block",
            }}
          >
            Cliquez sur un élément du canvas pour commencer
          </div>
        </div>
      </ResponsiveContainer>
    );
  }

  return (
    <div
      className={`pdf-builder-properties ${className || ""}`}
      onWheel={(e) => e.stopPropagation()}
      style={{
        padding: "12px",
        backgroundColor: "#f9f9f9",
        border: "1px solid #ddd",
        borderRadius: "4px",
        maxHeight: "calc(100vh - 32px)",
        overflowY: "auto",
      }}
    >
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          marginBottom: "12px",
        }}
      >
        <h4 style={{ margin: "0", fontSize: "14px", fontWeight: "bold" }}>
          Propriétés ({selectedElements.length})
        </h4>
        <div style={{ display: "flex", gap: "4px" }}>
          <button
            onClick={handleDeleteSelected}
            style={{
              padding: "4px 8px",
              border: "1px solid #dc3545",
              borderRadius: "4px",
              backgroundColor: "#dc3545",
              color: "#ffffff",
              cursor: "pointer",
              fontSize: "12px",
            }}
          >
            🗑️ Supprimer
          </button>
        </div>
      </div>

      {selectedElements.map((element) => (
        <div
          key={element.id}
          onWheel={(e) => e.stopPropagation()}
          style={{
            marginBottom: "16px",
            padding: "12px",
            backgroundColor: "#ffffff",
            border: "1px solid #e0e0e0",
            borderRadius: "4px",
            maxHeight: "calc(100vh - 120px)",
            overflowY: "auto",
          }}
        >
          <h5
            style={{
              margin: "0 0 8px 0",
              fontSize: "13px",
              fontWeight: "bold",
            }}
          >
            {element.type} - {element.id.slice(0, 8)}
          </h5>

          {/* Propriétés communes - masquées pour les éléments qui ont leurs propres onglets */}
          {!ELEMENTS_WITH_CUSTOM_PROPERTIES.has(element.type) && (
            <div style={{ display: "grid", gap: "8px" }}>
              <NumericPropertyInput
                label="Position X"
                value={element.x}
                defaultValue={0}
                unit="px"
                onChange={(value) =>
                  handlePropertyChange(element.id, "x", value)
                }
              />

              <NumericPropertyInput
                label="Position Y"
                value={element.y}
                defaultValue={0}
                unit="px"
                onChange={(value) =>
                  handlePropertyChange(element.id, "y", value)
                }
              />

              <NumericPropertyInput
                label="Largeur"
                value={element.width}
                defaultValue={100}
                min={1}
                unit="px"
                onChange={(value) =>
                  handlePropertyChange(element.id, "width", value)
                }
              />

              <NumericPropertyInput
                label="Hauteur"
                value={element.height}
                defaultValue={50}
                min={1}
                unit="px"
                onChange={(value) =>
                  handlePropertyChange(element.id, "height", value)
                }
              />

              <NumericPropertyInput
                label="Rotation"
                value={element.rotation}
                defaultValue={0}
                min={-180}
                max={180}
                unit="°"
                onChange={(value) =>
                  handlePropertyChange(element.id, "rotation", value)
                }
              />

              <div>
                <label
                  style={{
                    display: "block",
                    fontSize: "12px",
                    fontWeight: "bold",
                    marginBottom: "4px",
                  }}
                >
                  Opacité
                </label>
                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.1"
                  value={element.opacity || 1}
                  onChange={(e) =>
                    handlePropertyChange(
                      element.id,
                      "opacity",
                      parseFloat(e.target.value),
                    )
                  }
                  style={{ width: "100%" }}
                />
                <span style={{ fontSize: "11px", color: "#666" }}>
                  {Math.round((element.opacity || 1) * 100)}%
                </span>
              </div>
            </div>
          )}

          {/* Composant de propriétés spécifique selon le type d'élément */}
          {(() => {
            const PropertyComponent = ELEMENT_PROPERTY_COMPONENTS[element.type];
            if (PropertyComponent) {
              return (
                <PropertyComponent
                  element={element}
                  onChange={handlePropertyChange}
                  activeTab={activeTab}
                  setActiveTab={setActiveTab}
                />
              );
            }
            return null;
          })()}
          {!ELEMENTS_WITH_CUSTOM_PROPERTIES.has(element.type) && (
            <ElementProperties
              element={element}
              onChange={handlePropertyChange}
            />
          )}
        </div>
      ))}
    </div>
  );
});
