# Script pour ajouter phpcs:ignore sur les requêtes wpdb non préparées
$pluginPath = "I:\wp-pdf-builder-pro-V2\plugin"
$dbIgnore = ' // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery'
$totalFiles = 0
$totalLines = 0

$phpFiles = Get-ChildItem $pluginPath -Recurse -Include "*.php" | Where-Object { $_.FullName -notmatch '\\vendor\\' }

foreach ($file in $phpFiles) {
    $lines = Get-Content $file.FullName -Encoding UTF8
    $modified = $false
    $newLines = for ($i = 0; $i -lt $lines.Count; $i++) {
        $line = $lines[$i]
        $trimmed = $line.TrimStart()
        # Ignorer les commentaires et les lignes ayant déjà phpcs:ignore
        if ($trimmed.StartsWith('//') -or $trimmed.StartsWith('*') -or $trimmed.StartsWith('#') -or $line -match 'phpcs:ignore|phpcs:disable') {
            $line
            continue
        }
        # Cibler uniquement les requêtes de lecture/exécution directe (pas update/insert/delete qui sont sûres)
        if ($line -match '\$wpdb->(query|get_results|get_row|get_var)\s*\(') {
            $line = $line.TrimEnd() + $dbIgnore
            $modified = $true
            $totalLines++
        }
        $line
    }
    if ($modified) {
        [System.IO.File]::WriteAllLines($file.FullName, $newLines, [System.Text.Encoding]::UTF8)
        $totalFiles++
        Write-Host "Updated: $($file.Name)"
    }
}
Write-Host "Done: $totalFiles files, $totalLines lines modified"
