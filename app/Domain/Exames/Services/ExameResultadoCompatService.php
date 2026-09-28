<?php

namespace App\Domain\Exames\Services;

use App\Models\AlternativaFormulario;
use App\Models\Formulario;

class ExameResultadoCompatService
{
    /** Chaves canônicas usadas por relatórios, PDF e WhatsApp. */
    public const CHAVES_CANONICAS = [
        'result',
        'pendencias',
        'aprovado',
        'trabalho_altura',
        'espacao_confinado',
        'observacoes',
    ];

    public function isFormatoLegado(?array $resultado): bool
    {
        if (empty($resultado) || !is_array($resultado)) {
            return true;
        }

        foreach (array_keys($resultado) as $chave) {
            if (is_string($chave) && str_starts_with($chave, 'alternativa_id_')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normaliza o payload salvo em examesesmts.resultado:
     * - mantém respostas dinâmicas alternativa_id_*
     * - espelha chaves canônicas a partir de chave_canonica das alternativas
     * - se já for legado, devolve como está
     */
    public function normalizarParaGravacao(array $resultado, ?Formulario $formulario = null): array
    {
        if ($this->isFormatoLegado($resultado) && !$this->temChavesDinamicas($resultado)) {
            return $this->garantirChavesCanonicas($resultado);
        }

        $canonico = $this->extrairCanonicoDeDinamico($resultado, $formulario);

        return array_merge($resultado, $canonico);
    }

    /**
     * Para a UI: se legado, devolve estrutura legado; se dinâmico, devolve respostas no formato FormularioDefault.
     */
    public function prepararParaLeitura(?array $resultado): array
    {
        if ($resultado === null) {
            return [
                'formato' => 'legado',
                'resultado' => $this->garantirChavesCanonicas([]),
                'respostas' => [],
            ];
        }

        if ($this->isFormatoLegado($resultado)) {
            return [
                'formato' => 'legado',
                'resultado' => $this->garantirChavesCanonicas($resultado),
                'respostas' => [],
            ];
        }

        $respostas = [];
        foreach ($resultado as $chave => $valor) {
            if (is_string($chave) && str_starts_with($chave, 'alternativa_id_')) {
                $respostas[$chave] = $valor;
            }
        }

        return [
            'formato' => 'dinamico',
            'resultado' => $this->garantirChavesCanonicas(
                $this->extrairCanonicoDeDinamico($resultado)
            ),
            'respostas' => $respostas,
        ];
    }

    public function valorCanonico(?array $resultado, string $chave, $default = null)
    {
        if (empty($resultado) || !isset(self::CHAVES_CANONICAS[$chave]) && !in_array($chave, self::CHAVES_CANONICAS, true)) {
            // still allow lookup
        }

        if (isset($resultado[$chave])) {
            return $resultado[$chave];
        }

        $canonico = $this->extrairCanonicoDeDinamico($resultado ?? []);

        return $canonico[$chave] ?? $default;
    }

    private function temChavesDinamicas(array $resultado): bool
    {
        foreach (array_keys($resultado) as $chave) {
            if (is_string($chave) && str_starts_with($chave, 'alternativa_id_')) {
                return true;
            }
        }

        return false;
    }

    private function extrairCanonicoDeDinamico(array $resultado, ?Formulario $formulario = null): array
    {
        $mapa = [];

        if ($formulario) {
            foreach ($formulario->Setores ?? [] as $setor) {
                foreach ($setor->Alternativas ?? [] as $alt) {
                    if (!empty($alt->chave_canonica)) {
                        $mapa[(int) $alt->id] = $alt->chave_canonica;
                    }
                }
            }
        } else {
            $ids = [];
            foreach ($resultado as $chave => $valor) {
                if (is_string($chave) && preg_match('/^alternativa_id_(\d+)$/', $chave, $m)) {
                    $ids[] = (int) $m[1];
                }
            }
            if ($ids) {
                AlternativaFormulario::query()
                    ->whereIn('id', $ids)
                    ->whereNotNull('chave_canonica')
                    ->get(['id', 'chave_canonica'])
                    ->each(function ($alt) use (&$mapa) {
                        $mapa[(int) $alt->id] = $alt->chave_canonica;
                    });
            }
        }

        $canonico = [];
        foreach ($resultado as $chave => $item) {
            if (!is_string($chave) || !preg_match('/^alternativa_id_(\d+)$/', $chave, $m)) {
                continue;
            }
            $altId = (int) $m[1];
            $chaveCanonica = $mapa[$altId] ?? null;
            if (!$chaveCanonica || !in_array($chaveCanonica, self::CHAVES_CANONICAS, true)) {
                continue;
            }
            $valor = is_array($item) ? ($item['valor'] ?? null) : $item;
            $canonico[$chaveCanonica] = $valor;
        }

        return $this->garantirChavesCanonicas($canonico);
    }

    private function garantirChavesCanonicas(array $resultado): array
    {
        $defaults = [
            'result' => null,
            'pendencias' => null,
            'aprovado' => false,
            'trabalho_altura' => false,
            'espacao_confinado' => false,
            'observacoes' => null,
        ];

        foreach ($defaults as $chave => $default) {
            if (!array_key_exists($chave, $resultado)) {
                $resultado[$chave] = $default;
            }
        }

        return $resultado;
    }
}
