# MyBP - Guia para LLMs/Agents

## Visão Geral

**MyBP** é um sistema de gestão de RH desenvolvido em **Laravel 8 (PHP 8.2)** + **Vue.js 2**.

### Stack Principal

-   **Backend**: Laravel 8.12, Sanctum, Horizon (Redis), Websockets
-   **Frontend**: Vue 2.7.16, Bootstrap Vue, Laravel Mix
-   **Database**: MySQL/MariaDB + Redis
-   **Storage**: AWS S3
-   **Infra**: Docker, AWS ECS
-   **Integrações**: WhatsApp (ZapMe), Excel (Maatwebsite), PDF (DomPDF)

### Características Críticas

-   **Multi-tenant**: Sempre filtrar por `empresa_id`
-   **Sistema de Aprovações**: 3 níveis (Gestor → Aprovação Extra → RH)
-   **Real-time**: Laravel Websockets + Echo
-   **Jobs**: Horizon para filas assíncronas
-   **Cache/Queue**: Redis
-   **ORM**: Eloquent

### Infrastructure

-   **Containers**: Docker + Docker Compose
-   **Cloud**: AWS (ECS, ECR, S3)
-   **CI/CD**: Bitbucket Pipelines

---

## Estrutura de Diretórios (Resumo)

```
mybp/
├── app/
│   ├── Classes/           # ZapNotificacao (WhatsApp)
│   ├── Http/Controllers/  # Controllers
│   ├── Models/            # 150+ models Eloquent
│   ├── Services/          # Zapme/, Dynamus/, Cih/, Entrevistas/, Treinamento/
│   ├── Jobs/              # Jobs assíncronos
│   └── Tenant/            # Multi-tenancy
├── database/migrations/   # Migrations
├── docs/                  # Documentação técnica completa
├── resources/js/          # Componentes Vue
├── routes/web.php         # Rotas (1463 linhas)
└── docker-compose*.yml    # Configs Docker
```

---

## Models Principais (app/Models/)

**Core**: `User`, `Cliente` (multi-tenant), `Departamento`, `CentroCusto`, `Habilidade`

**Processos com Aprovação Extra**: `DemissaoPrevista`, `MudancaCargo`, `FeriasPrevista`, `ValorExtraPrevista`, `AprovacaoExtraConfig`

**Recrutamento**: `Curriculo`, `Vaga`, `VagasAbertas`, `FeedbackCurriculo`, `EntrevistaRh`, `ParecerRh`

**Admissão**: `Admissao`, `AdmissoesPrevista`, `DocumentosPreAdmissao`, `CartaOferta`

**Treinamento**: `Treinamento`, `TreinamentoEvento`, `Instrutor`, `CertificadoNr`

**Avaliações**: `Avaliacao`, `AvaliacaoNoventaDias`, `Formulario`, `AvaliacaoResposta`

**Medicina/Segurança**: `Exame`, `ExameFuncionario`, `Pcmso`, `Afastamento`

**Comunicação**: `Notificacao`, `NotificacaoWhatsapp`, `MensagemChat`

_Total: 150+ models em app/Models/_

---

## Sistema de Aprovações (⚠️ PADRÃO CRÍTICO)

**Fluxo**: Gestor → **Aprovação Extra** (opcional) → RH

### Processos com Aprovação Extra

`DemissaoPrevista`, `MudancaCargo`, `FeriasPrevista`, `ValorExtraPrevista`

### AprovacaoExtraConfig (configurável por empresa)

```php
tipo_processo: 'demissao'|'mudanca_cargo'|'ferias'|'valor_extra'
usuarios_autorizados: [user_ids] // Quem pode aprovar
nome_aprovacao: "Gerência" // Label personalizado
```

### Quem Pode Aprovar?

-   `privilegio_gestao_rh` OU `privilegio_aprovar_por_rh` (sempre)
-   Usuários em `usuarios_autorizados` (por config)

### Colunas nas Tabelas

`aprovacao_extra_id`, `status_aprovacao_extra`, `obs_aprovacao_extra`, `data_aprovacao_extra`

**📖 Detalhes**: [docs/PADRAO_APROVACAO_EXTRA.md](./docs/PADRAO_APROVACAO_EXTRA.md)

---

## Padrões de Código

### Controllers (estrutura típica)

```php
class ExemploController extends Controller {
    public function atualizar(Request $request) {
        $resultado = $this->filtro($request)->paginate($request->pages);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'dados' => ['itens' => $resultado->items()]
        ]);
    }

    private function filtro($request) {
        return Model::query()
            ->with(['rel'])
            ->where('empresa_id', auth()->user()->empresa_id) // ⚠️ Multi-tenant!
            ->when($request->campo, fn($q) => $q->where('campo', 'like', "%{$request->campo}%"))
            ->orderBy('created_at', 'desc');
    }
}
```

### Models

```php
protected $fillable = ['campo1', 'campo2'];
protected $casts = ['data' => 'datetime', 'ativo' => 'boolean'];

public function empresa() { return $this->belongsTo(Cliente::class, 'empresa_id'); }
```

### WhatsApp (ZapNotificacao)

```php
(new ZapNotificacao())->enviar([
    'telefone' => '5598999023762',
    'mensagem' => 'Sua solicitação foi aprovada!',
    'enviado_id' => auth()->id(),
    'anexo' => ['arquivo' => $pdfBase64, 'tipo' => ZapNotificacao::EXTENSAO_PDF] // opcional
]);
```

---

## Frontend Vue 2

**Localização**: `resources/js/components/`

### Template Essencial

```vue
<template>
    <div class="container-fluid">
        <input v-model="filtros.campo" @keyup.enter="atualizar" />
        <table>
            <tr v-for="item in dados" :key="item.id">
                <td>{{ item.campo }}</td>
            </tr>
        </table>
        <pagination v-model="pagina" :ultima="ultima" @input="atualizar" />
    </div>
</template>

<script>
export default {
    data() {
        return { dados: [], filtros: {}, pagina: 1, ultima: 1 }
    },
    mounted() {
        this.atualizar()
    },
    methods: {
        atualizar() {
            axios.post('/api/rota/atualizar', { ...this.filtros, pages: this.pagina }).then((r) => {
                this.dados = r.data.dados.itens
                this.pagina = r.data.atual
            })
        }
    }
}
</script>
```

**Bibliotecas**: SweetAlert2 (`$swal`), BootstrapVue, vue-multiselect, TinyMCE

---

## Rotas (`routes/web.php` - 1463 linhas)

### Padrão

```php
// API
Route::post('/api/modelo/atualizar', [Controller::class, 'atualizar']);
Route::post('/api/modelo/salvar|deletar', [Controller::class, 'salvar|deletar']);
Route::post('/api/modelo/aprovar-gestor|extra|rh', [Controller::class, 'aprovar*']);

// Views
Route::get('/modelo', fn() => view('modelo.index'))->name('modelo');
```

## Comandos Essenciais

```bash
# Docker
docker compose up -d && docker compose exec mybpdp bash

# Laravel
php artisan migrate && php artisan cache:clear && php artisan horizon

# Assets
npm run watch   # Dev
npm run prod    # Prod

# Deploy
./deploy-full.sh
```

**Lista completa**: [COMANDOS_UTEIS.md](./COMANDOS_UTEIS.md)

---

## Deploy (AWS ECS)

**Scripts**: `deploy-full.sh`, `deployDocker.sh`, `bitbucket-pipelines.yml`

**Docs**: [README-DEPLOY.md](./docs/README-DEPLOY.md), [SOLUCAO_DUPLICACAO_JOB_ECS.md](./docs/SOLUCAO_DUPLICACAO_JOB_ECS.md), [LIMPEZA_ECR_AUTOMATICA.md](./docs/LIMPEZA_ECR_AUTOMATICA.md)

## Fluxos Principais

### 1. Recrutamento e Seleção

```
Vaga Aberta → Currículos → Triagem → Entrevista RH →
Teste Prático → Parecer → Aprovação → Admissão
```

### 2. Admissão

```
Pré-admissão → Documentos → Exames → Carta de Oferta →
Admissão Efetivada
```

### 3. Demissão (com Aprovação Extra)

```
Solicitação → Aprovação Gestor → Aprovação Extra (opcional) →
Aprovação RH → Demissão Efetivada
```

### 4. Férias

```
Solicitação → Aprovação Gestor → Aprovação Extra (opcional) →
Aprovação RH → Férias Aprovadas
```

### 5. Treinamentos

```
Planejamento → Convocação → Realização → Certificação →
Controle de Vencimentos
```

### 6. Avaliações

```
Período Avaliativo → Formulários → Respostas →
Consolidação → Feedback
```

---

## Boas Práticas

### 1. Multi-tenancy (CRÍTICO ⚠️)

```php
// ✅ SEMPRE filtrar por empresa_id
Model::where('empresa_id', auth()->user()->empresa_id)->get();

// ❌ NUNCA buscar sem filtro (vaza dados entre empresas!)
Model::all();
```

### 2. Eager Loading (Performance)

```php
// ✅ 1 query
Model::with('relacionamento')->get();

// ❌ N+1 queries
foreach (Model::all() as $m) { $m->relacionamento; }
```

### 3. Select Apenas Colunas Necessárias

```php
// ✅ Select otimizado
Model::select('id', 'nome', 'email')->where('ativo', true)->get();

// ❌ Select * (traz todas as colunas, mesmo as não usadas)
Model::where('ativo', true)->get();
```

### 4. Filas para Operações Lentas

```php
// ✅ Assíncrono
EnviarEmailJob::dispatch($dados);

// ❌ Síncrono (trava requisição)
Mail::send(...);
```

### 5. Transações em Operações Críticas

```php
try {
    DB::beginTransaction();
    // operações
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    return response()->json(['erro' => true], 500);
}
```

### 6. Validação de Requests

```php
// ✅ Form Request
class StoreRequest extends FormRequest {
    public function rules() {
        return ['campo' => 'required|max:255'];
    }
}
```

---

**Última atualização**: 2026-02-07  
**Versão Laravel**: 8.12  
**Versão PHP**: 8.2  
**Versão Vue**: 2.7.16
