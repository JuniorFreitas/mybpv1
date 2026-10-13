<?php

namespace App\Services\CartaOferta;

use App\Models\Arquivo;
use App\Models\CartaOferta;
use App\Models\CartaOfertaAceite;
use App\Models\CartaOfertaTemplate;
use App\Models\Sistema;
use App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PDF da carta oferta (template publicado da empresa; sem template, o modelo padrão).
 */
class CartaOfertaPdfService
{
    public function __construct(
        protected CartaOfertaContextoBuilder $contexto,
        protected CartaOfertaTemplateRenderer $renderer,
        protected ComunicadoDispensaSemJustaCausaPdfService $documentoPadrao,
    ) {
    }

    public function gerar(CartaOferta $carta, ?string $solicitante = null): string
    {
        $dados = $this->contexto->paraCarta($carta);
        $nomeCargo = $dados['cargo'] !== CartaOfertaContextoBuilder::NAO_INFORMADO ? $dados['cargo'] : 'Cargo';
        $dadosEmpresa = $this->resolverDadosEmpresa((int) ($carta->empresa_id ?? 0), $dados['empresa'] ?? []);

        $template = CartaOfertaTemplate::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $carta->empresa_id)
            ->publicado()
            ->orderBy('versao', 'desc')
            ->first();

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');

        if ($template) {
            $html = $this->renderer->render($template->conteudo_html, $dados);
            $pdf->loadView('pdf.admissao.carta-oferta-template', [
                'html' => $html,
                'dados_empresa' => $dadosEmpresa,
            ]);
        } else {
            $pdf->loadView('pdf.admissao.carta-oferta', [
                'cartaOferta' => $carta,
                'nomeCargo' => $nomeCargo,
                'dados_empresa' => $dadosEmpresa,
            ]);
        }

        // Carta oferta: rodapé sem o nome de quem gerou.
        $this->documentoPadrao->aplicarPadraoDocumento($pdf, '');

        return $pdf->output();
    }

    /**
     * Proposta "congelada": após o aceite, o PDF fica travado (hash).
     * Enquanto não houver aceite, regenera para refletir o layout/template atual.
     */
    public function propostaCongelada(CartaOferta $carta): Arquivo
    {
        $jaAceita = CartaOfertaAceite::query()
            ->where('carta_oferta_id', $carta->id)
            ->exists();

        if (
            $jaAceita
            && $carta->proposta_arquivo_id
            && ($arquivo = Arquivo::find($carta->proposta_arquivo_id))
        ) {
            return $arquivo;
        }

        $conteudo = $this->gerar($carta);
        $arquivo = $this->guardar($carta, $conteudo, 'proposta', 'Carta Oferta.pdf');

        $carta->forceFill([
            'proposta_arquivo_id' => $arquivo->id,
            'proposta_hash' => hash('sha256', $conteudo),
        ])->saveQuietly();

        return $arquivo;
    }

    public function guardar(CartaOferta $carta, string $conteudo, string $prefixo, string $nome): Arquivo
    {
        $disco = Arquivo::DISCO_DOCUMENTO_ASSINATURA;
        $path = 'carta-oferta/' . $carta->id . '/' . $prefixo . '_' . Str::lower(Str::random(20)) . '.pdf';
        Storage::disk($disco)->put($path, $conteudo);

        return Arquivo::create([
            'quem_enviou' => null,
            'nome' => $nome,
            'imagem' => false,
            'layout' => null,
            'extensao' => '.pdf',
            'file' => $path,
            'thumb' => null,
            'bytes' => strlen($conteudo),
            'temporario' => false,
            'chave' => null,
            'disco' => $disco,
        ]);
    }

    public function conteudo(Arquivo $arquivo): ?string
    {
        return Storage::disk($arquivo->disco)->get($arquivo->file);
    }

    /**
     * @param  array<string, mixed>  $empresaContexto
     * @return array<string, mixed>
     */
    private function resolverDadosEmpresa(int $empresaId, array $empresaContexto = []): array
    {
        $dados = [];
        if ($empresaId > 0) {
            try {
                $dados = Sistema::getEmpresa($empresaId) ?: [];
            } catch (\Throwable $e) {
                $dados = [];
            }
        }

        $razao = trim((string) ($dados['razao_social'] ?? ''));
        $logo = trim((string) ($dados['logo'] ?? ''));

        // Garante texto do cabeçalho mesmo se o cache/logo falhar no worker.
        if ($razao === '') {
            $dados['razao_social'] = trim((string) ($empresaContexto['razao_social'] ?? $empresaContexto['nome_fantasia'] ?? ''));
        }
        if (trim((string) ($dados['nome_fantasia'] ?? '')) === '') {
            $dados['nome_fantasia'] = trim((string) ($empresaContexto['nome_fantasia'] ?? ''));
        }
        if (trim((string) ($dados['cnpj'] ?? '')) === '') {
            $dados['cnpj'] = trim((string) ($empresaContexto['cnpj'] ?? ''));
        }
        if (!isset($dados['empresa_id'])) {
            $dados['empresa_id'] = $empresaId;
        }
        if ($logo === '') {
            $dados['logo'] = '';
        }
        if (!isset($dados['endereco_completo'])) {
            $dados['endereco_completo'] = '';
        }

        return $dados;
    }

}
