/**
 * Gestionnaire de nonce unifié pour le frontend
 * Assure la cohérence avec le système backend NonceManager
 */

/**
 * Configuration du nonce côté client
 */
interface NonceConfig {
  nonce: string;
  action: string;
  ajaxUrl: string;
  timestamp: number;
}

/**
 * Réponse de nonce du serveur
 */
interface NonceResponse {
  success: boolean;
  data?: {
    nonce?: string;
    message?: string;
    code?: string;
  };
}

/**
 * Gestionnaire de nonce client
 */
export class ClientNonceManager {
  /**
   * Action nonce unifié - doit correspondre au backend
   */
  static readonly NONCE_ACTION = "pdf_builder_ajax";

  /**
   * Clé pour stocker le nonce
   */
  static readonly STORAGE_KEY = "pdfBuilderNonce";

  /**
   * TTL du nonce en secondes (12 heures)
   */
  static readonly NONCE_TTL = 43200;

  /**
   * Obtenir le nonce actuel depuis la fenêtre globale
   */
  static getCurrentNonce(): string | null {
    return window.pdfBuilderData?.nonce || window.pdfBuilderNonce || null;
  }

  /**
   * Obtenir l'URL AJAX
   */
  static getAjaxUrl(): string {
    return window.pdfBuilderData?.ajaxUrl || "";
  }

  /**
   * Mettre à jour le nonce globalement
   */
  static setNonce(nonce: string): void {
    if (window.pdfBuilderData) {
      window.pdfBuilderData.nonce = nonce;
    }
    if (window.pdfBuilderReactData) {
      window.pdfBuilderReactData.nonce = nonce;
    }
    window.pdfBuilderNonce = nonce;
    sessionStorage.setItem(this.STORAGE_KEY, nonce);
  }

  /**
   * Obtenir un nonce frais du serveur
   */
  static async refreshNonce(currentNonce?: string): Promise<string | null> {
    const formData = new FormData();
    formData.append("action", "pdf_builder_get_fresh_nonce");
    if (currentNonce) {
      formData.append("nonce", currentNonce);
    }

    try {
      const response = await fetch(this.getAjaxUrl(), {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        
        return null;
      }

      const result: NonceResponse = await response.json();

      if (result.success && result.data?.nonce) {
        const freshNonce = result.data.nonce;
        this.setNonce(freshNonce);
        
        return freshNonce;
      } else {
        
        return null;
      }
    } catch (error) {
      
      return null;
    }
  }

  /**
   * Ajouter le nonce à un FormData
   */
  static addToFormData(formData: FormData, nonce?: string): FormData {
    const nonceToUse = nonce || this.getCurrentNonce();
    
    
    
    if (nonceToUse) {
      formData.append("nonce", nonceToUse);
      
    } else {
      
    }
    return formData;
  }

  /**
   * Ajouter le nonce à une URL (GET)
   */
  static addToUrl(url: string, nonce?: string): string {
    const nonceToUse = nonce || this.getCurrentNonce();
    if (!nonceToUse) {
      return url;
    }

    const separator = url.includes("?") ? "&" : "?";
    return `${url}${separator}nonce=${encodeURIComponent(nonceToUse)}`;
  }

  /**
   * Obtenir la configuration actuelle du nonce
   */
  static getConfig(): NonceConfig | null {
    const nonce = this.getCurrentNonce();
    if (!nonce) {
      return null;
    }

    return {
      nonce,
      action: this.NONCE_ACTION,
      ajaxUrl: this.getAjaxUrl(),
      timestamp: Math.floor(Date.now() / 1000),
    };
  }

  /**
   * Vérifier si le nonce est valide
   */
  static isValid(): boolean {
    const nonce = this.getCurrentNonce();
    return nonce !== null && nonce.length > 0;
  }

  /**
   * Logger une information
   */
  static log(message: string): void {
    
  }

  /**
   * Logger une erreur
   */
  static logError(message: string): void {
    
  }
}

export default ClientNonceManager;


