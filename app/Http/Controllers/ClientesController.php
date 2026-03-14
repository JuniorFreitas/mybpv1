<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
use App\Mail\Movimentacao\FeriasPrevista\SaidaMail;
use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Models\Area;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\FeriasPrevista;
use App\Models\Habilidade;
use App\Models\Papel;
use App\Models\Servico;
use App\Models\Sistema;
use App\Models\User;
use App\Services\AssinaturaDigital\AssinaturaCotaService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use MasterTag\DataHora;
use PDF;


class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.clientes.index');
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
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:clientes,cnpj',
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:clientes,cpf',
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'area_id' => 'required',
            'contato' => 'required',
            'aniversario' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
        ];


        array_merge($validar, $validaComum);

        if (!isset($dados['telefones'])) {
            return response()->json([
                'msg' => 'É Necessário Informar pelo menos Um número de telefone'
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, $validar);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Cliente',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $user = User::create([
                    'nome' => $dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA ? $dados['razao_social'] : $dados['nome'],
                    'password' => bcrypt('mybp2021'),
                    'login' => $dados['email'],
                    'tipo' => 'Empresa',
                    'temp' => false,
                    'empresa_id' => auth()->user()->empresa_id,
                    'ativo' => true,
                ]);

                User::find($user->id)->update(['empresa_id' => $user->id]);

                auth()->user()->ClientesEmpresa()->attach($user->id);

                $cliente = $user->Cliente()->create($dados);

                /**ToDo VER PORQUE TA ZERADO **/
                $cliente->id = $user->id;
                $cliente->save();

                foreach ($dados['telefones'] as $linha) {
                    $linha['cliente_id'] = $cliente->id;
                    $cliente->Telefones()->create($linha);
                }
                // Se tem Cliente
                if (isset($dados['servicos_cliente'])) {
                    foreach ($dados['servicos_cliente'] as $linha) {
                        $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;
                        $clienteServico = $cliente->ServicosCliente()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $clienteServico->Anexos()->attach($arquivo->id);
                                    }
                                }

                            }
                        }
                    }
                }

                // Se tem Prospect
                if (isset($dados['servicos_prospect'])) {
                    foreach ($dados['servicos_prospect'] as $linha) {
                        $prospect = $cliente->ServicosProspect()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $prospect->Anexos()->attach($arquivo->id);
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($linha['logo'])) {
                    foreach ($linha['logo'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Logo()->attach($arquivo->id);
                            }
                        }
                    }
                }

                if (isset($linha['mascote'])) {
                    foreach ($linha['mascote'] as $index => $anexo) {
                        //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $cliente->Mascote()->attach($arquivo->id);
                            }
                        }
                    }
                }

                if (isset($dados['cliente_config'])) {
                    $dados['cliente_config']['envia_whatsapp'] = $dados['cliente_config']['envia_whatsapp'] == 'true' ? true : false;
                    $dadosClienteConfig = [
                        'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                        'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                        'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                        'cliente_id' => $cliente->id,
                        'supervisor_etiqueta_bloqueio' => $dados['cliente_config']['supervisor_etiqueta_bloqueio'],
                    ];
                    if (Schema::hasColumn('cliente_configs', 'treinamento_permitir_desmarcar_realizado')) {
                        $dadosClienteConfig['treinamento_permitir_desmarcar_realizado'] = filter_var($dados['cliente_config']['treinamento_permitir_desmarcar_realizado'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    }
                    if (Schema::hasColumn('cliente_configs', 'configuracoes')) {
                        $dadosClienteConfig['configuracoes'] = $this->normalizarConfiguracoes($dados['cliente_config']['configuracoes'] ?? []);
                    }
                    $dadosClienteConfig = array_merge($dadosClienteConfig, $this->dadosConfigAssinatura($dados['cliente_config']));
                    ClienteConfig::create($dadosClienteConfig);

                }

                $dados_papel = [
                    'empresa_id' => $cliente->id,
                    'nome' => $dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA ? $dados['razao_social'] . ' - MASTER' : $dados['nome'] . ' - MASTER',
                    'descricao' => 'MASTER',
                    'email' => $dados['email'],
                    'ativo' => true,
                    'master' => true
                ];

                $papel = Papel::create($dados_papel);

                Sistema::grupoClinicaExame($cliente->id);

                $habilidades = collect($request->listaDeHabilidades)->filter(function ($habilidade) {
                    if ($habilidade['acesso'] == 'true') {
                        return $habilidade;
                    }
                })->pluck('id');

                $papel->habilidades()->attach($habilidades);

                if (isset($dados['segmentos_treinamento_ids']) && is_array($dados['segmentos_treinamento_ids'])) {
                    $cliente->SegmentosTreinamento()->sync($dados['segmentos_treinamento_ids']);
                }

                $this->criaOuAtualizaEmpresaCliente($cliente->id);
                $this->criaOuAtualizaGrupoAdm($cliente->id);

                \Artisan::call('mybp:grupoClinicaExame ' . $cliente->id);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                $msg = "error STORE CLIENTES:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json([
                    'msg' => $msg,
                ], 400);
            }
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Cliente $clientes
     * @return \Illuminate\Http\Response
     */
    public function show(Cliente $clientes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Cliente $cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Cliente $cliente)
    {
        $cliente = $cliente->load('Telefones', 'AreasEtiquetas', 'ServicosCliente.Anexos', 'ServicosProspect.Anexos', 'Logo', 'Mascote', 'ClienteConfig', 'Papel.habilidades', 'SegmentosTreinamento:id,nome,slug');
        $cliente->areas_etiquetas_del = [];
        $cliente->ServicosCliente->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });
        $cliente->ServicosProspect->transform(function ($item) {
            $item->anexosDel = [];
            return $item;
        });

        $listaDeHabilidades = Habilidade::orderBy('nome', 'asc')->get()->map(function ($habilidade) {
            $habilidade->caracter = substr_count($habilidade->nome, '_');
            $habilidade->acesso = false;
            $habilidade->menu = strstr($habilidade->nome, "_", true) == false ? $habilidade->nome : strstr($habilidade->nome, "_", true);
            if ($habilidade->caracter >= 2) {
                $habilidade->submenu = strstr($habilidade->nome, "_", false) == false ? $habilidade->nome : substr(strstr($habilidade->nome, "_", false), 1);
            }
            return $habilidade;
        });

        $todosMenu = array_unique(array_column($listaDeHabilidades->toArray(), 'menu'));
        $dadosAssinatura = (new AssinaturaCotaService())->listarUsuariosEGrupos((int) $cliente->id);

        return response()->json([
            'cliente' => $cliente,
            'listaDeHabilidades' => $listaDeHabilidades,
            'listaModeloCih' => ClienteConfig::MODELO_CIH,
            'todosMenu' => $todosMenu,
            'usuariosAlertaAssinatura' => $dadosAssinatura['usuarios'],
            'gruposAlertaAssinatura' => $dadosAssinatura['grupos'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Cliente $cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        if ($dados['tipo'] == Cliente::TIPO_PESSOA_JURIDICA) {
            $validar = [
                'cnpj' => 'required|min:18|unique:clientes,cnpj,' . $cliente->id,
                'razao_social' => 'required|min:2',
            ];
        } else {
            $validar = [
                'cpf' => 'required|min:14|unique:clientes,cpf,' . $cliente->id,
                'nome' => 'required|min:2',
            ];
        }

        $validaComum = [
            'area_id' => 'required',
            'contato' => 'required',
            'aniversario' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
        ];

        array_merge($validar, $validaComum);

        $dadosValidados = \Validator::make($dados, $validar);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Cliente',
                'erros' => $dadosValidados->errors()
            ], 400);

        }

        try {
            DB::beginTransaction();

            $cliente->update($dados);


            if (isset($dados['telefonesDelete'])) {
                foreach ($dados['telefonesDelete'] as $telefonesDelete) {
                    $cliente->Telefones()->find($telefonesDelete)->delete();
                }
            }
            if (isset($dados['telefones'])) {
                foreach ($dados['telefones'] as $linha) {
                    if (isset($linha['id'])) {
                        $cliente->Telefones()->find($linha['id'])->update($linha);
                    } else {
                        $cliente->Telefones()->create($linha);
                    }
                }
            }

            if (isset($dados['logoDel'])) {
                foreach ($dados['logoDel'] as $id) {
                    $cliente->Logo()->find($id)->delete();
                }
            }


            if (isset($dados['logo'])) {
                foreach ($dados['logo'] as $index => $anexo) {
                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                    if ($anexo['chave'] == null) {
                        Arquivo::whereId($anexo['id'])->update([
                            'nome' => $anexo['nome'],
                        ]);
                    } else {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $cliente->Logo()->attach($arquivo->id);
                        }
                    }
                }
            }


            if (isset($dados['mascoteDel'])) {
                foreach ($dados['mascoteDel'] as $id) {
                    $cliente->Mascote()->find($id)->delete();
                }
            }


            if (isset($dados['mascote'])) {
                foreach ($dados['mascote'] as $index => $anexo) {
                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                    if ($anexo['chave'] == null) {
                        Arquivo::whereId($anexo['id'])->update([
                            'nome' => $anexo['nome'],
                        ]);
                    } else {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $cliente->Mascote()->attach($arquivo->id);
                        }
                    }
                }
            }


            if (isset($dados['servicos_clienteDelete'])) {
                foreach ($dados['servicos_clienteDelete'] as $id) {
                    $cliente->ServicosCliente()->find($id)->delete();
                }
            }

            if (isset($dados['servicos_prospectDelete'])) {
                foreach ($dados['servicos_prospectDelete'] as $id) {
                    $cliente->ServicosProspect()->find($id)->delete();
                }
            }

            // Se Tem Serviço Cliente
            if (isset($dados['servicos_cliente'])) {

                foreach ($dados['servicos_cliente'] as $linha) {
                    $linha['ativo'] = $linha['ativo'] == 'true' ? true : false;

                    if (isset($linha['anexosDel'])) {
                        foreach ($linha['anexosDel'] as $id_anexo) {
                            $arquivo = Arquivo::find($id_anexo);
                            $arquivo->excluir();
                        }
                    }

                    if (isset($linha['id'])) {
                        $cliente->ServicosCliente()->find($linha['id'])->update($linha);
                        foreach ($linha['anexos'] as $index => $anexo) {
                            //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                            if ($anexo['chave'] == null) {
                                Arquivo::whereId($anexo['id'])->update([
                                    'nome' => $anexo['nome'],
                                ]);
                            } else {
                                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                if ($arquivo) {
                                    $arquivo->temporario = false;
                                    $arquivo->chave = '';
                                    $arquivo->save();
                                    $cliente->ServicosCliente()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                }
                            }
                        }
                    } else {
                        $servico = $cliente->ServicosCliente()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();

                                        $servico->Anexos()->attach($arquivo->id);

                                    }
                                }

                            }
                        }
                    }

                }
            }


            if (isset($dados['servicos_prospect'])) {
                foreach ($dados['servicos_prospect'] as $linha) {

                    if (isset($linha['anexosDel'])) {
                        foreach ($linha['anexosDel'] as $id_anexo) {
                            $arquivo = Arquivo::find($id_anexo);
                            $arquivo->excluir();
                        }
                    }

                    if (isset($linha['id'])) {

                        $cliente->ServicosProspect()->find($linha['id'])->update($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $cliente->ServicosProspect()->find($linha['id'])->Anexos()->attach($arquivo->id);
                                    }
                                }
                            }
                        }
                    } else {
                        $servico = $cliente->ServicosProspect()->create($linha);
                        if (isset($linha['anexos'])) {
                            foreach ($linha['anexos'] as $index => $anexo) {
                                //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                                if ($anexo['chave'] == null) {
                                    Arquivo::whereId($anexo['id'])->update([
                                        'nome' => $anexo['nome'],
                                    ]);
                                } else {
                                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                    if ($arquivo) {
                                        $arquivo->temporario = false;
                                        $arquivo->chave = '';
                                        $arquivo->save();
                                        $servico->Anexos()->attach($arquivo->id);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (isset($dados['segmentos_treinamento_ids']) && is_array($dados['segmentos_treinamento_ids'])) {
                $cliente->SegmentosTreinamento()->sync($dados['segmentos_treinamento_ids']);
            }
            if (isset($dados['cliente_config']) && !empty($dados['cliente_config']['id'])) {
                $config = ClienteConfig::find($dados['cliente_config']['id']);
                $dadosClienteConfig = [
                    'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                    'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                    'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                    'modelo_cih' => $dados['cliente_config']['modelo_cih'],
                    'supervisor_etiqueta_bloqueio' => $dados['cliente_config']['supervisor_etiqueta_bloqueio'],
                    'schedule_avaliacao_experiencia' => $dados['cliente_config']['schedule_avaliacao_experiencia'] ?? true,
                    'schedule_treinamento_vencimento' => $dados['cliente_config']['schedule_treinamento_vencimento'] ?? true,
                ];
                if (Schema::hasColumn('cliente_configs', 'treinamento_permitir_desmarcar_realizado')) {
                    $dadosClienteConfig['treinamento_permitir_desmarcar_realizado'] = filter_var($dados['cliente_config']['treinamento_permitir_desmarcar_realizado'] ?? false, FILTER_VALIDATE_BOOLEAN);
                }
                if (Schema::hasColumn('cliente_configs', 'configuracoes')) {
                    $existentes = is_array($config->configuracoes) ? $config->configuracoes : [];
                    $dadosClienteConfig['configuracoes'] = $this->normalizarConfiguracoes(
                        array_merge($existentes, $dados['cliente_config']['configuracoes'] ?? [])
                    );
                }
                $dadosClienteConfig = array_merge($dadosClienteConfig, $this->dadosConfigAssinatura($dados['cliente_config']));
                $config->update($dadosClienteConfig);
            } else {
                $dadosClienteConfig = [
                    'verifica_mes_vencimento' => $dados['cliente_config']['verifica_mes_vencimento'],
                    'envia_whatsapp' => $dados['cliente_config']['envia_whatsapp'],
                    'vencimento_aso' => $dados['cliente_config']['vencimento_aso'],
                    'modelo_cih' => $dados['cliente_config']['modelo_cih'],
                    'supervisor_etiqueta_bloqueio' => $dados['cliente_config']['supervisor_etiqueta_bloqueio'],
                    'schedule_avaliacao_experiencia' => $dados['cliente_config']['schedule_avaliacao_experiencia'] ?? true,
                    'schedule_treinamento_vencimento' => $dados['cliente_config']['schedule_treinamento_vencimento'] ?? true,
                    'cliente_id' => $cliente->id
                ];
                if (Schema::hasColumn('cliente_configs', 'treinamento_permitir_desmarcar_realizado')) {
                    $dadosClienteConfig['treinamento_permitir_desmarcar_realizado'] = filter_var($dados['cliente_config']['treinamento_permitir_desmarcar_realizado'] ?? false, FILTER_VALIDATE_BOOLEAN);
                }
                if (Schema::hasColumn('cliente_configs', 'configuracoes')) {
                    $dadosClienteConfig['configuracoes'] = $this->normalizarConfiguracoes($dados['cliente_config']['configuracoes'] ?? []);
                }
                $dadosClienteConfig = array_merge($dadosClienteConfig, $this->dadosConfigAssinatura($dados['cliente_config']));
                ClienteConfig::create($dadosClienteConfig);
            }

            $verificaTrue = $dados['listaDeHabilidades'];
            $verificaFalse = $dados['listaDeHabilidades'];

            $habilidades = collect($verificaTrue)->filter(function ($habilidade) {
                if ($habilidade['acesso'] == true) {
                    return $habilidade;
                }
            })->pluck('id');

            $cliente->Papel->habilidades()->sync($habilidades);

            $todosPapeis = Papel::whereEmpresaId($cliente->id)->where('master', false)->with('habilidades')->get();

            $habilidadesRetiradas = collect($verificaFalse)->filter(function ($habilidade) {
                if ($habilidade['acesso'] == false) {
                    return $habilidade;
                }
            })->pluck('id');

            foreach ($todosPapeis as $papel) {
                $papel->habilidades()->detach($habilidadesRetiradas);
            }

            $this->criaOuAtualizaEmpresaCliente($cliente->id);
            $this->criaOuAtualizaGrupoAdm($cliente->id);

            (new Cliente())->Cnpjs($cliente->id);
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {

            DB::rollBack();
            $msg = "error STORE CLIENTES:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }

    }

    private function parseLimiteAssinaturasMensal($valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $limite = (int) $valor;
        if ($limite < 0) {
            return 0;
        }

        return $limite;
    }

    private function parseListaInteiros($lista): array
    {
        if (!is_array($lista)) {
            return [];
        }

        return collect($lista)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();
    }

    /**
     * Normaliza o array de configuracoes (JSON) para persistência.
     * Garante chaves conhecidas com tipo correto; preserva demais chaves.
     */
    private function normalizarConfiguracoes(array $config): array
    {
        $config['treinamento_fat_obrigatorio'] = filter_var($config['treinamento_fat_obrigatorio'] ?? false, FILTER_VALIDATE_BOOLEAN);
        return $config;
    }

    private function dadosConfigAssinatura(array $dadosConfig): array
    {
        $dados = [];

        if (Schema::hasColumn('cliente_configs', 'assinatura_digital_habilitada')) {
            $dados['assinatura_digital_habilitada'] = filter_var(($dadosConfig['assinatura_digital_habilitada'] ?? false), FILTER_VALIDATE_BOOLEAN);
        }

        if (Schema::hasColumn('cliente_configs', 'limite_assinaturas_mensal')) {
            $dados['limite_assinaturas_mensal'] = $this->parseLimiteAssinaturasMensal($dadosConfig['limite_assinaturas_mensal'] ?? null);
        }

        if (Schema::hasColumn('cliente_configs', 'assinatura_alerta_user_ids')) {
            $dados['assinatura_alerta_user_ids'] = $this->parseListaInteiros($dadosConfig['assinatura_alerta_user_ids'] ?? []);
        }

        if (Schema::hasColumn('cliente_configs', 'assinatura_alerta_grupo_ids')) {
            $dados['assinatura_alerta_grupo_ids'] = $this->parseListaInteiros($dadosConfig['assinatura_alerta_grupo_ids'] ?? []);
        }

        return $dados;
    }

    private function criaOuAtualizaGrupoAdm($empresa_id)
    {
        $empresa = DB::table('clientes')->find($empresa_id, ['id', 'nome_fantasia']);

        $nomeGrupo = 'Administrador - ' . $empresa->nome_fantasia;

        $queryGrupoAdm = DB::table('papeis')
            ->where('empresa_id', $empresa_id)
            ->where('nome', $nomeGrupo)
            ->first();

        $queryHabilidadesEmpresa = DB::table('papeis')
            ->where('empresa_id', $empresa_id)
            ->where('master', true)
            ->join('papeis_habilidades', 'papeis.id', '=', 'papeis_habilidades.papel_id')
            ->pluck('habilidade_id')
            ->toArray();

        if (is_null($queryGrupoAdm)) {
            $idGP = DB::table('papeis')->insertGetId([
                'nome' => $nomeGrupo,
                'descricao' => 'Grupo para administradores',
                'empresa_id' => $empresa_id,
                'email' => '',
                'ativo' => true
            ]);
        } else {
            $idGP = $queryGrupoAdm->id;
        }

        DB::table('papeis_habilidades')
            ->where('papel_id', $idGP)
            ->delete();

        DB::table('papeis_habilidades')->insert(array_map(function ($habilidade) use ($idGP) {
            return [
                'papel_id' => $idGP,
                'habilidade_id' => $habilidade
            ];
        }, $queryHabilidadesEmpresa));
    }


    private function criaOuAtualizaEmpresaCliente($empresaId)
    {
        $query = DB::table('empresa_clientes')->where('empresa_id', $empresaId)->where('cliente_id', $empresaId);
        if ($query->count() == 0) {
            $query->insert(['empresa_id' => $empresaId, 'cliente_id' => $empresaId]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Cliente $clientes
     * @return \Illuminate\Http\Response
     */

    public function destroy(Cliente $cliente)
    {
        $this->authorize('administracao_clientes_delete');
        $cliente->delete();
    }

    public function atualizar(Request $request)
    {
        //        $resultado = User::with('Area:id,label', 'Telefones:id,cliente_id,numero');
        $resultado = Cliente::with('Area:id,label', 'Telefones:id,cliente_id,numero');

        $listaDeHabilidades = Habilidade::orderBy('nome', 'asc')->get()->map(function ($habilidade) {
            $habilidade->acesso = false;
            return $habilidade;
        });

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');

            $resultado->where('razao_social', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome_fantasia', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cnpj', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }

        if ($request->filled('campoTipo')) {
            $resultado->whereTipoCliente($request->campoTipo);
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        $servicos = Servico::whereAtivo(true)->orderBy('titulo')->get();
        $areas = Area::whereAtivo(true)->get();
        $resultado = $resultado->orderByDesc('ativo')->orderBy('razao_social')->orderBy('nome')->orderBy('tipo_cliente')->paginate(50);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'servicos' => $servicos,
                'areas' => $areas,
                'listaDeHabilidades' => $listaDeHabilidades
            ]
        ]);
    }

    public function ativaDesativa(Cliente $cliente)
    {
        $cliente->ativo = !$cliente->ativo;
        $cliente->save();
        $cliente->refresh();

        $users = User::whereEmpresaId($cliente->id)->get();
        foreach ($users as $user) {
            $user['ativo'] = $cliente->ativo;
            $user->save();
            $user->refresh();
        }

        return response()->json(['ativo' => $cliente->ativo], 201);
    }

    public function buscaCNPJ(Request $request)
    {
        return Sistema::verificaCnpjCadastrado(Cliente::class, $request->cnpj);
    }

    public function buscaCPF(Request $request)
    {
        return Sistema::verificaCpfCadastrado(Cliente::class, $request->cpf);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, [
            Arquivo::MIME_JPEG,
            Arquivo::MIME_JPG,
            Arquivo::MIME_PNG,
            Arquivo::MIME_PDF,
            Arquivo::MIME_DOC,
            Arquivo::MIME_DOCX,
            Arquivo::MIME_PPS,
            Arquivo::MIME_PPSX,
            Arquivo::MIME_PPT,
            Arquivo::MIME_PPTX,
            Arquivo::MIME_XLS,
            Arquivo::MIME_XLSX,
            Arquivo::MIME_ZIP,
            Arquivo::MIME_RAR,
        ], Arquivo::DISCO_CLIENTE);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::download(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    // Logo-------------------------------------------------
    public function uploadLogo(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_CLIENTE);
    }

    public function logoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function logoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    //foto
    public function logoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    // Mascote-------------------------------------------------
    public function uploadMascote(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_CLIENTE);
    }

    public function mascoteShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function mascoteDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    public function mascoteDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLIENTE, $arquivo);
    }

    //PDF
    public function getFichaPdf(Cliente $cliente)
    {
        $dados = $cliente;
        $pdf = PDF::loadView('pdf.cliente.pdf', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("ficha_cliente_" . STR::slug($dados->tipo == 'Pessoa Jurídica' ? $dados->razao_social : $dados->nome) . ".pdf");
    }

    public function export()
    {
        $dataUnica = new DataHora(null);
        return Excel::download(new ClientesExport, "cliente_{$dataUnica->nomeUnico()}.xlsx");
    }

    //Verifica Servicos Clientes Vencidos
    public function clientesProximoVencimento()
    {
        $hoje = new DataHora();
        $trintaDias = new DataHora($hoje->addDia(30));

        $clientes = Cliente::whereAtivo(true)->whereHas('ServicosCliente', function ($query) use ($trintaDias) {
            $query->whereAtivo(true)->whereAtivo(true)->where('data_encerramento', '<=', $trintaDias->dataInsert());
        })->with(['ServicosCliente' => function ($query) use ($trintaDias) {
            $query->with('Servico')->whereAtivo(true)
                ->where('data_encerramento', '<=', $trintaDias->dataInsert());
        }]);

        if ($clientes->count() >= 1) {
            $dados = ['dados' => $clientes->get(['id', 'razao_social', 'nome_fantasia', 'nome'])];
            try {
                Mail::send('email.clientes.vencendo', $dados, function ($m) use ($dados) {
                    $m->from('naoresponda@mybp.com.br', 'SGIBPSE - E-mail Automatico');
                    $m->subject("Serviços de Clientes Vencidos ou próximo ao vencimento");
                    $m->to('adm.sede@bpse.com.br');
                });
                \Log::info("E-mail enviado com sucesso para clientes vencidos total de {$clientes->count()}");
                return response()->json(['enviado' => true], 200);
            } catch (\Exception $e) {
                \Log::debug("Error ao enviar e-maill de Vencimento de Servicos: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
                return response()->json(['enviado' => false], 400);
            }
        }

    }
}
