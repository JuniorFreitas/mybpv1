<?php

namespace App\Services\ControleExames;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\ControleExames\JobExame;
use App\Models\AlternativaFormulario;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\RespostaAlternativas;
use Illuminate\Support\Carbon;
use MasterTag\DataHora;
use RuntimeException;

class ExameEncaminhamentoNotificacaoService
{
    public function __construct(
        private readonly WhatsappNotificationGateService $whatsappGate,
        private readonly WhatsappCurriculoTelefoneResolver $telefoneResolver,
        private readonly WhatsappMessageFactory $messageFactory,
    ) {
    }

    /**
     * Reenvia e-mail e/ou WhatsApp do encaminhamento já existente, com as mesmas regras do envio original.
     *
     * @return array{email: bool, whatsapp: bool, mensagens: string[]}
     */
    public function reenviar(ExameFuncionario $exame, bool $enviaEmail, bool $enviaWhatsapp): array
    {
        if (!$enviaEmail && !$enviaWhatsapp) {
            throw new RuntimeException('Selecione ao menos um canal: e-mail ou WhatsApp.');
        }

        if (!$this->estaDentroDoPrazoReenvio($exame)) {
            throw new RuntimeException(
                'Não é possível reenviar: a data de realização já passou (fora do prazo).'
            );
        }

        $exame->loadMissing([
            'EmpresaExame',
            'ExameTipo',
            'Feedback.Curriculo',
            'Feedback.TelPrincipal',
        ]);

        $empExame = $exame->EmpresaExame;
        $colaborador = $exame->Feedback;

        if (!$empExame || !$colaborador || !$colaborador->Curriculo) {
            throw new RuntimeException('Não foi possível carregar os dados do encaminhamento.');
        }

        $tipoExameLabel = $this->resolverTipoExameLabel($exame);
        $token = (string) $exame->token;
        $dataEncaminhamento = (new DataHora())->dataHoraCompleta();
        $dataRealizacao = $this->resolverDataRealizacao($exame);

        $resultado = [
            'email' => false,
            'whatsapp' => false,
            'mensagens' => [],
        ];

        if ($enviaEmail) {
            $this->enviarEmail(
                $exame,
                $colaborador,
                $empExame,
                $tipoExameLabel,
                $token,
                $dataEncaminhamento,
                $dataRealizacao
            );
            $resultado['email'] = true;
            $resultado['mensagens'][] = 'E-mail enfileirado.';
        }

        if ($enviaWhatsapp) {
            $enviado = $this->enviarWhatsapp(
                $colaborador,
                $empExame,
                $tipoExameLabel,
                $dataEncaminhamento,
                $dataRealizacao
            );

            if (!$enviado) {
                \Log::debug('Reenvio WhatsApp de encaminhamento não enviado', [
                    'exame_funcionario_id' => $exame->id,
                    'empresa_id' => $empExame->empresa_id,
                    'user_id' => auth()->id(),
                ]);

                throw new RuntimeException('Não foi possível enviar pelo WhatsApp.');
            }

            $resultado['whatsapp'] = true;
            $resultado['mensagens'][] = 'WhatsApp enviado.';
        }

        return $resultado;
    }

    public function enviarEmail(
        ExameFuncionario $exame,
        FeedbackCurriculo $colaborador,
        EmpresaExame $empExame,
        string $tipoExameLabel,
        string $token,
        string $dataEncaminhamento,
        string $dataRealizacao
    ): void {
        $link = route('publico.encaminhamento_exame_fichapdf', [
            'exame' => $exame,
            'token' => $token,
        ]);

        $dtEmailClinica = [
            'clinica' => $empExame->nome,
            'email' => trim(mb_strtolower((string) ($empExame->dados['email'] ?? ''))),
            'assunto' => "Encaminhamento de Exame {$tipoExameLabel} colaborador {$colaborador->Curriculo->nome}",
            'colaborador' => $colaborador->Curriculo->nome,
            'colaborador_email' => trim(mb_strtolower((string) $colaborador->Curriculo->email)),
            'idade' => $colaborador->Curriculo->idade,
            'tipoExame' => $tipoExameLabel,
            'empresa_id' => $empExame->empresa_id,
            'encaminhamento_data' => $dataEncaminhamento,
            'data_realizacao' => $dataRealizacao,
            'link' => $link,
        ];

        $dtEmailColaborador = [
            'clinica' => $empExame,
            'email' => trim(mb_strtolower((string) $colaborador->Curriculo->email)),
            'assunto' => "Encaminhamento de Exame {$tipoExameLabel}",
            'colaborador' => $colaborador->Curriculo->nome,
            'tipoExame' => $tipoExameLabel,
            'empresa_id' => $empExame->empresa_id,
            'encaminhamento_data' => $dataEncaminhamento,
            'data_realizacao' => $dataRealizacao,
            'link' => $link,
        ];

        JobExame::dispatch([
            'dtEmailClinica' => $dtEmailClinica,
            'dtEmailColaborador' => $dtEmailColaborador,
        ]);
    }

    public function enviarWhatsapp(
        FeedbackCurriculo $colaborador,
        EmpresaExame $empExame,
        string $tipoExameLabel,
        string $dataEncaminhamento,
        string $dataRealizacao
    ): bool {
        $empresaId = (int) (auth()->user()->empresa_id ?? $empExame->empresa_id);

        $podeEnviarWhatsapp = $this->whatsappGate->podeEnviar(
            TipoMensagemWhatsapp::ExameEncaminhamento,
            $empresaId,
        );

        $telefonePrincipal = $this->telefoneResolver->telefonePrincipalPermiteWhatsapp($colaborador->TelPrincipal)
            ? $colaborador->TelPrincipal
            : null;

        if (!$podeEnviarWhatsapp || !$telefonePrincipal) {
            return false;
        }

        $mensagem = $this->messageFactory->render(
            TipoMensagemWhatsapp::ExameEncaminhamento,
            $empresaId,
            [
                'nome_destinatario' => $colaborador->Curriculo->nome,
                'tipo_exame' => $tipoExameLabel,
                'clinica_nome' => $empExame->nome,
                'clinica_endereco' => $empExame->dados['endereco']['endereco_completo'] ?? '',
                'clinica_telefone' => $empExame->dados['telefone'] ?? '',
                'data_encaminhamento' => $dataEncaminhamento,
                'data_realizacao' => $dataRealizacao,
            ]
        );

        (new ZapNotificacao())->enviar([
            'enviado_id' => $colaborador->curriculo_id,
            'telefone' => $telefonePrincipal->sonumero,
            'mensagem' => $mensagem,
            '_whatsapp_meta' => ZapNotificacao::meta(
                TipoMensagemWhatsapp::ExameEncaminhamento,
                $empresaId,
            ),
        ]);

        return true;
    }

    public function resolverTipoExameLabel(ExameFuncionario $exame): string
    {
        if ($exame->exame_tipo_id) {
            if ($exame->relationLoaded('ExameTipo') && $exame->ExameTipo) {
                return (string) $exame->ExameTipo->label;
            }

            $tipo = ExameTipo::find($exame->exame_tipo_id);

            return $tipo ? (string) $tipo->label : 'Exame';
        }

        $empresaId = (int) (auth()->user()->empresa_id ?? $exame->empresa_id);
        $tipoOrdem = AlternativaFormulario::whereNome('Tipo de ordem')
            ->whereEmpresaId($empresaId)
            ->first();

        if (!$tipoOrdem) {
            return 'Exame';
        }

        $valor = $exame->respostas['alternativa_id_' . $tipoOrdem->id]['valor'] ?? null;
        if ($valor === null) {
            return 'Exame';
        }

        $alternativa = RespostaAlternativas::query()
            ->where('alternativa_id', $tipoOrdem->id)
            ->where('value', $valor)
            ->first();

        return $alternativa ? (string) $alternativa->label : 'Exame';
    }

    public function resolverDataRealizacao(ExameFuncionario $exame): string
    {
        $raw = $exame->getAttributes()['encaminhamento_data'] ?? null;
        if ($raw) {
            return (new DataHora($raw))->dataCompleta();
        }

        return (new DataHora($exame->created_at))->dataCompleta();
    }

    /**
     * Reenvio permitido enquanto a data de realização for hoje ou futura (prazo até o dia da realização).
     * Fora do prazo: data de realização anterior ao dia atual.
     */
    public function estaDentroDoPrazoReenvio(ExameFuncionario $exame): bool
    {
        $dataRealizacao = $this->resolverDataRealizacaoCarbon($exame);

        return $dataRealizacao->greaterThanOrEqualTo(now()->startOfDay());
    }

    protected function resolverDataRealizacaoCarbon(ExameFuncionario $exame): Carbon
    {
        $raw = $exame->getAttributes()['encaminhamento_data'] ?? null;
        if ($raw) {
            return Carbon::parse($raw)->startOfDay();
        }

        return Carbon::parse($exame->created_at)->startOfDay();
    }
}
