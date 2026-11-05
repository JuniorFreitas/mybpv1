<?php

namespace App\Http\Requests\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Documentos\ArmazenamentoDocumentoSst;
use App\Models\SaudeDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Envio de documento SST (novo ou nova versão). O tipo real do arquivo é conferido de novo no serviço.
 */
class EnviarDocumentoSstRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $novo = $this->route('id') === null;

        return [
            'arquivo' => ['required', 'file', 'max:' . ArmazenamentoDocumentoSst::TAMANHO_MAXIMO_KB, 'mimes:' . implode(',', array_keys(ArmazenamentoDocumentoSst::TIPOS_ACEITOS))],
            'tipo' => [$novo ? 'required' : 'prohibited', 'string', Rule::in(array_keys(SaudeDocumento::TIPOS))],
            'titulo' => [$novo ? 'required' : 'prohibited', 'string', 'max:200'],
            'sigilo' => ['nullable', Rule::in([SaudeDocumento::SIGILO_COMUM, SaudeDocumento::SIGILO_SENSIVEL])],
            'feedback_id' => ['nullable', 'integer', 'min:1'],
            'responsavel' => ['nullable', 'string', 'max:150'],
            'observacao' => ['nullable', 'string', 'max:1000'],
            'emissao' => ['nullable', 'string', 'max:10'],
            'vigencia_inicio' => ['nullable', 'string', 'max:10'],
            'vigencia_fim' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'arquivo.required' => 'Escolha o arquivo.',
            'arquivo.max' => 'O arquivo pode ter no máximo ' . (int) (ArmazenamentoDocumentoSst::TAMANHO_MAXIMO_KB / 1024) . ' MB.',
            'arquivo.mimes' => 'Envie PDF, imagem (JPG/PNG), Word (DOCX) ou Excel (XLSX).',
            'tipo.required' => 'Escolha o tipo do documento.',
            'titulo.required' => 'Informe o título.',
        ];
    }
}
