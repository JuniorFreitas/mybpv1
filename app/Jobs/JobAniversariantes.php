<?php

namespace App\Jobs;

use App\Mail\AniversariantesMail;
use App\Models\ParabensEnviado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobAniversariantes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($dados)
    {
        $this->mail = [
            'selecionados' => $dados['selecionados'],
            'empresa_id' => $dados['empresa_id']
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $selecionados = \DB::table('curriculos')
                           ->select(['id', 'nome', 'email', 'nascimento', 'rg', 'orgao_expeditor'])
                           ->whereIn('id', $this->mail['selecionados'])->get();

        foreach ($selecionados as $aniversariante) {
            \Mail::send(new AniversariantesMail([
                'nome' => $aniversariante->nome,
                'email' => $aniversariante->email,
                'empresa_id' => $this->mail['empresa_id'],
            ]));

            ParabensEnviado::withoutGlobalScopes()->where('curriculo_id', $aniversariante->id)->where('ano', date('Y'))->update([
                'empresa_id' => $this->mail['empresa_id'],
                'status' => ParabensEnviado::STATUS_ENVIADO,
                'curriculo_id' => $aniversariante->id,
                'ano' => (int) date('Y'),
            ]);
        }
    }
}
