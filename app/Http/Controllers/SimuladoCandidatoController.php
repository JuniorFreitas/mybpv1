<?php

namespace App\Http\Controllers;

use App\Models\Curriculo;
use App\Models\SimuladoCandidato;
use App\Models\SimuladoCandidatoResposta;
use App\Models\SimuladoVaga;
use App\Models\Sistema;
use App\Models\Vinculo;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class SimuladoCandidatoController extends Controller
{
    public function index($vaga_id, $simulado_id, $slug)
    {
        $simulado = SimuladoVaga::with('Simulado')
            ->whereSimuladoId($simulado_id)
            ->whereVagaId($vaga_id)
            ->whereHas('Simulado', function ($q) {
                $q->whereAtivo(true);
            });

        if ($simulado->count() == 0) {
            abort(404);
        }
        $simulado = $simulado->first();
        return view('provas.index', compact('simulado'));
    }

    public function autenticar(Request $request)
    {
        $cpf = Sistema::transformCpfCnpj($request->cpf);

        $dataNascimento = Sistema::dataTransform($request->nascimento);
        if (!Sistema::validaCPF($cpf)) {
            return response()->json(['msg' => 'CPF inválido'], 400);
        }
        $candidato = Curriculo::whereCpf($cpf)
            ->whereNascimento((new DataHora($dataNascimento))->dataInsert())
            ->whereHas('FeedBack', function ($q) use ($request) {
                $q->whereVagaId($request->vaga_id);
            })
            ->with('FeedBack')
            ->with(['Vinculo' => function ($q) use ($request) {
                $q->whereVagaId($request->vaga_id);
            }]);
        if ($candidato->count() == 0) {
            return response()->json(['msg' => 'Não foi possivel autenticar, CPF e/ou Data de Nascimento inválido!', 'autenticado' => false], 400);
        } else {
            $candidato = $candidato->first();
            return response()->json(['curriculo' => $candidato, 'autenticado' => true]);
        }
    }

    public function getSimulado(Request $request)
    {
        $simuladoCandidato = SimuladoCandidato::whereSimuladoVagaId($request->simulado_vaga_id)
            ->whereCurriculoId($request->curriculo_id)
            ->whereHas('SimuladoVaga.Simulado', function ($query) use ($request) {
                $query->whereId($request->simulado_id);
            });

        if ($simuladoCandidato->count() == 0) {
            $simuladoVaga = SimuladoVaga::whereId($request->simulado_vaga_id)->first();

            $simuladoCandidato->create([
                'simulado_vaga_id' => $request->simulado_vaga_id,
                'curriculo_id' => $request->curriculo_id,
                'duracao_segundos' => $simuladoVaga->duracao,
                'finalizado' => false,
            ]);

        }

        $simuladoCandidato = $simuladoCandidato->first()->load('SimuladoVaga.Simulado.Perguntas.Respostas');
        return response()->json($simuladoCandidato, 200);

    }

    public function gravaTempo(Request $request)
    {
        SimuladoCandidato::whereSimuladoVagaId($request->simulado_vaga_id)
            ->whereCurriculoId($request->curriculo_id)
            ->first()->update([
                'duracao_segundos' => $request->tempo
            ]);

        return response()->json([], 201);
    }

    public function responder(Request $request)
    {
        $simuladoRespCandidato = SimuladoCandidatoResposta::whereCurriculoId($request->curriculo_id)
            ->whereSimuladoVagaId($request->simulado_vaga_id)
            ->whereSimuladoPerguntaId($request->simulado_pergunta_id);

        if ($simuladoRespCandidato->count() == 0) {
            SimuladoCandidatoResposta::create([
                'simulado_vaga_id' => $request->simulado_vaga_id,
                'curriculo_id' => $request->curriculo_id,
                'simulado_pergunta_id' => $request->simulado_pergunta_id,
                'simulado_resposta_id' => $request->simulado_resposta_id,
            ]);
            return response()->json('', 201);
        } else {
            $simuladoRespCandidato->update(
                ['simulado_resposta_id' => $request->simulado_resposta_id]
            );
            return response()->json('', 201);
        }
    }

    public function finalizar(Request $request)
    {
        $agora = (new DataHora())->dataHoraInsert();


        $simulado = SimuladoCandidato::whereSimuladoVagaId($request->simulado_vaga_id)
            ->whereCurriculoId($request->curriculo_id)
            ->first();

        $respostaSimulado = SimuladoVaga::whereId($simulado->simulado_vaga_id)
            ->whereSimuladoId($request->simulado_id)->first()
            ->Perguntas->transform(function ($item) {
                $item->correto = null;
                foreach ($item->Respostas as $r) {
                    if ($r->correto) {
                        $item->correto = $r->id;
                    }
                }
                return $item;
            });


        $candidatoRespostaSimulado = SimuladoCandidatoResposta::whereSimuladoVagaId($request->simulado_vaga_id)
            ->whereCurriculoId($request->curriculo_id)->get();

        $acertos = 0;
        foreach ($respostaSimulado as $respSim) {
            foreach ($candidatoRespostaSimulado as $caResp) {
                if ($respSim->correto == $caResp->simulado_resposta_id) {
                    $acertos++;
                }
            }
        }

        $simulado->update([
            'duracao_segundos' => $request->tempo,
            'finalizado' => true,
            'data_finalizacao' => $agora,
            'acertos' => $acertos
        ]);


        return response()->json(['finalizado' => true, 'data_finalizacao' => (new DataHora($agora))->dataCompleta() . ' ' . (new DataHora($agora))->horaCompleta()], 201);
    }

    public function salvarVinculo(Request $request)
    {
//        dd('oi');
        $dados = $request->input();
//        $dados['parente'] = ;
        Vinculo::create($dados);
        return response()->json([],201);
    }

}
