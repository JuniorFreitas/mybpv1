<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Cloud;
use App\Models\ItensCloud;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateItensCloudRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        /** @var ItensCloud $itenscloud */
        $itenscloud = $this->route('itenscloud');
        Cloud::encontrarAutorizadoOuAbortar($itenscloud->cloud_id);

        // Impede troca de cloud_id via payload (IDOR)
        $this->merge(['cloud_id' => $itenscloud->cloud_id]);
    }

    public function rules(): array
    {
        /** @var ItensCloud $itenscloud */
        $itenscloud = $this->route('itenscloud');
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
                })->ignore($itenscloud->id),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar pasta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
