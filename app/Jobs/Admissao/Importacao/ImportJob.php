<?php

namespace App\Jobs\Admissao\Importacao;

use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Models\VagasAbertas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, InteractsWithAuthentication;

    public $tries = 1;
    private $dados;
    private $empresa_id;
    private $user;
    public $timeout = 0;

    public function __construct($user, $dados, $empresa_id)
    {
        $this->dados = $dados;
        $this->empresa_id = $empresa_id;
        $this->user = $user;
    }

    //Comentar esse metodo se for usar com Job
//    public function __invoke()
//    {
//        $this->handle();
//    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $dados = $this->dados;
        $empresa_id = (int)$this->empresa_id;

        Auth::loginUsingId($this->user->id);
        /*
        $dadosValidados = \Validator::make($dados, [
            '*.curriculo.cpf' => ['required', 'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresa_id),
                new VerificaCpfEmpresaRules($empresa_id, true)
            ],
            '*.curriculo.nome' => 'required|max:255',
            '*.curriculo.email' => 'email:rfc,dns',
            '*.curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.rg' => 'nullable|max:200',
            '*.curriculo.rg_data_emissao' => 'nullable|max:10|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.filiacao_pai' => 'max:255',
            '*.curriculo.filiacao_mae' => 'required|max:255',
            '*.curriculo.pcd' => 'required|boolean',
            '*.curriculo.cid' => 'required_if:*.curriculo.pcd,true',
            '*.curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            '*.curriculo.endereco.cep' => 'required|min:9',
            '*.curriculo.endereco.logradouro' => 'required|max:255',
            '*.curriculo.endereco.numero' => 'nullable|max:10',
            '*.curriculo.endereco.complemento' => 'nullable|max:255',
            '*.curriculo.endereco.bairro' => 'required|max:255',
            '*.curriculo.endereco.municipio' => 'required|max:255',
            '*.curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            '*.curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
            '*.curriculo.telefone.numero' => 'required|max:16',
            '*.admissao.area_etiqueta_id' => ['required', new AreaEmpresaRules($empresa_id)],
            '*.admissao.data_entrega_area' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.salario' => 'max:100',
            '*.admissao.pis' => 'nullable|max:200',
            '*.admissao.ctps_numero' => 'nullable|max:200',
            '*.admissao.ctps_serie' => 'nullable|max:200',
            '*.admissao.ctps_data_emissao' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.titulo_eleitor_numero' => 'nullable|max:200',
            '*.admissao.titulo_eleitor_sessao' => 'nullable|max:200',
            '*.admissao.titulo_eleitor_zona' => 'nullable|max:200',
            '*.admissao.data_aso' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.data_admissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
            '*.admissao.admissao_encerramento' => [
                function ($attribute, $value, $fail) use ($dados) {
                    $i = (int)explode('.', $attribute)[0];

                    if (in_array($dados[$i]['admissao']['tipo_admissao'], [Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO, Admissao::TIPO_ADMISSAO_TEMPORARIO])
                        && is_null($value)
                        && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value) == 0
                    ) {
                        $fail("O {$attribute} deve ser preenchido com formato da data dd/mm/aaaa");
                    }
                }],
            '*.admissao.prazo_experiencia' => [function ($attribute, $value, $fail) use ($dados) {
                $i = (int)explode('.', $attribute)[0];
                if ($dados[$i]['admissao']['tipo_admissao'] == Admissao::TIPO_ADMISSAO_FIXO && !in_array($value, Admissao::TODOS_PRAZOS)) {
                    $fail("A linha {$attribute} só pode ser um dos tipos de prazo: " . implode(',', Admissao::TODOS_PRAZOS));
                }
            }],
            '*.admissao.banco.nome' => 'nullable|max:200',
            '*.admissao.banco.agencia' => 'nullable|max:200',
            '*.admissao.banco.conta' => 'nullable|max:200',
            '*.admissao.banco.pix' => 'boolean',
            '*.admissao.banco.pix_tipo_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
            '*.admissao.banco.pix_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao fazer importação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        */

        try {
//            $teste = collect($dados)->split(1000);
//            \Log::info($teste[4]->toArray());
            DB::beginTransaction();
            foreach ($dados as $item) {

                $usuario = User::where('empresa_id', $empresa_id)->whereHas('Curriculo', function ($q) use ($item) {
                    $q->where('cpf', $item['curriculo']['cpf']);
                });

                $dadosUser = [
                    'nome' => $item['curriculo']['nome'],
                    'login' => $item['curriculo']['email'],
                    'password' => Sistema::SenhaCpf($item['curriculo']['cpf']),
                    'tipo' => User::FUNCIONARIO,
                    'ativo' => true,
                    'temp' => false,
                    'termos' => false,
                    'empresa_id' => $empresa_id
                ];


                if ($usuario->count() == 0) {
                    \Log::info("Iniciando criação do Colaborador - " .$item['curriculo']['nome']);
                    $usuario = User::create($dadosUser);
                } else {
                    \Log::info("Iniciando atualizaçaão do Colaborador - " .$item['curriculo']['nome']);
                    $usuario = $usuario->first();
                    $usuario->update($dadosUser);
                }

                //Cria ou atualiza os dados bancarios
                $dadosConta = [
                    'banco' => $item['admissao']['banco']['nome'],
                    'agencia' => $item['admissao']['banco']['agencia'],
                    'conta' => $item['admissao']['banco']['conta'],
                    'pix' => $item['admissao']['banco']['pix'],
                    'tipochavepix' => $item['admissao']['banco']['pix_tipo_chave'],
                    'chavepix' => $item['admissao']['banco']['pix_chave'],
                ];

                $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);

                //Cria ou atualiza o Curriculo
                $dadosCurriculo = [
                    'id' => $usuario->id,
                    'cpf' => $item['curriculo']['cpf'],
                    'nome' => $item['curriculo']['nome'],
                    'estado_civil' => $item['curriculo']['estado_civil'],
                    'cnh' => $item['curriculo']['cnh'],
                    'cnh_vencimento' => $item['curriculo']['cnh_vencimento'],
                    'email' => $item['curriculo']['email'],
                    'nascimento' => $item['curriculo']['nascimento'],
                    'naturalidade' => $item['curriculo']['naturalidade'],
                    'logradouro' => $item['curriculo']['endereco']['logradouro'],
                    'end_numero' => $item['curriculo']['endereco']['numero'],
                    'complemento' => $item['curriculo']['endereco']['complemento'],
                    'bairro' => $item['curriculo']['endereco']['bairro'],
                    'municipio' => $item['curriculo']['endereco']['municipio'],
                    'uf' => $item['curriculo']['endereco']['uf'],
                    'cep' => $item['curriculo']['endereco']['cep'],
                    'uf_vaga' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->uf,
                    'municipio_id' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->id,
                    'rg' => $item['curriculo']['rg'],
                    'rg_data_emissao' => $item['curriculo']['rg_data_emissao'],
                    'filiacao_pai' => $item['curriculo']['filiacao_pai'],
                    'filiacao_mae' => $item['curriculo']['filiacao_mae'],
                    'sexo' => $item['curriculo']['sexo'],
                    'pcd' => $item['curriculo']['pcd'],
                    'cid' => $item['curriculo']['cid'],
                    'vaga_pretendida' => $item['curriculo']['vaga_pretendida']
                ];

                $curriculo = Curriculo::find($usuario->id);

                if (is_null($curriculo)) {
                    $curriculo = Curriculo::create($dadosCurriculo);
                } else {
                    $curriculo->update($dadosCurriculo);
                }

                //Cria ou atualiza o Telefone
                $dadosTel = [
                    'curriculo_id' => $curriculo->id,
                    'tipo' => $item['curriculo']['telefone']['whatsapp'],
                    'pais' => "55",
                    'numero' => $item['curriculo']['telefone']['numero'],
                    'principal' => true,
                ];

                $telefone_id = $curriculo->Telefones()->updateOrCreate($dadosTel)->id;

                //Cria ou atualiza o Feedback
                $curriculo->Feedback()->updateOrCreate([
                    'curriculo_id' => $curriculo->id,
                    'selecionado' => 'sim',
                    'vaga_id' => $item['curriculo']['vaga_pretendida'],
                    'cliente_id' => $empresa_id,
                    'empresa_id' => $empresa_id,
                    'interesse' => true,
                    'contato_realizado' => true,
                    'telefone_id' => $telefone_id,
                    'vagas_abertas_id' => $item['curriculo']['vaga_pretendida']
                ]);

                //Criações de entrevistas
                $curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
                $curriculo->Feedback->parecerRota()->updateOrCreate([]);
                $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
                $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
                $curriculo->Feedback->individualRh()->updateOrCreate([]);
                $curriculo->Feedback->gestorRh()->updateOrCreate([]);
                $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);

                //Criações de resultado integrado
                $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate([
                    'responsavel_envio' => 'importacao',
                    'documentos_entregue' => false,
                    'encaminhado_exame' => (bool)$item['admissao']['encaminhado_exame'],
                    'encaminhado_exame_data' => $item['admissao']['encaminhado_exame_data'],
                    'encaminhado_treinamento' => (bool)$item['admissao']['encaminhado_treinamento'],
                    'encaminhado_treinamento_data' => $item['admissao']['encaminhado_treinamento_data'],
                ]);

                //Criações de admissao
                $curriculo->Feedback->Admissao()->updateOrCreate([
                    'centro_custo_id' => $item['admissao']['centro_custo_id'],
                    'area_etiqueta_id' => $item['admissao']['area_etiqueta_id'],
                    'data_entrega_area' => $item['admissao']['data_entrega_area'],
                    'data_admissao' => $item['admissao']['data_admissao'],
                    'cargo' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
                    'funcao' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
                    'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                    'salario' => $item['admissao']['salario'],
                    'pis' => $item['admissao']['pis'],
                    'tipo_admissao' => $item['admissao']['tipo_admissao'],
                    'prazo_experiencia' => $item['admissao']['prazo_experiencia'],
                    'data_encerramento' => $item['admissao']['admissao_encerramento'],
                    'usuario_id' => auth()->user()->id,
                ]);

                Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, $item['admissao']['tipo_admissao'], $item['admissao']['prazo_experiencia'], $item['admissao']['data_admissao'], $item['admissao']['admissao_encerramento']);
                AdmissaoAso::criarAtualizar($curriculo->Feedback->Admissao->id, $empresa_id, $item['admissao']['data_aso']);

                //DadosAdmissoes
                $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
                    'ctps_numero' => $item['admissao']['ctps_numero'],
                    'ctps_serie' => $item['admissao']['ctps_serie'],
                    'ctps_data_emissao' => $item['admissao']['ctps_data_emissao'],
                    'titulo_eleitor_numero' => $item['admissao']['titulo_eleitor_numero'],
                    'titulo_eleitor_sessao' => $item['admissao']['titulo_eleitor_sessao'],
                    'titulo_eleitor_zona' => $item['admissao']['titulo_eleitor_zona'],
                ]);
                \Log::info("Fim Colaborador - ".$curriculo->id.' - '.$curriculo->nome);
            }
            DB::commit();
            \Log::info('Importação realizada com sucesso');
            return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage() . ' - ' . $e->getLine());
            return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
        }
    }
}
