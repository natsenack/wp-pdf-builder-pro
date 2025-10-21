import React from 'react';
import { TextRenderer } from './TextRenderer';
import { RectangleRenderer } from './RectangleRenderer';
import { ImageRenderer } from './ImageRenderer';
import { TableRenderer } from './TableRenderer';
import { DynamicTextRenderer } from './DynamicTextRenderer';
import { BarcodeRenderer } from './BarcodeRenderer';
import { ProgressBarRenderer } from './ProgressBarRenderer';

/**
 * ElementRenderer - Renderer principal pour tous les types d'éléments
 * Route vers le renderer approprié selon le type d'élément
 */
function ElementRenderer({ element, scale = 1, templateData = {}, interactive = false }) {
  // Rendu selon le type d'élément
  switch (element.type) {
    case 'text':
      return (
        <TextRenderer
          element={element}
          canvasScale={scale}
        />
      );

    case 'dynamic-text':
      return (
        <DynamicTextRenderer
          element={element}
          previewData={templateData}
          canvasScale={scale}
        />
      );

    case 'rectangle':
    case 'shape-rectangle':
      return (
        <RectangleRenderer
          element={element}
          canvasScale={scale}
        />
      );

    case 'image':
      return (
        <ImageRenderer
          element={element}
          canvasScale={scale}
        />
      );

    case 'product_table':
      return (
        <TableRenderer
          element={element}
          previewData={templateData}
          canvasScale={scale}
        />
      );

    case 'barcode':
    case 'qrcode':
      return (
        <BarcodeRenderer
          element={element}
          previewData={templateData}
          canvasScale={scale}
        />
      );

    case 'progress-bar':
      return (
        <ProgressBarRenderer
          element={element}
          previewData={templateData}
          canvasScale={scale}
        />
      );

    case 'customer_info':
      return (
        <div style={{
          position: 'absolute',
          left: `${element.x * scale}px`,
          top: `${element.y * scale}px`,
          width: `${element.width * scale}px`,
          minHeight: `${element.height * scale}px`,
          padding: '10px',
          backgroundColor: element.properties?.backgroundColor || '#f8f9fa',
          border: element.properties?.borderWidth ? `${element.properties.borderWidth}px solid ${element.properties.borderColor || '#dee2e6'}` : 'none',
          borderRadius: '4px',
          fontSize: '12px',
          lineHeight: '1.4'
        }}>
          <div><strong>Client:</strong> {templateData.customer?.name || 'N/A'}</div>
          <div><strong>Email:</strong> {templateData.customer?.email || 'N/A'}</div>
          <div><strong>Téléphone:</strong> {templateData.customer?.phone || 'N/A'}</div>
          {templateData.customer?.address && (
            <div><strong>Adresse:</strong> {templateData.customer.address.replace('\n', ', ')}</div>
          )}
        </div>
      );

    case 'company_info':
      return (
        <div style={{
          position: 'absolute',
          left: `${element.x * scale}px`,
          top: `${element.y * scale}px`,
          width: `${element.width * scale}px`,
          minHeight: `${element.height * scale}px`,
          padding: '10px',
          backgroundColor: element.properties?.backgroundColor || '#f8f9fa',
          border: element.properties?.borderWidth ? `${element.properties.borderWidth}px solid ${element.properties.borderColor || '#dee2e6'}` : 'none',
          borderRadius: '4px',
          fontSize: '12px',
          lineHeight: '1.4'
        }}>
          <div><strong>Entreprise:</strong> {templateData.company?.name || 'N/A'}</div>
          <div><strong>Email:</strong> {templateData.company?.email || 'N/A'}</div>
          <div><strong>Téléphone:</strong> {templateData.company?.phone || 'N/A'}</div>
          {templateData.company?.address && (
            <div><strong>Adresse:</strong> {templateData.company.address.replace('\n', ', ')}</div>
          )}
        </div>
      );

    case 'order_number':
      return (
        <div style={{
          position: 'absolute',
          left: `${element.x * scale}px`,
          top: `${element.y * scale}px`,
          width: `${element.width * scale}px`,
          minHeight: `${element.height * scale}px`,
          padding: '8px',
          backgroundColor: element.properties?.backgroundColor || '#e3f2fd',
          border: element.properties?.borderWidth ? `${element.properties.borderWidth}px solid ${element.properties.borderColor || '#2196f3'}` : 'none',
          borderRadius: '4px',
          fontSize: '14px',
          fontWeight: 'bold',
          color: element.properties?.color || '#1976d2',
          textAlign: 'center',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          Commande #{templateData.order?.number || 'N/A'}
        </div>
      );

    default:
      // Élément inconnu - afficher un placeholder
      return (
        <div style={{
          position: 'absolute',
          left: `${element.x * scale}px`,
          top: `${element.y * scale}px`,
          width: `${element.width * scale}px`,
          minHeight: `${element.height * scale}px`,
          padding: '10px',
          backgroundColor: '#ffebee',
          border: '1px solid #f44336',
          borderRadius: '4px',
          color: '#c62828',
          fontSize: '12px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          <div style={{ textAlign: 'center' }}>
            <div>❓</div>
            <div>Type: {element.type}</div>
          </div>
        </div>
      );
  }
}

export default ElementRenderer;