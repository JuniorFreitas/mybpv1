<?php

namespace App\Services\Admissao;

use App\Models\CentroCusto;
use App\Models\FeedbackCurriculo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use MasterTag\DataHora;

/**
 * Monta a query (Eloquent Builder) filtrada da listagem do processo de Admissão.
 * Extraído de AdmissaoController::filtro (TD-002 fatia 3).
 *
 * Usado tanto pelo controller (tela de listagem) quanto por Jobs de exportação
 * (JobExportarExcel, JobExportaAdmissoes) via `(new AdmissaoController())->filtro(...)`.
 */
class AdmissaoProcessoFiltroQuery
{
    public function aplicar(Request $request): Builder
    {
        // Listagem do processo: só colunas/relações usadas nos cards (DBA: payload enxuto, sem N+1)
        $resultado = FeedbackCurriculo::select([
            'id', 'curriculo_id', 'vagas_abertas_id', 'empresa_id', 'created_at',
        ])
            ->whereHas('ResultadoIntegrado')
            ->with([
                'ResultadoIntegrado:id,feedback_id,documentos_entregue,documentos_entregue_data,encaminhado_exame,encaminhado_exame_data,encaminhado_treinamento,encaminhado_treinamento_data,responsavel_envio',
                'Curriculo:id,nome,cpf,pcd',
                'Curriculo.FotoTres' => function ($q) {
                    $q->select('arquivos.id', 'arquivos.file', 'arquivos.thumb', 'arquivos.disco', 'arquivos.imagem');
                },
                'Curriculo.TelPrincipal' => function ($q) {
                    $q->select(
                        'curriculo_telefone.id',
                        'curriculo_telefone.curriculo_id',
                        'curriculo_telefone.numero',
                        'curriculo_telefone.tipo'
                    );
                },
                // Accessor vaga_aberta_municipio usa Vaga (não VagaSelecionada)
                'VagaAberta:id,vaga_id,municipio_id',
                'VagaAberta.Vaga:id,nome',
                'VagaAberta.Municipio:id,nome,uf',
                'Admissao:id,feedback_id,status,tipo_admissao,data_admissao,numero_cracha,centro_custo_id,cargo,funcao',
                'Admissao.UltimoAso:id,feedback_id,data_realizacao',
                'Admissao.Demissao:id,feedback_id,data_desmobilizacao',
                'UltimoAso:id,feedback_id,data_realizacao',
            ])->filtrarPorCnpjECentroCusto($request);

        $filtroPeriodo = $request->filtroPeriodo === true || $request->filtroPeriodo === 'true' || $request->filtroPeriodo === '1';

        if ($filtroPeriodo) {
            if ($request->filled('dataInicio') && $request->filled('dataFim')) {
                $dataInicio = new DataHora($request->dataInicio . ' 00:00:00');
                $dataFim = new DataHora($request->dataFim . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->periodo ?? '');
                if (count($periodo) === 2) {
                    $dataInicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                    $dataFim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                } else {
                    $dataInicio = null;
                    $dataFim = null;
                }
            }
            if ($dataInicio && $dataFim) {
                $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
                });
            }
        }

        if ($request->campoDemitido) {
            $resultado->Demitidos();
        } else {
            $resultado->Admitidos();
        }

        $filtroAso = $request->filtroAso === true || $request->filtroAso === 'true' || $request->filtroAso === '1';

        if ($filtroAso) {
            if ($request->filled('dataInicioAso') && $request->filled('dataFimAso')) {
                $dataInicioAso = new DataHora($request->dataInicioAso . ' 00:00:00');
                $dataFimAso = new DataHora($request->dataFimAso . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->campoAso ?? '');
                $dataInicioAso = count($periodo) === 2 ? new DataHora(trim($periodo[0]) . ' 00:00:00') : null;
                $dataFimAso = count($periodo) === 2 ? new DataHora(trim($periodo[1]) . ' 23:59:59') : null;
            }
            if ($dataInicioAso && $dataFimAso) {
                $resultado->whereHas('UltimoAso', function ($q) use ($dataInicioAso, $dataFimAso) {
                    $q->where('data_realizacao', '>=', $dataInicioAso->dataHoraInsert())->where('data_realizacao', '<=', $dataFimAso->dataHoraInsert());
                });
            }
        }

        $filtroDataAdmissao = $request->filtroDataAdmissao === true || $request->filtroDataAdmissao === 'true' || $request->filtroDataAdmissao === '1';

        if ($filtroDataAdmissao) {
            if ($request->filled('dataInicioAdmissao') && $request->filled('dataFimAdmissao')) {
                $dataInicioAdm = new DataHora($request->dataInicioAdmissao . ' 00:00:00');
                $dataFimAdm = new DataHora($request->dataFimAdmissao . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->campoAdmisaoData ?? '');
                $dataInicioAdm = count($periodo) === 2 ? new DataHora(trim($periodo[0]) . ' 00:00:00') : null;
                $dataFimAdm = count($periodo) === 2 ? new DataHora(trim($periodo[1]) . ' 23:59:59') : null;
            }
            if ($dataInicioAdm && $dataFimAdm) {
                $resultado->whereHas('Admissao', function ($q) use ($dataInicioAdm, $dataFimAdm) {
                    $q->where('data_admissao', '>=', $dataInicioAdm->dataHoraInsert())->where('data_admissao', '<=', $dataFimAdm->dataHoraInsert());
                });
            }
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoBusca')) {
            $busca = $request->campoBusca;
            $resultado->where(function ($q) use ($busca) {
                $q->whereHas('Curriculo', function ($query) use ($busca) {
                    $query->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('cpf', 'like', '%' . $busca . '%')
                        ->orWhere('id', $busca);
                });
                if (is_numeric(trim($busca))) {
                    $q->orWhere('id', (int) $busca);
                }
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoStatusAdmissao')) {
            if ($request->campoStatusAdmissao == 'EM PROCESSO') {
                $resultado->whereDoesntHave('Admissao');
            } else {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->whereStatus($request->campoStatusAdmissao);
                });
            }
        }

        if ($request->filled('campoTipoAdmissao')) {
            $resultado->whereHas('Admissao', function ($query) use ($request) {
                $query->whereTipoAdmissao($request->campoTipoAdmissao);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('VagaAberta.Municipio', function ($q) use ($request) {
                $q->whereUf($request->campoUf);
            });
        }

        if ($request->filled('campoCnpj')) {
            $centroCusto = new CentroCusto();
            $centros_custos = $centroCusto->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            $cc = $centroCusto->centrosDoCnpj($centros_custos, (string) $request->campoCnpj);

            if ($cc === null) {
                $resultado->whereRaw('1 = 0');
            } elseif (!$request->filled('campoCentroCusto')) {
                $resultado->whereHas('Admissao', function ($query) use ($cc) {
                    if ($cc[0]['matriz']) {
                        $query->where(function ($query) use ($cc) {
                            $query->whereIn('centro_custo_id', $cc->pluck('id')
                                ->toArray())->orWhere('centro_custo_id', null);
                        })->where('filial', false);
                    } else {
                        $query->where(function ($query) use ($cc) {
                            $query->whereIn('centro_custo_filial_id', $cc->pluck('filial_id')
                                ->toArray())->orWhere('centro_custo_filial_id', null);
                        })->where('filial', true);
                    }
                });
            } else {
                $resultado->whereHas('Admissao', function ($query) use ($request, $cc) {
                    if ($cc[0]['matriz']) {
                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ? $request->campoCentroCusto : null;
                        $query->where('centro_custo_id', $campoCentroCusto)
                            ->where('filial', false);
                    } else {
                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ? $request->campoCentroCusto : null;
                        $query->where('centro_custo_filial_id', $campoCentroCusto)
                            ->where('filial', true);
                    }
                });
            }
        }

        return $resultado->orderByDesc('created_at');
    }
}
