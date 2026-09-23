<?php

namespace App\Services\Aniversariante;

use App\Mail\AniversariantesMail;
use App\Models\Admissao;
use App\Models\ParabensEnviado;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AniversarianteEnvioDiaService
{
    /**
     * Dispara felicitações do dia (timezone da aplicação).
     *
     * @return array{total:int, enviados:int, erros:int, ignorados:int}
     */
    public function disparar(?CarbonInterface $referencia = null): array
    {
        $referencia = $referencia ? Carbon::instance($referencia) : now();
        $ano = (int) $referencia->format('Y');
        $mes = (int) $referencia->format('m');
        $dia = (int) $referencia->format('d');

        $selecionados = $this->buscarAniversariantesDoDia($ano, $mes, $dia);

        $resumo = [
            'total' => count($selecionados),
            'enviados' => 0,
            'erros' => 0,
            'ignorados' => 0,
        ];

        foreach ($selecionados as $selecionado) {
            $resultado = $this->processarAniversariante($selecionado, $ano);
            $resumo[$resultado]++;
        }

        if ($resumo['total'] > 0) {
            Log::info('Aniversariantes do dia processados', [
                'data' => $referencia->format('d/m/Y'),
                'total' => $resumo['total'],
                'enviados' => $resumo['enviados'],
                'erros' => $resumo['erros'],
                'ignorados' => $resumo['ignorados'],
            ]);
        }

        return $resumo;
    }

    /**
     * Reprocessa registros do ano corrente travados em enviando/erro.
     *
     * @return array{total:int, enviados:int, erros:int, ignorados:int}
     */
    public function retentarPendentesDoAno(?int $ano = null): array
    {
        $ano = $ano ?? (int) now()->format('Y');

        $pendentes = DB::select(
            "select c.id, c.nome, c.email, coalesce(p.empresa_id, u.empresa_id) as empresa_id
               from parabens_enviados p
               inner join curriculos c on c.id = p.curriculo_id and c.deleted_at is null
               inner join users u on u.id = c.id
              where p.ano = ?
                and p.status in (?, ?)
              order by p.id asc",
            [$ano, ParabensEnviado::STATUS_ENVIANDO, ParabensEnviado::STATUS_ERRO]
        );

        $resumo = [
            'total' => count($pendentes),
            'enviados' => 0,
            'erros' => 0,
            'ignorados' => 0,
        ];

        foreach ($pendentes as $selecionado) {
            $resultado = $this->processarAniversariante($selecionado, $ano);
            $resumo[$resultado]++;
        }

        Log::info('Retentativa de aniversariantes pendentes', [
            'ano' => $ano,
            'total' => $resumo['total'],
            'enviados' => $resumo['enviados'],
            'erros' => $resumo['erros'],
            'ignorados' => $resumo['ignorados'],
        ]);

        return $resumo;
    }

    /**
     * @return list<object>
     */
    public function buscarAniversariantesDoDia(int $ano, int $mes, int $dia): array
    {
        $query = "select c.id, c.nome, c.email, u.empresa_id
                    from curriculos c
                             inner join feedback_curriculos fc on c.id = fc.curriculo_id
                             inner join users u on c.id = u.id
                             inner join admissoes a on fc.id = a.feedback_id
                             inner join clientes cl on u.empresa_id = cl.id
                   where not exists (
                            select 1
                              from parabens_enviados p
                             where p.curriculo_id = fc.curriculo_id
                               and p.ano = ?
                               and p.status in (?, ?)
                         )
                     and not exists (
                            select 1 from demissaos d where fc.id = d.feedback_id
                         )
                     and fc.deleted_at is null
                     and a.status = ?
                     and month(c.nascimento) = ?
                     and day(c.nascimento) = ?
                     and c.deleted_at is null
                     and cl.ativo = true";

        return DB::select($query, [
            $ano,
            ParabensEnviado::STATUS_ENVIADO,
            ParabensEnviado::STATUS_NAO,
            Admissao::STATUS_ADMISSAO_ADMITIDO,
            $mes,
            $dia,
        ]);
    }

    public function normalizarEmail(?string $email): string
    {
        $email = strtolower(trim((string) $email));
        $email = preg_replace('/\s+/', '', $email) ?? '';

        return $email;
    }

    public function emailValido(?string $email): bool
    {
        $email = $this->normalizarEmail($email);

        if ($email === '') {
            return false;
        }

        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function emailIgnorado(?string $email): bool
    {
        $email = $this->normalizarEmail($email);

        if ($email === '') {
            return false;
        }

        $suprimidos = array_map(
            fn ($item) => $this->normalizarEmail($item),
            config('mail.suppress_recipients', ['sistema@mybp.com.br'])
        );

        return in_array($email, $suprimidos, true);
    }

    /**
     * @param  object{id:int,nome:?string,email:?string,empresa_id:int}  $selecionado
     * @return 'enviados'|'erros'|'ignorados'
     */
    public function processarAniversariante(object $selecionado, int $ano): string
    {
        $curriculoId = (int) $selecionado->id;
        $empresaId = (int) $selecionado->empresa_id;
        $email = $this->normalizarEmail($selecionado->email ?? '');

        if ($this->emailIgnorado($email)) {
            $this->marcarStatus($curriculoId, $empresaId, $ano, ParabensEnviado::STATUS_NAO);

            return 'ignorados';
        }

        if (! $this->emailValido($email)) {
            $this->marcarStatus($curriculoId, $empresaId, $ano, ParabensEnviado::STATUS_ERRO);
            Log::warning('Aniversariante sem e-mail válido', [
                'curriculo_id' => $curriculoId,
                'empresa_id' => $empresaId,
            ]);

            return 'erros';
        }

        $this->marcarStatus($curriculoId, $empresaId, $ano, ParabensEnviado::STATUS_ENVIANDO);

        try {
            Mail::send(new AniversariantesMail([
                'nome' => $selecionado->nome,
                'email' => $email,
                'empresa_id' => $empresaId,
            ]));

            $this->marcarStatus($curriculoId, $empresaId, $ano, ParabensEnviado::STATUS_ENVIADO);

            return 'enviados';
        } catch (Throwable $e) {
            $this->marcarStatus($curriculoId, $empresaId, $ano, ParabensEnviado::STATUS_ERRO);
            Log::error('Falha ao enviar felicitações de aniversário', [
                'curriculo_id' => $curriculoId,
                'empresa_id' => $empresaId,
                'erro' => $e->getMessage(),
            ]);

            return 'erros';
        }
    }

    public function marcarStatus(int $curriculoId, int $empresaId, int $ano, string $status): void
    {
        $existente = DB::table('parabens_enviados')
            ->where('curriculo_id', $curriculoId)
            ->where('ano', $ano)
            ->orderByDesc('id')
            ->first();

        if ($existente) {
            DB::table('parabens_enviados')
                ->where('id', $existente->id)
                ->update([
                    'empresa_id' => $empresaId,
                    'status' => $status,
                ]);

            return;
        }

        DB::table('parabens_enviados')->insert([
            'curriculo_id' => $curriculoId,
            'empresa_id' => $empresaId,
            'ano' => $ano,
            'status' => $status,
        ]);
    }
}
