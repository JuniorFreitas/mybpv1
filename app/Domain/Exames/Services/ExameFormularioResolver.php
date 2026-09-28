<?php

namespace App\Domain\Exames\Services;

use App\Models\ExameTipo;
use App\Models\Formulario;
use Illuminate\Support\Facades\Cache;

class ExameFormularioResolver
{
    public const TITULO_FALLBACK_ENCAMINHAMENTO = 'Exames';

    public const TITULO_FALLBACK_RESULTADO = 'Resultado SESMT';

    public const CACHE_TTL_SECONDS = 300;

    public function resolverEncaminhamento(?int $exameTipoId, ?int $empresaId = null): ?Formulario
    {
        return $this->resolver($exameTipoId, $empresaId, 'encaminhamento');
    }

    public function resolverResultado(?int $exameTipoId, ?int $empresaId = null): ?Formulario
    {
        return $this->resolver($exameTipoId, $empresaId, 'resultado');
    }

    public function invalidarCache(?int $exameTipoId, ?int $empresaId = null): void
    {
        $empresaId = $empresaId ?? (auth()->check() ? (int) auth()->user()->empresa_id : null);
        foreach (['encaminhamento', 'resultado'] as $contexto) {
            Cache::forget($this->cacheKey($exameTipoId, $empresaId, $contexto));
            if ($exameTipoId) {
                Cache::forget($this->cacheKey(null, $empresaId, $contexto));
            }
        }
    }

    private function resolver(?int $exameTipoId, ?int $empresaId, string $contexto): ?Formulario
    {
        $empresaId = $empresaId ?? (auth()->check() ? (int) auth()->user()->empresa_id : null);
        $cacheKey = $this->cacheKey($exameTipoId, $empresaId, $contexto);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($exameTipoId, $empresaId, $contexto) {
            $formularioId = null;

            if ($exameTipoId) {
                $tipo = ExameTipo::query()->find($exameTipoId);
                if ($tipo) {
                    $formularioId = $contexto === 'resultado'
                        ? $tipo->formulario_resultado_id
                        : $tipo->formulario_encaminhamento_id;
                }
            }

            if ($formularioId) {
                $form = $this->carregarFormulario($formularioId, $empresaId);
                if ($form) {
                    return $form;
                }
            }

            $titulo = $contexto === 'resultado'
                ? self::TITULO_FALLBACK_RESULTADO
                : self::TITULO_FALLBACK_ENCAMINHAMENTO;

            return $this->carregarPorTitulo($titulo, $empresaId);
        });
    }

    private function carregarFormulario(int $formularioId, ?int $empresaId): ?Formulario
    {
        $query = Formulario::withoutGlobalScopes()
            ->where('id', $formularioId);

        if ($empresaId) {
            $query->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
            });
        }

        $form = $query->first();

        return $form ? $this->carregarRelacoes($form) : null;
    }

    private function carregarPorTitulo(string $titulo, ?int $empresaId): ?Formulario
    {
        $query = Formulario::withoutGlobalScopes()->where('titulo', $titulo);

        if ($empresaId) {
            $form = (clone $query)->where('empresa_id', $empresaId)->first();
            if ($form) {
                return $this->carregarRelacoes($form);
            }
        }

        $form = $query->whereNull('empresa_id')->first()
            ?? Formulario::withoutGlobalScopes()->where('titulo', $titulo)->first();

        return $form ? $this->carregarRelacoes($form) : null;
    }

    private function carregarRelacoes(Formulario $formulario): Formulario
    {
        return $formulario->load([
            'Setores.Alternativas' => function ($q) {
                $q->where(function ($inner) {
                    $inner->where('alternativa_formularios.ativo', true)
                        ->orWhereNull('alternativa_formularios.ativo');
                });
            },
            'Setores.Alternativas.Opcoes',
        ]);
    }

    private function cacheKey(?int $exameTipoId, ?int $empresaId, string $contexto): string
    {
        return sprintf(
            'exame_formulario:%s:tipo:%s:empresa:%s',
            $contexto,
            $exameTipoId ?? 'null',
            $empresaId ?? 'null'
        );
    }
}
