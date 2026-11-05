<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Configurações → Médicos responsáveis. Regras no domínio.
 */
class MedicosResponsaveisController extends Controller
{
    public function __construct(private readonly MedicosResponsaveisService $medicos)
    {
    }

    public function index()
    {
        return view('g.saude-ocupacional.medicos.index');
    }

    public function dados(): JsonResponse
    {
        $empresaId = $this->empresaId();

        return response()->json([
            'medicos' => $this->medicos->listar($empresaId),
            'opcoes' => $this->medicos->opcoes($empresaId),
            'coordenador' => $this->medicos->coordenadorVigente($empresaId),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $medico = $this->medicos->salvar($this->empresaId(), null, $this->validar($request), $request->user());

        return response()->json(['id' => $medico->id, 'mensagem' => 'Médico cadastrado.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->medicos->salvar($this->empresaId(), $id, $this->validar($request), $request->user());

        return response()->json(['id' => $id, 'mensagem' => 'Médico atualizado.']);
    }

    public function ativo(Request $request, int $id): JsonResponse
    {
        $ativo = (bool) $request->validate(['ativo' => ['required', 'boolean']])['ativo'];
        $this->medicos->alterarAtivo($this->empresaId(), $id, $ativo, $request->user());

        return response()->json(['mensagem' => $ativo ? 'Médico ativado.' : 'Médico desativado.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->medicos->excluir($this->empresaId(), $id, $request->user());

        return response()->json(['mensagem' => 'Médico excluído.']);
    }

    /**
     * Formato básico; regras (CRM único, coordenador sem sobreposição, clínica da empresa) no domínio.
     *
     * @return array<string, mixed>
     */
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'crm' => ['required', 'string', 'max:20'],
            'crm_uf' => ['required', 'string', 'size:2'],
            'rqe' => ['nullable', 'string', 'max:20'],
            'especialidade' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'vinculos' => ['nullable', 'array', 'max:20'],
            'vinculos.*.id' => ['nullable', 'integer'],
            'vinculos.*.papel' => ['required', 'string', 'in:coordenador,examinador'],
            'vinculos.*.empresa_exame_id' => ['nullable', 'integer'],
            'vinculos.*.vigencia_inicio' => ['required', 'string', 'max:10'],
            'vinculos.*.vigencia_fim' => ['nullable', 'string', 'max:10'],
        ], [
            'vinculos.*.vigencia_inicio.required' => 'Informe o início da vigência de cada vínculo.',
        ]);
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
