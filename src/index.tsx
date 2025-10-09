// Simple export pour commencer
const PDFBuilderPro = {
  version: '1.0.0',
  init: (containerId: string) => {
    console.log(`PDF Builder Pro initialized for container: ${containerId}`);
  }
};

// Attacher à window pour WordPress
if (typeof window !== 'undefined') {
  (window as any).PDFBuilderPro = PDFBuilderPro;
}

export default PDFBuilderPro;