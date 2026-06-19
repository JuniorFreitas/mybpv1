# Download de anexos de CIH via CLI

Documentacao do comando Artisan que baixa, em lote, os arquivos de evidencia vinculados aos apontamentos CIH a partir de um CSV exportado pelo sistema.

Codigo: `App\Console\Commands\DownloadCihAnexosFromCsvCommand`  
Assinatura: `mybp:download-cih-anexos`

## 1. Objetivo

Dado um CSV com a coluna `CIH ID`, o comando:

1. le os identificadores unicos de CIH;
2. consulta o banco (`cih_evidencia` -> `arquivos`, validando em `cihs`);
3. copia cada objeto do disco Laravel indicado em `arquivos.disco` para uma pasta local.

## 2. Pre-requisitos

- Ambiente com `.env` apontando para o banco e storage corretos.
- CSV no padrao do relatorio CIH:
  - separador `;`;
  - cabecalho com coluna `CIH ID`.
- Permissao de leitura no bucket/prefixo do disco configurado.

## 3. Uso

### 3.1 Sintaxe

```bash
php artisan mybp:download-cih-anexos {csv} [opcoes]
```

`csv`: caminho absoluto ou relativo a raiz do projeto.

### 3.2 Opcoes

- `--output-dir=`: diretorio de destino (padrao: `download_anexos_cih`).
- `--dry-run`: so verifica existencia no storage, sem gravar arquivos.
- `-v`: com `--dry-run`, lista cada objeto.
- `--with-thumbs`: baixa tambem thumbs quando existirem.
- `--empresa-id=`: filtra CIH por empresa.
- `--with-trashed`: inclui CIH com soft delete.
- `--chunk-cih=`: tamanho do lote no `whereIn` (padrao `500`).

### 3.3 Exemplos

```bash
# Simulacao (recomendado)
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv --dry-run

# Simulacao com detalhes
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv --dry-run -v

# Download real para pasta padrao
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv

# Download para outro local
php artisan mybp:download-cih-anexos /caminho/export.csv --output-dir=/tmp/anexos

# Download filtrando por empresa
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv --empresa-id=1

# Download incluindo CIH com soft delete
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv --with-trashed

# Download incluindo thumbs de imagens
php artisan mybp:download-cih-anexos admissao_cih3993_20260409212951.csv --with-thumbs
```

Com Docker:

```bash
# Simulacao (dry-run) no container
docker compose exec mybpdp php artisan mybp:download-cih-anexos /var/www/html/admissao_cih.csv --dry-run

# Download real no container
docker compose exec mybpdp php artisan mybp:download-cih-anexos /var/www/html/admissao_cih.csv
```

## 4. Formato dos arquivos de saida

Todos os arquivos sao gravados na raiz do diretorio de saida (sem subpastas), com nome:

`{cih_id}_{arquivo_id}_{nome_sanitizado}{extensao}`

## 5. Seguranca

- A pasta `download_anexos_cih/` deve ficar no `.gitignore`.
- Nao versionar anexos/CSVs com dados sensiveis.

## 6. Referencia tecnica

- `cih_evidencia`: liga `cih_id` a `arquivo_id`.
- `arquivos`: metadados e caminho (`file`, `disco`, `nome`, `extensao`, `thumb`).
- `cihs`: filtros de `empresa_id` e `deleted_at`.
- `config/filesystems.php`: discos e roots (ex.: `evidencia-cih`).

