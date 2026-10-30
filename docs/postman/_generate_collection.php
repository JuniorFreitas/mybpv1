<?php

declare(strict_types=1);

$base = '{{base_url}}/api/v3';

function req(string $method, string $url): array
{
    $accept = (str_contains($url, 'download') || str_contains($url, '/arquivo') || str_contains($url, 'thumb'))
        ? '*/*'
        : 'application/json';

    return [
        'method' => $method,
        'header' => [['key' => 'Accept', 'value' => $accept]],
        'url' => $url,
    ];
}

function item(string $name, array $request, ?string $description = null, ?array $events = null, ?array $examples = null): array
{
    if ($description !== null) {
        $request['description'] = $description;
    }
    $out = ['name' => $name, 'request' => $request];
    if ($events !== null) {
        $out['event'] = $events;
    }
    if ($examples !== null) {
        $out['response'] = $examples;
    }

    return $out;
}

function captureScript(string $var, string $path = 'data.0.public_id'): array
{
    return [[
        'listen' => 'test',
        'script' => [
            'type' => 'text/javascript',
            'exec' => [
                'pm.test("Status esperado", function () {',
                '  pm.expect(pm.response.code).to.be.oneOf([200, 401, 403, 404]);',
                '});',
                'if (pm.response.code === 200) {',
                '  try {',
                '    const j = pm.response.json();',
                '    const parts = "' . $path . '".split(".");',
                '    let cur = j;',
                '    for (const p of parts) { if (cur == null) break; cur = cur[p]; }',
                '    if (cur) { pm.collectionVariables.set("' . $var . '", cur); console.log("saved ' . $var . '=", cur); }',
                '  } catch (e) { console.warn(e); }',
                '}',
            ],
        ],
    ]];
}

function example(string $name, int $code, array $body): array
{
    return [
        'name' => $name,
        'status' => $code === 200 ? 'OK' : 'Error',
        'code' => $code,
        '_postman_previewlanguage' => 'json',
        'header' => [['key' => 'Content-Type', 'value' => 'application/json']],
        'body' => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
    ];
}

function folder(string $name, string $desc, array $items): array
{
    return ['name' => $name, 'description' => $desc, 'item' => $items];
}

$collectionDesc = <<<'MD'
# MyBP — API Externa v3

API **somente leitura** por empresa (client credentials + Bearer Sanctum).

## Setup rápido

1. Importe esta collection **e** o environment `Api-Externa-v3.postman_environment.json`
2. Preencha `client_id` e `client_secret`
3. Rode **0. Getting Started → 1. Obter token** (grava `access_token`)
4. Rode **2. Listar processos ativos** (captura `processo_public_id`)
5. Explore detalhe / nested / relatórios

## Swagger

`{{base_url}}/api/v3/docs`

## Convenções

| Tema | Regra |
|------|--------|
| IDs | UUID em `public_id` / `*_public_id` |
| Paginação | `page` + `per_page` (máx. 100) |
| Erros | `{ "success": false, "msg": "..." }` |
| Relatórios | extras em `context` |
| LGPD | CPF mascarado |

## Ordem sugerida

Auth → Processos ativos → Detalhe → Relatórios/efetivo → módulos do seu escopo
MD;

$processoRow = [
    'public_id' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
    'status' => 'Admitido',
    'curriculo' => [
        'public_id' => '7c9e6679-7425-40de-944b-e07fc1f90ae7',
        'nome' => 'Maria Silva',
        'cpf' => '***.***.***-99',
    ],
    'admissao' => [
        'status' => 'ADMITIDO',
        'cargo' => 'Operador',
        'data_admissao' => '2026-01-10',
    ],
    'empresa' => [
        'public_id' => '550e8400-e29b-41d4-a716-446655440000',
        'razao_social' => 'ACME Serviços Ltda',
        'cnpj' => '12.345.678/0001-99',
    ],
    'links' => [
        'self' => '/api/v3/processos/3fa85f64-5717-4562-b3fc-2c963f66afa6',
        'etapas' => '/api/v3/processos/3fa85f64-5717-4562-b3fc-2c963f66afa6/etapas',
    ],
];

$processoExemplo = [
    'success' => true,
    'data' => [$processoRow],
    'meta' => ['current_page' => 1, 'per_page' => 20, 'total' => 1, 'last_page' => 1],
];

$folders = [];

$folders[] = folder('0. Getting Started', "Rode nesta ordem:\n1) Obter token\n2) Listar processos ativos\n3) Catálogo de relatórios", [
    item(
        '1. Obter token (client_credentials)',
        [
            'auth' => ['type' => 'noauth'],
            'method' => 'POST',
            'header' => [
                ['key' => 'Accept', 'value' => 'application/json'],
                ['key' => 'Content-Type', 'value' => 'application/json'],
            ],
            'body' => [
                'mode' => 'raw',
                'raw' => json_encode([
                    'grant_type' => 'client_credentials',
                    'client_id' => '{{client_id}}',
                    'client_secret' => '{{client_secret}}',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ],
            'url' => $base . '/oauth/token',
            'description' => "## Autenticação\n\nTroca credenciais por Bearer.\n\n**Pré-requisito:** `client_id` e `client_secret` preenchidos.\n\nO script grava `access_token` na collection.",
        ],
        null,
        [[
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    'pm.test("Token emitido", function () {',
                    '  pm.response.to.have.status(200);',
                    '  const j = pm.response.json();',
                    '  pm.expect(j.success).to.eql(true);',
                    '  pm.expect(j.access_token).to.be.a("string");',
                    '  pm.collectionVariables.set("access_token", j.access_token);',
                    '  if (j.scope) { pm.collectionVariables.set("token_scope", j.scope); }',
                    '  console.log("expires_in=", j.expires_in, "scope=", j.scope);',
                    '});',
                ],
            ],
        ]],
        [example('Token OK', 200, [
            'success' => true,
            'access_token' => '1|exemploTokenNaoUsarEmProducao',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'cloud:read processos:read curriculos:read relatorios:read',
        ])]
    ),
    item(
        '2. Listar processos ativos (captura IDs)',
        req('GET', $base . '/processos/ativos?page={{page}}&per_page={{per_page}}'),
        "## Processos ativos\n\nLista admitidos sem demissão.\n\nFiltros: `q`, `cpf`, `uf`, `empresa_public_id`, `tipo_admissao`, `data_admissao_de/ate`.\n\nScript salva `processo_public_id`, `curriculo_public_id`, `empresa_public_id`.",
        [[
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    'if (pm.response.code !== 200) { return; }',
                    'const j = pm.response.json();',
                    'const row = (j.data && j.data[0]) || null;',
                    'if (!row) { console.warn("lista vazia"); return; }',
                    'pm.collectionVariables.set("processo_public_id", row.public_id);',
                    'pm.collectionVariables.set("public_id", row.public_id);',
                    'if (row.curriculo && row.curriculo.public_id) {',
                    '  pm.collectionVariables.set("curriculo_public_id", row.curriculo.public_id);',
                    '}',
                    'if (row.empresa && row.empresa.public_id) {',
                    '  pm.collectionVariables.set("empresa_public_id", row.empresa.public_id);',
                    '}',
                    'console.log("processo=", row.public_id);',
                ],
            ],
        ]],
        [example('Processos ativos', 200, $processoExemplo)]
    ),
    item(
        '3. Catálogo de relatórios',
        req('GET', $base . '/relatorios'),
        "Lista slugs. Scope: `relatorios:read`.\n\nDepois: `/relatorios/{slug}` (ex. efetivo, aniversariantes?mes=10).",
        null,
        [example('Catálogo', 200, [
            'success' => true,
            'data' => [
                ['slug' => 'efetivo', 'nome' => 'Efetivo', 'filtros' => ['q', 'cargo'], 'links' => ['self' => '/api/v3/relatorios/efetivo']],
                ['slug' => 'aniversariantes', 'nome' => 'Aniversariantes', 'filtros' => ['mes']],
            ],
        ])]
    ),
]);

$folders[] = folder('Cloud', "Scope `cloud:read`.\n\n1) Listar → captura `cloud_public_id`\n2) Itens / pastas / download", [
    item('Listar clouds', req('GET', $base . '/cloud?page={{page}}&per_page={{per_page}}&ativo=true'),
        "Filtros: `q`, `ativo`.\n\nCampos: `public_id`, `nome`, `slug`, `usuarios_count`, `itens_count`.",
        captureScript('cloud_public_id'),
        [example('Clouds', 200, [
            'success' => true,
            'data' => [[
                'public_id' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
                'nome' => 'Documentos RH',
                'slug' => 'documentos-rh',
                'ativo' => true,
                'usuarios_count' => 12,
                'itens_count' => 340,
            ]],
            'meta' => ['current_page' => 1, 'per_page' => 20, 'total' => 1, 'last_page' => 1],
        ])]),
    item('Detalhe cloud', req('GET', $base . '/cloud/{{cloud_public_id}}'), 'Membros admin/grupo e grupos ativos.'),
    item('Buscar itens', req('GET', $base . '/cloud/{{cloud_public_id}}/buscar?q=contrato')),
    item('Pastas', req('GET', $base . '/cloud/{{cloud_public_id}}/pastas')),
    item('Resolver path', req('GET', $base . '/cloud/{{cloud_public_id}}/resolver-path?path=/RH/Contratos')),
    item('Listar itens', req('GET', $base . '/cloud/{{cloud_public_id}}/itens?page={{page}}&per_page={{per_page}}'), null, captureScript('item_public_id')),
    item('Detalhe item', req('GET', $base . '/cloud/{{cloud_public_id}}/itens/{{item_public_id}}')),
    item('Download item', req('GET', $base . '/cloud/{{cloud_public_id}}/itens/{{item_public_id}}/download')),
    item('Arquivo (stream)', req('GET', $base . '/cloud/{{cloud_public_id}}/itens/{{item_public_id}}/arquivo')),
    item('Thumb', req('GET', $base . '/cloud/{{cloud_public_id}}/itens/{{item_public_id}}/thumb')),
]);

$folders[] = folder('Fornecedores', "Scope `fornecedores:read`. Detalhe captura `servico_public_id` e `arquivo_public_id`.", [
    item('Listar fornecedores', req('GET', $base . '/fornecedores?page={{page}}&per_page={{per_page}}'), null, captureScript('fornecedor_public_id')),
    item('Buscar fornecedores', req('GET', $base . '/fornecedores/buscar?q=forn&page={{page}}&per_page={{per_page}}'), 'Mínimo 2 caracteres em `q`.'),
    item('Tipos de serviço', req('GET', $base . '/fornecedores/tipos-servico')),
    item('Detalhe fornecedor', req('GET', $base . '/fornecedores/{{fornecedor_public_id}}'), null, [[
        'listen' => 'test',
        'script' => [
            'type' => 'text/javascript',
            'exec' => [
                'if (pm.response.code!==200) return;',
                'const d = pm.response.json().data || {};',
                'if (d.servicos && d.servicos[0]) pm.collectionVariables.set("servico_public_id", d.servicos[0].public_id);',
                'if (d.anexos && d.anexos[0]) pm.collectionVariables.set("arquivo_public_id", d.anexos[0].public_id);',
            ],
        ],
    ]]),
    item('Serviços', req('GET', $base . '/fornecedores/{{fornecedor_public_id}}/servicos')),
    item('Detalhe serviço', req('GET', $base . '/fornecedores/{{fornecedor_public_id}}/servicos/{{servico_public_id}}')),
    item('Download anexo fornecedor', req('GET', $base . '/fornecedores/{{fornecedor_public_id}}/anexos/{{arquivo_public_id}}/download')),
    item('Download anexo serviço', req('GET', $base . '/fornecedores/{{fornecedor_public_id}}/servicos/{{servico_public_id}}/anexos/{{arquivo_public_id}}/download')),
]);

$folders[] = folder('Avaliacoes', "Scope `avaliacoes:read`. Campanhas → feedbacks → resultado.", [
    item('Listar avaliacoes', req('GET', $base . '/avaliacoes?page={{page}}&per_page={{per_page}}'), null, captureScript('avaliacao_public_id')),
    item('Tipos de avaliacao', req('GET', $base . '/avaliacoes/tipos'), null, captureScript('tipo_public_id')),
    item('Tipos de avaliador', req('GET', $base . '/avaliacoes/tipos-avaliador')),
    item('Tópicos do tipo', req('GET', $base . '/avaliacoes/tipos/{{tipo_public_id}}/topicos')),
    item('Detalhe avaliacao', req('GET', $base . '/avaliacoes/{{avaliacao_public_id}}')),
    item('Feedbacks', req('GET', $base . '/avaliacoes/{{avaliacao_public_id}}/feedbacks?page={{page}}&per_page={{per_page}}'), null, [[
        'listen' => 'test',
        'script' => [
            'type' => 'text/javascript',
            'exec' => [
                'if (pm.response.code!==200) return;',
                'const row = (pm.response.json().data||[])[0];',
                'if (!row) return;',
                'pm.collectionVariables.set("feedback_public_id", row.public_id);',
                'if (row.funcionario && row.funcionario.public_id) pm.collectionVariables.set("funcionario_public_id", row.funcionario.public_id);',
            ],
        ],
    ]]),
    item('Detalhe feedback', req('GET', $base . '/avaliacoes/{{avaliacao_public_id}}/feedbacks/{{feedback_public_id}}')),
    item('Resultado consolidado', req('GET', $base . '/avaliacoes/{{avaliacao_public_id}}/feedbacks/{{feedback_public_id}}/resultado')),
    item('Feedbacks do colaborador', req('GET', $base . '/avaliacoes/{{avaliacao_public_id}}/colaboradores/{{funcionario_public_id}}/feedbacks')),
]);

$detalheData = $processoRow;
$detalheData['links']['exames'] = '/api/v3/processos/3fa85f64-5717-4562-b3fc-2c963f66afa6/exames';
$detalheData['links']['fluxo'] = '/api/v3/fluxo/3fa85f64-5717-4562-b3fc-2c963f66afa6';

$folders[] = folder('Processos', "Scope `processos:read`.\n\nListagens + nested. Use `processo_public_id` do Getting Started.", [
    item(
        'Ativos',
        req('GET', $base . '/processos/ativos?page={{page}}&per_page={{per_page}}&q=&uf='),
        "Admitidos sem demissão.\n\nExemplos:\n- `?q=Maria&uf=MA`\n- `?empresa_public_id={{empresa_public_id}}`\n- `?data_admissao_de=2026-01-01&data_admissao_ate=2026-12-31`",
        captureScript('processo_public_id'),
        [example('Ativos', 200, $processoExemplo)]
    ),
    item('Em andamento', req('GET', $base . '/processos/em-andamento?page={{page}}&per_page={{per_page}}'), 'Filtros: `status_admissao`, `parecer_de/ate`.'),
    item('Demitidos', req('GET', $base . '/processos/demitidos?page={{page}}&per_page={{per_page}}')),
    item('Buscar', req('GET', $base . '/processos/buscar?q=Maria'), 'Mínimo 2 caracteres.'),
    item(
        'Detalhe',
        req('GET', $base . '/processos/{{processo_public_id}}'),
        "Payload completo. Apenas `empresa` (sem campo `cliente` duplicado).",
        null,
        [example('Detalhe', 200, ['success' => true, 'data' => $detalheData])]
    ),
    item('Etapas', req('GET', $base . '/processos/{{processo_public_id}}/etapas'), 'Mapa booleano das etapas do fluxo.'),
    item('Exames', req('GET', $base . '/processos/{{processo_public_id}}/exames')),
    item('Documentos', req('GET', $base . '/processos/{{processo_public_id}}/documentos')),
    item('Ferias', req('GET', $base . '/processos/{{processo_public_id}}/ferias')),
    item('Pre-admissao', req('GET', $base . '/processos/{{processo_public_id}}/pre-admissao')),
]);

$folders[] = folder('Curriculos', "Scope `curriculos:read`.", [
    item('Listar', req('GET', $base . '/curriculos?page={{page}}&per_page={{per_page}}'), null, captureScript('curriculo_public_id')),
    item('Buscar', req('GET', $base . '/curriculos/buscar?q=Maria')),
    item('Detalhe', req('GET', $base . '/curriculos/{{curriculo_public_id}}')),
    item('Feedbacks', req('GET', $base . '/curriculos/{{curriculo_public_id}}/feedbacks?page={{page}}&per_page={{per_page}}')),
    item('Documentos', req('GET', $base . '/curriculos/{{curriculo_public_id}}/documentos')),
    item('Experiencias', req('GET', $base . '/curriculos/{{curriculo_public_id}}/experiencias')),
]);

$folders[] = folder('Fluxo', "Scope `fluxo:read`.", [
    item('Detalhe fluxo', req('GET', $base . '/fluxo/{{processo_public_id}}'), 'Detalhe completo + etapas.'),
]);

$folders[] = folder('Vagas Abertas', "Scope `vagas:read`.", [
    item('Listar', req('GET', $base . '/vagas-abertas?page={{page}}&per_page={{per_page}}'), null, captureScript('vaga_aberta_public_id')),
    item('Buscar', req('GET', $base . '/vagas-abertas/buscar?q=Oper')),
    item('Detalhe', req('GET', $base . '/vagas-abertas/{{vaga_aberta_public_id}}')),
    item('Simulados', req('GET', $base . '/vagas-abertas/{{vaga_aberta_public_id}}/simulados')),
    item('Projetos', req('GET', $base . '/vagas-abertas/{{vaga_aberta_public_id}}/projetos')),
]);

$folders[] = folder('Centros de Custo', "Scope `centros-custo:read`. Gestores sem login.", [
    item('Listar', req('GET', $base . '/centros-custo?page={{page}}&per_page={{per_page}}'), null, captureScript('centro_custo_public_id')),
    item('Buscar', req('GET', $base . '/centros-custo/buscar?q=CC')),
    item('Detalhe', req('GET', $base . '/centros-custo/{{centro_custo_public_id}}')),
    item('Filiais', req('GET', $base . '/centros-custo/{{centro_custo_public_id}}/filiais')),
    item('Gestores', req('GET', $base . '/centros-custo/{{centro_custo_public_id}}/gestores')),
]);

$folders[] = folder('CIH', "Scope `cih:read`.", [
    item('Listar', req('GET', $base . '/cih?page={{page}}&per_page={{per_page}}'), null, captureScript('cih_public_id')),
    item('Tags', req('GET', $base . '/cih/tags')),
    item('Detalhe', req('GET', $base . '/cih/{{cih_public_id}}')),
    item('Colaboradores', req('GET', $base . '/cih/{{cih_public_id}}/colaboradores')),
]);

$folders[] = folder('Medidas Administrativas', "Scope `medidas:read`. Motivo só no detalhe.", [
    item('Listar', req('GET', $base . '/medidas-administrativas?page={{page}}&per_page={{per_page}}'), null, captureScript('medida_public_id')),
    item('Detalhe', req('GET', $base . '/medidas-administrativas/{{medida_public_id}}')),
]);

$folders[] = folder('Historico', "Scope `historico:read`.", [
    item('Listar', req('GET', $base . '/historico?page={{page}}&per_page={{per_page}}'), null, captureScript('processo_public_id')),
    item('Detalhe', req('GET', $base . '/historico/{{processo_public_id}}')),
    item('Medidas', req('GET', $base . '/historico/{{processo_public_id}}/medidas')),
    item('Afastamentos', req('GET', $base . '/historico/{{processo_public_id}}/afastamentos')),
]);

$folders[] = folder('Pos Admissao', "Scope `pos-admissao:read`. Entrevista sanitizada.", [
    item('Listar', req('GET', $base . '/pos-admissao?page={{page}}&per_page={{per_page}}'), null, captureScript('processo_public_id')),
    item('Detalhe', req('GET', $base . '/pos-admissao/{{processo_public_id}}')),
    item('Entrevista desligamento', req('GET', $base . '/pos-admissao/{{processo_public_id}}/entrevista-desligamento')),
]);

$relItems = [
    item('Catalogo', req('GET', $base . '/relatorios'), 'Lista slugs + filtros sugeridos.'),
];

$slugs = [
    'efetivo' => 'Admitidos. Filtros: q, cargo, tipo_admissao, centro_custo_public_id, datas',
    'centro-custo' => 'Totais por centro de custo',
    'vencimento-asos' => 'Use somente_alerta=true para foco em vencidos/próximos',
    'vencimento-treinamentos' => 'Período: data_vencimento_de / data_vencimento_ate',
    'vencimento-ferias' => 'Avos / períodos aquisitivos',
    'ferias' => 'status_ferias=aguardando|gozando',
    'medidas-administrativas' => 'context.graficos.tipos',
    'avaliacao-experiencia' => 'context.resumo',
    'aniversariantes' => 'mes=1..12 (variável {{mes}})',
    'nps' => 'context.resumo.media_geral',
];

foreach ($slugs as $slug => $desc) {
    $url = $base . '/relatorios/' . $slug . '?page={{page}}&per_page={{per_page}}';
    if ($slug === 'aniversariantes') {
        $url .= '&mes={{mes}}';
    }
    if ($slug === 'vencimento-asos') {
        $url .= '&somente_alerta=false';
    }
    $examples = null;
    if ($slug === 'efetivo') {
        $examples = [example('Efetivo', 200, [
            'success' => true,
            'data' => [[
                'processo_public_id' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
                'nome' => 'Ana Relatorio',
                'cpf' => '***.***.***-88',
                'cargo' => 'Analista',
                'centro_custo' => 'CC Relatorio',
            ]],
            'meta' => ['current_page' => 1, 'per_page' => 20, 'total' => 1, 'last_page' => 1],
        ])];
    }
    if ($slug === 'aniversariantes') {
        $examples = [example('Aniversariantes', 200, [
            'success' => true,
            'data' => [['nome' => 'Ana Relatorio', 'aniversario' => '15/10', 'hoje' => false]],
            'meta' => ['current_page' => 1, 'per_page' => 20, 'total' => 1, 'last_page' => 1],
            'context' => ['mes' => 10],
        ])];
    }
    $relItems[] = item(ucfirst(str_replace('-', ' ', $slug)), req('GET', $url), $desc, null, $examples);
}

$folders[] = folder('Weekly', "Scope `weekly:read`. Quadros, listas e tarefas. Sem login e sem arquivo binário.", [
    item('Listar quadros', req('GET', $base . '/weekly?page={{page}}&per_page={{per_page}}&q='), null, captureScript('weekly_public_id')),
    item('Detalhe do quadro', req('GET', $base . '/weekly/{{weekly_public_id}}'), null, captureScript('lista_public_id', 'data.listas.0.public_id')),
    item('Listas', req('GET', $base . '/weekly/{{weekly_public_id}}/listas')),
    item('Tarefas da lista', req('GET', $base . '/weekly/{{weekly_public_id}}/listas/{{lista_public_id}}/tarefas?page={{page}}&per_page={{per_page}}'), null, captureScript('tarefa_public_id')),
    item('Detalhe da tarefa', req('GET', $base . '/weekly/{{weekly_public_id}}/tarefas/{{tarefa_public_id}}')),
]);

$folders[] = folder(
    'Relatorios',
    "Scope `relatorios:read`.\n\n1) Catalogo\n2) Execute o slug\n\nResposta: `data` + `meta` + opcional `context`.",
    $relItems
);

$vars = [
    'base_url' => 'http://localhost:8000',
    'client_id' => '',
    'client_secret' => '',
    'access_token' => '',
    'token_scope' => '',
    'page' => '1',
    'per_page' => '20',
    'mes' => (string) (int) date('n'),
    'public_id' => '',
    'cloud_public_id' => '',
    'item_public_id' => '',
    'fornecedor_public_id' => '',
    'servico_public_id' => '',
    'arquivo_public_id' => '',
    'avaliacao_public_id' => '',
    'tipo_public_id' => '',
    'feedback_public_id' => '',
    'funcionario_public_id' => '',
    'processo_public_id' => '',
    'curriculo_public_id' => '',
    'empresa_public_id' => '',
    'vaga_aberta_public_id' => '',
    'centro_custo_public_id' => '',
    'cih_public_id' => '',
    'medida_public_id' => '',
    'weekly_public_id' => '',
    'lista_public_id' => '',
    'tarefa_public_id' => '',
];

$collection = [
    'info' => [
        '_postman_id' => 'c3e8a1f0-api-externa-v3-0002',
        'name' => 'MyBP — API Externa v3',
        'description' => $collectionDesc,
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'variable' => array_map(
        static fn (string $k, string $v): array => ['key' => $k, 'value' => $v],
        array_keys($vars),
        array_values($vars)
    ),
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{access_token}}', 'type' => 'string']],
    ],
    'event' => [[
        'listen' => 'prerequest',
        'script' => [
            'type' => 'text/javascript',
            'exec' => [
                'if (!pm.request.headers.has("Accept")) {',
                '  pm.request.headers.add({ key: "Accept", value: "application/json" });',
                '}',
            ],
        ],
    ]],
    'item' => $folders,
];

$outPath = __DIR__ . '/Api-Externa-v3.postman_collection.json';
file_put_contents($outPath, json_encode($collection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n");
echo "Wrote {$outPath} folders=" . count($folders) . PHP_EOL;
