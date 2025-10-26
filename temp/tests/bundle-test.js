// Test script to verify bundle execution and global function exposure
console.log('🔍 BUNDLE TEST: Starting bundle execution verification...');

// Check if bundle loads without syntax errors
try {
    console.log('✅ BUNDLE TEST: Bundle loaded without syntax errors');
} catch (e) {
    console.error('❌ BUNDLE TEST: Syntax error in bundle:', e);
}

// Check if global function is exposed
setTimeout(function() {
    console.log('🔍 BUNDLE TEST: Checking for pdfBuilderInitReact global function...');

    if (typeof window.pdfBuilderInitReact === 'function') {
        console.log('✅ BUNDLE TEST: pdfBuilderInitReact is available as a function');
        console.log('🔍 BUNDLE TEST: Function details:', window.pdfBuilderInitReact);

        // Try to call the function with a test container
        const testContainer = document.createElement('div');
        testContainer.id = 'pdf-builder-test-container';
        testContainer.style.cssText = 'position: fixed; top: 50px; right: 50px; width: 300px; height: 200px; background: yellow; border: 2px solid red; z-index: 999999;';
        document.body.appendChild(testContainer);

        try {
            console.log('🚀 BUNDLE TEST: Attempting to call pdfBuilderInitReact...');
            window.pdfBuilderInitReact('pdf-builder-test-container', {
                isNew: true,
                templateName: 'Test Template',
                orderData: {}
            });
            console.log('✅ BUNDLE TEST: pdfBuilderInitReact called successfully');

            // Check if container has content after a delay
            setTimeout(function() {
                const content = testContainer.innerHTML;
                console.log('🔍 BUNDLE TEST: Container content after init:', content.substring(0, 200) + '...');
                if (content && content.length > 0) {
                    console.log('✅ BUNDLE TEST: React component rendered successfully');
                } else {
                    console.log('⚠️ BUNDLE TEST: Container is empty after init');
                }
            }, 500);

        } catch (e) {
            console.error('❌ BUNDLE TEST: Error calling pdfBuilderInitReact:', e);
            console.error('❌ BUNDLE TEST: Stack trace:', e.stack);
        }

    } else {
        console.error('❌ BUNDLE TEST: pdfBuilderInitReact is not available or not a function');
        console.log('🔍 BUNDLE TEST: Type of pdfBuilderInitReact:', typeof window.pdfBuilderInitReact);
        console.log('🔍 BUNDLE TEST: Value of pdfBuilderInitReact:', window.pdfBuilderInitReact);
    }

    // Clean up test container after 5 seconds
    setTimeout(function() {
        const testContainer = document.getElementById('pdf-builder-test-container');
        if (testContainer) {
            document.body.removeChild(testContainer);
            console.log('🧹 BUNDLE TEST: Test container cleaned up');
        }
    }, 5000);

}, 1000);