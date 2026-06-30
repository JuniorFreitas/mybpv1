<?php

namespace App\Services\Entrevistas;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Models\LogHistorico;
use App\Models\ParecerRota;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ParecerRotaWhatsappService
{
    public function montarMensagem(ParecerRota $parecer, User $user): string
    {
        $parecer->loadMissing('FeedbackCurriculo.Curriculo');
        $user->loadMissing('Empresa');

        $nome = $parecer->FeedbackCurriculo?->Curriculo?->nome ?? 'Candidato';

        return app(WhatsappMessageFactory::class)->render(
            TipoMensagemWhatsapp::ParecerRotaTransporte,
            (int) $user->empresa_id,
            [
                'nome_destinatario' => $nome,
                'rota' => $this->valorOuNaoInformado($parecer->qual),
                'bairro' => $this->valorOuNaoInformado($parecer->bairro_rota),
                'ponto_referencia' => $this->valorOuNaoInformado($parecer->ponto_referencia_rota),
            ]
        );
    }

    public function montarAcaoLog(ParecerRota $parecer, string $telefoneMascara): string
    {
        $rota = $this->valorOuNaoInformado($parecer->qual);
        $bairro = $this->valorOuNaoInformado($parecer->bairro_rota);
        $ponto = $this->valorOuNaoInformado($parecer->ponto_referencia_rota);

        return sprintf(
            'Enviou WhatsApp do Parecer Rota Transporte (rota: %s, bairro: %s, ponto: %s, telefone: %s)',
            $rota,
            $bairro,
            $ponto,
            $telefoneMascara
        );
    }

    public function atualizarTelefoneWhatsapp(int $curriculoId, string $numero, string $tipo): TelefoneCurriculo
    {
        if ($tipo !== TelefoneCurriculo::TIPO_WHATS) {
            throw new InvalidArgumentException('O telefone deve ser do tipo WhatsApp.');
        }

        $numeroSanitizado = preg_replace('/[^0-9]/', '', $numero);

        if (strlen($numeroSanitizado) < 10) {
            throw new InvalidArgumentException('Telefone inválido.');
        }

        return TelefoneCurriculo::query()->updateOrCreate(
            ['curriculo_id' => $curriculoId, 'principal' => true],
            [
                'tipo' => TelefoneCurriculo::TIPO_WHATS,
                'pais' => '55',
                'numero' => $numeroSanitizado,
                'principal' => true,
            ]
        );
    }

    public function enviar(ParecerRota $parecer, string $telefone, string $tipo, User $user): ParecerRota
    {
        if (!$parecer->tem_rota) {
            throw new InvalidArgumentException('Envio permitido somente quando há rota que atende.');
        }

        if ($tipo !== TelefoneCurriculo::TIPO_WHATS) {
            throw new InvalidArgumentException('O telefone deve ser do tipo WhatsApp.');
        }

        if (!app(WhatsappNotificationGateService::class)->podeEnviar(
            TipoMensagemWhatsapp::ParecerRotaTransporte,
            (int) $user->empresa_id,
        )) {
            throw new InvalidArgumentException(
                'Envio de WhatsApp não permitido: empresa sem WhatsApp liberado ou módulo de Transporte desativado.',
            );
        }

        $parecer->loadMissing('FeedbackCurriculo');

        $feedbackId = $parecer->feedback_id;
        $curriculoId = $parecer->FeedbackCurriculo?->curriculo_id;
        if (!$curriculoId || !$feedbackId) {
            throw new InvalidArgumentException('Currículo não encontrado para este parecer.');
        }

        $telefoneSalvo = $this->atualizarTelefoneWhatsapp($curriculoId, $telefone, $tipo);

        if ($telefoneSalvo->tipo !== TelefoneCurriculo::TIPO_WHATS) {
            throw new InvalidArgumentException('O telefone principal deve ser do tipo WhatsApp.');
        }

        $mensagem = $this->montarMensagem($parecer, $user);
        $telefoneMascara = $this->mascararTelefone($telefoneSalvo->sonumero);
        $enviadoEm = now();

        DB::transaction(function () use ($parecer, $user, $feedbackId, $curriculoId, $telefoneSalvo, $mensagem, $telefoneMascara, $enviadoEm) {
            (new ZapNotificacao())->enviar([
                'enviado_id' => $curriculoId,
                'telefone' => $telefoneSalvo->sonumero,
                'mensagem' => $mensagem,
                '_whatsapp_meta' => ZapNotificacao::meta(
                    TipoMensagemWhatsapp::ParecerRotaTransporte,
                    (int) $user->empresa_id,
                ),
            ]);

            LogHistorico::createLog(
                $feedbackId,
                $this->montarAcaoLog($parecer, $telefoneMascara)
            );

            $parecer->update([
                'whatsapp_enviado_em' => $enviadoEm,
                'whatsapp_enviado_por' => $user->id,
            ]);
        });

        return $parecer->fresh([
            'QuemEnviouWhatsapp:id,nome',
        ]);
    }

    public function mascararTelefone(string $telefone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $telefone);

        if (strlen($digits) <= 4) {
            return '****';
        }

        return '****' . substr($digits, -4);
    }

    private function valorOuNaoInformado(?string $valor): string
    {
        $valor = trim((string) $valor);

        return $valor !== '' ? $valor : 'Não informado';
    }
}
