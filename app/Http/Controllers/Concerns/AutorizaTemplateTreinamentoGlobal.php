<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait AutorizaTemplateTreinamentoGlobal
{
    public const EMPRESA_ID_TEMPLATE_GLOBAL = 100;

    protected function empresaIdUsuario(): int
    {
        return (int) auth()->user()->empresa_id;
    }

    protected function isAdminTemplatesGlobais(): bool
    {
        return $this->empresaIdUsuario() === self::EMPRESA_ID_TEMPLATE_GLOBAL;
    }

    protected function autorizarTemplateGlobal(): void
    {
        if (!$this->isAdminTemplatesGlobais()) {
            abort(403, 'O cadastro de Carteiras e Etiquetas é restrito à MyBP (empresa 100).');
        }
    }

    /**
     * Templates custom: só o tenant dono. Globais: só admin 100.
     */
    protected function autorizarEscritaTemplate(?int $empresaIdDoRegistro): void
    {
        if ($empresaIdDoRegistro === null) {
            $this->autorizarTemplateGlobal();

            return;
        }

        if ((int) $empresaIdDoRegistro !== $this->empresaIdUsuario() && !$this->isAdminTemplatesGlobais()) {
            abort(403, 'Sem permissão para alterar este template.');
        }
    }

    /**
     * Resolve empresa_id no store (somente MyBP / empresa 100):
     * - global=true → null (template de todas as empresas)
     * - empresa_id → custom da empresa alvo
     */
    protected function resolverEmpresaIdParaStore(Request $request): ?int
    {
        $this->autorizarTemplateGlobal();

        if ($request->boolean('global')) {
            return null;
        }

        if (!$request->filled('empresa_id')) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Informe a empresa do template customizado ou marque como global.',
            ]);
        }

        $empresaId = (int) $request->input('empresa_id');
        $existe = Cliente::query()
            ->where('id', $empresaId)
            ->where('ativo', true)
            ->exists();

        if (!$existe) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Empresa inválida ou inativa.',
            ]);
        }

        return $empresaId;
    }

    /**
     * Admin: campoEscopo = ''|global|empresa (+ campoEmpresaId opcional).
     * Tenant: sempre só os próprios.
     */
    protected function aplicarFiltroEscopoListagem(Builder $query, Request $request): void
    {
        if (!$this->isAdminTemplatesGlobais()) {
            $query->where('empresa_id', $this->empresaIdUsuario());

            return;
        }

        $escopo = (string) $request->input('campoEscopo', '');
        if ($escopo === '' && $request->boolean('globais')) {
            $escopo = 'global';
        }

        if ($escopo === 'global') {
            $query->whereNull('empresa_id');

            return;
        }

        if ($escopo === 'empresa') {
            if ($request->filled('campoEmpresaId')) {
                $query->where('empresa_id', (int) $request->input('campoEmpresaId'));
            } else {
                $query->whereNotNull('empresa_id');
            }
        }
    }

    /**
     * Slug interno (não exposto ao cliente). Único por escopo global ou por empresa.
     */
    protected function nomeClone(string $nome): string
    {
        $sufixo = '_clone';
        $base = rtrim($nome);
        $limite = 255 - strlen($sufixo);

        return Str::limit($base, $limite, '') . $sufixo;
    }

    protected function gerarSlugUnico(string $table, string $nome, ?int $empresaId, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($nome) ?: 'item', 50, '');
        $candidate = $base;
        $i = 2;

        while (true) {
            $query = DB::table($table)->where('slug', $candidate);
            if ($empresaId === null) {
                $query->whereNull('empresa_id');
            } else {
                $query->where('empresa_id', $empresaId);
            }
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            if (!$query->exists()) {
                return $candidate;
            }
            $suffix = '-' . $i;
            $candidate = Str::limit($base, 50 - strlen($suffix), '') . $suffix;
            $i++;
        }
    }
}
