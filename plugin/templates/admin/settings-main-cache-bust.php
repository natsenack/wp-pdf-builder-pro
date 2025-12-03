<?php
// CACHE-BUSTED VERSION - Fichier créé le 2025-12-03 pour contourner le cache serveur
// Ce fichier n'existe pas dans le cache, donc il sera chargé immédiatement

// FORCE NO-CACHE HEADERS
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// DEBUG MESSAGE
echo "<script>console.log('🚀 CACHE BUSTED FILE LOADED - " . date('H:i:s') . " - UNIQUE ID: " . uniqid() . "');</script>";

// AJOUT DU BOUTON FLOTTANT IMMÉDIATEMENT
echo '
<!-- Bouton de sauvegarde flottant - CACHE BUSTED VERSION -->
<div id="pdf-builder-floating-save-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: block; background: #fff; border: 2px solid #007cba; border-radius: 8px; padding: 5px;">
    <button id="pdf-builder-floating-save-btn" class="button button-primary" style="padding: 12px 20px; font-size: 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 8px; transition: all 0.3s ease;">
        💾 CACHE BUSTED - Enregistrer
    </button>
</div>
<script>
console.log("🎯 BOUTON FLOTTANT AJOUTÉ VIA CACHE BUST - " + new Date().toLocaleTimeString());
</script>
';

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