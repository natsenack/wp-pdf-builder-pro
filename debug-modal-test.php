<?php
// Debug Modal Test for PDF Builder Pro
echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Modal Test - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007cba; color: white; }
        .btn-secondary { background: #f1f1f1; color: #333; }
        .modal-test { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .modal-content { background: white; padding: 20px; border-radius: 8px; max-width: 500px; width: 90%; }
        .close-btn { float: right; cursor: pointer; font-size: 20px; }
    </style>
</head>
<body>
    <h1>🧪 Debug Modal Test - PDF Builder Pro</h1>

    <div class='test-section'>
        <h2>1. Basic Modal Test</h2>
        <button class='btn btn-primary' onclick='showBasicModal()'>Show Basic Modal</button>
        <button class='btn btn-secondary' onclick='hideBasicModal()'>Hide Modal</button>
        <div id='basic-modal' class='modal-test'>
            <div class='modal-content'>
                <span class='close-btn' onclick='hideBasicModal()'>×</span>
                <h3>Basic Modal Test</h3>
                <p>This is a basic modal to test if modals work at all.</p>
                <p>If you can see this, basic modals are working!</p>
            </div>
        </div>
    </div>

    <div class='test-section'>
        <h2>2. Z-Index Test</h2>
        <p>Z-index of modal: <span id='zindex-display'>9999</span></p>
        <button class='btn btn-secondary' onclick='testZIndex()'>Test Z-Index</button>
    </div>

    <div class='test-section'>
        <h2>3. JavaScript Console Test</h2>
        <button class='btn btn-secondary' onclick='testConsole()'>Test Console Logs</button>
        <p>Open browser console (F12) and click the button above.</p>
    </div>

    <div class='test-section'>
        <h2>4. PDF Builder Components Check</h2>
        <div id='component-check'>
            <p>Checking if PDF Builder components are loaded...</p>
        </div>
    </div>

    <script>
        function showBasicModal() {
            document.getElementById('basic-modal').style.display = 'flex';
            console.log('✅ Basic modal shown');
        }

        function hideBasicModal() {
            document.getElementById('basic-modal').style.display = 'none';
            console.log('✅ Basic modal hidden');
        }

        function testZIndex() {
            const modal = document.getElementById('basic-modal');
            const computedStyle = window.getComputedStyle(modal);
            const zIndex = computedStyle.getPropertyValue('z-index');
            document.getElementById('zindex-display').textContent = zIndex;
            console.log('Modal z-index:', zIndex);
        }

        function testConsole() {
            console.log('🧪 Console test started');
            console.log('Current URL:', window.location.href);
            console.log('User Agent:', navigator.userAgent);
            console.log('Window dimensions:', window.innerWidth, 'x', window.innerHeight);

            // Test if PDF Builder globals exist
            console.log('window.pdfBuilderAjax exists:', typeof window.pdfBuilderAjax !== 'undefined');
            if (typeof window.pdfBuilderAjax !== 'undefined') {
                console.log('pdfBuilderAjax:', window.pdfBuilderAjax);
            }

            // Test React
            console.log('React available:', typeof React !== 'undefined');
            console.log('ReactDOM available:', typeof ReactDOM !== 'undefined');

            console.log('✅ Console test completed');
        }

        // Auto-run component check
        window.addEventListener('load', function() {
            const checkDiv = document.getElementById('component-check');

            // Check if PDF Builder scripts are loaded
            const scripts = document.querySelectorAll('script');
            let pdfBuilderScripts = [];
            scripts.forEach(script => {
                if (script.src && script.src.includes('pdf-builder')) {
                    pdfBuilderScripts.push(script.src);
                }
            });

            let scriptList = '<ul>';
            pdfBuilderScripts.forEach(function(src) {
                scriptList += '<li>' + src.split('/').pop() + '</li>';
            });
            scriptList += '</ul>';

            let reactStatus = typeof React !== 'undefined' ? '✅ Yes' : '❌ No';
            let reactDomStatus = typeof ReactDOM !== 'undefined' ? '✅ Yes' : '❌ No';
            let ajaxStatus = typeof window.pdfBuilderAjax !== 'undefined' ? '✅ Yes' : '❌ No';

            checkDiv.innerHTML = `
                <p><strong>PDF Builder Scripts Found:</strong> ` + pdfBuilderScripts.length + `</p>
                ` + scriptList + `
                <p><strong>React Available:</strong> ` + reactStatus + `</p>
                <p><strong>ReactDOM Available:</strong> ` + reactDomStatus + `</p>
                <p><strong>PDF Builder Ajax:</strong> ` + ajaxStatus + `</p>
            `;

            console.log('🔍 Component check completed');
        });

        // Test keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideBasicModal();
                console.log('ESC key pressed - modal closed');
            }
        });
    </script>
</body>
</html>";
?>