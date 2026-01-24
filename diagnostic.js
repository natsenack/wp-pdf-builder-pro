/**
 * Diagnostic script to test PDF Builder React initialization
 * Run this in browser console on the PDF editor page
 */

(function() {
    console.log('🔍 PDF Builder React Diagnostic');
    console.log('================================');

    // Check if scripts are loaded
    const scripts = document.querySelectorAll('script[src*="pdf-builder"]');
    console.log('📄 Scripts loaded:', scripts.length);
    scripts.forEach(script => {
        console.log('  -', script.src);
    });

    // Check window objects
    console.log('🌐 Window objects:');
    console.log('  - window.pdfBuilderReact:', !!window.pdfBuilderReact);
    console.log('  - window.initPDFBuilderReact:', !!window.initPDFBuilderReact);

    if (window.pdfBuilderReact) {
        console.log('  - _isWebpackBundle:', window.pdfBuilderReact._isWebpackBundle);
        console.log('  - initPDFBuilderReact function:', typeof window.pdfBuilderReact.initPDFBuilderReact);
        console.log('  - Available methods:', Object.keys(window.pdfBuilderReact));
    }

    // Check container
    const container = document.getElementById('pdf-builder-react-root');
    console.log('📦 Container found:', !!container);

    if (container) {
        console.log('  - Container initialized:', container.hasAttribute('data-react-initialized'));
    }

    // Test initialization
    console.log('🧪 Testing initialization...');
    if (window.initPDFBuilderReact) {
        try {
            const result = window.initPDFBuilderReact();
            console.log('  - initPDFBuilderReact() result:', result);
        } catch (error) {
            console.error('  - initPDFBuilderReact() error:', error);
        }
    } else {
        console.error('  - initPDFBuilderReact not available');
    }

    console.log('================================');
    console.log('🔍 Diagnostic complete');
})();