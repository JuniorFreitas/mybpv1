<?php

namespace App\Http\Controllers;

use App\Models\Simulado;
use App\Models\SimuladoPergunta;
use App\Models\SimuladoResposta;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class SimuladoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.provas.index');
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
        $dados = $request->input();

        if (!isset($dados['perguntas'])) {
            return response()->json([
                'msg' => 'ERRO: É necessário inserir Questões',
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'titulo' => 'required',
//            'perguntas.*.enunciado' => 'required|unique:simulado_perguntas,enunciado,' . $prova->id,
            'ativo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Simulado',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $perguntas = collect($dados['perguntas']);

                if ($perguntas->duplicates('enunciado')->count() > 0) {
                    return response()->json([
                        'msg' => "Verifique as questões com o enunciados duplicados!",
                    ], 400);
                }

                //Cria um registro Simulado e salva o id
                $prova = Simulado::create($dados);

                foreach ($perguntas as $pergunta) {
                    //Verifica se o tipo da prova é objetiva
                    if ($dados['tipo_prova'] === 'objetiva') {
                        //Verifico se tem Respostas
                        if (!isset($pergunta['respostas'])) {
                            return response()->json([
                                'msg' => 'ERRO: É necessário inserir uma opção para questão',
                            ], 400);
                        }
                        //Converte Imagens para a url base
//                    $pergunta['enunciado'] = str_replace('../responsive_filemanager/', asset('') . "responsive_filemanager/", $pergunta['enunciado']);
//                    $pergunta['enunciado'] = str_replace('<img src="' . asset(''), '<img class="img-fluid d-block mx-auto" src="' . asset(''), $pergunta['enunciado']);

                        $respostas = collect($pergunta['respostas']);

                        // Se possuir somente 1 ou nenhuma questão
                        if ($respostas->count() <= 1) {
                            return response()->json([
                                'msg' => 'ERRO: A Questão' . $pergunta['enunciado'] . ' deve ter MAIS de uma OPÇÃO',
                            ], 400);
                        }

                        // Se não tiver nenhuma opção
                        if ($respostas->where('correto')->count() == 0) {
                            return response()->json([
                                'msg' => 'A Questão' . $pergunta['enunciado'] . ' não possui nenhuma OPÇÃO MARCADA como CORRETA',
                            ], 400);
                        }

                        // Se Houver a Opção e tiver mais de 1 true
                        if ($respostas->where('correto')->count() > 1) {
                            return response()->json([
                                'msg' => 'A Questão' . $pergunta['enunciado'] . ' possui mais de uma OPÇÃO MARCADA como CORRETA',
                            ], 400);
                        }

                        // Se Houver Respostas duplicada
                        if ($respostas->duplicates('resposta')->count() > 0) {
                            return response()->json([
                                'msg' => 'Verifique as respostas duplicadas',
                            ], 400);
                        }
                    }

                    if (isset($pergunta['perguntasDelete'])) {
                        foreach ($pergunta['perguntasDelete'] as $lin) {
                            $prova->Perguntas()->find($lin)->delete();
                        }
                    }

                    $perg = $prova->Perguntas()->create($pergunta);

                    if ($dados['tipo_prova'] === 'objetiva') {
                        foreach ($respostas as $resposta) {
                            $resposta['simulado_pergunta_id'] = $perg->id;
                            SimuladoResposta::create($resposta);
                        }
                    }
                };

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
     * @param \App\Models\Simulado $prova
     * @return \Illuminate\Http\Response
     */
    public function show(Simulado $prova)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Simulado $prova
     * @return Simulado|\Illuminate\Http\Response
     */
    public function edit(Simulado $prova)
    {
        return $prova->load('Perguntas.Respostas');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Simulado $prova
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Simulado $prova)
    {
        $dados = $request->input();

        if (!isset($dados['perguntas'])) {
            return response()->json([
                'msg' => 'ERRO: É necessário inserir Questões',
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'titulo' => 'required',
//            'perguntas.*.enunciado' => 'required|unique:simulado_perguntas,enunciado,' . $prova->id,
            'ativo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Simulado',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $perguntas = collect($dados['perguntas']);

                if ($perguntas->duplicates('enunciado')->count() > 0) {
                    return response()->json([
                        'msg' => "Verifique as questões com o enunciados duplicados!",
                    ], 400);
                }

                if (isset($dados['respostasDelete'])) {
                    foreach ($dados['respostasDelete'] as $lin) {
                        SimuladoResposta::find($lin)->delete();
                    }
                }

                if (isset($dados['perguntasDelete'])) {
                    foreach ($dados['perguntasDelete'] as $lin) {
                        SimuladoPergunta::find($lin)->delete();
                    }
                }

                foreach ($perguntas as $pergunta) {
                    //Verifico se tem Respostas
                    if (!isset($pergunta['respostas'])) {
                        return response()->json([
                            'msg' => 'ERRO: É necessário inserir uma opção para questão',
                        ], 400);
                    }
                    //Converte Imagens para a url base
//                    $pergunta['enunciado'] = str_replace('../responsive_filemanager/', asset('') . "responsive_filemanager/", $pergunta['enunciado']);
//                    $pergunta['enunciado'] = str_replace('<img src="' . asset(''), '<img class="img-fluid d-block mx-auto" src="' . asset(''), $pergunta['enunciado']);

                    $respostas = collect($pergunta['respostas']);

                    // Se possuir somente 1 ou nenhuma questão
                    if ($respostas->count() <= 1) {
                        return response()->json([
                            'msg' => 'ERRO: A Questão' . $pergunta['enunciado'] . ' deve ter MAIS de uma OPÇÃO',
                        ], 400);
                    }

                    // Se não tiver nenhuma opção
                    if ($respostas->where('correto')->count() == 0) {
                        return response()->json([
                            'msg' => 'A Questão' . $pergunta['enunciado'] . ' não possui nenhuma OPÇÃO MARCADA como CORRETA',
                        ], 400);
                    }

                    // Se Houver a Opção e tiver mais de 1 true
                    if ($respostas->where('correto')->count() > 1) {
                        return response()->json([
                            'msg' => 'A Questão' . $pergunta['enunciado'] . ' possui mais de uma OPÇÃO MARCADA como CORRETA',
                        ], 400);
                    }

                    // Se Houver Respostas duplicada
                    if ($respostas->duplicates('resposta')->count() > 0) {
                        return response()->json([
                            'msg' => 'Verifique as respostas duplicadas',
                        ], 400);
                    }

                    //Cria um registro Simulado e salva o id
                    $prova->update($dados);

                    if (isset($pergunta['perguntasDelete'])) {
                        foreach ($pergunta['perguntasDelete'] as $lin) {
                            $prova->Perguntas()->find($lin)->delete();
                        }
                    }

                    if (isset($pergunta['id'])) {

                        $prova->Perguntas()->find($pergunta['id'])->update($pergunta);
                        foreach ($respostas as $resposta) {
                            if (isset($resposta['id'])) {
                                $resposta['simulado_pergunta_id'] = $pergunta['id'];
                                SimuladoResposta::find($resposta['id'])->update($resposta);
                            } else {
                                $resposta['simulado_pergunta_id'] = $pergunta['id'];
                                SimuladoResposta::create($resposta);
                            }
                        }
                    } else {
                        $perg = $prova->Perguntas()->create($pergunta);
                        foreach ($respostas as $resposta) {
                            $resposta['simulado_pergunta_id'] = $perg->id;
                            SimuladoResposta::create($resposta);
                        }
                    }
                };

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
     * @param \App\Models\Simulado $prova
     * @return \Illuminate\Http\Response
     */
    public function destroy(Simulado $prova)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = Simulado::orderBy('titulo', $request->ordem ?: 'Asc');
        if ($request->filled('campoBusca')) {
            $resultado->where("titulo", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        return $resultado;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        return Sistema::pg($resultado);
    }

    /**
     * Ativa e desativa.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ativaDesativa(Simulado $prova)
    {
        return Sistema::ativaDesativa($prova);
    }
}
