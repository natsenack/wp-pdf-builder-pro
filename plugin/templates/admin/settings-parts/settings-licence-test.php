<?php
/**
 * Test simple pour le fichier licence - version diagnostic
 */
if (!defined('ABSPATH')) exit('Direct access forbidden');
?>
<div class="pdf-builder-section">
    <h2>🧪 Test Licence - Version Diagnostic</h2>
    <p>Ce fichier de test s'est chargé correctement !</p>
    <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <div style="background: #e7f3ff; border: 1px solid #0073aa; padding: 10px; margin: 10px 0;">
        <h3>🔍 Diagnostic du fichier licence</h3>
        <ul>
            <li><strong>ABSPATH défini:</strong> <?php echo defined('ABSPATH') ? 'OUI' : 'NON'; ?></li>
            <li><strong>Fichier licence chargé:</strong> ✅ OUI</li>
            <li><strong>Erreurs PHP:</strong> <?php echo error_get_last() ? 'OUI' : 'NON'; ?></li>
        </ul>
    </div>
</div>

<script>
console.log('🧪 PDF BUILDER - Script licence inline chargé!');
</script>