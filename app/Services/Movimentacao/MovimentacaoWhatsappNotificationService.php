<?php

namespace App\Services\Movimentacao;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Domain\Whatsapp\Services\WhatsappUsuarioTelefoneResolver;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MovimentacaoWhatsappNotificationService
{
    public function __construct(
        private readonly WhatsappNotificationGateService $gate,
        private readonly WhatsappUsuarioTelefoneResolver $telefoneResolver,
    ) {
    }

    public function enviarNotificacaoAprovacao(
        int $empresaId,
        array $destinatariosEmails,
        string $modulo,
        string $tipo,
        string $colaborador,
        string $url,
        string $nomeAprovacaoExtra = 'Aprovação Extra'
    ): void {
        if (!$this->gate->podeEnviar(TipoMensagemWhatsapp::MovimentacaoAprovacao, $empresaId)) {
            Log::info('WhatsApp não enviado: módulo desabilitado ou empresa sem permissão', [
                'empresa_id' => $empresaId,
                'modulo' => TipoMensagemWhatsapp::MovimentacaoAprovacao->modulo(),
                'tipo' => TipoMensagemWhatsapp::MovimentacaoAprovacao->value,
            ]);

            return;
        }

        $textos = $this->resolverTextos($modulo, $tipo, $nomeAprovacaoExtra);

        $indiceDestinatario = 0;
        foreach (array_unique(array_filter($destinatariosEmails)) as $email) {
            $usuario = User::withoutGlobalScopes()
                ->select('id', 'nome', 'login', 'empresa_id', 'ativo')
                ->where('login', $email)
                ->where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->first();

            if (!$usuario) {
                continue;
            }

            $telefone = $this->telefoneResolver->resolverNumeroEnvio($usuario->id);
            if ($telefone === null) {
                continue;
            }

            if (!$this->gate->podeEnviar(
                TipoMensagemWhatsapp::MovimentacaoAprovacao,
                $empresaId,
                $usuario->id,
            )) {
                continue;
            }

            $mensagem = app(WhatsappMessageFactory::class)->render(
                TipoMensagemWhatsapp::MovimentacaoAprovacao,
                $empresaId,
                [
                    'nome_destinatario' => $usuario->nome,
                    'titulo_notificacao' => $textos['titulo'],
                    'mensagem_notificacao' => $textos['mensagem'],
                    'modulo_movimentacao' => $modulo,
                    'colaborador' => $colaborador,
                    'url_sistema' => $url,
                ]
            );

            (new ZapNotificacao())->enviar([
                'enviado_id' => $usuario->id,
                'telefone' => $telefone,
                'mensagem' => $mensagem,
                '_whatsapp_meta' => ZapNotificacao::meta(
                    TipoMensagemWhatsapp::MovimentacaoAprovacao,
                    $empresaId,
                    $usuario->id,
                ),
            ], ZapNotificacao::calcularDelayFila($indiceDestinatario));

            $indiceDestinatario++;
        }
    }

    /** @return array{titulo: string, mensagem: string} */
    private function resolverTextos(string $modulo, string $tipo, string $nomeAprovacaoExtra): array
    {
        $moduloLower = mb_strtolower($modulo);

        $titulos = [
            'criacao' => "Nova solicitação de {$moduloLower} — sua aprovação é necessária",
            'pendente_aprovacao_extra' => "{$modulo} — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "{$modulo} — aguardando aprovação do RH",
            'reprovado_gestor' => "Solicitação de {$moduloLower} reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Solicitação de {$moduloLower} reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Solicitação de {$moduloLower} reprovada pelo RH",
            'cancelado' => "Solicitação de {$moduloLower} cancelada",
            'aprovado_final' => "{$modulo} aprovada em todas as etapas",
        ];

        $mensagens = [
            'criacao' => "Uma nova solicitação de {$moduloLower} foi registrada e está aguardando sua análise. Acesse o sistema para aprovar ou reprovar.",
            'pendente_aprovacao_extra' => "O gestor já aprovou. A solicitação agora aguarda a análise de {$nomeAprovacaoExtra}. Você será notificado quando houver conclusão.",
            'pendente_aprovacao_rh' => 'O gestor e a aprovação anterior já validaram a solicitação. Agora ela aguarda a análise do RH para ser concluída.',
            'reprovado_gestor' => "A solicitação de {$moduloLower} foi reprovada pelo gestor e o processo foi encerrado.",
            'reprovado_aprovacao_extra' => "A solicitação de {$moduloLower} foi reprovada por {$nomeAprovacaoExtra} e o processo foi encerrado.",
            'reprovado_rh' => "A solicitação de {$moduloLower} foi reprovada pelo RH e o processo foi encerrado.",
            'cancelado' => "A solicitação de {$moduloLower} foi cancelada e o processo foi encerrado.",
            'aprovado_final' => "A solicitação de {$moduloLower} foi aprovada por todos os responsáveis e está concluída. Os próximos passos já podem ser realizados.",
        ];

        return [
            'titulo' => $titulos[$tipo] ?? 'Notificação',
            'mensagem' => $mensagens[$tipo] ?? '',
        ];
    }
}
