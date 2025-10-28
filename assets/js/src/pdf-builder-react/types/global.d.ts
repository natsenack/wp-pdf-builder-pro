// Types globaux pour l'application PDF Builder
declare global {
  interface Window {
    pdfBuilderData: {
      templateId: string | null;
      isEditing: boolean;
      ajaxUrl: string;
      nonce: string;
    };
  }
}

export {};