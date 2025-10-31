/**
 * HTML Preview Renderer - Rendu HTML pour l'aperçu des templates
 *
 * Utilise les données JSON sauvegardées pour créer un aperçu HTML fidèle
 * au contenu réel du template, sans dépendre du Canvas 2D.
 */

export class HtmlPreviewRenderer {
  static renderPreview(elements: any[], dataProvider: any, canvas: any): string {
    console.log('[HTML PREVIEW] 🔄 renderPreview called with', elements.length, 'elements');
    console.log('[HTML PREVIEW] 📋 Elements:', elements.map(e => ({ type: e.type, id: e.id, text: e.text?.substring(0, 30) + '...' })));

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
        console.log('[HTML PREVIEW] 🎨 Rendering element:', element.type, element.id, 'text:', element.text?.substring(0, 50));
        return this.renderElement(element, dataProvider);
      })
      .join('');

    console.log('[HTML PREVIEW] ✅ Generated HTML length:', elementsHtml.length);
    return `
      <div style="${canvasStyle}" class="pdf-preview-canvas">
        <div style="position: absolute; top: 10px; left: 10px; background: yellow; color: black; padding: 5px; font-size: 12px; z-index: 1000;">
          HTML Preview - ${elements.length} éléments
        </div>
        ${elementsHtml}
      </div>
    `;
  }

  static renderElement(element: any, dataProvider: any): string {
    const baseStyle = this.getBaseStyle(element);

    // Ajouter des bordures de debug pour voir les éléments
    const debugStyle = `
      border: 1px solid red !important;
      background-color: rgba(255, 0, 0, 0.1) !important;
    `;

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
    const scale = element.scale || 100;
    const scaleFactor = scale / 100;
    const transform = `rotate(${element.rotation || 0}deg) scale(${scaleFactor})`;

    return `
      position: absolute;
      left: ${element.x || 0}px;
      top: ${element.y || 0}px;
      width: ${element.width || 100}px;
      height: ${element.height || 50}px;
      transform: ${transform};
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
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; border: 1px solid blue !important;">
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
      <div style="${baseStyle}background-color: ${backgroundColor}; border: ${borderWidth}px solid ${borderColor}; border-radius: ${borderRadius}px; border: 2px solid green !important;">
      </div>
    `;
  }

  static renderLine(element: any, baseStyle: string): string {
    const strokeColor = element.strokeColor || element.color || '#000000';
    const strokeWidth = element.strokeWidth || element.height || 2;

    return `
      <div style="${baseStyle}background-color: ${strokeColor}; height: ${strokeWidth}px; border: 2px solid orange !important;">
      </div>
    `;
  }

  static renderCompanyLogo(element: any, baseStyle: string): string {
    const src = element.src || '';
    const objectFit = element.objectFit || element.fit || 'contain';
    const backgroundColor = element.backgroundColor || 'transparent';
    const borderRadius = element.borderRadius || 0;
    const showBorder = element.showBorder !== false;
    const borderColor = element.borderColor || '#000000';
    const borderWidth = showBorder ? (element.borderWidth || 1) : 0;
    const alignment = element.alignment || 'left';

    console.log('[HTML PREVIEW] 🏢 Company logo src:', src);

    if (!src) {
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; border-radius: ${borderRadius}px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px; border: ${borderWidth}px solid ${borderColor}; border: 2px solid purple !important;">
          Logo
        </div>
      `;
    }

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; border-radius: ${borderRadius}px; overflow: hidden; border: ${borderWidth}px solid ${borderColor}; border: 2px solid purple !important;">
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
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; border: 2px solid cyan !important;">
        ${this.escapeHtml(documentType)}
      </div>
    `;
  }

  static renderOrderNumber(element: any, baseStyle: string, dataProvider: any): string {
    // Utiliser les propriétés sauvegardées pour construire le contenu
    const showLabel = element.showLabel !== false;
    const showDate = element.showDate !== false;
    const labelText = element.labelText || 'N° de commande :';
    const labelPosition = element.labelPosition || 'left';
    const labelTextAlign = element.labelTextAlign || 'left';
    const contentAlign = element.contentAlign || 'right';

    // Contenu principal
    const orderNumber = element.text || dataProvider.getVariableValue('order_number') || 'Commande #WC-12345';

    // Date si activée
    let datePart = '';
    if (showDate) {
      const orderDate = dataProvider.getVariableValue('order_date') || new Date().toLocaleDateString('fr-FR');
      datePart = ` (${orderDate})`;
    }

    const fullContent = orderNumber + datePart;

    // Styles
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Inter, sans-serif';
    const fontWeight = element.fontWeight || 'normal';
    const color = element.color || element.textColor || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 🔢 Order number:', fullContent, 'showLabel:', showLabel, 'showDate:', showDate);

    if (!showLabel) {
      // Affichage simple sans label
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${contentAlign}; border: 2px solid magenta !important;">
          ${this.escapeHtml(fullContent)}
        </div>
      `;
    }

    // Affichage avec label
    const labelStyle = `font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; color: ${color}; text-align: ${labelTextAlign};`;
    const contentStyle = `font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; color: ${color}; text-align: ${contentAlign};`;

    if (labelPosition === 'top') {
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; border: 2px solid magenta !important;">
          <div style="${labelStyle}">${this.escapeHtml(labelText)}</div>
          <div style="${contentStyle}">${this.escapeHtml(fullContent)}</div>
        </div>
      `;
    } else if (labelPosition === 'bottom') {
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; border: 2px solid magenta !important;">
          <div style="${contentStyle}">${this.escapeHtml(fullContent)}</div>
          <div style="${labelStyle}">${this.escapeHtml(labelText)}</div>
        </div>
      `;
    } else {
      // left or right
      const flexDirection = labelPosition === 'left' ? 'row' : 'row-reverse';
      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; display: flex; flex-direction: ${flexDirection}; align-items: center; border: 2px solid magenta !important;">
          <div style="${labelStyle}flex: 1;">${this.escapeHtml(labelText)}</div>
          <div style="${contentStyle}flex: 1;">${this.escapeHtml(fullContent)}</div>
        </div>
      `;
    }
  }

  static renderCustomerInfo(element: any, baseStyle: string, dataProvider: any): string {
    // Priorité aux propriétés sauvegardées
    const text = element.text || this.buildCustomerInfoText(dataProvider);
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || element.bodyFontFamily || 'Inter, sans-serif';
    const fontWeight = element.fontWeight || element.bodyFontWeight || 'bold';
    const textAlign = element.textAlign || 'left';
    const color = element.textColor || element.color || '#374151';
    const headerTextColor = element.headerTextColor || color;
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 👤 Customer info:', text.substring(0, 50) + '...', 'bodyFontWeight:', fontWeight);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.4; border: 2px solid yellow !important;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderCompanyInfo(element: any, baseStyle: string, dataProvider: any): string {
    // Utiliser les propriétés sauvegardées pour déterminer quoi afficher
    const showHeaders = element.showHeaders !== false;
    const showBorders = element.showBorders !== false;
    const showCompanyName = element.showCompanyName !== false;
    const showAddress = element.showAddress !== false;
    const showPhone = element.showPhone !== false;
    const showEmail = element.showEmail !== false;
    const showVat = element.showVat !== false;

    // Si du texte personnalisé est sauvegardé, l'utiliser en priorité
    if (element.text && element.text.trim()) {
      const fontSize = element.fontSize || 14;
      const fontFamily = element.fontFamily || element.bodyFontFamily || 'Inter, sans-serif';
      const fontWeight = element.fontWeight || 'normal';
      const textAlign = element.textAlign || 'center';
      const color = element.color || '#000000';
      const backgroundColor = element.backgroundColor || 'transparent';
      const theme = element.theme || 'classic';

      // Appliquer les styles du thème
      let themedStyles = '';
      switch (theme) {
        case 'corporate':
          themedStyles = 'border: 1px solid #1f2937; border-radius: 4px; padding: 8px;';
          break;
        case 'modern':
          themedStyles = 'border: 1px solid #3b82f6; border-radius: 4px; padding: 8px;';
          break;
        case 'elegant':
          themedStyles = 'border: 1px solid #8b5cf6; border-radius: 4px; padding: 8px;';
          break;
        case 'minimal':
          themedStyles = 'border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px;';
          break;
        case 'professional':
          themedStyles = 'border: 1px solid #059669; border-radius: 4px; padding: 8px;';
          break;
        default:
          themedStyles = '';
      }

      console.log('[HTML PREVIEW] 🏢 Company info (custom text):', element.text.substring(0, 50) + '...', 'theme:', theme);

      return `
        <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.4; ${themedStyles} border: 2px solid lime !important;">
          ${this.escapeHtml(element.text)}
        </div>
      `;
    }

    // Sinon, construire le texte selon les propriétés sauvegardées
    const companyInfoParts = [];

    if (showCompanyName) {
      const companyName = dataProvider.getVariableValue('company_name') || 'Ma Société SARL';
      if (companyName) companyInfoParts.push(companyName);
    }

    if (showAddress) {
      const companyAddress = dataProvider.getVariableValue('company_address') || '456 Avenue des Champs\n75008 Paris\nFrance';
      if (companyAddress) companyInfoParts.push(companyAddress);
    }

    if (showPhone) {
      const companyPhone = dataProvider.getVariableValue('company_phone') || '+33 1 98 76 54 32';
      if (companyPhone) companyInfoParts.push(`Tél: ${companyPhone}`);
    }

    if (showEmail) {
      const companyEmail = dataProvider.getVariableValue('company_email') || 'contact@masociete.com';
      if (companyEmail) companyInfoParts.push(`Email: ${companyEmail}`);
    }

    if (showVat) {
      const companyVat = dataProvider.getVariableValue('company_vat') || 'FR 12 345 678 901';
      if (companyVat) companyInfoParts.push(`TVA: ${companyVat}`);
    }

    const text = companyInfoParts.join('\n');
    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || element.bodyFontFamily || 'Inter, sans-serif';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'center';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';
    const theme = element.theme || 'classic';

    // Appliquer les styles du thème
    let themedStyles = '';
    switch (theme) {
      case 'corporate':
        themedStyles = 'border: 1px solid #1f2937; border-radius: 4px; padding: 8px;';
        break;
      case 'modern':
        themedStyles = 'border: 1px solid #3b82f6; border-radius: 4px; padding: 8px;';
        break;
      case 'elegant':
        themedStyles = 'border: 1px solid #8b5cf6; border-radius: 4px; padding: 8px;';
        break;
      case 'minimal':
        themedStyles = 'border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px;';
        break;
      case 'professional':
        themedStyles = 'border: 1px solid #059669; border-radius: 4px; padding: 8px;';
        break;
      default:
        themedStyles = '';
    }

    console.log('[HTML PREVIEW] 🏢 Company info (constructed):', text.substring(0, 50) + '...', 'theme:', theme);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; white-space: pre-line; line-height: 1.4; ${themedStyles} border: 2px solid lime !important;">
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
    console.log('[HTML PREVIEW] 📊 Element columns config:', element.columns);

    if (!Array.isArray(products) || products.length === 0) {
      return `
        <div style="${baseStyle}color: #666; font-style: italic; border: 2px solid red !important;">
          Aucun produit
        </div>
      `;
    }

    // Normaliser la configuration des colonnes - gérer les deux formats
    let enabledColumns: string[] = [];
    const columnsConfig = element.columns || {};

    if (Array.isArray(columnsConfig)) {
      // Format tableau : ["product", "qty", "price", "total"]
      console.log('[HTML PREVIEW] 📊 Using array format for columns');
      columnsConfig.forEach(col => {
        switch (col.toLowerCase()) {
          case 'product':
          case 'name':
            enabledColumns.push('name');
            break;
          case 'qty':
          case 'quantity':
            enabledColumns.push('quantity');
            break;
          case 'price':
            enabledColumns.push('price');
            break;
          case 'total':
            enabledColumns.push('total');
            break;
          case 'image':
            enabledColumns.push('image');
            break;
          case 'sku':
            enabledColumns.push('sku');
            break;
          case 'description':
            enabledColumns.push('description');
            break;
        }
      });
    } else {
      // Format objet : {name: true, quantity: true, price: true, total: true}
      console.log('[HTML PREVIEW] 📊 Using object format for columns');
      const columnOrder = ['image', 'sku', 'description', 'name', 'quantity', 'price', 'total'];
      columnOrder.forEach(col => {
        if (columnsConfig[col] !== false && columnsConfig[col] !== undefined) {
          enabledColumns.push(col);
        }
      });
    }

    // Si aucune colonne n'est configurée, utiliser les valeurs par défaut
    if (enabledColumns.length === 0) {
      console.log('[HTML PREVIEW] 📊 No columns configured, using defaults');
      enabledColumns = ['image', 'name', 'quantity', 'price', 'total'];
    }

    console.log('[HTML PREVIEW] 📊 Final enabled columns:', enabledColumns);

    // Propriétés d'affichage générales
    const showHeaders = element.showHeaders !== false;
    const showBorders = element.showBorders !== false;
    const showAlternatingRows = element.showAlternatingRows !== false;
    const showSubtotal = element.showSubtotal === undefined ? true : element.showSubtotal !== false;
    const showShipping = element.showShipping === undefined ? true : element.showShipping !== false;
    const showTax = element.showTax === undefined ? true : element.showTax !== false || element.showTaxes === undefined ? true : element.showTaxes !== false;
    const showDiscount = element.showDiscount === undefined ? true : element.showDiscount !== false;
    const showTotal = element.showTotal === undefined ? true : element.showTotal !== false;

    // Propriétés de style du tableau
    const tableStyle = element.tableStyle || 'default';
    const fontSize = element.fontSize || 12;
    const fontFamily = element.fontFamily || 'Arial, sans-serif';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'left';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';
    const dataSource = element.dataSource || 'order_items';
    const padding = element.padding || 0;
    const margin = element.margin || 0;
    const opacity = element.opacity !== undefined ? element.opacity : 100;
    const borderRadius = element.borderRadius || 0;
    const boxShadow = element.boxShadow || 'none';
    const cellPadding = element.cellPadding || element.cellpadding || 6;
    const cellSpacing = element.cellSpacing || element.cellspacing || 0;
    const tableWidth = element.tableWidth || element.width || '100%';
    const tableHeight = element.tableHeight || element.height || 'auto';
    const minWidth = element.minWidth || 'auto';
    const maxWidth = element.maxWidth || 'none';
    const minHeight = element.minHeight || 'auto';
    const maxHeight = element.maxHeight || 'none';
    const verticalAlign = element.verticalAlign || 'top';
    const caption = element.caption || '';
    const summary = element.summary || 'Tableau des produits commandés';
    const columnWidths = element.columnWidths || {};
    const columnAlignments = element.columnAlignments || {};
    const showProductLinks = element.showProductLinks !== false;
    const showProductImages = element.showProductImages !== false;
    const maxDescriptionLength = element.maxDescriptionLength || 100;
    const numberFormat = element.numberFormat || 'fr-FR';
    const currency = element.currency || '€';
    const currencyPosition = element.currencyPosition || 'after';
    const headerBackgroundColor = element.headerBackgroundColor || '#f8f9fa';
    const headerTextColor = element.headerTextColor || '#000000';
    const alternateRowColor = element.alternateRowColor || 'transparent';
    const textColor = element.textColor || element.color || '#000000';
    const rowHeight = element.rowHeight || 'auto';
    const spacing = element.spacing || 8;

    // Headers personnalisés ou par défaut
    let headers = element.headers || [];

    // Construire les headers selon les colonnes activées
    if (!element.headers || element.headers.length === 0 || element.headers.length !== enabledColumns.length) {
      console.log('[HTML PREVIEW] 📊 Generating dynamic headers - custom headers missing or count mismatch');
      const dynamicHeaders: string[] = [];
      enabledColumns.forEach(col => {
        switch (col) {
          case 'image': dynamicHeaders.push('Image'); break;
          case 'sku': dynamicHeaders.push('SKU'); break;
          case 'description': dynamicHeaders.push('Description'); break;
          case 'name': dynamicHeaders.push('Produit'); break;
          case 'quantity': dynamicHeaders.push('Qté'); break;
          case 'price': dynamicHeaders.push('Prix'); break;
          case 'total': dynamicHeaders.push('Total'); break;
        }
      });
      headers = dynamicHeaders;
    } else {
      console.log('[HTML PREVIEW] 📊 Using custom headers:', headers);
    }

    console.log('[HTML PREVIEW] 📊 Product table properties from JSON:', {
      // Propriétés d'affichage
      showHeaders, showBorders, showAlternatingRows,
      showSubtotal, showShipping, showTax, showDiscount, showTotal,
      // Colonnes
      columns: element.columns,
      headers: element.headers,
      // Couleurs et styles
      headerBackgroundColor, headerTextColor, textColor, alternateRowColor,
      backgroundColor, borderColor,
      // Dimensions
      tableWidth, tableHeight, rowHeight, spacing,
      // Police
      fontSize, fontFamily, fontWeight, textAlign,
      // Autres
      dataSource, showProductImages, showProductLinks, maxDescriptionLength,
      numberFormat, currency, currencyPosition
    });

    const borderColor = element.borderColor || '#e5e7eb';

    // Appliquer le style du tableau
    let tableStyleCss = `font-weight: ${fontWeight}; color: ${textColor}; background-color: ${backgroundColor};`;
    if (borderRadius > 0) {
      tableStyleCss += ` border-radius: ${borderRadius}px;`;
    }
    if (boxShadow !== 'none') {
      tableStyleCss += ` box-shadow: ${boxShadow};`;
    }
    switch (tableStyle) {
      case 'minimal':
        tableStyleCss += ' border: none;';
        break;
      case 'bordered':
        tableStyleCss += ` border: 2px solid ${borderColor};`;
        break;
      case 'default':
      default:
        tableStyleCss += showBorders ? ` border: 1px solid ${borderColor};` : '';
        break;
    }

    console.log('[HTML PREVIEW] 📏 Table sizing and style properties:', {
      tableWidth,
      tableHeight,
      minWidth,
      maxWidth,
      minHeight,
      maxHeight,
      padding,
      margin,
      borderRadius,
      boxShadow,
      opacity,
      tableStyle,
      tableStyleCss: tableStyleCss.substring(0, 100) + '...'
    });

    let tableHtml = `<table style="width: ${tableWidth}; height: ${tableHeight}; min-width: ${minWidth}; max-width: ${maxWidth}; min-height: ${minHeight}; max-height: ${maxHeight}; border-collapse: collapse; ${tableStyleCss} font-size: ${fontSize}px; font-family: ${fontFamily}; text-align: ${textAlign}; vertical-align: ${verticalAlign};" cellpadding="${cellPadding}" cellspacing="${cellSpacing}" summary="${summary}">`;

    // Ajouter la légende si définie
    if (caption) {
      tableHtml += `<caption style="caption-side: top; margin-bottom: 8px; font-weight: bold; text-align: ${textAlign};">${this.escapeHtml(caption)}</caption>`;
    }

    if (showHeaders && headers.length > 0) {
      tableHtml += `<thead><tr style="background-color: ${headerBackgroundColor}; color: ${headerTextColor};">`;
      headers.forEach((header: string, index: number) => {
        const col = enabledColumns[index];
        const width = columnWidths[col] || 'auto';
        const align = columnAlignments[col] || 'left';
        tableHtml += `<th style="text-align: ${align}; ${showBorders ? `border: 1px solid ${borderColor};` : ''} font-weight: bold; ${rowHeight !== 'auto' ? `height: ${rowHeight}px;` : ''} ${width !== 'auto' ? `width: ${width};` : ''}">${header}</th>`;
      });
      tableHtml += `</tr></thead>`;
    }

    tableHtml += '<tbody>';
    products.forEach((product: any, index: number) => {
      console.log('[HTML PREVIEW] 📊 Rendering product:', index, product.name || 'unnamed', 'with columns:', enabledColumns);
      const rowStyle = index % 2 === 1 && showAlternatingRows ? `background-color: ${alternateRowColor};` : '';
      tableHtml += `<tr style="${rowStyle}">`;

      // Colonnes selon la configuration activée
      enabledColumns.forEach((col, index) => {
        const width = columnWidths[col] || 'auto';
        const align = columnAlignments[col] || (col === 'quantity' || col === 'price' || col === 'total' ? 'right' : 'left');
        const cellStyle = `color: ${textColor}; ${showBorders ? `border: 1px solid ${borderColor};` : ''} ${rowHeight !== 'auto' ? `height: ${rowHeight}px;` : ''} ${width !== 'auto' ? `width: ${width};` : ''}`;

        switch (col) {
          case 'image':
            const imageSrc = product.image || product.image_url || product.thumbnail || '';
            const imageHtml = showProductImages && imageSrc ? `<img src="${imageSrc}" alt="${product.name || 'Product'}" style="max-width: 50px; max-height: 50px; object-fit: contain;">` : '';
            tableHtml += `<td style="${cellStyle} text-align: center;">${imageHtml}</td>`;
            break;
          case 'sku':
            tableHtml += `<td style="${cellStyle} text-align: ${align};">${this.escapeHtml(product.sku || product.sku_code || '')}</td>`;
            break;
          case 'description':
            let description = product.description || product.short_description || '';
            if (description.length > maxDescriptionLength) {
              description = description.substring(0, maxDescriptionLength) + '...';
            }
            tableHtml += `<td style="${cellStyle} text-align: ${align};">${this.escapeHtml(description)}</td>`;
            break;
          case 'name':
            const productName = product.name || product.title || product.product_name || 'Produit';
            const displayName = showProductLinks && product.url ? `<a href="${product.url}" target="_blank" style="color: ${textColor}; text-decoration: none;">${this.escapeHtml(productName)}</a>` : this.escapeHtml(productName);
            tableHtml += `<td style="${cellStyle} text-align: ${align};">${displayName}</td>`;
            break;
          case 'quantity':
            const qty = product.quantity || product.qty || product.amount || 1;
            tableHtml += `<td style="${cellStyle} text-align: ${align};">${qty}</td>`;
            break;
          case 'price':
            const price = product.price || product.unit_price || product.cost || 0;
            tableHtml += `<td style="${cellStyle} text-align: ${align};">${this.formatPrice(price, currency, currencyPosition, numberFormat)}</td>`;
            break;
          case 'total':
            const total = product.total || product.line_total || (product.price * product.quantity) || 0;
            tableHtml += `<td style="${cellStyle} text-align: ${align}; font-weight: bold;">${this.formatPrice(total, currency, currencyPosition, numberFormat)}</td>`;
            break;
        }
      });

      tableHtml += '</tr>';
    });

    // Calculer les totaux des produits
    const subtotal = products.reduce((sum: number, product: any) => {
      const total = product.total || product.line_total || (product.price * product.quantity) || 0;
      return sum + total;
    }, 0);

    const shipping = element.shipping || element.shipping_cost || 0;
    const tax = element.tax || element.taxes || element.tax_amount || 0;
    const discount = element.discount || element.discount_amount || 0;
    const grandTotal = subtotal + shipping + tax - discount;

    console.log('[HTML PREVIEW] 📊 Totals calculated:', { subtotal, shipping, tax, discount, grandTotal });

    console.log('[HTML PREVIEW] 📊 Display options:', {
      showSubtotal,
      showShipping,
      showTax,
      showDiscount,
      showTotal,
      subtotal: subtotal > 0,
      shipping: shipping > 0,
      tax: tax > 0,
      discount: discount > 0,
      grandTotal: grandTotal > 0
    });

    // Lignes supplémentaires si activées
    // Forcer l'affichage du subtotal et total si valeurs > 0 (priorité sur les propriétés show*)
    if ((showSubtotal || subtotal > 0) && subtotal > 0) {
      console.log('[HTML PREVIEW] 📊 Adding subtotal row (forced if value > 0)');
      tableHtml += `<tr style="font-weight: bold; background-color: #f8f9fa;"><td colspan="${headers.length - 1}" style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">Sous-total:</td><td style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">${this.formatPrice(subtotal, currency, currencyPosition, numberFormat)}</td></tr>`;
    }

    if (showShipping && shipping > 0) {
      console.log('[HTML PREVIEW] 📊 Adding shipping row');
      tableHtml += `<tr><td colspan="${headers.length - 1}" style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">Frais de port:</td><td style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">${this.formatPrice(shipping, currency, currencyPosition, numberFormat)}</td></tr>`;
    }

    if (showTax && tax > 0) {
      console.log('[HTML PREVIEW] 📊 Adding tax row');
      tableHtml += `<tr><td colspan="${headers.length - 1}" style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">TVA:</td><td style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">${this.formatPrice(tax, currency, currencyPosition, numberFormat)}</td></tr>`;
    }

    if (showDiscount && discount > 0) {
      console.log('[HTML PREVIEW] 📊 Adding discount row');
      tableHtml += `<tr><td colspan="${headers.length - 1}" style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">Remise:</td><td style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">-${this.formatPrice(discount, currency, currencyPosition, numberFormat)}</td></tr>`;
    }

    // Forcer l'affichage du total si valeur > 0 (priorité sur la propriété showTotal)
    if ((showTotal || grandTotal > 0) && grandTotal > 0) {
      console.log('[HTML PREVIEW] 📊 Adding total row (forced if value > 0)');
      tableHtml += `<tr style="font-weight: bold; font-size: 14px; background-color: #e3f2fd;"><td colspan="${headers.length - 1}" style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">TOTAL:</td><td style="text-align: right; ${showBorders ? `border: 1px solid ${borderColor};` : ''}">${this.formatPrice(grandTotal, currency, currencyPosition, numberFormat)}</td></tr>`;
    }

    tableHtml += '</tbody></table>';

    console.log('[HTML PREVIEW] 📊 Generated table HTML length:', tableHtml.length);
    return `<div style="${baseStyle}padding: ${padding}px; margin: ${margin}px; background-color: ${backgroundColor}; opacity: ${opacity / 100}; border-radius: ${borderRadius}px; box-shadow: ${boxShadow}; display: inline-block; min-width: ${minWidth}; max-width: ${maxWidth}; min-height: ${minHeight}; max-height: ${maxHeight};">${tableHtml}</div>`;
  }

  static renderMentions(element: any, baseStyle: string, dataProvider: any): string {
    // Utiliser les propriétés sauvegardées
    const mentionType = element.mentionType || 'custom';
    const selectedMentions = element.selectedMentions || [];
    const medleySeparator = element.medleySeparator || ' | ';
    const customText = element.text || '';

    let text = customText;

    // Si c'est un type medley avec des mentions sélectionnées, construire le texte
    if (mentionType === 'medley' && selectedMentions.length > 0) {
      const mentionTexts = selectedMentions.map((mention: string) => {
        switch (mention) {
          case 'cgv':
            return 'Conditions Générales de Vente applicables. Consultez notre site web pour plus de détails.';
          case 'legal':
            return 'Document établi sous la responsabilité de l\'entreprise. Toutes les informations sont confidentielles.';
          case 'tva_info':
            return 'TVA non applicable - article 293 B du CGI. Régime micro-entreprise.';
          case 'siret_info':
            return `SIRET ${dataProvider.getVariableValue('company_vat') || '123 456 789 00012'}`;
          default:
            return mention;
        }
      });

      text = mentionTexts.join(` ${medleySeparator} `);
    }

    const fontSize = element.fontSize || 8;
    const fontFamily = element.fontFamily || 'Arial';
    const fontWeight = element.fontWeight || 'normal';
    const textAlign = element.textAlign || 'center';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 📋 Mentions:', text.substring(0, 50) + '...', 'type:', mentionType, 'selected:', selectedMentions);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; font-weight: ${fontWeight}; text-align: ${textAlign}; line-height: 1.2; border: 2px solid pink !important;">
        ${this.escapeHtml(text)}
      </div>
    `;
  }

  static renderDynamicText(element: any, baseStyle: string, dataProvider: any): string {
    // Utiliser les propriétés sauvegardées
    const textTemplate = element.textTemplate || '';
    let text = element.text || 'Texte dynamique...';

    // Si un template est défini, essayer de le résoudre
    if (textTemplate && textTemplate.startsWith('checkbox_')) {
      // Templates de cases à cocher
      switch (textTemplate) {
        case 'checkbox_order_confirmation':
          text = '☐ Je confirme ma commande selon les termes du devis';
          break;
        default:
          text = `☐ ${text}`;
      }
    }

    const fontSize = element.fontSize || 14;
    const fontFamily = element.fontFamily || 'Arial';
    const color = element.color || '#000000';
    const backgroundColor = element.backgroundColor || 'transparent';

    console.log('[HTML PREVIEW] 🔤 Dynamic text:', text.substring(0, 50) + '...', 'template:', textTemplate);

    return `
      <div style="${baseStyle}background-color: ${backgroundColor}; color: ${color}; font-size: ${fontSize}px; font-family: ${fontFamily}; white-space: pre-line; line-height: 1.4; border: 2px solid brown !important;">
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

  static formatPrice(price: any, currency: string = '€', currencyPosition: string = 'after', numberFormat: string = 'fr-FR'): string {
    if (typeof price === 'number') {
      const formatted = price.toLocaleString(numberFormat, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      return currencyPosition === 'before' ? `${currency}${formatted}` : `${formatted}${currency}`;
    }
    if (typeof price === 'string') {
      return price.includes(currency) ? price : (currencyPosition === 'before' ? `${currency}${price}` : `${price}${currency}`);
    }
    return `0${currency}`;
  }

  static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}