import sys

# Receber argumentos passados pela linha de comando
arg1 = 30
arg2 = 40

# Exemplo de processamento com os argumentos
resultado = int(arg1) + int(arg2)

# Exibir o resultado
print(resultado)


import sys
import json
import xlwt
import os

# Receber o JSON enviado do PHP como argumento
# dados_json = sys.argv[1]

# Converter o JSON de volta para um array em Python
# meu_array = json.loads(dados_json)

# Criar um novo arquivo Excel
# arquivo_excel = xlwt.Workbook(encoding='utf-8')
# planilha = arquivo_excel.add_sheet("Dados")

# Escrever os dados do array na planilha
# for linha, dados_linha in enumerate(meu_array):
#     for coluna, dado in enumerate(dados_linha):
#         planilha.write(linha, coluna, dado)

# Salvar o arquivo Excel
# arquivo_excel.save("dados.xls")

# Obter o caminho absoluto do arquivo Excel gerado
# caminho_absoluto = os.path.abspath("dados.xls")

# Exibir uma mensagem de sucesso com o caminho absoluto do arquivo
print("Arquivo Excel gerado com sucesso!")
# print("Endereço do arquivo:", caminho_absoluto)







import sys
import json
import xlwt
import os

caminho_arquivo = sys.argv[1]
caminho_salva = sys.argv[2]+"/dados.xls"

with open(caminho_arquivo, 'r') as arquivo:
    # Carregar o conteúdo do arquivo JSON em um objeto Python
    meu_array = json.load(arquivo)

    # Criar um novo arquivo Excel
    arquivo_excel = xlwt.Workbook(encoding='utf-8')
    planilha = arquivo_excel.add_sheet("Dados")

    # Escrever os dados do array na planilha
    for linha, dados_linha in enumerate(meu_array):
        for coluna, dado in enumerate(dados_linha):
            planilha.write(linha, coluna, dado)

#     Salvar o arquivo Excel
    arquivo_excel.save(caminho_salva)

# print(dados_json)
print(caminho_salva)
