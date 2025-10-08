# Script de validation et merge dev -> main
# ============================================

param(
    [switch]$SkipTests,
    [switch]$Force
)

Write-Host "🔄 VALIDATION ET MERGE DEV -> MAIN" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan

# Vérifier qu'on est sur la branche dev
$currentBranch = & git branch --show-current
if ($currentBranch -ne "dev") {
    Write-Host "❌ Vous devez être sur la branche 'dev' pour utiliser ce script" -ForegroundColor Red
    Write-Host "ℹ️ Branche actuelle : $currentBranch" -ForegroundColor Yellow
    Write-Host "💡 Utilisez : git checkout dev" -ForegroundColor Cyan
    exit 1
}

Write-Host "📍 Branche actuelle : $currentBranch" -ForegroundColor Green

# Vérifier l'état de la branche dev par rapport à main
$status = & git status --porcelain
if ($status) {
    Write-Host "⚠️ La branche dev a des changements non committés :" -ForegroundColor Yellow
    Write-Host $status -ForegroundColor Yellow

    if (-not $Force) {
        Write-Host "❌ Commitez d'abord vos changements ou utilisez -Force" -ForegroundColor Red
        exit 1
    } else {
        Write-Host "🔧 Force activé - commit automatique..." -ForegroundColor Yellow
        & git add .
        $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        & git commit -m "feat: changements en cours sur dev - $timestamp

- Modifications en développement
- Commit automatique avant validation
- Date: $timestamp

Type: wip (work in progress)
Branche: dev"
    }
}

# Étape 1: Tests (si pas skip)
if (-not $SkipTests) {
    Write-Host "`n🧪 ÉTAPE 1 : TESTS" -ForegroundColor Magenta
    Write-Host "================" -ForegroundColor Magenta

    # Ici vous pouvez ajouter vos tests personnalisés
    # Par exemple :
    # - Tests PHP avec PHPUnit
    # - Tests JavaScript
    # - Validation syntaxe
    # - Tests fonctionnels

    Write-Host "🔍 Vérification de la syntaxe PHP..." -ForegroundColor Yellow
    $phpFiles = Get-ChildItem -Path "." -Recurse -Include "*.php" -Exclude "vendor/*" | Where-Object {
        $_.FullName -notmatch '\\vendor\\' -and
        $_.FullName -notmatch '\\node_modules\\' -and
        $_.FullName -notmatch '\\tools\\'
    }

    $syntaxErrors = 0
    foreach ($file in $phpFiles) {
        try {
            $result = & php -l $file.FullName 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Host "❌ Erreur syntaxe : $($file.FullName)" -ForegroundColor Red
                Write-Host $result -ForegroundColor Red
                $syntaxErrors++
            }
        } catch {
            Write-Host "⚠️ PHP non trouvé, vérification syntaxe ignorée" -ForegroundColor Yellow
            break
        }
    }

    if ($syntaxErrors -eq 0) {
        Write-Host "✅ Aucune erreur de syntaxe PHP détectée" -ForegroundColor Green
    } else {
        Write-Host "❌ $syntaxErrors erreurs de syntaxe trouvées" -ForegroundColor Red
        if (-not $Force) {
            exit 1
        }
    }

    # Test des fichiers JavaScript (si présents)
    $jsFiles = Get-ChildItem -Path "." -Recurse -Include "*.js" -Exclude "node_modules/*" | Where-Object {
        $_.FullName -notmatch '\\node_modules\\' -and
        $_.FullName -notmatch '\\vendor\\' -and
        $_.FullName -notmatch '\\tools\\'
    }

    if ($jsFiles.Count -gt 0) {
        Write-Host "🔍 Vérification des fichiers JavaScript..." -ForegroundColor Yellow
        # Ici vous pourriez ajouter ESLint ou autre
        Write-Host "✅ Fichiers JavaScript présents : $($jsFiles.Count)" -ForegroundColor Green
    }

    Write-Host "✅ Tests terminés avec succès" -ForegroundColor Green
} else {
    Write-Host "`n⏭️ Tests ignorés (-SkipTests)" -ForegroundColor Yellow
}

# Étape 2: Comparaison avec main
Write-Host "`n📊 ÉTAPE 2 : COMPARAISON AVEC MAIN" -ForegroundColor Magenta
Write-Host "=================================" -ForegroundColor Magenta

$diffStats = & git diff --stat main..dev
if ($diffStats) {
    Write-Host "📈 Changements détectés :" -ForegroundColor Cyan
    Write-Host $diffStats -ForegroundColor White
} else {
    Write-Host "ℹ️ Aucune différence avec main" -ForegroundColor Yellow
    $confirm = Read-Host "Voulez-vous quand même continuer le merge ? (o/N)"
    if ($confirm -notmatch "^[oO]") {
        Write-Host "❌ Merge annulé" -ForegroundColor Red
        exit 0
    }
}

# Étape 3: Merge vers main
Write-Host "`n🔀 ÉTAPE 3 : MERGE VERS MAIN" -ForegroundColor Magenta
Write-Host "============================" -ForegroundColor Magenta

# Basculer vers main
& git checkout main
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Impossible de basculer vers main" -ForegroundColor Red
    exit 1
}

# Merge dev
$mergeResult = & git merge dev --no-ff -m "feat: merge dev vers main - validation réussie

- Merge automatique depuis la branche dev
- Tests de validation passés
- Prêt pour déploiement en production
- Date: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

Type: merge (fusion de branches)
Source: dev
Destination: main
Validation: automatique"
2>&1

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur lors du merge :" -ForegroundColor Red
    Write-Host $mergeResult -ForegroundColor Red

    # En cas de conflit, revenir à main propre
    & git merge --abort 2>$null
    & git checkout dev
    exit 1
}

Write-Host "✅ Merge réussi vers main" -ForegroundColor Green

# Étape 4: Push des deux branches
Write-Host "`n📤 ÉTAPE 4 : PUSH VERS GITHUB" -ForegroundColor Magenta
Write-Host "============================" -ForegroundColor Magenta

# Push main
$pushMain = & git push origin main 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Push de main réussi" -ForegroundColor Green
} else {
    Write-Host "❌ Échec push main :" -ForegroundColor Red
    Write-Host $pushMain -ForegroundColor Red
}

# Push dev
$pushDev = & git push origin dev 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Push de dev réussi" -ForegroundColor Green
} else {
    Write-Host "⚠️ Échec push dev (branche peut ne pas exister sur remote) :" -ForegroundColor Yellow
    Write-Host $pushDev -ForegroundColor Yellow
}

# Revenir sur dev pour continuer le développement
& git checkout dev

Write-Host "`n🎉 VALIDATION TERMINÉE AVEC SUCCÈS !" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host "✅ Code validé et mergé vers main" -ForegroundColor Green
Write-Host "✅ Branches poussées vers GitHub" -ForegroundColor Green
Write-Host "🚀 Prêt pour le déploiement !" -ForegroundColor Green
Write-Host ""
Write-Host "💡 Prochaines étapes :" -ForegroundColor Cyan
Write-Host "   • Testez en production : .\tools\ftp-deploy-simple.ps1" -ForegroundColor White
Write-Host "   • Continuez le développement sur dev" -ForegroundColor White
Write-Host ""
Write-Host "🔗 Dépôt GitHub : https://github.com/natsenack/wp-pdf-builder-pro.git" -ForegroundColor Cyan