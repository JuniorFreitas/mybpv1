<?php

namespace App\Jobs\Admissao\Processo;

use App\Http\Controllers\AdmissaoController;
use App\Models\FeedbackCurriculo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobExportarExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public mixed $dados;
    public string $local;
    public int $usuario_id;
    public string $nome_arquivo;
    public int $timeout = 0;

    public function __construct(int $usuario_id, string $local, mixed $dados, string $nome_arquivo)
    {
        $this->usuario_id = $usuario_id;
        $this->local = $local;
        $this->nome_arquivo = $nome_arquivo;
        $this->dados = $this->filtrarDados($dados);
    }

    private function filtrarDados($dados)
    {
        if (!empty($dados->selecionados)) {
            return $this->filtrarSelecionados($dados->selecionados);
        }

        return (new AdmissaoController())->filtro($dados)->get();
    }

    private function filtrarSelecionados(array $selecionados)
    {
        return FeedbackCurriculo::select([
            'id', 'curriculo_id', 'vaga_id', 'telefone_id', 'vagas_abertas_id', 'vaga_projeto_id', 'empresa_id'
        ])
            ->whereHas('ResultadoIntegrado')
            ->with($this->getRelacionamentos())
            ->whereIn('id', $selecionados)
            ->get();
    }

    private function getRelacionamentos(): array
    {
        return [
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
        ];
    }

    public function handle(): void
    {
        $filtrados = $this->dados;
        $usuario = auth()->loginUsingId($this->usuario_id);

        $dadosParaExportacao = $this->prepararDadosParaExportacao($filtrados);

        $array = [
            'usuario' => $usuario,
            'local' => $this->local,
            'dados' => array_merge($this->obterCabecalho(), $dadosParaExportacao),
            'arquivo' => $this->nome_arquivo
        ];

        \Artisan::call("mybp:exportarExcel", $array);
    }

    private function prepararDadosParaExportacao($filtrados): array
    {
        $rows = [];

        foreach ($filtrados as $row) {
            $rows[] = $this->processarLinha($row);
        }

        return $rows;
    }

    private function processarLinha($row): array
    {
        $dependentes = $this->formatarDependentes($row->Curriculo->Dependentes);

        return [
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
            $this->formatarEscolaridade($row),
            $row->parecerRh->calca ?? 'NÃO INFORMADO',
            $row->parecerRh->bota ?? 'NÃO INFORMADO',
            $row->parecerRh->camisa_meia ?? 'NÃO INFORMADO',
            $row->parecerRh->camisa_protecao ?? 'NÃO INFORMADO',
            $row->empresa->cnpj ? $row->empresa->razao_social : $row->empresa->nome,
            $row->VagaAberta->VagaSelecionada->nome . ' - ' . $row->VagaAberta->Municipio->uf,
            $row->parecerRh->ex_funcionario ? 'SIM' : 'NÃO',
            $row->TelPrincipal->numero ?? 'NÃO INFORMADO',
            $row->Curriculo->email,
            $row->parecerRh->turnos_seis_por_dois ? 'SIM' : 'NÃO',
            $row->parecerRh->indicado_por ?? "",
            $row->parecerTecnica->indicado_area ?? "",
            $row->Curriculo->endereco_completo,
            $row->parecerRota->tem_rota ? 'SIM' : 'NÃO',
            $row->parecerRota->qual ?? 'NÃO INFORMADO',
            $row->parecerRota->bairro_rota ?? 'NÃO INFORMADO',
            $row->parecerRota->ponto_referencia_rota ?? 'NÃO INFORMADO',
            $row->parecerRota->pega_onibus ? 'SIM' : 'NÃO',
            $row->parecerRota->pega_onibus_qual_ponto ?? 'NÃO INFORMADO',
            $row->parecerRota->bairro_residencia ?? 'NÃO INFORMADO',
            $row->parecerRota->ponto_referencia_residencia ?? 'NÃO INFORMADO',
            $row->parecerTeste->qual_teste ?? 'NÃO INFORMADO',
            $row->parecerTeste->nota_teste ?: 'Aguardando',
            $row->parecerTecnica->experiencia_cargas_rigger ? 'SIM' : 'NÃO',
            $row->parecerTecnica->opera_plat_movel ? 'SIM' : 'NÃO',
            $row->parecerTecnica->opera_plat_ponte ? 'SIM' : 'NÃO',
            $dependentes,
            $row->parecerRh->qual_rota ? 'SIM' : 'NÃO'
        ];
    }

    private function formatarEscolaridade($row): string
    {
        return $row->Curriculo->Escolaridade->nome ?? 'NÃO INFORMADO';
    }

    private function formatarDependentes($dependentes): string
    {
        return $dependentes->count() . ' dependente(s)';
    }

    private function obterCabecalho(): array
    {
        return [
            'Nome', 'Estado Civil', 'Sexo', 'CPF', 'Nome do Pai', 'Nome da Mãe', 'PCD', 'Destro', 'CNH', 'Data de Nascimento',
            'Idade', 'Escolaridade', 'Calça', 'Bota', 'Camisa/Meia', 'Camisa Proteção', 'Empresa', 'Vaga - UF', 'Ex-Funcionário',
            'Telefone Principal', 'E-mail', 'Turno 6x2', 'Indicado por', 'Indicado para Área', 'Endereço Completo',
            'Tem Rota', 'Qual Rota', 'Bairro Rota', 'Ponto de Referência Rota', 'Pega Ônibus', 'Ponto de Ônibus',
            'Bairro Residência', 'Ponto de Referência Residência', 'Tipo de Teste', 'Nota do Teste', 'Experiência com Cargas/Rigger',
            'Opera Plataforma Móvel', 'Opera Ponte Rolante', 'Dependentes', 'Qual Rota'
        ];
    }
}
