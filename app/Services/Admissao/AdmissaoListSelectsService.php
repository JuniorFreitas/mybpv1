<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\PadraoTreinamento;
use App\Models\UsuarioDependente;

/**
 * Monta os dados de apoio (selects) usados nas telas de Admissão.
 * Extraído de AdmissaoController::listSelects/getTiposDependentes (TD-002 fatia 2).
 */
class AdmissaoListSelectsService
{
    /**
     * @return array<string, mixed>
     */
    public function listSelects(): array
    {
        $padroes = PadraoTreinamento::query()
            ->ativos()
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug']);

        return [
            'tipos_admissao' => Admissao::TODOS_TIPOS_ADMISSAO,
            'status_admissao' => Admissao::TODOS_STATUS_ADMISSAO,
            'status_carteira_treinamento' => Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO,
            'padroes_treinamento' => $padroes,
            'todos_prazos' => Admissao::TODOS_PRAZOS,
            'todos_status_treinamentos' => Admissao::TODOS_STATUS_TREINAMENTOS,
            'todos_status_documentos' => Admissao::TODOS_STATUS_DOCUMENTOS,
            'todos_status_documentos_portaria' => Admissao::TODOS_STATUS_DOCUMENTOS_PORTARIA,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function tiposDependentes(): array
    {
        return UsuarioDependente::TIPOS_DEPENDENTES;
    }
}
