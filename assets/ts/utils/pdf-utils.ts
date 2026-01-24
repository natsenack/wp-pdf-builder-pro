import { AjaxResponse, PDFTemplate, PDFElement } from '@/shared';

/**
 * Utilitaires pour les appels AJAX WordPress
 */
export class WPAjax {
  private static readonly nonce = window.pdfBuilderPro?.nonce || '';
  private static readonly ajaxUrl = window.ajaxurl || window.pdfBuilderPro?.ajaxUrl || '';

  /**
   * Effectue un appel AJAX vers WordPress
   */
  static async post<T = any>(
    action: string,
    data: Record<string, any> = {}
  ): Promise<AjaxResponse<T>> {
    const formData = new FormData();

    // Ajout de l'action et du nonce
    formData.append('action', action);
    if (this.nonce) {
      formData.append('nonce', this.nonce);
    }

    // Ajout des données
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, String(value));
      }
    });

    try {
      const response = await fetch(this.ajaxUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result: AjaxResponse<T> = await response.json();
      return result;
    } catch (error) {
      console.error(`Erreur AJAX pour l'action "${action}":`, error);
      throw error;
    }
  }

  /**
   * Raccourci pour les appels réussis
   */
  static async postSuccess<T = any>(
    action: string,
    data: Record<string, any> = {}
  ): Promise<T> {
    const response = await this.post<T>(action, data);

    if (!response.success) {
      throw new Error(response.data?.message || 'Erreur inconnue');
    }

    return response.data;
  }
}

/**
 * Utilitaires pour les templates PDF
 */
export class PDFTemplateUtils {
  /**
   * Valide un template PDF
   */
  static validateTemplate(template: PDFTemplate): boolean {
    return !!(
      template.id &&
      template.name &&
      template.settings &&
      template.settings.pageSize &&
      template.settings.orientation &&
      Array.isArray(template.settings.fonts) &&
      template.settings.colors
    );
  }

  /**
   * Crée un template par défaut
   */
  static createDefaultTemplate(): PDFTemplate {
    return {
      id: `template_${Date.now()}`,
      name: 'Nouveau Template',
      description: 'Template PDF par défaut',
      category: 'general',
      isDefault: false,
      settings: {
        pageSize: 'A4',
        orientation: 'portrait',
        margins: {
          top: 20,
          right: 20,
          bottom: 20,
          left: 20,
        },
        fonts: [
          {
            name: 'Arial',
            family: 'Arial, sans-serif',
            weight: 'normal',
            size: 12,
          },
        ],
        colors: {
          primary: '#007cba',
          secondary: '#005a87',
          accent: '#00a32a',
          text: '#1d2327',
          background: '#ffffff',
        },
      },
    };
  }

  /**
   * Clone un template
   */
  static cloneTemplate(template: PDFTemplate): PDFTemplate {
    return {
      ...template,
      id: `template_${Date.now()}`,
      name: `${template.name} (Copie)`,
      isDefault: false,
    };
  }
}

/**
 * Utilitaires pour les éléments PDF
 */
export class PDFElementUtils {
  /**
   * Génère un ID unique pour un élément
   */
  static generateId(): string {
    return `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Valide un élément PDF
   */
  static validateElement(element: PDFElement): boolean {
    return !!(
      element.id &&
      element.type &&
      element.position &&
      typeof element.position.x === 'number' &&
      typeof element.position.y === 'number' &&
      typeof element.position.width === 'number' &&
      typeof element.position.height === 'number' &&
      typeof element.zIndex === 'number'
    );
  }

  /**
   * Vérifie si deux éléments se chevauchent
   */
  static elementsOverlap(element1: PDFElement, element2: PDFElement): boolean {
    const rect1 = element1.position;
    const rect2 = element2.position;

    return !(
      rect1.x + rect1.width <= rect2.x ||
      rect2.x + rect2.width <= rect1.x ||
      rect1.y + rect1.height <= rect2.y ||
      rect2.y + rect2.height <= rect1.y
    );
  }

  /**
   * Calcule la surface d'un élément
   */
  static getElementArea(element: PDFElement): number {
    return element.position.width * element.position.height;
  }
}

/**
 * Utilitaires généraux
 */
export class Utils {
  /**
   * Attend un délai spécifié
   */
  static delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Génère un UUID v4
   */
  static generateUUID(): string {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = Math.random() * 16 | 0;
      const v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }

  /**
   * Formate une taille de fichier
   */
  static formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  /**
   * Échappe les caractères HTML
   */
  static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Tronque un texte
   */
  static truncateText(text: string, maxLength: number, suffix = '...'): string {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - suffix.length) + suffix;
  }
}