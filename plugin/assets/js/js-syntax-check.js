/**
 * PDF Builder Pro - JavaScript Syntax Diagnostic
 * Diagnostic script to identify try/catch imbalances and line-specific errors
 */

(function() {
    'use strict';

    console.log('🔍 PDF Builder: Starting JavaScript syntax diagnostic...');

    // Function to count try/catch blocks in a script
    function analyzeScript(script, index) {
        if (!script || !script.textContent) return null;

        const content = script.textContent;
        const tryMatches = content.match(/try\s*\{/g) || [];
        const catchMatches = content.match(/\}\s*catch/g) || [];
        const finallyMatches = content.match(/\}\s*finally/g) || [];

        // Check for potential syntax issues around line 1889
        const lines = content.split('\n');
        let line1889 = '';
        if (lines.length >= 1889) {
            line1889 = lines[1888]; // 0-based index
        }

        return {
            index: index,
            src: script.src || 'inline',
            tryCount: tryMatches.length,
            catchCount: catchMatches.length,
            finallyCount: finallyMatches.length,
            balanced: tryMatches.length === catchMatches.length,
            totalLines: lines.length,
            line1889: line1889.trim(),
            hasLine1889: lines.length >= 1889
        };
    }

    // Function to check for syntax errors by attempting to parse scripts
    function checkScriptSyntax(script, index) {
        if (!script || !script.textContent) return null;

        const content = script.textContent.trim();

        // Skip scripts that contain HTML templates (Elementor templates)
        if (content.startsWith('<') || content.includes('<div') || content.includes('<span') || content.includes('<a ')) {
            console.log(`⏭️ Skipping HTML template script ${index} (${script.src || 'inline'})`);
            return { index: index, src: script.src || 'inline', syntaxValid: true, error: null, skipped: true };
        }

        try {
            // Try to create a new Function to check syntax
            new Function(content);
            return { index: index, src: script.src || 'inline', syntaxValid: true, error: null };
        } catch (syntaxError) {
            // Log the first 200 characters of the problematic script
            const scriptPreview = content.substring(0, 200).replace(/\n/g, '\\n');
            console.error(`❌ SYNTAX ERROR in script ${index} (${script.src || 'inline'}):`, syntaxError.message);
            console.error(`❌ SCRIPT PREVIEW (${index}): "${scriptPreview}..."`);
            return {
                index: index,
                src: script.src || 'inline',
                syntaxValid: false,
                error: syntaxError.message,
                scriptPreview: scriptPreview,
                line: syntaxError.lineNumber,
                column: syntaxError.columnNumber
            };
        }
    }

    // Analyze all scripts on the page
    function diagnoseScripts() {
        const scripts = document.querySelectorAll('script');
        const results = [];
        let totalTry = 0;
        let totalCatch = 0;
        let syntaxErrors = [];

        console.log(`📊 Found ${scripts.length} script tags to analyze`);

        scripts.forEach((script, index) => {
            const result = analyzeScript(script, index + 1);
            if (result) {
                results.push(result);
                totalTry += result.tryCount;
                totalCatch += result.catchCount;

                if (!result.balanced) {
                    console.error(`❌ IMBALANCE in script ${index + 1}:`, result);
                } else {
                    console.log(`✅ Balanced script ${index + 1}: try=${result.tryCount}, catch=${result.catchCount}, lines=${result.totalLines}`);
                }

                // Check if this script contains line 1889
                if (result.hasLine1889 && result.line1889) {
                    console.log(`🎯 Script ${index + 1} contains line 1889: "${result.line1889}"`);
                }

                // Check syntax
                const syntaxCheck = checkScriptSyntax(script, index + 1);
                if (syntaxCheck && !syntaxCheck.syntaxValid) {
                    syntaxErrors.push(syntaxCheck);
                }
            }
        });

        console.log(`📈 TOTAL: try=${totalTry}, catch=${totalCatch}, balanced=${totalTry === totalCatch}`);
        console.log(`🚨 SYNTAX ERRORS FOUND: ${syntaxErrors.length}`);

        if (syntaxErrors.length > 0) {
            console.error('🚨 CRITICAL: Syntax errors detected:', syntaxErrors);
        }

        if (totalTry !== totalCatch) {
            console.error('🚨 CRITICAL: Try/Catch imbalance detected across all scripts!');
        } else {
            console.log('✅ All scripts appear to have balanced try/catch blocks');
        }

        // Store results for debugging
        window.pdfBuilderScriptDiagnostic = {
            results: results,
            totalTry: totalTry,
            totalCatch: totalCatch,
            balanced: totalTry === totalCatch,
            syntaxErrors: syntaxErrors,
            scriptsAnalyzed: scripts.length
        };

        return { results, syntaxErrors };
    }

    // Function to check a specific script by URL
    window.checkScriptByUrl = function(urlPattern) {
        console.log(`🔍 Checking scripts matching pattern: ${urlPattern}`);
        const scripts = document.querySelectorAll('script[src]');
        let found = false;

        scripts.forEach(script => {
            if (script.src && script.src.includes(urlPattern)) {
                console.log(`🎯 Found matching script: ${script.src}`);
                const result = checkScriptSyntax(script, 'manual-check');
                if (result && !result.syntaxValid) {
                    console.error('❌ SYNTAX ERROR FOUND:', result);
                } else {
                    console.log('✅ Script syntax OK');
                }
                found = true;
            }
        });

        if (!found) {
            console.log(`❓ No scripts found matching pattern: ${urlPattern}`);
        }
    };

    // Function to analyze all scripts with detailed logging
    window.diagnoseAllScripts = function() {
        console.log('🔍 Running comprehensive script analysis...');
        const allScripts = document.querySelectorAll('script');
        console.log(`📊 Total scripts found: ${allScripts.length}`);

        allScripts.forEach((script, index) => {
            const src = script.src || 'inline';
            const hasSrc = !!script.src;
            const contentLength = script.textContent ? script.textContent.length : 0;

            console.log(`Script ${index + 1}: ${src} (${hasSrc ? 'external' : 'inline'}, ${contentLength} chars)`);

            if (hasSrc && src.includes('e821388a4c81a3eb329f7487abf29bfa')) {
                console.log('🎯 FOUND PROBLEMATIC SCRIPT! Analyzing in detail...');
                const result = checkScriptSyntax(script, index + 1);
                if (result && !result.syntaxValid) {
                    console.error('🚨 CRITICAL ERROR in problematic script:', result);
                    // Try to identify the problematic line
                    if (script.textContent) {
                        const lines = script.textContent.split('\n');
                        if (lines.length >= 9871) {
                            const problematicLine = lines[9870]; // 0-based index
                            console.error('🚨 Line 9871 content:', problematicLine);
                            console.error('🚨 Characters around error:', problematicLine.substring(0, 50));
                        }
                    }
                }
            }
        });
    };

    // Run diagnostic when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 DOM loaded, starting initial diagnostic...');
            diagnoseScripts();
            console.log('🔧 Diagnostic functions available: checkScriptByUrl(), diagnoseAllScripts()');
        });
    } else {
        console.log('📋 DOM already loaded, starting diagnostic...');
        diagnoseScripts();
        console.log('🔧 Diagnostic functions available: checkScriptByUrl(), diagnoseAllScripts()');
    }

    // Also run after delays to catch dynamically loaded scripts
    setTimeout(() => {
        console.log('🔄 Running delayed diagnostic (2s)...');
        diagnoseScripts();
    }, 2000);

    setTimeout(() => {
        console.log('🔄 Running delayed diagnostic (5s) with comprehensive analysis...');
        diagnoseScripts();
        // Also run the comprehensive analysis
        setTimeout(() => {
            window.diagnoseAllScripts();
        }, 1000);
    }, 5000);

})();