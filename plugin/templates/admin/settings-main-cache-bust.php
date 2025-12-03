<?php
// CACHE-BUSTED VERSION - Fichier créé le 2025-12-03 pour contourner le cache serveur
// Ce fichier n'existe pas dans le cache, donc il sera chargé immédiatement

// FORCE NO-CACHE HEADERS
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// DEBUG MESSAGE
echo "<script>console.log('🚀 CACHE BUSTED FILE LOADED - " . date('H:i:s') . " - UNIQUE ID: " . uniqid() . "');</script>";

// FORCE RELOAD SCRIPT
echo "<script>
if (!localStorage.getItem('cache_busted_20251203')) {
    localStorage.setItem('cache_busted_20251203', 'true');
    console.log('🔄 Forcing page reload to bypass cache...');
    setTimeout(function() {
        window.location.reload(true);
    }, 100);
}
</script>";
?>