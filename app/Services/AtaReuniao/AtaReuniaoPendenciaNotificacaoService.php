<?php

namespace App\Services\AtaReuniao;

use App\Jobs\AtaReuniao\SendAtaReuniaoPendenciaMailJob;
use App\Models\AtaReuniaoNotificacaoConfig;
use App\Models\AtaReuniaoNotificacao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AtaReuniaoPendenciaNotificacaoService
{
    public const TIPO_D2 = 'pendencia_d2';
    public const TIPO_VENCIMENTO = 'pendencia_vencimento';
    public const TIPO_ATRASO_PREFIXO = 'pendencia_atraso_d';

    private const STATUS_ABERTOS = [
        'andamento',
        'pendente',
        'nao_iniciada',
        'em_andamento',
        'aguardando_terceiro',
        'aguardando_validacao',
        'reprogramada',
    ];

    private const STATUS_FECHADOS = [
        'concluido',
        'concluida',
        'cancelada',
        'cancelado',
    ];

    public function notificarD2(int $empresaId, ?Carbon $dataBase = null): int
    {
        $config = AtaReuniaoNotificacaoConfig::obterOuPadrao($empresaId);
        $dataBase = ($dataBase ?: now())->copy()->startOfDay();

        return $this->notificarAntecedencia($empresaId, $config, $dataBase);
    }

    public function notificarConfiguradas(int $empresaId, ?Carbon $dataBase = null): array
    {
        $config = AtaReuniaoNotificacaoConfig::obterOuPadrao($empresaId);
        $dataBase = ($dataBase ?: now())->copy()->startOfDay();

        $totais = [
            'antecedencia' => $this->notificarAntecedencia($empresaId, $config, $dataBase),
            'vencimento' => 0,
            'atrasos' => 0,
        ];

        if ($config->reenviar_no_vencimento) {
            $totais['vencimento'] = $this->notificarPorData($empresaId, self::TIPO_VENCIMENTO, $dataBase, $dataBase);
        }

        if ($config->cobrar_apos_atraso) {
            foreach ($config->diasEscalonamento() as $diasAtraso) {
                $tipo = self::TIPO_ATRASO_PREFIXO . $diasAtraso;
                $prazoAlvo = $dataBase->copy()->subDays($diasAtraso);
                $totais['atrasos'] += $this->notificarPorData($empresaId, $tipo, $prazoAlvo, $dataBase, $diasAtraso);
            }
        }

        return $totais;
    }

    private function notificarAntecedencia(int $empresaId, AtaReuniaoNotificacaoConfig $config, Carbon $dataBase): int
    {
        if ($config->usar_dias_uteis) {
            return $this->notificarAntecedenciaDiasUteis($empresaId, $config, $dataBase);
        }

        $dataAlvo = $dataBase->copy()->addDays($config->dias_antecedencia)->startOfDay();

        return $this->notificarPorData($empresaId, self::TIPO_D2, $dataAlvo, $dataBase);
    }

    private function notificarAntecedenciaDiasUteis(int $empresaId, AtaReuniaoNotificacaoConfig $config, Carbon $dataBase): int
    {
        $inicio = $dataBase->copy()->startOfDay();
        $fim = $dataBase->copy()->addDays(max(10, $config->dias_antecedencia + 7))->startOfDay();
        $total = 0;

        $pendencias = DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->join('users as u', 'u.id', '=', 'a.responsavel_id')
            ->leftJoin('area_etiquetas as area', 'area.id', '=', 'ata.area_etiqueta_id')
            ->leftJoin('users as gestor', 'gestor.id', '=', 'area.gestor_id')
            ->where(function ($query) use ($empresaId) {
                $query->where('a.empresa_id', $empresaId)
                    ->orWhere(function ($subQuery) use ($empresaId) {
                        $subQuery->whereNull('a.empresa_id')->where('ata.empresa_id', $empresaId);
                    });
            })
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereNull('u.deleted_at')
            ->where('u.ativo', true)
            ->whereIn('a.status', self::STATUS_ABERTOS)
            ->whereBetween('a.prazo', [$inicio->toDateString(), $fim->toDateString()])
            ->whereNotNull('a.responsavel_id')
            ->whereNotNull('u.login')
            ->select([
                'a.id as pendencia_id',
                'a.ata_reuniao_id',
                'a.empresa_id as pendencia_empresa_id',
                'a.titulo as pendencia_titulo',
                'a.acao',
                'a.descricao',
                'a.prazo',
                'a.status',
                'a.prioridade',
                'ata.empresa_id as ata_empresa_id',
                'ata.codigo as ata_codigo',
                'ata.titulo as ata_titulo',
                'u.id as responsavel_id',
                'u.nome as responsavel_nome',
                'u.login as responsavel_email',
                'gestor.nome as gestor_nome',
                'gestor.login as gestor_email',
            ])
            ->orderBy('a.prazo')
            ->get();

        foreach ($pendencias as $pendencia) {
            $dataNotificacao = $this->subtrairDiasUteis($empresaId, Carbon::parse($pendencia->prazo), $config->dias_antecedencia);

            if (!$dataNotificacao->isSameDay($dataBase) || !filter_var($pendencia->responsavel_email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($this->criarNotificacao($empresaId, self::TIPO_D2, $pendencia, $dataBase, null, $config)) {
                $total++;
            }
        }

        return $total;
    }

    private function notificarPorData(int $empresaId, string $tipo, Carbon $prazoAlvo, Carbon $dataBase, ?int $diasAtraso = null): int
    {
        $total = 0;

        $pendencias = $this->pendenciasPorPrazo($empresaId, $prazoAlvo)->get();

        foreach ($pendencias as $pendencia) {
            if (!filter_var($pendencia->responsavel_email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($this->criarNotificacao($empresaId, $tipo, $pendencia, $dataBase, $diasAtraso)) {
                $total++;
            }
        }

        return $total;
    }

    private function pendenciasPorPrazo(int $empresaId, Carbon $prazoAlvo)
    {
        return DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->join('users as u', 'u.id', '=', 'a.responsavel_id')
            ->leftJoin('area_etiquetas as area', 'area.id', '=', 'ata.area_etiqueta_id')
            ->leftJoin('users as gestor', 'gestor.id', '=', 'area.gestor_id')
            ->where(function ($query) use ($empresaId) {
                $query->where('a.empresa_id', $empresaId)
                    ->orWhere(function ($subQuery) use ($empresaId) {
                        $subQuery->whereNull('a.empresa_id')->where('ata.empresa_id', $empresaId);
                    });
            })
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereNull('u.deleted_at')
            ->where('u.ativo', true)
            ->whereIn('a.status', self::STATUS_ABERTOS)
            ->whereDate('a.prazo', $prazoAlvo->toDateString())
            ->whereNotNull('a.responsavel_id')
            ->whereNotNull('u.login')
            ->select([
                'a.id as pendencia_id',
                'a.ata_reuniao_id',
                'a.empresa_id as pendencia_empresa_id',
                'a.titulo as pendencia_titulo',
                'a.acao',
                'a.descricao',
                'a.prazo',
                'a.status',
                'a.prioridade',
                'ata.empresa_id as ata_empresa_id',
                'ata.codigo as ata_codigo',
                'ata.titulo as ata_titulo',
                'u.id as responsavel_id',
                'u.nome as responsavel_nome',
                'u.login as responsavel_email',
                'gestor.nome as gestor_nome',
                'gestor.login as gestor_email',
            ])
            ->orderBy('a.prazo');
    }

    private function criarNotificacao(int $empresaId, string $tipo, object $pendencia, Carbon $dataBase, ?int $diasAtraso = null, ?AtaReuniaoNotificacaoConfig $config = null): bool
    {
        $config = $config ?: AtaReuniaoNotificacaoConfig::obterOuPadrao($empresaId);
        $notificacao = AtaReuniaoNotificacao::firstOrCreate([
            'empresa_id' => $empresaId,
            'ata_reuniao_acao_id' => $pendencia->pendencia_id,
            'destinatario_id' => $pendencia->responsavel_id,
            'tipo' => $tipo,
            'data_prazo_referencia' => $pendencia->prazo,
        ], [
            'ata_reuniao_id' => $pendencia->ata_reuniao_id,
            'canal' => 'email',
            'modo_disparo' => 'automatico',
            'status' => 'pendente',
            'destinatario_nome' => $pendencia->responsavel_nome,
            'destinatario_email' => $pendencia->responsavel_email,
            'assunto' => $this->assunto($tipo, $pendencia->pendencia_id),
            'payload' => $this->payload($pendencia, $tipo, $dataBase, $diasAtraso, $config),
        ]);

        if (!$notificacao->wasRecentlyCreated) {
            return false;
        }

        SendAtaReuniaoPendenciaMailJob::dispatch($notificacao->id);

        return true;
    }

    public function marcarAtrasadas(int $empresaId, ?Carbon $dataBase = null): int
    {
        $dataBase = ($dataBase ?: now())->copy()->startOfDay();

        return DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->where(function ($query) use ($empresaId) {
                $query->where('a.empresa_id', $empresaId)
                    ->orWhere(function ($subQuery) use ($empresaId) {
                        $subQuery->whereNull('a.empresa_id')->where('ata.empresa_id', $empresaId);
                    });
            })
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereNotIn('a.status', array_merge(self::STATUS_FECHADOS, ['atrasada']))
            ->whereNotNull('a.prazo')
            ->whereDate('a.prazo', '<', $dataBase->toDateString())
            ->update([
                'a.status' => 'atrasada',
                'a.updated_at' => now(),
            ]);
    }

    private function payload(object $pendencia, string $tipo, Carbon $dataBase, ?int $diasAtraso = null, ?AtaReuniaoNotificacaoConfig $config = null): array
    {
        $descricao = $pendencia->descricao ?: $pendencia->acao;
        $cc = [];

        if ($config?->incluir_gestor_copia && !empty($pendencia->gestor_email) && filter_var($pendencia->gestor_email, FILTER_VALIDATE_EMAIL)) {
            $cc[] = ['email' => $pendencia->gestor_email, 'nome' => $pendencia->gestor_nome ?: null];
        }

        return [
            'tipo' => $tipo,
            'mensagem_contexto' => $this->mensagemContexto($tipo, $diasAtraso),
            'nome_responsavel' => $pendencia->responsavel_nome,
            'codigo_pendencia' => $this->codigoPendencia($pendencia->pendencia_id),
            'ata' => trim(($pendencia->ata_codigo ?: 'ATA') . ' - ' . ($pendencia->ata_titulo ?: 'Ata de Reunião')),
            'pendencia' => $descricao,
            'prazo' => Carbon::parse($pendencia->prazo)->format('d/m/Y'),
            'status' => $pendencia->status,
            'prioridade' => $pendencia->prioridade ?: 'media',
            'cc' => $cc,
            'data_disparo' => $dataBase->format('d/m/Y'),
            'link' => url('/g/administracao/atareuniao/' . $pendencia->ata_reuniao_id . '/editar'),
        ];
    }

    private function assunto(string $tipo, int $pendenciaId): string
    {
        $codigo = $this->codigoPendencia($pendenciaId);

        if ($tipo === self::TIPO_VENCIMENTO) {
            return 'Pendência vence hoje - ' . $codigo;
        }

        if (str_starts_with($tipo, self::TIPO_ATRASO_PREFIXO)) {
            return 'Pendência em atraso - ' . $codigo;
        }

        return 'Pendência próxima do vencimento - ' . $codigo;
    }

    private function mensagemContexto(string $tipo, ?int $diasAtraso): string
    {
        if ($tipo === self::TIPO_VENCIMENTO) {
            return 'A pendência abaixo vence hoje.';
        }

        if (str_starts_with($tipo, self::TIPO_ATRASO_PREFIXO)) {
            return 'A pendência abaixo está atrasada há ' . $diasAtraso . ' dia(s).';
        }

        return 'A pendência abaixo está próxima do vencimento.';
    }

    private function subtrairDiasUteis(int $empresaId, Carbon $data, int $dias): Carbon
    {
        $resultado = $data->copy()->startOfDay();
        $subtraidos = 0;

        while ($subtraidos < $dias) {
            $resultado->subDay();

            if ($resultado->isWeekend() || $this->isFeriado($empresaId, $resultado)) {
                continue;
            }

            $subtraidos++;
        }

        return $resultado;
    }

    private function isFeriado(int $empresaId, Carbon $data): bool
    {
        return DB::table('feriados')
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->whereDate('data', $data->toDateString())
            ->exists();
    }

    private function codigoPendencia(int $id): string
    {
        return 'PEND-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
}
