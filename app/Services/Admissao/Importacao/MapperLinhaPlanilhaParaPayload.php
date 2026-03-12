<?php

namespace App\Services\Admissao\Importacao;

use App\Models\Sistema;

/**
 * Converte uma linha da planilha (já validada) + IDs resolvidos no payload
 * curriculo + admissao no formato esperado pelo PersistidorAdmissaoImportada (e ImportJob).
 */
class MapperLinhaPlanilhaParaPayload
{
    /**
     * @param array<string, mixed> $linha Linha da planilha (chaves = nomes das colunas)
     * @param int $vagasAbertasId ID de VagasAbertas (vaga_pretendida)
     * @param int|null $areaEtiquetaId ID de AreaEtiqueta (pode ser null se não informado)
     * @param int $centroCustoId ID de CentroCusto
     * @return array{curriculo: array, admissao: array}
     */
    public function map(array $linha, int $vagasAbertasId, ?int $areaEtiquetaId, int $centroCustoId): array
    {
        $get = function (string $key, $default = '') use ($linha) {
            $v = $linha[$key] ?? $default;
            return $v === null || $v === '' ? $default : trim((string) $v);
        };

        $cpf = $get('cpf');
        $cpfMascarado = $cpf ? Sistema::mascaraCpf($cpf) : '';

        $email = $get('email');
        if ($email === '') {
            $email = Sistema::EMAILPADRAO;
        } else {
            $email = mb_strtolower($email);
        }

        $whatsapp = strtolower($get('whatsapp', 'NAO')) === 'sim' ? 'whatsapp' : 'celular';
        $telefoneNumero = $get('telefone_numero');
        $telefoneMascarado = $telefoneNumero ? Sistema::mascaraTelefone($telefoneNumero) : '';

        $curriculo = [
            'cpf' => $cpfMascarado,
            'nome' => $get('nome'),
            'naturalidade' => $get('naturalidade'),
            'email' => $email,
            'cnh' => $get('cnh', 'NAO'),
            'cnh_vencimento' => $get('cnh_vencimento'),
            'estado_civil' => $get('estado_civil'),
            'rg' => preg_replace('/[^0-9]/', '', $get('rg')),
            'rg_data_emissao' => $get('rg_emissao'),
            'nascimento' => $get('nascimento'),
            'sexo' => $this->normalizarSexo($get('sexo')),
            'filiacao_pai' => $get('pai'),
            'filiacao_mae' => $get('mae'),
            'pcd' => strtolower($get('pcd', 'NAO')) === 'sim',
            'cid' => $get('cid'),
            'vaga_pretendida' => $vagasAbertasId,
            'telefone' => [
                'whatsapp' => $whatsapp,
                'numero' => $telefoneMascarado,
            ],
            'endereco' => [
                'cep' => $get('cep') ? Sistema::mascaraCep($get('cep')) : '',
                'logradouro' => $get('endereco'),
                'numero' => $get('numero'),
                'complemento' => $get('complemento'),
                'bairro' => $get('bairro'),
                'municipio' => $get('municipio'),
                'uf' => $get('uf'),
            ],
        ];

        $salario = $get('salario');
        $salarioFormatado = $salario !== '' ? number_format((float) str_replace([',', '.'], ['', '.'], $salario), 2, ',', '.') : '';

        $admissao = [
            'area_etiqueta_id' => $areaEtiquetaId,
            'centro_custo_id' => $centroCustoId,
            'data_entrega_area' => $get('data_entrega_area'),
            'salario' => $salarioFormatado,
            'pis' => $get('pis'),
            'ctps_numero' => $get('ctps_numero'),
            'ctps_serie' => $get('ctps_serie'),
            'ctps_data_emissao' => $get('ctps_data_emissao'),
            'titulo_eleitor_numero' => $get('titulo_eleitor_numero'),
            'titulo_eleitor_sessao' => $get('titulo_eleitor_sessao'),
            'titulo_eleitor_zona' => $get('titulo_eleitor_zona'),
            'tipo_admissao' => mb_strtoupper($get('tipo_admissao')),
            'data_admissao' => $get('data_admissao'),
            'data_aso' => $get('data_aso'),
            'admissao_encerramento' => $get('admissao_encerramento'),
            'prazo_experiencia' => $this->normalizarPrazoExperiencia($get('prazo_experiencia')),
            'encaminhado_documento' => strtolower($get('encaminhado_documento', 'NAO')) === 'sim',
            'encaminhado_documento_data' => $get('encaminhado_documento_data'),
            'encaminhado_exame' => strtolower($get('encaminhado_exame', 'NAO')) === 'sim',
            'encaminhado_exame_data' => $get('encaminhado_exame_data'),
            'encaminhado_treinamento' => strtolower($get('encaminhado_treinamento', 'NAO')) === 'sim',
            'encaminhado_treinamento_data' => $get('encaminhado_treinamento_data'),
            'numero_cracha' => $get('numero_cracha'),
            'matricula' => $get('matricula'),
            'banco' => [
                'nome' => $get('banco'),
                'agencia' => $get('agencia'),
                'conta' => $get('conta'),
                'pix' => strtolower($get('pix', 'NAO')) === 'sim',
                'pix_tipo_chave' => $get('pix_tipo_chave'),
                'pix_chave' => $get('pix_chave'),
            ],
        ];

        return ['curriculo' => $curriculo, 'admissao' => $admissao];
    }

    private function normalizarSexo(string $valor): string
    {
        $v = strtoupper(trim($valor));
        if ($v === 'M' || $v === 'MASCULINO') {
            return 'Masculino';
        }
        if ($v === 'F' || $v === 'FEMININO') {
            return 'Feminino';
        }
        return $valor ? ucwords(strtolower($valor)) : '';
    }

    private function normalizarPrazoExperiencia(string $valor): string
    {
        $v = trim($valor);
        return $v ? ucfirst(strtolower($v)) : '';
    }
}
