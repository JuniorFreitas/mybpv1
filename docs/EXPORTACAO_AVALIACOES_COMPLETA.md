# Exportação Completa de Avaliações

## Visão Geral
Sistema de exportação completa de avaliações para Excel (CSV) incluindo todos os dados relacionados: feedbacks, avaliadores, funcionários avaliados e suas respostas.

## Funcionalidades Implementadas

### 1. Dados Exportados

#### Dados da Avaliação
- ID da Avaliação
- Título da Avaliação
- Tipo de Avaliação
- Status da Avaliação
- Ano da Avaliação
- Data Início
- Data Fim
- Auto Avaliação (Sim/Não)
- Tipo PJ (CLT/PJ)
- Ativo (Sim/Não)
- Data de Criação

#### Dados do Feedback
- ID do Feedback
- Status do Feedback
- Origem do Feedback (Funcionário/Avaliador)
- Avaliador Principal (Sim/Não)
- Comentário do Avaliador
- Nota Final
- Data Início do Feedback
- Data Fim do Feedback

#### Dados do Funcionário Avaliado
- ID do Funcionário
- Nome do Funcionário
- Login do Funcionário
- Matrícula
- CPF
- Cargo
- Área
- Centro de Custo
- Data de Admissão

#### Dados do Avaliador
- ID do Avaliador
- Nome do Avaliador
- Login do Avaliador

#### Estatísticas
- Total de Respostas
- Média Geral das Notas

## Estrutura do Arquivo

### Formato
- **Tipo**: CSV (Comma-Separated Values)
- **Separador**: Ponto e vírgula (;)
- **Encoding**: UTF-8 com BOM
- **Nome do Arquivo**: `avaliacoes_[RANDOM]_[TIMESTAMP].csv`

### Estrutura de Linhas
- **Uma linha por feedback**: Cada feedback de cada avaliação gera uma linha separada
- **Dados repetidos**: Os dados da avaliação se repetem para cada feedback associado
- **Sem feedbacks**: Se uma avaliação não tem feedbacks, uma única linha é exportada com os dados básicos

## Arquivos Criados/Modificados

### 1. Job de Exportação
**Arquivo**: `app/Jobs/JobExportaAvaliacoesCsv.php`

**Principais Recursos**:
- Processamento em background (fila)
- Sistema de lock distribuído (evita duplicações)
- Processamento em chunks de 500 registros
- Upload automático para S3
- Notificação ao usuário ao finalizar
- Tratamento de erros robusto
- Logs detalhados

**Métodos Principais**:
- `handle()`: Execução principal do job
- `buildQuery()`: Constrói query com filtros e relacionamentos
- `createLocalCsvFile()`: Cria arquivo CSV local
- `formatRow()`: Formata cada linha do CSV
- `getDadosFuncionario()`: Busca dados completos do funcionário
- `limparTexto()`: Remove caracteres especiais dos textos

### 2. Controller
**Arquivo**: `app/Http/Controllers/AvaliacaoController.php`

**Método Adicionado**: `export(Request $request)`
- Valida permissões
- Recebe filtros da interface
- Dispara job de exportação
- Retorna feedback ao usuário

### 3. Rota
**Arquivo**: `routes/web.php`

**Rota Adicionada**:
```php
Route::post('avaliacao/export', [AvaliacaoController::class, 'export'])
    ->name('avaliacao.export')
    ->middleware('can:cadastro_avaliacao');
```

### 4. Interface Vue.js
**Arquivo**: `resources/js/components/cadastros/avaliacoes/avaliacao/index.vue`

**Melhorias**:
- Botão "Exportar Excel" adicionado
- Estado de carregamento durante exportação
- Feedback visual (loading spinner)
- Mensagens de sucesso/erro
- Aplica filtros ativos da listagem

## Filtros Aplicáveis

A exportação respeita todos os filtros aplicados na interface:

1. **Busca por Texto**: Título da avaliação ou ID
2. **Ano da Avaliação**: Filtra por ano específico
3. **Tipo de Avaliação**: Filtra por tipo específico
4. **Status**: Aguardando Início, Aberta, Encerrada
5. **Tipo PJ**: CLT ou PJ

## Performance e Otimizações

### Processamento em Background
- Uso de filas do Laravel (Queue)
- Não bloqueia a interface do usuário
- Permite exportações grandes sem timeout

### Sistema de Lock
- Evita processamento duplicado
- Lock distribuído via Cache
- Timeout de 20 minutos
- Liberação automática em caso de falha

### Otimização de Memória
- Processamento em chunks de 500 registros
- Eager loading de relacionamentos
- Limpeza automática de arquivo temporário

### Relacionamentos Carregados
```php
'AvaliacaoTipo',
'AvaliacaoFeedbacks' => [
    'Funcionario:id,nome,login,cpf',
    'Avaliador:id,nome,login',
    'Respostas'
]
```

## Como Usar

### 1. Pela Interface
1. Acesse: **Cadastro > Avaliações**
2. Aplique os filtros desejados
3. Clique em **"Exportar Excel"**
4. Aguarde a notificação de conclusão
5. Acesse a área de downloads para baixar o arquivo

### 2. Programaticamente
```php
use App\Jobs\JobExportaAvaliacoesCsv;

JobExportaAvaliacoesCsv::dispatch(
    auth()->id(),
    "Cadastro - Avaliações",
    "avaliacoes_export.csv",
    [
        'ano_avaliacao' => 2025,
        'status' => 'Aberta',
        'tipo_pj' => 'CLT'
    ]
);
```

## Tratamento de Erros

### Logs Registrados
- Início e fim da exportação
- Filtros aplicados
- Erros detalhados com stack trace
- Tempo de processamento

### Falhas e Retry
- Até 3 tentativas automáticas
- Lock liberado em caso de falha
- Notificação de erro ao usuário

### Dados Faltantes
- Valores padrão "N/A" para campos vazios
- Try-catch ao buscar dados do funcionário
- Validações de relacionamentos nulos

## Segurança

### Permissões
- Requer permissão `cadastro_avaliacao`
- Validação de empresa (tenant)
- Exporta apenas dados da empresa do usuário

### Dados Sensíveis
- CPF exportado apenas para usuários autorizados
- Comentários com caracteres especiais removidos
- Textos sanitizados (quebras de linha, tabs)

## Manutenção e Monitoramento

### Logs para Verificar
```bash
# Ver processamento de exportações
tail -f storage/logs/laravel.log | grep "Avaliações"

# Ver erros
tail -f storage/logs/laravel.log | grep "ERROR"
```

### Tabelas Relacionadas
- `avaliacoes`: Dados principais
- `avaliacao_feedbacks`: Feedbacks individuais
- `avaliacao_respostas`: Respostas às questões
- `users`: Funcionários e avaliadores
- `feedback_curriculos`: Dados complementares
- `admissoes`: Dados de admissão
- `exportacoes`: Histórico de exportações

## Possíveis Melhorias Futuras

1. **Exportação por Tópicos**: Incluir detalhes das respostas por tópico
2. **Gráficos**: Gerar arquivo Excel com gráficos
3. **Filtros Avançados**: Filtrar por avaliador, funcionário específico
4. **Formato XLSX**: Suporte a Excel nativo (não apenas CSV)
5. **Agendamento**: Exportações automáticas agendadas
6. **Compressão**: ZIP para arquivos muito grandes
7. **Dashboard**: Estatísticas antes de exportar

## Suporte

Para dúvidas ou problemas:
1. Verificar logs em `storage/logs/laravel.log`
2. Verificar fila: `php artisan queue:work`
3. Verificar permissões do usuário
4. Verificar conectividade com S3

---

**Data de Implementação**: 15 de outubro de 2025
**Versão**: 1.0.0
**Responsável**: Sistema MyBP
