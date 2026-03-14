<?php

namespace App\Services\Admissao\Importacao;

use App\Models\Admissao;
use App\Models\Sistema;
use Illuminate\Support\Facades\Lang;

/**
 * Valida uma linha da planilha de importação de admissões.
 * Retorna erros por campo: [ 'campo' => [ 'mensagem', 'como_corrigir'? ], ... ]
 */
class ValidadorLinhaPlanilhaAdmissao
{
    private const CAMPOS_OBRIGATORIOS = [
        'cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'municipio', 'uf',
        'telefone_numero', 'cod_vaga', 'centro_custo', 'tipo_admissao', 'data_admissao', 'data_aso',
    ];

    private const LISTA_CNH = ['SIM', 'NAO'];
    private const LISTA_SEXO = ['MASCULINO', 'FEMININO', 'M', 'F'];
    private const LISTA_WHATSAPP = ['SIM', 'NAO'];
    private const LISTA_PIX = ['SIM', 'NAO'];
    private const LISTA_PCD = ['SIM', 'NAO'];
    private const LISTA_PIX_TIPO_CHAVE = ['CPF', 'CNPJ', 'EMAIL', 'ALEATORIA'];

    /**
     * Valida uma linha e retorna erros por campo.
     * Chave = nome do campo; valor = array com 'mensagem' e opcionalmente 'como_corrigir'.
     *
     * @param array<string, mixed> $linha
     * @return array<string, array{mensagem: string, como_corrigir?: string}>
     */
    public function validar(array $linha, int $numeroLinha, int $empresaId): array
    {
        $erros = [];

        foreach (self::CAMPOS_OBRIGATORIOS as $campo) {
            $valor = $this->valorLinha($linha, $campo);
            if ($valor === null || trim((string) $valor) === '') {
                $key = 'importacao_admissao.' . $campo . '_obrigatorio';
                $msg = Lang::get($key, [], 'pt_BR');
                $erros[$campo] = [
                    'mensagem' => ($msg && $msg !== $key) ? (string) $msg : ($campo . ' é obrigatório.'),
                    'como_corrigir' => ($msg && $msg !== $key) ? (string) $msg : 'Preencha o campo conforme o guia de importação.',
                ];
            }
        }

        $this->validarCpf($linha, $erros);
        $this->validarDatas($linha, $erros);
        $this->validarListas($linha, $erros);
        $this->validarCondicionais($linha, $erros);
        $this->validarCentroCustoExistente($linha, $empresaId, $erros);

        return $erros;
    }

    private function valorLinha(array $linha, string $campo)
    {
        if (array_key_exists($campo, $linha)) {
            return $linha[$campo];
        }
        return null;
    }

    private function valorString(array $linha, string $campo): string
    {
        $v = $this->valorLinha($linha, $campo);
        return $v !== null ? trim((string) $v) : '';
    }

    private function validarCpf(array $linha, array &$erros): void
    {
        if (isset($erros['cpf'])) {
            return;
        }
        $cpf = $this->valorString($linha, 'cpf');
        if ($cpf === '') {
            return;
        }
        $cpfNumeros = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpfNumeros) !== 11) {
            $erros['cpf'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.cpf_invalido', [], 'pt_BR'),
                'como_corrigir' => 'Use 11 dígitos ou formato 000.000.000-00.',
            ];
            return;
        }
        if (!Sistema::validaRuleCPF($cpfNumeros)) {
            $erros['cpf'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.cpf_invalido', [], 'pt_BR'),
                'como_corrigir' => 'Use 11 dígitos ou formato 000.000.000-00.',
            ];
        }
    }

    private function validarDatas(array $linha, array &$erros): void
    {
        $colunasData = ['data_admissao', 'data_aso', 'admissao_encerramento', 'cnh_vencimento', 'rg_emissao', 'nascimento', 'data_entrega_area', 'ctps_data_emissao', 'encaminhado_documento_data', 'encaminhado_exame_data', 'encaminhado_treinamento_data'];
        $padraoData = '/^\d{1,2}\/\d{1,2}\/\d{4}$/';
        foreach ($colunasData as $campo) {
            $v = $this->valorString($linha, $campo);
            if ($v === '') {
                continue;
            }
            if (!preg_match($padraoData, $v)) {
                $erros[$campo] = [
                    'mensagem' => (string) Lang::get('importacao_admissao.data_formato', [], 'pt_BR'),
                    'como_corrigir' => 'Use dia/mês/ano com barras (dd/mm/aaaa).',
                ];
            }
        }
    }

    private function validarListas(array $linha, array &$erros): void
    {
        $listas = [
            'cnh' => self::LISTA_CNH,
            'sexo' => self::LISTA_SEXO,
            'whatsapp' => self::LISTA_WHATSAPP,
            'pix' => self::LISTA_PIX,
            'pcd' => self::LISTA_PCD,
            'pix_tipo_chave' => self::LISTA_PIX_TIPO_CHAVE,
        ];
        foreach ($listas as $campo => $valoresPermitidos) {
            $v = $this->valorString($linha, $campo);
            if ($v === '') {
                continue;
            }
            $vUpper = mb_strtoupper($v);
            $ok = false;
            foreach ($valoresPermitidos as $permitido) {
                if ($vUpper === mb_strtoupper($permitido)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                $key = 'importacao_admissao.' . $campo . '_valores';
                $msg = (string) Lang::get($key, [], 'pt_BR');
                $erros[$campo] = [
                    'mensagem' => 'Valor não permitido.',
                    'como_corrigir' => $msg ?: 'Use um dos valores: ' . implode(', ', $valoresPermitidos),
                ];
            }
        }

        $tipoAdmissao = $this->valorString($linha, 'tipo_admissao');
        if ($tipoAdmissao !== '' && !in_array(mb_strtoupper($tipoAdmissao), Admissao::TODOS_TIPOS_ADMISSAO, true)) {
            $erros['tipo_admissao'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.tipo_admissao_valores', [], 'pt_BR'),
                'como_corrigir' => 'Use: FIXO, TEMPORARIO, INTERMITENTE, DETERMINADO, PJ, ESTÁGIO ou APRENDIZ.',
            ];
        }

        $prazo = $this->valorString($linha, 'prazo_experiencia');
        if ($prazo !== '' && !in_array($prazo, Admissao::TODOS_PRAZOS, true)) {
            $erros['prazo_experiencia'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.prazo_experiencia_valores', [], 'pt_BR'),
                'como_corrigir' => 'Use: Nenhum, 30+15, 30+30, 45+45, 30+60 ou 60+30.',
            ];
        }
    }

    private function validarCondicionais(array $linha, array &$erros): void
    {
        $tipoAdmissao = mb_strtoupper($this->valorString($linha, 'tipo_admissao'));

        if ($tipoAdmissao === Admissao::TIPO_ADMISSAO_FIXO) {
            $prazo = $this->valorString($linha, 'prazo_experiencia');
            if ($prazo === '' || !in_array($prazo, Admissao::TODOS_PRAZOS, true)) {
                $erros['prazo_experiencia'] = [
                    'mensagem' => (string) Lang::get('importacao_admissao.prazo_experiencia_obrigatorio', [], 'pt_BR'),
                    'como_corrigir' => 'Para FIXO, preencha: Nenhum, 30+15, 30+30, 45+45, 30+60 ou 60+30.',
                ];
            }
        }

        if (in_array($tipoAdmissao, [Admissao::TIPO_ADMISSAO_TEMPORARIO, Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO], true)) {
            $encerramento = $this->valorString($linha, 'admissao_encerramento');
            if ($encerramento === '') {
                $erros['admissao_encerramento'] = [
                    'mensagem' => (string) Lang::get('importacao_admissao.admissao_encerramento_obrigatorio', [], 'pt_BR'),
                    'como_corrigir' => 'Informe a data de fim do contrato (dd/mm/aaaa).',
                ];
            }
        }

        $pcd = mb_strtoupper($this->valorString($linha, 'pcd'));
        if ($pcd === 'SIM') {
            $cid = $this->valorString($linha, 'cid');
            if ($cid === '') {
                $erros['cid'] = [
                    'mensagem' => (string) Lang::get('importacao_admissao.cid_obrigatorio_pcd', [], 'pt_BR'),
                    'como_corrigir' => 'Quando PCD for SIM, preencha o código CID.',
                ];
            }
        }

        $pix = mb_strtoupper($this->valorString($linha, 'pix'));
        if ($pix === 'SIM') {
            $pixTipo = $this->valorString($linha, 'pix_tipo_chave');
            $pixChave = $this->valorString($linha, 'pix_chave');
            if ($pixTipo === '' || $pixChave === '') {
                $erros['pix_chave'] = [
                    'mensagem' => (string) Lang::get('importacao_admissao.pix_chave_obrigatorio', [], 'pt_BR'),
                    'como_corrigir' => 'Preencha pix_tipo_chave (CPF, CNPJ, EMAIL ou ALEATORIA) e pix_chave.',
                ];
            }
        }

        $encDoc = mb_strtoupper($this->valorString($linha, 'encaminhado_documento'));
        if ($encDoc === 'SIM' && $this->valorString($linha, 'encaminhado_documento_data') === '') {
            $erros['encaminhado_documento_data'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.encaminhado_documento_data', [], 'pt_BR'),
                'como_corrigir' => 'Informe a data (dd/mm/aaaa).',
            ];
        }
        $encExame = mb_strtoupper($this->valorString($linha, 'encaminhado_exame'));
        if ($encExame === 'SIM' && $this->valorString($linha, 'encaminhado_exame_data') === '') {
            $erros['encaminhado_exame_data'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.encaminhado_exame_data', [], 'pt_BR'),
                'como_corrigir' => 'Informe a data (dd/mm/aaaa).',
            ];
        }
        $encTreino = mb_strtoupper($this->valorString($linha, 'encaminhado_treinamento'));
        if ($encTreino === 'SIM' && $this->valorString($linha, 'encaminhado_treinamento_data') === '') {
            $erros['encaminhado_treinamento_data'] = [
                'mensagem' => (string) Lang::get('importacao_admissao.encaminhado_treinamento_data', [], 'pt_BR'),
                'como_corrigir' => 'Informe a data (dd/mm/aaaa).',
            ];
        }
    }

    /**
     * Valida se o centro de custo informado existe para a empresa (código ou nome).
     */
    private function validarCentroCustoExistente(array $linha, int $empresaId, array &$erros): void
    {
        if (isset($erros['centro_custo'])) {
            return;
        }
        $valor = $this->valorString($linha, 'centro_custo');
        if ($valor === '') {
            return;
        }

        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverCentroCusto($empresaId, $valor);

        if ($resultado['erro'] !== null) {
            $erros['centro_custo'] = [
                'mensagem' => $resultado['erro'],
                'como_corrigir' => 'Use o código ou nome do centro de custo cadastrado na empresa.',
            ];
        }
    }
}
