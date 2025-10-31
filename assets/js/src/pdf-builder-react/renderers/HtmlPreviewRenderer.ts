/**
 * HTML Preview Renderer - Rendu HTML pour l'aperçu des templates
 *
 * Utilise les données JSON sauvegardées pour créer un aperçu HTML fidèle
 * au contenu réel du template, sans dépendre du Canvas 2D.
 */

export class HtmlPreviewRenderer {
  static renderPreview(elements: any[], dataProvider: any, canvas: any): string {
    console.log('[HTML PREVIEW] 🔄 renderPreview called with', elements.length, 'elements');

    const canvasStyle = `
      position: relative;
      width: ${canvas.width || 794}px;
      height: ${canvas.height || 1123}px;
      background-color: ${canvas.backgroundColor || '#ffffff'};
      margin: 0 auto;
      border: 1px solid #ddd;
      overflow: hidden;
      font-family: Arial, sans-serif;
    `;

    const elementsHtml = elements
      .filter(element => element.visible !== false)
      .map(element => {
        console.log('[HTML PREVIEW] 🎨 Rendering element:', element.type, element.id);
        return this.renderElement(element, dataProvider);
      })
      .join('');

    return `
      <div style="${canvasStyle}" class="pdf-preview-canvas">
        ${elementsHtml}
      </div>
    `;
  }

  static renderElement(element: any, dataProvider: any): string {
    const baseStyle = this.getBaseStyle(element);

    switch (element.type) {
      case 'text':
        return this.renderText(element, baseStyle);

      case 'rectangle':
        return this.renderRectangle(element, baseStyle);

      case 'line':
        return this.renderLine(element, baseStyle);

      case 'company_logo':
        return this.renderCompanyLogo(element, baseStyle);

      case 'document_type':
        return this.renderDocumentType(element, baseStyle, dataProvider);

      case 'order_number':
        return this.renderOrderNumber(element, baseStyle, dataProvider);

      case 'customer_info':
        return this.renderCustomerInfo(element, baseStyle, dataProvider);

      case 'company_info':
        return this.renderCompanyInfo(element, baseStyle, dataProvider);

      case 'product_table':
        return this.renderProductTable(element, baseStyle, dataProvider);

      case 'mentions':
        return this.renderMentions(element, baseStyle, dataProvider);

      case 'dynamic-text':
        return this.renderDynamicText(element, baseStyle, dataProvider);

      default:
        console.log('[HTML PREVIEW] ❓ Unknown element type:', element.type);
        return '';
    }
  }

  static getBaseStyle(element: any): string {
    return `
      position: absolute;
      left: ${element.x || 0}px;
      top: ${element.y || 0}px;
      width: ${element.width || 100}px;
      height: ${element.height || 50}px;
      transform: rotate(${element.rotation || 0}deg);
      opacity: ${(element.opacity || 100) / 100};
      z-index: 1;
    `.trim();
  }

  static renderText(element: any, baseStyle: string): string {
    const text = element.text || '';
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Arial';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'left';
    const color = element.color || element.textColor || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 📝 Text element:', text.substring(0, 50) + '...');

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.2;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderRectangle(element: any, baseStyle: string): string {
    const backgroundColor = element.backgroundColor || element.fillColor || '#ffffff';
    const borderColor = element.borderColor || '#000000';
    const borderWidth = element.borderWidth || 0;
    const borderRadius = element.borderRadius || 0;

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; border: ${borderWidth}px solid ${borderColor}; border-radius: ${borderRadius}px;">
      </div>
    `;
  }

  static renderLine(element: any, baseStyle: string): string {
    const strokeColor = element.strokeColor || element.color || '#000000';
    const strokeWidth = element.strokeWidth || element.height || 2;

    return `
      <div style="${baseStyle}background-color: ${strokeColor}; height: ${strokeWidth}px;">
      </div>
    `;
  }

  static renderCompanyLogo(element: any, baseStyle: string): string {
    const src = element.src || '';
    const objectFit = element.objectFit || 'contain';
    const backgroundColor = element.backgroundColor || 'transparent';
    const borderRadius = element.borderRadius || 0;

    console.log('[HTML PREVIEW] 🏢 Company logo src:', src);

    if (!src) {
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; border-radius: ${borderRadius}px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">
          Logo
        </div>
      `;
    }

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; border-radius: ${borderRadius}px; overflow: hidden;">
        <img src="${src}" alt="Company Logo" style="width: 100%; height: 100%; object-fit: ${objectFit};" />
      </div>
    `;
  }

  static renderDocumentType(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const documentType = element.documentType || element.text || dataProvider.getVariableValue('document_type') || 'FACTURE';
    const fontSize = element.fontSize || 20;
    const fontFamily = element.fontFamily || 'Arial';
    const fontWeight = element.fontWeight || 'bold';
    const textAlign = element.textAlign || 'left';
    const color = element.textColor || element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 📄 Document type:', documentType);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign};">
        ${this.escapeHtml(documentType)}
      </div>
    `;
  }

  static renderOrderNumber(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || dataProvider.getVariableValue('order_number') || 'Commande #WC-12345';
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Arial';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'right';
    const color = element.color || element.textColor || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 🔢 Order number:', text);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign};">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderCustomerInfo(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || this.buildCustomerInfoText(dataProvider);
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Inter, sans-serif';
    const fontWeight = element.fontWeight || 'bold';
    const textAlign = element.textAlign || 'left';
    const color = element.textColor || element.color || '#374151';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 👤 Customer info:', text.substring(0, 50) + '...');

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.4;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderCompanyInfo(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || this.buildCompanyInfoText(dataProvider);
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Inter, sans-serif';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'center';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 🏢 Company info:', text.substring(0, 50) + '...');

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.4;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderProductTable(element: any, baseStyle: string, dataProvider: any): string {
    // Utiliser les données sauvegardées ou du dataProvider
    let products = element.products;
    if (!products) {
      const dataSource = element.dataSource || 'order_items';
      products = dataProvider.getVariableValue(dataSource) || [];
    }

    console.log('[HTML PREVIEW] 📊 Products:', Array.isArray(products) ? products.length : 'not array', products);

    if (!Array.isArray(products) || products.length === 0) {
      return `
        <div style="${baseStyle}color: #666; font-style: italic;">
          Aucun produit
        </div>
      `;
    }

    const showHeaders = element.showHeaders !== false;
    const showBorders = element.showBorders || false;
    const headers = element.headers || ['Produit', 'Qté', 'Prix'];
    const headerBackgroundColor = element.headerBackgroundColor || '#1f2937';
    const headerTextColor = element.headerTextColor || '#ffffff';
    const textColor = element.textColor || '#111827';
    const alternateRowColor = element.alternateRowColor || '#f9f9f9';

    let tableHtml = `<table style="width: 100%; border-collapse: collapse; ${showBorders ? 'border: 1px solid #ddd;' : ''} font-size: 12px;">`;

    if (showHeaders) {
      tableHtml += `<thead><tr style="background-color: ${headerBackgroundColor}; color: ${headerTextColor};">`;
      headers.forEach(header => {
        tableHtml += `<th style="padding: 6px 8px; text-align: left; ${showBorders ? 'border: 1px solid #ddd;' : ''} font-weight: bold;">${header}</th>`;
      });
      tableHtml += `</tr></thead>`;
    }

    tableHtml += '<tbody>';
    products.forEach((product: any, index: number) => {
      const rowStyle = index % 2 === 1 ? `background-color: ${alternateRowColor};` : '';
      tableHtml += `<tr style="${rowStyle}">`;

      if (element.columns?.name !== false) {
        tableHtml += `<td style="padding: 6px 8px; color: ${textColor}; ${showBorders ? 'border: 1px solid #ddd;' : ''}">${this.escapeHtml(product.name || 'Produit')}</td>`;
      }
      if (element.columns?.quantity !== false) {
        tableHtml += `<td style="padding: 6px 8px; color: ${textColor}; ${showBorders ? 'border: 1px solid #ddd;' : ''} text-align: center;">${product.quantity || 1}</td>`;
      }
      if (element.columns?.price !== false) {
        tableHtml += `<td style="padding: 6px 8px; color: ${textColor}; ${showBorders ? 'border: 1px solid #ddd;' : ''} text-align: right;">${this.formatPrice(product.price) || '0€'}</td>`;
      }
      if (element.columns?.total !== false) {
        tableHtml += `<td style="padding: 6px 8px; color: ${textColor}; ${showBorders ? 'border: 1px solid #ddd;' : ''} text-align: right; font-weight: bold;">${this.formatPrice(product.total) || '0€'}</td>`;
      }

      tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table>';

    return `<div style="${baseStyle}">${tableHtml}</div>`;
  }

  static renderMentions(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || 'Mentions légales...';
    const fontSize = element.fontSize || 8;
    const fontFamily = element.fontFamily || 'Arial';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'center';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 📋 Mentions:', text.substring(0, 50) + '...');

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; line-height: 1.2;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderDynamicText(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || 'Texte dynamique...';
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Arial';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 🔤 Dynamic text:', text.substring(0, 50) + '...');

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; white-space: pre-line; line-height: 1.4;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static buildCustomerInfoText(dataProvider: any): string {
    return [
      dataProvider.getVariableValue('customer_name'),
      dataProvider.getVariableValue('customer_email'),
      dataProvider.getVariableValue('customer_phone'),
      dataProvider.getVariableValue('customer_address')
    ].filter(info => info).join('\n');
  }

  static buildCompanyInfoText(dataProvider: any): string {
    return [
      dataProvider.getVariableValue('company_name'),
      dataProvider.getVariableValue('company_address'),
      dataProvider.getVariableValue('company_phone'),
      dataProvider.getVariableValue('company_email')
    ].filter(info => info).join('\n');
  }

  static formatPrice(price: any): string {
    if (typeof price === 'number') {
      return price.toFixed(2) + '€';
    }
    if (typeof price === 'string') {
      return price.includes('€') ? price : price + '€';
    }
    return '0€';
  }

  static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}