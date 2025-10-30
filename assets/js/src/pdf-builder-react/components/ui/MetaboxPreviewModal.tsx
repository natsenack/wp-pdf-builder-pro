import React, { useRef, useEffect, useState } from 'react';
import { Element } from '../../types/elements';
import { PreviewRenderer, DataProvider } from '../../renderers/PreviewRenderer';
import { MetaboxDataProvider, WooCommerceData } from '../../providers/MetaboxDataProvider';

interface MetaboxPreviewModalProps {
  isOpen: boolean;
  onClose: () => void;
  orderId: number;
  templateId: number;
  nonce: string;
}

interface PreviewData {
  order_id: number;
  template_id: number;
  elements: any[];
  order: any;
  billing: any;
  shipping: any;
  items: any[];
  mode: string;
}

export function MetaboxPreviewModal({ 
  isOpen, 
  onClose, 
  orderId, 
  templateId,
  nonce 
}: MetaboxPreviewModalProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [zoom, setZoom] = useState(1.0);
  const [isLoading, setIsLoading] = useState(false);
  const [previewElements, setPreviewElements] = useState<any[]>([]);
  const [previewData, setPreviewData] = useState<PreviewData | null>(null);
  const [dataProvider, setDataProvider] = useState<DataProvider | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [canvasWidth] = useState(794);
  const [canvasHeight] = useState(1123);

  // Charger les données de prévisualisation depuis l'API
  useEffect(() => {
    if (isOpen && orderId && templateId) {
      loadPreviewData();
    }
  }, [isOpen, orderId, templateId]);

  const loadPreviewData = async () => {
    setIsLoading(true);
    setError(null);

    try {
      const formData = new FormData();
      formData.append('action', 'pdf_builder_get_preview_data');
      formData.append('order_id', orderId.toString());
      formData.append('template_id', templateId.toString());
      formData.append('nonce', nonce);

      const response = await fetch((window as any).ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success && result.data) {
        setPreviewData(result.data);
        setPreviewElements(result.data.elements || []);
        // Créer le DataProvider avec les données WooCommerce
        const provider = new MetaboxDataProvider(result.data as WooCommerceData);
        setDataProvider(provider);
      } else {
        setError(result.data || 'Erreur lors du chargement des données');
      }
    } catch (err) {
      setError('Erreur réseau: ' + (err instanceof Error ? err.message : 'Erreur inconnue'));
    } finally {
      setIsLoading(false);
    }
  };

  // Fonction pour remplacer les variables dynamiques avec données réelles WooCommerce
  const replaceVariables = (text: string): string => {
    if (!previewData) return text;

    const variables: { [key: string]: string } = {
      // Variables client
      '{{customer_name}}': `${previewData.billing.first_name} ${previewData.billing.last_name}`.trim(),
      '{{customer_email}}': previewData.billing.email || 'email@inconnu.com',
      '{{customer_phone}}': previewData.billing.phone || '+33 0 00 00 00 00',
      '{{customer_address}}': [
        previewData.billing.address_1,
        previewData.billing.address_2,
        previewData.billing.postcode,
        previewData.billing.city,
        previewData.billing.country
      ].filter(Boolean).join('\n'),
      '{{customer_firstname}}': previewData.billing.first_name || 'Client',
      '{{customer_lastname}}': previewData.billing.last_name || 'Inconnu',
      
      // Variables commande
      '{{order_number}}': previewData.order.number || `CMD-${previewData.order.id}`,
      '{{order_date}}': previewData.order.date || new Date().toLocaleDateString('fr-FR'),
      '{{order_status}}': previewData.order.status || 'pending',
      '{{order_total}}': `${previewData.order.total.toFixed(2)} €` || '0,00 €',
      '{{order_subtotal}}': `${previewData.order.subtotal.toFixed(2)} €` || '0,00 €',
      '{{order_shipping}}': `${previewData.order.shipping_total.toFixed(2)} €` || '0,00 €',
      '{{order_tax}}': `${previewData.order.tax_total.toFixed(2)} €` || '0,00 €',
      
      // Variables adresse d'expédition
      '{{shipping_name}}': `${previewData.shipping.first_name} ${previewData.shipping.last_name}`.trim(),
      '{{shipping_address}}': [
        previewData.shipping.address_1,
        previewData.shipping.address_2,
        previewData.shipping.postcode,
        previewData.shipping.city,
        previewData.shipping.country
      ].filter(Boolean).join('\n'),
    };

    let result = text;
    Object.entries(variables).forEach(([variable, value]) => {
      result = result.replace(new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
    });
    
    return result;
  };

  // Rendre l'aperçu sur le canvas
  useEffect(() => {
    if (previewElements.length > 0 && !isLoading) {
      renderPreview();
    }
  }, [previewElements, zoom, previewData]);

  const renderPreview = () => {
    if (!canvasRef.current || !dataProvider || previewElements.length === 0) return;

    try {
      PreviewRenderer.render({
        canvas: canvasRef.current,
        elements: previewElements,
        dataProvider: dataProvider,
        zoom: zoom,
        width: canvasWidth,
        height: canvasHeight
      });
    } catch (error) {
      console.error('Erreur lors du rendu de l\'aperçu metabox:', error);
    }
  };

  const renderElement = (ctx: CanvasRenderingContext2D, element: Element) => {
    ctx.save();
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    const props = element as any;

    switch (element.type) {
      case 'rectangle':
        ctx.fillStyle = props.fillColor || props.backgroundColor || '#ffffff';
        ctx.strokeStyle = props.strokeColor || props.borderColor || '#000000';
        ctx.lineWidth = props.strokeWidth || props.borderWidth || 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
        break;

      case 'text':
        ctx.fillStyle = props.color || props.textColor || '#000000';
        ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 14}px ${props.fontFamily || 'Arial'}`;
        const textAlign = props.textAlign || props.align || 'left';
        ctx.textAlign = textAlign as CanvasTextAlign;
        ctx.textBaseline = 'top';
        const text = replaceVariables(props.text || 'Texte');
        const lines = text.split('\n');
        let y = 0;
        lines.forEach((line: string) => {
          const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width : 0;
          ctx.fillText(line, x, y);
          y += (props.fontSize || 14) * 1.2;
        });
        break;

      case 'company_logo':
        if (props.src || props.imageUrl) {
          const img = new Image();
          img.onload = () => {
            ctx.drawImage(img, 0, 0, element.width, element.height);
          };
          img.src = props.src || props.imageUrl;
        } else {
          ctx.fillStyle = '#f0f0f0';
          ctx.strokeStyle = '#ccc';
          ctx.lineWidth = 1;
          ctx.fillRect(0, 0, element.width, element.height);
          ctx.strokeRect(0, 0, element.width, element.height);
          ctx.fillStyle = '#666';
          ctx.font = '12px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText('Logo', element.width / 2, element.height / 2);
        }
        break;

      case 'customer_info':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontSize || 12}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';
        
        const customerInfo = [
          `${previewData?.billing.first_name || ''} ${previewData?.billing.last_name || ''}`.trim(),
          previewData?.billing.address_1 || '',
          `${previewData?.billing.postcode || ''} ${previewData?.billing.city || ''}`,
          previewData?.billing.email || '',
          previewData?.billing.phone || ''
        ].filter(Boolean);
        
        let customerY = 0;
        customerInfo.forEach(line => {
          ctx.fillText(line, 0, customerY);
          customerY += (props.fontSize || 12) * 1.3;
        });
        break;

      case 'product_table':
        // Rendu simplifié du tableau
        ctx.fillStyle = props.headerColor || '#f0f0f0';
        ctx.fillRect(0, 0, element.width, 30);
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 12px Arial';
        ctx.fillText('Produit', 10, 10);
        ctx.fillText('Qté', element.width - 100, 10);
        ctx.fillText('Prix', element.width - 50, 10);
        
        if (previewData?.items) {
          let tableY = 35;
          previewData.items.forEach(item => {
            ctx.font = '11px Arial';
            ctx.fillText(item.name || 'Produit', 10, tableY);
            ctx.fillText(item.quantity.toString(), element.width - 100, tableY);
            ctx.fillText(`${item.total.toFixed(2)} €`, element.width - 50, tableY);
            tableY += 25;
          });
        }
        break;

      default:
        break;
    }

    ctx.restore();
  };

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.5)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2000
    }}>
      <div style={{
        backgroundColor: '#ffffff',
        borderRadius: '8px',
        padding: '20px',
        maxWidth: '95vw',
        maxHeight: '95vh',
        width: 'auto',
        display: 'flex',
        flexDirection: 'column',
        gap: '16px'
      }}>
        <div style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: '1px solid #e0e0e0',
          paddingBottom: '12px'
        }}>
          <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600' }}>
            Aperçu du PDF - Commande #{orderId}
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

        {/* Barre de contrôle zoom */}
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
          <span style={{ fontSize: '14px', fontWeight: '500', minWidth: '80px', textAlign: 'center' }}>
            {Math.round(zoom * 100)}%
          </span>
          <button
            onClick={() => setZoom(Math.min(2.0, zoom + 0.25))}
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
            onClick={() => setZoom(1.0)}
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

        {/* Zone de rendu */}
        <div style={{
          border: '1px solid #e0e0e0',
          borderRadius: '4px',
          padding: '16px',
          backgroundColor: '#f9f9f9',
          overflow: 'auto',
          maxHeight: '60vh',
          maxWidth: '90vw',
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'flex-start'
        }}>
          {isLoading ? (
            <div style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              height: '200px',
              color: '#666'
            }}>
              ⏳ Chargement de l'aperçu...
            </div>
          ) : error ? (
            <div style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              height: '200px',
              color: '#dc3545',
              backgroundColor: '#f8d7da',
              borderRadius: '4px',
              padding: '20px',
              textAlign: 'center'
            }}>
              ❌ {error}
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
                imageRendering: 'auto',
                width: `${canvasWidth * zoom}px`,
                height: `${canvasHeight * zoom}px`,
                maxWidth: '100%',
                maxHeight: '100%'
              }}
            />
          )}
        </div>

        {/* Boutons d'action */}
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
              cursor: 'pointer',
              fontWeight: '500'
            }}
          >
            Fermer
          </button>
          <button
            onClick={() => window.print()}
            style={{
              padding: '10px 20px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#007cba',
              color: '#ffffff',
              cursor: 'pointer',
              fontWeight: '500'
            }}
          >
            Imprimer
          </button>
        </div>
      </div>
    </div>
  );
}
