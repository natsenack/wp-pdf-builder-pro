<?php
/**
 * PDF Builder Pro - Direct Database Check (No WordPress Auth)
 * Diagnostique les tables de base de données
 */

// Configuration directe
$db_config = parse_url('mysql://' . (getenv('DB_USER') ?: 'root') . ':' . (getenv('DB_PASSWORD') ?: '') . '@' . (getenv('DB_HOST') ?: 'localhost') . '/' . (getenv('DB_NAME') ?: 'wp_pdf_builder'));

// Pour test: charger wp-config.php s'il existe
if (file_exists(__DIR__ . '/../../wp-config.php')) {
    require_once(__DIR__ . '/../../wp-config.php');
}

// Connexion directe
$mysqli = new mysqli(
    defined('DB_HOST') ? DB_HOST : 'localhost',
    defined('DB_USER') ? DB_USER : 'root',
    defined('DB_PASSWORD') ? DB_PASSWORD : '',
    defined('DB_NAME') ? DB_NAME : 'wordpress'
);

if ($mysqli->connect_error) {
    die('Erreur de connexion: ' . $mysqli->connect_error);
}

$prefix = defined('$table_prefix') ? $table_prefix : 'wp_';
$tables_required = [
    $prefix . 'pdf_builder_settings',
    $prefix . 'pdf_builder_templates'
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>PDF Builder Pro - Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 PDF Builder Pro - Database Check</h1>
    
    <div class="status info">
        <strong>Database:</strong> <?php echo DB_NAME; ?><br>
        <strong>Prefix:</strong> <?php echo htmlspecialchars($prefix); ?><br>
        <strong>Host:</strong> <?php echo htmlspecialchars(DB_HOST); ?>
    </div>

    <h2>Tables Status</h2>
    
    <?php foreach ($tables_required as $table) : ?>
        <?php
            $result = $mysqli->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table'");
            $exists = $result && $result->num_rows > 0;
        ?>
        <div class="status <?php echo $exists ? 'ok' : 'error'; ?>">
            <strong><?php echo htmlspecialchars($table); ?></strong><br>
            <?php if ($exists) : ?>
                ✓ EXISTS
                <?php
                    // Show table info
                    $info = $mysqli->query("DESCRIBE $table");
                    echo "<br><small>Columns: " . $info->num_rows . "</small>";
                ?>
            <?php else : ?>
                ✗ MISSING
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <h2>Create Tables</h2>
    
    <?php
        // Try to create missing tables
        foreach ($tables_required as $table) {
            $result = $mysqli->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table'");
            if (!($result && $result->num_rows > 0)) {
                // Table doesn't exist, create it
                if ($table === $prefix . 'pdf_builder_settings') {
                    $sql = "CREATE TABLE $table (
                        option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        option_name varchar(191) NOT NULL DEFAULT '',
                        option_value longtext NOT NULL,
                        autoload varchar(20) NOT NULL DEFAULT 'yes',
                        PRIMARY KEY (option_id),
                        UNIQUE KEY option_name (option_name)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                } else {
                    $sql = "CREATE TABLE $table (
                        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        template_name varchar(191) NOT NULL,
                        template_data longtext,
                        created_at datetime DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        UNIQUE KEY template_name (template_name)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                }
                
                if ($mysqli->query($sql)) {
                    echo '<div class="status ok">✓ Created: <code>' . htmlspecialchars($table) . '</code></div>';
                } else {
                    echo '<div class="status error">✗ Error creating: <code>' . htmlspecialchars($table) . '</code><br>Error: ' . htmlspecialchars($mysqli->error) . '</div>';
                }
            }
        }
    ?>

    <p><strong>Done!</strong> Refresh this page to verify tables were created.</p>
</body>
</html>
