<?php

namespace App\Mail;

use App\Models\Cliente;
use App\Models\VagasAbertas;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecrutamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $dados = [];

    public function __construct(array $dados)
    {
        $empresa = Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
        $vagaAberta = VagasAbertas::withoutGlobalScopes()
            ->with('Municipio')
            ->find($dados['vaga_aberta_id']);

        $localidade = null;
        if ($vagaAberta?->Municipio) {
            $localidade = trim(($vagaAberta->Municipio->nome ?? '') . '/' . ($vagaAberta->Municipio->uf ?? ''), '/');
        }

        $tituloVaga = $vagaAberta?->titulo ?: 'vaga aberta';
        $razaoSocial = $empresa?->razao_social ?: 'MyBPIN';

        $this->dados = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'empresa_id' => $dados['empresa_id'],
            'vaga_aberta_id' => $dados['vaga_aberta_id'],
            'titulo_vaga' => $tituloVaga,
            'localidade' => $localidade,
            'razao_social' => $razaoSocial,
        ];

        $assunto = 'Candidatura recebida — ' . $tituloVaga;

        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', $razaoSocial);
        $this->subject = $assunto;
        $this->assunto = $assunto;
    }

    public function build()
    {
        return $this->view('email.recrutamento.cadastro');
    }
}
