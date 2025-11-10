// Types globaux pour l'application PDF Builder
declare global {
  interface Window {
    pdfBuilderData?: {
      templateId?: string | null;
      isEditing?: boolean;
      ajaxUrl?: string;
      nonce?: string;
      auto_save_interval?: number;
      existingTemplate?: unknown;
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
    pdfBuilderCanvasSettings?: Record<string, unknown> | {
      auto_save_interval?: number;
      auto_save_enabled?: boolean;
    };
  }
}

export {};