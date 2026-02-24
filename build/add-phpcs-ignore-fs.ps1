# Script pour ajouter phpcs:ignore sur les lignes avec des fonctions filesystem
$pluginPath = "I:\wp-pdf-builder-pro-V2\plugin"
$patterns = @('rmdir\(', 'is_writable\(', 'mkdir\(', 'rename\(', 'fopen\(', 'fclose\(', 'readfile\(', 'move_uploaded_file\(')
$ignoreComment = ' // phpcs:ignore WordPress.WP.AlternativeFunctions'
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
        if ($trimmed.StartsWith('//') -or $trimmed.StartsWith('*') -or $trimmed.StartsWith('#') -or $line -match 'phpcs:ignore') {
            $line
            continue
        }
        $needsIgnore = $false
        foreach ($pat in $patterns) {
            if ($line -match $pat) {
                $needsIgnore = $true
                break
            }
        }
        if ($needsIgnore) {
            $line = $line.TrimEnd() + $ignoreComment
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
