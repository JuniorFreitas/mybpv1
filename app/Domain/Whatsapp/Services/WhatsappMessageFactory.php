<?php

namespace App\Domain\Whatsapp\Services;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;

class WhatsappMessageFactory
{
    public function __construct(
        private readonly WhatsappConfigService $configService,
        private readonly WhatsappTemplateRenderer $renderer,
    ) {
    }

    public function render(TipoMensagemWhatsapp $tipo, int $empresaId, array $contexto): string
    {
        $contact = $this->configService->resolveContactData($empresaId);
        $global = $this->buildGlobalContext($contact);
        $merged = array_merge($global, $contexto);

        $template = $this->configService->getTemplateCorpo($empresaId, $tipo);

        $mensagem = $this->renderer->render($template, $merged);

        return $this->garantirRodapeMybp($mensagem);
    }

    public function garantirRodapeMybp(string $mensagem): string
    {
        $rodape = trim((string) config('whatsapp_templates.rodape_padrao', ''));

        if ($rodape === '' || $this->mensagemJaContemRodape($mensagem, $rodape)) {
            return $mensagem;
        }

        return rtrim($mensagem) . "\n\n" . $rodape;
    }

    private function mensagemJaContemRodape(string $mensagem, string $rodape): bool
    {
        $normalizar = static function (string $texto): string {
            $texto = str_replace(['*', '_'], '', $texto);

            return preg_replace('/\s+/', ' ', trim($texto)) ?? '';
        };

        return str_contains($normalizar($mensagem), $normalizar($rodape));
    }

    public function preview(TipoMensagemWhatsapp $tipo, int $empresaId, array $contexto = []): string
    {
        $exemplo = $this->contextoExemplo($tipo);
        $merged = array_merge($exemplo, $contexto);

        return $this->render($tipo, $empresaId, $merged);
    }

    /** @return array<string, string> */
    private function buildGlobalContext(array $contact): array
    {
        $assinatura = trim((string) ($contact['texto_assinatura'] ?? ''));

        if ($assinatura === '') {
            $assinatura = '*Equipe ' . ($contact['nome_exibicao'] ?? 'Não informado') . '*';
        }

        $rodape = config('whatsapp_templates.rodape_padrao', '');

        return [
            'empresa_nome' => (string) ($contact['nome_exibicao'] ?? 'Não informado'),
            'empresa_telefone' => (string) ($contact['telefone_contato'] ?? 'Não informado'),
            'empresa_endereco' => (string) ($contact['endereco_completo'] ?? 'Não informado'),
            'assinatura' => $assinatura,
            'rodape_mybp' => $rodape,
        ];
    }

    /** @return array<string, string> */
    private function contextoExemplo(TipoMensagemWhatsapp $tipo): array
    {
        $base = [
            'nome_destinatario' => 'João Silva',
            'vaga_titulo' => 'Auxiliar Administrativo',
            'vaga_cidade' => 'São Luís/MA',
            'data_entrevista' => '25/06/2026',
            'local_entrevista' => 'Av. Principal, 100 — Centro',
            'intro_provas' => 'Parabéns, *João Silva*. Você foi *selecionado(a)*! Você está recebendo um convite para realizar a avaliação abaixo.',
            'links_provas' => 'https://mybp.exemplo/prova/1',
            'tipo_exame' => 'Admissional',
            'clinica_nome' => 'Clínica Exemplo',
            'clinica_endereco' => 'Rua das Flores, 50',
            'clinica_telefone' => '(98) 99999-0000',
            'data_encaminhamento' => '23/06/2026',
            'data_realizacao' => '25/06/2026',
            'url_documentos' => 'https://mybp.exemplo/empresa/documentos',
            'url_carta' => 'https://mybp.exemplo/empresa/carta-oferta/token',
            'observacao' => '',
            'periodo' => '01/07/2026 a 15/07/2026',
            'centro_custo' => 'CC-001',
            'area' => 'Operações',
            'link_sim' => 'https://mybp.exemplo/convocacao/s/token',
            'link_nao' => 'https://mybp.exemplo/convocacao/n/token',
            'prazo_resposta' => '24/06/2026 18:00',
            'rota' => 'Linha 10',
            'bairro' => 'Centro',
            'ponto_referencia' => 'Praça Central',
            'titulo_notificacao' => 'Nova solicitação de férias — sua aprovação é necessária',
            'mensagem_notificacao' => 'Uma nova solicitação foi registrada e está aguardando sua análise. Acesse o sistema para aprovar ou reprovar.',
            'modulo_movimentacao' => 'Férias',
            'url_sistema' => 'https://mybp.exemplo/movimentacao',
        ];

        if ($tipo === TipoMensagemWhatsapp::AdmissaoDocumentos) {
            $base['observacao'] = '';
        }

        return $base;
    }

    public static function buildIntroProvas(string $nome, string $vagaTitulo, int $quantidadeProvas): string
    {
        if ($quantidadeProvas > 1) {
            return "Parabéns, *{$nome}*. Você foi *selecionado(a)*!\n"
                . "Você está recebendo um convite para realizar as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$vagaTitulo}* através da plataforma MyBP.\n"
                . "Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n";
        }

        return "Parabéns, *{$nome}*. Você foi *selecionado(a)*!\n"
            . "Você está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$vagaTitulo}* através da plataforma MyBP.\n"
            . "Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n";
    }

    public static function valorOuNaoInformado(?string $valor): string
    {
        $valor = trim((string) $valor);

        return $valor !== '' ? $valor : 'Não informado';
    }
}
