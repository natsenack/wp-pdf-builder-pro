import React, { useState, useCallback, useEffect } from "react";
import { Header } from "./header/Header";
import { Canvas } from "./canvas/Canvas";
import { Toolbar } from "./toolbar/Toolbar";
import { PropertiesPanel } from "./properties/PropertiesPanel";
import { ElementLibrary } from "./element-library/ElementLibrary";
import { EditorProvider } from "../contexts/EditorContext";
import "../styles/editor.css";

interface AppProps {
  title?: string;
}

/**
 * Main PDF Builder component
 * This is the root component that will be mounted in the WordPress admin page
 */
export const PDFBuilderApp: React.FC<AppProps> = ({
  title = "PDF Builder Pro",
}) => {
  const [selectedElement, setSelectedElement] = useState<string | null>(null);
  const [showProperties, setShowProperties] = useState(false);
  const [scale, setScale] = useState(100);
  const [templateData, setTemplateData] = useState<any>(null);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    // Component mounted successfully
    
    setIsReady(true);

    // Charger les données du template depuis WordPress
    if (window.pdfBuilderData?.templateId) {
      fetchTemplate(window.pdfBuilderData.templateId);
    }

    return () => {
      
    };
  }, []);

  const fetchTemplate = async (templateId: number) => {
    try {
      const response = await fetch(window.pdfBuilderData?.ajaxUrl || "", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "pdf_builder_load_template",
          nonce: window.pdfBuilderData?.nonce || "",
          template_id: templateId.toString(),
        }),
      });

      if (response.ok) {
        const data = await response.json();
        setTemplateData(data);
      }
    } catch (error) {
      
    }
  };

  const handleElementSelect = useCallback((elementId: string) => {
    setSelectedElement(elementId);
    setShowProperties(true);
  }, []);

  const handleZoomChange = useCallback((newScale: number) => {
    setScale(Math.max(25, Math.min(200, newScale)));
  }, []);

  if (!isReady) {
    return (
      <div className="pdfb-pdf-builder-loading">
        <div className="pdfb-spinner"></div>
        <p>Initialisation du PDF Builder...</p>
      </div>
    );
  }

  return (
    <EditorProvider>
      <div className="pdfb-pdf-builder-editor">
        <Header title={title} scale={scale} onZoomChange={handleZoomChange} />

        <div className="pdfb-pdf-builder-body">
          <ElementLibrary />

          <main className="pdfb-pdf-builder-main">
            <Toolbar selectedElement={selectedElement} />
            <Canvas
              scale={scale}
              selectedElement={selectedElement}
              onSelectElement={handleElementSelect}
              templateData={templateData}
            />
          </main>

          {showProperties && (
            <PropertiesPanel
              selectedElement={selectedElement}
              onClose={() => setShowProperties(false)}
            />
          )}
        </div>
      </div>
    </EditorProvider>
  );
};

export default PDFBuilderApp;


