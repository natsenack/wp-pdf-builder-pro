// Test script to verify PDF Builder fixes
// Run this in browser console on admin pages

(function() {
    console.log('🧪 PDF Builder Pro - Test Script');
    console.log('================================');

    // Test 1: Check global variables
    console.log('✅ Test 1: Global variables');
    console.log('   window.pdfBuilderAjax:', typeof window.pdfBuilderAjax, window.pdfBuilderAjax ? '✓' : '✗');
    console.log('   window.pdfBuilderPro:', typeof window.pdfBuilderPro, window.pdfBuilderPro ? '✓' : '✗');
    console.log('   window.pdfBuilderData:', typeof window.pdfBuilderData, window.pdfBuilderData ? '✓' : '✗');

    // Test 2: Check nonce availability
    const nonce = window.pdfBuilderPro?.nonce || window.pdfBuilderAjax?.nonce || '';
    console.log('✅ Test 2: Nonce availability');
    console.log('   Nonce found:', nonce ? '✓ (' + nonce.substring(0, 10) + '...)' : '✗');

    // Test 3: Test AJAX call (if on order edit page)
    const orderId = new URLSearchParams(window.location.search).get('id');
    if (orderId && window.pdfBuilderAjax?.ajaxurl) {
        console.log('✅ Test 3: AJAX test for order', orderId);

        const testAjax = async () => {
            try {
                const formData = new FormData();
                formData.append('action', 'pdf_builder_get_order_data');
                formData.append('order_id', orderId);
                formData.append('nonce', nonce);

                const response = await fetch(window.pdfBuilderAjax.ajaxurl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();
                console.log('   AJAX result:', result.success ? '✓ SUCCESS' : '✗ FAILED');
                if (!result.success) {
                    console.log('   Error:', result.data?.message || 'Unknown error');
                }
            } catch (error) {
                console.log('   AJAX error:', error.message);
            }
        };

        testAjax();
    } else {
        console.log('ℹ️  Test 3: Skipped (not on order edit page or no ajaxurl)');
    }

    console.log('================================');
    console.log('Test completed. Check results above.');
})();