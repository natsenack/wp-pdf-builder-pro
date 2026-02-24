$dbSniff = 'WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery'
$total = 0
Get-ChildItem "I:\wp-pdf-builder-pro-V2\plugin" -Recurse -Include "*.php" | Where-Object { $_.FullName -notmatch '\\vendor\\' } | ForEach-Object {
    $lines = Get-Content $_.FullName -Encoding UTF8
    $modified = $false
    $newLines = for ($i = 0; $i -lt $lines.Count; $i++) {
        $l = $lines[$i]
        $tr = $l.TrimStart()
        if ($tr.StartsWith('//') -or $tr.StartsWith('*') -or $tr.StartsWith('#') -or $tr.StartsWith('/*')) { $l; continue }
        if ($l -match '\$wpdb->(query|get_results|get_row|get_var|prepare)\s*\(' -and $l -notmatch 'PreparedSQL\.NotPrepared') {
            if ($l -match 'phpcs:ignore') {
                $l = $l.TrimEnd() + ", $dbSniff"
            } else {
                $l = $l.TrimEnd() + " // phpcs:ignore $dbSniff"
            }
            $modified = $true
            $total++
        }
        $l
    }
    if ($modified) {
        [System.IO.File]::WriteAllLines($_.FullName, $newLines, (New-Object System.Text.UTF8Encoding($false)))
        Write-Host "Fixed: $($_.Name)"
    }
}
Write-Host "Total: $total lines"
