<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ChecklistsTarefa;
use App\Models\ChecklistsTarefaItem;
use App\Models\ListaTarefa;
use App\Models\Quadro;
use App\Models\Tarefa;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait GuardsWeeklyReportTenant
{
    protected function assertWeeklyEmpresaId(int $empresaId): void
    {
        $authEmpresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!$authEmpresaId || $authEmpresaId !== $empresaId) {
            throw new HttpException(403, 'Acesso negado a este tenant.');
        }
    }

    protected function assertWeeklyHierarchy(
        int $empresaId,
        Quadro $quadro,
        ?ListaTarefa $lista = null,
        ?Tarefa $tarefa = null,
        ?ChecklistsTarefa $checklist = null,
        ?ChecklistsTarefaItem $item = null
    ): void {
        $this->assertWeeklyEmpresaId($empresaId);

        if ((int) $quadro->empresa_id !== $empresaId) {
            throw new HttpException(404, 'Quadro não encontrado.');
        }

        if ($lista && (int) $lista->quadro_id !== (int) $quadro->id) {
            throw new HttpException(404, 'Lista não encontrada neste quadro.');
        }

        if ($tarefa && $lista && (int) $tarefa->lista_id !== (int) $lista->id) {
            throw new HttpException(404, 'Tarefa não encontrada nesta lista.');
        }

        if ($checklist && $tarefa && (int) $checklist->tarefa_id !== (int) $tarefa->id) {
            throw new HttpException(404, 'Checklist não encontrada nesta tarefa.');
        }

        if ($item && $checklist && (int) $item->checklist_id !== (int) $checklist->id) {
            throw new HttpException(404, 'Item não encontrado nesta checklist.');
        }
    }
}
