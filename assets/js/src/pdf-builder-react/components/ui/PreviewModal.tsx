import React, { useRef, useEffect, useState } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Element } from '../../types/elements';

interface PreviewModalProps {
  isOpen: boolean;
  onClose: () => void;
  canvasWidth: number;
  canvasHeight: number;
}

export function PreviewModal({ isOpen, onClose, canvasWidth, canvasHeight }: PreviewModalProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [zoom, setZoom] = useState(1);
  const [isLoading, setIsLoading] = useState(false);
  const [previewElements, setPreviewElements] = useState<any[]>([]);
  const { state } = useBuilder();

  // Récupérer les éléments du template depuis la base de données
  useEffect(() => {
    if (isOpen) {
      loadTemplateElements();
    }
  }, [isOpen]);

  // Re-rendre quand les éléments changent
  useEffect(() => {
    if (isOpen && previewElements.length > 0) {
      renderPreview();
    }
  }, [isOpen, previewElements, zoom]);

  const loadTemplateElements = async () => {
    setIsLoading(true);
    try {
      // Récupérer l'ID du template depuis l'URL ou le state
      const urlParams = new URLSearchParams(window.location.search);
      const templateId = urlParams.get('template_id') || state.template?.id;

      if (!templateId) {
        console.warn('Aucun template ID trouvé pour l\'aperçu');
        setPreviewElements(state.elements); // Fallback vers le state local
        setIsLoading(false);
        return;
      }

      // Faire une requête AJAX pour récupérer les données du template
      const ajaxUrl = (window as any).ajaxurl || '/wp-admin/admin-ajax.php';
      const nonce = (window as any).pdfBuilderData?.nonce || (window as any).pdfBuilderNonce || (window as any).pdfBuilderReactData?.nonce || '';
      console.log('PreviewModal: Using nonce:', nonce, 'from pdfBuilderData:', (window as any).pdfBuilderData?.nonce, 'pdfBuilderNonce:', (window as any).pdfBuilderNonce, 'pdfBuilderReactData:', (window as any).pdfBuilderReactData?.nonce);
      const response = await fetch(`${ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${nonce}`, {
        method: 'GET'
      });

      const data = await response.json();

      if (data.success && data.data && data.data.elements) {
        console.log('PreviewModal: Retrieved elements from DB:', data.data.elements);
        console.log('PreviewModal: Canvas dimensions:', canvasWidth, 'x', canvasHeight);
        // Corriger automatiquement les coordonnées des éléments qui dépassent A4
        const correctedElements = data.data.elements.map((element: any) => {
          const corrected = { ...element };

          // S'assurer que l'élément ne dépasse pas à droite
          if (corrected.x + corrected.width > canvasWidth) {
            corrected.x = Math.max(0, canvasWidth - corrected.width);
            console.log(`Corrected element ${element.type} x from ${element.x} to ${corrected.x}`);
          }

          // S'assurer que l'élément ne dépasse pas en bas
          if (corrected.y + corrected.height > canvasHeight) {
            corrected.y = Math.max(0, canvasHeight - corrected.height);
            console.log(`Corrected element ${element.type} y from ${element.y} to ${corrected.y}`);
          }

          // S'assurer que les coordonnées ne sont pas négatives
          corrected.x = Math.max(0, corrected.x);
          corrected.y = Math.max(0, corrected.y);

          return corrected;
        });

        console.log('PreviewModal: Corrected elements:', correctedElements);
        setPreviewElements(correctedElements);
      } else {
        console.warn('Erreur lors de la récupération du template:', data.data);
        setPreviewElements(state.elements); // Fallback
      }
    } catch (error) {
      console.error('Erreur lors du chargement du template:', error);
      setPreviewElements(state.elements); // Fallback
    }
    setIsLoading(false);
  };

  // Fonction pour rendre l'aperçu
  const renderPreview = () => {
    if (!canvasRef.current) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    setIsLoading(true);

    // Définir la taille du canvas pour l'aperçu (dimensions de base, le zoom sera géré par CSS ou transformation)
    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    // Clear canvas avec fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

    // Plus de transformation scale - le zoom est géré par CSS
    // Rendre tous les éléments avec leurs coordonnées absolues
    previewElements.forEach(element => {
      renderElement(ctx, element);
    });
    setIsLoading(false);
  };

  // Fonction simplifiée pour rendre un élément (version aperçu)
  const renderElement = (ctx: CanvasRenderingContext2D, element: Element) => {
    ctx.save();
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    const props = element as any;

    switch (element.type) {
      case 'rectangle':
        ctx.fillStyle = props.fillColor || '#ffffff';
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
        break;

      case 'text':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 14}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = (props.textAlign || 'left') as CanvasTextAlign;
        ctx.textBaseline = 'top';
        const text = props.text || 'Texte';
        const lines = text.split('\n');
        let y = 0;
        lines.forEach((line: string) => {
          ctx.fillText(line, 0, y);
          y += props.fontSize || 14;
        });
        break;

      case 'company_logo':
        // Placeholder pour le logo
        ctx.fillStyle = '#f0f0f0';
        ctx.strokeStyle = '#ccc';
        ctx.lineWidth = 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Company Logo', element.width / 2, element.height / 2);
        break;

      case 'order_number':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontWeight || 'bold'} ${props.fontSize || 16}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = (props.textAlign || 'left') as CanvasTextAlign;
        ctx.textBaseline = 'top';
        const orderText = props.text || 'N° de commande';
        ctx.fillText(orderText, 0, 0);
        break;

      case 'company_info':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 12}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = (props.textAlign || 'left') as CanvasTextAlign;
        ctx.textBaseline = 'top';
        const infoText = props.text || 'Informations entreprise';
        const infoLines = infoText.split('\n');
        let infoY = 0;
        infoLines.forEach((line: string) => {
          ctx.fillText(line, 0, infoY);
          infoY += props.fontSize || 12;
        });
        break;

      case 'product_table':
        // Placeholder simple pour le tableau
        ctx.fillStyle = '#f9f9f9';
        ctx.strokeStyle = '#ddd';
        ctx.lineWidth = 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Tableau produits', element.width / 2, element.height / 2);
        break;

      default:
        // Élément générique
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.restore();
  };

  useEffect(() => {
    if (isOpen) {
      renderPreview();
    }
  }, [isOpen, state.elements, zoom]);

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2000
    }}>
      <div style={{
        backgroundColor: '#ffffff',
        borderRadius: '8px',
        padding: '20px',
        maxWidth: '90vw',
        maxHeight: '90vh',
        display: 'flex',
        flexDirection: 'column',
        gap: '16px'
      }}>
        {/* Header de la modale */}
        <div style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: '1px solid #e0e0e0',
          paddingBottom: '12px'
        }}>
          <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600' }}>
            Aperçu du PDF
          </h3>
          <button
            onClick={onClose}
            style={{
              background: 'none',
              border: 'none',
              fontSize: '24px',
              cursor: 'pointer',
              color: '#666',
              padding: '4px'
            }}
          >
            ×
          </button>
        </div>

        {/* Contrôles */}
        <div style={{
          display: 'flex',
          gap: '12px',
          alignItems: 'center'
        }}>
          <button
            onClick={() => setZoom(Math.max(0.25, zoom - 0.25))}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Zoom -
          </button>
          <span style={{ fontSize: '14px', fontWeight: '500' }}>
            {Math.round(zoom * 100)}%
          </span>
          <button
            onClick={() => setZoom(Math.min(3, zoom + 0.25))}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Zoom +
          </button>
          <button
            onClick={() => setZoom(1)}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            100%
          </button>
        </div>

        {/* Canvas d'aperçu */}
        <div style={{
          border: '1px solid #e0e0e0',
          borderRadius: '4px',
          padding: '16px',
          backgroundColor: '#f9f9f9',
          overflow: 'auto',
          maxHeight: '60vh',
          maxWidth: '80vw'
        }}>
          {isLoading ? (
            <div style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              height: '200px',
              color: '#666'
            }}>
              Chargement de l'aperçu...
            </div>
          ) : (
            <canvas
              ref={canvasRef}
              style={{
                border: '1px solid #ddd',
                borderRadius: '4px',
                backgroundColor: '#ffffff',
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
                transform: `scale(${zoom})`,
                transformOrigin: 'top left',
                imageRendering: 'pixelated'
              }}
            />
          )}
        </div>

        {/* Actions */}
        <div style={{
          display: 'flex',
          gap: '12px',
          justifyContent: 'flex-end'
        }}>
          <button
            onClick={onClose}
            style={{
              padding: '10px 20px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Fermer
          </button>
          <button
            style={{
              padding: '10px 20px',
              border: 'none',
              borderRadius: '4px',
              backgroundColor: '#007bff',
              color: '#ffffff',
              cursor: 'pointer',
              fontWeight: '500'
            }}
          >
            Télécharger PDF
          </button>
        </div>
      </div>
    </div>
  );
}