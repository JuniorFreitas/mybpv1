<?php

namespace App\Http\Controllers;

use App\Jobs\JobBoasVindasClinica;
use App\Models\EmpresaExame;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmpresaExameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.empresa-exame.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_empresa_exame_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'dados.cnpj' => 'required|min:18',
            'dados.email' => 'required|email:rfc,dns',
            'ativo' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Empresa',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                $bairro = $dados['dados']['endereco']['bairro'];
                $cep = $dados['dados']['endereco']['cep'];
                $complemento = $dados['dados']['endereco']['complemento'] ? $dados['dados']['endereco']['complemento'] . ',' : '';
                $end_numero = $dados['dados']['endereco']['end_numero'] ? "Nº " . $dados['dados']['endereco']['end_numero'] : ' S/N';
                $logradouro = $dados['dados']['endereco']['logradouro'];
                $municipio = $dados['dados']['endereco']['municipio'];
                $uf = $dados['dados']['endereco']['uf'];

                $dados['dados']['endereco']['endereco_completo'] = "$logradouro, $end_numero , $complemento CEP: $cep, $bairro, $municipio/$uf";


                DB::beginTransaction();
                EmpresaExame::create($dados);
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
     * @param \App\Models\EmpresaExame $empresaExame
     * @return \Illuminate\Http\Response
     */
    public function show(EmpresaExame $empresaExame)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmpresaExame $empresaExame
     * @return \Illuminate\Http\Response
     */
    public function edit(EmpresaExame $empresaExame)
    {
        return $empresaExame;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\EmpresaExame $empresaExame
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, EmpresaExame $empresaExame)
    {
        $this->authorize('cadastro_empresa_exame_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'dados.cnpj' => 'required|min:18',
            'dados.email' => 'required|email:rfc,dns',
            'ativo' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a Empresa',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                $bairro = $dados['dados']['endereco']['bairro'];
                $cep = $dados['dados']['endereco']['cep'];
                $complemento = $dados['dados']['endereco']['complemento'] ? $dados['dados']['endereco']['complemento'] . ',' : '';
                $end_numero = $dados['dados']['endereco']['end_numero'] ? "Nº " . $dados['dados']['endereco']['end_numero'] : ' S/N';
                $logradouro = $dados['dados']['endereco']['logradouro'];
                $municipio = $dados['dados']['endereco']['municipio'];
                $uf = $dados['dados']['endereco']['uf'];

                $dados['dados']['endereco']['endereco_completo'] = "$logradouro, $end_numero , $complemento CEP: $cep, $bairro, $municipio/$uf";

                DB::beginTransaction();
                $grupo = Papel::whereEmpresaId($empresaExame->empresa_id)->Clinica()->first();
                if (is_null($empresaExame->user_id)) {
                    $password = Str::random(8);
                    $user = User::create([
                        'nome' => $dados['nome'],
                        'login' => $dados['dados']['email'],
                        'password' => bcrypt($password),
                        'tipo' =>  User::CLINICA_EXAME,
                        'temp' => false,
                        'grupo_id' => $grupo->id,
                        'cadastrou' => auth()->id(),
                        'empresa_id' => $empresaExame->empresa_id,
                        'ativo' => $dados['ativo'],
                    ]);
                    $dados['user_id'] = $user->id;

                    $dadosEmail = [
                        'nome' => $dados['nome'],
                        'email' => $dados['dados']['email'],
                        'senha' => $password,
                        'empresa_id' => $empresaExame->empresa_id,
                    ];
                    JobBoasVindasClinica::dispatch($dadosEmail);
                } else {
                    $empresaExame->Usuario->find($empresaExame->user_id)->update([
                        'nome' => $dados['nome'],
                        'grupo_id' => $grupo->id,
                        'login' => $dados['dados']['email'],
                        'ativo' => $dados['ativo'],
                    ]);
                }
                $empresaExame->update($dados);
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
     * @param \App\Models\EmpresaExame $empresaExame
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpresaExame $empresaExame)
    {
        $empresaExame->delete();
//        return response()->json([],200);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_empresa_exame_update');

        $empresa = EmpresaExame::find($request->id);
        $grupo = Papel::whereEmpresaId($empresa->empresa_id)->Clinica()->first();

        if (is_null($empresa->user_id)) {
            $password = Str::random(8);
            $user = User::create([
                'nome' => $empresa->nome,
                'login' => $empresa->dados['email'],
                'password' => bcrypt($password),
                'tipo' =>  User::CLINICA_EXAME,
                'grupo_id' => $grupo->id,
                'temp' => false,
                'cadastrou' => auth()->id(),
                'empresa_id' => $empresa->empresa_id,
                'ativo' => !$empresa->ativo,
            ]);
            $empresa->user_id = $user->id;

            $dadosEmail = [
                'nome' => $empresa->nome,
                'email' => $empresa->dados['email'],
                'senha' => $password,
                'empresa_id' => $empresa->empresa_id,
            ];
            JobBoasVindasClinica::dispatch($dadosEmail);
        } else {
            $empresa->Usuario->find($empresa->user_id)->update([
                'nome' => $empresa->nome,
                'login' => $empresa->dados['email'],
                'grupo_id' => $grupo->id,
                'ativo' => !$empresa->ativo,
            ]);
        }

        $empresa->ativo = !$empresa->ativo;
        $empresa->save();
        $empresa->refresh();


        return response()->json(['ativo' => $empresa->ativo], 201);
    }

    public function atualizar(Request $request)
    {
        $resultado = EmpresaExame::orderBy('nome');

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items()]
        ]);
    }
}
