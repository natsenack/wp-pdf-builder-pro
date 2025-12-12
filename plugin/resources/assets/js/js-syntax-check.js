/**
 * PDF Builder Pro - JavaScript Syntax Diagnostic
 * Diagnostic script to identify try/catch imbalances
 */

(function() {
    'use strict';

    console.log('🔍 PDF Builder: Starting JavaScript syntax diagnostic...');

    // Function to count try/catch blocks in a script
    function analyzeScript(script) {
        if (!script || !script.textContent) return null;

        const content = script.textContent;
        const tryMatches = content.match(/try\s*\{/g) || [];
        const catchMatches = content.match(/\}\s*catch/g) || [];

        return {
            src: script.src || 'inline',
            tryCount: tryMatches.length,
            catchCount: catchMatches.length,
            balanced: tryMatches.length === catchMatches.length
        };
    }

    // Analyze all scripts on the page
    function diagnoseScripts() {
        const scripts = document.querySelectorAll('script');
        const results = [];
        let totalTry = 0;
        let totalCatch = 0;

        console.log(`📊 Found ${scripts.length} script tags to analyze`);

        scripts.forEach((script, index) => {
            const result = analyzeScript(script);
            if (result) {
                results.push(result);
                totalTry += result.tryCount;
                totalCatch += result.catchCount;

                if (!result.balanced) {
                    console.error(`❌ IMBALANCE in script ${index + 1}:`, result);
                } else {
                    console.log(`✅ Balanced script ${index + 1}:`, result);
                }
            }
        });

        console.log(`📈 TOTAL: try=${totalTry}, catch=${totalCatch}, balanced=${totalTry === totalCatch}`);

        if (totalTry !== totalCatch) {
            console.error('🚨 CRITICAL: Try/Catch imbalance detected across all scripts!');
            console.error('This may cause "Missing catch or finally after try" errors');
        } else {
            console.log('✅ All scripts appear to have balanced try/catch blocks');
        }

        // Store results for debugging
        window.pdfBuilderScriptDiagnostic = {
            results: results,
            totalTry: totalTry,
            totalCatch: totalCatch,
            balanced: totalTry === totalCatch
        };
    }

    // Run diagnostic when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', diagnoseScripts);
    } else {
        diagnoseScripts();
    }

    // Also run after a short delay to catch dynamically loaded scripts
    setTimeout(diagnoseScripts, 2000);

})();