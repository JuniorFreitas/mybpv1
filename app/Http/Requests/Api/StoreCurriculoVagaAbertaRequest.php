<?php

namespace App\Http\Requests\Api;

use App\Models\Sistema;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCurriculoVagaAbertaRequest extends FormRequest
{
    private bool $editando = false;

    public function authorize(): bool
    {
        return true;
    }

    public function isEditando(): bool
    {
        return $this->editando;
    }

    protected function prepareForValidation(): void
    {
        $cpf = Sistema::transformCpfCnpj($this->input('cpf_padrao'));
        $empresaId = $this->input('empresa_id');

        $this->editando = User::whereHas('Curriculo', function ($q) use ($cpf) {
            $q->withoutGlobalScopes()->whereCpf($cpf);
        })->whereEmpresaId($empresaId)->exists();

        $merge = [
            'cpf' => $cpf,
            'lido' => false,
            'email' => mb_strtolower((string) $this->input('email')),
        ];

        $vagaAberta = VagasAbertas::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereId($this->input('vaga_aberta_id'))
            ->with('Municipio')
            ->first();

        if ($vagaAberta && $vagaAberta->Municipio) {
            $merge['uf_vaga'] = mb_strtoupper($vagaAberta->Municipio->uf);
            $merge['municipio_id'] = $vagaAberta->municipio_id;
            $merge['vaga_pretendida'] = $vagaAberta->id;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $empresaId = $this->input('empresa_id');
        $emailRule = app()->environment('local') ? 'required|email:rfc' : 'required|email:rfc,dns';

        $rules = [
            'nome' => 'required|min:3',
            'cpf' => [
                'required',
                'min:14',
                new CpfValidoEmpresaRules($empresaId),
                new VerificaCpfEmpresaRules($empresaId, $this->editando),
            ],
            'vaga_aberta_id' => ['required', new VagaAbertaEmpresaRules($empresaId)],
            'nascimento' => 'required|min:10',
            'email' => $emailRule,
            'cep' => 'required|min:9',
            'logradouro' => 'required',
            'bairro' => 'required',
            'municipio' => 'required',
            'uf' => 'required|min:2',
            'formacao' => 'required',
            'formacao_instituicao' => 'required',
            'formacao_status' => 'required',
            'vaga_pretendida' => 'required',
            'municipio_id' => 'required',
            'pcd' => 'required',
            'disponibilidade_sabado' => 'required',
            'disponibilidade_domingo' => 'required',
            'telefones' => ['required', 'array', 'min:1'],
            'telefones.*.numero' => 'required|min:14',
        ];

        if ($this->input('temqualificacao') && $this->has('qualificacoes')) {
            $rules['qualificacoes.*.nome'] = 'required';
            $rules['qualificacoes.*.instituicao'] = 'required';
            $rules['qualificacoes.*.mes_conclusao'] = 'required';
            $rules['qualificacoes.*.ano_conclusao'] = 'required';
        }

        if ($this->input('temexperiencia') && $this->has('experiencias')) {
            $rules['experiencias.*.empresa'] = 'required';
            $rules['experiencias.*.cargo'] = 'required';
            $rules['experiencias.*.principais_atv'] = 'required';
            $rules['experiencias.*.data_inicio'] = 'required';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar curriculo',
            'erros' => $validator->errors(),
        ], 400));
    }
}
