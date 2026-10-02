<?php

namespace App\Services\Aniversariante;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Models\AniversarianteWhatsappMensagem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AniversarianteWhatsappService
{
    public const STATUS_ENFILEIRADO = 'enfileirado';

    public const STATUS_IGNORADO = 'ignorado';

    public const DELAY_MIN_SEGUNDOS = 5;

    public const DELAY_MAX_SEGUNDOS = 15;

    public const ASSINATURA = 'Equipe de RH';

    public function __construct(
        private readonly WhatsappNotificationGateService $gate,
        private readonly WhatsappCurriculoTelefoneResolver $telefones,
    ) {
    }

    public function garantirPadroes(int $empresaId): void
    {
        if ($empresaId <= 0 || ! Schema::hasTable('aniversariante_whatsapp_mensagens')) {
            return;
        }

        $existentes = AniversarianteWhatsappMensagem::query()
            ->where('empresa_id', $empresaId)
            ->pluck('dia')
            ->map(fn ($dia) => (int) $dia)
            ->all();

        $agora = now();
        $novas = [];

        foreach (AniversarianteWhatsappMensagensPadrao::todas() as $dia => $corpo) {
            if (in_array((int) $dia, $existentes, true)) {
                continue;
            }

            $novas[] = [
                'empresa_id' => $empresaId,
                'dia' => (int) $dia,
                'corpo' => $corpo,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        if ($novas !== []) {
            DB::table('aniversariante_whatsapp_mensagens')->insert($novas);
        }
    }

    public function diaDaMensagem(int $dia): int
    {
        if ($dia < 1) {
            return 1;
        }

        if ($dia > 30) {
            return 30;
        }

        return $dia;
    }

    public function renderizar(string $corpo, string $nome): string
    {
        $nome = trim($nome);
        if ($nome === '') {
            $nome = 'Colaborador';
        }

        $texto = preg_replace('/\[Nome\]/iu', $nome, $corpo) ?? $corpo;

        return str_replace('#BPTEAM', self::ASSINATURA, $texto);
    }

    public function corpoDoDia(int $empresaId, int $dia): string
    {
        $dia = $this->diaDaMensagem($dia);
        $this->garantirPadroes($empresaId);

        $corpo = AniversarianteWhatsappMensagem::query()
            ->where('empresa_id', $empresaId)
            ->where('dia', $dia)
            ->value('corpo');

        if (is_string($corpo) && trim($corpo) !== '') {
            return $corpo;
        }

        return AniversarianteWhatsappMensagensPadrao::todas()[$dia];
    }

    public function enviarSeHabilitado(object $selecionado): void
    {
        if (! Schema::hasTable('cliente_configs') || ! Schema::hasColumn('cliente_configs', 'aniversario_whatsapp')) {
            return;
        }

        $curriculoId = (int) ($selecionado->id ?? 0);
        $empresaId = (int) ($selecionado->empresa_id ?? 0);

        if ($curriculoId <= 0 || $empresaId <= 0 || ! $this->gate->podeEnviarAniversario($empresaId)) {
            return;
        }

        if ($this->whatsappJaEnfileirado($curriculoId)) {
            return;
        }

        $telefone = $this->telefones->resolverPrincipalWhatsapp($curriculoId);

        if ($telefone === null) {
            $this->marcarWhatsapp($curriculoId, $empresaId, self::STATUS_IGNORADO);

            return;
        }

        $mensagem = $this->renderizar(
            $this->corpoDoDia($empresaId, $this->diaDoCurriculo($curriculoId)),
            (string) ($selecionado->nome ?? '')
        );

        (new ZapNotificacao())->enviar([
            'enviado_id' => $curriculoId,
            'telefone' => $telefone->sonumero,
            'mensagem' => $mensagem,
            '_whatsapp_meta' => [
                'tipo' => 'aniversario',
                'empresa_id' => $empresaId,
            ],
        ], random_int(self::DELAY_MIN_SEGUNDOS, self::DELAY_MAX_SEGUNDOS));

        $this->marcarWhatsapp($curriculoId, $empresaId, self::STATUS_ENFILEIRADO);
    }

    private function diaDoCurriculo(int $curriculoId): int
    {
        $dia = (int) now()->format('d');

        if (Schema::hasTable('curriculos')) {
            $nascimento = DB::table('curriculos')->where('id', $curriculoId)->value('nascimento');
            if ($nascimento) {
                $timestamp = strtotime((string) $nascimento);
                if ($timestamp !== false) {
                    $dia = (int) date('d', $timestamp);
                }
            }
        }

        return $this->diaDaMensagem($dia);
    }

    private function whatsappJaEnfileirado(int $curriculoId): bool
    {
        if (! Schema::hasTable('parabens_enviados') || ! Schema::hasColumn('parabens_enviados', 'whatsapp_status')) {
            return false;
        }

        $status = DB::table('parabens_enviados')
            ->where('curriculo_id', $curriculoId)
            ->where('ano', (int) now()->format('Y'))
            ->orderByDesc('id')
            ->value('whatsapp_status');

        return $status === self::STATUS_ENFILEIRADO;
    }

    private function marcarWhatsapp(int $curriculoId, int $empresaId, string $status): void
    {
        if (! Schema::hasTable('parabens_enviados') || ! Schema::hasColumn('parabens_enviados', 'whatsapp_status')) {
            return;
        }

        $id = DB::table('parabens_enviados')
            ->where('curriculo_id', $curriculoId)
            ->where('ano', (int) now()->format('Y'))
            ->orderByDesc('id')
            ->value('id');

        if ($id) {
            DB::table('parabens_enviados')->where('id', $id)->update([
                'empresa_id' => $empresaId,
                'whatsapp_status' => $status,
            ]);

            return;
        }

        DB::table('parabens_enviados')->insert([
            'curriculo_id' => $curriculoId,
            'empresa_id' => $empresaId,
            'ano' => (int) now()->format('Y'),
            'status' => 'não',
            'whatsapp_status' => $status,
        ]);
    }
}
