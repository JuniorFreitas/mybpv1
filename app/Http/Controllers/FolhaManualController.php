<?php

namespace App\Http\Controllers;

use App\Jobs\controle_ponto\JobExportaPontoManualPdf;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FolhaManualController extends Controller
{
    public function index()
    {
        return view('g.controle-ponto.folha-manual.index');
    }

    public function filtro(Request $request)
    {
        $query = DB::table('feedback_curriculos AS fc')
            ->join('curriculos AS c', 'fc.curriculo_id', '=', 'c.id')
            ->join('admissoes AS a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('a.status', '=', 'admitido')
                    ->whereNull('a.deleted_at');
            })
            ->join('centro_custos AS cc', 'a.centro_custo_id', '=', 'cc.id')
            ->leftJoin('centro_custo_filials AS ccf', 'cc.id', '=', 'ccf.centro_custo_id')
            ->leftJoin('cliente_filials AS cf', 'ccf.cliente_filial_id', '=', 'cf.id')
            ->leftJoin('dados_admissaos AS da2', 'a.id', '=', 'da2.admissao_id')
            ->leftJoin('demissaos AS d2', 'fc.id', '=', 'd2.feedback_id')
            ->whereNull('fc.deleted_at')
            ->where('fc.empresa_id', auth()->user()->empresa_id);

        if ($request->filled('campoBusca')) {
            $query->where('c.nome', 'like', "%$request->campoBusca%");
        }

        if ($request->filled('campoCentroDeCusto')) {
            $query->where('cc.id', $request->campoCentroDeCusto);
        }

        if ($request->filled('campoCargo')) {
            $query->where('a.cargo', $request->campoCargo);
        }

        if ($request->campoStatus == 'demitido') {
            $query->where('a.status', 'admitido')
                ->where('a.deleted_at', null)
                ->where('d2.data_desmobilizacao', '<>', null)
                ->whereRaw('DATEDIFF(NOW(), d2.data_desmobilizacao) <= 30')
                ->select(
                    'fc.id',
                    DB::raw("CONCAT(c.nome, CASE WHEN d2.data_desmobilizacao IS NOT NULL THEN ' (Demitido)' ELSE '' END) AS nome"),
                    'c.nome AS nome_sem_status',
                    DB::raw("DATE_FORMAT(a.data_admissao, '%d/%m/%Y') AS data_admissao"),
                    'a.cargo',
                    'a.funcao',
                    'a.pis',
                    DB::raw("CONCAT(da2.ctps_numero, '-', da2.ctps_serie) AS ctps"),
                    'a.filial',
                    'cc.label AS centro_custo_label',
                    'cf.dados AS centro_custo_dados',
                    'a.centro_custo_filial_id',
                    'd2.data_desmobilizacao',
                    DB::raw('DATEDIFF(NOW(), d2.data_desmobilizacao) AS dias')
                );
        } else {
            $query->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('demissaos AS d')
                    ->whereRaw('fc.id = d.feedback_id');
            })
                ->select(
                    'fc.id',
                    DB::raw("CONCAT(c.nome, '') AS nome"),
                    'c.nome AS nome_sem_status',
                    DB::raw("DATE_FORMAT(a.data_admissao, '%d/%m/%Y') AS data_admissao"),
                    'a.cargo',
                    'a.funcao',
                    'a.pis',
                    DB::raw("CONCAT(da2.ctps_numero, '-', da2.ctps_serie) AS ctps"),
                    'a.filial',
                    'cc.label AS centro_custo_label',
                    'cf.dados AS centro_custo_dados',
                    'a.centro_custo_filial_id',
                    DB::raw('NULL AS data_desmobilizacao'),
                    DB::raw('NULL AS dias')
                );
        }

        return $query->orderBy('nome', 'ASC');
    }

    private function listaCentroCustos()
    {
        return DB::table('feedback_curriculos AS fc')
            ->join('admissoes AS a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('a.status', '=', 'admitido')
                    ->whereNull('a.deleted_at');
            })
            ->join('centro_custos AS cc', 'a.centro_custo_id', '=', 'cc.id')
            ->leftJoin('centro_custo_filials AS ccf', 'cc.id', '=', 'ccf.centro_custo_id')
            ->select('cc.label AS centro_custo_label', 'cc.id AS cc_id')
            ->whereNull('fc.deleted_at')
            ->where('fc.empresa_id', auth()->user()->empresa_id)
            ->orderBy('centro_custo_label', 'ASC')
            ->distinct()
            ->get();
    }

    private function listaCargos()
    {
        return DB::table('feedback_curriculos AS fc')
            ->join('admissoes AS a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('a.status', '=', 'admitido')
                    ->whereNull('a.deleted_at');
            })
            ->select('a.cargo AS cargo')
            ->whereNull('fc.deleted_at')
            ->where('fc.empresa_id', auth()->user()->empresa_id)
            ->whereNotNull('a.cargo')
            ->orderBy('a.cargo', 'ASC')
            ->distinct()
            ->get();
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'lista_centro_custos' => $this->listaCentroCustos(),
                'lista_cargos' => $this->listaCargos(),
            ]
        ]);
    }

    public function imprimir(Request $request)
    {

        $dados = collect($request->input())->toArray();

        $dados['usuario_id'] = auth()->id();
        $dados['quem_gerou'] = auth()->user()->nome;

        JobExportaPontoManualPdf::dispatch(auth()->user()->id, $dados);

        return 'ok';
//        return view('pdf.controle-ponto.ponto-manual.manual', $dados);

        $view = 'pdf.controle-ponto.ponto-manual.manual';
        $nameArquivo = "folha_ponto_manual" . (new DataHora())->nomeUnico() . ".pdf";
        $pdf = \PDF::loadView($view, $dados);

        return $pdf->download($nameArquivo);
    }

    public function pdf()
    {
        $dados = [
            "periodo" => "21/02/2023 à 23/03/2023",
            "empresa" => [
                "id" => 104,
                "cnpj" => "22.055.835/0001-81",
                "razao_social" => "BPSE BUSINESS PARTNERS SERVICOS EMPRESARIAIS EIRELI",
                "nome_fantasia" => "BPSE",
                "cep" => "65065-180",
                "logradouro" => "Avenida dos Holandeses",
                "numero" => null,
                "complemento" => "Luminy Plaza",
                "bairro" => "Olho D'Água",
                "municipio" => "São Luís",
                "uf" => "MA",
                "contato" => "Danielle Sanches",
                "endereco_completo" => "Avenida dos Holandeses, Luminy Plaza, S/N, Olho D'Água, 65065-180, São Luís-MA",
                "logo" => [
                    [
                        "id" => 96,
                        "nome" => "logo_bpse",
                        "layout" => "retrato",
                        "disco" => "disco-cliente",
                        "imagem" => true,
                        "file" => "xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ.png",
                        "thumb" => "xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ_p.png",
                        "url" => "https://mybp-dev.s3.amazonaws.com/arquivos/disco-cliente/xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ.png",
                        "urlThumb" => "https://mybp-dev.s3.amazonaws.com/arquivos/disco-cliente/xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ.png",
                        "urlDownload" => "https://mybp-dev.s3.amazonaws.com/arquivos/disco-cliente/xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ.png",
                        "urlDelete" => "http://localhost:8000/g/administracao/clientes/anexo/xMpHjIrz1jrpSA1H9HQ3hvRbcuJyqahDpxkN3FfZ.png",
                        "pivot" => [
                            "cliente_id" => 104,
                            "arquivo_id" => 96
                        ]
                    ]
                ]
            ],
            "quem_gerou" => 'Junior Freitas',
            "calendario" => [
                [
                    "feriado" => true,
                    "dia" => "21",
                    "diaExt" => "Ter",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "22",
                    "diaExt" => "Qua",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "23",
                    "diaExt" => "Qui",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "24",
                    "diaExt" => "Sex",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "25",
                    "diaExt" => "Sáb",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "26",
                    "diaExt" => "Dom",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "27",
                    "diaExt" => "Seg",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "28",
                    "diaExt" => "Ter",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "01",
                    "diaExt" => "Qua",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "02",
                    "diaExt" => "Qui",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "03",
                    "diaExt" => "Sex",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "04",
                    "diaExt" => "Sáb",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "05",
                    "diaExt" => "Dom",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "06",
                    "diaExt" => "Seg",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "07",
                    "diaExt" => "Ter",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "08",
                    "diaExt" => "Qua",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "09",
                    "diaExt" => "Qui",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "10",
                    "diaExt" => "Sex",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "11",
                    "diaExt" => "Sáb",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "12",
                    "diaExt" => "Dom",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "13",
                    "diaExt" => "Seg",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "14",
                    "diaExt" => "Ter",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "15",
                    "diaExt" => "Qua",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "16",
                    "diaExt" => "Qui",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "17",
                    "diaExt" => "Sex",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "18",
                    "diaExt" => "Sáb",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "19",
                    "diaExt" => "Dom",
                    "repouso" => true,
                    "entrada" => "",
                    "intervalo_almoco" => "",
                    "fim_intervalo_almoco" => "",
                    "saida" => ""
                ],
                [
                    "feriado" => false,
                    "dia" => "20",
                    "diaExt" => "Seg",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "21",
                    "diaExt" => "Ter",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "22",
                    "diaExt" => "Qua",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ],
                [
                    "feriado" => false,
                    "dia" => "23",
                    "diaExt" => "Qui",
                    "repouso" => false,
                    "entrada" => "07:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "saida" => "17:30"
                ]
            ],
            "selecionados" => [
                [
                    "nome" => "ADRIANNE SILVA SALES PROHMANN",
                    "cargo" => "ANALISTA DE GENTE E GESTÃO PL",
                    "centro_custo" => "Não Informado",
                    "data_admissao" => "19/10/2022",
                    "funcao" => "ANALISTA DE GENTE E GESTÃO PL",
                    "pis" => "13081939371",
                    "ctps" => "077259-0024",

                    "matricula" => null
                ]
            ],
            "repouso" => [
                "sabado" => "Sábado",
                "domingo" => "Domingo"
            ],
            "dias_normais" => [
                "segunda" => [
                    "label" => "Segunda",
                    "entrada" => "07:30",
                    "saida" => "17:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "repouso" => false
                ],
                "terca" => [
                    "label" => "Terça",
                    "entrada" => "07:30",
                    "saida" => "17:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "repouso" => false
                ],
                "quarta" => [
                    "label" => "Quarta",
                    "entrada" => "07:30",
                    "saida" => "17:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "repouso" => false
                ],
                "quinta" => [
                    "label" => "Quinta",
                    "entrada" => "07:30",
                    "saida" => "17:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "repouso" => false
                ],
                "sexta" => [
                    "label" => "Sexta",
                    "entrada" => "07:30",
                    "saida" => "17:30",
                    "intervalo_almoco" => "12:00",
                    "fim_intervalo_almoco" => "13:00",
                    "repouso" => false
                ]
            ]
        ];

        return view('pdf.controle-ponto.ponto-manual.manual', compact('dados'));
    }
}
