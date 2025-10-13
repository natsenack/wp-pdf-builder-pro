console.log('🔍 DEBUG: MetaBoxes.js file loaded successfully');

jQuery(function () {
    console.log('🚀 MetaBoxes.js jQuery ready - WooCommerce PDF Invoice metabox initializing');

    jQuery('.woo-pdf-invoice-view').click(function () {
        console.log('🎯 BUTTON CLICKED: WooCommerce PDF Invoice View button clicked');

        var nonceValue = jQuery('.woo-pdf-invoice-nounce').val();
        var invoiceId = jQuery('.woo-pdf-invoice-list').val();

        console.log('🔑 NONCE VALUE:', nonceValue);
        console.log('🆔 INVOICE ID:', invoiceId);

        var url = nonceValue + '&invoice_id=' + invoiceId;
        console.log('🔗 GENERATED URL:', url);

        try {
            console.log('🪟 OPENING WINDOW...');
            window.open(url);
            console.log('✅ WINDOW OPENED SUCCESSFULLY');
        } catch (error) {
            console.error('❌ ERROR OPENING WINDOW:', error);
        }
    });

    console.log('✅ MetaBoxes.js initialization complete - button handler attached');
});
//# sourceMappingURL=MetaBoxes.js.map