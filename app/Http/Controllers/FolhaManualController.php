<?php

namespace App\Http\Controllers;

use App\Jobs\controle_ponto\JobExportaPontoManualPdf;
use App\Models\FeedbackCurriculo;
use App\Models\Feriado;
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
        $query = FeedbackCurriculo::select(['id', 'curriculo_id', 'empresa_id', 'vagas_abertas_id'])
            ->whereHas('Admissao', function ($query) {
                $query->where('status', 'admitido');
            })
            ->with(
                'Admissao:id,feedback_id,data_admissao,cargo,funcao,pis,centro_custo_id,matricula',
                'Admissao.DadosAdmissoes',
                'Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'Empresa:id,cnpj,razao_social,nome_fantasia,cep,logradouro,numero,complemento,bairro,municipio,uf,contato',
                'Empresa.Logo:id,nome,layout,disco,imagem,file,thumb',
                'Admissao.CentroCusto:id,label'
            );

        if ($request->filled('campoBusca')) {
            $query->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        return $query;
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
            ]
        ]);
    }

    public function imprimir(Request $request)
    {
//        $resultado = $this->filtro($request)->whereIn('id', $request->selecionados)->get()->map(function ($item) {
//            $ctps_numero = $item->Admissao->DadosAdmissoes ? $item->Admissao->DadosAdmissoes->ctps_numero : '';
//            $ctps_serie = $item->Admissao->DadosAdmissoes ? $item->Admissao->DadosAdmissoes->ctps_serie : '';
//            return [
//                'nome' => $item->Curriculo->nome,
//                'cargo' => $item->Admissao->cargo,
//                'centro_custo' => $item->Admissao->CentroCusto ? $item->Admissao->CentroCusto->label : 'Não Informado',
//                'data_admissao' => $item->Admissao->data_admissao,
//                'funcao' => $item->Admissao->funcao,
//                'pis' => $item->Admissao->pis,
//                'ctps' => $ctps_numero . '-' . $ctps_serie,
//                'empresa' => $item->Empresa,
//                'matricula' => $item->Admissao->matricula
//            ];
//        })->toArray();
//
//        $dataInicio = new DataHora($request->data_inicio);
//        $dataFim = new DataHora($request->data_fim);
//
//        $calendario = [];
//        $qntDias = DataHora::diferencaDias($dataInicio->dataInsert(), $dataFim->dataInsert());
//
//        $dataInicio->subtrairDia(1);
//
//
//        $repouso = collect($request->dias)->filter(function ($item) {
//            return $item['repouso'];
//        })->map(function ($item) {
//            return $item['label'];
//        })->toArray();
//
//        $dias_normais = collect($request->dias)->filter(function ($item) {
//            return !$item['repouso'];
//        });
//
//        foreach (range(0, $qntDias) as $d) {
//            $dia = $dataInicio->addDia(1);
//            $calendario[] = [
//                'feriado' => (bool)Feriado::where('data', (new DataHora($dia))->dataInsert())->select(['id'])->where('ativo', true)->first(),
//                'dia' => substr($dia,0,2),
//                'diaExt' => substr((new DataHora($dia))->diaSemanaExtM(), 0, (new DataHora($dia))->diaSemanaExtM() == 'Sábado' ? 4 : 3),
//                'repouso' => in_array((new DataHora($dia))->diaSemanaExtM(), $repouso),
//                'entrada' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['entrada'] : '',
//                'intervalo_almoco' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['intervalo_almoco'] : '',
//                'fim_intervalo_almoco' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['fim_intervalo_almoco'] : '',
//                'saida' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['saida'] : '',
//            ];
//        }
//
//        $dados = [
//            'periodo' => DataHora::dataFormatada($request->data_inicio) . ' à ' . DataHora::dataFormatada($request->data_fim),
//            'calendario' => $calendario,
//            'selecionados' => $resultado,
//            'repouso' => $repouso,
//            'dias_normais' => $dias_normais,
//            'empresa' => $resultado[0]['empresa'],
//        ];

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
