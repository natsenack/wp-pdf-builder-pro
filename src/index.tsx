// Simple export pour commencer
const PDFBuilderPro = {
  version: '1.0.0',
  init: (containerId: string) => {
  }
};

// Attacher à window pour WordPress
if (typeof window !== 'undefined') {
  (window as any).PDFBuilderPro = PDFBuilderPro;
}

export default PDFBuilderPro;