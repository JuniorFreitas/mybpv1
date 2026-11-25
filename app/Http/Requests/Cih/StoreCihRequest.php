<?php

namespace App\Http\Requests\Cih;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\CihTag;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCihRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $tagId = (int) $this->input('tag_id', 0);
        $areaId = (int) $this->input('area_id', 0);

        $rules = [
            'tag_id' => ['required', 'integer'],
            'outra_tag' => [
                function ($attribute, $value, $fail) {
                    if ((int) $this->input('tag_id') === 0 && $value == '') {
                        $fail('O campo especifique deve ser preenchido.');
                    }
                },
            ],
            'colaboradores' => ['required', 'array', 'min:1'],
            'colaboradores.*.id' => $this->existsNaEmpresa('feedback_curriculos'),
            'acao' => ['required'],
            'area_id' => ['nullable', 'integer'],
            'centro_custo_id' => ['nullable', 'integer', $this->existsNaEmpresa('centro_custos')],
            'anexos' => [
                function ($attribute, $value, $fail) {
                    $cihTag = CihTag::where('id', $this->input('tag_id'))
                        ->where('empresa_id', $this->empresaIdAutenticada())
                        ->first();
                    if ($cihTag && $cihTag->anexos_obrigatorios && count((array) $value) === 0) {
                        $fail('É necessário anexar o(s) arquivo(s) obrigatório(s) para a tipo selecionado.');
                    }
                },
            ],
        ];

        if ($tagId > 0) {
            $rules['tag_id'][] = $this->existsNaEmpresa('cih_tags');
        }

        if ($areaId > 0) {
            $rules['area_id'][] = $this->existsNaEmpresa('area_etiquetas');
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Informações',
            'erros' => $validator->errors(),
        ], 400));
    }
}
