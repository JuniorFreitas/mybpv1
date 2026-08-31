<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Municipio;
use App\Models\Projeto;
use App\Models\Simulado;
use App\Models\Vaga;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use App\Models\Vencimento;
use App\Tenant\Scopes\ScopeEmpresaGrupo;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MasterTag\DataHora;

class VagasAbertasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('cadastro_vagas_abertas');
        return view('g.cadastros.vagas_abertas.index');
    }

    /**
     * Empresas do grupo pra escolher em qual delas abrir a vaga (cargo é
     * compartilhado no grupo, mas o recrutamento em si é sempre pra 1
     * empresa específica).
     */
    public function empresasDisponiveis()
    {
        $empresaIds = ScopeEmpresaGrupo::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        return response()->json(
            Cliente::withoutGlobalScopes()
                ->whereIn('id', $empresaIds)
                ->orderBy('nome_fantasia')
                ->get(['id', 'nome_fantasia'])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_vagas_abertas_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['ativo_sistema'] = $dados['ativo_sistema'] == 'true' ? true : false;

        $empresaIdsDoGrupo = ScopeEmpresaGrupo::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required',
            'empresa_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::in($empresaIdsDoGrupo)],
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $empresaEscolhida = !empty($dados['empresa_id']) ? (int) $dados['empresa_id'] : null;
                unset($dados['empresa_id']); // deixa o EmpresaObserver estampar a empresa ativa primeiro

                $vagas_aberta = VagasAbertas::create($dados);

                if ($empresaEscolhida && $empresaEscolhida !== $vagas_aberta->empresa_id) {
                    $vagas_aberta->empresa_id = $empresaEscolhida;
                    $vagas_aberta->saveQuietly();
                }

                if (isset($dados['simulados'])) {
                    foreach ($dados['simulados'] as $simulado) {


                        if ($simulado['tipo_prova'] == 'subjetiva') {
                            $simulado['online'] = false;
                            $simulado['duracao'] = 0;
                        } else {
                            $simulado['online'] = $simulado['online'] == 'true';
                        }

                        $simulado['data_inicio'] = (new DataHora())->dataHoraInsert();
                        $simulado['data_fim'] = (new DataHora())->dataHoraInsert();
                        $simulado['ativo'] = $simulado['ativo'] == 'true';

                        if (isset($simulado['novo'])) {
                            $vagas_aberta->Simulados()->create($simulado);
                        } else {
                            $vagas_aberta->Simulados->find($simulado['id'])->update($simulado);
                        }
                    }
                }

                if ($erroProjetos = $this->validarProjetosVaga($dados['projetos'] ?? [])) {
                    DB::rollBack();

                    return $erroProjetos;
                }

                if (isset($dados['projetos'])) {
                    foreach ($dados['projetos'] as $projetos) {
                        if (isset($projetos['novo'])) {
                            $projetos['vaga_aberta_id'] = $vagas_aberta->id;
                            $projetos['qnt_preenchida'] = 0;
                            VagaProjeto::create($projetos);
                            $this->sincronizarRestanteProjeto((int) $projetos['projeto_id']);
                        }
                    }
                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function show(VagasAbertas $vagas_aberta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return VagasAbertas|\Illuminate\Http\Response
     */
    public function edit(VagasAbertas $vagas_aberta)
    {
        $vagas_aberta->load(
            'Municipio',
            'Vaga',
            'Vaga.Cbo.familia',
            'Vaga.Vencimentos:id,label,segmento_treinamento_id,vinculo_todos_cargos',
            'Vaga.Vencimentos.SegmentoTreinamento:id,nome',
            'Simulados',
            'Projetos.Projeto:id,nome,qnt_total,qnt_total_restante'
        );

        $vagas_aberta->Simulados->transform(function ($item) {
            $item->tipo_prova = $item->simulado->tipo_prova;
            return $item;
        });

        $vagas_aberta->Projetos->transform(function (VagaProjeto $vagaProjeto) {
            $projeto = $vagaProjeto->Projeto;
            if (!$projeto) {
                return $vagaProjeto;
            }

            $alocadaOutras = (int) VagaProjeto::where('projeto_id', $vagaProjeto->projeto_id)
                ->where('id', '!=', $vagaProjeto->id)
                ->sum('qnt_total');

            $alocadaTotal = (int) VagaProjeto::where('projeto_id', $vagaProjeto->projeto_id)->sum('qnt_total');
            $livreGlobal = max(0, (int) $projeto->qnt_total - $alocadaTotal);

            $vagaProjeto->qnt_alocada_outras = $alocadaOutras;
            $vagaProjeto->projeto_qnt_total = (int) $projeto->qnt_total;
            $vagaProjeto->qnt_livre_projeto = $livreGlobal;
            $vagaProjeto->qnt_maxima_permitida = max(
                (int) $vagaProjeto->qnt_preenchida,
                $livreGlobal
            );

            return $vagaProjeto;
        });

        $vencimentosTodosCargos = Vencimento::with('SegmentoTreinamento:id,nome')
            ->where('vinculo_todos_cargos', true)
            ->where('ativo', true)
            ->whereNotNull('label')
            ->get(['id', 'label', 'segmento_treinamento_id', 'vinculo_todos_cargos']);

        if ($vagas_aberta->Vaga) {
            $merged = $vagas_aberta->Vaga->Vencimentos->concat($vencimentosTodosCargos)->unique('id')->values();
            $vagas_aberta->Vaga->setRelation('vencimentos', $merged);
        }

        return $vagas_aberta;

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, VagasAbertas $vagas_aberta)
    {
        $this->authorize('cadastro_vagas_abertas_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['ativo_sistema'] = $dados['ativo_sistema'] == 'true' ? true : false;

        $empresaIdsDoGrupo = ScopeEmpresaGrupo::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required',
            'empresa_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::in($empresaIdsDoGrupo)],
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            if ($erroProjetosDelete = $this->validarProjetosDelete($dados['projetosDelete'] ?? [], $vagas_aberta->id)) {
                return $erroProjetosDelete;
            }

            if ($erroProjetos = $this->validarProjetosVaga($dados['projetos'] ?? [], $vagas_aberta->id)) {
                return $erroProjetos;
            }

            try {
                DB::beginTransaction();

                $empresaEscolhida = !empty($dados['empresa_id']) ? (int) $dados['empresa_id'] : null;
                unset($dados['empresa_id']);

                $vagas_aberta->update($dados);

                if ($empresaEscolhida && $empresaEscolhida !== $vagas_aberta->empresa_id) {
                    $vagas_aberta->empresa_id = $empresaEscolhida;
                    $vagas_aberta->saveQuietly();
                }
                if (isset($dados['simuladosDelete'])) {
                    foreach ($dados['simuladosDelete'] as $id) {
                        $vagas_aberta->Simulados->find($id)->delete();
                    }
                }

                if (isset($dados['simulados'])) {
                    foreach ($dados['simulados'] as $simulado) {

                        if ($simulado['simulado']['tipo_prova'] == 'subjetiva') {
                            $simulado['online'] = false;
                            $simulado['duracao'] = 0;
                        } else {
                            $simulado['online'] = $simulado['online'] == 'true';
                        }

                        $simulado['data_inicio'] = (new DataHora())->dataHoraInsert();
                        $simulado['data_fim'] = (new DataHora())->dataHoraInsert();
                        $simulado['ativo'] = $simulado['ativo'] == 'true';

                        if (isset($simulado['novo'])) {
                            $vagas_aberta->Simulados()->create($simulado);
                        } else {
                            $vagas_aberta->Simulados->find($simulado['id'])->update($simulado);
                        }
                    }
                }

                if (isset($dados['projetosDelete'])) {
                    foreach ($dados['projetosDelete'] as $id) {
                        $vagaProjetos = VagaProjeto::whereId($id)->whereVagaAbertaId($vagas_aberta->id)->first();
                        if (!$vagaProjetos) {
                            continue;
                        }
                        $projetoId = (int) $vagaProjetos->projeto_id;
                        $vagaProjetos->delete();
                        $this->sincronizarRestanteProjeto($projetoId);
                    }
                }

                if (isset($dados['projetos'])) {
                    foreach ($dados['projetos'] as $projetos) {
                        $qnt_total = intval($projetos['qnt_total']);
                        if (isset($projetos['novo'])) {
                            $projetos['vaga_aberta_id'] = $vagas_aberta->id;
                            $projetos['qnt_preenchida'] = 0;
                            VagaProjeto::create($projetos);
                            $this->sincronizarRestanteProjeto((int) $projetos['projeto_id']);
                        } else {
                            $vagaProjeto = VagaProjeto::whereId($projetos['id'])
                                ->whereVagaAbertaId($vagas_aberta->id)
                                ->first();

                            if (!$vagaProjeto) {
                                DB::rollBack();

                                return response()->json(['msg' => 'Vínculo de projeto não encontrado.'], 422);
                            }

                            if ((int) $vagaProjeto->qnt_total === $qnt_total) {
                                continue;
                            }

                            $vagaProjeto->update(['qnt_total' => $qnt_total]);
                            $this->sincronizarRestanteProjeto((int) $vagaProjeto->projeto_id);
                        }
                    }
                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function destroy(VagasAbertas $vagas_aberta)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_vagas_abertas');
        $treinamentosGlobaisCount = Vencimento::query()
            ->whereAtivo(true)
            ->where('vinculo_todos_cargos', true)
            ->count();

        $resultado = VagasAbertas::with([
            'Vaga:id,nome,cbo_id',
            'Vaga.Cbo:id,codigo,titulo,codigo_familia',
            'Vaga.Cbo.familia:codigo,titulo',
            'Vaga.Vencimentos' => function ($q) {
                $q->select('vencimentos.id', 'vencimentos.label', 'vencimentos.segmento_treinamento_id', 'vencimentos.vinculo_todos_cargos', 'vencimentos.ativo')
                    ->where('vencimentos.ativo', true);
            },
            'Municipio',
            'Simulados.Simulado:id,titulo,tipo_prova',
            'Projetos.Projeto:id,nome',
        ]);

        if ($request->filled('campoBusca')) {
            $termo = $request->campoBusca;
            $resultado->where(function ($query) use ($termo) {
                $query->where('titulo', 'like', '%' . $termo . '%')
                    ->orWhere('id', $termo)
                    ->orWhereHas('Vaga', function ($q) use ($termo) {
                        $q->where('nome', 'like', '%' . $termo . '%');
                    })
                    ->orWhereHas('Municipio', function ($q) use ($termo) {
                        $q->where('nome', 'like', '%' . $termo . '%');
                    });
            });
        }

        if ($request->filled('campoAtivoSite')) {
            $resultado->whereAtivo($request->campoAtivoSite == 'true');
        } elseif ($request->filled('campoStatus')) {
            $resultado->whereAtivo($request->campoStatus == 'true');
        }

        if ($request->filled('campoAtivoSistema')) {
            $resultado->where('ativo_sistema', $request->campoAtivoSistema == 'true');
        }

        if ($request->filled('campoCargoId')) {
            $resultado->where('vaga_id', (int) $request->campoCargoId);
        }

        if ($request->filled('campoMunicipioId')) {
            $resultado->where('municipio_id', (int) $request->campoMunicipioId);
        }

        if ($request->filled('campoComProvas')) {
            if ($request->campoComProvas === 'sim') {
                $resultado->whereHas('Simulados');
            } elseif ($request->campoComProvas === 'nao') {
                $resultado->whereDoesntHave('Simulados');
            }
        }

        if ($request->filled('campoProjetoId')) {
            if ($request->campoProjetoId === 'com_vinculo') {
                $resultado->whereHas('Projetos');
            } elseif ($request->campoProjetoId === 'sem_vinculo') {
                $resultado->whereDoesntHave('Projetos');
            } elseif (is_numeric($request->campoProjetoId)) {
                $resultado->whereHas('Projetos', function ($q) use ($request) {
                    $q->where('projeto_id', (int) $request->campoProjetoId);
                });
            }
        }

        $resultado = $resultado->orderByDesc('updated_at')->paginate(50);
        $simulados = Simulado::whereAtivo(true)->orderBy('titulo')->get();
        $projetos = Projeto::query()
            ->orderBy('nome')
            ->get(['id', 'nome', 'qnt_total', 'qnt_total_restante', 'preenchidas'])
            ->map(function (Projeto $projeto) {
                $alocada = (int) VagaProjeto::where('projeto_id', $projeto->id)->sum('qnt_total');
                $projeto->qnt_alocada = $alocada;
                $projeto->qnt_disponivel_projeto = max(0, (int) $projeto->qnt_total - $alocada);

                return $projeto;
            });

        $items = collect($resultado->items())->map(function (VagasAbertas $item) use ($treinamentosGlobaisCount) {
            $item->slug = "{$item->id}/" . Str::slug($item->titulo);
            $item->municipio_label = $item->Municipio
                ? "{$item->Municipio->nome} - {$item->Municipio->uf}"
                : null;
            $item->cargo_nome = $item->Vaga?->nome;

            $vinculados = $item->Vaga ? $item->Vaga->Vencimentos->count() : 0;
            $item->treinamentos_vinculados_count = $vinculados;
            $item->treinamentos_globais_count = $item->Vaga ? $treinamentosGlobaisCount : 0;
            $item->treinamentos_total_count = $vinculados + ($item->Vaga ? $treinamentosGlobaisCount : 0);

            $cbo = $item->Vaga?->Cbo;
            $item->cbo_codigo = $cbo?->codigo;
            $item->cbo_codigo_familia = $cbo?->codigo_familia ?? $cbo?->familia?->codigo;
            $item->cbo_titulo = $cbo?->titulo;
            $item->cbo_familia = $cbo?->familia?->titulo;
            $item->cbo_vinculado = (bool) $cbo;

            $descricaoPlain = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $item->descricao, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
            $item->descricao_tem_conteudo = $descricaoPlain !== '';
            $item->descricao_resumo = $descricaoPlain !== '' ? Str::limit($descricaoPlain, 300, '…') : null;

            $item->updated_at_br = $item->updated_at?->format('d/m/Y H:i');

            $simuladosVaga = $item->Simulados ?? collect();
            $item->simulados_count = $simuladosVaga->count();
            $item->simulados_ativos_count = $simuladosVaga->where('ativo', true)->count();
            $item->simulados_titulos = $simuladosVaga->map(function ($simuladoVaga) {
                $titulo = $simuladoVaga->Simulado?->titulo;
                if (!$titulo) {
                    return null;
                }

                return $simuladoVaga->ativo ? $titulo : "{$titulo} (inativa)";
            })->filter()->values()->all();

            $projetosVaga = $item->Projetos ?? collect();
            $item->projetos_count = $projetosVaga->count();
            $item->projetos_titulos = $projetosVaga->map(function (VagaProjeto $vagaProjeto) {
                $nome = $vagaProjeto->Projeto?->nome;
                if (!$nome) {
                    return null;
                }

                $total = (int) $vagaProjeto->qnt_total;
                $preenchidas = (int) $vagaProjeto->qnt_preenchida;

                return "{$nome} ({$preenchidas}/{$total})";
            })->filter()->values()->all();

            $item->treinamentos_vinculados_labels = $item->Vaga
                ? $item->Vaga->Vencimentos->pluck('label')->filter()->values()->all()
                : [];
            $item->treinamentos_tem_vinculo = $item->treinamentos_total_count > 0;

            return $item;
        })->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $items,
                'simulados' => $simulados,
                'projetos' => $projetos,
                'filtros' => $this->opcoesFiltroVagasAbertas(),
            ]
        ]);
    }

    private function opcoesFiltroVagasAbertas(): array
    {
        $vagaIds = VagasAbertas::query()
            ->whereNotNull('vaga_id')
            ->distinct()
            ->pluck('vaga_id');

        $municipioIds = VagasAbertas::query()
            ->whereNotNull('municipio_id')
            ->distinct()
            ->pluck('municipio_id');

        $cargos = $vagaIds->isEmpty()
            ? collect()
            : Vaga::query()
                ->whereIn('id', $vagaIds)
                ->whereAtivo(true)
                ->orderBy('nome')
                ->get(['id', 'nome']);

        $municipios = $municipioIds->isEmpty()
            ? collect()
            : Municipio::query()
                ->whereIn('id', $municipioIds)
                ->orderBy('nome')
                ->get(['id', 'nome', 'uf']);

        $projetoIds = VagaProjeto::query()
            ->distinct()
            ->pluck('projeto_id');

        $projetosFiltro = Projeto::query()
            ->orderBy('nome')
            ->get(['id', 'nome', 'qnt_total', 'qnt_total_restante'])
            ->map(function (Projeto $projeto) {
                $alocada = (int) VagaProjeto::where('projeto_id', $projeto->id)->sum('qnt_total');
                $projeto->qnt_alocada = $alocada;
                $projeto->qnt_disponivel_projeto = max(0, (int) $projeto->qnt_total - $alocada);

                return $projeto;
            });

        return [
            'cargos' => $cargos->values(),
            'municipios' => $municipios->values(),
            'projetos' => $projetosFiltro->values(),
            'projetos_vinculados_ids' => $projetoIds->values(),
        ];
    }

    /**
     * Treinamentos (vencimentos) vinculados ao cargo, para uso na tela de Vagas Abertas.
     */
    public function treinamentosDoCargo(Vaga $vaga)
    {
        $this->authorize('cadastro_vagas_abertas');

        $vaga->load([
            'Vencimentos' => function ($q) {
                $q->select('vencimentos.id', 'vencimentos.label', 'vencimentos.segmento_treinamento_id', 'vencimentos.vinculo_todos_cargos')
                    ->where('vencimentos.ativo', true);
            },
            'Vencimentos.SegmentoTreinamento:id,nome',
        ]);

        $globais = Vencimento::with('SegmentoTreinamento:id,nome')
            ->where('vinculo_todos_cargos', true)
            ->where('ativo', true)
            ->whereNotNull('label')
            ->get(['id', 'label', 'segmento_treinamento_id', 'vinculo_todos_cargos']);

        $merged = $vaga->Vencimentos->concat($globais)->unique('id')->values();

        return response()->json(
            $merged->map(function ($v) {
                return [
                    'id' => $v->id,
                    'label' => $v->label,
                    'padrao_treinamento' => optional($v->SegmentoTreinamento)->nome ?? 'Geral',
                    'todos_cargos' => (bool) ($v->vinculo_todos_cargos ?? false),
                ];
            })
        );
    }

    public function ativaDesativa(VagasAbertas $vagas_aberta)
    {
        $this->authorize('cadastro_vagas_abertas_update');
        $vagas_aberta->ativo = !$vagas_aberta->ativo;
        $vagas_aberta->save();
        $vagas_aberta->refresh();
        return response()->json(['ativo' => $vagas_aberta->ativo], 201);
    }

    public function ativaDesativaSistema(VagasAbertas $vagas_aberta)
    {
        $this->authorize('cadastro_vagas_abertas_update');
        $vagas_aberta->ativo_sistema = !$vagas_aberta->ativo_sistema;
        $vagas_aberta->save();
        $vagas_aberta->refresh();
        return response()->json(['ativo_sistema' => $vagas_aberta->ativo_sistema], 201);
    }

    public function vagaAbertaSimulado($simulado, $vaga_aberta)
    {

        $vaga = VagasAbertas::find($vaga_aberta)->load('Vaga');

        $prova = Simulado::find($simulado)->load('Perguntas');

        $pdf = \PDF::loadView('pdf.cadastro.prova.provasubjetiva', compact('prova', 'vaga'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("prova.pdf");
    }

    /**
     * Valida remoção de vínculos de projetos na edição da vaga aberta.
     */
    private function validarProjetosDelete(array $ids, int $vagaAbertaId): ?\Illuminate\Http\JsonResponse
    {
        foreach ($ids as $id) {
            $vagaProjeto = VagaProjeto::whereId($id)->whereVagaAbertaId($vagaAbertaId)->first();

            if (!$vagaProjeto) {
                continue;
            }

            if ((int) $vagaProjeto->qnt_preenchida > 0) {
                $nome = Projeto::find($vagaProjeto->projeto_id)?->nome ?? 'Projeto';

                return response()->json([
                    'msg' => "Não é possível remover o projeto \"{$nome}\" porque já possui {$vagaProjeto->qnt_preenchida} vaga(s) preenchida(s).",
                ], 422);
            }
        }

        return null;
    }

    /**
     * Valida vínculos de projetos antes de persistir na vaga aberta.
     */
    private function validarProjetosVaga(array $projetos, ?int $vagaAbertaId = null): ?\Illuminate\Http\JsonResponse
    {
        if ($projetos === []) {
            return null;
        }

        $projetoIds = [];

        foreach ($projetos as $index => $item) {
            $linha = $index + 1;

            if (empty($item['projeto_id'])) {
                return response()->json(['msg' => "Selecione o projeto na linha #{$linha}."], 422);
            }

            $projetoId = (int) $item['projeto_id'];

            if (in_array($projetoId, $projetoIds, true)) {
                return response()->json(['msg' => 'Não é permitido vincular o mesmo projeto mais de uma vez nesta vaga.'], 422);
            }

            $projetoIds[] = $projetoId;

            $qntTotal = (int) ($item['qnt_total'] ?? 0);

            if ($qntTotal < 1) {
                return response()->json(['msg' => "Informe a quantidade de vagas na linha #{$linha}."], 422);
            }

            $isNovo = !empty($item['novo']) || empty($item['id']);

            if (!$isNovo) {
                $vagaProjeto = VagaProjeto::whereId($item['id'])
                    ->when($vagaAbertaId, fn ($query) => $query->whereVagaAbertaId($vagaAbertaId))
                    ->first();

                if (!$vagaProjeto) {
                    return response()->json(['msg' => "Vínculo de projeto da linha #{$linha} não encontrado."], 422);
                }

                $qntPreenchida = (int) $vagaProjeto->qnt_preenchida;

                if ($qntTotal < $qntPreenchida) {
                    return response()->json([
                        'msg' => "A linha #{$linha} possui {$qntPreenchida} vaga(s) preenchida(s). A quantidade não pode ser menor que isso.",
                    ], 422);
                }

                $projeto = Projeto::find($projetoId);

                if (!$projeto) {
                    return response()->json(['msg' => "Projeto da linha #{$linha} não encontrado."], 422);
                }

                $maxPermitida = $this->quantidadeMaximaLinhaProjeto($projeto, $qntPreenchida);

                if ($qntTotal > $maxPermitida) {
                    return response()->json([
                        'msg' => "O projeto \"{$projeto->nome}\" possui {$this->quantidadeLivreProjeto($projeto)} vaga(s) livre(s). A quantidade total não pode ser maior que {$maxPermitida}.",
                    ], 422);
                }

                continue;
            }

            $projeto = Projeto::find($projetoId);

            if (!$projeto) {
                return response()->json(['msg' => "Projeto da linha #{$linha} não encontrado."], 422);
            }

            $livreProjeto = $this->quantidadeLivreProjeto($projeto);

            foreach ($projetos as $outroIndex => $outro) {
                if ($outroIndex === $index) {
                    continue;
                }

                if ((int) ($outro['projeto_id'] ?? 0) !== $projetoId) {
                    continue;
                }

                if (!empty($outro['novo']) || empty($outro['id'])) {
                    $livreProjeto -= (int) ($outro['qnt_total'] ?? 0);
                }
            }

            $maxPermitida = max(1, $livreProjeto);

            if ($qntTotal > $maxPermitida) {
                return response()->json([
                    'msg' => "O projeto \"{$projeto->nome}\" possui {$this->quantidadeLivreProjeto($projeto)} vaga(s) livre(s). A quantidade total não pode ser maior que {$maxPermitida}.",
                ], 422);
            }
        }

        return null;
    }

    /**
     * Vagas livres no projeto (capacidade menos alocações já distribuídas).
     */
    private function quantidadeLivreProjeto(Projeto $projeto): int
    {
        $alocada = (int) VagaProjeto::where('projeto_id', $projeto->id)->sum('qnt_total');

        return max(0, (int) $projeto->qnt_total - $alocada);
    }

    /**
     * Máximo permitido na linha: não menor que preenchidas e não maior que livre do projeto.
     */
    private function quantidadeMaximaLinhaProjeto(Projeto $projeto, int $qntPreenchida = 0): int
    {
        return max($qntPreenchida, $this->quantidadeLivreProjeto($projeto));
    }

    /**
     * Capacidade máxima desta vaga dentro do projeto, com base na configuração real do projeto.
     * @deprecated Usar quantidadeMaximaLinhaProjeto()
     */
    private function quantidadeMaximaVagaProjeto(Projeto $projeto, ?int $vagaProjetoId = null, int $qntPreenchida = 0): int
    {
        return $this->quantidadeMaximaLinhaProjeto($projeto, $qntPreenchida);
    }

    /**
     * Mantém qnt_total_restante coerente com a capacidade configurada do projeto.
     */
    private function sincronizarRestanteProjeto(int $projetoId): void
    {
        $projeto = Projeto::find($projetoId);

        if (!$projeto) {
            return;
        }

        $alocada = (int) VagaProjeto::where('projeto_id', $projetoId)->sum('qnt_total');

        $projeto->update([
            'qnt_total_restante' => max(0, (int) $projeto->qnt_total - $alocada),
        ]);
    }
}
