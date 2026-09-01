<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Aniversariante\AniversarianteMensagemRenderer;
use App\Services\Aniversariante\AniversarianteMensagemResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AniversariantesMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $dados = [];

    public function __construct(array $dados)
    {
        $empresa = User::find($dados['empresa_id'] ?? null);
        $empresaNome = $empresa?->nome ?: 'MyBP';
        $this->dados = $dados;
        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', $empresaNome);
        $this->subject = $empresaNome . " - FELIZ ANIVERSÁRIO";
        $this->assunto = $this->subject;

        $resolver = app(AniversarianteMensagemResolver::class);
        $renderer = app(AniversarianteMensagemRenderer::class);
        $placeholders = [
            'nome' => $dados['nome'] ?? '',
            'empresa' => $empresa?->nome ?? '',
        ];
        $html = $resolver->conteudoHtml(isset($dados['empresa_id']) ? (int) $dados['empresa_id'] : null);
        $mensagem = $renderer->render($html, $placeholders);
        if (! $resolver->temConteudoUtil($mensagem)) {
            $mensagem = $renderer->render(AniversarianteMensagemResolver::MENSAGEM_PADRAO, $placeholders);
        }
        $this->dados['mensagem'] = $mensagem;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.aniversariantes.parabens');
    }
}
