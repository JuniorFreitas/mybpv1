#!/usr/bin/env python3
"""Monta manual.html a partir de modelo/ (capa e estilo) e secoes/*.html.

Para alterar o manual, edite os arquivos em secoes/ (um por capitulo) e rode ./gerar-pdf.sh.
O sumario e gerado automaticamente a partir dos titulos <h1> e <h2>.
"""
import glob
import html
import json
import os
import re

BASE = os.path.dirname(os.path.abspath(__file__))


def ler(caminho):
    with open(os.path.join(BASE, caminho), encoding="utf-8") as arquivo:
        return arquivo.read()


def texto(trecho):
    return html.unescape(re.sub(r"<[^>]+>", "", trecho)).strip()


capitulos = []
corpo = []
for caminho in sorted(glob.glob(os.path.join(BASE, "secoes", "*.html"))):
    conteudo = ler(os.path.relpath(caminho, BASE))
    numero = 0

    def ancora(match):
        global numero
        numero += 1
        ident = "%s-%d" % (capitulos[-1]["id"], numero)
        capitulos[-1]["itens"].append((ident, texto(match.group(2))))
        return '<h2 id="%s"%s>%s</h2>' % (ident, match.group(1), match.group(2))

    h1 = re.search(r'<h1 id="([^"]+)">(.*?)</h1>', conteudo, re.S)
    capitulos.append({"id": h1.group(1), "titulo": texto(h1.group(2)), "itens": []})
    conteudo = re.sub(r"<h2(?![^>]*\bid=)([^>]*)>(.*?)</h2>", ancora, conteudo, flags=re.S)
    corpo.append(conteudo)

# paginas.json (gerado por gerar-pdf.sh) traz a pagina de cada titulo no PDF.
paginas = {}
if os.path.exists(os.path.join(BASE, "paginas.json")):
    paginas = json.loads(ler("paginas.json"))


def entrada(ident, titulo):
    pagina = paginas.get(ident)
    numero = '<span class="lin"></span><span class="pg">%s</span>' % pagina if pagina else ""
    return '<a href="#%s"><span>%s</span>%s</a>' % (ident, html.escape(titulo), numero)


with open(os.path.join(BASE, "titulos.json"), "w", encoding="utf-8") as arquivo:
    json.dump([[c["id"], c["titulo"]] + [list(i) for i in c["itens"]] for c in capitulos], arquivo, ensure_ascii=False)

sumario = ['<section class="sumario">', "<h1>Sumário</h1>", '<ol class="caps">']
for cap in capitulos:
    sumario.append("<li>" + entrada(cap["id"], cap["titulo"]))
    if cap["itens"]:
        sumario.append("<ul>")
        for ident, titulo in cap["itens"]:
            sumario.append("<li>%s</li>" % entrada(ident, titulo))
        sumario.append("</ul>")
    sumario.append("</li>")
sumario += ["</ol>", "</section>"]

pagina = """<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>MyBPIN — Manual Completo do Sistema</title>
<style>
%s
</style>
</head>
<body>
<div class="cabecalho"><img src="img/mybpin-logo-fundo-claro.png" alt="MyBPin"><span>Manual Completo do Sistema</span></div>
<table class="pagina">
<thead><tr><td><div class="cab-espaco"></div></td></tr></thead>
<tbody><tr><td>
%s
%s
%s
%s
</td></tr></tbody>
</table>
</body>
</html>
""" % (ler("modelo/estilo.css"), ler("modelo/capa.html"), "\n".join(sumario), "\n\n".join(corpo), ler("modelo/contracapa.html"))

with open(os.path.join(BASE, "manual.html"), "w", encoding="utf-8") as arquivo:
    arquivo.write(pagina)
print("manual.html montado: %d capitulos" % len(capitulos))
