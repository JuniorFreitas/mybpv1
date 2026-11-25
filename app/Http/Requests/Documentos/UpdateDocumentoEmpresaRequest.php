<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\TipoDocumento;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use MasterTag\DataHora;

class UpdateDocumentoEmpresaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        $input['ativo'] = ($input['ativo'] ?? null) == 'true';

        $this->replace($input);
    }

    public function rules(): array
    {
        $dados = $this->all();

        return [
            'documentos_empresa.tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $tipoEmpresa = !empty($dados['tipo_empresa']) ? 'empresa' : 'contrato';
                $tipoDocumento = TipoDocumento::whereTipo($tipoEmpresa)->first();
                if (!$tipoDocumento) {
                    $fail('Verificar o tipo de documento');
                }
            }],
            'documentos_empresa.data_inicio' => [function ($attribute, $value, $fail) use ($dados) {
                $dataInicio = $dados['documentos_empresa']['data_inicio'] ?? null;
                $dataEncerramento = $dados['documentos_empresa']['data_encerramento'] ?? null;
                $diffDias = DataHora::diferencaDias($dataInicio, $dataEncerramento);

                if ($diffDias <= 0) {
                    $fail('Data Vencimento precisa ser maior que a Data início');
                }
            }],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Documento Empresa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
