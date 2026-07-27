# Como aplicar os redirects 301

## Pré-requisito
Os dois domínios antigos precisam continuar hospedados (DNS apontando para o servidor)
até você confirmar que os redirects estão funcionando. Só então desligue.

---

## institutodho.com.br

1. Acesse o painel de hospedagem (cPanel, Plesk ou SSH)
2. Navegue até a raiz do domínio `institutodho.com.br` (normalmente `public_html/`)
3. Faça upload do arquivo `institutodho.com.br.htaccess` com o nome `.htaccess`
   - Se já existir um `.htaccess`, adicione as linhas abaixo do que já está, não substitua
4. Aguarde ~1 minuto e teste:

```
curl -I https://institutodho.com.br/hse
# Deve retornar: HTTP/1.1 301 Moved Permanently
# Location: https://cvatbrasil.com/pages/treinamentos/hse.html

curl -I https://institutodho.com.br/copsoq
# Location: https://cvatbrasil.com/pages/treinamentos/copsoq.html

curl -I https://institutodho.com.br/qualquer-coisa
# Location: https://cvatbrasil.com/
```

---

## hse-it.com.br

1. Mesmo processo — acesse a raiz de `hse-it.com.br`
2. Faça upload de `hse-it.com.br.htaccess` como `.htaccess`
3. Teste:

```
curl -I https://hse-it.com.br/hse
# Location: https://cvatbrasil.com/pages/treinamentos/hse.html

curl -I https://hse-it.com.br/copsoq-iii
# Location: https://cvatbrasil.com/pages/treinamentos/copsoq.html

curl -I https://hse-it.com.br/qualquer-coisa
# Location: https://cvatbrasil.com/
```

---

## Após confirmar que todos os redirects respondem 301

1. Aguarde o Google reindexar (Search Console > Cobertura — pode levar 1-4 semanas)
2. Só então cancele a hospedagem dos domínios antigos
3. Mantenha o registro DNS (mesmo sem hospedagem ativa) por pelo menos 6 meses
   para evitar que alguém registre o domínio e capture o tráfego residual

---

## Pendência obrigatória antes de publicar

- [ ] Confirmar link Voomp para oferta de R$490 e atualizar em:
      `pages/treinamentos/copsoq.html` (buscar por "ATUALIZAR")
