#!/usr/bin/env python3
"""
Supprime les blocs CSS dupliqués dans les fichiers source src/css/*.css
Règle : quand un sélecteur apparaît N fois, les propriétés sont fusionnées
         (les déclarations ultérieures écrasent les précédentes).
Les blocs @keyframes, @media, @font-face et les commentaires sont conservés.
"""

import re
import sys
import os
import copy

SRC_DIR = os.path.join(os.path.dirname(__file__), '..', 'src', 'css')

# ──────────────────────────────────────────────────────────────────────────────
# Tokenizer minimaliste : découpe le CSS en tokens de haut niveau
# ──────────────────────────────────────────────────────────────────────────────

def tokenize(css: str) -> list:
    """
    Retourne une liste de fragments : chaque élément est un dict :
      {'type': 'comment'|'at-block'|'rule'|'whitespace', 'raw': str, ...}
    """
    tokens = []
    i = 0
    n = len(css)

    while i < n:
        # Commentaires /* … */
        if css[i:i+2] == '/*':
            end = css.find('*/', i+2)
            end = end + 2 if end != -1 else n
            tokens.append({'type': 'comment', 'raw': css[i:end]})
            i = end
            continue

        # Règles @  (at-block ou at-statement)
        if css[i] == '@':
            # Lire le nom
            m = re.match(r'@([\w-]+)', css[i:])
            at_name = m.group(1) if m else ''
            # Chercher le prochain { ou ;
            j = i + len(at_name) + 1
            while j < n and css[j] not in ('{', ';'):
                j += 1
            if j < n and css[j] == ';':
                # at-statement sans bloc : @import, @charset, @use…
                tokens.append({'type': 'at-statement', 'raw': css[i:j+1], 'name': at_name})
                i = j + 1
                continue
            if j < n and css[j] == '{':
                # at-block : @media, @keyframes, @font-face, @supports…
                depth = 0
                k = j
                while k < n:
                    if css[k] == '{':
                        depth += 1
                    elif css[k] == '}':
                        depth -= 1
                        if depth == 0:
                            k += 1
                            break
                    k += 1
                tokens.append({'type': 'at-block', 'raw': css[i:k], 'name': at_name})
                i = k
                continue

        # Règle normale (sélecteur + bloc)
        if css[i] == '}':
            # stray closing brace – skip
            i += 1
            continue

        # Espace/newline seul
        if css[i] in (' ', '\t', '\n', '\r'):
            j = i
            while j < n and css[j] in (' ', '\t', '\n', '\r'):
                j += 1
            tokens.append({'type': 'whitespace', 'raw': css[i:j]})
            i = j
            continue

        # Lire jusqu'au prochain { → c'est le sélecteur
        brace = css.find('{', i)
        if brace == -1:
            # Fin de fichier sans bloc – texte résiduel
            tokens.append({'type': 'whitespace', 'raw': css[i:]})
            break
        selector = css[i:brace].strip()

        # Lire le bloc de propriétés (tenant compte des braces imbriquées rares)
        depth = 0
        k = brace
        while k < n:
            if css[k] == '{':
                depth += 1
            elif css[k] == '}':
                depth -= 1
                if depth == 0:
                    k += 1
                    break
            k += 1
        raw_block = css[i:k]
        body = css[brace+1:k-1]  # contenu entre { et }

        tokens.append({
            'type': 'rule',
            'raw': raw_block,
            'selector': selector,
            'body': body,
        })
        i = k

    return tokens


# ──────────────────────────────────────────────────────────────────────────────
# Merge des propriétés CSS d'un bloc
# ──────────────────────────────────────────────────────────────────────────────

def parse_declarations(body: str) -> list:
    """
    Retourne une liste ordonnée de (prop, value) en préservant l'ordre d'apparition.
    Les vendor-prefixes sont traités comme des propriétés distinctes.
    """
    decls = []
    for line in re.split(r';', body):
        line = line.strip()
        if not line or line.startswith('/*'):
            continue
        if ':' in line:
            prop, _, val = line.partition(':')
            decls.append((prop.strip(), val.strip()))
    return decls


def merge_declarations(existing: list, incoming: list) -> list:
    """
    Fusionne incoming dans existing : les propriétés de incoming écrasent
    celles de existing si elles ont le même nom, sinon s'ajoutent à la fin.
    """
    merged = list(existing)  # copies
    existing_props = [p for p, _ in merged]
    for prop, val in incoming:
        if prop in existing_props:
            idx = existing_props.index(prop)
            merged[idx] = (prop, val)
        else:
            merged.append((prop, val))
            existing_props.append(prop)
    return merged


def declarations_to_str(decls: list, indent: str = '    ') -> str:
    return ''.join(f'\n{indent}{p}: {v};' for p, v in decls)


# ──────────────────────────────────────────────────────────────────────────────
# Déduplication principale
# ──────────────────────────────────────────────────────────────────────────────

def deduplicate(css: str) -> tuple:
    """
    Retourne (css_clean, nb_doublons_supprimés)
    """
    tokens = tokenize(css)
    removed = 0

    # Première passe : fusionner les règles ordinaires ayant le même sélecteur
    seen_rules: dict = {}   # selector → index dans output_tokens
    output_tokens: list = []

    for tok in tokens:
        if tok['type'] != 'rule':
            output_tokens.append(tok)
            continue

        sel = tok['selector']

        if sel not in seen_rules:
            seen_rules[sel] = len(output_tokens)
            output_tokens.append(tok)
        else:
            # Fusionner dans le token existant
            first_idx = seen_rules[sel]
            first_tok = output_tokens[first_idx]

            existing_decls = parse_declarations(first_tok['body'])
            incoming_decls = parse_declarations(tok['body'])
            merged = merge_declarations(existing_decls, incoming_decls)

            # Reconstruire le bloc fusionné
            indent = '    '
            new_body = declarations_to_str(merged, indent) + '\n'
            new_raw = f"{sel} {{{new_body}}}"
            output_tokens[first_idx] = {**first_tok, 'body': new_body, 'raw': new_raw}

            # Supprimer le doublon (remplacer par token vide)
            output_tokens.append({'type': 'whitespace', 'raw': ''})
            removed += 1

    # Reconstruire le CSS
    parts = []
    for tok in output_tokens:
        parts.append(tok['raw'])

    clean = ''.join(parts)

    # Dédoublonner aussi les @keyframes par nom
    kf_seen = set()
    def _replace_kf(m):
        name = re.search(r'@keyframes\s+([\w-]+)', m.group(0))
        n = name.group(1) if name else m.group(0)[:30]
        if n in kf_seen:
            nonlocal removed
            removed += 1
            return ''
        kf_seen.add(n)
        return m.group(0)

    clean = re.sub(r'@keyframes\s+[\w-]+\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}', _replace_kf, clean)

    # Nettoyer les lignes vides excessives (> 2 consécutives)
    clean = re.sub(r'\n{3,}', '\n\n', clean)
    clean = clean.strip() + '\n'

    return clean, removed


# ──────────────────────────────────────────────────────────────────────────────
# Main
# ──────────────────────────────────────────────────────────────────────────────

def main():
    src = os.path.abspath(SRC_DIR)
    files = [f for f in os.listdir(src) if f.endswith('.css') and not f.endswith('.min.css')]
    files.sort()

    total_removed = 0
    for fname in files:
        path = os.path.join(src, fname)
        css = open(path, 'r', encoding='utf-8').read()
        clean, removed = deduplicate(css)

        if removed > 0:
            # Sauvegarde
            bak = path + '.bak'
            open(bak, 'w', encoding='utf-8').write(css)
            open(path, 'w', encoding='utf-8').write(clean)
            old_size = len(css.encode())
            new_size = len(clean.encode())
            print(f'  ✅ {fname}: {removed} doublon(s) supprimé(s) — {old_size} → {new_size} octets ({old_size - new_size:+d})')
        else:
            print(f'  ✔  {fname}: aucun doublon')
        total_removed += removed

    print(f'\nTotal : {total_removed} doublon(s) supprimé(s) dans {len(files)} fichier(s).')


if __name__ == '__main__':
    main()
