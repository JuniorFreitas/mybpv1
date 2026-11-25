<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Cloud;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreItensCloudRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Resolve e trava o cloud_id a partir do Cloud autorizado (evita IDOR), igual ao controller original.
        $cloud = Cloud::encontrarAutorizadoOuAbortar($this->input('cloud_id') ?? 0);
        $this->merge(['cloud_id' => $cloud->id]);
    }

    public function rules(): array
    {
        $dados = $this->all();

        return [
            'label' => [
                'required',
                Rule::unique('itens_cloud')->where(function ($query) use ($dados) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereCloudId($dados['cloud_id'] ?? null)
                            ->whereLabel($dados['label'] ?? null)
                            ->whereTipo($dados['tipo'] ?? null)
                            ->wherePertence($dados['pertence'] ?? null),
                        'itens_cloud'
                    );
                }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao criar nova pasta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
