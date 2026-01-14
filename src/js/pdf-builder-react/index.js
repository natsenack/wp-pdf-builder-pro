// Simple export
console.log('index.js is being executed');
const exports = {
  initPDFBuilderReact: () => console.log('init'),
  loadTemplate: () => console.log('load'),
  getEditorState: () => ({}),
  setEditorState: () => {},
  getCurrentTemplate: () => null,
  exportTemplate: () => {},
  saveTemplate: () => {},
  registerEditorInstance: () => {},
  resetAPI: () => {},
  updateCanvasDimensions: () => {},
  _isWebpackBundle: true
};

if (typeof window !== 'undefined') {
  window.pdfBuilderReact = exports;
}
