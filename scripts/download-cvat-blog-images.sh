#!/usr/bin/env bash
# Baixa as imagens do cvat.blog para o diretório assets/img/blog/
# Execute na raiz do repositório: bash scripts/download-cvat-blog-images.sh
#
# Pré-requisito: curl
# Após download, escaneie os arquivos com ClamAV ou VirusTotal antes de commitar.

set -euo pipefail

DEST="assets/img/blog"
mkdir -p "$DEST"

UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

download() {
  local url="$1"
  local filename
  filename=$(python3 -c "import urllib.parse, sys; print(urllib.parse.unquote(sys.argv[1].split('/')[-1]))" "$url" 2>/dev/null || basename "$url")
  local dest="$DEST/$filename"

  if [[ -f "$dest" ]]; then
    echo "  ↩ Já existe: $filename"
    return 0
  fi

  echo "  ↓ $filename"
  if curl -fsSL -A "$UA" -e "http://cvat.blog/" --retry 3 --retry-delay 2 \
       -o "$dest" "$url" 2>/dev/null; then
    echo "    ✓ $(du -sh "$dest" | cut -f1)"
  else
    echo "    ✗ Falha ao baixar: $url"
    rm -f "$dest"
  fi
}

echo "Iniciando download das imagens do cvat.blog..."
echo ""

# Lista completa das 78 imagens únicas referenciadas nos artigos
download "http://cvat.blog/wp-content/uploads/2020/12/Lideranca.jpg"
download "http://cvat.blog/wp-content/uploads/2020/12/team-of-happy-financiers-reading-report-on-company-progress-scaled-1.jpg"
download "http://cvat.blog/wp-content/uploads/2021/01/Rotatividade-1.jpg"
download "http://cvat.blog/wp-content/uploads/2021/04/Design-sem-nome-38.png"
download "http://cvat.blog/wp-content/uploads/2021/04/O-Coaching-como-um-Negocio.jpg"
download "http://cvat.blog/wp-content/uploads/2021/05/Banner-de-Artigo-1.png"
download "http://cvat.blog/wp-content/uploads/2021/05/Design-sem-nome-37.png"
download "http://cvat.blog/wp-content/uploads/2021/05/pexels-pixabay-327540-scaled-1.jpg"
download "http://cvat.blog/wp-content/uploads/2021/06/Design-sem-nome-33.png"
download "http://cvat.blog/wp-content/uploads/2021/06/Design-sem-nome-34.png"
download "http://cvat.blog/wp-content/uploads/2021/06/Design-sem-nome-35.png"
download "http://cvat.blog/wp-content/uploads/2021/06/Design-sem-nome-36.png"
download "http://cvat.blog/wp-content/uploads/2021/07/Design-sem-nome-28.png"
download "http://cvat.blog/wp-content/uploads/2021/08/Design-sem-nome-23.png"
download "http://cvat.blog/wp-content/uploads/2021/08/Design-sem-nome-25.png"
download "http://cvat.blog/wp-content/uploads/2021/08/Design-sem-nome-26.png"
download "http://cvat.blog/wp-content/uploads/2021/08/Design-sem-nome-27.png"
download "http://cvat.blog/wp-content/uploads/2021/12/Inicial-Site.png"
download "http://cvat.blog/wp-content/uploads/2023/12/Design-sem-nome-18.png"
download "http://cvat.blog/wp-content/uploads/2024/08/lideranca-autentica.jpg"
download "http://cvat.blog/wp-content/uploads/2024/10/Design-sem-nome-4.png"
download "http://cvat.blog/wp-content/uploads/2024/10/Design-sem-nome-9.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-39.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-40.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-41.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-42.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-43.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-44.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-45.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-46.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-47.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-48.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-49.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-50.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-51.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-52.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Design-sem-nome-55.png"
download "http://cvat.blog/wp-content/uploads/2024/12/Ferramenta-para-o-Desenvolvimento-de-Liderancas-4.png"
download "http://cvat.blog/wp-content/uploads/2025/03/5.png"
download "http://cvat.blog/wp-content/uploads/2025/03/7.png"
download "http://cvat.blog/wp-content/uploads/2025/03/8.png"
download "http://cvat.blog/wp-content/uploads/2025/03/DALL%C2%B7E-2025-03-31-15.46.34-An-artistic-metaphorical-illustration-of-the-Four-Water-Tanks-of-Life-representing-the-four-pillars-of-personal-success.-Each-water-tank-is-large-an.webp"
download "http://cvat.blog/wp-content/uploads/2025/03/Design-sem-nome-56.png"
download "http://cvat.blog/wp-content/uploads/2025/04/Design-sem-nome-34-1024x576.png"
download "http://cvat.blog/wp-content/uploads/2025/04/Design-sem-nome-35-1024x576.png"
download "http://cvat.blog/wp-content/uploads/2025/04/Design-sem-nome-36.png"
download "http://cvat.blog/wp-content/uploads/2025/04/Imagem-do-WhatsApp-de-2025-04-24-as-10.12.04_0b5af7c4.jpg"
download "http://cvat.blog/wp-content/uploads/2025/04/O-MAIOR-CONSULTOR-DO-MUNDO-10.png"
download "http://cvat.blog/wp-content/uploads/2025/04/O-MAIOR-CONSULTOR-DO-MUNDO-3.png"
download "http://cvat.blog/wp-content/uploads/2025/04/O-MAIOR-CONSULTOR-DO-MUNDO-4.png"
download "http://cvat.blog/wp-content/uploads/2025/04/O-MAIOR-CONSULTOR-DO-MUNDO-5.png"
download "http://cvat.blog/wp-content/uploads/2025/04/O-MAIOR-CONSULTOR-DO-MUNDO-9.png"
download "http://cvat.blog/wp-content/uploads/2025/05/Design-sem-nome-26.png"
download "http://cvat.blog/wp-content/uploads/2025/06/Design-sem-nome.png"
download "http://cvat.blog/wp-content/uploads/2025/06/pexels-photo-7598011-7598011.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/alto-falante-alegre-falando-e-olhando-para-distancia.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/cena-autentica-clube-livro-1.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/close-up-da-colecao-de-sinalizacao-ambiental.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/conceito-abstrato-de-rede-ainda-arranjo-de-vida.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/conceito-de-educacao-estudante-estudando-e-brainstorming-conceito-de-campus-perto-de-estudantes-discutindo-seu-assunto-em-livros-ou-livros-didaticos-foco-seletivo.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/confident-young-builder-girl-mouth-sealed-with-warning-tape-holds-tape-shows-stop-gesture-isolated-orange-background-with-copy-space-2-1024x748.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/executivo-tocando-um-icone-na-rede-social.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/fire-exit-emergency-attention-sign.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/gerente-corporativo-conversando-com-um-grupo-de-trabalhadores-manuais-durante-uma-reuniao-de-equipe-em-uma-fabrica.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/humor-alegre-grupo-de-pessoas-em-conferencia-de-negocios-em-sala-de-aula-moderna-durante-o-dia.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/tres-pessoas-discutindo-um-plano-em-uma-fabrica.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/vista-frontal-empresario-segurando-o-rei-1024x683.jpg"
download "http://cvat.blog/wp-content/uploads/2025/09/vista-frontal-empresario-segurando-o-rei.jpg"
download "http://cvat.blog/wp-content/uploads/2025/11/ChatGPT-Image-18-de-nov.-de-2025-17_10_00.png"
download "http://cvat.blog/wp-content/uploads/2025/11/HSE-IT-Escala-de-Cores.png"
download "http://cvat.blog/wp-content/uploads/2025/11/heartbeat-healthcare-life-health-cardiogram-concept.jpg"
download "http://cvat.blog/wp-content/uploads/2025/11/young-builder-shirt-vest-helmet-covering-face-with-hands-looking-upset-front-view-1024x683.jpg"
download "http://cvat.blog/wp-content/uploads/2026/02/three-factory-workers-safety-hats-discussing-manufacture-plan-1.jpg"
download "http://cvat.blog/wp-content/uploads/2026/03/anxiety-inducing-imagery-with-unease-angst-feelings.jpg"
download "http://cvat.blog/wp-content/uploads/2026/03/d9f55de2-a3c2-4a20-a536-a4e0380d21a3.jpg"
download "http://cvat.blog/wp-content/uploads/2026/03/happy-corporate-manager-shaking-hands-with-black-worker-after-staff-meeting-factory.jpg"
download "http://cvat.blog/wp-content/uploads/2026/03/solar-panel-manufacturing-plant-researcher-taking-notes-files-1.jpg"
download "http://cvat.blog/wp-content/uploads/2026/03/view-male-engineer-work-engineers-day-celebration.jpg"

echo ""
echo "Download concluído. Arquivos em: $DEST/"
echo "Total baixados: $(ls -1 "$DEST/" | wc -l) arquivo(s)"
echo ""
echo "Próximos passos:"
echo "  1. Escanear com ClamAV: clamscan -r $DEST/"
echo "  2. Ou enviar para VirusTotal (API ou web): https://www.virustotal.com/gui/home/upload"
echo "  3. Após confirmar limpos: git add $DEST/ && git commit -m 'feat: migrate blog images from cvat.blog to cvatbrasil.com'"
echo "  4. git push origin claude/setup-cvat-brasil-site-G3TEJ && git push origin claude/setup-cvat-brasil-site-G3TEJ:main"
echo "  5. Me informe no chat que os arquivos foram pushed — farei a atualização dos 76 artigos HTML"
