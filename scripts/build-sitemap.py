#!/usr/bin/env python3
"""
Build script: gera /sitemap.xml a partir das páginas estáticas e de /data/blog.json.
Run sempre que novas páginas ou artigos forem publicados.

Usage:
  python3 scripts/build-sitemap.py
"""

import json
from datetime import date
from pathlib import Path

ROOT        = Path(__file__).parent.parent
BLOG_JSON   = ROOT / 'data' / 'blog.json'
SITEMAP_OUT = ROOT / 'sitemap.xml'
BASE_URL    = 'https://cvatbrasil.com'
TODAY       = date.today().isoformat()   # data de lastmod para páginas sem data específica

# ── Páginas institucionais (ordem: importância decrescente) ───────────────────

STATIC_PAGES = [
    # path                                  changefreq  priority  lastmod
    ('/',                                   'weekly',   '1.0',    TODAY),
    ('/pages/treinamentos',                 'monthly',  '0.9',    TODAY),
    ('/pages/treinamentos/hse',             'monthly',  '0.9',    TODAY),
    ('/pages/treinamentos/copsoq',          'monthly',  '0.9',    TODAY),
    ('/pages/nr01',                         'monthly',  '0.8',    TODAY),
    ('/pages/diagnostico',                  'monthly',  '0.8',    TODAY),
    ('/pages/solucoes/cvat',                'monthly',  '0.8',    TODAY),
    ('/pages/solucoes/copsoq',              'monthly',  '0.8',    TODAY),
    ('/pages/solucoes/hse-it',              'monthly',  '0.8',    TODAY),
    ('/pages/solucoes/gestor-nr01',         'monthly',  '0.8',    TODAY),
    ('/pages/para-empresas',                'monthly',  '0.7',    TODAY),
    ('/pages/para-rh',                      'monthly',  '0.7',    TODAY),
    ('/pages/para-consultores',             'monthly',  '0.7',    TODAY),
    ('/pages/blog/',                        'weekly',   '0.8',    TODAY),
    ('/pages/sobre',                        'monthly',  '0.6',    TODAY),
    ('/pages/contato',                      'monthly',  '0.6',    TODAY),
    ('/contato',                            'monthly',  '0.6',    TODAY),
]


def url_block(loc: str, lastmod: str, changefreq: str, priority: str) -> str:
    return (
        f'  <url>\n'
        f'    <loc>{loc}</loc>\n'
        f'    <lastmod>{lastmod}</lastmod>\n'
        f'    <changefreq>{changefreq}</changefreq>\n'
        f'    <priority>{priority}</priority>\n'
        f'  </url>'
    )


def build() -> None:
    articles = json.loads(BLOG_JSON.read_text(encoding='utf-8'))
    # Ordena por data decrescente (mais recente primeiro)
    articles.sort(key=lambda a: a.get('data_iso', '2000-01-01'), reverse=True)

    blocks = ['<?xml version="1.0" encoding="UTF-8"?>',
              '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', '']

    # ── Páginas institucionais ────────────────────────────────────────────────
    blocks.append('  <!-- ── Páginas institucionais ────────────────────────────────────── -->')
    for path, freq, pri, mod in STATIC_PAGES:
        blocks.append(url_block(f'{BASE_URL}{path}', mod, freq, pri))

    # ── Artigos do blog ───────────────────────────────────────────────────────
    blocks.append('')
    blocks.append(f'  <!-- ── Artigos do blog ({len(articles)} artigos) ────────────────────────────── -->')
    for a in articles:
        slug = a['link'].lstrip('/')           # pages/blog/slug.html
        loc  = f'{BASE_URL}/{slug}'
        mod  = a.get('data_iso', TODAY)
        blocks.append(url_block(loc, mod, 'monthly', '0.6'))

    blocks.append('')
    blocks.append('</urlset>')
    blocks.append('')

    xml = '\n'.join(blocks)
    SITEMAP_OUT.write_text(xml, encoding='utf-8')

    total = len(STATIC_PAGES) + len(articles)
    print(f'✓  sitemap.xml gerado: {len(STATIC_PAGES)} páginas + {len(articles)} artigos = {total} URLs')
    print(f'   Saída: {SITEMAP_OUT}')


if __name__ == '__main__':
    build()
