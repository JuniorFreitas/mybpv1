<?php

namespace App\Services\Treinamento;

use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

/**
 * Regras de montagem da carteira e da etiqueta de bloqueio.
 * Identificador (chapa = numero_cracha, matrícula = admissao.matricula) e blade opcional
 * ficam na configuração do modelo. Blade vazio usa o layout padrão.
 */
class CarteiraMontagem
{
    public const IDENTIFICADOR_CHAPA = 'chapa';

    public const IDENTIFICADOR_MATRICULA = 'matricula';

    public const VIEW_CARTEIRA = 'pdf.treinamento.carteira.cart_treinamento';

    public const VIEW_ETIQUETA = 'pdf.treinamento.carteira.cart_bloqueio';

    private const NAMESPACE_VIEW = 'pdf.treinamento.carteira.';

    /**
     * @return array<int, string>
     */
    public static function identificadores(): array
    {
        return [self::IDENTIFICADOR_CHAPA, self::IDENTIFICADOR_MATRICULA];
    }

    public static function normalizarIdentificador(?string $identificador): string
    {
        $identificador = strtolower(trim((string) $identificador));

        return in_array($identificador, self::identificadores(), true)
            ? $identificador
            : self::IDENTIFICADOR_CHAPA;
    }

    public static function label(string $identificador): string
    {
        return self::normalizarIdentificador($identificador) === self::IDENTIFICADOR_MATRICULA
            ? 'Matr'
            : 'Chapa';
    }

    /**
     * @param array<string, mixed>|null $admissao
     */
    public static function valor(string $identificador, ?array $admissao): ?string
    {
        if (!$admissao) {
            return null;
        }

        $campo = self::normalizarIdentificador($identificador) === self::IDENTIFICADOR_MATRICULA
            ? 'matricula'
            : 'numero_cracha';

        $valor = $admissao[$campo] ?? null;

        return $valor === null || $valor === '' ? null : (string) $valor;
    }

    /**
     * Nome do arquivo (sem .blade.php) dentro de resources/views/pdf/treinamento/carteira/.
     */
    public static function normalizarBlade(?string $blade): ?string
    {
        $blade = strtolower(trim((string) $blade));
        if ($blade === '') {
            return null;
        }

        $blade = preg_replace('/\.blade\.php$/', '', $blade) ?? $blade;

        if (!preg_match('/^[a-z0-9_]+$/', $blade)) {
            throw ValidationException::withMessages([
                'blade' => 'Informe só o nome do arquivo (letras, números e _). Ex.: cart_treinamento_coimbra',
            ]);
        }

        $view = self::NAMESPACE_VIEW . $blade;
        if (!View::exists($view)) {
            throw ValidationException::withMessages([
                'blade' => 'Arquivo não encontrado em resources/views/pdf/treinamento/carteira/' . $blade . '.blade.php',
            ]);
        }

        return $blade;
    }

    public static function viewCarteira(?string $blade): string
    {
        return self::viewExistente($blade, self::VIEW_CARTEIRA);
    }

    public static function viewEtiqueta(?string $blade): string
    {
        return self::viewExistente($blade, self::VIEW_ETIQUETA);
    }

    /**
     * Completa o config usado no PDF com rótulo, valor e view resolvida.
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed>|null $admissao
     * @return array<string, mixed>
     */
    public static function aplicarNoConfig(array $config, ?array $admissao): array
    {
        $identificadorCarteira = self::normalizarIdentificador($config['identificador'] ?? null);
        $identificadorEtiqueta = self::normalizarIdentificador($config['identificador_etiqueta'] ?? $identificadorCarteira);

        $config['identificador'] = $identificadorCarteira;
        $config['identificador_label'] = self::label($identificadorCarteira);
        $config['identificador_valor'] = self::valor($identificadorCarteira, $admissao);
        $config['blade_carteira'] = self::viewCarteira($config['blade_carteira'] ?? null);

        $config['identificador_etiqueta'] = $identificadorEtiqueta;
        $config['identificador_etiqueta_label'] = self::label($identificadorEtiqueta);
        $config['identificador_etiqueta_valor'] = self::valor($identificadorEtiqueta, $admissao);
        $config['blade_etiqueta'] = self::viewEtiqueta($config['blade_etiqueta'] ?? null);

        return $config;
    }

    /**
     * Agrupa itens consecutivos que usam a mesma view, preservando a ordem da seleção.
     *
     * @param array<int, array<string, mixed>> $treinamentos
     * @return array<int, array{view: string, itens: array<int, array<string, mixed>>}>
     */
    public static function agruparPorView(array $treinamentos, string $chaveView, string $viewPadrao): array
    {
        $grupos = [];
        foreach ($treinamentos as $item) {
            $view = (string) ($item['segmento_config'][$chaveView] ?? $viewPadrao);
            if ($view === '') {
                $view = $viewPadrao;
            }
            $ultimo = count($grupos) - 1;
            if ($ultimo >= 0 && $grupos[$ultimo]['view'] === $view) {
                $grupos[$ultimo]['itens'][] = $item;
                continue;
            }
            $grupos[] = ['view' => $view, 'itens' => [$item]];
        }

        return $grupos;
    }

    private static function viewExistente(?string $blade, string $viewPadrao): string
    {
        $blade = strtolower(trim((string) $blade));
        if ($blade === '') {
            return $viewPadrao;
        }

        if (str_contains($blade, '.')) {
            return View::exists($blade) ? $blade : $viewPadrao;
        }

        $view = self::NAMESPACE_VIEW . $blade;

        return View::exists($view) ? $view : $viewPadrao;
    }
}
