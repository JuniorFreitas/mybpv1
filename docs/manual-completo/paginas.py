#!/usr/bin/env python3
"""Le o texto de cada pagina do PDF (paginas-texto.json) e grava paginas.json com a pagina de cada titulo do sumario."""
import json
import os
import re

BASE = os.path.dirname(os.path.abspath(__file__))


def limpo(texto):
    return re.sub(r"\s+", "", texto)


with open(os.path.join(BASE, "paginas-texto.json"), encoding="utf-8") as arquivo:
    textos = [limpo(t) for t in json.load(arquivo)]
with open(os.path.join(BASE, "titulos.json"), encoding="utf-8") as arquivo:
    titulos = json.load(arquivo)

# O sumario tambem contem todos os titulos: a busca comeca depois da pagina que lista o ultimo capitulo.
ultimo = limpo(titulos[-1][1])
inicio = next(i for i, t in enumerate(textos) if ultimo in t) + 1

paginas = {}
atual = inicio
for capitulo in titulos:
    entradas = [(capitulo[0], capitulo[1])] + [tuple(item) for item in capitulo[2:]]
    for ident, titulo in entradas:
        alvo = limpo(titulo)
        for i in range(atual, len(textos)):
            if alvo in textos[i]:
                paginas[ident] = i + 1
                atual = i
                break

with open(os.path.join(BASE, "paginas.json"), "w", encoding="utf-8") as arquivo:
    json.dump(paginas, arquivo)
print("paginas.json: %d titulos localizados" % len(paginas))
