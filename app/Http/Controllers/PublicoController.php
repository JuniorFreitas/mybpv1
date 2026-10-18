<?php

namespace App\Http\Controllers;

use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Sistema;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicoController extends Controller
{

    public function download(Request $request, string $nome)
    {
        return Storage::disk('fotos_imovel')->url($nome);
    }

    public function upload(Request $request)
    {
        if ($request->arquivo->getClientOriginalName() == "Edital da UFMA.pdf") {
            return response()->json([
                'msg' => "O upload do arquivo \"{$request->arquivo->getClientOriginalName()}\" falhou",
                'erros' => []
            ], 400);
        } else {
            return response()->json([
                'id' => '300',
                'nome' => $request->arquivo->getClientOriginalName(),
                'imagem' => true,
                'extensao' => '.jpg',
                'thumb' => 'https://osegredo.com.br/wp-content/uploads/2017/09/O-que-as-pessoas-felizes-t%C3%AAm-em-comum-site-830x450.jpg',
            ], 201);
        }
    }

    public function listaVagas(Request $request)
    {
        $empresaId = $this->empresaIdObrigatorio($request);

        return response()->json([
            'vagas' => Vaga::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->whereAtivo(true)
                ->get(),
        ], 200);
    }

    public function listaAreasEtiquetas(Request $request)
    {
        $empresaId = $this->empresaIdObrigatorio($request);

        return response()->json([
            'areas' => AreaEtiqueta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->whereAtivo(true)
                ->get(),
        ], 200);
    }

    public function listaAreasEtiquetasCliente(Request $request, Cliente $cliente)
    {
        return response()->json($cliente->AreasEtiquetas()->whereAtivo(true)->get());
    }

    public function listaCentroCusto(Request $request)
    {
        $empresaId = $this->empresaIdObrigatorio($request);
        $incluirInativos = $request->boolean('incluir_inativos');

        $query = CentroCusto::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->select(['id', 'label', 'ativo', 'empresa_id']);
        if (!$incluirInativos) {
            $query->whereAtivo(true);
        }

        $centros = $query
            ->with('Filiais', function ($query) use ($empresaId) {
                $query->select(['id', 'centro_custo_id', 'cliente_filial_id', 'empresa_id'])
                    ->where('empresa_id', $empresaId)
                    ->whereAtivo(true)
                    ->with('Filial:id,dados->razao_social as razao_social,dados->nome_fantasia as nome_fantasia');
            })
            ->get()
            ->transform(function ($item) {
                $item->text = $item->label;
                if (!$item->ativo) {
                    $item->label = $item->label . ' - (-- Inativo --)';
                }
                return $item;
            });

        if ($incluirInativos) {
            $centros = $centros
                ->sortBy(fn ($item) => strtolower($item->label))
                ->sortByDesc('ativo')
                ->values();
        }

        return ['centro_custos' => $centros];
    }

    public function listaCentroCustoPorCnpj(Request $request)
    {
        return (new CentroCusto())->listaCentroCustoPorCnpj($request);
    }

    public function cnpjbusca(Request $request)
    {
        return Sistema::cnpjSearch($request->cnpj);
    }

    private function empresaIdObrigatorio(Request $request): int
    {
        $empresaId = (int) $request->input('empresa_id', $request->query('empresa_id', 0));
        abort_unless($empresaId > 0, 400, 'empresa_id é obrigatório');

        $ativa = Cliente::withoutGlobalScopes()
            ->where('id', $empresaId)
            ->where('ativo', true)
            ->exists();
        abort_unless($ativa, 404, 'Empresa não encontrada');

        return $empresaId;
    }
}
