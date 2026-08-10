#!/usr/bin/env python3
"""
Baixa imagens do cvat.blog, verifica via VirusTotal (SHA-256 lookup + upload),
e migra para /assets/img/blog/ se limpas.

Requer:
  export VT_API_KEY="<sua_chave>"   # NUNCA hardcode aqui

Usage:
  python3 scripts/migrate-images.py [--dry-run] [--scan-only]
"""

import hashlib
import json
import os
import re
import sys
import time
import urllib.request
import urllib.error
import urllib.parse
from pathlib import Path

ROOT        = Path(__file__).parent.parent
BLOG_DIR    = ROOT / 'pages' / 'blog'
ASSET_DIR   = ROOT / 'assets' / 'img' / 'blog'
REPORT_FILE = ROOT / 'scripts' / 'migrate-images-report.json'

DRY_RUN    = '--dry-run'  in sys.argv
SCAN_ONLY  = '--scan-only' in sys.argv

VT_BASE    = 'https://www.virustotal.com/api/v3'
RATE_LIMIT = 15   # segundos entre chamadas VT (tier free: 4/min)

# ── helpers ──────────────────────────────────────────────────────────────────

def vt_api_key() -> str:
    key = os.environ.get('VT_API_KEY', '').strip()
    if not key:
        sys.exit('ERRO: VT_API_KEY não definida. Execute: export VT_API_KEY="..."')
    return key


def sha256(path: Path) -> str:
    h = hashlib.sha256()
    with open(path, 'rb') as f:
        for chunk in iter(lambda: f.read(65536), b''):
            h.update(chunk)
    return h.hexdigest()


def vt_request(method: str, path: str, *, data=None, extra_headers=None) -> dict:
    url = VT_BASE + path
    key = vt_api_key()
    headers = {'x-apikey': key, 'Accept': 'application/json'}
    if extra_headers:
        headers.update(extra_headers)

    req = urllib.request.Request(url, method=method, headers=headers)
    if data:
        req.data = data

    with urllib.request.urlopen(req, cafile='/root/.ccr/ca-bundle.crt', timeout=60) as r:
        return json.loads(r.read())


def vt_hash_lookup(file_hash: str) -> dict | None:
    """Returns VT report dict, or None if not found (404)."""
    try:
        return vt_request('GET', f'/files/{file_hash}')
    except urllib.error.HTTPError as e:
        if e.code == 404:
            return None
        raise


def vt_upload(path: Path) -> dict:
    """Upload file to VT and return analysis id."""
    boundary = 'VTBoundary'
    filename  = path.name.encode()
    body_parts = (
        f'--{boundary}\r\nContent-Disposition: form-data; name="file"; filename="{path.name}"\r\nContent-Type: application/octet-stream\r\n\r\n'
        .encode()
        + path.read_bytes()
        + f'\r\n--{boundary}--\r\n'.encode()
    )
    headers = {'Content-Type': f'multipart/form-data; boundary={boundary}'}
    return vt_request('POST', '/files', data=body_parts, extra_headers=headers)


def vt_poll_analysis(analysis_id: str, max_wait: int = 120) -> dict:
    """Poll VT analysis until complete (or timeout)."""
    for _ in range(max_wait // 15):
        time.sleep(15)
        result = vt_request('GET', f'/analyses/{analysis_id}')
        if result.get('data', {}).get('attributes', {}).get('status') == 'completed':
            return result
    raise TimeoutError(f'VT analysis {analysis_id} não completou em {max_wait}s')


def is_clean(report: dict) -> tuple[bool, int, int]:
    """Returns (clean, malicious_count, suspicious_count)."""
    stats = (report.get('data', {})
                   .get('attributes', {})
                   .get('last_analysis_stats', {}))
    mal = stats.get('malicious', 0)
    sus = stats.get('suspicious', 0)
    return (mal == 0 and sus == 0), mal, sus


def download(url: str, dest: Path) -> None:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req, cafile='/root/.ccr/ca-bundle.crt', timeout=30) as r, \
         open(dest, 'wb') as f:
        f.write(r.read())


# ── collect all unique image URLs from articles ───────────────────────────────

def collect_urls() -> dict[str, list[Path]]:
    """Returns {url: [article_path, ...]}"""
    pattern = re.compile(
        r'http://cvat\.blog/wp-content/uploads/[^\s"\'<>]+'
    )
    url_map: dict[str, list[Path]] = {}
    for article in sorted(BLOG_DIR.glob('*.html')):
        text = article.read_text(encoding='utf-8')
        for url in pattern.findall(text):
            url = url.rstrip(')')   # strip accidental trailing paren
            url_map.setdefault(url, []).append(article)
    return url_map


# ── main ──────────────────────────────────────────────────────────────────────

def main():
    ASSET_DIR.mkdir(parents=True, exist_ok=True)
    url_map = collect_urls()

    print(f'Imagens únicas encontradas: {len(url_map)}')
    print(f'Modo: {"DRY-RUN" if DRY_RUN else ("SCAN-ONLY" if SCAN_ONLY else "MIGRAR")}')
    print()

    report = {
        'clean': [],
        'flagged': [],
        'errors': [],
        'skipped_existing': [],
    }

    for idx, (url, articles) in enumerate(sorted(url_map.items()), 1):
        filename = url.split('/')[-1]
        dest     = ASSET_DIR / filename
        tmp_path = Path(f'/tmp/{filename}')

        print(f'[{idx:>3}/{len(url_map)}] {filename}')

        # ── Step 1: download ──────────────────────────────────────────────
        try:
            download(url, tmp_path)
        except Exception as e:
            print(f'  ✗ Falha ao baixar: {e}')
            report['errors'].append({'url': url, 'error': str(e), 'step': 'download'})
            time.sleep(2)
            continue

        file_hash = sha256(tmp_path)
        print(f'  SHA-256: {file_hash}')

        if DRY_RUN:
            print(f'  [DRY-RUN] Pulando VT scan e migração.')
            tmp_path.unlink(missing_ok=True)
            continue

        # ── Step 2: VT hash lookup ────────────────────────────────────────
        time.sleep(RATE_LIMIT)
        try:
            vt_report = vt_hash_lookup(file_hash)
        except Exception as e:
            print(f'  ✗ Erro VT lookup: {e}')
            report['errors'].append({'url': url, 'hash': file_hash, 'error': str(e), 'step': 'vt_lookup'})
            tmp_path.unlink(missing_ok=True)
            continue

        if vt_report is None:
            # ── Step 3: not in VT → upload ────────────────────────────────
            print(f'  Hash desconhecido no VT — fazendo upload...')
            time.sleep(RATE_LIMIT)
            try:
                upload_result = vt_upload(tmp_path)
                analysis_id   = upload_result['data']['id']
                print(f'  Upload OK, analysis id: {analysis_id} — aguardando resultado...')
                time.sleep(RATE_LIMIT)
                vt_report = vt_poll_analysis(analysis_id)
                # poll returns analysis; convert to file-report-like structure
                vt_report = {'data': {'attributes': vt_report['data']['attributes']}}
            except Exception as e:
                print(f'  ✗ Erro VT upload/poll: {e}')
                report['errors'].append({'url': url, 'hash': file_hash, 'error': str(e), 'step': 'vt_upload'})
                tmp_path.unlink(missing_ok=True)
                continue

        # ── Step 4: evaluate result ───────────────────────────────────────
        clean, mal, sus = is_clean(vt_report)
        engines = (vt_report.get('data', {})
                             .get('attributes', {})
                             .get('last_analysis_results', {}))
        flagging = {k: v for k, v in engines.items()
                    if v.get('category') in ('malicious', 'suspicious')}

        if not clean:
            print(f'  ⚠ SINALIZADO: malicious={mal}, suspicious={sus}')
            for eng, detail in flagging.items():
                print(f'      {eng}: {detail.get("result")}')
            report['flagged'].append({
                'url': url,
                'filename': filename,
                'hash': file_hash,
                'malicious': mal,
                'suspicious': sus,
                'engines': flagging,
                'articles': [str(a) for a in articles],
            })
            tmp_path.unlink(missing_ok=True)
            print()
            print('PARADO: arquivo sinalizado. Verifique o relatório antes de continuar.')
            print('  Pressione Enter para continuar com o próximo arquivo ou Ctrl-C para abortar.')
            input()
            continue

        print(f'  ✓ Limpo (malicious={mal}, suspicious={sus})')
        report['clean'].append({'url': url, 'filename': filename, 'hash': file_hash})

        if SCAN_ONLY:
            tmp_path.unlink(missing_ok=True)
            continue

        # ── Step 5: migrate ───────────────────────────────────────────────
        if not dest.exists():
            tmp_path.rename(dest)
            print(f'  ✓ Salvo em: assets/img/blog/{filename}')
        else:
            tmp_path.unlink(missing_ok=True)
            print(f'  ↩ Já existe, pulando cópia.')
            report['skipped_existing'].append(filename)

        print()

    # ── Save report ───────────────────────────────────────────────────────────
    REPORT_FILE.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding='utf-8')
    print()
    print('── SUMÁRIO ──────────────────────────────────────────────')
    print(f'  Limpos  : {len(report["clean"])}')
    print(f'  Sinalizados: {len(report["flagged"])}')
    print(f'  Erros   : {len(report["errors"])}')
    print(f'  Existentes: {len(report["skipped_existing"])}')
    print(f'  Relatório salvo: scripts/migrate-images-report.json')

    if report['flagged']:
        print()
        print('ATENÇÃO: Arquivos sinalizados — NÃO migrados:')
        for f in report['flagged']:
            print(f'  {f["filename"]} — mal={f["malicious"]}, sus={f["suspicious"]}')

    return len(report['flagged'])


if __name__ == '__main__':
    sys.exit(main())
