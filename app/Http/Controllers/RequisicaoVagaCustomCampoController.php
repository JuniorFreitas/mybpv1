<?php

namespace App\Http\Controllers;

use App\Models\RequisicaoVagaCustomCampo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequisicaoVagaCustomCampoController extends Controller
{
    private function empresaId()
    {
        return auth()->user()->empresa_id;
    }

    /**
     * Lista os campos custom da empresa (para formulário dinâmico e tela de configuração).
     */
    public function index()
    {
        $campos = RequisicaoVagaCustomCampo::porEmpresa($this->empresaId())->get();
        return response()->json($campos);
    }

    /**
     * Cria um novo campo custom.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(RequisicaoVagaCustomCampo::TIPOS)],
            'opcoes' => 'nullable|array',
            'opcoes.*' => 'string|max:255',
            'obrigatorio' => 'boolean',
            'ordem' => 'integer|min:0',
        ]);

        if (($validated['tipo'] ?? '') === RequisicaoVagaCustomCampo::TIPO_SELECT) {
            if (empty($validated['opcoes']) || !is_array($validated['opcoes'])) {
                return response()->json(['msg' => 'Campo do tipo "select" deve ter pelo menos uma opção.'], 400);
            }
            $validated['opcoes'] = array_values(array_filter($validated['opcoes']));
            if (empty($validated['opcoes'])) {
                return response()->json(['msg' => 'Campo do tipo "select" deve ter pelo menos uma opção.'], 400);
            }
        } else {
            $validated['opcoes'] = null;
        }

        $validated['empresa_id'] = $this->empresaId();
        $validated['obrigatorio'] = $request->boolean('obrigatorio');
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);

        $campo = RequisicaoVagaCustomCampo::create($validated);
        return response()->json($campo, 201);
    }

    /**
     * Atualiza um campo custom (deve pertencer à empresa).
     */
    public function update(Request $request, $id)
    {
        $campo = RequisicaoVagaCustomCampo::withoutGlobalScopes()
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId())
            ->firstOrFail();

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(RequisicaoVagaCustomCampo::TIPOS)],
            'opcoes' => 'nullable|array',
            'opcoes.*' => 'string|max:255',
            'obrigatorio' => 'boolean',
            'ordem' => 'integer|min:0',
        ]);

        if (($validated['tipo'] ?? '') === RequisicaoVagaCustomCampo::TIPO_SELECT) {
            if (empty($validated['opcoes']) || !is_array($validated['opcoes'])) {
                return response()->json(['msg' => 'Campo do tipo "select" deve ter pelo menos uma opção.'], 400);
            }
            $validated['opcoes'] = array_values(array_filter($validated['opcoes']));
            if (empty($validated['opcoes'])) {
                return response()->json(['msg' => 'Campo do tipo "select" deve ter pelo menos uma opção.'], 400);
            }
        } else {
            $validated['opcoes'] = null;
        }

        $validated['obrigatorio'] = $request->boolean('obrigatorio');
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);

        $campo->update($validated);
        return response()->json($campo);
    }

    /**
     * Remove um campo custom (deve pertencer à empresa).
     */
    public function destroy($id)
    {
        $campo = RequisicaoVagaCustomCampo::withoutGlobalScopes()
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId())
            ->firstOrFail();

        $campo->delete();
        return response()->json([], 204);
    }
}
