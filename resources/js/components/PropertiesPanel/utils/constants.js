// Configuration des presets par template pour le texte dynamique
export const TEMPLATE_PRESETS = {
  'total_only': {
    fontSize: 16,
    fontWeight: 'bold',
    textAlign: 'right',
    color: '#2563eb'
  },
  'order_info': {
    fontSize: 12,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'customer_info': {
    fontSize: 12,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'customer_address': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    lineHeight: 1.3
  },
  'full_header': {
    fontSize: 14,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#1f2937'
  },
  'invoice_header': {
    fontSize: 18,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#1f2937',
    fontFamily: 'Arial'
  },
  'order_summary': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'right',
    color: '#374151',
    lineHeight: 1.4
  },
  'payment_info': {
    fontSize: 12,
    fontWeight: 'bold',
    textAlign: 'left',
    color: '#059669'
  },
  'payment_terms': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.3
  },
  'shipping_info': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    lineHeight: 1.3
  },
  'thank_you': {
    fontSize: 14,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669',
    fontStyle: 'italic'
  },
  'legal_notice': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.2
  },
  'bank_details': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    fontFamily: 'Courier New'
  },
  'contact_info': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'order_confirmation': {
    fontSize: 14,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#059669'
  },
  'delivery_note': {
    fontSize: 12,
    fontWeight: 'bold',
    textAlign: 'left',
    color: '#1f2937'
  },
  'warranty_info': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#059669',
    lineHeight: 1.3
  },
  'return_policy': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#dc2626',
    lineHeight: 1.3
  },
  'signature_line': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'invoice_footer': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#6b7280'
  },
  'terms_conditions': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.2
  },
  'quality_guarantee': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669'
  },
  'eco_friendly': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669'
  },
  'follow_up': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'custom': {
    fontSize: 14,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  }
};

export const ELEMENT_PROPERTY_PROFILES = {
  // Logo entreprise (même propriétés que logo)
  company_logo: {
    appearance: {
      sections: ['colors', 'borders', 'effects'],
      properties: {
        colors: ['backgroundColor'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['image'],
      properties: {
        image: ['imageUrl', 'alt', 'objectFit']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Tableaux produits (propriétés complètes)
  product_table: {
    appearance: {
      sections: ['table_appearance', 'colors', 'typography', 'borders', 'effects'],
      properties: {
        table_appearance: ['tableStyle', 'evenRowBg', 'oddRowBg', 'evenRowTextColor', 'oddRowTextColor'],
        colors: ['backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation', 'scaleX', 'scaleY'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['table'],
      properties: {
        table: ['columns', 'showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal']
      }
    },
    effects: {
      sections: ['opacity', 'shadows', 'filters'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY', 'shadowBlur'],
        filters: ['brightness', 'contrast', 'saturate']
      }
    }
  },
  // Éléments texte statique
  text: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['text'],
      properties: {
        text: ['text']
      }
    },
    effects: {
      sections: ['opacity', 'shadows', 'filters'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY'],
        filters: ['brightness', 'contrast', 'saturate']
      }
    }
  },
  // Éléments d'informations client (accès aux couleurs et apparence)
  customer_info: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['customer_fields'],
      properties: {
        customer_fields: ['customerName', 'customerAddress', 'customerPhone', 'customerEmail']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments texte dynamique
  'dynamic-text': {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['dynamic_text', 'variables'],
      properties: {
        dynamic_text: ['dynamicText'],
        variables: ['variables']
      }
    },
    effects: {
      sections: ['opacity', 'shadows', 'filters'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY'],
        filters: ['brightness', 'contrast', 'saturate']
      }
    }
  },
  // Éléments numéro de commande
  order_number: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['order_number'],
      properties: {
        order_number: ['format', 'showLabel', 'labelText', 'labelAlign', 'previewOrderNumber', 'highlightNumber', 'numberBackground']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments d'informations société
  company_info: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['company_info'],
      properties: {
        company_info: ['template']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments de mentions légales
  mentions: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['mentions'],
      properties: {
        mentions: ['type']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments par défaut (tous les autres types)
  default: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: [],
      properties: {}
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  }
};