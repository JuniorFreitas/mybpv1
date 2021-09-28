<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\PeriodoPontoEletronico;
use App\Models\PontoEletronico;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class PontoEletronicoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('g.controle-ponto.ponto-eletronico.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'foto' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao registrar o ponto',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {

            try {
                // verificar tambem inicio da primeiro periodos e o fim da ultimo periodo
                \DB::beginTransaction();
                $usuario = auth()->user();
                $jornada = null;
                if (isset($usuario->EscalasFuncionario[0])) {
                    $jornada = PontoEletronico::getJornadaAtual($usuario->EscalasFuncionario[0]->id);
                    $periodos = $jornada->periodos;
                }
                if($jornada==null){
                    return response()->json(['msg' => 'Nenhuma escala de trabalhado vinculada ao funcionário'], 400);
                }

                $hoje = new DataHora();
                $image_parts = explode(";base64,", $request->foto);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];

                $image_base64 = base64_decode($image_parts[1]);
                $nome = \Str::random(40) . ".$image_type";
                \Storage::disk(Arquivo::DISCO_PONTO_ELETRONICO)->put($nome,$image_base64);
                $ponto = PontoEletronico::periodoAberto($hoje->dataInsert()); // pegar o ponto com periodo ainda aberto
                if(!$ponto){
                    // se nao tem ponto aberto, verificar se tem pelo menos alum ponto aberto hoje
                    $ponto = PontoEletronico::whereDate('created_at',$hoje->dataInsert())
                        ->whereFuncionarioId($usuario->id)->first();
                }
                $arquivo=null;
                if($ponto){
                    $arquivo = Arquivo::create([
                        'quem_enviou' => auth()->id(),
                        'nome' => 'controle_ponto_id_'.$ponto->id,
                        'imagem' => true,
                        'layout' => 'retrato',
                        'extensao' => ".$image_type",
                        'file' => $nome,
                        'thumb' => $nome,
                        'bytes' => \Storage::disk(Arquivo::DISCO_PONTO_ELETRONICO)->getSize($nome),
                        'temporario' => false,
                        'chave' => null,
                    ]);
                    $periodo = $ponto->PeriodosEmAberto()->first();
                    if($periodo){
                        $periodo->autenticacao_saida = \Str::random(40);
                        $periodo->saida = $hoje->dataHoraInsert();
                        $periodo->facial_saida = false;
                        $periodo->lat_saida = $request->lat;
                        $periodo->long_saida = $request->long;
                        $periodo->minutos = DataHora::diferencaMinutos($periodo->entrada, $periodo->saida);

                        $periodo->arquivo_id_saida = $arquivo->id;
                        $periodo->save();

                        // atualizando as duracoes realizadas na saida
                        $ponto->recalcularDuracoes();
                    }else{
                        $periodo = $ponto->Periodos()->create([
                            //'autenticacao_entrada'=>\Str::random(40),
                            'entrada'=>$hoje->dataHoraInsert(),
                            'facial_entrada'=>false,
                            'arquivo_id_entrada'=>$arquivo->id,
                            'lat_entrada'=>$request->lat,
                            'long_entrada'=>$request->long,
                            'minutos'=>0,
                        ]);
                        $periodo->autenticacao_entrada = \Str::random(40);
                        $periodo->save();
                    }

                }else{
                    $config = $usuario->ConfigEmpresa;
                    $ponto = new PontoEletronico();
                    $ponto->jornada_id = $jornada->id;
                    $ponto->ocorrencia_id = $jornada->ocorrencia_id;

                    $ponto->tipo_frequencia = $config->tipo_frequencia;
                    $ponto->tempo_limite_falta = $config->tempo_limite_falta;
                    $ponto->tempo_limite_saida = $config->tempo_limite_saida;
                    $ponto->limite_tolerancia = $config->limite_tolerancia;
                    $ponto->verificado = false;
                    $ponto->duracao = $jornada->getTotalMinutos();

                    $ponto->save();

                    $arquivo = Arquivo::create([
                        'quem_enviou' => auth()->id(),
                        'nome' => 'controle_ponto_id_'.$ponto->id,
                        'imagem' => true,
                        'layout' => 'retrato',
                        'extensao' => ".$image_type",
                        'file' => $nome,
                        'thumb' => $nome,
                        'bytes' => \Storage::disk(Arquivo::DISCO_PONTO_ELETRONICO)->getSize($nome),
                        'temporario' => false,
                        'chave' => null,
                    ]);

                    $periodo = $ponto->Periodos()->create([
                        //'autenticacao_entrada'=>\Str::random(40),
                        'entrada'=>$hoje->dataHoraInsert(),
                        'facial_entrada'=>false,
                        'arquivo_id_entrada'=>$arquivo->id,
                        'lat_entrada'=>$request->lat,
                        'long_entrada'=>$request->long,
                        'minutos'=>0,
                    ]);
                    $periodo->autenticacao_entrada = \Str::random(40);
                    $periodo->save();


                }
                \DB::commit();

                $inicio = new DataHora($hoje->dataCompleta() . ' 00:00:00');
                $fim = new DataHora($hoje->dataCompleta() . ' 23:59:59');
                $consulta = PontoEletronico::whereFuncionarioId(auth()->id())
                    ->whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
                    ->orderBy('created_at')
                    ->with('Periodos:id,ponto_id,entrada,saida')
                    ->get();
                $minutosTrabalhados = 0;
                foreach ($consulta as $ponto){
                    $minutosTrabalhados += intval($ponto->Periodos()->sum('minutos'));
                }

                return response()->json([
                    'registrosHoje' => $consulta,
                    'duracao' => $jornada ? PontoEletronico::duracaoJornada($jornada):null,
                    'minutos_trabalhados' => $minutosTrabalhados
                ], 200);

            } catch (\Exception $exception) {
                \DB::rollBack();
                if (isset($arquivo)) {
                    $arquivo->excluir();
                }else{
                    \Storage::disk(Arquivo::DISCO_PONTO_ELETRONICO)->delete($nome);
                }


                return response()->json(['msg' => $exception->getMessage()], 500);
            }

        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Models\PontoEletronico $pontoEletronico
     * @return \Illuminate\Http\Response
     */
    public function show(PontoEletronico $ponto) {

    }

    public function showPeriodo(PontoEletronico $ponto,PeriodoPontoEletronico $periodo){
        if($ponto->funcionario_id == auth()->id()){
            $periodo->load(['FotoEntrada','FotoSaida']);
            $periodo->justificativa = $ponto->justificativa;
            return response($periodo,200);
        }else{
            return response([],403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Models\PontoEletronico $pontoEletronico
     * @return \Illuminate\Http\Response
     */
    public function edit(PontoEletronico $pontoEletronico) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Models\PontoEletronico $pontoEletronico
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PontoEletronico $pontoEletronico) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Models\PontoEletronico $pontoEletronico
     * @return \Illuminate\Http\Response
     */
    public function destroy(PontoEletronico $pontoEletronico) {
        //
    }

    public function atualizarHistorico(Request $request) {
        $inicio = new DataHora($request->data . ' 00:00:00');
        $fim = new DataHora($inicio->dataCompleta() . ' 23:59:59');
        $usuario = auth()->user();
        $jornada = null;
        if (isset($usuario->EscalasFuncionario[0])) {
            $jornada = PontoEletronico::getJornadaAtual($usuario->EscalasFuncionario[0]->id,$inicio->dataInsert());
        }
        $historicos = PontoEletronico::whereFuncionarioId(auth()->id())
            ->whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            ->orderBy('created_at')
            ->with(['Periodos:id,ponto_id,entrada,saida',
                'Jornada.Ocorrencia'=> function($q){
                    return $q->withoutGlobalScopes();
                }
                ,'Ocorrencia'])
            ->get();

        $minutosTrabalhados = 0;
        foreach ($historicos as $ponto){
            $minutosTrabalhados += intval($ponto->Periodos()->sum('minutos'));
        }
        return response()->json([
            'historicos' => $historicos,
            'duracao' => $jornada ? PontoEletronico::duracaoJornada($jornada):null,
            'minutos_trabalhados' => $minutosTrabalhados,
        ]);
    }

    public function init(Request $request) {
        $usuario = auth()->user();
        $usuario->load('EscalasFuncionario.Jornadas',
        //'EscalasFuncionario.Jornadas.Periodos',
        );
        $jornada = null;
        if (isset($usuario->EscalasFuncionario[0])) {
            $jornada = PontoEletronico::getJornadaAtual($usuario->EscalasFuncionario[0]->id);
            $periodos = $jornada->periodos;
        }
        $hoje = new DataHora();

        $inicio = new DataHora($hoje->dataCompleta() . ' 00:00:00');
        $fim = new DataHora($hoje->dataCompleta() . ' 23:59:59');
        $registrosHoje = PontoEletronico::whereFuncionarioId(auth()->id())
            ->whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            ->orderBy('created_at')
            ->with('Periodos:id,ponto_id,entrada,saida')
            ->get();

        $minutosTrabalhados = 0;
        foreach ($registrosHoje as $ponto){
            $minutosTrabalhados += intval($ponto->Periodos()->sum('minutos'));
        }

        return response()->json([
            'lista_perimetros' => $usuario->PerimetrosFuncionario,
            'lista_escalas' => $usuario->EscalasFuncionario[0],
            'periodos' => $periodos,
            'duracao' => $jornada ? PontoEletronico::duracaoJornada($jornada):null,
            'registros' => $registrosHoje,
            'minutos_trabalhados' => $minutosTrabalhados,
            'agora' => $hoje->dataHoraInsert(),
        ]);
    }

    public function fotoShow(Request $request, $arquivo)
    {

        /*$path = Arquivo::buscaPath($arquivo);
        if ($path == false) {
            return response("", 404);
        } else {
            return \Storage::disk($disco)->response($arquivo);
            $conteudo = Arquivo::buscaConteudo($arquivo);
            header("Content-type: " . Arquivo::getMimeType($path));
            header('Content-Length: ' . filesize($path));
            echo $conteudo;
        }*/


        $path = Arquivo::buscaPath($arquivo);
        if ($path == false) {
            return response("", 404);
        } else {
            $disco = Arquivo::nomeDisco($arquivo);

            return \Storage::disk($disco)->response($arquivo);
        }
    }
}
