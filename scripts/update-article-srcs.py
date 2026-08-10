#!/usr/bin/env python3
"""
Após as imagens serem migradas para /assets/img/blog/,
este script:
  1. Substitui todos os src="http://cvat.blog/wp-content/uploads/..." por
     src="/assets/img/blog/{filename}" nos 76 artigos.
  2. Corrige os 41 links href para cvat.blog (Parte A) nos 25 artigos.
  3. Gera relatório final.

Usage:
  python3 scripts/update-article-srcs.py [--dry-run]
"""

import re, sys, hashlib
from pathlib import Path

ROOT      = Path(__file__).parent.parent
BLOG_DIR  = ROOT / 'pages' / 'blog'
ASSET_DIR = ROOT / 'assets' / 'img' / 'blog'
DRY_RUN   = '--dry-run' in sys.argv

IMG_SRC_PAT = re.compile(
    r'src="http://cvat\.blog/wp-content/uploads/[^"]*?/([^"/]+)"'
)
HREF_CVAT_PAT = re.compile(
    r'href="https?://cvat\.blog/([^"]*)"'
)

def sha256_file(path: Path) -> str:
    h = hashlib.sha256()
    with open(path, 'rb') as f:
        for chunk in iter(lambda: f.read(65536), b''):
            h.update(chunk)
    return h.hexdigest()

def fix_src(m: re.Match) -> str:
    raw_filename = m.group(1)
    dest = ASSET_DIR / raw_filename
    if dest.exists():
        return f'src="/assets/img/blog/{raw_filename}"'
    return m.group(0)   # leave unchanged if file not present

def fix_href(m: re.Match) -> str:
    slug = m.group(1).rstrip('/')
    # Blog article → internal article page
    candidate = BLOG_DIR / f'{slug}.html'
    if candidate.exists():
        return f'href="/pages/blog/{slug}.html"'
    # Known page mappings
    mappings = {
        '':                          '/pages/treinamentos',
        'treinamentos':              '/pages/treinamentos',
        'sobre':                     '/pages/sobre',
        'contato':                   '/pages/contato',
        'solucoes/cvat':             '/pages/solucoes/cvat',
        'solucoes/copsoq':           '/pages/solucoes/copsoq',
        'solucoes/hse-it':           '/pages/solucoes/hse-it',
        'pages/treinamentos/hse':    '/pages/treinamentos/hse',
        'pages/treinamentos/copsoq': '/pages/treinamentos/copsoq',
    }
    if slug in mappings:
        return f'href="{mappings[slug]}"'
    # Fallback: point to blog listing
    return f'href="/pages/blog/"'

# ── Process articles ──────────────────────────────────────────────────────────
articles = sorted(BLOG_DIR.glob('*.html'))
src_changes  = 0
href_changes = 0
articles_modified = []

for article in articles:
    original = article.read_text(encoding='utf-8')
    updated  = original

    # Fix image src
    updated, n1 = IMG_SRC_PAT.subn(fix_src, updated)
    # Fix href links
    updated, n2 = HREF_CVAT_PAT.subn(fix_href, updated)

    if updated != original:
        src_changes  += n1
        href_changes += n2
        articles_modified.append((article.name, n1, n2))
        if not DRY_RUN:
            article.write_text(updated, encoding='utf-8')

# ── Report ────────────────────────────────────────────────────────────────────
print(f'Artigos modificados: {len(articles_modified)}')
print(f'  src substituídos : {src_changes}')
print(f'  href corrigidos  : {href_changes}')
if DRY_RUN:
    print('  [DRY-RUN — nenhum arquivo alterado]')
print()

for name, n1, n2 in articles_modified:
    print(f'  {name}: {n1} src, {n2} href')

# ── SHA-256 manifest of migrated images ──────────────────────────────────────
print()
print('SHA-256 das imagens em /assets/img/blog/:')
images = sorted(ASSET_DIR.glob('*'))
for img in images:
    print(f'  {sha256_file(img)}  {img.name}')
print(f'  Total: {len(images)} arquivo(s)')

# ── Grep verification ─────────────────────────────────────────────────────────
if not DRY_RUN:
    print()
    remaining_src  = 0
    remaining_href = 0
    for article in articles:
        text = article.read_text(encoding='utf-8')
        remaining_src  += len(re.findall(r'src="http://cvat\.blog/', text))
        remaining_href += len(re.findall(r'href="https?://cvat\.blog/', text))

    print(f'Verificação pós-correção:')
    print(f'  src="cvat.blog" restantes : {remaining_src}')
    print(f'  href="cvat.blog" restantes: {remaining_href}')
    if remaining_src == 0 and remaining_href == 0:
        print('  ✓ Zero ocorrências de cvat.blog em src/href nos artigos')
    else:
        print('  ⚠ Ainda existem referências ao cvat.blog — verifique manualmente')

if __name__ == '__main__':
    pass
