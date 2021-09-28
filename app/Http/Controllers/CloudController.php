<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\GrupoCloud;
use App\Models\ItensCloud;
use Illuminate\Http\Request;

class CloudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cloud.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cloud_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'label' => 'required|min:1|unique:itens_cloud,label',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar nova pasta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {

            $dados['quem_criou'] = auth()->id();
            $cloud = ItensCloud::create($dados);

            $permissoes = collect([GrupoCloud::GRUPOADMIN,GrupoCloud::GRUPOADMINFINANCEIRO]);
            if ($request->filled('permissoes')) {
                $dadosPermissao = [];
                foreach ($dados['permissoes'] as $grupo) {
                    $dadosPermissao[] = $grupo['id'];
                }
                $permissoes = $permissoes->concat($dadosPermissao);
            }

            $cloud->Permissoes()->attach($permissoes);

            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Cloud $cloud
     * @return \Illuminate\Http\Response
     */
    public function show(Cloud $cloud)
    {
        return abort(403);
    }

    public function editarPasta(ItensCloud $item)
    {
        $iteCloud = $item;
        $iteCloud->permissoes = $item->Permissoes->transform(function ($i) {
            $i->permitido = true;
            return $i;
        });
        return $iteCloud;
    }

    public function getSingle($id)
    {
        $this->authorize('cloud');
        $cloud = Cloud::with('Itens', 'Raiz')->find($id);
        return view('g.cloud.index', compact('cloud'));
    }

    public function atualizar(Request $request, $cloud, $id = null)
    {
        $resultado = ItensCloud::whereCloudId($cloud)
            ->with(
                'Pertence:id,pertence',
                'Arquivo:id,bytes,file,extensao,thumb,imagem',
                'Criou:id,nome',
                'Editou:id,nome'
            );

        if (!$id) {
            $resultado->whereNull('pertence');
        }

        if ($id) {
            $itemBusca = ItensCloud::find($id);
            if (!$itemBusca) {
                return response()->json(['msg' => 'Pasta ou Arquivo não encontrado!'], 400);
            }

            if ($itemBusca->tipo == 'pasta') {
                if ($itemBusca->TemPermissao) {
                    $resultado->wherePertence($id);
                } else {
                    return response()->json(['msg' => 'Sem permissao para acessar a pasta',], 403);
                }
            } else {
                return response()->json(['msg' => 'O item não é uma pasta'], 400);
            }
        }

        $resultado = $resultado->orderBy('tipo')->orderBy('label')->get();

        $resultado->transform(function (ItensCloud $item) {
            $item->append('TemPermissao');
            return $item;
        });

        //Permitindo sempre para Grupo Todos
        $grupos = GrupoCloud::whereAtivo(true)->whereNotIn('id', [GrupoCloud::GRUPOADMIN,GrupoCloud::GRUPOADMINFINANCEIRO])->get()->transform(function ($item) {
            $item->permitido = false;
            return $item;
        });

        $habilidades = auth()->user()->GrupoCloud->Habilidades;

        return response()->json([
            'lista' => $resultado,
            'grupos' => $grupos,
            'habilidades' => $habilidades
        ]);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow([Arquivo::DISCO_CLOUD], $arquivo);
    }

    //anexo ou foto
    public function download($arquivo)
    {
        return Arquivo::anexoDownload([Arquivo::DISCO_CLOUD], $arquivo);
    }
}
