<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Alertas\AlertasConfigService;
use App\Domain\SaudeOcupacional\Conformidade\ConformidadeService;
use App\Domain\SaudeOcupacional\Conformidade\EncaminhamentoLoteService;
use App\Domain\SaudeOcupacional\Seguranca\PerfilAcessoSaude;
use App\Http\Controllers\Controller;
use App\Jobs\SaudeOcupacional\JobExportarConformidade;
use App\Models\ExameTipo;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * ASO e Exames → Conformidade: situação do ASO de cada colaborador ativo, encaminhamento em lote,
 * exportação e configuração dos alertas.
 */
class ConformidadeController extends Controller
{
    public function __construct(
        private readonly ConformidadeService $conformidade,
        private readonly EncaminhamentoLoteService $lote,
        private readonly AlertasConfigService $alertas
    ) {
    }

    public function index()
    {
        return view('g.saude-ocupacional.conformidade.index');
    }

    public function dados(Request $request): JsonResponse
    {
        $filtros = $this->filtros($request);
        $hoje = CarbonImmutable::today();
        $empresaId = $this->empresaId();
        $pagina = $this->conformidade->listar($empresaId, $filtros, max(1, (int) $request->input('page', 1)), (int) $request->input('pages', 30), $hoje);

        return response()->json([
            'itens' => $pagina->items(),
            'total' => $pagina->total(),
            'pagina' => $pagina->currentPage(),
            'por_pagina' => $pagina->perPage(),
            'ultima_pagina' => $pagina->lastPage(),
            // Contadores dos chips: mesmos filtros de busca/cargo, sem o de situação.
            'totais' => $this->conformidade->totais($empresaId, $hoje, array_merge($filtros, ['status' => null])),
            'janela_dias' => $this->conformidade->janela($empresaId)['dias'],
        ]);
    }

    public function opcoes(Request $request): JsonResponse
    {
        $empresaId = $this->empresaId();

        return response()->json([
            'cargos' => DB::table('vagas')->where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(['id', 'nome'])
                ->map(fn ($v) => ['value' => (int) $v->id, 'label' => (string) $v->nome])->all(),
            'tipos_exame' => ExameTipo::query()->where('ativo', true)->where('label', '!=', ExameTipo::LABEL_DEMISSIONAL)->orderBy('id')->get(['id', 'label'])
                ->map(fn ($t) => ['id' => (int) $t->id, 'label' => (string) $t->label])->all(),
            'clinicas' => DB::table('empresa_exames')->where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get(['id', 'nome'])
                ->map(fn ($c) => ['id' => (int) $c->id, 'label' => (string) $c->nome])->all(),
            'pcmsos' => DB::table('pcmsos')->where('empresa_id', $empresaId)->where('ativo', true)->orderBy('label')->get(['id', 'label'])
                ->map(fn ($p) => ['id' => (int) $p->id, 'label' => (string) $p->label])->all(),
            'pode_encaminhar' => Gate::allows('admissao_controle_exames'),
            'pode_configurar' => app(PerfilAcessoSaude::class)->podeConfigurar($request->user()),
            'alertas' => $this->alertas->obter($empresaId),
        ]);
    }

    public function encaminhar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'feedback_ids' => ['required', 'array', 'min:1', 'max:' . EncaminhamentoLoteService::MAX_COLABORADORES],
            'feedback_ids.*' => ['integer'],
            'exame_tipo_id' => ['required', 'integer'],
            'empresa_exame_id' => ['required', 'integer'],
            'pcmso_id' => ['required', 'integer'],
            'data_realizacao' => ['required', 'string', 'max:10'],
            'envia_email' => ['boolean'],
            'envia_whatsapp' => ['boolean'],
            'ignorar_com_encaminhamento_aberto' => ['boolean'],
        ], [
            'empresa_exame_id.required' => 'Escolha a clínica.',
            'pcmso_id.required' => 'Escolha o PCMSO.',
            'data_realizacao.required' => 'Informe a data de realização.',
        ]);

        $resultado = $this->lote->enfileirar($this->empresaId(), $dados, $request->user());
        $ign = $resultado['ignorados'];
        $partes = array_filter([
            $ign['com_encaminhamento_aberto'] ? $ign['com_encaminhamento_aberto'] . ' já tinha(m) encaminhamento sem resultado' : null,
            $ign['em_processamento'] ? $ign['em_processamento'] . ' já está(ão) sendo encaminhado(s)' : null,
            $ign['fora_da_lista_de_ativos'] ? $ign['fora_da_lista_de_ativos'] . ' não está(ão) entre os ativos' : null,
        ]);

        return response()->json($resultado + [
            'mensagem' => $resultado['enfileirados'] . ' encaminhamento(s) enfileirado(s).' . ($partes ? ' Ignorados: ' . implode('; ', $partes) . '.' : ''),
        ]);
    }

    public function exportar(Request $request): JsonResponse
    {
        $filtros = $this->filtros($request);
        $usuarioId = (int) $request->user()->id;
        $chave = 'saude_ocupacional:export_conformidade:' . $usuarioId . ':' . md5(json_encode($filtros));

        // Mesma exportação já em andamento: não gera de novo.
        if (!Cache::add($chave, 1, now()->addMinutes(10))) {
            return response()->json(['mensagem' => 'Essa exportação já está sendo gerada. Você será notificado quando terminar.']);
        }

        $arquivo = 'conformidade_aso_' . Str::lower(Str::random(6)) . '_' . date('YmdHis');
        JobExportarConformidade::dispatch($this->empresaId(), $usuarioId, $filtros, $arquivo, $chave);

        return response()->json(['mensagem' => 'Estamos gerando o Excel. Você será notificado quando terminar.']);
    }

    public function salvarAlertas(Request $request): JsonResponse
    {
        if (!app(PerfilAcessoSaude::class)->podeConfigurar($request->user())) {
            abort(403);
        }

        $dados = $request->validate([
            'janelas' => ['required', 'array', 'min:1', 'max:4'],
            'janelas.*' => ['integer', 'min:1', 'max:180'],
            'resumo_semanal' => ['required', 'boolean'],
        ]);

        return response()->json($this->alertas->salvar($this->empresaId(), $dados['janelas'], (bool) $dados['resumo_semanal']) + [
            'mensagem' => 'Alertas salvos.',
        ]);
    }

    /**
     * @return array{status: string|null, busca: string|null, vaga_id: int|null}
     */
    private function filtros(Request $request): array
    {
        $dados = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', ConformidadeService::STATUS)],
            'busca' => ['nullable', 'string', 'max:100'],
            'vaga_id' => ['nullable', 'integer'],
        ]);

        return [
            'status' => $dados['status'] ?? null,
            'busca' => isset($dados['busca']) ? trim($dados['busca']) : null,
            'vaga_id' => !empty($dados['vaga_id']) ? (int) $dados['vaga_id'] : null,
        ];
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
