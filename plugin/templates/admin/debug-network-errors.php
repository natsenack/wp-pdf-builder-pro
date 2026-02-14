<!-- Script de débogage des erreurs réseau -->
<script>
(function() {
    'use strict';
    
    console.log('%c[NETWORK DEBUG] Monitoring AJAX & Script Loading', 'background: #ff0000; color: #fff; padding: 5px;');
    
    // 1. Intercepter toutes les requêtes AJAX jQuery
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxError(function(event, jqXHR, settings, thrownError) {
            console.error('%c[AJAX ERROR]', 'background: #ff0000; color: #fff; padding: 3px;', {
                url: settings.url,
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText ? jqXHR.responseText.substring(0, 500) : '',
                error: thrownError
            });
            
            // Si c'est du HTML au lieu de JSON
            if (jqXHR.responseText && jqXHR.responseText.trim().startsWith('<')) {
                console.error('%c[HTML INSTEAD OF JSON]', 'background: #ff6600; color: #fff; padding: 3px;', settings.url);
                console.log('Response preview:', jqXHR.responseText.substring(0, 1000));
            }
        });
        
        jQuery(document).ajaxComplete(function(event, jqXHR, settings) {
            if (jqXHR.status >= 400) {
                console.warn('%c[AJAX ' + jqXHR.status + ']', 'background: #ff9900; color: #fff; padding: 3px;', settings.url);
            }
        });
    }
    
    // 2. Intercepter les erreurs de chargement de script
    window.addEventListener('error', function(e) {
        if (e.target && (e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK')) {
            console.error('%c[RESOURCE LOAD ERROR]', 'background: #cc0000; color: #fff; padding: 3px;', {
                type: e.target.tagName,
                src: e.target.src || e.target.href,
                message: e.message
            });
        } else if (e.message && e.message.includes('Unexpected token')) {
            console.error('%c[SYNTAX ERROR - Probable HTML in JS]', 'background: #990000; color: #fff; padding: 3px;', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno
            });
            
            // Essayer de récupérer le contenu du fichier
            if (e.filename) {
                fetch(e.filename)
                    .then(r => r.text())
                    .then(text => {
                        console.log('%c[FILE CONTENT]', 'background: #0066cc; color: #fff; padding: 3px;', e.filename);
                        console.log(text.substring(0, 500));
                    })
                    .catch(err => console.error('Cannot fetch:', err));
            }
        }
    }, true);
    
    // 3. Intercepter les requêtes Fetch
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                if (!response.ok) {
                    console.warn('%c[FETCH ERROR ' + response.status + ']', 'background: #ff9900; color: #fff; padding: 3px;', args[0]);
                    
                    // Cloner pour lire le contenu sans consommer la réponse
                    response.clone().text().then(text => {
                        if (text.trim().startsWith('<')) {
                            console.error('%c[HTML RESPONSE IN FETCH]', 'background: #ff0000; color: #fff; padding: 3px;', args[0]);
                            console.log(text.substring(0, 1000));
                        }
                    });
                }
                return response;
            })
            .catch(error => {
                console.error('%c[FETCH NETWORK ERROR]', 'background: #cc0000; color: #fff; padding: 3px;', args[0], error);
                throw error;
            });
    };
    
    // 4. Surveiller les scripts ajoutés dynamiquement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.tagName === 'SCRIPT') {
                    console.log('%c[DYNAMIC SCRIPT ADDED]', 'background: #0099ff; color: #fff; padding: 3px;', {
                        src: node.src || '(inline)',
                        content: node.src ? '' : node.textContent.substring(0, 200)
                    });
                    
                    // Vérifier si c'est un script inline qui pourrait poser problème
                    if (!node.src && node.textContent.includes('<')) {
                        console.warn('%c[INLINE SCRIPT WITH HTML CHARS]', 'background: #ff6600; color: #fff; padding: 3px;', node.textContent.substring(0, 300));
                    }
                }
            });
        });
    });
    
    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });
    
    console.log('%c[NETWORK DEBUG] Monitoring active', 'background: #00cc00; color: #fff; padding: 5px;');
})();
</script>
