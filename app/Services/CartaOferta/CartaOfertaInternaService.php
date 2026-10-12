<?php

namespace App\Services\CartaOferta;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Classes\ZapNotificacao;
use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Jobs\JobEnvioCartaOferta;
use App\Models\CartaOferta;
use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use RuntimeException;

class CartaOfertaInternaService
{
    public function criar(array $dados, int $empresaId, int $usuarioId): CartaOferta
    {
        $template = CartaOfertaTemplate::query()
            ->where('empresa_id', $empresaId)
            ->publicado()
            ->orderByDesc('versao')
            ->first();

        if (!$template) {
            throw new RuntimeException('Publique um template de Carta Oferta antes de criar uma carta.');
        }

        // curriculos não tem empresa_id: tenant via users (Pessoa)
        $curriculo = Curriculo::query()
            ->where('id', $dados['curriculo_id'])
            ->whereHas('Pessoa', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->first();

        if (!$curriculo) {
            throw new RuntimeException('Currículo não encontrado nesta empresa.');
        }

        if (empty($curriculo->email)) {
            throw new RuntimeException('O candidato não possui e-mail cadastrado.');
        }

        $vagaAberta = VagasAbertas::query()
            ->where('id', $dados['vagas_abertas_id'])
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$vagaAberta) {
            throw new RuntimeException('Vaga aberta não encontrada nesta empresa.');
        }

        $aberta = CartaOferta::query()
            ->where('empresa_id', $empresaId)
            ->where('curriculo_id', $curriculo->id)
            ->whereIn('status', [
                CartaOferta::STATUS_PENDENTE_ANEXO,
                CartaOferta::STATUS_AGUARDANDO_RH,
            ])
            ->exists();

        if ($aberta) {
            throw new RuntimeException('Já existe uma carta oferta em andamento para este candidato.');
        }

        $dataInicio = null;
        if (!empty($dados['data_inicio'])) {
            $dataInicio = (new DataHora($dados['data_inicio']))->dataInsert();
        }

        $salario = (new CartaOfertaContextoBuilder())
            ->normalizarSalarioParaPersistencia($dados['salario'] ?? null);

        $vagaProjetoId = $this->resolverVagaProjetoId(
            $vagaAberta->id,
            $empresaId,
            isset($dados['vaga_projeto_id']) ? (int) $dados['vaga_projeto_id'] : null
        );

        /** @var CartaOferta $carta */
        $carta = DB::transaction(function () use ($dados, $empresaId, $usuarioId, $curriculo, $vagaAberta, $dataInicio, $salario, $vagaProjetoId) {
            $publicId = (string) Str::uuid();

            $carta = CartaOferta::create([
                'token' => $publicId,
                'public_id' => $publicId,
                'empresa_id' => $empresaId,
                'user_id' => $usuarioId,
                'curriculo_id' => $curriculo->id,
                'feedback_id' => $dados['feedback_id'] ?? null,
                'vagas_abertas_id' => $vagaAberta->id,
                'vaga_projeto_id' => $vagaProjetoId,
                'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
                'local' => CartaOferta::LOCAL_MYBP,
                'setor' => $dados['setor'] ?? null,
                'salario' => $salario,
                'data_inicio' => $dataInicio,
                'logs' => [[
                    'data' => (new DataHora())->dataHoraInsert(),
                    'mensagem' => 'Carta oferta criada internamente no MyBPIN',
                    'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
                    'usuario' => auth()->user()->nome ?? ('user:' . $usuarioId),
                    'usuario_id' => $usuarioId,
                ]],
            ]);

            return $carta;
        });

        $carta = $carta->fresh(['Curriculo', 'vagaAberta.Vaga']);
        $carta->setRelation(
            'empresa',
            Cliente::withoutGlobalScopes()
                ->select(['id', 'apelido', 'razao_social', 'nome_fantasia', 'cnpj'])
                ->find($empresaId)
        );

        $this->dispararEnvio($carta, $empresaId, $usuarioId);

        return $carta;
    }

    protected function dispararEnvio(CartaOferta $carta, int $empresaId, int $usuarioId): void
    {
        $apelido = $carta->empresa->apelido ?? '';
        $publicId = $carta->public_id ?: $carta->token;
        $link = rtrim((string) env('APP_URL'), '/') . "/{$apelido}/carta-oferta/{$publicId}";

        if (Sistema::assinaturaDigitalHabilitada($empresaId)) {
            try {
                app(\App\Services\AssinaturaDigital\AssinaturaCotaService::class)
                    ->validarDisponibilidadeOrFail($empresaId);
            } catch (RuntimeException $e) {
                throw $e;
            }

            JobProcessarEnvioAssinatura::dispatch(
                JobProcessarEnvioAssinatura::TIPO_CARTA_OFERTA,
                $empresaId,
                $usuarioId,
                ['carta_oferta_id' => (int) $carta->id],
                []
            );

            return;
        }

        JobEnvioCartaOferta::dispatch([
            'nome' => $carta->Curriculo->nome,
            'email' => $carta->Curriculo->email,
            'empresa_id' => $empresaId,
            'url_documento' => $link,
        ]);

        $telefone = app(WhatsappCurriculoTelefoneResolver::class)
            ->resolverPrincipalWhatsapp((int) $carta->curriculo_id);

        if (
            $telefone
            && app(WhatsappNotificationGateService::class)->podeEnviar(
                TipoMensagemWhatsapp::CartaOfertaSgi,
                $empresaId,
            )
        ) {
            $tel = $telefone->sonumero;
            $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
            if ($ambiente != 'prod') {
                $zapTelAtivo = DB::table('zap_numeros')->where('ativo', true)->first();
                $tel = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
            }

            $mensagemWhats = app(WhatsappMessageFactory::class)->render(
                TipoMensagemWhatsapp::CartaOfertaSgi,
                $empresaId,
                [
                    'nome_destinatario' => $carta->Curriculo->nome,
                    'url_carta' => $link,
                    'prazo_resposta' => '24h úteis',
                ]
            );

            (new ZapNotificacao())->enviar([
                'enviado_id' => $usuarioId,
                'telefone' => preg_replace('/[^0-9]/', '', $tel),
                'mensagem' => $mensagemWhats,
                '_whatsapp_meta' => ZapNotificacao::meta(
                    TipoMensagemWhatsapp::CartaOfertaSgi,
                    $empresaId,
                ),
            ]);
        }
    }

    /**
     * Aceita projeto opcional, validando o vínculo com a vaga quando informado.
     */
    protected function resolverVagaProjetoId(int $vagaAbertaId, int $empresaId, ?int $vagaProjetoId): ?int
    {
        if (!$vagaProjetoId) {
            return null;
        }

        $ok = VagaProjeto::query()
            ->where('id', $vagaProjetoId)
            ->where('vaga_aberta_id', $vagaAbertaId)
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$ok) {
            throw new RuntimeException('Projeto inválido para a vaga selecionada.');
        }

        return $vagaProjetoId;
    }
}
