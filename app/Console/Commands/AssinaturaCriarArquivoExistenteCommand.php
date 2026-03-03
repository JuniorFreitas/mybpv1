<?php

namespace App\Console\Commands;

use App\Models\DocumentoParaAssinatura;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Console\Command;

class AssinaturaCriarArquivoExistenteCommand extends Command
{
    protected $signature = 'assinatura:criar-existente
        {empresa_id : ID da empresa (clientes.id)}
        {arquivo_id : ID do arquivo PDF existente}
        {tipo_documento : Tipo do documento (ex: documento_demissao)}
        {documentable_type : Classe do model (ex: App\\Models\\DemissaoPrevista)}
        {documentable_id : ID do model relacionado}
        {--solicitante_id= : ID do usuário solicitante}
        {--ordem=sequencial : Ordem (sequencial|paralelo)}
        {--expira= : Data de expiração (YYYY-MM-DD)}
        {--signatario=* : Signatário no formato email:nome:cpf (cpf opcional)}';

    protected $description = 'Cria documento de assinatura a partir de um PDF já existente (arquivo_id).';

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        $arquivoId = (int) $this->argument('arquivo_id');
        $tipoDocumento = (string) $this->argument('tipo_documento');
        $documentableType = (string) $this->argument('documentable_type');
        $documentableId = (int) $this->argument('documentable_id');
        $solicitanteId = $this->option('solicitante_id') ? (int) $this->option('solicitante_id') : null;

        $ordemInput = (string) $this->option('ordem');
        $ordem = $ordemInput === 'paralelo'
            ? DocumentoParaAssinatura::ORDEM_PARALELO
            : DocumentoParaAssinatura::ORDEM_SEQUENCIAL;

        $expira = $this->option('expira');
        $dataExpiracao = $expira ? new \DateTime($expira) : null;

        $signatarios = [];
        $rawSignatarios = (array) $this->option('signatario');
        foreach ($rawSignatarios as $item) {
            $partes = explode(':', $item);
            $email = $partes[0] ?? '';
            $nome = $partes[1] ?? '';
            $cpf = $partes[2] ?? null;
            if (!$email || !$nome) {
                $this->error("Signatário inválido: {$item}. Use email:nome:cpf");
                return 1;
            }
            $signatarios[] = [
                'email' => $email,
                'nome' => $nome,
                'cpf' => $cpf,
            ];
        }
        if (empty($signatarios)) {
            $this->error('Nenhum signatário informado. Use --signatario=email:nome:cpf');
            return 1;
        }

        try {
            $doc = app(AssinaturaDigitalService::class)->criarEnvioComArquivoExistente(
                $empresaId,
                $arquivoId,
                $tipoDocumento,
                $documentableType,
                $documentableId,
                $solicitanteId,
                $signatarios,
                $ordem,
                $dataExpiracao
            );

            $this->info('Documento criado: ID ' . $doc->id);
            $this->info('Signatários: ' . $doc->signatarios->count());
            return 0;
        } catch (\Throwable $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
