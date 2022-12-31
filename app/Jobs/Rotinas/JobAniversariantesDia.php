<?php

namespace App\Jobs\Rotinas;

use App\Mail\AniversariantesMail;
use App\Models\Admissao;
use App\Models\ParabensEnviado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobAniversariantesDia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function __invoke()
    {
        $this->handle();
    }

    public function handle()
    {
        try {
            $query = \DB::raw("select c.id, c.nome, c.email, DATE_FORMAT(c.nascimento, '%d/%m/%Y') as nascimento, u.empresa_id
                        from curriculos c
                                 inner join feedback_curriculos fc on c.id = fc.curriculo_id
                                 inner join users u on c.id = u.id
                                 inner join admissoes a on fc.id = a.feedback_id
                        where not exists(select fc.id,d.feedback_id from demissaos d where fc.id = d.feedback_id)
                          and not exists(select p.curriculo_id from parabens_enviados p where fc.curriculo_id = p.curriculo_id and p.ano = year(now()))
                          and fc.deleted_at is null
                          and a.status = ?
                          and c.email != 'sistema@mybp.com.br'
                          and month(c.nascimento) = month(now())
                          and day(c.nascimento) = day(now())
                          and c.deleted_at is null
                    ");

            $selecionados = \DB::select($query, [Admissao::STATUS_ADMISSAO_ADMITIDO]);

            if (count($selecionados) > 0) {
                foreach ($selecionados as $selecionado) {
                    \DB::table('parabens_enviados')->insert([
                        'empresa_id' => $selecionado->empresa_id,
                        'status' => ParabensEnviado::STATUS_ENVIANDO,
                        'curriculo_id' => $selecionado->id,
                        'ano' => (int)date('Y'),
                    ]);

                    \Mail::send(new AniversariantesMail([
                        'nome' => $selecionado->nome,
                        'email' => $selecionado->email,
                        'empresa_id' => $selecionado->empresa_id,
                    ]));

                    \DB::table('parabens_enviados')
                        ->where('curriculo_id', $selecionado->id)
                        ->where('empresa_id', $selecionado->empresa_id)
                        ->where('ano', (int)date('Y'))
                        ->update([
                            'status' => ParabensEnviado::STATUS_ENVIADO,
                        ]);
                }
                \Log::info(count($selecionados) . ' - Aniversariantes do dia ' . date('d/m/Y') . ' que receberam notificações!');
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
