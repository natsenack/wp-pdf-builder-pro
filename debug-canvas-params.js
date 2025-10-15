// Debug script pour vérifier les paramètres canvas
console.log('=== DEBUG CANVAS PARAMETERS ===');
console.log('window.pdfBuilderCanvasSettings:', window.pdfBuilderCanvasSettings);

if (window.pdfBuilderCanvasSettings) {
  console.log('pan_with_mouse:', window.pdfBuilderCanvasSettings.pan_with_mouse);
  console.log('smooth_zoom:', window.pdfBuilderCanvasSettings.smooth_zoom);
  console.log('zoom_with_wheel:', window.pdfBuilderCanvasSettings.zoom_with_wheel);
} else {
  console.log('❌ window.pdfBuilderCanvasSettings is not defined!');
}

console.log('=== END DEBUG ===');