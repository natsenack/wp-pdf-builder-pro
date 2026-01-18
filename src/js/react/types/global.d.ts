// Types globaux pour l'application PDF Builder
declare global {
  interface Window {
    pdfBuilderData?: {
      templateId?: string | number | null;
      isEditing?: boolean;
      ajaxUrl?: string;
      nonce?: string;
      auto_save_interval?: number;
      existingTemplate?: unknown;
      hasExistingData?: boolean;
    };
    pdfBuilderNonce?: string;
    pdfBuilderReactData?: {
      nonce?: string;
      ajaxUrl?: string;
      strings?: {
        loading?: string;
        error?: string;
      };
      auto_save_interval?: number;
    };
    pdfBuilderCanvasSettings?: Record<string, unknown>;
  }
}

export {};



