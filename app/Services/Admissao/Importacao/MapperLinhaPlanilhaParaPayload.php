<?php

namespace App\Services\Admissao\Importacao;

use App\Models\Sistema;

/**
 * Converte uma linha da planilha (já validada) + IDs resolvidos no payload
 * curriculo + admissao no formato esperado pelo PersistidorAdmissaoImportada (e ImportJob).
 * Normaliza datas para Y-m-d e trata placeholder "aaaa-mm-dd" como vazio.
 */
class MapperLinhaPlanilhaParaPayload
{
    /** Placeholders de data que devem ser tratados como vazio (cnh_vencimento, etc). */
    private const PLACEHOLDERS_DATA = ['aaaa-mm-dd', 'dd/mm/aaaa', ''];

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

        $cnh = strtoupper(trim($get('cnh', 'NAO')));
        $cnhVencimento = $this->normalizarDataParaPayload($get('cnh_vencimento'), true);
        if ($cnh === 'SIM' && ($cnhVencimento === null || $cnhVencimento === '')) {
            $cnh = 'NAO';
            $cnhVencimento = null;
        } elseif ($cnh === 'NAO' && $cnhVencimento !== null && $cnhVencimento !== '') {
            $cnhVencimento = null;
        }

        $curriculo = [
            'cpf' => $cpfMascarado,
            'nome' => $get('nome'),
            'naturalidade' => $get('naturalidade'),
            'email' => $email,
            'cnh' => $cnh,
            'cnh_vencimento' => $cnhVencimento,
            'estado_civil' => $get('estado_civil'),
            'rg' => preg_replace('/[^0-9]/', '', $get('rg')),
            'rg_data_emissao' => $this->normalizarDataParaPayload($get('rg_emissao'), true),
            'nascimento' => $this->normalizarDataParaPayload($get('nascimento'), false),
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
            'data_entrega_area' => $this->normalizarDataParaPayload($get('data_entrega_area'), true),
            'salario' => $salarioFormatado,
            'pis' => $get('pis'),
            'ctps_numero' => $get('ctps_numero'),
            'ctps_serie' => $get('ctps_serie'),
            'ctps_data_emissao' => $this->normalizarDataParaPayload($get('ctps_data_emissao'), true),
            'titulo_eleitor_numero' => $get('titulo_eleitor_numero'),
            'titulo_eleitor_sessao' => $get('titulo_eleitor_sessao'),
            'titulo_eleitor_zona' => $get('titulo_eleitor_zona'),
            'tipo_admissao' => mb_strtoupper($get('tipo_admissao')),
            'data_admissao' => $this->normalizarDataParaPayload($get('data_admissao'), false),
            'data_aso' => $this->normalizarDataParaPayload($get('data_aso'), false),
            'admissao_encerramento' => $this->normalizarDataParaPayload($get('admissao_encerramento'), true),
            'prazo_experiencia' => $this->normalizarPrazoExperiencia($get('prazo_experiencia')),
            'encaminhado_documento' => strtolower($get('encaminhado_documento', 'NAO')) === 'sim',
            'encaminhado_documento_data' => $this->normalizarDataParaPayload($get('encaminhado_documento_data'), true),
            'encaminhado_exame' => strtolower($get('encaminhado_exame', 'NAO')) === 'sim',
            'encaminhado_exame_data' => $this->normalizarDataParaPayload($get('encaminhado_exame_data'), true),
            'encaminhado_treinamento' => strtolower($get('encaminhado_treinamento', 'NAO')) === 'sim',
            'encaminhado_treinamento_data' => $this->normalizarDataParaPayload($get('encaminhado_treinamento_data'), true),
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

    /**
     * Normaliza valor de data da planilha para formato aceito pelo banco (Y-m-d) e pelo DataHora.
     * Trata placeholder "aaaa-mm-dd" e vazio como null quando opcional.
     * Corrige formato trocado da planilha (ex.: 1994-29-7 = dia 29, mês 7 -> 1994-07-29).
     *
     * @param string|null $valor Valor bruto (dd/mm/yyyy, yyyy-mm-dd, ou yyyy-d-m incorreto)
     * @param bool $opcional Se true, retorna null para vazio/placeholder/inválido
     * @return string|null Data em Y-m-d ou null
     */
    private function normalizarDataParaPayload(?string $valor, bool $opcional): ?string
    {
        $v = $valor === null ? '' : trim((string) $valor);
        if ($v === '') {
            return $opcional ? null : '';
        }
        $vLower = mb_strtolower($v);
        foreach (self::PLACEHOLDERS_DATA as $placeholder) {
            if ($placeholder !== '' && $vLower === $placeholder) {
                return $opcional ? null : '';
            }
        }
        if (preg_match('/^aaaa[-\\/]?mm[-\\/]?dd$/i', preg_replace('/\s+/', '', $vLower))) {
            return $opcional ? null : '';
        }

        // dd/mm/yyyy ou d/m/yyyy (permite espaços opcionais)
        if (preg_match('/^(\d{1,2})\s*[\/\-]\s*(\d{1,2})\s*[\/\-]\s*(\d{4})$/', $v, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $ano = (int) $m[3];
            if ($mes >= 1 && $mes <= 12 && $dia >= 1 && $dia <= 31 && checkdate($mes, $dia, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            }
        }

        // yyyy-mm-dd ou yyyy-d-m (permite espaços; segundo segmento pode ser dia ou mês)
        if (preg_match('/^(\d{4})\s*[\/\-]\s*(\d{1,2})\s*[\/\-]\s*(\d{1,2})$/', $v, $m)) {
            $ano = (int) $m[1];
            $a = (int) $m[2];
            $b = (int) $m[3];
            if ($a >= 1 && $a <= 12 && $b >= 1 && $b <= 31 && checkdate($a, $b, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $a, $b);
            }
            // Planilha com ordem trocada: yyyy-d-m (dia, mês) ex.: 1994-29-7 -> 1994-07-29
            if ($b >= 1 && $b <= 12 && $a >= 1 && $a <= 31 && checkdate($b, $a, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $b, $a);
            }
        }

        // Fallback: três números separados por qualquer coisa (ex.: 1994-29-7 vindo como string do Excel)
        if (preg_match('/^(\d{4})\D+(\d{1,2})\D+(\d{1,2})$/', $v, $m)) {
            $ano = (int) $m[1];
            $a = (int) $m[2];
            $b = (int) $m[3];
            if ($a >= 1 && $a <= 12 && $b >= 1 && $b <= 31 && checkdate($a, $b, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $a, $b);
            }
            if ($b >= 1 && $b <= 12 && $a >= 1 && $a <= 31 && checkdate($b, $a, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $b, $a);
            }
        }

        return $opcional ? null : $v;
    }
}
