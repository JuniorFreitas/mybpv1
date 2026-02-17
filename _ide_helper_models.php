<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
    /**
     * App\Models\Acessos
     *
     * @property int $id
     * @property int $user_id
     * @property string $ip
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos query()
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereIp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereUserId($value)
     * @mixin \Eloquent
     */
    class Acessos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Admissao
     *
     * @property int $id
     * @property int|null $centro_custo_id
     * @property string|null $matricula
     * @property int|null $feedback_id
     * @property bool $filial
     * @property int|null $centro_custo_filial_id
     * @property string|null $contrato
     * @property string|null $funcao
     * @property string|null $cargo
     * @property float|null $salario
     * @property string|null $status
     * @property string|null $documento
     * @property string|null $documento_portaria
     * @property string|null $tipo_admissao
     * @property string|null $data_encerramento
     * @property string|null $prazo_experiencia
     * @property string|null $tipo_treinamento
     * @property string|null $treinamento
     * @property string|null $data_treinamento
     * @property string|null $carteira_treinamento
     * @property string|null $nr_trinta_tres
     * @property string|null $data_nr_trinta_tres
     * @property string|null $nr_trinta_cinco
     * @property string|null $data_nr_trinta_cinco
     * @property string|null $trinta_dois_sessenta
     * @property string|null $data_trinta_dois_sessenta
     * @property string|null $numero_cracha
     * @property string|null $data_aso
     * @property bool|null $foto_escaneada
     * @property string|null $status_carteira_treinamento
     * @property int|null $usuario_id
     * @property int|null $editado_usuario_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $data_admissao
     * @property string|null $data_adm_prevista
     * @property string|null $data_desmobilizacao
     * @property string|null $avaliacao
     * @property string|null $obs_avaliacao
     * @property int|null $user_avaliacao
     * @property string|null $responsavel_feedback
     * @property string|null $data_avaliacao
     * @property int|null $area_etiqueta_id
     * @property bool|null $deu_baixa_epi
     * @property bool|null $cipa
     * @property array|null $alternativas
     * @property string|null $data_desmob
     * @property int|null $usuario_desmob
     * @property bool|null $pendencia
     * @property string|null $pendencias_quais
     * @property string|null $outros
     * @property string|null $preenchido_por_rh
     * @property string|null $preenchido_por_adm
     * @property string|null $preenchido_por_ssma
     * @property string|null $data_entrega_area
     * @property bool|null $biometria
     * @property string|null $data_biometria
     * @property int|null $formulario_id
     * @property string|null $pis
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property string|null $acessar_area_porto
     * @property string|null $avaliacao_psicologica
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \App\Models\AreaEtiqueta|null $AreaEtiqueta
     * @property-read \App\Models\Treinamento|null $Carteira
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\DadosAdmissao|null $DadosAdmissoes
     * @property-read \App\Models\Demissao|null $Demissao
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeriasAdquiridas> $FeriasAdquiridas
     * @property-read int|null $ferias_adquiridas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FotoTres
     * @property-read int|null $foto_tres_count
     * @property-read \App\Models\User|null $QuemAdmitiu
     * @property-read \App\Models\User|null $QuemAlterou
     * @property-read \App\Models\ResultadoIntegrado|null $ResultadoIntegrado
     * @property-read \App\Models\Examesesmt|null $UltimoAso
     * @property-read \App\Models\AdmissaoAso|null $UltimoAsoAtivo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \App\Models\ParecerRh|null $parecerRh
     * @property-read \App\Models\ParecerRota|null $parecerRota
     * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
     * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao admitidos()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao demitidos()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao query()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAcessarAreaPorto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAlternativas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAreaEtiquetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAvaliacaoPsicologica($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereBiometria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCargo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCarteiraTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCipa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereContrato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAdmPrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAdmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataBiometria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataDesmob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataDesmobilizacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataEncerramento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataEntregaArea($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataNrTrintaCinco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataNrTrintaTres($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataTrintaDoisSessenta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDeuBaixaEpi($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDocumento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDocumentoPortaria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereEditadoUsuarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFotoEscaneada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereMatricula($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNrTrintaCinco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNrTrintaTres($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNumeroCracha($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereObsAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereOutros($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePendencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePendenciasQuais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePis($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePrazoExperiencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorAdm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorSsma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereResponsavelFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereStatusCarteiraTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTipoAdmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTipoTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTrintaDoisSessenta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUserAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUsuarioDesmob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUsuarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao withoutTrashed()
     * @property-read \App\Models\Afastamento|null $Afastamento
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao centroCustoPorCnpj($request, $centros_custos)
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao naoAfastados()
     * @property bool $usa_lentes_corretivas
     * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUsaLentesCorretivas($value)
     * @mixin \Eloquent
     */
    class Admissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AdmissaoAso
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $admissao_id
     * @property int|null $user_alterou_id
     * @property string $data_aso
     * @property string $data_vencimento
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $data_vencimento_formatada
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso query()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereDataAso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereDataVencimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereUserAlterouId($value)
     * @mixin \Eloquent
     */
    class AdmissaoAso extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AdmissoesPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int|null $colaborador_id
     * @property int $centro_custo_id
     * @property string $tipo_contrato
     * @property int $cargo_id
     * @property \Illuminate\Support\Carbon $data_admissao
     * @property float $salario
     * @property int|null $user_id
     * @property string|null $solicitante
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property bool|null $filial
     * @property int|null $centro_custo_filial_id
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property bool $aprovado_via_script
     * @property int|null $quem_deletou_id
     * @property string|null $deleted_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Vaga|null $Cargo
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $Colaborador
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\User|null $UserAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @property-read mixed $salario_format
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCargoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAdmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereTipoContrato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUserId($value)
     * @mixin \Eloquent
     */
    class AdmissoesPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Afastamento
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $cadastrado_id
     * @property string $motivo
     * @property string $data_inicio
     * @property string $data_fim
     * @property string|null $observacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $periodo
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereCadastradoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereMotivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Afastamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AfastamentoFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $quem_cadastrou
     * @property \Illuminate\Support\Carbon|null $data_inicio
     * @property \Illuminate\Support\Carbon|null $data_fim
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereQuemCadastrou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AfastamentoFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AlternativaFormulario
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $nome
     * @property string $tipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RespostaAlternativas> $Opcoes
     * @property-read int|null $opcoes_count
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario query()
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AlternativaFormulario whereTipo($value)
     * @mixin \Eloquent
     */
    class AlternativaFormulario extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AprovacaoExtraConfig
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $tipo_processo
     * @property string $nome_aprovacao
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Cliente|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig query()
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereTipoProcesso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereNomeAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereUpdatedAt($value)
     * @mixin \Eloquent
     * @property array|null $usuarios_autorizados Array de user_ids autorizados a aprovar (além de quem tem privilegio_rh)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig ativo()
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig tipoProcesso($tipo)
     * @method static \Illuminate\Database\Eloquent\Builder|AprovacaoExtraConfig whereUsuariosAutorizados($value)
     */
    class AprovacaoExtraConfig extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Area
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Area newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Area newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Area query()
     * @method static \Illuminate\Database\Eloquent\Builder|Area whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Area whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Area whereLabel($value)
     * @mixin \Eloquent
     */
    class Area extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AreaEtiqueta
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property int|null $centro_custo_id
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\User|null $Gestor
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta query()
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereLabel($value)
     * @mixin \Eloquent
     */
    class AreaEtiqueta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Arquivo
     *
     * @property int $id
     * @property int|null $quem_enviou
     * @property string $nome
     * @property bool $imagem
     * @property string|null $layout
     * @property string $extensao
     * @property string $file
     * @property string|null $thumb
     * @property int $bytes
     * @property bool $temporario
     * @property string|null $chave
     * @property string|null $disco
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read string $url
     * @property-read string $url_delete
     * @property-read string $url_download
     * @property-read string $url_thumb
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo query()
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereBytes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereChave($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereDisco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereExtensao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereFile($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereImagem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereLayout($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereQuemEnviou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereTemporario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereThumb($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Arquivo whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Arquivo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AtaReuniao
     *
     * @property int $id
     * @property int $quem_cadastrou Usuario da sessão
     * @property string $local
     * @property string $data_inicio
     * @property string $data_fim
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property int|null $area_etiqueta_id
     * @property int|null $centro_custo_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoAcao> $Acoes
     * @property-read int|null $acoes_count
     * @property-read \App\Models\AreaEtiqueta|null $Area
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoAssunto> $Assuntos
     * @property-read int|null $assuntos_count
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoParticipante> $Participantes
     * @property-read int|null $participantes_count
     * @property-read User|null $QuemCadastrou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoTipo> $Tipos
     * @property-read int|null $tipos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao query()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao vinculados()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereAreaEtiquetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereLocal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereQuemCadastrou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AtaReuniao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AtaReuniaoAcao
     *
     * @property int $id
     * @property int $ata_reuniao_id
     * @property string $responsavel
     * @property string $email
     * @property string $acao
     * @property string|null $prazo
     * @property int|null $continuo
     * @property string|null $observacao
     * @property string $status
     * @property-read \App\Models\AtaReuniao|null $AtaReuniao
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao query()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAtaReuniaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereContinuo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao wherePrazo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereResponsavel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereStatus($value)
     * @mixin \Eloquent
     */
    class AtaReuniaoAcao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AtaReuniaoAssunto
     *
     * @property int $id
     * @property int $ata_reuniao_id
     * @property string $assunto
     * @property-read \App\Models\AtaReuniao|null $AtaReuniao
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto query()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAssunto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAtaReuniaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereId($value)
     * @mixin \Eloquent
     */
    class AtaReuniaoAssunto extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AtaReuniaoParticipante
     *
     * @property int $id
     * @property int $ata_reuniao_id
     * @property string|null $nome
     * @property int|null $user_id
     * @property string $funcao
     * @property-read \App\Models\AtaReuniao|null $AtaReuniao
     * @property-read User|null $User
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante query()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereAtaReuniaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereUserId($value)
     * @mixin \Eloquent
     */
    class AtaReuniaoParticipante extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AtaReuniaoTipo
     *
     * @property int $id
     * @property int $ata_reuniao_id
     * @property string $tipo Comentário, Assuntos Pendentes ou Próxima Reunião
     * @property string|null $observacao
     * @property-read \App\Models\AtaReuniao|null $AtaReuniao
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereAtaReuniaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoTipo whereTipo($value)
     * @mixin \Eloquent
     */
    class AtaReuniaoTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AuditoriaInterna
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $usuario_id
     * @property int|null $feedback_id
     * @property int $colaborador_id
     * @property string $tipo
     * @property string $descricao
     * @property array $dados
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Cliente $empresa
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna query()
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereDados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AuditoriaInterna whereUsuarioId($value)
     * @mixin \Eloquent
     */
    class AuditoriaInterna extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Avaliacao
     *
     * @property int $id
     * @property int $avaliacao_tipo_id
     * @property int $empresa_id
     * @property string $titulo
     * @property string|null $data_inicio_prazo
     * @property string|null $data_fim_prazo
     * @property string $status
     * @property bool $ativo
     * @property-read \App\Models\AvaliacaoTipo $AvaliacaoTipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAvaliacaoTipoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataFimPrazo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataInicioPrazo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereTitulo($value)
     * @property bool $auto_avaliacao
     * @property mixed|null $fluxo
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAutoAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereFluxo($value)
     * @property int $ano_avaliacao Ano da avaliação
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAnoAvaliacao($value)
     * @property bool $tipo_pj
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoFeedback> $AvaliacaoFeedbacks
     * @property-read int|null $avaliacao_feedbacks_count
     * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereTipoPj($value)
     * @mixin \Eloquent
     */
    class Avaliacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoAnualFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $pergunta_id
     * @property int $nota
     * @property int $quantidade_avaliacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, AvaliacaoAnualFeedback> $AvaliacaoQuantidade
     * @property-read int|null $avaliacao_quantidade_count
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\FormularioAvaliacaoAnual|null $Pergunta
     * @property-read \App\Models\Topicos|null $Topicos
     * @property-read User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback wherePerguntaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereQuantidadeAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AvaliacaoAnualFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoAnualFeedbackQuantidade
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $quantidade_avaliacao
     * @property int $gestor_id
     * @property string $gestor_imediato
     * @property string|null $observacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereGestorImediato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereQuantidadeAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AvaliacaoAnualFeedbackQuantidade extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoAvaliadoresTipos
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $label
     * @property string|null $descricao
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereLabel($value)
     * @property bool $tipo_pj
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAvaliadoresTipos whereTipoPj($value)
     * @mixin \Eloquent
     */
    class AvaliacaoAvaliadoresTipos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoFeedback
     *
     * @property int $id
     * @property int|null $avaliacao_id
     * @property int $empresa_id
     * @property string $origem_feedback
     * @property bool $principal
     * @property int|null $avaliador_id
     * @property int|null $funcionario_id
     * @property int|null $nota_final_total
     * @property string|null $inicio_feedback
     * @property string|null $fim_feedback
     * @property string|null $comentario
     * @property string $status
     * @property string|null $estado_atual
     * @property string|null $estado_desejado
     * @property-read \App\Models\Avaliacao|null $Avaliacao
     * @property-read \App\Models\User|null $Avaliador
     * @property-read \App\Models\User|null $Funcionario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoResposta> $Respostas
     * @property-read int|null $respostas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback origemAvaliador()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliadorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereComentario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEstadoAtual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEstadoDesejado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereFimFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereFuncionarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereInicioFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereNotaFinalTotal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereOrigemFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback wherePrincipal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereStatus($value)
     * @property int|null $avaliacao_tipo_id
     * @property-read \App\Models\AvaliacaoAvaliadoresTipos|null $TipoAvaliador
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliacaoTipoId($value)
     * @property bool $tipo_pj
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereTipoPj($value)
     * @mixin \Eloquent
     */
    class AvaliacaoFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoNoventaDias
     *
     * @property int $id
     * @property string $pergunta
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias wherePergunta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AvaliacaoNoventaDias extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoNoventaFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $pergunta_id
     * @property int $gestor_id usuário em sessãos
     * @property int $nota
     * @property int $quantidade_avaliacao
     * @property string $gestor_imediato
     * @property string|null $observacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoNoventaFeedbackQuantidade> $AvaliacaoQuantidade
     * @property-read int|null $avaliacao_quantidade_count
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\AvaliacaoNoventaDias|null $Pergunta
     * @property-read User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereGestorImediato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback wherePerguntaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereQuantidadeAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AvaliacaoNoventaFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoNoventaFeedbackQuantidade
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $quantidade_avaliacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property mixed $data_admissao
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereQuantidadeAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedbackQuantidade whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class AvaliacaoNoventaFeedbackQuantidade extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoNoventaVencimento
     *
     * @property int $id
     * @property int $feedback_id
     * @property string|null $prazo_dez_inicial
     * @property string|null $prazo_cinco_inicial
     * @property string|null $prazo_dia_inicial
     * @property string|null $prazo_dez_final
     * @property string|null $prazo_cinco_final
     * @property string|null $prazo_dia_final
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoCincoFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoCincoInicial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDezFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDezInicial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDiaFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDiaInicial($value)
     * @mixin \Eloquent
     * @property string|null $token_avaliacao Token único para acesso público à avaliação
     * @property string|null $token_expiracao Data de expiração do token
     * @property int $avaliacao_realizada Indica se a avaliação já foi realizada via token
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereAvaliacaoRealizada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereTokenAvaliacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereTokenExpiracao($value)
     */
    class AvaliacaoNoventaVencimento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoResposta
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $avaliacao_feedback_id
     * @property int|null $topico_id
     * @property int $nota
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereAvaliacaoFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereTopicoId($value)
     * @mixin \Eloquent
     */
    class AvaliacaoResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoResultado
     *
     * @property int $id
     * @property int|null $avaliacao_feedback_id
     * @property int|null $gestor_id
     * @property int|null $topico_id
     * @property string $plano_de_acao
     * @property string|null $responsavel
     * @property int $empresa_id
     * @property string|null $inicio
     * @property string|null $termino
     * @property string|null $status
     * @property array|null $dados_extras
     * @property-read \App\Models\AvaliacaoTopico|null $Topico
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereAvaliacaoFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereDadosExtras($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado wherePlanoDeAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereResponsavel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereTermino($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereTopicoId($value)
     * @property bool $tipo_pj
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereTipoPj($value)
     * @mixin \Eloquent
     */
    class AvaliacaoResultado extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoTipo
     *
     * @property int $id
     * @property string $nome
     * @property string $descricao
     * @property int $empresa_id
     * @property bool $ativo
     * @property-read \App\Models\AvaliacaoTopico $AvaliacaoTipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereNome($value)
     * @property bool $tipo_pj
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereTipoPj($value)
     * @mixin \Eloquent
     */
    class AvaliacaoTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\AvaliacaoTopico
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $avaliacao_tipo_id
     * @property string|null $topico_pai_id
     * @property string $topico
     * @property string|null $topico_explicacao
     * @property bool $ativo
     * @property-read \App\Models\AvaliacaoTipo|null $AvaliacaoTipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, AvaliacaoTopico> $Subtopicos
     * @property-read int|null $subtopicos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico query()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico topicosPais()
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAvaliacaoTipoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoExplicacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoPaiId($value)
     * @mixin \Eloquent
     * @property bool $tipo_pj
     * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTipoPj($value)
     */
    class AvaliacaoTopico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Beneficio
     *
     * @property int $id
     * @property string $nome
     * @property int $tipobeneficio_id
     * @property int|null $cliente_id
     * @property float $valor
     * @property string $aplicacao
     * @property string $periodicidade
     * @property float $valor_descontado
     * @property string $opcao_desconto
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Beneficio> $BeneficioFeedback
     * @property-read int|null $beneficio_feedback_count
     * @property-read \App\Models\User|null $Empresa
     * @property-read \App\Models\TipoBeneficio|null $TipoBeneficio
     * @property-read mixed $valor_format
     * @property-read mixed $valordescontado_format
     * @property-write mixed $valordescontado
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio query()
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereAplicacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereOpcaoDesconto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio wherePeriodicidade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereTipobeneficioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereValor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereValorDescontado($value)
     * @mixin \Eloquent
     */
    class Beneficio extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\BeneficioFeedback
     *
     * @property int $beneficio_id
     * @property int $feedback_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Beneficio|null $Beneficio
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereBeneficioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|BeneficioFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class BeneficioFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CartaOferta
     *
     * @property int $id
     * @property string|null $token
     * @property int|null $empresa_id
     * @property int|null $curriculo_id
     * @property int|null $feedback_id
     * @property int|null $vagas_abertas_id
     * @property int|null $vaga_projeto_id
     * @property int|null $arquivo_id
     * @property string $status
     * @property string $local
     * @property array|null $logs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \App\Models\Arquivo|null $anexo
     * @property-read \App\Models\Curriculo|null $curriculo
     * @property-read \App\Models\Cliente|null $empresa
     * @property-read \App\Models\FeedbackCurriculo|null $feedback
     * @property-read mixed $log
     * @property-read mixed $ultima_atualizacao
     * @property-read \App\Models\VagasAbertas|null $vagaAberta
     * @property-read \App\Models\VagaProjeto|null $vagaProjeto
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta query()
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereArquivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereLocal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereLogs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereVagaProjetoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereVagasAbertasId($value)
     * @mixin \Eloquent
     */
    class CartaOferta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CarteiraAssinatura
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $arquivo_id
     * @property string $nome
     * @property string $tipo
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Arquivo|null $Arquivo
     * @property-read \App\Models\Cliente $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura query()
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereArquivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CarteiraAssinatura whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CarteiraAssinatura extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CategoriaPlanoConta
     *
     * @property int $id
     * @property string $descricao
     * @property bool $ativo
     * @property int $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta query()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereId($value)
     * @mixin \Eloquent
     */
    class CategoriaPlanoConta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CategoriaVagas
     *
     * @property int $id
     * @property string $titulo
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas query()
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereTitulo($value)
     * @mixin \Eloquent
     */
    class CategoriaVagas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CentroCusto
     *
     * @property int $id
     * @property int|null $gestor_id
     * @property int|null $cliente_id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admissao> $Admissao
     * @property-read int|null $admissao_count
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CentroCustoFilial> $Filiais
     * @property-read int|null $filiais_count
     * @property-read \App\Models\User|null $Gestor
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto query()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCusto whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CentroCusto extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CentroCustoFilial
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $centro_custo_id
     * @property int $cliente_filial_id
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\CentroCusto $CentroCusto
     * @property-read \App\Models\Cliente $Empresa
     * @property-read \App\Models\ClienteFilial $Filial
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial query()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereClienteFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|CentroCustoFilial withoutTrashed()
     * @mixin \Eloquent
     */
    class CentroCustoFilial extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CertificadoAlumar
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int $cliente_id
     * @property bool $nacional
     * @property int|null $empresa_treinamento_trinta_tres_id
     * @property int|null $empresa_treinamento_trinta_cinco_id
     * @property int|null $instrutor_trinta_tres_id
     * @property int|null $instrutor_trinta_cinco_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamentoTrintaCinco
     * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamentoTrintaTres
     * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTrintaCinco
     * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTrintaTres
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\Instrutor|null $InstrutorTrintaCinco
     * @property-read \App\Models\Instrutor|null $InstrutorTrintaTres
     * @property-read \App\Models\Treinamento|null $Treinamento
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar query()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereEmpresaTreinamentoTrintaCincoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereEmpresaTreinamentoTrintaTresId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereInstrutorTrintaCincoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereInstrutorTrintaTresId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereNacional($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CertificadoAlumar extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CertificadoNr
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $nr_dez_instituicao
     * @property \Illuminate\Support\Carbon|null $nr_dez_emissao
     * @property \Illuminate\Support\Carbon|null $nr_dez_validade
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr query()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezEmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezInstituicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezValidade($value)
     * @mixin \Eloquent
     */
    class CertificadoNr extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CertificadoSgi
     *
     * @property int $id
     * @property int $cliente_id
     * @property int $treinamento_evento_id
     * @property int $pessoa_evento_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property mixed $0
     * @property mixed $1
     * @property mixed $2
     * @property mixed $3
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi query()
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi wherePessoaEventoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereTreinamentoEventoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CertificadoSgi extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ChecklistsTarefa
     *
     * @property int $id
     * @property int $tarefa_id
     * @property string $titulo
     * @property int $ordem
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistsTarefaItem> $Itens
     * @property-read int|null $itens_count
     * @property-read \App\Models\Tarefa|null $Tarefa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa query()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereTarefaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefa whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ChecklistsTarefa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ChecklistsTarefaItem
     *
     * @property int $id
     * @property int $checklist_id
     * @property string $titulo
     * @property bool $concluido
     * @property int $ordem
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\ChecklistsTarefa|null $CheckList
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem query()
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereChecklistId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereConcluido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ChecklistsTarefaItem whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ChecklistsTarefaItem extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Cih
     *
     * @property int $id
     * @property int|null $tag_id
     * @property int|null $gestor_id
     * @property string|null $outra_tag
     * @property bool $varios_colaboradores
     * @property string|null $colaboradores_avulso
     * @property int|null $feedback_id somente quem foi admitido
     * @property int|null $cliente_id Cliente empresa
     * @property int $user_lancamento_id Responsavel pelo lançamenro usuario em sessão
     * @property int|null $area_id
     * @property string|null $outra_area
     * @property string|null $obs_lancamento Responsavel pela aprovação usuario em sessão
     * @property string $data_lancamento
     * @property string $acao
     * @property int|null $user_aprovacao_id Responsavel pela aprovação usuario em sessão
     * @property string|null $obs_aprovacao Responsavel pela aprovação usuario em sessão
     * @property string|null $data_aprovacao
     * @property string|null $status aberto, aprovado
     * @property int|null $user_rh_id
     * @property string|null $resposta_rh
     * @property string|null $obs_rh
     * @property string|null $data_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property int|null $centro_custo_id
     * @property string|null $centro_custo_outro
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\AreaEtiqueta|null $Area
     * @property-read \App\Models\CentroCusto|null $CentroDeCusto
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeedbackCurriculo> $CihFeedbacks
     * @property-read int|null $cih_feedbacks_count
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\FeedbackCurriculo|null $Colaborador
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeedbackCurriculo> $Colaboradores
     * @property-read int|null $colaboradores_count
     * @property-read \App\Models\User|null $Empresa
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\User|null $ResponsavelAprovacao
     * @property-read \App\Models\User|null $ResponsavelLancamento
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\CihTag|null $Tag
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static Builder|Cih newModelQuery()
     * @method static Builder|Cih newQuery()
     * @method static Builder|Cih onlyTrashed()
     * @method static Builder|Cih query()
     * @method static Builder|Cih vinculados()
     * @method static Builder|Cih whereAcao($value)
     * @method static Builder|Cih whereAreaId($value)
     * @method static Builder|Cih whereCentroCustoId($value)
     * @method static Builder|Cih whereCentroCustoOutro($value)
     * @method static Builder|Cih whereClienteId($value)
     * @method static Builder|Cih whereColaboradoresAvulso($value)
     * @method static Builder|Cih whereCreatedAt($value)
     * @method static Builder|Cih whereDataAprovacao($value)
     * @method static Builder|Cih whereDataAprovacaoRh($value)
     * @method static Builder|Cih whereDataLancamento($value)
     * @method static Builder|Cih whereDeletedAt($value)
     * @method static Builder|Cih whereEmpresaId($value)
     * @method static Builder|Cih whereFeedbackId($value)
     * @method static Builder|Cih whereGestorId($value)
     * @method static Builder|Cih whereId($value)
     * @method static Builder|Cih whereObsAprovacao($value)
     * @method static Builder|Cih whereObsLancamento($value)
     * @method static Builder|Cih whereObsRh($value)
     * @method static Builder|Cih whereOutraArea($value)
     * @method static Builder|Cih whereOutraTag($value)
     * @method static Builder|Cih whereRespostaRh($value)
     * @method static Builder|Cih whereStatus($value)
     * @method static Builder|Cih whereTagId($value)
     * @method static Builder|Cih whereUpdatedAt($value)
     * @method static Builder|Cih whereUserAprovacaoId($value)
     * @method static Builder|Cih whereUserDeletouId($value)
     * @method static Builder|Cih whereUserLancamentoId($value)
     * @method static Builder|Cih whereUserRhId($value)
     * @method static Builder|Cih whereVariosColaboradores($value)
     * @method static Builder|Cih withTrashed()
     * @method static Builder|Cih withoutTrashed()
     * @mixin \Eloquent
     * @property-read mixed $data_criacao
     * @property-read mixed $data_iso_aprovacao_gestor
     * @property-read mixed $data_iso_aprovacao_rh
     * @property-read mixed $data_iso_criacao
     * @property-read mixed $data_iso_lancamento
     */
    class Cih extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CihTag
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $label
     * @property bool $ativo
     * @property bool $anexo_obrigatorio
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag query()
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereAnexoObrigatorio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CihTag extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClassificacaoRescisao
     *
     * @property int $id
     * @property string $classe
     * @property string $descricao
     * @property string $periodo
     * @property bool $ativo
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereClasse($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao wherePeriodo($value)
     * @mixin \Eloquent
     */
    class ClassificacaoRescisao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClassificacaoRescisaoCurriculo
     *
     * @property int $classificacao_id
     * @property int|null $feedback_id
     * @property string|null $observacoes
     * @property string|null $quem_classificou
     * @property \Illuminate\Support\Carbon|null $data_afastamento
     * @property string|null $preenchido_por
     * @property int|null $user_id
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereClassificacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereDataAfastamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereObservacoes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo wherePreenchidoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereQuemClassificou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereUserId($value)
     * @mixin \Eloquent
     */
    class ClassificacaoRescisaoCurriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Cliente
     *
     * @property int $id
     * @property string $tipo_cliente
     * @property string|null $cnpj
     * @property string|null $cpf
     * @property string|null $nome
     * @property string|null $apelido
     * @property string $tipo
     * @property string|null $razao_social
     * @property string|null $nome_fantasia
     * @property int $area_id
     * @property string|null $ramo
     * @property string|null $cep
     * @property string|null $logradouro
     * @property string|null $numero
     * @property string|null $complemento
     * @property string|null $bairro
     * @property string|null $municipio
     * @property string|null $uf
     * @property string|null $contato
     * @property string|null $email
     * @property string|null $tel_principal
     * @property string|null $aniversario
     * @property string|null $como_conheceu
     * @property string|null $como_conheceu_outro
     * @property string|null $politica_ehs
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $missao
     * @property string|null $visao
     * @property string|null $valores
     * @property string|null $politica_gq
     * @property-read \App\Models\Area|null $Area
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AreaEtiqueta> $AreasEtiquetas
     * @property-read int|null $areas_etiquetas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vaga> $Cargos
     * @property-read int|null $cargos_count
     * @property-read \App\Models\CarteiraAssinatura|null $CarteiraAssinatura
     * @property-read \App\Models\ClienteConfig|null $ClienteConfig
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $EmpresaFuncionarios
     * @property-read int|null $empresa_funcionarios_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClienteFilial> $Filiais
     * @property-read int|null $filiais_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Funcionarios
     * @property-read int|null $funcionarios_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Logo
     * @property-read int|null $logo_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Mascote
     * @property-read int|null $mascote_count
     * @property-read \App\Models\Papel|null $Papel
     * @property-read \App\Models\ParabensEnviado|null $Parabens
     * @property-read \App\Models\PesquisaClimaCliente|null $PesquisaClimaCliente
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServicosCliente> $ServicosCliente
     * @property-read int|null $servicos_cliente_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServicosProspects> $ServicosProspect
     * @property-read int|null $servicos_prospect_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClienteTelefone> $Telefones
     * @property-read int|null $telefones_count
     * @property-read \App\Models\EmpresaTemporaria|null $Temporaria
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagasAbertas> $VagasAbertas
     * @property-read int|null $vagas_abertas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $endereco_completo
     * @property mixed $inicio
     * @method static Builder|Cliente newModelQuery()
     * @method static Builder|Cliente newQuery()
     * @method static Builder|Cliente query()
     * @method static Builder|Cliente whereAniversario($value)
     * @method static Builder|Cliente whereApelido($value)
     * @method static Builder|Cliente whereAreaId($value)
     * @method static Builder|Cliente whereAtivo($value)
     * @method static Builder|Cliente whereBairro($value)
     * @method static Builder|Cliente whereCep($value)
     * @method static Builder|Cliente whereCnpj($value)
     * @method static Builder|Cliente whereComoConheceu($value)
     * @method static Builder|Cliente whereComoConheceuOutro($value)
     * @method static Builder|Cliente whereComplemento($value)
     * @method static Builder|Cliente whereContato($value)
     * @method static Builder|Cliente whereCpf($value)
     * @method static Builder|Cliente whereCreatedAt($value)
     * @method static Builder|Cliente whereEmail($value)
     * @method static Builder|Cliente whereId($value)
     * @method static Builder|Cliente whereLogradouro($value)
     * @method static Builder|Cliente whereMissao($value)
     * @method static Builder|Cliente whereMunicipio($value)
     * @method static Builder|Cliente whereNome($value)
     * @method static Builder|Cliente whereNomeFantasia($value)
     * @method static Builder|Cliente whereNumero($value)
     * @method static Builder|Cliente wherePoliticaEhs($value)
     * @method static Builder|Cliente wherePoliticaGq($value)
     * @method static Builder|Cliente whereRamo($value)
     * @method static Builder|Cliente whereRazaoSocial($value)
     * @method static Builder|Cliente whereTelPrincipal($value)
     * @method static Builder|Cliente whereTipo($value)
     * @method static Builder|Cliente whereTipoCliente($value)
     * @method static Builder|Cliente whereUf($value)
     * @method static Builder|Cliente whereUpdatedAt($value)
     * @method static Builder|Cliente whereValores($value)
     * @method static Builder|Cliente whereVisao($value)
     * @mixin \Eloquent
     */
    class Cliente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClienteAreaEtiqueta
     *
     * @property int $cliente_id
     * @property int $area_etiqueta_id
     * @property string|null $numero_supervisor
     * @property mixed $0
     * @property mixed $1
     * @property mixed $2
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereAreaEtiquetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteAreaEtiqueta whereNumeroSupervisor($value)
     * @mixin \Eloquent
     */
    class ClienteAreaEtiqueta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClienteConfig
     *
     * @property int $id
     * @property int|null $verifica_mes_vencimento
     * @property int|null $cliente_id
     * @property bool|null $envia_whatsapp
     * @property int|null $vencimento_aso
     * @property string $modelo_cih
     * @property bool $supervisor_etiqueta_bloqueio
     * @property-read \App\Models\Cliente|null $Cliente
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereEnviaWhatsapp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereModeloCih($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereSupervisorEtiquetaBloqueio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVencimentoAso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVerificaMesVencimento($value)
     * @mixin \Eloquent
     */
    class ClienteConfig extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClienteFilial
     *
     * @property int $id
     * @property int $empresa_id
     * @property mixed $dados
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property-read \App\Models\Cliente $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $endereco_completo
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial userEmpresa()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial withoutTrashed()
     * @mixin \Eloquent
     */
    class ClienteFilial extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ClienteTelefone
     *
     * @property int $id
     * @property string $tipo
     * @property string $pais
     * @property string $numero
     * @property string|null $ramal
     * @property string|null $detalhe
     * @property int $cliente_id
     * @property bool $principal
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone query()
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereDetalhe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone wherePais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone wherePrincipal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereRamal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereTipo($value)
     * @mixin \Eloquent
     */
    class ClienteTelefone extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Cloud
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $nome
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $ativo
     * @property-read \App\Models\Cliente $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItensCloud> $Itens
     * @property-read int|null $itens_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItensCloud> $Raiz
     * @property-read int|null $raiz_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Usuarios
     * @property-read int|null $usuarios_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud query()
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Cloud extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Curriculo
     *
     * @property int $id
     * @property string $cpf
     * @property string|null $rg
     * @property string|null $rg_data_emissao
     * @property string|null $naturalidade
     * @property string|null $nacionalidade
     * @property string|null $orgao_expeditor
     * @property string|null $carteira_trabalho
     * @property string $nome
     * @property string|null $estado_civil
     * @property string|null $cnh
     * @property string|null $cnh_vencimento
     * @property string $nascimento
     * @property string|null $logradouro
     * @property string|null $end_numero
     * @property string|null $complemento
     * @property string|null $bairro
     * @property string|null $municipio
     * @property string|null $uf
     * @property string|null $cep
     * @property string|null $email
     * @property int|null $formacao
     * @property string|null $formacao_instituicao
     * @property string|null $formacao_curso
     * @property string|null $formacao_status
     * @property int|null $vaga_pretendida
     * @property string|null $uf_vaga
     * @property int|null $municipio_id
     * @property bool|null $pcd
     * @property string|null $cid
     * @property bool|null $viajar
     * @property bool|null $lido
     * @property int|null $usuario_lido
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $datalido
     * @property string|null $filiacao_pai
     * @property string|null $filiacao_mae
     * @property bool|null $disponibilidade_sabado
     * @property bool|null $disponibilidade_domingo
     * @property string|null $sexo
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $AnexosCpfRg
     * @property-read int|null $anexos_cpf_rg_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Antecedentes
     * @property-read int|null $antecedentes_count
     * @property-read \App\Models\CurriculoAtualizacao|null $Atualizacao
     * @property-read \App\Models\CartaOferta|null $CartaOferta
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CartaOfertaArquivo
     * @property-read int|null $carta_oferta_arquivo_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CartaSindicato
     * @property-read int|null $carta_sindicato_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CartaoVacinaFilho
     * @property-read int|null $cartao_vacina_filho_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CarteiraVacina
     * @property-read int|null $carteira_vacina_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CertificadoEscolaridade
     * @property-read int|null $certificado_escolaridade_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CertificadoReservista
     * @property-read int|null $certificado_reservista_count
     * @property-read \App\Models\Municipio|null $Cidade
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ComprovanteEnd
     * @property-read int|null $comprovante_end_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ContaBanco
     * @property-read int|null $conta_banco_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CtpsFrente
     * @property-read int|null $ctps_frente_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CtpsVerso
     * @property-read int|null $ctps_verso_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $DeclaracaoEscolarFilho
     * @property-read int|null $declaracao_escolar_filho_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UsuarioDependente> $Dependentes
     * @property-read int|null $dependentes_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailPreAdmissao> $EmailsPreAdmissao
     * @property-read int|null $emails_pre_admissao_count
     * @property-read \App\Models\Escolaridade|null $Escolaridade
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CurriculoExperiencia> $Experiencias
     * @property-read int|null $experiencias_count
     * @property-read \App\Models\FeedbackCurriculo|null $FeedBack
     * @property-read \App\Models\Escolaridade|null $Formacao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FotoTres
     * @property-read int|null $foto_tres_count
     * @property-read \App\Models\ParabensEnviado|null $Parabens
     * @property-read \App\Models\ParecerRh|null $ParecerRh
     * @property-read \App\Models\ParecerRota|null $ParecerRota
     * @property-read \App\Models\ParecerEntrevistaTecnica|null $ParecerTecnica
     * @property-read \App\Models\ParecerTestePratico|null $ParecerTeste
     * @property-read \App\Models\User|null $Pessoa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $PisRescisao
     * @property-read int|null $pis_rescisao_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CurriculoQualificacao> $Qualificacoes
     * @property-read int|null $qualificacoes_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $RgcpfFilho
     * @property-read int|null $rgcpf_filho_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TelefoneCurriculo> $Telefones
     * @property-read int|null $telefones_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $TituloEleitor
     * @property-read int|null $titulo_eleitor_count
     * @property-read \App\Models\Treinamento|null $Treinamentos
     * @property-read \App\Models\User|null $User
     * @property-read \App\Models\User|null $Usuario
     * @property-read \App\Models\Vaga|null $Vaga
     * @property-read \App\Models\VagasAbertas|null $VagaAberta
     * @property-read \App\Models\NotificacaoWhats|null $WhatsAppNotificacao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $c_token
     * @property-read mixed $cpf_format
     * @property-read mixed $endereco_completo
     * @property-read mixed $idade
     * @property-read mixed $rg_format
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereBairro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCarteiraTrabalho($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCep($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCnh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCnhVencimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereComplemento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCpf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereDatalido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereDisponibilidadeDomingo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereDisponibilidadeSabado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereEndNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereEstadoCivil($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFiliacaoMae($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFiliacaoPai($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoCurso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoInstituicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereLido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereLogradouro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereMunicipio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereMunicipioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNacionalidade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNascimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNaturalidade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereOrgaoExpeditor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo wherePcd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereRg($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereRgDataEmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereSexo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUfVaga($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUsuarioLido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereVagaPretendida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereViajar($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Curriculo withoutTrashed()
     * @property-read \App\Models\TelefoneCurriculo|null $TelPrincipal
     * @mixin \Eloquent
     */
    class Curriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CurriculoAtualizacao
     *
     * @property int $curriculo_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoAtualizacao whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CurriculoAtualizacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CurriculoExperiencia
     *
     * @property int $id
     * @property int $curriculo_id
     * @property string $empresa
     * @property string $cargo
     * @property string $principais_atv
     * @property string|null $data_inicio
     * @property string|null $data_fim
     * @property string|null $referencia_nome
     * @property string|null $referencia_telefone
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia query()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereCargo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereEmpresa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia wherePrincipaisAtv($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereReferenciaNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereReferenciaTelefone($value)
     * @mixin \Eloquent
     */
    class CurriculoExperiencia extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CurriculoQualificacao
     *
     * @property int $id
     * @property int $curriculo_id
     * @property string $nome
     * @property string $instituicao
     * @property string $mes_conclusao
     * @property int $ano_conclusao
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereAnoConclusao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereInstituicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereMesConclusao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CurriculoQualificacao whereNome($value)
     * @mixin \Eloquent
     */
    class CurriculoQualificacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\CursoFormacaoRH
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string $curso
     * @property string $instituicao
     * @property \Illuminate\Support\Carbon $emissao
     * @property \Illuminate\Support\Carbon|null $validade
     * @property bool|null $certificado
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH query()
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereCertificado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereCurso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereEmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereInstituicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereValidade($value)
     * @mixin \Eloquent
     */
    class CursoFormacaoRH extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DadosAdmissao
     *
     * @property int $id
     * @property int|null $admissao_id
     * @property string|null $ctps_numero
     * @property string|null $ctps_serie
     * @property string|null $ctps_uf
     * @property string|null $ctps_data_emissao
     * @property string|null $titulo_eleitor_numero
     * @property string|null $titulo_eleitor_sessao
     * @property string|null $titulo_eleitor_zona
     * @property string|null $cert_reservista_num
     * @property string|null $cert_reservista_categoria
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao query()
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCertReservistaCategoria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCertReservistaNum($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsDataEmissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsSerie($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereCtpsUf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorSessao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DadosAdmissao whereTituloEleitorZona($value)
     * @mixin \Eloquent
     */
    class DadosAdmissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Demissao
     *
     * @property int $id
     * @property int $feedback_id
     * @property bool $cipa
     * @property Carbon $data_desmobilizacao
     * @property int $motivo_rescisao_id
     * @property string|null $outro_motivo
     * @property int $tipo_aviso_id
     * @property string $solicitado_por
     * @property string|null $comentario
     * @property int $user_id
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @property-read FeedbackCurriculo $Feedback
     * @property-read User $User
     * @property-read Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read MotivoRescisao $motivoRescisao
     * @property-read TipoAviso $tipoAviso
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao query()
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereCipa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereComentario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereDataDesmobilizacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereMotivoRescisaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereOutroMotivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereSolicitadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereTipoAvisoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demissao whereUserId($value)
     * @mixin \Eloquent
     */
    class Demissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DemissaoPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int $colaborador_id
     * @property int $centro_custo_id
     * @property string|null $aviso
     * @property \Illuminate\Support\Carbon $data_demissao
     * @property string|null $tipo_aviso
     * @property float $valor
     * @property int|null $user_id
     * @property string|null $solicitante
     * @property string|null $status
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property int|null $filial
     * @property int|null $centro_custo_filial_id
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property bool $aprovado_via_script
     * @property int|null $quem_deletou_id
     * @property string|null $deleted_at
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read User|null $Colaborador
     * @property-read User|null $GestorAprovacao
     * @property-read User|null $RhAprovacao
     * @property-read User|null $UserAprovacao
     * @property-read User|null $AprovacaoExtra
     * @property-read User|null $UserCadastrou
     * @property-read mixed $valor_format
     * @property-write mixed $data_pagamento
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAviso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataDemissao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereTipoAviso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereValor($value)
     * @mixin \Eloquent
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacaoExtra($value)
     */
    class DemissaoPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Departamento
     *
     * @property int $id
     * @property string $label
     * @property int|null $cliente_id
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \App\Models\User|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departamento whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Departamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentoContratos
     *
     * @property int $id
     * @property int $empresa_id
     * @property array $dados_cadastrais
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \App\Models\Cliente|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos query()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereDadosCadastrais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoContratos whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class DocumentoContratos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentoEmpresa
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $contrato_id
     * @property bool $tipo_empresa
     * @property array $documentos_empresa
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \App\Models\DocumentoContratos|null $Contrato
     * @property-read \App\Models\Cliente|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa query()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereContratoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereDocumentosEmpresa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereTipoEmpresa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoEmpresa whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class DocumentoEmpresa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentoSsma
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $contrato_id
     * @property bool $tipo_ssma
     * @property array $documentos_ssma
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \App\Models\DocumentoContratos|null $Contrato
     * @property-read \App\Models\Cliente|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma query()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereContratoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereDocumentosSsma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereTipoSsma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentoSsma whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class DocumentoSsma extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentosCurriculosAdmissaoEmpresa
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $categoria_id
     * @property string $label
     * @property string|null $metodo
     * @property string|null $descricao
     * @property string $tipo
     * @property string|null $url_arquivo
     * @property array|null $configuracoes
     * @property int $ordem
     * @property bool $ativo
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa query()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereCategoriaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereConfiguracoes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereMetodo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereUrlArquivo($value)
     * @mixin \Eloquent
     */
    class DocumentosCurriculosAdmissaoEmpresa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentosCurriculosCatAdmissaoEmpresa
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $categoria_id
     * @property string $label
     * @property string|null $metodo
     * @property string|null $descricao
     * @property string $tipo
     * @property string|null $url_arquivo
     * @property mixed|null $configuracoes
     * @property int $ordem
     * @property bool $ativo
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa query()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereCategoriaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereConfiguracoes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereMetodo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosCatAdmissaoEmpresa whereUrlArquivo($value)
     * @mixin \Eloquent
     */
    class DocumentosCurriculosCatAdmissaoEmpresa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\DocumentosPreAdmissao
     *
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao query()
     * @mixin \Eloquent
     */
    class DocumentosPreAdmissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmailPreAdmissao
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $curriculo_id
     * @property int $quem_enviou_id
     * @property string|null $observacao
     * @property int $email_atual
     * @property int $email_padrao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmailAtual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmailPadrao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereQuemEnviouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EmailPreAdmissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaConfig
     *
     * @property int $empresa_id
     * @property string $tipo_frequencia
     * @property int $tempo_limite_falta
     * @property int $tempo_limite_saida
     * @property string $dia_nova_frequencia
     * @property int $limite_tolerancia
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereDiaNovaFrequencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereLimiteTolerancia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTempoLimiteFalta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTempoLimiteSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaConfig whereTipoFrequencia($value)
     * @mixin \Eloquent
     */
    class EmpresaConfig extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaDispositivos
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $descricao
     * @property float $lat
     * @property float $long
     * @property int $perimetro
     * @property int $obrigatorio
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereLat($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereLong($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereObrigatorio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos wherePerimetro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaDispositivos whereUserDeletouId($value)
     * @mixin \Eloquent
     */
    class EmpresaDispositivos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaEscala
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $descricao
     * @property \Illuminate\Support\Carbon $inicio
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EscalaJornada> $Jornadas
     * @property-read int|null $jornadas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala whereUserDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaEscala withoutTrashed()
     * @mixin \Eloquent
     */
    class EmpresaEscala extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaExame
     *
     * @property int $id
     * @property int|null $user_id
     * @property int|null $empresa_id
     * @property string $nome
     * @property array $dados
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User|null $Empresa
     * @property-read \App\Models\User|null $Usuario
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereDados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereUserId($value)
     * @mixin \Eloquent
     */
    class EmpresaExame extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaPerimetro
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $descricao
     * @property float $lat
     * @property float $long
     * @property int $perimetro
     * @property bool $obrigatorio
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLat($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereLong($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereObrigatorio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro wherePerimetro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro whereUserDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaPerimetro withoutTrashed()
     * @mixin \Eloquent
     */
    class EmpresaPerimetro extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaTemporaria
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $razao_social
     * @property array $dados
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereDados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereRazaoSocial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EmpresaTemporaria extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EmpresaTreinamento
     *
     * @property int $id
     * @property string $nome
     * @property string $endereco
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereEndereco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EmpresaTreinamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EntrevistaDesligamento
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $superior_imediato
     * @property string|null $motivo
     * @property string|null $trabalharia_novamente
     * @property string|null $contr_melhoria
     * @property string|null $relacao_interpessoal
     * @property string|null $recursos_fisicos
     * @property string|null $valores_normas
     * @property string|null $planejamento
     * @property string|null $sob_superior_imediato
     * @property string|null $direcao_empresa
     * @property string|null $oportunidades
     * @property string|null $salario_beneficio
     * @property string|null $atividade
     * @property string|null $comentarios
     * @property string|null $parecer_entrevistador
     * @property bool $pode_voltar
     * @property string|null $porque_pode_voltar
     * @property string $quem_entrevistou
     * @property int $user_entrevista
     * @property string|null $data_entrevista
     * @property string|null $preenchido_por
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $formulario_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereAtividade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereComentarios($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereContrMelhoria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereDataEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereDirecaoEmpresa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereMotivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereOportunidades($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereParecerEntrevistador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePlanejamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePodeVoltar($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePorquePodeVoltar($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePreenchidoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereQuemEntrevistou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereRecursosFisicos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereRelacaoInterpessoal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSalarioBeneficio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSobSuperiorImediato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSuperiorImediato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereTrabalhariaNovamente($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereUserEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereValoresNormas($value)
     * @mixin \Eloquent
     */
    class EntrevistaDesligamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EntrevistaRh
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $parecer
     * @property string|null $indicado_para
     * @property int|null $nota
     * @property string|null $entrevistado_por
     * @property int|null $user_id
     * @property string|null $comentario
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh query()
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereComentario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereEntrevistadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereIndicadoPara($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereParecer($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaRh whereUserId($value)
     * @mixin \Eloquent
     */
    class EntrevistaRh extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EscalaJornada
     *
     * @property int $id
     * @property int $escala_id
     * @property int $ocorrencia_id
     * @property string $tipo
     * @property int $repetir
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\EmpresaEscala|null $Escala
     * @property-read \App\Models\OcorrenciaJornada|null $Ocorrencia
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PeriodoJornada> $Periodos
     * @property-read int|null $periodos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada query()
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereEscalaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereOcorrenciaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereRepetir($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereUserDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada withoutTrashed()
     * @mixin \Eloquent
     */
    class EscalaJornada extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Escolaridade
     *
     * @property int $id
     * @property string $tipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade query()
     * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Escolaridade whereTipo($value)
     * @mixin \Eloquent
     */
    class Escolaridade extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\EtapaTipo
     *
     * @property int $id
     * @property int $cliente_id
     * @property string $nome
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Cliente|null $Cliente
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|EtapaTipo whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EtapaTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Etapas
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int $user_id
     * @property int $vaga_id
     * @property string $etapa
     * @property bool|null $enviado_email
     * @property string|null $text_email
     * @property string|null $observacao
     * @property string $status classificado,desclassificado,andamento
     * @property string|null $preenchido_por
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $passo_id é o id da etapa_tipo
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas query()
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereEnviadoEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereEtapa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas wherePassoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas wherePreenchidoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereTextEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Etapas whereVagaId($value)
     * @mixin \Eloquent
     */
    class Etapas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Exame
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $exame_tipo_id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|Exame newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Exame newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Exame query()
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereExameTipoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exame whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Exame extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameEmpresa
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $razao_social
     * @property string|null $cnpj
     * @property string|null $cep
     * @property string|null $logradouro
     * @property string|null $numero
     * @property string|null $complemento
     * @property string|null $bairro
     * @property string|null $municipio
     * @property string|null $uf
     * @property string|null $telefone
     * @property string|null $email
     * @property int $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereBairro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCep($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCnpj($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereComplemento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereLogradouro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereMunicipio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereRazaoSocial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereTelefone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ExameEmpresa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameFuncionario
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $formulario_id
     * @property int $feedback_id
     * @property array $respostas
     * @property int $empresa_exame_id
     * @property int $user_encaminhou_id
     * @property string $token
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool|null $pcmso
     * @property int|null $pcmso_id
     * @property int|null $exame_tipo_id
     * @property \Illuminate\Support\Carbon|null $encaminhamento_data
     * @property-read \App\Models\EmpresaExame|null $EmpresaExame
     * @property-read \App\Models\ExameTipo|null $ExameTipo
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\Formulario|null $Formulario
     * @property-read \App\Models\Pcmso|null $PcmsoDados
     * @property-read \App\Models\User|null $QuemEncaminhou
     * @property-read \App\Models\Examesesmt|null $Sesmt
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEmpresaExameId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEncaminhamentoData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereExameTipoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario wherePcmso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario wherePcmsoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereRespostas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereUserEncaminhouId($value)
     * @mixin \Eloquent
     */
    class ExameFuncionario extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameRisco
     *
     * @property int $id
     * @property int $empresa_id
     * @property int|null $risco_tipo
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\ExameRiscoTipo|null $Tipo
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereRiscoTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRisco whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ExameRisco extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameRiscoTipo
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameRiscoTipo whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ExameRiscoTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameTipo
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ExameTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ExameTreinamento
     *
     * @property int $id
     * @property int $feedback_id
     * @property bool $exame_realizado
     * @property \Illuminate\Support\Carbon|null $data_realizado
     * @property string|null $tipo_exame
     * @property bool|null $trabalho_altura
     * @property bool|null $espaco_confinado
     * @property int $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereDataRealizado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereEspacoConfinado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereExameRealizado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereTipoExame($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereTrabalhoAltura($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereUserId($value)
     * @mixin \Eloquent
     */
    class ExameTreinamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Examesesmt
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int $exame_funcionario_id
     * @property int $empresa_id
     * @property bool $exame_realizado
     * @property array|null $resultado
     * @property string|null $data_realizacao
     * @property string|null $data_vencimento
     * @property bool $vencido
     * @property bool $atual
     * @property int $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExameFuncionario> $ExameFuncionario
     * @property-read int|null $exame_funcionario_count
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt query()
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereAtual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereDataRealizacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereDataVencimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereExameFuncionarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereExameRealizado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereResultado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereVencido($value)
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @mixin \Eloquent
     */
    class Examesesmt extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Exportacao
     *
     * @property int $id
     * @property int $user_id
     * @property string $arquivo
     * @property string $local
     * @property bool $removido
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $data_hora_criacao
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereArquivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereLocal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereRemovido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Exportacao whereUserId($value)
     * @mixin \Eloquent
     */
    class Exportacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeedbackCurriculo
     *
     * @property int $curriculo_id
     * @property int $id
     * @property string|null $selecionado
     * @property int|null $vaga_id
     * @property int|null $usuario_entrevista_marcado
     * @property int|null $cliente_id
     * @property bool|null $contato_realizado
     * @property bool|null $interesse
     * @property string|null $data_entrevista
     * @property string|null $local_entrevista
     * @property int|null $telefone_id
     * @property string|null $obs
     * @property string|null $created_at
     * @property string|null $updated_at
     * @property string|null $status
     * @property bool|null $envia_mail_provas
     * @property string|null $data_envia_mail_provas
     * @property int|null $user_envia_mail_provas
     * @property bool|null $envia_mail_proxima_etapa
     * @property string|null $data_envia_mail_proxima_etapa
     * @property int|null $user_envia_mail_proxima_etapa
     * @property bool|null $envia_mail_desclassificacao
     * @property string|null $data_envia_mail_desclassificacao
     * @property int|null $user_envia_mail_desclassificacao
     * @property bool|null $envia_whatsapp
     * @property string|null $data_envia_whatsapp
     * @property int|null $user_envia_whatsapp
     * @property int|null $empresa_id
     * @property int|null $vagas_abertas_id
     * @property int|null $vaga_projeto_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $AcordoHora
     * @property-read int|null $acordo_hora_count
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Afastamento> $Afastamentos
     * @property-read int|null $afastamentos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ArquivamentoDossie
     * @property-read int|null $arquivamento_dossie_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ArquivamentoEletronico
     * @property-read int|null $arquivamento_eletronico_count
     * @property-read \App\Models\AvaliacaoNoventaVencimento|null $AvaliacaoNoventaVencimento
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $AvisoFerias
     * @property-read int|null $aviso_ferias_count
     * @property-read \App\Models\UsuarioConta|null $BancoConta
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $BookRescisao
     * @property-read int|null $book_rescisao_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CartoesPonto
     * @property-read int|null $cartoes_ponto_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $CertificadoTreinSeg
     * @property-read int|null $certificado_trein_seg_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CertificadoNr> $CertificadosNr
     * @property-read int|null $certificados_nr_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ChaveFgts
     * @property-read int|null $chave_fgts_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cih> $Cih
     * @property-read int|null $cih_count
     * @property-read \App\Models\ClassificacaoRescisaoCurriculo|null $ClassificacaoRescisao
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ComprovanteDevCtp
     * @property-read int|null $comprovante_dev_ctp_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ComprovanteDevolucaoCtps
     * @property-read int|null $comprovante_devolucao_ctps_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ComprovantePagamento
     * @property-read int|null $comprovante_pagamento_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ContraChequeMensais
     * @property-read int|null $contra_cheque_mensais_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ContratoTrabalhoAssinado
     * @property-read int|null $contrato_trabalho_assinado_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ControleAsos
     * @property-read int|null $controle_asos_count
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CursoFormacaoRH> $CursosFormacoes
     * @property-read int|null $cursos_formacoes_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $DeclaracaoDependentesImposto
     * @property-read int|null $declaracao_dependentes_imposto_count
     * @property-read \App\Models\Demissao|null $Demissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $DocChecklist
     * @property-read int|null $doc_checklist_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $DocSelecao
     * @property-read int|null $doc_selecao_count
     * @property-read \App\Models\Cliente|null $Empresa
     * @property-read \App\Models\EntrevistaDesligamento|null $EntrevistaDesligamento
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Etapas> $EtapaStatus
     * @property-read int|null $etapa_status_count
     * @property-read \App\Models\ExameTreinamento|null $Exame
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ExameDemissional
     * @property-read int|null $exame_demissional_count
     * @property-read \App\Models\ExameFuncionario|null $ExamesFuncionario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FichaEntregaEpi
     * @property-read int|null $ficha_entrega_epi_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FichaRegistrada
     * @property-read int|null $ficha_registrada_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $GuiaSeguroDesemprego
     * @property-read int|null $guia_seguro_desemprego_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedidaAdministrativa> $MedidasAdministrativas
     * @property-read int|null $medidas_administrativas_count
     * @property-read \App\Models\MotivoRescisaoCurriculo|null $MotivoRescisao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $NadaConstaFichaEpi
     * @property-read int|null $nada_consta_ficha_epi_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $OrdemServicoAssinada
     * @property-read int|null $ordem_servico_assinada_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $PlanoSaudeAssinado
     * @property-read int|null $plano_saude_assinado_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $PppAssinado
     * @property-read int|null $ppp_assinado_count
     * @property-read \App\Models\User|null $QuemMarcou
     * @property-read \App\Models\ResultadoIntegrado|null $ResultadoIntegrado
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $SalarioFamiliaAssinado
     * @property-read int|null $salario_familia_assinado_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Examesesmt> $Sesmt
     * @property-read int|null $sesmt_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoCandidato> $Simulados
     * @property-read int|null $simulados_count
     * @property-read \App\Models\TelefoneCurriculo|null $TelPrincipal
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $TermoConfiabilidade
     * @property-read int|null $termo_confiabilidade_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $TermoRescisao
     * @property-read int|null $termo_rescisao_count
     * @property-read \App\Models\TipoAvisoCurriculo|null $TipoAviso
     * @property-read \App\Models\Treinamento|null $Treinamento
     * @property-read \App\Models\Examesesmt|null $UltimoAso
     * @property-read \App\Models\VagasAbertas|null $VagaAberta
     * @property-read \App\Models\VagaProjetoFeedback|null $VagaProjeto
     * @property-read \App\Models\Vaga|null $VagaSelecionada
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ValeTransporteAssinado
     * @property-read int|null $vale_transporte_assinado_count
     * @property-read \App\Models\Vinculo|null $Vinculo
     * @property-read \App\Models\NotificacaoWhats|null $WhatsAppNotificacao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \App\Models\EntrevistaRh|null $entrevistaRh
     * @property-read \App\Models\GestorRh|null $gestorRh
     * @property mixed $datalido
     * @property-read mixed $f_c_token
     * @property-read mixed $vaga_aberta_municipio
     * @property-read \App\Models\IndividualRh|null $individualRh
     * @property-read \App\Models\ParecerRh|null $parecerRh
     * @property-read \App\Models\ParecerRota|null $parecerRota
     * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
     * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo admitidos()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo demitidos()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereContatoRealizado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailDesclassificacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailProvas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailProximaEtapa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaWhatsapp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailDesclassificacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailProvas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailProximaEtapa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaWhatsapp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereInteresse($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereLocalEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereSelecionado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereTelefoneId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailDesclassificacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailProvas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailProximaEtapa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaWhatsapp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUsuarioEntrevistaMarcado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereVagaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereVagaProjetoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereVagasAbertasId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo withoutTrashed()
     * @property-read \App\Models\Examesesmt|null $AsoAdmissional
     * @property-read \App\Models\TelefoneCurriculo|null $telCadPrincipal
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo filtrarPorCnpjECentroCusto($request)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo filtrarPorNome($dados)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo filtrarPorTipoExame($dados)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo filtrarPorUltimoAso($dados)
     * @mixin \Eloquent
     */
    class FeedbackCurriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeedbackHistorico
     *
     * @property int $id
     * @property int $feedback_id
     * @property string $situacao
     * @property string $descricao
     * @property string $compromisso
     * @property string $data
     * @property int $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo $Feedback
     * @property-read \App\Models\User $UsuarioRelator
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static Builder|FeedbackHistorico newModelQuery()
     * @method static Builder|FeedbackHistorico newQuery()
     * @method static Builder|FeedbackHistorico query()
     * @method static Builder|FeedbackHistorico whereCompromisso($value)
     * @method static Builder|FeedbackHistorico whereCreatedAt($value)
     * @method static Builder|FeedbackHistorico whereData($value)
     * @method static Builder|FeedbackHistorico whereDescricao($value)
     * @method static Builder|FeedbackHistorico whereFeedbackId($value)
     * @method static Builder|FeedbackHistorico whereId($value)
     * @method static Builder|FeedbackHistorico whereSituacao($value)
     * @method static Builder|FeedbackHistorico whereUpdatedAt($value)
     * @method static Builder|FeedbackHistorico whereUserId($value)
     * @mixin \Eloquent
     */
    class FeedbackHistorico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeedbackPreadmissao
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $user_finalizou_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo $Feedback
     * @property-read \App\Models\User $UserFinalizou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereUserFinalizouId($value)
     * @mixin \Eloquent
     */
    class FeedbackPreadmissao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Feriado
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $descricao
     * @property \Illuminate\Support\Carbon $data
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado query()
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereId($value)
     * @mixin \Eloquent
     */
    class Feriado extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Ferias
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $admissao_id
     * @property int $periodo_aquisitivo_id
     * @property \Illuminate\Support\Carbon $data_saida
     * @property \Illuminate\Support\Carbon $data_retorno
     * @property \Illuminate\Support\Carbon $ultima_data
     * @property int|null $qnt_dias
     * @property int|null $dias_saldo
     * @property bool $tem_faltas
     * @property int|null $qnt_faltas
     * @property int $solicitante_id
     * @property string|null $obs_solicitante
     * @property \Illuminate\Support\Carbon $data_solicitacao
     * @property int|null $gestor_id
     * @property int|null $gestor_aprovacao_id
     * @property string|null $obs_gestor
     * @property string|null $status_aprovacao_gestor
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_gestor
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property string|null $status_ferias
     * @property \Illuminate\Support\Carbon|null $data_status_ferias
     * @property int|null $ferias_prevista_id
     * @property bool $aprovado_via_script
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $quem_deletou_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property bool $abono_pecuniario
     * @property bool $adiantamento_decimo_terceiro
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read User|null $Empresa
     * @property-read \App\Models\FeriasPrevista|null $FeriasPrevista
     * @property-read User|null $Gestor
     * @property-read User|null $GestorAprovacao
     * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
     * @property-read User|null $RhAprovacao
     * @property-read User|null $Solicitante
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias query()
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAbonoPecuniario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAdiantamentoDecimoTerceiro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataAprovacaoGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataRetorno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataSolicitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataStatusFerias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDiasSaldo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereFeriasPrevistaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereGestorAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias wherePeriodoAquisitivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQntDias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQntFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereSolicitanteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusFerias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereTemFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereUltimaData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|Ferias withoutTrashed()
     * @mixin \Eloquent
     */
    class Ferias extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasAdquiridas
     *
     * @property int $id
     * @property int $admissao_id
     * @property string $periodo_gozado
     * @property int $qnt_dias
     * @property string $data_saida
     * @property string $data_retorno
     * @property string $proximo_periodo
     * @property string $data_limite
     * @property int $user_cadastrou_id
     * @property int|null $user_alterou_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $status
     * @property int|null $ferias_prevista_id
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \App\Models\Curriculo|null $Colaborador
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\FeriasPrevista|null $FeriasPrevista
     * @property-read \App\Models\User|null $UsuarioCadastrou
     * @property-read \App\Models\User|null $UsuarioEditou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataLimite($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataRetorno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereFeriasPrevistaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas wherePeriodoGozado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereProximoPeriodo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereQntDias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUserAlterouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUserCadastrouId($value)
     * @mixin \Eloquent
     */
    class FeriasAdquiridas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasCalculoAvos
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $admissao_id
     * @property int $periodo_aquisitivo_id
     * @property float $total_avos
     * @property array|null $historico
     * @property bool $atualizado_via_script
     * @property \Illuminate\Support\Carbon $ultima_atualizacao
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \App\Models\User|null $Empresa
     * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-write mixed $ultima_atualiazao
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereAtualizadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereHistorico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos wherePeriodoAquisitivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereTotalAvos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasCalculoAvos whereUltimaAtualizacao($value)
     * @mixin \Eloquent
     */
    class FeriasCalculoAvos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $quem_cadastrou
     * @property int $ano
     * @property bool|null $comprada
     * @property int|null $dias_comprados
     * @property \Illuminate\Support\Carbon|null $data_inicio
     * @property \Illuminate\Support\Carbon|null $data_fim
     * @property float $valor
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $valor_format
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereAno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereComprada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereDiasComprados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereQuemCadastrou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasFeedback whereValor($value)
     * @mixin \Eloquent
     */
    class FeriasFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int $colaborador_id
     * @property int $centro_custo_id
     * @property \Illuminate\Support\Carbon $data_saida
     * @property int $qnt_dias
     * @property \Illuminate\Support\Carbon $data_retorno
     * @property int $dias_saldo
     * @property int|null $user_id
     * @property string|null $solicitante
     * @property string|null $status
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property bool $tem_faltas
     * @property int|null $qnt_faltas
     * @property int|null $user_rh_id
     * @property string|null $resposta_rh
     * @property string|null $obs_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property string|null $periodo_aquisitivo
     * @property \Illuminate\Support\Carbon|null $ultima_data
     * @property string|null $mes
     * @property int|null $periodo_aquisitivo_id
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\Curriculo|null $Colaborador
     * @property-read User|null $Empresa
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read User|null $GestorAprovacao
     * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
     * @property-read User|null $QuemAprovou
     * @property-read User|null $RhAprovacao
     * @property-read User|null $UserCadastrou
     * @property-read User|null $AprovacaoExtra
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataRetorno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDiasSaldo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereMes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista wherePeriodoAquisitivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista wherePeriodoAquisitivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntDias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereRespostaRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereTemFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUltimaData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserRhId($value)
     * @mixin \Eloquent
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatusAprovacaoExtra($value)
     */
    class FeriasPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasPrevistaDados
     *
     * @property int $id
     * @property int $ferias_prevista_id referencia ao colaborador HASONE
     * @property int|null $centro_custo_id
     * @property int|null $solicitante_id
     * @property \Illuminate\Support\Carbon|null $data_saida
     * @property int|null $qnt_dias
     * @property \Illuminate\Support\Carbon|null $data_retorno
     * @property int|null $dias_saldo
     * @property string|null $status
     * @property string|null $obs
     * @property int|null $periodo_aquisitivo_id
     * @property string|null $ultima_data
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property bool $tem_faltas
     * @property int|null $qnt_faltas
     * @property int|null $user_rh_id
     * @property string|null $resposta_rh
     * @property string|null $obs_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property int|null $gestor_id
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\FeriasPrevistaMov|null $FeriasPrevistaMov
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
     * @property-read \App\Models\User|null $QuemAprovou
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataRetorno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDiasSaldo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereFeriasPrevistaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados wherePeriodoAquisitivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereQntDias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereQntFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereRespostaRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereSolicitanteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereTemFaltas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUltimaData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUserRhId($value)
     * @mixin \Eloquent
     */
    class FeriasPrevistaDados extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FeriasPrevistaMov
     *
     * @property int $id
     * @property int $colaborador_id
     * @property int $dias_saldo
     * @property int|null $empresa_id
     * @property int|null $ultimo_periodo_aquisitivo_id
     * @property string|null $ultima_data
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Curriculo|null $Colaborador
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeriasPrevistaDados> $FeriasPrevistaDados
     * @property-read int|null $ferias_prevista_dados_count
     * @property-read \App\Models\FeriasPrevistaDados|null $FeriasPrevistaDadosUltimo
     * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov query()
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereDiasSaldo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUltimaData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUltimoPeriodoAquisitivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class FeriasPrevistaMov extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FormaContrato
     *
     * @property int $id
     * @property string $titulo
     * @property bool $ativo
     * @property int $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato query()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaContrato whereTitulo($value)
     * @mixin \Eloquent
     */
    class FormaContrato extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FormaPagamento
     *
     * @property int $id
     * @property string $descricao
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormaPagamento whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class FormaPagamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Formulario
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $titulo
     * @property string|null $descricao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SetoresFormulario> $Setores
     * @property-read int|null $setores_count
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario query()
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereTitulo($value)
     * @mixin \Eloquent
     */
    class Formulario extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FormularioAvaliacaoAnual
     *
     * @property int $id
     * @property string $pergunta
     * @property int $topicos_id
     * @property-read \App\Models\Topicos|null $Topicos
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual query()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual wherePergunta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual whereTopicosId($value)
     * @mixin \Eloquent
     */
    class FormularioAvaliacaoAnual extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FormularioResposta
     *
     * @property int $id
     * @property int $formulario_id
     * @property int $user_id
     * @property array $respostas
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Formulario|null $Formulario
     * @property-read \App\Models\User|null $Usuario
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta query()
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereRespostas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FormularioResposta whereUserId($value)
     * @mixin \Eloquent
     */
    class FormularioResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Fornecedor
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $tipo Fornecedor, Terceiro, Parceiro
     * @property string|null $cnpj
     * @property string|null $cpf
     * @property string|null $nome
     * @property string $tipo_pessoa
     * @property string|null $razao_social
     * @property string|null $nome_fantasia
     * @property string|null $cep
     * @property string|null $logradouro
     * @property string|null $numero
     * @property string|null $complemento
     * @property string|null $bairro
     * @property string|null $municipio
     * @property string|null $uf
     * @property string|null $contato
     * @property string|null $email
     * @property string|null $aniversario
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FornecedorServico> $Servicos
     * @property-read int|null $servicos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UsuarioTelefone> $Telefones
     * @property-read int|null $telefones_count
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $endereco_completo
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor query()
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereAniversario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereBairro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCep($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCnpj($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereComplemento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereContato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCpf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereLogradouro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereMunicipio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNomeFantasia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereRazaoSocial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereTipoPessoa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereUf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Fornecedor whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Fornecedor extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\FornecedorServico
     *
     * @property int $id
     * @property int $fornecedor_id
     * @property int|null $tipo_servico_fornecedor_id
     * @property string|null $vencimento quando for utilizado para fornecedor
     * @property \Illuminate\Support\Carbon|null $data_inicio
     * @property \Illuminate\Support\Carbon|null $data_encerramento
     * @property string|null $escopo
     * @property string|null $valor
     * @property string|null $tipo_faturamento
     * @property string|null $status
     * @property string|null $feedback
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\TipoServico|null $TipoServico
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico query()
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereDataEncerramento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereEscopo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereFornecedorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereTipoFaturamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereTipoServicoFornecedorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereValor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereVencimento($value)
     * @mixin \Eloquent
     */
    class FornecedorServico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Galeria
     *
     * @property int $id
     * @property string $titulo
     * @property string|null $descricao
     * @property int|null $ordem
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Fotos
     * @property-read int|null $fotos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria query()
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Galeria extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\GestorRh
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $parecer
     * @property string|null $indicado_para
     * @property int|null $nota
     * @property string|null $entrevistado_por
     * @property int|null $user_id
     * @property string|null $comentario
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $formulario_id
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh query()
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereComentario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereEntrevistadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereIndicadoPara($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereParecer($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GestorRh whereUserId($value)
     * @mixin \Eloquent
     */
    class GestorRh extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\GrupoCloud
     *
     * @property int $id
     * @property string $nome
     * @property string $descricao
     * @property bool $ativo
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Users
     * @property-read int|null $users_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Usuarios
     * @property-read int|null $usuarios_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HabilidadeCloud> $habilidades
     * @property-read int|null $habilidades_count
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud query()
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereNome($value)
     * @mixin \Eloquent
     */
    class GrupoCloud extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\GruposChat
     *
     * @property int $id
     * @property string $nome
     * @property int $empresa_id
     * @property int $criou_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat query()
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereCriouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class GruposChat extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Habilidade
     *
     * @property int $id
     * @property string $nome
     * @property string $descricao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Papel> $papeis
     * @property-read int|null $papeis_count
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade query()
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Habilidade whereNome($value)
     * @mixin \Eloquent
     */
    class Habilidade extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\HabilidadeCloud
     *
     * @property int $id
     * @property string $nome
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GrupoCloud> $grupo
     * @property-read int|null $grupo_count
     * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud query()
     * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|HabilidadeCloud whereNome($value)
     * @mixin \Eloquent
     */
    class HabilidadeCloud extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\HorarioAcesso
     *
     * @property int $id
     * @property mixed $abertura
     * @property mixed $fechamento
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso query()
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereAbertura($value)
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereFechamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|HorarioAcesso whereId($value)
     * @mixin \Eloquent
     */
    class HorarioAcesso extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\IndividualRh
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $parecer
     * @property int|null $nota
     * @property string|null $entrevistado_por
     * @property int|null $user_id
     * @property string|null $comentario
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $avaliacao_psicologica
     * @property int|null $formulario_id
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh query()
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereAvaliacaoPsicologica($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereComentario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereEntrevistadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereParecer($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IndividualRh whereUserId($value)
     * @mixin \Eloquent
     */
    class IndividualRh extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Instrutor
     *
     * @property int $id
     * @property string $nome
     * @property int|null $arquivo_id
     * @property string|null $assinatura
     * @property string|null $cargo
     * @property string|null $registro
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor query()
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereArquivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereAssinatura($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereCargo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereRegistro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Instrutor extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Intermitente
     *
     * @property int $id
     * @property int $feedback_id somente quem foi admitido
     * @property int|null $cliente_id Cliente empresa
     * @property int $user_lancamento_id Responsavel pelo lançamenro usuario em sessão
     * @property int|null $area_id
     * @property int|null $tipo_id
     * @property string|null $obs_lancamento Responsavel pela aprovação usuario em sessão
     * @property string $data_lancamento
     * @property string $encerramento_previsto
     * @property string|null $acao
     * @property int|null $user_aprovacao_id Responsavel pela aprovação usuario em sessão
     * @property string|null $obs_aprovacao Responsavel pela aprovação usuario em sessão
     * @property string|null $data_aprovacao
     * @property string|null $status aberto, aprovado
     * @property bool|null $devolve_epi
     * @property bool|null $devolve_cracha
     * @property Carbon|null $created_at
     * @property Carbon|null $updated_at
     * @property int $empresa_id
     * @property string $hash_colaborador
     * @property string|null $resposta_colaborador
     * @property string|null $data_resposta_colaborador
     * @property int|null $centro_custo_id
     * @property int|null $prazo_resposta
     * @property string|null $prazo_resposta_expiracao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\AreaEtiqueta|null $Area
     * @property-read \App\Models\CentroCusto|null $CentroDeCusto
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\FeedbackCurriculo|null $Colaborador
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IntermitenteProrrogacao> $Prorrogacao
     * @property-read int|null $prorrogacao_count
     * @property-read User|null $ResponsavelAprovacao
     * @property-read User|null $ResponsavelLancamento
     * @property-read \App\Models\IntermitenteTipo|null $Tipo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente query()
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereAreaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDataLancamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDataRespostaColaborador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDevolveCracha($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDevolveEpi($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereEncerramentoPrevisto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereHashColaborador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereObsLancamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente wherePrazoResposta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente wherePrazoRespostaExpiracao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereRespostaColaborador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereTipoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereUserLancamentoId($value)
     * @mixin \Eloquent
     */
    class Intermitente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\IntermitenteFixoPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int $colaborador_id
     * @property int $centro_custo_id
     * @property int|null $cargo_anterior_id
     * @property float|null $salario_anterior
     * @property int|null $novo_cargo_id
     * @property float|null $novo_salario
     * @property int|null $user_id
     * @property string|null $data_modificacao
     * @property string|null $autorizado_por
     * @property string|null $motivos
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property bool|null $filial
     * @property int|null $centro_custo_filial_id
     * @property int|null $anterior_vaga_aberta_id
     * @property int|null $nova_vaga_aberta_id
     * @property int|null $area_etiqueta_id
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property bool $aprovado_via_script
     * @property int|null $quem_deletou_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\AreaEtiqueta|null $AreaEtiqueta
     * @property-read \App\Models\Vaga|null $CargoAnterior
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $Colaborador
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\Vaga|null $NovoCargo
     * @property-read \App\Models\User|null $QuemDeletou
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\User|null $Solicitante
     * @property-read \App\Models\User|null $UserAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @property-read \App\Models\VagasAbertas|null $VagaAbertaAnterior
     * @property-read \App\Models\VagasAbertas|null $VagaAbertaNova
     * @property-read mixed $novo_salario_format
     * @property-read mixed $salario_anterior_format
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAnteriorVagaAbertaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAreaEtiquetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAutorizadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCargoAnteriorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataModificacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereMotivos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereNovaVagaAbertaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereNovoCargoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereNovoSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereSalarioAnterior($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista withoutTrashed()
     * @mixin \Eloquent
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property-read \App\Models\User|null $AprovacaoExtra
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereStatusAprovacaoExtra($value)
     */
    class IntermitenteFixoPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\IntermitenteProrrogacao
     *
     * @property int $id
     * @property int $intermitente_id
     * @property string $data_inicio
     * @property string $data_fim
     * @property string $solicitante
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereIntermitenteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class IntermitenteProrrogacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\IntermitenteTipo
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class IntermitenteTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ItensCloud
     *
     * @property int $id
     * @property int $cloud_id
     * @property int|null $arquivo_id
     * @property string $label
     * @property string $tipo
     * @property int|null $pertence
     * @property int $quem_criou
     * @property bool $aprovado
     * @property int|null $quem_aprovou
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property bool $revisado
     * @property int|null $quem_revisou
     * @property \Illuminate\Support\Carbon|null $data_revisao
     * @property int|null $quem_editou
     * @property int|null $quem_excluiu
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property bool $movido
     * @property int|null $quem_moveu
     * @property \Illuminate\Support\Carbon|null $data_movido
     * @property int|null $pertence_anterior
     * @property-read \App\Models\User|null $Aprovou
     * @property-read \App\Models\Arquivo|null $Arquivo
     * @property-read \App\Models\Cloud|null $Cloud
     * @property-read \App\Models\User|null $Criou
     * @property-read \App\Models\User|null $Editou
     * @property-read \App\Models\User|null $Excluiu
     * @property-read \Illuminate\Database\Eloquent\Collection<int, ItensCloud> $Itens
     * @property-read int|null $itens_count
     * @property-read \App\Models\User|null $Moveu
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GrupoCloud> $Permissoes
     * @property-read int|null $permissoes_count
     * @property-read ItensCloud|null $Pertence
     * @property-read ItensCloud|null $PertenceAntes
     * @property-read \App\Models\User|null $Revisou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $tem_permissao
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud query()
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereAprovado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereArquivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereCloudId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataMovido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDataRevisao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereMovido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud wherePertence($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud wherePertenceAnterior($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemAprovou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemCriou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemEditou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemExcluiu($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemMoveu($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereQuemRevisou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereRevisado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|ItensCloud withoutTrashed()
     * @mixin \Eloquent
     */
    class ItensCloud extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Lancamento
     *
     * @property int $id
     * @property int $quem_cadastrou
     * @property int|null $quem_alterou
     * @property int $plano_id
     * @property string|null $descricao
     * @property float $valor
     * @property float $saldo
     * @property string $operacao
     * @property \Illuminate\Support\Carbon $data_hora
     * @property \Illuminate\Support\Carbon|null $data_pendente quando vai receber ou pagar
     * @property \Illuminate\Support\Carbon|null $data_hora_concluido quando recebeu ou pagou
     * @property bool $concluido
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int $empresa_id
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LancamentoForma> $Formas
     * @property-read int|null $formas_count
     * @property-read \App\Models\PlanoConta|null $PlanoConta
     * @property-read \App\Models\User|null $QuemAlterou
     * @property-read \App\Models\User|null $QuemCadastrou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $credito
     * @property-read mixed $debito
     * @property-read mixed $dias_atraso
     * @property-read mixed $dias_atraso_concluido
     * @property-read mixed $operacao_text
     * @property-read mixed $saldo_format
     * @property-read mixed $valor_format
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereConcluido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataHora($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataHoraConcluido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDataPendente($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereOperacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento wherePlanoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereQuemAlterou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereQuemCadastrou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereSaldo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Lancamento whereValor($value)
     * @mixin \Eloquent
     */
    class Lancamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\LancamentoForma
     *
     * @property int $id
     * @property int $lancamento_id
     * @property int $forma_pagamento_id
     * @property float $valor
     * @property string|null $observacoes
     * @property-read \App\Models\FormaPagamento|null $FormaPagamento
     * @property-read \App\Models\Lancamento $Lancamento
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $valor_format
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma query()
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereFormaPagamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereLancamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereObservacoes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LancamentoForma whereValor($value)
     * @mixin \Eloquent
     */
    class LancamentoForma extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ListaTarefa
     *
     * @property int $id
     * @property int $quadro_id
     * @property int $user_id
     * @property string $titulo
     * @property int $ordem
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Quadro|null $Quadro
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tarefa> $Tarefas
     * @property-read int|null $tarefas_count
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa query()
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereQuadroId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ListaTarefa whereUserId($value)
     * @mixin \Eloquent
     */
    class ListaTarefa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\LogHistorico
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $empresa_id
     * @property string $acao
     * @property int $user_id
     * @property string $data
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico query()
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereUserId($value)
     * @mixin \Eloquent
     */
    class LogHistorico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\LogTarefa
     *
     * @property int $id
     * @property int $tarefa_id
     * @property int $lista_anterior
     * @property int $lista_atual
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\ListaTarefa|null $ListaAnterior
     * @property-read \App\Models\ListaTarefa|null $ListaAtual
     * @property-read \App\Models\Tarefa|null $Tarefa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa query()
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereListaAnterior($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereListaAtual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereTarefaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogTarefa whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class LogTarefa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\LogWeekly
     *
     * @property int $id
     * @property int $quadro_id
     * @property int|null $tarefa_id
     * @property int $user_id
     * @property string $descricao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Quadro|null $Quadro
     * @property-read \App\Models\User|null $Usuario
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly query()
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereQuadroId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereTarefaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogWeekly whereUserId($value)
     * @mixin \Eloquent
     */
    class LogWeekly extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\LogoCliente
     *
     * @property int $id
     * @property string $nome
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Fotos
     * @property-read int|null $fotos_count
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente query()
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class LogoCliente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ManutencaoProgramada
     *
     * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada query()
     * @mixin \Eloquent
     */
    class ManutencaoProgramada extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MedidaAdministrativa
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $user_id
     * @property string|null $solicitante
     * @property string $tipo
     * @property string|null $definicao
     * @property string|null $motivo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $causa
     * @property string $data_solicitacao
     * @property string|null $data_retorno
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $tipo_medida
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa query()
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereCausa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDataRetorno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDataSolicitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDefinicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereMotivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereUserId($value)
     * @mixin \Eloquent
     * @property int|null $quem_deletou_id
     * @property string|null $deleted_at
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereQuemDeletouId($value)
     */
    class MedidaAdministrativa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MensagemChat
     *
     * @property int $id
     * @property int $de_id
     * @property int|null $para_id
     * @property int|null $grupo_id
     * @property string $tipo
     * @property string|null $mensagem
     * @property int|null $arquivo_id
     * @property bool $visto
     * @property \Illuminate\Support\Carbon|null $datahora_visto
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User|null $De
     * @property-read \App\Models\GruposChat|null $Grupo
     * @property-read \App\Models\User|null $Para
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat query()
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereArquivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereDatahoraVisto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereDeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereGrupoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereMensagem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereParaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereVisto($value)
     * @mixin \Eloquent
     */
    class MensagemChat extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MetasFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property string $nome
     * @property string $descricao
     * @property \Illuminate\Support\Carbon $data_inicio
     * @property \Illuminate\Support\Carbon $data_fim
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class MetasFeedback extends \Eloquent {}
}

namespace App\Models\Models{
    /**
     * App\Models\Models\PontoEletronico
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $funcionario_id
     * @property int|null $jornada_id
     * @property int $ocorrencia_id
     * @property int $duracao
     * @property int|null $duracao_normal
     * @property int|null $duracao_extra
     * @property int|null $duracao_noturna
     * @property string $tipo_frequencia
     * @property int $tempo_limite_falta
     * @property int $tempo_limite_saida
     * @property int $limite_tolerancia
     * @property string|null $justificativa
     * @property int $verificado
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read EmpresaEscala|null $Escala
     * @property-read User|null $Funcionario
     * @property-read EscalaJornada|null $Jornada
     * @property-read OcorrenciaJornada|null $OcorrenciaJornada
     * @property-read PeriodoJornada|null $Periodo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico query()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNormal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNoturna($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFuncionarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJornadaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJustificativa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLimiteTolerancia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteFalta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTipoFrequencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereVerificado($value)
     * @mixin \Eloquent
     */
    class PontoEletronico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MotivoRescisao
     *
     * @property int $id
     * @property string $descricao
     * @property bool $ativo
     * @property string|null $nome_pdf
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao query()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereNomePdf($value)
     * @mixin \Eloquent
     */
    class MotivoRescisao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MotivoRescisaoCurriculo
     *
     * @property int $motivo_id
     * @property int|null $feedback_id
     * @property string|null $outro
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereMotivoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereOutro($value)
     * @mixin \Eloquent
     */
    class MotivoRescisaoCurriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MudaCargoPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int $colaborador_id
     * @property int $centro_custo_id
     * @property int|null $cargo_anterior_id
     * @property float|null $salario_anterior
     * @property int|null $novo_cargo_id
     * @property float|null $novo_salario
     * @property int|null $user_id
     * @property string|null $autorizado_por
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Vaga|null $CargoAnterior
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $Colaborador
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\Vaga|null $NovoCargo
     * @property-read \App\Models\User|null $UserAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @property-read mixed $novo_salario_format
     * @property-read mixed $salario_anterior_format
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereAutorizadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCargoAnteriorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoCargoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereSalarioAnterior($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUserId($value)
     * @mixin \Eloquent
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property-read \App\Models\User|null $AprovacaoExtra
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereStatusAprovacaoExtra($value)
     */
    class MudaCargoPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\MudancaCargo
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $admissao_id
     * @property int $colaborador_id
     * @property bool $mantem_centro_custo
     * @property int|null $anterior_centro_custo_id
     * @property bool|null $anterior_filial
     * @property int|null $anterior_centro_custo_filial_id
     * @property int|null $novo_centro_custo_id
     * @property bool|null $novo_filial
     * @property int|null $novo_centro_custo_filial_id
     * @property bool $mantem_cargo
     * @property int|null $anterior_vaga_aberta_id
     * @property int|null $nova_vaga_aberta_id
     * @property bool|null $mantem_funcao
     * @property string|null $anterior_funcao
     * @property string|null $nova_funcao
     * @property bool $mantem_salario
     * @property float $anterior_salario
     * @property float $novo_salario
     * @property int $solicitante_id
     * @property string|null $obs_solicitante
     * @property string $data_solicitacao
     * @property int|null $gestor_id
     * @property int|null $gestor_aprovacao_id
     * @property string|null $obs_gestor_aprovacao
     * @property string|null $status_aprovacao_gestor
     * @property string|null $data_aprovacao_gestor
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property string|null $data_aprovacao_rh
     * @property bool $aprovado_via_script
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $quem_deletou_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\CentroCusto|null $CentroCustoAnterior
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilialAnterior
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilialNovo
     * @property-read \App\Models\CentroCusto|null $CentroCustoNovo
     * @property-read \App\Models\User|null $Colaborador
     * @property-read \App\Models\Cliente|null $Empresa
     * @property-read \App\Models\User|null $Gestor
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\User|null $QuemDeletou
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\User|null $Solicitante
     * @property-read \App\Models\VagasAbertas|null $VagaAbertaAnterior
     * @property-read \App\Models\VagasAbertas|null $VagaAbertaNova
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo query()
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAdmissaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAnteriorVagaAbertaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereDataAprovacaoGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereDataSolicitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereGestorAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereMantemCargo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereMantemCentroCusto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereMantemFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereMantemSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovaFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovaVagaAbertaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovoCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovoCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovoFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereNovoSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereObsGestorAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereObsSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereSolicitanteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereStatusAprovacaoGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo withoutTrashed()
     * @mixin \Eloquent
     * @property int $treinamento_funcao
     * @property string|null $treinamento_data_inicio
     * @property string|null $treinamento_data_fim
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereTreinamentoDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereTreinamentoDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo whereTreinamentoFuncao($value)
     */
    class MudancaCargo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Municipio
     *
     * @property int $id
     * @property string $nome
     * @property string $uf
     * @property bool $capital
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagasAbertas> $VagasAbertas
     * @property-read int|null $vagas_abertas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio query()
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereCapital($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereUf($value)
     * @mixin \Eloquent
     */
    class Municipio extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Notificacao
     *
     * @property int $id
     * @property string $tipo
     * @property int $user_id
     * @property array $payload
     * @property bool $visto
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Notificacao whereVisto($value)
     * @mixin \Eloquent
     */
    class Notificacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\NotificacaoWhats
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int $vaga_id
     * @property int $etapa_id
     * @property int $messageid
     * @property int $user_id
     * @property string|null $mensagem
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats query()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereEtapaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereMensagem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereMessageid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhats whereVagaId($value)
     * @mixin \Eloquent
     */
    class NotificacaoWhats extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\NotificacaoWhatsapp
     *
     * @property int $id
     * @property int $user_id
     * @property string $telefone
     * @property int $messageid
     * @property int $enviado_id
     * @property string $mensagem
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User $EnviadoPor
     * @property-read \App\Models\User $User
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp query()
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereEnviadoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereMensagem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereMessageid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereTelefone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|NotificacaoWhatsapp whereUserId($value)
     * @mixin \Eloquent
     */
    class NotificacaoWhatsapp extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Ocorrencia
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int|null $usuario_id
     * @property int|null $setor_id
     * @property string $assunto
     * @property int $quem_criou
     * @property int|null $quem_atualizou
     * @property \Illuminate\Support\Carbon|null $datahora_finalizou
     * @property int|null $quem_finalizou
     * @property string $status
     * @property string $tipo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \App\Models\User|null $Atualizou
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $Criou
     * @property-read \App\Models\User|null $Finalizou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RespostaOcorrencia> $Respostas
     * @property-read int|null $respostas_count
     * @property-read \App\Models\OcorrenciaSetor|null $Setor
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $Tags
     * @property-read int|null $tags_count
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $status_andamento
     * @property-read mixed $status_finalizado
     * @property-read mixed $status_novo
     * @property-read mixed $status_text
     * @property-read mixed $tipo_anotacao
     * @property-read mixed $tipo_documentacao
     * @property-read mixed $tipo_problema
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia query()
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereAssunto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereDatahoraFinalizou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereQuemAtualizou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereQuemCriou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereQuemFinalizou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereSetorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereUsuarioId($value)
     * @mixin \Eloquent
     */
    class Ocorrencia extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\OcorrenciaJornada
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $descricao
     * @property bool $trabalhado
     * @property bool $conta_horas
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada query()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereContaHoras($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereTrabalhado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class OcorrenciaJornada extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\OcorrenciaSetor
     *
     * @property int $id
     * @property string $nome
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor query()
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaSetor whereNome($value)
     * @mixin \Eloquent
     */
    class OcorrenciaSetor extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\OpcaoAlternativa
     *
     * @property int $id
     * @property int $alternativa_id
     * @property string $label
     * @property bool $selecionado
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa query()
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereAlternativaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereSelecionado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|OpcaoAlternativa whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class OpcaoAlternativa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Papel
     *
     * @property int $id
     * @property string $nome
     * @property string $descricao
     * @property string|null $email
     * @property int|null $empresa_id
     * @property bool $ativo
     * @property bool|null $master
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Habilidade> $habilidades
     * @property-read int|null $habilidades_count
     * @method static \Illuminate\Database\Eloquent\Builder|Papel clinica()
     * @method static \Illuminate\Database\Eloquent\Builder|Papel newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Papel newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Papel notClinica()
     * @method static \Illuminate\Database\Eloquent\Builder|Papel query()
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereMaster($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Papel whereNome($value)
     * @mixin \Eloquent
     */
    class Papel extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ParabensEnviado
     *
     * @property int $id
     * @property int|null $curriculo_id
     * @property int|null $empresa_id
     * @property int $ano
     * @property string|null $status
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado query()
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereAno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereStatus($value)
     * @mixin \Eloquent
     */
    class ParabensEnviado extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ParecerEntrevistaTecnica
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property string|null $tempo_funcao
     * @property bool|null $trabalhou_alumar
     * @property bool|null $indicado
     * @property string|null $indicado_por
     * @property bool|null $rota
     * @property bool|null $ssma
     * @property string|null $ssma_ex
     * @property bool|null $roupa_pvc
     * @property string|null $roupa_pvc_ex
     * @property bool|null $roupa_pvc_dificuldade
     * @property bool|null $turno
     * @property bool|null $trabalhou_mecanico_manutencao
     * @property string|null $trabalhou_mecanico_manutencao_ex
     * @property bool|null $trabalhou_raquete_produto_quimico
     * @property string|null $trabalhou_raquete_produto_quimico_ex
     * @property string|null $tipos_de_talha
     * @property bool|null $fechamento_flange
     * @property string|null $fechamento_flange_ex
     * @property string|null $milimetros_polegada
     * @property bool|null $manuseio_macarico
     * @property string|null $manuseio_macarico_ex
     * @property bool|null $trocou_valvulas
     * @property string|null $trocou_valvulas_ex
     * @property string|null $ferramentas_elevacao_carga
     * @property string|null $opera_plat_movel
     * @property string|null $opera_plat_movel_ex
     * @property string|null $opera_plat_ponte
     * @property string|null $opera_plat_onte_ex
     * @property string|null $experiencia_cargas_rigger
     * @property string|null $experiencia_cargas_rigger_ex
     * @property bool|null $trabalhou_overhaul
     * @property string|null $trabalhou_overhaul_ex
     * @property bool|null $abertura_tubo_seis_polegada
     * @property bool|null $vareta_seis_polegada
     * @property bool|null $filete_acabemento
     * @property string|null $observacao
     * @property string|null $indicado_area
     * @property string|null $resultado_final
     * @property int|null $nota
     * @property int|null $entrevistado_por
     * @property string|null $quem_entrevistou
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $tipo_contratacao
     * @property string|null $texto_livre
     * @property string $tipo_entrevista
     * @property int|null $formulario_id
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @property-read \App\Models\User|null $QuemEntrevistou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $data_entrevista
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica query()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereAberturaTuboSeisPolegada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereEntrevistadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereExperienciaCargasRigger($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereExperienciaCargasRiggerEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFechamentoFlange($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFechamentoFlangeEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFerramentasElevacaoCarga($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFileteAcabemento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicadoArea($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereManuseioMacarico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereManuseioMacaricoEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereMilimetrosPolegada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatMovel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatMovelEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatOnteEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatPonte($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereQuemEntrevistou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereResultadoFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvc($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvcDificuldade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvcEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereSsma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereSsmaEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTempoFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTextoLivre($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTipoContratacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTipoEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTiposDeTalha($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouAlumar($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouMecanicoManutencao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouMecanicoManutencaoEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouOverhaul($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouOverhaulEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouRaqueteProdutoQuimico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouRaqueteProdutoQuimicoEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrocouValvulas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrocouValvulasEx($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTurno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereVaretaSeisPolegada($value)
     * @mixin \Eloquent
     */
    class ParecerEntrevistaTecnica extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ParecerRh
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property bool|null $cnh
     * @property bool|null $ex_funcionario
     * @property string|null $cnh_tipo
     * @property string|null $rota_bairro
     * @property int|null $calca
     * @property int|null $bota
     * @property int|null $camisa_protecao
     * @property string|null $camisa_meia
     * @property string|null $mora_com_quem
     * @property bool|null $casado
     * @property string|null $tempodeconvivencia
     * @property bool|null $filhos
     * @property string|null $qnt_filhos
     * @property bool|null $conjuge_trabalha
     * @property string|null $trabalho_conjuge
     * @property bool|null $religioso
     * @property string|null $religiao_praticante
     * @property bool|null $fuma
     * @property string|null $frequencia_fuma
     * @property bool|null $bebe
     * @property string|null $frequencia_bebe
     * @property bool|null $indicacao
     * @property string|null $indicado_por
     * @property bool|null $alumar_experiencia
     * @property string|null $alumar_experiencia_area
     * @property bool|null $outra_industria_experiencia
     * @property string|null $outra_industria_nome
     * @property string|null $grau_instrucao
     * @property bool|null $horaextra
     * @property bool|null $turnos_seis_por_dois
     * @property bool|null $noturno
     * @property bool|null $acidente_trabalho
     * @property string|null $acidente_trabalho_qual
     * @property bool|null $afastamento_inss
     * @property string|null $afastamento_inss_qual
     * @property string|null $situacao_saude
     * @property string|null $nr_dez
     * @property string|null $comportamento_seguro
     * @property string|null $energia_para_trabalho
     * @property string|null $postura
     * @property string|null $historico_profissional
     * @property string|null $historico_educacional
     * @property string|null $objetivos_expectativas
     * @property string|null $auto_imagem
     * @property int|null $competencias
     * @property int|null $comportamento_etico
     * @property int|null $comprometimento
     * @property int|null $comunicacao
     * @property int|null $cultura_qualidade
     * @property int|null $foco_cliente
     * @property int|null $iniciativa
     * @property int|null $orientacao_resultados
     * @property int|null $trabalho_equipe
     * @property string|null $parecer_final
     * @property string|null $parecer_final_um
     * @property int|null $nota
     * @property string|null $comentarios
     * @property int|null $entrevistador
     * @property string|null $quem_entrevistou
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $destro
     * @property string $tipo_entrevista
     * @property int|null $nota_digitacao
     * @property string|null $dinamicadegrupo
     * @property string|null $obs_dinamicadegrupo
     * @property bool|null $experiencia_callcenter
     * @property string|null $disponibilidade_horarios
     * @property bool|null $turnos_seis_por_um
     * @property string|null $horario_preferencial
     * @property string|null $obs_call
     * @property string|null $obs_horario
     * @property int|null $formulario_id
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CertificadoNr> $Nr
     * @property-read int|null $nr_count
     * @property-read \App\Models\User|null $QuemEntrevistou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \App\Models\EntrevistaRh|null $entrevistaRh
     * @property-read \App\Models\GestorRh|null $gestorRh
     * @property-read mixed $data_entrevista
     * @property-read \App\Models\IndividualRh|null $individualRh
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh query()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAcidenteTrabalho($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAcidenteTrabalhoQual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAfastamentoInss($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAfastamentoInssQual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAlumarExperiencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAlumarExperienciaArea($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAutoImagem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereBebe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereBota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCalca($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCamisaMeia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCamisaProtecao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCasado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCnh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCnhTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComentarios($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCompetencias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComportamentoEtico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComportamentoSeguro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComprometimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComunicacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereConjugeTrabalha($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCulturaQualidade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDestro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDinamicadegrupo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDisponibilidadeHorarios($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereEnergiaParaTrabalho($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereEntrevistador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereExFuncionario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereExperienciaCallcenter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFilhos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFocoCliente($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFrequenciaBebe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFrequenciaFuma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFuma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereGrauInstrucao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHistoricoEducacional($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHistoricoProfissional($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHoraextra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHorarioPreferencial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIndicacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIndicadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIniciativa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereMoraComQuem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNotaDigitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNoturno($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNrDez($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObjetivosExpectativas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsCall($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsDinamicadegrupo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsHorario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOrientacaoResultados($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOutraIndustriaExperiencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOutraIndustriaNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereParecerFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereParecerFinalUm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh wherePostura($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereQntFilhos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereQuemEntrevistou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereReligiaoPraticante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereReligioso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereRotaBairro($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereSituacaoSaude($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTempodeconvivencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTipoEntrevista($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTrabalhoConjuge($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTrabalhoEquipe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTurnosSeisPorDois($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTurnosSeisPorUm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ParecerRh extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ParecerRota
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property bool|null $tem_rota
     * @property string|null $qual
     * @property string|null $bairro_rota
     * @property string|null $ponto_referencia_rota
     * @property string|null $bairro_residencia
     * @property string|null $ponto_referencia_residencia
     * @property bool|null $pega_onibus
     * @property string|null $pega_onibus_qual_ponto
     * @property bool|null $vale_transporte
     * @property bool|null $rota_disponivel_turno_a
     * @property bool|null $rota_disponivel_turno_b
     * @property bool|null $rota_disponivel_turno_c
     * @property bool|null $rota_disponivel_turno_o
     * @property string|null $rota_disponivel_outros
     * @property bool|null $rota_atende
     * @property string|null $rota_tipo
     * @property int|null $aprovado_por
     * @property string|null $quem_entrevistou
     * @property string|null $observacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $formulario_id
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @property-read \App\Models\User|null $QuemAprovou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $data_entrevista
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota query()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereAprovadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereBairroResidencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereBairroRota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePegaOnibus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePegaOnibusQualPonto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePontoReferenciaResidencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota wherePontoReferenciaRota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereQual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereQuemEntrevistou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaAtende($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelOutros($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoA($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoB($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoC($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaDisponivelTurnoO($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereRotaTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereTemRota($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerRota whereValeTransporte($value)
     * @mixin \Eloquent
     */
    class ParecerRota extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ParecerTestePratico
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property bool|null $fez_teste
     * @property string|null $data_horario_realizacao
     * @property string|null $responsavel_pelo_teste
     * @property string|null $qual_teste
     * @property int|null $resultado_teste
     * @property int|null $nota_teste
     * @property string|null $parecer_final_teste
     * @property int|null $entrevistador
     * @property string|null $quem_entrevistou
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $formulario_id
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\User|null $Entrevistador
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $nota_teste_format
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico query()
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereDataHorarioRealizacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereEntrevistador($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFezTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereNotaTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereParecerFinalTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereQualTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereQuemEntrevistou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereResponsavelPeloTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereResultadoTeste($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ParecerTestePratico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Pcmso
     *
     * @property int $id
     * @property int $empresa_id
     * @property string $label
     * @property bool $ativo
     * @property-read \App\Models\Cliente $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso query()
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Pcmso whereLabel($value)
     * @mixin \Eloquent
     */
    class Pcmso extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PeriodoAquisitivo
     *
     * @property int $id
     * @property string $label
     * @property int $ano_inicial
     * @property int $ano_final
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo query()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoFinal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoInicial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereLabel($value)
     * @mixin \Eloquent
     */
    class PeriodoAquisitivo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PeriodoJornada
     *
     * @property int $id
     * @property int $jornada_id
     * @property \Illuminate\Support\Carbon $entrada
     * @property \Illuminate\Support\Carbon $saida
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\EscalaJornada|null $Jornada
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada query()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereJornadaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereUserDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada withoutTrashed()
     * @mixin \Eloquent
     */
    class PeriodoJornada extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PeriodoPontoEletronico
     *
     * @property int $id
     * @property int $ponto_id
     * @property string|null $autenticacao_entrada
     * @property \Illuminate\Support\Carbon $entrada
     * @property bool $facial_entrada
     * @property int|null $arquivo_id_entrada
     * @property float|null $lat_entrada
     * @property float|null $long_entrada
     * @property string|null $autenticacao_saida
     * @property \Illuminate\Support\Carbon|null $saida
     * @property bool|null $facial_saida
     * @property int|null $arquivo_id_saida
     * @property float|null $lat_saida
     * @property float|null $long_saida
     * @property int $minutos
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property int|null $user_deletou_id
     * @property-read \App\Models\Arquivo|null $FotoEntrada
     * @property-read \App\Models\Arquivo|null $FotoSaida
     * @property-read \App\Models\PontoEletronico|null $Ponto
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $hora_entrada
     * @property-read mixed $hora_saida
     * @property-read mixed $horas_trabalhadas
     * @property-read mixed $horas_trabalhadas_format
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico query()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereArquivoIdEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereArquivoIdSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereAutenticacaoEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereAutenticacaoSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereFacialEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereFacialSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLatEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLatSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLongEntrada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereLongSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereMinutos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico wherePontoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico whereUserDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico withTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|PeriodoPontoEletronico withoutTrashed()
     * @mixin \Eloquent
     */
    class PeriodoPontoEletronico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaCliente
     *
     * @property int $tipo_id
     * @property int $cliente_id
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\PesquisaClimaTipo|null $Tipo
     * @method static Builder|PesquisaClimaCliente newModelQuery()
     * @method static Builder|PesquisaClimaCliente newQuery()
     * @method static Builder|PesquisaClimaCliente query()
     * @method static Builder|PesquisaClimaCliente whereClienteId($value)
     * @method static Builder|PesquisaClimaCliente whereTipoId($value)
     * @mixin \Eloquent
     */
    class PesquisaClimaCliente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaPergunta
     *
     * @property int $id
     * @property int $tipo_id
     * @property string $pergunta
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaRespostaCandidato> $PerguntaResposta
     * @property-read int|null $pergunta_resposta_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaResposta> $Resposta
     * @property-read int|null $resposta_count
     * @property-read \App\Models\PesquisaClimaTipo|null $Tipo
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta query()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta wherePergunta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereTipoId($value)
     * @mixin \Eloquent
     */
    class PesquisaClimaPergunta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaPerguntaResposta
     *
     * @property int $id
     * @property int $pergunta_id
     * @property string $resposta
     * @property int $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaRespostaCandidato> $PerguntaResposta
     * @property-read int|null $pergunta_resposta_count
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta query()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta wherePerguntaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereResposta($value)
     * @mixin \Eloquent
     */
    class PesquisaClimaPerguntaResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaPerguntaRespostaCandidato
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $pergunta_id
     * @property int|null $resposta_id
     * @property string|null $respostadigitada
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $cliente_id
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\PesquisaClimaPergunta|null $Pergunta
     * @property-read \App\Models\PesquisaClimaPerguntaResposta|null $Resposta
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato query()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato wherePerguntaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereRespostaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereRespostadigitada($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class PesquisaClimaPerguntaRespostaCandidato extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaResposta
     *
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta query()
     * @mixin \Eloquent
     */
    class PesquisaClimaResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PesquisaClimaTipo
     *
     * @property int $id
     * @property string $nome
     * @property int $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPergunta> $PesquisaClimaPergunta
     * @property-read int|null $pesquisa_clima_pergunta_count
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo query()
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereNome($value)
     * @mixin \Eloquent
     */
    class PesquisaClimaTipo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PessoaEmpresa
     *
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property mixed $cpf
     * @property mixed $email
     * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa query()
     * @mixin \Eloquent
     */
    class PessoaEmpresa extends \Eloquent {}
}

namespace App\Models\Pivot{
    /**
     * App\Models\Pivot\TreinamentoVencimento
     *
     * @property int $treinamento_id
     * @property int $vencimento_id
     * @property string $data_vencimento
     * @property string|null $data_treinamento
     * @property string|null $numero_fat
     * @property int|null $arquivo_id
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento query()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereDataTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereDataVencimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereNumeroFat($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereTreinamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereVencimentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereArquivoId($value)
     * @property-read Arquivo|null $arquivo
     * @mixin \Eloquent
     * @property-read mixed $data_treinamento_default
     * @property-read mixed $data_vencimento_default
     */
    class TreinamentoVencimento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PlanejamentoDiario
     *
     * @property int $id
     * @property int $user_id
     * @property string $data
     * @property string|null $tarefas_agendadas
     * @property string|null $importante
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property mixed $0
     * @property mixed $1
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlanejamentoDiarioTarefas> $Tarefas
     * @property-read int|null $tarefas_count
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario query()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereImportante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereTarefasAgendadas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiario whereUserId($value)
     * @mixin \Eloquent
     */
    class PlanejamentoDiario extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PlanejamentoDiarioTarefas
     *
     * @property int $id
     * @property int $planejamento_id
     * @property string $tarefa
     * @property string $status
     * @property-read \App\Models\PlanejamentoDiario|null $PlanejamentoDiario
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas query()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas wherePlanejamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereTarefa($value)
     * @mixin \Eloquent
     */
    class PlanejamentoDiarioTarefas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PlanoConta
     *
     * @property int $id
     * @property int|null $categoria_plano_id
     * @property string $descricao
     * @property string $operacao c-credito , d-debito, t-todos
     * @property bool $ativo
     * @property int $empresa_id
     * @property-read \App\Models\CategoriaPlanoConta|null $Categoria
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $operacao_credito
     * @property-read mixed $operacao_debito
     * @property-read mixed $operacao_text
     * @property-read mixed $operacao_todas
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta query()
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereCategoriaPlanoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PlanoConta whereOperacao($value)
     * @mixin \Eloquent
     */
    class PlanoConta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PontoEletronico
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $funcionario_id
     * @property int|null $jornada_id
     * @property int $ocorrencia_id
     * @property int $duracao
     * @property int|null $duracao_normal
     * @property int|null $duracao_extra
     * @property int|null $duracao_noturna
     * @property string $tipo_frequencia
     * @property int $tempo_limite_falta
     * @property int $tempo_limite_saida
     * @property int $limite_tolerancia
     * @property string|null $justificativa
     * @property bool $verificado
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User|null $Funcionario
     * @property-read \App\Models\EscalaJornada|null $Jornada
     * @property-read \App\Models\OcorrenciaJornada|null $Ocorrencia
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PeriodoPontoEletronico> $Periodos
     * @property-read int|null $periodos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PeriodoPontoEletronico> $PeriodosEmAberto
     * @property-read int|null $periodos_em_aberto_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $dia
     * @property-read mixed $dia_sem
     * @property-read mixed $dia_semana
     * @property-read mixed $duracao_jornada
     * @property-read mixed $duracao_jornada_original
     * @property-read mixed $horas_extra
     * @property-read mixed $horas_extra_format
     * @property-read mixed $horas_normal
     * @property-read mixed $horas_normal_format
     * @property-read mixed $horas_normal_original
     * @property-read mixed $horas_normal_original_format
     * @property-read mixed $horas_noturna
     * @property-read mixed $horas_noturna_format
     * @property-read mixed $total_minutos
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico query()
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNormal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereDuracaoNoturna($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereFuncionarioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJornadaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereJustificativa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereLimiteTolerancia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereOcorrenciaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteFalta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTempoLimiteSaida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereTipoFrequencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PontoEletronico whereVerificado($value)
     * @mixin \Eloquent
     */
    class PontoEletronico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Projeto
     *
     * @property int $id
     * @property string $nome
     * @property int $qnt_total
     * @property int $qnt_total_restante
     * @property int $preenchidas
     * @property int $empresa_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagaProjeto> $VagasProjeto
     * @property-read int|null $vagas_projeto_count
     * @property-read mixed $tem_vaga
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto query()
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto wherePreenchidas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereQntTotal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereQntTotalRestante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Projeto extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\PromocaoFeedback
     *
     * @property int $id
     * @property int $feedback_id
     * @property string $novo_cargo
     * @property float $novo_salario
     * @property string $motivo
     * @property float $percentual
     * @property string $tipo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $novo_salario_format
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback query()
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereMotivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereNovoCargo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereNovoSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback wherePercentual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|PromocaoFeedback whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class PromocaoFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Quadro
     *
     * @property int $id
     * @property int $user_id
     * @property string $titulo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int $empresa_id
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ListaTarefa> $Listas
     * @property-read int|null $listas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogWeekly> $Logs
     * @property-read int|null $logs_count
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro query()
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Quadro whereUserId($value)
     * @mixin \Eloquent
     */
    class Quadro extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\RecrutamentoHistorico
     *
     * @property int $id
     * @property int $curriculo_id
     * @property int|null $feedback_id
     * @property int $empresa_id
     * @property int $user_id
     * @property string $acao
     * @property string $modulo
     * @property string|null $descricao
     * @property array|null $dados_anteriores
     * @property array|null $dados_novos
     * @property array|null $request_completo
     * @property string|null $ip_address
     * @property string|null $user_agent
     * @property string|null $session_id
     * @property \Illuminate\Support\Carbon $data_acao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Curriculo $curriculo
     * @property-read \App\Models\User $empresa
     * @property-read \App\Models\FeedbackCurriculo|null $feedback
     * @property-read \App\Models\User $usuario
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico query()
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereDadosAnteriores($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereDadosNovos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereDataAcao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereIpAddress($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereModulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereRequestCompleto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereSessionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereUserAgent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecrutamentoHistorico whereUserId($value)
     */
    class RecrutamentoHistorico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\RecuperacaoSenha
     *
     * @property int $id
     * @property int $user_id
     * @property string $token
     * @property string $ip_solicitacao
     * @property \Illuminate\Support\Carbon $solicitacao
     * @property \Illuminate\Support\Carbon $expiracao
     * @property string|null $ip_recuperacao
     * @property \Illuminate\Support\Carbon|null $recuperacao
     * @property bool $recuperado
     * @property-read \App\Models\User|null $User
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha query()
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereExpiracao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereIpRecuperacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereIpSolicitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereRecuperacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereRecuperado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereSolicitacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RecuperacaoSenha whereUserId($value)
     * @mixin \Eloquent
     */
    class RecuperacaoSenha extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\RequisicaoVaga
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int $centro_custo_id
     * @property int $cargo_id
     * @property int|null $area_id
     * @property int $quantidade
     * @property string $tipo_contratacao
     * @property string $prioridade
     * @property bool $imediata
     * @property \Illuminate\Support\Carbon|null $previsao_inicio
     * @property string|null $solicitante
     * @property int $user_id
     * @property string|null $observacao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property-read \App\Models\AreaEtiqueta|null $Area
     * @property-read \App\Models\Vaga|null $Cargo
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\TipoContratacao|null $OutrasInformacoes
     * @property-read \App\Models\User|null $User
     * @property-read \App\Models\User|null $UserAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @property-read mixed $data_solicitacao
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga query()
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereAreaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCargoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereImediata($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrevisaoInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrioridade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereQuantidade($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereTipoContratacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUserId($value)
     * @mixin \Eloquent
     */
    class RequisicaoVaga extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\RespostaAlternativas
     *
     * @property int $id
     * @property int $alternativa_id
     * @property string $label
     * @property bool|null $selecionado Para os checkbox vir marcado
     * @property int|null $link_id
     * @property int $ordem
     * @property int|null $value
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas query()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereAlternativaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLinkId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereSelecionado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereValue($value)
     * @mixin \Eloquent
     */
    class RespostaAlternativas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\RespostaOcorrencia
     *
     * @property int $id
     * @property int $ocorrencia_id
     * @property int $user_id
     * @property string $resposta
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Ocorrencia $Ocorrencia
     * @property-read User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia query()
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereOcorrenciaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereResposta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|RespostaOcorrencia whereUserId($value)
     * @mixin \Eloquent
     */
    class RespostaOcorrencia extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ResultadoIntegrado
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property bool $documentos_entregue
     * @property string|null $documentos_entregue_data
     * @property bool $encaminhado_exame
     * @property string|null $encaminhado_exame_data
     * @property int|null $pcmso_id
     * @property int|null $empresa_exame_id
     * @property bool $encaminhado_treinamento
     * @property string|null $encaminhado_treinamento_data
     * @property bool|null $excessao
     * @property string|null $autorizado_por
     * @property int $usuario_id
     * @property string $responsavel_envio
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $formulario_id
     * @property-read \App\Models\Admissao|null $Admissao
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @property-read \App\Models\CertificadoAlumar|null $Certificado
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FotoTres
     * @property-read int|null $foto_tres_count
     * @property-read \App\Models\Pcmso|null $Pcmso
     * @property-read \App\Models\Treinamento|null $Treinamento
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \App\Models\ParecerRh|null $parecerRh
     * @property-read \App\Models\ParecerRota|null $parecerRota
     * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
     * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado query()
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereAutorizadoPor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereDocumentosEntregue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereDocumentosEntregueData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEmpresaExameId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoExame($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoExameData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoTreinamentoData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereExcessao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereFormularioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado wherePcmsoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereResponsavelEnvio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereUsuarioId($value)
     * @mixin \Eloquent
     */
    class ResultadoIntegrado extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Servico
     *
     * @property int $id
     * @property string $titulo
     * @property bool $ativo
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Servico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Servico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Servico query()
     * @method static \Illuminate\Database\Eloquent\Builder|Servico whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Servico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Servico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Servico whereTitulo($value)
     * @mixin \Eloquent
     */
    class Servico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ServicosCliente
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int|null $servico_id
     * @property \Illuminate\Support\Carbon $data_inicio
     * @property \Illuminate\Support\Carbon $data_encerramento
     * @property string|null $escopo
     * @property float $valor
     * @property string $tipo_faturamento
     * @property string $status
     * @property string|null $feedback
     * @property bool $ativo
     * @property string $tipo_contrato
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\Servico|null $Servico
     * @property-read \App\Models\TipoServico|null $TipoServico
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente query()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereDataEncerramento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereEscopo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereServicoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereTipoContrato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereTipoFaturamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereValor($value)
     * @mixin \Eloquent
     */
    class ServicosCliente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ServicosProspects
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int|null $servico_id
     * @property \Illuminate\Support\Carbon $data_envio_proposta
     * @property string|null $escopo
     * @property string $status
     * @property string|null $feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\Servico|null $Servico
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects query()
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereDataEnvioProposta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereEscopo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereFeedback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereServicoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereStatus($value)
     * @mixin \Eloquent
     */
    class ServicosProspects extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SetoresFormulario
     *
     * @property int $id
     * @property int|null $empresa_id
     * @property string $nome
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AlternativaFormulario> $Alternativas
     * @property-read int|null $alternativas_count
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario query()
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SetoresFormulario whereNome($value)
     * @mixin \Eloquent
     */
    class SetoresFormulario extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Simulado
     *
     * @property int $id
     * @property string $titulo
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property string|null $tipo_prova
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoPergunta> $Perguntas
     * @property-read int|null $perguntas_count
     * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $qnt_questoes
     * @property-read mixed $slug
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado query()
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereTipoProva($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Simulado extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SimuladoCandidato
     *
     * @property int $id
     * @property int $simulado_vaga_id
     * @property int|null $feedback_id
     * @property int $duracao_segundos
     * @property bool $finalizado
     * @property \Illuminate\Support\Carbon|null $data_finalizacao
     * @property int|null $acertos
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $status
     * @property int|null $empresa_id
     * @property-read \App\Models\FeedbackCurriculo|null $Candidato
     * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato query()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereAcertos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereDataFinalizacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereDuracaoSegundos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereFinalizado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereSimuladoVagaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class SimuladoCandidato extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SimuladoCandidatoResposta
     *
     * @property int $simulado_vaga_id
     * @property int|null $feedback_id
     * @property int $simulado_pergunta_id
     * @property int $simulado_resposta_id
     * @property-read \App\Models\Curriculo|null $Candidato
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\SimuladoPergunta|null $Perguntas
     * @property-read \App\Models\SimuladoResposta|null $Resposta
     * @property-read \App\Models\SimuladoCandidato|null $SimuladoCandidato
     * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta query()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoPerguntaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoRespostaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoVagaId($value)
     * @mixin \Eloquent
     */
    class SimuladoCandidatoResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SimuladoPergunta
     *
     * @property int $id
     * @property int $simulado_id
     * @property string $enunciado
     * @property int|null $qnt_linhas
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoResposta> $Respostas
     * @property-read int|null $respostas_count
     * @property-read \App\Models\Simulado|null $Simulado
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta query()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereEnunciado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereQntLinhas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoPergunta whereSimuladoId($value)
     * @mixin \Eloquent
     */
    class SimuladoPergunta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SimuladoResposta
     *
     * @property int $id
     * @property int $simulado_pergunta_id
     * @property string $resposta
     * @property bool $correto
     * @property-read \App\Models\SimuladoPergunta|null $Pergunta
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta query()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereCorreto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereResposta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereSimuladoPerguntaId($value)
     * @mixin \Eloquent
     */
    class SimuladoResposta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\SimuladoVaga
     *
     * @property int $id
     * @property int $simulado_id
     * @property int $vaga_id
     * @property \Illuminate\Support\Carbon $data_inicio
     * @property \Illuminate\Support\Carbon $data_fim
     * @property int $duracao
     * @property bool|null $online
     * @property int|null $empresa_id
     * @property int|null $vagas_abertas_id
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoPergunta> $Perguntas
     * @property-read int|null $perguntas_count
     * @property-read \App\Models\Simulado|null $Simulado
     * @property-read \App\Models\Vaga|null $Vaga
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagasAbertas> $VagasAbertas
     * @property-read int|null $vagas_abertas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $duracao_segundos
     * @property-read mixed $qnt_questoes
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga query()
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereDuracao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereOnline($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereSimuladoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereVagaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|SimuladoVaga whereVagasAbertasId($value)
     * @mixin \Eloquent
     */
    class SimuladoVaga extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Tag
     *
     * @property int $id
     * @property string $nome
     * @property int|null $empresa_id
     * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
     * @method static \Illuminate\Database\Eloquent\Builder|Tag whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tag whereNome($value)
     * @mixin \Eloquent
     */
    class Tag extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Tarefa
     *
     * @property int $id
     * @property int $lista_id
     * @property int $user_id
     * @property string $titulo
     * @property string|null $descricao
     * @property int $ordem
     * @property \Illuminate\Support\Carbon|null $datahora_inicio
     * @property \Illuminate\Support\Carbon|null $datahora_entrega
     * @property string|null $lembrete
     * @property bool $concluido
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistsTarefa> $Checklists
     * @property-read int|null $checklists_count
     * @property-read \App\Models\ListaTarefa|null $Lista
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LogWeekly> $Logs
     * @property-read int|null $logs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Membros
     * @property-read int|null $membros_count
     * @property-read \App\Models\User|null $Usuario
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $data_hora_entrega_formatada
     * @property-read mixed $data_hora_inicio_formatada
     * @property-read mixed $em_atraso
     * @property-read mixed $lembrete_text
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa query()
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereConcluido($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDatahoraEntrega($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDatahoraInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereLembrete($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereListaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Tarefa whereUserId($value)
     * @mixin \Eloquent
     */
    class Tarefa extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TelefoneCurriculo
     *
     * @property int $id
     * @property string $tipo
     * @property string $pais
     * @property string $numero
     * @property string|null $ramal
     * @property string|null $detalhe
     * @property int $curriculo_id
     * @property bool $principal
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $sonumero
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereCurriculoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereDetalhe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo wherePais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo wherePrincipal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereRamal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereTipo($value)
     * @mixin \Eloquent
     */
    class TelefoneCurriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TelefoneFornecedor
     *
     * @property int $id
     * @property string $tipo
     * @property string $pais
     * @property string $numero
     * @property string|null $ramal
     * @property string|null $detalhe
     * @property int $fornecedor_id
     * @property bool $principal
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor query()
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereDetalhe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereFornecedorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor wherePais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor wherePrincipal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereRamal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereTipo($value)
     * @mixin \Eloquent
     */
    class TelefoneFornecedor extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Testemunhal
     *
     * @property int $id
     * @property string $nome
     * @property string|null $subtitulo
     * @property string $texto
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
     * @property-read int|null $anexo_count
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal query()
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereSubtitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereTexto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Testemunhal extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoAviso
     *
     * @property int $id
     * @property string $descricao
     * @property bool $ativo
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAviso whereId($value)
     * @mixin \Eloquent
     */
    class TipoAviso extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoAvisoCurriculo
     *
     * @property int $tipo_aviso_id
     * @property int|null $feedback_id
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo whereTipoAvisoId($value)
     * @mixin \Eloquent
     */
    class TipoAvisoCurriculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoBeneficio
     *
     * @property int $id
     * @property string $nome
     * @property int|null $cliente_id
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \App\Models\User|null $Empresa
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TipoBeneficio extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoContratacao
     *
     * @property int $id
     * @property int $requisicao_vaga_id
     * @property string $posicao
     * @property string $processo
     * @property string|null $nome_indicacao
     * @property string $contrato
     * @property string|null $local_trabalho
     * @property string $horario
     * @property int|null $gestor_id
     * @property string|null $gestor
     * @property bool|null $ppra
     * @property string|null $salario
     * @property float|null $salario_valor
     * @property string|null $beneficio
     * @property string|null $beneficio_excecao
     * @property string|null $treinamento
     * @property string|null $treinamento_excecao
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read User|null $GestorAprovacao
     * @property-read \App\Models\RequisicaoVaga|null $Requisicao
     * @property-read mixed $salario_valor_format
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereBeneficio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereBeneficioExcecao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereContrato($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereHorario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereLocalTrabalho($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereNomeIndicacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePosicao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao wherePpra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereProcesso($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereRequisicaoVagaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereSalario($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereSalarioValor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereTreinamento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereTreinamentoExcecao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoContratacao whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TipoContratacao extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoDocumento
     *
     * @property int $id
     * @property string $nome
     * @property string $tipo pode ser empresa, ssma...
     * @property bool $ativo
     * @property int $empresa_id
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumento whereTipo($value)
     * @mixin \Eloquent
     */
    class TipoDocumento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoDocumentoServico
     *
     * @property int $id
     * @property string $nome
     * @property string $tipo pode ser para contrato, ssma...
     * @property bool $ativo
     * @property int $empresa_id
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoDocumentoServico whereTipo($value)
     * @mixin \Eloquent
     */
    class TipoDocumentoServico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoRecebeEmail
     *
     * @property int $id
     * @property string $nome
     * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail whereNome($value)
     * @mixin \Eloquent
     */
    class TipoRecebeEmail extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoServico
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServico whereLabel($value)
     * @mixin \Eloquent
     */
    class TipoServico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TipoServicoFornecedor
     *
     * @property int $id
     * @property string $label
     * @property bool $ativo
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor query()
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TipoServicoFornecedor extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Topicos
     *
     * @property int $id
     * @property string $nome
     * @property bool $ativo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormularioAvaliacaoAnual> $Perguntas
     * @property-read int|null $perguntas_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos query()
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Topicos whereNome($value)
     * @mixin \Eloquent
     */
    class Topicos extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TransferenciaPrevista
     *
     * @property int $id
     * @property int|null $colaborador_id
     * @property int $centro_custo_origem_id
     * @property int $centro_custo_destino_id
     * @property \Illuminate\Support\Carbon $data_transferencia
     * @property int|null $user_id
     * @property string|null $solicitante
     * @property string|null $obs
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $gestor_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\CentroCusto|null $CentroCustoDestino
     * @property-read \App\Models\CentroCusto|null $CentroCustoOrigem
     * @property-read \App\Models\Curriculo|null $Colaborador
     * @property-read User|null $GestorAprovacao
     * @property-read User|null $QuemAprovou
     * @property-read User|null $UserAprovacao
     * @property-read User|null $UserCadastrou
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCentroCustoDestinoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCentroCustoOrigemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataTransferencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUserId($value)
     * @mixin \Eloquent
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property int|null $rh_aprovacao_id
     * @property string|null $status_aprovacao_rh
     * @property string|null $obs_rh
     * @property string|null $data_aprovacao_rh
     * @property-read \App\Models\User|null $AprovacaoExtra
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereStatusAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereStatusAprovacaoRh($value)
     */
    class TransferenciaPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Treinamento
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int|null $cadastrou
     * @property string|null $tipo parada, fixo
     * @property int|null $gerou_id
     * @property string|null $data_envio
     * @property bool|null $enviado_email
     * @property int|null $enviou_id
     * @property string|null $email_envio
     * @property bool|null $email_aberto
     * @property \Illuminate\Support\Carbon|null $data_email_aberto
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
     * @property-read \App\Models\User|null $QuemCadastrou
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vencimento> $Vencimentos
     * @property-read int|null $vencimentos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $token
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento query()
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereCadastrou($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereDataEmailAberto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereDataEnvio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEmailAberto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEmailEnvio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEnviadoEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEnviouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereGerouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereUpdatedAt($value)
     * @mixin \Eloquent
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $arquivosVencimentos
     * @property-read int|null $arquivos_vencimentos_count
     */
    class Treinamento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TreinamentoEvento
     *
     * @property int $id
     * @property int $cliente_id
     * @property int $treinamento_sgi_id
     * @property int $empresa_treinamento_id
     * @property \Illuminate\Support\Carbon $data_inicio
     * @property \Illuminate\Support\Carbon $data_fim
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamento
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Instrutor> $InstrutoresEvento
     * @property-read int|null $instrutores_evento_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PessoaEmpresa> $PessoasEvento
     * @property-read int|null $pessoas_evento_count
     * @property-read \App\Models\TreinamentoSgi|null $TreinamentoSgi
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento query()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereDataFim($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereDataInicio($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereEmpresaTreinamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereTreinamentoSgiId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoEvento whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TreinamentoEvento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TreinamentoSgi
     *
     * @property int $id
     * @property string $nome
     * @property string $titulo_certificado
     * @property string|null $conteudo_abordado
     * @property string|null $conteudo_programatico
     * @property int $carga_horaria
     * @property int|null $validade
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi query()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereCargaHoraria($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereConteudoAbordado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereConteudoProgramatico($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereTituloCertificado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereValidade($value)
     * @mixin \Eloquent
     */
    class TreinamentoSgi extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\TreinamentoVencimentoHistorico
     *
     * @property int $id
     * @property int $feedback_id
     * @property int $empresa_id
     * @property int $treinamento_id
     * @property int $user_id
     * @property array $treinamentos_vencimentos
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico query()
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereTreinamentoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereTreinamentosVencimentos($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimentoHistorico whereUserId($value)
     */
    class TreinamentoVencimentoHistorico extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\User
     *
     * @property int $id
     * @property string $nome
     * @property string|null $logradouro
     * @property string|null $complemento
     * @property string|null $bairro
     * @property string|null $municipio
     * @property string|null $uf
     * @property string|null $cep
     * @property string|null $login
     * @property string|null $password
     * @property string $tipo
     * @property int|null $grupo_id
     * @property int|null $grupo_cloud_id
     * @property string|null $cadastrou
     * @property bool $ativo
     * @property bool $temp
     * @property \Illuminate\Support\Carbon|null $ultimo_acesso
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $remember_token
     * @property bool|null $termos
     * @property string|null $device_token
     * @property string|null $api_token
     * @property int|null $empresa_id
     * @property bool|null $gestor
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property bool $privilegio_gestor_area
     * @property bool $privilegio_gestor_centro_custo
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ArquivamentoDossie
     * @property-read int|null $arquivamento_dossie_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoFeedback> $Avaliadores
     * @property-read int|null $avaliadores_count
     * @property-read \App\Models\UsuarioConta|null $BancoConta
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $ClienteFuncionarios
     * @property-read int|null $cliente_funcionarios_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $ClientesEmpresa
     * @property-read int|null $clientes_empresa_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ClientesLogo
     * @property-read int|null $clientes_logo_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $ClientesMascote
     * @property-read int|null $clientes_mascote_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cloud> $Clouds
     * @property-read int|null $clouds_count
     * @property-read \App\Models\EmpresaConfig|null $ConfigEmpresa
     * @property-read \App\Models\Curriculo|null $Curriculo
     * @property-read \App\Models\Cliente|null $DadosEmpresa
     * @property-read \App\Models\Cliente|null $Empresa
     * @property-read \App\Models\ClienteConfig|null $EmpresaConfiguracoes
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpresaEscala> $EmpresaEscalas
     * @property-read int|null $empresa_escalas_count
     * @property-read \App\Models\EmpresaExame|null $EmpresaExame
     * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $EmpresaFuncionarios
     * @property-read int|null $empresa_funcionarios_count
     * @property-read \App\Models\EmpresaConfig|null $EmpresaPontoConfiguracoes
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpresaEscala> $EscalasFuncionario
     * @property-read int|null $escalas_funcionario_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exportacao> $Exportacoes
     * @property-read int|null $exportacoes_count
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormaPagamento> $FormasPagamento
     * @property-read int|null $formas_pagamento_count
     * @property-read \App\Models\Fornecedor|null $Fornecedor
     * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $FornecedoresEmpresa
     * @property-read int|null $fornecedores_empresa_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $FotoPerfil
     * @property-read int|null $foto_perfil_count
     * @property-read \App\Models\GrupoCloud|null $GrupoCloud
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GrupoCloud> $GrupoClouds
     * @property-read int|null $grupo_clouds_count
     * @property-read \App\Models\Papel|null $Papel
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpresaPerimetro> $PerimetrosEmpresa
     * @property-read int|null $perimetros_empresa_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpresaPerimetro> $PerimetrosFuncionario
     * @property-read int|null $perimetros_funcionario_count
     * @property-read \App\Models\RecuperacaoSenha|null $RecuperacaoSenha
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TipoRecebeEmail> $UserRecebeEmail
     * @property-read int|null $user_recebe_email_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Avaliacao> $avaliadoresFuncionario
     * @property-read int|null $avaliadores_funcionario_count
     * @property mixed $loginl
     * @property-read mixed $privilegios
     * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
     * @property-read int|null $notifications_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     * @method static Builder|User ativoNaoExcluido()
     * @method static \Database\Factories\UserFactory factory(...$parameters)
     * @method static Builder|User newModelQuery()
     * @method static Builder|User newQuery()
     * @method static Builder|User onlyTrashed()
     * @method static Builder|User query()
     * @method static Builder|User tiposGerenciais()
     * @method static Builder|User whereApiToken($value)
     * @method static Builder|User whereAtivo($value)
     * @method static Builder|User whereBairro($value)
     * @method static Builder|User whereCadastrou($value)
     * @method static Builder|User whereCep($value)
     * @method static Builder|User whereComplemento($value)
     * @method static Builder|User whereCreatedAt($value)
     * @method static Builder|User whereDeletedAt($value)
     * @method static Builder|User whereDeviceToken($value)
     * @method static Builder|User whereEmpresaId($value)
     * @method static Builder|User whereGestor($value)
     * @method static Builder|User whereGrupoCloudId($value)
     * @method static Builder|User whereGrupoId($value)
     * @method static Builder|User whereId($value)
     * @method static Builder|User whereLogin($value)
     * @method static Builder|User whereLogradouro($value)
     * @method static Builder|User whereMunicipio($value)
     * @method static Builder|User whereNome($value)
     * @method static Builder|User wherePassword($value)
     * @method static Builder|User wherePrivilegioGestorArea($value)
     * @method static Builder|User wherePrivilegioGestorCentroCusto($value)
     * @method static Builder|User whereRememberToken($value)
     * @method static Builder|User whereTemp($value)
     * @method static Builder|User whereTermos($value)
     * @method static Builder|User whereTipo($value)
     * @method static Builder|User whereUf($value)
     * @method static Builder|User whereUltimoAcesso($value)
     * @method static Builder|User whereUpdatedAt($value)
     * @method static Builder|User withTrashed()
     * @method static Builder|User withoutTrashed()
     * @property bool $require_password_reset Habilita/desabilita reset forçado de senha
     * @property int|null $password_reset_days Quantidade de dias para forçar reset de senha
     * @property \Illuminate\Support\Carbon|null $password_changed_at Data da última alteração de senha
     * @property-read Collection<int, \App\Models\UsuarioTelefone> $Telefones
     * @property-read int|null $telefones_count
     * @method static Builder|User wherePasswordChangedAt($value)
     * @method static Builder|User wherePasswordResetDays($value)
     * @method static Builder|User whereRequirePasswordReset($value)
     * @mixin \Eloquent
     */
    class User extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\UsuarioConta
     *
     * @property int $id
     * @property int $user_id
     * @property string|null $banco
     * @property string|null $agencia
     * @property string|null $conta
     * @property bool $pix
     * @property string|null $tipochavepix
     * @property string|null $chavepix
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta query()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereAgencia($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereBanco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereChavepix($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereConta($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta wherePix($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereTipochavepix($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioConta whereUserId($value)
     * @mixin \Eloquent
     */
    class UsuarioConta extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\UsuarioDependente
     *
     * @property int $id
     * @property int $user_id
     * @property string $tipo
     * @property string|null $outro_tipo
     * @property string $nome
     * @property string|null $cpf
     * @property \Illuminate\Support\Carbon|null $nascimento
     * @property string|null $observacao
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente query()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereCpf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereNascimento($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereObservacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereOutroTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioDependente whereUserId($value)
     * @mixin \Eloquent
     */
    class UsuarioDependente extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\UsuarioTelefone
     *
     * @property int $id
     * @property string $tipo
     * @property string $pais
     * @property string $numero
     * @property string|null $ramal
     * @property string|null $detalhe
     * @property int $user_id
     * @property bool $principal
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @property-read mixed $sonumero
     * @property-read mixed $tipo_text
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone query()
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereDetalhe($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereNumero($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone wherePais($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone wherePrincipal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereRamal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereTipo($value)
     * @property-read \App\Models\User $user
     * @mixin \Eloquent
     */
    class UsuarioTelefone extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Vaga
     *
     * @property int $id
     * @property int|null $categoria_id
     * @property string $nome
     * @property bool $ativo
     * @property int|null $empresa_id
     * @property-read \App\Models\CategoriaVagas|null $Categoria
     * @property-read \App\Models\User|null $Empresa
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Etapas> $EtapaStatus
     * @property-read int|null $etapa_status_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoVaga> $SimuladoVaga
     * @property-read int|null $simulado_vaga_count
     * @property-read \App\Models\VagasAbertas|null $VagaAberta
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga query()
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereCategoriaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereNome($value)
     * @mixin \Eloquent
     */
    class Vaga extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\VagaProjeto
     *
     * @property int $id
     * @property int $empresa_id
     * @property int $projeto_id
     * @property int $vaga_aberta_id
     * @property int $qnt_total
     * @property int $qnt_preenchida
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeedbackCurriculo> $Feedbacks
     * @property-read int|null $feedbacks_count
     * @property-read \App\Models\Projeto|null $Projeto
     * @property-read \App\Models\VagasAbertas|null $VagaAberta
     * @property-read mixed $tem_vaga
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto query()
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereProjetoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereQntPreenchida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereQntTotal($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjeto whereVagaAbertaId($value)
     * @mixin \Eloquent
     */
    class VagaProjeto extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\VagaProjetoFeedback
     *
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \App\Models\VagaProjeto|null $VagaProjeto
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback query()
     * @mixin \Eloquent
     */
    class VagaProjetoFeedback extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\VagasAbertas
     *
     * @property int $id
     * @property int $vaga_id
     * @property string|null $titulo
     * @property string|null $descricao
     * @property int|null $municipio_id
     * @property bool $ativo
     * @property bool $ativo_sistema
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $empresa_id
     * @property-read \App\Models\Vaga|null $Cargo
     * @property-read \App\Models\Cliente|null $Empresa
     * @property-read \App\Models\Municipio|null $Municipio
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagaProjeto> $Projetos
     * @property-read int|null $projetos_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoVaga> $Simulados
     * @property-read int|null $simulados_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoVaga> $SimuladosAtivos
     * @property-read int|null $simulados_ativos_count
     * @property-read \App\Models\Vaga|null $Vaga
     * @property-read \App\Models\Vaga|null $VagaSelecionada
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas query()
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereAtivoSistema($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereMunicipioId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereTitulo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereVagaId($value)
     * @mixin \Eloquent
     */
    class VagasAbertas extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ValorExtraPrevista
     *
     * @property int $id
     * @property int|null $cliente_id
     * @property int|null $colaborador_id
     * @property int $centro_custo_id
     * @property string $tipo
     * @property float|null $periodo_dias
     * @property int|null $user_id
     * @property string|null $solicitante
     * @property string|null $obs
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $user_aprovacao_id
     * @property \Illuminate\Support\Carbon|null $data_aprovacao
     * @property string|null $obs_aprovacao
     * @property string|null $status_aprovacao
     * @property int|null $empresa_id
     * @property int|null $gestor_id
     * @property int|null $filial
     * @property int|null $centro_custo_filial_id
     * @property int|null $rh_aprovacao_id
     * @property string|null $obs_rh
     * @property string|null $status_aprovacao_rh
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
     * @property bool $aprovado_via_script
     * @property int|null $quem_deletou_id
     * @property string|null $deleted_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
     * @property-read int|null $anexos_count
     * @property-read \App\Models\CentroCusto|null $CentroCusto
     * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
     * @property-read \App\Models\Cliente|null $Cliente
     * @property-read \App\Models\User|null $Colaborador
     * @property-read \App\Models\User|null $GestorAprovacao
     * @property-read \App\Models\User|null $RhAprovacao
     * @property-read \App\Models\User|null $UserAprovacao
     * @property-read \App\Models\User|null $UserCadastrou
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista query()
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereAprovadoViaScript($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCentroCustoFilialId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCentroCustoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereClienteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereColaboradorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereFilial($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereGestorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObs($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista wherePeriodoDias($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereQuemDeletouId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereRhAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereSolicitante($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacaoRh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereTipo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserAprovacaoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserId($value)
     * @mixin \Eloquent
     * @property int|null $aprovacao_extra_id
     * @property string|null $status_aprovacao_extra
     * @property string|null $obs_aprovacao_extra
     * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
     * @property-read \App\Models\User|null $AprovacaoExtra
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereAprovacaoExtraId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsAprovacaoExtra($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacaoExtra($value)
     */
    class ValorExtraPrevista extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Vencimento
     *
     * @property int $id
     * @property string $label
     * @property string|null $descricao
     * @property int|null $prazo_parada
     * @property int|null $prazo_fixo
     * @property int|null $ordem
     * @property bool $ativo
     * @property int|null $empresa_id
     * @property string|null $label_reduzida
     * @property bool|null $exibir_na_carteira
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento query()
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereDescricao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereEmpresaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereExibirNaCarteira($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereLabel($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereLabelReduzida($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereOrdem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoFixo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoParada($value)
     * @mixin \Eloquent
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $arquivosVencimentos
     * @property-read int|null $arquivos_vencimentos_count
     */
    class Vencimento extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\Vinculo
     *
     * @property int $id
     * @property int|null $feedback_id
     * @property int $vaga_id
     * @property bool $parente
     * @property string|null $nome
     * @property string|null $funcao
     * @property string|null $grau_parentesco
     * @property bool|null $foi_empregado
     * @property string|null $local_empregado
     * @property string|null $outra_empresa_parceira
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\FeedbackCurriculo|null $Feedback
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
     * @property-read int|null $activities_count
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo query()
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFeedbackId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFoiEmpregado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereFuncao($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereGrauParentesco($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereLocalEmpregado($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereNome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereOutraEmpresaParceira($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereParente($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Vinculo whereVagaId($value)
     * @mixin \Eloquent
     */
    class Vinculo extends \Eloquent {}
}

namespace App\Models{
    /**
     * App\Models\ZapNumeros
     *
     * @property int $id
     * @property string $telefone
     * @property int $ativo
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros query()
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereAtivo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereTelefone($value)
     * @mixin \Eloquent
     */
    class ZapNumeros extends \Eloquent {}
}

