<?php

namespace App\Services\Cbo;

class CboColumnMapper
{
    /** @var array<int, string> */
    private const OCUPACAO_CODIGO = ['codigo_ocupacao', 'cod_ocupacao', 'cod_ocup', 'codigo', 'cod'];

    /** @var array<int, string> */
    private const OCUPACAO_TITULO = [
        'titulo',
        'titulo_ocupacao',
        'nome_ocupacao',
        'ocupacao',
        'descricao',
        'descricao_ocupacao',
        // cbo2002-perfilocupacional.csv (matriz ocupação × atividades)
        'nome_grande_area',
        'nome',
        'nome_atividade',
    ];

    /** @var array<int, string> */
    private const OCUPACAO_FAMILIA = ['codigo_familia', 'cod_familia', 'familia', 'cod_fam', 'codfamilia'];

    /** @var array<int, string> */
    private const FAMILIA_CODIGO = ['codigo_familia', 'cod_familia', 'cod_fam', 'codigo', 'cod'];

    /** @var array<int, string> */
    private const FAMILIA_TITULO = ['titulo_familia', 'titulo', 'familia', 'descricao_familia', 'descricao', 'nome'];

    /** @var array<int, string> */
    private const PERFIL_CODIGO_FAMILIA = [
        'cod_familia',
        'codigo_familia',
        'codigo',
        'cod',
        'familia',
        'cod_fam',
    ];

    /** @var array<int, string> */
    private const PERFIL_SUMARIO = [
        'descricao_sumaria',
        'descricao',
        'sumaria',
        'descricao_da_familia',
        'perfil_ocupacional',
        'perfil',
        'texto',
        'nome_atividade',
        'nome_ativ',
        'nome_da_atividade',
        'descricao_atividade',
    ];

    /**
     * @param array<int, string> $headers índice => chave normalizada
     * @return array{codigo: int|null, titulo: int|null, familia: int|null}
     */
    public function mapOcupacaoColumns(array $headers): array
    {
        return [
            'codigo' => $this->firstMatchingIndex($headers, self::OCUPACAO_CODIGO),
            'titulo' => $this->firstMatchingIndex($headers, self::OCUPACAO_TITULO),
            'familia' => $this->firstMatchingIndex($headers, self::OCUPACAO_FAMILIA),
        ];
    }

    /**
     * @param array<int, string> $headers
     * @return array{codigo: int|null, titulo: int|null}
     */
    public function mapFamiliaColumns(array $headers): array
    {
        return [
            'codigo' => $this->firstMatchingIndex($headers, self::FAMILIA_CODIGO),
            'titulo' => $this->firstMatchingIndex($headers, self::FAMILIA_TITULO),
        ];
    }

    /**
     * @param array<int, string> $headers
     * @return array{codigo_familia: int|null, descricao_sumaria: int|null}
     */
    public function mapPerfilColumns(array $headers): array
    {
        return [
            'codigo_familia' => $this->firstMatchingIndex($headers, self::PERFIL_CODIGO_FAMILIA),
            'descricao_sumaria' => $this->firstMatchingIndex($headers, self::PERFIL_SUMARIO),
        ];
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string> $preferredOrder primeiro match por ordem de preferência
     */
    private function firstMatchingIndex(array $headers, array $preferredOrder): ?int
    {
        $headerToIndex = [];
        foreach ($headers as $i => $key) {
            if ($key !== '') {
                $headerToIndex[$key] = $i;
            }
        }

        foreach ($preferredOrder as $name) {
            if (isset($headerToIndex[$name])) {
                return $headerToIndex[$name];
            }
        }

        return null;
    }

    public function deriveCodigoFamiliaFromOcupacao(string $codigoOcupacao): ?string
    {
        $digits = preg_replace('/\D/', '', $codigoOcupacao) ?? '';
        if (strlen($digits) >= 4) {
            return substr($digits, 0, 4);
        }

        return null;
    }
}
