<?php
/**
 * Script de débogage pour analyser les tokens PHP
 */

$filePath = 'I:\wp-pdf-builder-pro-V2\plugin\src\utilities\PDF_Builder_Onboarding_Manager.php';
$content = file_get_contents($filePath);

$wordpressFunctions = ["add_action"];

$tokens = token_get_all($content);

echo "Analyse des tokens pour: $filePath\n";
echo "=====================================\n";

for ($i = 0; $i < count($tokens); $i++) {
    $token = $tokens[$i];

    if (is_array($token)) {
        $tokenType = $token[0];
        $tokenValue = $token[1];

        // Look for T_STRING tokens that match WordPress functions
        if ($tokenType === T_STRING && in_array($tokenValue, $wordpressFunctions)) {
            echo "Trouvé: $tokenValue à la position $i\n";

            // Check if next token is opening parenthesis (function call)
            $nextToken = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
            echo "  Next token: " . (is_array($nextToken) ? token_name($nextToken[0]) . ":'" . $nextToken[1] . "'" : "'$nextToken'") . "\n";

            if ($nextToken === '(' || (is_array($nextToken) && $nextToken[0] === T_WHITESPACE && isset($tokens[$i + 2]) && $tokens[$i + 2] === '(')) {
                echo "  C'est un appel de fonction\n";

                // Check if already prefixed with backslash
                $prevToken = isset($tokens[$i - 1]) ? $tokens[$i - 1] : null;
                echo "  Prev token: " . (is_array($prevToken) ? token_name($prevToken[0]) . ":'" . $prevToken[1] . "'" : "'$prevToken'") . "\n";

                if ($prevToken !== '\\' && (!is_array($prevToken) || $prevToken[0] !== T_NS_SEPARATOR)) {
                    echo "  Pas encore préfixé\n";

                    // Check if we're inside a string or comment
                    $inString = false;
                    $inComment = false;

                    // Scan backwards to check context
                    for ($j = $i - 1; $j >= 0; $j--) {
                        $checkToken = $tokens[$j];

                        if (is_array($checkToken)) {
                            if ($checkToken[0] === T_COMMENT || $checkToken[0] === T_DOC_COMMENT) {
                                $inComment = true;
                                echo "  Dans un commentaire\n";
                                break;
                            }
                            if ($checkToken[0] === T_CONSTANT_ENCAPSED_STRING || $checkToken[0] === T_ENCAPSED_AND_WHITESPACE) {
                                $inString = true;
                                echo "  Dans une chaîne\n";
                                break;
                            }
                        } elseif ($checkToken === '"' || $checkToken === "'") {
                            $inString = true;
                            echo "  Dans une chaîne (délimiteur: $checkToken)\n";
                            break;
                        }
                    }

                    if (!$inString && !$inComment) {
                        echo "  *** DEVRAIT ÊTRE CORRIGÉ ***\n";
                    } else {
                        echo "  Ne pas corriger (contexte spécial)\n";
                    }
                } else {
                    echo "  Déjà préfixé\n";
                }
            } else {
                echo "  Pas un appel de fonction\n";
            }
            echo "\n";
        }
    }
}
?>
