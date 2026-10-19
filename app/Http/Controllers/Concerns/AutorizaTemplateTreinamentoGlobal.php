<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
            abort(403, 'O cadastro de templates globais de treinamento é restrito à empresa responsável pela configuração global.');
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
     * Resolve empresa_id no store: global (null), custom do tenant, ou custom de empresa alvo (admin 100).
     */
    protected function resolverEmpresaIdParaStore(Request $request): ?int
    {
        if ($request->boolean('global')) {
            $this->autorizarTemplateGlobal();

            return null;
        }

        if ($this->isAdminTemplatesGlobais() && $request->filled('empresa_id')) {
            $this->autorizarTemplateGlobal();
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

        return $this->empresaIdUsuario();
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
}
