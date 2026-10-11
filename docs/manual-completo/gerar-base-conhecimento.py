#!/usr/bin/env python3
"""Gera a base de conhecimento (RAG) a partir de secoes/*.html.

Saida em base-conhecimento/:
  chunks.jsonl   um trecho por linha, com texto em Markdown e metadados (capitulo, secao, caminho no menu, pagina do PDF)
  md/*.md        o mesmo conteudo em Markdown, um arquivo por capitulo (leitura humana e indexadores que preferem arquivos)
  indice.json    resumo: versao, data, quantidade de trechos e lista de capitulos

Regra de corte: cada <h2> vira um bloco; blocos grandes sao divididos nos <h3>. Todo trecho carrega o
caminho completo ("Capitulo > Secao > Subsecao") no proprio texto, para ser entendido fora de contexto.
"""
import datetime
import glob
import hashlib
import html
import json
import os
import re
from html.parser import HTMLParser

BASE = os.path.dirname(os.path.abspath(__file__))
SAIDA = os.path.join(BASE, "base-conhecimento")
VERSAO = "3.0"
LIMITE = 1800  # caracteres a partir dos quais uma secao e dividida nos <h3>


class ParaMarkdown(HTMLParser):
    """Converte o HTML das secoes em Markdown simples, registrando titulos, caminho de menu e imagens."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.blocos = []  # (tipo, conteudo): h1, h2, h3, h4, texto, caminho, imagem
        self.buf = []
        self.pilha = []
        self.listas = []
        self.linha = None
        self.tabela = None
        self.em_figura = False

    def _fecha(self, tipo="texto"):
        texto = re.sub(r"[ \t]+", " ", "".join(self.buf)).strip()
        self.buf = []
        if texto:
            self.blocos.append((tipo, texto))

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag in ("h1", "h2", "h3", "h4", "p", "div", "figcaption"):
            self._fecha()
        if tag == "span" and a.get("class") == "caminho":
            self._fecha()
            self.pilha.append("caminho")
            return
        if tag == "figure":
            self._fecha()
            self.em_figura = True
        if tag == "img" and self.em_figura:
            self.blocos.append(("imagem", a.get("src", "")))
        if tag in ("ul", "ol"):
            self._fecha()
            self.listas.append([tag, int(a.get("start", 1))])
        if tag == "li":
            self._fecha()
            ordenada = self.listas and self.listas[-1][0] == "ol"
            marca = "%d. " % self.listas[-1][1] if ordenada else "- "
            if ordenada:
                self.listas[-1][1] += 1
            self.buf.append("  " * (len(self.listas) - 1) + marca)
        if tag == "table":
            self._fecha()
            self.tabela = []
        if tag == "tr":
            self.linha = []
        if tag in ("td", "th"):
            self.buf = []
        if tag in ("b", "strong"):
            self.buf.append("**")
        if tag in ("i", "em") and self.tabela is None:
            self.buf.append("*")
        if tag == "br":
            self.buf.append(" ")
        self.pilha.append(tag)

    def handle_endtag(self, tag):
        if self.pilha and self.pilha[-1] == "caminho" and tag == "span":
            self.pilha.pop()
            self._fecha("caminho")
            return
        if tag in ("b", "strong"):
            self.buf.append("**")
        if tag in ("i", "em") and self.tabela is None:
            self.buf.append("*")
        if tag in ("h1", "h2", "h3", "h4"):
            self._fecha(tag)
        if tag in ("p", "div", "li"):
            self._fecha()
        if tag == "figcaption":
            self.buf = []  # legenda nao entra no texto; a imagem ja foi registrada
        if tag == "figure":
            self.em_figura = False
        if tag in ("td", "th") and self.linha is not None:
            self.linha.append(re.sub(r"\s+", " ", "".join(self.buf)).strip())
            self.buf = []
        if tag == "tr" and self.tabela is not None and self.linha:
            self.tabela.append(self.linha)
            self.linha = None
        if tag == "table" and self.tabela is not None:
            linhas = self.tabela
            self.tabela = None
            if linhas:
                cab = linhas[0]
                for linha in linhas[1:]:
                    if len(linha) == 2:
                        self.blocos.append(("texto", "- **%s:** %s" % (linha[0].replace("**", ""), linha[1])))
                    else:
                        pares = ["%s: %s" % (cab[i] if i < len(cab) else "", c) for i, c in enumerate(linha)]
                        self.blocos.append(("texto", "- " + " | ".join(pares)))
        if tag in ("ul", "ol") and self.listas:
            self.listas.pop()
        if self.pilha and self.pilha[-1] == tag:
            self.pilha.pop()

    def handle_data(self, data):
        if self.pilha and self.pilha[-1] == "figcaption":
            return
        self.buf.append(re.sub(r"\s+", " ", data))


def limpa(titulo):
    return re.sub(r"\*\*", "", titulo).strip()


def slug(texto):
    texto = re.sub(r"[^a-z0-9]+", "-", texto.lower().translate(str.maketrans("áàâãéêíóôõúç", "aaaaeeiooouc")))
    return texto.strip("-")


paginas = {}
if os.path.exists(os.path.join(BASE, "paginas.json")):
    with open(os.path.join(BASE, "paginas.json"), encoding="utf-8") as arquivo:
        paginas = json.load(arquivo)

os.makedirs(os.path.join(SAIDA, "md"), exist_ok=True)
for antigo in glob.glob(os.path.join(SAIDA, "md", "*.md")):
    os.remove(antigo)

chunks = []
capitulos = []
for caminho in sorted(glob.glob(os.path.join(BASE, "secoes", "*.html"))):
    with open(caminho, encoding="utf-8") as arquivo:
        fonte = arquivo.read()
    cap_id = re.search(r'<h1 id="([^"]+)"', fonte).group(1)
    conversor = ParaMarkdown()
    conversor.feed(fonte)
    conversor._fecha()

    capitulo = ""
    md = []
    secoes = []  # cada secao: {titulo, ancora, caminho, subs: [{titulo, linhas, imagens}]}
    n_h2 = 0
    for tipo, conteudo in conversor.blocos:
        if tipo == "h1":
            capitulo = limpa(conteudo)
            md.append("# " + capitulo)
            secoes.append({"titulo": "", "ancora": cap_id, "caminho": "", "subs": [{"titulo": "", "linhas": [], "imagens": []}]})
        elif tipo == "h2":
            n_h2 += 1
            md.append("\n## " + limpa(conteudo))
            secoes.append({"titulo": limpa(conteudo), "ancora": "%s-%d" % (cap_id, n_h2), "caminho": "", "subs": [{"titulo": "", "linhas": [], "imagens": []}]})
        elif tipo == "h3":
            md.append("\n### " + limpa(conteudo))
            secoes[-1]["subs"].append({"titulo": limpa(conteudo), "linhas": [], "imagens": []})
        elif tipo == "h4":
            md.append("\n#### " + limpa(conteudo))
            secoes[-1]["subs"][-1]["linhas"].append("**%s**" % limpa(conteudo))
        elif tipo == "caminho":
            md.append("Caminho no menu: " + conteudo)
            if not secoes[-1]["caminho"]:
                secoes[-1]["caminho"] = conteudo
            secoes[-1]["subs"][-1]["linhas"].append("Caminho no menu: " + conteudo)
        elif tipo == "imagem":
            secoes[-1]["subs"][-1]["imagens"].append(conteudo)
        else:
            md.append(conteudo)
            secoes[-1]["subs"][-1]["linhas"].append(conteudo)

    nome = os.path.basename(caminho).replace(".html", ".md")
    with open(os.path.join(SAIDA, "md", nome), "w", encoding="utf-8") as arquivo:
        arquivo.write("\n".join(md).strip() + "\n")
    capitulos.append({"id": cap_id, "titulo": capitulo, "arquivo": "md/" + nome})

    for secao in secoes:
        total = sum(len(l) for s in secao["subs"] for l in s["linhas"])
        if total <= LIMITE:
            grupos = [("", [l for s in secao["subs"] for l in (["**%s**" % s["titulo"]] if s["titulo"] else []) + s["linhas"]],
                       [i for s in secao["subs"] for i in s["imagens"]])]
        else:
            grupos = [(s["titulo"], s["linhas"], s["imagens"]) for s in secao["subs"]]
        for subtitulo, linhas, imagens in grupos:
            if not linhas:
                continue
            trilha = " > ".join(p for p in (capitulo, secao["titulo"], subtitulo) if p)
            texto = "%s\n\n%s" % (trilha, "\n".join(linhas))
            chunks.append({
                "id": "%s#%s" % (secao["ancora"], slug(subtitulo) if subtitulo else "geral"),
                "capitulo": capitulo,
                "secao": secao["titulo"],
                "subsecao": subtitulo,
                "caminho_menu": secao["caminho"],
                "ancora": secao["ancora"],
                "pagina_pdf": paginas.get(secao["ancora"]),
                "imagens": imagens,
                "fonte": "secoes/" + os.path.basename(caminho),
                "texto": texto,
                "hash": hashlib.sha1(texto.encode("utf-8")).hexdigest()[:12],
            })

# ids unicos (subtitulos repetidos como "Filtros" dentro da mesma secao)
vistos = {}
for chunk in chunks:
    vistos[chunk["id"]] = vistos.get(chunk["id"], 0) + 1
    if vistos[chunk["id"]] > 1:
        chunk["id"] += "-%d" % vistos[chunk["id"]]

with open(os.path.join(SAIDA, "chunks.jsonl"), "w", encoding="utf-8") as arquivo:
    for chunk in chunks:
        arquivo.write(json.dumps(chunk, ensure_ascii=False) + "\n")

with open(os.path.join(SAIDA, "indice.json"), "w", encoding="utf-8") as arquivo:
    json.dump({
        "produto": "MyBPIN",
        "versao_manual": VERSAO,
        "gerado_em": datetime.date.today().isoformat(),
        "idioma": "pt-BR",
        "trechos": len(chunks),
        "capitulos": capitulos,
    }, arquivo, ensure_ascii=False, indent=2)

tamanhos = sorted(len(c["texto"]) for c in chunks)
print("base-conhecimento: %d trechos (mediana %d caracteres, maior %d)" % (len(chunks), tamanhos[len(tamanhos) // 2], tamanhos[-1]))
