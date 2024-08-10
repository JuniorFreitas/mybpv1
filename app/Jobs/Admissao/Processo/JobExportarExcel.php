<?php

namespace App\Jobs\Admissao\Processo;

use App\Http\Controllers\AdmissaoController;
use App\Models\ClienteFilial;
use App\Models\FeedbackCurriculo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobExportarExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public $dados;
    public $local;
    public $usuario;
    public $usuario_id;
    public $nome_arquivo;
    public $timeout = 0;


    /**
     * @param $usuario_id
     * @param $local
     * @param $dados
     * @param $nome_arquivo
     */
    public function __construct($usuario_id, $local, $dados, $nome_arquivo)
    {
        $this->local = $local;
        $this->usuario_id = $usuario_id;
        $this->nome_arquivo = $nome_arquivo;

        if (isset($dados->selecionados) && count($dados->selecionados) > 0) {
            $filtrados = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'telefone_id', 'vagas_abertas_id', 'vaga_projeto_id', 'empresa_id'])
                ->whereHas('ResultadoIntegrado')
                ->with([
                    'Admissao:id,feedback_id,area_etiqueta_id,centro_custo_id,centro_custo_filial_id,filial,funcao,cargo,salario,documento,documento_portaria,tipo_admissao,treinamento,tipo_treinamento,data_treinamento,numero_cracha,pis,status_carteira_treinamento,status,data_admissao',
                    'Admissao.AreaEtiqueta:id,label,empresa_id,gestor_id,centro_custo_id',
                    'Admissao.CentroCusto:id,label',
                    'Admissao.CentroCustoFilial',
                    'Admissao.CentroCustoFilial.Filial:id,dados',
                    'Admissao.DadosAdmissoes',
                    'Admissao.UltimoAso',
                    'Admissao.QuemAdmitiu:id,nome',
                    'Admissao.QuemAlterou:id,nome',
                    'BancoConta',
                    'ResultadoIntegrado:id,feedback_id,documentos_entregue,documentos_entregue,encaminhado_exame,encaminhado_exame,encaminhado_treinamento,encaminhado_treinamento,responsavel_envio',
                    'Curriculo:id,nome,estado_civil,naturalidade,nacionalidade,carteira_trabalho,cnh,cnh_vencimento,sexo,cpf,rg,rg_data_emissao,orgao_expeditor,logradouro,end_numero,complemento,bairro,municipio,uf,cep,filiacao_pai,filiacao_mae,pcd,nascimento,email,disponibilidade_sabado,disponibilidade_domingo',
                    'Curriculo.FotoTres:id',
                    'parecerRh:id,feedback_id,destro,cnh_tipo,calca,bota,camisa_meia,camisa_protecao,ex_funcionario,turnos_seis_por_dois,indicado_por',
                    'parecerTecnica:id,feedback_id,indicado_area,experiencia_cargas_rigger,opera_plat_movel,opera_plat_ponte',
                    'parecerRota:id,feedback_id,tem_rota,qual,bairro_rota,ponto_referencia_rota,pega_onibus,pega_onibus_qual_ponto,bairro_residencia,ponto_referencia_residencia',
                    'parecerTeste:id,feedback_id,qual_teste,nota_teste',
                    'parecerTecnica:id,feedback_id,experiencia_cargas_rigger,opera_plat_movel,opera_plat_ponte',
                    'VagaAberta:id,empresa_id,vaga_id,titulo,municipio_id,ativo',
                    'VagaAberta.VagaSelecionada:id,nome,empresa_id,ativo',
                    'VagaAberta.Municipio:id,nome',
                    'Empresa:id,razao_social,cnpj,nome,cpf,area_id',
                    'Empresa.Area',
                    'VagaAberta.Projetos.Projeto'
                ])->whereIn('id', $dados->selecionados)->get();
        } else {
            $filtrados = (new AdmissaoController())->filtro($dados)->get();
        }

        $this->dados = $filtrados;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $filtrados = $this->dados;
        $Usuario = auth()->loginUsingId($this->usuario_id);
//        $clienteFilial = new ClienteFilial();
//        $clienteTemFilial = $clienteFilial->temFilial($Usuario->empresa_id);

        $head = [[
            "Nome",
            "Estado Civil",
            "Sexo",
            "CPF",
            "Pai",
            "Mãe",
            "PCD",
            "Destro/Canhoto",
            "CNH",
            "Nascimento",
            "Idade",
            "Formação",
            "Calça",
            "Bota",
            "Camisa Meia",
            "Camisa Proteção",
            "Empresa",
            "Vaga",
            "Ex Funcionário",
            "Contato",
            "E-mail",
            "Disponibilidade para turnos 6X2",
            "Indicado por quem",
            "Indicado para qual área",
            "Endereço",
            "Tem Rota que atende",
            "Qual",
            "Bairro Rota",
            "Ponto de referência Rota",
            "Informado sobre ponto de referência",
            "Qual",
            "Bairro Residência",
            "Ponto de referência Residência",
            "Teste aplicado",
            "Resultado Teste Prático",
            "Rigger",
            "Plataforma Movél",
            "Ponte Rolante",
            "Encaminhado para Documentos",
            "Data Encaminhado para Documentos",
            "Encaminhado para Exame",
            "Data Encaminhado para Exame",
            "Encaminhado para treinamento",
            "Data Encaminhado para Treinamento",
            "Área",
            "Centro de Custo",
            "CNPJ Filial",
            "Centro de custo filial",
            "Função",
            "Cargo",
            "Salário R$",
            "Documento",
            "Documento Portaria",
            "Tipo de admissão",
            "Treinamento",
            "Tipo de Treinamento",
            "Data Treinamento",
            "Número Crachá",
            "Data do ASO",
            "PIS",
            "CTPS",
            "CTPS Série",
            "CTPS Data Emissão",
            "CTPS UF",
            "Título de Eleitor",
            "Título de Eleitor Sessão",
            "Título de Eleitor Zona",
            "Certificado de Reservista",
            "Certificado de Reservista Categoria",
            "Dependentes",
            "Conta PIX",
            "Tipo de Chave PIX",
            "Chave PIX",
            "Banco",
            "Agência",
            "Conta",
            "Status Carteira de Treinamento e Etiqueta",
            "Status",
            "Data da Admissão",
            "Foto",
            "Quem Admitiu",
            "Quem Alterou",
        ]];

        $rows = [];

        foreach ($filtrados as $row) {
            $dependentes = "";

            if (count($row->Curriculo->Dependentes) > 0) {
                $row->Curriculo->Dependentes->each(function ($item) use (&$dependentes) {
                    $cpf = $item->cpf ?: "Não informado";
                    $nascimento = $item->nascimento ?: 'Não informado';
                    $dependentes .= "Tipo: ";
                    $dependentes .= $item->tipo == 'outro' ? $item->outro_tipo : \App\Models\UsuarioDependente::TIPOS_DEPENDENTES[$item->tipo];
                    $dependentes .= "\nNome: " . $item->nome;
                    $dependentes .= "\nCPF: " . $cpf;
                    $dependentes .= "\nData de Nascimento: " . $nascimento;
                    $dependentes .= "\n\n";
                });
            }

            $rows[] = array(
                $row->Curriculo->nome,
                $row->Curriculo->estado_civil ?? 'NÃO INFORMADO',
                $row->Curriculo->sexo ?? 'NÃO INFORMADO',
                $row->Curriculo->cpf,
                $row->Curriculo->filiacao_pai ?? "",
                $row->Curriculo->filiacao_mae,
                $row->Curriculo->pcd ? 'SIM' : 'NÃO',
                $row->parecerRh->destro ?? 'NÃO INFORMADO',
                $row->parecerRh->cnh_tipo ?? 'NÃO INFORMADO',
                $row->Curriculo->nascimento,
                $row->Curriculo->idade,
                $row->Curriculo->Formacao->id >= 8 ? ($row->Curriculo->Formacao->tipo ? $row->Curriculo->Formacao->tipo . ' - ' . $row->Curriculo->formacao_curso : 'NÃO INFORMADO') : 'NÃO INFORMADO',
                $row->parecerRh->calca ?? 'NÃO INFORMADO',
                $row->parecerRh->bota ?? 'NÃO INFORMADO',
                $row->parecerRh->camisa_meia ?? 'NÃO INFORMADO',
                $row->parecerRh->camisa_protecao ?? 'NÃO INFORMADO',
                $row->empresa->cnpj ? $row->empresa->razao_social : $row->empresa->nome,
                $row->VagaAberta->VagaSelecionada->nome . ' - ' . $row->VagaAberta->Municipio->uf,
                $row->parecerRh->ex_funcionario ? 'SIM' : 'NÃO',
                $row->TelPrincipal ? $row->TelPrincipal->numero : 'NÃO INFORMADO',
                $row->Curriculo->email,
                $row->parecerRh ? $row->parecerRh->turnos_seis_por_dois ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->parecerRh->indicado_por ?? "",
                $row->parecerTecnica->indicado_area ?? "",
                $row->Curriculo->endereco_completo,
                $row->parecerRota ? $row->parecerRota->tem_rota ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->qual ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->bairro_rota ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->ponto_referencia_rota ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->pega_onibus ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->pega_onibus_qual_ponto ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->bairro_residencia ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerRota ? $row->parecerRota->ponto_referencia_residencia ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
                $row->parecerTeste->qual_teste ?? 'NÃO INFORMADO',
                $row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando',
                $row->parecerTecnica ? $row->parecerTecnica->experiencia_cargas_rigger ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->parecerTecnica ? $row->parecerTecnica->opera_plat_movel ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->parecerTecnica ? $row->parecerTecnica->opera_plat_ponte ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
                $row->ResultadoIntegrado->documentos_entregue ? 'SIM' : 'NÃO',
                $row->ResultadoIntegrado->documentos_entregue ? (new \MasterTag\DataHora($row->ResultadoIntegrado->documentos_entregue_data))->dataCompleta() : '',
                $row->ResultadoIntegrado->encaminhado_exame ? 'SIM' : 'NÃO',
                $row->ResultadoIntegrado->encaminhado_exame ? (new \MasterTag\DataHora($row->ResultadoIntegrado->encaminhado_exame_data))->dataCompleta() : '',
                $row->ResultadoIntegrado->encaminhado_treinamento ? 'SIM' : 'NÃO',
                $row->ResultadoIntegrado->encaminhado_treinamento ? (new \MasterTag\DataHora($row->ResultadoIntegrado->encaminhado_treinamento_data))->dataCompleta() : '',
                $row->Admissao->AreaEtiqueta->label ?? "NÃO INFORMADO",
                $row->Admissao->CentroCusto->label ?? "NÃO INFORMADO",
                $row->Admissao->filial ? 'SIM' : 'NÃO',
                $row->Admissao->CentroCustoFilial->Filial->dados->razao_social ?? "",
                $row->Admissao->funcao ?? "NÃO INFORMADO",
                $row->Admissao->cargo ?? "NÃO INFORMADO",
                $row->Admissao->salario ?? "NÃO INFORMADO",
                $row->Admissao->documento ?? "NÃO INFORMADO",
                $row->Admissao->documento_portaria ?? "NÃO INFORMADO",
                $row->Admissao->tipo_admissao ?? "NÃO INFORMADO",
                $row->Admissao->treinamento ?? "NÃO INFORMADO",
                $row->Admissao->tipo_treinamento ?? "NÃO INFORMADO",
                $row->Admissao->data_treinamento ?? "NÃO INFORMADO",
                $row->Admissao->numero_cracha ?? "NÃO INFORMADO",
                $row->Admissao->UltimoAso->data_realizacao ?? "NÃO INFORMADO",
                $row->Admissao->pis ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->ctps_numero ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->ctps_serie ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->ctps_data_emissao ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->ctps_uf ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->titulo_eleitor_numero ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->titulo_eleitor_sessao ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->titulo_eleitor_zona ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->cert_reservista_num ?? "NÃO INFORMADO",
                $row->Admissao->DadosAdmissoes->cert_reservista_categoria ?? "NÃO INFORMADO",
                $dependentes,
                $row->BancoConta ? $row->BancoConta->pix ? 'SIM' : 'NÃO' : "NÃO INFORMADO",
                $row->BancoConta->tipochavepix ?? "",
                $row->BancoConta->chavepix ?? "",
                $row->BancoConta->banco ?? "NÃO INFORMADO",
                $row->BancoConta->agencia ?? "NÃO INFORMADO",
                $row->BancoConta->conta ?? "NÃO INFORMADO",
                $row->Admissao->status_carteira_treinamento ?? "NÃO INFORMADO",
                $row->Admissao->status ?? "NÃO INFORMADO",
                $row->Admissao->data_admissao ?? "NÃO INFORMADO",
                $row->Curriculo ? $row->Curriculo->FotoTres ? $row->Curriculo->FotoTres->count() > 0 ? 'SIM' : 'NÃO' : 'NÃO' : 'NÃO',
                $row->Admissao->QuemAdmitiu->nome ?? "",
                $row->Admissao->QuemAlterou->nome ?? "",
            );
        }

        $array = [
            'usuario' => $Usuario,
            'local' => $this->local,
            'dados' => array_merge($head, $rows),
            'arquivo' => $this->nome_arquivo
        ];

        \Artisan::call("mybp:exportarExcel", $array);
    }
}
