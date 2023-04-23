import sys
import json
import xlwt
import os
import redis
import boto3
from botocore.exceptions import NoCredentialsError

path_arquivo = os.path.dirname(os.path.abspath(__file__))
nomedoarquivo = sys.argv[1]
caminho_absoluto_arquivo = os.path.join(path_arquivo, nomedoarquivo)
disco = 'arquivos/disco-exportacao'
arquivo_json = caminho_absoluto_arquivo + '.json'
arquivo_xls = caminho_absoluto_arquivo + '.xls'
arquivo_s3 = os.path.join(disco, nomedoarquivo + '.xls')
chave_redis = f'mybp_database_{nomedoarquivo}'

def buscar_credenciais_aws():
    arquivo_env = os.path.join(path_arquivo.split('scripts')[0], '.env')
    chaves_busca = ['FILESYSTEM_DRIVER', 'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_BUCKET', 'REDIS_HOST', 'REDIS_PASSWORD', 'REDIS_PORT']
    credencialAws = {}

    with open(arquivo_env, 'r') as file:
        linhas = file.readlines()
        for linha in linhas:
            for chave in chaves_busca:
                if chave in linha:
                    valor_encontrado = linha.split('=')[1].strip()
                    credencialAws[chave] = valor_encontrado

    return credencialAws

def buscar_credenciais_redis():
    arquivo_env = os.path.join(path_arquivo.split('scripts')[0], '.env')
    chaves_busca = ['REDIS_HOST', 'REDIS_PASSWORD', 'REDIS_PORT']
    credencialRedis = {}

    with open(arquivo_env, 'r') as file:
        linhas = file.readlines()
        for linha in linhas:
            for chave in chaves_busca:
                if chave in linha:
                    valor_encontrado = linha.split('=')[1].strip()
                    credencialRedis[chave] = valor_encontrado

    return credencialRedis

def gerar_xls():
    try:
        credencialRedis = buscar_credenciais_redis()
        conexao_redis = redis.Redis(host=credencialRedis['REDIS_HOST'], port=credencialRedis['REDIS_PORT'], password=credencialRedis['REDIS_PASSWORD'])

        meu_array = json.loads(conexao_redis.get(chave_redis).decode('utf-8'))
        arquivo_excel = xlwt.Workbook(encoding='utf-8')
        planilha = arquivo_excel.add_sheet("Dados")

        for linha, dados_linha in enumerate(meu_array):
            for coluna, dado in enumerate(dados_linha):
                planilha.write(linha, coluna, dado)

        conexao_redis.delete(chave_redis)
        arquivo_excel.save(arquivo_xls)

        credencialAws = buscar_credenciais_aws()
        s3 = boto3.client(credencialAws['FILESYSTEM_DRIVER'], aws_access_key_id=credencialAws['AWS_ACCESS_KEY_ID'], aws_secret_access_key=credencialAws['AWS_SECRET_ACCESS_KEY'])

        try:
            s3.upload_file(arquivo_xls, credencialAws['AWS_BUCKET'], arquivo_s3)
            print("Arquivo enviado com sucesso para o S3.")
            if os.path.isfile(arquivo_xls):
                os.remove(arquivo_xls)
        except NoCredentialsError:
            print("Credenciais do S3 não encontradas.")
    except Exception as e:
        print("Erro:", e)

gerar_xls()
