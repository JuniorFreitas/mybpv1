<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\TipoDocumento;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use MasterTag\DataHora;

class UpdateDocumentoSsmaRequest extends FormRequest
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
            'documentos_ssma.*.observacao' => [function ($attribute, $value, $fail) {
                if (strlen((string) $value) <= 3) {
                    $fail('Informe uma observação maior que 3 caracteres.');
                }
            }],
            'documentos_ssma.tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $tipoSsma = !empty($dados['tipo_ssma']) ? 'ssma' : 'contrato';
                $tipoDocumento = TipoDocumento::whereTipo($tipoSsma)->first();
                if (!$tipoDocumento) {
                    $fail('Verificar o tipo de documento');
                }
            }],
            'documentos_ssma.data_inicio' => [function ($attribute, $value, $fail) use ($dados) {
                $dataInicio = $dados['documentos_ssma']['data_inicio'] ?? null;
                $dataEncerramento = $dados['documentos_ssma']['data_encerramento'] ?? null;
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
            'msg' => 'Erro ao atualizar Documento SSMA',
            'erros' => $validator->errors(),
        ], 400));
    }
}
