#!/usr/bin/env python3
"""
Build script: pre-renders /pages/blog/index.html with static article HTML from /data/blog.json.
Run whenever blog.json is updated so the listing page is indexable without JavaScript.

Usage:
  python3 scripts/build-blog-index.py
"""

import json
import re
import html as html_lib
from pathlib import Path

ROOT       = Path(__file__).parent.parent
BLOG_JSON  = ROOT / 'data' / 'blog.json'
INDEX_HTML = ROOT / 'pages' / 'blog' / 'index.html'

# ── SVGs de placeholder (espelha os definidos no inline JS do index.html) ──

FEAT_SVG = {
    'blue':  '<svg width="64" height="64" viewBox="0 0 64 64" fill="none" aria-hidden="true"><rect x="8" y="8" width="48" height="48" rx="8" fill="var(--color-blue-light)"/><path d="M20 32h24M20 24h16M20 40h20" stroke="var(--color-blue)" stroke-width="2.5" stroke-linecap="round"/></svg>',
    'green': '<svg width="64" height="64" viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M32 8C19 22 12 29 12 39a20 20 0 0040 0c0-10-7-17-20-31z" fill="#d1fae5" stroke="var(--color-green)" stroke-width="2.5" stroke-linecap="round"/></svg>',
    'coral': '<svg width="64" height="64" viewBox="0 0 64 64" fill="none" aria-hidden="true"><circle cx="32" cy="22" r="10" fill="#fde8de" stroke="var(--color-coral)" stroke-width="2.5"/><path d="M12 54c0-11.046 8.954-20 20-20s20 8.954 20 20" stroke="var(--color-coral)" stroke-width="2.5" stroke-linecap="round"/></svg>',
}
CARD_SVG = {
    'blue':  '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><rect x="8" y="6" width="24" height="28" rx="3" fill="var(--color-blue-light)" stroke="var(--color-blue)" stroke-width="1.8"/><path d="M14 16h12M14 22h12M14 28h8" stroke="var(--color-blue)" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'green': '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><path d="M20 5C13 14 8 19 8 25a12 12 0 0024 0c0-6-5-11-12-20z" fill="#d1fae5" stroke="var(--color-green)" stroke-width="1.8"/></svg>',
    'coral': '<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><circle cx="20" cy="14" r="6" fill="#fde8de" stroke="var(--color-coral)" stroke-width="1.8"/><path d="M8 34c0-6.627 5.373-12 12-12s12 5.373 12 12" stroke="var(--color-coral)" stroke-width="1.8" stroke-linecap="round"/></svg>',
}

CLOCK_ICON = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M7 4v3l2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'


def avatar_cls(cor: str) -> str:
    return f' blog-author-avatar--{cor}' if cor and cor != 'default' else ''


def render_featured(item: dict) -> str:
    if item.get('imagem'):
        media = f'<img src="{item["imagem"]}" alt="{html_lib.escape(item["titulo"])}" loading="eager" style="width:100%;height:100%;object-fit:cover;" />'
    else:
        cor  = item.get('cor', 'blue')
        media = f'<div class="blog-featured-img-placeholder">{FEAT_SVG.get(cor, FEAT_SVG["blue"])}</div>'

    return (
        f'<article class="blog-featured" data-cat="{item["categoria"]}" aria-labelledby="post-featured-title">\n'
        f'          <div class="blog-featured-img" aria-hidden="true">\n'
        f'            {media}\n'
        f'          </div>\n'
        f'          <div class="blog-featured-body">\n'
        f'            <div class="blog-featured-meta">\n'
        f'              <span class="badge badge-{item["cor"]}">{item["categoria_label"]}</span>\n'
        f'              <span class="blog-meta-read">{CLOCK_ICON}{item["tempo_leitura"]} min de leitura</span>\n'
        f'              <time datetime="{item["data_iso"]}" class="blog-meta-date">{item["data_formatada"]}</time>\n'
        f'            </div>\n'
        f'            <h3 id="post-featured-title" class="blog-featured-title">\n'
        f'              <a href="{item["link"]}">{item["titulo"]}</a>\n'
        f'            </h3>\n'
        f'            <p class="blog-featured-excerpt">{item["excerpt"]}</p>\n'
        f'            <div class="blog-featured-footer">\n'
        f'              <div class="blog-author-mini">\n'
        f'                <span class="blog-author-avatar{avatar_cls(item.get("autor_cor", ""))}" aria-hidden="true">{item["autor_iniciais"]}</span>\n'
        f'                <span>{item["autor_nome"]}</span>\n'
        f'              </div>\n'
        f'              <a href="{item["link"]}" class="btn btn-primary btn-sm">Ler artigo completo</a>\n'
        f'            </div>\n'
        f'          </div>\n'
        f'        </article>'
    )


def render_card(item: dict) -> str:
    if item.get('imagem'):
        media = f'<img src="{item["imagem"]}" alt="{html_lib.escape(item["titulo"])}" loading="lazy" style="width:100%;height:100%;object-fit:cover;" />'
    else:
        cor  = item.get('cor', 'blue')
        media = f'<div class="blog-card-img-placeholder blog-card-img-placeholder--{cor}">{CARD_SVG.get(cor, CARD_SVG["blue"])}</div>'

    post_id = f'post-dyn-{item["id"]}-title'
    return (
        f'<article class="blog-card" data-cat="{item["categoria"]}" aria-labelledby="{post_id}">\n'
        f'          <div class="blog-card-img" aria-hidden="true">\n'
        f'            {media}\n'
        f'          </div>\n'
        f'          <div class="blog-card-body">\n'
        f'            <div class="blog-card-meta">\n'
        f'              <span class="badge badge-{item["cor"]}">{item["categoria_label"]}</span>\n'
        f'              <span class="blog-meta-read">{item["tempo_leitura"]} min</span>\n'
        f'            </div>\n'
        f'            <h3 id="{post_id}" class="blog-card-title">\n'
        f'              <a href="{item["link"]}">{item["titulo"]}</a>\n'
        f'            </h3>\n'
        f'            <p class="blog-card-excerpt">{item["excerpt"]}</p>\n'
        f'            <div class="blog-card-footer">\n'
        f'              <div class="blog-author-mini">\n'
        f'                <span class="blog-author-avatar{avatar_cls(item.get("autor_cor", ""))}" aria-hidden="true">{item["autor_iniciais"]}</span>\n'
        f'                <span>{item["autor_nome"]}</span>\n'
        f'              </div>\n'
        f'              <time datetime="{item["data_iso"]}">{item["data_formatada"]}</time>\n'
        f'            </div>\n'
        f'          </div>\n'
        f'        </article>'
    )


def build() -> None:
    items = json.loads(BLOG_JSON.read_text(encoding='utf-8'))

    featured = next((i for i in items if i.get('destaque')), None)
    cards    = [i for i in items if not i.get('destaque')]
    total    = len(items)

    featured_html = render_featured(featured) if featured else ''
    cards_html    = '\n          '.join(render_card(c) for c in cards)

    html = INDEX_HTML.read_text(encoding='utf-8')

    # ── Inject featured article ──────────────────────────────────────────────
    # Uses sentinel comments so the replacement is idempotent (safe to run multiple times).
    html = re.sub(
        r'<!-- FEATURED_START -->[\s\S]*?<!-- FEATURED_END -->',
        f'<!-- FEATURED_START -->\n        {featured_html}\n      <!-- FEATURED_END -->',
        html,
        count=1,
    )

    # ── Inject article cards ─────────────────────────────────────────────────
    # Uses sentinel comments so the replacement is idempotent (safe to run multiple times).
    html = re.sub(
        r'<!-- CARDS_START -->[\s\S]*?<!-- CARDS_END -->',
        f'<!-- CARDS_START -->\n          {cards_html}\n        <!-- CARDS_END -->',
        html,
        count=1,
    )

    # ── Update "Mostrando X de Y artigos" counter ────────────────────────────
    html = re.sub(
        r'(<p class="blog-load-more-text">)([\s\S]*?)(</p>)',
        lambda m: f'{m.group(1)}Mostrando {total} de {total} artigos{m.group(3)}',
        html,
        count=1,
    )

    INDEX_HTML.write_text(html, encoding='utf-8')

    print(f'✓  {total} artigos pré-renderizados em {INDEX_HTML.relative_to(ROOT)}')
    print(f'   Destaque : {featured["titulo"][:60] if featured else "nenhum"}')
    print(f'   Cards    : {len(cards)}')


if __name__ == '__main__':
    build()
